<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoriaModel extends Model
{
    protected $table = 't_categorias';
    protected $primaryKey = 'idCategoria';
    protected $returnType = 'array';
    protected $allowedFields = ['nombre', 'imagen'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    /**
     * Obtiene las categorías que tienen al menos un producto asociado con stock
     */
    public function obtenerCategoriasConProductos()
    {
        return $this->select('t_categorias.*')
                    ->join('t_inventario', 't_inventario.id_categoria = t_categorias.idCategoria', 'inner')
                    ->where('t_inventario.stock !=', 0)
                    ->groupBy('t_categorias.idCategoria')
                    ->orderBy('t_categorias.nombre', 'ASC')
                    ->findAll();
    }
}
