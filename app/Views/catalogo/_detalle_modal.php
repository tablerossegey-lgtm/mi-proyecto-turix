
<!-- Botón de Cerrar Premium (X) -->
<button type="button" 
        class="btn-close btn-close-white btn-close-premium shadow-none" 
        data-bs-dismiss="modal" 
        aria-label="Close"
        onclick="const modal = bootstrap.Modal.getInstance(document.getElementById('modalDetalle')); if(modal) modal.hide();">
</button>

<style>
    .extra-thumb {
        width: 70px;
        height: 70px;
        object-fit: cover;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        opacity: 0.6;
    }
    .extra-thumb:hover {
        transform: translateY(-2px);
        border-color: rgba(255, 193, 7, 0.5);
        opacity: 0.9;
    }
    .extra-thumb.active {
        transform: translateY(-2px);
        border-color: #ffc107;
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
        opacity: 1;
    }
    .product-zoom {
        transition: opacity 0.15s ease-in-out;
    }
</style>

<div class="modal-body p-4 p-md-5">
    <div class="row g-4 mt-1">
        <!-- Columna de la Imagen -->
        <div class="col-12 col-md-6">
            <div class="product-image-container d-flex align-items-center justify-content-center p-4 rounded-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); min-height: 320px;">
                <?php 
                $foto = $p['foto'] ?? '';
                $isUrl = (strpos($foto, 'http://') === 0 || strpos($foto, 'https://') === 0);
                if ($isUrl) {
                    $foto_final_src = $foto;
                } else {
                    $categoriaFolder = isset($p['nombre_categoria']) ? str_replace(' ', '', ucwords(strtolower($p['nombre_categoria']))) : '';
                    $ruta_foto = 'uploads/' . $categoriaFolder . '/' . $foto;
                    $foto_final_src = (!empty($foto) && file_exists(FCPATH . $ruta_foto)) ? base_url($ruta_foto) : base_url('uploads/SinImagen.png');
                }
                ?>
                <img id="main-img" src="<?= $foto_final_src ?>" 
                     class="img-fluid product-zoom" 
                     alt="<?= esc($p['descripcion']) ?>"
                     onerror="this.src='<?= base_url('uploads/SinImagen.png') ?>'; this.onerror=null;">
            </div>
            <?php if (!empty($imagenes_adicionales)): ?>
            <div class="extra-images mt-3 d-flex flex-wrap gap-2 justify-content-center">
                <!-- Miniatura de la foto principal -->
                <img src="<?= $foto_final_src ?>" 
                     class="img-thumbnail extra-thumb active" 
                     alt="<?= esc($p['descripcion']) ?>" 
                     onclick="changeMainImage(this)"
                     onerror="this.src='<?= base_url('uploads/SinImagen.png') ?>'; this.onerror=null;">
                
                <?php foreach ($imagenes_adicionales as $img):
                    $adicionalFoto = $img['ruta_foto'] ?? '';
                    $isAdicionalUrl = (strpos($adicionalFoto, 'http://') === 0 || strpos($adicionalFoto, 'https://') === 0);
                    if ($isAdicionalUrl) {
                        $foto_src = $adicionalFoto;
                    } else {
                        $ruta = 'uploads/' . $categoriaFolder . '/' . $adicionalFoto;
                        $foto_src = (!empty($adicionalFoto) && file_exists(FCPATH . $ruta)) ? base_url($ruta) : base_url('uploads/SinImagen.png');
                    }
                ?>
                    <img src="<?= $foto_src ?>" 
                         class="img-thumbnail extra-thumb" 
                         alt="<?= esc($p['descripcion']) ?>" 
                         onclick="changeMainImage(this)"
                         onerror="this.src='<?= base_url('uploads/SinImagen.png') ?>'; this.onerror=null;">
                <?php endforeach; ?>
            </div>
            
            <script>
                function changeMainImage(element) {
                    const mainImg = document.getElementById('main-img');
                    mainImg.style.opacity = '0';
                    setTimeout(() => {
                        mainImg.src = element.src;
                        mainImg.style.opacity = '1';
                    }, 150);

                    document.querySelectorAll('.extra-thumb').forEach(thumb => {
                        thumb.classList.remove('active');
                    });
                    element.classList.add('active');
                }
            </script>
            <?php endif; ?>
        </div>

        <!-- Columna de Detalles del Producto -->
        <div class="col-12 col-md-6 d-flex flex-column justify-content-between">
            <div>
                <!-- SKU Badge -->
                <div class="mb-3">
                    <span class="sku-premium-badge text-uppercase">
                        SKU: <?= esc($p['codigo_sku']) ?>
                    </span>
                </div>
                
                <h3 class="text-white fw-bold mb-3 product-detail-title">
                    <?= esc($p['descripcion']) ?>
                </h3>
                
                <div class="mb-4 d-flex align-items-center gap-3">
                    <span class="badge bg-warning text-dark px-3 py-2 fs-5 fw-bold shadow-sm price-badge-premium">
                        $<?= number_format($p['precio'], 2) ?>
                    </span>
                    <span class="text-white-50 stock-text-premium">
                        <?php if ((int)$p['stock'] === 0): ?>
                            | &nbsp;<span class="badge bg-danger text-white px-2.5 py-1.5 fw-bold badge-agotado-premium">Agotado</span>
                        <?php elseif ((int)$p['stock'] === 1): ?>
                            | &nbsp;Stock: <strong class="text-white">1</strong> pza
                        <?php else: ?>
                            | &nbsp;Stock: <strong class="text-white"><?= $p['stock'] ?></strong> pzas
                        <?php endif; ?>
                    </span>
                </div>

                <div class="text-white-50 mb-4">
                    <h6 class="text-white border-bottom border-secondary pb-2 fw-semibold product-detail-section-title">Descripción del producto</h6>
                    <p class="product-detail-desc">
                        <?= nl2br(esc($p['descripcion'])) ?> 
                    </p>
                </div>

                <?php if (!empty($p['masDetalle'])): ?>
                <div class="text-white-50 mb-4">
                    <h6 class="text-white border-bottom border-secondary pb-2 fw-semibold product-detail-section-title">Detalles adicionales</h6>
                    <p class="product-detail-desc">
                        <?= nl2br(esc($p['masDetalle'])) ?> 
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Botones de Acción -->
            <div class="d-grid gap-2 mt-3">
                <a href="https://wa.me/529995441466?text=Hola, me interesa el producto: <?= urlencode($p['descripcion']) ?>" 
                   target="_blank" 
                   class="btn btn-whatsapp-premium fw-bold py-3 shadow d-flex align-items-center justify-content-center gap-2">
                    <i class="fab fa-whatsapp fs-5"></i> Preguntar por WhatsApp
                </a>                    
                <button type="button" class="btn btn-outline-secondary text-white border-secondary d-flex align-items-center justify-content-center py-2" data-bs-dismiss="modal" onclick="const modal = bootstrap.Modal.getInstance(document.getElementById('modalDetalle')); if(modal) modal.hide();">
                    Seguir explorando
                </button>
            </div>
        </div>
    </div>
</div>