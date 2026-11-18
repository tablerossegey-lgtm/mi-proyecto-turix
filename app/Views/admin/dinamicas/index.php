<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link class="styles-admin-theme" rel="stylesheet" href="<?= base_url('css/admin.css?v=1.4') ?>">

<div class="container py-4">
    <!-- Toast Notifications -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
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
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 admin-title"><i class="bi bi-gift-fill text-warning me-2"></i>Rifas y dinámicas</h2>
            <p class="text-muted mb-0">Administra rifas, sorteos, participantes, premios, notificaciones y entregas de TurixShop.</p>
            <div class="admin-subtitle-line" style="background-color: #ffc107;"></div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-warning text-dark rounded-pill px-4 fw-bold d-inline-flex align-items-center gap-2 shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#modalNuevaDinamica">
                <i class="bi bi-plus-circle-fill"></i> Nueva dinámica
            </button>
            <a href="<?= base_url('admin/cuentas') ?>" class="btn btn-outline-dark rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2">
                <i class="bi bi-wallet2"></i> Cuentas Clientes
            </a>
        </div>
    </div>

    <!-- Indicadores principales (5 tarjetas) -->
    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
        <div class="col">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #22c55e !important; background: #1f222b !important;">
                <div class="admin-stat-label"><i class="bi bi-broadcast text-success me-1"></i> Dinámicas activas</div>
                <div class="admin-stat-value text-success"><?= (int) $indicadores['activas'] ?></div>
            </div>
        </div>
        <div class="col">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #ffc107 !important; background: #1f222b !important;">
                <div class="admin-stat-label"><i class="bi bi-calendar-event text-warning me-1"></i> Próximos sorteos</div>
                <div class="admin-stat-value text-warning"><?= (int) $indicadores['proximos_sorteos'] ?></div>
            </div>
        </div>
        <div class="col">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #64748b !important; background: #1f222b !important;">
                <div class="admin-stat-label"><i class="bi bi-flag-fill text-secondary me-1"></i> Finalizadas</div>
                <div class="admin-stat-value text-white"><?= (int) $indicadores['finalizadas'] ?></div>
            </div>
        </div>
        <div class="col">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #38bdf8 !important; background: #1f222b !important;">
                <div class="admin-stat-label"><i class="bi bi-people-fill text-info me-1"></i> Participantes activos</div>
                <div class="admin-stat-value text-info"><?= (int) $indicadores['total_participantes_activas'] ?></div>
            </div>
        </div>
        <div class="col">
            <div class="admin-stat-card h-100" style="border-left: 3px solid #f97316 !important; background: #1f222b !important;">
                <div class="admin-stat-label"><i class="bi bi-box-seam text-warning me-1"></i> Premios por entregar</div>
                <div class="admin-stat-value" style="color: #fb923c;"><?= (int) $indicadores['premios_pendientes'] ?></div>
            </div>
        </div>
    </div>

    <!-- Filtros de búsqueda -->
    <div class="card border-0 shadow-sm admin-card p-3 mb-4">
        <form method="GET" action="<?= base_url('admin/dinamicas') ?>" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label text-white-50 small mb-1">Buscar por nombre</label>
                <input type="text" name="q" value="<?= esc($filtros['q']) ?>"
                       class="form-control form-control-sm bg-dark text-white border-secondary"
                       placeholder="Ej. Rifa navideña 2026...">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label text-white-50 small mb-1">Año</label>
                <select name="anio" class="form-select form-select-sm bg-dark text-white border-secondary">
                    <option value="">Todos</option>
                    <?php foreach ($anios as $a): ?>
                        <option value="<?= $a ?>" <?= ((string) $a === $filtros['anio']) ? 'selected' : '' ?>><?= $a ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label text-white-50 small mb-1">Estado</label>
                <select name="estado" class="form-select form-select-sm bg-dark text-white border-secondary">
                    <option value="">Todos</option>
                    <?php foreach ($estados as $k => $lbl): ?>
                        <option value="<?= esc($k) ?>" <?= ($k === $filtros['estado']) ? 'selected' : '' ?>><?= esc($lbl) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label text-white-50 small mb-1">Modalidad</label>
                <select name="modalidad" class="form-select form-select-sm bg-dark text-white border-secondary">
                    <option value="">Todas</option>
                    <?php foreach ($modalidades as $k => $lbl): ?>
                        <option value="<?= esc($k) ?>" <?= ($k === $filtros['modalidad']) ? 'selected' : '' ?>><?= esc($lbl) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-warning text-dark fw-bold w-100">
                    <i class="bi bi-search me-1"></i> Filtrar
                </button>
                <a href="<?= base_url('admin/dinamicas') ?>" class="btn btn-sm btn-outline-secondary" title="Limpiar">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Listado de dinámicas -->
    <div class="card border-0 shadow-sm admin-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead>
                    <tr>
                        <th>Nombre de la dinámica</th>
                        <th>Periodo de participación</th>
                        <th>Fecha y hora del sorteo</th>
                        <th class="text-center">Participantes</th>
                        <th class="text-center">Premios</th>
                        <th>Modalidad</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dinamicas)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-white-50">
                                <i class="bi bi-gift fs-1 d-block mb-2 text-warning opacity-50"></i>
                                No hay dinámicas registradas con los filtros seleccionados.
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3"
                                            data-bs-toggle="modal" data-bs-target="#modalNuevaDinamica">
                                        <i class="bi bi-plus-circle me-1"></i> Crear primera dinámica
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $badgeEstado = [
                            'borrador'                 => 'bg-secondary',
                            'programada'               => 'bg-info text-dark',
                            'activa'                   => 'bg-success',
                            'participaciones_cerradas' => 'bg-warning text-dark',
                            'sorteo_realizado'         => 'bg-primary',
                            'finalizada'               => 'bg-dark border border-secondary',
                            'cancelada'                => 'bg-danger',
                        ];
                        ?>
                        <?php foreach ($dinamicas as $d): ?>
                            <tr>
                                <td>
                                    <a href="<?= base_url('admin/dinamicas/detalle/' . $d['id']) ?>" class="fw-bold text-white text-decoration-none">
                                        <?= esc($d['nombre']) ?>
                                    </a>
                                    <?php if ((float) $d['importe_minimo'] > 0): ?>
                                        <div class="small text-white-50">Compra mín: $<?= number_format((float) $d['importe_minimo'], 2) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-white-50">
                                    <?= date('d/m/Y', strtotime($d['fecha_inicio'])) ?> — <?= date('d/m/Y', strtotime($d['fecha_fin'])) ?>
                                </td>
                                <td class="small text-white">
                                    <?php if (!empty($d['fecha_sorteo'])): ?>
                                        <?= date('d/m/Y', strtotime($d['fecha_sorteo'])) ?>
                                        <?php if (!empty($d['hora_sorteo'])): ?>
                                            <span class="text-white-50">· <?= date('g:i a', strtotime($d['hora_sorteo'])) ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-white-50">Por definir</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark rounded-pill px-2"><?= (int) $d['total_participantes'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning text-dark rounded-pill px-2"><?= (int) $d['total_premios'] ?></span>
                                </td>
                                <td class="small text-white-50">
                                    <?= esc($modalidades[$d['modalidad_sorteo']] ?? $d['modalidad_sorteo']) ?>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeEstado[$d['estado']] ?? 'bg-secondary' ?> rounded-pill">
                                        <?= esc($estados[$d['estado']] ?? $d['estado']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?= base_url('admin/dinamicas/detalle/' . $d['id']) ?>"
                                           class="btn btn-sm btn-outline-warning rounded-pill px-3" title="Ver detalle">
                                            <i class="bi bi-eye me-1"></i> Gestionar
                                        </a>
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

<!-- Modal: Nueva Dinámica -->
<div class="modal fade" id="modalNuevaDinamica" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form action="<?= base_url('admin/dinamicas/guardar') ?>" method="POST" enctype="multipart/form-data"
              class="modal-content bg-dark text-white border border-secondary border-opacity-25 rounded-4">
            <div class="modal-header border-secondary border-opacity-25">
                <h5 class="modal-title fw-bold"><i class="bi bi-gift-fill text-warning me-2"></i>Nueva dinámica / rifa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <?= csrf_field() ?>
            <div class="modal-body">
                    <h6 class="text-warning fw-bold text-uppercase small mb-3">1. Información general</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-8">
                            <label class="form-label small">Nombre de la dinámica *</label>
                            <input type="text" name="nombre" class="form-control bg-dark text-white border-secondary" required
                                   placeholder="Ej. Gran rifa navideña Turixshop 2026">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small">Estado inicial</label>
                            <select name="estado" class="form-select bg-dark text-white border-secondary">
                                <option value="activa" selected>Activa</option>
                                <option value="programada">Programada</option>
                                <option value="borrador">Borrador</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Descripción</label>
                            <textarea name="descripcion" rows="2" class="form-control bg-dark text-white border-secondary"></textarea>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small">Inicio participación *</label>
                            <input type="date" name="fecha_inicio" class="form-control bg-dark text-white border-secondary" required
                                   value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small">Fin participación *</label>
                            <input type="date" name="fecha_fin" class="form-control bg-dark text-white border-secondary" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small">Fecha del sorteo</label>
                            <input type="date" name="fecha_sorteo" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small">Hora del sorteo</label>
                            <input type="time" name="hora_sorteo" class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small">Imagen promocional (opcional)</label>
                            <input type="file" name="imagen_promo" accept="image/*" class="form-control form-control-sm bg-dark text-white border-secondary">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small">Observaciones</label>
                            <input type="text" name="observaciones" class="form-control form-control-sm bg-dark text-white border-secondary">
                        </div>
                    </div>

                    <h6 class="text-warning fw-bold text-uppercase small mb-3">2. Modalidad del sorteo</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <label class="form-label small">Modalidad</label>
                            <select name="modalidad_sorteo" class="form-select bg-dark text-white border-secondary">
                                <?php foreach ($modalidades as $k => $lbl): ?>
                                    <option value="<?= esc($k) ?>" <?= $k === 'por_definir' ? 'selected' : '' ?>><?= esc($lbl) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small">Plataforma digital (opcional)</label>
                            <input type="text" name="plataforma_digital" class="form-control bg-dark text-white border-secondary"
                                   placeholder="Ej. AppSorteos" value="AppSorteos">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small">URL de plataforma (opcional)</label>
                            <input type="url" name="url_plataforma" class="form-control bg-dark text-white border-secondary"
                                   placeholder="https://app-sorteos.com/es" value="https://app-sorteos.com/es">
                        </div>
                    </div>

                    <h6 class="text-warning fw-bold text-uppercase small mb-3">3. Requisitos de participación</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <label class="form-label small">Modalidad de participación</label>
                            <select name="tipo_requisito" class="form-select bg-dark text-white border-secondary">
                                <?php foreach ($tiposRequisito as $k => $lbl): ?>
                                    <option value="<?= esc($k) ?>" <?= $k === 'acumulado_unico' ? 'selected' : '' ?>><?= esc($lbl) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label small">Importe mínimo (MXN)</label>
                            <input type="number" step="0.01" min="0" name="importe_minimo" value="100.00"
                                   class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label small">Máx. participaciones/cliente</label>
                            <input type="number" min="1" name="max_participaciones_cliente" value="1"
                                   class="form-control bg-dark text-white border-secondary" placeholder="Vacío = sin límite">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small">Máx. premios por cliente</label>
                            <input type="number" min="1" name="max_premios_cliente" value="1"
                                   class="form-control bg-dark text-white border-secondary">
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-3 pt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permitir_clientes_ocasionales" value="1" id="chkOcasionales" checked>
                                <label class="form-check-label small" for="chkOcasionales">Permitir clientes ocasionales</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permitir_registro_manual" value="1" id="chkManual" checked>
                                <label class="form-check-label small" for="chkManual">Permitir registro manual</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permitir_multiples_premios" value="1" id="chkMultiPremio">
                                <label class="form-check-label small" for="chkMultiPremio">Permitir ganar más de un premio</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="requiere_compra_liquidada" value="1" id="chkLiquidada">
                                <label class="form-check-label small" for="chkLiquidada">Exigir compra liquidada</label>
                            </div>
                        </div>
                    </div>

                    <h6 class="text-warning fw-bold text-uppercase small mb-2">4. Plantilla de notificación WhatsApp</h6>
                    <div class="mb-2">
                        <textarea name="plantilla_whatsapp" rows="4" class="form-control form-control-sm bg-dark text-white border-secondary"><?= esc($plantillaDefecto) ?></textarea>
                        <div class="form-text text-white-50">Variables: <code>{cliente}</code>, <code>{dinamica}</code>, <code>{fecha_sorteo}</code>, <code>{hora_sorteo}</code>, <code>{participaciones}</code></div>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-25">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4">
                        <i class="bi bi-save me-1"></i> Guardar dinámica
                    </button>
                </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
