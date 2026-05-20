<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ProductoModel;

class Catalogo extends BaseController
{
    protected $productoModel;

    public function __construct()
    {
        $this->productoModel = new ProductoModel();
    }

    public function index()
    {
        $productos = $this->productoModel->obtenerTodosConCategoria();

        $data = [
            'productos' => $productos,
            'titulo' => 'Catálogo de Productos'
        ];

        if ($this->request->getHeaderLine('HX-Request')) {
            return view('catalogo/_lista_productos', $data);
        }

        return view('catalogo/index', $data);
    }

    public function porCategoria(int $categoriaId)
    {
        // Filtramos productos por categoría y obtenemos el nombre de la categoría usando el Modelo
        $productos = $this->productoModel->obtenerPorCategoria($categoriaId);

        // Obtenemos el nombre de la categoría para mostrarlo en el título usando su Modelo (MVC)
        $categoriaModel = new \App\Models\CategoriaModel();
        $categoria = $categoriaModel->find($categoriaId);
        $nombreCategoria = $categoria ? ' > ' . $categoria['nombre'] : '';

        $data = [
            'productos' => $productos,
            'titulo' => 'Productos' . $nombreCategoria,
            'categoria_id' => $categoriaId // Pasamos el ID para la barra de búsqueda
        ];

        // Retornamos una vista parcial para usar con HTMX o la vista completa con layout si se recarga la página
        if ($this->request->getHeaderLine('HX-Request')) {
            return view('catalogo/_lista_productos', $data);
        }

        return view('catalogo/por_categoria', $data);
    }

    /**
     * Endpoint para la búsqueda en vivo vía HTMX
     */
    public function buscar($categoriaId = null)
    {
        $termino = $this->request->getPost('q');
        
        // Si no hay término, simplemente regresamos los productos normales
        if (empty(trim((string)$termino))) {
            if ($categoriaId) {
                $productos = $this->productoModel->obtenerPorCategoria((int)$categoriaId);
            } else {
                $productos = $this->productoModel->obtenerTodosConCategoria();
            }
        } else {
            // Buscamos con el término ingresado
            $productos = $this->productoModel->buscarProductos((string)$termino, $categoriaId ? (int)$categoriaId : null);
        }

        return view('catalogo/_grid_productos', ['productos' => $productos]);
    }
}
