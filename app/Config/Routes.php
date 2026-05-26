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
$routes->get('catalogo/detalle/(:num)', 'Catalogo::detalle/$1');

// Rutas de Administración de Galerías
$routes->group('admin', function($routes) {
    $routes->get('productos', 'AdminProductos::index');
    $routes->post('productos/crear', 'AdminProductos::crear');
    $routes->get('productos/galeria/(:num)', 'AdminProductos::galeria/$1');
    $routes->post('productos/subir-imagen/(:num)', 'AdminProductos::subirImagen/$1');
    $routes->post('productos/cambiar-principal/(:num)', 'AdminProductos::cambiarPrincipal/$1');
    $routes->post('productos/eliminar-imagen/(:num)', 'AdminProductos::eliminarImagen/$1');
    $routes->post('productos/actualizar-orden/(:num)', 'AdminProductos::actualizarOrden/$1');
});