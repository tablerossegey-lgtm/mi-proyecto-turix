<?php

namespace App\Models;

use CodeIgniter\Model;

class CuponUsoModel extends Model
{
    protected $table            = 't_cupones_usos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_cupon',
        'id_cliente',
        'folio_compra',
        'id_compra',
        'fecha_uso',
        'subtotal_original',
        'descuento_aplicado',
        'total_neto',
        'estado',
        'revertido_en',
        'revertido_por',
        'usuario_id',
        'usuario_nombre',
    ];

    protected $useTimestamps = false;

    /**
     * Obtiene el historial de usos de cupones para un cliente.
     */
    public function obtenerPorCliente(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
            ->orderBy('fecha_uso', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }
}

