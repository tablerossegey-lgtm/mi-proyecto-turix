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
        $productos = $this->productoModel->obtenerTodosConCategoria(true);

        $data = [
            'productos' => $productos,
            'titulo' => 'Catálogo de Productos'
        ];

        if ($this->request->getHeaderLine('HX-Request') && !$this->request->getHeaderLine('HX-Boosted')) {
            return view('catalogo/_lista_productos', $data);
        }

        return view('catalogo/index', $data);
    }

    public function porCategoria(int $categoriaId)
    {
        // Filtramos productos por categoría y obtenemos el nombre de la categoría usando el Modelo (solo con stock)
        $productos = $this->productoModel->obtenerPorCategoria($categoriaId, true);

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
        if ($this->request->getHeaderLine('HX-Request') && !$this->request->getHeaderLine('HX-Boosted')) {
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
        
        // Si no hay término, simplemente regresamos los productos normales (solo con stock)
        if (empty(trim((string)$termino))) {
            if ($categoriaId) {
                $productos = $this->productoModel->obtenerPorCategoria((int)$categoriaId, true);
            } else {
                $productos = $this->productoModel->obtenerTodosConCategoria(true);
            }
        } else {
            // Buscamos con el término ingresado
            $productos = $this->productoModel->buscarProductos((string)$termino, $categoriaId ? (int)$categoriaId : null, true);
        }

        return view('catalogo/_grid_productos', ['productos' => $productos]);
    }

    /**
     * Muestra la sección Venta Especial (Productos con precio promoción / especial)
     */
    public function ventaEspecial()
    {
        $productos = $this->productoModel->obtenerProductosOferta(true, null, true);

        $data = [
            'productos'  => $productos,
            'titulo'     => 'Venta Especial - Productos en Oferta',
            'es_ofertas' => true
        ];

        if ($this->request->getHeaderLine('HX-Request') && !$this->request->getHeaderLine('HX-Boosted')) {
            return view('catalogo/_lista_productos', $data);
        }

        return view('catalogo/venta_especial', $data);
    }

    /**
     * Endpoint para la búsqueda en vivo vía HTMX en Venta Especial
     */
    public function buscarOfertas()
    {
        $termino = $this->request->getPost('q');
        
        if (empty(trim((string)$termino))) {
            $productos = $this->productoModel->obtenerProductosOferta(true, null, true);
        } else {
            $productos = $this->productoModel->buscarProductosOferta((string)$termino, true, true);
        }

        return view('catalogo/_grid_productos', [
            'productos'  => $productos,
            'es_ofertas' => true
        ]);
    }

    public function detalle($id)
    {
        $producto = $this->productoModel->obtenerPorIdConCategoria((int)$id);

        if (!$producto || (int)($producto['stock'] ?? 0) === 0) {
            return '<div class="p-4 text-white">Producto no encontrado o sin stock.</div>';
        }

        // Cargar imágenes adicionales del inventario
        $inventarioImagenesModel = new \App\Models\InventarioImagenesModel();
        $imagenesAdicionales = $inventarioImagenesModel->obtenerPorProducto((int)$producto['id']);

        $esOfertas = (bool)$this->request->getGet('oferta');

        $data['p'] = $producto;
        $data['imagenes_adicionales'] = $imagenesAdicionales;
        $data['es_ofertas'] = $esOfertas;
        return view('catalogo/_detalle_modal', $data);
    }
}
