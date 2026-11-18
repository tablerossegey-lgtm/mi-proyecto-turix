<?php

namespace App\Libraries;

use App\Models\DinamicaBitacoraModel;
use App\Models\DinamicaGanadorModel;
use App\Models\DinamicaModel;
use App\Models\DinamicaParticipanteModel;
use App\Models\DinamicaPremioModel;
use CodeIgniter\Database\ConnectionInterface;

class DinamicaService
{
    protected ConnectionInterface $db;
    protected DinamicaCalculator $calculator;
    protected DinamicaModel $dinamicaModel;
    protected DinamicaParticipanteModel $participanteModel;
    protected DinamicaPremioModel $premioModel;
    protected DinamicaGanadorModel $ganadorModel;
    protected DinamicaBitacoraModel $bitacoraModel;

    public function __construct(?ConnectionInterface $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
        $this->calculator = new DinamicaCalculator();
        $this->dinamicaModel = new DinamicaModel($this->db);
        $this->participanteModel = new DinamicaParticipanteModel($this->db);
        $this->premioModel = new DinamicaPremioModel($this->db);
        $this->ganadorModel = new DinamicaGanadorModel($this->db);
        $this->bitacoraModel = new DinamicaBitacoraModel($this->db);
    }

    public function tablasDisponibles(): bool
    {
        return $this->db->tableExists('t_dinamicas')
            && $this->db->tableExists('t_dinamica_participantes');
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
     * Obtiene todas las operaciones de compra del periodo (sin duplicar encargos/ventas y sin abonos).
     *
     * @return array<int, array<string, mixed>>
     */
    public function obtenerOperacionesPeriodo(string $fechaInicio, string $fechaFin): array
    {
        $cuentaRows = $this->db->table('t_cuenta_cliente')
            ->select("idCompra AS id_registro, idCliente, fechaCompra AS fecha, totalProduc AS monto, estatusCompra AS estatus, descProducto AS descripcion, folio_compra, 'cuenta' AS origen_tabla", false)
            ->where('fechaCompra >=', $fechaInicio)
            ->where('fechaCompra <=', $fechaFin)
            ->where('idCliente >', 0)
            ->get()
            ->getResultArray();

        $semillasRows = $this->db->table('t_ventas_semillas')
            ->select("id AS id_registro, id_cliente AS idCliente, fecha, (cantidad * precio_venta) AS monto, estatus_pago AS estatus, producto AS descripcion, NULL AS folio_compra, 'semillas' AS origen_tabla", false)
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
     * Evalúa una dinámica sin modificar la base de datos (útil para seguimiento de próximos).
     */
    public function evaluarDinamica(array $dinamica): array
    {
        $clientesMap = $this->obtenerClientesMapa();
        $operaciones = $this->obtenerOperacionesPeriodo(
            (string) $dinamica['fecha_inicio'],
            (string) $dinamica['fecha_fin']
        );
        $referenciasManuales = $this->participanteModel->obtenerReferenciasManualesMapa((int) $dinamica['id']);

        return $this->calculator->evaluarClientesPeriodo(
            $operaciones,
            $clientesMap,
            $dinamica,
            $referenciasManuales
        );
    }

    /**
     * Sincroniza de forma idempotente los participantes automáticos de una dinámica.
     */
    public function sincronizarParticipantes(int $idDinamica, ?string $motivoForzado = null, bool $registrarBitacora = true): array
    {
        $dinamica = $this->dinamicaModel->find($idDinamica);
        if (!$dinamica) {
            return ['success' => false, 'message' => 'Dinámica no encontrada.'];
        }

        if (!empty($dinamica['listado_cerrado']) && empty($motivoForzado)) {
            return ['success' => false, 'message' => 'El listado de participaciones ya fue cerrado oficialmente.'];
        }

        if (in_array($dinamica['estado'], ['finalizada', 'cancelada'], true) && empty($motivoForzado)) {
            return ['success' => false, 'message' => 'No se puede sincronizar una dinámica finalizada o cancelada.'];
        }

        $eval = $this->evaluarDinamica($dinamica);
        $elegibles = $eval['elegibles'];
        $evaluados = $eval['evaluados'];

        $this->db->transBegin();

        try {
            $existentesAuto = $this->participanteModel
                ->where('id_dinamica', $idDinamica)
                ->where('origen', 'automatico')
                ->findAll();

            $existentesPorCliente = [];
            foreach ($existentesAuto as $ex) {
                if (!empty($ex['id_cliente'])) {
                    $existentesPorCliente[(int) $ex['id_cliente']] = $ex;
                }
            }

            $siguienteControl = $this->participanteModel->obtenerSiguienteNumeroControl($idDinamica);
            $nuevos = 0;
            $actualizados = 0;
            $inactivados = 0;

            // 1. Insertar o actualizar clientes elegibles
            foreach ($elegibles as $idCliente => $datos) {
                $detalleJson = json_encode($datos['compras_detalle'], JSON_UNESCAPED_UNICODE);
                if (isset($existentesPorCliente[$idCliente])) {
                    $ex = $existentesPorCliente[$idCliente];
                    $updateData = [
                        'nombre_participante'  => $datos['nombre'],
                        'telefono'             => $datos['telefono'] ?: $ex['telefono'],
                        'importe_acumulado'    => $datos['importe_acumulado'],
                        'num_compras_validas'  => $datos['num_compras_validas'],
                        'num_participaciones'  => $datos['num_participaciones'],
                        'fecha_cumplimiento'   => $ex['fecha_cumplimiento'] ?: $datos['fecha_cumplimiento'],
                        'estado'               => 'elegible',
                        'motivo_estado'        => null,
                        'detalle_compras_json' => $detalleJson,
                    ];
                    $this->participanteModel->update((int) $ex['id'], $updateData);
                    $actualizados++;
                } else {
                    $this->participanteModel->insert([
                        'id_dinamica'          => $idDinamica,
                        'id_cliente'           => $idCliente,
                        'es_ocasional'         => 0,
                        'numero_control'       => $siguienteControl++,
                        'nombre_participante'  => $datos['nombre'],
                        'telefono'             => $datos['telefono'] ?: null,
                        'importe_acumulado'    => $datos['importe_acumulado'],
                        'num_compras_validas'  => $datos['num_compras_validas'],
                        'num_participaciones'  => $datos['num_participaciones'],
                        'fecha_cumplimiento'   => $datos['fecha_cumplimiento'],
                        'origen'               => 'automatico',
                        'estado'               => 'elegible',
                        'motivo_estado'        => null,
                        'notificado_whatsapp'  => 0,
                        'en_listado_definitivo'=> !empty($dinamica['listado_cerrado']) ? 1 : 0,
                        'detalle_compras_json' => $detalleJson,
                    ]);
                    $nuevos++;
                }
            }

            // 2. Marcar como no_elegible a quienes dejaron de cumplir el requisito (sin eliminar físicamente)
            foreach ($existentesPorCliente as $idCliente => $ex) {
                if (isset($elegibles[$idCliente])) {
                    continue;
                }

                $infoEval = $evaluados[$idCliente] ?? null;
                $nuevoAcumulado = $infoEval ? $infoEval['importe_acumulado'] : 0.00;
                $nuevoNumCompras = $infoEval ? $infoEval['num_compras_validas'] : 0;
                $detalleJson = $infoEval ? json_encode($infoEval['compras_detalle'], JSON_UNESCAPED_UNICODE) : json_encode([]);

                $motivo = in_array($idCliente, FidelidadCalculator::CLIENTES_EXCLUIDOS, true)
                    ? 'Cuenta interna excluida de dinámicas.'
                    : ($nuevoNumCompras === 0
                        ? 'Sin compras válidas vigentes en el periodo (compra cancelada, eliminada o fuera de periodo).'
                        : sprintf(
                            'Importe acumulado actual ($%s) inferior al mínimo requerido ($%s).',
                            number_format($nuevoAcumulado, 2),
                            number_format((float) $dinamica['importe_minimo'], 2)
                        ));

                if ($ex['estado'] !== 'no_elegible' || (float) $ex['importe_acumulado'] !== (float) $nuevoAcumulado) {
                    $this->participanteModel->update((int) $ex['id'], [
                        'importe_acumulado'    => $nuevoAcumulado,
                        'num_compras_validas'  => $nuevoNumCompras,
                        'num_participaciones'  => 0,
                        'estado'               => 'no_elegible',
                        'motivo_estado'        => $motivo,
                        'detalle_compras_json' => $detalleJson,
                    ]);
                    if ($ex['estado'] === 'elegible') {
                        $inactivados++;
                    }
                }
            }

            if ($registrarBitacora && ($nuevos > 0 || $inactivados > 0 || !empty($motivoForzado))) {
                $this->bitacoraModel->registrar([
                    'id_dinamica' => $idDinamica,
                    'accion'      => !empty($motivoForzado) ? 'sincronizacion_forzada' : 'sincronizacion',
                    'motivo'      => $motivoForzado,
                    'detalle'     => "Sincronización de compras: {$nuevos} nuevos, {$actualizados} actualizados, {$inactivados} marcados no elegibles.",
                ]);
            }

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                return ['success' => false, 'message' => 'Error al sincronizar participantes en la base de datos.'];
            }

            $this->db->transCommit();

            return [
                'success'              => true,
                'nuevos'               => $nuevos,
                'actualizados'         => $actualizados,
                'inactivados'          => $inactivados,
                'proximos'             => $eval['proximos'],
                'importe_total_valido' => $eval['importe_total_valido'],
                'message'              => "Sincronización completada: {$nuevos} nuevos, {$actualizados} actualizados, {$inactivados} no elegibles.",
            ];
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Error en sincronizarParticipantes: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno al sincronizar: ' . $e->getMessage()];
        }
    }

    /**
     * Evalúa dinámicas activas tras registrar/editar/eliminar una compra de un cliente.
     *
     * @return array<int, array<string, mixed>> Avisos informativos para mostrar tras la compra
     */
    public function evaluarCompraClienteEnDinamicasActivas(int $idCliente, ?string $fechaCompra = null): array
    {
        if ($idCliente <= 0 || in_array($idCliente, FidelidadCalculator::CLIENTES_EXCLUIDOS, true) || !$this->tablasDisponibles()) {
            return [];
        }

        $dinamicasActivas = $this->dinamicaModel
            ->where('estado', 'activa')
            ->where('listado_cerrado', 0)
            ->findAll();

        if (empty($dinamicasActivas)) {
            return [];
        }

        $avisos = [];
        foreach ($dinamicasActivas as $din) {
            if ($fechaCompra !== null && $fechaCompra !== '') {
                if ($fechaCompra < $din['fecha_inicio'] || $fechaCompra > $din['fecha_fin']) {
                    continue;
                }
            }

            $antes = $this->participanteModel
                ->where('id_dinamica', (int) $din['id'])
                ->where('id_cliente', $idCliente)
                ->where('estado', 'elegible')
                ->first();

            $sync = $this->sincronizarParticipantes((int) $din['id'], null, false);
            if (empty($sync['success'])) {
                continue;
            }

            $despues = $this->participanteModel
                ->where('id_dinamica', (int) $din['id'])
                ->where('id_cliente', $idCliente)
                ->where('estado', 'elegible')
                ->first();

            if ($despues) {
                if (!$antes) {
                    $avisos[] = [
                        'tipo'    => 'success',
                        'mensaje' => sprintf(
                            'El cliente ha alcanzado el importe mínimo de $%s y ya participa en %s.',
                            number_format((float) $din['importe_minimo'], 2),
                            $din['nombre']
                        ),
                    ];
                } else {
                    $avisos[] = [
                        'tipo'    => 'info',
                        'mensaje' => sprintf(
                            'El cliente ya se encuentra registrado en esta dinámica (%s).',
                            $din['nombre']
                        ),
                    ];
                }
            } else {
                foreach ($sync['proximos'] ?? [] as $prox) {
                    if ((int) $prox['id_cliente'] === $idCliente) {
                        $avisos[] = [
                            'tipo'    => 'warning',
                            'mensaje' => sprintf(
                                'El cliente acumula $%s en compras. Le faltan $%s para participar en %s.',
                                number_format((float) $prox['importe_acumulado'], 2),
                                number_format((float) $prox['importe_faltante'], 2),
                                $din['nombre']
                            ),
                        ];
                        break;
                    }
                }
            }
        }

        return $avisos;
    }

    /**
     * Obtiene el resumen de participación en dinámicas para la vista de Cuenta Corriente del cliente.
     */
    public function obtenerResumenClienteEnDinamicas(int $idCliente): array
    {
        if ($idCliente <= 0 || in_array($idCliente, FidelidadCalculator::CLIENTES_EXCLUIDOS, true) || !$this->tablasDisponibles()) {
            return [
                'activas'   => [],
                'historial' => [],
                'premios'   => [],
            ];
        }

        $dinamicasActivas = $this->dinamicaModel
            ->where('estado', 'activa')
            ->orderBy('fecha_fin', 'ASC')
            ->findAll();

        $activas = [];
        foreach ($dinamicasActivas as $din) {
            $part = $this->participanteModel
                ->where('id_dinamica', (int) $din['id'])
                ->where('id_cliente', $idCliente)
                ->orderBy('id', 'ASC')
                ->first();

            $eval = $this->evaluarDinamica($din);
            $evalCliente = $eval['evaluados'][$idCliente] ?? null;

            $acumulado = $part ? (float) $part['importe_acumulado'] : ($evalCliente ? (float) $evalCliente['importe_acumulado'] : 0.0);
            $importeMinimo = (float) $din['importe_minimo'];
            $faltante = max(0.0, round($importeMinimo - $acumulado, 2));
            $participaciones = ($part && $part['estado'] === 'elegible') ? (int) $part['num_participaciones'] : 0;

            $activas[] = [
                'id_dinamica'       => (int) $din['id'],
                'nombre'            => $din['nombre'],
                'fecha_sorteo'      => $din['fecha_sorteo'],
                'importe_minimo'    => $importeMinimo,
                'importe_acumulado' => $acumulado,
                'importe_faltante'  => $participaciones > 0 ? 0.0 : $faltante,
                'estado_cliente'    => $part ? $part['estado'] : ($acumulado > 0 ? 'proximo' : 'sin_compras'),
                'num_participaciones'=> $participaciones,
                'notificado'        => $part ? (int) $part['notificado_whatsapp'] : 0,
            ];
        }

        $historial = $this->db->table('t_dinamica_participantes p')
            ->select('p.*, d.nombre AS dinamica_nombre, d.estado AS dinamica_estado, d.fecha_inicio, d.fecha_fin, d.fecha_sorteo')
            ->join('t_dinamicas d', 'd.id = p.id_dinamica', 'inner')
            ->where('p.id_cliente', $idCliente)
            ->orderBy('d.fecha_inicio', 'DESC')
            ->get()
            ->getResultArray();

        $premios = $this->db->table('t_dinamica_ganadores g')
            ->select('g.*, pr.nombre_publico AS premio_nombre, pr.estado_entrega, d.nombre AS dinamica_nombre')
            ->join('t_dinamica_premios pr', 'pr.id = g.id_premio', 'inner')
            ->join('t_dinamicas d', 'd.id = g.id_dinamica', 'inner')
            ->where('g.id_cliente', $idCliente)
            ->orderBy('g.creado_en', 'DESC')
            ->get()
            ->getResultArray();

        return [
            'activas'   => $activas,
            'historial' => $historial,
            'premios'   => $premios,
        ];
    }
}
