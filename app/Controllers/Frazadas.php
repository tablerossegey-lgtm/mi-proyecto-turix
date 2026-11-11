<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Frazadas extends BaseController
{
    protected string $baseDir;
    protected string $publicDir;
    protected int $porPagina = 24;

    public function __construct()
    {
        $this->publicDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'Encargos' . DIRECTORY_SEPARATOR . 'Frazadas';
    }

    /**
     * Obtiene todos los archivos de frazadas ordenados y con metadatos
     */
    private function obtenerTodasLasFrazadas(): array
    {
        if (!is_dir($this->publicDir)) {
            return [];
        }

        $frazadas = [];
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'jfif'];

        // Escanear directorio raíz y subdirectorios (si existen categorías)
        $items = scandir($this->publicDir);
        natcasesort($items);
        $contador = 1;

        // Primero revisamos si hay subcarpetas o si todos los archivos están en la raíz
        $subcarpetas = [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..')
                continue;
            $fullPath = $this->publicDir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($fullPath)) {
                $subcarpetas[] = $item;
            }
        }
        natcasesort($subcarpetas);

        // Archivos en la raíz
        foreach ($items as $file) {
            if ($file === '.' || $file === '..')
                continue;
            $filePath = $this->publicDir . DIRECTORY_SEPARATOR . $file;
            if (is_file($filePath)) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, $allowedExts)) {
                    // Extraer número del archivo si viene en el nombre (ej: frazada-045.jpeg -> 045)
                    if (preg_match('/(\d+)/', $file, $matches)) {
                        $numInt = (int) $matches[1];
                        $numeroFormateado = str_pad((string) $numInt, 3, '0', STR_PAD_LEFT);
                    } else {
                        $numInt = $contador;
                        $numeroFormateado = str_pad((string) $contador, 3, '0', STR_PAD_LEFT);
                    }

                    $frazadas[] = [
                        'id' => $numInt,
                        'numero' => $numeroFormateado,
                        'titulo' => 'Modelo #' . $numeroFormateado,
                        'archivo' => $file,
                        'categoria' => 'General',
                        'url' => base_url('uploads/Encargos/Frazadas/' . rawurlencode($file)),
                        'mtime' => filemtime($filePath)
                    ];
                    $contador++;
                }
            }
        }

        // Archivos en subcarpetas (si el usuario las organiza a futuro)
        foreach ($subcarpetas as $sub) {
            $subPath = $this->publicDir . DIRECTORY_SEPARATOR . $sub;
            $subFiles = scandir($subPath);
            foreach ($subFiles as $file) {
                if ($file === '.' || $file === '..')
                    continue;
                $filePath = $subPath . DIRECTORY_SEPARATOR . $file;
                if (is_file($filePath)) {
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (in_array($ext, $allowedExts)) {
                        $numeroFormateado = str_pad((string) $contador, 3, '0', STR_PAD_LEFT);
                        $frazadas[] = [
                            'id' => $contador,
                            'numero' => $numeroFormateado,
                            'titulo' => 'Modelo #' . $numeroFormateado,
                            'archivo' => $file,
                            'categoria' => $sub,
                            'url' => base_url('uploads/Encargos/Frazadas/' . rawurlencode($sub) . '/' . rawurlencode($file)),
                            'mtime' => filemtime($filePath)
                        ];
                        $contador++;
                    }
                }
            }
        }

        return $frazadas;
    }

    /**
     * Vista principal del catálogo de Frazadas
     */
    public function index()
    {
        $todas = $this->obtenerTodasLasFrazadas();
        $totalItems = count($todas);

        $q = trim((string) ($this->request->getGet('q') ?? ''));
        $cat = trim((string) ($this->request->getGet('cat') ?? ''));
        $pagina = max(1, (int) ($this->request->getGet('p') ?? 1));

        // Filtrado si aplica
        $filtradas = $this->filtrarFrazadas($todas, $q, $cat);
        $totalFiltradas = count($filtradas);

        // Paginación
        $totalPaginas = max(1, (int) ceil($totalFiltradas / $this->porPagina));
        if ($pagina > $totalPaginas) {
            $pagina = $totalPaginas;
        }

        $offset = ($pagina - 1) * $this->porPagina;
        $itemsPagina = array_slice($filtradas, $offset, $this->porPagina);

        // Extraer categorías únicas
        $categorias = array_values(array_unique(array_column($todas, 'categoria')));

        $data = [
            'frazadas' => $itemsPagina,
            'totalFrazadas' => $totalItems,
            'totalFiltradas' => $totalFiltradas,
            'paginaActual' => $pagina,
            'totalPaginas' => $totalPaginas,
            'porPagina' => $this->porPagina,
            'busqueda' => $q,
            'categoriaActiva' => $cat,
            'categorias' => $categorias,
            'telefonoWa' => '529995441466',
            'titulo' => 'Catálogo de Frazadas por Encargo'
        ];

        if ($this->request->getHeaderLine('HX-Request') && !$this->request->getHeaderLine('HX-Boosted')) {
            return view('frazadas/_grid', $data);
        }

        return view('frazadas/index', $data);
    }

    /**
     * Búsqueda en vivo (HTMX)
     */
    public function buscar()
    {
        $todas = $this->obtenerTodasLasFrazadas();
        $q = trim((string) ($this->request->getPost('q') ?? $this->request->getGet('q') ?? ''));
        $cat = trim((string) ($this->request->getPost('cat') ?? $this->request->getGet('cat') ?? ''));
        $pagina = max(1, (int) ($this->request->getPost('p') ?? $this->request->getGet('p') ?? 1));

        $filtradas = $this->filtrarFrazadas($todas, $q, $cat);
        $totalFiltradas = count($filtradas);

        $totalPaginas = max(1, (int) ceil($totalFiltradas / $this->porPagina));
        if ($pagina > $totalPaginas) {
            $pagina = $totalPaginas;
        }

        $offset = ($pagina - 1) * $this->porPagina;
        $itemsPagina = array_slice($filtradas, $offset, $this->porPagina);

        $data = [
            'frazadas' => $itemsPagina,
            'totalFrazadas' => count($todas),
            'totalFiltradas' => $totalFiltradas,
            'paginaActual' => $pagina,
            'totalPaginas' => $totalPaginas,
            'porPagina' => $this->porPagina,
            'busqueda' => $q,
            'categoriaActiva' => $cat,
            'categorias' => array_values(array_unique(array_column($todas, 'categoria'))),
            'telefonoWa' => '529995441466',
            'titulo' => 'Catálogo de Frazadas por Encargo'
        ];

        return view('frazadas/_grid', $data);
    }

    /**
     * Lógica de filtro por término de búsqueda y categoría
     */
    private function filtrarFrazadas(array $frazadas, string $q, string $cat): array
    {
        $qLimpio = ltrim(trim($q), '#');

        return array_filter($frazadas, function ($item) use ($qLimpio, $cat) {
            // Filtro por categoría
            if (!empty($cat) && strcasecmp($item['categoria'], $cat) !== 0) {
                return false;
            }

            // Filtro por búsqueda (número de modelo o título)
            if (!empty($qLimpio)) {
                $numStr = (string) $item['id'];
                $numPad = $item['numero'];
                $titulo = strtolower($item['titulo']);
                $qLower = strtolower($qLimpio);

                $coincideNumero = (strpos($numStr, $qLimpio) !== false) || (strpos($numPad, $qLimpio) !== false);
                $coincideTitulo = (strpos($titulo, $qLower) !== false);

                return $coincideNumero || $coincideTitulo;
            }

            return true;
        });
    }
}

