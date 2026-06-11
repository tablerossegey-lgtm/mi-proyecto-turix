<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link class="styles-admin-theme" rel="stylesheet" href="<?= base_url('css/admin.css?v=1.2') ?>">
<div class="container py-4">
    <!-- Encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 admin-title">Control de Caja Chica</h2>
            <p class="text-muted mb-0">Visualiza y gestiona los ingresos y egresos diarios de tu negocio.</p>
            <div class="admin-subtitle-line" style="background-color: #ffc107;"></div>
        </div>
        <div>
            <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold d-inline-flex align-items-center gap-2 text-dark" data-bs-toggle="modal" data-bs-target="#modalNuevoMovimiento">
                <i class="fas fa-plus-circle"></i> Registrar Movimiento
            </button>
        </div>
    </div>

    <!-- Notificaciones flotantes tipo Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
        <?php if (session()->getFlashdata('success')): ?>
            <div id="toast-success" class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <div>
                            <strong>¡Exitoso!</strong><br>
                            <span class="small text-white-50"><?= esc(session()->getFlashdata('success')) ?></span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div id="toast-error" class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-circle-fill text-danger fs-5"></i>
                        <div>
                            <strong>¡Error!</strong><br>
                            <span class="small text-white-50"><?= esc(session()->getFlashdata('error')) ?></span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Balance / Resumen de Caja -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm admin-card p-3">
                <div class="admin-stat-card p-3 rounded-3" style="background: rgba(46, 204, 113, 0.08); border: 1px solid rgba(46, 204, 113, 0.15);">
                    <div class="admin-stat-label opacity-75 text-success" style="font-size: 0.85rem;"><i class="fas fa-arrow-circle-down me-1"></i> Total Ingresos</div>
                    <div class="fs-3 fw-bold text-success mt-1">$<?= number_format($totalIngresos, 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm admin-card p-3">
                <div class="admin-stat-card p-3 rounded-3" style="background: rgba(231, 76, 60, 0.08); border: 1px solid rgba(231, 76, 60, 0.15);">
                    <div class="admin-stat-label opacity-75 text-danger" style="font-size: 0.85rem;"><i class="fas fa-arrow-circle-up me-1"></i> Total Egresos</div>
                    <div class="fs-3 fw-bold text-danger mt-1">$<?= number_format($totalEgresos, 2) ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm admin-card p-3">
                <div class="admin-stat-card p-3 rounded-3" style="background: rgba(241, 196, 15, 0.08); border: 1px solid rgba(241, 196, 15, 0.15);">
                    <div class="admin-stat-label opacity-75 text-warning" style="font-size: 0.85rem;"><i class="fas fa-vault me-1"></i> Saldo en Caja</div>
                    <div class="fs-3 fw-bold text-warning mt-1">$<?= number_format($saldoCaja, 2) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de Movimientos -->
    <div class="card border-0 shadow-sm admin-card">
        <div class="card-header border-bottom border-secondary border-opacity-25 py-3 bg-transparent d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-white"><i class="fas fa-history me-2 text-warning"></i> Historial de Movimientos de Caja</h6>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead class="text-muted admin-table-thead">
                    <tr>
                        <th class="ps-4 py-3 admin-table-th" style="width: 120px;">Fecha</th>
                        <th class="py-3 admin-table-th">Concepto / Descripción</th>
                        <th class="py-3 text-center admin-table-th" style="width: 150px;">Tipo</th>
                        <th class="py-3 text-end admin-table-th" style="width: 180px;">Monto</th>
                        <th class="py-3 text-end pe-4 admin-table-th" style="width: 100px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($movimientos)): ?>
                        <?php foreach ($movimientos as $m): ?>
                            <tr class="admin-table-tr">
                                <td class="ps-4 py-3 text-white-50 small">
                                    <?= date('d/m/Y', strtotime($m['fecha'])) ?>
                                </td>
                                <td class="py-3 text-white fw-semibold">
                                    <?= esc($m['descripcion']) ?>
                                </td>
                                <td class="py-3 text-center">
                                    <?php if ($m['tipo'] === 'Ingreso'): ?>
                                        <span class="badge bg-success rounded-pill px-3 py-1.5 fw-bold" style="font-size: 0.75rem;">
                                            <i class="fas fa-arrow-down me-1"></i> Ingreso
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger rounded-pill px-3 py-1.5 fw-bold" style="font-size: 0.75rem;">
                                            <i class="fas fa-arrow-up me-1"></i> Egreso
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-end fw-bold <?= $m['tipo'] === 'Ingreso' ? 'text-success' : 'text-danger' ?>">
                                    <?= $m['tipo'] === 'Ingreso' ? '+' : '-' ?>$<?= number_format($m['monto'], 2) ?>
                                </td>
                                <td class="py-3 text-end pe-4">
                                    <button type="button"
                                            class="btn btn-danger btn-sm d-flex align-items-center justify-content-center rounded-3 p-2 ms-auto"
                                            title="Eliminar movimiento"
                                            onclick="confirmarEliminarMovimiento('<?= base_url('admin/caja/eliminar/' . $m['idMovimiento']) ?>')">
                                        <i class="fas fa-trash-alt text-white"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="fas fa-piggy-bank fs-1 text-muted opacity-35"></i>
                                </div>
                                <h6 class="fw-bold text-white-50">Sin movimientos registrados</h6>
                                <p class="mb-0 small">Aún no se han registrado ingresos o egresos de caja chica.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL: CONFIRMAR ELIMINACIÓN DE MOVIMIENTO -->
<div class="modal fade" id="modalConfirmarEliminarMovimiento" tabindex="-1" aria-labelledby="modalConfElimMovLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white" style="border-radius: 20px; background-color: #121824; border: 1px solid rgba(255,255,255,0.15);">
            <div class="modal-header modal-encargo-header py-3 px-4" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="modalConfElimMovLabel">
                    <i class="fas fa-exclamation-triangle text-danger"></i> ¿Eliminar movimiento?
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="mb-0 text-white-50" style="font-size: 0.95rem;">¿Estás seguro de que deseas eliminar este movimiento de caja? Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer d-flex gap-2 justify-content-center pb-4" style="border-top: none;">
                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <form id="formConfirmarEliminarMovimiento" action="" method="POST" class="d-inline" onsubmit="mostrarSpinner(this, 'Eliminando...')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: REGISTRAR MOVIMIENTO -->
<div class="modal fade" id="modalNuevoMovimiento" tabindex="-1" aria-labelledby="modalNuevoMovimientoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-encargo-content text-white" style="background-color: #1a252f;">
            <div class="modal-header modal-encargo-header py-3 px-4" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title fw-bold text-white" id="modalNuevoMovimientoLabel">
                    <i class="fas fa-plus-circle me-2 text-warning"></i> Registrar Movimiento de Caja
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevoMovimiento" action="<?= base_url('admin/caja/crear') ?>" method="POST" onsubmit="mostrarSpinner(this, 'Registrando...')">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Tipo de Movimiento -->
                        <div class="col-12">
                            <label class="form-label modal-encargo-label d-block">Tipo de Movimiento</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="tipo" id="tipo_ingreso" value="Ingreso" checked>
                                <label class="btn btn-outline-success fw-bold py-2" for="tipo_ingreso"><i class="fas fa-arrow-down me-1"></i> Ingreso</label>
                                
                                <input type="radio" class="btn-check" name="tipo" id="tipo_egreso" value="Egreso">
                                <label class="btn btn-outline-danger fw-bold py-2" for="tipo_egreso"><i class="fas fa-arrow-up me-1"></i> Egreso</label>
                            </div>
                        </div>

                        <!-- Fecha -->
                        <div class="col-12">
                            <label for="fecha" class="form-label modal-encargo-label">Fecha</label>
                            <input type="date" class="form-control modal-encargo-control text-white" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <!-- Descripción / Concepto -->
                        <div class="col-12">
                            <label for="descripcion" class="form-label modal-encargo-label">Concepto / Descripción *</label>
                            <input type="text" class="form-control modal-encargo-control text-white" id="descripcion" name="descripcion" required placeholder="Ej: Pago de luz local, Venta directa, etc.">
                        </div>

                        <!-- Monto -->
                        <div class="col-12">
                            <label for="monto" class="form-label modal-encargo-label">Monto ($) *</label>
                            <input type="number" step="0.01" min="0.01" class="form-control modal-encargo-control text-white" id="monto" name="monto" required placeholder="0.00">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer modal-encargo-footer d-flex gap-2" style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <button type="button" class="btn btn-outline-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark hover-shadow">
                        <i class="fas fa-save me-1"></i> Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    document.body.classList.add('admin-body');

    function initCajaToasts() {
        // Inicializar y mostrar toasts
        function inicializarToasts() {
            const toasts = document.querySelectorAll('.toast:not(.showing):not(.show)');
            toasts.forEach(toastEl => {
                if (typeof bootstrap !== 'undefined') {
                    const toast = new bootstrap.Toast(toastEl);
                    toast.show();
                    
                    // Eliminar el elemento al ocultarse para no llenar el DOM
                    toastEl.addEventListener('hidden.bs.toast', () => {
                        toastEl.remove();
                    });
                }
            });
        }
        inicializarToasts();
    }

    if (document.readyState === 'loading') {
        document.addEventListener("DOMContentLoaded", initCajaToasts);
    } else {
        initCajaToasts();
    }

    // Registrar la función globalmente para el onclick
    window.confirmarEliminarMovimiento = function(url) {
        const form = document.getElementById('formConfirmarEliminarMovimiento');
        if (form) {
            form.action = url;
        }
        const modalEl = document.getElementById('modalConfirmarEliminarMovimiento');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        } else {
            // Fallback
            if (confirm('¿Estás seguro de que deseas eliminar este movimiento de caja? Esta acción no se puede deshacer.')) {
                if (form) {
                    form.submit();
                }
            }
        }
    };
})();
</script>
<?= $this->endSection() ?>
