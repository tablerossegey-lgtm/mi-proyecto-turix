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

    /**
     * Busca productos por término, opcionalmente filtrando por categoría
     */
    public function buscarProductos(string $termino, ?int $categoriaId = null)
    {
        $builder = $this->select('t_inventario.*, t_categorias.nombre as nombre_categoria')
                        ->join('t_categorias', 't_categorias.idCategoria = t_inventario.id_categoria', 'left')
                        ->groupStart()
                            ->like('t_inventario.descripcion', $termino)
                            ->orLike('t_inventario.codigo_sku', $termino)
                        ->groupEnd();

        if ($categoriaId) {
            $builder->where('t_inventario.id_categoria', $categoriaId);
        }

        return $builder->findAll();
    }

    /**
     * Obtiene un producto por su ID junto con el nombre de su categoría
     */
    public function obtenerPorIdConCategoria(int $id)
    {
        return $this->select('t_inventario.*, t_categorias.nombre as nombre_categoria')
                    ->join('t_categorias', 't_categorias.idCategoria = t_inventario.id_categoria', 'left')
                    ->find($id);
    }

    /**
     * Obtiene todos los productos con el conteo de imágenes adicionales en galería y filtros de búsqueda
     *
     * @param string|null $termino
     * @return array
     */
    public function obtenerTodosConConteoImagenes(?string $termino = null): array
    {
        $builder = $this->select('t_inventario.*, t_categorias.nombre as nombre_categoria, COUNT(t_inventario_imagenes.id) as total_imagenes')
            ->join('t_categorias', 't_categorias.idCategoria = t_inventario.id_categoria', 'left')
            ->join('t_inventario_imagenes', 't_inventario_imagenes.id_producto = t_inventario.id', 'left')
            ->groupBy('t_inventario.id')
            ->orderBy('t_inventario.id', 'DESC');

        if (!empty($termino)) {
            $builder->groupStart()
                    ->like('t_inventario.descripcion', $termino)
                    ->orLike('t_inventario.codigo_sku', $termino)
                    ->groupEnd();
        }

        return $builder->findAll();
    }
}
