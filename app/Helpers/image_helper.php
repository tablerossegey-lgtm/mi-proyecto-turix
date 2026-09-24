<?php

if (!function_exists('obtener_ruta_imagen')) {
    /**
     * Resuelve la ruta correcta de la imagen del producto, buscando en la raíz 
     * de la carpeta de la categoría y en sus subdirectorios (ej. Navidad, SanValentín).
     *
     * @param string|null $foto Nombre del archivo de foto
     * @param string|null $categoriaNombre Nombre de la categoría
     * @return string URL base de la imagen o placeholder
     */
    function obtener_ruta_imagen(?string $foto, ?string $categoriaNombre): string
    {
        if (empty($foto)) {
            return base_url('uploads/SinImagen.png');
        }

        $isUrl = (strpos($foto, 'http://') === 0 || strpos($foto, 'https://') === 0);
        $filename = $foto;
        if ($isUrl) {
            $filename = basename(parse_url($foto, PHP_URL_PATH));
        }

        $categoriaFolder = !empty($categoriaNombre) ? str_replace(' ', '', ucwords(strtolower($categoriaNombre))) : '';
        $rutaImagen = 'uploads/SinImagen.png';

        if (!empty($filename)) {
            // 1. Intentar en la raíz de la categoría (ej: "uploads/Festividades/principal_xxx.jpg")
            $pathIntento = "uploads/{$categoriaFolder}/" . $filename;
            if (file_exists(FCPATH . $pathIntento)) {
                $rutaImagen = $pathIntento;
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
                                break;
                            }
                        }
                    }
                }
            }

            if ($rutaImagen === 'uploads/SinImagen.png') {
                // 3. Buscar en Festividades/Halloween específicamente
                if (file_exists(FCPATH . 'uploads/Festividades/Halloween/' . $filename)) {
                    $rutaImagen = 'uploads/Festividades/Halloween/' . $filename;
                } else {
                    // 4. Buscar en cualquier otra subcarpeta de uploads
                    $uploadsDir = FCPATH . 'uploads';
                    if (is_dir($uploadsDir)) {
                        $folders = scandir($uploadsDir);
                        foreach ($folders as $folder) {
                            if ($folder !== '.' && $folder !== '..' && is_dir($uploadsDir . '/' . $folder) && $folder !== $categoriaFolder) {
                                if (file_exists($uploadsDir . '/' . $folder . '/' . $filename)) {
                                    $rutaImagen = 'uploads/' . $folder . '/' . $filename;
                                    break;
                                }
                                $subDirs = scandir($uploadsDir . '/' . $folder);
                                foreach ($subDirs as $sub) {
                                    if ($sub !== '.' && $sub !== '..' && is_dir($uploadsDir . '/' . $folder . '/' . $sub)) {
                                        if (file_exists($uploadsDir . '/' . $folder . '/' . $sub . '/' . $filename)) {
                                            $rutaImagen = 'uploads/' . $folder . '/' . $sub . '/' . $filename;
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Si no se encontró ningún archivo físico local pero era una URL, usar la URL como último recurso
        if ($rutaImagen === 'uploads/SinImagen.png' && $isUrl) {
            return $foto;
        }

        return base_url($rutaImagen);
    }
}

if (!function_exists('obtener_ruta_categoria')) {
    /**
     * Resuelve la ruta correcta de la imagen de la categoría.
     *
     * @param string|null $imagen Campo imagen de la categoría en la BD
     * @param string|null $categoriaNombre Nombre de la categoría
     * @return string URL base de la imagen o default SinCategoria.jpg
     */
    function obtener_ruta_categoria(?string $imagen, ?string $categoriaNombre): string
    {
        $catRuta = '';
        
        // 1. Usar el campo de la base de datos si existe el archivo y es .jpg
        if (!empty($imagen)) {
            $pathInfo = pathinfo($imagen);
            if (isset($pathInfo['extension']) && strtolower($pathInfo['extension']) === 'jpg') {
                $rutaBD = 'images/categorias/' . $imagen;
                if (file_exists(FCPATH . $rutaBD)) {
                    $catRuta = $rutaBD;
                }
            }
        }

        // 2. Si no, calcular dinámicamente el nombre según el nombre de la categoría (solo .jpg)
        if (empty($catRuta) && !empty($categoriaNombre)) {
            $unaccented = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ'], 
                ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N'], 
                $categoriaNombre
            );
            $baseName = str_replace(' ', '', ucwords(strtolower($unaccented)));
            
            // Check only .jpg
            $rutaDinamicaJpg = 'images/categorias/' . $baseName . '.jpg';
            
            if (file_exists(FCPATH . $rutaDinamicaJpg)) {
                $catRuta = $rutaDinamicaJpg;
            }
        }

        // 3. Si no existe ninguno, usar imagen por defecto (SinCategoria.jpg)
        if (empty($catRuta)) {
            $catRuta = 'images/categorias/SinCategoria.jpg';
        }

        return base_url($catRuta);
    }
}

if (!function_exists('es_video')) {
    /**
     * Determina si la ruta o nombre de archivo corresponde a un video.
     *
     * @param string|null $archivo
     * @return bool
     */
    function es_video(?string $archivo): bool
    {
        if (empty($archivo)) {
            return false;
        }
        $cleanPath = parse_url($archivo, PHP_URL_PATH) ?? $archivo;
        return (bool) preg_match('/\.(mp4|webm|ogg)$/i', $cleanPath);
    }
}

if (!function_exists('producto_promo_activa')) {
    /**
     * Determina si la promoción/precio especial de un producto está actualmente activa según la fecha de hoy.
     *
     * Reglas:
     * - Debe tener un precio_promo > 0 y menor que el precio normal.
     * - Si tiene fecha_inicio_promo, hoy debe ser >= fecha_inicio_promo.
     * - Si tiene fecha_fin_promo, hoy debe ser <= fecha_fin_promo.
     *
     * @param array $producto
     * @return bool
     */
    function producto_promo_activa(array $producto): bool
    {
        $precio = (float)($producto['precio'] ?? 0);
        $precioPromo = (float)($producto['precio_promo'] ?? 0);

        if ($precioPromo <= 0 || $precioPromo >= $precio) {
            return false;
        }

        $hoy = date('Y-m-d');
        $inicio = !empty($producto['fecha_inicio_promo']) ? trim(substr($producto['fecha_inicio_promo'], 0, 10)) : null;
        $fin = !empty($producto['fecha_fin_promo']) ? trim(substr($producto['fecha_fin_promo'], 0, 10)) : null;

        if ($inicio && $hoy < $inicio) {
            return false;
        }

        if ($fin && $hoy > $fin) {
            return false;
        }

        return true;
    }
}

if (!function_exists('preparar_producto_para_cliente')) {
    /**
     * Ajusta el producto para el catálogo público.
     * Si la promoción NO está activa (aún no empieza o ya venció):
     * - precio_promo se establece en 0.00
     * - en_promo se establece en false
     * De este modo, el cliente NO puede ver el precio especial en HTML, JS, ni DevTools antes del inicio.
     *
     * @param array $producto
     * @return array
     */
    function preparar_producto_para_cliente(array &$producto): array
    {
        $promoActiva = producto_promo_activa($producto);
        $producto['en_promo'] = $promoActiva;
        if (!$promoActiva) {
            $producto['precio_promo'] = 0.00;
        }
        return $producto;
    }
}

if (!function_exists('obtener_estado_promo_admin')) {
    /**
     * Devuelve el estado descriptivo de la promoción para el panel de administración.
     *
     * @param array $producto
     * @return array|null
     */
    function obtener_estado_promo_admin(array $producto): ?array
    {
        $precio = (float)($producto['precio'] ?? 0);
        $precioPromo = (float)($producto['precio_promo'] ?? 0);

        if ($precioPromo <= 0 || $precioPromo >= $precio) {
            return null;
        }

        $hoy = date('Y-m-d');
        $inicio = !empty($producto['fecha_inicio_promo']) ? trim(substr($producto['fecha_inicio_promo'], 0, 10)) : null;
        $fin = !empty($producto['fecha_fin_promo']) ? trim(substr($producto['fecha_fin_promo'], 0, 10)) : null;

        $fmtInicio = $inicio ? date('d/m/Y', strtotime($inicio)) : null;
        $fmtFin = $fin ? date('d/m/Y', strtotime($fin)) : null;

        if ($inicio && $hoy < $inicio) {
            return [
                'estado' => 'programada',
                'texto'  => "Programada: $" . number_format($precioPromo, 2) . " (inicia {$fmtInicio}" . ($fmtFin ? " al {$fmtFin}" : "") . ")",
                'badge'  => 'bg-info text-dark',
                'icono'  => 'fa-calendar-alt'
            ];
        }

        if ($fin && $hoy > $fin) {
            return [
                'estado' => 'finalizada',
                'texto'  => "Finalizada: $" . number_format($precioPromo, 2) . " (venció {$fmtFin})",
                'badge'  => 'bg-secondary text-white',
                'icono'  => 'fa-calendar-times'
            ];
        }

        $rango = "";
        if ($inicio && $fin) {
            $rango = " ({$fmtInicio} al {$fmtFin})";
        } elseif ($fin) {
            $rango = " (hasta {$fmtFin})";
        } elseif ($inicio) {
            $rango = " (desde {$fmtInicio})";
        }

        return [
            'estado' => 'activa',
            'texto'  => "OFERTA: $" . number_format($precioPromo, 2) . $rango,
            'badge'  => 'bg-danger text-white',
            'icono'  => 'fa-tag'
        ];
    }
}


