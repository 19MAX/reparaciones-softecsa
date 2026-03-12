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
        $db = \Config\Database::connect();

        $usuarios = $db->table('usuarios u')
            ->select('
            u.id,
            u.cedula,
            u.nombre,
            u.apellido,
            u.rol,
            u.activo,
            tc.tipo_comision,
            tc.valor_comision
        ')
            ->join('tecnicos_config tc', 'tc.usuario_id = u.id', 'left') // LEFT JOIN para que aparezcan también admin y recepción
            ->get()
            ->getResultArray();

        $data = [
            'titulo' => 'Lista de Usuarios',
            'usuarios' => $usuarios,
        ];

        return view('admin/usuarios/index', $data);
    }

    public function crear()
    {
        try {
            $cedula = $this->request->getPost('cedula');
            $nombres = $this->request->getPost('nombres');
            $apellidos = $this->request->getPost('apellidos');
            $role = $this->request->getPost('role');
            $tipoComision = $this->request->getPost('tipo_comision');
            $valorComision = $this->request->getPost('valor_comision');
            $password = $this->request->getPost('password');
            $repeatPassword = $this->request->getPost('repeatPassword');

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
                    // Ajusta los valores al ENUM de tu migración: admin, tecnico, recepcion
                    'rules' => 'required|in_list[admin,recepcion,tecnico]',
                ],
                'repeatPassword' => [
                    'label' => 'Repetir Contraseña',
                    'rules' => 'required|matches[password]',
                ],
            ];

            if ($role === 'tecnico') {
                $rules['tipo_comision'] = [
                    'label' => 'Tipo de Comisión',
                    'rules' => 'required|in_list[porcentaje,fijo]',
                ];
                $rules['valor_comision'] = [
                    'label' => 'Valor de Comisión',
                    'rules' => 'required|numeric|greater_than_equal_to[0]',
                ];
            }

            $validation->setRules($rules);

            if (!$validation->run($data)) {
                return redirectView('admin/usuarios', $validation, [['Errores de validación', 'error', 'top-end']], $data);
            }

            // Iniciar transacción para garantizar consistencia en ambas tablas
            $db = \Config\Database::connect();
            $db->transStart();

            // 1. Insertar en tabla usuarios (solo campos que le pertenecen)
            $usuarioData = [
                'cedula' => $cedula,
                'nombre' => $nombres,   // ojo: la migración usa 'nombre', no 'nombres'
                'apellido' => $apellidos, // ojo: la migración usa 'apellido', no 'apellidos'
                'password' => $password,
                'rol' => $role,      // ojo: la migración usa 'rol', no 'role'
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $this->usuarioModel->insert($usuarioData);
            $usuarioId = $db->insertID(); // Obtener el ID recién insertado

            // 2. Si es técnico, insertar en tecnicos_config
            if ($role === 'tecnico') {
                $tecnicoData = [
                    'usuario_id' => $usuarioId,
                    'tipo_comision' => $tipoComision,
                    'valor_comision' => $valorComision,
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                $tecnicoConfigModel = new \App\Models\TecnicosConfigModel();
                $tecnicoConfigModel->insert($tecnicoData);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error al guardar en la base de datos.');
            }

            return redirectView('admin/usuarios', null, [['Usuario registrado exitosamente', 'success', 'top-end']], null);

        } catch (\Exception $e) {
            log_message('error', '[UsuariosController::crear] ' . $e->getMessage());
            return redirectView('admin/usuarios', null, [['Error al registrar al usuario: ' . $e->getMessage(), 'error', 'top-end']], $data ?? []);
        }
    }

    public function editar()
    {
        try {
            $idUsuario = $this->request->getPost('id_usuario');
            $role = $this->request->getPost('role');
            $cedula = $this->request->getPost('cedula');
            $nombres = $this->request->getPost('nombres');
            $apellidos = $this->request->getPost('apellidos');
            $tipoComision = $this->request->getPost('tipo_comision');
            $valorComision = $this->request->getPost('valor_comision');
            $password = $this->request->getPost('password');
            $repeatPassword = $this->request->getPost('repeatPassword');

            // Verificar permisos sobre admins
            $usuarioObjetivo = $this->usuarioModel->find($idUsuario);
            $idUsuarioLogueado = session()->get('id_usuario');

            if ($usuarioObjetivo && $usuarioObjetivo['rol'] === 'admin') {
                if ($idUsuario != $idUsuarioLogueado) {
                    throw new \Exception('No tienes permisos para editar a otros administradores.');
                }
            }

            // Datos para validación
            $dataValidar = [
                'cedula' => $cedula,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'role' => $role,
            ];

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
                    'rules' => 'required|in_list[admin,recepcion,tecnico]', // ajustado al ENUM de la migración
                ],
            ];

            if (!empty($password)) {
                $dataValidar['password'] = $password;
                $dataValidar['repeatPassword'] = $repeatPassword;

                $rules['password'] = [
                    'label' => 'Contraseña',
                    'rules' => 'required|min_length[6]',
                ];
                $rules['repeatPassword'] = [
                    'label' => 'Repetir Contraseña',
                    'rules' => 'required|matches[password]',
                ];
            }

            if ($role === 'tecnico') {
                $dataValidar['tipo_comision'] = $tipoComision;
                $dataValidar['valor_comision'] = $valorComision;

                $rules['tipo_comision'] = [
                    'label' => 'Tipo de Comisión',
                    'rules' => 'required|in_list[porcentaje,fijo]',
                ];
                $rules['valor_comision'] = [
                    'label' => 'Valor de Comisión',
                    'rules' => 'required|numeric|greater_than_equal_to[0]',
                ];
            }

            $validation = \Config\Services::validation();
            $validation->setRules($rules);

            if (!$validation->run($dataValidar)) {
                return redirectView('admin/usuarios', $validation, [['Errores de validación', 'error', 'top-end']], $dataValidar);
            }

            $db = \Config\Database::connect();
            $db->transStart();

            // 1. Actualizar tabla usuarios
            $usuarioData = [
                'cedula' => $cedula,
                'nombre' => $nombres,   // campo real en la migración
                'apellido' => $apellidos, // campo real en la migración
                'rol' => $role,      // campo real en la migración
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if (!empty($password)) {
                $usuarioData['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $this->usuarioModel->update($idUsuario, $usuarioData);

            // 2. Manejar tecnicos_config según el rol
            $tecnicoConfigModel = new \App\Models\TecnicosConfigModel();

            if ($role === 'tecnico') {
                $tecnicoExistente = $tecnicoConfigModel->where('usuario_id', $idUsuario)->first();

                $tecnicoData = [
                    'tipo_comision' => $tipoComision,
                    'valor_comision' => $valorComision,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                if ($tecnicoExistente) {
                    // Ya tenía config de técnico → actualizar
                    $tecnicoConfigModel->update($tecnicoExistente['id'], $tecnicoData);
                } else {
                    // No tenía config (era otro rol antes) → insertar
                    $tecnicoData['usuario_id'] = $idUsuario;
                    $tecnicoData['created_at'] = date('Y-m-d H:i:s');
                    $tecnicoConfigModel->insert($tecnicoData);
                }
            } else {
                // Si cambió de técnico a otro rol, eliminar su config de comisión
                $tecnicoConfigModel->where('usuario_id', $idUsuario)->delete();
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error al actualizar en la base de datos.');
            }

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

            $usuario = $this->usuarioModel->find($idUsuario);

            if (!$usuario) {
                throw new \Exception('El usuario no existe.');
            }

            // Proteger admins
            if ($usuario['rol'] === 'admin') {
                throw new \Exception('No se puede eliminar a un usuario administrador.');
            }

            $db = \Config\Database::connect();
            $db->transStart();

            // 1. Eliminar config de técnico si existe (aunque el FK CASCADE lo haría,
            //    es más explícito y seguro manejarlo aquí)
            $tecnicoConfigModel = new \App\Models\TecnicosConfigModel();
            $tecnicoConfigModel->where('usuario_id', $idUsuario)->delete();

            // 2. Eliminar usuario
            $this->usuarioModel->delete($idUsuario);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error al eliminar el usuario.');
            }

            return redirectView('admin/usuarios', null, [['Usuario eliminado exitosamente', 'success', 'top-end']], null);

        } catch (\Exception $e) {
            log_message('error', '[UsuariosController::eliminar] ' . $e->getMessage());
            return redirectView('admin/usuarios', null, [['Error al eliminar el usuario: ' . $e->getMessage(), 'error', 'top-end']], null);
        }
    }


}
