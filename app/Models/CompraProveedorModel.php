<?php

namespace App\Models;

use CodeIgniter\Model;

class CompraProveedorModel extends Model
{
    protected $table = 't_compras_proveedor';
    protected $primaryKey = 'idCompraProveedor';
    protected $returnType = 'array';
    protected $allowedFields = [
        'idProveedor',
        'fechaCompra',
        'descripcion',
        'montoTotal',
        'subtotal',
        'envio_local_estimado',
        'impuesto_importacion',
        'total_pagado',
        'total_productos',
        'factor_pedido'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    /**
     * Obtiene las compras ordenadas por fecha descendente, uniendo con proveedores
     */
    public function obtenerTodasConProveedor()
    {
        return $this->select('t_compras_proveedor.*, t_proveedores.nombre as nombre_proveedor')
                    ->join('t_proveedores', 't_proveedores.idProveedor = t_compras_proveedor.idProveedor', 'left')
                    ->orderBy('t_compras_proveedor.fechaCompra', 'DESC')
                    ->orderBy('t_compras_proveedor.idCompraProveedor', 'DESC')
                    ->findAll();
    }

    /**
     * Obtiene las compras paginadas con datos de proveedor,
     * con filtro opcional de búsqueda libre por nombre de proveedor.
     */
    public function obtenerPaginadoConProveedor(int $limite, int $offset, string $busqueda = '')
    {
        $builder = $this->select('t_compras_proveedor.*, t_proveedores.nombre as nombre_proveedor')
                        ->join('t_proveedores', 't_proveedores.idProveedor = t_compras_proveedor.idProveedor', 'left')
                        ->orderBy('t_compras_proveedor.fechaCompra', 'DESC')
                        ->orderBy('t_compras_proveedor.idCompraProveedor', 'DESC');

        if ($busqueda !== '') {
            $builder->like('t_proveedores.nombre', $busqueda);
        }

        return $builder->findAll($limite, $offset);
    }

    /**
     * Cuenta el total de compras, con filtro opcional de búsqueda por nombre de proveedor.
     * Usa su propio builder para no contaminar el builder del modelo.
     */
    public function contarTodas(string $busqueda = ''): int
    {
        $db = \Config\Database::connect();
        $builder = $db->table('t_compras_proveedor cp')
                      ->join('t_proveedores p', 'p.idProveedor = cp.idProveedor', 'left');

        if ($busqueda !== '') {
            $builder->like('p.nombre', $busqueda);
        }

        return $builder->countAllResults();
    }
}
