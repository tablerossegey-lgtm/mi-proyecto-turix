<?php

use App\Controllers\PruebaNeubox;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('pruebaneubox', 'PruebaNeubox::index');
$routes->get('catalogo', 'Catalogo::index');
