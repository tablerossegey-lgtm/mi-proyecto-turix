<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoriaModel extends Model
{
    protected $table = 't_categorias';
    protected $primaryKey = 'idCategoria';
    protected $returnType = 'array';
    protected $allowedFields = ['nombre', 'imagen'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];
}
