<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('css/admin.css?v=1.2') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <!-- Encabezado / Migas de pan -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <div class="mb-1">
                <a href="<?= base_url('admin/productos') ?>" class="text-warning fw-bold text-decoration-none small">
                    <i class="fas fa-chevron-left me-1"></i> Volver a Productos
                </a>
            </div>
            <h2 class="fw-bold mb-1 admin-title">Administrar Galería del Producto</h2>
            <p class="text-muted mb-0">Agrega imágenes adicionales o elimina las existentes para ampliar la galería de este producto.</p>
            <div class="admin-subtitle-line"></div>
        </div>
    </div>

    <!-- Notificaciones flotantes tipo Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
        <?php if (session()->getFlashdata('success')): ?>
            <?php
                $successMsg = session()->getFlashdata('success');
                $title = '¡Exitoso!';
                if (stripos($successMsg, 'cargaron') !== false || stripos($successMsg, 'subieron') !== false) {
                    $title = '¡Imágenes guardadas!';
                } elseif (stripos($successMsg, 'principal') !== false) {
                    $title = '¡Imagen actualizada!';
                } elseif (stripos($successMsg, 'eliminada') !== false) {
                    $title = '¡Imagen eliminada!';
                } elseif (stripos($successMsg, 'orden') !== false) {
                    $title = '¡Orden actualizado!';
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

    <div class="row g-4">
        <!-- Columna de Detalles del Producto -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden p-4 h-100 admin-card">
                <h5 class="fw-bold mb-3 border-bottom pb-2 admin-title border-secondary-subtle">
                    <i class="fas fa-info-circle me-1 text-warning"></i> Info del Producto
                </h5>
                
                <!-- Imagen Principal -->
                <div class="product-image-container rounded-4 mb-3 d-flex align-items-center justify-content-center p-3 admin-info-img-container">
                    <?php 
                        $srcUrl = obtener_ruta_imagen($p['foto'] ?? '', $p['nombre_categoria'] ?? '');
                    ?>
                    <img src="<?= $srcUrl ?>" 
                         alt="<?= esc($p['descripcion']) ?>" 
                         class="img-fluid rounded-3 admin-info-img" 
                         onerror="this.src='<?= base_url('uploads/SinImagen.png') ?>'; this.onerror=null;">
                </div>

                <!-- Modificar Imagen Principal -->
                <div class="mb-3 text-center">
                    <form action="<?= base_url('admin/productos/cambiar-principal/' . $p['id']) ?>" method="POST" enctype="multipart/form-data" id="form-foto-principal" class="m-0" hx-boost="true">
                        <label for="fotoPrincipalInput" class="btn btn-cambiar-principal w-100 fw-bold py-2 rounded-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-camera fs-6"></i> Cambiar Imagen Principal
                        </label>
                        <input type="file" name="foto_principal" id="fotoPrincipalInput" class="d-none" accept="image/jpeg, image/png, image/gif" onchange="if(typeof this.form.requestSubmit === 'function') { this.form.requestSubmit(); } else { this.form.submit(); }">
                    </form>
                </div>


                <div class="mb-2">
                    <span class="text-muted small">SKU:</span>
                    <div class="font-monospace text-warning fw-semibold fs-5"><?= esc($p['codigo_sku']) ?></div>
                </div>

                <div class="mb-2">
                    <span class="text-muted small">Descripción:</span>
                    <div class="fw-bold fs-5 admin-title"><?= esc($p['descripcion']) ?></div>
                </div>

                <div class="mb-3">
                    <span class="text-muted small">Categoría:</span>
                    <div class="fw-medium text-secondary"><?= esc($p['nombre_categoria'] ?: 'Sin Categoría') ?></div>
                </div>

                <div class="row g-3 mb-2">
                    <div class="col-6">
                        <div class="admin-stat-card admin-stat-card-price">
                            <div class="admin-stat-label">
                                <i class="fas fa-tag"></i> Precio
                            </div>
                            <div class="admin-stat-value">$<?= number_format($p['precio'], 2) ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="admin-stat-card admin-stat-card-stock">
                            <div class="admin-stat-label">
                                <i class="fas fa-cubes"></i> Stock
                            </div>
                            <?php if ((int)$p['stock'] === 0): ?>
                                <div class="admin-stat-value text-danger">Agotado</div>
                            <?php else: ?>
                                <div class="admin-stat-value text-success"><?= $p['stock'] ?> <span class="admin-stat-unit">pzs</span></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna de Carga y Galería de Imágenes -->
        <div class="col-12 col-lg-8">
            <!-- Sección de Carga -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 admin-card">
                <h5 class="fw-bold mb-3 admin-title"><i class="fas fa-cloud-upload-alt me-1 text-warning"></i> Cargar Imágenes Nuevas</h5>
                <form id="formSubirImagenes" action="<?= base_url('admin/productos/subir-imagen/' . $p['id']) ?>" method="POST" enctype="multipart/form-data" hx-boost="true">
                    <div class="drag-zone p-4 rounded-4 text-center d-flex flex-column align-items-center justify-content-center border-dashed admin-drag-zone">
                        <input type="file" name="imagenes[]" id="fileInput" multiple accept="image/jpeg, image/png" class="d-none">
                        <div class="mb-3 icon-container">
                            <i class="fas fa-images fs-1 text-warning opacity-70"></i>
                        </div>
                        <h6 class="fw-bold admin-title">Haz clic aquí para seleccionar archivos</h6>
                        <p class="text-muted small mb-0">Formatos permitidos: JPG, JPEG, PNG. Puedes seleccionar múltiples fotos.</p>
                        <div id="fileList" class="mt-3 text-warning fw-semibold small w-100 text-center"></div>
                    </div>
                    <div class="d-grid mt-3">
                        <button type="submit" class="btn btn-warning py-3 fw-bold rounded-3 shadow hover-warning text-dark" id="uploadBtn" disabled>
                            <i class="fas fa-upload me-1 text-dark"></i> Guardar Imágenes en Galería
                        </button>
                    </div>
                </form>
            </div>

            <!-- Galería de Fotos Existentes -->
            <div class="card border-0 shadow-sm rounded-4 p-4 admin-card">
                <h5 class="fw-bold mb-4 border-bottom pb-2 admin-title border-secondary-subtle">
                    <i class="fas fa-images me-1 text-warning"></i> Imágenes de Galería Existentes
                </h5>

                <?php if (!empty($imagenes_adicionales)): ?>
                    <div class="row g-3 row-cols-2 row-cols-sm-3 row-cols-md-4">
                        <?php foreach ($imagenes_adicionales as $img): ?>
                            <?php 
                                $foto_src = obtener_ruta_imagen($img['ruta_foto'] ?? '', $p['nombre_categoria'] ?? '');
                            ?>
                            <div class="col">
                                <div class="card border-0 rounded-4 overflow-hidden position-relative bg-light shadow-sm gallery-card admin-gallery-card h-100">
                                    <!-- Preview Image -->
                                    <div class="ratio ratio-1x1 overflow-hidden admin-gallery-card-ratio">
                                        <img src="<?= $foto_src ?>" 
                                             class="img-fluid object-fit-contain" 
                                             alt="Imagen adicional"
                                             onerror="this.src='<?= base_url('uploads/SinImagen.png') ?>'; this.onerror=null;">
                                    </div>

                                    <!-- Botón de Borrar (Floating Icon) -->
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <button type="button" 
                                                class="btn btn-danger btn-sm rounded-circle d-flex align-items-center justify-content-center shadow admin-gallery-delete-btn" 
                                                title="Eliminar esta foto"
                                                onclick="confirmarEliminarImagen('<?= base_url('admin/productos/eliminar-imagen/' . $img['id']) ?>')">
                                            <i class="fas fa-trash-alt fs-6"></i>
                                        </button>
                                    </div>
                                     <div class="p-2 text-center bg-white border-top" style="color: #475569 !important;">
                                         <form action="<?= base_url('admin/productos/actualizar-orden/' . $img['id']) ?>" method="POST" class="m-0 d-flex align-items-center justify-content-center gap-2" hx-boost="true">
                                             <label for="orden-input-<?= $img['id'] ?>" class="small font-monospace m-0 fw-semibold">Orden:</label>
                                             <input type="number" 
                                                    id="orden-input-<?= $img['id'] ?>"
                                                    name="orden" 
                                                    value="<?= $img['orden'] ?>" 
                                                    class="form-control form-control-sm text-center py-0 px-1 font-monospace fw-bold" 
                                                    style="width: 55px; height: 26px; border: 1px solid #cbd5e1; border-radius: 4px; background-color: #f8fafc;" 
                                                    min="1" 
                                                    required 
                                                    onchange="if(typeof this.form.requestSubmit === 'function') { this.form.requestSubmit(); } else { this.form.submit(); }">
                                         </form>
                                     </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <div class="mb-3">
                            <i class="far fa-images fs-1 text-muted opacity-50"></i>
                        </div>
                        <h6 class="fw-bold admin-title">No hay imágenes en la galería</h6>
                        <p class="small mb-0">Sube imágenes usando el formulario superior para crear la galería.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 text-white" style="background-color: #1a252f; border-radius: 16px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 py-3 px-4">
                <h5 class="modal-title fw-bold text-white" id="confirmDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-0 text-white-50">¿Estás seguro de que deseas eliminar esta imagen de la galería? Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer border-top border-secondary border-opacity-25 d-flex gap-2">
                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <form id="formConfirmarEliminarImagen" action="" hx-post="" hx-target="body" method="POST" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                        <i class="fas fa-trash-alt me-1"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    document.body.classList.add('admin-body');

    // Registrar la función globalmente para el onclick
    window.confirmarEliminarImagen = function(url) {
        const form = document.getElementById('formConfirmarEliminarImagen');
        if (form) {
            form.action = url;
            form.setAttribute('action', url);
            form.setAttribute('hx-post', url);
            if (typeof htmx !== 'undefined') {
                htmx.process(form);
            }
        }
        const modalEl = document.getElementById('confirmDeleteModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        } else {
            // Fallback en caso de que bootstrap no esté cargado
            if (confirm('¿Estás seguro de que deseas eliminar esta imagen de la galería? Esta acción no se puede deshacer.')) {
                if (form) {
                    form.action = url;
                    form.setAttribute('action', url);
                    form.submit();
                }
            }
        }
    };

    // Mostrar spinner en el botón de eliminar al confirmar
    const formConfirmarEliminarImagen = document.getElementById('formConfirmarEliminarImagen');
    if (formConfirmarEliminarImagen) {
        formConfirmarEliminarImagen.addEventListener('submit', function() {
            const btnSubmit = formConfirmarEliminarImagen.querySelector('button[type="submit"]');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Eliminando...';
            }

            // Ocultar el modal para que Bootstrap limpie su estado
            const modalEl = document.getElementById('confirmDeleteModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }
            }

            // Forzar limpieza del body para evitar scroll bloqueado si HTMX hace el swap rápido
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(el => el.remove());
        });
    }

    // Mostrar spinner en el botón de guardar imágenes al enviar
    const formSubirImagenes = document.getElementById('formSubirImagenes');
    if (formSubirImagenes) {
        formSubirImagenes.addEventListener('submit', function() {
            const btnSubmit = document.getElementById('uploadBtn');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando...';
            }
        });
    }

    // Mostrar spinner al cambiar la imagen principal
    const formFotoPrincipal = document.getElementById('form-foto-principal');
    if (formFotoPrincipal) {
        formFotoPrincipal.addEventListener('submit', function() {
            const labelSubmit = formFotoPrincipal.querySelector('label[for="fotoPrincipalInput"]');
            if (labelSubmit) {
                labelSubmit.style.pointerEvents = 'none';
                labelSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Cambiando...';
            }
        });
    }

    // Configuración de la zona de arrastre (dragZone) local
    const dragZone = document.querySelector('.drag-zone');
    const fileInput = document.querySelector('#fileInput');
    const fileList = document.querySelector('#fileList');
    const uploadBtn = document.querySelector('#uploadBtn');

    if (dragZone && fileInput) {
        dragZone.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', (e) => {
            const files = e.target.files;
            if (files.length > 0) {
                fileList.innerHTML = `<i class="fas fa-file-image me-1"></i> ${files.length} archivo(s) seleccionado(s):<br>` + 
                                      Array.from(files).map(f => `<span class="text-secondary">• ${f.name}</span>`).join('<br>');
                uploadBtn.removeAttribute('disabled');
            } else {
                fileList.innerHTML = '';
                uploadBtn.setAttribute('disabled', 'true');
            }
        });
    }

    // Inicializar y mostrar toasts de notificaciones del servidor
    const toasts = document.querySelectorAll('.toast:not(.showing):not(.show)');
    toasts.forEach(toastEl => {
        if (typeof bootstrap !== 'undefined') {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
            toastEl.addEventListener('hidden.bs.toast', () => {
                toastEl.remove();
            });
        }
    });
})();
</script>
<?= $this->endSection() ?>
