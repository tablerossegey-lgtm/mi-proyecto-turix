<?php

namespace App\Models;

use CodeIgniter\Model;

class CuentaClienteModel extends Model
{
    protected $table            = 't_cuenta_cliente';
    protected $primaryKey       = 'idCompra';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'idCliente',
        'fechaCompra',
        'cantidad',
        'descProducto',
        'precioUnit',
        'totalProduc',
        'estatusCompra',
        'idInventario'
    ];

    // Dates
    protected $useTimestamps = false;

    /**
     * Obtiene el historial de compras de un cliente con datos de inventario
     *
     * @param int $idCliente
     * @return array
     */
    public function obtenerComprasPorCliente(int $idCliente)
    {
        return $this->select('t_cuenta_cliente.*, t_inventario.codigo_sku')
                    ->join('t_inventario', 't_inventario.id = t_cuenta_cliente.idInventario', 'left')
                    ->where('t_cuenta_cliente.idCliente', $idCliente)
                    ->orderBy('t_cuenta_cliente.fechaCompra', 'DESC')
                    ->orderBy('t_cuenta_cliente.idCompra', 'DESC')
                    ->findAll();
    }
}
