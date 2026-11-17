<?php
$totalClientes = (int) ($resumen['total_clientes_activos'] ?? 0);
$totalCompras = (int) ($resumen['total_compras'] ?? 0);
$importeTotal = (float) ($resumen['importe_total'] ?? 0);
$promedioCompras = (float) ($resumen['promedio_compras'] ?? 0);
$mejorCliente = $resumen['mejor_cliente'] ?? null;

$queryDetalle = http_build_query([
    'modo' => $periodo['modo'],
    'anio' => $periodo['anio'],
    'fecha_inicio' => $periodo['fecha_inicio'],
    'fecha_fin' => $periodo['fecha_fin'],
]);
?>

<!-- Tarjetas Resumen -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl">
        <div class="card border-0 shadow-sm admin-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-white-50 small fw-semibold text-uppercase">Clientes Activos</span>
                <span class="badge bg-primary bg-opacity-25 text-info rounded-circle p-2"><i class="fas fa-users"></i></span>
            </div>
            <div class="fs-3 fw-bold text-white"><?= number_format($totalClientes) ?></div>
            <div class="small text-white-50 mt-1">Con compras válidas</div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl">
        <div class="card border-0 shadow-sm admin-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-white-50 small fw-semibold text-uppercase">Total Compras</span>
                <span class="badge bg-success bg-opacity-25 text-success rounded-circle p-2"><i class="fas fa-shopping-bag"></i></span>
            </div>
            <div class="fs-3 fw-bold text-white"><?= number_format($totalCompras) ?></div>
            <div class="small text-white-50 mt-1">Operaciones válidas</div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl">
        <div class="card border-0 shadow-sm admin-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-white-50 small fw-semibold text-uppercase">Importe Total</span>
                <span class="badge bg-warning bg-opacity-25 text-warning rounded-circle p-2"><i class="fas fa-dollar-sign"></i></span>
            </div>
            <div class="fs-3 fw-bold text-warning">$<?= number_format($importeTotal, 2) ?></div>
            <div class="small text-white-50 mt-1">Neto en el periodo</div>
        </div>
    </div>

    <div class="col-6 col-md-6 col-xl">
        <div class="card border-0 shadow-sm admin-card h-100 p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-white-50 small fw-semibold text-uppercase">Promedio / Cliente</span>
                <span class="badge bg-info bg-opacity-25 text-info rounded-circle p-2"><i class="fas fa-chart-line"></i></span>
            </div>
            <div class="fs-3 fw-bold text-white"><?= number_format($promedioCompras, 2) ?></div>
            <div class="small text-white-50 mt-1">Compras por cliente</div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl">
        <div class="card border-0 shadow-sm admin-card h-100 p-3 border-warning border-opacity-25">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-warning small fw-semibold text-uppercase">Líder de Fidelidad</span>
                <span class="badge bg-warning text-dark rounded-circle p-2"><i class="fas fa-crown"></i></span>
            </div>
            <?php if ($mejorCliente): ?>
                <div class="fs-5 fw-bold text-white text-truncate" title="<?= esc($mejorCliente['nombre']) ?>">
                    <?= esc($mejorCliente['nombre']) ?>
                </div>
                <div class="small text-warning fw-semibold mt-1">
                    <?= number_format((float) $mejorCliente['puntaje_total'], 2) ?> pts · <?= (int) $mejorCliente['numero_compras'] ?> compras
                </div>
            <?php else: ?>
                <div class="fs-5 fw-bold text-white-50">Sin datos</div>
                <div class="small text-white-50 mt-1">Sin compras en periodo</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (empty($ranking_visible)): ?>
    <div class="card border-0 shadow-sm admin-card p-5 text-center">
        <div class="mb-3">
            <i class="fas fa-trophy fa-3x text-warning opacity-50"></i>
        </div>
        <h5 class="text-white fw-bold">No se encontraron compras válidas en este periodo</h5>
        <p class="text-white-50 mb-0">Intenta seleccionar otro rango de fechas o año para visualizar el ranking de fidelidad.</p>
    </div>
<?php else: ?>
    <!-- Gráfica Top Clientes Horizontal -->
    <?php
    $listaGrafica = $grafica_clientes ?? $ranking_visible;
    $tituloGrafica = ($top === 'todos')
        ? 'Todos los Clientes con Mayor Fidelidad (' . count($listaGrafica) . ')'
        : 'Top ' . esc($top) . ' Clientes con Mayor Fidelidad';
    ?>
    <div class="card border-0 shadow-sm admin-card p-3 p-md-4 mb-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-3 gap-2">
            <div>
                <h5 class="fw-bold text-white mb-0"><i class="fas fa-chart-bar text-warning me-2"></i><?= $tituloGrafica ?></h5>
                <small class="text-white-50">Evaluación integral: Frecuencia (40%), Constancia (30%), Importe (20%) y Recencia (10%)</small>
            </div>
            <span class="badge bg-dark border border-secondary text-white-50 px-3 py-2 rounded-pill">
                <?= esc($periodo['etiqueta']) ?>
            </span>
        </div>
        <div style="position: relative; height: <?= max(200, count($listaGrafica) * 34) ?>px; width: 100%;">
            <canvas id="chartTop10Fidelidad"></canvas>
        </div>
    </div>

    <!-- Tabla de Ranking -->
    <div class="card border-0 shadow-sm admin-card">
        <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 p-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <h5 class="fw-bold text-white mb-0">
                <i class="fas fa-list-ol text-warning me-2"></i>Tabla de Ranking
                <span class="badge bg-warning text-dark ms-2 rounded-pill fs-6"><?= count($ranking_visible) ?></span>
            </h5>
            <small class="text-white-50">Mostrando <?= $top === 'todos' ? 'todos los clientes activos' : 'Top ' . esc($top) ?> de <?= $totalClientes ?> clientes activos</small>
        </div>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead class="table-active text-uppercase small text-white-50">
                    <tr>
                        <th class="ps-3 text-center" style="width: 75px;">Pos.</th>
                        <th>Cliente</th>
                        <th class="text-center">Compras</th>
                        <th class="text-center">Meses Activos</th>
                        <th class="text-end">Importe Total</th>
                        <th class="text-center">Primera Compra</th>
                        <th class="text-center">Última Compra</th>
                        <th class="text-center">Días Últ.</th>
                        <th style="min-width: 190px;">Puntuación</th>
                        <th class="text-center pe-3">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ranking_visible as $c): ?>
                        <?php
                        $pos = (int) $c['posicion'];
                        $score = (float) $c['puntaje_total'];
                        $badgePos = '<span class="badge bg-secondary bg-opacity-50 text-white rounded-pill px-2 py-1">#' . $pos . '</span>';
                        $rowHighlight = '';
                        if ($pos === 1) {
                            $badgePos = '<span class="badge rounded-pill px-2 py-1 fw-bold" style="background: linear-gradient(135deg, #ffd700, #f59e0b); color: #1e293b;"><i class="fas fa-medal me-1"></i>1°</span>';
                            $rowHighlight = 'style="background-color: rgba(255, 215, 0, 0.06);"';
                        } elseif ($pos === 2) {
                            $badgePos = '<span class="badge rounded-pill px-2 py-1 fw-bold" style="background: linear-gradient(135deg, #e2e8f0, #94a3b8); color: #0f172a;"><i class="fas fa-medal me-1"></i>2°</span>';
                            $rowHighlight = 'style="background-color: rgba(226, 232, 240, 0.05);"';
                        } elseif ($pos === 3) {
                            $badgePos = '<span class="badge rounded-pill px-2 py-1 fw-bold" style="background: linear-gradient(135deg, #d97706, #b45309); color: #ffffff;"><i class="fas fa-medal me-1"></i>3°</span>';
                            $rowHighlight = 'style="background-color: rgba(217, 119, 6, 0.05);"';
                        }

                        $barColor = 'bg-danger';
                        if ($score >= 75) {
                            $barColor = 'bg-success';
                        } elseif ($score >= 50) {
                            $barColor = 'bg-warning';
                        } elseif ($score >= 25) {
                            $barColor = 'bg-info';
                        }
                        ?>
                        <tr <?= $rowHighlight ?>>
                            <td class="ps-3 text-center"><?= $badgePos ?></td>
                            <td>
                                <div class="fw-bold text-white"><?= esc($c['nombre']) ?></div>
                                <?php if (!empty($c['cel'])): ?>
                                    <small class="text-white-50"><i class="fab fa-whatsapp text-success me-1"></i><?= esc($c['cel']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary bg-opacity-25 text-info rounded-pill px-3 py-1 fw-bold">
                                    <?= (int) $c['numero_compras'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="text-white fw-semibold"><?= (int) $c['meses_activos'] ?></span>
                                <small class="text-white-50">/ <?= (int) $periodo['total_meses'] ?></small>
                            </td>
                            <td class="text-end fw-bold text-warning">
                                $<?= number_format((float) $c['importe_total'], 2) ?>
                            </td>
                            <td class="text-center small text-white-50">
                                <?= date('d/m/Y', strtotime($c['primera_compra'])) ?>
                            </td>
                            <td class="text-center small text-white">
                                <?= date('d/m/Y', strtotime($c['ultima_compra'])) ?>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= (int) $c['dias_ultima_compra'] <= 30 ? 'bg-success bg-opacity-25 text-success' : ((int) $c['dias_ultima_compra'] <= 90 ? 'bg-warning bg-opacity-25 text-warning' : 'bg-secondary bg-opacity-25 text-white-50') ?> rounded-pill">
                                    <?= (int) $c['dias_ultima_compra'] ?> d
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-white small"><?= number_format($score, 2) ?> / 100</span>
                                    <span class="text-white-50" style="font-size: 0.7rem;"
                                          title="Frec: <?= $c['puntaje_frecuencia'] ?> | Const: <?= $c['puntaje_constancia'] ?> | Imp: <?= $c['puntaje_importe'] ?> | Rec: <?= $c['puntaje_recencia'] ?>">
                                        F:<?= number_format((float) $c['puntaje_frecuencia'], 1) ?> · C:<?= number_format((float) $c['puntaje_constancia'], 1) ?>
                                    </span>
                                </div>
                                <div class="progress bg-dark" style="height: 8px; border-radius: 999px;">
                                    <div class="progress-bar <?= $barColor ?>" role="progressbar"
                                         style="width: <?= min(100, max(0, $score)) ?>%;"
                                         aria-valuenow="<?= $score ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </td>
                            <td class="text-center pe-3">
                                <button type="button"
                                        class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-semibold"
                                        onclick="abrirDetalleFidelidad('<?= base_url('admin/cuentas/ranking/detalle/' . (int) $c['idCliente'] . '?' . $queryDetalle) ?>')">
                                    <i class="fas fa-eye me-1"></i>Ver
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<script id="top10-chart-data" type="application/json">
<?= json_encode(array_map(static function (array $item): array {
    return [
        'posicion' => (int) $item['posicion'],
        'nombre' => $item['nombre'],
        'puntaje_total' => (float) $item['puntaje_total'],
        'puntaje_frecuencia' => (float) $item['puntaje_frecuencia'],
        'puntaje_constancia' => (float) $item['puntaje_constancia'],
        'puntaje_importe' => (float) $item['puntaje_importe'],
        'puntaje_recencia' => (float) $item['puntaje_recencia'],
    ];
}, $listaGrafica ?? ($grafica_clientes ?? $top10)), JSON_UNESCAPED_UNICODE) ?>
</script>

