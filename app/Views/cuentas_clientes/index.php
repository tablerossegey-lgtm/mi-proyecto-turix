<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link class="styles-admin-theme" rel="stylesheet" href="<?= base_url('css/admin.css?v=1.4') ?>">
<style>
.btn-filtro-cuenta {
    color: rgba(255, 255, 255, 0.6) !important;
    border-color: rgba(255, 255, 255, 0.2) !important;
    background: transparent !important;
    transition: all 0.2s ease-in-out;
}
.btn-filtro-cuenta:hover {
    color: #121824 !important;
    background-color: #f8f9fa !important;
    border-color: #f8f9fa !important;
}
</style>
<div class="container py-4">
    <!-- Encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 admin-title">Cuentas Corrientes de Clientes</h2>
            <p class="text-muted mb-0">Lleva el control de adeudos, abonos y ventas de forma libre o de inventario para
                tus clientes fijos.</p>
            <div class="admin-subtitle-line" style="background-color: #28a745;"></div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= base_url('admin/cuentas/ranking') ?>"
                class="btn btn-warning rounded-pill px-4 fw-bold d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="fas fa-trophy"></i> Ranking de Fidelidad
            </a>
            <a href="<?= base_url('admin/productos') ?>"
                class="btn btn-outline-dark rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2">
                <i class="fas fa-arrow-left"></i> Volver a Productos
            </a>
        </div>
    </div>

    <!-- Notificaciones flotantes tipo Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
        <?php if (session()->getFlashdata('success')): ?>
            <div id="toast-success" class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3"
                role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle text-success fs-5"></i>
                        <div><?= session()->getFlashdata('success') ?></div>
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
                        <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                        <div><?= session()->getFlashdata('error') ?></div>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <!-- Columna Izquierda: Buscador y Lista de Clientes -->
        <div class="col-12 col-lg-4 col-md-5">
            <div class="card border-0 shadow-sm admin-card p-3 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-white mb-0"><i class="fas fa-users text-success me-2"></i> Clientes Fijos</h5>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
                        <i class="fas fa-user-plus"></i> Nuevo
                    </button>
                </div>

                <!-- Buscador de Clientes -->
                <form id="form-buscador-clientes" action="<?= base_url('admin/cuentas') ?>" method="GET" class="mb-3"
                    hx-get="<?= base_url('admin/cuentas') ?>"
                    hx-target="#lista-clientes"
                    hx-select="#lista-clientes"
                    hx-swap="outerHTML">
                    <input type="hidden" name="filter" id="input-filter-value" value="<?= esc($filter ?? 'todos') ?>">
                    <div class="input-group">
                        <input type="text" id="search-cliente" name="q"
                            class="form-control bg-dark text-white border-secondary" placeholder="Buscar cliente..."
                            value="<?= esc($q) ?>" autocomplete="off"
                            hx-get="<?= base_url('admin/cuentas') ?>"
                            hx-include="#input-filter-value"
                            hx-trigger="input changed delay:300ms, search"
                            hx-target="#lista-clientes"
                            hx-select="#lista-clientes"
                            hx-swap="outerHTML"
                            oninput="toggleClearBtn(this.value)">
                        <button class="btn btn-success" type="submit" id="btn-search-cliente"><i
                                class="fas fa-search"></i></button>
                        <button class="btn btn-outline-secondary" type="button" id="btn-clear-search"
                            hx-get="<?= base_url('admin/cuentas') ?>"
                            hx-vals='{"q": ""}'
                            hx-target="#lista-clientes"
                            hx-select="#lista-clientes"
                            hx-swap="outerHTML"
                            style="display: <?= !empty($q) ? 'block' : 'none' ?>;"
                            onclick="document.getElementById('search-cliente').value = ''; this.style.display = 'none';">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </form>

                <!-- Lista de Clientes -->
                <div class="client-sidebar list-group list-group-flush rounded-3 border border-secondary border-opacity-25"
                    id="lista-clientes">
                    
                    <!-- Filtros Rápidos -->
                    <div class="p-2 border-bottom border-secondary border-opacity-25 bg-dark bg-opacity-10 d-flex gap-2 justify-content-center">
                        <?php $activeFilter = $filter ?? 'todos'; ?>
                        <button type="button" 
                                class="btn btn-sm rounded-pill px-3 py-1 fw-bold d-flex align-items-center gap-1 <?= $activeFilter === 'todos' ? 'btn-success text-white' : 'btn-filtro-cuenta' ?>"
                                style="font-size: 0.75rem;"
                                hx-get="<?= base_url('admin/cuentas') ?>"
                                hx-include="#search-cliente"
                                hx-vals='{"filter": "todos"}'
                                hx-target="#lista-clientes"
                                hx-select="#lista-clientes"
                                hx-swap="outerHTML"
                                onclick="document.getElementById('input-filter-value').value = 'todos'">
                            Todos
                        </button>
                        <button type="button" 
                                class="btn btn-sm rounded-pill px-3 py-1 fw-bold d-flex align-items-center gap-1 <?= $activeFilter === 'empacar' ? 'btn-danger text-white' : 'btn-filtro-cuenta' ?>"
                                style="font-size: 0.75rem;"
                                hx-get="<?= base_url('admin/cuentas') ?>"
                                hx-include="#search-cliente"
                                hx-vals='{"filter": "empacar"}'
                                hx-target="#lista-clientes"
                                hx-select="#lista-clientes"
                                hx-swap="outerHTML"
                                onclick="document.getElementById('input-filter-value').value = 'empacar'">
                            <i class="fas fa-box"></i> Por Empacar
                        </button>
                        <button type="button" 
                                class="btn btn-sm rounded-pill px-3 py-1 fw-bold d-flex align-items-center gap-1 <?= $activeFilter === 'entrega' ? 'btn-warning text-dark' : 'btn-filtro-cuenta' ?>"
                                style="font-size: 0.75rem;"
                                hx-get="<?= base_url('admin/cuentas') ?>"
                                hx-include="#search-cliente"
                                hx-vals='{"filter": "entrega"}'
                                hx-target="#lista-clientes"
                                hx-select="#lista-clientes"
                                hx-swap="outerHTML"
                                onclick="document.getElementById('input-filter-value').value = 'entrega'">
                            <i class="fas fa-boxes"></i> Por Entregar
                        </button>
                    </div>

                    <?php if (!empty($clientes)): ?>
                        <?php foreach ($clientes as $c): ?>
                            <div class="list-group-item client-item p-3 border-bottom border-secondary border-opacity-25 text-white d-flex justify-content-between align-items-center"
                                id="client-item-<?= $c['idCliente'] ?>"
                                hx-get="<?= base_url('admin/cuentas/compras/' . $c['idCliente']) ?>"
                                hx-target="#compras-cliente-container" hx-indicator="#loading-indicator"
                                onclick="seleccionarCliente(<?= $c['idCliente'] ?>)">
                                <div>
                                    <div class="fw-bold text-truncate client-name" style="max-width: 180px;">
                                        <?= esc($c['nombre']) ?></div>
                                    <small class="text-white-50 client-phone"><i class="fas fa-phone-alt me-1 text-muted"
                                            style="font-size: 0.75rem;"></i> <?= esc($c['cel'] ?: 'Sin teléfono') ?></small>
                                    
                                    <!-- Badges de entrega en el sidebar -->
                                    <div class="mt-1 d-flex gap-1.5 flex-wrap">
                                        <?php if (($c['cant_por_empacar'] ?? 0) > 0): ?>
                                            <span class="badge bg-danger text-white font-monospace" style="font-size: 0.65rem;" title="Productos por empacar">
                                                <i class="fas fa-box"></i> <?= $c['cant_por_empacar'] ?> empacar
                                            </span>
                                        <?php endif; ?>
                                        <?php if (($c['cant_por_entregar'] ?? 0) > 0): ?>
                                            <span class="badge bg-info text-dark font-monospace" style="font-size: 0.65rem;" title="Productos por entregar">
                                                <i class="fas fa-boxes"></i> <?= $c['cant_por_entregar'] ?> entregar
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <?php if ($c['total_pendiente'] > 0): ?>
                                        <span class="badge bg-danger rounded-pill fw-bold" id="sidebar-debt-<?= $c['idCliente'] ?>">
                                            $<?= number_format($c['total_pendiente'], 2) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill" id="sidebar-debt-<?= $c['idCliente'] ?>">
                                            $0.00
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted small">No se encontraron clientes.</div>
                    <?php endif; ?>
                    <div class="p-4 text-center text-muted small" id="no-search-results" style="display: none;">No se
                        encontraron resultados.</div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Detalle de Compras y Balance -->
        <div class="col-12 col-lg-8 col-md-7">
            <!-- Indicador de Carga HTMX -->
            <div id="loading-indicator" class="htmx-indicator text-center py-4 w-100">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="text-muted mt-2 small">Cargando información de cuenta...</p>
            </div>

            <!-- Contenedor Principal (HTMX carga el parcial aquí) -->
            <div id="compras-cliente-container">
                <div class="card border-0 shadow-sm admin-card p-5 text-center text-white-50">
                    <i class="bi bi-wallet2 fs-1 text-success opacity-50 mb-3"></i>
                    <h5 class="text-white fw-bold">Selecciona un Cliente</h5>
                    <p class="small mb-0">Haz clic en alguno de los clientes del listado de la izquierda para ver su
                        estado de cuenta, compras detalladas y generar su orden de cobro para WhatsApp.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: REGISTRAR COMPRA -->
<div class="modal fade" id="modalNuevaCompra" tabindex="-1" aria-labelledby="modalNuevaCompraLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white">
            <div class="modal-header modal-encargo-header py-3 px-4"
                style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title fw-bold text-white" id="modalNuevaCompraLabel">
                    <i class="fas fa-cart-plus me-2 text-success"></i> Registrar Compra / Cuenta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="formNuevaCompra" action="<?= base_url('admin/cuentas/crear') ?>" method="POST">
                <input type="hidden" name="id_cliente" id="modal_id_cliente" value="">

                <div class="modal-body p-4">
                    <!-- Datos Generales de la Compra -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label for="fecha_compra" class="form-label modal-encargo-label">Fecha de Compra</label>
                            <input type="date" class="form-control modal-encargo-control text-white" id="fecha_compra"
                                name="fecha_compra" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="estatus_compra" class="form-label modal-encargo-label d-block">Estado de Pago
                                (Toda la Compra)</label>
                            <div class="d-flex align-items-center gap-3 pt-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="estatus_compra"
                                        id="status_pendiente" value="0" checked>
                                    <label class="form-check-label text-danger fw-bold" for="status_pendiente">Pendiente
                                        (Debe)</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="estatus_compra"
                                        id="status_pagado" value="1">
                                    <label class="form-check-label text-success fw-bold" for="status_pagado">Pagado
                                        (Caja)</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="border-top: 1px dashed rgba(255,255,255,0.15); margin: 20px 0;"></div>

                    <!-- Sección: Agregar Producto -->
                    <div class="bg-dark p-3 rounded border border-secondary border-opacity-25 mb-4">
                        <h6 class="fw-bold mb-3 text-success"><i class="fas fa-plus-circle me-1"></i> Agregar Producto a
                            la Compra</h6>

                        <div class="mb-3">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="tipo_compra_temp" id="tipo_inventario"
                                    value="inventario" checked onclick="toggleTipoForm('inventario')">
                                <label class="btn btn-outline-success fw-bold" for="tipo_inventario"><i
                                        class="bi bi-box-seam me-1"></i> Del Inventario</label>

                                <input type="radio" class="btn-check" name="tipo_compra_temp" id="tipo_libre"
                                    value="libre" onclick="toggleTipoForm('libre')">
                                <label class="btn btn-outline-success fw-bold" for="tipo_libre"><i
                                        class="bi bi-pen me-1"></i> Formato Libre</label>
                            </div>
                        </div>

                        <!-- Campos de Producto del Inventario -->
                        <div id="seccion-inventario" class="mb-3">
                            <div class="position-relative mb-3">
                                <label for="producto_search" class="form-label modal-encargo-label">Buscar en
                                    Inventario</label>
                                <input type="hidden" id="modal_id_inventario" value="0">
                                <input type="text" class="form-control modal-encargo-control text-white w-100"
                                    id="producto_search" placeholder="Escribe SKU o Nombre..." autocomplete="off">
                                <div class="autocomplete-dropdown shadow-lg" id="dropdown_productos"></div>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="descontar_stock"
                                    value="1" checked>
                                <label class="form-check-label modal-encargo-label text-white-50" for="descontar_stock"
                                    style="margin-left: 0.5rem;">Descontar unidades del inventario actual</label>
                            </div>
                        </div>

                        <!-- Campos Formato Libre -->
                        <div class="mb-3" id="seccion-desc-libre" style="display: none;">
                            <label for="desc_producto" class="form-label modal-encargo-label">Descripción del
                                Producto</label>
                            <input type="text" class="form-control modal-encargo-control text-white" id="desc_producto"
                                placeholder="Ej: Bolsa de regalo grande decorada">
                        </div>

                        <div class="row g-3">
                            <div class="col-6 col-md-4">
                                <label for="cantidad" class="form-label modal-encargo-label">Cantidad</label>
                                <input type="number" class="form-control modal-encargo-control text-white" id="cantidad"
                                    value="1" min="1">
                            </div>
                            <div class="col-6 col-md-4">
                                <label for="precio_unit" class="form-label modal-encargo-label">Precio Unitario
                                    ($)</label>
                                <input type="number" step="0.01" min="0"
                                    class="form-control modal-encargo-control text-white" id="precio_unit" value="0.00">
                            </div>
                            <div class="col-12 col-md-4 d-flex align-items-end">
                                <button type="button"
                                    class="btn btn-success w-100 py-2.5 rounded fw-bold text-white hover-shadow"
                                    id="btn-agregar-producto-lista">
                                    <i class="fas fa-plus me-1"></i> Agregar a Lista
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Tabla de Productos Agregados -->
                    <h6 class="fw-bold mb-2"><i class="fas fa-list me-1 text-warning"></i> Productos agregados a esta
                        compra</h6>
                    <div class="table-responsive rounded border border-secondary border-opacity-25 mb-3">
                        <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: rgba(0, 0, 0, 0.25); --bs-table-hover-bg: rgba(255, 255, 255, 0.04); border-color: rgba(255, 255, 255, 0.08);">
                            <thead class="text-white-50 small" style="background-color: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                <tr>
                                    <th class="ps-3 py-2 text-white-50">Producto / Detalle</th>
                                    <th class="py-2 text-center text-white-50" style="width: 100px;">Cant.</th>
                                    <th class="py-2 text-end text-white-50" style="width: 120px;">Precio U.</th>
                                    <th class="py-2 text-end text-white-50" style="width: 120px;">Subtotal</th>
                                    <th class="py-2 text-center" style="width: 80px;"></th>
                                </tr>
                            </thead>
                            <tbody id="tbody-productos-lista" class="text-white">
                                <!-- Se llena dinámicamente con JavaScript -->
                                <tr id="tr-lista-vacia">
                                    <td colspan="5" class="text-center py-4 text-white-50 small">
                                        No has añadido productos a esta compra todavía.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Sección: Aplicar cupón o descuento -->
                    <div class="bg-dark p-3 rounded border border-info border-opacity-25 mb-3" id="seccion-aplicar-beneficio">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                            <h6 class="fw-bold mb-0 text-info">
                                <i class="fas fa-ticket-alt me-1"></i> Aplicar cupón o descuento
                            </h6>
                            <span class="badge bg-info text-dark rounded-pill" id="badge-cupones-detectados" style="display:none;">
                                0 disponible(s)
                            </span>
                        </div>

                        <div class="btn-group w-100 mb-3" role="group" aria-label="Modo de descuento">
                            <input type="radio" class="btn-check" name="modo_beneficio" id="beneficio_ninguno" value="ninguno" checked onchange="cambiarModoBeneficio('ninguno')">
                            <label class="btn btn-sm btn-outline-secondary fw-bold" for="beneficio_ninguno">Sin beneficio</label>

                            <input type="radio" class="btn-check" name="modo_beneficio" id="beneficio_cupon" value="cupon" onchange="cambiarModoBeneficio('cupon')">
                            <label class="btn btn-sm btn-outline-info fw-bold" for="beneficio_cupon" id="lbl-tab-cupon">Usar cupón</label>

                            <input type="radio" class="btn-check" name="modo_beneficio" id="beneficio_manual" value="manual" onchange="cambiarModoBeneficio('manual')">
                            <label class="btn btn-sm btn-outline-warning fw-bold" for="beneficio_manual">Descuento manual</label>
                        </div>

                        <!-- Subpanel: Cupón disponible -->
                        <div id="panel-beneficio-cupon" style="display: none;" class="mb-2">
                            <label for="select_cupon_compra" class="form-label modal-encargo-label">Selecciona un cupón disponible del cliente</label>
                            <select class="form-select modal-encargo-control text-white mb-2" id="select_cupon_compra" onchange="recalcularDesgloseCompra()">
                                <option value="">-- Selecciona un cupón --</option>
                            </select>
                            <div id="info-cupon-seleccionado" class="small text-white-50 p-2 rounded border border-secondary border-opacity-25" style="display:none; background: rgba(0,0,0,0.2);"></div>
                        </div>

                        <!-- Subpanel: Descuento manual -->
                        <div id="panel-beneficio-manual" style="display: none;" class="mb-2">
                            <div class="row g-2">
                                <div class="col-6 col-md-4">
                                    <label class="form-label modal-encargo-label">Tipo</label>
                                    <select class="form-select modal-encargo-control text-white" id="manual_tipo_desc" onchange="recalcularDesgloseCompra()">
                                        <option value="fijo">Importe fijo ($)</option>
                                        <option value="porcentaje">Porcentaje (%)</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-4">
                                    <label class="form-label modal-encargo-label">Valor</label>
                                    <input type="number" step="0.01" min="0" class="form-control modal-encargo-control text-white" id="manual_valor_desc" value="0.00" oninput="recalcularDesgloseCompra()">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label modal-encargo-label">Motivo obligatorio *</label>
                                    <input type="text" class="form-control modal-encargo-control text-white" id="manual_motivo_desc" placeholder="Ej: Cortesía por fidelidad" oninput="recalcularDesgloseCompra()">
                                </div>
                            </div>
                        </div>

                        <div id="alerta-beneficio-compra" class="alert alert-warning py-2 px-3 small mb-0 mt-2" style="display: none;"></div>
                    </div>

                    <!-- Gastos adicionales / envío (no reciben descuento) -->
                    <div class="row g-2 align-items-center mb-3">
                        <div class="col-12 col-sm-7">
                            <label for="gastos_envio_compra" class="form-label modal-encargo-label mb-0">
                                <i class="fas fa-shipping-fast me-1 text-white-50"></i> Gastos de envío / adicionales ($) <small class="text-white-50">(no aplica descuento)</small>
                            </label>
                        </div>
                        <div class="col-12 col-sm-5">
                            <input type="number" step="0.01" min="0" class="form-control form-control-sm modal-encargo-control text-white text-end" id="gastos_envio_compra" value="0.00" oninput="recalcularDesgloseCompra()">
                        </div>
                    </div>

                    <!-- Desglose y Gran Total Calculado -->
                    <div class="p-3 rounded bg-dark border border-secondary border-opacity-25">
                        <div class="d-flex justify-content-between align-items-center small text-white-50 mb-1">
                            <span>Subtotal de productos:</span>
                            <span id="lbl-subtotal-compra">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center small text-info mb-1" id="row-descuento-compra" style="display:none !important;">
                            <span id="lbl-descuento-concepto">Descuento aplicado:</span>
                            <span id="lbl-descuento-compra">-$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center small text-white-50 mb-1" id="row-envio-compra" style="display:none !important;">
                            <span>Gastos de envío:</span>
                            <span id="lbl-envio-compra">$0.00</span>
                        </div>
                        <div class="border-top border-secondary border-opacity-25 pt-2 mt-2 d-flex justify-content-between align-items-center">
                            <span class="small text-white-50 fw-bold">TOTAL NETO DE LA COMPRA:</span>
                            <span class="fs-4 fw-bold text-success" id="lbl-total-calculado">$0.00</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer modal-encargo-footer d-flex gap-2"
                    style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold text-white hover-shadow"
                        id="btn-registrar-compra-submit" disabled>
                        <i class="fas fa-save me-1"></i> Registrar Compra
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: EDITAR COMPRA -->
<div class="modal fade" id="modalEditarCompra" tabindex="-1" aria-labelledby="modalEditarCompraLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white">
            <div class="modal-header modal-encargo-header py-3 px-4"
                style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title fw-bold text-white" id="modalEditarCompraLabel">
                    <i class="fas fa-edit me-2 text-warning"></i> Modificar Compra
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="formEditarCompra" action="" method="POST" hx-target="#compras-cliente-container" hx-swap="innerHTML">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label modal-encargo-label text-white-50">Producto:</label>
                        <div class="p-2.5 rounded bg-dark border border-secondary border-opacity-25"
                            id="edit-lbl-producto" style="font-size: 0.95rem;">-</div>
                    </div>

                    <div class="row g-3">
                        <!-- Fecha de Compra -->
                        <div class="col-12">
                            <label for="edit_fecha_compra" class="form-label modal-encargo-label">Fecha de
                                Compra</label>
                            <input type="date" class="form-control modal-encargo-control text-white"
                                id="edit_fecha_compra" name="fecha_compra">
                        </div>

                        <!-- Cantidad -->
                        <div class="col-6">
                            <label for="edit_cantidad" class="form-label modal-encargo-label">Cantidad *</label>
                            <input type="number" class="form-control modal-encargo-control text-white"
                                id="edit_cantidad" name="cantidad" min="1" required oninput="calcularTotalEdit()">
                        </div>

                        <!-- Precio Unitario -->
                        <div class="col-6">
                            <label for="edit_precio_unit" class="form-label modal-encargo-label">Precio Unitario ($)
                                *</label>
                            <input type="number" step="0.01" min="0"
                                class="form-control modal-encargo-control text-white" id="edit_precio_unit"
                                name="precio_unit" required oninput="calcularTotalEdit()">
                        </div>

                        <!-- Total Calculado -->
                        <div class="col-12">
                            <div
                                class="p-3 rounded bg-dark border border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                                <span class="small text-white-50 fw-bold">TOTAL ESTIMADO:</span>
                                <span class="fs-4 fw-bold text-warning" id="edit-lbl-total-calculado">$0.00</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch mb-3" id="edit-seccion-stock" style="display:none;">
                                <input class="form-check-input" type="checkbox" role="switch" id="edit_descontar_stock"
                                    name="descontar_stock" value="1" checked>
                                <label class="form-check-label modal-encargo-label text-white-50"
                                    for="edit_descontar_stock" style="margin-left: 0.5rem;">Ajustar unidades en el
                                    inventario actual</label>
                            </div>
                        </div>

                        <!-- Estado de la compra -->
                        <div class="col-12">
                            <label for="edit_estatus_compra" class="form-label modal-encargo-label d-block">Estado de
                                Pago</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="estatus_compra"
                                    id="edit_status_pendiente" value="0">
                                <label class="form-check-label text-danger fw-bold"
                                    for="edit_status_pendiente">Pendiente (Debe)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="estatus_compra"
                                    id="edit_status_pagado" value="1">
                                <label class="form-check-label text-success fw-bold" for="edit_status_pagado">Pagado
                                    (Caja)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer modal-encargo-footer d-flex gap-2"
                    style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark hover-shadow">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: NUEVO CLIENTE -->
<div class="modal fade" id="modalNuevoCliente" tabindex="-1" aria-labelledby="modalNuevoClienteLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white"
            style="border-radius: 20px; background-color: #121824; border: 1px solid rgba(255,255,255,0.15);">
            <div class="modal-header modal-encargo-header py-3 px-4"
                style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title fw-bold text-white" id="modalNuevoClienteLabel">
                    <i class="fas fa-user-plus me-2 text-success"></i> Registrar Nuevo Cliente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="formNuevoCliente" action="<?= base_url('admin/cuentas/crear-cliente') ?>" method="POST"
                  hx-post="<?= base_url('admin/cuentas/crear-cliente') ?>"
                  hx-target="#lista-clientes"
                  hx-select="#lista-clientes"
                  hx-swap="outerHTML"
                  onsubmit="mostrarSpinner(this, 'Registrando...')">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Nombre -->
                        <div class="col-12">
                            <label for="nuevo_cliente_nombre" class="form-label modal-encargo-label">Nombre Completo *</label>
                            <input type="text" class="form-control modal-encargo-control text-white"
                                id="nuevo_cliente_nombre" name="nombre" required placeholder="Ej: Juan Pérez">
                        </div>

                        <!-- Teléfono -->
                        <div class="col-12">
                            <label for="nuevo_cliente_cel" class="form-label modal-encargo-label">Teléfono de Contacto (WhatsApp)</label>
                            <input type="tel" class="form-control modal-encargo-control text-white"
                                id="nuevo_cliente_cel" name="cel" placeholder="Ej: 9991234567">
                        </div>

                        <!-- Tipo de Cliente -->
                        <div class="col-12">
                            <label for="nuevo_cliente_tipo" class="form-label modal-encargo-label">Tipo de Cliente / Etiqueta</label>
                            <input type="text" class="form-control modal-encargo-control text-white"
                                id="nuevo_cliente_tipo" name="tipoCliente" placeholder="Ej: General, Mayorista, Distribuidor, etc.">
                        </div>
                    </div>
                </div>

                <div class="modal-footer modal-encargo-footer d-flex gap-2"
                    style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold text-white hover-shadow">
                        <i class="fas fa-save me-1"></i> Registrar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: EDITAR CLIENTE -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-labelledby="modalEditarClienteLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white">
            <div class="modal-header modal-encargo-header py-3 px-4"
                style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title fw-bold text-white" id="modalEditarClienteLabel">
                    <i class="fas fa-user-edit me-2 text-warning"></i> Editar Datos del Cliente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="formEditarCliente" action="" method="POST" hx-target="#compras-cliente-container" hx-swap="innerHTML">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Nombre -->
                        <div class="col-12">
                            <label for="edit_cliente_nombre" class="form-label modal-encargo-label">Nombre Completo
                                *</label>
                            <input type="text" class="form-control modal-encargo-control text-white"
                                id="edit_cliente_nombre" name="nombre" required placeholder="Ej: Juan Pérez">
                        </div>

                        <!-- Teléfono -->
                        <div class="col-12">
                            <label for="edit_cliente_cel" class="form-label modal-encargo-label">Teléfono de Contacto
                                (WhatsApp)</label>
                            <input type="tel" class="form-control modal-encargo-control text-white"
                                id="edit_cliente_cel" name="cel" placeholder="Ej: 9991234567">
                        </div>

                        <!-- Tipo de Cliente -->
                        <div class="col-12">
                            <label for="edit_cliente_tipo" class="form-label modal-encargo-label">Tipo de Cliente /
                                Etiqueta</label>
                            <input type="text" class="form-control modal-encargo-control text-white"
                                id="edit_cliente_tipo" name="tipoCliente"
                                placeholder="Ej: General, Mayorista, Distribuidor, etc.">
                        </div>
                    </div>
                </div>

                <div class="modal-footer modal-encargo-footer d-flex gap-2"
                    style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark hover-shadow">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: TICKET DIGITAL -->
<div class="modal fade" id="modalTicketDigital" tabindex="-1" aria-labelledby="modalTicketDigitalLabel"
    aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white"
            style="border-radius: 20px; background-color: #121824; border: 1px solid rgba(255,255,255,0.1);">
            <div class="modal-body p-4 text-center">
                <h4 class="fw-bold mb-3 text-white" id="modalTicketDigitalLabel">Ticket Digital</h4>

                <div class="p-4 rounded-3 mb-4 text-start custom-scroll"
                    style="background-color: #1a2238; border: 1px solid rgba(255,255,255,0.05); font-family: 'Courier New', Courier, monospace; font-size: 0.95rem; line-height: 1.6; max-height: 55vh; overflow-y: auto;">
                    <div id="ticket-content" style="white-space: pre-wrap; color: #ffffff;"></div>
                </div>
                <div id="ticket-dinamicas-avisos" class="text-start mb-3 d-none"></div>

                <button type="button"
                    class="btn w-100 py-3 rounded-pill fw-bold text-dark d-flex align-items-center justify-content-center gap-2 mb-2"
                    id="btn-copiar-resumen-ticket"
                    style="background: linear-gradient(135deg, #2ce3f6 0%, #0072ff 100%); border: none; font-size: 1.05rem; transition: transform 0.2s, box-shadow 0.2s; color: #121824 !important;">
                    <i class="fas fa-copy"></i> Copiar al Portapapeles
                </button>

                <button type="button"
                    class="btn w-100 py-3 rounded-pill fw-bold text-white d-flex align-items-center justify-content-center gap-2 mb-3"
                    id="btn-compartir-whatsapp-ticket"
                    style="background-color: #25D366; border: none; font-size: 1.05rem; transition: transform 0.2s, box-shadow 0.2s;">
                    <i class="fab fa-whatsapp"></i> Compartir por WhatsApp
                </button>

                <button type="button" class="btn btn-link text-white-50 text-decoration-none fw-semibold"
                    data-bs-dismiss="modal" id="btn-cerrar-ticket">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: CONFIRMAR ELIMINACIÓN -->
<div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-labelledby="modalConfirmarEliminarLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content modal-encargo-content text-white"
            style="border-radius: 20px; background-color: #121824; border: 1px solid rgba(255,255,255,0.15);">
            <div class="modal-header modal-encargo-header py-3 px-4"
                style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2"
                    id="modalConfirmarEliminarLabel">
                    <i class="fas fa-exclamation-triangle text-danger"></i> ¿Eliminar compra?
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="mb-0 text-white-50" style="font-size: 0.95rem;">¿Estás seguro de que deseas eliminar este
                    registro de compra? Si es de inventario, se devolverá el stock.</p>
            </div>
            <div class="modal-footer d-flex gap-2 justify-content-center pb-4" style="border-top: none;">
                <button type="button" class="btn btn-outline-light rounded-pill px-4"
                    data-bs-dismiss="modal">Cancelar</button>
                <form id="formConfirmarEliminar" action="" method="POST" class="d-inline" hx-target="#compras-cliente-container" hx-swap="innerHTML">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- MODAL: NUEVO / EDITAR CUPÓN -->
<div class="modal fade" id="modalCupon" tabindex="-1" aria-labelledby="modalCuponLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white">
            <div class="modal-header modal-encargo-header py-3 px-4" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title fw-bold text-white" id="modalCuponLabel">
                    <i class="fas fa-ticket-alt me-2 text-info"></i> Nuevo Cupón
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formCupon" onsubmit="guardarCupon(event)">
                <input type="hidden" id="cupon_id" value="">
                <input type="hidden" id="cupon_id_cliente" value="">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label modal-encargo-label">Cliente asignado</label>
                            <input type="text" class="form-control modal-encargo-control text-info fw-bold" id="cupon_nombre_cliente" readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="cupon_codigo" class="form-label modal-encargo-label">Código único del cupón *</label>
                            <div class="input-group">
                                <input type="text" class="form-control modal-encargo-control text-white text-uppercase font-monospace" id="cupon_codigo" placeholder="Ej: NAV26-001" required maxlength="50">
                                <button type="button" class="btn btn-outline-info" onclick="generarCodigoCupon()" title="Generar código único">
                                    <i class="fas fa-magic me-1"></i> Generar
                                </button>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="cupon_concepto" class="form-label modal-encargo-label">Nombre o concepto del beneficio *</label>
                            <input type="text" class="form-control modal-encargo-control text-white" id="cupon_concepto" placeholder="Ej: Premio navideño por fidelidad" required maxlength="150">
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="cupon_tipo" class="form-label modal-encargo-label">Tipo de descuento *</label>
                            <select class="form-select modal-encargo-control text-white" id="cupon_tipo" onchange="toggleCamposTipoCupon()">
                                <option value="fijo">Importe fijo ($)</option>
                                <option value="porcentaje">Porcentaje (%)</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="cupon_valor" class="form-label modal-encargo-label" id="lbl_cupon_valor">Valor del descuento ($) *</label>
                            <input type="number" step="0.01" min="0.01" class="form-control modal-encargo-control text-white" id="cupon_valor" required placeholder="30.00">
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="cupon_limite_usos" class="form-label modal-encargo-label">Límite de usos *</label>
                            <input type="number" min="1" class="form-control modal-encargo-control text-white" id="cupon_limite_usos" value="1" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="cupon_compra_minima" class="form-label modal-encargo-label">Importe mínimo de compra (opcional)</label>
                            <input type="number" step="0.01" min="0" class="form-control modal-encargo-control text-white" id="cupon_compra_minima" placeholder="Sin mínimo">
                        </div>
                        <div class="col-12 col-md-6" id="grupo_cupon_descuento_maximo" style="display:none;">
                            <label for="cupon_descuento_maximo" class="form-label modal-encargo-label">Importe máximo de descuento (opcional)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control modal-encargo-control text-white" id="cupon_descuento_maximo" placeholder="Sin límite máximo">
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="cupon_fecha_emision" class="form-label modal-encargo-label">Fecha de emisión *</label>
                            <input type="date" class="form-control modal-encargo-control text-white" id="cupon_fecha_emision" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="cupon_fecha_inicio" class="form-label modal-encargo-label">Inicio de vigencia *</label>
                            <input type="date" class="form-control modal-encargo-control text-white" id="cupon_fecha_inicio" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="cupon_fecha_vencimiento" class="form-label modal-encargo-label">Fecha de vencimiento (opcional)</label>
                            <input type="date" class="form-control modal-encargo-control text-white" id="cupon_fecha_vencimiento">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="cupon_motivo_origen" class="form-label modal-encargo-label">Motivo u origen del cupón</label>
                            <input type="text" class="form-control modal-encargo-control text-white" id="cupon_motivo_origen" list="lista_motivos_cupon" placeholder="Ej: Reconocimiento por fidelidad">
                            <datalist id="lista_motivos_cupon">
                                <option value="Reconocimiento por fidelidad">
                                <option value="Dinámica navideña">
                                <option value="Promoción especial">
                                <option value="Cumpleaños">
                                <option value="Aniversario">
                                <option value="Compensación comercial">
                            </datalist>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="cupon_estado_preview" class="form-label modal-encargo-label">Estado</label>
                            <input type="text" class="form-control modal-encargo-control text-white-50" id="cupon_estado_preview" value="Automático según vigencia y usos" readonly>
                        </div>

                        <div class="col-12">
                            <label for="cupon_observaciones" class="form-label modal-encargo-label">Observaciones</label>
                            <textarea class="form-control modal-encargo-control text-white" id="cupon_observaciones" rows="2" placeholder="Notas administrativas o referencia de la tarjeta/campaña..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-encargo-footer d-flex gap-2" style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-dark rounded-pill px-4 fw-bold" id="btn-guardar-cupon">
                        <i class="fas fa-save me-1"></i> Guardar Cupón
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: DETALLE E HISTORIAL DE CUPÓN -->
<div class="modal fade" id="modalDetalleCupon" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white">
            <div class="modal-header modal-encargo-header py-3 px-4" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-ticket-alt me-2 text-info"></i> Detalle del Cupón
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="detalle-cupon-body">
                <div class="text-center py-4 text-white-50">
                    <div class="spinner-border text-info" role="status"></div>
                </div>
            </div>
            <div class="modal-footer modal-encargo-footer" style="border-top: 1px solid rgba(255,255,255,0.1);">
                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: CANCELAR CUPÓN -->
<div class="modal fade" id="modalCancelarCupon" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white">
            <div class="modal-header modal-encargo-header py-3 px-4" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="fas fa-ban me-2"></i> Cancelar Cupón
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="cancelar_cupon_id" value="">
                <p class="text-white-50 mb-3">
                    ¿Deseas cancelar el cupón <strong class="text-info font-monospace" id="cancelar_cupon_codigo"></strong>?
                    El cupón conservará su historial pero no podrá ser utilizado en futuras compras.
                </p>
                <label for="cancelar_cupon_motivo" class="form-label modal-encargo-label">Motivo de cancelación</label>
                <input type="text" class="form-control modal-encargo-control text-white" id="cancelar_cupon_motivo" placeholder="Ej: Cancelado por el administrador">
            </div>
            <div class="modal-footer modal-encargo-footer d-flex gap-2" style="border-top: 1px solid rgba(255,255,255,0.1);">
                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-danger rounded-pill px-4 fw-bold" id="btn-confirmar-cancelar-cupon" onclick="confirmarCancelarCupon()">
                    Confirmar Cancelación
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: REGISTRAR ABONO / PAGO -->
<div class="modal fade" id="modalRegistrarAbono" tabindex="-1" aria-labelledby="modalRegistrarAbonoLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white"
            style="border-radius: 20px; background-color: #121824; border: 1px solid rgba(255,255,255,0.15);">
            <div class="modal-header modal-encargo-header py-3 px-4"
                style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2"
                    id="modalRegistrarAbonoLabel">
                    <i class="fas fa-hand-holding-usd text-success"></i> Registrar Pago / Abono
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="formRegistrarAbono" action="<?= base_url('admin/cuentas/abonar') ?>" method="POST" hx-post="<?= base_url('admin/cuentas/abonar') ?>" hx-target="#compras-cliente-container" hx-swap="innerHTML">
                <?= csrf_field() ?>
                <input type="hidden" name="id_cliente" id="abono_id_cliente" value="">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="fecha_abono" class="form-label modal-encargo-label text-white-50">Fecha de
                            Pago</label>
                        <input type="date" class="form-control modal-encargo-control text-white" id="fecha_abono"
                            name="fecha_abono" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="abono_monto" class="form-label modal-encargo-label text-white-50">Monto a Abonar ($)
                            *</label>
                        <input type="number" step="0.01" min="0.01"
                            class="form-control modal-encargo-control text-white fs-4 fw-bold text-success"
                            id="abono_monto" name="monto" required placeholder="0.00">
                        <div class="form-text text-white-50 mt-1" id="abono_monto_help">Monto total pendiente: $0.00
                        </div>
                    </div>
                </div>

                <div class="modal-footer modal-encargo-footer d-flex gap-2"
                    style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold text-white hover-shadow">
                        <i class="fas fa-save me-1"></i> Registrar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    document.body.classList.add('admin-body');

    window.activeClientId = null;
    window.toggleClearBtn = function(val) {
        const btn = document.getElementById('btn-clear-search');
        if (btn) {
            btn.style.display = val.trim() !== '' ? 'block' : 'none';
        }
    };

    // Cambiar estilos de selección de cliente activo
    function seleccionarCliente(id) {
        window.activeClientId = id;
        document.querySelectorAll('.client-item').forEach(item => {
            item.classList.remove('active');
        });
        const element = document.getElementById('client-item-' + id);
        if (element) {
            element.classList.add('active');
        }
        // En móviles y tabletas verticales, desplazar suavemente al área de detalle
        if (window.innerWidth < 992) {
            setTimeout(() => {
                const target = document.getElementById('compras-cliente-container');
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 180);
        }
    }

    let listaProductos = [];

    // Cambiar tipo de formulario en el modal de nueva compra
    function toggleTipoForm(tipo) {
        if (tipo === 'inventario') {
            document.getElementById('seccion-inventario').style.display = 'block';
            document.getElementById('seccion-desc-libre').style.display = 'none';
            document.getElementById('modal_id_inventario').value = '0';
            document.getElementById('producto_search').value = '';
        } else {
            document.getElementById('seccion-inventario').style.display = 'none';
            document.getElementById('seccion-desc-libre').style.display = 'block';
            document.getElementById('modal_id_inventario').value = '0';
            document.getElementById('producto_search').value = '';
            document.getElementById('desc_producto').value = '';
        }
        document.getElementById('cantidad').value = '1';
        document.getElementById('precio_unit').value = '0.00';
    }

    // Renderizar la lista de productos agregados en el modal
    function renderListaProductos() {
        const tbody = document.getElementById('tbody-productos-lista');
        const btnSubmit = document.getElementById('btn-registrar-compra-submit');

        if (!tbody) return;

        tbody.innerHTML = '';

        if (listaProductos.length === 0) {
            tbody.innerHTML = `
                <tr id="tr-lista-vacia">
                    <td colspan="5" class="text-center py-4 text-white-50 small">
                        No has añadido productos a esta compra todavía.
                    </td>
                </tr>
            `;
            if (btnSubmit) btnSubmit.disabled = true;
            recalcularDesgloseCompra();
            return;
        }

        if (btnSubmit) btnSubmit.disabled = false;

        let granTotal = 0.00;

        listaProductos.forEach((prod, index) => {
            const subtotal = prod.cantidad * prod.precio_unit;
            granTotal += subtotal;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="ps-3 py-2">
                    <div class="fw-semibold text-white" style="font-size: 0.9rem;">${prod.desc_producto}</div>
                    ${prod.id_inventario > 0 ? `<small class="text-success" style="font-size: 0.75rem;"><i class="bi bi-box-seam me-1"></i>Inventario${prod.descontar_stock ? ' (Descuenta Stock)' : ''}</small>` : '<small class="text-info" style="font-size: 0.75rem;"><i class="bi bi-pen me-1"></i>Formato Libre</small>'}
                </td>
                <td class="py-2 text-center fw-semibold text-white">${prod.cantidad}</td>
                <td class="py-2 text-end text-white fw-medium">$${prod.precio_unit.toFixed(2)}</td>
                <td class="py-2 text-end fw-bold text-success">$${subtotal.toFixed(2)}</td>
                <td class="py-2 text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm border-0 p-1" onclick="eliminarProductoLista(${index})">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        recalcularDesgloseCompra();
    }

    // Eliminar producto de la lista temporal
    function eliminarProductoLista(index) {
        listaProductos.splice(index, 1);
        renderListaProductos();
    }

    // Mantener la función dummy para compatibilidad con el evento autocomplete
    function calcularTotal() {
        // No-op
    }

    // Calcular el total estimado en edición
    function calcularTotalEdit() {
        const cant = parseInt(document.getElementById('edit_cantidad').value) || 0;
        const price = parseFloat(document.getElementById('edit_precio_unit').value) || 0;
        const total = cant * price;
        document.getElementById('edit-lbl-total-calculado').innerText = '$' + total.toFixed(2);
    }

    // Inicializar autocompletado del buscador de productos en el modal
    function initProductoSearchAutocomplete() {
        const input = document.getElementById('producto_search');
        if (!input) return;
        const dropdown = document.getElementById('dropdown_productos');
        const hidden = document.getElementById('modal_id_inventario');
        const priceInput = document.getElementById('precio_unit');

        input.addEventListener('focus', function () {
            if (input.value.trim().length > 0) {
                dropdown.style.display = 'block';
            }
        });

        let searchTimer = null;
        let searchAbortCtrl = null;
        const clientSearchCache = {};

        function renderSearchResults(data) {
            dropdown.innerHTML = '';
            if (!Array.isArray(data) || data.length === 0) {
                dropdown.innerHTML = '<div class="text-white-50 p-2 text-center small">No se encontraron productos</div>';
                dropdown.style.display = 'block';
                return;
            }

            data.forEach(p => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'autocomplete-item';
                btn.innerHTML = `<strong>${p.sku}</strong> - ${p.descripcion} ($${p.precio}) <span class="float-end text-white-50">Stock: ${p.stock}</span>`;
                btn.addEventListener('click', function () {
                    hidden.value = p.id;
                    input.value = `${p.sku} - ${p.descripcion}`;
                    priceInput.value = p.precio;
                    dropdown.style.display = 'none';
                    calcularTotal();
                });
                dropdown.appendChild(btn);
            });
            dropdown.style.display = 'block';
        }

        input.addEventListener('input', function () {
            const query = input.value.trim();

            if (searchTimer) {
                clearTimeout(searchTimer);
            }

            if (query.length < 2) {
                if (searchAbortCtrl) {
                    searchAbortCtrl.abort();
                }
                dropdown.style.display = 'none';
                dropdown.innerHTML = '';
                return;
            }

            // Si ya está en la caché del navegador, mostrarlo al instante
            const cacheKey = query.toLowerCase();
            if (clientSearchCache[cacheKey]) {
                if (searchAbortCtrl) {
                    searchAbortCtrl.abort();
                }
                renderSearchResults(clientSearchCache[cacheKey]);
                return;
            }

            // Debounce para evitar sobrecargar conexiones MySQL mientras el usuario escribe
            searchTimer = setTimeout(() => {
                if (searchAbortCtrl) {
                    searchAbortCtrl.abort();
                }
                searchAbortCtrl = new AbortController();

                dropdown.innerHTML = '<div class="text-white-50 p-2 text-center small"><i class="fas fa-spinner fa-spin me-1"></i> Buscando productos...</div>';
                dropdown.style.display = 'block';

                // Timeout de seguridad (5 seg) para que nunca se quede colgado en "Buscando productos..."
                const timeoutId = setTimeout(() => {
                    if (searchAbortCtrl) {
                        searchAbortCtrl.abort();
                    }
                }, 5000);

                fetch('<?= base_url("admin/cuentas/buscar-productos") ?>?term=' + encodeURIComponent(query), {
                    signal: searchAbortCtrl.signal
                })
                    .then(res => {
                        clearTimeout(timeoutId);
                        if (!res.ok) {
                            throw new Error('Error de respuesta del servidor (' + res.status + ')');
                        }
                        return res.json();
                    })
                    .then(data => {
                        clientSearchCache[cacheKey] = data;
                        renderSearchResults(data);
                    })
                    .catch(err => {
                        clearTimeout(timeoutId);
                        if (err.name === 'AbortError') {
                            // Si fue abortado porque el usuario siguió escribiendo o por timeout
                            return;
                        }
                        console.error('Error buscando productos:', err);
                        dropdown.innerHTML = '<div class="text-white-50 p-2 text-center small">No se pudo obtener resultados en este momento</div>';
                        dropdown.style.display = 'block';
                    });
            }, 300);
        });

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener("DOMContentLoaded", initProductoSearchAutocomplete);
    } else {
        initProductoSearchAutocomplete();
    }

    let cuponesDisponiblesCompra = [];

    // Función para configurar y abrir el modal de registrar compra
    function abrirModalNuevaCompra(idCliente) {
        document.getElementById('modal_id_cliente').value = idCliente;
        document.getElementById('formNuevaCompra').reset();
        listaProductos = [];
        cuponesDisponiblesCompra = [];
        document.getElementById('beneficio_ninguno').checked = true;
        cambiarModoBeneficio('ninguno');
        document.getElementById('gastos_envio_compra').value = '0.00';
        renderListaProductos();
        toggleTipoForm('inventario');

        // Detectar cupones disponibles del cliente
        const selectCupon = document.getElementById('select_cupon_compra');
        const badgeCupones = document.getElementById('badge-cupones-detectados');
        const radioCupon = document.getElementById('beneficio_cupon');
        const lblTabCupon = document.getElementById('lbl-tab-cupon');

        selectCupon.innerHTML = '<option value="">Cargando cupones...</option>';
        badgeCupones.style.display = 'none';
        radioCupon.disabled = true;
        lblTabCupon.classList.add('disabled', 'opacity-50');

        fetch('<?= base_url("admin/cuentas/cupones/disponibles") ?>/' + idCliente)
            .then(r => r.json())
            .then(res => {
                cuponesDisponiblesCompra = (res && res.success && Array.isArray(res.cupones)) ? res.cupones : [];
                selectCupon.innerHTML = '<option value="">-- Selecciona un cupón --</option>';

                if (cuponesDisponiblesCompra.length > 0) {
                    badgeCupones.innerText = cuponesDisponiblesCompra.length + ' disponible(s)';
                    badgeCupones.style.display = 'inline-block';
                    radioCupon.disabled = false;
                    lblTabCupon.classList.remove('disabled', 'opacity-50');

                    cuponesDisponiblesCompra.forEach(c => {
                        const valStr = c.tipo_descuento === 'porcentaje'
                            ? parseFloat(c.valor).toFixed(2) + '%'
                            : '$' + parseFloat(c.valor).toFixed(2);
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = `${c.codigo} — ${c.concepto} (${valStr})`;
                        selectCupon.appendChild(opt);
                    });
                } else {
                    selectCupon.innerHTML = '<option value="">El cliente no tiene cupones disponibles</option>';
                }
                recalcularDesgloseCompra();
            })
            .catch(() => {
                selectCupon.innerHTML = '<option value="">No se pudieron cargar los cupones</option>';
            });

        const modal = new bootstrap.Modal(document.getElementById('modalNuevaCompra'));
        modal.show();
    }

    // Función para abrir el modal de edición de compras
    function abrirEditarCompra(compra) {
        const form = document.getElementById('formEditarCompra');
        const url = '<?= base_url("admin/cuentas/editar") ?>/' + compra.idCompra;
        form.action = url;
        form.setAttribute('hx-post', url);
        if (typeof htmx !== 'undefined') {
            htmx.process(form);
        }

        document.getElementById('edit-lbl-producto').innerText = (compra.codigo_sku ? '[' + compra.codigo_sku + '] ' : '') + compra.descProducto;
        document.getElementById('edit_fecha_compra').value = compra.fechaCompra || '';
        document.getElementById('edit_cantidad').value = compra.cantidad;
        document.getElementById('edit_precio_unit').value = compra.precioUnit;

        // Si es de inventario, habilitar switch de stock
        if (parseInt(compra.idInventario) > 0) {
            document.getElementById('edit-seccion-stock').style.display = 'block';
            document.getElementById('edit_descontar_stock').checked = true;
        } else {
            document.getElementById('edit-seccion-stock').style.display = 'none';
            document.getElementById('edit_descontar_stock').checked = false;
        }

        if (compra.estatusCompra == '1') {
            document.getElementById('edit_status_pagado').checked = true;
        } else {
            document.getElementById('edit_status_pendiente').checked = true;
        }

        calcularTotalEdit();

        const modal = new bootstrap.Modal(document.getElementById('modalEditarCompra'));
        modal.show();
    }

    // Escuchar el evento de toast del servidor para mostrar las notificaciones de éxito/error
    document.body.addEventListener('mostrarToast', function (evt) {
        const message = evt.detail.message || 'Operación exitosa';
        mostrarToastExito(message);
    });

    // Buscador en vivo de clientes en la barra lateral
    function initCuentasClientes() {
        // Escuchar cuando HTMX reemplace la lista de clientes para re-aplicar la clase active al cliente seleccionado
        document.body.addEventListener('htmx:afterSwap', function (evt) {
            if (evt.detail.target.id === 'lista-clientes') {
                if (window.activeClientId) {
                    const element = document.getElementById('client-item-' + window.activeClientId);
                    if (element) {
                        element.classList.add('active');
                    }
                }
            }
        });

        // Inicializar y mostrar toasts de notificaciones del servidor
        function inicializarToasts() {
            const toasts = document.querySelectorAll('.toast:not(.showing):not(.show)');
            toasts.forEach(toastEl => {
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
                toastEl.addEventListener('hidden.bs.toast', () => {
                    toastEl.remove();
                });
            });
        }
        inicializarToasts();

        // Formatear el ticket para mostrarlo con colores en la vista del modal
        function formatTicketForDisplay(text) {
            // Reemplazar asteriscos por span con color verde/azul
            let html = text.replace(/\*(.*?)\*/g, '<span style="color: #55efc4; font-weight: bold;">$1</span>');
            // Reemplazar montos por span con color cyan brillante (soportando comas en miles)
            html = html.replace(/(\$[\d,]+(?:\.\d{2})?)/g, '<span style="color: #00cec9; font-weight: bold;">$1</span>');
            return html;
        }

        // Variable local para guardar el saldo pendiente devuelto por ajax
        let nuevoSaldoPendiente = null;
        let esAbono = false;

        // Manejar el clic en el botón de agregar producto a la lista temporal
        const btnAgregarLista = document.getElementById('btn-agregar-producto-lista');
        if (btnAgregarLista) {
            btnAgregarLista.addEventListener('click', function () {
                const isInventario = document.getElementById('tipo_inventario').checked;
                const cantidad = parseInt(document.getElementById('cantidad').value) || 0;
                const precioUnit = parseFloat(document.getElementById('precio_unit').value) || 0;

                if (cantidad <= 0) {
                    alert('La cantidad debe ser mayor a 0.');
                    return;
                }
                if (precioUnit < 0) {
                    alert('El precio unitario no puede ser negativo.');
                    return;
                }

                let idInventario = 0;
                let descProducto = '';
                let descontarStock = false;

                if (isInventario) {
                    idInventario = parseInt(document.getElementById('modal_id_inventario').value) || 0;
                    const searchVal = document.getElementById('producto_search').value.trim();

                    if (idInventario <= 0 || !searchVal) {
                        alert('Por favor, busca y selecciona un producto del inventario.');
                        return;
                    }

                    // Extraer descripción limpia (quitar el prefijo del SKU si lo hay)
                    const hyphenIdx = searchVal.indexOf(' - ');
                    if (hyphenIdx !== -1) {
                        descProducto = searchVal.substring(hyphenIdx + 3).trim();
                    } else {
                        descProducto = searchVal;
                    }
                    descontarStock = document.getElementById('descontar_stock').checked;
                } else {
                    descProducto = document.getElementById('desc_producto').value.trim();
                    if (!descProducto) {
                        alert('Por favor, escribe una descripción para el producto libre.');
                        return;
                    }
                }

                // Agregar producto al array temporal
                listaProductos.push({
                    tipo_compra: isInventario ? 'inventario' : 'libre',
                    id_inventario: idInventario,
                    desc_producto: descProducto,
                    cantidad: cantidad,
                    precio_unit: precioUnit,
                    descontar_stock: descontarStock
                });

                // Renderizar la tabla de productos
                renderListaProductos();

                // Limpiar inputs de producto
                document.getElementById('producto_search').value = '';
                document.getElementById('modal_id_inventario').value = '0';
                document.getElementById('desc_producto').value = '';
                document.getElementById('cantidad').value = '1';
                document.getElementById('precio_unit').value = '0.00';
            });
        }

        // Manejar el submit del formulario de registro de compra por FETCH
        const formNuevaCompra = document.getElementById('formNuevaCompra');
        if (formNuevaCompra) {
            formNuevaCompra.addEventListener('submit', function (e) {
                e.preventDefault();

                if (listaProductos.length === 0) {
                    alert('Por favor, agrega al menos un producto a la lista antes de registrar.');
                    return;
                }

                const desglose = recalcularDesgloseCompra();
                if (!desglose.valido) {
                    alert(desglose.error || 'Verifica el cupón o descuento seleccionado antes de confirmar.');
                    return;
                }

                const btnSubmit = document.getElementById('btn-registrar-compra-submit');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Registrando...';
                }

                const idCliente = document.getElementById('modal_id_cliente').value;
                const fechaCompra = document.getElementById('fecha_compra').value;
                const estatusCompra = document.querySelector('input[name="estatus_compra"]:checked').value;

                const payload = {
                    id_cliente: idCliente,
                    fecha_compra: fechaCompra,
                    estatus_compra: estatusCompra,
                    productos: listaProductos,
                    gastos_envio: desglose.gastosEnvio,
                    beneficio: desglose.beneficio
                };

                fetch(formNuevaCompra.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => {
                    const contentType = res.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return res.json();
                    }
                    return res.text().then(text => {
                        throw new Error(text);
                    });
                })
                .then(data => {
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="fas fa-save me-1"></i> Registrar Compra';
                    }

                    if (data.success) {
                        // Ocultar modal de nueva compra
                        const modalNueva = bootstrap.Modal.getInstance(document.getElementById('modalNuevaCompra'));
                        if (modalNueva) modalNueva.hide();

                        // Guardar saldo de retorno
                        nuevoSaldoPendiente = data.totalPendiente;
                        esAbono = false;

                        // Cargar y formatear texto en el modal del ticket
                        document.getElementById('ticket-content').innerHTML = formatTicketForDisplay(data.ticket);

                        // Mostrar avisos informativos de dinámicas activas sin interferir
                        const avisosEl = document.getElementById('ticket-dinamicas-avisos');
                        if (avisosEl) {
                            const avisos = Array.isArray(data.dinamicas_avisos) ? data.dinamicas_avisos : [];
                            if (avisos.length > 0) {
                                avisosEl.innerHTML = avisos.map(a => {
                                    const cls = a.tipo === 'success' ? 'alert-success' : (a.tipo === 'warning' ? 'alert-warning' : 'alert-info');
                                    return `<div class="alert ${cls} py-2 px-3 small mb-2 d-flex align-items-center gap-2"><i class="bi bi-gift-fill"></i><span>${a.mensaje}</span></div>`;
                                }).join('');
                                avisosEl.classList.remove('d-none');
                            } else {
                                avisosEl.innerHTML = '';
                                avisosEl.classList.add('d-none');
                            }
                        }

                        // Guardar datos en los botones del ticket
                        const btnCopiar = document.getElementById('btn-copiar-resumen-ticket');
                        const btnWhatsApp = document.getElementById('btn-compartir-whatsapp-ticket');
                        if (btnCopiar) {
                            btnCopiar.setAttribute('data-ticket', data.ticket);
                        }
                        if (btnWhatsApp) {
                            btnWhatsApp.setAttribute('data-ticket', data.ticket);
                            btnWhatsApp.setAttribute('data-cel', data.cel || '');
                        }

                        // Abrir modal de ticket digital
                        const modalTicket = new bootstrap.Modal(document.getElementById('modalTicketDigital'));
                        modalTicket.show();
                    } else {
                        alert(data.message || 'Ocurrió un error al registrar la compra.');
                    }
                })
                .catch(err => {
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="fas fa-save me-1"></i> Registrar Compra';
                    }
                    console.error('Error submitting purchase:', err);
                    
                    const errorText = err.message || '';
                    if (errorText.startsWith('<!') || errorText.startsWith('<html') || errorText.includes('name="password"') || errorText.includes('<form')) {
                        alert('Tu sesión de usuario ha expirado o se produjo un error en el servidor. La página se recargará.');
                        window.location.reload();
                    } else {
                        alert('Error de conexión al registrar la compra.');
                    }
                });
            });
        }


        // Manejar el clic en el botón para copiar la información al portapapeles
        const btnCopiar = document.getElementById('btn-copiar-resumen-ticket');
        if (btnCopiar) {
            btnCopiar.addEventListener('click', function () {
                const text = this.getAttribute('data-ticket');
                if (!text) return;

                navigator.clipboard.writeText(text).then(() => {
                    // Feedback visual
                    const originalHTML = btnCopiar.innerHTML;
                    btnCopiar.innerHTML = '<i class="fas fa-check"></i> ¡Copiado!';
                    setTimeout(() => {
                        btnCopiar.innerHTML = originalHTML;
                    }, 2000);
                }).catch(err => {
                    console.error('Error al copiar:', err);
                    alert('No se pudo copiar el texto automáticamente. Por favor, selecciónalo manualmente.');
                });
            });
        }

        // Manejar el clic en el botón de WhatsApp para compartir el mensaje
        const btnWhatsApp = document.getElementById('btn-compartir-whatsapp-ticket');
        if (btnWhatsApp) {
            btnWhatsApp.addEventListener('click', function () {
                const text = this.getAttribute('data-ticket') || '';
                const cel = this.getAttribute('data-cel') || '';
                
                // Intentar copiar al portapapeles en segundo plano para mayor comodidad del usuario
                if (text) {
                    navigator.clipboard.writeText(text).catch(err => console.error('Error al copiar en segundo plano:', err));
                }

                // Limpieza de teléfono para WhatsApp
                const telClean = cel.replace(/[^0-9]/g, '');
                let phone = telClean;
                if (phone.length === 10) {
                    phone = '52' + phone;
                }

                // Generar URL de WhatsApp
                const waUrl = `https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(text)}`;
                
                // Abrir enlace en pestaña nueva
                window.open(waUrl, '_blank');
            });
        }

        // Actualizar la vista al cerrar el modal del ticket
        const modalTicketEl = document.getElementById('modalTicketDigital');
        if (modalTicketEl) {
            modalTicketEl.addEventListener('hidden.bs.modal', function () {
                const idCliente = document.getElementById('modal_id_cliente').value;
                if (idCliente) {
                    // Si fue un abono, la vista ya está actualizada mediante el submit HTMX.
                    // Retornamos temprano para evitar recargas duplicadas/redudantes.
                    if (esAbono) {
                        esAbono = false;
                        return;
                    }

                    // Actualizar badge de la barra lateral
                    if (nuevoSaldoPendiente !== null) {
                        const sidebarBadge = document.getElementById('sidebar-debt-' + idCliente);
                        if (sidebarBadge) {
                            sidebarBadge.innerText = '$' + nuevoSaldoPendiente;
                            if (parseFloat(nuevoSaldoPendiente.replace(/,/g, '')) > 0) {
                                sidebarBadge.className = 'badge bg-danger rounded-pill fw-bold';
                            } else {
                                sidebarBadge.className = 'badge bg-secondary rounded-pill';
                            }
                        }
                    }

                    // Disparar recarga de la tabla del cliente vía click/htmx
                    const clientItem = document.getElementById('client-item-' + idCliente);
                    if (clientItem) {
                        clientItem.click();
                    }
                }
            });
        }

        // Deshabilitar botón de guardar y mostrar spinner en formulario de registrar pago
        const formAbono = document.getElementById('formRegistrarAbono');
        if (formAbono) {
            formAbono.addEventListener('submit', function () {
                const btnSubmit = formAbono.querySelector('button[type="submit"]');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Registrando...';
                }
            });
        }

        // Spinner en formulario de editar compra
        const formEditarCompra = document.getElementById('formEditarCompra');
        if (formEditarCompra) {
            formEditarCompra.addEventListener('submit', function () {
                const btnSubmit = formEditarCompra.querySelector('button[type="submit"]');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando...';
                }
            });
        }

        // Spinner en formulario de editar cliente
        const formEditarCliente = document.getElementById('formEditarCliente');
        if (formEditarCliente) {
            formEditarCliente.addEventListener('submit', function () {
                const btnSubmit = formEditarCliente.querySelector('button[type="submit"]');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando...';
                }
            });
        }

        // Spinner en formulario de confirmar eliminación
        const formConfirmarEliminar = document.getElementById('formConfirmarEliminar');
        if (formConfirmarEliminar) {
            formConfirmarEliminar.addEventListener('submit', function () {
                const btnSubmit = formConfirmarEliminar.querySelector('button[type="submit"]');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Eliminando...';
                }
            });
        }

        // Escuchar el evento abonoRegistrado disparado por HTMX
        document.body.addEventListener("abonoRegistrado", function (evt) {
            const ticket = evt.detail.ticket;
            const cel = evt.detail.cel;
            if (!ticket) return;

            // Limpiar nuevoSaldoPendiente para evitar usar valores antiguos
            nuevoSaldoPendiente = null;
            esAbono = true;

            // Cargar y formatear texto en el modal del ticket
            const ticketContentEl = document.getElementById('ticket-content');
            if (ticketContentEl) {
                ticketContentEl.innerHTML = formatTicketForDisplay(ticket);
            }

            // Guardar datos en los botones del ticket
            const btnCopiar = document.getElementById('btn-copiar-resumen-ticket');
            const btnWhatsApp = document.getElementById('btn-compartir-whatsapp-ticket');
            if (btnCopiar) {
                btnCopiar.setAttribute('data-ticket', ticket);
            }
            if (btnWhatsApp) {
                btnWhatsApp.setAttribute('data-ticket', ticket);
                btnWhatsApp.setAttribute('data-cel', cel || '');
            }

            // Abrir modal de ticket digital
            const modalEl = document.getElementById('modalTicketDigital');
            if (modalEl) {
                const modalTicket = new bootstrap.Modal(modalEl);
                modalTicket.show();
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener("DOMContentLoaded", initCuentasClientes);
    } else {
        initCuentasClientes();
    }

    // Función para abrir el modal de editar cliente
    function abrirModalEditarCliente(cliente) {
        const form = document.getElementById('formEditarCliente');
        const url = '<?= base_url("admin/cuentas/editar-cliente") ?>/' + cliente.idCliente;
        form.action = url;
        form.setAttribute('hx-post', url);
        if (typeof htmx !== 'undefined') {
            htmx.process(form);
        }
        document.getElementById('edit_cliente_nombre').value = cliente.nombre;
        document.getElementById('edit_cliente_cel').value = cliente.cel || '';
        document.getElementById('edit_cliente_tipo').value = cliente.tipoCliente || '';

        const modal = new bootstrap.Modal(document.getElementById('modalEditarCliente'));
        modal.show();
    }

    // Función para abrir el modal de confirmar eliminación de compra
    function confirmarEliminarCompra(url) {
        const form = document.getElementById('formConfirmarEliminar');
        if (form) {
            form.action = url;
            form.setAttribute('hx-post', url);
            if (typeof htmx !== 'undefined') {
                htmx.process(form);
            }
        }
        const modal = new bootstrap.Modal(document.getElementById('modalConfirmarEliminar'));
        modal.show();
    }

    // Función para abrir el modal de registrar pago / abono
    function abrirModalRegistrarAbono(idCliente, saldoPendiente) {
        document.getElementById('abono_id_cliente').value = idCliente;
        const modalIdCliente = document.getElementById('modal_id_cliente');
        if (modalIdCliente) {
            modalIdCliente.value = idCliente;
        }
        document.getElementById('abono_monto').value = parseFloat(saldoPendiente).toFixed(2);
        document.getElementById('abono_monto_help').innerText = `Monto total pendiente: $${parseFloat(saldoPendiente).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

        const modal = new bootstrap.Modal(document.getElementById('modalRegistrarAbono'));
        modal.show();
    }

    // Manejar el cierre de modales y feedback al completar peticiones HTMX
    document.addEventListener("htmx:afterOnLoad", function (evt) {
        const targetId = evt.detail.elt.id;

        if (evt.detail.xhr.status === 200) {
            let modalId = null;
            let successMessage = "";
            let btnRestoreHTML = null;
            let btnRestoreSelector = null;

            if (targetId === "formEditarCompra") {
                modalId = "modalEditarCompra";
                successMessage = "Compra modificada con éxito.";
                btnRestoreHTML = '<i class="fas fa-save me-1"></i> Guardar Cambios';
                btnRestoreSelector = '#formEditarCompra button[type="submit"]';
            } else if (targetId === "formRegistrarAbono") {
                modalId = "modalRegistrarAbono";
                successMessage = "Abono registrado con éxito.";
                document.getElementById("abono_monto").value = "";
                btnRestoreHTML = '<i class="fas fa-save me-1"></i> Registrar Pago';
                btnRestoreSelector = '#formRegistrarAbono button[type="submit"]';
            } else if (targetId === "formConfirmarEliminar") {
                modalId = "modalConfirmarEliminar";
                successMessage = "Registro de compra eliminado.";
                btnRestoreHTML = 'Eliminar';
                btnRestoreSelector = '#formConfirmarEliminar button[type="submit"]';
            } else if (targetId === "formEditarCliente") {
                modalId = "modalEditarCliente";
                successMessage = "Datos del cliente actualizados.";
                btnRestoreHTML = '<i class="fas fa-save me-1"></i> Guardar Cambios';
                btnRestoreSelector = '#formEditarCliente button[type="submit"]';
            } else if (targetId === "formNuevoCliente") {
                modalId = "modalNuevoCliente";
                successMessage = "Cliente registrado con éxito.";
                btnRestoreHTML = '<i class="fas fa-save me-1"></i> Registrar Cliente';
                btnRestoreSelector = '#formNuevoCliente button[type="submit"]';
                
                // Limpiar campos del formulario
                const nombreInput = document.getElementById("nuevo_cliente_nombre");
                const celInput = document.getElementById("nuevo_cliente_cel");
                const tipoInput = document.getElementById("nuevo_cliente_tipo");
                if (nombreInput) nombreInput.value = "";
                if (celInput) celInput.value = "";
                if (tipoInput) tipoInput.value = "";
            }

            // Restaurar botón de submit (quitar spinner y re-habilitar)
            if (btnRestoreSelector && btnRestoreHTML) {
                const btn = document.querySelector(btnRestoreSelector);
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = btnRestoreHTML;
                }
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

            if (successMessage) {
                mostrarToastExito(successMessage);
            }
        }
    });

    // Escuchar errores de respuesta de HTMX
    document.addEventListener("htmx:responseError", function (evt) {
        const xhr = evt.detail.xhr;
        const msg = xhr.responseText || "Ocurrió un error inesperado.";
        mostrarToastError(msg);

        // Restaurar botón de submit del formulario si aplica
        const targetId = evt.detail.elt.id;
        if (targetId === "formNuevoCliente") {
            const btn = document.querySelector('#formNuevoCliente button[type="submit"]');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i> Registrar Cliente';
            }
        }
    });

    // Función para mostrar toasts flotantes dinámicamente
    function mostrarToastExito(message) {
        const container = document.querySelector(".toast-container");
        if (!container) return;

        const toastHtml = `
            <div class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3 show"
                role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                       <i class="fas fa-check-circle text-success fs-5"></i>
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

    // Función para mostrar toasts flotantes dinámicamente de error
    function mostrarToastError(message) {
        const container = document.querySelector(".toast-container");
        if (!container) return;

        const toastHtml = `
            <div class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3 show"
                role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                       <i class="fas fa-exclamation-circle text-danger fs-5"></i>
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

    function copiarEstadoCuenta(btn) {
        const text = btn.getAttribute('data-mensaje');
        if (!text) return;

        navigator.clipboard.writeText(text).then(() => {
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> ¡Copiado!';
            
            // Cambiar temporalmente a estilos de éxito
            const originalClass = btn.className;
            btn.className = btn.className.replace('btn-outline-success', 'btn-success text-white');

            mostrarToastExito('Estado de cuenta copiado al portapapeles.');

            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.className = originalClass;
            }, 2000);
        }).catch(err => {
            console.error('Error al copiar:', err);
            // Fallback manual con textarea si falla
            try {
                const textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.cssText = "position:fixed;top:-9999px;left:-9999px;width:1px;height:1px;opacity:0;";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                const successful = document.execCommand('copy');
                document.body.removeChild(textArea);
                if (successful) {
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-check"></i> ¡Copiado!';
                    const originalClass = btn.className;
                    btn.className = btn.className.replace('btn-outline-success', 'btn-success text-white');
                    
                    mostrarToastExito('Estado de cuenta copiado al portapapeles.');
                    
                    setTimeout(() => {
                        btn.innerHTML = originalHTML;
                        btn.className = originalClass;
                    }, 2000);
                } else {
                    alert('No se pudo copiar el texto automáticamente. Por favor, selecciónalo manualmente.');
                }
            } catch (fallbackErr) {
                alert('No se pudo copiar el texto automáticamente.');
            }
        });
    }

    // =========================================================================
    // FUNCIONES DE CUPONES Y DESCUENTOS
    // =========================================================================

    function cambiarModoBeneficio(modo) {
        document.getElementById('panel-beneficio-cupon').style.display = (modo === 'cupon') ? 'block' : 'none';
        document.getElementById('panel-beneficio-manual').style.display = (modo === 'manual') ? 'block' : 'none';
        recalcularDesgloseCompra();
    }

    function recalcularDesgloseCompra() {
        let subtotal = 0.0;
        listaProductos.forEach(p => {
            subtotal += (parseInt(p.cantidad, 10) || 0) * (parseFloat(p.precio_unit) || 0);
        });
        subtotal = Math.round(subtotal * 100) / 100;

        const gastosEnvioEl = document.getElementById('gastos_envio_compra');
        const gastosEnvio = Math.max(0, Math.round((parseFloat(gastosEnvioEl ? gastosEnvioEl.value : 0) || 0) * 100) / 100);

        const modoEl = document.querySelector('input[name="modo_beneficio"]:checked');
        const modo = modoEl ? modoEl.value : 'ninguno';

        const alertaEl = document.getElementById('alerta-beneficio-compra');
        const infoCuponEl = document.getElementById('info-cupon-seleccionado');
        const rowDescEl = document.getElementById('row-descuento-compra');
        const rowEnvioEl = document.getElementById('row-envio-compra');

        let descuento = 0.0;
        let valido = true;
        let errorMsg = '';
        let conceptoDesc = 'Descuento aplicado:';
        let beneficio = { modo: 'ninguno' };

        if (alertaEl) {
            alertaEl.style.display = 'none';
            alertaEl.innerText = '';
        }
        if (infoCuponEl) infoCuponEl.style.display = 'none';

        if (modo === 'cupon') {
            const idCupon = parseInt(document.getElementById('select_cupon_compra').value || '0', 10);
            const cupon = cuponesDisponiblesCompra.find(c => parseInt(c.id, 10) === idCupon);

            if (!cupon) {
                valido = false;
                errorMsg = 'Selecciona un cupón disponible de la lista o elige "Sin beneficio".';
            } else {
                const valCupon = parseFloat(cupon.valor) || 0;
                const minCompra = parseFloat(cupon.compra_minima) || 0;
                const maxDesc = parseFloat(cupon.descuento_maximo) || 0;
                const limiteUsos = parseInt(cupon.limite_usos, 10) || 1;

                if (infoCuponEl) {
                    const valTxt = cupon.tipo_descuento === 'porcentaje' ? valCupon.toFixed(2) + '%' : '$' + valCupon.toFixed(2);
                    const minTxt = minCompra > 0 ? 'Compra mín: $' + minCompra.toFixed(2) : 'Sin mínimo';
                    const vigTxt = cupon.fecha_vencimiento ? 'Vence: ' + cupon.fecha_vencimiento : 'Sin vencimiento';
                    infoCuponEl.innerHTML = `<strong class="text-info">${cupon.codigo}</strong> · ${cupon.concepto} · Valor: <strong>${valTxt}</strong> · ${minTxt} · ${vigTxt}`;
                    infoCuponEl.style.display = 'block';
                }

                if (subtotal <= 0) {
                    valido = false;
                    errorMsg = 'Agrega productos a la compra para calcular el descuento del cupón.';
                } else if (minCompra > 0 && subtotal < minCompra) {
                    valido = false;
                    errorMsg = `La compra requiere un mínimo de $${minCompra.toFixed(2)} para usar este cupón.`;
                } else if (cupon.tipo_descuento === 'fijo') {
                    if (limiteUsos === 1 && valCupon > subtotal) {
                        valido = false;
                        errorMsg = `El cupón ($${valCupon.toFixed(2)}) supera el subtotal elegible ($${subtotal.toFixed(2)}). Conserva este cupón de un solo uso para una compra posterior.`;
                    } else {
                        descuento = Math.min(valCupon, subtotal);
                    }
                } else if (cupon.tipo_descuento === 'porcentaje') {
                    descuento = Math.round(subtotal * (valCupon / 100) * 100) / 100;
                    if (maxDesc > 0) descuento = Math.min(descuento, maxDesc);
                    descuento = Math.min(descuento, subtotal);
                }

                if (valido) {
                    conceptoDesc = `Cupón ${cupon.codigo}:`;
                    beneficio = { modo: 'cupon', id_cupon: idCupon };
                }
            }
        } else if (modo === 'manual') {
            const tipoManual = document.getElementById('manual_tipo_desc').value;
            const valorManual = parseFloat(document.getElementById('manual_valor_desc').value) || 0;
            const motivoManual = (document.getElementById('manual_motivo_desc').value || '').trim();

            if (subtotal <= 0) {
                valido = false;
                errorMsg = 'Agrega productos antes de aplicar un descuento manual.';
            } else if (valorManual <= 0) {
                valido = false;
                errorMsg = 'Ingresa un valor mayor a 0 para el descuento manual.';
            } else if (motivoManual === '') {
                valido = false;
                errorMsg = 'Debes especificar el motivo obligatorio para el descuento manual.';
            } else if (tipoManual === 'fijo') {
                if (valorManual > subtotal) {
                    valido = false;
                    errorMsg = `El descuento manual no puede superar el subtotal ($${subtotal.toFixed(2)}).`;
                } else {
                    descuento = valorManual;
                }
            } else if (tipoManual === 'porcentaje') {
                if (valorManual > 100) {
                    valido = false;
                    errorMsg = 'El porcentaje no puede ser mayor a 100%.';
                } else {
                    descuento = Math.round(subtotal * (valorManual / 100) * 100) / 100;
                }
            }

            if (valido) {
                conceptoDesc = `Descuento manual (${motivoManual}):`;
                beneficio = {
                    modo: 'manual',
                    tipo_manual: tipoManual,
                    valor_manual: valorManual,
                    motivo_manual: motivoManual
                };
            }
        }

        if (!valido && errorMsg && alertaEl && listaProductos.length > 0) {
            alertaEl.innerText = errorMsg;
            alertaEl.style.display = 'block';
        }

        descuento = Math.round(descuento * 100) / 100;
        const totalNeto = Math.max(0, Math.round((subtotal - descuento + gastosEnvio) * 100) / 100);

        document.getElementById('lbl-subtotal-compra').innerText = '$' + subtotal.toFixed(2);
        if (descuento > 0) {
            rowDescEl.style.setProperty('display', 'flex', 'important');
            document.getElementById('lbl-descuento-concepto').innerText = conceptoDesc;
            document.getElementById('lbl-descuento-compra').innerText = '-$' + descuento.toFixed(2);
        } else {
            rowDescEl.style.setProperty('display', 'none', 'important');
        }

        if (gastosEnvio > 0) {
            rowEnvioEl.style.setProperty('display', 'flex', 'important');
            document.getElementById('lbl-envio-compra').innerText = '$' + gastosEnvio.toFixed(2);
        } else {
            rowEnvioEl.style.setProperty('display', 'none', 'important');
        }

        document.getElementById('lbl-total-calculado').innerText = '$' + totalNeto.toFixed(2);

        return { valido, error: errorMsg, subtotal, descuento, gastosEnvio, totalNeto, beneficio };
    }

    function irASeccionCupones() {
        const seccion = document.getElementById('seccion-cupones-cliente');
        const collapseEl = document.getElementById('collapseCuponesCliente');
        if (collapseEl && !collapseEl.classList.contains('show')) {
            new bootstrap.Collapse(collapseEl, { toggle: true });
        }
        if (seccion) {
            seccion.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function toggleCamposTipoCupon() {
        const tipo = document.getElementById('cupon_tipo').value;
        const grupoMax = document.getElementById('grupo_cupon_descuento_maximo');
        const lblValor = document.getElementById('lbl_cupon_valor');
        if (tipo === 'porcentaje') {
            grupoMax.style.display = 'block';
            lblValor.innerText = 'Porcentaje de descuento (%) *';
        } else {
            grupoMax.style.display = 'none';
            document.getElementById('cupon_descuento_maximo').value = '';
            lblValor.innerText = 'Valor del descuento ($) *';
        }
    }

    function abrirModalNuevoCupon(idCliente, nombreCliente) {
        document.getElementById('formCupon').reset();
        document.getElementById('cupon_id').value = '';
        document.getElementById('cupon_id_cliente').value = idCliente;
        document.getElementById('cupon_nombre_cliente').value = nombreCliente;
        document.getElementById('cupon_fecha_emision').value = '<?= date('Y-m-d') ?>';
        document.getElementById('cupon_fecha_inicio').value = '<?= date('Y-m-d') ?>';
        document.getElementById('cupon_limite_usos').value = '1';
        document.getElementById('cupon_estado_preview').value = 'Automático según vigencia y usos';
        document.getElementById('modalCuponLabel').innerHTML = '<i class="fas fa-ticket-alt me-2 text-info"></i> Nuevo Cupón';
        toggleCamposTipoCupon();
        generarCodigoCupon();
        new bootstrap.Modal(document.getElementById('modalCupon')).show();
    }

    function abrirModalEditarCupon(cupon, nombreCliente) {
        document.getElementById('formCupon').reset();
        document.getElementById('cupon_id').value = cupon.id;
        document.getElementById('cupon_id_cliente').value = cupon.id_cliente;
        document.getElementById('cupon_nombre_cliente').value = nombreCliente;
        document.getElementById('cupon_codigo').value = cupon.codigo;
        document.getElementById('cupon_concepto').value = cupon.concepto;
        document.getElementById('cupon_tipo').value = cupon.tipo_descuento;
        document.getElementById('cupon_valor').value = cupon.valor;
        document.getElementById('cupon_limite_usos').value = cupon.limite_usos;
        document.getElementById('cupon_compra_minima').value = cupon.compra_minima || '';
        document.getElementById('cupon_descuento_maximo').value = cupon.descuento_maximo || '';
        document.getElementById('cupon_fecha_emision').value = cupon.fecha_emision;
        document.getElementById('cupon_fecha_inicio').value = cupon.fecha_inicio;
        document.getElementById('cupon_fecha_vencimiento').value = cupon.fecha_vencimiento || '';
        document.getElementById('cupon_motivo_origen').value = cupon.motivo_origen || '';
        document.getElementById('cupon_observaciones').value = cupon.observaciones || '';
        document.getElementById('cupon_estado_preview').value = (cupon.estado_resuelto || cupon.estado || '').toUpperCase();
        document.getElementById('modalCuponLabel').innerHTML = '<i class="fas fa-edit me-2 text-warning"></i> Editar Cupón';
        toggleCamposTipoCupon();
        new bootstrap.Modal(document.getElementById('modalCupon')).show();
    }

    function generarCodigoLocal(prefijo = 'CUP') {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        let rand = '';
        for (let i = 0; i < 6; i++) {
            rand += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return prefijo + '-' + rand;
    }

    function generarCodigoCupon() {
        const inputCodigo = document.getElementById('cupon_codigo');
        fetch('<?= base_url("admin/cuentas/cupones/generar-codigo") ?>', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(r => {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(res => {
                if (res && res.success && res.codigo) {
                    inputCodigo.value = res.codigo;
                } else {
                    inputCodigo.value = generarCodigoLocal('CUP');
                }
            })
            .catch(() => {
                inputCodigo.value = generarCodigoLocal('CUP');
            });
    }

    function recargarPanelCliente(idCliente) {
        const clientItem = document.getElementById('client-item-' + idCliente);
        if (clientItem) {
            clientItem.click();
        }
    }

    function guardarCupon(e) {
        e.preventDefault();
        const idCupon = document.getElementById('cupon_id').value;
        const idCliente = document.getElementById('cupon_id_cliente').value;
        const btn = document.getElementById('btn-guardar-cupon');
        btn.disabled = true;

        const payload = {
            id_cliente: idCliente,
            codigo: document.getElementById('cupon_codigo').value.trim(),
            concepto: document.getElementById('cupon_concepto').value.trim(),
            tipo_descuento: document.getElementById('cupon_tipo').value,
            valor: document.getElementById('cupon_valor').value,
            limite_usos: document.getElementById('cupon_limite_usos').value,
            compra_minima: document.getElementById('cupon_compra_minima').value,
            descuento_maximo: document.getElementById('cupon_descuento_maximo').value,
            fecha_emision: document.getElementById('cupon_fecha_emision').value,
            fecha_inicio: document.getElementById('cupon_fecha_inicio').value,
            fecha_vencimiento: document.getElementById('cupon_fecha_vencimiento').value,
            motivo_origen: document.getElementById('cupon_motivo_origen').value.trim(),
            observaciones: document.getElementById('cupon_observaciones').value.trim()
        };

        const url = idCupon
            ? '<?= base_url("admin/cuentas/cupones/editar") ?>/' + idCupon
            : '<?= base_url("admin/cuentas/cupones/crear") ?>';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            if (res && res.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalCupon'));
                if (modal) modal.hide();
                mostrarToastExito(res.message || 'Cupón guardado con éxito.');
                recargarPanelCliente(idCliente);
            } else {
                alert((res && res.message) || 'No se pudo guardar el cupón.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            alert('Error de comunicación al guardar el cupón.');
        });
    }

    function verDetalleCupon(idCupon) {
        const body = document.getElementById('detalle-cupon-body');
        body.innerHTML = '<div class="text-center py-4 text-white-50"><div class="spinner-border text-info"></div></div>';
        new bootstrap.Modal(document.getElementById('modalDetalleCupon')).show();

        fetch('<?= base_url("admin/cuentas/cupones/detalle") ?>/' + idCupon)
            .then(r => r.json())
            .then(res => {
                if (!res || !res.success) {
                    body.innerHTML = '<div class="alert alert-danger">No se pudo cargar el detalle del cupón.</div>';
                    return;
                }
                const c = res.cupon;
                const valTxt = c.tipo_descuento === 'porcentaje' ? parseFloat(c.valor).toFixed(2) + '%' : '$' + parseFloat(c.valor).toFixed(2);
                let usosHtml = '<p class="text-white-50 small mb-0">Sin usos registrados.</p>';
                if (res.usos && res.usos.length > 0) {
                    usosHtml = '<ul class="list-group list-group-flush">' + res.usos.map(u => `
                        <li class="list-group-item bg-transparent text-white border-secondary border-opacity-25 px-0 py-2 small">
                            <span class="badge ${u.estado === 'aplicado' ? 'bg-success' : 'bg-secondary'} me-1">${u.estado.toUpperCase()}</span>
                            <strong>${u.folio_compra}</strong> · ${u.fecha_uso} · Descuento: <strong class="text-info">-$${parseFloat(u.descuento_aplicado).toFixed(2)}</strong>
                            (Subtotal: $${parseFloat(u.subtotal_original).toFixed(2)} → Neto: $${parseFloat(u.total_neto).toFixed(2)})
                        </li>
                    `).join('') + '</ul>';
                }

                let bitacoraHtml = '<p class="text-white-50 small mb-0">Sin movimientos en bitácora.</p>';
                if (res.bitacora && res.bitacora.length > 0) {
                    bitacoraHtml = '<ul class="list-group list-group-flush">' + res.bitacora.map(b => `
                        <li class="list-group-item bg-transparent text-white-50 border-secondary border-opacity-25 px-0 py-2 small">
                            <span class="badge bg-dark border border-secondary text-white me-1">${b.accion}</span>
                            <span class="text-white">${b.detalle || ''}</span>
                            <div class="text-muted mt-0.5" style="font-size: 0.72rem;">${b.creado_en} · Por: ${b.usuario_nombre || 'Sistema'}</div>
                        </li>
                    `).join('') + '</ul>';
                }

                body.innerHTML = `
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-4"><small class="text-white-50 d-block">Código</small><strong class="text-info font-monospace fs-5">${c.codigo}</strong></div>
                        <div class="col-6 col-md-4"><small class="text-white-50 d-block">Estado</small><span class="badge bg-info text-dark text-uppercase">${c.estado_resuelto}</span></div>
                        <div class="col-12 col-md-4"><small class="text-white-50 d-block">Valor</small><strong class="text-white fs-5">${valTxt}</strong></div>
                        <div class="col-12"><small class="text-white-50 d-block">Concepto</small><span class="text-white">${c.concepto}</span></div>
                        <div class="col-6 col-md-4"><small class="text-white-50 d-block">Emisión</small><span>${c.fecha_emision}</span></div>
                        <div class="col-6 col-md-4"><small class="text-white-50 d-block">Vigencia</small><span>${c.fecha_inicio} a ${c.fecha_vencimiento || 'Sin vencimiento'}</span></div>
                        <div class="col-6 col-md-4"><small class="text-white-50 d-block">Usos</small><span>${c.usos_realizados} / ${c.limite_usos}</span></div>
                        ${c.motivo_origen ? `<div class="col-12"><small class="text-white-50 d-block">Motivo / Origen</small><span>${c.motivo_origen}</span></div>` : ''}
                        ${c.observaciones ? `<div class="col-12"><small class="text-white-50 d-block">Observaciones</small><span>${c.observaciones}</span></div>` : ''}
                    </div>
                    <h6 class="fw-bold text-info border-top border-secondary border-opacity-25 pt-3">Historial de usos</h6>
                    ${usosHtml}
                    <h6 class="fw-bold text-warning border-top border-secondary border-opacity-25 pt-3 mt-3">Bitácora de auditoría</h6>
                    ${bitacoraHtml}
                `;
            })
            .catch(() => {
                body.innerHTML = '<div class="alert alert-danger">Error al consultar el detalle.</div>';
            });
    }

    function cancelarCupon(idCupon, codigo) {
        document.getElementById('cancelar_cupon_id').value = idCupon;
        document.getElementById('cancelar_cupon_codigo').innerText = codigo;
        document.getElementById('cancelar_cupon_motivo').value = '';
        new bootstrap.Modal(document.getElementById('modalCancelarCupon')).show();
    }

    function confirmarCancelarCupon() {
        const idCupon = document.getElementById('cancelar_cupon_id').value;
        const motivo = document.getElementById('cancelar_cupon_motivo').value.trim();
        const btn = document.getElementById('btn-confirmar-cancelar-cupon');
        btn.disabled = true;

        fetch('<?= base_url("admin/cuentas/cupones/cancelar") ?>/' + idCupon, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ motivo })
        })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            if (res && res.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalCancelarCupon'));
                if (modal) modal.hide();
                mostrarToastExito(res.message || 'Cupón cancelado.');
                if (window.activeClientId) recargarPanelCliente(window.activeClientId);
            } else {
                alert((res && res.message) || 'No se pudo cancelar el cupón.');
            }
        })
        .catch(() => {
            btn.disabled = false;
            alert('Error de conexión al cancelar el cupón.');
        });
    }

    function copiarCodigoCupon(codigo, btn) {
        navigator.clipboard.writeText(codigo).then(() => {
            mostrarToastExito('Código ' + codigo + ' copiado al portapapeles.');
        }).catch(() => {});
    }

    function filtrarTablaCupones() {
        const estado = document.getElementById('filtro-cupon-estado').value;
        const desde = document.getElementById('filtro-cupon-desde').value;
        const hasta = document.getElementById('filtro-cupon-hasta').value;
        const rows = document.querySelectorAll('#tabla-cupones-cliente tbody tr.cupon-row');

        rows.forEach(tr => {
            const rowEstado = tr.getAttribute('data-estado') || '';
            const rowEmision = tr.getAttribute('data-emision') || '';
            let visible = true;

            if (estado !== 'todos' && rowEstado !== estado) visible = false;
            if (desde && rowEmision < desde) visible = false;
            if (hasta && rowEmision > hasta) visible = false;

            tr.style.display = visible ? '' : 'none';
        });
    }

    function limpiarFiltrosCupones() {
        document.getElementById('filtro-cupon-estado').value = 'todos';
        document.getElementById('filtro-cupon-desde').value = '';
        document.getElementById('filtro-cupon-hasta').value = '';
        filtrarTablaCupones();
    }

    // Exportar funciones globales al objeto window para acceso desde atributos HTML
    window.seleccionarCliente = seleccionarCliente;
    window.toggleTipoForm = toggleTipoForm;
    window.renderListaProductos = renderListaProductos;
    window.eliminarProductoLista = eliminarProductoLista;
    window.calcularTotal = calcularTotal;
    window.calcularTotalEdit = calcularTotalEdit;
    window.abrirModalNuevaCompra = abrirModalNuevaCompra;
    window.abrirEditarCompra = abrirEditarCompra;
    window.abrirModalEditarCliente = abrirModalEditarCliente;
    window.confirmarEliminarCompra = confirmarEliminarCompra;
    window.abrirModalRegistrarAbono = abrirModalRegistrarAbono;
    window.mostrarToastExito = mostrarToastExito;
    window.mostrarToastError = mostrarToastError;
    window.copiarEstadoCuenta = copiarEstadoCuenta;
    window.cambiarModoBeneficio = cambiarModoBeneficio;
    window.recalcularDesgloseCompra = recalcularDesgloseCompra;
    window.irASeccionCupones = irASeccionCupones;
    window.toggleCamposTipoCupon = toggleCamposTipoCupon;
    window.abrirModalNuevoCupon = abrirModalNuevoCupon;
    window.abrirModalEditarCupon = abrirModalEditarCupon;
    window.generarCodigoCupon = generarCodigoCupon;
    window.guardarCupon = guardarCupon;
    window.verDetalleCupon = verDetalleCupon;
    window.cancelarCupon = cancelarCupon;
    window.confirmarCancelarCupon = confirmarCancelarCupon;
    window.copiarCodigoCupon = copiarCodigoCupon;
    window.filtrarTablaCupones = filtrarTablaCupones;
    window.limpiarFiltrosCupones = limpiarFiltrosCupones;
})();
</script>
<?= $this->endSection() ?>