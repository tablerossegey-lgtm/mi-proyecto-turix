<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link class="styles-admin-theme" rel="stylesheet" href="<?= base_url('css/admin.css?v=1.2') ?>">

<div class="container py-4">
    <!-- Encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 admin-title">Compras a Proveedores</h2>
            <p class="text-muted mb-0">Registra y administra las facturas de proveedores, prorratea costos y actualiza
                el stock del inventario.</p>
            <div class="admin-subtitle-line" style="background-color: #ffc107;"></div>
        </div>
        <div>
            <button type="button"
                class="btn btn-warning rounded-pill px-4 fw-bold d-inline-flex align-items-center gap-2 text-dark"
                data-bs-toggle="modal" data-bs-target="#modalNuevaCompra">
                <i class="fas fa-cart-plus"></i> Registrar Compra
            </button>
        </div>
    </div>

    <!-- Notificaciones flotantes tipo Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
        <?php if (session()->getFlashdata('success')): ?>
            <div id="toast-success" class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3"
                role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <div>
                            <strong>¡Exitoso!</strong><br>
                            <span class="small text-white-50"><?= esc(session()->getFlashdata('success')) ?></span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div id="toast-error" class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3"
                role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-circle-fill text-danger fs-5"></i>
                        <div>
                            <strong>¡Error!</strong><br>
                            <span class="small text-white-50"><?= esc(session()->getFlashdata('error')) ?></span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Historial de Compras -->
    <div class="card border-0 shadow-sm admin-card">
        <div class="card-header border-bottom border-secondary border-opacity-25 py-3 bg-transparent">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <!-- Título + badge -->
                <div class="d-flex align-items-center gap-3">
                    <h6 class="fw-bold mb-0 text-white"><i class="fas fa-file-invoice-dollar me-2 text-warning"></i>
                        Historial de Facturas de Proveedores</h6>
                    <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-2">
                        <?= number_format($totalRegistros) ?> registro<?= $totalRegistros !== 1 ? 's' : '' ?>
                    </span>
                </div>

                <!-- Buscador live search por proveedor -->
                <form method="GET" action="<?= base_url('admin/compras') ?>" id="form-filtro-proveedor"
                    class="d-flex align-items-center gap-2" hx-boost="true">
                    <div class="input-group input-group-sm" style="min-width: 240px; max-width: 340px;">
                        <span class="input-group-text border-0"
                            style="background: rgba(255,255,255,0.07); color: #ffc107;">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="busqueda" id="live-search-proveedor"
                            class="form-control form-control-sm border-0 fw-semibold"
                            style="background: rgba(255,255,255,0.07); color: #fff;" placeholder="Buscar proveedor..."
                            value="<?= esc($busqueda) ?>" autocomplete="off">
                        <?php if ($busqueda !== ''): ?>
                            <a href="<?= base_url('admin/compras') ?>" hx-boost="true"
                                class="btn btn-sm border-0 d-flex align-items-center px-2" title="Limpiar búsqueda"
                                style="background: rgba(220,53,69,0.25); color: #ff6b6b;">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead class="text-muted admin-table-thead">
                    <tr>
                        <th class="ps-4 py-3 admin-table-th" style="width: 100px;">Folio ID</th>
                        <th class="py-3 admin-table-th" style="width: 120px;">Fecha</th>
                        <th class="py-3 admin-table-th">Proveedor</th>
                        <th class="py-3 admin-table-th">Descripción</th>
                        <th class="py-3 text-center admin-table-th" style="width: 120px;">Factor</th>
                        <th class="py-3 text-end admin-table-th" style="width: 150px;">Total</th>
                        <th class="py-3 text-center pe-4 admin-table-th" style="width: 150px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($compras)): ?>
                        <?php foreach ($compras as $c): ?>
                            <tr class="admin-table-tr custom-row-glow">
                                <td class="ps-4 py-3 text-warning fw-bold">
                                    #<?= $c['idCompraProveedor'] ?>
                                </td>
                                <td class="py-3 text-white-50 small">
                                    <?= date('d/m/Y', strtotime($c['fechaCompra'])) ?>
                                </td>
                                <td class="py-3 text-white fw-semibold">
                                    <?= esc($c['nombre_proveedor'] ?: 'Proveedor no identificado') ?>
                                </td>
                                <td class="py-3 text-white-50 text-truncate" style="max-width: 250px;"
                                    title="<?= esc($c['descripcion']) ?>">
                                    <?= esc($c['descripcion'] ?: '-') ?>
                                </td>
                                <td class="py-3 text-center text-info fw-bold cost-badge">
                                    x<?= number_format($c['factor_pedido'], 4) ?>
                                </td>
                                <td class="py-3 text-end fw-bold text-success cost-badge">
                                    $<?= number_format($c['total_pagado'], 2) ?>
                                </td>
                                <td class="py-3 text-center pe-4">
                                    <div class="d-inline-flex gap-2">
                                        <!-- Ver Detalle (vía HTMX) -->
                                        <button type="button"
                                            class="btn btn-warning btn-sm d-flex align-items-center justify-content-center rounded-3 p-2"
                                            title="Ver detalle de la compra"
                                            hx-get="<?= base_url('admin/compras/detalle/' . $c['idCompraProveedor']) ?>"
                                            hx-target="#contenido-modal" hx-indicator="#loading-indicator"
                                            data-bs-toggle="modal" data-bs-target="#modalDetalle">
                                            <i class="fas fa-eye text-dark"></i>
                                        </button>
                                        <!-- Eliminar Compra -->
                                        <button type="button"
                                            class="btn btn-danger btn-sm d-flex align-items-center justify-content-center rounded-3 p-2"
                                            title="Eliminar compra e importes"
                                            onclick="confirmarEliminarCompra('<?= base_url('admin/compras/eliminar/' . $c['idCompraProveedor']) ?>')">
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
                                    <i class="fas fa-file-invoice-dollar fs-1 text-muted opacity-35"></i>
                                </div>
                                <h6 class="fw-bold text-white-50">Sin facturas registradas</h6>
                                <p class="mb-0 small">Registra tus compras a proveedores para actualizar stock y egresos de
                                    Caja Chica.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPaginas > 1): ?>
            <!-- Paginación -->
            <?php
            // Query string base que preserva la búsqueda activa en cada link de página
            $qs = $busqueda !== '' ? '&busqueda=' . urlencode($busqueda) : '';
            ?>
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 px-4 py-3"
                style="border-top: 1px solid rgba(255,255,255,0.07);">
                <!-- Info de registros -->
                <small class="text-white-50">
                    Mostrando
                    <strong class="text-white"><?= number_format(($paginaActual - 1) * $porPagina + 1) ?></strong>
                    –
                    <strong
                        class="text-white"><?= number_format(min($paginaActual * $porPagina, $totalRegistros)) ?></strong>
                    de <strong class="text-white"><?= number_format($totalRegistros) ?></strong> registros
                </small>

                <!-- Botones de página -->
                <nav aria-label="Paginación de compras" hx-boost="true">
                    <ul class="pagination pagination-sm mb-0 gap-1">

                        <!-- Primera página -->
                        <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link rounded-3 border-0 fw-bold"
                                href="<?= base_url('admin/compras') ?>?page=1<?= $qs ?>"
                                style="background: rgba(255,255,255,0.07); color: #fff;" title="Primera página"
                                aria-label="Primera página">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>

                        <!-- Anterior -->
                        <li class="page-item <?= $paginaActual <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link rounded-3 border-0 fw-bold"
                                href="<?= base_url('admin/compras') ?>?page=<?= $paginaActual - 1 ?><?= $qs ?>"
                                style="background: rgba(255,255,255,0.07); color: #fff;" aria-label="Anterior">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>

                        <?php
                        // Mostrar ventana de páginas centrada en la actual
                        $ventana = 2;
                        $inicio = max(1, $paginaActual - $ventana);
                        $fin = min($totalPaginas, $paginaActual + $ventana);
                        ?>

                        <?php if ($inicio > 1): ?>
                            <li class="page-item">
                                <a class="page-link rounded-3 border-0" href="<?= base_url('admin/compras') ?>?page=1<?= $qs ?>"
                                    style="background: rgba(255,255,255,0.07); color: #aaa;">1</a>
                            </li>
                            <?php if ($inicio > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link border-0" style="background: transparent; color:#555;">…</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($p = $inicio; $p <= $fin; $p++): ?>
                            <li class="page-item <?= $p === $paginaActual ? 'active' : '' ?>">
                                <a class="page-link rounded-3 border-0 fw-bold"
                                    href="<?= base_url('admin/compras') ?>?page=<?= $p ?><?= $qs ?>" style="<?= $p === $paginaActual
                                              ? 'background:#ffc107; color:#000;'
                                              : 'background:rgba(255,255,255,0.07); color:#fff;' ?>">
                                    <?= $p ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($fin < $totalPaginas): ?>
                            <?php if ($fin < $totalPaginas - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link border-0" style="background: transparent; color:#555;">…</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link rounded-3 border-0"
                                    href="<?= base_url('admin/compras') ?>?page=<?= $totalPaginas ?><?= $qs ?>"
                                    style="background: rgba(255,255,255,0.07); color: #aaa;"><?= $totalPaginas ?></a>
                            </li>
                        <?php endif; ?>

                        <!-- Siguiente -->
                        <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
                            <a class="page-link rounded-3 border-0 fw-bold"
                                href="<?= base_url('admin/compras') ?>?page=<?= $paginaActual + 1 ?><?= $qs ?>"
                                style="background: rgba(255,255,255,0.07); color: #fff;" aria-label="Siguiente">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>

                        <!-- Última página -->
                        <li class="page-item <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>">
                            <a class="page-link rounded-3 border-0 fw-bold"
                                href="<?= base_url('admin/compras') ?>?page=<?= $totalPaginas ?><?= $qs ?>"
                                style="background: rgba(255,255,255,0.07); color: #fff;" title="Última página"
                                aria-label="Última página">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>

                    </ul>
                </nav>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- INDICADOR DE CARGA HTMX -->
<div id="loading-indicator"
    class="htmx-indicator position-fixed top-50 start-50 translate-middle p-3 bg-dark bg-opacity-75 rounded-3"
    style="z-index: 1200;">
    <div class="spinner-border text-warning" role="status"></div>
</div>

<!-- MODAL: DETALLES DE COMPRA (HTMX TARGET) -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" id="contenido-modal"
            style="background-color: #121824; color: #ffffff; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1);">
            <div class="p-5 text-center text-white">
                <div class="spinner-border text-warning" role="status"></div>
                <p class="mt-2 mb-0">Cargando desglose de compra...</p>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: CONFIRMAR ELIMINACIÓN DE COMPRA -->
<div class="modal fade" id="modalConfirmarEliminarCompra" tabindex="-1" aria-labelledby="modalConfElimCompraLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white"
            style="border-radius: 20px; background-color: #121824; border: 1px solid rgba(255,255,255,0.15);">
            <div class="modal-header modal-encargo-header py-3 px-4"
                style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2"
                    id="modalConfElimCompraLabel">
                    <i class="fas fa-exclamation-triangle text-danger"></i> ¿Eliminar registro de compra?
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="text-white-50" style="font-size: 0.95rem;">Esta acción realizará las siguientes operaciones:
                </p>
                <ul class="text-start text-white-50 small mx-auto d-table mb-0">
                    <li>Se <strong>restará el stock</strong> sumado a los productos del inventario.</li>
                    <li>Se <strong>eliminará el egreso</strong> correspondiente de la Caja Chica.</li>
                    <li>Se eliminará permanentemente la factura y su detalle.</li>
                </ul>
                <p class="mt-3 mb-0 text-warning fw-bold small"><i class="fas fa-exclamation-circle me-1"></i> Asegúrese
                    de que el stock actual cubra la cantidad a restar para evitar stock en negativo.</p>
            </div>
            <div class="modal-footer d-flex gap-2 justify-content-center pb-4" style="border-top: none;">
                <button type="button" class="btn btn-outline-light rounded-pill px-4"
                    data-bs-dismiss="modal">Cancelar</button>
                <form id="formConfirmarEliminarCompra" action="" method="POST" class="d-inline" hx-boost="true">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Confirmar y Revertir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: REGISTRAR NUEVA COMPRA -->
<div class="modal fade" id="modalNuevaCompra" tabindex="-1" aria-labelledby="modalNuevaCompraLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 95% !important; width: 1450px !important;">
        <form id="formNuevaCompra" action="<?= base_url('admin/compras/crear') ?>" method="POST" class="w-100"
            hx-boost="true">
            <?= csrf_field() ?>
            <div class="modal-content modal-encargo-content text-white"
                style="background-color: #121824; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1);">
                <div class="modal-header modal-encargo-header py-3 px-4"
                    style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2"
                        id="modalNuevaCompraLabel">
                        <i class="fas fa-cart-plus text-warning"></i> Registrar Compra a Proveedor
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Datos Generales de la Compra -->
                    <h6 class="text-warning fw-bold mb-3"><i class="fas fa-file-invoice"></i> 1. Información General de
                        Factura</h6>
                    <div class="row g-3 mb-4 p-3 rounded-4 compra-details-box">
                        <!-- Proveedor (Live Search / Autocomplete) -->
                        <div class="col-12 col-md-4">
                            <label for="proveedor-search-input" class="form-label modal-encargo-label">Proveedor
                                *</label>
                            <!-- Campo oculto que envía el ID real al servidor -->
                            <input type="hidden" name="idProveedor" id="idProveedor" value="" required>
                            <div class="proveedor-autocomplete">
                                <input type="text" id="proveedor-search-input"
                                    class="form-control modal-encargo-control text-white"
                                    placeholder="Escribe para buscar proveedor..." autocomplete="off"
                                    spellcheck="false">
                                <div class="proveedor-autocomplete-list" id="proveedor-autocomplete-list"></div>
                            </div>
                        </div>

                        <!-- Fecha Compra -->
                        <div class="col-12 col-md-4">
                            <label for="fechaCompra" class="form-label modal-encargo-label">Fecha de Compra *</label>
                            <input type="date" class="form-control modal-encargo-control text-white" id="fechaCompra"
                                name="fechaCompra" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <!-- Descripcion / Concepto -->
                        <div class="col-12 col-md-4">
                            <label for="descripcion" class="form-label modal-encargo-label">Descripción General /
                                Comentario</label>
                            <input type="text" class="form-control modal-encargo-control text-white" id="descripcion"
                                name="descripcion" placeholder="Ej: Compra de navidad, Lote de plumas, etc.">
                        </div>

                        <!-- Costos Adicionales -->
                        <div class="col-12 col-md-6">
                            <label for="envio_local_estimado" class="form-label modal-encargo-label">Envío Local o Envío
                                Estimado ($)</label>
                            <input type="number" step="0.01" class="form-control modal-encargo-control text-white"
                                id="envio_local_estimado" name="envio_local_estimado" min="0" value="0.00"
                                onkeyup="recalcularMontos()" onchange="recalcularMontos()">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="impuesto_importacion" class="form-label modal-encargo-label">Impuesto o Tarifa
                                de Importación ($)</label>
                            <input type="number" step="0.01" class="form-control modal-encargo-control text-white"
                                id="impuesto_importacion" name="impuesto_importacion" min="0" value="0.00"
                                onkeyup="recalcularMontos()" onchange="recalcularMontos()">
                        </div>
                    </div>

                    <!-- Detalle de Productos -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-warning fw-bold mb-0"><i class="fas fa-boxes"></i> 2. Artículos Comprados</h6>
                        <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-4 fw-bold"
                            onclick="agregarFila()">
                            <i class="fas fa-plus"></i> Agregar Artículo
                        </button>
                    </div>

                    <!-- Contenedor de filas dinámicas (Diseño de Tabla Compacta) -->
                    <div class="table-responsive border border-secondary border-opacity-25 rounded-4 p-0 mb-3"
                        style="background: rgba(255,255,255,0.01);">
                        <table class="table table-hover align-middle mb-0 text-white" id="tabla-productos-compra"
                            style="min-width: 1350px;">
                            <thead class="text-white-50 small"
                                style="background: rgba(255, 255, 255, 0.03); border-bottom: 1px solid rgba(255,255,255,0.08);">
                                <tr>
                                    <th class="text-center" style="width: 40px;">#</th>
                                    <th style="width: 260px;">Seleccionar del Inventario</th>
                                    <th style="width: 160px;">SKU</th>
                                    <th style="width: 220px;">Nombre Artículo</th>
                                    <th class="text-center" style="width: 75px;">Cant.</th>
                                    <th class="text-end" style="width: 105px;">Costo Prov.</th>
                                    <th class="text-center" style="width: 75px;">Margen %</th>
                                    <th class="text-end" style="width: 105px;">Sugerido</th>
                                    <th class="text-end" style="width: 105px;">Venta Final</th>
                                    <th class="text-center" style="width: 170px;">Costo Prorrateado</th>
                                    <th class="text-start" style="width: 180px;">Acciones Inv.</th>
                                    <th class="text-center" style="width: 50px;">Quitar</th>
                                </tr>
                            </thead>
                            <tbody id="productos-container">
                                <!-- Las filas de productos (tr.row-producto) se inyectarán aquí con JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Resumen del Pedido en el pie del modal -->
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 p-3 rounded-4 mt-4"
                        style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05);">
                        <div class="text-white-50 small">
                            <div>Total Base de Productos: <strong class="text-white">$<span
                                        id="total-productos-label">0.00</span></strong></div>
                            <div>Envío + Impuesto: <strong class="text-white">$<span
                                        id="total-adicionales-label">0.00</span></strong></div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold text-white-50 text-uppercase small">Importe total pagado:</span>
                            <span class="fs-3 fw-bold text-success cost-badge" id="total-pagado-label">$0.00</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer modal-encargo-footer d-flex gap-2"
                    style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark hover-shadow"
                        id="btn-submit-compra">
                        <i class="fas fa-save me-1"></i> Registrar Compra
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Exponer inventario como array JS
    (function () {
        document.body.classList.add('admin-body');

        // Exponer inventario como array JS
        const inventario = <?= json_encode($inventario) ?>;
        let filaIndex = 0;

        // Inicializar toasts de notificaciones (con soporte para HTMX y auto-limpieza)
        function inicializarToasts() {
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
        }

        function initComprasProveedores() {
            inicializarToasts();

            // ── Gestión del modal de nueva compra ────────────────────────────────
            const modalNuevaCompra = document.getElementById('modalNuevaCompra');
            if (modalNuevaCompra) {

                // Al ABRIR: limpiar y agregar la primera fila fresca
                modalNuevaCompra.addEventListener('show.bs.modal', function () {
                    // Limpiar contenedor de artículos
                    const container = document.getElementById('productos-container');
                    if (container) container.innerHTML = '';

                    // Reiniciar índice de filas
                    filaIndex = 0;

                    // Agregar primera fila vacía
                    agregarFila();

                    // Reiniciar resumen de montos
                    const els = ['total-productos-label', 'total-adicionales-label'];
                    els.forEach(id => { const el = document.getElementById(id); if (el) el.innerText = '0.00'; });
                    const totalPagado = document.getElementById('total-pagado-label');
                    if (totalPagado) totalPagado.innerText = '$0.00';

                    // Reiniciar campos generales del formulario
                    const form = document.getElementById('formNuevaCompra');
                    if (form) {
                        form.querySelectorAll('input:not([type=hidden]):not([type=checkbox])').forEach(inp => {
                            if (inp.name === 'fechaCompra') {
                                // Mantener la fecha de hoy
                            } else if (['envio_local_estimado', 'impuesto_importacion'].includes(inp.name)) {
                                inp.value = '0.00';
                            } else if (!inp.closest('.row-producto')) {
                                inp.value = '';
                            }
                        });
                        // Rehabilitar botón de envío por si quedó deshabilitado
                        const btn = document.getElementById('btn-submit-compra');
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-save me-1"></i> Registrar Compra';
                        }
                    }
                });

                // Al CERRAR: nada extra (el show.bs.modal ya limpia al reabrir)
            }

            // Evitar doble envío
            const formNuevaCompra = document.getElementById('formNuevaCompra');
            if (formNuevaCompra) {
                formNuevaCompra.addEventListener('submit', function (e) {
                    const btnSubmit = document.getElementById('btn-submit-compra');
                    const rows = document.querySelectorAll('.row-producto');

                    if (rows.length === 0) {
                        e.preventDefault();
                        alert('Debe agregar al menos un artículo a la compra.');
                        return;
                    }

                    if (btnSubmit) {
                        btnSubmit.disabled = true;
                        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Procesando...';
                    }
                });
            }

            // Mostrar spinner al confirmar eliminación
            const formConfirmarEliminar = document.getElementById('formConfirmarEliminarCompra');
            if (formConfirmarEliminar) {
                formConfirmarEliminar.addEventListener('submit', function () {
                    const btnSubmit = formConfirmarEliminar.querySelector('button[type="submit"]');
                    if (btnSubmit) {
                        btnSubmit.disabled = true;
                        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Revirtiendo...';
                    }
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener("DOMContentLoaded", initComprasProveedores);
        } else {
            initComprasProveedores();
        }

        // Escuchar el evento de HTMX tras un swap de contenido para mostrar toasts en peticiones dinámicas
        document.body.addEventListener('htmx:afterSettle', inicializarToasts);

        // Escuchar antes de que ocurra el swap de HTMX para ocultar los modales y limpiar el body
        document.body.addEventListener('htmx:beforeSwap', function (evt) {
            if (evt.detail.elt.id === 'formNuevaCompra' || evt.detail.elt.id === 'formConfirmarEliminarCompra') {
                const modalId = evt.detail.elt.id === 'formNuevaCompra' ? 'modalNuevaCompra' : 'modalConfirmarEliminarCompra';
                const modalEl = document.getElementById(modalId);
                if (modalEl && typeof bootstrap !== 'undefined') {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.hide();
                }

                // Forzar limpieza inmediata del body y backdrops para evitar scroll bloqueado
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(el => el.remove());
            }
        });

        // ───────────────────────────────────────────────────────────────────────────
        // Autocomplete de producto (filas dinámicas)
        // ───────────────────────────────────────────────────────────────────────────
        // inventario ya está disponible como constante JS al inicio del script

        /**
         * Inicializa el autocomplete de producto en la fila recién creada.
         * Llamado desde agregarFila() después de insertar el HTML.
         */
        function initProductoAutocomplete(index) {
            const row = document.getElementById(`row_${index}`);
            if (!row) return;

            const searchInput = row.querySelector('.prod-search-input');
            const hiddenInput = row.querySelector('.hidden-id-producto');
            const dropdownList = row.querySelector('.prod-autocomplete-list');
            const inputSku = row.querySelector('.input-sku');
            const inputNombre = row.querySelector('.input-nombre');
            const divAct = document.getElementById(`div_act_${index}`);
            const divNew = document.getElementById(`div_new_${index}`);
            const checkNew = document.getElementById(`check_new_${index}`);

            let paActiveIndex = -1;
            let paFiltered = [];

            function phHighlight(text, q) {
                if (!q) return text;
                const i = text.toLowerCase().indexOf(q.toLowerCase());
                if (i === -1) return text;
                return text.slice(0, i)
                    + `<span class="pm-highlight">${text.slice(i, i + q.length)}</span>`
                    + text.slice(i + q.length);
            }

            function renderProdDropdown(query) {
                const q = query.trim().toLowerCase();
                paFiltered = inventario.filter(p =>
                    q === '' ||
                    p.descripcion.toLowerCase().includes(q) ||
                    p.codigo_sku.toLowerCase().includes(q)
                );

                dropdownList.innerHTML = '';
                paActiveIndex = -1;

                // Opción "Producto Libre" siempre en primer lugar
                const libreDiv = document.createElement('div');
                libreDiv.className = 'prod-autocomplete-item libre-item';
                libreDiv.innerHTML = '<i class="fas fa-box-open me-2"></i>Producto Libre (No en inventario)';
                libreDiv.addEventListener('mousedown', (e) => { e.preventDefault(); selectProductoLibre(); });
                dropdownList.appendChild(libreDiv);

                if (paFiltered.length === 0 && q !== '') {
                    const empty = document.createElement('div');
                    empty.className = 'prod-autocomplete-empty';
                    empty.innerHTML = '<i class="fas fa-search me-1"></i>Sin coincidencias en inventario';
                    dropdownList.appendChild(empty);
                } else {
                    paFiltered.forEach((prod, i) => {
                        const div = document.createElement('div');
                        div.className = 'prod-autocomplete-item';
                        div.innerHTML = `${phHighlight(prod.descripcion, q)}<span class="sku-tag">(${phHighlight(prod.codigo_sku, q)})</span>`;
                        div.title = `${prod.descripcion} (${prod.codigo_sku})`;
                        div.addEventListener('mousedown', (e) => { e.preventDefault(); selectProducto(prod); });
                        dropdownList.appendChild(div);
                    });
                }

                dropdownList.classList.add('open');
            }

            function selectProducto(prod) {
                hiddenInput.value = prod.id;
                searchInput.value = `${prod.descripcion} (${prod.codigo_sku})`;
                inputSku.value = prod.codigo_sku;
                inputSku.setAttribute('readonly', 'true');
                inputNombre.value = prod.descripcion;
                inputNombre.setAttribute('readonly', 'true');
                divNew.style.display = 'none';
                checkNew.checked = false;
                divAct.style.display = 'block';
                const precio = parseFloat(prod.precio) || 0;
                row.querySelector('.input-venta-final').value = precio.toFixed(2);
                dropdownList.classList.remove('open');
                recalcularMontos();
            }

            function selectProductoLibre() {
                hiddenInput.value = '';
                searchInput.value = '';
                inputSku.value = '';
                inputSku.removeAttribute('readonly');
                inputNombre.value = '';
                inputNombre.removeAttribute('readonly');
                divNew.style.display = 'block';
                checkNew.checked = false;
                divAct.style.display = 'none';
                dropdownList.classList.remove('open');
                searchInput.setAttribute('placeholder', 'Producto Libre — escribe o busca...');
                inputSku.focus();
                recalcularMontos();
            }

            function setPaActive(newIdx) {
                const items = dropdownList.querySelectorAll('.prod-autocomplete-item');
                items.forEach((el, i) => el.classList.toggle('pa-active', i === newIdx));
                if (newIdx >= 0 && items[newIdx]) items[newIdx].scrollIntoView({ block: 'nearest' });
                paActiveIndex = newIdx;
            }

            function closeDrop() { dropdownList.classList.remove('open'); paActiveIndex = -1; }

            searchInput.addEventListener('input', function () {
                hiddenInput.value = '';
                renderProdDropdown(this.value);
            });

            searchInput.addEventListener('focus', function () {
                renderProdDropdown(this.value);
            });

            searchInput.addEventListener('keydown', function (e) {
                const items = dropdownList.querySelectorAll('.prod-autocomplete-item');
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    setPaActive(Math.min(paActiveIndex + 1, items.length - 1));
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    setPaActive(Math.max(paActiveIndex - 1, 0));
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (paActiveIndex === 0) { selectProductoLibre(); return; }
                    const realIdx = paActiveIndex - 1; // offset por el item Libre
                    if (realIdx >= 0 && paFiltered[realIdx]) selectProducto(paFiltered[realIdx]);
                } else if (e.key === 'Escape') {
                    closeDrop();
                }
            });

            document.addEventListener('click', function (e) {
                if (!row.contains(e.target)) closeDrop();
            });
        }

        // Agregar nueva fila de producto
        window.agregarFila = function () {
            const container = document.getElementById('productos-container');
            const index = filaIndex++;

            const filaHtml = `
        <tr class="row-producto" id="row_${index}" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
            <!-- # Índice -->
            <td class="text-center fw-bold text-white-50">
                <span class="row-index"></span>
            </td>

            <!-- Seleccionar Inventario -->
            <td>
                <input type="hidden" class="hidden-id-producto" name="productos[${index}][id_producto]" value="">
                <div class="prod-autocomplete">
                    <input type="text"
                           class="form-control table-input-compact prod-search-input text-white"
                           placeholder="Busca por nombre o SKU..."
                           autocomplete="off" spellcheck="false">
                    <div class="prod-autocomplete-list"></div>
                </div>
            </td>

            <!-- SKU -->
            <td>
                <input type="text" class="form-control table-input-compact text-white input-sku" name="productos[${index}][sku]" placeholder="Ej: TS-PROD-01" required>
            </td>

            <!-- Nombre -->
            <td>
                <input type="text" class="form-control table-input-compact text-white input-nombre" name="productos[${index}][nombre]" placeholder="Nombre del artículo" required>
            </td>

            <!-- Cantidad -->
            <td>
                <input type="number" class="form-control table-input-compact text-white text-center input-cantidad" name="productos[${index}][cantidad]" min="1" value="1" onkeyup="recalcularMontos()" onchange="recalcularMontos()" required>
            </td>

            <!-- Costo Proveedor -->
            <td>
                <input type="number" step="0.01" class="form-control table-input-compact text-white text-end input-precio-proveedor" name="productos[${index}][precio_proveedor]" min="0" value="0.00" onkeyup="recalcularMontos()" onchange="recalcularMontos()" required>
            </td>

            <!-- Margen -->
            <td>
                <input type="number" step="0.1" class="form-control table-input-compact text-white text-center input-margen" name="productos[${index}][margen]" min="0" value="30" onkeyup="recalcularMontos()" onchange="recalcularMontos()">
            </td>

            <!-- Venta Sugerido -->
            <td>
                <input type="text" class="form-control table-input-compact text-white-50 text-end input-venta-sugerido" readonly value="$0.00">
            </td>

            <!-- Venta Final -->
            <td>
                <input type="number" step="0.01" class="form-control table-input-compact text-white text-end input-venta-final" name="productos[${index}][precio_venta_final]" min="0" value="0.00">
            </td>

            <!-- Costo Prorrateado -->
            <td class="text-center text-success small">
                <div class="d-flex flex-column gap-0.5 justify-content-center align-items-center">
                    <span style="font-size: 0.72rem; display: block; white-space: nowrap;">U: <strong class="text-success">$<span class="label-costo-real-unit">0.00</span></strong></span>
                    <span style="font-size: 0.72rem; display: block; white-space: nowrap;">T: <strong class="text-success">$<span class="label-costo-real-total">0.00</span></strong></span>
                </div>
            </td>

            <!-- Acciones Inv. -->
            <td>
                <div class="d-flex flex-column gap-1 align-items-start justify-content-center px-1">
                    <div class="form-check checkbox-actualizar-inventario-div m-0" id="div_act_${index}">
                        <input class="form-check-input input-actualizar-inventario" type="checkbox" name="productos[${index}][actualizar_inventario]" value="1" checked id="check_act_${index}">
                        <label class="form-check-label text-success fw-bold" style="font-size: 0.75rem; cursor: pointer; white-space: nowrap;" for="check_act_${index}">Actualizar precio</label>
                    </div>
                    <div class="form-check checkbox-registrar-nuevo-div m-0" id="div_new_${index}" style="display: block;">
                        <input class="form-check-input input-registrar-nuevo" type="checkbox" name="productos[${index}][registrar_nuevo_inventario]" value="1" id="check_new_${index}" onchange="toggleRegistrarNuevo(this, ${index})">
                        <label class="form-check-label text-warning fw-bold" style="font-size: 0.75rem; cursor: pointer; white-space: nowrap;" for="check_new_${index}">Crear nuevo</label>
                    </div>
                </div>
            </td>

            <!-- Quitar -->
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm p-1 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;" onclick="eliminarFila(${index})">
                    <i class="fas fa-trash-alt" style="font-size: 0.75rem;"></i>
                </button>
            </td>
        </tr>
        `;

            container.insertAdjacentHTML('beforeend', filaHtml);
            actualizarNumeracion();
            recalcularMontos();
            // Inicializar autocomplete en la fila recién creada
            initProductoAutocomplete(index);
        };

        // Eliminar fila de producto
        window.eliminarFila = function (id) {
            const row = document.getElementById(`row_${id}`);
            if (row) {
                row.remove();
                actualizarNumeracion();
                recalcularMontos();
            }
        };

        // Actualizar números de filas
        function actualizarNumeracion() {
            const indexes = document.querySelectorAll('.row-index');
            indexes.forEach((el, idx) => {
                el.innerText = idx + 1;
            });
        }

        // (cambiarProducto reemplazado por initProductoAutocomplete / selectProducto)

        // Al checkear "Registrar como nuevo" en producto libre
        window.toggleRegistrarNuevo = function (checkboxEl, idx) {
            const divAct = document.getElementById(`div_act_${idx}`);
            if (checkboxEl.checked) {
                divAct.style.display = "block";
            } else {
                divAct.style.display = "none";
            }
        };

        // Recalcular montos en tiempo real
        window.recalcularMontos = function () {
            const rows = document.querySelectorAll('.row-producto');
            let total_productos = 0;

            // 1. Calcular base
            rows.forEach(row => {
                const qty = parseInt(row.querySelector('.input-cantidad').value) || 0;
                const price = parseFloat(row.querySelector('.input-precio-proveedor').value) || 0;
                total_productos += qty * price;
            });

            const envio = parseFloat(document.getElementById('envio_local_estimado').value) || 0;
            const impuesto = parseFloat(document.getElementById('impuesto_importacion').value) || 0;

            const total_pagado = total_productos + envio + impuesto;
            const total_adicionales = envio + impuesto;

            // Calcular el factor multiplicador de prorrateo
            const factor = total_productos > 0 ? (total_pagado / total_productos) : 1;

            // 2. Aplicar prorrateo y calcular sugeridos por fila
            rows.forEach(row => {
                const qty = parseInt(row.querySelector('.input-cantidad').value) || 0;
                const price = parseFloat(row.querySelector('.input-precio-proveedor').value) || 0;
                const margen = parseFloat(row.querySelector('.input-margen').value) || 0;

                const costo_real_unit = price * factor;
                const costo_real_total = costo_real_unit * qty;
                const venta_sugerido = costo_real_unit * (1 + margen / 100);

                row.querySelector('.label-costo-real-unit').innerText = costo_real_unit.toFixed(2);
                row.querySelector('.label-costo-real-total').innerText = costo_real_total.toFixed(2);
                row.querySelector('.input-venta-sugerido').value = '$' + venta_sugerido.toFixed(2);

                // Si el precio de venta final es 0 o está vacío, o no se ha modificado manualmente,
                // podemos sugerir colocar el precio sugerido
                const inputFinal = row.querySelector('.input-venta-final');
                if (inputFinal.value === "" || parseFloat(inputFinal.value) === 0 || inputFinal.dataset.touched !== "true") {
                    inputFinal.value = venta_sugerido.toFixed(2);
                }
            });

            // 3. Imprimir totales generales
            document.getElementById('total-productos-label').innerText = total_productos.toFixed(2);
            document.getElementById('total-adicionales-label').innerText = total_adicionales.toFixed(2);
            document.getElementById('total-pagado-label').innerText = '$' + total_pagado.toFixed(2);
        };

        // Marcar que el usuario modificó manualmente el precio final
        document.addEventListener('input', function (e) {
            if (e.target && e.target.classList.contains('input-venta-final')) {
                e.target.dataset.touched = "true";
            }
        });

        // Confirmar eliminación de factura
        window.confirmarEliminarCompra = function (url) {
            const form = document.getElementById('formConfirmarEliminarCompra');
            if (form) {
                form.action = url;
                form.setAttribute('action', url);
                if (typeof htmx !== 'undefined') {
                    htmx.process(form);
                }
            }
            const modalEl = document.getElementById('modalConfirmarEliminarCompra');
            if (modalEl && typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            } else {
                if (confirm('¿Estás seguro de que deseas eliminar este registro de compra? Se revertirá el stock de los productos y se removerá el egreso en Caja Chica.')) {
                    if (form) {
                        if (typeof htmx !== 'undefined') {
                            htmx.trigger(form, 'submit');
                        } else {
                            form.submit();
                        }
                    }
                }
            }
        };
        // ─── Live Search: filtrar por proveedor al escribir ───────────────────────
        (function () {
            const searchInput = document.getElementById('live-search-proveedor');
            const searchForm = document.getElementById('form-filtro-proveedor');
            if (!searchInput || !searchForm) return;

            let debounceTimer = null;

            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function () {
                    if (typeof htmx !== 'undefined') {
                        htmx.trigger(searchForm, 'submit');
                    } else {
                        searchForm.submit();
                    }
                }, 450);
            });

            // Limpiar con Escape
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    clearTimeout(debounceTimer);
                    searchInput.value = '';
                    if (typeof htmx !== 'undefined') {
                        htmx.trigger(searchForm, 'submit');
                    } else {
                        searchForm.submit();
                    }
                }
            });

            // Focus automático si hay búsqueda activa (mantener cursor al final)
            if (searchInput.value !== '') {
                const len = searchInput.value.length;
                searchInput.focus();
                searchInput.setSelectionRange(len, len);
            }
        })(); // ─── fin: Live Search barra ────────────────────────────────────────

        // ─── Autocomplete de Proveedor (Modal Registrar Compra) ───────────────────
        (function () {
            // Datos de proveedores desde PHP (ya disponibles en la página)
            const proveedoresList = <?= json_encode(array_map(fn($p) => [
                'id' => $p['idProveedor'],
                'nombre' => $p['nombre']
            ], $proveedores)) ?>;

            const searchInput = document.getElementById('proveedor-search-input');
            const hiddenInput = document.getElementById('idProveedor');
            const dropdownList = document.getElementById('proveedor-autocomplete-list');

            if (!searchInput || !hiddenInput || !dropdownList) return;

            let activeIndex = -1;
            let filteredItems = [];

            // Resaltar coincidencia dentro del texto
            function highlight(text, query) {
                if (!query) return text;
                const idx = text.toLowerCase().indexOf(query.toLowerCase());
                if (idx === -1) return text;
                return text.slice(0, idx)
                    + '<span class="match-highlight">' + text.slice(idx, idx + query.length) + '</span>'
                    + text.slice(idx + query.length);
            }

            function renderDropdown(query) {
                const q = query.trim().toLowerCase();
                filteredItems = q === ''
                    ? proveedoresList
                    : proveedoresList.filter(p => p.nombre.toLowerCase().includes(q));

                dropdownList.innerHTML = '';
                activeIndex = -1;

                if (filteredItems.length === 0) {
                    dropdownList.innerHTML = '<div class="proveedor-autocomplete-empty"><i class="fas fa-search me-1"></i>Sin resultados</div>';
                } else {
                    filteredItems.forEach(function (p, i) {
                        const div = document.createElement('div');
                        div.className = 'proveedor-autocomplete-item';
                        div.innerHTML = highlight(p.nombre, q);
                        div.dataset.id = p.id;
                        div.dataset.nombre = p.nombre;
                        div.addEventListener('mousedown', function (e) {
                            e.preventDefault(); // evitar que el input pierda foco antes de click
                            selectProveedor(p.id, p.nombre);
                        });
                        dropdownList.appendChild(div);
                    });
                }

                dropdownList.classList.add('open');
            }

            function selectProveedor(id, nombre) {
                hiddenInput.value = id;
                searchInput.value = nombre;
                searchInput.classList.remove('is-invalid-custom');
                dropdownList.classList.remove('open');
                dropdownList.innerHTML = '';
                activeIndex = -1;
            }

            function closeDropdown() {
                dropdownList.classList.remove('open');
                activeIndex = -1;
            }

            function setActiveItem(newIndex) {
                const items = dropdownList.querySelectorAll('.proveedor-autocomplete-item');
                items.forEach((el, i) => {
                    el.classList.toggle('active', i === newIndex);
                    if (i === newIndex) el.scrollIntoView({ block: 'nearest' });
                });
                activeIndex = newIndex;
            }

            // Al escribir → filtrar
            searchInput.addEventListener('input', function () {
                // Si el usuario modifica el texto, borramos la selección previa
                hiddenInput.value = '';
                renderDropdown(this.value);
            });

            // Al hacer foco → abrir con todos si no hay texto aún
            searchInput.addEventListener('focus', function () {
                if (this.value === '') renderDropdown('');
                else renderDropdown(this.value);
            });

            // Navegación con teclado
            searchInput.addEventListener('keydown', function (e) {
                const items = dropdownList.querySelectorAll('.proveedor-autocomplete-item');

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    setActiveItem(Math.min(activeIndex + 1, items.length - 1));
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    setActiveItem(Math.max(activeIndex - 1, 0));
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (activeIndex >= 0 && filteredItems[activeIndex]) {
                        const p = filteredItems[activeIndex];
                        selectProveedor(p.id, p.nombre);
                    }
                } else if (e.key === 'Escape') {
                    closeDropdown();
                }
            });

            // Cerrar al hacer click fuera
            document.addEventListener('click', function (e) {
                if (!searchInput.contains(e.target) && !dropdownList.contains(e.target)) {
                    closeDropdown();
                    // Si el campo tiene texto pero no hay ID seleccionado, marcar error
                    if (searchInput.value !== '' && hiddenInput.value === '') {
                        searchInput.classList.add('is-invalid-custom');
                    }
                }
            });

            // Validación antes de enviar el formulario
            const formNuevaCompra = document.getElementById('formNuevaCompra');
            if (formNuevaCompra) {
                formNuevaCompra.addEventListener('submit', function (e) {
                    if (!hiddenInput.value) {
                        e.preventDefault();
                        searchInput.classList.add('is-invalid-custom');
                        searchInput.focus();
                        searchInput.setAttribute('placeholder', '⚠ Selecciona un proveedor de la lista');
                        return;
                    }
                }, true); // capture: true para ejecutar antes del handler de doble envío
            }

            // Resetear al cerrar/abrir el modal
            const modalEl = document.getElementById('modalNuevaCompra');
            if (modalEl) {
                modalEl.addEventListener('hidden.bs.modal', function () {
                    searchInput.value = '';
                    hiddenInput.value = '';
                    searchInput.classList.remove('is-invalid-custom');
                    searchInput.setAttribute('placeholder', 'Escribe para buscar proveedor...');
                    closeDropdown();
                });

            }
        })();
    })();
</script>
<?= $this->endSection() ?>