<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ClienteModel;
use App\Services\ApiPrivadaService;

class ClientesController extends BaseController
{

    private $clienteModel;
    private $apiPrivadaService;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
        $this->apiPrivadaService = new ApiPrivadaService();
    }

    public function index()
    {
        $data = [
            'titulo' => 'Lista de Clientes',
            'clientes' => $this->clienteModel->findAll(),
        ];

        return view('admin/clientes/index', $data);
    }

    public function buscarCedula()
    {
        try {
            $newCsrfToken = csrf_hash();
            $data = $this->request->getJSON();

            if (is_null($data) || !isset($data->cedula)) {
                return $this->response->setHeader('X-CSRF-TOKEN', $newCsrfToken)
                    ->setJSON(['status' => 'validation', 'message' => 'Cédula requerida', 'code' => 400], 400);
            }

            $cedula = trim($data->cedula);

            // 1. Consultar BD Local
            $clienteLocal = $this->clienteModel->where('cedula', $cedula)->first();

            if ($clienteLocal) {
                return $this->response->setHeader('X-CSRF-TOKEN', $newCsrfToken)
                    ->setJSON([
                        'status' => 'success',
                        'message' => 'Cliente local encontrado',
                        'code' => 200,
                        'origin' => 'local', // Flag para saber de donde viene
                        'persona' => [
                            'id' => $clienteLocal['id'],
                            'cedula' => $clienteLocal['cedula'],
                            'nombres' => $clienteLocal['nombres'],
                            'apellidos' => $clienteLocal['apellidos'],
                            'telefono' => $clienteLocal['telefono'],
                            'telefono_secundario' => $clienteLocal['telefono_secundario'] ?? '', // Nuevo campo
                            'email' => $clienteLocal['email'],
                            // 'direccion' => $clienteLocal['direccion'],
                        ]
                    ]);
            }

            // 2. Consultar API Externa
            $persona = $this->apiPrivadaService->getDataUser($cedula);

            if ($persona && $persona['success'] && isset($persona['data'])) {
                $d = $persona['data'];
                return $this->response->setHeader('X-CSRF-TOKEN', $newCsrfToken)
                    ->setJSON([
                        'status' => 'success',
                        'message' => 'Datos obtenidos de Registro Civil',
                        'code' => 200,
                        'origin' => 'api',
                        'persona' => [
                            'id' => '', // ID vacío porque es nuevo en tu sistema
                            'cedula' => $d['identification'], // Asegurar devolver la cédula
                            'nombres' => $d['name'],
                            'apellidos' => $d['surname'],
                            'email' => $d['email'] ?? '',
                            'telefono' => $d['phone'] ?? '',
                            'telefono_secundario' => '', // API usualmente no trae esto
                            'direccion' => $d['address'] ?? ''
                        ]
                    ]);
            }

            // 3. No encontrado
            return $this->response->setHeader('X-CSRF-TOKEN', $newCsrfToken)
                ->setJSON([
                    'status' => 'error',
                    'message' => 'No encontrado. Registre manualmente.',
                    'code' => 404
                ], 404);

        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()])->setStatusCode(500);
        }
    }

    public function crearJs()
    {
        // Verificar que sea una petición AJAX/JSON
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setBody('Acceso denegado');
        }

        try {
            // 1. Obtener el JSON enviado por el fetch
            $json = $this->request->getJSON(true); // true para recibirlo como array asociativo

            // 2. Definir reglas de validación (basadas en tu tabla 'clientes')
            $rules = [
                'cedula' => [
                    'label' => 'Cédula',
                    'rules' => 'required|is_unique[clientes.cedula]|max_length[20]',
                ],
                'nombres' => [
                    'label' => 'Nombres',
                    'rules' => 'required|max_length[100]',
                ],
                'apellidos' => [
                    'label' => 'Apellidos',
                    'rules' => 'required|max_length[100]',
                ],
                'email' => [
                    'label' => 'Email',
                    'rules' => 'permit_empty|valid_email|max_length[150]',
                ],
                'telefono' => [
                    'label' => 'Teléfono',
                    'rules' => 'permit_empty|max_length[20]',
                ],
                'telefono_secundario' => [
                    'label' => 'Teléfono Secundario',
                    'rules' => 'permit_empty|max_length[20]',
                ],
            ];

            // 3. Validar los datos del JSON
            $validation = \Config\Services::validation();

            // Usamos setRules y run pasándole el array $json directamente
            $validation->setRules($rules);

            if (!$validation->run($json)) {
                // Retornar error con el nuevo token CSRF
                return $this->response->setJSON([
                    'status' => 'error',
                    'token' => csrf_hash(), // Regenerar token para que el formulario no expire
                    'errors' => $validation->getErrors()
                ]);
            }

            // 4. Preparar datos para insertar
            $clienteData = [
                'cedula' => $json['cedula'],
                'nombres' => $json['nombres'],
                'apellidos' => $json['apellidos'],
                'telefono' => $json['telefono'] ?? null,
                'telefono_secundario' => $json['telefono_secundario'] ?? null,
                'email' => $json['email'] ?? null,
                'created_at' => date('Y-m-d H:i:s'), // O dejar que el modelo lo maneje
            ];

            // 5. Insertar usando el Modelo (asegúrate de tener cargado $this->clienteModel)
            // Asumo que tu modelo se llama ClienteModel
            $insertID = $this->clienteModel->insert($clienteData);

            if ($insertID) {
                // Obtener los datos recién creados para devolverlos al JS (útil para actualizar la vista)
                $nuevoCliente = $this->clienteModel->find($insertID);

                return $this->response->setJSON([
                    'status' => 'success',
                    'token' => csrf_hash(), // Token nuevo
                    'msg' => 'Cliente registrado exitosamente',
                    'client_data' => $nuevoCliente // Datos para tu variable this.client en JS
                ]);
            } else {
                throw new \Exception("No se pudo insertar el registro en la base de datos.");
            }

        } catch (\Exception $e) {
            log_message('error', '[ClientesController::crearJs] ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'token' => csrf_hash(),
                'errors' => ['exception' => 'Error del sistema: ' . $e->getMessage()]
            ]);
        }
    }

    public function actualizarJs()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setBody('Acceso denegado');
        }

        try {
            $json = $this->request->getJSON(true);
            
            // Validar que venga el ID
            if (empty($json['id'])) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'token'  => csrf_hash(),
                    'errors' => 'ID de cliente no identificado.'
                ]);
            }

            $id = $json['id'];

            // Reglas de validación para EDICIÓN
            $rules = [
                'cedula' => [
                    'label' => 'Cédula',
                    // IMPORTANTE: is_unique[tabla.campo,columna_id,valor_id]
                    // Esto le dice a CI4: "Verifica que sea única, EXCEPTO para el ID actual"
                    'rules' => "required|max_length[20]|is_unique[clientes.cedula,id,{$id}]", 
                    'errors' => [
                        'is_unique' => 'Esta cédula ya pertenece a otro cliente.'
                    ]
                ],
                'nombres' => 'required|max_length[100]',
                'apellidos' => 'required|max_length[100]',
                'email' => 'permit_empty|valid_email|max_length[150]',
                'telefono' => 'permit_empty|max_length[20]',
                'telefono_secundario' => 'permit_empty|max_length[20]',
            ];

            $validation = \Config\Services::validation();
            $validation->setRules($rules);

            if (!$validation->run($json)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'token'  => csrf_hash(),
                    'errors' => $validation->getErrors()
                ]);
            }

            // Preparar datos para actualizar
            $clienteData = [
                'cedula'              => $json['cedula'],
                'nombres'             => $json['nombres'],
                'apellidos'           => $json['apellidos'],
                'telefono'            => $json['telefono'] ?? null,
                'telefono_secundario' => $json['telefono_secundario'] ?? null,
                'email'               => $json['email'] ?? null,
                'updated_at' => date('Y-m-d H:i:s'), // O dejar que el modelo lo maneje
            ];

            // Actualizar usando el Modelo
            $this->clienteModel->update($id, $clienteData);

            // Devolver los datos actualizados para refrescar la vista JS
            $clienteActualizado = $this->clienteModel->find($id);

            return $this->response->setJSON([
                'status'      => 'success',
                'token'       => csrf_hash(),
                'msg'         => 'Datos actualizados correctamente',
                'client_data' => $clienteActualizado
            ]);

        } catch (\Exception $e) {
            log_message('error', '[ClientesController::actualizarJs] ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'token'  => csrf_hash(),
                'errors' => 'Error del sistema: ' . $e->getMessage()
            ]);
        }
    }
}
