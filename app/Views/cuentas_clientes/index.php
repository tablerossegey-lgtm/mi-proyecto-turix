<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('css/admin.css?v=1.2') ?>">
<style>
    .client-sidebar {
        max-height: 650px;
        overflow-y: auto;
    }
    .client-item {
        cursor: pointer;
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
        background: rgba(255, 255, 255, 0.02);
    }
    .client-item:hover {
        background: rgba(255, 255, 255, 0.05);
        border-left-color: var(--turix-yellow);
    }
    .client-item.active {
        background: rgba(255, 140, 0, 0.08);
        border-left-color: var(--turix-accent);
    }
    .autocomplete-dropdown {
        position: absolute;
        width: 100%;
        max-height: 250px;
        overflow-y: auto;
        z-index: 1050;
        background: #2a2e3d;
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 0.5rem;
        display: none;
    }
    .autocomplete-item {
        background: transparent;
        color: #ffffff;
        border: none;
        width: 100%;
        text-align: left;
        padding: 0.6rem 1rem;
        font-size: 0.85rem;
        transition: background 0.2s ease;
    }
    .autocomplete-item:hover {
        background: rgba(255, 255, 255, 0.08);
        color: var(--turix-yellow);
    }
    .htmx-indicator {
        display: none;
    }
    .htmx-request .htmx-indicator {
        display: inline-block;
    }
    .htmx-request.htmx-indicator {
        display: inline-block;
    }
    
    /* Variables de Switch CSS Premium */
    .form-switch-premium .form-check-input {
        width: 2.8em;
        height: 1.5em;
        cursor: pointer;
    }
    .form-switch-premium .form-check-input:checked {
        background-color: #198754;
        border-color: #198754;
    }
    .form-switch-premium .form-check-input:not(:checked) {
        background-color: #dc3545;
        border-color: #dc3545;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <!-- Encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 admin-title">Cuentas Corrientes de Clientes</h2>
            <p class="text-muted mb-0">Lleva el control de adeudos, abonos y ventas de forma libre o de inventario para tus clientes fijos.</p>
            <div class="admin-subtitle-line" style="background-color: #28a745;"></div>
        </div>
        <div>
            <a href="<?= base_url('admin/productos') ?>" class="btn btn-outline-dark rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2">
                <i class="fas fa-arrow-left"></i> Volver a Productos
            </a>
        </div>
    </div>

    <!-- Notificaciones flotantes tipo Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
        <?php if (session()->getFlashdata('success')): ?>
            <div id="toast-success" class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle text-success fs-5"></i>
                        <div><?= session()->getFlashdata('success') ?></div>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div id="toast-error" class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                        <div><?= session()->getFlashdata('error') ?></div>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
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
                <form action="<?= base_url('admin/cuentas') ?>" method="GET" class="mb-3" id="form-buscador-clientes" onsubmit="return false;">
                    <div class="input-group">
                        <input type="text" id="search-cliente" name="q" class="form-control bg-dark text-white border-secondary" placeholder="Buscar cliente..." value="<?= esc($q) ?>" autocomplete="off">
                        <button class="btn btn-success" type="button" id="btn-search-cliente"><i class="fas fa-search"></i></button>
                        <button class="btn btn-outline-secondary" type="button" id="btn-clear-search" style="display: none;"><i class="fas fa-times"></i></button>
                    </div>
                </form>

                <!-- Lista de Clientes -->
                <div class="client-sidebar list-group list-group-flush rounded-3 border border-secondary border-opacity-25" id="lista-clientes">
                    <?php if (!empty($clientes)): ?>
                        <?php foreach ($clientes as $c): ?>
                            <div class="list-group-item client-item p-3 border-bottom border-secondary border-opacity-25 text-white d-flex justify-content-between align-items-center"
                                 id="client-item-<?= $c['idCliente'] ?>"
                                 hx-get="<?= base_url('admin/cuentas/compras/' . $c['idCliente']) ?>"
                                 hx-target="#compras-cliente-container"
                                 hx-indicator="#loading-indicator"
                                 onclick="seleccionarCliente(<?= $c['idCliente'] ?>)">
                                <div>
                                    <div class="fw-bold text-truncate client-name" style="max-width: 180px;"><?= esc($c['nombre']) ?></div>
                                    <small class="text-white-50 client-phone"><i class="fas fa-phone-alt me-1 text-muted" style="font-size: 0.75rem;"></i> <?= esc($c['cel'] ?: 'Sin teléfono') ?></small>
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
                    <div class="p-4 text-center text-muted small" id="no-search-results" style="display: none;">No se encontraron resultados.</div>
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
                    <p class="small mb-0">Haz clic en alguno de los clientes del listado de la izquierda para ver su estado de cuenta, compras detalladas y generar su orden de cobro para WhatsApp.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: REGISTRAR COMPRA -->
<div class="modal fade" id="modalNuevaCompra" tabindex="-1" aria-labelledby="modalNuevaCompraLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white">
            <div class="modal-header modal-encargo-header py-3 px-4" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title fw-bold text-white" id="modalNuevaCompraLabel">
                    <i class="fas fa-cart-plus me-2 text-success"></i> Registrar Compra / Cuenta
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevaCompra" action="<?= base_url('admin/cuentas/crear') ?>" method="POST">
                <input type="hidden" name="id_cliente" id="modal_id_cliente" value="">
                
                <div class="modal-body p-4">
                    <!-- Selector de Tipo de Compra -->
                    <div class="mb-4 text-center">
                        <label class="form-label modal-encargo-label d-block text-start">Origen del Producto</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="tipo_compra" id="tipo_inventario" value="inventario" checked onclick="toggleTipoForm('inventario')">
                            <label class="btn btn-outline-success fw-bold" for="tipo_inventario"><i class="bi bi-box-seam me-1"></i> Del Inventario</label>
                            
                            <input type="radio" class="btn-check" name="tipo_compra" id="tipo_libre" value="libre" onclick="toggleTipoForm('libre')">
                            <label class="btn btn-outline-success fw-bold" for="tipo_libre"><i class="bi bi-pen me-1"></i> Formato Libre</label>
                        </div>
                    </div>

                    <!-- Campos de Producto del Inventario -->
                    <div id="seccion-inventario" class="mb-3">
                        <div class="position-relative mb-3">
                            <label for="producto_search" class="form-label modal-encargo-label">Buscar en Inventario *</label>
                            <input type="hidden" name="id_inventario" id="modal_id_inventario" value="0">
                            <input type="text" class="form-control modal-encargo-control text-white w-100" id="producto_search" placeholder="Escribe SKU o Nombre..." autocomplete="off">
                            <div class="autocomplete-dropdown shadow-lg" id="dropdown_productos"></div>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="descontar_stock" name="descontar_stock" value="1" checked>
                            <label class="form-check-label modal-encargo-label text-white-50" for="descontar_stock" style="margin-left: 0.5rem;">Descontar unidades del inventario actual</label>
                        </div>
                    </div>

                    <!-- Campos Comunes / Formato Libre -->
                    <div class="row g-3">
                        <!-- Descripción del Producto (libre) -->
                        <div class="col-12" id="seccion-desc-libre" style="display: none;">
                            <label for="desc_producto" class="form-label modal-encargo-label">Descripción del Producto *</label>
                            <input type="text" class="form-control modal-encargo-control text-white" id="desc_producto" name="desc_producto" placeholder="Ej: Bolsa de regalo grande decorada">
                        </div>

                        <!-- Fecha de Compra -->
                        <div class="col-12">
                            <label for="fecha_compra" class="form-label modal-encargo-label">Fecha de Compra</label>
                            <input type="date" class="form-control modal-encargo-control text-white" id="fecha_compra" name="fecha_compra" value="<?= date('Y-m-d') ?>">
                        </div>

                        <!-- Cantidad -->
                        <div class="col-6">
                            <label for="cantidad" class="form-label modal-encargo-label">Cantidad *</label>
                            <input type="number" class="form-control modal-encargo-control text-white" id="cantidad" name="cantidad" value="1" min="1" required oninput="calcularTotal()">
                        </div>

                        <!-- Precio Unitario -->
                        <div class="col-6">
                            <label for="precio_unit" class="form-label modal-encargo-label">Precio Unitario ($) *</label>
                            <input type="number" step="0.01" min="0" class="form-control modal-encargo-control text-white" id="precio_unit" name="precio_unit" value="0.00" required oninput="calcularTotal()">
                        </div>

                        <!-- Total Calculado -->
                        <div class="col-12">
                            <div class="p-3 rounded bg-dark border border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                                <span class="small text-white-50 fw-bold">TOTAL ESTIMADO:</span>
                                <span class="fs-4 fw-bold text-success" id="lbl-total-calculado">$0.00</span>
                            </div>
                        </div>

                        <!-- Estado de la compra -->
                        <div class="col-12">
                            <label for="estatus_compra" class="form-label modal-encargo-label d-block">Estado de Pago</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="estatus_compra" id="status_pendiente" value="0" checked>
                                <label class="form-check-label text-danger fw-bold" for="status_pendiente">Pendiente (Debe)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="estatus_compra" id="status_pagado" value="1">
                                <label class="form-check-label text-success fw-bold" for="status_pagado">Pagado (Caja)</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer modal-encargo-footer d-flex gap-2" style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold text-white hover-shadow">
                        <i class="fas fa-save me-1"></i> Registrar Compra
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: EDITAR COMPRA -->
<div class="modal fade" id="modalEditarCompra" tabindex="-1" aria-labelledby="modalEditarCompraLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white">
            <div class="modal-header modal-encargo-header py-3 px-4" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title fw-bold text-white" id="modalEditarCompraLabel">
                    <i class="fas fa-edit me-2 text-warning"></i> Modificar Compra
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditarCompra" action="" method="POST">
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label modal-encargo-label text-white-50">Producto:</label>
                        <div class="p-2.5 rounded bg-dark border border-secondary border-opacity-25" id="edit-lbl-producto" style="font-size: 0.95rem;">-</div>
                    </div>

                    <div class="row g-3">
                        <!-- Fecha de Compra -->
                        <div class="col-12">
                            <label for="edit_fecha_compra" class="form-label modal-encargo-label">Fecha de Compra</label>
                            <input type="date" class="form-control modal-encargo-control text-white" id="edit_fecha_compra" name="fecha_compra">
                        </div>

                        <!-- Cantidad -->
                        <div class="col-6">
                            <label for="edit_cantidad" class="form-label modal-encargo-label">Cantidad *</label>
                            <input type="number" class="form-control modal-encargo-control text-white" id="edit_cantidad" name="cantidad" min="1" required oninput="calcularTotalEdit()">
                        </div>

                        <!-- Precio Unitario -->
                        <div class="col-6">
                            <label for="edit_precio_unit" class="form-label modal-encargo-label">Precio Unitario ($) *</label>
                            <input type="number" step="0.01" min="0" class="form-control modal-encargo-control text-white" id="edit_precio_unit" name="precio_unit" required oninput="calcularTotalEdit()">
                        </div>

                        <!-- Total Calculado -->
                        <div class="col-12">
                            <div class="p-3 rounded bg-dark border border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                                <span class="small text-white-50 fw-bold">TOTAL ESTIMADO:</span>
                                <span class="fs-4 fw-bold text-warning" id="edit-lbl-total-calculado">$0.00</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch mb-3" id="edit-seccion-stock" style="display:none;">
                                <input class="form-check-input" type="checkbox" role="switch" id="edit_descontar_stock" name="descontar_stock" value="1" checked>
                                <label class="form-check-label modal-encargo-label text-white-50" for="edit_descontar_stock" style="margin-left: 0.5rem;">Ajustar unidades en el inventario actual</label>
                            </div>
                        </div>

                        <!-- Estado de la compra -->
                        <div class="col-12">
                            <label for="edit_estatus_compra" class="form-label modal-encargo-label d-block">Estado de Pago</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="estatus_compra" id="edit_status_pendiente" value="0">
                                <label class="form-check-label text-danger fw-bold" for="edit_status_pendiente">Pendiente (Debe)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="estatus_compra" id="edit_status_pagado" value="1">
                                <label class="form-check-label text-success fw-bold" for="edit_status_pagado">Pagado (Caja)</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer modal-encargo-footer d-flex gap-2" style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark hover-shadow">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: EDITAR CLIENTE -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-labelledby="modalEditarClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white">
            <div class="modal-header modal-encargo-header py-3 px-4" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title fw-bold text-white" id="modalEditarClienteLabel">
                    <i class="fas fa-user-edit me-2 text-warning"></i> Editar Datos del Cliente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditarCliente" action="" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Nombre -->
                        <div class="col-12">
                            <label for="edit_cliente_nombre" class="form-label modal-encargo-label">Nombre Completo *</label>
                            <input type="text" class="form-control modal-encargo-control text-white" id="edit_cliente_nombre" name="nombre" required placeholder="Ej: Juan Pérez">
                        </div>

                        <!-- Teléfono -->
                        <div class="col-12">
                            <label for="edit_cliente_cel" class="form-label modal-encargo-label">Teléfono de Contacto (WhatsApp)</label>
                            <input type="tel" class="form-control modal-encargo-control text-white" id="edit_cliente_cel" name="cel" placeholder="Ej: 9991234567">
                        </div>

                        <!-- Tipo de Cliente -->
                        <div class="col-12">
                            <label for="edit_cliente_tipo" class="form-label modal-encargo-label">Tipo de Cliente / Etiqueta</label>
                            <input type="text" class="form-control modal-encargo-control text-white" id="edit_cliente_tipo" name="tipoCliente" placeholder="Ej: General, Mayorista, Distribuidor, etc.">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer modal-encargo-footer d-flex gap-2" style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark hover-shadow">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.body.classList.add('admin-body');

    // Cambiar estilos de selección de cliente activo
    function seleccionarCliente(id) {
        document.querySelectorAll('.client-item').forEach(item => {
            item.classList.remove('active');
        });
        const element = document.getElementById('client-item-' + id);
        if (element) {
            element.classList.add('active');
        }
    }

    // Cambiar tipo de formulario en el modal de nueva compra
    function toggleTipoForm(tipo) {
        if (tipo === 'inventario') {
            document.getElementById('seccion-inventario').style.display = 'block';
            document.getElementById('seccion-desc-libre').style.display = 'none';
            document.getElementById('modal_id_inventario').value = '0';
            document.getElementById('producto_search').required = true;
            document.getElementById('desc_producto').required = false;
        } else {
            document.getElementById('seccion-inventario').style.display = 'none';
            document.getElementById('seccion-desc-libre').style.display = 'block';
            document.getElementById('modal_id_inventario').value = '0';
            document.getElementById('producto_search').value = '';
            document.getElementById('producto_search').required = false;
            document.getElementById('desc_producto').required = true;
            document.getElementById('precio_unit').value = '0.00';
            calcularTotal();
        }
    }

    // Calcular el total estimado en nueva compra
    function calcularTotal() {
        const cant = parseInt(document.getElementById('cantidad').value) || 0;
        const price = parseFloat(document.getElementById('precio_unit').value) || 0;
        const total = cant * price;
        document.getElementById('lbl-total-calculado').innerText = '$' + total.toFixed(2);
    }

    // Calcular el total estimado en edición
    function calcularTotalEdit() {
        const cant = parseInt(document.getElementById('edit_cantidad').value) || 0;
        const price = parseFloat(document.getElementById('edit_precio_unit').value) || 0;
        const total = cant * price;
        document.getElementById('edit-lbl-total-calculado').innerText = '$' + total.toFixed(2);
    }

    // Inicializar autocompletado del buscador de productos en el modal
    document.addEventListener("DOMContentLoaded", function() {
        const input = document.getElementById('producto_search');
        const dropdown = document.getElementById('dropdown_productos');
        const hidden = document.getElementById('modal_id_inventario');
        const priceInput = document.getElementById('precio_unit');

        input.addEventListener('focus', function() {
            if (input.value.trim().length > 0) {
                dropdown.style.display = 'block';
            }
        });

        input.addEventListener('input', function() {
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
                        btn.addEventListener('click', function() {
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
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    });

    // Función para configurar y abrir el modal de registrar compra
    function abrirModalNuevaCompra(idCliente) {
        document.getElementById('modal_id_cliente').value = idCliente;
        document.getElementById('formNuevaCompra').reset();
        toggleTipoForm('inventario');
        calcularTotal();
        const modal = new bootstrap.Modal(document.getElementById('modalNuevaCompra'));
        modal.show();
    }

    // Función para abrir el modal de edición de compras
    function abrirEditarCompra(compra) {
        document.getElementById('formEditarCompra').action = '<?= base_url("admin/cuentas/editar") ?>/' + compra.idCompra;
        
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
    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('search-cliente');
        const clearBtn = document.getElementById('btn-clear-search');
        const clientItems = document.querySelectorAll('.client-item');
        const noResults = document.getElementById('no-search-results');

        function filterClients() {
            const query = searchInput.value.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
            let visibleCount = 0;

            clientItems.forEach(item => {
                const name = item.querySelector('.client-name').textContent.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
                const phone = item.querySelector('.client-phone').textContent.toLowerCase();

                if (name.includes(query) || phone.includes(query)) {
                    item.style.setProperty('display', 'flex', 'important');
                    visibleCount++;
                } else {
                    item.style.setProperty('display', 'none', 'important');
                }
            });

            // Mostrar u ocultar botón de limpiar
            if (query.length > 0) {
                clearBtn.style.display = 'block';
            } else {
                clearBtn.style.display = 'none';
            }

            // Mostrar mensaje si no hay resultados
            if (visibleCount === 0 && clientItems.length > 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', filterClients);
            
            // Ejecutar al cargar por si viene de una recarga con valor preestablecido
            filterClients();
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                filterClients();
                searchInput.focus();
            });
        }
        
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
    });

    // Función para abrir el modal de editar cliente
    function abrirModalEditarCliente(cliente) {
        document.getElementById('formEditarCliente').action = '<?= base_url("admin/cuentas/editar-cliente") ?>/' + cliente.idCliente;
        document.getElementById('edit_cliente_nombre').value = cliente.nombre;
        document.getElementById('edit_cliente_cel').value = cliente.cel || '';
        document.getElementById('edit_cliente_tipo').value = cliente.tipoCliente || '';
        
        const modal = new bootstrap.Modal(document.getElementById('modalEditarCliente'));
        modal.show();
    }
</script>
<?= $this->endSection() ?>
