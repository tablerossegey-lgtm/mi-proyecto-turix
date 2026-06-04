<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        $productoModel = new \App\Models\ProductoModel();
        $categoriaModel = new \App\Models\CategoriaModel();

        // 5 productos más recientes
        $novedades = $productoModel->select('t_inventario.*, t_categorias.nombre as nombre_categoria')
                                   ->join('t_categorias', 't_categorias.idCategoria = t_inventario.id_categoria', 'left')
                                   ->orderBy('t_inventario.fecha_creacion', 'DESC')
                                   ->limit(5)
                                   ->findAll();

        // Categorías que tienen al menos un producto asociado, ordenadas alfabéticamente
        $categorias = $categoriaModel->obtenerCategoriasConProductos();

        $data = [
            'novedades'  => $novedades,
            'categorias' => $categorias,
            'titulo'     => 'Inicio'
        ];

        return view('home', $data);
    }
}
