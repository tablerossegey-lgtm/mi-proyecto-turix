<?php

namespace App\Models;

use CodeIgniter\Model;

class CajaChicaModel extends Model
{
    protected $table            = 't_caja_chica';
    protected $primaryKey       = 'idMovimiento';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'fecha',
        'descripcion',
        'monto',
        'tipo'
    ];

    // Dates
    protected $useTimestamps = false;
}
