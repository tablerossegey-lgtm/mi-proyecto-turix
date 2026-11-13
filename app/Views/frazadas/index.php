<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container py-4">

    <!-- Encabezado / Banner Superior -->
    <div class="p-4 p-md-5 rounded-4 mb-4 text-white position-relative shadow-lg overflow-hidden" 
         style="background: linear-gradient(135deg, #0b0f17 0%, #1a2234 50%, #0f172a 100%); border: 1px solid rgba(255, 193, 7, 0.3);">
        
        <div class="row align-items-center position-relative z-1">
            <div class="col-lg-8">
                <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold text-uppercase mb-2 shadow-sm d-inline-flex align-items-center gap-1">
                    <i class="bi bi-stars"></i> Pedidos por Encargo
                </span>
                <h1 class="display-6 fw-bold mb-2 font-outfit text-white">
                    Catálogo de Frazadas
                </h1>
                <p class="text-white-50 mb-3 fs-6" style="max-width: 650px;">
                    Explora nuestra colección con <strong>más de <?= $totalFrazadas ?> modelos</strong>. Identifica tu número de modelo favorito y encárgalo directamente con nosotros por WhatsApp.
                </p>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="badge rounded-pill bg-dark border border-secondary text-white-50 px-3 py-2 small">
                        <i class="bi bi-check-circle-fill text-success me-1"></i> Diseños Exclusivos
                    </span>
                    <span class="badge rounded-pill bg-dark border border-secondary text-white-50 px-3 py-2 small">
                        <i class="bi bi-whatsapp text-success me-1"></i> Atención Inmediata
                    </span>
                </div>
            </div>

            <!-- Buscador en Vivo por Modelo -->
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card p-3 rounded-4 shadow-sm" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    <label for="inputBusquedaFrazada" class="form-label text-warning small fw-bold mb-2">
                        <i class="bi bi-search me-1"></i> Buscar por Número de Modelo
                    </label>
                    <div class="position-relative">
                        <input type="search" 
                               id="inputBusquedaFrazada"
                               name="q" 
                               value="<?= esc($busqueda) ?>"
                               class="form-control rounded-pill text-white shadow-none" 
                               placeholder="Ej: 15, #42, 120..." 
                               hx-post="<?= base_url('frazadas/buscar') ?>" 
                               hx-trigger="input changed delay:350ms, search" 
                               hx-target="#frazadas-grid-container" 
                               hx-swap="innerHTML"
                               hx-include="#filtroCategoriaFrazada"
                               style="background: rgba(0,0,0,0.45); border: 1.5px solid rgba(255, 193, 7, 0.4); padding-left: 2.6rem; color: #ffffff;">
                        <i class="bi bi-search position-absolute text-warning" style="left: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; opacity: 0.85;"></i>
                    </div>
                    <small class="text-white-50 mt-1 d-block" style="font-size: 0.75rem;">
                        Escribe el número para ver el modelo de inmediato.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros por Categoría (si hay subcarpetas o categorías) -->
    <input type="hidden" id="filtroCategoriaFrazada" name="cat" value="<?= esc($categoriaActiva) ?>">
    <?php if (count($categorias) > 1): ?>
        <div class="mb-4 d-flex flex-wrap gap-2 align-items-center">
            <span class="text-white-50 small me-2"><i class="bi bi-funnel me-1"></i> Categorías:</span>
            <button type="button" 
                    class="btn btn-sm rounded-pill px-3 <?= empty($categoriaActiva) ? 'btn-warning fw-bold text-dark' : 'btn-outline-secondary text-white' ?>"
                    onclick="filtrarCategoriaFrazada('')">
                Todas (<?= $totalFrazadas ?>)
            </button>
            <?php foreach ($categorias as $c): ?>
                <button type="button" 
                        class="btn btn-sm rounded-pill px-3 <?= ($categoriaActiva === $c) ? 'btn-warning fw-bold text-dark' : 'btn-outline-secondary text-white' ?>"
                        onclick="filtrarCategoriaFrazada('<?= esc($c) ?>')">
                    <?= esc($c) ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Contenedor Dinámico de la Cuadrícula -->
    <div id="frazadas-grid-container">
        <?= $this->include('frazadas/_grid') ?>
    </div>

</div>

<!-- Modal Visor en Grande (Lightbox) -->
<div class="modal fade" id="modalFrazada" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content text-white rounded-4 shadow-lg border-0" 
             style="background: #121824; border: 1px solid rgba(255,255,255,0.12);">
            
            <div class="modal-header border-0 pb-0 px-4 pt-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold small" id="modalFrazadaBadge">
                        Modelo
                    </span>
                    <h5 class="modal-title fw-bold text-white mt-1" id="modalFrazadaTitulo">
                        Frazada
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-4 text-center">
                <!-- Imagen en Alta Calidad -->
                <div class="rounded-3 overflow-hidden position-relative mb-3 d-flex align-items-center justify-content-center"
                     style="background: #000; min-height: 380px; max-height: 550px;">
                    <img id="modalFrazadaImg" 
                         src="" 
                         alt="Modelo Frazada" 
                         class="img-fluid rounded" 
                         style="max-height: 520px; width: auto; object-fit: contain;">
                </div>

                <div class="p-3 rounded-3 mb-3 text-start small" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                    <div class="d-flex align-items-center gap-2 text-warning mb-1">
                        <i class="bi bi-info-circle-fill"></i>
                        <span class="fw-bold">Artículo bajo pedido / encargo</span>
                    </div>
                    <div class="text-white-50">
                        Los modelos se apartan y surten sobre pedido. Al dar clic al botón de abajo te comunicaremos con nuestra atención a clientes por WhatsApp con tu modelo seleccionado.
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                    <a id="modalFrazadaBtnWa" 
                       href="#" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="btn btn-success rounded-pill fw-bold px-4 py-2 d-inline-flex align-items-center justify-content-center gap-2 shadow"
                       style="background-color: #25D366; border-color: #25D366;">
                        <i class="bi bi-whatsapp fs-5"></i> Encargar este Modelo por WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estilos específicos para las tarjetas de Frazadas -->
<style>
#inputBusquedaFrazada::placeholder {
    color: rgba(255, 255, 255, 0.65) !important;
    opacity: 1 !important;
}

#inputBusquedaFrazada::-webkit-input-placeholder {
    color: rgba(255, 255, 255, 0.65) !important;
    opacity: 1 !important;
}

#inputBusquedaFrazada::-moz-placeholder {
    color: rgba(255, 255, 255, 0.65) !important;
    opacity: 1 !important;
}

#inputBusquedaFrazada:-ms-input-placeholder {
    color: rgba(255, 255, 255, 0.65) !important;
    opacity: 1 !important;
}

.card-frazada:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.45) !important;
    border-color: rgba(255, 193, 7, 0.5) !important;
}

.frazada-img-wrapper:hover .frazada-thumb {
    transform: scale(1.08);
}

.frazada-thumb {
    transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.frazada-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.45);
    opacity: 0;
    transition: opacity 0.25s ease;
    backdrop-filter: blur(2px);
}

.frazada-img-wrapper:hover .frazada-overlay {
    opacity: 1;
}
</style>

<script>
// Función para abrir el modal con la imagen en grande
function abrirModalFrazada(url, titulo, urlWa) {
    document.getElementById('modalFrazadaImg').src = url;
    document.getElementById('modalFrazadaTitulo').innerText = 'Frazada ' + titulo;
    document.getElementById('modalFrazadaBadge').innerText = titulo;
    document.getElementById('modalFrazadaBtnWa').href = urlWa;

    const modal = new bootstrap.Modal(document.getElementById('modalFrazada'));
    modal.show();
}

// Cambiar de página mediante HTMX
function cambiarPaginaFrazadas(pagina) {
    const q = document.getElementById('inputBusquedaFrazada').value;
    const cat = document.getElementById('filtroCategoriaFrazada').value;

    htmx.ajax('POST', '<?= base_url('frazadas/buscar') ?>', {
        target: '#frazadas-grid-container',
        swap: 'innerHTML',
        values: { q: q, cat: cat, p: pagina }
    });

    // Scroll suave al inicio de la cuadrícula
    const gridEl = document.getElementById('frazadas-grid-container');
    if (gridEl) {
        gridEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Filtrar por categoría
function filtrarCategoriaFrazada(categoria) {
    document.getElementById('filtroCategoriaFrazada').value = categoria;
    const q = document.getElementById('inputBusquedaFrazada').value;

    htmx.ajax('POST', '<?= base_url('frazadas/buscar') ?>', {
        target: '#frazadas-grid-container',
        swap: 'innerHTML',
        values: { q: q, cat: categoria, p: 1 }
    });
}

// Limpiar búsqueda
function limpiarBusquedaFrazadas() {
    const input = document.getElementById('inputBusquedaFrazada');
    if (input) input.value = '';
    filtrarCategoriaFrazada('');
}
</script>
<?= $this->endSection() ?>

