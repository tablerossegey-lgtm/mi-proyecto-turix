<?php

namespace App\Models;

use CodeIgniter\Model;

class InventarioUbicacionModel extends Model
{
    protected $table = 't_inventario_ubicaciones';
    protected $returnType = 'array';
    protected $allowedFields = ['id_producto', 'id_ubicacion', 'cantidad'];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    /**
     * Obtiene el desglose de stock por ubicación para un producto específico.
     * Retorna un mapa del tipo [id_ubicacion => cantidad]
     *
     * @param int $idProducto
     * @return array
     */
    public function obtenerStockDesglosado(int $idProducto): array
    {
        $resultados = $this->where('id_producto', $idProducto)->findAll();
        $desglose = [];
        
        foreach ($resultados as $row) {
            $desglose[(int)$row['id_ubicacion']] = (int)$row['cantidad'];
        }
        
        return $desglose;
    }

    /**
     * Guarda o actualiza el stock de un producto en una ubicación específica.
     *
     * @param int $idProducto
     * @param int $idUbicacion
     * @param int $cantidad
     * @return bool
     */
    public function guardarStock(int $idProducto, int $idUbicacion, int $cantidad): bool
    {
        $existing = $this->where([
            'id_producto'  => $idProducto,
            'id_ubicacion' => $idUbicacion
        ])->first();

        if ($existing) {
            return $this->where([
                'id_producto'  => $idProducto,
                'id_ubicacion' => $idUbicacion
            ])->set(['cantidad' => $cantidad])->update();
        } else {
            return $this->insert([
                'id_producto'  => $idProducto,
                'id_ubicacion' => $idUbicacion,
                'cantidad'     => $cantidad
            ]);
        }
    }
}
