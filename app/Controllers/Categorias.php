<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CategoriaModel;
use CodeIgniter\HTTP\ResponseInterface;

class Categorias extends BaseController
{
    public function index()
    {
        $model = new CategoriaModel();
        $data['categorias'] = $model->findAll();

        return view('categorias/index', $data);
    }
}
