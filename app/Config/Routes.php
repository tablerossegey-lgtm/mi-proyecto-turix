<?php

use App\Controllers\PruebaNeubox;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('catalogo', 'Catalogo::index');
$routes->get('categorias', 'Categorias::index');
$routes->get('catalogo/categoria/(:num)', 'Catalogo::porCategoria/$1');
$routes->post('catalogo/buscar', 'Catalogo::buscar');
$routes->post('catalogo/buscar/(:num)', 'Catalogo::buscar/$1');