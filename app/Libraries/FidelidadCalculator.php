<?php

namespace App\Libraries;

use DateTimeImmutable;

class FidelidadCalculator
{
    /**
     * IDs de clientes excluidos del ranking (p. ej. cuentas internas o de administración).
     * 63 = Noemí Carrillo
     */
    public const CLIENTES_EXCLUIDOS = [63];

    /**
     * Resuelve las fechas, referencia de recencia y meses del periodo seleccionado.
     */
    public function resolverPeriodo(
        string $modo = 'anio_actual',
        ?int $anio = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        ?string $hoy = null
    ): array {
        $hoyDate = $hoy ? new DateTimeImmutable($hoy) : new DateTimeImmutable('today');
        $anioActual = (int) $hoyDate->format('Y');
        $anioSeleccionado = $anio ?: $anioActual;

        switch ($modo) {
            case 'mes_actual':
                $inicio = $hoyDate->modify('first day of this month');
                $fin = $hoyDate;
                $etiqueta = 'Mes actual (' . $inicio->format('d/m/Y') . ' - ' . $fin->format('d/m/Y') . ')';
                break;

            case 'anio_anterior':
                $anioPrev = $anioActual - 1;
                $inicio = new DateTimeImmutable("{$anioPrev}-01-01");
                $fin = new DateTimeImmutable("{$anioPrev}-12-31");
                $anioSeleccionado = $anioPrev;
                $etiqueta = "Año {$anioPrev} (01/01/{$anioPrev} - 31/12/{$anioPrev})";
                break;

            case 'anio':
                $inicio = new DateTimeImmutable("{$anioSeleccionado}-01-01");
                $fin = ($anioSeleccionado === $anioActual)
                    ? $hoyDate
                    : new DateTimeImmutable("{$anioSeleccionado}-12-31");
                $etiqueta = "Año {$anioSeleccionado} (" . $inicio->format('d/m/Y') . ' - ' . $fin->format('d/m/Y') . ')';
                break;

            case 'personalizado':
                $inicio = $this->parseDateOrDefault($fechaInicio, $hoyDate->format('Y') . '-01-01');
                $fin = $this->parseDateOrDefault($fechaFin, $hoyDate->format('Y-m-d'));
                if ($inicio > $fin) {
                    [$inicio, $fin] = [$fin, $inicio];
                }
                $etiqueta = 'Personalizado (' . $inicio->format('d/m/Y') . ' - ' . $fin->format('d/m/Y') . ')';
                break;

            case 'anio_actual':
            default:
                $modo = 'anio_actual';
                $anioSeleccionado = $anioActual;
                $inicio = new DateTimeImmutable("{$anioActual}-01-01");
                $fin = $hoyDate;
                $etiqueta = "Año actual ({$inicio->format('d/m/Y')} - {$fin->format('d/m/Y')})";
                break;
        }

        $refRecencia = ($fin > $hoyDate) ? $hoyDate : $fin;
        $totalMeses = $this->calcularMesesPeriodo($inicio, $fin);

        return [
            'modo' => $modo,
            'anio' => $anioSeleccionado,
            'fecha_inicio' => $inicio->format('Y-m-d'),
            'fecha_fin' => $fin->format('Y-m-d'),
            'fecha_referencia_recencia' => $refRecencia->format('Y-m-d'),
            'total_meses' => $totalMeses,
            'etiqueta' => $etiqueta,
        ];
    }

    private function parseDateOrDefault(?string $value, string $default): DateTimeImmutable
    {
        if (!empty($value)) {
            $parsed = DateTimeImmutable::createFromFormat('Y-m-d', trim($value));
            if ($parsed instanceof DateTimeImmutable) {
                return $parsed;
            }
        }
        return new DateTimeImmutable($default);
    }

    public function calcularMesesPeriodo(DateTimeImmutable $inicio, DateTimeImmutable $fin): int
    {
        $meses = (((int) $fin->format('Y') - (int) $inicio->format('Y')) * 12)
            + ((int) $fin->format('n') - (int) $inicio->format('n')) + 1;

        return max(1, $meses);
    }

    public function esGastoEnvio(?string $descripcion): bool
    {
        $texto = mb_strtolower(trim((string) $descripcion), 'UTF-8');
        if ($texto === '') {
            return false;
        }

        return (bool) preg_match('/\b(env[ií]os?|fletes?|paqueter[ií]as?)\b/u', $texto);
    }

    public function esCompraCancelada(?string $estatus, ?string $descripcion): bool
    {
        $est = mb_strtolower(trim((string) $estatus), 'UTF-8');
        if (in_array($est, ['2', 'c', 'cancelado', 'cancelada', 'anulado', 'anulada'], true)) {
            return true;
        }

        $texto = mb_strtolower(trim((string) $descripcion), 'UTF-8');
        if ($texto !== '' && preg_match('/\b(cancelad[oa]s?|anulad[oa]s?)\b/u', $texto)) {
            return true;
        }

        return false;
    }

    public function calcularPuntajeRecencia(int $dias): float
    {
        if ($dias <= 30) {
            return 10.0;
        }
        if ($dias <= 60) {
            return 7.5;
        }
        if ($dias <= 90) {
            return 5.0;
        }
        if ($dias <= 180) {
            return 2.5;
        }

        return 0.0;
    }

    /**
     * Calcula el ranking completo de fidelidad para todos los clientes activos en el periodo.
     *
     * @param array<int, array<string, mixed>> $operaciones
     * @param array<int, array<string, mixed>> $clientesMap
     * @param array<string, mixed> $periodo
     * @return array<string, mixed>
     */
    public function calcularRanking(
        array $operaciones,
        array $clientesMap,
        array $periodo,
        array $clientesExcluidos = self::CLIENTES_EXCLUIDOS
    ): array {
        $acumulado = [];
        $excluidosLookup = array_flip(array_map('intval', $clientesExcluidos));

        foreach ($operaciones as $op) {
            $idCliente = (int) ($op['idCliente'] ?? 0);
            $fecha = trim((string) ($op['fecha'] ?? ''));
            $monto = (float) ($op['monto'] ?? 0);
            $estatus = (string) ($op['estatus'] ?? '');
            $descripcion = (string) ($op['descripcion'] ?? '');

            if ($idCliente <= 0 || isset($excluidosLookup[$idCliente]) || $fecha === '' || !isset($clientesMap[$idCliente])) {
                continue;
            }

            if ($fecha < $periodo['fecha_inicio'] || $fecha > $periodo['fecha_fin']) {
                continue;
            }

            if ($this->esCompraCancelada($estatus, $descripcion) || $this->esGastoEnvio($descripcion)) {
                continue;
            }

            if (!isset($acumulado[$idCliente])) {
                $acumulado[$idCliente] = [
                    'fechas_validas' => [],
                    'meses_activos' => [],
                    'importe_total' => 0.0,
                ];
            }

            if ($monto > 0) {
                $acumulado[$idCliente]['fechas_validas'][$fecha] = true;
                $acumulado[$idCliente]['meses_activos'][substr($fecha, 0, 7)] = true;
                $acumulado[$idCliente]['importe_total'] += $monto;
            } elseif ($monto < 0) {
                // Descuentos, devoluciones o saldos a favor reducen el importe sin contar como compra
                $acumulado[$idCliente]['importe_total'] += $monto;
            }
        }

        $clientesActivos = [];
        $maxCompras = 0;
        $maxImporte = 0.0;
        $totalComprasGlobal = 0;
        $importeTotalGlobal = 0.0;

        foreach ($acumulado as $idCliente => $datos) {
            $numCompras = count($datos['fechas_validas']);
            if ($numCompras === 0) {
                continue;
            }

            $importeNeto = max(0.0, round($datos['importe_total'], 2));
            $mesesActivos = count($datos['meses_activos']);
            $fechas = array_keys($datos['fechas_validas']);
            sort($fechas);
            $primeraCompra = $fechas[0];
            $ultimaCompra = $fechas[count($fechas) - 1];

            $diasDesdeUltima = max(
                0,
                (int) (new DateTimeImmutable($ultimaCompra))
                    ->diff(new DateTimeImmutable($periodo['fecha_referencia_recencia']))
                    ->format('%r%a')
            );

            $maxCompras = max($maxCompras, $numCompras);
            $maxImporte = max($maxImporte, $importeNeto);
            $totalComprasGlobal += $numCompras;
            $importeTotalGlobal += $importeNeto;

            $clienteInfo = $clientesMap[$idCliente];
            $clientesActivos[] = [
                'idCliente' => $idCliente,
                'nombre' => $clienteInfo['nombre'] ?? 'Cliente #' . $idCliente,
                'cel' => $clienteInfo['cel'] ?? '',
                'tipoCliente' => $clienteInfo['tipoCliente'] ?? 'General',
                'numero_compras' => $numCompras,
                'meses_activos' => $mesesActivos,
                'importe_total' => $importeNeto,
                'primera_compra' => $primeraCompra,
                'ultima_compra' => $ultimaCompra,
                'dias_ultima_compra' => $diasDesdeUltima,
            ];
        }

        $totalMesesPeriodo = (int) ($periodo['total_meses'] ?? 1);
        $ranking = [];

        foreach ($clientesActivos as $item) {
            $pFrecuencia = $maxCompras > 0
                ? round(($item['numero_compras'] / $maxCompras) * 40, 2)
                : 0.0;
            $pConstancia = $totalMesesPeriodo > 0
                ? round((min($item['meses_activos'], $totalMesesPeriodo) / $totalMesesPeriodo) * 30, 2)
                : 0.0;
            $pImporte = $maxImporte > 0
                ? round(($item['importe_total'] / $maxImporte) * 20, 2)
                : 0.0;
            $pRecencia = $this->calcularPuntajeRecencia($item['dias_ultima_compra']);

            $pTotal = round($pFrecuencia + $pConstancia + $pImporte + $pRecencia, 2);

            $item['puntaje_frecuencia'] = $pFrecuencia;
            $item['puntaje_constancia'] = $pConstancia;
            $item['puntaje_importe'] = $pImporte;
            $item['puntaje_recencia'] = $pRecencia;
            $item['puntaje_total'] = $pTotal;

            $ranking[] = $item;
        }

        usort($ranking, static function (array $a, array $b): int {
            return ($b['puntaje_total'] <=> $a['puntaje_total'])
                ?: ($b['numero_compras'] <=> $a['numero_compras'])
                ?: ($b['meses_activos'] <=> $a['meses_activos'])
                ?: ($b['importe_total'] <=> $a['importe_total'])
                ?: strcmp($b['ultima_compra'], $a['ultima_compra'])
                ?: ($a['idCliente'] <=> $b['idCliente']);
        });

        foreach ($ranking as $idx => &$row) {
            $row['posicion'] = $idx + 1;
        }
        unset($row);

        $totalClientes = count($ranking);

        return [
            'ranking' => $ranking,
            'top10' => array_slice($ranking, 0, 10),
            'resumen' => [
                'total_clientes_activos' => $totalClientes,
                'total_compras' => $totalComprasGlobal,
                'importe_total' => round($importeTotalGlobal, 2),
                'promedio_compras' => $totalClientes > 0 ? round($totalComprasGlobal / $totalClientes, 2) : 0.0,
                'mejor_cliente' => $ranking[0] ?? null,
            ],
            'max_compras' => $maxCompras,
            'max_importe' => $maxImporte,
        ];
    }
}

