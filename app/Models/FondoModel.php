<?php

namespace App\Models;

use CodeIgniter\Model;

class FondoModel extends Model
{
    protected $table            = 't_fondos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nombre',
        'descripcion',
        'porcentaje_sugerido',
        'meta_monto',
        'activo'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'creado_en';
    protected $updatedField  = 'actualizado_en';
}
