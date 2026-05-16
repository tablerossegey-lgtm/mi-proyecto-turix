<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ProductoModel;

class Catalogo extends BaseController
{
    public function index()
    {
        $model = new ProductoModel();
        $productos = $model->select('t_inventario.*, t_categorias.nombre as nombre_categoria')
            ->join('t_categorias', 't_categorias.idCategoria = t_inventario.id_categoria', 'left')
            ->findAll();

        $data = [
            'productos' => $productos,
            'titulo'    => 'Catálogo de Productos'
        ];

        if ($this->request->getHeaderLine('HX-Request')) {
            return view('catalogo/_lista_productos', $data);
        }

        return view('catalogo/index', $data);
    }

    public function porCategoria(int $categoriaId)
    {
        $model = new ProductoModel();

        // Filtramos productos por categoría y obtenemos el nombre de la categoría para la ruta de las imágenes
        $productos = $model->select('t_inventario.*, t_categorias.nombre as nombre_categoria')
            ->join('t_categorias', 't_categorias.idCategoria = t_inventario.id_categoria')
            ->where('t_inventario.id_categoria', $categoriaId)
            ->findAll();

        $data = [
            'productos' => $productos,
            'titulo'    => 'Productos de la Categoría'
        ];

        // Retornamos una vista parcial para usar con HTMX o la vista completa con layout si se recarga la página
        if ($this->request->getHeaderLine('HX-Request')) {
            return view('catalogo/_lista_productos', $data);
        }

        return view('catalogo/por_categoria', $data);
    }
}
