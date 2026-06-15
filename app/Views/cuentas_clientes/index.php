<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link class="styles-admin-theme" rel="stylesheet" href="<?= base_url('css/admin.css?v=1.2') ?>">
<div class="container py-4">
    <!-- Encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 admin-title">Cuentas Corrientes de Clientes</h2>
            <p class="text-muted mb-0">Lleva el control de adeudos, abonos y ventas de forma libre o de inventario para
                tus clientes fijos.</p>
            <div class="admin-subtitle-line" style="background-color: #28a745;"></div>
        </div>
        <div>
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
                <h5 class="fw-bold text-white mb-3"><i class="fas fa-users text-success me-2"></i> Clientes Fijos</h5>

                <!-- Buscador de Clientes -->
                <form id="form-buscador-clientes" action="<?= base_url('admin/cuentas') ?>" method="GET" class="mb-3"
                    hx-get="<?= base_url('admin/cuentas') ?>"
                    hx-target="#lista-clientes"
                    hx-select="#lista-clientes"
                    hx-swap="outerHTML">
                    <div class="input-group">
                        <input type="text" id="search-cliente" name="q"
                            class="form-control bg-dark text-white border-secondary" placeholder="Buscar cliente..."
                            value="<?= esc($q) ?>" autocomplete="off"
                            hx-get="<?= base_url('admin/cuentas') ?>"
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
                        <table class="table table-hover align-middle mb-0" style="background-color: rgba(0,0,0,0.15);">
                            <thead class="text-white-50 small" style="background-color: rgba(255,255,255,0.02);">
                                <tr>
                                    <th class="ps-3 py-2">Producto / Detalle</th>
                                    <th class="py-2 text-center" style="width: 100px;">Cant.</th>
                                    <th class="py-2 text-end" style="width: 120px;">Precio U.</th>
                                    <th class="py-2 text-end" style="width: 120px;">Subtotal</th>
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

                    <!-- Gran Total Calculado -->
                    <div
                        class="p-3 rounded bg-dark border border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                        <span class="small text-white-50 fw-bold">TOTAL ESTIMADO COMPRA:</span>
                        <span class="fs-4 fw-bold text-success" id="lbl-total-calculado">$0.00</span>
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

                <div class="p-4 rounded-3 mb-4 text-start"
                    style="background-color: #1a2238; border: 1px solid rgba(255,255,255,0.05); font-family: 'Courier New', Courier, monospace; font-size: 0.95rem; line-height: 1.6;">
                    <div id="ticket-content" style="white-space: pre-wrap; color: #ffffff;"></div>
                </div>

                <button type="button"
                    class="btn w-100 py-3 rounded-pill fw-bold text-dark d-flex align-items-center justify-content-center gap-2 mb-3"
                    id="btn-copiar-whatsapp"
                    style="background: linear-gradient(135deg, #2ce3f6 0%, #0072ff 100%); border: none; font-size: 1.1rem; transition: transform 0.2s, box-shadow 0.2s; color: #121824 !important;">
                    <i class="fas fa-copy"></i> WhatsApp
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
            document.getElementById('lbl-total-calculado').innerText = '$0.00';
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
                    <div class="fw-semibold" style="font-size: 0.9rem;">${prod.desc_producto}</div>
                    ${prod.id_inventario > 0 ? `<small class="text-success" style="font-size: 0.75rem;"><i class="bi bi-box-seam me-1"></i>Inventario${prod.descontar_stock ? ' (Descuenta Stock)' : ''}</small>` : '<small class="text-white-50" style="font-size: 0.75rem;"><i class="bi bi-pen me-1"></i>Formato Libre</small>'}
                </td>
                <td class="py-2 text-center fw-semibold">${prod.cantidad}</td>
                <td class="py-2 text-end text-white-50">$${prod.precio_unit.toFixed(2)}</td>
                <td class="py-2 text-end fw-bold text-success">$${subtotal.toFixed(2)}</td>
                <td class="py-2 text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm border-0 p-1" onclick="eliminarProductoLista(${index})">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('lbl-total-calculado').innerText = '$' + granTotal.toFixed(2);
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

        input.addEventListener('input', function () {
            const query = input.value.trim();
            if (query.length < 2) {
                dropdown.style.display = 'none';
                return;
            }

            fetch('<?= base_url("admin/cuentas/buscar-productos") ?>?term=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(data => {
                    dropdown.innerHTML = '';
                    if (data.length === 0) {
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
                });
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

    // Función para configurar y abrir el modal de registrar compra
    function abrirModalNuevaCompra(idCliente) {
        document.getElementById('modal_id_cliente').value = idCliente;
        document.getElementById('formNuevaCompra').reset();
        listaProductos = [];
        renderListaProductos();
        toggleTipoForm('inventario');
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

    // Toggle de estatus de compra rápido con Javascript / Fetch
    function toggleEstatusCompra(idCompra) {
        fetch('<?= base_url("admin/cuentas/toggle") ?>/' + idCompra, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // 1. Recargar el contenido del historial del cliente (HTMX volverá a cargar la lista)
                    // O podemos actualizar localmente los elementos de la tabla y los balances:
                    const badge = document.getElementById('badge-estatus-' + idCompra);
                    const switchInput = document.getElementById('switch-estatus-' + idCompra);
                    const row = document.getElementById('compra-row-' + idCompra);

                    if (data.nuevoEstado == '1') {
                        if (badge) {
                            badge.className = 'badge bg-success font-monospace';
                            badge.innerText = 'Pagado';
                        }
                        if (row) {
                            row.style.opacity = '0.75';
                        }
                    } else {
                        if (badge) {
                            badge.className = 'badge bg-danger font-monospace';
                            badge.innerText = 'Pendiente';
                        }
                        if (row) {
                            row.style.opacity = '1';
                        }
                    }

                    // 2. Actualizar tarjetas de balance en la UI
                    const lblPendiente = document.getElementById('lbl-total-pendiente');
                    const lblPagado = document.getElementById('lbl-total-pagado');
                    const lblTotal = document.getElementById('lbl-total-compras');

                    if (lblPendiente) lblPendiente.innerText = '$' + data.totalPendiente;
                    if (lblPagado) lblPagado.innerText = '$' + data.totalPagado;
                    if (lblTotal) lblTotal.innerText = '$' + data.totalCompras;

                    // 3. Actualizar badge del cliente en la barra lateral
                    const idCliente = document.getElementById('modal_id_cliente').value;
                    const sidebarBadge = document.getElementById('sidebar-debt-' + idCliente);
                    if (sidebarBadge) {
                        sidebarBadge.innerText = '$' + data.totalPendiente;
                        if (parseFloat(data.totalPendiente.replace(/,/g, '')) > 0) {
                            sidebarBadge.className = 'badge bg-danger rounded-pill fw-bold';
                        } else {
                            sidebarBadge.className = 'badge bg-secondary rounded-pill';
                        }
                    }
                } else {
                    alert('No se pudo cambiar el estado de la compra: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Ocurrió un error al procesar el cambio de estado.');
            });
    }

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
            // Reemplazar montos por span con color cyan brillante
            html = html.replace(/(\$\d+(?:\.\d{2})?)/g, '<span style="color: #00cec9; font-weight: bold;">$1</span>');
            return html;
        }

        // Variable local para guardar el saldo pendiente devuelto por ajax
        let nuevoSaldoPendiente = null;

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
                    productos: listaProductos
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

                        // Cargar y formatear texto en el modal del ticket
                        document.getElementById('ticket-content').innerHTML = formatTicketForDisplay(data.ticket);

                        // Guardar datos en el botón de WhatsApp
                        const btnCopiar = document.getElementById('btn-copiar-whatsapp');
                        if (btnCopiar) {
                            btnCopiar.setAttribute('data-ticket', data.ticket);
                            btnCopiar.setAttribute('data-cel', data.cel || '');
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


        // Manejar el clic en el botón de WhatsApp para copiar la información
        const btnCopiar = document.getElementById('btn-copiar-whatsapp');
        if (btnCopiar) {
            btnCopiar.addEventListener('click', function () {
                const text = this.getAttribute('data-ticket');

                // Copiar al portapapeles
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

        // Actualizar la vista al cerrar el modal del ticket
        const modalTicketEl = document.getElementById('modalTicketDigital');
        if (modalTicketEl) {
            modalTicketEl.addEventListener('hidden.bs.modal', function () {
                const idCliente = document.getElementById('modal_id_cliente').value;
                if (idCliente) {
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

            // Cargar y formatear texto en el modal del ticket
            const ticketContentEl = document.getElementById('ticket-content');
            if (ticketContentEl) {
                ticketContentEl.innerHTML = formatTicketForDisplay(ticket);
            }

            // Guardar datos en el botón de WhatsApp
            const btnCopiar = document.getElementById('btn-copiar-whatsapp');
            if (btnCopiar) {
                btnCopiar.setAttribute('data-ticket', ticket);
                btnCopiar.setAttribute('data-cel', cel || '');
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

    // Exportar funciones globales al objeto window para acceso desde atributos HTML
    window.seleccionarCliente = seleccionarCliente;
    window.toggleTipoForm = toggleTipoForm;
    window.renderListaProductos = renderListaProductos;
    window.eliminarProductoLista = eliminarProductoLista;
    window.calcularTotal = calcularTotal;
    window.calcularTotalEdit = calcularTotalEdit;
    window.abrirModalNuevaCompra = abrirModalNuevaCompra;
    window.abrirEditarCompra = abrirEditarCompra;
    window.toggleEstatusCompra = toggleEstatusCompra;
    window.abrirModalEditarCliente = abrirModalEditarCliente;
    window.confirmarEliminarCompra = confirmarEliminarCompra;
    window.abrirModalRegistrarAbono = abrirModalRegistrarAbono;
    window.mostrarToastExito = mostrarToastExito;
    window.copiarEstadoCuenta = copiarEstadoCuenta;
})();
</script>
<?= $this->endSection() ?>