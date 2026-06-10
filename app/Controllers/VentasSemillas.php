<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ClienteModel;
use App\Models\CuentaClienteModel;
use App\Models\VentaSemillaModel;
use App\Models\ProductoModel;

class VentasSemillas extends BaseController
{
    protected $clienteModel;
    protected $cuentaClienteModel;
    protected $ventaModel;
    protected $productoModel;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
        $this->cuentaClienteModel = new CuentaClienteModel();
        $this->ventaModel = new VentaSemillaModel();
        $this->productoModel = new ProductoModel();
    }

    public function index()
    {
        $estadisticas = $this->ventaModel->obtenerEstadisticas();
        $ventas = $this->ventaModel->obtenerVentasConClientes();
        $clientes = $this->clienteModel->orderBy('nombre', 'ASC')->findAll();
        $clientesReporte = $this->ventaModel->obtenerVentasPorClientes();

        $productosPredefinidos = [
            [
                'nombre'       => 'Bolsa de Pepitas',
                'precio_venta' => 15.00,
                'precio_bea'   => 14.00,
                'ganancia'     => 1.00,
                'inventario'   => 'En inventario'
            ],
            [
                'nombre'       => 'Bolsa de Mix frutos secos',
                'precio_venta' => 20.00,
                'precio_bea'   => 19.00,
                'ganancia'     => 1.00,
                'inventario'   => 'En inventario'
            ],
            [
                'nombre'       => 'Bolsa de Palomitas',
                'precio_venta' => 10.00,
                'precio_bea'   => 10.00,
                'ganancia'     => 0.00,
                'inventario'   => 'En inventario'
            ],
            [
                'nombre'       => 'Bolsa de Palomitas Jumbo',
                'precio_venta' => 20.00,
                'precio_bea'   => 20.00,
                'ganancia'     => 0.00,
                'inventario'   => 'En inventario'
            ],
            [
                'nombre'       => 'Repelente',
                'precio_venta' => 75.00,
                'precio_bea'   => 70.00,
                'ganancia'     => 5.00,
                'inventario'   => 'Aun no se encuentra inventario'
            ]
        ];

        $data = [
            'titulo'                => 'Control de Ventas de Semillas y Repelentes',
            'estadisticas'          => $estadisticas,
            'ventas'                => $ventas,
            'clientes'              => $clientes,
            'clientesReporte'       => $clientesReporte,
            'productosPredefinidos' => $productosPredefinidos
        ];

        return view('admin/semillas/index', $data);
    }

    public function crear()
    {
        $id_cliente = (int)$this->request->getPost('id_cliente');
        $nombre_cliente_libre = $this->request->getPost('nombre_cliente_libre');
        $fecha = $this->request->getPost('fecha') ?: date('Y-m-d');
        $producto = $this->request->getPost('producto');
        $cantidad = (int)$this->request->getPost('cantidad');
        $precio_venta = (float)$this->request->getPost('precio_venta');
        $precio_bea = (float)$this->request->getPost('precio_bea');
        $metodo_pago = $this->request->getPost('metodo_pago'); // 'contado' o 'cuenta'

        if (empty($producto) || $cantidad <= 0 || $precio_venta < 0 || $precio_bea < 0) {
            return redirect()->back()->withInput()->with('error', 'Por favor, completa los campos correctamente.');
        }

        // Obtener el nombre del cliente si está registrado
        $nombreClienteReg = '';
        if ($id_cliente > 0) {
            $cliente = $this->clienteModel->find($id_cliente);
            if ($cliente) {
                $nombreClienteReg = $cliente['nombre'];
            }
        }

        // Buscar si existe el producto en el inventario para descontar stock
        $productoInventario = $this->productoModel->where('descripcion', $producto)->first();
        $idInventario = 0;
        if ($productoInventario) {
            $idInventario = (int)$productoInventario['id'];
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Descontar stock del inventario si el producto existe
        if ($idInventario > 0) {
            $nuevoStock = $productoInventario['stock'] - $cantidad;
            $this->productoModel->update($idInventario, ['stock' => max(0, $nuevoStock)]);
        }

        if ($metodo_pago === 'cuenta') {
            if ($id_cliente <= 0) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Debes seleccionar un cliente registrado para vender a cuenta.');
            }

            // 1. Insertar en cuenta corriente del cliente
            $nuevaCompra = [
                'idCliente'     => $id_cliente,
                'fechaCompra'   => $fecha,
                'cantidad'      => $cantidad,
                'descProducto'  => $producto,
                'precioUnit'    => $precio_venta,
                'totalProduc'   => $precio_venta * $cantidad,
                'estatusCompra' => '0', // 0 = Pendiente
                'idInventario'  => $idInventario
            ];
            
            $this->cuentaClienteModel->insert($nuevaCompra);
            $idCompra = $this->cuentaClienteModel->getInsertID();

            // 2. Insertar en t_ventas_semillas
            $nuevaVenta = [
                'id_cliente'        => $id_cliente,
                'nombre_cliente'    => null,
                'fecha'             => $fecha,
                'producto'          => $producto,
                'cantidad'          => $cantidad,
                'precio_venta'      => $precio_venta,
                'precio_bea'        => $precio_bea,
                'estatus_pago'      => 'Pendiente',
                'entregado_bea'     => 'No',
                'id_cuenta_cliente' => $idCompra
            ];
            $this->ventaModel->insert($nuevaVenta);

        } else {
            // Venta de Contado
            // 1. Insertar en t_ventas_semillas
            $nuevaVenta = [
                'id_cliente'        => $id_cliente > 0 ? $id_cliente : null,
                'nombre_cliente'    => $id_cliente > 0 ? null : $nombre_cliente_libre,
                'fecha'             => $fecha,
                'producto'          => $producto,
                'cantidad'          => $cantidad,
                'precio_venta'      => $precio_venta,
                'precio_bea'        => $precio_bea,
                'estatus_pago'      => 'Pagado',
                'entregado_bea'     => 'No',
                'id_cuenta_cliente' => null
            ];
            $this->ventaModel->insert($nuevaVenta);

            // 2. Registrar el ingreso completo en Caja Chica
            $nombreMostrar = $id_cliente > 0 ? $nombreClienteReg : (empty($nombre_cliente_libre) ? 'Cliente Gral.' : $nombre_cliente_libre);
            $db->table('t_caja_chica')->insert([
                'fecha'       => $fecha,
                'descripcion' => "Venta Contado Semillas: {$producto} ({$nombreMostrar})",
                'monto'       => $precio_venta * $cantidad,
                'tipo'        => 'Ingreso'
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(500)->setBody('Error al registrar la venta en la base de datos.');
            }
            return redirect()->back()->withInput()->with('error', 'Error al registrar la venta en la base de datos.');
        }

        session()->setFlashdata('success', 'Venta registrada con éxito.');

        if ($this->request->getHeaderLine('HX-Request')) {
            return $this->index();
        }

        return redirect()->to(base_url('admin/semillas'))->with('success', 'Venta registrada con éxito.');
    }

    public function entregarBea()
    {
        $db = \Config\Database::connect();
        
        // Calcular la suma total lista para entregar
        $query = $db->query("SELECT SUM(precio_bea * cantidad) as total FROM t_ventas_semillas WHERE estatus_pago = 'Pagado' AND entregado_bea = 'No'");
        $totalBea = (float)($query->getRow()->total ?? 0.00);

        if ($totalBea <= 0) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(400)->setBody('No hay dinero pendiente de entrega para ventas cobradas.');
            }
            return redirect()->back()->with('error', 'No hay dinero pendiente de entrega para ventas cobradas.');
        }

        $db->transStart();

        // 1. Actualizar registros a entregado
        $db->table('t_ventas_semillas')
            ->where('estatus_pago', 'Pagado')
            ->where('entregado_bea', 'No')
            ->update(['entregado_bea' => 'Si']);

        // 2. Registrar el egreso en Caja Chica
        $db->table('t_caja_chica')->insert([
            'fecha'       => date('Y-m-d'),
            'descripcion' => 'Liquidación ventas a Bea - Corte de semillas y repelente',
            'monto'       => $totalBea,
            'tipo'        => 'Egreso'
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(500)->setBody('Error al procesar la liquidación con Bea.');
            }
            return redirect()->back()->with('error', 'Error al procesar la liquidación con Bea.');
        }

        session()->setFlashdata('success', 'Liquidación procesada correctamente. Se retiraron $' . number_format($totalBea, 2) . ' de la Caja Chica.');

        if ($this->request->getHeaderLine('HX-Request')) {
            return $this->index();
        }

        return redirect()->to(base_url('admin/semillas'))->with('success', 'Liquidación procesada correctamente. Se retiraron $' . number_format($totalBea, 2) . ' de la Caja Chica.');
    }

    public function eliminar($id)
    {
        $venta = $this->ventaModel->find($id);
        if (!$venta) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(404)->setBody('Venta no encontrada.');
            }
            return redirect()->back()->with('error', 'Venta no encontrada.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // Si la venta tiene una cuenta corriente asociada, eliminarla
        if (!empty($venta['id_cuenta_cliente'])) {
            $this->cuentaClienteModel->delete($venta['id_cuenta_cliente']);
        }

        // Si la venta era de contado y estaba pagada, tenemos que descontar el ingreso de la caja chica?
        // Generalmente no es automático para evitar desajustes manuales, pero se elimina el registro de venta.
        $this->ventaModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(500)->setBody('Error al eliminar el registro de venta.');
            }
            return redirect()->back()->with('error', 'Error al eliminar el registro de venta.');
        }

        session()->setFlashdata('success', 'Venta eliminada correctamente.');

        if ($this->request->getHeaderLine('HX-Request')) {
            return $this->index();
        }

        return redirect()->to(base_url('admin/semillas'))->with('success', 'Venta eliminada correctamente.');
    }
}
