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
            $mensaje .= "  · {$c['cantidad']} {$c['descProducto']}: $" . number_format($c['totalProduc'], 2) . "\n";
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
    <div class="row g-3 mt-3">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="admin-stat-card border border-secondary border-opacity-25 p-3 rounded-3" style="background: rgba(0,0,0,0.15); height: 100%;">
                <div class="admin-stat-label opacity-75"><i class="fas fa-shopping-bag me-1 text-info"></i> Suma de Adeudos</div>
                <div class="fs-4 fw-bold text-white mt-1" id="lbl-total-compras">$<?= number_format($totalComprasActivas, 2) ?></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="admin-stat-card border border-secondary border-opacity-25 p-3 rounded-3" style="background: rgba(0,0,0,0.15); height: 100%;">
                <div class="admin-stat-label opacity-75"><i class="fas fa-check-circle me-1 text-success"></i> Abonado a Cuenta</div>
                <div class="fs-4 fw-bold text-success mt-1" id="lbl-total-pagado">$<?= number_format($totalPagadoActivo, 2) ?></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="admin-stat-card border border-secondary border-opacity-25 p-3 rounded-3" style="background: rgba(0,0,0,0.15); height: 100%;">
                <div class="admin-stat-label opacity-75"><i class="fas fa-hourglass-half me-1 text-danger"></i> Total Pendiente</div>
                <div class="fs-4 fw-bold text-danger mt-1" id="lbl-total-pendiente">$<?= number_format($totalPendiente, 2) ?></div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="admin-stat-card border border-secondary border-opacity-25 p-3 rounded-3" style="background: rgba(0,0,0,0.15); height: 100%;">
                <div class="admin-stat-label opacity-75"><i class="fas fa-boxes me-1 text-warning"></i> Control de Entregas</div>
                <div class="fs-4 fw-bold text-warning mt-1" id="lbl-por-entregar" style="font-size: 1.15rem; padding-top: 4px;">
                    <span class="text-danger" id="lbl-val-por-empacar"><?= $porEmpacarCount ?? 0 ?></span> <span class="small text-white-50" style="font-size: 0.72rem;">por empacar</span>
                    <span class="mx-1 text-white-50">|</span>
                    <span class="text-info" id="lbl-val-por-entregar"><?= $porEntregarCount ?? 0 ?></span> <span class="small text-white-50" style="font-size: 0.72rem;">por entregar</span>
                </div>
            </div>
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
                                $<?= number_format($c['totalProduc'], 2) ?>
                            </td>
                            <td class="py-3 text-center">
                                <div class="form-check form-switch form-switch-premium d-inline-block">
                                    <input class="form-check-input" type="checkbox" role="switch" 
                                           id="switch-estatus-<?= $c['idCompra'] ?>" 
                                           <?= $c['estatusCompra'] == '1' ? 'checked' : '' ?>
                                           onclick="toggleEstatusCompra(<?= $c['idCompra'] ?>)">
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
                                            <button class="dropdown-item py-1.5 px-3 rounded d-flex align-items-center gap-2" type="button" onclick="cambiarEstatusEntrega(<?= $c['idCompra'] ?>, 0)">
                                                <span class="text-danger"><i class="fas fa-box"></i> Pendiente</span>
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item py-1.5 px-3 rounded d-flex align-items-center gap-2" type="button" onclick="cambiarEstatusEntrega(<?= $c['idCompra'] ?>, 1)">
                                                <span class="text-info"><i class="fas fa-boxes"></i> Empacado</span>
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item py-1.5 px-3 rounded d-flex align-items-center gap-2" type="button" onclick="cambiarEstatusEntrega(<?= $c['idCompra'] ?>, 2)">
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
