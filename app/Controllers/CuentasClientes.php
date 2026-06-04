<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ClienteModel;
use App\Models\CuentaClienteModel;
use App\Models\ProductoModel;

class CuentasClientes extends BaseController
{
    protected $clienteModel;
    protected $cuentaClienteModel;
    protected $productoModel;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
        $this->cuentaClienteModel = new CuentaClienteModel();
        $this->productoModel = new ProductoModel();
    }

    public function index()
    {
        $q = $this->request->getVar('q');
        
        // Obtener clientes con balances sumados en una sola consulta
        $db = \Config\Database::connect();
        $builder = $db->table('t_clientes c')
            ->select('c.*, 
                ((SELECT COALESCE(SUM(totalProduc), 0) FROM t_cuenta_cliente WHERE idCliente = c.idCliente) - 
                 (SELECT COALESCE(SUM(abono), 0) FROM t_abono_cliente WHERE idCliente = c.idCliente)) AS total_pendiente,
                (SELECT COALESCE(SUM(abono), 0) FROM t_abono_cliente WHERE idCliente = c.idCliente) AS total_pagado')
            ->orderBy('c.nombre', 'ASC');

        if (!empty($q)) {
            $builder->like('c.nombre', $q)
                    ->orLike('c.cel', $q);
        }

        $clientes = $builder->get()->getResultArray();

        $data = [
            'clientes' => $clientes,
            'q' => $q,
            'titulo' => 'Gestión de Cuentas de Clientes'
        ];

        return view('cuentas_clientes/index', $data);
    }

    public function obtenerCompras($idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return $this->response->setStatusCode(404)->setBody('Cliente no encontrado');
        }

        $compras = $this->cuentaClienteModel->obtenerComprasPorCliente($idCliente);

        // Obtener abonos
        $db = \Config\Database::connect();
        $abonos = $db->table('t_abono_cliente')
            ->where('idCliente', $idCliente)
            ->orderBy('fechaAbono', 'DESC')
            ->orderBy('idAbono', 'DESC')
            ->get()
            ->getResultArray();

        // Calcular balances históricos matemáticos
        $totalComprasHistorico = 0;
        $totalComprasActivas = 0;
        foreach ($compras as $c) {
            $totalComprasHistorico += $c['totalProduc'];
            if ($c['estatusCompra'] == '0') {
                $totalComprasActivas += $c['totalProduc'];
            }
        }

        $totalPagadoHistorico = 0;
        foreach ($abonos as $a) {
            $totalPagadoHistorico += $a['abono'];
        }

        $totalPendiente = $totalComprasHistorico - $totalPagadoHistorico;
        
        // Lo abonado hacia las compras pendientes actuales
        $abonadoActivo = $totalComprasActivas - $totalPendiente;

        $data = [
            'cliente' => $cliente,
            'compras' => $compras,
            'abonos'  => $abonos,
            'totalPendiente' => $totalPendiente,
            'totalPagadoActivo' => $abonadoActivo,
            'totalComprasActivas' => $totalComprasActivas,
            'totalPagadoHistorico' => $totalPagadoHistorico
        ];

        return view('cuentas_clientes/_tabla_compras', $data);
    }

    public function crear()
    {
        $idCliente = $this->request->getPost('id_cliente');
        $tipoCompra = $this->request->getPost('tipo_compra'); // 'inventario' o 'libre'
        $fechaCompra = $this->request->getPost('fecha_compra') ?: date('Y-m-d');
        $cantidad = (int)$this->request->getPost('cantidad');
        $precioUnit = (float)$this->request->getPost('precio_unit');
        $estatusCompra = $this->request->getPost('estatus_compra') ?: '0'; // '0' = Pendiente, '1' = Pagado
        $descontarStock = $this->request->getPost('descontar_stock') === '1';

        if (empty($idCliente) || $cantidad <= 0 || $precioUnit < 0) {
            return redirect()->back()->with('error', 'Por favor, completa los campos correctamente.');
        }

        $idInventario = 0;
        $descProducto = '';

        if ($tipoCompra === 'inventario') {
            $idInventario = (int)$this->request->getPost('id_inventario');
            $producto = $this->productoModel->find($idInventario);
            
            if (!$producto) {
                return redirect()->back()->with('error', 'Producto de inventario no válido.');
            }
            
            $descProducto = $producto['descripcion'];
            
            // Descontar del stock si corresponde
            if ($descontarStock) {
                $nuevoStock = $producto['stock'] - $cantidad;
                $this->productoModel->update($idInventario, ['stock' => max(0, $nuevoStock)]);
            }
        } else {
            $descProducto = $this->request->getPost('desc_producto');
            if (empty($descProducto)) {
                return redirect()->back()->with('error', 'Por favor, ingresa una descripción para el producto libre.');
            }
        }

        $totalProduc = $cantidad * $precioUnit;

        $nuevaCompra = [
            'idCliente'     => $idCliente,
            'fechaCompra'   => $fechaCompra,
            'cantidad'      => $cantidad,
            'descProducto'  => $descProducto,
            'precioUnit'    => $precioUnit,
            'totalProduc'   => $totalProduc,
            'estatusCompra' => $estatusCompra,
            'idInventario'  => $idInventario
        ];

        if ($this->cuentaClienteModel->insert($nuevaCompra)) {
            if ($estatusCompra == '1') {
                $this->registrarPagoCaja($idCliente, $totalProduc);
            }
            return redirect()->to(base_url('admin/cuentas'))->with('success', 'Compra registrada con éxito.');
        }

        return redirect()->back()->with('error', 'No se pudo registrar la compra.');
    }

    public function editar($idCompra)
    {
        $compra = $this->cuentaClienteModel->find($idCompra);
        if (!$compra) {
            return redirect()->back()->with('error', 'Compra no encontrada.');
        }

        $cantidadNueva = (int)$this->request->getPost('cantidad');
        $precioUnitNuevo = (float)$this->request->getPost('precio_unit');
        $fechaCompra = $this->request->getPost('fecha_compra') ?: date('Y-m-d');
        $estatusCompra = $this->request->getPost('estatus_compra') ?: '0';
        $descontarStock = $this->request->getPost('descontar_stock') === '1';

        if ($cantidadNueva <= 0 || $precioUnitNuevo < 0) {
            return redirect()->back()->with('error', 'Valores de cantidad o precio incorrectos.');
        }

        // Manejo de Stock en Edición
        $idInventario = (int)$compra['idInventario'];
        if ($idInventario > 0 && $descontarStock) {
            $producto = $this->productoModel->find($idInventario);
            if ($producto) {
                // Revertir stock antiguo y aplicar el nuevo
                $cantidadDiferencia = $cantidadNueva - (int)$compra['cantidad'];
                $nuevoStock = $producto['stock'] - $cantidadDiferencia;
                $this->productoModel->update($idInventario, ['stock' => max(0, $nuevoStock)]);
            }
        }

        $totalProduc = $cantidadNueva * $precioUnitNuevo;

        $this->cuentaClienteModel->update($idCompra, [
            'fechaCompra'   => $fechaCompra,
            'cantidad'      => $cantidadNueva,
            'precioUnit'    => $precioUnitNuevo,
            'totalProduc'   => $totalProduc,
            'estatusCompra' => $estatusCompra
        ]);

        // Si cambió de Pendiente a Pagado, registrar pago
        if ($compra['estatusCompra'] == '0' && $estatusCompra == '1') {
            $this->registrarPagoCaja($compra['idCliente'], $totalProduc);
        }

        return redirect()->to(base_url('admin/cuentas'))->with('success', 'Compra modificada con éxito.');
    }

    public function eliminar($idCompra)
    {
        $compra = $this->cuentaClienteModel->find($idCompra);
        if (!$compra) {
            return redirect()->back()->with('error', 'Compra no encontrada.');
        }

        // Si es de inventario y se asume que descontó stock, lo devolvemos
        $idInventario = (int)$compra['idInventario'];
        if ($idInventario > 0) {
            $producto = $this->productoModel->find($idInventario);
            if ($producto) {
                $nuevoStock = $producto['stock'] + (int)$compra['cantidad'];
                $this->productoModel->update($idInventario, ['stock' => $nuevoStock]);
            }
        }

        $this->cuentaClienteModel->delete($idCompra);

        return redirect()->to(base_url('admin/cuentas'))->with('success', 'Registro de compra eliminado.');
    }

    public function toggleEstado($idCompra)
    {
        $compra = $this->cuentaClienteModel->find($idCompra);
        if (!$compra) {
            return $this->response->setJSON(['success' => false, 'message' => 'Compra no encontrada']);
        }

        $nuevoEstado = $compra['estatusCompra'] == '0' ? '1' : '0';
        $this->cuentaClienteModel->update($idCompra, ['estatusCompra' => $nuevoEstado]);

        if ($nuevoEstado == '1') {
            $this->registrarPagoCaja($compra['idCliente'], $compra['totalProduc']);
        }

        // Recalcular saldo total del cliente para enviarlo de vuelta
        $compras = $this->cuentaClienteModel->obtenerComprasPorCliente($compra['idCliente']);
        
        $totalComprasHistorico = 0;
        $totalComprasActivas = 0;
        foreach ($compras as $c) {
            $totalComprasHistorico += $c['totalProduc'];
            if ($c['estatusCompra'] == '0') {
                $totalComprasActivas += $c['totalProduc'];
            }
        }

        $db = \Config\Database::connect();
        $totalPagadoHistorico = $db->table('t_abono_cliente')
            ->where('idCliente', $compra['idCliente'])
            ->selectSum('abono')
            ->get()
            ->getRow()
            ->abono ?? 0;

        $totalPendiente = $totalComprasHistorico - $totalPagadoHistorico;
        $abonadoActivo = $totalComprasActivas - $totalPendiente;

        return $this->response->setJSON([
            'success' => true,
            'nuevoEstado' => $nuevoEstado,
            'totalPendiente' => number_format($totalPendiente, 2),
            'totalPagado' => number_format($abonadoActivo, 2),
            'totalCompras' => number_format($totalComprasActivas, 2)
        ]);
    }

    public function buscarProductosJson()
    {
        $term = $this->request->getVar('term');
        if (empty($term)) {
            return $this->response->setJSON([]);
        }

        $productos = $this->productoModel->buscarProductos($term);
        
        $output = [];
        foreach ($productos as $p) {
            $output[] = [
                'id' => $p['id'],
                'sku' => $p['codigo_sku'],
                'descripcion' => $p['descripcion'],
                'precio' => $p['precio'],
                'stock' => $p['stock']
            ];
        }

        return $this->response->setJSON($output);
    }

    public function editarCliente($idCliente)
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado.');
        }

        $nombre = $this->request->getPost('nombre');
        $cel = $this->request->getPost('cel');
        $tipoCliente = $this->request->getPost('tipoCliente');

        if (empty($nombre)) {
            return redirect()->back()->with('error', 'El nombre del cliente no puede estar vacío.');
        }

        $this->clienteModel->update($idCliente, [
            'nombre'      => $nombre,
            'cel'         => $cel,
            'tipoCliente' => $tipoCliente
        ]);

        return redirect()->to(base_url('admin/cuentas'))->with('success', 'Datos del cliente actualizados con éxito.');
    }

    private function registrarPagoCaja($idCliente, $monto)
    {
        $db = \Config\Database::connect();
        $cliente = $this->clienteModel->find($idCliente);
        $nombreCliente = $cliente ? $cliente['nombre'] : 'Desconocido';

        // Insertar abono
        $db->table('t_abono_cliente')->insert([
            'idCliente'  => $idCliente,
            'fechaAbono' => date('Y-m-d'),
            'abono'      => $monto,
            'idCompra'   => 0
        ]);

        // Insertar en caja chica
        $db->table('t_caja_chica')->insert([
            'fecha'       => date('Y-m-d'),
            'descripcion' => 'Abono de cliente: ' . $nombreCliente,
            'monto'       => $monto,
            'tipo'        => 'Ingreso'
        ]);
    }
}
