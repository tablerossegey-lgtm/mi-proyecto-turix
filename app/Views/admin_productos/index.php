<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link class="styles-admin-theme" rel="stylesheet" href="<?= base_url('css/admin.css?v=1.2') ?>">
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
            <a href="<?= base_url() ?>" hx-boost="true" class="btn btn-outline-dark rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2">
                <i class="fas fa-arrow-left"></i> Volver al Catálogo
            </a>
        </div>
    </div>

    <!-- Buscador Premium -->
    <div class="card border-0 shadow-sm mb-4 admin-card">
        <div class="card-body p-4">
            <form hx-post="<?= base_url('admin/productos') ?>" hx-target="#productos-tabla-wrapper" hx-swap="innerHTML" class="m-0">
                <div class="input-group admin-search-group">
                    <span class="input-group-text admin-search-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="search" 
                           class="form-control py-3 admin-search-input" 
                           placeholder="Buscar producto por SKU o descripción..." 
                           name="q" 
                           value="<?= esc($q ?? '') ?>"
                           hx-post="<?= base_url('admin/productos') ?>"
                           hx-trigger="input changed delay:300ms, search"
                           hx-target="#productos-tabla-wrapper"
                           hx-swap="innerHTML"
                           oninput="toggleLimpiarBtn(this.value)">
                    <button class="btn btn-warning px-4 fw-bold admin-search-btn" type="submit">Buscar</button>
                    <button id="btn-limpiar-busqueda" 
                            class="btn btn-secondary px-3 d-flex align-items-center justify-content-center admin-search-btn" 
                            type="button"
                            hx-post="<?= base_url('admin/productos') ?>"
                            hx-vals='{"q": ""}'
                            hx-target="#productos-tabla-wrapper"
                            hx-swap="innerHTML"
                            style="display: <?= !empty($q) ? 'flex' : 'none' ?>;"
                            onclick="document.querySelector('.admin-search-input').value = ''; this.style.display = 'none';">
                        Limpiar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notificaciones flotantes tipo Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
        <?php if (session()->getFlashdata('success')): ?>
            <?php 
                $successMsg = session()->getFlashdata('success');
                $title = '¡Exitoso!';
                if (stripos($successMsg, 'registrado') !== false) {
                    $title = '¡Producto registrado!';
                } elseif (stripos($successMsg, 'actualizado') !== false) {
                    $title = '¡Producto actualizado!';
                }
            ?>
            <div id="toast-success" class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <div>
                            <strong><?= $title ?></strong><br>
                            <span class="small text-white-50"><?= esc($successMsg) ?></span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div id="toast-error" class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-circle-fill text-danger fs-5"></i>
                        <div>
                            <strong>¡Error!</strong><br>
                            <span class="small text-white-50"><?= esc(session()->getFlashdata('error')) ?></span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tabla de Productos Wrapper -->
    <div class="card border-0 shadow-sm admin-card" id="productos-tabla-wrapper">
        <?= $this->include('admin_productos/_tabla_productos') ?>
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
                <form id="formNuevoProducto" action="<?= base_url('admin/productos/crear') ?>" method="POST" enctype="multipart/form-data" hx-boost="true">
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
                                <label class="form-label text-white-50 small fw-semibold">Categoría *</label>
                                <div class="position-relative searchable-select-container" id="container-nuevo-categoria">
                                    <input type="hidden" name="id_categoria" id="id_categoria" required>
                                    <input type="text" 
                                           class="form-control text-white w-100" 
                                           id="categoria_search_input" 
                                           placeholder="Escribe para buscar..." 
                                           autocomplete="off"
                                           required>
                                    <div class="dropdown-menu w-100 p-2 shadow-lg" 
                                         id="categoria_search_dropdown" 
                                         style="max-height: 200px; overflow-y: auto; background-color: #2a2e3d; border: 1px solid rgba(255,255,255,0.12); border-radius: 0.5rem; display: none; position: absolute; z-index: 1060; left: 0; right: 0;">
                                        <?php foreach ($categorias as $cat): ?>
                                            <button type="button" 
                                                    class="dropdown-item text-white border-0 py-2 px-3 rounded-2 text-start categoria-item-option" 
                                                    style="background: transparent; font-size: 0.85rem;"
                                                    data-id="<?= $cat['idCategoria'] ?>"
                                                    data-nombre="<?= esc($cat['nombre']) ?>">
                                                <strong><?= esc($cat['nombre']) ?></strong>
                                            </button>
                                        <?php endforeach; ?>
                                        <div class="text-white-50 p-2 text-center small categoria-search-no-results" style="display: none;">No se encontraron categorías</div>
                                    </div>
                                </div>
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
                            <div class="col-12 col-md-6 col-lg-3">
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
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="precio_promo" class="form-label text-white-50 small fw-semibold">Precio de Promoción ($)</label>
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       class="form-control text-white" 
                                       id="precio_promo" 
                                       name="precio_promo" 
                                       placeholder="0.00">
                            </div>

                            <!-- Stock Casa -->
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="stock_casa" class="form-label text-white-50 small fw-semibold">Stock Casa *</label>
                                <input type="number" 
                                       min="0" 
                                       class="form-control text-white" 
                                       id="stock_casa" 
                                       name="stock_casa" 
                                       required 
                                       value="0"
                                       placeholder="0">
                            </div>

                            <!-- Stock Oficina -->
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="stock_oficina" class="form-label text-white-50 small fw-semibold">Stock Oficina *</label>
                                <input type="number" 
                                       min="0" 
                                       class="form-control text-white" 
                                       id="stock_oficina" 
                                       name="stock_oficina" 
                                       required 
                                       value="0"
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

    <!-- Modal Editar Producto -->
    <div class="modal fade" id="modalEditarProducto" tabindex="-1" aria-labelledby="modalEditarProductoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 text-white">
                <div class="modal-header py-3 px-4">
                    <h5 class="modal-title fw-bold text-white" id="modalEditarProductoLabel">
                        <i class="fas fa-edit me-1 text-warning"></i> Editar Datos del Producto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarProducto" action="" method="POST" enctype="multipart/form-data" hx-boost="true">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <!-- SKU -->
                            <div class="col-12 col-md-6">
                                <label for="edit_codigo_sku" class="form-label text-white-50 small fw-semibold">SKU *</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control text-white" 
                                           id="edit_codigo_sku" 
                                           name="codigo_sku" 
                                           required 
                                           placeholder="Ej: BOL-EXP-VIA-01">
                                    <button class="btn btn-warning fw-bold px-3 d-flex align-items-center justify-content-center" 
                                            type="button" 
                                            id="edit_btnGenerarSKU" 
                                            title="Sugerir SKU a partir del nombre"
                                            onclick="sugerirSKU(true);">
                                        <i class="fas fa-magic text-dark"></i> &nbsp;Sugerir
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Categoría -->
                            <div class="col-12 col-md-6">
                                <label class="form-label text-white-50 small fw-semibold">Categoría *</label>
                                <div class="position-relative searchable-select-container" id="container-editar-categoria">
                                    <input type="hidden" name="id_categoria" id="edit_id_categoria" required>
                                    <input type="text" 
                                           class="form-control text-white w-100" 
                                           id="edit_categoria_search_input" 
                                           placeholder="Escribe para buscar..." 
                                           autocomplete="off"
                                           required>
                                    <div class="dropdown-menu w-100 p-2 shadow-lg" 
                                         id="edit_categoria_search_dropdown" 
                                         style="max-height: 200px; overflow-y: auto; background-color: #2a2e3d; border: 1px solid rgba(255,255,255,0.12); border-radius: 0.5rem; display: none; position: absolute; z-index: 1060; left: 0; right: 0;">
                                        <?php foreach ($categorias as $cat): ?>
                                            <button type="button" 
                                                    class="dropdown-item text-white border-0 py-2 px-3 rounded-2 text-start edit-categoria-item-option" 
                                                    style="background: transparent; font-size: 0.85rem;"
                                                    data-id="<?= $cat['idCategoria'] ?>"
                                                    data-nombre="<?= esc($cat['nombre']) ?>">
                                                <strong><?= esc($cat['nombre']) ?></strong>
                                            </button>
                                        <?php endforeach; ?>
                                        <div class="text-white-50 p-2 text-center small edit-categoria-search-no-results" style="display: none;">No se encontraron categorías</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="col-12">
                                <label for="edit_descripcion" class="form-label text-white-50 small fw-semibold">Nombre del Producto *</label>
                                <input type="text" 
                                       class="form-control text-white" 
                                       id="edit_descripcion" 
                                       name="descripcion" 
                                       required 
                                       placeholder="Ej: Bolsa Expandible de Viaje Plegable">
                            </div>

                            <!-- Precio -->
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="edit_precio" class="form-label text-white-50 small fw-semibold">Precio ($) *</label>
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       class="form-control text-white" 
                                       id="edit_precio" 
                                       name="precio" 
                                       required 
                                       placeholder="0.00">
                            </div>

                            <!-- Precio Promoción -->
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="edit_precio_promo" class="form-label text-white-50 small fw-semibold">Precio de Promoción ($)</label>
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       class="form-control text-white" 
                                       id="edit_precio_promo" 
                                       name="precio_promo" 
                                       placeholder="0.00">
                            </div>

                            <!-- Stock Casa -->
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="edit_stock_casa" class="form-label text-white-50 small fw-semibold">Stock Casa *</label>
                                <input type="number" 
                                       min="0" 
                                       class="form-control text-white" 
                                       id="edit_stock_casa" 
                                       name="stock_casa" 
                                       required 
                                       placeholder="0">
                            </div>

                            <!-- Stock Oficina -->
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="edit_stock_oficina" class="form-label text-white-50 small fw-semibold">Stock Oficina *</label>
                                <input type="number" 
                                       min="0" 
                                       class="form-control text-white" 
                                       id="edit_stock_oficina" 
                                       name="stock_oficina" 
                                       required 
                                       placeholder="0">
                            </div>

                            <!-- Foto Principal -->
                            <div class="col-12">
                                <label for="edit_foto_principal" class="form-label text-white-50 small fw-semibold">Foto Principal (Dejar vacío para conservar la actual)</label>
                                <input type="file" 
                                       class="form-control text-white" 
                                       id="edit_foto_principal" 
                                       name="foto_principal" 
                                       accept="image/jpeg, image/png, image/gif">
                                <p class="text-white-50 small mb-0 mt-1" style="font-size: 0.75rem;">Formatos permitidos: JPG, JPEG, PNG, GIF. Reemplazará la foto principal actual y la moverá de carpeta si cambia de categoría.</p>
                            </div>

                            <!-- Más Detalle -->
                            <div class="col-12">
                                <label for="edit_masDetalle" class="form-label text-white-50 small fw-semibold">Más Detalles / Especificaciones</label>
                                <textarea class="form-control text-white" 
                                          style="height: 100px; resize: none;"
                                          id="edit_masDetalle" 
                                          name="masDetalle" 
                                          placeholder="Especificaciones técnicas, colores, dimensiones, etc."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex gap-2">
                        <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold hover-warning text-dark">
                            <i class="fas fa-save me-1 text-dark"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
    document.body.classList.add('admin-body');

    window.toggleLimpiarBtn = function(val) {
        const btn = document.getElementById('btn-limpiar-busqueda');
        if (btn) {
            btn.style.display = val.trim() !== '' ? 'flex' : 'none';
        }
    };

    window.sugerirSKU = function(isEdit = false) {
        const prefix = isEdit ? 'edit_' : '';
        const nombreInput = document.getElementById(prefix + 'descripcion');
        const skuInput = document.getElementById(prefix + 'codigo_sku');
        const categoriaSelect = document.getElementById(prefix + 'id_categoria');
        const searchInputId = isEdit ? 'edit_categoria_search_input' : 'categoria_search_input';
        const searchInput = document.getElementById(searchInputId);
        
        // Validar que se haya seleccionado categoría primero
        if (categoriaSelect.value === "") {
            alert('Por favor, selecciona primero una Categoría para poder incluir su prefijo en el SKU.');
            if (searchInput) searchInput.focus();
            return;
        }

        const nombre = nombreInput.value.trim();
        if (!nombre) {
            alert('Por favor, escribe primero el Nombre del Producto para poder sugerir un SKU.');
            nombreInput.focus();
            return;
        }

        // Obtener el texto de la categoría seleccionada (ej. "Juguetes") desde el input buscador
        const categoriaTexto = searchInput ? searchInput.value.trim() : '';
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
    };

    window.abrirEditarProducto = function(producto) {
        if (typeof bootstrap === 'undefined') return;
        const modalEl = document.getElementById('modalEditarProducto');
        if (!modalEl) return;
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        
        // Configurar acción del formulario dinámicamente con la URL de edición y procesar con HTMX
        const formEditarObj = document.getElementById('formEditarProducto');
        const actionUrl = "<?= base_url('admin/productos/editar/') ?>" + producto.id;
        formEditarObj.action = actionUrl;
        formEditarObj.setAttribute('action', actionUrl);
        if (typeof htmx !== 'undefined') {
            htmx.process(formEditarObj);
        }
        
        // Rellenar campos del formulario
        document.getElementById('edit_codigo_sku').value = producto.codigo_sku;
        document.getElementById('edit_descripcion').value = producto.descripcion;
        document.getElementById('edit_precio').value = producto.precio;
        document.getElementById('edit_precio_promo').value = producto.precio_promo || '0.00';
        document.getElementById('edit_stock_casa').value = producto.stock_casa !== undefined ? producto.stock_casa : 0;
        document.getElementById('edit_stock_oficina').value = producto.stock_oficina !== undefined ? producto.stock_oficina : 0;
        document.getElementById('edit_masDetalle').value = producto.masDetalle || '';
        
        // Rellenar campo oculto y buscador del selector de categoría
        const hiddenInputEdit = document.getElementById('edit_id_categoria');
        const searchInputEdit = document.getElementById('edit_categoria_search_input');
        const matchingOption = document.querySelector(`.edit-categoria-item-option[data-id="${producto.id_categoria}"]`);
        if (matchingOption) {
            hiddenInputEdit.value = producto.id_categoria;
            searchInputEdit.value = matchingOption.getAttribute('data-nombre');
        } else {
            hiddenInputEdit.value = '';
            searchInputEdit.value = '';
        }
        
        // Limpiar el campo file
        document.getElementById('edit_foto_principal').value = '';

        modal.show();
    };

    // Deshabilitar botón de guardar y mostrar spinner al enviar el formulario para evitar doble submit (validando categoría)
    const formNuevo = document.getElementById('formNuevoProducto');
    if (formNuevo) {
        formNuevo.addEventListener('submit', function(e) {
            const hiddenCat = document.getElementById('id_categoria');
            if (!hiddenCat.value) {
                e.preventDefault();
                alert('Por favor, selecciona una categoría válida de la lista sugerida.');
                document.getElementById('categoria_search_input').focus();
                return;
            }
            const btnSubmit = formNuevo.querySelector('button[type="submit"]');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando...';
            }
        });
    }

    const formEditar = document.getElementById('formEditarProducto');
    if (formEditar) {
        formEditar.addEventListener('submit', function(e) {
            const hiddenCat = document.getElementById('edit_id_categoria');
            if (!hiddenCat.value) {
                e.preventDefault();
                alert('Por favor, selecciona una categoría válida de la lista sugerida.');
                document.getElementById('edit_categoria_search_input').focus();
                return;
            }
            const btnSubmit = formEditar.querySelector('button[type="submit"]');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando...';
            }
        });
    }



    // Inicializar y mostrar toasts de notificaciones del servidor (con soporte para HTMX y auto-limpieza)
    function inicializarToasts() {
        const toasts = document.querySelectorAll('.toast:not(.showing):not(.show)');
        toasts.forEach(toastEl => {
            if (typeof bootstrap !== 'undefined') {
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
                
                // Eliminar el elemento después de que se oculte para que no ensucie el DOM
                toastEl.addEventListener('hidden.bs.toast', () => {
                    toastEl.remove();
                });
            }
        });
    }

    inicializarToasts();
    
    // Escuchar el evento de HTMX tras un swap de contenido para mostrar toasts en peticiones dinámicas
    document.body.addEventListener('htmx:afterSettle', inicializarToasts);

    // ----------------- BUSCADOR DE CATEGORÍAS (TIPO SEARCHABLE SELECT) -----------------
    // Función general para inicializar un buscador de categorías (tipo searchable-select)
    function inicializarBuscadorCategorias(inputId, dropdownId, hiddenId, optionClass, noResultsClass) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        const hidden = document.getElementById(hiddenId);
        const options = dropdown.querySelectorAll('.' + optionClass);
        const noResults = dropdown.querySelector('.' + noResultsClass);

        // Mostrar dropdown al enfocar
        input.addEventListener('focus', function() {
            dropdown.style.display = 'block';
        });

        // Filtrar categorías al escribir
        input.addEventListener('input', function() {
            hidden.value = '';
            const query = input.value.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
            let matches = 0;

            options.forEach(opt => {
                const nombre = opt.getAttribute('data-nombre').normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();

                if (nombre.includes(query)) {
                    opt.style.display = 'block';
                    matches++;
                } else {
                    opt.style.display = 'none';
                }
            });

            if (matches === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        });

        // Seleccionar una opción
        options.forEach(opt => {
            opt.addEventListener('click', function() {
                const id = opt.getAttribute('data-id');
                const nombre = opt.getAttribute('data-nombre');

                hidden.value = id;
                input.value = nombre;
                dropdown.style.display = 'none';
            });
        });

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', function(e) {
            const container = input.closest('.searchable-select-container');
            if (container && !container.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    }

    // Inicializar buscadores de categorías
    inicializarBuscadorCategorias(
        'categoria_search_input',
        'categoria_search_dropdown',
        'id_categoria',
        'categoria-item-option',
        'categoria-search-no-results'
    );

    inicializarBuscadorCategorias(
        'edit_categoria_search_input',
        'edit_categoria_search_dropdown',
        'edit_id_categoria',
        'edit-categoria-item-option',
        'edit-categoria-search-no-results'
    );

    // Resetear al abrir/cerrar modal
    const modalNuevoEl = document.getElementById('modalNuevoProducto');
    if (modalNuevoEl) {
        modalNuevoEl.addEventListener('show.bs.modal', function () {
            document.getElementById('id_categoria').value = '';
            const searchInput = document.getElementById('categoria_search_input');
            searchInput.value = '';
            
            // Restablecer el estado de las opciones del dropdown
            const dropdown = document.getElementById('categoria_search_dropdown');
            dropdown.style.display = 'none';
            dropdown.querySelectorAll('.categoria-item-option').forEach(opt => opt.style.display = 'block');
            dropdown.querySelector('.categoria-search-no-results').style.display = 'none';
        });
    }

    const modalEditarEl = document.getElementById('modalEditarProducto');
    if (modalEditarEl) {
        modalEditarEl.addEventListener('hidden.bs.modal', function () {
            document.getElementById('edit_id_categoria').value = '';
            const searchInput = document.getElementById('edit_categoria_search_input');
            searchInput.value = '';
            
            // Restablecer el estado de las opciones del dropdown
            const dropdown = document.getElementById('edit_categoria_search_dropdown');
            dropdown.style.display = 'none';
            dropdown.querySelectorAll('.edit-categoria-item-option').forEach(opt => opt.style.display = 'block');
            dropdown.querySelector('.edit-categoria-search-no-results').style.display = 'none';
        });
    }
})();
</script>
<?= $this->endSection() ?>
