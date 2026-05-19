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
        $data['categorias'] = $model->orderBy('nombre', 'ASC')->findAll();

        if ($this->request->getHeaderLine('HX-Request')) {
            return view('categorias/_lista_categorias', $data);
        }

        return view('categorias/index', $data);
    }
}
