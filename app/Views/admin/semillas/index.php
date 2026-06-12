<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link class="styles-admin-theme" rel="stylesheet" href="<?= base_url('css/admin.css?v=1.2') ?>">
<style>
    .htmx-indicator {
        display: none !important;
    }
    .htmx-request .htmx-indicator {
        display: inline-block !important;
    }
    .htmx-request.htmx-indicator {
        display: inline-block !important;
    }
</style>

<!-- Notificaciones flotantes tipo Toast -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;"></div>

<div id="semillas-container" class="container py-4">
    <!-- Scripts inline de Flashdata para lanzar Toasts tras cargas HTMX -->
    <?php if (session()->getFlashdata('success')): ?>
        <script>
            (function() {
                const msg = "<?= esc(session()->getFlashdata('success'), 'js') ?>";
                setTimeout(() => {
                    if (window.mostrarToastExito) {
                        window.mostrarToastExito(msg);
                    }
                }, 50);
            })();
        </script>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <script>
            (function() {
                const msg = "<?= esc(session()->getFlashdata('error'), 'js') ?>";
                setTimeout(() => {
                    if (window.mostrarToastError) {
                        window.mostrarToastError(msg);
                    } else {
                        alert(msg);
                    }
                }, 50);
            })();
        </script>
    <?php endif; ?>

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
                class="btn btn-dark rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2 text-white border border-secondary">
                <i class="bi bi-wallet2"></i> Cuentas Clientes
            </a>
        </div>
    </div>

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
                    <span id="lbl-por-entregar-bea">$<?= number_format($estadisticas['por_entregar_bea'], 2) ?></span>
                    <?php if ($estadisticas['por_entregar_bea'] > 0): ?>
                        <button class="btn btn-sm btn-success rounded-pill px-2.5 py-1 fw-bold text-white small"
                            style="font-size: 0.7rem; box-shadow: 0 0 10px rgba(40,167,69,0.3);"
                            data-bs-toggle="modal" data-bs-target="#modalLiquidarBea"
                            onclick="actualizarMontoLiquidar()">
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white">
            <div class="modal-header modal-encargo-header py-3 px-4">
                <h5 class="modal-title fw-bold text-white" id="modalNuevaVentaLabel">
                    <i class="bi bi-plus-circle-fill text-warning me-2"></i> Registrar Venta de Snacks / Repelente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevaVenta" action="<?= base_url('admin/semillas/crear') ?>" method="POST" onsubmit="return validarVentaForm();"
                hx-post="<?= base_url('admin/semillas/crear') ?>"
                hx-target="#semillas-container"
                hx-select="#semillas-container"
                hx-swap="outerHTML"
                hx-indicator="#btn-crear-spinner">
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

                        <!-- Autocomplete para cliente registrado (mismo estilo que Nuevo Encargo) -->
                        <div id="seccion_cliente_registrado" style="display: none;">
                            <input type="hidden" name="id_cliente" id="id_cliente" value="0">
                            <div class="position-relative searchable-select-container" id="container-venta-cliente">
                                <input type="text"
                                       class="form-control modal-encargo-control text-white w-100"
                                       id="venta_cliente_search_input"
                                       placeholder="Escribe para buscar por nombre o teléfono..."
                                       autocomplete="off">
                                <div class="dropdown-menu w-100 p-2 shadow-lg"
                                     id="venta_cliente_search_dropdown"
                                     style="max-height: 200px; overflow-y: auto; background-color: #2a2e3d; border: 1px solid rgba(255,255,255,0.12); border-radius: 0.5rem; display: none; position: absolute; z-index: 1060; left: 0; right: 0;">
                                    <?php foreach ($clientes as $c): ?>
                                        <button type="button"
                                                class="dropdown-item text-white border-0 py-2 px-3 rounded-2 text-start venta-cliente-item-option"
                                                style="background: transparent; font-size: 0.85rem;"
                                                data-id="<?= $c['idCliente'] ?>"
                                                data-nombre="<?= esc($c['nombre']) ?>"
                                                data-cel="<?= esc($c['cel']) ?>">
                                            <strong><?= esc($c['nombre']) ?></strong> <?= !empty($c['cel']) ? '('.esc($c['cel']).')' : '' ?>
                                        </button>
                                    <?php endforeach; ?>
                                    <div class="text-white-50 p-2 text-center small venta-cliente-no-results" style="display: none;">No se encontraron clientes</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN: AGREGAR PRODUCTO -->
                    <div class="card p-3 mb-3 bg-dark border-secondary border-opacity-25" style="border-radius: 12px;">
                        <h6 class="fw-bold text-warning mb-2" style="font-size: 0.9rem;">
                            <i class="bi bi-cart-plus-fill"></i> Agregar Producto a la Venta
                        </h6>
                        
                        <div class="row g-2 mb-2">
                            <div class="col-12 col-md-7">
                                <label for="producto_sel" class="form-label modal-encargo-label" style="font-size: 0.75rem;">Producto</label>
                                <select class="form-select modal-encargo-select text-white w-100 py-1.5" id="producto_sel" onchange="seleccionarProductoPredefinido(this.value)">
                                    <option value="" selected>-- Selecciona un Producto --</option>
                                    <?php foreach ($productosPredefinidos as $p): ?>
                                        <option value="<?= esc($p['nombre']) ?>"><?= esc($p['nombre']) ?> - Venta: $<?= number_format($p['precio_venta'], 2) ?> (<?= $p['inventario'] ?>)</option>
                                    <?php endforeach; ?>
                                    <option value="otro">Otro (Personalizado)</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-5" id="seccion_producto_nombre_personalizado" style="display: none;">
                                <label for="producto_custom" class="form-label modal-encargo-label" style="font-size: 0.75rem;">Nombre Producto Personalizado</label>
                                <input type="text" class="form-control modal-encargo-control text-white py-1.5" id="producto_custom" placeholder="Ej: Mix Especial">
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-4">
                                <label for="cantidad" class="form-label modal-encargo-label" style="font-size: 0.75rem;">Cantidad</label>
                                <input type="number" class="form-control modal-encargo-control text-white py-1.5" id="cantidad" value="1" min="1">
                            </div>
                            <div class="col-4">
                                <label for="precio_venta" class="form-label modal-encargo-label" style="font-size: 0.75rem;">Precio Venta ($)</label>
                                <input type="number" step="0.01" min="0" class="form-control modal-encargo-control text-white py-1.5" id="precio_venta" value="0.00">
                            </div>
                            <div class="col-4">
                                <label for="precio_bea" class="form-label modal-encargo-label" style="font-size: 0.75rem;">Precio Bea ($)</label>
                                <input type="number" step="0.01" min="0" class="form-control modal-encargo-control text-white py-1.5" id="precio_bea" value="0.00">
                            </div>
                        </div>

                        <button type="button" class="btn btn-warning btn-sm fw-bold text-dark w-100 mt-3 d-flex align-items-center justify-content-center gap-1" onclick="agregarProductoALaLista()">
                            <i class="bi bi-plus-circle-fill"></i> Agregar Producto
                        </button>
                    </div>

                    <!-- TABLA DE PRODUCTOS AGREGADOS -->
                    <div class="mb-3">
                        <label class="form-label modal-encargo-label"><i class="bi bi-list-ul"></i> Productos Seleccionados</label>
                        <div class="table-responsive border border-secondary border-opacity-25 rounded" style="max-height: 180px; overflow-y: auto; background-color: rgba(0,0,0,0.15);">
                            <table class="table table-dark table-sm align-middle mb-0" id="tabla_productos_lista">
                                <thead class="text-white-50" style="font-size: 0.75rem; background-color: rgba(255,255,255,0.05);">
                                    <tr>
                                        <th class="ps-2">Producto</th>
                                        <th class="text-center" style="width: 65px;">Cant.</th>
                                        <th class="text-end" style="width: 95px;">Precio Venta</th>
                                        <th class="text-end" style="width: 95px;">Precio Bea</th>
                                        <th class="text-end" style="width: 100px;">Total</th>
                                        <th class="text-center" style="width: 45px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_productos_lista" style="font-size: 0.8rem;">
                                    <tr id="tr_lista_vacia">
                                        <td colspan="6" class="text-center py-3 text-white-50">Aún no has agregado ningún producto.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- METODO DE PAGO Y FECHA -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label modal-encargo-label d-block">Pago *</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="metodo_pago" id="pago_contado" value="contado" checked onclick="toggleMetodoPago('contado')">
                                <label class="btn btn-outline-success btn-sm fw-bold" for="pago_contado">Contado</label>
                                
                                <input type="radio" class="btn-check" name="metodo_pago" id="pago_cuenta" value="cuenta" onclick="toggleMetodoPago('cuenta')">
                                <label class="btn btn-outline-danger btn-sm fw-bold" for="pago_cuenta">A Cuenta</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <label for="fecha" class="form-label modal-encargo-label">Fecha de Venta *</label>
                            <input type="date" class="form-control modal-encargo-control text-white py-1.5" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <!-- Contenedor para los inputs ocultos de los productos a enviar en el POST -->
                    <div id="inputs_productos_ocultos"></div>

                    <!-- Resumen del Registro -->
                    <div class="p-3 rounded bg-dark border border-secondary border-opacity-25 mt-3">
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
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark hover-shadow d-inline-flex align-items-center gap-2">
                        <span class="spinner-border spinner-border-sm htmx-indicator" role="status" aria-hidden="true" id="btn-crear-spinner"></span>
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
                    <span class="fs-3 fw-bold text-success" id="modal-liquidar-monto">$<?= number_format($estadisticas['por_entregar_bea'], 2) ?></span>
                </div>
                <p class="small text-white-50 mb-0">Esta acción marcará los registros correspondientes como <strong>Entregados</strong> y registrará de forma automática el Egreso en Caja Chica.</p>
            </div>
            <div class="modal-footer d-flex gap-2 justify-content-center pb-4" style="border-top: none;">
                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <form id="formLiquidarBea" action="<?= base_url('admin/semillas/entregar') ?>" method="POST" class="d-inline"
                    hx-post="<?= base_url('admin/semillas/entregar') ?>"
                    hx-target="#semillas-container"
                    hx-select="#semillas-container"
                    hx-swap="outerHTML"
                    hx-indicator="#btn-confirmar-liquidar-spinner">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold d-inline-flex align-items-center gap-2">
                        <span class="spinner-border spinner-border-sm htmx-indicator" role="status" aria-hidden="true" id="btn-confirmar-liquidar-spinner"></span>
                        Confirmar Liquidación
                    </button>
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
                <form id="formConfirmarEliminarVenta" action="" method="POST" class="d-inline"
                    hx-target="#semillas-container"
                    hx-select="#semillas-container"
                    hx-swap="outerHTML"
                    hx-indicator="#btn-confirmar-eliminar-spinner">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold d-inline-flex align-items-center gap-2">
                        <span class="spinner-border spinner-border-sm htmx-indicator" role="status" aria-hidden="true" id="btn-confirmar-eliminar-spinner"></span>
                        Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
</div> <!-- Cierre de semillas-container -->

<script>
(function() {
    // Pasar productos predefinidos a JS
    const productosPredefinidos = <?= json_encode($productosPredefinidos) ?>;

    // Controlar selección de tipo de cliente en el modal
    function toggleClienteInput(tipo) {
        if (tipo === 'general') {
            document.getElementById('seccion_cliente_libre').style.display = 'block';
            document.getElementById('seccion_cliente_registrado').style.display = 'none';
            document.getElementById('id_cliente').value = '0';
            document.getElementById('venta_cliente_search_input').value = '';
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

    // Inicializar el buscador de clientes en el modal de Venta
    (function initVentaClienteAutocomplete() {
        const input    = document.getElementById('venta_cliente_search_input');
        const dropdown = document.getElementById('venta_cliente_search_dropdown');
        const hidden   = document.getElementById('id_cliente');
        const options  = dropdown ? dropdown.querySelectorAll('.venta-cliente-item-option') : [];
        const noResults = dropdown ? dropdown.querySelector('.venta-cliente-no-results') : null;

        if (!input || !dropdown) return;

        // Mostrar dropdown al enfocar
        input.addEventListener('focus', function () {
            dropdown.style.display = 'block';
            // Mostrar todos al abrir
            options.forEach(opt => opt.style.display = 'block');
            if (noResults) noResults.style.display = 'none';
        });

        // Filtrar al escribir (por nombre o teléfono)
        input.addEventListener('input', function () {
            hidden.value = '0';
            const query = input.value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
            let matches = 0;

            options.forEach(opt => {
                const nombre = opt.getAttribute('data-nombre').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
                const cel    = opt.getAttribute('data-cel').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();

                if (nombre.includes(query) || cel.includes(query)) {
                    opt.style.display = 'block';
                    matches++;
                } else {
                    opt.style.display = 'none';
                }
            });

            if (noResults) noResults.style.display = matches === 0 ? 'block' : 'none';
        });

        // Seleccionar una opción
        options.forEach(opt => {
            opt.addEventListener('click', function () {
                hidden.value  = opt.getAttribute('data-id');
                input.value   = opt.getAttribute('data-nombre');
                dropdown.style.display = 'none';
            });
        });

        // Cerrar al hacer clic fuera
        document.addEventListener('click', function (e) {
            const container = input.closest('.searchable-select-container');
            if (container && !container.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    })();

    let itemsVenta = [];

    // Al seleccionar un producto, auto-completar precios
    function seleccionarProductoPredefinido(nombre) {
        const customDiv = document.getElementById('seccion_producto_nombre_personalizado');
        const customInput = document.getElementById('producto_custom');
        const precioVentaInput = document.getElementById('precio_venta');
        const precioBeaInput = document.getElementById('precio_bea');

        if (nombre === 'otro') {
            customDiv.style.display = 'block';
            customInput.value = '';
            precioVentaInput.value = '0.00';
            precioBeaInput.value = '0.00';
        } else {
            customDiv.style.display = 'none';
            customInput.value = '';

            // Buscar en el JSON
            const prod = productosPredefinidos.find(p => p.nombre === nombre);
            if (prod) {
                precioVentaInput.value = prod.precio_venta.toFixed(2);
                precioBeaInput.value = prod.precio_bea.toFixed(2);
            } else {
                precioVentaInput.value = '0.00';
                precioBeaInput.value = '0.00';
            }
        }
    }

    // Agregar un producto a la lista intermedia
    function agregarProductoALaLista() {
        const pSel = document.getElementById('producto_sel').value;
        const customVal = document.getElementById('producto_custom').value.trim();
        const cantidadVal = parseInt(document.getElementById('cantidad').value) || 0;
        const precioVentaVal = parseFloat(document.getElementById('precio_venta').value) || 0;
        const precioBeaVal = parseFloat(document.getElementById('precio_bea').value) || 0;

        if (!pSel) {
            alert('Por favor, selecciona un producto.');
            return;
        }

        let nombreProducto = pSel;
        if (pSel === 'otro') {
            if (!customVal) {
                alert('Escribe el nombre del producto personalizado.');
                return;
            }
            nombreProducto = customVal;
        }

        if (cantidadVal <= 0) {
            alert('La cantidad debe ser mayor o igual a 1.');
            return;
        }

        if (precioVentaVal < 0 || precioBeaVal < 0) {
            alert('Los precios no pueden ser negativos.');
            return;
        }

        // Agregar al arreglo
        itemsVenta.push({
            producto: nombreProducto,
            cantidad: cantidadVal,
            precio_venta: precioVentaVal,
            precio_bea: precioBeaVal
        });

        // Limpiar campos de selección de producto
        document.getElementById('producto_sel').value = '';
        document.getElementById('producto_custom').value = '';
        document.getElementById('seccion_producto_nombre_personalizado').style.display = 'none';
        document.getElementById('cantidad').value = '1';
        document.getElementById('precio_venta').value = '0.00';
        document.getElementById('precio_bea').value = '0.00';

        // Renderizar lista y actualizar cálculos
        renderListaProductos();
        calcularGranTotalForm();
    }

    // Eliminar producto de la lista intermedia
    function eliminarProductoDeLaLista(index) {
        itemsVenta.splice(index, 1);
        renderListaProductos();
        calcularGranTotalForm();
    }

    // Renderizar la lista de productos en el DOM y generar inputs ocultos
    function renderListaProductos() {
        const tbody = document.getElementById('tbody_productos_lista');
        const inputsContainer = document.getElementById('inputs_productos_ocultos');

        tbody.innerHTML = '';
        inputsContainer.innerHTML = '';

        if (itemsVenta.length === 0) {
            tbody.innerHTML = `
                <tr id="tr_lista_vacia">
                    <td colspan="6" class="text-center py-3 text-white-50">Aún no has agregado ningún producto.</td>
                </tr>
            `;
            return;
        }

        itemsVenta.forEach((item, index) => {
            const total = item.cantidad * item.precio_venta;
            
            // Fila de la tabla
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="ps-2 fw-semibold text-white">${escapeHtml(item.producto)}</td>
                <td class="text-center fw-bold text-white-50">${item.cantidad}</td>
                <td class="text-end text-white">$${item.precio_venta.toFixed(2)}</td>
                <td class="text-end text-success">$${item.precio_bea.toFixed(2)}</td>
                <td class="text-end fw-bold text-white">$${total.toFixed(2)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm border-0 p-1" onclick="eliminarProductoDeLaLista(${index})">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);

            // Inputs ocultos para el envío del formulario
            inputsContainer.innerHTML += `
                <input type="hidden" name="productos[]" value="${escapeHtml(item.producto)}">
                <input type="hidden" name="cantidades[]" value="${item.cantidad}">
                <input type="hidden" name="precios_venta[]" value="${item.precio_venta}">
                <input type="hidden" name="precios_bea[]" value="${item.precio_bea}">
            `;
        });
    }

    // Calcular montos acumulados y ganancias totales en el formulario
    function calcularGranTotalForm() {
        let totalVenta = 0;
        let totalBea = 0;

        itemsVenta.forEach(item => {
            totalVenta += item.cantidad * item.precio_venta;
            totalBea += item.cantidad * item.precio_bea;
        });

        const totalGanancia = totalVenta - totalBea;

        document.getElementById('lbl_venta_total').innerText = '$' + totalVenta.toFixed(2);
        document.getElementById('lbl_bea_total').innerText = '$' + totalBea.toFixed(2);
        document.getElementById('lbl_ganancia_total').innerText = '$' + totalGanancia.toFixed(2);
    }

    // Helper para escapar HTML y evitar inyecciones XSS en el cliente
    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Validar formulario antes de enviar
    function validarVentaForm() {
        if (itemsVenta.length === 0) {
            alert('Debes agregar al menos un producto a la lista de venta antes de registrar.');
            return false;
        }

        const metodo = document.querySelector('input[name="metodo_pago"]:checked').value;
        const idCliente = parseInt(document.getElementById('id_cliente').value) || 0;

        if (metodo === 'cuenta' && idCliente <= 0) {
            alert('Debes seleccionar un cliente registrado del listado para poder guardar a cuenta.');
            // Destacar el campo de búsqueda
            const searchInput = document.getElementById('venta_cliente_search_input');
            if (searchInput) {
                searchInput.focus();
                searchInput.classList.add('is-invalid');
                setTimeout(() => searchInput.classList.remove('is-invalid'), 3000);
            }
            return false;
        }

        return true;
    }

    // Actualizar monto a liquidar en el modal a partir del KPI en tiempo real
    function actualizarMontoLiquidar() {
        const lblVal = document.getElementById('lbl-por-entregar-bea');
        const modalVal = document.getElementById('modal-liquidar-monto');
        if (lblVal && modalVal) {
            // Extract numerical value or copy text exactly
            modalVal.textContent = lblVal.textContent;
        }
    }

    // Configurar modal de eliminación
    function confirmarEliminarVenta(id, tieneCuentaCliente) {
        const form = document.getElementById('formConfirmarEliminarVenta');
        form.action = '<?= base_url("admin/semillas/eliminar") ?>/' + id;
        form.setAttribute('hx-post', '<?= base_url("admin/semillas/eliminar") ?>/' + id);
        if (typeof htmx !== 'undefined') {
            htmx.process(form);
        }

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

    if (searchInputVentas) {
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
    }

    if (btnClearSearchVentas) {
        // Acción para limpiar la búsqueda
        btnClearSearchVentas.addEventListener('click', function() {
            searchInputVentas.value = '';
            btnClearSearchVentas.style.display = 'none';
            
            // Simular evento input para restaurar todos los registros
            searchInputVentas.dispatchEvent(new Event('input'));
            searchInputVentas.focus();
        });
    }

    // Funciones para mostrar toasts flotantes dinámicamente
    function mostrarToastExito(message) {
        const container = document.querySelector(".toast-container");
        if (!container) return;

        const toastHtml = `
            <div class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3 show"
                role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                       <i class="bi bi-check-circle-fill text-success fs-5"></i>
                       <div>${message}</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        `;

        const tempDiv = document.createElement("div");
        tempDiv.innerHTML = toastHtml.trim();
        const toastEl = tempDiv.firstChild;
        container.appendChild(toastEl);

        const toastInstance = new bootstrap.Toast(toastEl);
        toastInstance.show();

        toastEl.addEventListener("hidden.bs.toast", () => {
            toastEl.remove();
        });
    }

    function mostrarToastError(message) {
        const container = document.querySelector(".toast-container");
        if (!container) return;

        const toastHtml = `
            <div class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3 show"
                role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                       <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                       <div>${message}</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        `;

        const tempDiv = document.createElement("div");
        tempDiv.innerHTML = toastHtml.trim();
        const toastEl = tempDiv.firstChild;
        container.appendChild(toastEl);

        const toastInstance = new bootstrap.Toast(toastEl);
        toastInstance.show();

        toastEl.addEventListener("hidden.bs.toast", () => {
            toastEl.remove();
        });
    }

    // Inicializar cálculos al cargar la página
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            calcularGranTotalForm();
        });
    } else {
        calcularGranTotalForm();
    }

    // Resetear lista al abrir el modal
    const modalNuevaVentaEl = document.getElementById('modalNuevaVenta');
    if (modalNuevaVentaEl) {
        modalNuevaVentaEl.addEventListener('show.bs.modal', function () {
            itemsVenta = [];
            renderListaProductos();
            
            // Restablecer campos del modal
            document.getElementById('cli_general').checked = true;
            toggleClienteInput('general');
            document.getElementById('id_cliente').value = '0';
            document.getElementById('nombre_cliente_libre').value = '';
            document.getElementById('producto_sel').value = '';
            document.getElementById('producto_custom').value = '';
            document.getElementById('seccion_producto_nombre_personalizado').style.display = 'none';
            document.getElementById('cantidad').value = '1';
            document.getElementById('precio_venta').value = '0.00';
            document.getElementById('precio_bea').value = '0.00';
            document.getElementById('pago_contado').checked = true;
            calcularGranTotalForm();
        });
    }

    // Manejar el cierre de modales y feedback al completar peticiones HTMX
    document.addEventListener("htmx:afterOnLoad", function (evt) {
        const targetId = evt.detail.elt.id;

        if (evt.detail.xhr.status === 200) {
            let modalId = null;

            if (targetId === "formNuevaVenta") {
                modalId = "modalNuevaVenta";
            } else if (targetId === "formConfirmarEliminarVenta") {
                modalId = "modalConfirmarEliminarVenta";
            } else if (targetId === "formLiquidarBea") {
                modalId = "modalLiquidarBea";
            }

            if (modalId) {
                const modalEl = document.getElementById(modalId);
                if (modalEl) {
                     const modalInstance = bootstrap.Modal.getInstance(modalEl);
                     if (modalInstance) {
                         modalInstance.hide();
                     }
                }
            }
        }
    });

    // Exportar funciones globales al objeto window para acceso desde atributos HTML
    window.toggleClienteInput = toggleClienteInput;
    window.toggleMetodoPago = toggleMetodoPago;
    window.seleccionarProductoPredefinido = seleccionarProductoPredefinido;
    window.calcularGranTotalForm = calcularGranTotalForm;
    window.validarVentaForm = validarVentaForm;
    window.confirmarEliminarVenta = confirmarEliminarVenta;
    window.mostrarToastExito = mostrarToastExito;
    window.mostrarToastError = mostrarToastError;
    window.agregarProductoALaLista = agregarProductoALaLista;
    window.eliminarProductoDeLaLista = eliminarProductoDeLaLista;
    window.actualizarMontoLiquidar = actualizarMontoLiquidar;
})();
</script>
<?= $this->endSection() ?>
