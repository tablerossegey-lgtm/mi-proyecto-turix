<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<link class="styles-admin-theme" rel="stylesheet" href="<?= base_url('css/admin.css?v=1.2') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <!-- Encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 admin-title">Registro de Pedidos Encargados</h2>
            <p class="text-muted mb-0">Lleva el control de los productos del inventario que tus clientes te han encargado por conseguir.</p>
            <div class="admin-subtitle-line" style="background-color: #0dcaf0;"></div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-info rounded-pill px-4 fw-bold text-dark d-inline-flex align-items-center gap-2 hover-shadow" data-bs-toggle="modal" data-bs-target="#modalNuevoEncargo">
                <i class="fas fa-cart-plus text-dark"></i> Registrar Encargo
            </button>
            <a href="<?= base_url('admin/productos') ?>" class="btn btn-outline-dark rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2">
                <i class="fas fa-arrow-left"></i> Volver a Productos
            </a>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Pendientes -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="admin-card p-3 d-flex flex-column">
                <div class="admin-stat-card admin-stat-card-stock">
                    <div class="admin-stat-label">
                        <i class="fas fa-hourglass-half"></i> Unidades Pendientes
                    </div>
                    <div class="admin-stat-value">
                        <?= $total_pendientes ?> <span class="admin-stat-unit">pza(s)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Anticipos -->
        <div class="col-12 col-md-6 col-lg-3">
            <div class="admin-card p-3 d-flex flex-column">
                <div class="admin-stat-card admin-stat-card-anticipos">
                    <div class="admin-stat-label">
                        <i class="fas fa-hand-holding-usd"></i> Anticipos en Caja
                    </div>
                    <div class="admin-stat-value">
                        $<?= number_format($total_anticipos, 2) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de Estado -->
    <div class="card border-0 shadow-sm mb-4 admin-card">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?= base_url('admin/encargos') ?>" 
                       class="btn btn-sm rounded-pill px-3 fw-bold <?= empty($estado_filtro) ? 'btn-info text-dark' : 'btn-outline-light' ?>">
                        Todos
                    </a>
                    <a href="<?= base_url('admin/encargos?estado=Pendiente') ?>" 
                       class="btn btn-sm rounded-pill px-3 fw-bold <?= $estado_filtro === 'Pendiente' ? 'btn-warning text-dark' : 'btn-outline-light' ?>">
                        Pendientes
                    </a>
                    <a href="<?= base_url('admin/encargos?estado=Conseguido') ?>" 
                       class="btn btn-sm rounded-pill px-3 fw-bold <?= $estado_filtro === 'Conseguido' ? 'btn-success text-white' : 'btn-outline-light' ?>">
                        Conseguidos
                    </a>
                    <a href="<?= base_url('admin/encargos?estado=Entregado') ?>" 
                       class="btn btn-sm rounded-pill px-3 fw-bold <?= $estado_filtro === 'Entregado' ? 'btn-primary text-white' : 'btn-outline-light' ?>">
                        Entregados
                    </a>
                    <a href="<?= base_url('admin/encargos?estado=Cancelado') ?>" 
                       class="btn btn-sm rounded-pill px-3 fw-bold <?= $estado_filtro === 'Cancelado' ? 'btn-secondary text-white' : 'btn-outline-light' ?>">
                        Cancelados
                    </a>
                </div>

                <?php 
                $productoFiltroId = service('request')->getVar('producto_id');
                if ($productoFiltroId): 
                    // Obtener nombre del producto filtrado
                    $nomProd = 'Producto específico';
                    foreach ($productos as $p) {
                        if ($p['id'] == $productoFiltroId) {
                            $nomProd = $p['descripcion'];
                            break;
                        }
                    }
                ?>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-dark border border-info text-info px-3 py-2 rounded-pill small">
                            Filtrado: <strong><?= esc($nomProd) ?></strong>
                        </span>
                        <a href="<?= base_url('admin/encargos') ?>" class="text-white-50 text-decoration-none small hover-warning">
                            <i class="fas fa-times-circle"></i> Quitar filtro
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Notificaciones -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success border-0 rounded-3 shadow-sm py-3 mb-4 d-flex align-items-center gap-2">
            <i class="fas fa-check-circle fs-5 text-success"></i>
            <div><?= session()->getFlashdata('success') ?></div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 rounded-3 shadow-sm py-3 mb-4 d-flex align-items-center gap-2">
            <i class="fas fa-exclamation-circle fs-5 text-danger"></i>
            <div><?= session()->getFlashdata('error') ?></div>
        </div>
    <?php endif; ?>

    <!-- Listado de Pedidos Encargados -->
    <div class="card border-0 shadow-sm admin-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead class="text-muted admin-table-thead">
                    <tr>
                        <th class="ps-4 py-3 admin-table-th">Producto</th>
                        <th class="py-3 admin-table-th">Cliente</th>
                        <th class="py-3 text-center admin-table-th">Cantidad</th>
                        <th class="py-3 text-center admin-table-th">Anticipo</th>
                        <th class="py-3 text-center admin-table-th">Estado</th>
                        <th class="py-3 admin-table-th">Fecha Encargo</th>
                        <th class="py-3 text-end pe-4 admin-table-th">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Si hay filtro de producto por GET, filtramos los encargos en la vista
                    if ($productoFiltroId) {
                        $encargos = array_filter($encargos, function($e) use ($productoFiltroId) {
                            return $e['id_producto'] == $productoFiltroId;
                        });
                    }
                    ?>
                    <?php if (!empty($encargos)): ?>
                        <?php foreach ($encargos as $e): 
                            // Configuración de imagen del producto
                            $srcUrl = obtener_ruta_imagen($e['foto_producto'] ?? '', $e['nombre_categoria'] ?? '');

                            // Limpieza de teléfono para WhatsApp
                            $contacto = $e['contacto_cliente'] ?? '';
                            $telefonoLimpio = preg_replace('/[^0-9]/', '', $contacto);
                            if (strlen($telefonoLimpio) === 10) {
                                $telefonoLimpio = '52' . $telefonoLimpio;
                            }

                            // Mensaje personalizado de WhatsApp según el estado
                            $msgText = "Hola " . $e['nombre_cliente'] . ", te escribo de TurixShop referente a tu encargo de " . $e['cantidad'] . " pza(s) de '" . ($e['descripcion_producto'] ?: 'Producto') . "'";
                            if ($e['estado'] === 'Conseguido') {
                                $msgText .= ". ¡Ya tenemos tu producto listo para entrega! Quedamos a tus órdenes.";
                            } elseif ($e['estado'] === 'Pendiente') {
                                $msgText .= ". Te confirmamos que ya estamos gestionando tu pedido para conseguirlo lo antes posible.";
                            } else {
                                $msgText .= ". Queremos darte seguimiento a tu pedido. Saludos.";
                            }
                            $waUrl = "https://wa.me/{$telefonoLimpio}?text=" . urlencode($msgText);

                            // Clases de Badge por estado
                            $badgeClass = 'estado-badge-pendiente';
                            if ($e['estado'] === 'Conseguido') $badgeClass = 'estado-badge-conseguido';
                            elseif ($e['estado'] === 'Entregado') $badgeClass = 'estado-badge-entregado';
                            elseif ($e['estado'] === 'Cancelado') $badgeClass = 'estado-badge-cancelado';
                        ?>
                            <tr class="admin-table-tr">
                                <!-- Producto info -->
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 overflow-hidden d-flex align-items-center justify-content-center admin-img-thumb-container" style="width: 50px; height: 50px;">
                                            <img src="<?= $srcUrl ?>" 
                                                 alt="<?= esc($e['descripcion_producto'] ?? 'Eliminado') ?>" 
                                                 class="img-fluid admin-img-thumb" 
                                                 onerror="this.src='<?= base_url('uploads/SinImagen.png') ?>'; this.onerror=null;">
                                        </div>
                                        <div>
                                            <?php if ($e['id_producto']): ?>
                                                <div class="fw-semibold admin-product-title" style="font-size: 0.9rem;"><?= esc($e['descripcion_producto']) ?></div>
                                                <span class="badge text-dark font-monospace admin-sku-badge" style="font-size: 0.75rem;">
                                                    <?= esc($e['codigo_sku']) ?>
                                                </span>
                                            <?php else: ?>
                                                <div class="text-danger fw-semibold small">Producto eliminado de inventario</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Cliente info -->
                                <td class="py-3">
                                    <div class="fw-semibold text-white"><?= esc($e['nombre_cliente']) ?></div>
                                    <?php if (!empty($contacto)): ?>
                                        <span class="text-white-50 small d-inline-flex align-items-center gap-1.5 mt-0.5">
                                            <i class="fas fa-phone-alt text-muted" style="font-size: 0.75rem;"></i> <?= esc($contacto) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-white-50 small opacity-40">Sin teléfono</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Cantidad -->
                                <td class="py-3 text-center text-white fw-bold">
                                    <?= $e['cantidad'] ?> pz(s)
                                </td>

                                <!-- Anticipo -->
                                <td class="py-3 text-center fw-bold">
                                    <?php if ($e['anticipo'] > 0): ?>
                                        <span class="text-success">$<?= number_format($e['anticipo'], 2) ?></span>
                                    <?php else: ?>
                                        <span class="text-white-50 opacity-40">$0.00</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Estado -->
                                <td class="py-3 text-center">
                                    <span class="badge px-3 py-1.5 rounded-pill <?= $badgeClass ?> font-monospace">
                                        <?= esc($e['estado']) ?>
                                    </span>
                                </td>

                                <!-- Fecha -->
                                <td class="py-3 text-white-50 small">
                                    <?= date('d/m/Y H:i', strtotime($e['creado_en'])) ?>
                                </td>

                                <!-- Acciones -->
                                <td class="py-3 text-end pe-4">
                                    <div class="d-inline-flex gap-1.5">
                                        <?php if (!empty($telefonoLimpio)): ?>
                                            <a href="<?= $waUrl ?>" 
                                               target="_blank" 
                                               class="btn btn-whatsapp-link btn-sm d-flex align-items-center justify-content-center rounded-3 p-2" 
                                               title="Enviar mensaje por WhatsApp">
                                                <i class="fab fa-whatsapp fs-6"></i>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <button type="button" 
                                                class="btn btn-warning btn-sm d-flex align-items-center justify-content-center rounded-3 p-2 text-dark" 
                                                title="Editar encargo"
                                                onclick="abrirEditarEncargo(<?= htmlspecialchars(json_encode($e)) ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <form action="<?= base_url('admin/encargos/eliminar/' . $e['id']) ?>" 
                                              method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirm('¿Estás seguro de que deseas eliminar este encargo? Esta acción no se puede deshacer.');"
                                              hx-boost="true">
                                            <button type="submit" 
                                                    class="btn btn-danger btn-sm d-flex align-items-center justify-content-center rounded-3 p-2" 
                                                    title="Eliminar encargo">
                                                <i class="fas fa-trash-alt text-white"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            
                            <?php if (!empty($e['notas'])): ?>
                                <tr class="admin-table-tr" style="background-color: rgba(0, 0, 0, 0.15) !important;">
                                    <td colspan="7" class="ps-5 py-2 text-white-50 small">
                                        <i class="fas fa-sticky-note me-2 text-info"></i><strong>Notas del Encargo:</strong> <?= esc($e['notas']) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="fas fa-clipboard-list fs-1 text-muted opacity-50"></i>
                                </div>
                                <h5 class="fw-bold admin-product-title">No hay encargos registrados</h5>
                                <p class="mb-0">Registra uno nuevo utilizando el botón superior.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Registrar Encargo -->
<div class="modal fade" id="modalNuevoEncargo" tabindex="-1" aria-labelledby="modalNuevoEncargoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white">
            <div class="modal-header modal-encargo-header py-3 px-4">
                <h5 class="modal-title fw-bold text-white" id="modalNuevoEncargoLabel">
                    <i class="fas fa-cart-plus me-1 text-info"></i> Registrar Nuevo Encargo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevoEncargo" action="<?= base_url('admin/encargos/crear') ?>" method="POST" hx-boost="true">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Producto -->
                        <div class="col-12">
                            <label for="id_producto" class="form-label modal-encargo-label">Seleccionar Producto del Inventario *</label>
                            <div class="position-relative searchable-select-container" id="container-nuevo-producto">
                                <input type="hidden" name="id_producto" id="id_producto_hidden" value="" required>
                                <input type="text" 
                                       class="form-control modal-encargo-control text-white w-100" 
                                       id="producto_search_input" 
                                       placeholder="Escribe para buscar por SKU o nombre de producto..." 
                                       autocomplete="off"
                                       required>
                                <div class="dropdown-menu w-100 p-2 shadow-lg" 
                                     id="producto_search_dropdown" 
                                     style="max-height: 250px; overflow-y: auto; background-color: #2a2e3d; border: 1px solid rgba(255,255,255,0.12); border-radius: 0.5rem; display: none; position: absolute; z-index: 1060; left: 0; right: 0;">
                                    <?php foreach ($productos as $p): ?>
                                        <button type="button" 
                                                class="dropdown-item text-white border-0 py-2 px-3 rounded-2 text-start producto-item-option" 
                                                style="background: transparent; font-size: 0.85rem;"
                                                data-id="<?= $p['id'] ?>"
                                                data-sku="<?= esc($p['codigo_sku']) ?>"
                                                data-descripcion="<?= esc($p['descripcion']) ?>"
                                                data-precio="<?= number_format($p['precio'], 2) ?>">
                                            <strong><?= esc($p['codigo_sku']) ?></strong> - <?= esc($p['descripcion']) ?> ($<?= number_format($p['precio'], 2) ?>)
                                        </button>
                                    <?php endforeach; ?>
                                    <div class="text-white-50 p-2 text-center small search-no-results" style="display: none;">No se encontraron productos</div>
                                </div>
                            </div>
                        </div>

                        <!-- Cliente -->
                        <div class="col-12 col-md-6">
                            <label for="nombre_cliente" class="form-label modal-encargo-label">Nombre del Cliente *</label>
                            <div class="position-relative searchable-select-container" id="container-nuevo-cliente">
                                <input type="text" 
                                       class="form-control modal-encargo-control text-white w-100" 
                                       id="nombre_cliente" 
                                       name="nombre_cliente" 
                                       required 
                                       placeholder="Escribe para buscar o registrar..."
                                       autocomplete="off">
                                <div class="dropdown-menu w-100 p-2 shadow-lg" 
                                     id="cliente_search_dropdown" 
                                     style="max-height: 200px; overflow-y: auto; background-color: #2a2e3d; border: 1px solid rgba(255,255,255,0.12); border-radius: 0.5rem; display: none; position: absolute; z-index: 1060; left: 0; right: 0;">
                                    <?php foreach ($clientes as $c): ?>
                                        <button type="button" 
                                                class="dropdown-item text-white border-0 py-2 px-3 rounded-2 text-start cliente-item-option" 
                                                style="background: transparent; font-size: 0.85rem;"
                                                data-id="<?= $c['idCliente'] ?>"
                                                data-nombre="<?= esc($c['nombre']) ?>"
                                                data-cel="<?= esc($c['cel']) ?>">
                                            <strong><?= esc($c['nombre']) ?></strong> <?= !empty($c['cel']) ? '('.esc($c['cel']).')' : '' ?>
                                        </button>
                                    <?php endforeach; ?>
                                    <div class="text-white-50 p-2 text-center small cliente-search-no-results" style="display: none;">Escribe para registrar a este cliente nuevo</div>
                                </div>
                            </div>
                        </div>

                        <!-- Teléfono / Contacto -->
                        <div class="col-12 col-md-6">
                            <label for="contacto_cliente" class="form-label modal-encargo-label">Teléfono de Contacto (WhatsApp)</label>
                            <input type="tel" 
                                   class="form-control modal-encargo-control text-white" 
                                   id="contacto_cliente" 
                                   name="contacto_cliente" 
                                   placeholder="Ej: 9991234567">
                        </div>

                        <!-- Cantidad -->
                        <div class="col-12 col-md-6">
                            <label for="cantidad" class="form-label modal-encargo-label">Cantidad (unidades) *</label>
                            <input type="number" 
                                   class="form-control modal-encargo-control text-white" 
                                   id="cantidad" 
                                   name="cantidad" 
                                   value="1" 
                                   min="1" 
                                   required>
                        </div>

                        <!-- Anticipo -->
                        <div class="col-12 col-md-6">
                            <label for="anticipo" class="form-label modal-encargo-label">Anticipo / Adelanto ($)</label>
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   class="form-control modal-encargo-control text-white" 
                                   id="anticipo" 
                                   name="anticipo" 
                                   value="0.00" 
                                   placeholder="0.00">
                        </div>

                        <!-- Notas -->
                        <div class="col-12">
                            <label for="notas" class="form-label modal-encargo-label">Notas / Observaciones del Pedido</label>
                            <textarea class="form-control modal-encargo-control text-white" 
                                      style="height: 100px; resize: none;"
                                      id="notas" 
                                      name="notas" 
                                      placeholder="Especificar talla, color, fecha prometida, etc."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-encargo-footer d-flex gap-2">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info rounded-pill px-4 fw-bold text-dark hover-shadow">
                        <i class="fas fa-save me-1 text-dark"></i> Guardar Encargo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Encargo -->
<div class="modal fade" id="modalEditarEncargo" tabindex="-1" aria-labelledby="modalEditarEncargoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white">
            <div class="modal-header modal-encargo-header py-3 px-4">
                <h5 class="modal-title fw-bold text-white" id="modalEditarEncargoLabel">
                    <i class="fas fa-edit me-1 text-info"></i> Editar Pedido Encargado
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditarEncargo" action="" method="POST" hx-boost="true">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Producto -->
                        <div class="col-12 col-md-8">
                            <label for="edit_id_producto" class="form-label modal-encargo-label">Producto *</label>
                            <div class="position-relative searchable-select-container" id="container-editar-producto">
                                <input type="hidden" name="id_producto" id="edit_id_producto_hidden" value="" required>
                                <input type="text" 
                                       class="form-control modal-encargo-control text-white w-100" 
                                       id="edit_producto_search_input" 
                                       placeholder="Escribe para buscar por SKU o nombre de producto..." 
                                       autocomplete="off"
                                       required>
                                <div class="dropdown-menu w-100 p-2 shadow-lg" 
                                     id="edit_producto_search_dropdown" 
                                     style="max-height: 250px; overflow-y: auto; background-color: #2a2e3d; border: 1px solid rgba(255,255,255,0.12); border-radius: 0.5rem; display: none; position: absolute; z-index: 1060; left: 0; right: 0;">
                                    <?php foreach ($productos as $p): ?>
                                        <button type="button" 
                                                class="dropdown-item text-white border-0 py-2 px-3 rounded-2 text-start edit-producto-item-option" 
                                                style="background: transparent; font-size: 0.85rem;"
                                                data-id="<?= $p['id'] ?>"
                                                data-sku="<?= esc($p['codigo_sku']) ?>"
                                                data-descripcion="<?= esc($p['descripcion']) ?>"
                                                data-precio="<?= number_format($p['precio'], 2) ?>">
                                            <strong><?= esc($p['codigo_sku']) ?></strong> - <?= esc($p['descripcion']) ?> ($<?= number_format($p['precio'], 2) ?>)
                                        </button>
                                    <?php endforeach; ?>
                                    <div class="text-white-50 p-2 text-center small edit-search-no-results" style="display: none;">No se encontraron productos</div>
                                </div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="col-12 col-md-4">
                            <label for="edit_estado" class="form-label modal-encargo-label">Estado del Encargo *</label>
                            <select class="form-select modal-encargo-select" id="edit_estado" name="estado" required>
                                <option value="Pendiente">Pendiente</option>
                                <option value="Conseguido">Conseguido</option>
                                <option value="Entregado">Entregado</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>

                        <!-- Cliente -->
                        <div class="col-12 col-md-6">
                            <label for="edit_nombre_cliente" class="form-label modal-encargo-label">Nombre del Cliente *</label>
                            <div class="position-relative searchable-select-container" id="container-editar-cliente">
                                <input type="text" 
                                       class="form-control modal-encargo-control text-white w-100" 
                                       id="edit_nombre_cliente" 
                                       name="nombre_cliente" 
                                       required 
                                       placeholder="Escribe para buscar o registrar..."
                                       autocomplete="off">
                                <div class="dropdown-menu w-100 p-2 shadow-lg" 
                                     id="edit_cliente_search_dropdown" 
                                     style="max-height: 200px; overflow-y: auto; background-color: #2a2e3d; border: 1px solid rgba(255,255,255,0.12); border-radius: 0.5rem; display: none; position: absolute; z-index: 1060; left: 0; right: 0;">
                                    <?php foreach ($clientes as $c): ?>
                                        <button type="button" 
                                                class="dropdown-item text-white border-0 py-2 px-3 rounded-2 text-start edit-cliente-item-option" 
                                                style="background: transparent; font-size: 0.85rem;"
                                                data-id="<?= $c['idCliente'] ?>"
                                                data-nombre="<?= esc($c['nombre']) ?>"
                                                data-cel="<?= esc($c['cel']) ?>">
                                            <strong><?= esc($c['nombre']) ?></strong> <?= !empty($c['cel']) ? '('.esc($c['cel']).')' : '' ?>
                                        </button>
                                    <?php endforeach; ?>
                                    <div class="text-white-50 p-2 text-center small edit-cliente-search-no-results" style="display: none;">Escribe para registrar a este cliente nuevo</div>
                                </div>
                            </div>
                        </div>

                        <!-- Teléfono / Contacto -->
                        <div class="col-12 col-md-6">
                            <label for="edit_contacto_cliente" class="form-label modal-encargo-label">Teléfono de Contacto (WhatsApp)</label>
                            <input type="tel" 
                                   class="form-control modal-encargo-control text-white" 
                                   id="edit_contacto_cliente" 
                                   name="contacto_cliente">
                        </div>

                        <!-- Cantidad -->
                        <div class="col-12 col-md-6">
                            <label for="edit_cantidad" class="form-label modal-encargo-label">Cantidad (unidades) *</label>
                            <input type="number" 
                                   class="form-control modal-encargo-control text-white" 
                                   id="edit_cantidad" 
                                   name="cantidad" 
                                   min="1" 
                                   required>
                        </div>

                        <!-- Anticipo -->
                        <div class="col-12 col-md-6">
                            <label for="edit_anticipo" class="form-label modal-encargo-label">Anticipo / Adelanto ($)</label>
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   class="form-control modal-encargo-control text-white" 
                                   id="edit_anticipo" 
                                   name="anticipo">
                        </div>

                        <!-- Notas -->
                        <div class="col-12">
                            <label for="edit_notas" class="form-label modal-encargo-label">Notas / Observaciones del Pedido</label>
                            <textarea class="form-control modal-encargo-control text-white" 
                                      style="height: 100px; resize: none;"
                                      id="edit_notas" 
                                      name="notas"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-encargo-footer d-flex gap-2">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info rounded-pill px-4 fw-bold text-dark hover-shadow">
                        <i class="fas fa-save me-1 text-dark"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.body.classList.add('admin-body');

    // Función general para inicializar un buscador de productos
    function inicializarBuscadorProductos(inputId, dropdownId, hiddenId, optionClass, noResultsClass, onSelectCallback = null) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        const hidden = document.getElementById(hiddenId);
        const options = dropdown.querySelectorAll('.' + optionClass);
        const noResults = dropdown.querySelector('.' + noResultsClass);

        // Mostrar dropdown al enfocar
        input.addEventListener('focus', function() {
            dropdown.style.display = 'block';
        });

        // Filtrar productos al escribir
        input.addEventListener('input', function() {
            const query = input.value.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
            let matches = 0;

            options.forEach(opt => {
                const sku = opt.getAttribute('data-sku').normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
                const desc = opt.getAttribute('data-descripcion').normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();

                if (sku.includes(query) || desc.includes(query)) {
                    opt.style.display = 'block';
                    matches++;
                } else {
                    opt.style.display = 'none';
                }
            });

            if (matches === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        });

        // Seleccionar una opción
        options.forEach(opt => {
            opt.addEventListener('click', function() {
                const id = opt.getAttribute('data-id');
                const sku = opt.getAttribute('data-sku');
                const desc = opt.getAttribute('data-descripcion');
                const precio = opt.getAttribute('data-precio');

                hidden.value = id;
                input.value = `${sku} - ${desc} ($${precio})`;
                dropdown.style.display = 'none';

                if (onSelectCallback) {
                    onSelectCallback(id, sku, desc, precio);
                }
            });
        });

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', function(e) {
            const container = input.closest('.searchable-select-container');
            if (container && !container.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    }

    // Función general para inicializar un buscador de clientes
    function inicializarBuscadorClientes(inputId, dropdownId, phoneInputId, optionClass, noResultsClass) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        const phoneInput = document.getElementById(phoneInputId);
        const options = dropdown.querySelectorAll('.' + optionClass);
        const noResults = dropdown.querySelector('.' + noResultsClass);

        // Mostrar dropdown al enfocar
        input.addEventListener('focus', function() {
            dropdown.style.display = 'block';
        });

        // Filtrar clientes al escribir
        input.addEventListener('input', function() {
            const query = input.value.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
            let matches = 0;

            options.forEach(opt => {
                const nombre = opt.getAttribute('data-nombre').normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
                const cel = opt.getAttribute('data-cel').normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();

                if (nombre.includes(query) || cel.includes(query)) {
                    opt.style.display = 'block';
                    matches++;
                } else {
                    opt.style.display = 'none';
                }
            });

            if (matches === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        });

        // Seleccionar una opción
        options.forEach(opt => {
            opt.addEventListener('click', function() {
                const nombre = opt.getAttribute('data-nombre');
                const cel = opt.getAttribute('data-cel');

                input.value = nombre;
                if (cel) {
                    phoneInput.value = cel;
                }
                dropdown.style.display = 'none';
            });
        });

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', function(e) {
            const container = input.closest('.searchable-select-container');
            if (container && !container.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    }

    // Inicializar buscadores
    inicializarBuscadorProductos(
        'producto_search_input', 
        'producto_search_dropdown', 
        'id_producto_hidden', 
        'producto-item-option', 
        'search-no-results'
    );

    inicializarBuscadorProductos(
        'edit_producto_search_input', 
        'edit_producto_search_dropdown', 
        'edit_id_producto_hidden', 
        'edit-producto-item-option', 
        'edit-search-no-results'
    );

    // Inicializar buscadores de clientes
    inicializarBuscadorClientes(
        'nombre_cliente',
        'cliente_search_dropdown',
        'contacto_cliente',
        'cliente-item-option',
        'cliente-search-no-results'
    );

    inicializarBuscadorClientes(
        'edit_nombre_cliente',
        'edit_cliente_search_dropdown',
        'edit_contacto_cliente',
        'edit-cliente-item-option',
        'edit-cliente-search-no-results'
    );

    // Abre modal de edición cargando dinámicamente los datos del encargo
    function abrirEditarEncargo(encargo) {
        const modal = new bootstrap.Modal(document.getElementById('modalEditarEncargo'));
        
        // Configurar acción del formulario dinámicamente con la URL de edición
        document.getElementById('formEditarEncargo').action = "<?= base_url('admin/encargos/editar/') ?>" + encargo.id;
        
        // Rellenar campos del formulario
        document.getElementById('edit_estado').value = encargo.estado;
        document.getElementById('edit_nombre_cliente').value = encargo.nombre_cliente;
        document.getElementById('edit_contacto_cliente').value = encargo.contacto_cliente || '';
        document.getElementById('edit_cantidad').value = encargo.cantidad;
        document.getElementById('edit_anticipo').value = encargo.anticipo;
        document.getElementById('edit_notas').value = encargo.notas || '';
        
        // Cargar producto seleccionado en el input de búsqueda editable
        const hiddenInput = document.getElementById('edit_id_producto_hidden');
        const searchInput = document.getElementById('edit_producto_search_input');
        if (encargo.id_producto) {
            hiddenInput.value = encargo.id_producto;
            const opt = document.querySelector(`.edit-producto-item-option[data-id="${encargo.id_producto}"]`);
            if (opt) {
                const sku = opt.getAttribute('data-sku');
                const desc = opt.getAttribute('data-descripcion');
                const precio = opt.getAttribute('data-precio');
                searchInput.value = `${sku} - ${desc} ($${precio})`;
            } else {
                searchInput.value = 'Producto no disponible en inventario';
            }
        } else {
            hiddenInput.value = '';
            searchInput.value = 'Producto eliminado';
        }
        
        modal.show();
    }

    // Auto-apertura y preselección del producto si se pasa por GET query params
    <?php 
    $autoOpenId = service('request')->getVar('nuevo_encargo_producto_id') ?: service('request')->getVar('producto_id');
    $shouldOpenModal = service('request')->getVar('nuevo_encargo_producto_id');
    if ($autoOpenId): 
    ?>
    document.addEventListener("DOMContentLoaded", function() {
        const id = "<?= $autoOpenId ?>";
        const opt = document.querySelector(`.producto-item-option[data-id="${id}"]`);
        if (opt) {
            const sku = opt.getAttribute('data-sku');
            const desc = opt.getAttribute('data-descripcion');
            const precio = opt.getAttribute('data-precio');
            document.getElementById('id_producto_hidden').value = id;
            document.getElementById('producto_search_input').value = `${sku} - ${desc} ($${precio})`;
        }
        
        <?php if ($shouldOpenModal): ?>
        const modalEl = document.getElementById('modalNuevoEncargo');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
        <?php endif; ?>
    });
    <?php endif; ?>

    // Deshabilitar botón de guardar y mostrar spinner al enviar formularios para evitar doble submit
    document.addEventListener("DOMContentLoaded", function() {
        const formNuevo = document.getElementById('formNuevoEncargo');
        if (formNuevo) {
            formNuevo.addEventListener('submit', function() {
                const btnSubmit = formNuevo.querySelector('button[type="submit"]');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando...';
                }
            });
        }

        const formEditar = document.getElementById('formEditarEncargo');
        if (formEditar) {
            formEditar.addEventListener('submit', function() {
                const btnSubmit = formEditar.querySelector('button[type="submit"]');
                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando...';
                }
            });
        }
    });
</script>
<?= $this->endSection() ?>
