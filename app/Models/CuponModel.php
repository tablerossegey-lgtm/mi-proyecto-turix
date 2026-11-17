<?php

namespace App\Models;

use CodeIgniter\Model;

class CuponModel extends Model
{
    protected $table            = 't_cupones';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'id_cliente',
        'codigo',
        'concepto',
        'tipo_descuento',
        'valor',
        'compra_minima',
        'descuento_maximo',
        'fecha_emision',
        'fecha_inicio',
        'fecha_vencimiento',
        'limite_usos',
        'usos_realizados',
        'motivo_origen',
        'observaciones',
        'estado',
        'cancelado_en',
        'cancelado_por',
        'motivo_cancelacion',
        'creado_por',
    ];

    protected $useTimestamps = false;

    /**
     * Obtiene los cupones de un cliente ordenados por fecha de emisión descendente.
     */
    public function obtenerPorCliente(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
            ->orderBy('fecha_emision', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * Verifica si un código de cupón ya existe (excluyendo opcionalmente un ID).
     */
    public function existeCodigo(string $codigo, ?int $excludeId = null): bool
    {
        $builder = $this->where('codigo', strtoupper(trim($codigo)));
        if ($excludeId !== null && $excludeId > 0) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->countAllResults() > 0;
    }
}

