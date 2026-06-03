<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoEncargadoModel extends Model
{
    protected $table = 't_pedidos_encargados';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id_producto',
        'nombre_cliente',
        'contacto_cliente',
        'cantidad',
        'anticipo',
        'estado',
        'notas'
    ];

    /**
     * Obtiene los encargos junto con la información del producto asociado
     */
    public function obtenerTodosConProducto(?string $estado = null)
    {
        $builder = $this->select('t_pedidos_encargados.*, t_inventario.codigo_sku, t_inventario.descripcion as descripcion_producto, t_inventario.foto as foto_producto, t_categorias.nombre as nombre_categoria')
                        ->join('t_inventario', 't_inventario.id = t_pedidos_encargados.id_producto', 'left')
                        ->join('t_categorias', 't_categorias.idCategoria = t_inventario.id_categoria', 'left')
                        ->orderBy('t_pedidos_encargados.creado_en', 'DESC');

        if (!empty($estado)) {
            $builder->where('t_pedidos_encargados.estado', $estado);
        }

        return $builder->findAll();
    }
}
