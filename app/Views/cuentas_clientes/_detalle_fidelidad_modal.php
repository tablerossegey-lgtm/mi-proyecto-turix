<?php
$mesesNombres = [
    '01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr',
    '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
    '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic',
];
?>
<div class="modal-header border-bottom border-secondary border-opacity-25">
    <div>
        <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark rounded-pill">#<?= (int) $cliente['posicion'] ?></span>
            <?= esc($cliente['nombre']) ?>
        </h5>
        <small class="text-white-50">
            Periodo: <?= esc($periodo['etiqueta']) ?>
            <?php if (!empty($cliente['cel'])): ?>
                · <i class="fab fa-whatsapp text-success ms-1"></i> <?= esc($cliente['cel']) ?>
            <?php endif; ?>
            · Tipo: <?= esc($cliente['tipoCliente'] ?: 'General') ?>
        </small>
    </div>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
</div>

<div class="modal-body p-3 p-md-4">
    <!-- Puntuación Final y Desglose -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="p-3 rounded-3 bg-dark border border-warning border-opacity-25 text-center h-100 d-flex flex-column justify-content-center">
                <span class="text-white-50 small text-uppercase fw-semibold">Puntuación Final</span>
                <div class="display-5 fw-bold text-warning my-1"><?= number_format((float) $cliente['puntaje_total'], 2) ?></div>
                <span class="text-white-50 small">de 100 puntos posibles</span>
                <div class="mt-2 pt-2 border-top border-secondary border-opacity-25 d-flex justify-content-around small">
                    <div>
                        <div class="text-white-50">Compras</div>
                        <div class="fw-bold text-white"><?= (int) $cliente['numero_compras'] ?></div>
                    </div>
                    <div>
                        <div class="text-white-50">Meses</div>
                        <div class="fw-bold text-white"><?= (int) $cliente['meses_activos'] ?>/<?= (int) $periodo['total_meses'] ?></div>
                    </div>
                    <div>
                        <div class="text-white-50">Acumulado</div>
                        <div class="fw-bold text-success">$<?= number_format((float) $detalle['importe_acumulado'], 2) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-8">
            <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-25 h-100">
                <h6 class="fw-bold text-white mb-3"><i class="fas fa-sliders-h text-warning me-2"></i>Desglose por Criterio</h6>

                <?php
                $criterios = [
                    ['label' => 'Frecuencia de compras (40%)', 'val' => (float) $cliente['puntaje_frecuencia'], 'max' => 40, 'color' => 'bg-primary'],
                    ['label' => 'Constancia mensual (30%)', 'val' => (float) $cliente['puntaje_constancia'], 'max' => 30, 'color' => 'bg-success'],
                    ['label' => 'Importe total comprado (20%)', 'val' => (float) $cliente['puntaje_importe'], 'max' => 20, 'color' => 'bg-warning'],
                    ['label' => 'Recencia de última compra (10%)', 'val' => (float) $cliente['puntaje_recencia'], 'max' => 10, 'color' => 'bg-info'],
                ];
                ?>
                <?php foreach ($criterios as $crit): ?>
                    <?php $pct = $crit['max'] > 0 ? ($crit['val'] / $crit['max']) * 100 : 0; ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-white-50"><?= esc($crit['label']) ?></span>
                            <span class="fw-bold text-white"><?= number_format($crit['val'], 2) ?> / <?= $crit['max'] ?></span>
                        </div>
                        <div class="progress bg-secondary bg-opacity-25" style="height: 7px;">
                            <div class="progress-bar <?= $crit['color'] ?>" style="width: <?= min(100, max(0, $pct)) ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Distribución Mensual -->
    <div class="mb-4">
        <h6 class="fw-bold text-white mb-2"><i class="fas fa-calendar-alt text-warning me-2"></i>Distribución Mensual de Compras</h6>
        <?php if (empty($detalle['distribucion_mensual'])): ?>
            <p class="text-white-50 small">Sin registros mensuales.</p>
        <?php else: ?>
            <div class="row g-2">
                <?php foreach ($detalle['distribucion_mensual'] as $m): ?>
                    <?php
                    [$y, $mo] = explode('-', $m['mes']);
                    $labelMes = ($mesesNombres[$mo] ?? $mo) . ' ' . $y;
                    ?>
                    <div class="col-6 col-sm-4 col-md-3">
                        <div class="p-2 rounded-3 bg-dark border border-secondary border-opacity-25 text-center">
                            <div class="small fw-bold text-warning"><?= esc($labelMes) ?></div>
                            <div class="text-white fw-semibold small"><?= (int) $m['compras'] ?> <?= (int) $m['compras'] === 1 ? 'compra' : 'compras' ?></div>
                            <div class="text-success small fw-bold">$<?= number_format((float) $m['importe'], 2) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Historial de Compras -->
    <div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="fw-bold text-white mb-0"><i class="fas fa-receipt text-warning me-2"></i>Historial de Compras en el Periodo</h6>
            <span class="badge bg-secondary bg-opacity-50 text-white"><?= count($detalle['historial']) ?> movimientos</span>
        </div>
        <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
            <table class="table table-dark table-sm table-hover align-middle mb-0">
                <thead class="table-active small text-white-50 sticky-top">
                    <tr>
                        <th>Fecha</th>
                        <th>Producto / Concepto</th>
                        <th>Origen</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-end">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalle['historial'] as $h): ?>
                        <tr>
                            <td class="small text-white-50 text-nowrap"><?= date('d/m/Y', strtotime($h['fecha'])) ?></td>
                            <td class="small text-white">
                                <?= esc($h['descripcion']) ?>
                                <?php if (!empty($h['codigo_sku'])): ?>
                                    <span class="badge bg-secondary bg-opacity-25 text-white-50 ms-1"><?= esc($h['codigo_sku']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-dark border border-secondary text-white-50" style="font-size: 0.7rem;">
                                    <?= esc($h['origen']) ?>
                                </span>
                            </td>
                            <td class="text-center small text-white"><?= (int) $h['cantidad'] ?></td>
                            <td class="text-end small fw-bold <?= $h['es_ajuste'] ? 'text-danger' : 'text-warning' ?>">
                                $<?= number_format((float) $h['monto'], 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-footer border-top border-secondary border-opacity-25">
    <a href="<?= base_url('admin/cuentas?q=' . urlencode($cliente['nombre'])) ?>" class="btn btn-outline-light btn-sm rounded-pill px-3">
        <i class="fas fa-wallet me-1"></i> Ir a Cuenta del Cliente
    </a>
    <button type="button" class="btn btn-warning btn-sm rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cerrar</button>
</div>

