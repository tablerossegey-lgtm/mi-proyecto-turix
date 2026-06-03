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
