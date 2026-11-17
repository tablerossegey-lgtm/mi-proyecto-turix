<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\CuponCalculator;
use App\Models\ClienteModel;
use App\Models\CuentaClienteModel;
use App\Models\CuponBitacoraModel;
use App\Models\CuponModel;
use App\Models\CuponUsoModel;
use App\Models\ProductoModel;

class CuentasClientes extends BaseController
{
    protected $clienteModel;
    protected $cuentaClienteModel;
    protected $productoModel;
    protected CuponModel $cuponModel;
    protected CuponUsoModel $cuponUsoModel;
    protected CuponBitacoraModel $bitacoraModel;
    protected CuponCalculator $cuponCalculator;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
        $this->cuentaClienteModel = new CuentaClienteModel();
        $this->productoModel = new ProductoModel();
        $this->cuponModel = new CuponModel();
        $this->cuponUsoModel = new CuponUsoModel();
        $this->bitacoraModel = new CuponBitacoraModel();
        $this->cuponCalculator = new CuponCalculator();
    }

    public function index()
    {
        $q = $this->request->getVar('q');
        $filter = $this->request->getVar('filter') ?: 'todos';
        
        // Obtener clientes con balances sumados y contadores de entrega
        $db = \Config\Database::connect();
        $builder = $db->table('t_clientes c')
            ->select('c.*, 
                ((SELECT COALESCE(SUM(totalProduc), 0) FROM t_cuenta_cliente WHERE idCliente = c.idCliente) - 
                 (SELECT COALESCE(SUM(abono), 0) FROM t_abono_cliente WHERE idCliente = c.idCliente)) AS total_pendiente,
                (SELECT COALESCE(SUM(abono), 0) FROM t_abono_cliente WHERE idCliente = c.idCliente) AS total_pagado,
                (SELECT COALESCE(SUM(cantidad), 0) FROM t_cuenta_cliente WHERE idCliente = c.idCliente AND estatus_entrega = 0) AS cant_por_empacar,
                (SELECT COALESCE(SUM(cantidad), 0) FROM t_cuenta_cliente WHERE idCliente = c.idCliente AND (estatus_entrega = 0 OR estatus_entrega = 1)) AS cant_por_entregar')
            ->orderBy('c.nombre', 'ASC');

        if (!empty($q)) {
            $builder->groupStart()
                    ->like('c.nombre', $q)
                    ->orLike('c.cel', $q)
                    ->groupEnd();
        }

        if ($filter === 'entrega') {
            $builder->having('cant_por_entregar > 0');
        } elseif ($filter === 'empacar') {
            $builder->having('cant_por_empacar > 0');
        }

        $clientes = $builder->get()->getResultArray();

        $data = [
            'clientes' => $clientes,
            'q' => $q,
            'filter' => $filter,
            'titulo' => 'Gestión de Cuentas de Clientes'
        ];

        return view('cuentas_clientes/index', $data);
    }

    public function obtenerCompras($idCliente)
    {
        $idClienteCast = (int)$idCliente;
        $this->recalcularEstatusCompras($idClienteCast);
        
        $data = $this->obtenerDatosCliente($idClienteCast);
        if (!$data) {
            return $this->response->setStatusCode(404)->setBody('Cliente no encontrado');
        }

        return view('cuentas_clientes/_tabla_compras', $data);
    }

    public function crear()
    {
        if ($this->request->isAJAX() || $this->request->getHeaderLine('HX-Request')) {
            $json = $this->request->getJSON(true);
            $idCliente = (int)($json['id_cliente'] ?? 0);
            $fechaCompra = $json['fecha_compra'] ?: date('Y-m-d');
            $estatusCompra = $json['estatus_compra'] ?: '0';
            $productosData = $json['productos'] ?? [];
            $gastosEnvio = round(max(0.0, (float)($json['gastos_envio'] ?? 0)), 2);
            $beneficio = is_array($json['beneficio'] ?? null) ? $json['beneficio'] : ['modo' => 'ninguno'];

            if (empty($idCliente) || empty($productosData)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Por favor, completa los campos correctamente y agrega al menos un producto.']);
            }

            $db = \Config\Database::connect();
            $db->transBegin();

            $subtotalElegible = 0.00;
            $itemsPreparados = [];
            $itemsTicket = [];

            foreach ($productosData as $prod) {
                $tipoCompra = $prod['tipo_compra'] ?? 'inventario';
                $idInventario = (int)($prod['id_inventario'] ?? 0);
                $cantidad = (int)($prod['cantidad'] ?? 0);
                $precioUnit = round((float)($prod['precio_unit'] ?? 0.00), 2);
                $descontarStock = isset($prod['descontar_stock']) ? (bool)$prod['descontar_stock'] : true;
                $descProducto = trim((string)($prod['desc_producto'] ?? ''));

                if ($cantidad <= 0 || $precioUnit < 0) {
                    $db->transRollback();
                    return $this->response->setJSON(['success' => false, 'message' => 'Valores de cantidad o precio incorrectos en uno de los productos.']);
                }

                if ($tipoCompra === 'inventario') {
                    $producto = $this->productoModel->find($idInventario);
                    if (!$producto) {
                        $db->transRollback();
                        return $this->response->setJSON(['success' => false, 'message' => 'Uno de los productos de inventario no es válido.']);
                    }
                    $descProducto = $producto['descripcion'];

                    if ($descontarStock) {
                        $nuevoStock = $producto['stock'] - $cantidad;
                        $this->productoModel->update($idInventario, ['stock' => max(0, $nuevoStock)]);
                    }
                } else {
                    if ($descProducto === '') {
                        $db->transRollback();
                        return $this->response->setJSON(['success' => false, 'message' => 'Falta la descripción para uno de los productos libres.']);
                    }
                }

                $importeOriginal = round($cantidad * $precioUnit, 2);
                $subtotalElegible = round($subtotalElegible + $importeOriginal, 2);

                $itemsPreparados[] = [
                    'idCliente'        => $idCliente,
                    'fechaCompra'      => $fechaCompra,
                    'cantidad'         => $cantidad,
                    'descProducto'     => $descProducto,
                    'precioUnit'       => $precioUnit,
                    'importe_original' => $importeOriginal,
                    'estatusCompra'    => $estatusCompra,
                    'idInventario'     => $idInventario,
                    'estatus_entrega'  => 0,
                ];

                $itemsTicket[] = "- {$cantidad} {$descProducto}: $" . number_format($importeOriginal, 2);
            }

            // Procesar cupón o descuento manual (máximo uno por operación)
            $modoBeneficio = (string)($beneficio['modo'] ?? 'ninguno');
            $descuentoTotal = 0.00;
            $idCuponAplicado = null;
            $codigoCuponAplicado = null;
            $tipoDescuentoAplicado = null;
            $motivoDescuento = null;
            $cuponBloqueado = null;

            if ($modoBeneficio === 'cupon') {
                $idCuponReq = (int)($beneficio['id_cupon'] ?? 0);
                if ($idCuponReq <= 0) {
                    $db->transRollback();
                    return $this->response->setJSON(['success' => false, 'message' => 'Selecciona un cupón válido para aplicar.']);
                }

                // Bloqueo pesimista FOR UPDATE para evitar uso simultáneo/duplicado
                $cuponBloqueado = $db->query('SELECT * FROM t_cupones WHERE id = ? FOR UPDATE', [$idCuponReq])->getRowArray();
                if (!$cuponBloqueado) {
                    $db->transRollback();
                    return $this->response->setJSON(['success' => false, 'message' => 'El cupón seleccionado no existe.']);
                }

                $eval = $this->cuponCalculator->calcularDescuentoCupon($cuponBloqueado, $subtotalElegible, $idCliente, date('Y-m-d'));
                if (!$eval['valid']) {
                    $db->transRollback();
                    return $this->response->setJSON(['success' => false, 'message' => $eval['message']]);
                }

                // Incremento atómico verificando límite de usos
                $db->query(
                    'UPDATE t_cupones SET usos_realizados = usos_realizados + 1 WHERE id = ? AND usos_realizados < limite_usos',
                    [$idCuponReq]
                );
                if ($db->affectedRows() === 0) {
                    $db->transRollback();
                    return $this->response->setJSON(['success' => false, 'message' => 'Este cupón ya fue consumido por otra operación simultánea.']);
                }

                $cuponBloqueado['usos_realizados'] = (int)$cuponBloqueado['usos_realizados'] + 1;
                $nuevoEstadoCupon = $this->cuponCalculator->resolverEstado($cuponBloqueado, date('Y-m-d'));
                $this->cuponModel->update($idCuponReq, ['estado' => $nuevoEstadoCupon]);

                $descuentoTotal = $eval['descuento'];
                $idCuponAplicado = $idCuponReq;
                $codigoCuponAplicado = $cuponBloqueado['codigo'];
                $tipoDescuentoAplicado = 'cupon';
                $motivoDescuento = $cuponBloqueado['concepto'];
            } elseif ($modoBeneficio === 'manual') {
                $tipoManual = (string)($beneficio['tipo_manual'] ?? 'fijo');
                $valorManual = (float)($beneficio['valor_manual'] ?? 0);
                $motivoManual = trim((string)($beneficio['motivo_manual'] ?? ''));

                $evalManual = $this->cuponCalculator->calcularDescuentoManual($tipoManual, $valorManual, $motivoManual, $subtotalElegible);
                if (!$evalManual['valid']) {
                    $db->transRollback();
                    return $this->response->setJSON(['success' => false, 'message' => $evalManual['message']]);
                }

                $descuentoTotal = $evalManual['descuento'];
                $tipoDescuentoAplicado = $tipoManual === 'porcentaje' ? 'manual_porcentaje' : 'manual_fijo';
                $motivoDescuento = $motivoManual;
            }

            $folioCompra = 'CMP-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $itemsProrrateados = $this->cuponCalculator->prorratearDescuentoEnItems($itemsPreparados, $descuentoTotal);

            $totalNetoProductos = 0.00;
            $firstIdCompra = null;

            foreach ($itemsProrrateados as $item) {
                $nuevaCompra = [
                    'idCliente'               => $item['idCliente'],
                    'fechaCompra'             => $item['fechaCompra'],
                    'cantidad'                => $item['cantidad'],
                    'descProducto'            => $item['descProducto'],
                    'precioUnit'              => $item['precioUnit'],
                    'totalProduc'             => $item['total_neto'],
                    'estatusCompra'           => $item['estatusCompra'],
                    'idInventario'            => $item['idInventario'],
                    'estatus_entrega'         => $item['estatus_entrega'],
                    'folio_compra'            => $folioCompra,
                    'importe_original'        => $item['importe_original'],
                    'descuento_aplicado'      => $item['descuento_aplicado'],
                    'id_cupon'                => $idCuponAplicado,
                    'codigo_cupon'            => $codigoCuponAplicado,
                    'tipo_descuento_aplicado' => $tipoDescuentoAplicado,
                    'motivo_descuento'        => $motivoDescuento,
                    'gastos_envio'            => 0.00,
                ];

                $this->cuentaClienteModel->insert($nuevaCompra);
                if ($firstIdCompra === null) {
                    $firstIdCompra = (int)$this->cuentaClienteModel->getInsertID();
                }
                $totalNetoProductos = round($totalNetoProductos + $item['total_neto'], 2);
            }

            // Gastos de envío (no reciben descuento)
            if ($gastosEnvio > 0) {
                $this->cuentaClienteModel->insert([
                    'idCliente'               => $idCliente,
                    'fechaCompra'             => $fechaCompra,
                    'cantidad'                => 1,
                    'descProducto'            => 'Gastos de envío / adicionales',
                    'precioUnit'              => $gastosEnvio,
                    'totalProduc'             => $gastosEnvio,
                    'estatusCompra'           => $estatusCompra,
                    'idInventario'            => 0,
                    'estatus_entrega'         => 0,
                    'folio_compra'            => $folioCompra,
                    'importe_original'        => $gastosEnvio,
                    'descuento_aplicado'      => 0.00,
                    'id_cupon'                => null,
                    'codigo_cupon'            => null,
                    'tipo_descuento_aplicado' => null,
                    'motivo_descuento'        => null,
                    'gastos_envio'            => $gastosEnvio,
                ]);
                $itemsTicket[] = "- Gastos de envío: $" . number_format($gastosEnvio, 2);
            }

            $totalCompraAcumulado = round($totalNetoProductos + $gastosEnvio, 2);

            // Registrar uso de cupón y bitácora
            $usuarioId = session()->get('id') ? (int)session()->get('id') : null;
            $usuarioNombre = session()->get('nombre_completo') ?: session()->get('nombre_usuario') ?: 'Sistema';

            if ($idCuponAplicado !== null) {
                $this->cuponUsoModel->insert([
                    'id_cupon'           => $idCuponAplicado,
                    'id_cliente'         => $idCliente,
                    'folio_compra'       => $folioCompra,
                    'id_compra'          => $firstIdCompra,
                    'fecha_uso'          => date('Y-m-d H:i:s'),
                    'subtotal_original'  => $subtotalElegible,
                    'descuento_aplicado' => $descuentoTotal,
                    'total_neto'         => $totalCompraAcumulado,
                    'estado'             => 'aplicado',
                    'usuario_id'         => $usuarioId,
                    'usuario_nombre'     => $usuarioNombre,
                ]);

                $this->bitacoraModel->registrar([
                    'id_cupon'        => $idCuponAplicado,
                    'id_cliente'      => $idCliente,
                    'accion'          => 'aplicacion',
                    'folio_compra'    => $folioCompra,
                    'id_compra'       => $firstIdCompra,
                    'monto_descuento' => $descuentoTotal,
                    'detalle'         => sprintf(
                        'Cupón %s aplicado a compra %s. Subtotal: $%s, Descuento: -$%s, Total neto: $%s.',
                        $codigoCuponAplicado,
                        $folioCompra,
                        number_format($subtotalElegible, 2),
                        number_format($descuentoTotal, 2),
                        number_format($totalCompraAcumulado, 2)
                    ),
                ]);
            } elseif ($modoBeneficio === 'manual' && $descuentoTotal > 0) {
                $this->bitacoraModel->registrar([
                    'id_cupon'        => null,
                    'id_cliente'      => $idCliente,
                    'accion'          => 'descuento_manual',
                    'folio_compra'    => $folioCompra,
                    'id_compra'       => $firstIdCompra,
                    'monto_descuento' => $descuentoTotal,
                    'detalle'         => sprintf(
                        'Descuento manual aplicado a compra %s. Motivo: %s. Subtotal: $%s, Descuento: -$%s, Total neto: $%s.',
                        $folioCompra,
                        $motivoDescuento,
                        number_format($subtotalElegible, 2),
                        number_format($descuentoTotal, 2),
                        number_format($totalCompraAcumulado, 2)
                    ),
                ]);
            }

            // Si es pagado, registrar en caja únicamente el importe neto efectivamente cobrado
            if ($estatusCompra == '1' && $totalCompraAcumulado > 0) {
                $this->registrarPagoCaja($idCliente, $totalCompraAcumulado, $fechaCompra);
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON(['success' => false, 'message' => 'No se pudo registrar la compra debido a un error de base de datos.']);
            }
            $db->transCommit();

            $this->recalcularEstatusCompras($idCliente);

            // Obtener detalles del cliente para el saldo y celular
            $cliente = $this->clienteModel->find($idCliente);

            // Calcular balances del cliente para el Ticket
            $compras = $this->cuentaClienteModel->where('idCliente', $idCliente)->findAll();
            $abonos = $db->table('t_abono_cliente')->where('idCliente', $idCliente)->get()->getResultArray();

            $totalComprasHistoricas = 0;
            foreach ($compras as $c) {
                $totalComprasHistoricas += $c['totalProduc'];
            }

            $totalAbonosHistoricos = 0;
            foreach ($abonos as $a) {
                $totalAbonosHistoricos += $a['abono'];
            }

            $saldoPendienteActual = $totalComprasHistoricas - $totalAbonosHistoricos;

            if ($estatusCompra == '0') {
                $saldoAnterior = $saldoPendienteActual - $totalCompraAcumulado;
                $nuevoTotal = $saldoPendienteActual;
            } else {
                $saldoAnterior = $saldoPendienteActual;
                $nuevoTotal = $saldoPendienteActual;
            }

            // Formatear Ticket Digital
            $fechaTicketCorta = date('d/m/y', strtotime($fechaCompra));
            $fechaTicketLarga = date('d/m/Y', strtotime($fechaCompra));

            $ticketText = "Te comparto la actualización de tu cuenta, junto con el detalle de tu compra.\n\n";
            $ticketText .= "*TICKET DIGITAL*\n";
            $ticketText .= "*Compra del día ({$fechaTicketCorta})*\n";
            $ticketText .= implode("\n", $itemsTicket) . "\n\n";
            $ticketText .= "*Subtotal de productos:* $" . number_format($subtotalElegible, 2) . "\n";
            if ($descuentoTotal > 0) {
                $etiquetaDesc = $codigoCuponAplicado ? "Cupón {$codigoCuponAplicado}" : "Descuento aplicado";
                $ticketText .= "*{$etiquetaDesc}:* -$" . number_format($descuentoTotal, 2) . "\n";
            }
            if ($gastosEnvio > 0) {
                $ticketText .= "*Gastos de envío:* $" . number_format($gastosEnvio, 2) . "\n";
            }
            $ticketText .= "*Total de la compra:* $" . number_format($totalCompraAcumulado, 2) . "\n\n";
            $ticketText .= "💰 Estado de cuenta actualizado\n\n";
            $ticketText .= "*Saldo Anterior:* $" . number_format($saldoAnterior, 2) . "\n";
            $ticketText .= "*Compra del {$fechaTicketLarga}:* $" . number_format($totalCompraAcumulado, 2) . "\n";
            $ticketText .= "*Saldo total pendiente:* $" . number_format($nuevoTotal, 2);

            return $this->response->setJSON([
                'success' => true,
                'folio'   => $folioCompra,
                'ticket'  => $ticketText,
                'cel'     => $cliente['cel'] ?? '',
                'totalPendiente' => number_format($saldoPendienteActual, 2),
                'message' => 'Compra registrada con éxito.'
            ]);
        }

        // Fallback para solicitudes no AJAX (comportamiento de un solo producto tradicional)
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
            'idInventario'  => $idInventario,
            'estatus_entrega' => 0
        ];

        if ($this->cuentaClienteModel->insert($nuevaCompra)) {
            if ($estatusCompra == '1') {
                $this->registrarPagoCaja($idCliente, $totalProduc, $fechaCompra);
            }
            $this->recalcularEstatusCompras($idCliente);
            return redirect()->to(base_url('admin/cuentas'))->with('success', 'Compra registrada con éxito.');
        }

        return redirect()->back()->with('error', 'No se pudo registrar la compra.');
    }

    public function editar($idCompra)
    {
        $compra = $this->cuentaClienteModel->find($idCompra);
        if (!$compra) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(404)->setBody('Compra no encontrada.');
            }
            return redirect()->back()->with('error', 'Compra no encontrada.');
        }

        $cantidadNueva = (int)$this->request->getPost('cantidad');
        $precioUnitNuevo = round((float)$this->request->getPost('precio_unit'), 2);
        $fechaCompra = $this->request->getPost('fecha_compra') ?: date('Y-m-d');
        $estatusCompra = $this->request->getPost('estatus_compra') ?: '0';
        $descontarStock = $this->request->getPost('descontar_stock') === '1';

        if ($cantidadNueva <= 0 || $precioUnitNuevo < 0) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(400)->setBody('Valores de cantidad o precio incorrectos.');
            }
            return redirect()->back()->with('error', 'Valores de cantidad o precio incorrectos.');
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        $idCliente = (int)$compra['idCliente'];
        $importeOriginalNuevo = round($cantidadNueva * $precioUnitNuevo, 2);
        $descuentoItem = round((float)($compra['descuento_aplicado'] ?? 0.00), 2);
        $idCupon = !empty($compra['id_cupon']) ? (int)$compra['id_cupon'] : null;
        $folioCompra = $compra['folio_compra'] ?? null;

        // Validar consistencia con descuentos / cupones aplicados
        if ($idCupon !== null) {
            $cupon = $db->query('SELECT * FROM t_cupones WHERE id = ? FOR UPDATE', [$idCupon])->getRowArray();
            if ($cupon) {
                // Calcular subtotal elegible de toda la operación (si tiene más ítems en el mismo folio)
                $otrosItems = [];
                $subtotalOtros = 0.00;
                if (!empty($folioCompra)) {
                    $otrosItems = $this->cuentaClienteModel
                        ->where('folio_compra', $folioCompra)
                        ->where('idCompra !=', $idCompra)
                        ->where('gastos_envio', 0)
                        ->findAll();
                    foreach ($otrosItems as $oi) {
                        $origOi = $oi['importe_original'] !== null ? (float)$oi['importe_original'] : (float)$oi['totalProduc'];
                        $subtotalOtros = round($subtotalOtros + $origOi, 2);
                    }
                }

                $subtotalOperacion = round($subtotalOtros + $importeOriginalNuevo, 2);

                // Evaluar reglas del cupón simulando que este uso aún está disponible
                $cuponParaEval = $cupon;
                $cuponParaEval['usos_realizados'] = max(0, (int)$cupon['usos_realizados'] - 1);
                if ($cuponParaEval['estado'] === CuponCalculator::ESTADO_UTILIZADO) {
                    $cuponParaEval['estado'] = CuponCalculator::ESTADO_DISPONIBLE;
                }

                $eval = $this->cuponCalculator->calcularDescuentoCupon(
                    $cuponParaEval,
                    $subtotalOperacion,
                    $idCliente,
                    $compra['fechaCompra'] ?? date('Y-m-d')
                );

                if (!$eval['valid']) {
                    $db->transRollback();
                    if ($this->request->getHeaderLine('HX-Request')) {
                        return $this->response->setStatusCode(400)->setBody('No se puede editar la compra: ' . $eval['message']);
                    }
                    return redirect()->back()->with('error', 'No se puede editar la compra: ' . $eval['message']);
                }

                $nuevoDescuentoOperacion = $eval['descuento'];

                // Prorratear nuevo descuento entre todos los ítems de la operación
                $itemsOp = [['idCompra' => (int)$idCompra, 'importe_original' => $importeOriginalNuevo]];
                foreach ($otrosItems as $oi) {
                    $origOi = $oi['importe_original'] !== null ? (float)$oi['importe_original'] : (float)$oi['totalProduc'];
                    $itemsOp[] = ['idCompra' => (int)$oi['idCompra'], 'importe_original' => $origOi];
                }
                $prorrateados = $this->cuponCalculator->prorratearDescuentoEnItems($itemsOp, $nuevoDescuentoOperacion);
                $descuentoItem = $prorrateados[0]['descuento_aplicado'];

                // Actualizar los otros ítems de la misma operación si existen
                for ($i = 1; $i < count($prorrateados); $i++) {
                    $this->cuentaClienteModel->update($prorrateados[$i]['idCompra'], [
                        'descuento_aplicado' => $prorrateados[$i]['descuento_aplicado'],
                        'totalProduc'        => $prorrateados[$i]['total_neto'],
                    ]);
                }

                // Actualizar registro de uso del cupón
                if (!empty($folioCompra)) {
                    $db->table('t_cupones_usos')
                        ->where('folio_compra', $folioCompra)
                        ->where('estado', 'aplicado')
                        ->update([
                            'subtotal_original'  => $subtotalOperacion,
                            'descuento_aplicado' => $nuevoDescuentoOperacion,
                            'total_neto'         => round($subtotalOperacion - $nuevoDescuentoOperacion, 2),
                        ]);
                }

                $this->bitacoraModel->registrar([
                    'id_cupon'        => $idCupon,
                    'id_cliente'      => $idCliente,
                    'accion'          => 'edicion',
                    'folio_compra'    => $folioCompra,
                    'id_compra'       => (int)$idCompra,
                    'monto_descuento' => $nuevoDescuentoOperacion,
                    'detalle'         => sprintf(
                        'Compra #%d editada con cupón %s. Nuevo subtotal op.: $%s, Descuento: -$%s.',
                        $idCompra,
                        $cupon['codigo'],
                        number_format($subtotalOperacion, 2),
                        number_format($nuevoDescuentoOperacion, 2)
                    ),
                ]);
            }
        } elseif ($descuentoItem > 0) {
            if (($compra['tipo_descuento_aplicado'] ?? '') === 'manual_porcentaje' && (float)$compra['importe_original'] > 0) {
                $pct = $descuentoItem / (float)$compra['importe_original'];
                $descuentoItem = round($importeOriginalNuevo * $pct, 2);
            }
            if ($descuentoItem > $importeOriginalNuevo) {
                $db->transRollback();
                $msg = 'El descuento aplicado ($' . number_format($descuentoItem, 2) . ') supera el nuevo importe de la compra ($' . number_format($importeOriginalNuevo, 2) . ').';
                if ($this->request->getHeaderLine('HX-Request')) {
                    return $this->response->setStatusCode(400)->setBody($msg);
                }
                return redirect()->back()->with('error', $msg);
            }
        }

        $totalProduc = round(max(0.0, $importeOriginalNuevo - $descuentoItem), 2);

        // Validar consistencia financiera si la compra ya estaba pagada y aumentó su importe neto
        if ($compra['estatusCompra'] == '1' && $totalProduc > (float)$compra['totalProduc']) {
            $totalPagado = (float)($db->table('t_abono_cliente')->where('idCliente', $idCliente)->selectSum('abono')->get()->getRow()->abono ?? 0.0);
            $otrasPagadas = (float)($db->table('t_cuenta_cliente')
                ->where('idCliente', $idCliente)
                ->where('estatusCompra', '1')
                ->where('idCompra !=', $idCompra)
                ->selectSum('totalProduc')
                ->get()->getRow()->totalProduc ?? 0.0);

            if ($totalPagado < round($otrasPagadas + $totalProduc, 2)) {
                // La diferencia queda pendiente: cambiamos estatusCompra a '0' o recalculamos FIFO para evitar saldo negativo inconsistente
                $estatusCompra = '0';
            }
        }

        // Manejo de Stock en Edición
        $idInventario = (int)$compra['idInventario'];
        if ($idInventario > 0 && $descontarStock) {
            $producto = $this->productoModel->find($idInventario);
            if ($producto) {
                $cantidadDiferencia = $cantidadNueva - (int)$compra['cantidad'];
                $nuevoStock = $producto['stock'] - $cantidadDiferencia;
                $this->productoModel->update($idInventario, ['stock' => max(0, $nuevoStock)]);
            }
        }

        $updateData = [
            'fechaCompra'        => $fechaCompra,
            'cantidad'           => $cantidadNueva,
            'precioUnit'         => $precioUnitNuevo,
            'totalProduc'        => $totalProduc,
            'estatusCompra'      => $estatusCompra,
            'descuento_aplicado' => $descuentoItem,
        ];
        if ($compra['importe_original'] !== null || $descuentoItem > 0) {
            $updateData['importe_original'] = $importeOriginalNuevo;
        }

        $this->cuentaClienteModel->update($idCompra, $updateData);

        $db->table('t_ventas_semillas')
            ->where('id_cuenta_cliente', $idCompra)
            ->update([
                'fecha'        => $fechaCompra,
                'cantidad'     => $cantidadNueva,
                'precio_venta' => $precioUnitNuevo,
                'estatus_pago' => $estatusCompra == '1' ? 'Pagado' : 'Pendiente'
            ]);

        // Si cambió de Pendiente a Pagado, registrar pago en caja por el importe neto
        if ($compra['estatusCompra'] == '0' && $estatusCompra == '1' && $totalProduc > 0) {
            $this->registrarPagoCaja($idCliente, $totalProduc, $fechaCompra);
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            return $this->response->setStatusCode(500)->setBody('Error al actualizar la compra.');
        }
        $db->transCommit();

        $this->recalcularEstatusCompras($idCliente);

        if ($this->request->getHeaderLine('HX-Request')) {
            $data = $this->obtenerDatosCliente($idCliente);
            return view('cuentas_clientes/_tabla_compras', $data);
        }

        return redirect()->to(base_url('admin/cuentas'))->with('success', 'Compra modificada con éxito.');
    }

    public function eliminar($idCompra)
    {
        $compra = $this->cuentaClienteModel->find($idCompra);
        if (!$compra) {
            return redirect()->back()->with('error', 'Compra no encontrada.');
        }

        $idCliente = (int)$compra['idCliente'];
        $db = \Config\Database::connect();
        $db->transBegin();

        // Si es de inventario y se asume que descontó stock, lo devolvemos
        $idInventario = (int)$compra['idInventario'];
        if ($idInventario > 0) {
            $producto = $this->productoModel->find($idInventario);
            if ($producto) {
                $nuevoStock = $producto['stock'] + (int)$compra['cantidad'];
                $this->productoModel->update($idInventario, ['stock' => $nuevoStock]);
            }
        }

        // Revertir uso de cupón si aplica y no quedan más ítems activos de la misma operación
        $idCupon = !empty($compra['id_cupon']) ? (int)$compra['id_cupon'] : null;
        $folioCompra = $compra['folio_compra'] ?? null;

        if ($idCupon !== null) {
            $quedanOtros = 0;
            if (!empty($folioCompra)) {
                $quedanOtros = $this->cuentaClienteModel
                    ->where('folio_compra', $folioCompra)
                    ->where('idCompra !=', $idCompra)
                    ->where('id_cupon', $idCupon)
                    ->countAllResults();
            }

            if ($quedanOtros === 0) {
                $usuarioId = session()->get('id') ? (int)session()->get('id') : null;
                $usoQuery = $db->table('t_cupones_usos')
                    ->where('id_cupon', $idCupon)
                    ->where('estado', 'aplicado');
                if (!empty($folioCompra)) {
                    $usoQuery->where('folio_compra', $folioCompra);
                } else {
                    $usoQuery->where('id_compra', $idCompra);
                }
                $usoQuery->update([
                    'estado'        => 'revertido',
                    'revertido_en'  => date('Y-m-d H:i:s'),
                    'revertido_por' => $usuarioId,
                ]);

                $cupon = $db->query('SELECT * FROM t_cupones WHERE id = ? FOR UPDATE', [$idCupon])->getRowArray();
                if ($cupon) {
                    $nuevosUsos = max(0, (int)$cupon['usos_realizados'] - 1);
                    $cupon['usos_realizados'] = $nuevosUsos;
                    $nuevoEstado = $this->cuponCalculator->resolverEstado($cupon, date('Y-m-d'));

                    $this->cuponModel->update($idCupon, [
                        'usos_realizados' => $nuevosUsos,
                        'estado'          => $nuevoEstado,
                    ]);

                    $this->bitacoraModel->registrar([
                        'id_cupon'        => $idCupon,
                        'id_cliente'      => $idCliente,
                        'accion'          => 'reversion',
                        'folio_compra'    => $folioCompra,
                        'id_compra'       => (int)$idCompra,
                        'monto_descuento' => (float)($compra['descuento_aplicado'] ?? 0),
                        'detalle'         => sprintf(
                            'Reversión de cupón %s por eliminación de compra #%d (%s). Estado restablecido a %s.',
                            $cupon['codigo'],
                            $idCompra,
                            $folioCompra ?: 'sin folio',
                            $nuevoEstado
                        ),
                    ]);
                }
            }
        }

        $this->cuentaClienteModel->delete($idCompra);

        $db->table('t_ventas_semillas')
            ->where('id_cuenta_cliente', $idCompra)
            ->delete();

        if ($db->transStatus() === false) {
            $db->transRollback();
            return redirect()->back()->with('error', 'No se pudo eliminar la compra.');
        }
        $db->transCommit();

        $this->recalcularEstatusCompras($idCliente);

        if ($this->request->getHeaderLine('HX-Request')) {
            $data = $this->obtenerDatosCliente($idCliente);
            return view('cuentas_clientes/_tabla_compras', $data);
        }

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

        $db = \Config\Database::connect();
        $db->table('t_ventas_semillas')
            ->where('id_cuenta_cliente', $idCompra)
            ->update(['estatus_pago' => $nuevoEstado == '1' ? 'Pagado' : 'Pendiente']);

        if ($nuevoEstado == '1') {
            $this->registrarPagoCaja($compra['idCliente'], $compra['totalProduc'], $compra['fechaCompra']);
        }

        $this->recalcularEstatusCompras((int)$compra['idCliente']);

        if ($this->request->getHeaderLine('HX-Request')) {
            $data = $this->obtenerDatosCliente($compra['idCliente']);
            $this->response->setHeader('HX-Trigger', json_encode([
                'mostrarToast' => ['message' => 'Estatus de pago actualizado con éxito.']
            ]));
            return view('cuentas_clientes/_tabla_compras', $data);
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
            'idCliente' => $compra['idCliente'],
            'totalPendiente' => number_format($totalPendiente, 2),
            'totalPagado' => number_format($abonadoActivo, 2),
            'totalCompras' => number_format($totalComprasActivas, 2)
        ]);
    }

    public function buscarProductosJson()
    {
        // Liberar el bloqueo de sesión de PHP para permitir peticiones AJAX simultáneas sin encolarse
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $term = $this->request->getVar('term');
        if (empty($term) || mb_strlen(trim($term)) < 2) {
            return $this->response->setJSON([]);
        }

        $termClean = trim($term);
        $cacheKey = 'buscar_prod_' . md5(mb_strtolower($termClean));

        $cached = cache($cacheKey);
        if ($cached !== null && is_array($cached)) {
            return $this->response->setJSON($cached);
        }

        try {
            $productos = $this->productoModel->buscarProductos($termClean, null, false, 25);
            
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

            // Cachear resultados por 5 minutos para que búsquedas repetidas sean instantáneas
            cache()->save($cacheKey, $output, 300);

            return $this->response->setJSON($output);
        } catch (\Throwable $e) {
            log_message('error', 'Error en buscarProductosJson: ' . $e->getMessage());
            return $this->response->setJSON([]);
        }
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

        if ($this->request->getHeaderLine('HX-Request')) {
            $data = $this->obtenerDatosCliente((int)$idCliente);
            return view('cuentas_clientes/_tabla_compras', $data);
        }

        return redirect()->to(base_url('admin/cuentas'))->with('success', 'Datos del cliente actualizados con éxito.');
    }

    public function registrarAbono()
    {
        $idCliente = (int)$this->request->getPost('id_cliente');
        $monto = (float)$this->request->getPost('monto');
        $fecha = $this->request->getPost('fecha_abono') ?: date('Y-m-d');

        if (empty($idCliente) || $monto <= 0) {
            return redirect()->back()->with('error', 'Por favor, ingresa un monto de abono válido.');
        }

        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return redirect()->back()->with('error', 'Cliente no encontrado.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Registrar el abono en t_abono_cliente
        $db->table('t_abono_cliente')->insert([
            'idCliente'  => $idCliente,
            'fechaAbono' => $fecha,
            'abono'      => $monto,
            'idCompra'   => 0
        ]);

        // 2. Registrar el ingreso en t_caja_chica
        $db->table('t_caja_chica')->insert([
            'fecha'       => $fecha,
            'descripcion' => 'Abono de cliente: ' . $cliente['nombre'],
            'monto'       => $monto,
            'tipo'        => 'Ingreso'
        ]);

        // 3. Recalcular el estatus de todas las compras del cliente cronológicamente
        $this->recalcularEstatusCompras($idCliente);

        $db->transComplete();

        if ($db->transStatus() === false) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(500)->setBody('Ocurrió un error al registrar el abono.');
            }
            return redirect()->back()->with('error', 'Ocurrió un error al registrar el abono.');
        }

        if ($this->request->getHeaderLine('HX-Request')) {
            $data = $this->obtenerDatosCliente($idCliente);

            // Generar el resumen de abono para el ticket digital
            $saldoPendiente = $data['totalPendiente'] ?? 0.00;
            $saldoAnterior = $saldoPendiente + $monto;
            $fechaTicket = date('d/m/y', strtotime($fecha));

            $ticketText = "*Resumen de Abono*\n\n";
            $ticketText .= "*Saldo Anterior:* $" . number_format($saldoAnterior, 2) . "\n";
            $ticketText .= "*Abono ({$fechaTicket}):* $" . number_format($monto, 2) . "\n";
            $ticketText .= "*Nuevo Saldo:* $" . number_format($saldoPendiente, 2);

            $this->response->setHeader('HX-Trigger', json_encode([
                'abonoRegistrado' => [
                    'ticket' => $ticketText,
                    'cel' => $cliente['cel'] ?? ''
                ]
            ]));

            return view('cuentas_clientes/_tabla_compras', $data);
        }

        return redirect()->to(base_url('admin/cuentas'))->with('success', 'Abono registrado con éxito por $' . number_format($monto, 2));
    }

    private function recalcularEstatusCompras(int $idCliente)
    {
        $db = \Config\Database::connect();
        
        $totalPagado = (float)($db->table('t_abono_cliente')
            ->where('idCliente', $idCliente)
            ->selectSum('abono')
            ->get()
            ->getRow()
            ->abono ?? 0.00);
            
        $compras = $this->cuentaClienteModel
            ->where('idCliente', $idCliente)
            ->orderBy('fechaCompra', 'ASC')
            ->orderBy('idCompra', 'ASC')
            ->findAll();

        // 1. Calcular la suma de lo que ya está marcado como Pagado (estatusCompra == '1')
        $sumaPagadas = 0.00;
        foreach ($compras as $c) {
            if ($c['estatusCompra'] == '1') {
                $sumaPagadas += (float)$c['totalProduc'];
            }
        }

        // 2. Si el total de abonos es menor a la suma de compras ya pagadas,
        // significa que se eliminó/editó algún abono y debemos recalcular todo por FIFO
        if ($totalPagado < $sumaPagadas) {
            $saldoRestante = round($totalPagado, 2);
            foreach ($compras as $c) {
                $totalProduc = round((float)$c['totalProduc'], 2);
                $nuevoEstatus = '0';
                
                if ($saldoRestante >= $totalProduc) {
                    $nuevoEstatus = '1';
                    $saldoRestante = round($saldoRestante - $totalProduc, 2);
                }
                
                if ($c['estatusCompra'] !== $nuevoEstatus) {
                    $this->cuentaClienteModel->update($c['idCompra'], ['estatusCompra' => $nuevoEstatus]);
                    
                    $db->table('t_ventas_semillas')
                       ->where('id_cuenta_cliente', $c['idCompra'])
                       ->update(['estatus_pago' => $nuevoEstatus == '1' ? 'Pagado' : 'Pendiente']);
                }
            }
        } else {
            // El abono cubre lo ya pagado. El saldo restante para las compras pendientes es:
            $saldoRestante = round($totalPagado - $sumaPagadas, 2);

            // Calcular cuánto suman las compras que están pendientes (estatusCompra == '0')
            $sumaPendientes = 0.00;
            foreach ($compras as $c) {
                if ($c['estatusCompra'] == '0') {
                    $sumaPendientes += (float)$c['totalProduc'];
                }
            }

            // Si el saldo restante cubre COMPLETAMENTE todas las compras pendientes, las marcamos todas como Pagadas
            if ($saldoRestante >= round($sumaPendientes, 2)) {
                foreach ($compras as $c) {
                    if ($c['estatusCompra'] == '0') {
                        $this->cuentaClienteModel->update($c['idCompra'], ['estatusCompra' => '1']);
                        
                        $db->table('t_ventas_semillas')
                           ->where('id_cuenta_cliente', $c['idCompra'])
                           ->update(['estatus_pago' => 'Pagado']);
                    }
                }
            }
            // Si no las cubre por completo, se quedan todas como Pendientes (no hacemos cambios a '1')
        }
    }

    private function registrarPagoCaja($idCliente, $monto, $fecha = null)
    {
        $db = \Config\Database::connect();
        $cliente = $this->clienteModel->find($idCliente);
        $nombreCliente = $cliente ? $cliente['nombre'] : 'Desconocido';
        $fechaPago = !empty($fecha) ? $fecha : date('Y-m-d');

        // Insertar abono
        $db->table('t_abono_cliente')->insert([
            'idCliente'  => $idCliente,
            'fechaAbono' => $fechaPago,
            'abono'      => $monto,
            'idCompra'   => 0
        ]);

        // Insertar en caja chica
        $db->table('t_caja_chica')->insert([
            'fecha'       => $fechaPago,
            'descripcion' => 'Abono de cliente: ' . $nombreCliente,
            'monto'       => $monto,
            'tipo'        => 'Ingreso'
        ]);
    }

    /**
     * Obtiene y calcula todos los datos financieros y de compras de un cliente.
     */
    private function obtenerDatosCliente(int $idCliente): ?array
    {
        $cliente = $this->clienteModel->find($idCliente);
        if (!$cliente) {
            return null;
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
        $porEmpacarCount = 0;
        $porEntregarCount = 0;
        foreach ($compras as $c) {
            $totalComprasHistorico += $c['totalProduc'];
            if ($c['estatusCompra'] == '0') {
                $totalComprasActivas += $c['totalProduc'];
            }
            
            // Contadores de entregas
            $estEntrega = (int)($c['estatus_entrega'] ?? 0);
            if ($estEntrega == 0) {
                $porEmpacarCount += $c['cantidad'];
            }
            if ($estEntrega == 0 || $estEntrega == 1) {
                $porEntregarCount += $c['cantidad'];
            }
        }

        $totalPagadoHistorico = 0;
        foreach ($abonos as $a) {
            $totalPagadoHistorico += $a['abono'];
        }

        $totalPendiente = $totalComprasHistorico - $totalPagadoHistorico;
        
        // Lo abonado hacia las compras pendientes actuales
        $abonadoActivo = $totalComprasActivas - $totalPendiente;

        // Cargar cupones y usos del cliente
        $cupones = [];
        $resumenCupones = [
            'total_monetario_disponible' => 0.00,
            'cantidad_activos'           => 0,
            'cantidad_porcentuales'      => 0,
            'total_descuentos_utilizados'=> 0.00,
        ];

        if ($db->tableExists('t_cupones')) {
            $cuponesRaw = $this->cuponModel->obtenerPorCliente($idCliente);
            $usosRaw = $db->tableExists('t_cupones_usos') ? $this->cuponUsoModel->obtenerPorCliente($idCliente) : [];

            $ultimoUsoPorCupon = [];
            $totalDescuentosCupon = 0.00;
            foreach ($usosRaw as $uso) {
                if (($uso['estado'] ?? '') === 'aplicado') {
                    $totalDescuentosCupon += (float)($uso['descuento_aplicado'] ?? 0);
                    $cid = (int)$uso['id_cupon'];
                    if (!isset($ultimoUsoPorCupon[$cid])) {
                        $ultimoUsoPorCupon[$cid] = $uso;
                    }
                }
            }

            $totalDescuentosManuales = 0.00;
            foreach ($compras as $c) {
                if (empty($c['id_cupon']) && (float)($c['descuento_aplicado'] ?? 0) > 0) {
                    $totalDescuentosManuales += (float)$c['descuento_aplicado'];
                }
            }

            $hoy = date('Y-m-d');
            foreach ($cuponesRaw as $cupon) {
                $estadoResuelto = $this->cuponCalculator->resolverEstado($cupon, $hoy);
                $cid = (int)$cupon['id'];
                $cupon['estado_resuelto'] = $estadoResuelto;
                $cupon['usos_disponibles'] = max(0, (int)$cupon['limite_usos'] - (int)$cupon['usos_realizados']);
                $cupon['ultima_fecha_uso'] = $ultimoUsoPorCupon[$cid]['fecha_uso'] ?? null;
                $cupon['ultimo_folio_compra'] = $ultimoUsoPorCupon[$cid]['folio_compra'] ?? null;
                $cupon['ultimo_id_compra'] = $ultimoUsoPorCupon[$cid]['id_compra'] ?? null;
                $cupones[] = $cupon;
            }

            $resumenCupones = $this->cuponCalculator->resumirCuponesDisponibles($cuponesRaw, $hoy);
            $resumenCupones['total_descuentos_utilizados'] = round($totalDescuentosCupon + $totalDescuentosManuales, 2);
        }

        return [
            'cliente' => $cliente,
            'compras' => $compras,
            'abonos'  => $abonos,
            'cupones' => $cupones,
            'resumenCupones' => $resumenCupones,
            'totalPendiente' => $totalPendiente,
            'totalPagadoActivo' => $abonadoActivo,
            'totalComprasActivas' => $totalComprasActivas,
            'totalPagadoHistorico' => $totalPagadoHistorico,
            'porEmpacarCount' => $porEmpacarCount,
            'porEntregarCount' => $porEntregarCount
        ];
    }

    public function actualizarEstatusEntrega($idCompra)
    {
        $compra = $this->cuentaClienteModel->find($idCompra);
        if (!$compra) {
            return $this->response->setJSON(['success' => false, 'message' => 'Compra no encontrada']);
        }

        $nuevoEstado = (int)$this->request->getPost('estatus_entrega');
        if (!in_array($nuevoEstado, [0, 1, 2])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Estado no válido']);
        }

        $this->cuentaClienteModel->update($idCompra, ['estatus_entrega' => $nuevoEstado]);

        // Recalcular los contadores de entrega del cliente
        $compras = $this->cuentaClienteModel->where('idCliente', $compra['idCliente'])->findAll();
        $porEmpacar = 0;
        $porEntregar = 0;
        foreach ($compras as $c) {
            $estEntrega = (int)($c['estatus_entrega'] ?? 0);
            if ($estEntrega == 0) {
                $porEmpacar += $c['cantidad'];
            }
            if ($estEntrega == 0 || $estEntrega == 1) {
                $porEntregar += $c['cantidad'];
            }
        }

        if ($this->request->getHeaderLine('HX-Request')) {
            $data = $this->obtenerDatosCliente($compra['idCliente']);
            $this->response->setHeader('HX-Trigger', json_encode([
                'mostrarToast' => ['message' => 'Estado de entrega actualizado con éxito.']
            ]));
            return view('cuentas_clientes/_tabla_compras', $data);
        }

        return $this->response->setJSON([
            'success' => true,
            'idCliente' => (int)$compra['idCliente'],
            'nuevoEstado' => $nuevoEstado,
            'porEmpacar' => $porEmpacar,
            'porEntregar' => $porEntregar
        ]);
    }

    public function crearCliente()
    {
        $nombre = trim((string)$this->request->getPost('nombre'));
        $cel = trim((string)$this->request->getPost('cel'));
        $tipoCliente = trim((string)$this->request->getPost('tipoCliente')) ?: 'General';

        if (empty($nombre)) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(400)->setBody('El nombre del cliente es obligatorio.');
            }
            return redirect()->back()->withInput()->with('error', 'El nombre del cliente es obligatorio.');
        }

        // Verificar si ya existe un cliente con ese nombre
        $existe = $this->clienteModel->where('nombre', $nombre)->first();
        if ($existe) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->response->setStatusCode(400)->setBody('Ya existe un cliente registrado con ese nombre.');
            }
            return redirect()->back()->withInput()->with('error', 'Ya existe un cliente registrado con ese nombre.');
        }

        $datos = [
            'nombre'      => $nombre,
            'cel'         => $cel,
            'tipoCliente' => $tipoCliente
        ];

        if ($this->clienteModel->insert($datos)) {
            if ($this->request->getHeaderLine('HX-Request')) {
                return $this->index();
            }
            return redirect()->to(base_url('admin/cuentas'))->with('success', 'Cliente registrado con éxito.');
        }

        if ($this->request->getHeaderLine('HX-Request')) {
            return $this->response->setStatusCode(500)->setBody('No se pudo registrar el cliente.');
        }
        return redirect()->back()->withInput()->with('error', 'No se pudo registrar el cliente.');
    }
}
