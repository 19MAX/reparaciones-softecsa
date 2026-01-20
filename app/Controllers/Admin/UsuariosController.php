<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use function PHPUnit\Framework\throwException;

class UsuariosController extends BaseController
{

    private $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

    public function index()
    {
        $data = [
            'titulo' => 'Lista de Usuarios',
            'usuarios' => $this->usuarioModel->findAll(),
        ];

        return view('admin/usuarios/index', $data);
    }

    public function crear()
    {

        try {
            // Obtener los datos del formulario
            $cedula = $this->request->getPost('cedula');
            $nombres = $this->request->getPost('nombres');
            $apellidos = $this->request->getPost('apellidos');
            $role = $this->request->getPost('role');

            // Nuevos inputs
            $tipoComision = $this->request->getPost('tipo_comision');
            $valorComision = $this->request->getPost('valor_comision');

            $password = $this->request->getPost('password');
            $repeatPassword = $this->request->getPost('repeatPassword');

            // Preparar datos para validación
            $data = [
                'cedula' => $cedula,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'password' => $password,
                'role' => $role,
                'repeatPassword' => $repeatPassword,
                'tipo_comision' => $tipoComision,
                'valor_comision' => $valorComision,
            ];

            $validation = \Config\Services::validation();

            // Definir las reglas de validación
            $rules = [
                'cedula' => [
                    'label' => 'Cédula',
                    'rules' => 'required|is_unique[usuarios.cedula]|max_length[20]',
                ],
                'nombres' => [
                    'label' => 'Nombres',
                    'rules' => 'required|max_length[100]',
                ],
                'apellidos' => [
                    'label' => 'Apellidos',
                    'rules' => 'required|max_length[100]',
                ],
                'password' => [
                    'label' => 'Contraseña',
                    'rules' => 'required|min_length[6]',
                ],
                'role' => [
                    'label' => 'Rol',
                    'rules' => 'required|in_list[admin,recepcionista,tecnico]',
                ],
                'repeatPassword' => [
                    'label' => 'Repetir Contraseña',
                    'rules' => 'required|matches[password]',
                ],
            ];

            if ($role === 'tecnico') {
                $rules['valor_comision'] = [
                    'label' => 'Valor de Comisión',
                    'rules' => 'required|numeric|greater_than_equal_to[0]'
                ];
            }

            $validation->setRules($rules);

            // Ejecutar la validación
            if (!$validation->run($data)) {
                return redirectView('admin/usuarios', $validation, [['Errores de validación', 'error', 'top-end']], $data);
            }

            // Preparar datos para insertar
            $usuarioData = [
                'cedula' => $cedula,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'password' => $password,
                'role' => $role,
                // Agregamos lógica para guardar NULL si no es técnico
                'tipo_comision' => ($role === 'tecnico') ? $tipoComision : null,
                'valor_comision' => ($role === 'tecnico') ? $valorComision : 0.00,
                'estado' => 'activo',
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => session()->get('id_usuario'),
            ];


            // Insertar usuario
            $this->usuarioModel->insert($usuarioData);

            return redirectView('admin/usuarios', null, [['Usuario registrado exitosamente', 'success', 'top-end']], null);

        } catch (\Exception $e) {
            log_message('error', '[UsuariosController::crear] ' . $e->getMessage());

            // Para solicitudes normales, redireccionar con error
            return redirectView('admin/usuarios', null, [['Error al registrar al usuario: ' . $e->getMessage(), 'error', 'top-end']], $data ?? []);
        }
    }

    public function editar()
    {
        try {
            $idUsuario = $this->request->getPost('id_usuario');
            $role = $this->request->getPost('role'); // Obtener el rol
            $usuarioData = [
                'cedula' => $this->request->getPost('cedula'),
                'nombres' => $this->request->getPost('nombres'),
                'apellidos' => $this->request->getPost('apellidos'),
                'role' => $role,
                // Actualizar lógica de comisión
                'tipo_comision' => ($role === 'tecnico') ? $this->request->getPost('tipo_comision') : null,
                'valor_comision' => ($role === 'tecnico') ? $this->request->getPost('valor_comision') : 0.00,
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => session()->get('id_usuario'),
            ];

            $usuarioObjetivo = $this->usuarioModel->find($idUsuario);
            $idUsuarioLogueado = session()->get('id_usuario');

            if ($usuarioObjetivo && $usuarioObjetivo['role'] === 'admin') {
                if ($idUsuario != $idUsuarioLogueado) {
                    throw new \Exception('No tienes permisos para editar a otros administradores.');
                }
            }

            $rules = [
                'cedula' => [
                    'label' => 'Cédula',
                    'rules' => 'required|max_length[20]|is_unique[usuarios.cedula,id,' . $idUsuario . ']',
                ],
                'nombres' => [
                    'label' => 'Nombres',
                    'rules' => 'required|max_length[100]',
                ],
                'apellidos' => [
                    'label' => 'Apellidos',
                    'rules' => 'required|max_length[100]',
                ],
                'role' => [
                    'label' => 'Rol',
                    'rules' => 'required|in_list[admin,recepcionista,tecnico]',
                ],
            ];

            $password = $this->request->getPost('password');
            $repeatPassword = $this->request->getPost('repeatPassword');

            if (!empty($password)) {
                $usuarioData['password'] = $password;

                $rules['password'] = [
                    'label' => 'Contraseña',
                    'rules' => 'required|min_length[6]',
                ];
                $rules['repeatPassword'] = [
                    'label' => 'Repetir Contraseña',
                    'rules' => 'required|matches[password]',
                ];

                $usuarioData['repeatPassword'] = $repeatPassword;
            }
            // Agregar validación condicional si es técnico
            if ($role === 'tecnico') {
                $rules['valor_comision'] = [
                    'label' => 'Valor de Comisión',
                    'rules' => 'required|numeric|greater_than_equal_to[0]'
                ];
            }
            $validation = \Config\Services::validation();
            $validation->setRules($rules);

            if (!$validation->run($usuarioData)) {
                return redirectView('admin/usuarios', $validation, [['Errores de validación', 'error', 'top-end']], $usuarioData);
            }

            if (isset($usuarioData['repeatPassword'])) {
                unset($usuarioData['repeatPassword']);
            }

            $this->usuarioModel->update($idUsuario, $usuarioData);

            return redirectView('admin/usuarios', null, [['Usuario actualizado exitosamente', 'success', 'top-end']], null);

        } catch (\Exception $e) {
            log_message('error', '[UsuariosController::editar] ' . $e->getMessage());
            return redirectView('admin/usuarios', null, [['Error: ' . $e->getMessage(), 'error', 'top-end']], $this->request->getPost());
        }
    }
    public function eliminar()
    {
        try {
            $idUsuario = $this->request->getPost('id_usuario');

            // Comprobar si el usuario es Admin
            $usuario = $this->usuarioModel->find($idUsuario);
            if ($usuario && $usuario['role'] === 'admin') {
                //lanzar una excepción si se intenta eliminar un usuario Admin
                throw new \Exception('No se puede eliminar al usuario');
            }

            // Eliminar usuario
            $this->usuarioModel->delete($idUsuario);

            return redirectView('admin/usuarios', null, [['Usuario eliminado exitosamente', 'success', 'top-end']], null);

        } catch (\Exception $e) {
            log_message('error', '[UsuariosController::eliminar] ' . $e->getMessage());

            // Para solicitudes normales, redireccionar con error
            return redirectView('admin/usuarios', null, [['Error al eliminar el usuario: ' . $e->getMessage(), 'error', 'top-end']], null);
        }
    }


}
