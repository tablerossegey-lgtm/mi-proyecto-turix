<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table = 't_clientes';
    protected $primaryKey = 'idCliente';
    protected $returnType = 'array';
    protected $allowedFields = ['nombre', 'cel', 'tipoCliente'];
    protected bool $allowEmptyInserts = false;
}
