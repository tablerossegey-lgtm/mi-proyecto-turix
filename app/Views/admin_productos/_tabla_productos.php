<?php if (!empty($productos)): ?>
    <?php foreach ($productos as $p): ?>
        <?php 
            $srcUrl = obtener_ruta_imagen($p['foto'] ?? '', $p['nombre_categoria'] ?? '');
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
            <td class="py-3 text-center">
                <?php if ($p['total_encargos_pendientes'] > 0): ?>
                    <a href="<?= base_url('admin/encargos?estado=Pendiente&producto_id=' . $p['id']) ?>" 
                       class="badge bg-info text-dark fw-bold rounded-pill px-3 py-1.5 shadow-sm text-decoration-none hover-shadow">
                        <i class="fas fa-clipboard-list me-1"></i> <?= $p['total_encargos_pendientes'] ?> pzas
                    </a>
                <?php else: ?>
                    <span class="text-white-50 opacity-30 small">-</span>
                <?php endif; ?>
            </td>
            <td class="py-3 text-end pe-4">
                <div class="d-inline-flex gap-2">
                    <a href="<?= base_url('admin/encargos?nuevo_encargo_producto_id=' . $p['id']) ?>" 
                       class="btn btn-outline-info btn-sm fw-bold px-3 py-2 d-inline-flex align-items-center gap-1.5 rounded-3 shadow-sm hover-info-btn">
                        <i class="fas fa-cart-plus"></i> Encargar
                    </a>
                    <a href="<?= base_url('admin/productos/galeria/' . $p['id']) ?>" 
                       class="btn btn-warning btn-sm fw-bold px-3 py-2 d-inline-flex align-items-center gap-1.5 rounded-3 shadow-sm hover-warning text-dark">
                        <i class="fas fa-photo-video text-dark"></i> Galerías
                    </a>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="7" class="text-center py-5 text-muted">
            <div class="mb-3">
                <i class="fas fa-search fs-1 text-muted opacity-50"></i>
            </div>
            <h5 class="fw-bold admin-product-title">No se encontraron productos</h5>
            <p class="mb-0">Prueba con otro término de búsqueda o SKU.</p>
        </td>
    </tr>
<?php endif; ?>
