<?php 
// Limpieza de teléfono para WhatsApp
$cel = $cliente['cel'] ?? '';
$telefonoLimpio = preg_replace('/[^0-9]/', '', $cel);
if (strlen($telefonoLimpio) === 10) {
    $telefonoLimpio = '52' . $telefonoLimpio;
}

// Generación de Mensaje de WhatsApp y Clipboard
$mensaje = "Hola *" . $cliente['nombre'] . "*, te comparto el estado de tu cuenta en *TurixShop*:\n\n";

if ($totalPendiente > 0) {
    // Agrupar compras pendientes por fecha
    $comprasPorFecha = [];
    foreach ($compras as $c) {
        if ($c['estatusCompra'] == '0') {
            $fecha = date('d/m/y', strtotime($c['fechaCompra']));
            $comprasPorFecha[$fecha][] = $c;
        }
    }

    foreach ($comprasPorFecha as $fecha => $itemsDeFecha) {
        $mensaje .= "*Compra del día ({$fecha})*\n";
        foreach ($itemsDeFecha as $c) {
            $descAplicado = (float)($c['descuento_aplicado'] ?? 0);
            if ($descAplicado > 0) {
                $impOriginal = $c['importe_original'] !== null ? (float)$c['importe_original'] : ((float)$c['totalProduc'] + $descAplicado);
                $etiquetaDesc = !empty($c['codigo_cupon']) ? "Cupón {$c['codigo_cupon']}" : "Descuento";
                $mensaje .= "  · {$c['cantidad']} {$c['descProducto']}: $" . number_format($impOriginal, 2)
                    . " ({$etiquetaDesc}: -$" . number_format($descAplicado, 2) . " → Neto: $" . number_format($c['totalProduc'], 2) . ")\n";
            } else {
                $mensaje .= "  · {$c['cantidad']} {$c['descProducto']}: $" . number_format($c['totalProduc'], 2) . "\n";
            }
        }
    }
    
    $mensaje .= "\n";
    $mensaje .= "*Subtotal:* $" . number_format($totalComprasActivas, 2) . "\n";
    if ($totalPagadoActivo > 0) {
        $fechaAbonoStr = '';
        if (!empty($abonos) && isset($abonos[0]['fechaAbono'])) {
            $fechaAbonoStr = ' (' . date('d/m/y', strtotime($abonos[0]['fechaAbono'])) . ')';
        }
        $mensaje .= "*Abono{$fechaAbonoStr}:* $" . number_format($totalPagadoActivo, 2) . "\n";
    }
    $mensaje .= "*Nuevo Total:* $" . number_format($totalPendiente, 2) . "\n";
} else {
    $mensaje .= "*¡Felicidades!* Tu cuenta se encuentra totalmente liquidada al día de hoy.\n";
}
$mensaje .= "\nCualquier duda o aclaración quedamos a tus órdenes. ¡Gracias por tu preferencia!";
$waUrl = "https://wa.me/{$telefonoLimpio}?text=" . urlencode($mensaje);
?>

<!-- Cabecera del Cliente Seleccionado -->
<div class="card border-0 shadow-sm admin-card p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-1 text-white"><?= esc($cliente['nombre']) ?></h4>
                <button type="button" class="btn btn-sm btn-outline-warning rounded-circle p-0 d-inline-flex align-items-center justify-content-center" 
                        style="width: 26px; height: 26px; border-color: rgba(255, 193, 7, 0.4);" title="Editar datos del cliente"
                        onclick="abrirModalEditarCliente(<?= htmlspecialchars(json_encode($cliente), ENT_QUOTES, 'UTF-8') ?>)">
                    <i class="fas fa-user-edit" style="font-size: 0.75rem;"></i>
                </button>
            </div>
            <span class="text-white-50 small d-inline-flex align-items-center gap-2">
                <i class="fas fa-phone-alt text-success"></i> <?= esc($cliente['cel'] ?: 'Sin número registrado') ?>
                <?php if (!empty($cliente['tipoCliente'])): ?>
                    <span class="badge bg-secondary font-monospace ms-2" style="font-size: 0.7rem;"><?= esc($cliente['tipoCliente']) ?></span>
                <?php endif; ?>
            </span>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <button type="button" class="btn btn-outline-success rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2"
                    data-mensaje="<?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>"
                    onclick="copiarEstadoCuenta(this)">
                <i class="fas fa-copy"></i> Copiar Cuenta
            </button>
            <button type="button" class="btn btn-outline-info rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2"
                    onclick="irASeccionCupones()">
                <i class="fas fa-ticket-alt"></i> Cupones y descuentos
                <?php if (($resumenCupones['cantidad_activos'] ?? 0) > 0): ?>
                    <span class="badge bg-info text-dark rounded-pill"><?= $resumenCupones['cantidad_activos'] ?></span>
                <?php endif; ?>
            </button>
            <?php if ($totalPendiente > 0): ?>
                <button type="button" class="btn btn-warning text-dark rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2" onclick="abrirModalRegistrarAbono(<?= $cliente['idCliente'] ?>, <?= $totalPendiente ?>)">
                    <i class="fas fa-hand-holding-usd text-dark"></i> Registrar Pago
                </button>
            <?php endif; ?>
            <button type="button" class="btn btn-success rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2" onclick="abrirModalNuevaCompra(<?= $cliente['idCliente'] ?>)">
                <i class="fas fa-cart-plus"></i> Registrar Compra
            </button>
        </div>
    </div>

    <!-- Cards de Balance del Cliente -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-5 g-3 mt-3">
        <div class="col">
            <div class="admin-stat-card border border-secondary border-opacity-25 p-3 rounded-3" style="background: rgba(0,0,0,0.15); height: 100%;">
                <div class="admin-stat-label opacity-75"><i class="fas fa-shopping-bag me-1 text-info"></i> Suma de Adeudos</div>
                <div class="fs-5 fw-bold text-white mt-1 text-nowrap" id="lbl-total-compras">$<?= number_format($totalComprasActivas, 2) ?></div>
            </div>
        </div>
        <div class="col">
            <div class="admin-stat-card border border-secondary border-opacity-25 p-3 rounded-3" style="background: rgba(0,0,0,0.15); height: 100%;">
                <div class="admin-stat-label opacity-75"><i class="fas fa-check-circle me-1 text-success"></i> Abonado a Cuenta</div>
                <div class="fs-5 fw-bold text-success mt-1 text-nowrap" id="lbl-total-pagado">$<?= number_format($totalPagadoActivo, 2) ?></div>
            </div>
        </div>
        <div class="col">
            <div class="admin-stat-card border border-secondary border-opacity-25 p-3 rounded-3" style="background: rgba(0,0,0,0.15); height: 100%;">
                <div class="admin-stat-label opacity-75"><i class="fas fa-hourglass-half me-1 text-danger"></i> Total Pendiente</div>
                <div class="fs-5 fw-bold text-danger mt-1 text-nowrap" id="lbl-total-pendiente">$<?= number_format($totalPendiente, 2) ?></div>
            </div>
        </div>
        <div class="col">
            <div class="admin-stat-card border border-secondary border-opacity-25 p-3 rounded-3" style="background: rgba(0,0,0,0.15); height: 100%;">
                <div class="admin-stat-label opacity-75"><i class="fas fa-boxes me-1 text-warning"></i> Control de Entregas</div>
                <div class="fw-bold text-warning mt-1" id="lbl-por-entregar" style="font-size: 1.05rem; padding-top: 2px;">
                    <span class="text-danger" id="lbl-val-por-empacar"><?= $porEmpacarCount ?? 0 ?></span> <span class="small text-white-50" style="font-size: 0.72rem;">por empacar</span>
                    <span class="mx-1 text-white-50">|</span>
                    <span class="text-info" id="lbl-val-por-entregar"><?= $porEntregarCount ?? 0 ?></span> <span class="small text-white-50" style="font-size: 0.72rem;">por entregar</span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="admin-stat-card border border-info border-opacity-25 p-3 rounded-3" style="background: rgba(13, 202, 240, 0.06); height: 100%; cursor: pointer;" onclick="irASeccionCupones()" title="Ver sección de Cupones y descuentos">
                <div class="admin-stat-label opacity-75"><i class="fas fa-ticket-alt me-1 text-info"></i> Cupones Disponibles</div>
                <div class="fs-5 fw-bold text-info mt-1 text-nowrap" id="lbl-cupones-disponibles">$<?= number_format($resumenCupones['total_monetario_disponible'] ?? 0, 2) ?></div>
                <div class="small text-white-50 mt-1" style="font-size: 0.74rem;">
                    <span class="fw-semibold text-white"><?= $resumenCupones['cantidad_activos'] ?? 0 ?></span> activo(s)
                    <?php if (($resumenCupones['cantidad_porcentuales'] ?? 0) > 0): ?>
                        · <span class="text-info"><?= $resumenCupones['cantidad_porcentuales'] ?> en %</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Apartado colapsable: Cupones y Descuentos -->
<?php 
$tieneCuponesActivos = ($resumenCupones['cantidad_activos'] ?? 0) > 0;
$cuponesLista = $cupones ?? [];
?>
<div class="card border-0 shadow-sm admin-card mb-4" id="seccion-cupones-cliente">
    <div class="card-header border-bottom border-secondary border-opacity-25 py-3 bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2">
        <button class="btn btn-link text-decoration-none text-white fw-bold p-0 d-inline-flex align-items-center gap-2"
                type="button" data-bs-toggle="collapse" data-bs-target="#collapseCuponesCliente"
                aria-expanded="<?= $tieneCuponesActivos ? 'true' : 'false' ?>" aria-controls="collapseCuponesCliente">
            <i class="fas fa-ticket-alt text-info"></i>
            <span>Cupones y Descuentos</span>
            <span class="badge bg-info text-dark rounded-pill"><?= $resumenCupones['cantidad_activos'] ?? 0 ?> disponible(s)</span>
            <i class="fas fa-chevron-down small text-white-50 ms-1"></i>
        </button>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-dark border border-secondary border-opacity-50 text-white-50 px-3 py-2" style="font-size: 0.75rem;">
                Total descuentos aplicados: <strong class="text-info">$<?= number_format($resumenCupones['total_descuentos_utilizados'] ?? 0, 2) ?></strong>
            </span>
            <button type="button" class="btn btn-sm btn-info text-dark rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1"
                    onclick="abrirModalNuevoCupon(<?= $cliente['idCliente'] ?>, '<?= htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8') ?>')">
                <i class="fas fa-plus"></i> Nuevo cupón
            </button>
        </div>
    </div>

    <div class="collapse <?= $tieneCuponesActivos ? 'show' : '' ?>" id="collapseCuponesCliente">
        <!-- Filtros de cupones -->
        <div class="p-3 border-bottom border-secondary border-opacity-25 bg-dark bg-opacity-25">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-4 col-md-3">
                    <label class="form-label text-white-50 small mb-1">Filtrar por estado</label>
                    <select class="form-select form-select-sm bg-dark text-white border-secondary" id="filtro-cupon-estado" onchange="filtrarTablaCupones()">
                        <option value="todos">Todos los estados</option>
                        <option value="disponible">Disponible</option>
                        <option value="programado">Programado</option>
                        <option value="utilizado">Utilizado</option>
                        <option value="vencido">Vencido</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
                <div class="col-6 col-sm-3 col-md-3">
                    <label class="form-label text-white-50 small mb-1">Emisión desde</label>
                    <input type="date" class="form-control form-control-sm bg-dark text-white border-secondary" id="filtro-cupon-desde" onchange="filtrarTablaCupones()">
                </div>
                <div class="col-6 col-sm-3 col-md-3">
                    <label class="form-label text-white-50 small mb-1">Emisión hasta</label>
                    <input type="date" class="form-control form-control-sm bg-dark text-white border-secondary" id="filtro-cupon-hasta" onchange="filtrarTablaCupones()">
                </div>
                <div class="col-12 col-sm-2 col-md-3 text-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary w-100" onclick="limpiarFiltrosCupones()">
                        <i class="fas fa-eraser me-1"></i> Limpiar filtros
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table" id="tabla-cupones-cliente">
                <thead class="text-muted admin-table-thead">
                    <tr>
                        <th class="ps-4 py-3 admin-table-th">Código</th>
                        <th class="py-3 admin-table-th">Concepto</th>
                        <th class="py-3 admin-table-th">Tipo</th>
                        <th class="py-3 text-end admin-table-th">Valor</th>
                        <th class="py-3 admin-table-th">Emisión</th>
                        <th class="py-3 admin-table-th">Vencimiento</th>
                        <th class="py-3 text-center admin-table-th">Usos disp.</th>
                        <th class="py-3 text-center admin-table-th">Estado</th>
                        <th class="py-3 admin-table-th">Fecha uso</th>
                        <th class="py-3 admin-table-th">Folio / Ref.</th>
                        <th class="py-3 text-end pe-4 admin-table-th">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cuponesLista)): ?>
                        <?php foreach ($cuponesLista as $cup): ?>
                            <?php
                            $est = $cup['estado_resuelto'] ?? $cup['estado'];
                            $badgeMap = [
                                'disponible' => ['bg-success', 'Disponible'],
                                'programado' => ['bg-info text-dark', 'Programado'],
                                'utilizado'  => ['bg-secondary', 'Utilizado'],
                                'vencido'    => ['bg-warning text-dark', 'Vencido'],
                                'cancelado'  => ['bg-danger', 'Cancelado'],
                            ];
                            $badgeCfg = $badgeMap[$est] ?? ['bg-secondary', ucfirst($est)];
                            $usosRestantes = max(0, (int)$cup['limite_usos'] - (int)$cup['usos_realizados']);
                            ?>
                            <tr class="admin-table-tr cupon-row" data-estado="<?= esc($est) ?>" data-emision="<?= esc($cup['fecha_emision']) ?>">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-dark border border-info text-info font-monospace"><?= esc($cup['codigo']) ?></span>
                                        <button type="button" class="btn btn-sm btn-link text-white-50 p-1" title="Copiar código"
                                                onclick="copiarCodigoCupon('<?= esc($cup['codigo']) ?>', this)">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3 text-white">
                                    <div class="fw-semibold" style="font-size: 0.85rem;"><?= esc($cup['concepto']) ?></div>
                                    <?php if (!empty($cup['motivo_origen'])): ?>
                                        <small class="text-white-50" style="font-size: 0.72rem;"><?= esc($cup['motivo_origen']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-white-50 small">
                                    <?= $cup['tipo_descuento'] === 'porcentaje' ? 'Porcentaje' : 'Importe fijo' ?>
                                </td>
                                <td class="py-3 text-end text-white fw-bold">
                                    <?php if ($cup['tipo_descuento'] === 'porcentaje'): ?>
                                        <?= number_format((float)$cup['valor'], 2) ?>%
                                        <?php if (!empty($cup['descuento_maximo'])): ?>
                                            <small class="d-block text-white-50 fw-normal" style="font-size: 0.7rem;">Máx $<?= number_format((float)$cup['descuento_maximo'], 2) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        $<?= number_format((float)$cup['valor'], 2) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($cup['compra_minima']) && (float)$cup['compra_minima'] > 0): ?>
                                        <small class="d-block text-white-50 fw-normal" style="font-size: 0.7rem;">Mín $<?= number_format((float)$cup['compra_minima'], 2) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-white-50 small">
                                    <?= date('d/m/Y', strtotime($cup['fecha_emision'])) ?>
                                </td>
                                <td class="py-3 text-white-50 small">
                                    <?= !empty($cup['fecha_vencimiento']) ? date('d/m/Y', strtotime($cup['fecha_vencimiento'])) : '<span class="text-muted">Sin vencimiento</span>' ?>
                                </td>
                                <td class="py-3 text-center text-white font-monospace small">
                                    <?= $usosRestantes ?> / <?= (int)$cup['limite_usos'] ?>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge <?= $badgeCfg[0] ?> font-monospace" style="font-size: 0.7rem;"><?= $badgeCfg[1] ?></span>
                                </td>
                                <td class="py-3 text-white-50 small">
                                    <?= !empty($cup['ultima_fecha_uso']) ? date('d/m/Y H:i', strtotime($cup['ultima_fecha_uso'])) : '-' ?>
                                </td>
                                <td class="py-3 text-white-50 font-monospace small">
                                    <?= !empty($cup['ultimo_folio_compra']) ? esc($cup['ultimo_folio_compra']) : '-' ?>
                                </td>
                                <td class="py-3 text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-3 p-1.5" title="Ver detalle e historial"
                                                onclick="verDetalleCupon(<?= (int)$cup['id'] ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ((int)$cup['usos_realizados'] === 0 && $est !== 'cancelado'): ?>
                                            <button type="button" class="btn btn-sm btn-warning text-dark rounded-3 p-1.5" title="Editar cupón"
                                                    onclick="abrirModalEditarCupon(<?= htmlspecialchars(json_encode($cup), ENT_QUOTES, 'UTF-8') ?>, '<?= htmlspecialchars($cliente['nombre'], ENT_QUOTES, 'UTF-8') ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if (in_array($est, ['disponible', 'programado'], true)): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-3 p-1.5" title="Cancelar cupón"
                                                    onclick="cancelarCupon(<?= (int)$cup['id'] ?>, '<?= esc($cup['codigo']) ?>')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="text-center py-4 text-white-50 small">
                                <i class="fas fa-ticket-alt fs-3 d-block mb-2 opacity-50"></i>
                                Este cliente aún no tiene cupones registrados. Usa <strong>Nuevo cupón</strong> para otorgar un beneficio.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Listado de Compras -->
<div class="card border-0 shadow-sm admin-card">
    <div class="card-header border-bottom border-secondary border-opacity-25 py-3 bg-transparent">
        <h6 class="fw-bold mb-0 text-white"><i class="fas fa-file-invoice-dollar me-2 text-warning"></i> Adeudos Pendientes</h6>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 admin-table">
            <thead class="text-muted admin-table-thead">
                <tr>
                    <th class="ps-4 py-3 admin-table-th">Fecha</th>
                    <th class="py-3 admin-table-th">Producto / Descripción</th>
                    <th class="py-3 text-center admin-table-th">Cant.</th>
                    <th class="py-3 text-end admin-table-th">P. Unitario</th>
                    <th class="py-3 text-end admin-table-th">Total</th>
                    <th class="py-3 text-center admin-table-th">Pago</th>
                    <th class="py-3 text-center admin-table-th">Entrega</th>
                    <th class="py-3 text-end pe-4 admin-table-th">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $comprasActivas = array_filter($compras, function($c) { 
                    $unpaid = ($c['estatusCompra'] == '0');
                    $undelivered = (($c['estatus_entrega'] ?? 0) != 2);
                    return $unpaid || $undelivered; 
                });
                if (!empty($comprasActivas)): 
                ?>
                    <?php foreach ($comprasActivas as $c): ?>
                        <tr class="admin-table-tr" id="compra-row-<?= $c['idCompra'] ?>" style="<?= $c['estatusCompra'] == '1' ? 'opacity: 0.75;' : '' ?>">
                            <td class="ps-4 py-3 text-white-50 small">
                                <?= date('d/m/Y', strtotime($c['fechaCompra'])) ?>
                            </td>
                            <td class="py-3 text-white">
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($c['idInventario'] > 0): ?>
                                        <span class="badge bg-success border border-success-subtle text-white font-monospace" style="font-size: 0.65rem;">Inventario</span>
                                        <div>
                                            <div class="fw-semibold" style="font-size: 0.88rem;"><?= esc($c['descProducto']) ?></div>
                                            <?php if (!empty($c['codigo_sku'])): ?>
                                                <small class="text-white-50 font-monospace" style="font-size: 0.75rem;">SKU: <?= esc($c['codigo_sku']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-secondary border border-secondary-subtle text-white-50 font-monospace" style="font-size: 0.65rem;">Libre</span>
                                        <span class="fw-semibold" style="font-size: 0.88rem;"><?= esc($c['descProducto']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="py-3 text-center text-white fw-semibold">
                                <?= $c['cantidad'] ?>
                            </td>
                            <td class="py-3 text-end text-white-50">
                                $<?= number_format($c['precioUnit'], 2) ?>
                            </td>
                            <td class="py-3 text-end text-white fw-bold">
                                <?php $descRow = (float)($c['descuento_aplicado'] ?? 0); ?>
                                <?php if ($descRow > 0): ?>
                                    <?php $origRow = $c['importe_original'] !== null ? (float)$c['importe_original'] : ((float)$c['totalProduc'] + $descRow); ?>
                                    <small class="text-white-50 text-decoration-line-through d-block fw-normal" style="font-size: 0.75rem;">
                                        $<?= number_format($origRow, 2) ?>
                                    </small>
                                    <span class="badge bg-info bg-opacity-25 text-info border border-info border-opacity-25 font-monospace d-inline-block my-0.5" style="font-size: 0.65rem;">
                                        <?= !empty($c['codigo_cupon']) ? 'Cupón ' . esc($c['codigo_cupon']) : 'Desc. manual' ?>: -$<?= number_format($descRow, 2) ?>
                                    </span>
                                    <div class="fw-bold text-white">$<?= number_format($c['totalProduc'], 2) ?></div>
                                <?php else: ?>
                                    $<?= number_format($c['totalProduc'], 2) ?>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 text-center">
                                <div class="form-check form-switch form-switch-premium d-inline-block">
                                     <input class="form-check-input" type="checkbox" role="switch" 
                                            id="switch-estatus-<?= $c['idCompra'] ?>" 
                                            <?= $c['estatusCompra'] == '1' ? 'checked' : '' ?>
                                            hx-post="<?= base_url("admin/cuentas/toggle/" . $c['idCompra']) ?>"
                                            hx-target="#compras-cliente-container"
                                            hx-indicator="#loading-indicator">
                                </div>
                                <div class="mt-1">
                                     <span class="badge <?= $c['estatusCompra'] == '1' ? 'bg-success' : 'bg-danger' ?> font-monospace" 
                                           id="badge-estatus-<?= $c['idCompra'] ?>" style="font-size: 0.65rem;">
                                         <?= $c['estatusCompra'] == '1' ? 'Pagado' : 'Pendiente' ?>
                                     </span>
                                </div>
                            </td>
                            <td class="py-3 text-center">
                                <div class="dropdown d-inline-block">
                                     <button class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-pill px-2.5 py-1 text-white border-secondary border-opacity-50" 
                                             type="button" 
                                             id="dropdownEntrega-<?= $c['idCompra'] ?>" 
                                             data-bs-toggle="dropdown" 
                                             aria-expanded="false"
                                             style="font-size: 0.75rem; min-width: 105px;">
                                         <?php if (($c['estatus_entrega'] ?? 0) == 0): ?>
                                             <span class="text-danger"><i class="fas fa-box me-1"></i> Pendiente</span>
                                         <?php elseif ($c['estatus_entrega'] == 1): ?>
                                             <span class="text-info"><i class="fas fa-boxes me-1"></i> Empacado</span>
                                         <?php else: ?>
                                             <span class="text-success"><i class="fas fa-check-circle me-1"></i> Entregado</span>
                                         <?php endif; ?>
                                     </button>
                                     <ul class="dropdown-menu dropdown-menu-dark shadow border-0 p-1" aria-labelledby="dropdownEntrega-<?= $c['idCompra'] ?>" style="font-size: 0.8rem; background-color: #1a2238;">
                                         <li>
                                             <button class="dropdown-item py-1.5 px-3 rounded d-flex align-items-center gap-2" type="button"
                                                     hx-post="<?= base_url("admin/cuentas/entrega/" . $c['idCompra']) ?>"
                                                     hx-vals='{"estatus_entrega": 0}'
                                                     hx-target="#compras-cliente-container"
                                                     hx-indicator="#loading-indicator">
                                                 <span class="text-danger"><i class="fas fa-box"></i> Pendiente</span>
                                             </button>
                                         </li>
                                         <li>
                                             <button class="dropdown-item py-1.5 px-3 rounded d-flex align-items-center gap-2" type="button"
                                                     hx-post="<?= base_url("admin/cuentas/entrega/" . $c['idCompra']) ?>"
                                                     hx-vals='{"estatus_entrega": 1}'
                                                     hx-target="#compras-cliente-container"
                                                     hx-indicator="#loading-indicator">
                                                 <span class="text-info"><i class="fas fa-boxes"></i> Empacado</span>
                                             </button>
                                         </li>
                                         <li>
                                             <button class="dropdown-item py-1.5 px-3 rounded d-flex align-items-center gap-2" type="button"
                                                     hx-post="<?= base_url("admin/cuentas/entrega/" . $c['idCompra']) ?>"
                                                     hx-vals='{"estatus_entrega": 2}'
                                                     hx-target="#compras-cliente-container"
                                                     hx-indicator="#loading-indicator">
                                                 <span class="text-success"><i class="fas fa-check-circle"></i> Entregado</span>
                                             </button>
                                         </li>
                                     </ul>
                                </div>
                            </td>
                            <td class="py-3 text-end pe-4">
                                <div class="d-inline-flex gap-1.5">
                                    <!-- Editar -->
                                    <button type="button" 
                                            class="btn btn-warning btn-sm d-flex align-items-center justify-content-center rounded-3 p-2 text-dark" 
                                            title="Editar compra"
                                            onclick="abrirEditarCompra(<?= htmlspecialchars(json_encode($c)) ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                     <!-- Eliminar -->
                                     <button type="button" 
                                             class="btn btn-danger btn-sm d-flex align-items-center justify-content-center rounded-3 p-2" 
                                             title="Eliminar registro"
                                             onclick="confirmarEliminarCompra('<?= base_url('admin/cuentas/eliminar/' . $c['idCompra']) ?>')">
                                         <i class="fas fa-trash-alt text-white"></i>
                                     </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <div class="mb-3">
                                <i class="fas fa-file-invoice fs-1 text-muted opacity-50"></i>
                            </div>
                            <h5 class="fw-bold text-white-50">Sin adeudos pendientes</h5>
                            <p class="mb-0 small">El cliente no tiene compras pendientes de pago en este momento.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Listado de Abonos -->
<div class="card border-0 shadow-sm admin-card mt-4">
    <div class="card-header border-bottom border-secondary border-opacity-25 py-3 bg-transparent d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-white"><i class="fas fa-hand-holding-usd me-2 text-success"></i> Historial de Abonos / Pagos</h6>
        <span class="badge bg-success rounded-pill px-3 py-1.5 fw-bold" style="font-size: 0.8rem;">Total Recibido Histórico: $<?= number_format($totalPagadoHistorico, 2) ?></span>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 admin-table">
            <thead class="text-muted admin-table-thead">
                <tr>
                    <th class="ps-4 py-3 admin-table-th">ID Abono</th>
                    <th class="py-3 admin-table-th">Fecha de Pago</th>
                    <th class="py-3 admin-table-th">Concepto / Compra Relacionada</th>
                    <th class="py-3 text-end pe-4 admin-table-th">Monto Abono</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($abonos)): ?>
                    <?php foreach ($abonos as $a): ?>
                        <tr class="admin-table-tr">
                            <td class="ps-4 py-3 text-white-50 font-monospace small">
                                #<?= $a['idAbono'] ?>
                            </td>
                            <td class="py-3 text-white">
                                <?= date('d/m/Y', strtotime($a['fechaAbono'])) ?>
                            </td>
                            <td class="py-3 text-white-50 small">
                                <?php if ($a['idCompra'] > 0): ?>
                                    <?php 
                                    // Buscar la descripción de la compra relacionada
                                    $descCompra = "Compra #{$a['idCompra']}";
                                    foreach ($compras as $co) {
                                        if ($co['idCompra'] == $a['idCompra']) {
                                            $descCompra = $co['descProducto'];
                                            break;
                                        }
                                    }
                                    ?>
                                    <span class="badge bg-dark border border-secondary text-white-50 me-1" style="font-size: 0.65rem;">Específico</span> Relacionado a: <strong><?= esc($descCompra) ?></strong>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success me-1" style="font-size: 0.65rem;">Abono General</span> Abono a cuenta corriente
                                <?php endif; ?>
                            </td>
                            <td class="py-3 text-end text-success fw-bold pe-4">
                                +$<?= number_format($a['abono'], 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <div class="mb-3">
                                <i class="fas fa-receipt fs-1 text-muted opacity-35"></i>
                            </div>
                            <h6 class="fw-bold text-white-50">No se han registrado abonos</h6>
                            <p class="mb-0 small">No hay pagos registrados para este cliente.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
</div>
</div>

<!-- OOB Swap para actualizar en caliente la barra lateral izquierda de clientes -->
<div id="client-item-<?= $cliente['idCliente'] ?>" hx-swap-oob="true"
    class="list-group-item client-item p-3 border-bottom border-secondary border-opacity-25 text-white d-flex justify-content-between align-items-center active"
    hx-get="<?= base_url('admin/cuentas/compras/' . $cliente['idCliente']) ?>"
    hx-target="#compras-cliente-container" hx-indicator="#loading-indicator"
    onclick="seleccionarCliente(<?= $cliente['idCliente'] ?>)">
    <div>
        <div class="fw-bold text-truncate client-name" style="max-width: 180px;">
            <?= esc($cliente['nombre']) ?></div>
        <small class="text-white-50 client-phone"><i class="fas fa-phone-alt me-1 text-muted"
                style="font-size: 0.75rem;"></i> <?= esc($cliente['cel'] ?: 'Sin teléfono') ?></small>
        
        <!-- Badges de entrega en el sidebar -->
        <div class="mt-1 d-flex gap-1.5 flex-wrap">
            <?php if (($porEmpacarCount ?? 0) > 0): ?>
                <span class="badge bg-danger text-white font-monospace" style="font-size: 0.65rem;" title="Productos por empacar">
                    <i class="fas fa-box"></i> <?= $porEmpacarCount ?> empacar
                </span>
            <?php endif; ?>
            <?php if (($porEntregarCount ?? 0) > 0): ?>
                <span class="badge bg-info text-dark font-monospace" style="font-size: 0.65rem;" title="Productos por entregar">
                    <i class="fas fa-boxes"></i> <?= $porEntregarCount ?> entregar
                </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="text-end">
        <?php if ($totalPendiente > 0): ?>
            <span class="badge bg-danger rounded-pill fw-bold" id="sidebar-debt-<?= $cliente['idCliente'] ?>">
                $<?= number_format($totalPendiente, 2) ?>
            </span>
        <?php else: ?>
            <span class="badge bg-secondary rounded-pill" id="sidebar-debt-<?= $cliente['idCliente'] ?>">
                $0.00
            </span>
        <?php endif; ?>
    </div>
</div>
