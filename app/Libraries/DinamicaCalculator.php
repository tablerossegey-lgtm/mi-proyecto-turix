<?php

namespace App\Libraries;

class DinamicaCalculator
{
    public const ESTADOS_DINAMICA = [
        'borrador'                => 'Borrador',
        'programada'              => 'Programada',
        'activa'                  => 'Activa',
        'participaciones_cerradas'=> 'Participaciones cerradas',
        'sorteo_realizado'        => 'Sorteo realizado',
        'finalizada'              => 'Finalizada',
        'cancelada'               => 'Cancelada',
    ];

    public const MODALIDADES_SORTEO = [
        'digital'     => 'Digital',
        'presencial'  => 'Presencial',
        'por_definir' => 'Por definir',
    ];

    public const TIPOS_REQUISITO = [
        'acumulado_unico'   => 'Una participación por cliente al alcanzar importe mínimo acumulado',
        'por_compra_minima' => 'Una participación por cada compra que alcance el importe mínimo',
        'multiplo_acumulado'=> 'Una participación por cada múltiplo del importe mínimo acumulado',
        'manual_sin_compra' => 'Participación manual sin compra requerida',
    ];

    public const ESTADOS_ENTREGA = [
        'pendiente_asignar'  => 'Pendiente de asignar',
        'ganador_registrado' => 'Ganador registrado',
        'ganador_notificado' => 'Ganador notificado',
        'pendiente_entrega'  => 'Pendiente de entrega',
        'entregado'          => 'Entregado',
    ];

    private FidelidadCalculator $fidelidadCalculator;

    public function __construct(?FidelidadCalculator $fidelidadCalculator = null)
    {
        $this->fidelidadCalculator = $fidelidadCalculator ?? new FidelidadCalculator();
    }

    public function esCompraCancelada(?string $estatus, ?string $descripcion): bool
    {
        return $this->fidelidadCalculator->esCompraCancelada($estatus, $descripcion);
    }

    public function esGastoEnvio(?string $descripcion): bool
    {
        return $this->fidelidadCalculator->esGastoEnvio($descripcion);
    }

    public function esCompraLiquidada(?string $estatus): bool
    {
        $est = mb_strtolower(trim((string) $estatus), 'UTF-8');
        return in_array($est, ['1', 'pagado', 'pagada', 'liquidado', 'liquidada'], true);
    }

    /**
     * Evalúa si una operación de compra es válida para una dinámica dada.
     *
     * @param array<string, mixed> $op
     * @param array<string, mixed> $dinamica
     * @param array<string, bool>  $referenciasManuales Mapa [referencia => true]
     * @return array{valida: bool, motivo: string, monto: float}
     */
    public function evaluarOperacion(array $op, array $dinamica, array $referenciasManuales = []): array
    {
        $idCliente = (int) ($op['idCliente'] ?? 0);
        $fecha = trim((string) ($op['fecha'] ?? ''));
        $monto = round((float) ($op['monto'] ?? 0), 2);
        $estatus = (string) ($op['estatus'] ?? '');
        $descripcion = (string) ($op['descripcion'] ?? '');

        if ($idCliente <= 0 || $fecha === '') {
            return ['valida' => false, 'motivo' => 'Sin cliente o fecha válida', 'monto' => 0.0];
        }

        $fechaInicio = (string) ($dinamica['fecha_inicio'] ?? '');
        $fechaFin = (string) ($dinamica['fecha_fin'] ?? '');
        if ($fechaInicio !== '' && $fecha < $fechaInicio) {
            return ['valida' => false, 'motivo' => 'Fuera de periodo (anterior al inicio)', 'monto' => 0.0];
        }
        if ($fechaFin !== '' && $fecha > $fechaFin) {
            return ['valida' => false, 'motivo' => 'Fuera de periodo (posterior al cierre)', 'monto' => 0.0];
        }

        if ($this->esCompraCancelada($estatus, $descripcion)) {
            return ['valida' => false, 'motivo' => 'Compra cancelada o anulada', 'monto' => 0.0];
        }

        if ($this->esGastoEnvio($descripcion)) {
            return ['valida' => false, 'motivo' => 'Gasto de envío excluido', 'monto' => 0.0];
        }

        $idRegistro = (string) ($op['id_registro'] ?? '');
        $folio = trim((string) ($op['folio_compra'] ?? ''));
        $origenOp = (string) ($op['origen_tabla'] ?? '');
        if (($idRegistro !== '' && isset($referenciasManuales[$idRegistro]))
            || ($origenOp !== '' && $idRegistro !== '' && isset($referenciasManuales[$origenOp . ':' . $idRegistro]))
            || ($folio !== '' && isset($referenciasManuales[$folio]))
        ) {
            return ['valida' => false, 'motivo' => 'Registrada manualmente', 'monto' => 0.0];
        }

        $requiereLiquidada = !empty($dinamica['requiere_compra_liquidada']);
        if ($requiereLiquidada && $monto > 0 && !$this->esCompraLiquidada($estatus)) {
            return ['valida' => false, 'motivo' => 'Compra a crédito pendiente de liquidar', 'monto' => 0.0];
        }

        return ['valida' => true, 'motivo' => 'Válida', 'monto' => $monto];
    }

    /**
     * Evalúa todas las operaciones del periodo para calcular clientes elegibles y próximos a participar.
     *
     * @param array<int, array<string, mixed>> $operaciones
     * @param array<int, array<string, mixed>> $clientesMap
     * @param array<string, mixed>             $dinamica
     * @param array<string, bool>              $referenciasManuales
     * @return array{
     *   elegibles: array<int, array<string, mixed>>,
     *   proximos: array<int, array<string, mixed>>,
     *   evaluados: array<int, array<string, mixed>>,
     *   importe_total_valido: float
     * }
     */
    public function evaluarClientesPeriodo(
        array $operaciones,
        array $clientesMap,
        array $dinamica,
        array $referenciasManuales = []
    ): array {
        $tipoRequisito = (string) ($dinamica['tipo_requisito'] ?? 'acumulado_unico');
        $importeMinimo = round(max(0.0, (float) ($dinamica['importe_minimo'] ?? 0)), 2);
        $maxParticipaciones = isset($dinamica['max_participaciones_cliente']) && $dinamica['max_participaciones_cliente'] !== '' && $dinamica['max_participaciones_cliente'] !== null
            ? max(1, (int) $dinamica['max_participaciones_cliente'])
            : null;

        // Ordenar cronológicamente para determinar con exactitud la fecha de cumplimiento
        usort($operaciones, static function (array $a, array $b): int {
            $fa = (string) ($a['fecha'] ?? '');
            $fb = (string) ($b['fecha'] ?? '');
            if ($fa !== $fb) {
                return strcmp($fa, $fb);
            }
            return ((int) ($a['id_registro'] ?? 0)) <=> ((int) ($b['id_registro'] ?? 0));
        });

        $porCliente = [];
        $importeTotalValido = 0.0;

        foreach ($operaciones as $op) {
            $idCliente = (int) ($op['idCliente'] ?? 0);
            if ($idCliente <= 0 || in_array($idCliente, FidelidadCalculator::CLIENTES_EXCLUIDOS, true) || !isset($clientesMap[$idCliente])) {
                continue;
            }

            $eval = $this->evaluarOperacion($op, $dinamica, $referenciasManuales);
            if (!$eval['valida']) {
                continue;
            }

            $monto = $eval['monto'];
            $fecha = (string) $op['fecha'];

            if (!isset($porCliente[$idCliente])) {
                $porCliente[$idCliente] = [
                    'id_cliente'              => $idCliente,
                    'nombre'                  => (string) ($clientesMap[$idCliente]['nombre'] ?? 'Cliente #' . $idCliente),
                    'telefono'                => (string) ($clientesMap[$idCliente]['cel'] ?? ''),
                    'importe_acumulado'       => 0.0,
                    'num_compras_validas'     => 0,
                    'compras_minimas_count'   => 0,
                    'ultima_fecha_compra'     => $fecha,
                    'fecha_cumplimiento'      => null,
                    'compras_detalle'         => [],
                ];
            }

            $porCliente[$idCliente]['importe_acumulado'] = round($porCliente[$idCliente]['importe_acumulado'] + $monto, 2);
            $importeTotalValido = round($importeTotalValido + $monto, 2);

            if ($monto > 0) {
                $porCliente[$idCliente]['num_compras_validas']++;
                if ($importeMinimo <= 0 || $monto >= $importeMinimo) {
                    $porCliente[$idCliente]['compras_minimas_count']++;
                }
            }

            if ($fecha >= $porCliente[$idCliente]['ultima_fecha_compra']) {
                $porCliente[$idCliente]['ultima_fecha_compra'] = $fecha;
            }

            // Determinar fecha en que cumplió por primera vez el requisito
            if ($porCliente[$idCliente]['fecha_cumplimiento'] === null) {
                if ($tipoRequisito === 'por_compra_minima' && $monto > 0 && ($importeMinimo <= 0 || $monto >= $importeMinimo)) {
                    $porCliente[$idCliente]['fecha_cumplimiento'] = $fecha;
                } elseif (in_array($tipoRequisito, ['acumulado_unico', 'multiplo_acumulado'], true)
                    && $porCliente[$idCliente]['importe_acumulado'] >= $importeMinimo
                    && $porCliente[$idCliente]['num_compras_validas'] > 0
                ) {
                    $porCliente[$idCliente]['fecha_cumplimiento'] = $fecha;
                }
            }

            $porCliente[$idCliente]['compras_detalle'][] = [
                'id_registro' => $op['id_registro'] ?? null,
                'origen'      => $op['origen_tabla'] ?? 'cuenta',
                'fecha'       => $fecha,
                'descripcion' => (string) ($op['descripcion'] ?? ''),
                'monto'       => $monto,
                'estatus'     => (string) ($op['estatus'] ?? ''),
                'folio'       => $op['folio_compra'] ?? null,
            ];
        }

        $elegibles = [];
        $proximos = [];
        $evaluados = [];

        foreach ($porCliente as $idCliente => $row) {
            $acumulado = max(0.0, (float) $row['importe_acumulado']);
            $participaciones = 0;

            if ($tipoRequisito !== 'manual_sin_compra' && $row['num_compras_validas'] > 0) {
                if ($tipoRequisito === 'acumulado_unico') {
                    if ($acumulado >= $importeMinimo) {
                        $participaciones = 1;
                    }
                } elseif ($tipoRequisito === 'por_compra_minima') {
                    // Si hubo devoluciones que dejaron el neto en 0 o negativo, no participa
                    if ($acumulado > 0) {
                        $participaciones = (int) $row['compras_minimas_count'];
                    }
                } elseif ($tipoRequisito === 'multiplo_acumulado') {
                    if ($importeMinimo > 0 && $acumulado >= $importeMinimo) {
                        $participaciones = (int) floor($acumulado / $importeMinimo);
                    } elseif ($importeMinimo <= 0 && $acumulado > 0) {
                        $participaciones = 1;
                    }
                }
            }

            if ($maxParticipaciones !== null && $participaciones > $maxParticipaciones) {
                $participaciones = $maxParticipaciones;
            }

            $row['importe_acumulado'] = round((float) $row['importe_acumulado'], 2);
            $row['num_participaciones'] = $participaciones;
            $evaluados[$idCliente] = $row;

            if ($participaciones > 0) {
                if ($row['fecha_cumplimiento'] === null) {
                    $row['fecha_cumplimiento'] = $row['ultima_fecha_compra'];
                }
                $elegibles[$idCliente] = $row;
            } elseif ($tipoRequisito !== 'manual_sin_compra' && $acumulado > 0 && $importeMinimo > 0) {
                $faltante = round(max(0.0, $importeMinimo - $acumulado), 2);
                $avance = $importeMinimo > 0 ? min(99.9, round(($acumulado / $importeMinimo) * 100, 1)) : 0.0;
                $row['importe_minimo'] = $importeMinimo;
                $row['importe_faltante'] = $faltante;
                $row['porcentaje_avance'] = $avance;
                $proximos[] = $row;
            }
        }

        usort($proximos, static function (array $a, array $b): int {
            if ($a['porcentaje_avance'] !== $b['porcentaje_avance']) {
                return $b['porcentaje_avance'] <=> $a['porcentaje_avance'];
            }
            return strcmp((string) $b['ultima_fecha_compra'], (string) $a['ultima_fecha_compra']);
        });

        return [
            'elegibles'            => $elegibles,
            'proximos'             => $proximos,
            'evaluados'            => $evaluados,
            'importe_total_valido' => round($importeTotalValido, 2),
        ];
    }

    /**
     * Genera la plantilla por defecto de WhatsApp para una dinámica.
     */
    public function plantillaWhatsAppPorDefecto(): string
    {
        return "🎄🎉 ¡Ya estás participando en *{dinamica}* de TurixShop!\n\n"
            . "👤 Participante: *{cliente}*\n"
            . "📅 Fecha del sorteo: {fecha_sorteo}\n"
            . "🕚 Hora: {hora_sorteo}\n"
            . "✨ Participaciones: {participaciones}\n\n"
            . "¡Gracias por tu compra y mucha suerte! 💙";
    }

    /**
     * Renderiza el mensaje de WhatsApp reemplazando variables dinámicas.
     */
    public function generarMensajeWhatsApp(array $dinamica, array $participante): string
    {
        $plantilla = trim((string) ($dinamica['plantilla_whatsapp'] ?? ''));
        if ($plantilla === '') {
            $plantilla = $this->plantillaWhatsAppPorDefecto();
        }

        $fechaSorteo = !empty($dinamica['fecha_sorteo'])
            ? $this->formatearFechaLarga((string) $dinamica['fecha_sorteo'])
            : 'Por definir';
        $horaSorteo = !empty($dinamica['hora_sorteo'])
            ? $this->formatearHora12h((string) $dinamica['hora_sorteo'])
            : 'Por definir';

        $reemplazos = [
            '{cliente}'       => (string) ($participante['nombre_participante'] ?? $participante['nombre'] ?? ''),
            '{nombre}'        => (string) ($participante['nombre_participante'] ?? $participante['nombre'] ?? ''),
            '{dinamica}'      => (string) ($dinamica['nombre'] ?? ''),
            '{fecha_sorteo}'  => $fechaSorteo,
            '{hora_sorteo}'   => $horaSorteo,
            '{participaciones}' => (string) ((int) ($participante['num_participaciones'] ?? 1)),
            '{{cliente}}'     => (string) ($participante['nombre_participante'] ?? $participante['nombre'] ?? ''),
            '{{dinamica}}'    => (string) ($dinamica['nombre'] ?? ''),
            '{{fecha_sorteo}}'=> $fechaSorteo,
            '{{hora_sorteo}}' => $horaSorteo,
            '{{participaciones}}' => (string) ((int) ($participante['num_participaciones'] ?? 1)),
        ];

        return strtr($plantilla, $reemplazos);
    }

    /**
     * Genera las líneas de nombres para copiar a AppSorteos o imprimir.
     *
     * @param array<int, array<string, mixed>> $participantes
     * @param array<string, mixed>             $dinamica
     * @param int[]                            $clientesGanadoresIds
     * @param int[]                            $participantesGanadoresIds
     * @return string[]
     */
    public function generarListadoSorteo(
        array $participantes,
        array $dinamica,
        array $clientesGanadoresIds = [],
        array $participantesGanadoresIds = []
    ): array {
        $elegibles = array_values(array_filter($participantes, static function (array $p) use ($clientesGanadoresIds, $participantesGanadoresIds): bool {
            if (($p['estado'] ?? '') !== 'elegible' || (int) ($p['num_participaciones'] ?? 0) <= 0) {
                return false;
            }
            if (!empty($participantesGanadoresIds) && in_array((int) $p['id'], $participantesGanadoresIds, true)) {
                return false;
            }
            if (!empty($clientesGanadoresIds) && !empty($p['id_cliente']) && in_array((int) $p['id_cliente'], $clientesGanadoresIds, true)) {
                return false;
            }
            return true;
        }));

        // Detectar nombres duplicados entre distintos participantes
        $conteoNombres = [];
        foreach ($elegibles as $p) {
            $norm = mb_strtolower(trim((string) $p['nombre_participante']), 'UTF-8');
            $conteoNombres[$norm] = ($conteoNombres[$norm] ?? 0) + 1;
        }

        $lineas = [];
        foreach ($elegibles as $p) {
            $nombreBase = trim((string) $p['nombre_participante']);
            $norm = mb_strtolower($nombreBase, 'UTF-8');
            if (($conteoNombres[$norm] ?? 0) > 1) {
                $nombreBase .= ' [#' . str_pad((string) ($p['numero_control'] ?? $p['id']), 3, '0', STR_PAD_LEFT) . ']';
            }

            $n = max(1, (int) ($p['num_participaciones'] ?? 1));
            if ($n === 1) {
                $lineas[] = $nombreBase;
            } else {
                for ($i = 1; $i <= $n; $i++) {
                    $lineas[] = "{$nombreBase} ({$i}/{$n})";
                }
            }
        }

        return $lineas;
    }

    public function formatearFechaLarga(string $fechaYmd): string
    {
        $ts = strtotime($fechaYmd);
        if ($ts === false) {
            return $fechaYmd;
        }
        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $diaSemana = $dias[(int) date('w', $ts)];
        $dia = (int) date('j', $ts);
        $mes = $meses[(int) date('n', $ts)];
        return "{$diaSemana} {$dia} de {$mes}";
    }

    public function formatearHora12h(string $hora): string
    {
        $ts = strtotime('1970-01-01 ' . $hora);
        if ($ts === false) {
            return $hora;
        }
        $h = date('g:i', $ts);
        $ampm = date('a', $ts) === 'am' ? 'a. m.' : 'p. m.';
        return "{$h} {$ampm}";
    }
}
