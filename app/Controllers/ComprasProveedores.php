<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CompraProveedorModel;
use App\Models\CompraProveedorDetalleModel;
use App\Models\ProductoModel;
use App\Models\CajaChicaModel;

class ComprasProveedores extends BaseController
{
    protected $compraModel;
    protected $detalleModel;
    protected $productoModel;
    protected $cajaModel;

    public function __construct()
    {
        $this->compraModel = new CompraProveedorModel();
        $this->detalleModel = new CompraProveedorDetalleModel();
        $this->productoModel = new ProductoModel();
        $this->cajaModel = new CajaChicaModel();
    }

    public function index()
    {
        $db = \Config\Database::connect();

        // --- Filtro de búsqueda por nombre de proveedor ---
        $busqueda = trim((string) ($this->request->getGet('busqueda') ?? ''));

        // --- Paginación ---
        $porPagina = 15;
        $paginaActual = (int) ($this->request->getGet('page') ?? 1);
        if ($paginaActual < 1)
            $paginaActual = 1;

        $totalRegistros = $this->compraModel->contarTodas($busqueda);
        $totalPaginas = (int) ceil($totalRegistros / $porPagina);
        if ($paginaActual > $totalPaginas && $totalPaginas > 0)
            $paginaActual = $totalPaginas;

        $offset = ($paginaActual - 1) * $porPagina;

        // Obtener historial de compras paginado (con filtro opcional por nombre de proveedor)
        $compras = $this->compraModel->obtenerPaginadoConProveedor($porPagina, $offset, $busqueda);

        // Obtener listado de proveedores para el modal de nueva compra
        $proveedores = $db->table('t_proveedores')->orderBy('nombre', 'ASC')->get()->getResultArray();

        // Obtener listado de productos de inventario para el selector
        $inventario = $this->productoModel->orderBy('descripcion', 'ASC')->findAll();

        $data = [
            'compras' => $compras,
            'proveedores' => $proveedores,
            'inventario' => $inventario,
            'titulo' => 'Compras a Proveedores',
            'paginaActual' => $paginaActual,
            'totalPaginas' => $totalPaginas,
            'totalRegistros' => $totalRegistros,
            'porPagina' => $porPagina,
            'busqueda' => $busqueda,
        ];

        return view('compras_proveedores/index', $data);
    }

    public function crear()
    {
        $idProveedor = $this->request->getPost('idProveedor');
        $fechaCompra = $this->request->getPost('fechaCompra') ?: date('Y-m-d');
        $descripcion = $this->request->getPost('descripcion');
        $envio_local_estimado = floatval($this->request->getPost('envio_local_estimado') ?: 0);
        $impuesto_importacion = floatval($this->request->getPost('impuesto_importacion') ?: 0);

        // Obtener lista de productos enviados en el form
        $items = $this->request->getPost('productos');

        if (empty($idProveedor) || empty($items) || !is_array($items)) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(400)->setBody('Por favor, seleccione un proveedor y agregue al menos un producto a la compra.');
            }
            return redirect()->back()->withInput()->with('error', 'Por favor, seleccione un proveedor y agregue al menos un producto a la compra.');
        }

        // 1. Calcular total base de productos
        $total_productos = 0;
        foreach ($items as $item) {
            $cantidad = intval($item['cantidad'] ?? 0);
            $precio_proveedor = floatval($item['precio_proveedor'] ?? 0);
            if ($cantidad <= 0 || $precio_proveedor < 0) {
                if ($this->request->getHeaderLine('HX-Request')) {
                    return $this->response->setStatusCode(400)->setBody('La cantidad debe ser mayor a 0 y el precio no puede ser negativo.');
                }
                return redirect()->back()->withInput()->with('error', 'La cantidad debe ser mayor a 0 y el precio no puede ser negativo.');
            }
            $total_productos += $precio_proveedor * $cantidad;
        }

        // 2. Calcular montos de la compra
        $total_pagado = $total_productos + $envio_local_estimado + $impuesto_importacion;
        $factor_pedido = $total_productos > 0 ? ($total_pagado / $total_productos) : 1.0;

        $db = \Config\Database::connect();
        $db->transStart();

        // 3. Guardar cabecera de la compra
        $idCompra = $this->compraModel->insert([
            'idProveedor' => $idProveedor,
            'fechaCompra' => $fechaCompra,
            'descripcion' => $descripcion,
            'montoTotal' => $total_pagado,
            'subtotal' => $total_productos,
            'envio_local_estimado' => $envio_local_estimado,
            'impuesto_importacion' => $impuesto_importacion,
            'total_pagado' => $total_pagado,
            'total_productos' => $total_productos,
            'factor_pedido' => $factor_pedido
        ]);

        if (!$idCompra) {
            $db->transRollback();
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(500)->setBody('No se pudo guardar la cabecera de la compra.');
            }
            return redirect()->back()->withInput()->with('error', 'No se pudo guardar la cabecera de la compra.');
        }

        // 4. Guardar detalles y actualizar stock/precios
        foreach ($items as $item) {
            $id_producto = !empty($item['id_producto']) ? intval($item['id_producto']) : null;
            $cantidad = intval($item['cantidad']);
            $precio_proveedor = floatval($item['precio_proveedor']);
            $margen = isset($item['margen']) && $item['margen'] !== '' ? floatval($item['margen']) : 0.0;

            // Prorrateo de gastos
            $costo_real_unit = $precio_proveedor * $factor_pedido;
            $costo_real_total = $costo_real_unit * $cantidad;

            // Sugerido y Venta Final
            $precio_venta_sugerido = $costo_real_unit * (1 + $margen / 100);
            $precio_venta_final = isset($item['precio_venta_final']) && $item['precio_venta_final'] !== ''
                ? floatval($item['precio_venta_final'])
                : $precio_venta_sugerido;

            $sku = $item['sku'] ?? '';
            $nombre = $item['nombre'] ?? '';

            if ($id_producto) {
                // Autocompletar con datos de la BD si faltan
                $prod = $this->productoModel->find($id_producto);
                if ($prod) {
                    if (empty($sku))
                        $sku = $prod['codigo_sku'];
                    if (empty($nombre))
                        $nombre = $prod['descripcion'];
                }
            } else {
                // Si es un producto libre pero el usuario pidió crearlo en el inventario
                if (!empty($item['registrar_nuevo_inventario']) && !empty($sku) && !empty($nombre)) {
                    $id_producto = $this->productoModel->insert([
                        'codigo_sku' => $sku,
                        'descripcion' => $nombre,
                        'precio' => $precio_venta_final,
                        'stock' => $cantidad,
                        'id_categoria' => null
                    ]);
                }
            }

            // Guardar registro de detalle
            $this->detalleModel->insert([
                'idCompraProveedor' => $idCompra,
                'id_producto' => $id_producto,
                'sku' => $sku,
                'nombre' => $nombre,
                'cantidad' => $cantidad,
                'precio_proveedor' => $precio_proveedor,
                'costo_real_unit' => $costo_real_unit,
                'costo_real_total' => $costo_real_total,
                'margen' => $margen,
                'precio_venta_sugerido' => $precio_venta_sugerido,
                'precio_venta_final' => $precio_venta_final
            ]);

            // Si está vinculado a un producto y NO se acaba de crear (para evitar sumar doble stock)
            if ($id_producto && empty($item['registrar_nuevo_inventario'])) {
                $prod = $this->productoModel->find($id_producto);
                if ($prod) {
                    $nuevoStock = $prod['stock'] + $cantidad;
                    $updateData = ['stock' => $nuevoStock];

                    if (!empty($item['actualizar_inventario'])) {
                        $updateData['precio'] = $precio_venta_final;
                    }

                    $this->productoModel->update($id_producto, $updateData);
                }
            }
        }

        // 5. Registrar el Egreso en Caja Chica
        $this->cajaModel->insert([
            'fecha' => $fechaCompra,
            'descripcion' => "Compra a proveedor ID: {$idCompra}",
            'monto' => $total_pagado,
            'tipo' => 'Egreso'
        ]);

        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(500)->setBody('Ocurrió un error al procesar la compra en la base de datos.');
            }
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al procesar la compra en la base de datos.');
        }

        session()->setFlashdata('success', 'Compra registrada exitosamente, stock actualizado e importe cargado a Caja Chica.');

        if ($this->request->getHeaderLine('HX-Request')) {
            return $this->index();
        }

        return redirect()->to(base_url('admin/compras'))->with('success', 'Compra registrada exitosamente, stock actualizado e importe cargado a Caja Chica.');
    }

    public function detalle(int $id)
    {
        $compra = $this->compraModel->select('t_compras_proveedor.*, t_proveedores.nombre as nombre_proveedor')
            ->join('t_proveedores', 't_proveedores.idProveedor = t_compras_proveedor.idProveedor', 'left')
            ->find($id);

        if (!$compra) {
            return '<div class="alert alert-danger p-3">La compra seleccionada no existe.</div>';
        }

        $detalles = $this->detalleModel->obtenerPorCompra($id);

        $data = [
            'compra' => $compra,
            'detalles' => $detalles
        ];

        return view('compras_proveedores/detalle', $data);
    }

    public function eliminar(int $id)
    {
        $compra = $this->compraModel->find($id);

        if (!$compra) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(404)->setBody('La compra no existe.');
            }
            return redirect()->to(base_url('admin/compras'))->with('error', 'La compra no existe.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Obtener detalles de compra para revertir stock del inventario
        $detalles = $this->detalleModel->obtenerPorCompra($id);
        foreach ($detalles as $d) {
            if ($d['id_producto']) {
                $prod = $this->productoModel->find($d['id_producto']);
                if ($prod) {
                    // Restamos la cantidad comprada, evitando que quede en negativo
                    $nuevoStock = max(0, $prod['stock'] - $d['cantidad']);
                    $this->productoModel->update($d['id_producto'], ['stock' => $nuevoStock]);
                }
            }
        }

        // 2. Eliminar el registro en la Caja Chica
        $descripcionCaja = "Compra a proveedor ID: {$id}";
        $this->cajaModel->where('descripcion', $descripcionCaja)
            ->where('tipo', 'Egreso')
            ->delete();

        // 3. Eliminar detalles y cabecera de la compra
        $this->detalleModel->where('idCompraProveedor', $id)->delete();
        $this->compraModel->delete($id);

        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(500)->setBody('No se pudo eliminar la compra.');
            }
            return redirect()->to(base_url('admin/compras'))->with('error', 'No se pudo eliminar la compra.');
        }

        session()->setFlashdata('success', 'Compra eliminada exitosamente. Se revirtió el stock de los productos y se removió el egreso en Caja Chica.');

        if ($this->request->getHeaderLine('HX-Request')) {
            return $this->index();
        }

        return redirect()->to(base_url('admin/compras'))->with('success', 'Compra eliminada exitosamente. Se revirtió el stock de los productos y se removió el egreso en Caja Chica.');
    }

    public function editar(int $id)
    {
        $compra = $this->compraModel->find($id);

        if (!$compra) {
            return '<div class="alert alert-danger p-3">La compra seleccionada no existe.</div>';
        }

        $db = \Config\Database::connect();
        
        // Obtener el nombre del proveedor
        $proveedorActual = $db->table('t_proveedores')->where('idProveedor', $compra['idProveedor'])->get()->getRowArray();
        $compra['nombre_proveedor'] = $proveedorActual ? $proveedorActual['nombre'] : '';

        // Obtener los detalles de la compra
        $detalles = $this->detalleModel->obtenerPorCompra($id);

        // Obtener listado de proveedores para el modal de edición
        $proveedores = $db->table('t_proveedores')->orderBy('nombre', 'ASC')->get()->getResultArray();

        // Obtener listado de productos de inventario para el selector
        $inventario = $this->productoModel->orderBy('descripcion', 'ASC')->findAll();

        $data = [
            'compra' => $compra,
            'detalles' => $detalles,
            'proveedores' => $proveedores,
            'inventario' => $inventario
        ];

        return view('compras_proveedores/editar', $data);
    }

    public function actualizar(int $id)
    {
        $compraExistente = $this->compraModel->find($id);
        if (!$compraExistente) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(404)->setBody('La compra no existe.');
            }
            return redirect()->back()->with('error', 'La compra no existe.');
        }

        $idProveedor = $this->request->getPost('idProveedor');
        $fechaCompra = $this->request->getPost('fechaCompra') ?: date('Y-m-d');
        $descripcion = $this->request->getPost('descripcion');
        $envio_local_estimado = floatval($this->request->getPost('envio_local_estimado') ?: 0);
        $impuesto_importacion = floatval($this->request->getPost('impuesto_importacion') ?: 0);

        // Obtener lista de productos enviados en el form
        $items = $this->request->getPost('productos');

        if (empty($idProveedor) || empty($items) || !is_array($items)) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(400)->setBody('Por favor, seleccione un proveedor y agregue al menos un producto a la compra.');
            }
            return redirect()->back()->withInput()->with('error', 'Por favor, seleccione un proveedor y agregue al menos un producto a la compra.');
        }

        // 1. Calcular total base de productos
        $total_productos = 0;
        foreach ($items as $item) {
            $cantidad = intval($item['cantidad'] ?? 0);
            $precio_proveedor = floatval($item['precio_proveedor'] ?? 0);
            if ($cantidad <= 0 || $precio_proveedor < 0) {
                if ($this->request->getHeaderLine('HX-Request')) {
                    return $this->response->setStatusCode(400)->setBody('La cantidad debe ser mayor a 0 y el precio no puede ser negativo.');
                }
                return redirect()->back()->withInput()->with('error', 'La cantidad debe ser mayor a 0 y el precio no puede ser negativo.');
            }
            $total_productos += $precio_proveedor * $cantidad;
        }

        // 2. Calcular montos de la compra
        $total_pagado = $total_productos + $envio_local_estimado + $impuesto_importacion;
        $factor_pedido = $total_productos > 0 ? ($total_pagado / $total_productos) : 1.0;

        $db = \Config\Database::connect();
        $db->transStart();

        // 3. Revertir stock del inventario correspondiente a la compra vieja antes de aplicar nuevos valores
        $detallesViejos = $this->detalleModel->obtenerPorCompra($id);
        foreach ($detallesViejos as $d) {
            if ($d['id_producto']) {
                $prod = $this->productoModel->find($d['id_producto']);
                if ($prod) {
                    $nuevoStock = max(0, $prod['stock'] - $d['cantidad']);
                    $this->productoModel->update($d['id_producto'], ['stock' => $nuevoStock]);
                }
            }
        }

        // 4. Actualizar cabecera de la compra
        $this->compraModel->update($id, [
            'idProveedor' => $idProveedor,
            'fechaCompra' => $fechaCompra,
            'descripcion' => $descripcion,
            'montoTotal' => $total_pagado,
            'subtotal' => $total_productos,
            'envio_local_estimado' => $envio_local_estimado,
            'impuesto_importacion' => $impuesto_importacion,
            'total_pagado' => $total_pagado,
            'total_productos' => $total_productos,
            'factor_pedido' => $factor_pedido
        ]);

        // 5. Eliminar detalles viejos
        $this->detalleModel->where('idCompraProveedor', $id)->delete();

        // 6. Guardar nuevos detalles y actualizar stock/precios
        foreach ($items as $item) {
            $id_producto = !empty($item['id_producto']) ? intval($item['id_producto']) : null;
            $cantidad = intval($item['cantidad']);
            $precio_proveedor = floatval($item['precio_proveedor']);
            $margen = isset($item['margen']) && $item['margen'] !== '' ? floatval($item['margen']) : 0.0;

            // Prorrateo de gastos
            $costo_real_unit = $precio_proveedor * $factor_pedido;
            $costo_real_total = $costo_real_unit * $cantidad;

            // Sugerido y Venta Final
            $precio_venta_sugerido = $costo_real_unit * (1 + $margen / 100);
            $precio_venta_final = isset($item['precio_venta_final']) && $item['precio_venta_final'] !== ''
                ? floatval($item['precio_venta_final'])
                : $precio_venta_sugerido;

            $sku = $item['sku'] ?? '';
            $nombre = $item['nombre'] ?? '';

            if ($id_producto) {
                // Autocompletar con datos de la BD si faltan
                $prod = $this->productoModel->find($id_producto);
                if ($prod) {
                    if (empty($sku))
                        $sku = $prod['codigo_sku'];
                    if (empty($nombre))
                        $nombre = $prod['descripcion'];
                }
            } else {
                // Si es un producto libre pero el usuario pidió crearlo en el inventario
                if (!empty($item['registrar_nuevo_inventario']) && !empty($sku) && !empty($nombre)) {
                    $id_producto = $this->productoModel->insert([
                        'codigo_sku' => $sku,
                        'descripcion' => $nombre,
                        'precio' => $precio_venta_final,
                        'stock' => $cantidad,
                        'id_categoria' => null
                    ]);
                }
            }

            // Guardar registro de detalle
            $this->detalleModel->insert([
                'idCompraProveedor' => $id,
                'id_producto' => $id_producto,
                'sku' => $sku,
                'nombre' => $nombre,
                'cantidad' => $cantidad,
                'precio_proveedor' => $precio_proveedor,
                'costo_real_unit' => $costo_real_unit,
                'costo_real_total' => $costo_real_total,
                'margen' => $margen,
                'precio_venta_sugerido' => $precio_venta_sugerido,
                'precio_venta_final' => $precio_venta_final
            ]);

            // Si está vinculado a un producto y NO se acaba de crear (para evitar sumar doble stock)
            if ($id_producto && empty($item['registrar_nuevo_inventario'])) {
                $prod = $this->productoModel->find($id_producto);
                if ($prod) {
                    $nuevoStock = $prod['stock'] + $cantidad;
                    $updateData = ['stock' => $nuevoStock];

                    if (!empty($item['actualizar_inventario'])) {
                        $updateData['precio'] = $precio_venta_final;
                    }

                    $this->productoModel->update($id_producto, $updateData);
                }
            }
        }

        // 7. Actualizar el Egreso en Caja Chica
        $descripcionCaja = "Compra a proveedor ID: {$id}";
        $registroCaja = $this->cajaModel->where('descripcion', $descripcionCaja)
            ->where('tipo', 'Egreso')
            ->first();

        if ($registroCaja) {
            $this->cajaModel->update($registroCaja['idMovimiento'], [
                'fecha' => $fechaCompra,
                'monto' => $total_pagado
            ]);
        } else {
            // Si por alguna razón no existía, lo creamos
            $this->cajaModel->insert([
                'fecha' => $fechaCompra,
                'descripcion' => $descripcionCaja,
                'monto' => $total_pagado,
                'tipo' => 'Egreso'
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(500)->setBody('Ocurrió un error al actualizar la compra en la base de datos.');
            }
            return redirect()->back()->withInput()->with('error', 'Ocurrió un error al actualizar la compra en la base de datos.');
        }

        session()->setFlashdata('success', 'Compra registrada exitosamente, stock de productos e importe en Caja Chica ajustados.');

        if ($this->request->getHeaderLine('HX-Request')) {
            return $this->index();
        }

        return redirect()->to(base_url('admin/compras'))->with('success', 'Compra registrada exitosamente, stock de productos e importe en Caja Chica ajustados.');
    }
}
