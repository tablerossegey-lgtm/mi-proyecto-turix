<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FondoModel;
use App\Models\FondoAportacionModel;
use App\Models\CajaChicaModel;

class FondosAhorro extends BaseController
{
    protected $fondoModel;
    protected $aportacionModel;
    protected $cajaModel;

    public function __construct()
    {
        $this->fondoModel      = new FondoModel();
        $this->aportacionModel = new FondoAportacionModel();
        $this->cajaModel       = new CajaChicaModel();
    }

    public function index()
    {
        // 1. Obtener fondos activos (por defecto "Bodega Turixshop")
        $fondos = $this->fondoModel->where('activo', 1)->findAll();

        if (empty($fondos)) {
            // Si por alguna razón no existiera ninguno, creamos el inicial
            $this->fondoModel->insert([
                'nombre'              => 'Bodega Turixshop',
                'descripcion'         => 'Fondo destinado a la construcción y acondicionamiento de la bodega propia.',
                'porcentaje_sugerido' => 10.00,
                'meta_monto'          => 30000.00,
                'activo'              => 1,
            ]);
            $fondos = $this->fondoModel->where('activo', 1)->findAll();
        }

        $idFondoSeleccionado = (int)($this->request->getGet('fondo') ?: $fondos[0]['id']);
        $fondoActual = $this->fondoModel->find($idFondoSeleccionado) ?: $fondos[0];

        // 2. Mes y Año a evaluar (por defecto mes y año actual)
        $mesActual = (int)($this->request->getGet('mes') ?: date('n'));
        $anioActual = (int)($this->request->getGet('anio') ?: date('Y'));

        // Determinar estado temporal del periodo
        $periodoActualSistema = (int)date('Y') * 100 + (int)date('n');
        $periodoSeleccionado   = $anioActual * 100 + $mesActual;

        $esMesEnCurso       = ($periodoSeleccionado === $periodoActualSistema);
        $esMesPasadoCerrado = ($periodoSeleccionado < $periodoActualSistema);
        $esMesFuturo        = ($periodoSeleccionado > $periodoActualSistema);

        // Rango de fechas del mes seleccionado
        $fechaInicio = sprintf('%04d-%02d-01', $anioActual, $mesActual);
        $fechaFin    = date('Y-m-t', strtotime($fechaInicio));

        // 3. Consultar Ingresos, Egresos y Balance de Caja Chica para ese mes
        $db = \Config\Database::connect();
        $queryCaja = $db->query("
            SELECT 
                IFNULL(SUM(IF(tipo = 'Ingreso', monto, 0)), 0) AS total_ingresos,
                IFNULL(SUM(IF(tipo = 'Egreso',  monto, 0)), 0) AS total_egresos,
                (IFNULL(SUM(IF(tipo = 'Ingreso', monto, 0)), 0) - IFNULL(SUM(IF(tipo = 'Egreso', monto, 0)), 0)) AS balance_neto
            FROM t_caja_chica 
            WHERE fecha BETWEEN ? AND ?
        ", [$fechaInicio, $fechaFin])->getRowArray();

        $ingresosMes = (float)($queryCaja['total_ingresos'] ?? 0);
        $egresosMes  = (float)($queryCaja['total_egresos'] ?? 0);
        $balanceNeto = (float)($queryCaja['balance_neto'] ?? 0);

        // 4. Calcular aportación sugerida según el porcentaje configurado
        $porcentaje = (float)$fondoActual['porcentaje_sugerido'];
        $aportacionSugerida = 0.00;
        if ($balanceNeto > 0) {
            $aportacionSugerida = round($balanceNeto * ($porcentaje / 100), 2);
        }

        // 5. Verificar si ya se registró la aportación mensual de este mes
        $aportacionRegistrada = $this->aportacionModel
            ->where('id_fondo', $fondoActual['id'])
            ->where('anio', $anioActual)
            ->where('mes', $mesActual)
            ->where('tipo_aportacion', 'Mensual')
            ->first();

        $yaAportado = !empty($aportacionRegistrada);

        // 6. Resumen acumulado y meta
        $saldoAcumulado = $this->aportacionModel->getTotalAcumulado($fondoActual['id']);
        $totalAportado  = $saldoAcumulado;
        $metaMonto      = (float)$fondoActual['meta_monto'];
        $progresoMeta   = ($metaMonto > 0) ? min(100, round(($saldoAcumulado / $metaMonto) * 100, 2)) : 0;

        $ultimaAportacion = $this->aportacionModel->getUltimaAportacion($fondoActual['id']);
        $historial        = $this->aportacionModel->getHistorial($fondoActual['id']);

        // 7. Calcular saldo disponible de Caja Chica para transferencias internas
        $saldoDisponibleCaja = $this->obtenerSaldoDisponibleCaja();

        // Nombres de los meses en español para la vista
        $meses = [
            1  => 'Enero',      2  => 'Febrero',   3  => 'Marzo',
            4  => 'Abril',      5  => 'Mayo',      6  => 'Junio',
            7  => 'Julio',      8  => 'Agosto',    9  => 'Septiembre',
            10 => 'Octubre',    11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $data = [
            'titulo'               => 'Fondos de Ahorro - Turixshop',
            'fondos'               => $fondos,
            'fondoActual'          => $fondoActual,
            'mesActual'            => $mesActual,
            'anioActual'           => $anioActual,
            'meses'                => $meses,
            'nombreMesActual'      => $meses[$mesActual] ?? '',
            'ingresosMes'          => $ingresosMes,
            'egresosMes'           => $egresosMes,
            'balanceNeto'          => $balanceNeto,
            'porcentaje'           => $porcentaje,
            'aportacionSugerida'   => $aportacionSugerida,
            'yaAportado'           => $yaAportado,
            'aportacionRegistrada' => $aportacionRegistrada,
            'saldoAcumulado'       => $saldoAcumulado,
            'totalAportado'        => $totalAportado,
            'metaMonto'            => $metaMonto,
            'progresoMeta'         => $progresoMeta,
            'ultimaAportacion'     => $ultimaAportacion,
            'historial'            => $historial,
            'saldoDisponibleCaja'  => $saldoDisponibleCaja,
            'esMesEnCurso'         => $esMesEnCurso,
            'esMesPasadoCerrado'   => $esMesPasadoCerrado,
            'esMesFuturo'          => $esMesFuturo,
        ];

        return view('fondos/index', $data);
    }

    /**
     * Registrar aportación mensual al fondo
     */
    public function registrarAportacion()
    {
        $idFondo = (int)$this->request->getPost('id_fondo');
        $anio    = (int)$this->request->getPost('anio');
        $mes     = (int)$this->request->getPost('mes');
        $monto   = (float)$this->request->getPost('monto_aportado');
        $notas   = trim($this->request->getPost('notas') ?? '');

        $fondo = $this->fondoModel->find($idFondo);
        if (!$fondo) {
            return redirect()->back()->with('error', 'El fondo especificado no existe.');
        }

        // Validar que el mes ya haya terminado
        $periodoActualSistema = (int)date('Y') * 100 + (int)date('n');
        $periodoSeleccionado   = $anio * 100 + $mes;
        if ($periodoSeleccionado >= $periodoActualSistema) {
            return redirect()->to(base_url("admin/fondos?fondo={$idFondo}&mes={$mes}&anio={$anio}"))
                             ->with('error', 'El mes aún está en curso. La aportación mensual podrá registrarse una vez finalizado el periodo.');
        }

        // Validar si ya existe aportación mensual registrada
        if ($this->aportacionModel->existeAportacionMes($idFondo, $anio, $mes, 'Mensual')) {
            return redirect()->to(base_url("admin/fondos?fondo={$idFondo}&mes={$mes}&anio={$anio}"))
                             ->with('error', 'Ya se encuentra registrada la aportación mensual para este periodo.');
        }

        if ($monto <= 0) {
            return redirect()->back()->with('error', 'El monto a aportar debe ser mayor a 0.');
        }

        // Validar que haya saldo suficiente disponible en Caja Chica
        $saldoDisponible = $this->obtenerSaldoDisponibleCaja();
        if ($monto > $saldoDisponible) {
            return redirect()->to(base_url("admin/fondos?fondo={$idFondo}&mes={$mes}&anio={$anio}"))
                             ->with('error', 'El monto de la aportación ($' . number_format($monto, 2) . ') supera el saldo disponible en Caja Chica ($' . number_format($saldoDisponible, 2) . ').');
        }

        // Recalcular balance real del mes para almacenar como evidencia histórica
        $fechaInicio = sprintf('%04d-%02d-01', $anio, $mes);
        $fechaFin    = date('Y-m-t', strtotime($fechaInicio));

        $db = \Config\Database::connect();
        $queryCaja = $db->query("
            SELECT (IFNULL(SUM(IF(tipo = 'Ingreso', monto, 0)), 0) - IFNULL(SUM(IF(tipo = 'Egreso', monto, 0)), 0)) AS balance_neto
            FROM t_caja_chica 
            WHERE fecha BETWEEN ? AND ?
        ", [$fechaInicio, $fechaFin])->getRowArray();

        $balanceNeto = (float)($queryCaja['balance_neto'] ?? 0);
        $porcentaje  = (float)$fondo['porcentaje_sugerido'];
        $montoSugerido = ($balanceNeto > 0) ? round($balanceNeto * ($porcentaje / 100), 2) : 0.00;

        $datosAportacion = [
            'id_fondo'            => $idFondo,
            'anio'                => $anio,
            'mes'                 => $mes,
            'balance_caja'        => $balanceNeto,
            'porcentaje_aplicado' => $porcentaje,
            'monto_sugerido'      => $montoSugerido,
            'monto_aportado'      => $monto,
            'tipo_aportacion'     => 'Mensual',
            'fecha_registro'      => date('Y-m-d'),
            'notas'               => !empty($notas) ? $notas : 'Aportación mensual correspondiente al periodo ' . sprintf('%02d/%04d', $mes, $anio)
        ];

        $db->transStart();
        $this->aportacionModel->insert($datosAportacion);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Ocurrió un error al registrar la aportación mensual en base de datos.');
        }

        return redirect()->to(base_url("admin/fondos?fondo={$idFondo}&mes={$mes}&anio={$anio}"))
                         ->with('success', '¡Aportación mensual registrada con éxito al fondo ' . esc($fondo['nombre']) . '!');
    }

    /**
     * Registrar aportación extraordinaria al fondo
     */
    public function registrarAportacionExtraordinaria()
    {
        $idFondo       = (int)$this->request->getPost('id_fondo');
        $monto         = (float)$this->request->getPost('monto_aportado');
        $fechaRegistro = $this->request->getPost('fecha_registro') ?: date('Y-m-d');
        $notas         = trim($this->request->getPost('notas') ?? '');

        $fondo = $this->fondoModel->find($idFondo);
        if (!$fondo) {
            return redirect()->back()->with('error', 'El fondo especificado no existe.');
        }

        if ($monto <= 0) {
            return redirect()->back()->with('error', 'El monto a aportar debe ser mayor a 0.');
        }

        // Validar saldo disponible en Caja Chica
        $saldoDisponible = $this->obtenerSaldoDisponibleCaja();
        if ($monto > $saldoDisponible) {
            return redirect()->back()->with('error', 'El monto de la aportación ($' . number_format($monto, 2) . ') supera el saldo disponible en Caja Chica ($' . number_format($saldoDisponible, 2) . ').');
        }

        $anio = (int)date('Y', strtotime($fechaRegistro));
        $mes  = (int)date('n', strtotime($fechaRegistro));

        $datosAportacion = [
            'id_fondo'            => $idFondo,
            'anio'                => $anio,
            'mes'                 => $mes,
            'balance_caja'        => 0.00,
            'porcentaje_aplicado' => 0.00,
            'monto_sugerido'      => 0.00,
            'monto_aportado'      => $monto,
            'tipo_aportacion'     => 'Extraordinaria',
            'fecha_registro'      => $fechaRegistro,
            'notas'               => !empty($notas) ? $notas : 'Aportación extraordinaria voluntaria'
        ];

        $db = \Config\Database::connect();
        $db->transStart();
        $this->aportacionModel->insert($datosAportacion);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Ocurrió un error al registrar la aportación extraordinaria.');
        }

        return redirect()->to(base_url("admin/fondos?fondo={$idFondo}&mes={$mes}&anio={$anio}"))
                         ->with('success', '¡Aportación extraordinaria de $' . number_format($monto, 2) . ' registrada con éxito al fondo ' . esc($fondo['nombre']) . '!');
    }

    /**
     * Guardar configuración del fondo (Porcentaje sugerido y Meta económica)
     */
    public function guardarConfiguracion()
    {
        $idFondo    = (int)$this->request->getPost('id_fondo');
        $porcentaje = (float)$this->request->getPost('porcentaje_sugerido');
        $metaMonto  = (float)$this->request->getPost('meta_monto');
        $descripcion= trim($this->request->getPost('descripcion') ?? '');

        $fondo = $this->fondoModel->find($idFondo);
        if (!$fondo) {
            return redirect()->back()->with('error', 'El fondo no existe.');
        }

        if ($porcentaje < 0 || $porcentaje > 100) {
            return redirect()->back()->with('error', 'El porcentaje debe estar entre 0% y 100%.');
        }

        $this->fondoModel->update($idFondo, [
            'porcentaje_sugerido' => $porcentaje,
            'meta_monto'          => max(0, $metaMonto),
            'descripcion'         => $descripcion
        ]);

        return redirect()->to(base_url("admin/fondos?fondo={$idFondo}"))
                         ->with('success', 'Configuración del fondo actualizada correctamente.');
    }

    /**
     * Eliminar aportación en caso de error administrativo
     */
    public function eliminarAportacion($id)
    {
        $aportacion = $this->aportacionModel->find($id);
        if (!$aportacion) {
            return redirect()->back()->with('error', 'La aportación no existe.');
        }

        $idFondo = $aportacion['id_fondo'];
        $this->aportacionModel->delete($id);

        return redirect()->to(base_url("admin/fondos?fondo={$idFondo}"))
                         ->with('success', 'Aportación eliminada correctamente.');
    }

    /**
     * Obtiene el dinero disponible de Caja Chica para transferencias internas a Fondos
     */
    private function obtenerSaldoDisponibleCaja(): float
    {
        $db = \Config\Database::connect();
        $queryCaja = $db->query("
            SELECT (IFNULL(SUM(IF(tipo = 'Ingreso', monto, 0)), 0) - IFNULL(SUM(IF(tipo = 'Egreso', monto, 0)), 0)) AS saldo_caja
            FROM t_caja_chica
        ")->getRowArray();

        $saldoCaja = (float)($queryCaja['saldo_caja'] ?? 0);
        $totalEnFondos = $this->aportacionModel->getTotalAportadoGlobal();

        return max(0, $saldoCaja - $totalEnFondos);
    }
}
