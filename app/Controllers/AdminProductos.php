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
        $id_categoria = $this->request->getVar('id_categoria');
        
        $productos = $this->productoModel->obtenerTodosConConteoImagenes($q, $id_categoria)->paginate(15);
        $pager = $this->productoModel->pager;

        $params = [];
        if ($q !== null && $q !== '') {
            $params['q'] = $q;
        }
        if ($id_categoria !== null && $id_categoria !== '') {
            $params['id_categoria'] = $id_categoria;
        }
        if (!empty($params)) {
            $pager->only(array_keys($params));
        }

        $categorias = $this->categoriaModel->orderBy('nombre', 'ASC')->findAll();

        $data = [
            'productos' => $productos,
            'pager' => $pager,
            'categorias' => $categorias,
            'q' => $q,
            'id_categoria' => $id_categoria
        ];

        if ($this->request->getHeaderLine('HX-Request') && $this->request->getHeaderLine('HX-Target') === 'productos-tabla-wrapper') {
            return view('admin_productos/_tabla_productos', $data);
        }

        $data['titulo'] = 'Administración de Galerías de Productos';
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
            // Obtener el orden máximo actual para este producto
            $maxOrdenRow = $this->inventarioImagenesModel->where('id_producto', $producto['id'])
                                                  ->selectMax('orden')
                                                  ->first();
            $siguienteOrden = isset($maxOrdenRow['orden']) ? ((int)$maxOrdenRow['orden'] + 1) : 1;

            $uploadedCount = 0;
            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/quicktime'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov'];

            foreach ($files['imagenes'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $mimeType = $file->getMimeType();
                    $extension = strtolower($file->getClientExtension());

                    if (in_array($mimeType, $allowedMimes) || in_array($extension, $allowedExtensions)) {
                        // Determinar ruta de subida (si detecta fantasma/fantasmita va a Festividades/Halloween)
                        $uploadPath = $this->determinarUploadPath($producto, $file->getClientName());

                        if (!is_dir($uploadPath)) {
                            mkdir($uploadPath, 0755, true);
                        }

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
                return redirect()->back()->with('success', "Se guardaron {$uploadedCount} archivo(s) correctamente en la galería.");
            }
        }

        return redirect()->back()->with('error', 'No se seleccionaron archivos válidos (formatos permitidos: JPG, PNG, MP4).');
    }

    public function eliminarImagen($id)
    {
        $imagen = $this->inventarioImagenesModel->find($id);

        if (!$imagen) {
            return redirect()->back()->with('error', 'Imagen no encontrada.');
        }

        $producto = $this->productoModel->obtenerPorIdConCategoria((int)$imagen['id_producto']);

        if ($producto) {
            $uploadPath = $this->determinarUploadPath($producto, $imagen['ruta_foto']);
            $filePath = $uploadPath . '/' . $imagen['ruta_foto'];
            
            // Eliminar archivo físico
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            // También verificar en Halloween directamente
            $halloweenPath = FCPATH . 'uploads/Festividades/Halloween/' . $imagen['ruta_foto'];
            if (file_exists($halloweenPath)) {
                @unlink($halloweenPath);
            }
            
            // Intentar borrar también del directorio raíz por si acaso
            $categoriaFolder = isset($producto['nombre_categoria']) ? str_replace(' ', '', ucwords(strtolower($producto['nombre_categoria']))) : '';
            if (!empty($categoriaFolder)) {
                $rootPath = FCPATH . 'uploads/' . $categoriaFolder . '/' . $imagen['ruta_foto'];
                if (file_exists($rootPath)) {
                    @unlink($rootPath);
                }
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
            if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                // Obtener el producto con su categoría
                $productoConCat = $this->productoModel->obtenerPorIdConCategoria((int)$id);
                $uploadPath = $this->determinarUploadPath($productoConCat ?: $producto, $file->getClientName());

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
                        if (file_exists(FCPATH . 'uploads/Festividades/Halloween/' . $oldFoto)) {
                            @unlink(FCPATH . 'uploads/Festividades/Halloween/' . $oldFoto);
                        }
                        $categoriaFolder = isset($productoConCat['nombre_categoria']) ? str_replace(' ', '', ucwords(strtolower($productoConCat['nombre_categoria']))) : '';
                        if (!empty($categoriaFolder)) {
                            $rootOldPath = FCPATH . 'uploads/' . $categoriaFolder . '/' . $oldFoto;
                            if (file_exists($rootOldPath)) {
                                @unlink($rootOldPath);
                            }
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
        $fechaInicioPromo = !empty($this->request->getPost('fecha_inicio_promo')) ? $this->request->getPost('fecha_inicio_promo') : null;
        $fechaFinPromo = !empty($this->request->getPost('fecha_fin_promo')) ? $this->request->getPost('fecha_fin_promo') : null;
        $stockCasaPost = $this->request->getPost('stock_casa');
        $stockOficinaPost = $this->request->getPost('stock_oficina');
        $masDetalle = $this->request->getPost('masDetalle') ?: null;

        // Validaciones básicas
        if (empty($sku) || empty($descripcion) || empty($idCategoria) || $precio === null || $stockCasaPost === null || $stockOficinaPost === null) {
            return redirect()->back()->withInput()->with('error', 'Por favor complete todos los campos obligatorios.');
        }

        $stockCasa = (int)$stockCasaPost;
        $stockOficina = (int)$stockOficinaPost;
        $stock = $stockCasa + $stockOficina;

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
            if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                $categoria = $this->categoriaModel->find($idCategoria);
                $prodTemp = [
                    'descripcion' => $descripcion,
                    'codigo_sku' => $sku,
                    'nombre_categoria' => $categoria['nombre'] ?? ''
                ];
                $uploadPath = $this->determinarUploadPath($prodTemp, $file->getClientName());

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
            'fecha_inicio_promo' => $fechaInicioPromo,
            'fecha_fin_promo'    => $fechaFinPromo,
            'stock'        => $stock,
            'foto'         => $fotoName,
            'masDetalle'   => $masDetalle
        ];

        if ($this->productoModel->insert($nuevoProducto)) {
            $idNuevo = $this->productoModel->getInsertID();
            if ($idNuevo) {
                $inventarioUbicacionModel = new \App\Models\InventarioUbicacionModel();
                $inventarioUbicacionModel->guardarStock($idNuevo, 1, $stockCasa);
                $inventarioUbicacionModel->guardarStock($idNuevo, 2, $stockOficina);
            }
            return redirect()->to(base_url('admin/productos'))->with('success', 'Producto registrado exitosamente.');
        }

        return redirect()->back()->withInput()->with('error', 'Ocurrió un error al registrar el producto.');
    }

    public function editar($id)
    {
        // Optimización: Obtener el producto junto con el nombre de su categoría en una sola consulta
        $producto = $this->productoModel->obtenerPorIdConCategoria((int)$id);

        if (!$producto) {
            return redirect()->to(base_url('admin/productos'))->with('error', 'Producto no encontrado.');
        }

        $sku = $this->request->getPost('codigo_sku');
        $descripcion = $this->request->getPost('descripcion');
        $idCategoria = $this->request->getPost('id_categoria');
        $precio = $this->request->getPost('precio');
        $precioPromo = $this->request->getPost('precio_promo') ?: 0.00;
        $fechaInicioPromo = !empty($this->request->getPost('fecha_inicio_promo')) ? $this->request->getPost('fecha_inicio_promo') : null;
        $fechaFinPromo = !empty($this->request->getPost('fecha_fin_promo')) ? $this->request->getPost('fecha_fin_promo') : null;
        $stockCasaPost = $this->request->getPost('stock_casa');
        $stockOficinaPost = $this->request->getPost('stock_oficina');
        $masDetalle = $this->request->getPost('masDetalle') ?: null;

        $oldIdCategoria = $producto['id_categoria'];

        // Validaciones básicas
        if (empty($sku) || empty($descripcion) || empty($idCategoria) || $precio === null || $stockCasaPost === null || $stockOficinaPost === null) {
            return redirect()->back()->withInput()->with('error', 'Por favor complete todos los campos obligatorios.');
        }

        $stockCasa = (int)$stockCasaPost;
        $stockOficina = (int)$stockOficinaPost;
        $stock = $stockCasa + $stockOficina;

        // Optimización: Validar SKU único únicamente si el SKU fue modificado
        if ($sku !== $producto['codigo_sku']) {
            $existing = $this->productoModel->where('codigo_sku', $sku)->where('id !=', $id)->first();
            if ($existing) {
                return redirect()->back()->withInput()->with('error', 'El SKU ingresado ya se encuentra registrado por otro producto.');
            }
        }

        // Procesar foto principal
        $fotoName = $producto['foto'];
        $file = $this->request->getFile('foto_principal');
        $hasNewFile = ($file && $file->isValid() && !$file->hasMoved());

        $categoriaNombre = $producto['nombre_categoria'] ?? '';
        if ($idCategoria != $oldIdCategoria) {
            $catRow = $this->categoriaModel->find($idCategoria);
            if ($catRow) {
                $categoriaNombre = $catRow['nombre'] ?? '';
            }
        }

        $oldUploadPath = $this->determinarUploadPath($producto);
        $prodNew = [
            'descripcion'      => $descripcion,
            'codigo_sku'       => $sku,
            'nombre_categoria' => $categoriaNombre
        ];
        $newUploadPath = $this->determinarUploadPath($prodNew, $hasNewFile ? $file->getClientName() : null);

        if ($hasNewFile) {
            $mimeType = $file->getMimeType();
            if (in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                if (!is_dir($newUploadPath)) {
                    mkdir($newUploadPath, 0755, true);
                }

                // Eliminar foto anterior si existía localmente y no es la por defecto
                $oldFoto = $producto['foto'] ?? '';
                if (!empty($oldFoto)) {
                    $isUrl = (strpos($oldFoto, 'http://') === 0 || strpos($oldFoto, 'https://') === 0);
                    if (!$isUrl && $oldFoto !== 'SinImagen.png') {
                        $oldFileFullPath = $oldUploadPath . '/' . $oldFoto;
                        if (file_exists($oldFileFullPath)) {
                            @unlink($oldFileFullPath);
                        }
                        if (file_exists(FCPATH . 'uploads/Festividades/Halloween/' . $oldFoto)) {
                            @unlink(FCPATH . 'uploads/Festividades/Halloween/' . $oldFoto);
                        }
                    }
                }

                $fotoName = 'principal_' . $file->getRandomName();
                $file->move($newUploadPath, $fotoName);
            }
        } else {
            // Si la ruta calculada cambió y ya tiene foto, mover la foto física
            if ($newUploadPath !== $oldUploadPath && !empty($fotoName)) {
                $isUrl = (strpos($fotoName, 'http://') === 0 || strpos($fotoName, 'https://') === 0);
                if (!$isUrl && $fotoName !== 'SinImagen.png') {
                    $oldFileFullPath = $oldUploadPath . '/' . $fotoName;
                    $newFileFullPath = $newUploadPath . '/' . $fotoName;

                    if (file_exists($oldFileFullPath)) {
                        if (!is_dir($newUploadPath)) {
                            mkdir($newUploadPath, 0755, true);
                        }
                        @rename($oldFileFullPath, $newFileFullPath);
                    }
                }
            }
        }

        // Actualizar registro
        $datosActualizar = [
            'codigo_sku'   => $sku,
            'descripcion'  => $descripcion,
            'id_categoria' => $idCategoria,
            'precio'       => $precio,
            'precio_promo' => $precioPromo,
            'fecha_inicio_promo' => $fechaInicioPromo,
            'fecha_fin_promo'    => $fechaFinPromo,
            'stock'        => $stock,
            'foto'         => $fotoName,
            'masDetalle'   => $masDetalle
        ];

        // Actualizar tabla intermedia de ubicaciones primero
        $inventarioUbicacionModel = new \App\Models\InventarioUbicacionModel();
        $inventarioUbicacionModel->guardarStock((int)$id, 1, $stockCasa);
        $inventarioUbicacionModel->guardarStock((int)$id, 2, $stockOficina);

        if ($this->productoModel->update($id, $datosActualizar)) {
            return redirect()->to(base_url('admin/productos'))->with('success', 'Producto actualizado exitosamente.');
        }

        return redirect()->back()->withInput()->with('error', 'Ocurrió un error al actualizar el producto.');
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

    /**
     * Determina la carpeta de subida para las fotos de un producto.
     * Si detecta la palabra "fantasma" o "fantasmita" (en descripción, SKU o nombre de archivo),
     * o si pertenece a Halloween, siempre devuelve la carpeta 'Festividades/Halloween'.
     */
    private function determinarUploadPath(array $producto, ?string $clientFilename = null): string
    {
        $desc = $producto['descripcion'] ?? '';
        $sku = $producto['codigo_sku'] ?? '';
        $categoriaFolder = isset($producto['nombre_categoria']) 
            ? str_replace(' ', '', ucwords(strtolower($producto['nombre_categoria']))) 
            : '';

        $textos = strtolower($desc . ' ' . $sku . ' ' . ($clientFilename ?? ''));

        // Detección prioritaria: si detecta fantasma o fantasmita
        if (
            strpos($textos, 'fantasma') !== false ||
            strpos($textos, 'fantasmita') !== false ||
            strpos($textos, 'fantasmas') !== false ||
            strpos($textos, 'fantasmitas') !== false
        ) {
            return FCPATH . 'uploads/Festividades/Halloween';
        }

        // Si la categoría es Festividades, resolver subcarpeta
        if (strtolower($categoriaFolder) === 'festividades') {
            $subfolder = $this->obtenerSubfolderFestividades($desc);
            $path = FCPATH . 'uploads/Festividades';
            if (!empty($subfolder)) {
                $path .= '/' . $subfolder;
            }
            return $path;
        }

        // Si no es categoría Festividades pero en el texto se detecta Halloween, calabaza, bruja, etc.
        if (
            strpos($textos, 'halloween') !== false ||
            strpos($textos, 'calabaza') !== false ||
            strpos($textos, 'bruja') !== false
        ) {
            return FCPATH . 'uploads/Festividades/Halloween';
        }

        $baseFolder = !empty($categoriaFolder) ? $categoriaFolder : 'SinCategoria';
        return FCPATH . 'uploads/' . $baseFolder;
    }

    /**
     * Detecta si un producto de Festividades pertenece a Fiestas Patrias, Navidad, SanValentín, etc.
     * basándose en su descripción (mismo criterio del catálogo).
     */
    private function obtenerSubfolderFestividades(string $descripcion): string
    {
        $descLower = strtolower($descripcion);

        // 1. Halloween tiene prioridad alta (especialmente fantasma / fantasmita)
        if (
            strpos($descLower, 'halloween') !== false ||
            strpos($descLower, 'fantasma') !== false ||
            strpos($descLower, 'fantasmita') !== false ||
            strpos($descLower, 'fantasmas') !== false ||
            strpos($descLower, 'fantasmitas') !== false ||
            strpos($descLower, 'bruja') !== false ||
            strpos($descLower, 'calabaza') !== false ||
            strpos($descLower, 'terror') !== false ||
            strpos($descLower, 'esqueleto') !== false ||
            strpos($descLower, 'araña') !== false ||
            strpos($descLower, 'arana') !== false ||
            strpos($descLower, 'calavera') !== false
        ) {
            return 'Halloween';
        }

        if (
            strpos($descLower, 'patrio') !== false || 
            strpos($descLower, 'patria') !== false || 
            strpos($descLower, 'patriótico') !== false || 
            strpos($descLower, 'patriotico') !== false || 
            strpos($descLower, 'patriótica') !== false || 
            strpos($descLower, 'patriotica') !== false || 
            strpos($descLower, 'independencia') !== false || 
            strpos($descLower, 'septiembre') !== false || 
            strpos($descLower, 'mexico') !== false || 
            strpos($descLower, 'méxico') !== false || 
            strpos($descLower, 'mexicano') !== false || 
            strpos($descLower, 'mexicana') !== false || 
            strpos($descLower, 'viva mex') !== false || 
            strpos($descLower, 'tricolor') !== false || 
            strpos($descLower, 'bandera') !== false ||
            strpos($descLower, 'banderita') !== false ||
            strpos($descLower, 'banderines') !== false ||
            strpos($descLower, 'grito') !== false ||
            strpos($descLower, 'rehilete') !== false
        ) {
            return 'Fiestas Patrias';
        }

        if (
            strpos($descLower, 'navidad') !== false || 
            strpos($descLower, 'navideñ') !== false || 
            strpos($descLower, 'navide') !== false || 
            strpos($descLower, 'grinch') !== false || 
            strpos($descLower, 'santa') !== false || 
            strpos($descLower, 'nochebuena') !== false ||
            strpos($descLower, 'luces') !== false ||
            strpos($descLower, 'serie') !== false ||
            strpos($descLower, 'led') !== false ||
            strpos($descLower, 'campana') !== false ||
            strpos($descLower, 'esfera') !== false ||
            strpos($descLower, 'guirnalda') !== false ||
            strpos($descLower, 'pino') !== false ||
            strpos($descLower, 'baston') !== false ||
            strpos($descLower, 'bastón') !== false ||
            strpos($descLower, 'copo') !== false ||
            strpos($descLower, 'reno') !== false
        ) {
            return 'Navidad';
        }
        
        if (
            strpos($descLower, 'valentin') !== false || 
            strpos($descLower, 'valentín') !== false || 
            strpos($descLower, 'amor') !== false || 
            strpos($descLower, 'te amo') !== false || 
            strpos($descLower, 'te_amo') !== false || 
            strpos($descLower, 'corazón') !== false || 
            strpos($descLower, 'corazon') !== false || 
            strpos($descLower, 'amistad') !== false ||
            strpos($descLower, 'vela') !== false ||
            strpos($descLower, 'flor') !== false ||
            strpos($descLower, 'rosa') !== false ||
            strpos($descLower, 'rosas') !== false ||
            strpos($descLower, 'peluche') !== false ||
            strpos($descLower, 'ramo') !== false ||
            strpos($descLower, 'bouquet') !== false ||
            strpos($descLower, 'lazo') !== false ||
            strpos($descLower, 'listón') !== false ||
            strpos($descLower, 'liston') !== false ||
            strpos($descLower, 'chocolate') !== false ||
            strpos($descLower, 'romantico') !== false ||
            strpos($descLower, 'romántico') !== false ||
            strpos($descLower, 'romantica') !== false ||
            strpos($descLower, 'romántica') !== false ||
            strpos($descLower, 'cupido') !== false
        ) {
            return 'San Valentín';
        }
        
        if (
            strpos($descLower, 'cumpleaños') !== false ||
            strpos($descLower, 'cumpleanos') !== false ||
            strpos($descLower, 'pastel') !== false ||
            strpos($descLower, 'globo') !== false ||
            strpos($descLower, 'globos') !== false ||
            strpos($descLower, 'confeti') !== false ||
            strpos($descLower, 'letrero') !== false ||
            strpos($descLower, 'cortina') !== false ||
            strpos($descLower, 'flecos') !== false ||
            strpos($descLower, 'bolsa de regalo') !== false ||
            strpos($descLower, 'holográfica') !== false ||
            strpos($descLower, 'holografica') !== false ||
            strpos($descLower, 'metalizada') !== false ||
            strpos($descLower, 'piñata') !== false ||
            strpos($descLower, 'pinata') !== false ||
            strpos($descLower, 'festejo') !== false ||
            strpos($descLower, 'celebración') !== false ||
            strpos($descLower, 'celebracion') !== false ||
            strpos($descLower, 'fiesta') !== false ||
            strpos($descLower, 'velita') !== false ||
            strpos($descLower, 'velitas') !== false ||
            strpos($descLower, 'decoración') !== false ||
            strpos($descLower, 'decoracion') !== false
        ) {
            return 'Cumpleaños';
        }

        if (
            strpos($descLower, 'padre') !== false ||
            strpos($descLower, 'papá') !== false ||
            strpos($descLower, 'papa') !== false
        ) {
            return 'Día del Padre';
        }
        
        return '';
    }
}

