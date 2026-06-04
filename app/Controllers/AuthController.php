<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;

class AuthController extends BaseController
{
    public function index()
    {
        // Si ya está logueado, redirigir al panel de administración
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/productos'));
        }

        return view('auth/login');
    }

    public function login()
    {
        $session = session();
        $usuarioModel = new UsuarioModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (empty($username) || empty($password)) {
            return redirect()->back()->with('error', 'Por favor, ingresa el usuario y la contraseña.');
        }

        // Buscar al usuario por nombre de usuario o correo electrónico
        $user = $usuarioModel->where('nombre_usuario', $username)
                            ->orWhere('correo_electronico', $username)
                            ->first();

        if (!$user) {
            return redirect()->back()->with('error', 'El usuario o la contraseña son incorrectos.');
        }

        $isValid = false;
        $dbHash = $user['contrasena_hash'];

        // Soporte para Hash Legacy SHA-256 (64 caracteres hexadecimales)
        if (strlen($dbHash) === 64 && !str_starts_with($dbHash, '$')) {
            $isValid = (hash('sha256', $password) === $dbHash);
            
            // Si es válido, actualizar inmediatamente a Bcrypt
            if ($isValid) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $usuarioModel->update($user['id'], [
                    'contrasena_hash' => $newHash
                ]);
            }
        } else {
            // Verificación estándar de PHP (Bcrypt/Argon2)
            $isValid = password_verify($password, $dbHash);
        }

        if (!$isValid) {
            return redirect()->back()->with('error', 'El usuario o la contraseña son incorrectos.');
        }

        // Crear sesión
        $ses_data = [
            'id'             => $user['id'],
            'nombre_usuario' => $user['nombre_usuario'],
            'correo'         => $user['correo_electronico'],
            'nombre_completo'=> $user['nombre_completo'],
            'rol'            => $user['rol'],
            'isLoggedIn'     => true,
        ];
        
        $session->set($ses_data);

        return redirect()->to(base_url('admin/productos'))->with('success', '¡Bienvenida de nuevo, ' . $user['nombre_completo'] . '!');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('catalogo'));
    }
}
