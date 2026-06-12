<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Si el usuario no ha iniciado sesión, intentar autologin por cookie remember_token
        if (!session()->get('isLoggedIn')) {
            $rememberToken = $_COOKIE['remember_token'] ?? null;
            if ($rememberToken) {
                $usuarioModel = new \App\Models\UsuarioModel();
                $user = $usuarioModel->where('remember_token', $rememberToken)->first();
                if ($user) {
                    $ses_data = [
                        'id'             => $user['id'],
                        'nombre_usuario' => $user['nombre_usuario'],
                        'correo'         => $user['correo_electronico'],
                        'nombre_completo'=> $user['nombre_completo'],
                        'rol'            => $user['rol'],
                        'isLoggedIn'     => true,
                    ];
                    session()->set($ses_data);
                    return; // Acceso concedido
                }
            }

            return redirect()->to(base_url('login'))->with('error', 'Acceso denegado. Por favor, inicia sesión.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se requiere acción después de procesar
    }
}
