<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class AdminProductos extends BaseController
{
    protected $productoModel;
    protected $inventarioImagenesModel;
    protected $categoriaModel;

    public function __construct()
    {
        $this->productoModel = new \App\Models\ProductoModel();
        $this->inventarioImagenesModel = new \App\Models\InventarioImagenesModel();
        $this->categoriaModel = new \App\Models\CategoriaModel();
    }

    public function index()
    {
        $q = $this->request->getVar('q');
        $productos = $this->productoModel->obtenerTodosConConteoImagenes($q);
        $categorias = $this->categoriaModel->orderBy('nombre', 'ASC')->findAll();

        $data = [
            'productos' => $productos,
            'categorias' => $categorias,
            'titulo' => 'Administración de Galerías de Productos',
            'q' => $q
        ];

        return view('admin_productos/index', $data);
    }

    public function galeria($id)
    {
        $producto = $this->productoModel->obtenerPorIdConCategoria((int)$id);

        if (!$producto) {
            return redirect()->to(base_url('admin/productos'))->with('error', 'Producto no encontrado.');
        }

        $imagenesAdicionales = $this->inventarioImagenesModel->obtenerPorProducto((int)$producto['id']);

        $data = [
            'p' => $producto,
            'imagenes_adicionales' => $imagenesAdicionales,
            'titulo' => 'Galería de: ' . $producto['descripcion']
        ];

        return view('admin_productos/galeria', $data);
    }

    public function subirImagen($id)
    {
        $producto = $this->productoModel->obtenerPorIdConCategoria((int)$id);

        if (!$producto) {
            return redirect()->back()->with('error', 'Producto no encontrado.');
        }

        $files = $this->request->getFiles();

        if (isset($files['imagenes'])) {
            $categoriaFolder = isset($producto['nombre_categoria']) ? str_replace(' ', '', ucwords(strtolower($producto['nombre_categoria']))) : '';
            $uploadPath = FCPATH . 'uploads/' . $categoriaFolder;

            // Asegurar que exista el directorio
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Obtener el orden máximo actual para este producto
            $maxOrdenRow = $this->inventarioImagenesModel->where('id_producto', $producto['id'])
                                                  ->selectMax('orden')
                                                  ->first();
            $siguienteOrden = isset($maxOrdenRow['orden']) ? ((int)$maxOrdenRow['orden'] + 1) : 1;

            $uploadedCount = 0;
            foreach ($files['imagenes'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    // Validar tipo de archivo (imagen)
                    $mimeType = $file->getMimeType();
                    if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
                        // Generar un nombre seguro y único
                        $newName = $file->getRandomName();
                        $file->move($uploadPath, $newName);

                        // Registrar en la base de datos
                        $this->inventarioImagenesModel->insert([
                            'id_producto' => $producto['id'],
                            'ruta_foto'   => $newName,
                            'orden'       => $siguienteOrden++
                        ]);
                        $uploadedCount++;
                    }
                }
            }

            if ($uploadedCount > 0) {
                return redirect()->back()->with('success', "Se cargaron {$uploadedCount} imágenes correctamente.");
            }
        }

        return redirect()->back()->with('error', 'No se seleccionaron archivos de imagen válidos.');
    }

    public function eliminarImagen($id)
    {
        $imagen = $this->inventarioImagenesModel->find($id);

        if (!$imagen) {
            return redirect()->back()->with('error', 'Imagen no encontrada.');
        }

        $producto = $this->productoModel->obtenerPorIdConCategoria((int)$imagen['id_producto']);

        if ($producto) {
            $categoriaFolder = isset($producto['nombre_categoria']) ? str_replace(' ', '', ucwords(strtolower($producto['nombre_categoria']))) : '';
            $filePath = FCPATH . 'uploads/' . $categoriaFolder . '/' . $imagen['ruta_foto'];
            
            // Eliminar archivo físico
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        // Eliminar registro en base de datos
        $this->inventarioImagenesModel->delete($id);

        return redirect()->back()->with('success', 'Imagen eliminada de la galería correctamente.');
    }

    public function cambiarPrincipal($id)
    {
        $producto = $this->productoModel->find($id);

        if (!$producto) {
            return redirect()->back()->with('error', 'Producto no encontrado.');
        }

        $file = $this->request->getFile('foto_principal');

        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Validar tipo de archivo (imagen)
            $mimeType = $file->getMimeType();
            if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
                // Obtener el nombre de la categoría del producto (para la carpeta)
                $categoria = $this->productoModel->obtenerPorIdConCategoria((int)$id);
                $categoriaFolder = isset($categoria['nombre_categoria']) ? str_replace(' ', '', ucwords(strtolower($categoria['nombre_categoria']))) : '';
                $uploadPath = FCPATH . 'uploads/' . $categoriaFolder;

                // Asegurar que exista el directorio
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                // Generar un nombre seguro y único
                $newName = 'principal_' . $file->getRandomName();
                $file->move($uploadPath, $newName);

                // Eliminar foto anterior si existía localmente y no es la por defecto
                $oldFoto = $producto['foto'] ?? '';
                if (!empty($oldFoto)) {
                    $isUrl = (strpos($oldFoto, 'http://') === 0 || strpos($oldFoto, 'https://') === 0);
                    if (!$isUrl && $oldFoto !== 'SinImagen.png') {
                        $oldFilePath = $uploadPath . '/' . $oldFoto;
                        if (file_exists($oldFilePath)) {
                            @unlink($oldFilePath);
                        }
                    }
                }

                // Actualizar en la base de datos (t_inventario)
                $this->productoModel->update($id, [
                    'foto' => $newName
                ]);

                return redirect()->back()->with('success', 'Imagen principal del producto actualizada correctamente.');
            }
        }

        return redirect()->back()->with('error', 'No se seleccionó una imagen válida para la foto principal.');
    }

    public function crear()
    {
        $sku = $this->request->getPost('codigo_sku');
        $descripcion = $this->request->getPost('descripcion');
        $idCategoria = $this->request->getPost('id_categoria');
        $precio = $this->request->getPost('precio');
        $precioPromo = $this->request->getPost('precio_promo') ?: 0.00;
        $stock = $this->request->getPost('stock');
        $masDetalle = $this->request->getPost('masDetalle') ?: null;

        // Validaciones básicas
        if (empty($sku) || empty($descripcion) || empty($idCategoria) || $precio === null || $stock === null) {
            return redirect()->back()->withInput()->with('error', 'Por favor complete todos los campos obligatorios.');
        }

        // Validar SKU único
        $existing = $this->productoModel->where('codigo_sku', $sku)->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('error', 'El SKU ingresado ya se encuentra registrado por otro producto.');
        }

        // Procesar foto principal
        $fotoName = '';
        $file = $this->request->getFile('foto_principal');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $mimeType = $file->getMimeType();
            if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
                $categoria = $this->categoriaModel->find($idCategoria);
                $categoriaFolder = isset($categoria['nombre']) ? str_replace(' ', '', ucwords(strtolower($categoria['nombre']))) : '';
                $uploadPath = FCPATH . 'uploads/' . $categoriaFolder;

                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $fotoName = 'principal_' . $file->getRandomName();
                $file->move($uploadPath, $fotoName);
            }
        }

        // Insertar registro
        $nuevoProducto = [
            'codigo_sku'   => $sku,
            'descripcion'  => $descripcion,
            'id_categoria' => $idCategoria,
            'precio'       => $precio,
            'precio_promo' => $precioPromo,
            'stock'        => $stock,
            'foto'         => $fotoName,
            'masDetalle'   => $masDetalle
        ];

        if ($this->productoModel->insert($nuevoProducto)) {
            return redirect()->to(base_url('admin/productos'))->with('success', 'Producto registrado exitosamente.');
        }

        return redirect()->back()->withInput()->with('error', 'Ocurrió un error al registrar el producto.');
    }

    public function actualizarOrden($id)
    {
        $imagen = $this->inventarioImagenesModel->find($id);

        if (!$imagen) {
            return redirect()->back()->with('error', 'Imagen de galería no encontrada.');
        }

        $orden = (int)$this->request->getPost('orden');

        if ($orden >= 1) {
            $this->inventarioImagenesModel->update($id, [
                'orden' => $orden
            ]);

            return redirect()->back()->with('success', 'Orden de la imagen actualizado correctamente.');
        }

        return redirect()->back()->with('error', 'El número de orden debe ser mayor o igual a 1.');
    }
}

