<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link class="styles-admin-theme" rel="stylesheet" href="<?= base_url('css/admin.css?v=1.4') ?>">

<?php
$badgeEstadoDinamica = [
    'borrador'                 => 'bg-secondary',
    'programada'               => 'bg-info text-dark',
    'activa'                   => 'bg-success',
    'participaciones_cerradas' => 'bg-warning text-dark',
    'sorteo_realizado'         => 'bg-primary',
    'finalizada'               => 'bg-dark border border-secondary',
    'cancelada'                => 'bg-danger',
];
$badgeEntrega = [
    'pendiente_asignar'  => 'bg-secondary',
    'ganador_registrado' => 'bg-info text-dark',
    'ganador_notificado' => 'bg-primary',
    'pendiente_entrega'  => 'bg-warning text-dark',
    'entregado'          => 'bg-success',
];
$urlAppSorteos = !empty($dinamica['url_plataforma']) ? $dinamica['url_plataforma'] : 'https://app-sorteos.com/es';
$listadoSorteoTexto = implode("\n", $lineasSorteo);
$listadoRestantesTexto = implode("\n", $lineasRestantes);
?>

<div class="container py-4">
    <!-- Toasts -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;" id="dinamica-toast-container">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="toast show align-items-center text-white bg-dark border-0 shadow-lg rounded-3" role="alert">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle text-success fs-5"></i>
                        <div><?= esc(session()->getFlashdata('success')) ?></div>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
                </div>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="toast show align-items-center text-white bg-dark border-0 shadow-lg rounded-3" role="alert">
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-circle text-danger fs-5"></i>
                        <div><?= esc(session()->getFlashdata('error')) ?></div>
                    </div>
                    <button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Encabezado -->
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                <h2 class="fw-bold mb-0 admin-title"><?= esc($dinamica['nombre']) ?></h2>
                <span class="badge <?= $badgeEstadoDinamica[$dinamica['estado']] ?? 'bg-secondary' ?> rounded-pill">
                    <?= esc($estadosDinamica[$dinamica['estado']] ?? $dinamica['estado']) ?>
                </span>
                <?php if (!empty($dinamica['listado_cerrado'])): ?>
                    <span class="badge bg-warning text-dark rounded-pill">
                        <i class="bi bi-lock-fill me-1"></i> Listado definitivo cerrado
                    </span>
                <?php endif; ?>
            </div>
            <p class="text-muted mb-0 small">
                Periodo: <strong><?= date('d/m/Y', strtotime($dinamica['fecha_inicio'])) ?></strong> al <strong><?= date('d/m/Y', strtotime($dinamica['fecha_fin'])) ?></strong>
                <?php if (!empty($dinamica['fecha_sorteo'])): ?>
                    · Sorteo: <strong><?= date('d/m/Y', strtotime($dinamica['fecha_sorteo'])) ?></strong>
                    <?= !empty($dinamica['hora_sorteo']) ? date('g:i a', strtotime($dinamica['hora_sorteo'])) : '' ?>
                <?php endif; ?>
            </p>
            <div class="admin-subtitle-line" style="background-color: #ffc107;"></div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if (empty($dinamica['listado_cerrado']) && $dinamica['tipo_requisito'] !== 'manual_sin_compra'): ?>
                <form action="<?= base_url('admin/dinamicas/sincronizar/' . $dinamica['id']) ?>" method="POST" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="tab" value="<?= esc($tabActiva) ?>">
                    <button type="submit" class="btn btn-success rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-2">
                        <i class="bi bi-arrow-repeat"></i> Actualizar participantes
                    </button>
                </form>
            <?php elseif (!empty($dinamica['listado_cerrado'])): ?>
                <button type="button" class="btn btn-outline-warning rounded-pill px-3 fw-semibold"
                        data-bs-toggle="modal" data-bs-target="#modalSincronizarForzado">
                    <i class="bi bi-shield-exclamation me-1"></i> Corrección posterior al cierre
                </button>
            <?php endif; ?>

            <?php if (empty($dinamica['listado_cerrado']) && !in_array($dinamica['estado'], ['finalizada', 'cancelada'], true)): ?>
                <button type="button" class="btn btn-warning text-dark rounded-pill px-3 fw-bold"
                        data-bs-toggle="modal" data-bs-target="#modalCerrarParticipaciones">
                    <i class="bi bi-lock-fill me-1"></i> Cerrar participaciones
                </button>
            <?php endif; ?>

            <button type="button" class="btn btn-outline-info rounded-pill px-3 fw-semibold"
                    data-bs-toggle="modal" data-bs-target="#modalEditarDinamica">
                <i class="bi bi-pencil-square me-1"></i> Editar
            </button>
            <a href="<?= base_url('admin/dinamicas') ?>" class="btn btn-outline-dark rounded-pill px-3 fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Volver
            </a>
        </div>
    </div>

    <!-- 8 Indicadores Principales -->
    <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
        <div class="col">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #22c55e !important; background: #1f222b !important;">
                <div class="admin-stat-label"><i class="bi bi-people-fill text-success me-1"></i> Clientes participantes</div>
                <div class="admin-stat-value text-success"><?= (int) $indicadores['clientes_participantes'] ?></div>
            </div>
        </div>
        <div class="col">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #38bdf8 !important; background: #1f222b !important;">
                <div class="admin-stat-label"><i class="bi bi-stars text-info me-1"></i> Total participaciones</div>
                <div class="admin-stat-value text-info"><?= (int) $indicadores['total_participaciones'] ?></div>
            </div>
        </div>
        <div class="col">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #ffc107 !important; background: #1f222b !important;">
                <div class="admin-stat-label"><i class="bi bi-hourglass-split text-warning me-1"></i> Próximos a cumplir</div>
                <div class="admin-stat-value text-warning"><?= (int) $indicadores['proximos_cumplir'] ?></div>
            </div>
        </div>
        <div class="col">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #a855f7 !important; background: #1f222b !important;">
                <div class="admin-stat-label"><i class="bi bi-cash-stack me-1" style="color:#c084fc;"></i> Compras válidas</div>
                <div class="admin-stat-value" style="color:#c084fc;">$<?= number_format((float) $indicadores['importe_total_valido'], 2) ?></div>
            </div>
        </div>
        <div class="col">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #10b981 !important; background: #1f222b !important;">
                <div class="admin-stat-label"><i class="fab fa-whatsapp text-success me-1"></i> Notificados</div>
                <div class="admin-stat-value text-success"><?= (int) $indicadores['notificados'] ?></div>
            </div>
        </div>
        <div class="col">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #f97316 !important; background: #1f222b !important;">
                <div class="admin-stat-label"><i class="bi bi-bell-slash me-1" style="color:#fb923c;"></i> Pendientes notificar</div>
                <div class="admin-stat-value" style="color:#fb923c;"><?= (int) $indicadores['pendientes_notificar'] ?></div>
            </div>
        </div>
        <div class="col">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #eab308 !important; background: #1f222b !important;">
                <div class="admin-stat-label"><i class="bi bi-gift-fill text-warning me-1"></i> Premios registrados</div>
                <div class="admin-stat-value text-warning"><?= (int) $indicadores['premios_registrados'] ?></div>
            </div>
        </div>
        <div class="col">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #22c55e !important; background: #1f222b !important;">
                <div class="admin-stat-label"><i class="bi bi-check2-all text-success me-1"></i> Premios entregados</div>
                <div class="admin-stat-value text-success"><?= (int) $indicadores['premios_entregados'] ?></div>
            </div>
        </div>
    </div>

    <!-- Navegación por Pestañas -->
    <ul class="nav nav-pills mb-4 gap-2" id="tabsDinamica" role="tablist">
        <li class="nav-item">
            <button class="nav-link <?= $tabActiva === 'resumen' ? 'active bg-warning text-dark fw-bold' : 'bg-dark text-white-50 border border-secondary border-opacity-25' ?> rounded-pill px-4"
                    data-bs-toggle="pill" data-bs-target="#pane-resumen" type="button">
                <i class="bi bi-info-circle me-1"></i> Resumen general
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $tabActiva === 'participantes' ? 'active bg-warning text-dark fw-bold' : 'bg-dark text-white-50 border border-secondary border-opacity-25' ?> rounded-pill px-4"
                    data-bs-toggle="pill" data-bs-target="#pane-participantes" type="button">
                <i class="bi bi-people me-1"></i> Participantes (<?= (int) $indicadores['clientes_participantes'] ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $tabActiva === 'seguimiento' ? 'active bg-warning text-dark fw-bold' : 'bg-dark text-white-50 border border-secondary border-opacity-25' ?> rounded-pill px-4"
                    data-bs-toggle="pill" data-bs-target="#pane-seguimiento" type="button">
                <i class="bi bi-graph-up-arrow me-1"></i> Seguimiento de compras (<?= (int) $indicadores['proximos_cumplir'] ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $tabActiva === 'premios' ? 'active bg-warning text-dark fw-bold' : 'bg-dark text-white-50 border border-secondary border-opacity-25' ?> rounded-pill px-4"
                    data-bs-toggle="pill" data-bs-target="#pane-premios" type="button">
                <i class="bi bi-gift me-1"></i> Premios (<?= (int) $indicadores['premios_registrados'] ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link <?= $tabActiva === 'ganadores' ? 'active bg-warning text-dark fw-bold' : 'bg-dark text-white-50 border border-secondary border-opacity-25' ?> rounded-pill px-4"
                    data-bs-toggle="pill" data-bs-target="#pane-ganadores" type="button">
                <i class="bi bi-trophy me-1"></i> Ganadores (<?= count($ganadores) ?>)
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- 1. RESUMEN GENERAL + PREPARAR SORTEO -->
        <div class="tab-pane fade <?= $tabActiva === 'resumen' ? 'show active' : '' ?>" id="pane-resumen">
            <div class="row g-4">
                <div class="col-12 col-lg-7">
                    <div class="card border-0 shadow-sm admin-card p-4 mb-4">
                        <h5 class="fw-bold text-white mb-3"><i class="bi bi-sliders text-warning me-2"></i>Configuración de la dinámica</h5>
                        <?php if (!empty($dinamica['imagen_promo'])): ?>
                            <div class="mb-3">
                                <img src="<?= base_url($dinamica['imagen_promo']) ?>" alt="Imagen promocional"
                                     class="img-fluid rounded-3 border border-secondary border-opacity-25" style="max-height: 220px; object-fit: cover;">
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($dinamica['descripcion'])): ?>
                            <p class="text-white-50 mb-3"><?= nl2br(esc($dinamica['descripcion'])) ?></p>
                        <?php endif; ?>

                        <div class="row g-3 small">
                            <div class="col-6">
                                <div class="text-white-50">Requisito de participación</div>
                                <div class="text-white fw-semibold"><?= esc($tiposRequisito[$dinamica['tipo_requisito']] ?? $dinamica['tipo_requisito']) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-white-50">Importe mínimo</div>
                                <div class="text-warning fw-bold fs-6">$<?= number_format((float) $dinamica['importe_minimo'], 2) ?> MXN</div>
                            </div>
                            <div class="col-6">
                                <div class="text-white-50">Máx. participaciones/cliente</div>
                                <div class="text-white fw-semibold"><?= $dinamica['max_participaciones_cliente'] ? (int) $dinamica['max_participaciones_cliente'] : 'Sin límite' ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-white-50">Máx. premios/cliente</div>
                                <div class="text-white fw-semibold"><?= $dinamica['max_premios_cliente'] ? (int) $dinamica['max_premios_cliente'] : 'Múltiples premios permitidos' ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-white-50">Modalidad del sorteo</div>
                                <div class="text-white fw-semibold">
                                    <?= esc($modalidades[$dinamica['modalidad_sorteo']] ?? $dinamica['modalidad_sorteo']) ?>
                                    <?= !empty($dinamica['plataforma_digital']) ? ' · ' . esc($dinamica['plataforma_digital']) : '' ?>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-white-50">Compra a crédito</div>
                                <div class="text-white fw-semibold">
                                    <?= !empty($dinamica['requiere_compra_liquidada']) ? 'Exige compra liquidada' : 'Cuenta desde su fecha original' ?>
                                </div>
                            </div>
                            <?php if (!empty($dinamica['listado_cerrado'])): ?>
                                <div class="col-12 mt-2">
                                    <div class="alert alert-warning bg-warning bg-opacity-10 border-warning border-opacity-25 text-warning mb-0">
                                        <i class="bi bi-lock-fill me-1"></i>
                                        Listado cerrado el <strong><?= date('d/m/Y H:i', strtotime($dinamica['cerrado_en'])) ?></strong>
                                        por <strong><?= esc($dinamica['cerrado_por_nombre']) ?></strong>.
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Cambiar Estado -->
                        <hr class="border-secondary border-opacity-25 my-4">
                        <form action="<?= base_url('admin/dinamicas/estado/' . $dinamica['id']) ?>" method="POST" class="row g-2 align-items-end">
                            <?= csrf_field() ?>
                            <div class="col-12 col-md-5">
                                <label class="form-label text-white-50 small mb-1">Cambiar estado de la dinámica</label>
                                <select name="estado" class="form-select form-select-sm bg-dark text-white border-secondary">
                                    <?php foreach ($estadosDinamica as $k => $lbl): ?>
                                        <option value="<?= esc($k) ?>" <?= $k === $dinamica['estado'] ? 'selected' : '' ?>><?= esc($lbl) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label text-white-50 small mb-1">Resolución / motivo (opcional)</label>
                                <input type="text" name="resolucion_cierre" class="form-control form-control-sm bg-dark text-white border-secondary"
                                       placeholder="Obligatorio si hay premios pendientes al finalizar">
                            </div>
                            <div class="col-12 col-md-3">
                                <button type="submit" class="btn btn-sm btn-outline-warning w-100 fw-semibold">Actualizar estado</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Preparar Sorteo -->
                <div class="col-12 col-lg-5">
                    <div class="card border-0 shadow-sm admin-card p-4 mb-4">
                        <h5 class="fw-bold text-white mb-2"><i class="bi bi-dice-5-fill text-warning me-2"></i>Preparar sorteo</h5>
                        <p class="text-white-50 small mb-3">
                            Incluye exclusivamente participaciones elegibles (<?= count($lineasSorteo) ?> líneas listas para sorteo).
                        </p>

                        <div class="d-grid gap-2 mb-3">
                            <button type="button" class="btn btn-warning text-dark fw-bold rounded-pill"
                                    data-listado="<?= htmlspecialchars($listadoSorteoTexto, ENT_QUOTES, 'UTF-8') ?>"
                                    onclick="copiarTextoDesdeBoton(this, 'Listado de participantes copiado para AppSorteos.')">
                                <i class="bi bi-clipboard-check me-2"></i> Copiar listado de nombres (AppSorteos)
                            </button>
                            <a href="<?= esc($urlAppSorteos) ?>" target="_blank" rel="noopener noreferrer"
                               class="btn btn-outline-info fw-semibold rounded-pill">
                                <i class="bi bi-box-arrow-up-right me-2"></i> Abrir AppSorteos
                            </a>
                            <a href="<?= base_url('admin/dinamicas/imprimir/' . $dinamica['id']) ?>" target="_blank"
                               class="btn btn-outline-light fw-semibold rounded-pill">
                                <i class="bi bi-printer me-2"></i> Imprimir nombres (papelitos)
                            </a>
                            <a href="<?= base_url('admin/dinamicas/exportar/' . $dinamica['id']) ?>"
                               class="btn btn-outline-success fw-semibold rounded-pill">
                                <i class="bi bi-file-earmark-excel me-2"></i> Exportar Excel administrativo
                            </a>
                        </div>

                        <label class="form-label text-white-50 small mb-1">Vista previa de listado para sorteo:</label>
                        <textarea class="form-control form-control-sm bg-dark text-white border-secondary font-monospace" rows="6" readonly><?= esc($listadoSorteoTexto) ?></textarea>
                    </div>

                    <!-- Bitácora -->
                    <div class="card border-0 shadow-sm admin-card p-4">
                        <h6 class="fw-bold text-white mb-3"><i class="bi bi-journal-text text-info me-2"></i>Bitácora de auditoría</h6>
                        <div style="max-height: 240px; overflow-y: auto;">
                            <?php if (empty($bitacora)): ?>
                                <div class="text-white-50 small">Sin movimientos registrados.</div>
                            <?php else: ?>
                                <?php foreach ($bitacora as $b): ?>
                                    <div class="border-bottom border-secondary border-opacity-25 py-2 small">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-warning fw-semibold"><?= esc($b['accion']) ?></span>
                                            <span class="text-white-50"><?= date('d/m/Y H:i', strtotime($b['creado_en'])) ?></span>
                                        </div>
                                        <div class="text-white-50"><?= esc($b['detalle']) ?></div>
                                        <?php if (!empty($b['motivo'])): ?>
                                            <div class="text-info fst-italic">Motivo: <?= esc($b['motivo']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. PARTICIPANTES -->
        <div class="tab-pane fade <?= $tabActiva === 'participantes' ? 'show active' : '' ?>" id="pane-participantes">
            <div class="card border-0 shadow-sm admin-card p-3 mb-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
                    <form method="GET" action="<?= base_url('admin/dinamicas/detalle/' . $dinamica['id']) ?>" class="row g-2 flex-grow-1 align-items-end">
                        <input type="hidden" name="tab" value="participantes">
                        <div class="col-12 col-md-3">
                            <label class="form-label text-white-50 small mb-1">Nombre del cliente</label>
                            <input type="text" name="q" value="<?= esc($filtrosPart['q']) ?>" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Buscar...">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label text-white-50 small mb-1">Estado</label>
                            <select name="estado" class="form-select form-select-sm bg-dark text-white border-secondary" onchange="this.form.submit()">
                                <option value="" <?= $filtrosPart['estado'] === '' ? 'selected' : '' ?>>Todos</option>
                                <option value="elegible" <?= $filtrosPart['estado'] === 'elegible' ? 'selected' : '' ?>>Elegibles</option>
                                <option value="no_elegible" <?= $filtrosPart['estado'] === 'no_elegible' ? 'selected' : '' ?>>No elegibles</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label text-white-50 small mb-1">Notificación</label>
                            <select name="notificado" class="form-select form-select-sm bg-dark text-white border-secondary" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <option value="1" <?= $filtrosPart['notificado'] === '1' ? 'selected' : '' ?>>Notificados</option>
                                <option value="0" <?= $filtrosPart['notificado'] === '0' ? 'selected' : '' ?>>Pendientes</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label text-white-50 small mb-1">Origen</label>
                            <select name="origen" class="form-select form-select-sm bg-dark text-white border-secondary" onchange="this.form.submit()">
                                <option value="">Todos</option>
                                <option value="automatico" <?= $filtrosPart['origen'] === 'automatico' ? 'selected' : '' ?>>Automático</option>
                                <option value="manual" <?= $filtrosPart['origen'] === 'manual' ? 'selected' : '' ?>>Manual</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold w-100">Filtrar</button>
                            <a href="<?= base_url('admin/dinamicas/detalle/' . $dinamica['id'] . '?tab=participantes&estado=') ?>" class="btn btn-sm btn-outline-secondary" title="Mostrar todos"><i class="bi bi-x-lg"></i></a>
                        </div>
                    </form>
                    <?php if (!empty($dinamica['permitir_registro_manual'])): ?>
                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold text-nowrap"
                                data-bs-toggle="modal" data-bs-target="#modalRegistroManual">
                            <i class="bi bi-person-plus-fill me-1"></i> Registro manual
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm admin-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 admin-table">
                        <thead>
                            <tr>
                                <th>No. Control</th>
                                <th>Nombre del cliente</th>
                                <th class="text-end">Acumulado</th>
                                <th class="text-center">Compras</th>
                                <th class="text-center">Partic.</th>
                                <th>Fecha cump.</th>
                                <th>Origen</th>
                                <th>Estado</th>
                                <th>Notificado WA</th>
                                <th>Fecha notif.</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($participantes)): ?>
                                <tr>
                                    <td colspan="11" class="text-center py-4 text-white-50">
                                        No hay participantes registrados con los filtros actuales.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($participantes as $p): ?>
                                    <?php
                                    $telLimpio = preg_replace('/[^0-9]/', '', (string) ($p['telefono'] ?? ''));
                                    if (strlen($telLimpio) === 10) {
                                        $telLimpio = '52' . $telLimpio;
                                    }
                                    $waUrl = $telLimpio !== '' ? 'https://wa.me/' . $telLimpio . '?text=' . rawurlencode($p['mensaje_whatsapp']) : '';
                                    ?>
                                    <tr id="fila-participante-<?= $p['id'] ?>">
                                        <td class="font-monospace text-warning fw-bold">#<?= str_pad((string) $p['numero_control'], 3, '0', STR_PAD_LEFT) ?></td>
                                        <td>
                                            <div class="fw-bold text-white"><?= esc($p['nombre_participante']) ?></div>
                                            <?php if (!empty($p['es_ocasional'])): ?>
                                                <span class="badge bg-secondary" style="font-size:0.65rem;">Ocasional</span>
                                            <?php endif; ?>
                                            <?php if (!empty($p['motivo_estado'])): ?>
                                                <div class="small text-danger"><?= esc($p['motivo_estado']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-semibold text-white">$<?= number_format((float) $p['importe_acumulado'], 2) ?></td>
                                        <td class="text-center text-white-50"><?= (int) $p['num_compras_validas'] ?></td>
                                        <td class="text-center"><span class="badge bg-info text-dark rounded-pill"><?= (int) $p['num_participaciones'] ?></span></td>
                                        <td class="small text-white-50"><?= $p['fecha_cumplimiento'] ? date('d/m/Y', strtotime($p['fecha_cumplimiento'])) : '-' ?></td>
                                        <td>
                                            <span class="badge <?= $p['origen'] === 'manual' ? 'bg-info text-dark' : 'bg-secondary' ?>">
                                                <?= $p['origen'] === 'manual' ? 'Manual' : 'Automático' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $p['estado'] === 'elegible' ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $p['estado'] === 'elegible' ? 'Elegible' : 'No elegible' ?>
                                            </span>
                                        </td>
                                        <td class="notif-badge-cell">
                                            <?php if (!empty($p['notificado_whatsapp'])): ?>
                                                <span class="badge bg-success"><i class="bi bi-check2 me-1"></i>Sí</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small text-white-50 notif-fecha-cell">
                                            <?= !empty($p['fecha_notificacion']) ? date('d/m/Y H:i', strtotime($p['fecha_notificacion'])) : '-' ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-light" title="Ver compras"
                                                        data-nombre="<?= esc($p['nombre_participante']) ?>"
                                                        data-compras="<?= htmlspecialchars($p['detalle_compras_json'] ?? '[]', ENT_QUOTES, 'UTF-8') ?>"
                                                        onclick="verDetalleComprasParticipante(this)">
                                                    <i class="bi bi-receipt"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-info" title="Copiar mensaje WhatsApp"
                                                        data-listado="<?= htmlspecialchars($p['mensaje_whatsapp'], ENT_QUOTES, 'UTF-8') ?>"
                                                        onclick="copiarTextoDesdeBoton(this, 'Mensaje de WhatsApp copiado.')">
                                                    <i class="bi bi-clipboard"></i>
                                                </button>
                                                <?php if ($waUrl !== ''): ?>
                                                    <a href="<?= esc($waUrl) ?>" target="_blank" rel="noopener noreferrer"
                                                       class="btn btn-sm btn-outline-success" title="Abrir WhatsApp">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (empty($p['notificado_whatsapp'])): ?>
                                                    <button type="button" class="btn btn-sm btn-success" title="Marcar como notificado"
                                                            onclick="confirmarNotificacionWA(<?= (int) $dinamica['id'] ?>, <?= (int) $p['id'] ?>, this)">
                                                        <i class="bi bi-check2-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Cambiar estado"
                                                        onclick="abrirModalEstadoParticipante(<?= (int) $p['id'] ?>, '<?= esc($p['nombre_participante'], 'js') ?>', '<?= esc($p['estado']) ?>')">
                                                    <i class="bi bi-toggles"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. SEGUIMIENTO DE COMPRAS (CLIENTES PRÓXIMOS) -->
        <div class="tab-pane fade <?= $tabActiva === 'seguimiento' ? 'show active' : '' ?>" id="pane-seguimiento">
            <div class="card border-0 shadow-sm admin-card">
                <div class="card-header bg-transparent border-secondary border-opacity-25 p-3">
                    <h6 class="fw-bold text-white mb-0">
                        <i class="bi bi-graph-up-arrow text-warning me-2"></i>Clientes próximos a cumplir el requisito
                    </h6>
                    <small class="text-white-50">Estos clientes no se incluyen en el sorteo hasta alcanzar el importe mínimo requerido.</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 admin-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th class="text-end">Acumulado</th>
                                <th class="text-end">Requisito</th>
                                <th class="text-end">Faltante</th>
                                <th style="min-width: 180px;">Avance</th>
                                <th>Última compra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($proximos)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-white-50">
                                        No hay clientes con compras parciales pendientes de alcanzar el mínimo.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($proximos as $prx): ?>
                                    <tr>
                                        <td class="fw-bold text-white"><?= esc($prx['nombre']) ?></td>
                                        <td class="text-end text-info fw-semibold">$<?= number_format((float) $prx['importe_acumulado'], 2) ?></td>
                                        <td class="text-end text-white-50">$<?= number_format((float) $prx['importe_minimo'], 2) ?></td>
                                        <td class="text-end text-warning fw-bold">$<?= number_format((float) $prx['importe_faltante'], 2) ?></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1 bg-dark" style="height: 8px;">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?= (float) $prx['porcentaje_avance'] ?>%;"></div>
                                                </div>
                                                <span class="small text-white fw-semibold"><?= number_format((float) $prx['porcentaje_avance'], 0) ?>%</span>
                                            </div>
                                        </td>
                                        <td class="small text-white-50"><?= date('d/m/Y', strtotime($prx['ultima_fecha_compra'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4. PREMIOS -->
        <div class="tab-pane fade <?= $tabActiva === 'premios' ? 'show active' : '' ?>" id="pane-premios">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">Premios configurados</h5>
                <button type="button" class="btn btn-warning text-dark fw-bold rounded-pill px-3"
                        data-bs-toggle="modal" data-bs-target="#modalPremio" onclick="limpiarModalPremio(<?= count($premios) + 1 ?>)">
                    <i class="bi bi-plus-circle me-1"></i> Agregar premio
                </button>
            </div>
            <div class="card border-0 shadow-sm admin-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 admin-table">
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Nombre público</th>
                                <th>Descripción administrativa</th>
                                <th>Producto inventario</th>
                                <th class="text-end">Valor est.</th>
                                <th>Estado entrega</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($premios)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-white-50">No hay premios registrados en esta dinámica.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($premios as $pr): ?>
                                    <tr>
                                        <td class="fw-bold text-warning">#<?= (int) $pr['orden'] ?></td>
                                        <td class="fw-bold text-white"><?= esc($pr['nombre_publico']) ?></td>
                                        <td class="small text-white-50"><?= esc($pr['descripcion_admin'] ?: '-') ?></td>
                                        <td class="small text-white-50">
                                            <?php if (!empty($pr['id_producto'])): ?>
                                                <?= esc($pr['producto_nombre'] ?? ('ID ' . $pr['id_producto'])) ?>
                                                <?php if (!empty($pr['inventario_descontado'])): ?>
                                                    <span class="badge bg-success ms-1">Descontado</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end text-white"><?= $pr['valor_estimado'] !== null ? '$' . number_format((float) $pr['valor_estimado'], 2) : '-' ?></td>
                                        <td>
                                            <span class="badge <?= $badgeEntrega[$pr['estado_entrega']] ?? 'bg-secondary' ?>">
                                                <?= esc($estadosEntrega[$pr['estado_entrega']] ?? $pr['estado_entrega']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                    onclick='editarModalPremio(<?= json_encode($pr, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if (empty($pr['id_ganador'])): ?>
                                                <form action="<?= base_url('admin/dinamicas/premios/eliminar/' . $dinamica['id'] . '/' . $pr['id']) ?>" method="POST" class="d-inline"
                                                      onsubmit="return confirm('¿Eliminar este premio?')">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 5. GANADORES Y ENTREGA -->
        <div class="tab-pane fade <?= $tabActiva === 'ganadores' ? 'show active' : '' ?>" id="pane-ganadores">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="fw-bold text-dark mb-0">Ganadores y entrega de premios</h5>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-dark fw-semibold rounded-pill px-3"
                            data-listado="<?= htmlspecialchars($listadoRestantesTexto, ENT_QUOTES, 'UTF-8') ?>"
                            onclick="copiarTextoDesdeBoton(this, 'Listado de participantes restantes copiado (excluyendo ganadores).')">
                        <i class="bi bi-clipboard me-1"></i> Copiar participantes restantes (<?= count($lineasRestantes) ?>)
                    </button>
                    <button type="button" class="btn btn-warning text-dark fw-bold rounded-pill px-3"
                            data-bs-toggle="modal" data-bs-target="#modalRegistrarGanador">
                        <i class="bi bi-trophy-fill me-1"></i> Registrar ganador
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm admin-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 admin-table">
                        <thead>
                            <tr>
                                <th>Premio</th>
                                <th>Ganador</th>
                                <th>Sorteo</th>
                                <th>Estado entrega</th>
                                <th>Notif. / Entrega</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ganadores)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-white-50">Aún no se han registrado ganadores.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ganadores as $g): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-warning">#<?= (int) $g['premio_orden'] ?> · <?= esc($g['premio_nombre_publico']) ?></div>
                                            <?php if (!empty($g['premio_descripcion_admin'])): ?>
                                                <div class="small text-white-50"><?= esc($g['premio_descripcion_admin']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-white"><?= esc($g['nombre_ganador']) ?></div>
                                            <div class="small text-white-50">Control #<?= str_pad((string) $g['numero_control'], 3, '0', STR_PAD_LEFT) ?></div>
                                        </td>
                                        <td class="small text-white-50">
                                            <?= $g['fecha_hora_sorteo'] ? date('d/m/Y H:i', strtotime($g['fecha_hora_sorteo'])) : '-' ?>
                                            <div><?= esc($modalidades[$g['modalidad_sorteo']] ?? $g['modalidad_sorteo']) ?></div>
                                            <?php if (!empty($g['enlace_resultado'])): ?>
                                                <a href="<?= esc($g['enlace_resultado']) ?>" target="_blank" class="text-info">Ver resultado</a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $badgeEntrega[$g['estado_entrega']] ?? 'bg-secondary' ?>">
                                                <?= esc($estadosEntrega[$g['estado_entrega']] ?? $g['estado_entrega']) ?>
                                            </span>
                                        </td>
                                        <td class="small text-white-50">
                                            <?php if (!empty($g['fecha_notificacion'])): ?>
                                                <div>Notif: <?= date('d/m/Y H:i', strtotime($g['fecha_notificacion'])) ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($g['fecha_entrega'])): ?>
                                                <div class="text-success">Entregado: <?= date('d/m/Y H:i', strtotime($g['fecha_entrega'])) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3"
                                                    onclick='abrirModalEntrega(<?= json_encode($g, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                                <i class="bi bi-box-seam me-1"></i> Gestionar entrega
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Registro Manual de Participante -->
<div class="modal fade" id="modalRegistroManual" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white border border-secondary border-opacity-25 rounded-4">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill text-info me-2"></i>Registro manual de participante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/dinamicas/participantes/manual/' . $dinamica['id']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label small">Tipo de participante</label>
                        <select name="tipo_participante" id="selectTipoParticipanteManual" class="form-select bg-dark text-white border-secondary" onchange="toggleTipoManual()">
                            <option value="existente">Cliente existente con cuenta</option>
                            <?php if (!empty($dinamica['permitir_clientes_ocasionales'])): ?>
                                <option value="ocasional">Participante ocasional (sin cuenta corriente)</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6" id="grupoClienteExistente">
                        <label class="form-label small">Seleccionar cliente</label>
                        <select name="id_cliente" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Selecciona un cliente --</option>
                            <?php foreach ($clientes as $cli): ?>
                                <option value="<?= (int) $cli['idCliente'] ?>"><?= esc($cli['nombre']) ?> <?= !empty($cli['cel']) ? '(' . esc($cli['cel']) . ')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small">Nombre del participante</label>
                        <input type="text" name="nombre_participante" class="form-control bg-dark text-white border-secondary"
                               placeholder="Obligatorio para ocasional">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small">WhatsApp (opcional)</label>
                        <input type="text" name="telefono" class="form-control bg-dark text-white border-secondary" placeholder="10 dígitos">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small">Importe compra (opcional)</label>
                        <input type="number" step="0.01" min="0" name="importe_acumulado" value="0" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small">Fecha participación</label>
                        <input type="date" name="fecha_cumplimiento" value="<?= date('Y-m-d') ?>" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small">Número participaciones</label>
                        <input type="number" min="1" name="num_participaciones" value="1" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small">Referencia compra (opcional)</label>
                        <input type="text" name="referencia_compra" class="form-control bg-dark text-white border-secondary" placeholder="Folio o ID">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small">Motivo del registro *</label>
                        <input type="text" name="motivo_registro" required class="form-control bg-dark text-white border-secondary" placeholder="Ej. Compra en efectivo sin cuenta">
                    </div>
                    <?php if (!empty($dinamica['listado_cerrado'])): ?>
                        <div class="col-12">
                            <label class="form-label small text-warning">Motivo de corrección posterior al cierre *</label>
                            <input type="text" name="motivo_cierre" required class="form-control bg-dark text-white border-warning">
                        </div>
                    <?php endif; ?>
                    <div class="col-12">
                        <label class="form-label small">Observaciones</label>
                        <textarea name="observaciones" rows="2" class="form-control bg-dark text-white border-secondary"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-dark fw-bold rounded-pill px-4">Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Premio -->
<div class="modal fade" id="modalPremio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border border-secondary border-opacity-25 rounded-4">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold"><i class="bi bi-gift-fill text-warning me-2"></i>Premio de la dinámica</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/dinamicas/premios/guardar/' . $dinamica['id']) ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="id_premio" id="premio_id" value="">
                <div class="modal-body row g-3">
                    <div class="col-4">
                        <label class="form-label small">Orden *</label>
                        <input type="number" min="1" name="orden" id="premio_orden" value="1" required class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-8">
                        <label class="form-label small">Nombre público del premio *</label>
                        <input type="text" name="nombre_publico" id="premio_nombre_publico" required class="form-control bg-dark text-white border-secondary"
                               placeholder="Ej. Premio sorpresa o Queso de bola">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Descripción administrativa (interna)</label>
                        <textarea name="descripcion_admin" id="premio_descripcion_admin" rows="2" class="form-control bg-dark text-white border-secondary"></textarea>
                    </div>
                    <div class="col-12 col-md-7">
                        <label class="form-label small">Producto de inventario (opcional)</label>
                        <select name="id_producto" id="premio_id_producto" class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Ninguno --</option>
                            <?php foreach ($productos as $prod): ?>
                                <option value="<?= (int) $prod['id'] ?>"><?= esc($prod['descripcion']) ?> (Stock: <?= (int) $prod['stock'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label small">Valor estimado</label>
                        <input type="number" step="0.01" min="0" name="valor_estimado" id="premio_valor_estimado" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Imagen (opcional)</label>
                        <input type="file" name="imagen" accept="image/*" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Observaciones</label>
                        <input type="text" name="observaciones" id="premio_observaciones" class="form-control bg-dark text-white border-secondary">
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">Guardar premio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Registrar Ganador -->
<div class="modal fade" id="modalRegistrarGanador" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border border-secondary border-opacity-25 rounded-4">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold"><i class="bi bi-trophy-fill text-warning me-2"></i>Registrar ganador</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/dinamicas/ganadores/registrar/' . $dinamica['id']) ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label small">Premio *</label>
                        <select name="id_premio" required class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Selecciona premio disponible --</option>
                            <?php foreach ($premios as $pr): ?>
                                <?php if (empty($pr['id_ganador'])): ?>
                                    <option value="<?= (int) $pr['id'] ?>">#<?= (int) $pr['orden'] ?> · <?= esc($pr['nombre_publico']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Participante ganador *</label>
                        <select name="id_participante" required class="form-select bg-dark text-white border-secondary">
                            <option value="">-- Selecciona participante elegible --</option>
                            <?php foreach ($todosParticipantes as $tp): ?>
                                <?php if ($tp['estado'] === 'elegible'): ?>
                                    <option value="<?= (int) $tp['id'] ?>">
                                        #<?= str_pad((string) $tp['numero_control'], 3, '0', STR_PAD_LEFT) ?> · <?= esc($tp['nombre_participante']) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Fecha y hora del sorteo</label>
                        <input type="datetime-local" name="fecha_hora_sorteo" value="<?= date('Y-m-d\TH:i') ?>" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Modalidad utilizada</label>
                        <select name="modalidad_sorteo" class="form-select bg-dark text-white border-secondary">
                            <?php foreach ($modalidades as $k => $lbl): ?>
                                <option value="<?= esc($k) ?>" <?= $k === $dinamica['modalidad_sorteo'] ? 'selected' : '' ?>><?= esc($lbl) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Enlace al resultado (opcional)</label>
                        <input type="url" name="enlace_resultado" class="form-control bg-dark text-white border-secondary" placeholder="https://app-sorteos.com/...">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Evidencia del sorteo (opcional)</label>
                        <input type="file" name="evidencia_sorteo" accept="image/*,.pdf" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Observaciones</label>
                        <textarea name="observaciones" rows="2" class="form-control bg-dark text-white border-secondary"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">Guardar ganador</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Gestión de Entrega de Premio -->
<div class="modal fade" id="modalEntregaPremio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border border-secondary border-opacity-25 rounded-4">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold"><i class="bi bi-box-seam text-warning me-2"></i>Gestionar entrega de premio</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEntregaPremio" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <div class="small text-white-50">Ganador: <strong class="text-white" id="entrega_ganador_nombre"></strong></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Estado de entrega</label>
                        <select name="estado_entrega" id="entrega_estado" class="form-select bg-dark text-white border-secondary">
                            <?php foreach ($estadosEntrega as $k => $lbl): ?>
                                <?php if ($k !== 'pendiente_asignar'): ?>
                                    <option value="<?= esc($k) ?>"><?= esc($lbl) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Evidencia de entrega (opcional)</label>
                        <input type="file" name="evidencia_entrega" accept="image/*,.pdf" class="form-control form-control-sm bg-dark text-white border-secondary">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Observaciones de entrega</label>
                        <textarea name="observaciones_entrega" id="entrega_observaciones" rows="2" class="form-control bg-dark text-white border-secondary"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold rounded-pill px-4">Actualizar entrega</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Detalle de Compras del Participante -->
<div class="modal fade" id="modalDetalleComprasPart" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-white border border-secondary border-opacity-25 rounded-4">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold">Compras válidas: <span id="detalleComprasNombre" class="text-warning"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalleComprasContenido"></div>
        </div>
    </div>
</div>

<!-- Modal: Cerrar Participaciones -->
<div class="modal fade" id="modalCerrarParticipaciones" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border border-warning border-opacity-50 rounded-4">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold text-warning"><i class="bi bi-lock-fill me-2"></i>Cerrar participaciones</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/dinamicas/cerrar/' . $dinamica['id']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <p class="small text-white-50">
                        Al cerrar oficialmente el listado se ejecutará una última validación de compras, se conservará la versión definitiva de participantes y se impedirán modificaciones automáticas posteriores.
                    </p>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">Confirmar cierre oficial</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Sincronización Forzada (post-cierre) -->
<div class="modal fade" id="modalSincronizarForzado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border border-warning border-opacity-50 rounded-4">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold text-warning">Corrección posterior al cierre</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/dinamicas/sincronizar/' . $dinamica['id']) ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <label class="form-label small">Motivo obligatorio (quedará registrado en bitácora) *</label>
                    <input type="text" name="motivo" required class="form-control bg-dark text-white border-warning">
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">Ejecutar corrección</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Cambiar Estado de Participante -->
<div class="modal fade" id="modalEstadoParticipante" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border border-secondary border-opacity-25 rounded-4">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold">Cambiar estado: <span id="estadoPartNombre" class="text-warning"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEstadoParticipante" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body row g-3">
                    <div class="col-12">
                        <label class="form-label small">Estado</label>
                        <select name="estado" id="estadoPartSelect" class="form-select bg-dark text-white border-secondary">
                            <option value="elegible">Elegible</option>
                            <option value="no_elegible">No elegible</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Motivo <?= !empty($dinamica['listado_cerrado']) ? '(obligatorio)' : '(opcional)' ?></label>
                        <input type="text" name="motivo" <?= !empty($dinamica['listado_cerrado']) ? 'required' : '' ?> class="form-control bg-dark text-white border-secondary">
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Dinámica -->
<div class="modal fade" id="modalEditarDinamica" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form action="<?= base_url('admin/dinamicas/actualizar/' . $dinamica['id']) ?>" method="POST" enctype="multipart/form-data"
              class="modal-content bg-dark text-white border border-secondary border-opacity-25 rounded-4">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold">Editar dinámica</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label small">Nombre *</label>
                        <input type="text" name="nombre" value="<?= esc($dinamica['nombre']) ?>" required class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Descripción</label>
                        <textarea name="descripcion" rows="2" class="form-control bg-dark text-white border-secondary"><?= esc($dinamica['descripcion']) ?></textarea>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small">Inicio participación</label>
                        <input type="date" name="fecha_inicio" value="<?= esc($dinamica['fecha_inicio']) ?>" <?= !empty($dinamica['listado_cerrado']) ? 'readonly' : '' ?> class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small">Fin participación</label>
                        <input type="date" name="fecha_fin" value="<?= esc($dinamica['fecha_fin']) ?>" <?= !empty($dinamica['listado_cerrado']) ? 'readonly' : '' ?> class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small">Fecha sorteo</label>
                        <input type="date" name="fecha_sorteo" value="<?= esc($dinamica['fecha_sorteo']) ?>" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small">Hora sorteo</label>
                        <input type="time" name="hora_sorteo" value="<?= esc($dinamica['hora_sorteo']) ?>" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-4">
                        <label class="form-label small">Modalidad</label>
                        <select name="modalidad_sorteo" class="form-select bg-dark text-white border-secondary">
                            <?php foreach ($modalidades as $k => $lbl): ?>
                                <option value="<?= esc($k) ?>" <?= $k === $dinamica['modalidad_sorteo'] ? 'selected' : '' ?>><?= esc($lbl) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-4">
                        <label class="form-label small">Plataforma</label>
                        <input type="text" name="plataforma_digital" value="<?= esc($dinamica['plataforma_digital']) ?>" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-4">
                        <label class="form-label small">URL plataforma</label>
                        <input type="url" name="url_plataforma" value="<?= esc($dinamica['url_plataforma']) ?>" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-4">
                        <label class="form-label small">Importe mínimo</label>
                        <input type="number" step="0.01" name="importe_minimo" value="<?= esc($dinamica['importe_minimo']) ?>" <?= !empty($dinamica['listado_cerrado']) ? 'readonly' : '' ?> class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-4">
                        <label class="form-label small">Máx. part/cliente</label>
                        <input type="number" min="1" name="max_participaciones_cliente" value="<?= esc($dinamica['max_participaciones_cliente']) ?>" <?= !empty($dinamica['listado_cerrado']) ? 'readonly' : '' ?> class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-4">
                        <label class="form-label small">Máx. premios/cliente</label>
                        <input type="number" min="1" name="max_premios_cliente" value="<?= esc($dinamica['max_premios_cliente'] ?: 1) ?>" class="form-control bg-dark text-white border-secondary">
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permitir_clientes_ocasionales" value="1" id="edOcasionales" <?= !empty($dinamica['permitir_clientes_ocasionales']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="edOcasionales">Permitir clientes ocasionales</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permitir_registro_manual" value="1" id="edManual" <?= !empty($dinamica['permitir_registro_manual']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="edManual">Permitir registro manual</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permitir_multiples_premios" value="1" id="edMultiPremio" <?= empty($dinamica['max_premios_cliente']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="edMultiPremio">Permitir ganar más de un premio</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Plantilla WhatsApp</label>
                        <textarea name="plantilla_whatsapp" rows="4" class="form-control form-control-sm bg-dark text-white border-secondary"><?= esc($dinamica['plantilla_whatsapp']) ?></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-secondary border-opacity-25">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<script>
function copiarTextoDesdeBoton(btn, mensajeExito) {
    const texto = btn.getAttribute('data-listado') || '';
    if (!texto) {
        alert('No hay contenido para copiar.');
        return;
    }
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(texto).then(() => mostrarToastDinamica(mensajeExito));
    } else {
        const ta = document.createElement('textarea');
        ta.value = texto;
        ta.style.position = 'fixed';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        mostrarToastDinamica(mensajeExito);
    }
}

function mostrarToastDinamica(msg) {
    const container = document.getElementById('dinamica-toast-container');
    if (!container) return;
    const el = document.createElement('div');
    el.className = 'toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3';
    el.innerHTML = `<div class="d-flex"><div class="toast-body d-flex align-items-center gap-2"><i class="fas fa-check-circle text-success fs-5"></i><div>${msg}</div></div><button type="button" class="btn-close btn-close-white m-auto me-2" data-bs-dismiss="toast"></button></div>`;
    container.appendChild(el);
    const toast = new bootstrap.Toast(el, { delay: 3500 });
    toast.show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
}

function confirmarNotificacionWA(idDinamica, idParticipante, btn) {
    btn.disabled = true;
    fetch(`<?= base_url('admin/dinamicas/participantes/notificar/') ?>${idDinamica}/${idParticipante}`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const fila = document.getElementById('fila-participante-' + idParticipante);
            if (fila) {
                fila.querySelector('.notif-badge-cell').innerHTML = '<span class="badge bg-success"><i class="bi bi-check2 me-1"></i>Sí</span>';
                fila.querySelector('.notif-fecha-cell').textContent = data.fecha_notificacion;
            }
            btn.remove();
            mostrarToastDinamica(data.message);
        } else {
            btn.disabled = false;
            alert(data.message || 'Error al confirmar notificación.');
        }
    })
    .catch(() => { btn.disabled = false; alert('Error de conexión.'); });
}

function verDetalleComprasParticipante(btn) {
    const nombre = btn.getAttribute('data-nombre') || '';
    const compras = JSON.parse(btn.getAttribute('data-compras') || '[]');
    document.getElementById('detalleComprasNombre').textContent = nombre;
    const cont = document.getElementById('detalleComprasContenido');
    if (!compras.length) {
        cont.innerHTML = '<div class="text-white-50 text-center py-3">Sin compras automáticas registradas (registro manual o sin detalle).</div>';
    } else {
        let html = '<div class="table-responsive"><table class="table table-sm table-dark table-hover mb-0"><thead><tr><th>Fecha</th><th>Descripción</th><th>Estatus</th><th class="text-end">Monto neto</th></tr></thead><tbody>';
        compras.forEach(c => {
            html += `<tr><td>${c.fecha}</td><td>${c.descripcion}</td><td>${c.estatus === '1' || c.estatus === 'Pagado' ? 'Pagado' : 'Cuenta'}</td><td class="text-end">$${Number(c.monto).toFixed(2)}</td></tr>`;
        });
        html += '</tbody></table></div>';
        cont.innerHTML = html;
    }
    new bootstrap.Modal(document.getElementById('modalDetalleComprasPart')).show();
}

function toggleTipoManual() {
    const val = document.getElementById('selectTipoParticipanteManual').value;
    document.getElementById('grupoClienteExistente').style.display = val === 'existente' ? '' : 'none';
}

function limpiarModalPremio(ordenSugerido) {
    document.getElementById('premio_id').value = '';
    document.getElementById('premio_orden').value = ordenSugerido;
    document.getElementById('premio_nombre_publico').value = '';
    document.getElementById('premio_descripcion_admin').value = '';
    document.getElementById('premio_id_producto').value = '';
    document.getElementById('premio_valor_estimado').value = '';
    document.getElementById('premio_observaciones').value = '';
}

function editarModalPremio(pr) {
    document.getElementById('premio_id').value = pr.id;
    document.getElementById('premio_orden').value = pr.orden;
    document.getElementById('premio_nombre_publico').value = pr.nombre_publico || '';
    document.getElementById('premio_descripcion_admin').value = pr.descripcion_admin || '';
    document.getElementById('premio_id_producto').value = pr.id_producto || '';
    document.getElementById('premio_valor_estimado').value = pr.valor_estimado || '';
    document.getElementById('premio_observaciones').value = pr.observaciones || '';
    new bootstrap.Modal(document.getElementById('modalPremio')).show();
}

function abrirModalEntrega(g) {
    document.getElementById('formEntregaPremio').action = `<?= base_url('admin/dinamicas/ganadores/entrega/' . $dinamica['id'] . '/') ?>${g.id}`;
    document.getElementById('entrega_ganador_nombre').textContent = g.nombre_ganador;
    document.getElementById('entrega_estado').value = g.estado_entrega || 'ganador_registrado';
    document.getElementById('entrega_observaciones').value = g.observaciones_entrega || '';
    new bootstrap.Modal(document.getElementById('modalEntregaPremio')).show();
}

function abrirModalEstadoParticipante(idPart, nombre, estadoActual) {
    document.getElementById('formEstadoParticipante').action = `<?= base_url('admin/dinamicas/participantes/estado/' . $dinamica['id'] . '/') ?>${idPart}`;
    document.getElementById('estadoPartNombre').textContent = nombre;
    document.getElementById('estadoPartSelect').value = estadoActual;
    new bootstrap.Modal(document.getElementById('modalEstadoParticipante')).show();
}
</script>
<?= $this->endSection() ?>
