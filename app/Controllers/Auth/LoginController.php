<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use CodeIgniter\HTTP\ResponseInterface;

class LoginController extends BaseController
{
    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

    public function login()
    {
        // Si ya hay sesión, pa' fuera
        if (session()->get('isLoggedIn')) {
            return redirect()->to('admin/dashboard');
        }
        return view('auth/login');
    }

    public function loginProcess()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['message' => 'Acceso no permitido']);
        }

        $json = $this->request->getJSON(true);

        // Validación básica de campos
        if (
            !$this->validateData($json, [
                'cedula' => 'required|min_length[10]', // Ajusta según tu país
                'password' => 'required'
            ])
        ) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $this->validator->getErrors(),
                'token' => csrf_hash()
            ]);
        }


        // Buscamos usuario (CodeIgniter filtra automáticamente los deleted_at != null)
        $usuario = $this->usuarioModel->where('cedula', $json['cedula'])->first();

        // 1. Verificar si existe
        if (!$usuario) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Credenciales incorrectas.', // Mensaje genérico por seguridad
                'token' => csrf_hash()
            ]);
        }

        // 2. Verificar Contraseña
        if (!password_verify($json['password'], $usuario['password'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Credenciales incorrectas.',
                'token' => csrf_hash()
            ]);
        }

        // 3. Verificar Estado (activo/inactivo/suspendido)
        if ($usuario['estado'] !== 'activo') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tu cuenta está ' . $usuario['estado'] . '. Contacta al administrador.',
                'token' => csrf_hash()
            ]);
        }

        // 4. Todo OK: Crear Sesión
        session()->set([
            'id_usuario' => $usuario['id'],
            'nombres' => $usuario['nombres'],
            'apellidos' => $usuario['apellidos'],
            'role' => $usuario['role'],
            'foto' => $usuario['foto_perfil'],
            'isLoggedIn' => true
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'redirect' => base_url('admin/dashboard'), // Cambia esto a tu ruta destino
            'token' => csrf_hash()
        ]);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
