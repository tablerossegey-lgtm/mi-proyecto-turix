<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\DinamicaCalculator;
use App\Libraries\DinamicaService;
use App\Libraries\FidelidadCalculator;
use App\Libraries\XlsxExporter;
use App\Models\ClienteModel;
use App\Models\DinamicaBitacoraModel;
use App\Models\DinamicaGanadorModel;
use App\Models\DinamicaModel;
use App\Models\DinamicaParticipanteModel;
use App\Models\DinamicaPremioModel;
use App\Models\ProductoModel;

class RifasDinamicas extends BaseController
{
    protected DinamicaModel $dinamicaModel;
    protected DinamicaParticipanteModel $participanteModel;
    protected DinamicaPremioModel $premioModel;
    protected DinamicaGanadorModel $ganadorModel;
    protected DinamicaBitacoraModel $bitacoraModel;
    protected ClienteModel $clienteModel;
    protected ProductoModel $productoModel;
    protected DinamicaCalculator $calculator;
    protected DinamicaService $service;

    public function __construct()
    {
        $this->dinamicaModel = new DinamicaModel();
        $this->participanteModel = new DinamicaParticipanteModel();
        $this->premioModel = new DinamicaPremioModel();
        $this->ganadorModel = new DinamicaGanadorModel();
        $this->bitacoraModel = new DinamicaBitacoraModel();
        $this->clienteModel = new ClienteModel();
        $this->productoModel = new ProductoModel();
        $this->calculator = new DinamicaCalculator();
        $this->service = new DinamicaService();
    }

    public function index()
    {
        $filtros = [
            'q'         => trim((string) $this->request->getVar('q')),
            'anio'      => trim((string) $this->request->getVar('anio')),
            'estado'    => trim((string) $this->request->getVar('estado')),
            'modalidad' => trim((string) $this->request->getVar('modalidad')),
        ];

        $indicadores = $this->dinamicaModel->obtenerIndicadoresPanel();
        $dinamicas = $this->dinamicaModel->obtenerListadoConConteos($filtros);
        $anios = $this->dinamicaModel->obtenerAniosDisponibles();

        return view('admin/dinamicas/index', [
            'titulo'            => 'Rifas y Dinámicas',
            'indicadores'       => $indicadores,
            'dinamicas'         => $dinamicas,
            'anios'             => $anios,
            'filtros'           => $filtros,
            'estados'           => DinamicaCalculator::ESTADOS_DINAMICA,
            'modalidades'       => DinamicaCalculator::MODALIDADES_SORTEO,
            'tiposRequisito'    => DinamicaCalculator::TIPOS_REQUISITO,
            'plantillaDefecto'  => $this->calculator->plantillaWhatsAppPorDefecto(),
        ]);
    }

    public function guardar()
    {
        $nombre = trim((string) $this->request->getPost('nombre'));
        $fechaInicio = trim((string) $this->request->getPost('fecha_inicio'));
        $fechaFin = trim((string) $this->request->getPost('fecha_fin'));

        if ($nombre === '' || $fechaInicio === '' || $fechaFin === '') {
            return redirect()->back()->withInput()->with('error', 'El nombre y las fechas de inicio y fin son obligatorios.');
        }

        if ($fechaInicio > $fechaFin) {
            return redirect()->back()->withInput()->with('error', 'La fecha de inicio no puede ser posterior a la fecha de fin.');
        }

        $imagenPromo = $this->subirImagenOpcional('imagen_promo', 'uploads/dinamicas');

        $maxPartRaw = $this->request->getPost('max_participaciones_cliente');
        $maxPart = ($maxPartRaw !== null && $maxPartRaw !== '') ? max(1, (int) $maxPartRaw) : null;

        $permitirMultiplesPremios = $this->request->getPost('permitir_multiples_premios') === '1';
        $maxPremios = $permitirMultiplesPremios ? null : max(1, (int) ($this->request->getPost('max_premios_cliente') ?: 1));

        $plantilla = trim((string) $this->request->getPost('plantilla_whatsapp'));
        if ($plantilla === '') {
            $plantilla = $this->calculator->plantillaWhatsAppPorDefecto();
        }

        $estado = trim((string) ($this->request->getPost('estado') ?: 'activa'));
        if (!array_key_exists($estado, DinamicaCalculator::ESTADOS_DINAMICA)) {
            $estado = 'activa';
        }

        $data = [
            'nombre'                        => $nombre,
            'descripcion'                   => trim((string) $this->request->getPost('descripcion')) ?: null,
            'imagen_promo'                  => $imagenPromo,
            'fecha_inicio'                  => $fechaInicio,
            'fecha_fin'                     => $fechaFin,
            'fecha_sorteo'                  => trim((string) $this->request->getPost('fecha_sorteo')) ?: null,
            'hora_sorteo'                   => trim((string) $this->request->getPost('hora_sorteo')) ?: null,
            'observaciones'                 => trim((string) $this->request->getPost('observaciones')) ?: null,
            'modalidad_sorteo'              => $this->request->getPost('modalidad_sorteo') ?: 'por_definir',
            'plataforma_digital'            => trim((string) $this->request->getPost('plataforma_digital')) ?: null,
            'url_plataforma'                => trim((string) $this->request->getPost('url_plataforma')) ?: null,
            'tipo_requisito'                => $this->request->getPost('tipo_requisito') ?: 'acumulado_unico',
            'importe_minimo'                => round(max(0.0, (float) $this->request->getPost('importe_minimo')), 2),
            'max_participaciones_cliente'   => $maxPart,
            'max_premios_cliente'           => $maxPremios,
            'permitir_clientes_ocasionales' => $this->request->getPost('permitir_clientes_ocasionales') === '1' ? 1 : 0,
            'permitir_registro_manual'      => $this->request->getPost('permitir_registro_manual') === '1' ? 1 : 0,
            'requiere_compra_liquidada'     => $this->request->getPost('requiere_compra_liquidada') === '1' ? 1 : 0,
            'plantilla_whatsapp'            => $plantilla,
            'estado'                        => $estado,
            'creado_por'                    => session()->get('id') ? (int) session()->get('id') : null,
        ];

        $id = $this->dinamicaModel->insert($data, true);
        if (!$id) {
            return redirect()->back()->withInput()->with('error', 'No se pudo registrar la dinámica.');
        }

        $this->bitacoraModel->registrar([
            'id_dinamica' => (int) $id,
            'accion'      => 'creacion',
            'detalle'     => "Dinámica '{$nombre}' creada con estado '{$estado}'.",
        ]);

        // Sincronizar automáticamente si se creó activa y requiere compras
        if ($estado === 'activa' && $data['tipo_requisito'] !== 'manual_sin_compra') {
            $this->service->sincronizarParticipantes((int) $id);
        }

        return redirect()->to(base_url('admin/dinamicas/detalle/' . $id))
            ->with('success', 'Dinámica creada correctamente.');
    }

    public function actualizar($id)
    {
        $dinamica = $this->dinamicaModel->find((int) $id);
        if (!$dinamica) {
            return redirect()->to(base_url('admin/dinamicas'))->with('error', 'Dinámica no encontrada.');
        }

        $nombre = trim((string) $this->request->getPost('nombre'));
        $fechaInicio = trim((string) $this->request->getPost('fecha_inicio'));
        $fechaFin = trim((string) $this->request->getPost('fecha_fin'));

        if ($nombre === '' || $fechaInicio === '' || $fechaFin === '') {
            return redirect()->back()->with('error', 'El nombre y las fechas de participación son obligatorios.');
        }

        $imagenPromo = $this->subirImagenOpcional('imagen_promo', 'uploads/dinamicas');
        $maxPartRaw = $this->request->getPost('max_participaciones_cliente');
        $maxPart = ($maxPartRaw !== null && $maxPartRaw !== '') ? max(1, (int) $maxPartRaw) : null;
        $permitirMultiplesPremios = $this->request->getPost('permitir_multiples_premios') === '1';
        $maxPremios = $permitirMultiplesPremios ? null : max(1, (int) ($this->request->getPost('max_premios_cliente') ?: 1));

        $update = [
            'nombre'                        => $nombre,
            'descripcion'                   => trim((string) $this->request->getPost('descripcion')) ?: null,
            'fecha_sorteo'                  => trim((string) $this->request->getPost('fecha_sorteo')) ?: null,
            'hora_sorteo'                   => trim((string) $this->request->getPost('hora_sorteo')) ?: null,
            'observaciones'                 => trim((string) $this->request->getPost('observaciones')) ?: null,
            'modalidad_sorteo'              => $this->request->getPost('modalidad_sorteo') ?: $dinamica['modalidad_sorteo'],
            'plataforma_digital'            => trim((string) $this->request->getPost('plataforma_digital')) ?: null,
            'url_plataforma'                => trim((string) $this->request->getPost('url_plataforma')) ?: null,
            'plantilla_whatsapp'            => trim((string) $this->request->getPost('plantilla_whatsapp')) ?: $dinamica['plantilla_whatsapp'],
            'permitir_clientes_ocasionales' => $this->request->getPost('permitir_clientes_ocasionales') === '1' ? 1 : 0,
            'permitir_registro_manual'      => $this->request->getPost('permitir_registro_manual') === '1' ? 1 : 0,
            'max_premios_cliente'           => $maxPremios,
        ];

        if ($imagenPromo !== null) {
            $update['imagen_promo'] = $imagenPromo;
        }

        // Si no está cerrada, también se pueden actualizar fechas y reglas de participación
        if (empty($dinamica['listado_cerrado'])) {
            $update['fecha_inicio'] = $fechaInicio;
            $update['fecha_fin'] = $fechaFin;
            $update['tipo_requisito'] = $this->request->getPost('tipo_requisito') ?: $dinamica['tipo_requisito'];
            $update['importe_minimo'] = round(max(0.0, (float) $this->request->getPost('importe_minimo')), 2);
            $update['max_participaciones_cliente'] = $maxPart;
            $update['requiere_compra_liquidada'] = $this->request->getPost('requiere_compra_liquidada') === '1' ? 1 : 0;
        }

        $this->dinamicaModel->update((int) $id, $update);

        $this->bitacoraModel->registrar([
            'id_dinamica' => (int) $id,
            'accion'      => 'edicion',
            'detalle'     => "Configuración de la dinámica '{$nombre}' actualizada.",
        ]);

        return redirect()->to(base_url('admin/dinamicas/detalle/' . $id))
            ->with('success', 'Dinámica actualizada correctamente.');
    }

    public function cambiarEstado($id)
    {
        $dinamica = $this->dinamicaModel->find((int) $id);
        if (!$dinamica) {
            return redirect()->to(base_url('admin/dinamicas'))->with('error', 'Dinámica no encontrada.');
        }

        $nuevoEstado = trim((string) $this->request->getPost('estado'));
        $resolucion = trim((string) $this->request->getPost('resolucion_cierre'));

        if (!array_key_exists($nuevoEstado, DinamicaCalculator::ESTADOS_DINAMICA)) {
            return redirect()->back()->with('error', 'Estado no válido.');
        }

        if ($nuevoEstado === 'finalizada') {
            $premiosPendientes = $this->premioModel
                ->where('id_dinamica', (int) $id)
                ->where('estado_entrega !=', 'entregado')
                ->countAllResults();

            if ($premiosPendientes > 0 && $resolucion === '') {
                return redirect()->back()->with('error', 'Existen premios sin entregar. Registra una resolución administrativa para finalizar.');
            }
        }

        $update = ['estado' => $nuevoEstado];
        if ($resolucion !== '') {
            $update['resolucion_cierre'] = $resolucion;
        }

        $this->dinamicaModel->update((int) $id, $update);

        $this->bitacoraModel->registrar([
            'id_dinamica' => (int) $id,
            'accion'      => 'cambio_estado',
            'motivo'      => $resolucion ?: null,
            'detalle'     => "Estado cambiado de '{$dinamica['estado']}' a '{$nuevoEstado}'.",
        ]);

        return redirect()->to(base_url('admin/dinamicas/detalle/' . $id))
            ->with('success', 'Estado actualizado correctamente.');
    }

    public function detalle($id)
    {
        $dinamica = $this->dinamicaModel->find((int) $id);
        if (!$dinamica) {
            return redirect()->to(base_url('admin/dinamicas'))->with('error', 'Dinámica no encontrada.');
        }

        $estadoParam = $this->request->getVar('estado');
        $filtrosPart = [
            'q'          => trim((string) $this->request->getVar('q')),
            'estado'     => $estadoParam !== null ? trim((string) $estadoParam) : 'elegible',
            'origen'     => trim((string) $this->request->getVar('origen')),
            'notificado' => $this->request->getVar('notificado') !== null ? (string) $this->request->getVar('notificado') : '',
        ];

        $todosParticipantes = $this->participanteModel->obtenerPorDinamica((int) $id);
        $participantesFiltrados = $this->participanteModel->obtenerPorDinamica((int) $id, $filtrosPart);
        $premios = $this->premioModel->obtenerPorDinamica((int) $id);
        $ganadores = $this->ganadorModel->obtenerPorDinamica((int) $id);
        $eval = $this->service->evaluarDinamica($dinamica);
        $bitacora = $this->bitacoraModel->obtenerPorDinamica((int) $id, 40);

        // Indicadores de detalle (8 tarjetas)
        $clientesParticipantes = 0;
        $totalParticipaciones = 0;
        $notificados = 0;
        $pendientesNotificar = 0;

        foreach ($todosParticipantes as &$p) {
            $p['mensaje_whatsapp'] = $this->calculator->generarMensajeWhatsApp($dinamica, $p);
            if ($p['estado'] === 'elegible') {
                $clientesParticipantes++;
                $totalParticipaciones += (int) $p['num_participaciones'];
                if (!empty($p['notificado_whatsapp'])) {
                    $notificados++;
                } else {
                    $pendientesNotificar++;
                }
            }
        }
        unset($p);

        foreach ($participantesFiltrados as &$pf) {
            $pf['mensaje_whatsapp'] = $this->calculator->generarMensajeWhatsApp($dinamica, $pf);
        }
        unset($pf);

        $premiosRegistrados = count($premios);
        $premiosEntregados = 0;
        foreach ($premios as $pr) {
            if ($pr['estado_entrega'] === 'entregado') {
                $premiosEntregados++;
            }
        }

        // Listados para AppSorteos (completo y restantes sin ganadores previos)
        $lineasSorteo = $this->calculator->generarListadoSorteo($todosParticipantes, $dinamica);

        $clientesGanadoresIds = [];
        $participantesGanadoresIds = [];
        foreach ($ganadores as $g) {
            if (!empty($g['id_cliente'])) {
                $clientesGanadoresIds[] = (int) $g['id_cliente'];
            }
            $participantesGanadoresIds[] = (int) $g['id_participante'];
        }
        $permiteMultiplesPremios = empty($dinamica['max_premios_cliente']);
        $lineasRestantes = $this->calculator->generarListadoSorteo(
            $todosParticipantes,
            $dinamica,
            $permiteMultiplesPremios ? [] : $clientesGanadoresIds,
            $participantesGanadoresIds
        );

        $clientes = $this->clienteModel
            ->whereNotIn('idCliente', FidelidadCalculator::CLIENTES_EXCLUIDOS)
            ->orderBy('nombre', 'ASC')
            ->findAll();
        $productos = $this->productoModel->orderBy('descripcion', 'ASC')->findAll();

        $tabActiva = trim((string) ($this->request->getVar('tab') ?: 'resumen'));

        return view('admin/dinamicas/detalle', [
            'titulo'                 => $dinamica['nombre'],
            'dinamica'               => $dinamica,
            'participantes'          => $participantesFiltrados,
            'todosParticipantes'     => $todosParticipantes,
            'proximos'               => $eval['proximos'],
            'premios'                => $premios,
            'ganadores'              => $ganadores,
            'bitacora'               => $bitacora,
            'clientes'               => $clientes,
            'productos'              => $productos,
            'filtrosPart'            => $filtrosPart,
            'tabActiva'              => $tabActiva,
            'lineasSorteo'           => $lineasSorteo,
            'lineasRestantes'        => $lineasRestantes,
            'estadosDinamica'        => DinamicaCalculator::ESTADOS_DINAMICA,
            'modalidades'            => DinamicaCalculator::MODALIDADES_SORTEO,
            'tiposRequisito'         => DinamicaCalculator::TIPOS_REQUISITO,
            'estadosEntrega'         => DinamicaCalculator::ESTADOS_ENTREGA,
            'indicadores'            => [
                'clientes_participantes' => $clientesParticipantes,
                'total_participaciones'  => $totalParticipaciones,
                'proximos_cumplir'       => count($eval['proximos']),
                'notificados'            => $notificados,
                'pendientes_notificar'   => $pendientesNotificar,
                'importe_total_valido'   => $eval['importe_total_valido'],
                'premios_registrados'    => $premiosRegistrados,
                'premios_entregados'     => $premiosEntregados,
            ],
        ]);
    }

    public function sincronizar($id)
    {
        $dinamica = $this->dinamicaModel->find((int) $id);
        if (!$dinamica) {
            return redirect()->to(base_url('admin/dinamicas'))->with('error', 'Dinámica no encontrada.');
        }

        $motivoForzado = trim((string) $this->request->getPost('motivo'));
        if (!empty($dinamica['listado_cerrado']) && $motivoForzado === '') {
            return redirect()->to(base_url('admin/dinamicas/detalle/' . $id . '?tab=participantes'))
                ->with('error', 'El listado ya está cerrado. Debes indicar un motivo para realizar una corrección.');
        }

        $res = $this->service->sincronizarParticipantes((int) $id, $motivoForzado ?: null);
        $tab = $this->request->getPost('tab') ?: 'participantes';

        return redirect()->to(base_url('admin/dinamicas/detalle/' . $id . '?tab=' . $tab))
            ->with($res['success'] ? 'success' : 'error', $res['message']);
    }

    public function registrarParticipanteManual($id)
    {
        $dinamica = $this->dinamicaModel->find((int) $id);
        if (!$dinamica) {
            return redirect()->to(base_url('admin/dinamicas'))->with('error', 'Dinámica no encontrada.');
        }

        if (empty($dinamica['permitir_registro_manual'])) {
            return redirect()->back()->with('error', 'Esta dinámica no permite registro manual de participantes.');
        }

        $motivoCierre = trim((string) $this->request->getPost('motivo_cierre'));
        if (!empty($dinamica['listado_cerrado']) && $motivoCierre === '') {
            return redirect()->back()->with('error', 'El listado está cerrado. Debes especificar el motivo en bitácora.');
        }

        $tipoClienteManual = $this->request->getPost('tipo_participante') ?: 'existente';
        $idCliente = $tipoClienteManual === 'existente' ? (int) $this->request->getPost('id_cliente') : null;
        $esOcasional = $tipoClienteManual === 'ocasional' ? 1 : 0;

        if ($esOcasional && empty($dinamica['permitir_clientes_ocasionales'])) {
            return redirect()->back()->with('error', 'Esta dinámica no permite participantes ocasionales.');
        }

        $nombre = trim((string) $this->request->getPost('nombre_participante'));
        $telefono = trim((string) $this->request->getPost('telefono')) ?: null;

        if ($idCliente) {
            if (in_array($idCliente, FidelidadCalculator::CLIENTES_EXCLUIDOS, true)) {
                return redirect()->back()->with('error', 'Este cliente corresponde a una cuenta interna excluida de dinámicas.');
            }
            $cliente = $this->clienteModel->find($idCliente);
            if (!$cliente) {
                return redirect()->back()->with('error', 'El cliente seleccionado no existe.');
            }
            if ($nombre === '') {
                $nombre = $cliente['nombre'];
            }
            if ($telefono === null && !empty($cliente['cel'])) {
                $telefono = $cliente['cel'];
            }

            // Validar duplicado para cliente existente
            $yaExiste = $this->participanteModel
                ->where('id_dinamica', (int) $id)
                ->where('id_cliente', $idCliente)
                ->where('estado', 'elegible')
                ->first();
            if ($yaExiste) {
                return redirect()->back()->with('error', 'Este cliente ya está registrado como participante elegible en esta dinámica.');
            }
        } elseif ($nombre === '') {
            return redirect()->back()->with('error', 'El nombre del participante es obligatorio.');
        } else {
            // Validar duplicado por nombre para ocasional
            $yaExisteOc = $this->participanteModel
                ->where('id_dinamica', (int) $id)
                ->where('es_ocasional', 1)
                ->where('LOWER(nombre_participante)', mb_strtolower($nombre, 'UTF-8'))
                ->first();
            if ($yaExisteOc) {
                return redirect()->back()->with('error', 'Ya existe un participante ocasional con ese nombre en esta dinámica.');
            }
        }

        $referencia = trim((string) $this->request->getPost('referencia_compra')) ?: null;
        if ($referencia !== null) {
            $refExiste = $this->participanteModel
                ->where('id_dinamica', (int) $id)
                ->where('referencia_compra', $referencia)
                ->first();
            if ($refExiste) {
                return redirect()->back()->with('error', 'Ya existe una participación manual con esa referencia de compra.');
            }
        }

        $numParticipaciones = max(1, (int) ($this->request->getPost('num_participaciones') ?: 1));
        if (!empty($dinamica['max_participaciones_cliente'])) {
            $numParticipaciones = min($numParticipaciones, (int) $dinamica['max_participaciones_cliente']);
        }

        $importe = round(max(0.0, (float) $this->request->getPost('importe_acumulado')), 2);
        $fecha = trim((string) $this->request->getPost('fecha_cumplimiento')) ?: date('Y-m-d');
        $motivoRegistro = trim((string) $this->request->getPost('motivo_registro'));

        $siguienteControl = $this->participanteModel->obtenerSiguienteNumeroControl((int) $id);

        $idPart = $this->participanteModel->insert([
            'id_dinamica'          => (int) $id,
            'id_cliente'           => $idCliente ?: null,
            'es_ocasional'         => $esOcasional,
            'numero_control'       => $siguienteControl,
            'nombre_participante'  => $nombre,
            'telefono'             => $telefono,
            'importe_acumulado'    => $importe,
            'num_compras_validas'  => $importe > 0 ? 1 : 0,
            'num_participaciones'  => $numParticipaciones,
            'fecha_cumplimiento'   => $fecha,
            'origen'               => 'manual',
            'estado'               => 'elegible',
            'referencia_compra'    => $referencia,
            'motivo_registro'      => $motivoRegistro ?: 'Registro manual administrativo',
            'observaciones'        => trim((string) $this->request->getPost('observaciones')) ?: null,
            'en_listado_definitivo'=> !empty($dinamica['listado_cerrado']) ? 1 : 0,
        ], true);

        $this->bitacoraModel->registrar([
            'id_dinamica'     => (int) $id,
            'id_participante' => (int) $idPart,
            'accion'          => 'registro_manual',
            'motivo'          => $motivoCierre ?: $motivoRegistro,
            'detalle'         => "Participante manual '{$nombre}' (#{$siguienteControl}) registrado con {$numParticipaciones} participación(es).",
        ]);

        return redirect()->to(base_url('admin/dinamicas/detalle/' . $id . '?tab=participantes'))
            ->with('success', 'Participante manual registrado correctamente.');
    }

    public function cambiarEstadoParticipante($idDinamica, $idParticipante)
    {
        $dinamica = $this->dinamicaModel->find((int) $idDinamica);
        $part = $this->participanteModel->where('id_dinamica', (int) $idDinamica)->find((int) $idParticipante);

        if (!$dinamica || !$part) {
            return redirect()->back()->with('error', 'Participante no encontrado.');
        }

        $nuevoEstado = $this->request->getPost('estado') === 'elegible' ? 'elegible' : 'no_elegible';
        $motivo = trim((string) $this->request->getPost('motivo'));

        if (!empty($dinamica['listado_cerrado']) && $motivo === '') {
            return redirect()->back()->with('error', 'El listado está cerrado. Debes registrar el motivo de la corrección.');
        }

        $this->participanteModel->update((int) $idParticipante, [
            'estado'                => $nuevoEstado,
            'motivo_estado'         => $motivo ?: ($nuevoEstado === 'no_elegible' ? 'Marcado manualmente como no elegible' : null),
            'en_listado_definitivo' => (!empty($dinamica['listado_cerrado']) && $nuevoEstado === 'elegible') ? 1 : 0,
        ]);

        $this->bitacoraModel->registrar([
            'id_dinamica'     => (int) $idDinamica,
            'id_participante' => (int) $idParticipante,
            'accion'          => 'cambio_estado_participante',
            'motivo'          => $motivo ?: null,
            'detalle'         => "Participante '{$part['nombre_participante']}' cambiado a '{$nuevoEstado}'.",
        ]);

        return redirect()->to(base_url('admin/dinamicas/detalle/' . $idDinamica . '?tab=participantes'))
            ->with('success', 'Estado del participante actualizado.');
    }

    public function marcarNotificado($idDinamica, $idParticipante)
    {
        $part = $this->participanteModel->where('id_dinamica', (int) $idDinamica)->find((int) $idParticipante);
        if (!$part) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Participante no encontrado.']);
        }

        $ahora = date('Y-m-d H:i:s');
        $usuario = session()->get('nombre_completo') ?: session()->get('nombre_usuario') ?: 'Administrador';

        $this->participanteModel->update((int) $idParticipante, [
            'notificado_whatsapp'   => 1,
            'fecha_notificacion'    => $ahora,
            'notificado_por_nombre' => $usuario,
        ]);

        $this->bitacoraModel->registrar([
            'id_dinamica'     => (int) $idDinamica,
            'id_participante' => (int) $idParticipante,
            'accion'          => 'notificacion_whatsapp',
            'detalle'         => "Notificación por WhatsApp confirmada para '{$part['nombre_participante']}'.",
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'            => true,
                'fecha_notificacion' => date('d/m/Y H:i', strtotime($ahora)),
                'message'            => 'Participante marcado como notificado.',
            ]);
        }

        return redirect()->to(base_url('admin/dinamicas/detalle/' . $idDinamica . '?tab=participantes'))
            ->with('success', 'Participante marcado como notificado.');
    }

    public function cerrarParticipaciones($id)
    {
        $dinamica = $this->dinamicaModel->find((int) $id);
        if (!$dinamica) {
            return redirect()->to(base_url('admin/dinamicas'))->with('error', 'Dinámica no encontrada.');
        }

        if (!empty($dinamica['listado_cerrado'])) {
            return redirect()->back()->with('error', 'El listado de participaciones ya se encuentra cerrado.');
        }

        // Última validación de compras antes de cerrar
        if ($dinamica['tipo_requisito'] !== 'manual_sin_compra') {
            $this->service->sincronizarParticipantes((int) $id, null, false);
        }

        $ahora = date('Y-m-d H:i:s');
        $usuarioId = session()->get('id') ? (int) session()->get('id') : null;
        $usuarioNombre = session()->get('nombre_completo') ?: session()->get('nombre_usuario') ?: 'Administrador';

        // Marcar listado definitivo
        $this->participanteModel
            ->where('id_dinamica', (int) $id)
            ->where('estado', 'elegible')
            ->set(['en_listado_definitivo' => 1])
            ->update();

        $this->dinamicaModel->update((int) $id, [
            'estado'             => 'participaciones_cerradas',
            'listado_cerrado'    => 1,
            'cerrado_en'         => $ahora,
            'cerrado_por_id'     => $usuarioId,
            'cerrado_por_nombre' => $usuarioNombre,
        ]);

        $totalDefinitivos = $this->participanteModel
            ->where('id_dinamica', (int) $id)
            ->where('estado', 'elegible')
            ->countAllResults();

        $this->bitacoraModel->registrar([
            'id_dinamica' => (int) $id,
            'accion'      => 'cierre_participaciones',
            'detalle'     => "Listado definitivo cerrado por {$usuarioNombre} con {$totalDefinitivos} participante(s) elegible(s).",
        ]);

        return redirect()->to(base_url('admin/dinamicas/detalle/' . $id . '?tab=participantes'))
            ->with('success', 'Participaciones cerradas oficialmente. Listado definitivo guardado.');
    }

    public function guardarPremio($id)
    {
        $dinamica = $this->dinamicaModel->find((int) $id);
        if (!$dinamica) {
            return redirect()->to(base_url('admin/dinamicas'))->with('error', 'Dinámica no encontrada.');
        }

        $idPremio = (int) $this->request->getPost('id_premio');
        $nombrePublico = trim((string) $this->request->getPost('nombre_publico'));
        if ($nombrePublico === '') {
            return redirect()->back()->with('error', 'El nombre público del premio es obligatorio.');
        }

        $imagen = $this->subirImagenOpcional('imagen', 'uploads/dinamicas/premios');
        $idProducto = (int) $this->request->getPost('id_producto');
        $valorRaw = $this->request->getPost('valor_estimado');

        $data = [
            'id_dinamica'       => (int) $id,
            'orden'             => max(1, (int) ($this->request->getPost('orden') ?: 1)),
            'nombre_publico'    => $nombrePublico,
            'descripcion_admin' => trim((string) $this->request->getPost('descripcion_admin')) ?: null,
            'id_producto'       => $idProducto > 0 ? $idProducto : null,
            'valor_estimado'    => ($valorRaw !== null && $valorRaw !== '') ? round((float) $valorRaw, 2) : null,
            'observaciones'     => trim((string) $this->request->getPost('observaciones')) ?: null,
        ];

        if ($imagen !== null) {
            $data['imagen'] = $imagen;
        }

        if ($idPremio > 0) {
            $this->premioModel->update($idPremio, $data);
            $accion = 'edicion_premio';
            $msg = 'Premio actualizado correctamente.';
        } else {
            $data['estado_entrega'] = 'pendiente_asignar';
            $data['inventario_descontado'] = 0;
            $idPremio = (int) $this->premioModel->insert($data, true);
            $accion = 'creacion_premio';
            $msg = 'Premio agregado correctamente (sin descontar inventario aún).';
        }

        $this->bitacoraModel->registrar([
            'id_dinamica' => (int) $id,
            'id_premio'   => $idPremio,
            'accion'      => $accion,
            'detalle'     => "Premio #{$data['orden']}: {$nombrePublico}.",
        ]);

        return redirect()->to(base_url('admin/dinamicas/detalle/' . $id . '?tab=premios'))
            ->with('success', $msg);
    }

    public function eliminarPremio($idDinamica, $idPremio)
    {
        $premio = $this->premioModel->where('id_dinamica', (int) $idDinamica)->find((int) $idPremio);
        if (!$premio) {
            return redirect()->back()->with('error', 'Premio no encontrado.');
        }

        $ganador = $this->ganadorModel->where('id_premio', (int) $idPremio)->first();
        if ($ganador) {
            return redirect()->back()->with('error', 'No se puede eliminar un premio que ya tiene un ganador asignado.');
        }

        $this->premioModel->delete((int) $idPremio);

        $this->bitacoraModel->registrar([
            'id_dinamica' => (int) $idDinamica,
            'accion'      => 'eliminacion_premio',
            'detalle'     => "Premio '{$premio['nombre_publico']}' eliminado.",
        ]);

        return redirect()->to(base_url('admin/dinamicas/detalle/' . $idDinamica . '?tab=premios'))
            ->with('success', 'Premio eliminado correctamente.');
    }

    public function registrarGanador($id)
    {
        $dinamica = $this->dinamicaModel->find((int) $id);
        if (!$dinamica) {
            return redirect()->to(base_url('admin/dinamicas'))->with('error', 'Dinámica no encontrada.');
        }

        $idPremio = (int) $this->request->getPost('id_premio');
        $idParticipante = (int) $this->request->getPost('id_participante');

        $premio = $this->premioModel->where('id_dinamica', (int) $id)->find($idPremio);
        $part = $this->participanteModel->where('id_dinamica', (int) $id)->find($idParticipante);

        if (!$premio || !$part) {
            return redirect()->back()->with('error', 'Premio o participante no válido.');
        }

        if ($part['estado'] !== 'elegible') {
            return redirect()->back()->with('error', 'Solo puedes asignar ganadores de la lista de participantes elegibles.');
        }

        if (!empty($dinamica['listado_cerrado']) && empty($part['en_listado_definitivo'])) {
            return redirect()->back()->with('error', 'El participante no forma parte del listado definitivo cerrado.');
        }

        $premioYaAsignado = $this->ganadorModel->where('id_premio', $idPremio)->first();
        if ($premioYaAsignado) {
            return redirect()->back()->with('error', 'Este premio ya tiene un ganador registrado.');
        }

        // Validar máximo de premios por cliente
        $maxPremios = $dinamica['max_premios_cliente'] !== null ? (int) $dinamica['max_premios_cliente'] : null;
        if ($maxPremios !== null) {
            $ganadosPorEste = 0;
            if (!empty($part['id_cliente'])) {
                $ganadosPorEste = $this->ganadorModel
                    ->where('id_dinamica', (int) $id)
                    ->where('id_cliente', (int) $part['id_cliente'])
                    ->countAllResults();
            } else {
                $ganadosPorEste = $this->ganadorModel
                    ->where('id_dinamica', (int) $id)
                    ->where('id_participante', $idParticipante)
                    ->countAllResults();
            }
            if ($ganadosPorEste >= $maxPremios) {
                return redirect()->back()->with('error', "Este cliente ya alcanzó el límite de {$maxPremios} premio(s) permitido(s) en esta dinámica.");
            }
        }

        $evidencia = $this->subirImagenOpcional('evidencia_sorteo', 'uploads/dinamicas/evidencias');
        $fechaHora = trim((string) $this->request->getPost('fecha_hora_sorteo')) ?: date('Y-m-d H:i:s');
        $fechaHora = str_replace('T', ' ', $fechaHora);
        if (strlen($fechaHora) === 16) {
            $fechaHora .= ':00';
        }

        $this->ganadorModel->insert([
            'id_dinamica'          => (int) $id,
            'id_premio'            => $idPremio,
            'id_participante'      => $idParticipante,
            'id_cliente'           => $part['id_cliente'] ?: null,
            'nombre_ganador'       => $part['nombre_participante'],
            'numero_participacion' => max(1, (int) ($this->request->getPost('numero_participacion') ?: 1)),
            'fecha_hora_sorteo'    => $fechaHora,
            'modalidad_sorteo'     => $this->request->getPost('modalidad_sorteo') ?: $dinamica['modalidad_sorteo'],
            'enlace_resultado'     => trim((string) $this->request->getPost('enlace_resultado')) ?: null,
            'evidencia_sorteo'     => $evidencia,
            'observaciones'        => trim((string) $this->request->getPost('observaciones')) ?: null,
            'usuario_responsable'  => session()->get('nombre_completo') ?: session()->get('nombre_usuario') ?: 'Administrador',
        ]);

        $this->premioModel->update($idPremio, ['estado_entrega' => 'ganador_registrado']);

        // Actualizar estado de la dinámica a sorteo_realizado si estaba activa o cerrada
        if (in_array($dinamica['estado'], ['activa', 'participaciones_cerradas'], true)) {
            $this->dinamicaModel->update((int) $id, ['estado' => 'sorteo_realizado']);
        }

        $this->bitacoraModel->registrar([
            'id_dinamica'     => (int) $id,
            'id_participante' => $idParticipante,
            'id_premio'       => $idPremio,
            'accion'          => 'registro_ganador',
            'detalle'         => "Ganador registrado: '{$part['nombre_participante']}' para premio '{$premio['nombre_publico']}'.",
        ]);

        return redirect()->to(base_url('admin/dinamicas/detalle/' . $id . '?tab=ganadores'))
            ->with('success', "Ganador '{$part['nombre_participante']}' registrado para el premio #{$premio['orden']}.");
    }

    public function actualizarEntregaPremio($idDinamica, $idGanador)
    {
        $dinamica = $this->dinamicaModel->find((int) $idDinamica);
        $ganador = $this->ganadorModel->where('id_dinamica', (int) $idDinamica)->find((int) $idGanador);

        if (!$dinamica || !$ganador) {
            return redirect()->back()->with('error', 'Registro de ganador no encontrado.');
        }

        $premio = $this->premioModel->find((int) $ganador['id_premio']);
        if (!$premio) {
            return redirect()->back()->with('error', 'Premio asociado no encontrado.');
        }

        $estadoEntrega = trim((string) $this->request->getPost('estado_entrega'));
        if (!array_key_exists($estadoEntrega, DinamicaCalculator::ESTADOS_ENTREGA)) {
            return redirect()->back()->with('error', 'Estado de entrega no válido.');
        }

        $evidenciaEntrega = $this->subirImagenOpcional('evidencia_entrega', 'uploads/dinamicas/entregas');
        $usuario = session()->get('nombre_completo') ?: session()->get('nombre_usuario') ?: 'Administrador';
        $ahora = date('Y-m-d H:i:s');

        $db = \Config\Database::connect();
        $db->transBegin();

        $updateGanador = [
            'usuario_responsable'   => $usuario,
            'observaciones_entrega' => trim((string) $this->request->getPost('observaciones_entrega')) ?: $ganador['observaciones_entrega'],
        ];

        if ($estadoEntrega === 'ganador_notificado' && empty($ganador['fecha_notificacion'])) {
            $updateGanador['fecha_notificacion'] = $ahora;
        }
        if ($estadoEntrega === 'entregado' && empty($ganador['fecha_entrega'])) {
            $updateGanador['fecha_entrega'] = $ahora;
        }
        if ($evidenciaEntrega !== null) {
            $updateGanador['evidencia_entrega'] = $evidenciaEntrega;
        }

        $this->ganadorModel->update((int) $idGanador, $updateGanador);

        $updatePremio = ['estado_entrega' => $estadoEntrega];

        // Descontar inventario al entregar si el premio tiene producto asociado y no se ha descontado
        if ($estadoEntrega === 'entregado' && !empty($premio['id_producto']) && empty($premio['inventario_descontado'])) {
            $producto = $this->productoModel->find((int) $premio['id_producto']);
            if ($producto) {
                $nuevoStock = max(0, (int) $producto['stock'] - 1);
                $this->productoModel->update((int) $premio['id_producto'], ['stock' => $nuevoStock]);
                $updatePremio['inventario_descontado'] = 1;
            }
        }

        $this->premioModel->update((int) $premio['id'], $updatePremio);

        $this->bitacoraModel->registrar([
            'id_dinamica'     => (int) $idDinamica,
            'id_participante' => (int) $ganador['id_participante'],
            'id_premio'       => (int) $premio['id'],
            'accion'          => 'entrega_premio',
            'detalle'         => "Estado de entrega de '{$premio['nombre_publico']}' actualizado a '{$estadoEntrega}'.",
        ]);

        if ($db->transStatus() === false) {
            $db->transRollback();
            return redirect()->back()->with('error', 'No se pudo actualizar el estado de entrega.');
        }

        $db->transCommit();

        return redirect()->to(base_url('admin/dinamicas/detalle/' . $idDinamica . '?tab=ganadores'))
            ->with('success', 'Estado de entrega actualizado correctamente.');
    }

    public function imprimir($id)
    {
        $dinamica = $this->dinamicaModel->find((int) $id);
        if (!$dinamica) {
            return redirect()->to(base_url('admin/dinamicas'))->with('error', 'Dinámica no encontrada.');
        }

        $participantes = $this->participanteModel->obtenerPorDinamica((int) $id, ['estado' => 'elegible']);
        $lineas = $this->calculator->generarListadoSorteo($participantes, $dinamica);

        return view('admin/dinamicas/imprimir', [
            'dinamica' => $dinamica,
            'lineas'   => $lineas,
        ]);
    }

    public function exportar($id)
    {
        $dinamica = $this->dinamicaModel->find((int) $id);
        if (!$dinamica) {
            return redirect()->to(base_url('admin/dinamicas'))->with('error', 'Dinámica no encontrada.');
        }

        $participantes = $this->participanteModel->obtenerPorDinamica((int) $id);

        $rows = [
            [['v' => 'TurixShop - Listado Administrativo de Participantes', 's' => 1]],
            ['Dinámica:', $dinamica['nombre'], 'Estado:', DinamicaCalculator::ESTADOS_DINAMICA[$dinamica['estado']] ?? $dinamica['estado']],
            ['Periodo:', date('d/m/Y', strtotime($dinamica['fecha_inicio'])) . ' al ' . date('d/m/Y', strtotime($dinamica['fecha_fin'])), 'Generado:', date('d/m/Y H:i')],
            [],
            [
                ['v' => 'No. Control', 's' => 2],
                ['v' => 'Nombre del Participante', 's' => 2],
                ['v' => 'Teléfono', 's' => 2],
                ['v' => 'Importe Acumulado ($)', 's' => 2],
                ['v' => 'Compras Válidas', 's' => 2],
                ['v' => 'Participaciones', 's' => 2],
                ['v' => 'Fecha Cumplimiento', 's' => 2],
                ['v' => 'Origen', 's' => 2],
                ['v' => 'Estado', 's' => 2],
                ['v' => 'Notificado WhatsApp', 's' => 2],
                ['v' => 'Fecha Notificación', 's' => 2],
            ],
        ];

        foreach ($participantes as $p) {
            $rows[] = [
                (int) $p['numero_control'],
                (string) $p['nombre_participante'],
                (string) ($p['telefono'] ?: '-'),
                ['v' => (float) $p['importe_acumulado'], 's' => 3],
                (int) $p['num_compras_validas'],
                (int) $p['num_participaciones'],
                $p['fecha_cumplimiento'] ? date('d/m/Y', strtotime($p['fecha_cumplimiento'])) : '-',
                ucfirst((string) $p['origen']),
                $p['estado'] === 'elegible' ? 'Elegible' : 'No elegible',
                !empty($p['notificado_whatsapp']) ? 'Sí' : 'No',
                $p['fecha_notificacion'] ? date('d/m/Y H:i', strtotime($p['fecha_notificacion'])) : '-',
            ];
        }

        $xlsx = (new XlsxExporter())->generate($rows, 'Participantes');
        $slug = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $dinamica['nombre']);
        $filename = 'participantes_' . strtolower(trim($slug, '_')) . '.xlsx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($xlsx);
    }

    private function subirImagenOpcional(string $fieldName, string $subDir): ?string
    {
        $file = $this->request->getFile($fieldName);
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $uploadPath = FCPATH . $subDir;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);

        return $subDir . '/' . $newName;
    }
}
