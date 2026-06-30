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
                    $subcategoria = 'navidad';
                } elseif (
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
                    $subcategoria = 'sanvalentin';
                } elseif (
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
                    $subcategoria = 'cumpleanos';
                } elseif (
                    strpos($descLower, 'padre') !== false ||
                    strpos($descLower, 'papá') !== false ||
                    strpos($descLower, 'papa') !== false
                ) {
                    $subcategoria = 'diadelpadre';
                } elseif (
                    strpos($descLower, 'halloween') !== false ||
                    strpos($descLower, 'bruja') !== false ||
                    strpos($descLower, 'calabaza') !== false ||
                    strpos($descLower, 'fantasma') !== false ||
                    strpos($descLower, 'terror') !== false ||
                    strpos($descLower, 'esqueleto') !== false ||
                    strpos($descLower, 'araña') !== false ||
                    strpos($descLower, 'arana') !== false ||
                    strpos($descLower, 'calavera') !== false
                ) {
                    $subcategoria = 'halloween';
                }
            }

            // Normalizar el nombre de la subcategoría para usar como clase CSS (sin acentos, minúsculas, sin espacios)
            $subcategoriaCss = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'ñ', ' '], 
                ['a', 'e', 'i', 'o', 'u', 'n', ''], 
                strtolower($subcategoria)
            );
            $p['foto_url'] = $srcUrl;
        ?>
        <div class="col-6 col-md-4 col-lg-3 col-xl-2 prod-filtrable prod-subcat-<?= $subcategoriaCss ?>">
            <div class="card h-100 card-producto">
                
                <?php if (isset($p['precio_promo']) && $p['precio_promo'] > 0 && $p['precio_promo'] < $p['precio']): ?>
                    <span class="badge bg-danger position-absolute" style="top: 15px; left: 15px; z-index: 10; font-size: 0.65rem; font-weight: bold; border-radius: 50px; padding: 4px 10px; letter-spacing: 0.5px;">PROMO</span>
                <?php endif; ?>

                <span class="sku-badge"><?= esc($p['codigo_sku']) ?></span>

                <div class="contenedor-foto">
                    <img src="<?= $srcUrl ?>"
                        class="img-fluid foto-producto" alt="<?= esc($p['descripcion']) ?>"
                        onerror="this.src='<?= base_url('uploads/SinImagen.png') ?>'; this.onerror=null;">
                    <button type="button" 
                            class="btn-quick-whatsapp shadow-sm"
                            title="Agregar al Carrito"
                            onclick="agregarAlCarritoRapido(<?= esc(json_encode($p)) ?>)">
                        <i class="bi bi-cart-plus"></i>
                    </button>
                </div>

                <div class="card-body">
                    <h6 class="descripcion-producto" title="<?= esc($p['descripcion']) ?>">
                        <?= esc($p['descripcion']) ?>
                    </h6>

                    <div class="precio-tag">
                        <?php if (isset($p['precio_promo']) && $p['precio_promo'] > 0 && $p['precio_promo'] < $p['precio']): ?>
                            <span class="simbolo-moneda">$</span>
                            <?= number_format($p['precio_promo'], 2) ?>
                            <span class="text-white-50 text-decoration-line-through ms-2 fs-6 fw-normal" style="font-size: 0.85rem;">
                                $<?= number_format($p['precio'], 2) ?>
                            </span>
                        <?php else: ?>
                            <span class="simbolo-moneda">$</span>
                            <?= number_format($p['precio'], 2) ?>
                        <?php endif; ?>
                    </div>
                    <button class="btn btn-turix w-100 shadow-sm fw-bold" 
                        data-bs-toggle="modal" 
                        data-bs-target="#modalDetalle"
                        hx-get="<?= base_url('catalogo/detalle/' . $p['id']) ?>" 
                        hx-target="#contenido-modal">
                        Ver Detalles <i class="bi bi-zoom-in ms-1"></i>
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
