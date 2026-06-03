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
                        <input type="file" name="foto_principal" id="fotoPrincipalInput" class="d-none" accept="image/jpeg, image/png, image/gif" onchange="this.form.submit();">
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
                <form action="<?= base_url('admin/productos/subir-imagen/' . $p['id']) ?>" method="POST" enctype="multipart/form-data" hx-boost="true">
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
                                        <form action="<?= base_url('admin/productos/eliminar-imagen/' . $img['id']) ?>" 
                                              method="POST" 
                                              class="m-0"
                                              onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta imagen de la galería? Esta acción no se puede deshacer.');"
                                              hx-boost="true">
                                            <button type="submit" 
                                                    class="btn btn-danger btn-sm rounded-circle d-flex align-items-center justify-content-center shadow admin-gallery-delete-btn" 
                                                    title="Eliminar esta foto">
                                                <i class="fas fa-trash-alt fs-6"></i>
                                            </button>
                                        </form>
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
                                                    onchange="this.form.submit();">
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

<script>
    document.body.classList.add('admin-body');

    // Inicializar y mostrar toasts de notificaciones del servidor
    document.addEventListener('DOMContentLoaded', function() {
        const toasts = document.querySelectorAll('.toast:not(.showing):not(.show)');
        toasts.forEach(toastEl => {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
            toastEl.addEventListener('hidden.bs.toast', () => {
                toastEl.remove();
            });
        });
    });

    const dragZone = document.querySelector('.drag-zone');
    const fileInput = document.querySelector('#fileInput');
    const fileList = document.querySelector('#fileList');
    const uploadBtn = document.querySelector('#uploadBtn');

    // Al hacer clic en la zona, activar el input de archivo
    dragZone.addEventListener('click', () => {
        fileInput.click();
    });

    // Detectar archivos seleccionados
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
</script>
<?= $this->endSection() ?>
