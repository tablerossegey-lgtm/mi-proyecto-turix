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

    protected array $casts = [];
    protected array $castHandlers = [];
}
