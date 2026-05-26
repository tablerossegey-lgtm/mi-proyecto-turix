<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link class="styles-admin-theme" rel="stylesheet" href="<?= base_url('css/admin.css?v=1.2') ?>">
<style>
    /* Estilos Premium para la modal de creación */
    #modalNuevoProducto .modal-content {
        background: #1f222b !important;
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        border-radius: 1.25rem !important;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3) !important;
    }
    
    #modalNuevoProducto .modal-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
    
    #modalNuevoProducto .modal-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
    
    #modalNuevoProducto .form-label {
        color: #e2e8f0 !important; /* Lighter labels for premium readability */
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    #modalNuevoProducto .form-control,
    #modalNuevoProducto .form-select {
        background-color: #2a2e3d !important; /* Lighter soft-dark container for fields */
        border: 1px solid rgba(255, 255, 255, 0.12) !important;
        border-radius: 0.5rem !important;
        color: #f8fafc !important; /* Crisp white text */
        padding: 0.75rem 1rem !important;
        font-size: 0.9rem !important;
        transition: all 0.25s ease-in-out !important;
    }
    
    #modalNuevoProducto .form-control::placeholder {
        color: rgba(255, 255, 255, 0.45) !important; /* High contrast placeholders */
    }
    
    #modalNuevoProducto .form-control:focus,
    #modalNuevoProducto .form-select:focus {
        border-color: #ffc107 !important;
        box-shadow: 0 0 10px rgba(255, 193, 7, 0.3) !important;
        background-color: #313647 !important;
        color: #ffffff !important;
    }

    /* Estilo premium del file input button native */
    #modalNuevoProducto input[type="file"]::file-selector-button {
        background-color: #ffc107 !important;
        color: #1e293b !important;
        border: none !important;
        border-radius: 0.375rem !important;
        padding: 0.375rem 1rem !important;
        margin-right: 1rem !important;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s ease-in-out !important;
    }
    
    #modalNuevoProducto input[type="file"]::file-selector-button:hover {
        background-color: #e0a800 !important;
        transform: scale(1.03);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <!-- Encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 admin-title">Panel de Administración de Galerías</h2>
            <p class="text-muted mb-0">Gestiona las imágenes adicionales y de galería para todo tu catálogo de productos.</p>
            <div class="admin-subtitle-line"></div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold hover-warning text-dark d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto">
                <i class="fas fa-plus text-dark"></i> Nuevo Producto
            </button>
            <a href="<?= base_url() ?>" class="btn btn-outline-dark rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2">
                <i class="fas fa-arrow-left"></i> Volver al Catálogo
            </a>
        </div>
    </div>

    <!-- Buscador Premium -->
    <div class="card border-0 shadow-sm mb-4 admin-card">
        <div class="card-body p-4">
            <form method="GET" action="<?= base_url('admin/productos') ?>">
                <div class="input-group admin-search-group">
                    <span class="input-group-text admin-search-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="search" 
                           class="form-control py-3 admin-search-input" 
                           placeholder="Buscar producto por SKU o descripción..." 
                           name="q" 
                           value="<?= esc($q ?? '') ?>">
                    <button class="btn btn-warning px-4 fw-bold admin-search-btn" type="submit">Buscar</button>
                    <?php if (!empty($q)): ?>
                        <a href="<?= base_url('admin/productos') ?>" class="btn btn-secondary px-3 d-flex align-items-center justify-content-center admin-search-btn">Limpiar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Notificaciones -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success border-0 rounded-3 shadow-sm py-3 mb-4 d-flex align-items-center gap-2">
            <i class="fas fa-check-circle fs-5 text-success"></i>
            <div><?= session()->getFlashdata('success') ?></div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 rounded-3 shadow-sm py-3 mb-4 d-flex align-items-center gap-2">
            <i class="fas fa-exclamation-circle fs-5 text-danger"></i>
            <div><?= session()->getFlashdata('error') ?></div>
        </div>
    <?php endif; ?>

    <!-- Tabla de Productos -->
    <div class="card border-0 shadow-sm admin-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead class="text-muted admin-table-thead">
                    <tr>
                        <th class="ps-4 py-3 admin-table-th">Foto</th>
                        <th class="py-3 admin-table-th">SKU</th>
                        <th class="py-3 admin-table-th">Descripción</th>
                        <th class="py-3 admin-table-th">Categoría</th>
                        <th class="py-3 text-center admin-table-th">Imágenes Galería</th>
                        <th class="py-3 text-end pe-4 admin-table-th">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productos)): ?>
                        <?php foreach ($productos as $p): ?>
                            <?php 
                                $foto = $p['foto'] ?? '';
                                $isUrl = (strpos($foto, 'http://') === 0 || strpos($foto, 'https://') === 0);
                                if ($isUrl) {
                                    $srcUrl = $foto;
                                } else {
                                    $categoriaFolder = isset($p['nombre_categoria']) ? str_replace(' ', '', ucwords(strtolower($p['nombre_categoria']))) : '';
                                    $rutaImagen = 'uploads/SinImagen.png';
                                    if (!empty($foto)) {
                                        $pathIntento = "uploads/{$categoriaFolder}/" . $foto;
                                        if (file_exists(FCPATH . $pathIntento)) {
                                            $rutaImagen = $pathIntento;
                                        }
                                    }
                                    $srcUrl = base_url($rutaImagen);
                                }
                            ?>
                            <tr class="admin-table-tr">
                                <td class="ps-4 py-3">
                                    <div class="rounded-3 overflow-hidden d-flex align-items-center justify-content-center admin-img-thumb-container">
                                        <img src="<?= $srcUrl ?>" 
                                             alt="<?= esc($p['descripcion']) ?>" 
                                             class="img-fluid admin-img-thumb" 
                                             onerror="this.src='<?= base_url('uploads/SinImagen.png') ?>'; this.onerror=null;">
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="badge text-dark font-monospace px-2.5 py-1.5 admin-sku-badge">
                                        <?= esc($p['codigo_sku']) ?>
                                    </span>
                                </td>
                                <td class="py-3">
                                    <div class="fw-semibold admin-product-title"><?= esc($p['descripcion']) ?></div>
                                    <div class="text-white-50 small mt-1">ID: <?= $p['id'] ?> | Precio: <strong class="text-warning">$<?= number_format($p['precio'], 2) ?></strong></div>
                                </td>
                                <td class="py-3">
                                    <span class="text-white-50 fw-medium"><?= esc($p['nombre_categoria'] ?: 'Sin Categoría') ?></span>
                                </td>
                                <td class="py-3 text-center">
                                    <?php if ($p['total_imagenes'] > 0): ?>
                                        <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-1.5 shadow-sm">
                                            <i class="fas fa-images me-1"></i> <?= $p['total_imagenes'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill px-3 py-1.5 text-muted admin-gallery-badge-empty">
                                            Vacía
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-end pe-4">
                                    <a href="<?= base_url('admin/productos/galeria/' . $p['id']) ?>" 
                                       class="btn btn-warning btn-sm fw-bold px-3 py-2 d-inline-flex align-items-center gap-1.5 rounded-3 shadow-sm hover-warning text-dark">
                                        <i class="fas fa-photo-video text-dark"></i> Galerías
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="fas fa-search fs-1 text-muted opacity-50"></i>
                                </div>
                                <h5 class="fw-bold admin-product-title">No se encontraron productos</h5>
                                <p class="mb-0">Prueba con otro término de búsqueda o SKU.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

    <!-- Modal Nuevo Producto -->
    <div class="modal fade" id="modalNuevoProducto" tabindex="-1" aria-labelledby="modalNuevoProductoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 text-white">
                <div class="modal-header py-3 px-4">
                    <h5 class="modal-title fw-bold text-white" id="modalNuevoProductoLabel">
                        <i class="fas fa-plus-circle me-1 text-warning"></i> Agregar Nuevo Producto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= base_url('admin/productos/crear') ?>" method="POST" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <!-- SKU -->
                            <div class="col-12 col-md-6">
                                <label for="codigo_sku" class="form-label text-white-50 small fw-semibold">SKU *</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control text-white" 
                                           id="codigo_sku" 
                                           name="codigo_sku" 
                                           required 
                                           placeholder="Ej: BOL-EXP-VIA-01">
                                    <button class="btn btn-warning fw-bold px-3 d-flex align-items-center justify-content-center" 
                                            type="button" 
                                            id="btnGenerarSKU" 
                                            title="Sugerir SKU a partir del nombre"
                                            onclick="sugerirSKU();">
                                        <i class="fas fa-magic text-dark"></i> &nbsp;Sugerir
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Categoría -->
                            <div class="col-12 col-md-6">
                                <label for="id_categoria" class="form-label text-white-50 small fw-semibold">Categoría *</label>
                                <select class="form-select text-white" 
                                        id="id_categoria" 
                                        name="id_categoria" 
                                        required>
                                    <option value="" disabled selected>Seleccione una categoría</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['idCategoria'] ?>"><?= esc($cat['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Descripción -->
                            <div class="col-12">
                                <label for="descripcion" class="form-label text-white-50 small fw-semibold">Nombre del Producto *</label>
                                <input type="text" 
                                       class="form-control text-white" 
                                       id="descripcion" 
                                       name="descripcion" 
                                       required 
                                       placeholder="Ej: Bolsa Expandible de Viaje Plegable">
                            </div>

                            <!-- Precio -->
                            <div class="col-12 col-md-4">
                                <label for="precio" class="form-label text-white-50 small fw-semibold">Precio ($) *</label>
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       class="form-control text-white" 
                                       id="precio" 
                                       name="precio" 
                                       required 
                                       placeholder="0.00">
                            </div>

                            <!-- Precio Promoción -->
                            <div class="col-12 col-md-4">
                                <label for="precio_promo" class="form-label text-white-50 small fw-semibold">Precio de Promoción ($)</label>
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       class="form-control text-white" 
                                       id="precio_promo" 
                                       name="precio_promo" 
                                       placeholder="0.00">
                            </div>

                            <!-- Stock -->
                            <div class="col-12 col-md-4">
                                <label for="stock" class="form-label text-white-50 small fw-semibold">Stock (Unidades) *</label>
                                <input type="number" 
                                       min="0" 
                                       class="form-control text-white" 
                                       id="stock" 
                                       name="stock" 
                                       required 
                                       placeholder="0">
                            </div>

                            <!-- Foto Principal -->
                            <div class="col-12">
                                <label for="foto_principal" class="form-label text-white-50 small fw-semibold">Foto Principal del Producto</label>
                                <input type="file" 
                                       class="form-control text-white" 
                                       id="foto_principal" 
                                       name="foto_principal" 
                                       accept="image/jpeg, image/png, image/gif">
                                <p class="text-white-50 small mb-0 mt-1" style="font-size: 0.75rem;">Formatos permitidos: JPG, JPEG, PNG, GIF. Se guardará de manera organizada según la categoría seleccionada.</p>
                            </div>

                            <!-- Más Detalle -->
                            <div class="col-12">
                                <label for="masDetalle" class="form-label text-white-50 small fw-semibold">Más Detalles / Especificaciones</label>
                                <textarea class="form-control text-white" 
                                          style="height: 100px; resize: none;"
                                          id="masDetalle" 
                                          name="masDetalle" 
                                          placeholder="Especificaciones técnicas, colores, dimensiones, etc."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex gap-2">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold hover-warning text-dark">
                            <i class="fas fa-save me-1 text-dark"></i> Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
    document.body.classList.add('admin-body');

    function sugerirSKU() {
        const nombreInput = document.getElementById('descripcion');
        const skuInput = document.getElementById('codigo_sku');
        const categoriaSelect = document.getElementById('id_categoria');
        
        // Validar que se haya seleccionado categoría primero
        if (categoriaSelect.value === "") {
            alert('Por favor, selecciona primero una Categoría para poder incluir su prefijo en el SKU.');
            categoriaSelect.focus();
            return;
        }

        const nombre = nombreInput.value.trim();
        if (!nombre) {
            alert('Por favor, escribe primero el Nombre del Producto para poder sugerir un SKU.');
            nombreInput.focus();
            return;
        }

        // Obtener el texto de la categoría seleccionada (ej. "Juguetes")
        const categoriaTexto = categoriaSelect.options[categoriaSelect.selectedIndex].text;
        let prefijoCat = categoriaTexto.normalize("NFD")
                                        .replace(/[\u0300-\u036f]/g, "") // Quitar acentos
                                        .toUpperCase()
                                        .replace(/[^A-Z0-9]/g, ""); // Quitar espacios/símbolos
        
        // Primeras 3 letras de la categoría (ej. "JUG")
        let prefijo = prefijoCat.substring(0, 3);

        // 1. Limpiar acentos y caracteres especiales del nombre
        let texto = nombre.normalize("NFD")
                          .replace(/[\u0300-\u036f]/g, "") // Quitar acentos
                          .toUpperCase()
                          .replace(/[^A-Z0-9\s]/g, ""); // Quitar símbolos

        // 2. Separar en palabras
        let palabras = texto.split(/\s+/);

        // 3. Palabras de parada (artículos y preposiciones a ignorar)
        const stopWords = new Set([
            'DE', 'LA', 'EL', 'CON', 'Y', 'PARA', 'UN', 'UNA', 'DEL', 'LOS', 'LAS', 'A', 'EN', 
            'POR', 'AL', 'O', 'SU', 'SUS', 'MAS', 'N', 'N°', 'NRO', 'MODELO', 'TIPO', 'CON'
        ]);

        // Filtrar palabras de parada y vacías
        let palabrasClave = palabras.filter(p => p.length > 0 && !stopWords.has(p));

        // Si no quedan palabras clave, usar las originales
        if (palabrasClave.length === 0) {
            palabrasClave = palabras.filter(p => p.length > 0);
        }

        let skuPartes = [];

        // 4. Tomar las primeras 3 letras de cada una de las palabras clave principales (máximo 4 palabras)
        const maxPalabras = 4;
        let palabrasATomar = palabrasClave.slice(0, maxPalabras);

        palabrasATomar.forEach(palabra => {
            if (palabra.length <= 3) {
                skuPartes.push(palabra);
            } else {
                skuPartes.push(palabra.substring(0, 3));
            }
        });

        // 5. Armar SKU sugerido: PREFIJO_CATEGORIA - PALABRAS_CLAVE - SUFIJO
        let skuSugerido = prefijo + '-' + skuPartes.join('-') + '-01';

        // 6. Escribir y animar
        skuInput.value = skuSugerido;
        
        // Animación de brillo de confirmación
        skuInput.style.borderColor = '#28a745';
        skuInput.style.boxShadow = '0 0 12px rgba(40, 167, 69, 0.5)';
        
        setTimeout(() => {
            skuInput.style.borderColor = '#ffc107';
            skuInput.style.boxShadow = '0 0 8px rgba(255, 193, 7, 0.25)';
        }, 1000);
    }
</script>
<?= $this->endSection() ?>
