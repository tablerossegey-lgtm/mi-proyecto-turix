<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link class="styles-admin-theme" rel="stylesheet" href="<?= base_url('css/admin.css?v=1.2') ?>">
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
            <form hx-post="<?= base_url('admin/productos') ?>" hx-target="#productos-tabla-body" hx-swap="innerHTML" class="m-0">
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
                           hx-target="#productos-tabla-body"
                           hx-swap="innerHTML"
                           oninput="toggleLimpiarBtn(this.value)">
                    <button class="btn btn-warning px-4 fw-bold admin-search-btn" type="submit">Buscar</button>
                    <button id="btn-limpiar-busqueda" 
                            class="btn btn-secondary px-3 d-flex align-items-center justify-content-center admin-search-btn" 
                            type="button"
                            hx-post="<?= base_url('admin/productos') ?>"
                            hx-vals='{"q": ""}'
                            hx-target="#productos-tabla-body"
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
                        <th class="py-3 text-center admin-table-th">Encargos Pendientes</th>
                        <th class="py-3 text-end pe-4 admin-table-th">Acciones</th>
                    </tr>
                </thead>
                <tbody id="productos-tabla-body">
                    <?= $this->include('admin_productos/_tabla_productos') ?>
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
                                <div class="dropdown dropdown-search-select" id="dropdownSearchCategoriaNuevo">
                                    <button class="form-select text-white text-start d-flex justify-content-between align-items-center" 
                                            type="button" 
                                            id="btnDropdownCategoriaNuevo" 
                                            data-bs-toggle="dropdown" 
                                            data-bs-auto-close="outside"
                                            aria-expanded="false">
                                        <span>Seleccione una categoría</span>
                                    </button>
                                    <div class="dropdown-menu w-100" aria-labelledby="btnDropdownCategoriaNuevo">
                                        <div class="position-relative">
                                            <input type="text" 
                                                   class="form-control search-input w-100 pe-5" 
                                                   id="inputSearchCategoriaNuevo" 
                                                   placeholder="Buscar categoría..." 
                                                   autocomplete="off">
                                            <button type="button" 
                                                    class="btn-clear-search position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent text-white-50 px-3 d-none" 
                                                    id="btnClearCategoriaNuevo" 
                                                    style="z-index: 10;"
                                                    title="Limpiar búsqueda">
                                                <i class="bi bi-x-lg small"></i>
                                            </button>
                                        </div>
                                        <div class="options-list" id="optionsListCategoriaNuevo">
                                            <?php foreach ($categorias as $cat): ?>
                                                <button class="dropdown-item category-option-btn-nuevo" 
                                                        type="button" 
                                                        data-value="<?= $cat['idCategoria'] ?>" 
                                                        data-name="<?= esc($cat['nombre']) ?>">
                                                    <?= esc($cat['nombre']) ?>
                                                </button>
                                            <?php endforeach; ?>
                                            <div class="text-muted text-center py-2 small d-none no-results-msg">No se encontraron resultados</div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="id_categoria" id="id_categoria" required>
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
                <form id="formEditarProducto" action="" method="POST" enctype="multipart/form-data">
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
                                <div class="dropdown dropdown-search-select" id="dropdownSearchCategoriaEdit">
                                    <button class="form-select text-white text-start d-flex justify-content-between align-items-center" 
                                            type="button" 
                                            id="btnDropdownCategoriaEdit" 
                                            data-bs-toggle="dropdown" 
                                            data-bs-auto-close="outside"
                                            aria-expanded="false">
                                        <span>Seleccione una categoría</span>
                                    </button>
                                    <div class="dropdown-menu w-100" aria-labelledby="btnDropdownCategoriaEdit">
                                        <div class="position-relative">
                                            <input type="text" 
                                                   class="form-control search-input w-100 pe-5" 
                                                   id="inputSearchCategoriaEdit" 
                                                   placeholder="Buscar categoría..." 
                                                   autocomplete="off">
                                            <button type="button" 
                                                    class="btn-clear-search position-absolute end-0 top-50 translate-middle-y border-0 bg-transparent text-white-50 px-3 d-none" 
                                                    id="btnClearCategoriaEdit" 
                                                    style="z-index: 10;"
                                                    title="Limpiar búsqueda">
                                                <i class="bi bi-x-lg small"></i>
                                            </button>
                                        </div>
                                        <div class="options-list" id="optionsListCategoriaEdit">
                                            <?php foreach ($categorias as $cat): ?>
                                                <button class="dropdown-item category-option-btn-edit" 
                                                        type="button" 
                                                        data-value="<?= $cat['idCategoria'] ?>" 
                                                        data-name="<?= esc($cat['nombre']) ?>">
                                                    <?= esc($cat['nombre']) ?>
                                                </button>
                                            <?php endforeach; ?>
                                            <div class="text-muted text-center py-2 small d-none no-results-msg">No se encontraron resultados</div>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="id_categoria" id="edit_id_categoria" required>
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
                            <div class="col-12 col-md-4">
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
                            <div class="col-12 col-md-4">
                                <label for="edit_precio_promo" class="form-label text-white-50 small fw-semibold">Precio de Promoción ($)</label>
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       class="form-control text-white" 
                                       id="edit_precio_promo" 
                                       name="precio_promo" 
                                       placeholder="0.00">
                            </div>

                            <!-- Stock -->
                            <div class="col-12 col-md-4">
                                <label for="edit_stock" class="form-label text-white-50 small fw-semibold">Stock (Unidades) *</label>
                                <input type="number" 
                                       min="0" 
                                       class="form-control text-white" 
                                       id="edit_stock" 
                                       name="stock" 
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

<script>
    document.body.classList.add('admin-body');

    function toggleLimpiarBtn(val) {
        const btn = document.getElementById('btn-limpiar-busqueda');
        if (btn) {
            btn.style.display = val.trim() !== '' ? 'flex' : 'none';
        }
    }

    function sugerirSKU(isEdit = false) {
        const prefix = isEdit ? 'edit_' : '';
        const nombreInput = document.getElementById(prefix + 'descripcion');
        const skuInput = document.getElementById(prefix + 'codigo_sku');
        const categoriaSelect = document.getElementById(prefix + 'id_categoria');
        
        // Validar que se haya seleccionado categoría primero
        if (categoriaSelect.value === "") {
            alert('Por favor, selecciona primero una Categoría para poder incluir su prefijo en el SKU.');
            // Abrir el dropdown de categoría correspondiente para guiar al usuario
            const btnFocusId = isEdit ? 'btnDropdownCategoriaEdit' : 'btnDropdownCategoriaNuevo';
            const btnFocus = document.getElementById(btnFocusId);
            if (btnFocus) btnFocus.focus();
            return;
        }

        const nombre = nombreInput.value.trim();
        if (!nombre) {
            alert('Por favor, escribe primero el Nombre del Producto para poder sugerir un SKU.');
            nombreInput.focus();
            return;
        }

        // Obtener el texto de la categoría seleccionada (ej. "Juguetes") desde el botón del dropdown
        const btnId = isEdit ? 'btnDropdownCategoriaEdit' : 'btnDropdownCategoriaNuevo';
        const btnDropdown = document.getElementById(btnId);
        const categoriaTexto = btnDropdown ? btnDropdown.querySelector('span').innerText : '';
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

    function abrirEditarProducto(producto) {
        const modal = new bootstrap.Modal(document.getElementById('modalEditarProducto'));
        
        // Configurar acción del formulario dinámicamente con la URL de edición
        document.getElementById('formEditarProducto').action = "<?= base_url('admin/productos/editar/') ?>" + producto.id;
        
        // Rellenar campos del formulario
        document.getElementById('edit_codigo_sku').value = producto.codigo_sku;
        document.getElementById('edit_descripcion').value = producto.descripcion;
        document.getElementById('edit_precio').value = producto.precio;
        document.getElementById('edit_precio_promo').value = producto.precio_promo || '0.00';
        document.getElementById('edit_stock').value = producto.stock;
        document.getElementById('edit_masDetalle').value = producto.masDetalle || '';
        
        // Rellenar campo oculto y botón del selector de categoría
        const hiddenInputEdit = document.getElementById('edit_id_categoria');
        const btnDropdownEdit = document.getElementById('btnDropdownCategoriaEdit');
        const matchingBtn = document.querySelector(`.category-option-btn-edit[data-value="${producto.id_categoria}"]`);
        if (matchingBtn) {
            hiddenInputEdit.value = producto.id_categoria;
            btnDropdownEdit.querySelector('span').innerText = matchingBtn.getAttribute('data-name');
        } else {
            hiddenInputEdit.value = '';
            btnDropdownEdit.querySelector('span').innerText = 'Seleccione una categoría';
        }
        
        // Limpiar el campo file
        document.getElementById('edit_foto_principal').value = '';

        modal.show();
    }

    // Deshabilitar botón de guardar y mostrar spinner al enviar el formulario para evitar doble submit
    document.addEventListener("DOMContentLoaded", function() {
        const formNuevo = document.getElementById('formNuevoProducto');
        if (formNuevo) {
            formNuevo.addEventListener('submit', function() {
                const btnSubmit = formNuevo.querySelector('button[type="submit"]');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando...';
                }
            });
        }

        const formEditar = document.getElementById('formEditarProducto');
        if (formEditar) {
            formEditar.addEventListener('submit', function() {
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
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
                
                // Eliminar el elemento después de que se oculte para que no ensucie el DOM
                toastEl.addEventListener('hidden.bs.toast', () => {
                    toastEl.remove();
                });
            });
        }

        inicializarToasts();
        
        // Escuchar el evento de HTMX tras un swap de contenido para mostrar toasts en peticiones dinámicas
        document.body.addEventListener('htmx:afterSettle', inicializarToasts);

        // ----------------- BUSCADOR DE CATEGORÍAS (NUEVO PRODUCTO) -----------------
        const btnDropdownNuevo = document.getElementById('btnDropdownCategoriaNuevo');
        const inputSearchNuevo = document.getElementById('inputSearchCategoriaNuevo');
        const hiddenInputNuevo = document.getElementById('id_categoria');
        const optionBtnsNuevo = document.querySelectorAll('.category-option-btn-nuevo');
        const dropdownSearchNuevo = document.getElementById('dropdownSearchCategoriaNuevo');
        const noResultsNuevo = dropdownSearchNuevo.querySelector('.no-results-msg');

        const btnClearNuevo = document.getElementById('btnClearCategoriaNuevo');

        // Filtrado en tiempo real
        inputSearchNuevo.addEventListener('input', function() {
            const filter = this.value.toLowerCase().trim();
            let found = false;
            
            // Mostrar/ocultar botón limpiar
            if (this.value.length > 0) {
                btnClearNuevo.classList.remove('d-none');
            } else {
                btnClearNuevo.classList.add('d-none');
            }

            optionBtnsNuevo.forEach(btn => {
                const text = btn.getAttribute('data-name').toLowerCase();
                if (text.includes(filter)) {
                    btn.classList.remove('d-none');
                    found = true;
                } else {
                    btn.classList.add('d-none');
                }
            });
            if (found) {
                noResultsNuevo.classList.add('d-none');
            } else {
                noResultsNuevo.classList.remove('d-none');
            }
        });

        // Evento de click para limpiar búsqueda
        btnClearNuevo.addEventListener('click', function(e) {
            e.stopPropagation(); // Evitar cerrar dropdown
            inputSearchNuevo.value = '';
            this.classList.add('d-none');
            inputSearchNuevo.dispatchEvent(new Event('input'));
            inputSearchNuevo.focus();
        });

        // Evento de selección
        optionBtnsNuevo.forEach(btn => {
            btn.addEventListener('click', function() {
                const val = this.getAttribute('data-value');
                const name = this.getAttribute('data-name');
                hiddenInputNuevo.value = val;
                btnDropdownNuevo.querySelector('span').innerText = name;
                
                // Cerrar dropdown
                const dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(btnDropdownNuevo);
                dropdownInstance.hide();
            });
        });

        // Resetear al abrir/cerrar modal
        const modalNuevoEl = document.getElementById('modalNuevoProducto');
        modalNuevoEl.addEventListener('show.bs.modal', function () {
            inputSearchNuevo.value = '';
            btnClearNuevo.classList.add('d-none');
            optionBtnsNuevo.forEach(btn => btn.classList.remove('d-none'));
            noResultsNuevo.classList.add('d-none');
            hiddenInputNuevo.value = '';
            btnDropdownNuevo.querySelector('span').innerText = 'Seleccione una categoría';
        });


        // ----------------- BUSCADOR DE CATEGORÍAS (EDITAR PRODUCTO) -----------------
        const btnDropdownEdit = document.getElementById('btnDropdownCategoriaEdit');
        const inputSearchEdit = document.getElementById('inputSearchCategoriaEdit');
        const hiddenInputEdit = document.getElementById('edit_id_categoria');
        const optionBtnsEdit = document.querySelectorAll('.category-option-btn-edit');
        const dropdownSearchEdit = document.getElementById('dropdownSearchCategoriaEdit');
        const noResultsEdit = dropdownSearchEdit.querySelector('.no-results-msg');

        const btnClearEdit = document.getElementById('btnClearCategoriaEdit');

        // Filtrado en tiempo real
        inputSearchEdit.addEventListener('input', function() {
            const filter = this.value.toLowerCase().trim();
            let found = false;
            
            // Mostrar/ocultar botón limpiar
            if (this.value.length > 0) {
                btnClearEdit.classList.remove('d-none');
            } else {
                btnClearEdit.classList.add('d-none');
            }

            optionBtnsEdit.forEach(btn => {
                const text = btn.getAttribute('data-name').toLowerCase();
                if (text.includes(filter)) {
                    btn.classList.remove('d-none');
                    found = true;
                } else {
                    btn.classList.add('d-none');
                }
            });
            if (found) {
                noResultsEdit.classList.add('d-none');
            } else {
                noResultsEdit.classList.remove('d-none');
            }
        });

        // Evento de click para limpiar búsqueda
        btnClearEdit.addEventListener('click', function(e) {
            e.stopPropagation(); // Evitar cerrar dropdown
            inputSearchEdit.value = '';
            this.classList.add('d-none');
            inputSearchEdit.dispatchEvent(new Event('input'));
            inputSearchEdit.focus();
        });

        // Evento de selección
        optionBtnsEdit.forEach(btn => {
            btn.addEventListener('click', function() {
                const val = this.getAttribute('data-value');
                const name = this.getAttribute('data-name');
                hiddenInputEdit.value = val;
                btnDropdownEdit.querySelector('span').innerText = name;
                
                // Cerrar dropdown
                const dropdownInstance = bootstrap.Dropdown.getOrCreateInstance(btnDropdownEdit);
                dropdownInstance.hide();
            });
        });

        // Resetear al abrir/cerrar modal
        const modalEditarEl = document.getElementById('modalEditarProducto');
        modalEditarEl.addEventListener('show.bs.modal', function () {
            inputSearchEdit.value = '';
            btnClearEdit.classList.add('d-none');
            optionBtnsEdit.forEach(btn => btn.classList.remove('d-none'));
            noResultsEdit.classList.add('d-none');
        });
    });
</script>
<?= $this->endSection() ?>
