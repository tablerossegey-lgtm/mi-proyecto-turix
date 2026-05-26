<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarioImagenesModel extends Model
{
    protected $table      = 't_inventario_imagenes';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['id_producto', 'ruta_foto', 'orden'];

    // Fechas (manejadas automáticamente a nivel BD)
    protected $useTimestamps = false;

    /**
     * Obtiene todas las fotos asociadas a un producto ordenadas por prioridad
     *
     * @param int $productoId
     * @return array
     */
    public function obtenerPorProducto(int $productoId): array
    {
        return $this->where('id_producto', $productoId)
                    ->orderBy('orden', 'ASC')
                    ->orderBy('id', 'ASC')
                    ->findAll();
    }
}
