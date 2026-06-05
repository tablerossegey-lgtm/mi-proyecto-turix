<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CajaChicaModel;

class CajaChica extends BaseController
{
    protected $cajaModel;

    public function __construct()
    {
        $this->cajaModel = new CajaChicaModel();
    }

    public function index()
    {
        // Obtener movimientos ordenados por fecha y ID descendente
        $movimientos = $this->cajaModel->orderBy('fecha', 'DESC')
                                       ->orderBy('idMovimiento', 'DESC')
                                       ->findAll();

        // Calcular balances
        $totalIngresos = 0.00;
        $totalEgresos = 0.00;

        foreach ($movimientos as $m) {
            $monto = (float)$m['monto'];
            if ($m['tipo'] === 'Ingreso') {
                $totalIngresos += $monto;
            } else if ($m['tipo'] === 'Egreso') {
                $totalEgresos += $monto;
            }
        }

        $saldoCaja = $totalIngresos - $totalEgresos;

        $data = [
            'movimientos'   => $movimientos,
            'totalIngresos' => $totalIngresos,
            'totalEgresos'  => $totalEgresos,
            'saldoCaja'     => $saldoCaja,
            'titulo'        => 'Control de Caja Chica'
        ];

        return view('caja/index', $data);
    }

    public function crear()
    {
        $fecha = $this->request->getPost('fecha') ?: date('Y-m-d');
        $descripcion = $this->request->getPost('descripcion');
        $monto = (float)$this->request->getPost('monto');
        $tipo = $this->request->getPost('tipo'); // 'Ingreso' o 'Egreso'

        if (empty($descripcion) || $monto <= 0 || !in_array($tipo, ['Ingreso', 'Egreso'])) {
            return redirect()->back()->withInput()->with('error', 'Por favor complete todos los campos correctamente.');
        }

        $nuevoMovimiento = [
            'fecha'       => $fecha,
            'descripcion' => $descripcion,
            'monto'       => $monto,
            'tipo'        => $tipo
        ];

        if ($this->cajaModel->insert($nuevoMovimiento)) {
            return redirect()->to(base_url('admin/caja'))->with('success', 'Movimiento registrado correctamente.');
        }

        return redirect()->back()->withInput()->with('error', 'No se pudo registrar el movimiento.');
    }

    public function eliminar($id)
    {
        $movimiento = $this->cajaModel->find($id);

        if (!$movimiento) {
            return redirect()->to(base_url('admin/caja'))->with('error', 'El movimiento no existe.');
        }

        if ($this->cajaModel->delete($id)) {
            return redirect()->to(base_url('admin/caja'))->with('success', 'Movimiento de caja eliminado correctamente.');
        }

        return redirect()->to(base_url('admin/caja'))->with('error', 'No se pudo eliminar el movimiento.');
    }
}
