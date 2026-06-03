<?php
// Detectar si estamos en una categoría específica basada en la URL actual o una variable pasada
$uri = service('uri');
$categoriaId = ($uri->getSegment(1) === 'catalogo' && $uri->getSegment(2) === 'categoria') ? $uri->getSegment(3) : null;
// Si la variable $categoria_id fue pasada explícitamente desde el controlador, la usamos:
if (isset($categoria_id)) {
    $categoriaId = $categoria_id;
}
$searchUrl = base_url('catalogo/buscar' . ($categoriaId ? '/' . $categoriaId : ''));

// Escaneo dinámico de subcarpetas si estamos en una categoría
$subcarpetas = [];
$categoriaNombre = '';
if ($categoriaId) {
    // Cargar la categoría para saber el nombre de la carpeta
    $categoriaModel = new \App\Models\CategoriaModel();
    $catData = $categoriaModel->find($categoriaId);
    if ($catData) {
        $categoriaNombre = $catData['nombre'];
        $categoriaFolder = str_replace(' ', '', ucwords(strtolower($categoriaNombre)));
        $dirPath = FCPATH . 'uploads/' . $categoriaFolder;
        if (is_dir($dirPath)) {
            $files = scandir($dirPath);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && is_dir($dirPath . '/' . $file)) {
                    $subcarpetas[] = $file;
                }
            }
        }
    }
}
?>
<div class="row mb-4 align-items-center">
    <div class="col-md-6 mb-3 mb-md-0">
        <h2 class="fw-bold titulo-seccion mb-0" style="color: var(--turix-dark);"><?= $titulo ?? 'Productos' ?></h2>
        <div style="width: 60px; height: 4px; background: var(--turix-accent); margin-top: 10px; border-radius: 2px;"></div>
    </div>
    <div class="col-md-6">
        <div class="position-relative">
            <input type="search" 
                   class="form-control rounded-pill shadow-sm" 
                   placeholder="Buscar productos..." 
                   name="q"
                   hx-post="<?= $searchUrl ?>" 
                   hx-trigger="input changed delay:500ms" 
                   hx-target="#productos-grid"
                   hx-swap="innerHTML"
                   style="padding-left: 2.5rem; padding-right: 1rem; border: 1px solid #cbd5e1; font-size: 0.95rem;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="position-absolute" style="top: 50%; left: 1rem; transform: translateY(-50%); color: #64748b;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </div>
    </div>
</div>

<?php if (!empty($subcarpetas)): ?>
    <!-- Filtros premium dinámicos por subcarpetas -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-center flex-wrap">
            <div class="btn-group shadow-sm bg-white p-1 rounded-pill flex-wrap justify-content-center" role="group" aria-label="Filtrar Subcategorías">
                <button type="button" class="btn btn-turix-filter active rounded-pill px-4 py-2" data-filter="all">
                    <i class="fas fa-th-large me-2"></i>Todos
                </button>
                <?php foreach ($subcarpetas as $sub): ?>
                    <?php 
                        $subCss = str_replace(
                            ['á', 'é', 'í', 'ó', 'ú', 'ñ', ' '], 
                            ['a', 'e', 'i', 'o', 'u', 'n', ''], 
                            strtolower($sub)
                        );
                        // Emojis representativos
                        $emoji = '✨ ';
                        $subLower = strtolower($sub);
                        if (strpos($subLower, 'navidad') !== false || strpos($subLower, 'navide') !== false) {
                            $emoji = '🎄 ';
                        } elseif (strpos($subLower, 'valentin') !== false || strpos($subLower, 'amor') !== false) {
                            $emoji = '❤️ ';
                        } elseif (strpos($subLower, 'cumple') !== false || strpos($subLower, 'cumpleanos') !== false) {
                            $emoji = '🎂 ';
                        }
                    ?>
                    <button type="button" class="btn btn-turix-filter rounded-pill px-4 py-2" data-filter="<?= $subCss ?>">
                        <?= $emoji ?><?= esc($sub) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row g-4" id="productos-grid">
    <?= $this->include('catalogo/_grid_productos') ?>
</div>

<div class="row mt-5">
    <div class="col-12 text-center">
        <button class="btn btn-outline-dark rounded-pill px-5 py-2 fw-bold shadow-sm" 
            hx-get="<?= base_url('categorias') ?>"
            hx-target="#main-content"
            style="border-width: 2px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="m15 18-6-6 6-6"/></svg>
            Volver a Categorías
        </button>
    </div>
</div>

<?php if (!empty($subcarpetas)): ?>
<script>
    // Inicializar al cargar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSubcatFilters);
    } else {
        initSubcatFilters();
    }

    // Reinicializar al ocurrir swaps de HTMX para mantener el filtrado vivo
    document.body.addEventListener('htmx:afterSwap', function(evt) {
        if (evt.detail.target.id === 'productos-grid' || evt.detail.target.id === 'main-content') {
            initSubcatFilters();
        }
    });

    function initSubcatFilters() {
        const filterButtons = document.querySelectorAll('.btn-turix-filter');
        if (filterButtons.length === 0) return;

        // Mantener la categoría seleccionada al buscar
        let activeFilter = 'all';
        const currentActive = document.querySelector('.btn-turix-filter.active');
        if (currentActive) {
            activeFilter = currentActive.getAttribute('data-filter');
        }

        filterButtons.forEach(button => {
            // Clonar para limpiar handlers anteriores y evitar fugas de memoria o múltiples llamadas
            const newBtn = button.cloneNode(true);
            button.parentNode.replaceChild(newBtn, button);
            
            // Si coincide con el filtro activo, mantener activo
            if (newBtn.getAttribute('data-filter') === activeFilter) {
                newBtn.classList.add('active');
            } else {
                newBtn.classList.remove('active');
            }

            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Desactivar botones y activar el seleccionado
                document.querySelectorAll('.btn-turix-filter').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                const filterValue = this.getAttribute('data-filter');
                const products = document.querySelectorAll('.prod-filtrable');
                
                products.forEach(p => {
                    if (filterValue === 'all') {
                        p.style.display = 'block';
                    } else {
                        if (p.classList.contains('prod-subcat-' + filterValue)) {
                            p.style.display = 'block';
                        } else {
                            p.style.display = 'none';
                        }
                    }
                });
            });
        });

        // Aplicar el filtro actual al redibujar
        const products = document.querySelectorAll('.prod-filtrable');
        products.forEach(p => {
            if (activeFilter === 'all') {
                p.style.display = 'block';
            } else {
                if (p.classList.contains('prod-subcat-' + activeFilter)) {
                    p.style.display = 'block';
                } else {
                    p.style.display = 'none';
                }
            }
        });
    }
</script>
<?php endif; ?>