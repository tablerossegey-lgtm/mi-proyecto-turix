<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\CuponCalculator;
use App\Models\ClienteModel;
use App\Models\CuponBitacoraModel;
use App\Models\CuponModel;
use App\Models\CuponUsoModel;

class Cupones extends BaseController
{
    protected CuponModel $cuponModel;
    protected CuponUsoModel $cuponUsoModel;
    protected CuponBitacoraModel $bitacoraModel;
    protected ClienteModel $clienteModel;
    protected CuponCalculator $calculator;

    public function __construct()
    {
        $this->cuponModel    = new CuponModel();
        $this->cuponUsoModel = new CuponUsoModel();
        $this->bitacoraModel = new CuponBitacoraModel();
        $this->clienteModel  = new ClienteModel();
        $this->calculator    = new CuponCalculator();
    }

    /**
     * Genera un código único de cupón que no exista en base de datos.
     */
    public function generarCodigo()
    {
        $prefijo = (string)($this->request->getVar('prefijo') ?: 'CUP');
        $codigo  = '';

        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('t_cupones')) {
                for ($i = 0; $i < 15; $i++) {
                    $candidato = $this->calculator->generarCodigoAleatorio($prefijo);
                    if (!$this->cuponModel->existeCodigo($candidato)) {
                        $codigo = $candidato;
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Error verificando código de cupón: ' . $e->getMessage());
        }

        if ($codigo === '') {
            $codigo = $this->calculator->generarCodigoAleatorio($prefijo);
        }

        return $this->response->setJSON([
            'success' => true,
            'codigo'  => $codigo,
        ]);
    }

    /**
     * Devuelve en JSON los cupones disponibles del cliente para el modal de compra.
     */
    public function disponibles($idCliente)
    {
        $idCliente = (int)$idCliente;
        $cupones   = $this->cuponModel->obtenerPorCliente($idCliente);
        $hoy       = date('Y-m-d');
        $activos   = [];

        foreach ($cupones as $c) {
            $estado = $this->calculator->resolverEstado($c, $hoy);
            if ($estado === CuponCalculator::ESTADO_DISPONIBLE) {
                $c['estado_resuelto']  = $estado;
                $c['usos_disponibles'] = max(0, (int)$c['limite_usos'] - (int)$c['usos_realizados']);
                $activos[]             = $c;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'cupones' => $activos,
        ]);
    }

    /**
     * Registra un nuevo cupón para un cliente.
     */
    public function crear()
    {
        $data = $this->request->getJSON(true);
        if (!is_array($data)) {
            $data = $this->request->getPost();
        }

        $idCliente = (int)($data['id_cliente'] ?? 0);
        $cliente   = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Cliente no encontrado.',
            ]);
        }

        $validation = $this->validarDatosCupon($data);
        if (!$validation['valid']) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => $validation['message'],
            ]);
        }

        $payload = $validation['payload'];
        $payload['id_cliente']      = $idCliente;
        $payload['usos_realizados'] = 0;
        $payload['creado_por']      = session()->get('id') ? (int)session()->get('id') : null;
        $payload['estado']          = $this->calculator->resolverEstado($payload);

        $db = \Config\Database::connect();
        $db->transStart();

        $this->cuponModel->insert($payload);
        $idCupon = (int)$this->cuponModel->getInsertID();

        $this->bitacoraModel->registrar([
            'id_cupon'        => $idCupon,
            'id_cliente'      => $idCliente,
            'accion'          => 'creacion',
            'monto_descuento' => $payload['tipo_descuento'] === 'fijo' ? $payload['valor'] : null,
            'detalle'         => sprintf(
                'Cupón %s (%s) creado. Tipo: %s, Valor: %s, Vigencia: %s a %s.',
                $payload['codigo'],
                $payload['concepto'],
                $payload['tipo_descuento'],
                $payload['valor'],
                $payload['fecha_inicio'],
                $payload['fecha_vencimiento'] ?: 'Sin vencimiento'
            ),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'No se pudo guardar el cupón en la base de datos.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'id'      => $idCupon,
            'message' => 'Cupón ' . $payload['codigo'] . ' registrado correctamente.',
        ]);
    }

    /**
     * Edita un cupón existente (solo si no ha sido utilizado).
     */
    public function editar($idCupon)
    {
        $idCupon = (int)$idCupon;
        $cupon   = $this->cuponModel->find($idCupon);
        if (!$cupon) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Cupón no encontrado.',
            ]);
        }

        if ((int)$cupon['usos_realizados'] > 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'No se puede editar un cupón que ya ha sido utilizado.',
            ]);
        }

        if ($cupon['estado'] === CuponCalculator::ESTADO_CANCELADO) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'No se puede editar un cupón cancelado.',
            ]);
        }

        $data = $this->request->getJSON(true);
        if (!is_array($data)) {
            $data = $this->request->getPost();
        }

        $validation = $this->validarDatosCupon($data, $idCupon);
        if (!$validation['valid']) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => $validation['message'],
            ]);
        }

        $payload = $validation['payload'];
        $payload['usos_realizados'] = (int)$cupon['usos_realizados'];
        if (($data['estado'] ?? '') === CuponCalculator::ESTADO_CANCELADO) {
            $payload['estado']       = CuponCalculator::ESTADO_CANCELADO;
            $payload['cancelado_en'] = date('Y-m-d H:i:s');
            $payload['cancelado_por'] = session()->get('id') ? (int)session()->get('id') : null;
        } else {
            $payload['estado'] = $this->calculator->resolverEstado($payload);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->cuponModel->update($idCupon, $payload);

        $this->bitacoraModel->registrar([
            'id_cupon'        => $idCupon,
            'id_cliente'      => (int)$cupon['id_cliente'],
            'accion'          => 'edicion',
            'monto_descuento' => $payload['tipo_descuento'] === 'fijo' ? $payload['valor'] : null,
            'detalle'         => sprintf(
                'Cupón %s editado. Concepto: %s, Tipo: %s, Valor: %s, Límite usos: %d.',
                $payload['codigo'],
                $payload['concepto'],
                $payload['tipo_descuento'],
                $payload['valor'],
                $payload['limite_usos']
            ),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al actualizar el cupón.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Cupón actualizado con éxito.',
        ]);
    }

    /**
     * Cancela un cupón sin eliminarlo físicamente.
     */
    public function cancelar($idCupon)
    {
        $idCupon = (int)$idCupon;
        $cupon   = $this->cuponModel->find($idCupon);
        if (!$cupon) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Cupón no encontrado.',
            ]);
        }

        if ($cupon['estado'] === CuponCalculator::ESTADO_CANCELADO) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Este cupón ya se encuentra cancelado.',
            ]);
        }

        $data   = $this->request->getJSON(true);
        $motivo = is_array($data) ? trim((string)($data['motivo'] ?? '')) : trim((string)$this->request->getPost('motivo'));
        if ($motivo === '') {
            $motivo = 'Cancelado por el administrador';
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->cuponModel->update($idCupon, [
            'estado'             => CuponCalculator::ESTADO_CANCELADO,
            'cancelado_en'       => date('Y-m-d H:i:s'),
            'cancelado_por'      => session()->get('id') ? (int)session()->get('id') : null,
            'motivo_cancelacion' => $motivo,
        ]);

        $this->bitacoraModel->registrar([
            'id_cupon'   => $idCupon,
            'id_cliente' => (int)$cupon['id_cliente'],
            'accion'     => 'cancelacion',
            'detalle'    => 'Cupón ' . $cupon['codigo'] . ' cancelado. Motivo: ' . $motivo,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'No se pudo cancelar el cupón.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Cupón cancelado correctamente.',
        ]);
    }

    /**
     * Devuelve el detalle completo de un cupón junto con su historial de usos y bitácora.
     */
    public function detalle($idCupon)
    {
        $idCupon = (int)$idCupon;
        $cupon   = $this->cuponModel->find($idCupon);
        if (!$cupon) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Cupón no encontrado.',
            ]);
        }

        $cupon['estado_resuelto']  = $this->calculator->resolverEstado($cupon);
        $cupon['usos_disponibles'] = max(0, (int)$cupon['limite_usos'] - (int)$cupon['usos_realizados']);

        $usos = $this->cuponUsoModel
            ->where('id_cupon', $idCupon)
            ->orderBy('fecha_uso', 'DESC')
            ->findAll();

        $bitacora = $this->bitacoraModel
            ->where('id_cupon', $idCupon)
            ->orderBy('creado_en', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        return $this->response->setJSON([
            'success'  => true,
            'cupon'    => $cupon,
            'usos'     => $usos,
            'bitacora' => $bitacora,
        ]);
    }

    /**
     * Valida y normaliza los campos de creación/edición de un cupón.
     */
    private function validarDatosCupon(array $data, ?int $excludeId = null): array
    {
        $codigo = strtoupper(trim((string)($data['codigo'] ?? '')));
        if ($codigo === '') {
            return ['valid' => false, 'message' => 'El código del cupón es obligatorio.'];
        }
        if (strlen($codigo) > 50) {
            return ['valid' => false, 'message' => 'El código del cupón no puede exceder 50 caracteres.'];
        }
        if ($this->cuponModel->existeCodigo($codigo, $excludeId)) {
            return ['valid' => false, 'message' => 'Ya existe un cupón registrado con el código "' . $codigo . '".'];
        }

        $concepto = trim((string)($data['concepto'] ?? ''));
        if ($concepto === '') {
            return ['valid' => false, 'message' => 'El nombre o concepto del beneficio es obligatorio.'];
        }

        $tipo = (string)($data['tipo_descuento'] ?? 'fijo');
        if (!in_array($tipo, ['fijo', 'porcentaje'], true)) {
            return ['valid' => false, 'message' => 'Tipo de descuento no válido.'];
        }

        $valor = round((float)($data['valor'] ?? 0), 2);
        if ($valor <= 0) {
            return ['valid' => false, 'message' => 'El valor del descuento debe ser mayor a 0.'];
        }
        if ($tipo === 'porcentaje' && $valor > 100) {
            return ['valid' => false, 'message' => 'El porcentaje de descuento no puede ser mayor a 100%.'];
        }

        $compraMinimaRaw = $data['compra_minima'] ?? null;
        $compraMinima    = ($compraMinimaRaw !== null && $compraMinimaRaw !== '') ? round((float)$compraMinimaRaw, 2) : null;
        if ($compraMinima !== null && $compraMinima < 0) {
            return ['valid' => false, 'message' => 'El importe mínimo de compra no puede ser negativo.'];
        }

        $descuentoMaxRaw = $data['descuento_maximo'] ?? null;
        $descuentoMax    = ($tipo === 'porcentaje' && $descuentoMaxRaw !== null && $descuentoMaxRaw !== '')
            ? round((float)$descuentoMaxRaw, 2)
            : null;
        if ($descuentoMax !== null && $descuentoMax <= 0) {
            return ['valid' => false, 'message' => 'El descuento máximo debe ser mayor a 0 si se especifica.'];
        }

        $fechaEmision     = !empty($data['fecha_emision']) ? (string)$data['fecha_emision'] : date('Y-m-d');
        $fechaInicio      = !empty($data['fecha_inicio']) ? (string)$data['fecha_inicio'] : $fechaEmision;
        $fechaVencimiento = !empty($data['fecha_vencimiento']) ? (string)$data['fecha_vencimiento'] : null;

        if ($fechaVencimiento !== null && $fechaVencimiento < $fechaInicio) {
            return ['valid' => false, 'message' => 'La fecha de vencimiento no puede ser anterior a la fecha de inicio de vigencia.'];
        }

        $limiteUsos = (int)($data['limite_usos'] ?? 1);
        if ($limiteUsos < 1) {
            return ['valid' => false, 'message' => 'El límite de usos debe ser al menos 1.'];
        }

        return [
            'valid'   => true,
            'payload' => [
                'codigo'            => $codigo,
                'concepto'          => $concepto,
                'tipo_descuento'    => $tipo,
                'valor'             => $valor,
                'compra_minima'     => $compraMinima,
                'descuento_maximo'  => $descuentoMax,
                'fecha_emision'     => $fechaEmision,
                'fecha_inicio'      => $fechaInicio,
                'fecha_vencimiento' => $fechaVencimiento,
                'limite_usos'       => $limiteUsos,
                'motivo_origen'     => trim((string)($data['motivo_origen'] ?? '')) ?: null,
                'observaciones'     => trim((string)($data['observaciones'] ?? '')) ?: null,
            ],
        ];
    }
}

