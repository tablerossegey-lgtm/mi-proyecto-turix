<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class PedidosEncargados extends BaseController
{
    protected $pedidoEncargadoModel;
    protected $productoModel;
    protected $clienteModel;

    public function __construct()
    {
        $this->pedidoEncargadoModel = new \App\Models\PedidoEncargadoModel();
        $this->productoModel = new \App\Models\ProductoModel();
        $this->clienteModel = new \App\Models\ClienteModel();
    }

    public function index()
    {
        $estadoFiltro = $this->request->getVar('estado');
        
        $encargos = $this->pedidoEncargadoModel->obtenerTodosConProducto($estadoFiltro);
        
        $productos = $this->productoModel->orderBy('descripcion', 'ASC')->findAll();
        $clientes = $this->clienteModel->orderBy('nombre', 'ASC')->findAll();

        $db = \Config\Database::connect();
        
        // 1. Total de unidades encargadas y pendientes
        $statsPendientes = $db->table('t_pedidos_encargados')
                             ->selectSum('cantidad')
                             ->where('estado', 'Pendiente')
                             ->get()
                             ->getRowArray();
        $totalPendientes = $statsPendientes['cantidad'] ?? 0;

        // 2. Suma de anticipos recibidos para pedidos no entregados/cancelados
        $statsAnticipos = $db->table('t_pedidos_encargados')
                            ->selectSum('anticipo')
                            ->whereIn('estado', ['Pendiente', 'Conseguido'])
                            ->get()
                            ->getRowArray();
        $totalAnticipos = $statsAnticipos['anticipo'] ?? 0.00;

        $data = [
            'encargos' => $encargos,
            'productos' => $productos,
            'clientes' => $clientes,
            'total_pendientes' => $totalPendientes,
            'total_anticipos' => $totalAnticipos,
            'estado_filtro' => $estadoFiltro,
            'titulo' => 'Control de Pedidos Encargados'
        ];

        return view('pedidos_encargados/index', $data);
    }

    public function crear()
    {
        $idProducto = $this->request->getPost('id_producto') ?: null;
        $productoCustom = trim((string)$this->request->getPost('producto_custom'));
        $nombreCliente = trim((string)$this->request->getPost('nombre_cliente'));
        $contactoCliente = trim((string)$this->request->getPost('contacto_cliente'));
        $cantidad = $this->request->getPost('cantidad');
        $anticipo = $this->request->getPost('anticipo') ?: 0.00;
        $notas = $this->request->getPost('notas') ?: null;
        $estado = $this->request->getPost('estado') ?: 'Pendiente';

        if (empty($nombreCliente)) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(400)->setBody('Por favor ingrese el nombre del cliente.');
            }
            return redirect()->back()->withInput()->with('error', 'Por favor ingrese el nombre del cliente.');
        }

        if (empty($idProducto) && empty($productoCustom) && empty($notas)) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(400)->setBody('Debe seleccionar un producto del inventario, escribir uno personalizado o detallar el pedido en el campo de Notas.');
            }
            return redirect()->back()->withInput()->with('error', 'Debe seleccionar un producto del inventario, escribir uno personalizado o detallar el pedido en el campo de Notas.');
        }

        // Si no hay producto de inventario pero hay uno custom, lo guardamos en notas
        if (empty($idProducto) && !empty($productoCustom)) {
            $notas = "[Producto: " . $productoCustom . "]" . ($notas !== null ? "\n" . $notas : "");
        }

        // Buscar o guardar en t_clientes
        $clienteExistente = $this->clienteModel->where('nombre', $nombreCliente)->first();
        if (!$clienteExistente) {
            $this->clienteModel->insert([
                'nombre' => $nombreCliente,
                'cel' => $contactoCliente,
                'tipoCliente' => 'G'
            ]);
        } else {
            // Si el cliente existe y no tiene teléfono, o cambió, actualizarlo
            if (!empty($contactoCliente) && $clienteExistente['cel'] !== $contactoCliente) {
                $this->clienteModel->update($clienteExistente['idCliente'], [
                    'cel' => $contactoCliente
                ]);
            }
        }

        $nuevoEncargo = [
            'id_producto' => $idProducto,
            'nombre_cliente' => $nombreCliente,
            'contacto_cliente' => $contactoCliente,
            'cantidad' => (int)$cantidad,
            'anticipo' => (float)$anticipo,
            'estado' => $estado,
            'notas' => $notas
        ];

        if ($this->pedidoEncargadoModel->insert($nuevoEncargo)) {
            session()->setFlashdata('success', 'Pedido encargado registrado con éxito.');
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->index();
            }
            return redirect()->to(base_url('admin/encargos'))->with('success', 'Pedido encargado registrado con éxito.');
        }

        if ($this->request->getHeaderLine('HX-Request')) {
            return $this->response->setStatusCode(500)->setBody('Ocurrió un error al registrar el encargo.');
        }
        return redirect()->back()->withInput()->with('error', 'Ocurrió un error al registrar el encargo.');
    }

    public function editar($id)
    {
        $encargo = $this->pedidoEncargadoModel->find($id);

        if (!$encargo) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(404)->setBody('El encargo especificado no existe.');
            }
            return redirect()->to(base_url('admin/encargos'))->with('error', 'El encargo especificado no existe.');
        }

        $idProducto = $this->request->getPost('id_producto') ?: null;
        $productoCustom = trim((string)$this->request->getPost('producto_custom'));
        $nombreCliente = trim((string)$this->request->getPost('nombre_cliente'));
        $contactoCliente = trim((string)$this->request->getPost('contacto_cliente'));
        $cantidad = $this->request->getPost('cantidad');
        $anticipo = $this->request->getPost('anticipo') ?: 0.00;
        $notas = $this->request->getPost('notas') ?: null;
        $estado = $this->request->getPost('estado');

        if (empty($nombreCliente) || empty($estado)) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(400)->setBody('Por favor complete todos los campos obligatorios.');
            }
            return redirect()->back()->with('error', 'Por favor complete todos los campos obligatorios.');
        }

        if (empty($idProducto) && empty($productoCustom) && empty($notas)) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(400)->setBody('Debe seleccionar un producto del inventario, escribir uno personalizado o detallar el pedido en el campo de Notas.');
            }
            return redirect()->back()->with('error', 'Debe seleccionar un producto del inventario, escribir uno personalizado o detallar el pedido en el campo de Notas.');
        }

        // Si no hay producto de inventario pero hay uno custom, lo guardamos en notas
        if (empty($idProducto) && !empty($productoCustom)) {
            $notas = "[Producto: " . $productoCustom . "]" . ($notas !== null ? "\n" . $notas : "");
        }

        // Buscar o guardar en t_clientes
        $clienteExistente = $this->clienteModel->where('nombre', $nombreCliente)->first();
        if (!$clienteExistente) {
            $this->clienteModel->insert([
                'nombre' => $nombreCliente,
                'cel' => $contactoCliente,
                'tipoCliente' => 'G'
            ]);
        } else {
            // Actualizar teléfono si es provisto
            if (!empty($contactoCliente) && $clienteExistente['cel'] !== $contactoCliente) {
                $this->clienteModel->update($clienteExistente['idCliente'], [
                    'cel' => $contactoCliente
                ]);
            }
        }

        $datosActualizados = [
            'id_producto' => $idProducto,
            'nombre_cliente' => $nombreCliente,
            'contacto_cliente' => $contactoCliente,
            'cantidad' => (int)$cantidad,
            'anticipo' => (float)$anticipo,
            'estado' => $estado,
            'notas' => $notas
        ];

        if ($this->pedidoEncargadoModel->update($id, $datosActualizados)) {
            session()->setFlashdata('success', 'El pedido encargado se ha actualizado correctamente.');
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->index();
            }
            return redirect()->to(base_url('admin/encargos'))->with('success', 'El pedido encargado se ha actualizado correctamente.');
        }

        if ($this->request->getHeaderLine('HX-Request')) {
            return $this->response->setStatusCode(500)->setBody('Ocurrió un error al actualizar el encargo.');
        }
        return redirect()->back()->with('error', 'Ocurrió un error al actualizar el encargo.');
    }

    public function eliminar($id)
    {
        $encargo = $this->pedidoEncargadoModel->find($id);

        if (!$encargo) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(404)->setBody('El encargo no existe.');
            }
            return redirect()->to(base_url('admin/encargos'))->with('error', 'El encargo no existe.');
        }

        if ($this->pedidoEncargadoModel->delete($id)) {
            session()->setFlashdata('success', 'El registro de encargo ha sido eliminado.');
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->index();
            }
            return redirect()->to(base_url('admin/encargos'))->with('success', 'El registro de encargo ha sido eliminado.');
        }

        if ($this->request->getHeaderLine('HX-Request')) {
            return $this->response->setStatusCode(500)->setBody('Ocurrió un error al intentar eliminar el encargo.');
        }
        return redirect()->to(base_url('admin/encargos'))->with('error', 'Ocurrió un error al intentar eliminar el encargo.');
    }
}
