<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 't_usuarios';
    protected $primaryKey = 'id'; // Default primary key
    protected $returnType = 'array';
    protected $allowedFields = [
        'nombre_usuario',
        'correo_electronico',
        'contrasena_hash',
        'nombre_completo',
        'rol',
        'fecha_creacion',
        'remember_token'
    ];
}
