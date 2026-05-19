<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsuarioModel;

class PruebaNeubox extends BaseController
{
    public function index()
    {
        try {
            $usuarioModel = new UsuarioModel();
            $total = $usuarioModel->countAll();
            
            echo "<h1>Conexión Exitosa en Neubox</h1>";
            echo "Tienes " . $total . " usuarios en tu tabla.";
        } catch (\Exception $e) {
            echo "<h1>Error de conexión</h1>";
            echo $e->getMessage();
        }
    }
}
