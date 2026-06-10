<?php

namespace App\Models;

use CodeIgniter\Model;

class UbicacionModel extends Model
{
    protected $table = 't_ubicaciones';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['nombre'];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
}
