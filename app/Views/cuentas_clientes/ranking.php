<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<link class="styles-admin-theme" rel="stylesheet" href="<?= base_url('css/admin.css?v=1.4') ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<div class="container py-4">
    <!-- Encabezado -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 admin-title">Ranking de Fidelidad de Clientes</h2>
            <p class="text-muted mb-0">Identifica a los clientes más frecuentes y constantes del negocio para reconocimientos y campañas.</p>
            <div class="admin-subtitle-line" style="background-color: var(--turix-accent, #ff8c00);"></div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a id="btn-exportar-excel"
               href="<?= base_url('admin/cuentas/ranking/exportar?' . http_build_query([
                   'modo' => $periodo['modo'],
                   'anio' => $periodo['anio'],
                   'fecha_inicio' => $periodo['fecha_inicio'],
                   'fecha_fin' => $periodo['fecha_fin'],
                   'top' => $top,
               ])) ?>"
               class="btn btn-success rounded-pill px-4 fw-bold d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
            <a href="<?= base_url('admin/cuentas') ?>"
               class="btn btn-outline-dark rounded-pill px-4 fw-semibold d-inline-flex align-items-center gap-2">
                <i class="fas fa-arrow-left"></i> Volver a Cuentas
            </a>
        </div>
    </div>

    <!-- Panel de Filtros -->
    <div class="card border-0 shadow-sm admin-card p-3 p-md-4 mb-4">
        <form id="form-filtros-fidelidad" method="GET" action="<?= base_url('admin/cuentas/ranking') ?>">
            <input type="hidden" name="modo" id="filtro-modo" value="<?= esc($periodo['modo']) ?>">
            <input type="hidden" name="partial" value="1">

            <!-- Accesos rápidos de periodo -->
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="text-white-50 small fw-semibold me-1"><i class="fas fa-calendar-check text-warning me-1"></i>Periodo rápido:</span>
                <?php
                $modosRapidos = [
                    'anio_actual' => 'Año actual',
                    'mes_actual' => 'Mes actual',
                    'anio_anterior' => 'Año anterior',
                    'personalizado' => 'Personalizado',
                ];
                ?>
                <?php foreach ($modosRapidos as $clave => $etiqueta): ?>
                    <button type="button"
                            class="btn btn-sm rounded-pill px-3 fw-semibold btn-modo-periodo <?= $periodo['modo'] === $clave ? 'btn-warning text-dark' : 'btn-outline-light' ?>"
                            data-modo="<?= esc($clave) ?>"
                            onclick="seleccionarModoPeriodo('<?= esc($clave) ?>')">
                        <?= esc($etiqueta) ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="row g-3 align-items-end">
                <div class="col-6 col-md-2">
                    <label for="filtro-anio" class="form-label text-white-50 small fw-semibold mb-1">Año</label>
                    <select name="anio" id="filtro-anio" class="form-select form-select-sm bg-dark text-white border-secondary"
                            onchange="seleccionarAnio(this.value)">
                        <?php foreach ($anios_disponibles as $anioOpt): ?>
                            <option value="<?= (int) $anioOpt ?>" <?= (int) $periodo['anio'] === (int) $anioOpt ? 'selected' : '' ?>>
                                <?= (int) $anioOpt ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <label for="filtro-fecha-inicio" class="form-label text-white-50 small fw-semibold mb-1">Fecha inicial</label>
                    <input type="date" name="fecha_inicio" id="filtro-fecha-inicio"
                           class="form-control form-control-sm bg-dark text-white border-secondary"
                           value="<?= esc($periodo['fecha_inicio']) ?>"
                           onchange="seleccionarPersonalizado()">
                </div>

                <div class="col-6 col-md-3">
                    <label for="filtro-fecha-fin" class="form-label text-white-50 small fw-semibold mb-1">Fecha final</label>
                    <input type="date" name="fecha_fin" id="filtro-fecha-fin"
                           class="form-control form-control-sm bg-dark text-white border-secondary"
                           value="<?= esc($periodo['fecha_fin']) ?>"
                           onchange="seleccionarPersonalizado()">
                </div>

                <div class="col-6 col-md-2">
                    <label for="filtro-top" class="form-label text-white-50 small fw-semibold mb-1">Mostrar</label>
                    <select name="top" id="filtro-top" class="form-select form-select-sm bg-dark text-white border-secondary"
                            onchange="recargarRanking()">
                        <?php
                        $opcionesTop = [
                            '5' => 'Top 5',
                            '10' => 'Top 10',
                            '15' => 'Top 15',
                            '20' => 'Top 20',
                            '50' => 'Top 50',
                            'todos' => 'Todos',
                        ];
                        ?>
                        <?php foreach ($opcionesTop as $val => $lbl): ?>
                            <option value="<?= esc($val) ?>" <?= $top === $val ? 'selected' : '' ?>><?= esc($lbl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-grid">
                    <button type="button" class="btn btn-sm btn-warning fw-bold rounded-pill py-2" onclick="recargarRanking()">
                        <i class="fas fa-sync-alt me-1"></i> Aplicar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Contenido Dinámico del Ranking -->
    <div id="ranking-contenido">
        <?= view('cuentas_clientes/_ranking_contenido') ?>
    </div>
</div>

<!-- Modal de Detalle de Fidelidad -->
<div class="modal fade" id="modalDetalleFidelidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content admin-card text-white border-secondary border-opacity-25" id="modalDetalleFidelidadBody">
            <div class="p-5 text-center">
                <div class="spinner-border text-warning" role="status"></div>
                <p class="mt-2 text-white-50 mb-0">Cargando detalle del cliente...</p>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    let chartFidelidadInstance = null;

    function actualizarBotonesModo(modoActivo) {
        document.querySelectorAll('.btn-modo-periodo').forEach(btn => {
            if (btn.dataset.modo === modoActivo) {
                btn.classList.remove('btn-outline-light');
                btn.classList.add('btn-warning', 'text-dark');
            } else {
                btn.classList.remove('btn-warning', 'text-dark');
                btn.classList.add('btn-outline-light');
            }
        });
    }

    function construirQueryParams(incluirPartial = false) {
        const params = new URLSearchParams();
        params.set('modo', document.getElementById('filtro-modo').value);
        params.set('anio', document.getElementById('filtro-anio').value);
        params.set('fecha_inicio', document.getElementById('filtro-fecha-inicio').value);
        params.set('fecha_fin', document.getElementById('filtro-fecha-fin').value);
        params.set('top', document.getElementById('filtro-top').value);
        if (incluirPartial) {
            params.set('partial', '1');
        }
        return params;
    }

    function actualizarEnlaceExportacion() {
        const btnExport = document.getElementById('btn-exportar-excel');
        if (!btnExport) return;
        const params = construirQueryParams(false);
        btnExport.href = '<?= base_url('admin/cuentas/ranking/exportar') ?>?' + params.toString();
    }

    window.seleccionarModoPeriodo = function (modo) {
        document.getElementById('filtro-modo').value = modo;
        actualizarBotonesModo(modo);

        const hoy = new Date();
        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
        const dd = String(hoy.getDate()).padStart(2, '0');
        const hoyStr = `${yyyy}-${mm}-${dd}`;

        if (modo === 'anio_actual') {
            document.getElementById('filtro-anio').value = yyyy;
            document.getElementById('filtro-fecha-inicio').value = `${yyyy}-01-01`;
            document.getElementById('filtro-fecha-fin').value = hoyStr;
        } else if (modo === 'mes_actual') {
            document.getElementById('filtro-anio').value = yyyy;
            document.getElementById('filtro-fecha-inicio').value = `${yyyy}-${mm}-01`;
            document.getElementById('filtro-fecha-fin').value = hoyStr;
        } else if (modo === 'anio_anterior') {
            const prev = yyyy - 1;
            document.getElementById('filtro-anio').value = prev;
            document.getElementById('filtro-fecha-inicio').value = `${prev}-01-01`;
            document.getElementById('filtro-fecha-fin').value = `${prev}-12-31`;
        }

        if (modo !== 'personalizado') {
            window.recargarRanking();
        }
    };

    window.seleccionarAnio = function (anio) {
        const hoy = new Date();
        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
        const dd = String(hoy.getDate()).padStart(2, '0');
        const hoyStr = `${yyyy}-${mm}-${dd}`;

        document.getElementById('filtro-modo').value = 'anio';
        actualizarBotonesModo('anio');
        document.getElementById('filtro-fecha-inicio').value = `${anio}-01-01`;
        document.getElementById('filtro-fecha-fin').value = (parseInt(anio, 10) === yyyy) ? hoyStr : `${anio}-12-31`;
        window.recargarRanking();
    };

    window.seleccionarPersonalizado = function () {
        document.getElementById('filtro-modo').value = 'personalizado';
        actualizarBotonesModo('personalizado');
        window.recargarRanking();
    };

    window.recargarRanking = function () {
        const contenedor = document.getElementById('ranking-contenido');
        if (!contenedor) return;

        const paramsPublic = construirQueryParams(false);
        window.history.replaceState({}, '', '<?= base_url('admin/cuentas/ranking') ?>?' + paramsPublic.toString());
        actualizarEnlaceExportacion();

        const paramsFetch = construirQueryParams(true);
        contenedor.style.opacity = '0.55';

        fetch('<?= base_url('admin/cuentas/ranking') ?>?' + paramsFetch.toString(), {
            headers: { 'HX-Request': 'true' }
        })
        .then(r => r.text())
        .then(html => {
            contenedor.innerHTML = html;
            contenedor.style.opacity = '1';
            renderizarGraficaTop10();
        })
        .catch(() => {
            contenedor.style.opacity = '1';
        });
    };

    window.abrirDetalleFidelidad = function (url) {
        const modalEl = document.getElementById('modalDetalleFidelidad');
        const bodyEl = document.getElementById('modalDetalleFidelidadBody');
        if (!modalEl || !bodyEl) return;

        bodyEl.innerHTML = `
            <div class="p-5 text-center">
                <div class="spinner-border text-warning" role="status"></div>
                <p class="mt-2 text-white-50 mb-0">Cargando detalle del cliente...</p>
            </div>
        `;

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        fetch(url, { headers: { 'HX-Request': 'true' } })
            .then(r => r.text())
            .then(html => {
                bodyEl.innerHTML = html;
            })
            .catch(() => {
                bodyEl.innerHTML = '<div class="p-4 text-center text-danger">No se pudo cargar el detalle del cliente.</div>';
            });
    };

    function renderizarGraficaTop10() {
        const canvas = document.getElementById('chartTop10Fidelidad');
        const dataScript = document.getElementById('top10-chart-data');
        if (!canvas || !dataScript || typeof Chart === 'undefined') return;

        let items = [];
        try {
            items = JSON.parse(dataScript.textContent || '[]');
        } catch (e) {
            return;
        }

        if (chartFidelidadInstance) {
            chartFidelidadInstance.destroy();
            chartFidelidadInstance = null;
        }

        if (!items.length) return;

        const labels = items.map(i => `#${i.posicion} ${i.nombre}`);
        const scores = items.map(i => i.puntaje_total);
        const colors = items.map(i => {
            if (i.posicion === 1) return '#ffd700';
            if (i.posicion === 2) return '#cbd5e1';
            if (i.posicion === 3) return '#d97706';
            return '#ff8c00';
        });

        chartFidelidadInstance = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Puntuación de Fidelidad',
                    data: scores,
                    backgroundColor: colors,
                    borderRadius: 6,
                    barThickness: 22
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        min: 0,
                        max: 100,
                        grid: { color: 'rgba(255, 255, 255, 0.08)' },
                        ticks: { color: '#94a3b8' }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { color: '#f8fafc', font: { weight: '600' } }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const item = items[ctx.dataIndex];
                                return [
                                    ` Total: ${item.puntaje_total.toFixed(2)} pts`,
                                    ` Frecuencia: ${item.puntaje_frecuencia.toFixed(2)} / 40`,
                                    ` Constancia: ${item.puntaje_constancia.toFixed(2)} / 30`,
                                    ` Importe: ${item.puntaje_importe.toFixed(2)} / 20`,
                                    ` Recencia: ${item.puntaje_recencia.toFixed(2)} / 10`
                                ];
                            }
                        }
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', renderizarGraficaTop10);
    document.addEventListener('htmx:afterSwap', renderizarGraficaTop10);
})();
</script>
<?= $this->endSection() ?>

