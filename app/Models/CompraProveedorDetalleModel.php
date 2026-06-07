<?php

namespace App\Models;

use CodeIgniter\Model;

class CompraProveedorDetalleModel extends Model
{
    protected $table = 't_compras_proveedor_detalle';
    protected $primaryKey = 'idDetalle';
    protected $returnType = 'array';
    protected $allowedFields = [
        'idCompraProveedor',
        'id_producto',
        'sku',
        'nombre',
        'cantidad',
        'precio_proveedor',
        'costo_real_unit',
        'costo_real_total',
        'margen',
        'precio_venta_sugerido',
        'precio_venta_final'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    /**
     * Obtiene todos los detalles de una compra específica
     */
    public function obtenerPorCompra(int $idCompraProveedor)
    {
        return $this->where('idCompraProveedor', $idCompraProveedor)->findAll();
    }
}
