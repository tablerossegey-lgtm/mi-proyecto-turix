<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link class="styles-admin-theme" rel="stylesheet" href="<?= base_url('css/admin.css?v=1.3') ?>">

<style>
.btn-extraordinaria {
    background-color: #1f222b !important;
    color: #ffc107 !important;
    border: 1.5px solid #ffc107 !important;
    transition: all 0.25s ease-in-out !important;
}
.btn-extraordinaria i {
    color: #ffc107 !important;
    transition: color 0.25s ease-in-out !important;
}
.btn-extraordinaria:hover {
    background-color: #ffc107 !important;
    color: #1f222b !important;
    border-color: #ffc107 !important;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.35) !important;
}
.btn-extraordinaria:hover i {
    color: #1f222b !important;
}
</style>

<div id="fondos-container" class="container py-4">
    <!-- Encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 admin-title">
                <i class="bi bi-piggy-bank text-warning me-2"></i>Fondos de Ahorro
            </h2>
            <p class="text-muted mb-0">Gestión de apartados estratégicos de capital y aportaciones extraordinarias desde Caja Chica.</p>
            <div class="admin-subtitle-line" style="background-color: #ffc107;"></div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <!-- Botón de Aportación Extraordinaria (SIEMPRE DISPONIBLE) -->
            <button type="button" class="btn btn-extraordinaria rounded-pill px-4 fw-bold d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAportacionExtraordinaria">
                <i class="fas fa-plus-circle"></i> Registrar Aportación Extraordinaria
            </button>
            <!-- Botón de Configuración del Fondo -->
            <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold text-dark d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalConfigurarFondo">
                <i class="fas fa-sliders-h"></i> Configurar Fondo
            </button>
        </div>
    </div>

    <!-- Notificaciones flotantes tipo Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
        <?php if (session()->getFlashdata('success')): ?>
            <div id="toast-success" class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4500">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <div>
                            <strong>¡Operación Exitosa!</strong><br>
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
                            <strong>¡Atención!</strong><br>
                            <span class="small text-white-50"><?= esc(session()->getFlashdata('error')) ?></span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pestañas de Fondos (Preparado para múltiples fondos futuros) -->
    <?php if (count($fondos) > 1): ?>
        <ul class="nav nav-pills mb-4 gap-2">
            <?php foreach ($fondos as $f): ?>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-4 <?= $f['id'] == $fondoActual['id'] ? 'active bg-warning text-dark fw-bold' : 'bg-dark text-white-50' ?>" 
                       href="<?= base_url('admin/fondos?fondo=' . $f['id']) ?>">
                        <i class="fas fa-vault me-2"></i><?= esc($f['nombre']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- SECCIÓN SUPERIOR: TARJETA DEL FONDO Y CÁLCULO DEL MES -->
    <div class="row g-4 mb-4">
        <!-- 1. TARJETA PRINCIPAL DEL FONDO (BODEGA TURIXSHOP) -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm admin-card h-100 p-4 position-relative overflow-hidden">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-warning bg-opacity-25 text-warning px-3 py-1.5 rounded-pill mb-2 fw-semibold">
                            <i class="fas fa-warehouse me-1"></i> Fondo Destinado
                        </span>
                        <h3 class="fw-bold text-white mb-1"><?= esc($fondoActual['nombre']) ?></h3>
                        <p class="text-white-50 small mb-0"><?= esc($fondoActual['descripcion'] ?? 'Fondo de ahorro del negocio.') ?></p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-dark border border-secondary text-white-50 px-2.5 py-1.5 rounded-3">
                            <i class="fas fa-percent text-warning me-1"></i> Ahorro: <strong><?= number_format($fondoActual['porcentaje_sugerido'], 1) ?>%</strong>
                        </span>
                    </div>
                </div>

                <!-- Saldo acumulado gigante -->
                <div class="p-3 rounded-4 mb-3" style="background: rgba(255, 193, 7, 0.06); border: 1px solid rgba(255, 193, 7, 0.2);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-white-50 small text-uppercase fw-semibold tracking-wide">Saldo Acumulado en Fondo</span>
                            <h2 class="display-6 fw-bold text-warning mb-0 mt-1">$<?= number_format($saldoAcumulado, 2) ?></h2>
                        </div>
                        <div class="fs-1 text-warning opacity-50 pe-2">
                            <i class="fas fa-piggy-bank"></i>
                        </div>
                    </div>
                </div>

                <!-- Barra de Progreso de Meta Económica -->
                <?php if ($metaMonto > 0): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1 small">
                            <span class="text-white-50"><i class="fas fa-flag-checkered text-warning me-1"></i> Meta: <strong>$<?= number_format($metaMonto, 2) ?></strong></span>
                            <span class="fw-bold text-warning"><?= number_format($progresoMeta, 1) ?>% completado</span>
                        </div>
                        <div class="progress" style="height: 10px; background-color: rgba(255,255,255,0.1); border-radius: 6px;">
                            <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: <?= min(100, $progresoMeta) ?>%;" 
                                 aria-valuenow="<?= $progresoMeta ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Datos de Aportación, Último Registro y Dinero Disponible -->
                <div class="row g-2 mt-auto pt-2 border-top border-secondary border-opacity-25">
                    <div class="col-4">
                        <div class="text-white-50 small"><i class="fas fa-coins me-1 text-info"></i> Total Aportado:</div>
                        <div class="fw-bold text-white fs-6">$<?= number_format($totalAportado, 2) ?></div>
                    </div>
                    <div class="col-4">
                        <div class="text-white-50 small"><i class="fas fa-wallet me-1 text-warning"></i> Caja Disponible:</div>
                        <div class="fw-bold text-warning fs-6 font-monospace">$<?= number_format($saldoDisponibleCaja, 2) ?></div>
                    </div>
                    <div class="col-4">
                        <div class="text-white-50 small"><i class="fas fa-calendar-check me-1 text-success"></i> Último Aporte:</div>
                        <div class="fw-bold text-white fs-6">
                            <?php if ($ultimaAportacion): ?>
                                $<?= number_format($ultimaAportacion['monto_aportado'], 2) ?> 
                                <span class="text-white-50 fw-normal small">(<?= date('d/m/Y', strtotime($ultimaAportacion['fecha_registro'])) ?>)</span>
                            <?php else: ?>
                                <span class="text-white-50 fw-normal small">Sin aportes</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. CÁLCULO DEL MES Y ACCIÓN DE APORTACIÓN MENSUAL -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm admin-card h-100 p-4">
                <!-- Selector de Periodo -->
                <form action="<?= base_url('admin/fondos') ?>" method="GET" class="row g-2 align-items-center mb-3">
                    <input type="hidden" name="fondo" value="<?= esc($fondoActual['id']) ?>">
                    <div class="col-7">
                        <label class="text-white-50 small mb-1"><i class="fas fa-calendar-alt text-warning me-1"></i> Seleccionar Mes:</label>
                        <select name="mes" class="form-select form-select-sm bg-dark text-white border-secondary" onchange="this.form.submit()">
                            <?php foreach ($meses as $numMes => $txtMes): ?>
                                <option value="<?= $numMes ?>" <?= $numMes == $mesActual ? 'selected' : '' ?>>
                                    <?= $txtMes ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-5">
                        <label class="text-white-50 small mb-1">Año:</label>
                        <select name="anio" class="form-select form-select-sm bg-dark text-white border-secondary" onchange="this.form.submit()">
                            <?php for ($y = date('Y') + 1; $y >= 2024; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == $anioActual ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </form>

                <!-- Desglose de Caja Chica del Mes Seleccionado -->
                <div class="bg-dark bg-opacity-50 p-3 rounded-3 border border-secondary border-opacity-25 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-white-50 small">Periodo analizado:</span>
                        <span class="badge bg-secondary text-white fw-bold px-3 py-1">
                            <?= esc($nombreMesActual) ?> <?= $anioActual ?>
                        </span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-secondary border-opacity-10">
                        <span class="text-white-50 small"><i class="fas fa-arrow-down text-success me-1"></i> Ingresos de Caja Chica:</span>
                        <span class="text-success fw-bold">$<?= number_format($ingresosMes, 2) ?></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-secondary border-opacity-10">
                        <span class="text-white-50 small"><i class="fas fa-arrow-up text-danger me-1"></i> Egresos de Caja Chica:</span>
                        <span class="text-danger fw-bold">$<?= number_format($egresosMes, 2) ?></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-2 mt-1">
                        <span class="text-white fw-semibold"><i class="fas fa-scale-balanced text-warning me-1"></i> Balance Neto del Mes:</span>
                        <span class="fw-bold fs-5 <?= $balanceNeto >= 0 ? 'text-white' : 'text-danger' ?>">
                            $<?= number_format($balanceNeto, 2) ?>
                        </span>
                    </div>
                </div>

                <!-- Caja de Aportación Sugerida / Estimada -->
                <div class="p-3 rounded-3 mb-3" style="background: rgba(46, 204, 113, 0.07); border: 1px dashed rgba(46, 204, 113, 0.35);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <?php if ($esMesEnCurso): ?>
                                <span class="text-white-50 small">Aportación estimada al momento (<?= number_format($porcentaje, 1) ?>% del balance):</span>
                            <?php else: ?>
                                <span class="text-white-50 small">Aportación Sugerida (<?= number_format($porcentaje, 1) ?>% del balance):</span>
                            <?php endif; ?>
                            <div class="fs-4 fw-bold text-success mt-1">$<?= number_format($aportacionSugerida, 2) ?></div>
                        </div>
                        <div class="text-end">
                            <?php if ($esMesEnCurso): ?>
                                <!-- MES EN CURSO: Botón deshabilitado -->
                                <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold text-white-50 shadow-sm" disabled title="El mes aún está en curso">
                                    <i class="fas fa-clock me-1"></i> Registrar aportación
                                </button>
                            <?php elseif ($esMesFuturo): ?>
                                <!-- MES FUTURO: No disponible -->
                                <span class="badge bg-secondary text-white-50 px-3 py-2 rounded-pill">
                                    Periodo futuro
                                </span>
                            <?php elseif ($yaAportado): ?>
                                <!-- MES PASADO YA APORTADO -->
                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-2 rounded-pill fw-bold">
                                    <i class="bi bi-check-circle-fill me-1"></i> Periodo Aportado
                                </span>
                            <?php elseif ($balanceNeto <= 0): ?>
                                <!-- MES PASADO SIN BALANCE POSITIVO -->
                                <span class="badge bg-secondary text-white-50 px-3 py-2 rounded-pill">
                                    Sin balance positivo
                                </span>
                            <?php else: ?>
                                <!-- MES PASADO FINALIZADO Y PENDIENTE: Habilitado -->
                                <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold text-dark d-inline-flex align-items-center gap-2 shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#modalRegistrarAportacion">
                                    <i class="fas fa-hand-holding-usd"></i> Registrar aportación
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Avisos Informativos según el estado del periodo -->
                <?php if ($esMesEnCurso): ?>
                    <div class="p-3 rounded-3 border d-flex align-items-center gap-3 mb-0" style="background: rgba(13, 202, 240, 0.08); border-color: rgba(13, 202, 240, 0.25) !important;">
                        <i class="bi bi-info-circle-fill text-info fs-5 flex-shrink-0"></i>
                        <div class="small" style="color: #cbd5e1; line-height: 1.45;">
                            El mes aún está en curso. La aportación mensual podrá registrarse una vez finalizado el periodo. Si deseas apartar dinero en este momento, utiliza el botón de <strong>Aportación Extraordinaria</strong>.
                        </div>
                    </div>
                <?php elseif ($yaAportado && $aportacionRegistrada): ?>
                    <div class="p-3 rounded-3 border d-flex align-items-center gap-3 mb-0" style="background: rgba(255, 193, 7, 0.08); border-color: rgba(255, 193, 7, 0.3) !important;">
                        <i class="bi bi-info-circle-fill text-warning fs-5 flex-shrink-0"></i>
                        <div class="small" style="color: #f1f5f9; line-height: 1.45;">
                            Aportaste <strong class="text-warning fw-bold">$<?= number_format($aportacionRegistrada['monto_aportado'], 2) ?></strong> el <strong class="text-white fw-bold"><?= date('d/m/Y', strtotime($aportacionRegistrada['fecha_registro'])) ?></strong>. Para mantener la integridad contable, el sistema no permite duplicar la aportación mensual del mismo mes.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- SECCIÓN INFERIOR: HISTORIAL DE APORTACIONES -->
    <div class="card border-0 shadow-sm admin-card">
        <div class="card-header border-bottom border-secondary border-opacity-25 py-3 bg-transparent d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-white">
                <i class="fas fa-history me-2 text-warning"></i> Historial de Aportaciones al Fondo
            </h6>
            <span class="badge bg-dark text-white-50 border border-secondary border-opacity-50">
                <?= count($historial) ?> registro(s)
            </span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead class="text-muted admin-table-thead">
                    <tr>
                        <th class="ps-4 py-3 admin-table-th">Periodo (Mes/Año)</th>
                        <th class="py-3 text-center admin-table-th">Tipo</th>
                        <th class="py-3 text-end admin-table-th">Balance Neto Caja</th>
                        <th class="py-3 text-center admin-table-th">% Aplicado</th>
                        <th class="py-3 text-end admin-table-th">Aportación Sugerida</th>
                        <th class="py-3 text-end admin-table-th">Aportación Real</th>
                        <th class="py-3 text-end admin-table-th">Saldo Acumulado</th>
                        <th class="py-3 text-center admin-table-th">Fecha Registro</th>
                        <th class="py-3 text-end pe-4 admin-table-th" style="width: 80px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($historial)): ?>
                        <?php foreach ($historial as $h): ?>
                            <tr class="admin-table-tr">
                                <td class="ps-4 py-3 text-white fw-bold">
                                    <i class="fas fa-calendar-check text-warning me-2"></i>
                                    <?= ($meses[$h['mes']] ?? $h['mes']) . ' ' . $h['anio'] ?>
                                </td>
                                <td class="py-3 text-center">
                                    <?php if ($h['tipo_aportacion'] === 'Extraordinaria'): ?>
                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2.5 py-1">
                                            <i class="fas fa-star me-1 small"></i>Extraordinaria
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-50 px-2.5 py-1">
                                            <i class="fas fa-calendar-alt me-1 small"></i>Mensual
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-end text-white-50 font-monospace">
                                    <?= $h['tipo_aportacion'] === 'Extraordinaria' ? '<span class="opacity-50">—</span>' : '$' . number_format($h['balance_caja'], 2) ?>
                                </td>
                                <td class="py-3 text-center">
                                    <?php if ($h['tipo_aportacion'] === 'Extraordinaria'): ?>
                                        <span class="text-white-50 opacity-50">—</span>
                                    <?php else: ?>
                                        <span class="badge bg-dark border border-secondary text-warning">
                                            <?= number_format($h['porcentaje_aplicado'], 1) ?>%
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-end text-white-50 font-monospace">
                                    <?= $h['tipo_aportacion'] === 'Extraordinaria' ? '<span class="opacity-50">—</span>' : '$' . number_format($h['monto_sugerido'], 2) ?>
                                </td>
                                <td class="py-3 text-end fw-bold text-success font-monospace fs-6">
                                    +$<?= number_format($h['monto_aportado'], 2) ?>
                                </td>
                                <td class="py-3 text-end fw-bold text-warning font-monospace">
                                    $<?= number_format($h['saldo_acumulado'], 2) ?>
                                </td>
                                <td class="py-3 text-center text-white-50 small">
                                    <?= date('d/m/Y', strtotime($h['fecha_registro'])) ?>
                                </td>
                                <td class="py-3 text-end pe-4">
                                    <button type="button" 
                                            class="btn btn-outline-danger btn-sm rounded-3 p-1.5" 
                                            title="Eliminar aportación"
                                            onclick="confirmarEliminarAportacion('<?= base_url('admin/fondos/eliminar/' . $h['id']) ?>', '<?= ($meses[$h['mes']] ?? $h['mes']) . ' ' . $h['anio'] ?> (<?= esc($h['tipo_aportacion']) ?>)', '<?= number_format($h['monto_aportado'], 2) ?>')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="fas fa-piggy-bank fs-1 text-muted opacity-35"></i>
                                </div>
                                <h6 class="fw-bold text-white-50">Sin aportaciones registradas</h6>
                                <p class="mb-0 small">Selecciona un periodo con balance positivo o realiza una aportación extraordinaria.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: REGISTRAR APORTACIÓN MENSUAL -->
<!-- ========================================== -->
<div class="modal fade" id="modalRegistrarAportacion" tabindex="-1" aria-labelledby="modalRegistrarAportacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary text-white shadow-lg">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold" id="modalRegistrarAportacionLabel">
                    <i class="fas fa-hand-holding-usd text-warning me-2"></i>Registrar Aportación Mensual al Fondo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formRegistrarAportacion" action="<?= base_url('admin/fondos/aportar') ?>" method="POST" onsubmit="mostrarSpinner(this, 'Guardando...')">
                <?= csrf_field() ?>
                <input type="hidden" name="id_fondo" value="<?= esc($fondoActual['id']) ?>">
                <input type="hidden" name="anio" value="<?= $anioActual ?>">
                <input type="hidden" name="mes" value="<?= $mesActual ?>">

                <div class="modal-body p-4">
                    <div class="alert alert-secondary bg-dark border border-secondary border-opacity-50 small mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-white-50">Fondo de Destino:</span>
                            <strong class="text-warning"><?= esc($fondoActual['nombre']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-white-50">Periodo a liquidar:</span>
                            <strong class="text-white"><?= esc($nombreMesActual) ?> <?= $anioActual ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-white-50">Balance Neto Caja Chica:</span>
                            <strong class="text-white">$<?= number_format($balanceNeto, 2) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-white-50">Porcentaje Aplicado:</span>
                            <strong class="text-warning"><?= number_format($porcentaje, 1) ?>%</strong>
                        </div>
                        <div class="d-flex justify-content-between pt-1 border-top border-secondary border-opacity-25">
                            <span class="text-white-50">Disponible en Caja Chica:</span>
                            <strong class="text-info font-monospace">$<?= number_format($saldoDisponibleCaja, 2) ?></strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="monto_aportado" class="form-label text-white fw-semibold">
                            Monto a Aportar ($) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-secondary bg-opacity-25 border-secondary text-warning fw-bold">$</span>
                            <input type="number" 
                                   class="form-control bg-dark text-white border-secondary fs-5 fw-bold text-success" 
                                   id="monto_aportado" 
                                   name="monto_aportado" 
                                   step="0.01" 
                                   min="0.01" 
                                   max="<?= max(0.01, $saldoDisponibleCaja) ?>"
                                   value="<?= number_format($aportacionSugerida, 2, '.', '') ?>" 
                                   required>
                        </div>
                        <div class="form-text text-white-50 small">
                            Aportación sugerida calculada: <strong>$<?= number_format($aportacionSugerida, 2) ?></strong>. Puedes ajustar o redondear el monto si lo requieres.
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="notas_aportacion" class="form-label text-white-50 small">Notas u Observaciones (Opcional):</label>
                        <input type="text" 
                               class="form-control form-control-sm bg-dark text-white border-secondary" 
                               id="notas_aportacion" 
                               name="notas" 
                               placeholder="Ej. Apartado correspondiente a Septiembre 2026">
                    </div>
                </div>

                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">
                        <i class="fas fa-check-circle me-1"></i> Confirmar y Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: REGISTRAR APORTACIÓN EXTRAORDINARIA -->
<!-- ========================================== -->
<div class="modal fade" id="modalAportacionExtraordinaria" tabindex="-1" aria-labelledby="modalAportacionExtraordinariaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary text-white shadow-lg">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold" id="modalAportacionExtraordinariaLabel">
                    <i class="fas fa-star text-warning me-2"></i>Registrar Aportación Extraordinaria
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formRegistrarAportacionExtraordinaria" action="<?= base_url('admin/fondos/aportar-extraordinaria') ?>" method="POST" onsubmit="mostrarSpinner(this, 'Guardando...')">
                <?= csrf_field() ?>
                <input type="hidden" name="id_fondo" value="<?= esc($fondoActual['id']) ?>">

                <div class="modal-body p-4">
                    <div class="alert alert-secondary bg-dark border border-secondary border-opacity-50 small mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-white-50">Fondo de Destino:</span>
                            <strong class="text-warning"><?= esc($fondoActual['nombre']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-white-50">Tipo de Aportación:</span>
                            <strong class="text-success"><i class="fas fa-star me-1 small"></i>Extraordinaria (Voluntaria)</strong>
                        </div>
                        <div class="d-flex justify-content-between pt-1 border-top border-secondary border-opacity-25">
                            <span class="text-white-50">Disponible en Caja Chica:</span>
                            <strong class="text-info font-monospace">$<?= number_format($saldoDisponibleCaja, 2) ?></strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="fecha_extraordinaria" class="form-label text-white-50 small mb-1">
                            Fecha de Aportación: <span class="text-danger">*</span>
                        </label>
                        <input type="date" 
                               class="form-control bg-dark text-white border-secondary" 
                               id="fecha_extraordinaria" 
                               name="fecha_registro" 
                               value="<?= date('Y-m-d') ?>" 
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="monto_extraordinario" class="form-label text-white fw-semibold">
                            Monto a Aportar ($) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-secondary bg-opacity-25 border-secondary text-warning fw-bold">$</span>
                            <input type="number" 
                                   class="form-control bg-dark text-white border-secondary fs-5 fw-bold text-success" 
                                   id="monto_extraordinario" 
                                   name="monto_aportado" 
                                   step="0.01" 
                                   min="0.01" 
                                   max="<?= max(0.01, $saldoDisponibleCaja) ?>" 
                                   placeholder="0.00" 
                                   required>
                        </div>
                        <div class="form-text text-white-50 small">
                            Indica la cantidad voluntaria a transferir de Caja Chica a este fondo.
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="notas_extraordinaria" class="form-label text-white-50 small">Notas u Observaciones (Opcional):</label>
                        <input type="text" 
                               class="form-control form-control-sm bg-dark text-white border-secondary" 
                               id="notas_extraordinaria" 
                               name="notas" 
                               placeholder="Ej. Aportación adicional por buenas ventas">
                    </div>
                </div>

                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">
                        <i class="fas fa-check-circle me-1"></i> Confirmar y Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: CONFIGURAR FONDO Y METAS -->
<!-- ========================================== -->
<div class="modal fade" id="modalConfigurarFondo" tabindex="-1" aria-labelledby="modalConfigurarFondoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-secondary text-white shadow-lg">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold" id="modalConfigurarFondoLabel">
                    <i class="fas fa-sliders-h text-warning me-2"></i>Configuración del Fondo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/fondos/configurar') ?>" method="POST" onsubmit="mostrarSpinner(this, 'Guardando...')">
                <?= csrf_field() ?>
                <input type="hidden" name="id_fondo" value="<?= esc($fondoActual['id']) ?>">

                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-white-50 small">Nombre del Fondo:</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary" value="<?= esc($fondoActual['nombre']) ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="porcentaje_sugerido" class="form-label text-white fw-semibold">
                            Porcentaje Sugerido de Ahorro (%):
                        </label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control bg-dark text-white border-secondary fw-bold text-warning" 
                                   id="porcentaje_sugerido" 
                                   name="porcentaje_sugerido" 
                                   step="0.1" 
                                   min="0" 
                                   max="100" 
                                   value="<?= number_format($fondoActual['porcentaje_sugerido'], 1, '.', '') ?>" 
                                   required>
                            <span class="input-group-text bg-secondary bg-opacity-25 border-secondary text-warning fw-bold">%</span>
                        </div>
                        <div class="form-text text-white-50 small">
                            Porcentaje del balance mensual de Caja Chica que se sugerirá transferir al fondo (ej. 10.0%, 15.0%).
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="meta_monto" class="form-label text-white fw-semibold">
                            Meta Económica del Fondo ($):
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-secondary bg-opacity-25 border-secondary text-warning fw-bold">$</span>
                            <input type="number" 
                                   class="form-control bg-dark text-white border-secondary fw-bold" 
                                   id="meta_monto" 
                                   name="meta_monto" 
                                   step="0.01" 
                                   min="0" 
                                   value="<?= number_format($fondoActual['meta_monto'], 2, '.', '') ?>">
                        </div>
                        <div class="form-text text-white-50 small">
                            Monto objetivo deseado (ej. $30,000.00). Se reflejará automáticamente en la barra de progreso. Deja en 0 si no deseas meta.
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="descripcion_fondo" class="form-label text-white-50 small">Descripción / Propósito:</label>
                        <textarea class="form-control bg-dark text-white border-secondary" 
                                  id="descripcion_fondo" 
                                  name="descripcion" 
                                  rows="2"><?= esc($fondoActual['descripcion']) ?></textarea>
                    </div>
                </div>

                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">
                        <i class="fas fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: CONFIRMAR ELIMINACIÓN DE APORTACIÓN -->
<!-- ========================================== -->
<div class="modal fade" id="modalEliminarAportacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border border-danger text-white shadow-lg">
            <div class="modal-header border-danger border-opacity-25">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Eliminar Registro de Aportación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-2 text-white">¿Estás seguro de que deseas eliminar la aportación de <strong id="txtElimPeriodo" class="text-warning"></strong> por un importe de <strong id="txtElimMonto" class="text-success"></strong>?</p>
                <p class="mb-0 text-white-50 small">Esta acción descontará el saldo acumulado en el fondo y habilitará nuevamente el botón para registrar la aportación correspondiente a ese mes si era mensual.</p>
            </div>
            <div class="modal-footer border-danger border-opacity-25">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 text-white" data-bs-dismiss="modal">Cancelar</button>
                <a id="btnConfirmarElimAportacion" href="#" class="btn btn-danger rounded-pill px-4 fw-bold" onclick="this.innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span> Eliminando...'; this.classList.add('disabled');">
                    <i class="fas fa-trash-alt me-1"></i> Sí, Eliminar
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Inicializar Toasts de Bootstrap si existen
    var toastElements = document.querySelectorAll('.toast');
    toastElements.forEach(function (el) {
        var toast = new bootstrap.Toast(el);
        toast.show();
    });
});

function confirmarEliminarAportacion(url, periodo, monto) {
    document.getElementById('txtElimPeriodo').textContent = periodo;
    document.getElementById('txtElimMonto').textContent = '$' + monto;
    document.getElementById('btnConfirmarElimAportacion').setAttribute('href', url);
    var modal = new bootstrap.Modal(document.getElementById('modalEliminarAportacion'));
    modal.show();
}
</script>
<?= $this->endSection() ?>
