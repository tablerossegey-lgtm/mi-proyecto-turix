<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table = 't_inventario';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['codigo_sku', 'descripcion', 'id_categoria', 'precio', 'stock', 'foto', 'masDetalle', 'precio_promo'];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    /**
     * Obtiene todos los productos junto con el nombre de su categoría
     */
    public function obtenerTodosConCategoria()
    {
        return $this->select('t_inventario.*, t_categorias.nombre as nombre_categoria')
            ->join('t_categorias', 't_categorias.idCategoria = t_inventario.id_categoria', 'left')
            ->findAll();
    }

    /**
     * Obtiene los productos filtrados por una categoría específica, junto con su nombre de categoría
     */
    public function obtenerPorCategoria(int $categoriaId)
    {
        return $this->select('t_inventario.*, t_categorias.nombre as nombre_categoria')
            ->join('t_categorias', 't_categorias.idCategoria = t_inventario.id_categoria')
            ->where('t_inventario.id_categoria', $categoriaId)
            ->findAll();
    }
}
