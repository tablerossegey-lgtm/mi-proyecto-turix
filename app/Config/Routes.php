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

// Rutas de Autenticación
$routes->get('login', 'AuthController::index');
$routes->post('login', 'AuthController::login');
$routes->get('logout', 'AuthController::logout');
$routes->get('autologin', 'AuthController::autologin');

// Rutas de Administración de Galerías (Protegidas por Filtro)
$routes->group('admin', ['filter' => 'adminAuth'], function($routes) {
    $routes->get('productos', 'AdminProductos::index');
    $routes->post('productos', 'AdminProductos::index');
    $routes->post('productos/crear', 'AdminProductos::crear');
    $routes->post('productos/editar/(:num)', 'AdminProductos::editar/$1');
    $routes->get('productos/galeria/(:num)', 'AdminProductos::galeria/$1');
    $routes->post('productos/subir-imagen/(:num)', 'AdminProductos::subirImagen/$1');
    $routes->post('productos/cambiar-principal/(:num)', 'AdminProductos::cambiarPrincipal/$1');
    $routes->post('productos/eliminar-imagen/(:num)', 'AdminProductos::eliminarImagen/$1');
    $routes->post('productos/actualizar-orden/(:num)', 'AdminProductos::actualizarOrden/$1');

    // Rutas de Pedidos Encargados por Clientes
    $routes->get('encargos', 'PedidosEncargados::index');
    $routes->post('encargos/crear', 'PedidosEncargados::crear');
    $routes->post('encargos/editar/(:num)', 'PedidosEncargados::editar/$1');
    $routes->post('encargos/eliminar/(:num)', 'PedidosEncargados::eliminar/$1');

    // Rutas de Cuentas de Clientes
    $routes->get('cuentas', 'CuentasClientes::index');
    $routes->get('cuentas/compras/(:num)', 'CuentasClientes::obtenerCompras/$1');
    $routes->post('cuentas/crear', 'CuentasClientes::crear');
    $routes->post('cuentas/abonar', 'CuentasClientes::registrarAbono');
    $routes->post('cuentas/editar/(:num)', 'CuentasClientes::editar/$1');
    $routes->post('cuentas/eliminar/(:num)', 'CuentasClientes::eliminar/$1');
    $routes->post('cuentas/toggle/(:num)', 'CuentasClientes::toggleEstado/$1');
    $routes->post('cuentas/editar-cliente/(:num)', 'CuentasClientes::editarCliente/$1');
    $routes->get('cuentas/buscar-productos', 'CuentasClientes::buscarProductosJson');

    // Rutas de Caja Chica
    $routes->get('caja', 'CajaChica::index');
    $routes->post('caja/crear', 'CajaChica::crear');
    $routes->post('caja/eliminar/(:num)', 'CajaChica::eliminar/$1');
});