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

