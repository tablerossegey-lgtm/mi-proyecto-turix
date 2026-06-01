<?php if (!empty($productos)): ?>
    <?php foreach ($productos as $index => $p): ?>
        <?php 
            $foto = $p['foto'] ?? '';
            $isUrl = (strpos($foto, 'http://') === 0 || strpos($foto, 'https://') === 0);
            $subcategoria = 'otros'; // Por defecto
            
            // Si es URL, intentar extraer el nombre del archivo para ver si existe localmente
            $filename = $foto;
            if ($isUrl) {
                $filename = basename(parse_url($foto, PHP_URL_PATH));
            }
            
            $categoriaFolder = isset($p['nombre_categoria']) ? str_replace(' ', '', ucwords(strtolower($p['nombre_categoria']))) : '';
            $rutaImagen = 'uploads/SinImagen.png';
            
            if (!empty($filename)) {
                // 1. Intentar en la raíz de la categoría (ej: "uploads/Festividades/principal_xxx.jpg")
                $pathIntento = "uploads/{$categoriaFolder}/" . $filename;
                if (file_exists(FCPATH . $pathIntento)) {
                    $rutaImagen = $pathIntento;
                    $subcategoria = 'otros';
                } else {
                    // 2. Buscar en las subcarpetas físicas de esta categoría (ej: Navidad, SanValentín)
                    $dirPath = FCPATH . 'uploads/' . $categoriaFolder;
                    if (!empty($categoriaFolder) && is_dir($dirPath)) {
                        $files = scandir($dirPath);
                        foreach ($files as $file) {
                            if ($file !== '.' && $file !== '..' && is_dir($dirPath . '/' . $file)) {
                                $pathSub = "uploads/{$categoriaFolder}/{$file}/" . $filename;
                                if (file_exists(FCPATH . $pathSub)) {
                                    $rutaImagen = $pathSub;
                                    $subcategoria = strtolower(trim($file));
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            
            // Si no se encontró ningún archivo físico local pero era una URL, usar la URL como último recurso
            if ($rutaImagen === 'uploads/SinImagen.png' && $isUrl) {
                $srcUrl = $foto;
            } else {
                $srcUrl = base_url($rutaImagen);
            }

            // Fallback: Si se quedó como "otros", clasificar según palabras clave en la descripción
            if ($subcategoria === 'otros') {
                $descLower = strtolower($p['descripcion']);
                if (
                    strpos($descLower, 'navidad') !== false || 
                    strpos($descLower, 'navideñ') !== false || 
                    strpos($descLower, 'navide') !== false || 
                    strpos($descLower, 'grinch') !== false || 
                    strpos($descLower, 'santa') !== false || 
                    strpos($descLower, 'nochebuena') !== false
                ) {
                    $subcategoria = 'navidad';
                } elseif (
                    strpos($descLower, 'valentin') !== false || 
                    strpos($descLower, 'valentín') !== false || 
                    strpos($descLower, 'amor') !== false || 
                    strpos($descLower, 'corazón') !== false || 
                    strpos($descLower, 'corazon') !== false || 
                    strpos($descLower, 'amistad') !== false
                ) {
                    $subcategoria = 'sanvalentin';
                }
            }

            // Normalizar el nombre de la subcategoría para usar como clase CSS (sin acentos, minúsculas, sin espacios)
            $subcategoriaCss = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'ñ', ' '], 
                ['a', 'e', 'i', 'o', 'u', 'n', ''], 
                strtolower($subcategoria)
            );
        ?>
        <div class="col-6 col-md-4 col-lg-3 col-xl-2 prod-filtrable prod-subcat-<?= $subcategoriaCss ?>">
            <div class="card h-100 card-producto">
                
                <span class="sku-badge"><?= esc($p['codigo_sku']) ?></span>

                <div class="contenedor-foto">
                    <img src="<?= $srcUrl ?>"
                        class="img-fluid foto-producto" alt="<?= esc($p['descripcion']) ?>"
                        onerror="this.src='<?= base_url('uploads/SinImagen.png') ?>'; this.onerror=null;">
                </div>

                <div class="card-body">
                    <h6 class="descripcion-producto" title="<?= esc($p['descripcion']) ?>">
                        <?= esc($p['descripcion']) ?>
                    </h6>

                    <div class="precio-tag">
                        <span style="font-size: 1rem; opacity: 0.7;">$</span>
                        <?= number_format($p['precio'], 2) ?>
                    </div>
                    <button class="btn btn-turix w-100 shadow-sm fw-bold" 
                        data-bs-toggle="modal" 
                        data-bs-target="#modalDetalle"
                        hx-get="<?= base_url('catalogo/detalle/' . $p['id']) ?>" 
                        hx-target="#contenido-modal">
                        Ver Detalles <i class="fas fa-search-plus ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="col-12">
        <div class="alert alert-warning border-0 shadow-sm rounded-4 p-4 text-center">
            <div class="mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-warning opacity-50"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <h5 class="fw-bold">No hay productos disponibles</h5>
            <p class="text-muted mb-0">Estamos actualizando nuestro stock. Vuelve pronto.</p>
        </div>
    </div>
<?php endif; ?>
