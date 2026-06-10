<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductoModel extends Model
{
    protected $table = 't_inventario';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['codigo_sku', 'descripcion', 'id_categoria', 'precio', 'stock', 'foto', 'masDetalle', 'precio_promo'];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $afterInsert = ['inicializarStockUbicaciones'];
    protected $afterUpdate = ['sincronizarStockUbicaciones'];

    protected array $casts = [];
    protected array $castHandlers = [];

    /**
     * Obtiene todos los productos junto con el nombre de su categoría
     */
    public function obtenerTodosConCategoria()
    {
        return $this->select('t_inventario.*, t_categorias.nombre as nombre_categoria,
            (SELECT COALESCE(SUM(cantidad), 0) FROM t_inventario_ubicaciones WHERE id_producto = t_inventario.id AND id_ubicacion = 1) AS stock_casa,
            (SELECT COALESCE(SUM(cantidad), 0) FROM t_inventario_ubicaciones WHERE id_producto = t_inventario.id AND id_ubicacion = 2) AS stock_oficina')
            ->join('t_categorias', 't_categorias.idCategoria = t_inventario.id_categoria', 'left')
            ->orderBy('t_inventario.id', 'DESC')
            ->findAll();
    }

    /**
     * Obtiene los productos filtrados por una categoría específica, junto con su nombre de categoría
     */
    public function obtenerPorCategoria(int $categoriaId)
    {
        return $this->select('t_inventario.*, t_categorias.nombre as nombre_categoria,
            (SELECT COALESCE(SUM(cantidad), 0) FROM t_inventario_ubicaciones WHERE id_producto = t_inventario.id AND id_ubicacion = 1) AS stock_casa,
            (SELECT COALESCE(SUM(cantidad), 0) FROM t_inventario_ubicaciones WHERE id_producto = t_inventario.id AND id_ubicacion = 2) AS stock_oficina')
            ->join('t_categorias', 't_categorias.idCategoria = t_inventario.id_categoria')
            ->where('t_inventario.id_categoria', $categoriaId)
            ->orderBy('t_inventario.id', 'DESC')
            ->findAll();
    }

    /**
     * Busca productos por término, opcionalmente filtrando por categoría
     */
    public function buscarProductos(string $termino, ?int $categoriaId = null)
    {
        $builder = $this->select('t_inventario.*, t_categorias.nombre as nombre_categoria,
            (SELECT COALESCE(SUM(cantidad), 0) FROM t_inventario_ubicaciones WHERE id_producto = t_inventario.id AND id_ubicacion = 1) AS stock_casa,
            (SELECT COALESCE(SUM(cantidad), 0) FROM t_inventario_ubicaciones WHERE id_producto = t_inventario.id AND id_ubicacion = 2) AS stock_oficina')
                        ->join('t_categorias', 't_categorias.idCategoria = t_inventario.id_categoria', 'left');

        if (!empty($termino)) {
            $palabras = array_filter(explode(' ', preg_replace('/\s+/', ' ', trim($termino))));
            if (!empty($palabras)) {
                $builder->groupStart()
                            ->groupStart();
                                foreach ($palabras as $palabra) {
                                    $builder->like('t_inventario.descripcion', $palabra);
                                }
                $builder->groupEnd()
                            ->orLike('t_inventario.codigo_sku', $termino)
                        ->groupEnd();
            }
        }

        if ($categoriaId) {
            $builder->where('t_inventario.id_categoria', $categoriaId);
        }

        return $builder->orderBy('t_inventario.id', 'DESC')->findAll();
    }

    /**
     * Obtiene un producto por su ID junto con el nombre de su categoría
     */
    public function obtenerPorIdConCategoria(int $id)
    {
        return $this->select('t_inventario.*, t_categorias.nombre as nombre_categoria,
            (SELECT COALESCE(SUM(cantidad), 0) FROM t_inventario_ubicaciones WHERE id_producto = t_inventario.id AND id_ubicacion = 1) AS stock_casa,
            (SELECT COALESCE(SUM(cantidad), 0) FROM t_inventario_ubicaciones WHERE id_producto = t_inventario.id AND id_ubicacion = 2) AS stock_oficina')
                    ->join('t_categorias', 't_categorias.idCategoria = t_inventario.id_categoria', 'left')
                    ->find($id);
    }

    /**
     * Obtiene todos los productos con el conteo de imágenes adicionales en galería y filtros de búsqueda
     *
     * @param string|null $termino
     * @return array
     */
    public function obtenerTodosConConteoImagenes(?string $termino = null): array
    {
        $builder = $this->select('t_inventario.*, t_categorias.nombre as nombre_categoria, COUNT(t_inventario_imagenes.id) as total_imagenes, 
            (SELECT COALESCE(SUM(pe.cantidad), 0) FROM t_pedidos_encargados pe WHERE pe.id_producto = t_inventario.id AND pe.estado = \'Pendiente\') as total_encargos_pendientes,
            (SELECT COALESCE(SUM(cantidad), 0) FROM t_inventario_ubicaciones WHERE id_producto = t_inventario.id AND id_ubicacion = 1) AS stock_casa,
            (SELECT COALESCE(SUM(cantidad), 0) FROM t_inventario_ubicaciones WHERE id_producto = t_inventario.id AND id_ubicacion = 2) AS stock_oficina')
            ->join('t_categorias', 't_categorias.idCategoria = t_inventario.id_categoria', 'left')
            ->join('t_inventario_imagenes', 't_inventario_imagenes.id_producto = t_inventario.id', 'left')
            ->groupBy('t_inventario.id')
            ->orderBy('t_inventario.id', 'DESC');

        if (!empty($termino)) {
            $palabras = array_filter(explode(' ', preg_replace('/\s+/', ' ', trim($termino))));
            if (!empty($palabras)) {
                $builder->groupStart()
                            ->groupStart();
                                foreach ($palabras as $palabra) {
                                    $builder->like('t_inventario.descripcion', $palabra);
                                }
                $builder->groupEnd()
                            ->orLike('t_inventario.codigo_sku', $termino)
                        ->groupEnd();
            }
        }

        return $builder->findAll();
    }

    /**
     * Inicializa el stock en la ubicación por defecto (Oficina - ID 2) al crear un producto.
     */
    protected function inicializarStockUbicaciones(array $data)
    {
        if (isset($data['data']['stock']) && isset($data['id'])) {
            $idProducto = $data['id'];
            $stock = (int)$data['data']['stock'];

            $inventarioUbicacionModel = new \App\Models\InventarioUbicacionModel();
            $inventarioUbicacionModel->guardarStock($idProducto, 1, 0);
            $inventarioUbicacionModel->guardarStock($idProducto, 2, $stock);
        }
        return $data;
    }

    /**
     * Sincroniza el stock de las ubicaciones cuando el stock global del producto es modificado.
     */
    protected function sincronizarStockUbicaciones(array $data)
    {
        if (isset($data['data']['stock']) && !empty($data['id'])) {
            $inventarioUbicacionModel = new \App\Models\InventarioUbicacionModel();
            $nuevoStockGlobal = (int)$data['data']['stock'];

            $ids = is_array($data['id']) ? $data['id'] : [$data['id']];

            foreach ($ids as $idProducto) {
                // Consultar la suma actual en ubicaciones
                $desglose = $inventarioUbicacionModel->obtenerStockDesglosado($idProducto);
                $sumaUbicaciones = (int)array_sum($desglose);

                $diferencia = $nuevoStockGlobal - $sumaUbicaciones;
                if ($diferencia === 0) {
                    continue;
                }

                $stockCasa = $desglose[1] ?? 0;
                $stockOficina = $desglose[2] ?? 0;

                if ($diferencia > 0) {
                    // Sumar diferencia a Oficina (ID 2)
                    $stockOficina += $diferencia;
                } else {
                    // Restar diferencia (en valor absoluto)
                    $aRestar = abs($diferencia);
                    
                    // Restar primero de Oficina
                    if ($stockOficina >= $aRestar) {
                        $stockOficina -= $aRestar;
                        $aRestar = 0;
                    } else {
                        $aRestar -= $stockOficina;
                        $stockOficina = 0;
                    }

                    // Si queda por restar, restar de Casa
                    if ($aRestar > 0) {
                        if ($stockCasa >= $aRestar) {
                            $stockCasa -= $aRestar;
                            $aRestar = 0;
                        } else {
                            $stockCasa = max(0, $stockCasa - $aRestar);
                        }
                    }
                }

                // Guardar cambios en la base de datos
                $inventarioUbicacionModel->guardarStock($idProducto, 1, $stockCasa);
                $inventarioUbicacionModel->guardarStock($idProducto, 2, $stockOficina);
            }
        }
        return $data;
    }
}
