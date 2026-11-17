<?php

namespace App\Models;

use App\Libraries\FidelidadCalculator;
use CodeIgniter\Database\ConnectionInterface;

class FidelidadModel
{
    protected ConnectionInterface $db;

    public function __construct(?ConnectionInterface $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    /**
     * Obtiene los años con registros de compras disponibles para el filtro de Año.
     *
     * @return int[]
     */
    public function obtenerAniosDisponibles(): array
    {
        $anioActual = (int) date('Y');
        $anios = [$anioActual, $anioActual - 1];

        $rowsCc = $this->db->table('t_cuenta_cliente')
            ->select('DISTINCT YEAR(fechaCompra) AS anio', false)
            ->where('fechaCompra IS NOT NULL')
            ->get()
            ->getResultArray();

        foreach ($rowsCc as $r) {
            if (!empty($r['anio'])) {
                $anios[] = (int) $r['anio'];
            }
        }

        $rowsVs = $this->db->table('t_ventas_semillas')
            ->select('DISTINCT YEAR(fecha) AS anio', false)
            ->where('fecha IS NOT NULL')
            ->get()
            ->getResultArray();

        foreach ($rowsVs as $r) {
            if (!empty($r['anio'])) {
                $anios[] = (int) $r['anio'];
            }
        }

        $anios = array_values(array_unique(array_filter($anios, static fn(int $y) => $y >= 2020 && $y <= $anioActual + 1)));
        rsort($anios);

        return $anios;
    }

    /**
     * Obtiene mapa de clientes indexado por idCliente.
     *
     * @return array<int, array<string, mixed>>
     */
    public function obtenerClientesMapa(): array
    {
        $builder = $this->db->table('t_clientes')
            ->select('idCliente, nombre, cel, tipoCliente');

        if (!empty(FidelidadCalculator::CLIENTES_EXCLUIDOS)) {
            $builder->whereNotIn('idCliente', FidelidadCalculator::CLIENTES_EXCLUIDOS);
        }

        $rows = $builder->get()->getResultArray();

        $mapa = [];
        foreach ($rows as $row) {
            $mapa[(int) $row['idCliente']] = $row;
        }

        return $mapa;
    }

    /**
     * Obtiene todas las operaciones de compra válidas en el periodo desde:
     * - t_cuenta_cliente (productos en existencia y por encargo registrados en cuenta)
     * - t_ventas_semillas (ventas directas de contado con cliente identificado y sin duplicar cuenta)
     *
     * @return array<int, array<string, mixed>>
     */
    public function obtenerOperacionesPeriodo(string $fechaInicio, string $fechaFin): array
    {
        $cuentaRows = $this->db->table('t_cuenta_cliente')
            ->select('idCliente, fechaCompra AS fecha, totalProduc AS monto, estatusCompra AS estatus, descProducto AS descripcion')
            ->where('fechaCompra >=', $fechaInicio)
            ->where('fechaCompra <=', $fechaFin)
            ->where('idCliente >', 0)
            ->get()
            ->getResultArray();

        $semillasRows = $this->db->table('t_ventas_semillas')
            ->select('id_cliente AS idCliente, fecha, (cantidad * precio_venta) AS monto, estatus_pago AS estatus, producto AS descripcion', false)
            ->where('fecha >=', $fechaInicio)
            ->where('fecha <=', $fechaFin)
            ->where('id_cliente IS NOT NULL')
            ->where('id_cliente >', 0)
            ->groupStart()
            ->where('id_cuenta_cliente IS NULL')
            ->orWhere('id_cuenta_cliente', 0)
            ->groupEnd()
            ->get()
            ->getResultArray();

        return array_merge($cuentaRows, $semillasRows);
    }

    /**
     * Obtiene el detalle completo de compras y distribución mensual de un cliente en el periodo.
     */
    public function obtenerDetalleClientePeriodo(
        int $idCliente,
        string $fechaInicio,
        string $fechaFin,
        FidelidadCalculator $calculator
    ): array {
        $cuentaRows = $this->db->table('t_cuenta_cliente cc')
            ->select('cc.idCompra AS id_registro, cc.fechaCompra AS fecha, cc.cantidad, cc.descProducto AS descripcion, cc.precioUnit AS precio_unit, cc.totalProduc AS monto, cc.estatusCompra AS estatus, cc.idInventario, i.codigo_sku')
            ->join('t_inventario i', 'i.id = cc.idInventario', 'left')
            ->where('cc.idCliente', $idCliente)
            ->where('cc.fechaCompra >=', $fechaInicio)
            ->where('cc.fechaCompra <=', $fechaFin)
            ->orderBy('cc.fechaCompra', 'DESC')
            ->orderBy('cc.idCompra', 'DESC')
            ->get()
            ->getResultArray();

        $semillasRows = $this->db->table('t_ventas_semillas vs')
            ->select('vs.id AS id_registro, vs.fecha, vs.cantidad, vs.producto AS descripcion, vs.precio_venta AS precio_unit, (vs.cantidad * vs.precio_venta) AS monto, vs.estatus_pago AS estatus', false)
            ->where('vs.id_cliente', $idCliente)
            ->where('vs.fecha >=', $fechaInicio)
            ->where('vs.fecha <=', $fechaFin)
            ->groupStart()
            ->where('vs.id_cuenta_cliente IS NULL')
            ->orWhere('vs.id_cuenta_cliente', 0)
            ->groupEnd()
            ->orderBy('vs.fecha', 'DESC')
            ->orderBy('vs.id', 'DESC')
            ->get()
            ->getResultArray();

        $historial = [];
        $distribucionMensual = [];
        $importeAcumulado = 0.0;

        $procesar = static function (array $row, string $origen) use (&$historial, &$distribucionMensual, &$importeAcumulado, $calculator): void {
            $estatus = (string) ($row['estatus'] ?? '');
            $desc = (string) ($row['descripcion'] ?? '');
            $monto = (float) ($row['monto'] ?? 0);
            $fecha = (string) ($row['fecha'] ?? '');

            if ($calculator->esCompraCancelada($estatus, $desc) || $calculator->esGastoEnvio($desc)) {
                return;
            }

            $esAjuste = $monto < 0;
            $importeAcumulado += $monto;

            $ym = substr($fecha, 0, 7);
            if (!isset($distribucionMensual[$ym])) {
                $distribucionMensual[$ym] = [
                    'mes' => $ym,
                    'fechas' => [],
                    'partidas' => 0,
                    'importe' => 0.0,
                ];
            }
            if (!$esAjuste && $monto > 0) {
                $distribucionMensual[$ym]['fechas'][$fecha] = true;
                $distribucionMensual[$ym]['partidas']++;
            }
            $distribucionMensual[$ym]['importe'] += $monto;

            $historial[] = [
                'fecha' => $fecha,
                'descripcion' => $desc,
                'cantidad' => (int) ($row['cantidad'] ?? 0),
                'precio_unit' => (float) ($row['precio_unit'] ?? 0),
                'monto' => $monto,
                'origen' => $origen,
                'codigo_sku' => $row['codigo_sku'] ?? null,
                'es_ajuste' => $esAjuste,
            ];
        };

        foreach ($cuentaRows as $r) {
            $origen = ((int) ($r['idInventario'] ?? 0) > 0) ? 'Inventario' : 'Encargo / Libre';
            $procesar($r, $origen);
        }

        foreach ($semillasRows as $r) {
            $procesar($r, 'Venta directa');
        }

        usort($historial, static fn(array $a, array $b) => strcmp($b['fecha'], $a['fecha']));

        ksort($distribucionMensual);
        $mensualList = [];
        foreach ($distribucionMensual as $ym => $info) {
            $mensualList[] = [
                'mes' => $ym,
                'compras' => count($info['fechas']),
                'partidas' => $info['partidas'],
                'importe' => round(max(0.0, $info['importe']), 2),
            ];
        }

        return [
            'historial' => $historial,
            'distribucion_mensual' => $mensualList,
            'importe_acumulado' => max(0.0, round($importeAcumulado, 2)),
        ];
    }
}

