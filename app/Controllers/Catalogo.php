<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ProductoModel;

class Catalogo extends BaseController
{
    public function index()
    {
        $model = new ProductoModel();
        $data['productos'] = $model->findAll();

        return view('catalogo/index', $data);
    }
}
