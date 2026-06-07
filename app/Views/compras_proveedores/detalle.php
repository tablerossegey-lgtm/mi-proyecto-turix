<div class="modal-header modal-encargo-header py-3 px-4" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
        <i class="fas fa-file-invoice-dollar text-warning"></i> Factura Folio #<?= $compra['idCompraProveedor'] ?>
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body p-4">
    <!-- Información de Cabecera -->
    <div class="row g-3 mb-4 p-3 rounded-4" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05);">
        <div class="col-12 col-sm-6 col-md-3">
            <span class="text-white-50 small d-block">Proveedor</span>
            <strong class="text-white fs-6"><?= esc($compra['nombre_proveedor'] ?: 'Proveedor General') ?></strong>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <span class="text-white-50 small d-block">Fecha de Compra</span>
            <strong class="text-white fs-6"><?= date('d/m/Y', strtotime($compra['fechaCompra'])) ?></strong>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <span class="text-white-50 small d-block">Descripción / Concepto</span>
            <strong class="text-white fs-6"><?= esc($compra['descripcion'] ?: '-') ?></strong>
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <span class="text-white-50 small d-block">Factor de Prorrateo</span>
            <strong class="text-info fs-6">x<?= number_format($compra['factor_pedido'], 4) ?></strong>
        </div>
    </div>

    <!-- Desglose de Gastos de Factura -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.01); border: 1px solid rgba(255, 255, 255, 0.03);">
                <span class="text-white-50 small d-block">Subtotal Productos</span>
                <span class="fs-5 fw-bold text-white">$<?= number_format($compra['subtotal'], 2) ?></span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.01); border: 1px solid rgba(255, 255, 255, 0.03);">
                <span class="text-white-50 small d-block">Costo de Envío</span>
                <span class="fs-5 fw-bold text-white-50">$<?= number_format($compra['envio_local_estimado'], 2) ?></span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.01); border: 1px solid rgba(255, 255, 255, 0.03);">
                <span class="text-white-50 small d-block">Impuestos de Importación</span>
                <span class="fs-5 fw-bold text-white-50">$<?= number_format($compra['impuesto_importacion'], 2) ?></span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 rounded-3" style="background: rgba(46, 204, 113, 0.05); border: 1px solid rgba(46, 204, 113, 0.1);">
                <span class="text-success small d-block"><i class="fas fa-check-circle me-1"></i> Total Pagado (Caja Chica)</span>
                <span class="fs-5 fw-bold text-success">$<?= number_format($compra['total_pagado'], 2) ?></span>
            </div>
        </div>
    </div>

    <!-- Artículos Desglosados -->
    <h6 class="text-warning fw-bold mb-3"><i class="fas fa-boxes me-2"></i> Desglose de Artículos Adquiridos</h6>
    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
        <table class="table table-hover align-middle mb-0 admin-table" style="border: 1px solid rgba(255,255,255,0.05);">
            <thead class="text-muted admin-table-thead" style="background-color: rgba(255, 255, 255, 0.02); position: sticky; top: 0; z-index: 10;">
                <tr>
                    <th class="ps-3 py-2.5 admin-table-th">Artículo</th>
                    <th class="py-2.5 admin-table-th">SKU</th>
                    <th class="py-2.5 text-center admin-table-th" style="width: 80px;">Cant.</th>
                    <th class="py-2.5 text-end admin-table-th">Costo Proveedor</th>
                    <th class="py-2.5 text-end admin-table-th text-info">Costo Real Unit</th>
                    <th class="py-2.5 text-end admin-table-th text-info">Costo Real Total</th>
                    <th class="py-2.5 text-center admin-table-th" style="width: 90px;">Margen</th>
                    <th class="py-2.5 text-end admin-table-th">Venta Sugerido</th>
                    <th class="py-2.5 text-end pe-3 admin-table-th text-success">Venta Final</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $d): ?>
                    <tr class="admin-table-tr" style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                        <td class="ps-3 py-3 text-white fw-semibold">
                            <?= esc($d['nombre']) ?>
                            <?php if ($d['id_producto']): ?>
                                <span class="badge bg-secondary rounded-pill px-2 py-0.5 ms-1 text-white-50" style="font-size: 0.65rem;">En Inventario</span>
                            <?php else: ?>
                                <span class="badge bg-dark rounded-pill px-2 py-0.5 ms-1 text-muted" style="font-size: 0.65rem; border: 1px solid rgba(255,255,255,0.05);">Gasto Operativo</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 text-white-50 font-monospace small">
                            <?= esc($d['sku'] ?: 'SIN SKU') ?>
                        </td>
                        <td class="py-3 text-center text-white fw-bold">
                            <?= $d['cantidad'] ?>
                        </td>
                        <td class="py-3 text-end text-white-50 font-monospace">
                            $<?= number_format($d['precio_proveedor'], 2) ?>
                        </td>
                        <td class="py-3 text-end text-info font-monospace fw-bold">
                            $<?= number_format($d['costo_real_unit'], 2) ?>
                        </td>
                        <td class="py-3 text-end text-info font-monospace fw-bold">
                            $<?= number_format($d['costo_real_total'], 2) ?>
                        </td>
                        <td class="py-3 text-center text-white-50 small">
                            <?= number_format($d['margen'], 1) ?>%
                        </td>
                        <td class="py-3 text-end text-white-50 font-monospace">
                            $<?= number_format($d['precio_venta_sugerido'], 2) ?>
                        </td>
                        <td class="py-3 text-end pe-3 text-success font-monospace fw-bold">
                            $<?= number_format($d['precio_venta_final'], 2) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-footer modal-encargo-footer" style="border-top: 1px solid rgba(255,255,255,0.1);">
    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
</div>
