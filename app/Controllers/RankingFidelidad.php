<?php

namespace App\Controllers;

use App\Libraries\FidelidadCalculator;
use App\Libraries\XlsxExporter;
use App\Models\FidelidadModel;

class RankingFidelidad extends BaseController
{
    protected FidelidadCalculator $calculator;
    protected FidelidadModel $fidelidadModel;

    public function __construct()
    {
        $this->calculator = new FidelidadCalculator();
        $this->fidelidadModel = new FidelidadModel();
    }

    public function index()
    {
        $ctx = $this->obtenerContextoRanking();

        if ($this->request->getHeaderLine('HX-Request') && $this->request->getVar('partial') === '1') {
            return view('cuentas_clientes/_ranking_contenido', $ctx);
        }

        return view('cuentas_clientes/ranking', $ctx);
    }

    public function detalle(int $idCliente)
    {
        $ctx = $this->obtenerContextoRanking();
        $clienteRanking = null;

        foreach ($ctx['ranking_completo'] as $row) {
            if ((int) $row['idCliente'] === $idCliente) {
                $clienteRanking = $row;
                break;
            }
        }

        if (!$clienteRanking) {
            return $this->response->setStatusCode(404)->setBody(
                '<div class="p-4 text-center text-muted">No se encontraron compras válidas para este cliente en el periodo seleccionado.</div>'
            );
        }

        $detalle = $this->fidelidadModel->obtenerDetalleClientePeriodo(
            $idCliente,
            $ctx['periodo']['fecha_inicio'],
            $ctx['periodo']['fecha_fin'],
            $this->calculator
        );

        return view('cuentas_clientes/_detalle_fidelidad_modal', [
            'cliente' => $clienteRanking,
            'periodo' => $ctx['periodo'],
            'detalle' => $detalle,
        ]);
    }

    public function exportar()
    {
        $ctx = $this->obtenerContextoRanking();
        $periodo = $ctx['periodo'];
        $topLabel = $ctx['top'] === 'todos' ? 'Todos' : 'Top ' . $ctx['top'];

        $rows = [
            [['v' => 'TurixShop - Ranking de Fidelidad de Clientes', 's' => 1]],
            ['Periodo evaluado:', $periodo['etiqueta']],
            ['Filtro de visualización:', $topLabel, 'Generado:', date('d/m/Y H:i')],
            [],
            [
                ['v' => 'Posición', 's' => 2],
                ['v' => 'Nombre del Cliente', 's' => 2],
                ['v' => 'Núm. Compras', 's' => 2],
                ['v' => 'Meses Activos', 's' => 2],
                ['v' => 'Importe Total ($)', 's' => 2],
                ['v' => 'Primera Compra', 's' => 2],
                ['v' => 'Última Compra', 's' => 2],
                ['v' => 'Días Últ. Compra', 's' => 2],
                ['v' => 'Pts. Frecuencia (40)', 's' => 2],
                ['v' => 'Pts. Constancia (30)', 's' => 2],
                ['v' => 'Pts. Importe (20)', 's' => 2],
                ['v' => 'Pts. Recencia (10)', 's' => 2],
                ['v' => 'Puntuación Final (100)', 's' => 2],
            ],
        ];

        foreach ($ctx['ranking_visible'] as $c) {
            $rows[] = [
                (int) $c['posicion'],
                (string) $c['nombre'],
                (int) $c['numero_compras'],
                (int) $c['meses_activos'],
                ['v' => (float) $c['importe_total'], 's' => 3],
                date('d/m/Y', strtotime($c['primera_compra'])),
                date('d/m/Y', strtotime($c['ultima_compra'])),
                (int) $c['dias_ultima_compra'],
                (float) $c['puntaje_frecuencia'],
                (float) $c['puntaje_constancia'],
                (float) $c['puntaje_importe'],
                (float) $c['puntaje_recencia'],
                (float) $c['puntaje_total'],
            ];
        }

        $exporter = new XlsxExporter();
        $binary = $exporter->generarBinario(
            'Ranking Fidelidad',
            $rows,
            [10, 30, 14, 14, 18, 15, 15, 16, 18, 18, 16, 16, 20]
        );

        $filename = 'ranking_fidelidad_turixshop_' . date('Ymd_His') . '.xlsx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($binary);
    }

    private function obtenerContextoRanking(): array
    {
        $modo = (string) ($this->request->getVar('modo') ?: 'anio_actual');
        $anioParam = $this->request->getVar('anio');
        $anio = is_numeric($anioParam) ? (int) $anioParam : null;
        $fechaInicio = $this->request->getVar('fecha_inicio') ?: null;
        $fechaFin = $this->request->getVar('fecha_fin') ?: null;

        $topRaw = (string) ($this->request->getVar('top') ?: '10');
        $opcionesTopValidas = ['5', '10', '15', '20', '50', 'todos'];
        $top = in_array($topRaw, $opcionesTopValidas, true) ? $topRaw : '10';

        $periodo = $this->calculator->resolverPeriodo($modo, $anio, $fechaInicio, $fechaFin);
        $aniosDisponibles = $this->fidelidadModel->obtenerAniosDisponibles();
        $clientesMap = $this->fidelidadModel->obtenerClientesMapa();
        $operaciones = $this->fidelidadModel->obtenerOperacionesPeriodo(
            $periodo['fecha_inicio'],
            $periodo['fecha_fin']
        );

        $resultado = $this->calculator->calcularRanking($operaciones, $clientesMap, $periodo);
        $rankingCompleto = $resultado['ranking'];
        $rankingVisible = ($top === 'todos')
            ? $rankingCompleto
            : array_slice($rankingCompleto, 0, (int) $top);

        return [
            'titulo' => 'Ranking de Fidelidad de Clientes',
            'periodo' => $periodo,
            'top' => $top,
            'anios_disponibles' => $aniosDisponibles,
            'ranking_completo' => $rankingCompleto,
            'ranking_visible' => $rankingVisible,
            'grafica_clientes' => $rankingVisible,
            'top10' => $resultado['top10'],
            'resumen' => $resultado['resumen'],
        ];
    }
}

