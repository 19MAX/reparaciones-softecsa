<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ChecklistItemModel;
use CodeIgniter\HTTP\ResponseInterface;

class ChecklistController extends BaseController
{
    protected $checklistModel;

    public function __construct()
    {
        $this->checklistModel = new ChecklistItemModel();
    }

    /**
     * 1. Obtener los items principales (fetchTopChecks)
     * GET /checklist/listar
     */
    public function listar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        // Puedes poner un limit(10) si son muchos, o traer todos los activos
        $data = $this->checklistModel
            ->where('activo', 1)
            ->orderBy('nombre', 'ASC') // O por popularidad si tuvieras esa columna
            ->findAll();

        return $this->response->setJSON($data);
    }

    /**
     * 2. Buscar items mientras escribe (searchChecks)
     * GET /checklist/buscar?q=pantalla
     */
    public function buscar()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $query = $this->request->getGet('q');

        if (empty($query)) {
            return $this->response->setJSON([]);
        }

        $data = $this->checklistModel
            ->like('nombre', $query)
            ->where('activo', 1)
            ->findAll(10); // Limitar a 10 resultados

        return $this->response->setJSON($data);
    }

    /**
     * 3. Crear nuevo item (createAndSelectCheck)
     * POST /checklist/crear
     */
    public function crear()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        try {
            // Recibimos JSON (igual que en tu ejemplo anterior)
            $json = $this->request->getJSON(true);
            $nombre = trim($json['nombre'] ?? '');

            // Validar manualmente o usar el modelo
            if (empty($nombre)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'token' => csrf_hash(),
                    'msg' => 'El nombre es obligatorio'
                ]);
            }

            // Datos a insertar
            $newItem = [
                'nombre' => $nombre,
                'activo' => 1
            ];

            // Intentar insertar (El modelo validará el is_unique)
            if ($this->checklistModel->insert($newItem)) {

                $id = $this->checklistModel->getInsertID();

                // Devolvemos el objeto completo para que Vue lo agregue a la lista
                return $this->response->setJSON([
                    'status' => 'success',
                    'token' => csrf_hash(),
                    'check' => [
                        'id' => $id,
                        'nombre' => $nombre
                    ]
                ]);
            } else {
                // Errores de validación del modelo (ej: duplicado)
                return $this->response->setJSON([
                    'status' => 'error',
                    'token' => csrf_hash(),
                    'msg' => $this->checklistModel->errors()
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'token' => csrf_hash(),
                'msg' => 'Error del servidor: ' . $e->getMessage()
            ]);
        }
    }
}
