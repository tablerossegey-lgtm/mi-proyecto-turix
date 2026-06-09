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
        $data['categorias'] = $model->obtenerCategoriasConProductos();

        if ($this->request->getHeaderLine('HX-Request') && !$this->request->getHeaderLine('HX-Boosted')) {
            return view('categorias/_lista_categorias', $data);
        }

        return view('categorias/index', $data);
    }
}
