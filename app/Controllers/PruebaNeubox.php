<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class PruebaNeubox extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        try {
            $query = $db->query("SELECT COUNT(*) as total FROM t_usuarios");
            $resultado = $query->getRow();
            echo "<h1>Conexión Exitosa en Neubox</h1>";
            echo "Tienes " . $resultado->total . " usuarios en tu tabla.";
        } catch (\Exception $e) {
            echo "<h1>Error de conexión</h1>";
            echo $e->getMessage();
        }
    }
}
