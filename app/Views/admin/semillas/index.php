<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link class="styles-admin-theme" rel="stylesheet" href="<?= base_url('css/admin.css?v=1.2') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <!-- Encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 admin-title">Ventas de Semillas y Repelentes</h2>
            <p class="text-muted mb-0">Registra ventas, consulta comisiones y administra las liquidaciones con Bea.</p>
            <div class="admin-subtitle-line" style="background-color: #ffc107;"></div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-warning rounded-pill px-4 fw-bold text-dark hover-shadow d-inline-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#modalNuevaVenta">
                <i class="bi bi-plus-circle"></i> Registrar Venta
            </button>
            <a href="<?= base_url('admin/cuentas') ?>"
                class="btn btn-outline-light rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2">
                <i class="bi bi-wallet2"></i> Cuentas Clientes
            </a>
        </div>
    </div>

    <!-- Alertas de Flashdata -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 bg-dark text-success shadow mb-4" role="alert" style="border-left: 4px solid #28a745 !important;">
            <i class="bi bi-check-circle-fill me-2"></i> <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 bg-dark text-danger shadow mb-4" role="alert" style="border-left: 4px solid #dc3545 !important;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Tarjetas de Estadísticas (KPIs) -->
    <div class="row g-3 mb-4">
        <!-- Pendiente de Cobro -->
        <div class="col-6 col-lg-3">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #ef4444 !important; background: #1f222b !important;">
                <div class="admin-stat-label">
                    <i class="bi bi-hourglass-split text-danger"></i> Pendiente por Cobrar
                </div>
                <div class="admin-stat-value text-danger">
                    $<?= number_format($estadisticas['pendiente_cobrar'], 2) ?>
                </div>
                <small class="text-white-50 mt-1" style="font-size: 0.75rem;">Aún adeudado por clientes</small>
            </div>
        </div>

        <!-- Cobrado, por entregar a Bea -->
        <div class="col-6 col-lg-3">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #10b981 !important; background: #1f222b !important;">
                <div class="admin-stat-label">
                    <i class="bi bi-cash-coin text-success"></i> Por Entregar a Bea
                </div>
                <div class="admin-stat-value text-success d-flex align-items-center justify-content-between">
                    <span>$<?= number_format($estadisticas['por_entregar_bea'], 2) ?></span>
                    <?php if ($estadisticas['por_entregar_bea'] > 0): ?>
                        <button class="btn btn-sm btn-success rounded-pill px-2.5 py-1 fw-bold text-white small"
                            style="font-size: 0.7rem; box-shadow: 0 0 10px rgba(40,167,69,0.3);"
                            data-bs-toggle="modal" data-bs-target="#modalLiquidarBea">
                            Liquidar
                        </button>
                    <?php endif; ?>
                </div>
                <small class="text-white-50 mt-1" style="font-size: 0.75rem;">Cobrado, pendiente liquidar</small>
            </div>
        </div>

        <!-- Ya entregado a Bea -->
        <div class="col-6 col-lg-3">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #0ea5e9 !important; background: #1f222b !important;">
                <div class="admin-stat-label">
                    <i class="bi bi-check-all text-info"></i> Entregado a Bea
                </div>
                <div class="admin-stat-value text-info">
                    $<?= number_format($estadisticas['entregado_bea'], 2) ?>
                </div>
                <small class="text-white-50 mt-1" style="font-size: 0.75rem;">Total histórico entregado</small>
            </div>
        </div>

        <!-- Ganancia Neta -->
        <div class="col-6 col-lg-3">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #0dcaf0 !important; background: #1f222b !important;">
                <div class="admin-stat-label">
                    <i class="bi bi-graph-up-arrow text-profit"></i> Tu Ganancia Neta
                </div>
                <div class="admin-stat-value text-profit">
                    $<?= number_format($estadisticas['ganancia_total'], 2) ?>
                </div>
                <small class="text-white-50 mt-1" style="font-size: 0.75rem;">De ventas cobradas ($1/$5 bolsa)</small>
            </div>
        </div>
    </div>

    <!-- Navegación por pestañas -->
    <div class="card border-0 shadow-sm admin-card mb-4">
        <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 p-0">
            <ul class="nav nav-tabs border-0 p-2 gap-2" id="seedsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link tab-nav-link active" id="general-tab" data-bs-toggle="tab"
                        data-bs-target="#general" type="button" role="tab" aria-controls="general"
                        aria-selected="true">
                        <i class="bi bi-list-task me-1"></i> Ventas Registradas
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link tab-nav-link" id="clientes-tab" data-bs-toggle="tab"
                        data-bs-target="#clientes" type="button" role="tab" aria-controls="clientes"
                        aria-selected="false">
                        <i class="bi bi-people me-1"></i> Reporte por Cliente
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-4">
            <div class="tab-content" id="seedsTabContent">
                <!-- Pestaña 1: Listado General de Ventas -->
                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <!-- Buscador de Ventas -->
                    <div class="mb-3 d-flex justify-content-end">
                        <div class="input-group" style="max-width: 350px;">
                            <span class="input-group-text bg-dark text-white border-secondary"><i class="bi bi-search"></i></span>
                            <input type="text" id="search-ventas" class="form-control bg-dark text-white border-secondary" placeholder="Buscar por cliente, producto o fecha...">
                            <button class="btn btn-outline-secondary" type="button" id="btn-clear-search-ventas" style="display: none; border-color: rgba(255, 255, 255, 0.15); border-left: none;"><i class="fas fa-times text-white-50"></i></button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead class="admin-table-thead text-white-50">
                                <tr>
                                    <th class="ps-3 admin-table-th">Fecha</th>
                                    <th class="admin-table-th">Cliente</th>
                                    <th class="admin-table-th">Producto</th>
                                    <th class="admin-table-th text-center">Cant.</th>
                                    <th class="admin-table-th text-end">Total Venta</th>
                                    <th class="admin-table-th text-end">A Bea</th>
                                    <th class="admin-table-th text-center">Estatus Pago</th>
                                    <th class="admin-table-th text-center">Entregado Bea</th>
                                    <th class="pe-3 admin-table-th text-center" style="width: 80px;"></th>
                                </tr>
                            </thead>
                            <tbody class="text-white">
                                <?php if (!empty($ventas)): ?>
                                    <?php foreach ($ventas as $v): ?>
                                        <tr class="admin-table-tr">
                                            <td class="ps-3 fw-semibold"><?= date('d/m/Y', strtotime($v['fecha'])) ?></td>
                                            <td>
                                                <?php if ($v['id_cliente'] > 0): ?>
                                                    <a href="<?= base_url('admin/cuentas') ?>" class="text-white text-decoration-none fw-bold hover-warning">
                                                        <i class="bi bi-person-fill text-muted me-1"></i> <?= esc($v['cliente_registrado']) ?>
                                                    </a>
                                                    <?php if ($v['cel_cliente']): ?>
                                                        <br><small class="text-white-50" style="font-size: 0.75rem;"><i class="bi bi-telephone-fill"></i> <?= esc($v['cel_cliente']) ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <i class="bi bi-person me-1 text-muted"></i> <?= esc($v['nombre_cliente'] ?: 'Cliente General') ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= esc($v['producto']) ?></td>
                                            <td class="text-center fw-semibold"><?= $v['cantidad'] ?></td>
                                            <td class="text-end fw-bold text-white">$<?= number_format($v['precio_venta'] * $v['cantidad'], 2) ?></td>
                                            <td class="text-end text-success fw-semibold">$<?= number_format($v['precio_bea'] * $v['cantidad'], 2) ?></td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill <?= $v['estatus_pago'] === 'Pagado' ? 'badge-status-paid' : 'badge-status-pending' ?>">
                                                    <?= esc($v['estatus_pago']) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge rounded-pill <?= $v['entregado_bea'] === 'Si' ? 'badge-status-delivered' : 'badge-status-not-delivered' ?>">
                                                    <?= $v['entregado_bea'] === 'Si' ? 'Entregado' : 'No entregado' ?>
                                                </span>
                                            </td>
                                            <td class="pe-3 text-center">
                                                <button class="btn btn-outline-danger btn-sm border-0 p-1.5"
                                                    onclick="confirmarEliminarVenta(<?= $v['id'] ?>, <?= !empty($v['id_cuenta_cliente']) ? 'true' : 'false' ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-5 text-white-50">
                                            <i class="bi bi-seedling fs-1 d-block mb-3 text-muted"></i>
                                            Aún no has registrado ninguna venta de semillas o repelente.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pestaña 2: Reporte Acumulado por Cliente -->
                <div class="tab-pane fade" id="clientes" role="tabpanel" aria-labelledby="clientes-tab">
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead class="admin-table-thead text-white-50">
                                <tr>
                                    <th class="ps-3 admin-table-th">Cliente</th>
                                    <th class="admin-table-th text-center">Total Ventas</th>
                                    <th class="admin-table-th text-center">Unidades Compradas</th>
                                    <th class="admin-table-th text-end">Total Consumido</th>
                                    <th class="admin-table-th text-end text-danger">Total Adeudado</th>
                                    <th class="pe-3 admin-table-th text-center" style="width: 150px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-white">
                                <?php if (!empty($clientesReporte)): ?>
                                    <?php foreach ($clientesReporte as $cr): ?>
                                        <tr class="admin-table-tr">
                                            <td class="ps-3 fw-bold">
                                                <i class="bi bi-person-circle text-muted me-1"></i> <?= esc($cr['nombre_cliente']) ?>
                                                <?php if ($cr['cel']): ?>
                                                    <br><small class="text-white-50 fw-normal" style="font-size: 0.75rem;"><i class="bi bi-telephone"></i> <?= esc($cr['cel']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center"><?= $cr['total_ventas'] ?></td>
                                            <td class="text-center"><?= $cr['total_productos'] ?></td>
                                            <td class="text-end fw-bold">$<?= number_format($cr['total_monto'], 2) ?></td>
                                            <td class="text-end fw-bold text-danger">
                                                $<?= number_format($cr['total_pendiente'], 2) ?>
                                            </td>
                                            <td class="pe-3 text-center">
                                                <?php if ($cr['id_cliente'] > 0): ?>
                                                    <a href="<?= base_url('admin/cuentas') ?>" class="btn btn-outline-warning btn-sm rounded-pill px-3 py-1 text-decoration-none">
                                                        <i class="bi bi-wallet2"></i> Ver Cuenta
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">Sin cuenta fija</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-white-50">
                                            No hay datos de clientes disponibles.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: REGISTRAR VENTA -->
<div class="modal fade" id="modalNuevaVenta" tabindex="-1" aria-labelledby="modalNuevaVentaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white">
            <div class="modal-header modal-encargo-header py-3 px-4">
                <h5 class="modal-title fw-bold text-white" id="modalNuevaVentaLabel">
                    <i class="bi bi-plus-circle-fill text-warning me-2"></i> Registrar Venta de Snacks / Repelente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevaVenta" action="<?= base_url('admin/semillas/crear') ?>" method="POST" onsubmit="return validarVentaForm();">
                <?= csrf_field() ?>
                
                <div class="modal-body p-4">
                    <!-- Selección de Cliente -->
                    <div class="mb-3">
                        <label class="form-label modal-encargo-label">Tipo de Cliente</label>
                        <div class="btn-group w-100 mb-2" role="group">
                            <input type="radio" class="btn-check" name="tipo_cliente_btn" id="cli_general" value="general" checked onclick="toggleClienteInput('general')">
                            <label class="btn btn-outline-warning btn-sm fw-bold" for="cli_general">Cliente General / Otro</label>
                            
                            <input type="radio" class="btn-check" name="tipo_cliente_btn" id="cli_registrado" value="registrado" onclick="toggleClienteInput('registrado')">
                            <label class="btn btn-outline-warning btn-sm fw-bold" for="cli_registrado">Cliente Fijo Registrado</label>
                        </div>
                        
                        <!-- Input para cliente general (Libre) -->
                        <div id="seccion_cliente_libre">
                            <input type="text" class="form-control modal-encargo-control text-white" id="nombre_cliente_libre" name="nombre_cliente_libre" placeholder="Nombre del cliente (Opcional)">
                        </div>

                        <!-- Dropdown para cliente registrado -->
                        <div id="seccion_cliente_registrado" style="display: none;">
                            <select class="form-select modal-encargo-select text-white w-100" id="id_cliente" name="id_cliente">
                                <option value="0" selected>-- Selecciona un Cliente Fijo --</option>
                                <?php foreach ($clientes as $c): ?>
                                    <option value="<?= $c['idCliente'] ?>"><?= esc($c['nombre']) ?> <?= $c['cel'] ? '('.$c['cel'].')' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Producto -->
                    <div class="mb-3">
                        <label for="producto_sel" class="form-label modal-encargo-label">Producto *</label>
                        <select class="form-select modal-encargo-select text-white w-100" id="producto_sel" name="producto_sel" onchange="seleccionarProductoPredefinido(this.value)" required>
                            <option value="" selected>-- Selecciona un Producto --</option>
                            <?php foreach ($productosPredefinidos as $p): ?>
                                <option value="<?= esc($p['nombre']) ?>"><?= esc($p['nombre']) ?> - Venta: $<?= number_format($p['precio_venta'], 2) ?> (<?= $p['inventario'] ?>)</option>
                            <?php endforeach; ?>
                            <option value="otro">Otro (Personalizado)</option>
                        </select>
                    </div>

                    <!-- Nombre del Producto personalizado (si elige "Otro") -->
                    <div class="mb-3" id="seccion_producto_nombre_personalizado" style="display: none;">
                        <label for="producto_custom" class="form-label modal-encargo-label">Nombre del Producto *</label>
                        <input type="text" class="form-control modal-encargo-control text-white" id="producto_custom" name="producto_custom" placeholder="Ej: Mix Especial">
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- Cantidad -->
                        <div class="col-6">
                            <label for="cantidad" class="form-label modal-encargo-label">Cantidad *</label>
                            <input type="number" class="form-control modal-encargo-control text-white" id="cantidad" name="cantidad" value="1" min="1" required oninput="calcularGranTotalForm()">
                        </div>
                        
                        <!-- Estatus / Método de Pago -->
                        <div class="col-6">
                            <label class="form-label modal-encargo-label d-block">Pago *</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="metodo_pago" id="pago_contado" value="contado" checked onclick="toggleMetodoPago('contado')">
                                <label class="btn btn-outline-success btn-sm fw-bold" for="pago_contado">Contado</label>
                                
                                <input type="radio" class="btn-check" name="metodo_pago" id="pago_cuenta" value="cuenta" onclick="toggleMetodoPago('cuenta')">
                                <label class="btn btn-outline-danger btn-sm fw-bold" for="pago_cuenta">A Cuenta</label>
                            </div>
                        </div>
                    </div>

                    <!-- Precios -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="precio_venta" class="form-label modal-encargo-label">Precio de Venta ($) *</label>
                            <input type="number" step="0.01" min="0" class="form-control modal-encargo-control text-white" id="precio_venta" name="precio_venta" value="0.00" required oninput="calcularGranTotalForm()">
                        </div>
                        <div class="col-6">
                            <label for="precio_bea" class="form-label modal-encargo-label">Precio Bea ($) *</label>
                            <input type="number" step="0.01" min="0" class="form-control modal-encargo-control text-white" id="precio_bea" name="precio_bea" value="0.00" required oninput="calcularGranTotalForm()">
                        </div>
                    </div>

                    <!-- Fecha -->
                    <div class="mb-4">
                        <label for="fecha" class="form-label modal-encargo-label">Fecha de Venta *</label>
                        <input type="date" class="form-control modal-encargo-control text-white" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <!-- Inputs finales ocultos que se envían -->
                    <input type="hidden" id="producto" name="producto" value="">

                    <!-- Resumen del Registro -->
                    <div class="p-3 rounded bg-dark border border-secondary border-opacity-25">
                        <div class="d-flex justify-content-between align-items-center mb-1 text-white-50 small">
                            <span>Total de la venta:</span>
                            <span class="fw-bold text-white" id="lbl_venta_total">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1 text-white-50 small">
                            <span>A pagar a Bea:</span>
                            <span class="fw-bold text-success" id="lbl_bea_total">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top border-secondary border-opacity-25">
                            <span class="small text-white-50 fw-bold">TU GANANCIA ESTIMADA:</span>
                            <span class="fs-5 fw-bold text-profit" id="lbl_ganancia_total">$0.00</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer modal-encargo-footer d-flex gap-2">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark hover-shadow">
                        <i class="bi bi-save"></i> Registrar Venta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: LIQUIDAR A BEA -->
<div class="modal fade" id="modalLiquidarBea" tabindex="-1" aria-labelledby="modalLiquidarBeaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content modal-encargo-content text-white" style="border-radius: 20px; background-color: #121824; border: 1px solid rgba(255,255,255,0.15);">
            <div class="modal-header modal-encargo-header py-3 px-4">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="modalLiquidarBeaLabel">
                    <i class="bi bi-cash-coin text-success"></i> Liquidación a Bea
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="mb-3 text-white-50" style="font-size: 0.95rem;">¿Estás seguro de que deseas liquidar las ventas a Bea?</p>
                <div class="p-3 bg-dark rounded border border-secondary border-opacity-25 mb-3">
                    <span class="small text-white-50 d-block mb-1">Monto a retirar de Caja Chica:</span>
                    <span class="fs-3 fw-bold text-success">$<?= number_format($estadisticas['por_entregar_bea'], 2) ?></span>
                </div>
                <p class="small text-white-50 mb-0">Esta acción marcará los registros correspondientes como <strong>Entregados</strong> y registrará de forma automática el Egreso en Caja Chica.</p>
            </div>
            <div class="modal-footer d-flex gap-2 justify-content-center pb-4" style="border-top: none;">
                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <form action="<?= base_url('admin/semillas/entregar') ?>" method="POST" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Confirmar Liquidación</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: CONFIRMAR ELIMINAR VENTA -->
<div class="modal fade" id="modalConfirmarEliminarVenta" tabindex="-1" aria-labelledby="modalConfirmarEliminarVentaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content modal-encargo-content text-white" style="border-radius: 20px; background-color: #121824; border: 1px solid rgba(255,255,255,0.15);">
            <div class="modal-header modal-encargo-header py-3 px-4">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="modalConfirmarEliminarVentaLabel">
                    <i class="bi bi-exclamation-triangle text-danger"></i> ¿Eliminar venta?
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="mb-0 text-white-50" style="font-size: 0.95rem;" id="p_eliminar_mensaje">¿Estás seguro de que deseas eliminar este registro de venta?</p>
            </div>
            <div class="modal-footer d-flex gap-2 justify-content-center pb-4" style="border-top: none;">
                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <form id="formConfirmarEliminarVenta" action="" method="POST" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Pasar productos predefinidos a JS
    const productosPredefinidos = <?= json_encode($productosPredefinidos) ?>;

    // Controlar selección de tipo de cliente en el modal
    function toggleClienteInput(tipo) {
        if (tipo === 'general') {
            document.getElementById('seccion_cliente_libre').style.display = 'block';
            document.getElementById('seccion_cliente_registrado').style.display = 'none';
            document.getElementById('id_cliente').value = '0';
        } else {
            document.getElementById('seccion_cliente_libre').style.display = 'none';
            document.getElementById('seccion_cliente_registrado').style.display = 'block';
            document.getElementById('nombre_cliente_libre').value = '';
        }
    }

    // Controlar comportamiento del método de pago
    function toggleMetodoPago(metodo) {
        if (metodo === 'cuenta') {
            // Obligar a que el cliente sea registrado
            document.getElementById('cli_registrado').checked = true;
            toggleClienteInput('registrado');
        }
    }

    // Al seleccionar un producto, auto-completar precios
    function seleccionarProductoPredefinido(nombre) {
        const customDiv = document.getElementById('seccion_producto_nombre_personalizado');
        const customInput = document.getElementById('producto_custom');
        const precioVentaInput = document.getElementById('precio_venta');
        const precioBeaInput = document.getElementById('precio_bea');

        if (nombre === 'otro') {
            customDiv.style.display = 'block';
            customInput.required = true;
            customInput.value = '';
            precioVentaInput.value = '0.00';
            precioBeaInput.value = '0.00';
        } else {
            customDiv.style.display = 'none';
            customInput.required = false;
            customInput.value = '';

            // Buscar en el JSON
            const prod = productosPredefinidos.find(p => p.nombre === nombre);
            if (prod) {
                precioVentaInput.value = prod.precio_venta.toFixed(2);
                precioBeaInput.value = prod.precio_bea.toFixed(2);
            }
        }
        calcularGranTotalForm();
    }

    // Calcular montos acumulados y ganancias en el formulario
    function calcularGranTotalForm() {
        const cantidad = parseInt(document.getElementById('cantidad').value) || 0;
        const precioVenta = parseFloat(document.getElementById('precio_venta').value) || 0;
        const precioBea = parseFloat(document.getElementById('precio_bea').value) || 0;

        const totalVenta = cantidad * precioVenta;
        const totalBea = cantidad * precioBea;
        const totalGanancia = totalVenta - totalBea;

        document.getElementById('lbl_venta_total').innerText = '$' + totalVenta.toFixed(2);
        document.getElementById('lbl_bea_total').innerText = '$' + totalBea.toFixed(2);
        document.getElementById('lbl_ganancia_total').innerText = '$' + totalGanancia.toFixed(2);
    }

    // Validar formulario antes de enviar
    function validarVentaForm() {
        const pSel = document.getElementById('producto_sel').value;
        const customVal = document.getElementById('producto_custom').value.trim();
        const inputProductoOculto = document.getElementById('producto');

        if (!pSel) {
            alert('Por favor, selecciona un producto.');
            return false;
        }

        if (pSel === 'otro') {
            if (!customVal) {
                alert('Escribe el nombre del producto personalizado.');
                return false;
            }
            inputProductoOculto.value = customVal;
        } else {
            inputProductoOculto.value = pSel;
        }

        const metodo = document.querySelector('input[name="metodo_pago"]:checked').value;
        const idCliente = parseInt(document.getElementById('id_cliente').value) || 0;

        if (metodo === 'cuenta' && idCliente <= 0) {
            alert('Debes seleccionar un cliente registrado para poder guardar a cuenta.');
            return false;
        }

        return true;
    }

    // Configurar modal de eliminación
    function confirmarEliminarVenta(id, tieneCuentaCliente) {
        const form = document.getElementById('formConfirmarEliminarVenta');
        form.action = '<?= base_url("admin/semillas/eliminar") ?>/' + id;

        const pMsg = document.getElementById('p_eliminar_mensaje');
        if (tieneCuentaCliente) {
            pMsg.innerHTML = '¿Estás seguro de que deseas eliminar esta venta? <br><strong class="text-danger">Aviso:</strong> Esta venta está vinculada a la cuenta corriente del cliente. Se eliminará también el adeudo correspondiente.';
        } else {
            pMsg.innerText = '¿Estás seguro de que deseas eliminar este registro de venta?';
        }

        const modal = new bootstrap.Modal(document.getElementById('modalConfirmarEliminarVenta'));
        modal.show();
    }

    // Elementos de búsqueda
    const searchInputVentas = document.getElementById('search-ventas');
    const btnClearSearchVentas = document.getElementById('btn-clear-search-ventas');

    // Filtrar la tabla de ventas registradas localmente
    searchInputVentas.addEventListener('input', function() {
        const query = this.value.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
        const rows = document.querySelectorAll('#general tbody tr');

        // Mostrar u ocultar el botón "x" de borrar
        if (query.length > 0) {
            btnClearSearchVentas.style.display = 'block';
        } else {
            btnClearSearchVentas.style.display = 'none';
        }

        rows.forEach(row => {
            // Ignorar la fila de "No hay registros" si existe
            if (row.cells.length <= 1) return; 

            const fecha = row.cells[0].textContent.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
            const cliente = row.cells[1].textContent.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
            const producto = row.cells[2].textContent.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();

            if (fecha.includes(query) || cliente.includes(query) || producto.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Acción para limpiar la búsqueda
    btnClearSearchVentas.addEventListener('click', function() {
        searchInputVentas.value = '';
        btnClearSearchVentas.style.display = 'none';
        
        // Simular evento input para restaurar todos los registros
        searchInputVentas.dispatchEvent(new Event('input'));
        searchInputVentas.focus();
    });

    // Inicializar cálculos al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        calcularGranTotalForm();
    });
</script>
<?= $this->endSection() ?>
