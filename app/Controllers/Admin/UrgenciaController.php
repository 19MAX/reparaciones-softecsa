<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UrgenciaModel;
use CodeIgniter\HTTP\ResponseInterface;

class UrgenciaController extends BaseController
{
    public $urgenciaModel;
    public $validation;

    public function __construct()
    {
        $this->urgenciaModel = new UrgenciaModel();
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {
        $data = [
            'urgencias' => $this->urgenciaModel->findAll(),
            'titulo' => 'Gestión de Urgencias'
        ];

        return view('admin/urgencias/index', $data);
    }

    public function crear()
    {
        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'descripcion' => $this->request->getPost('descripcion'),
            'recargo' => $this->request->getPost('recargo'),
            'tiempo_espera' => $this->request->getPost('tiempo_espera'),
            'activo' => 1
        ];

        $rules = [
            'nombre' => 'required|max_length[100]',
            'recargo' => 'required|numeric|greater_than_equal_to[0]',
            'tiempo_espera' => 'required|max_length[255]',
        ];

        $this->validation->setRules($rules);

        if (!$this->validation->run($data)) {
            return redirectView('admin/urgencias', $this->validation, [['Errores de validación', 'error', 'top-end']]);
        }

        $this->urgenciaModel->insert($data);

        return redirectView('admin/urgencias', null, [['Nivel de urgencia creado correctamente', 'success', 'top-end']]);
    }

    public function editar()
    {
        try {
            $id = $this->request->getPost('id');

            if (!$id || !$this->urgenciaModel->find($id)) {
                return redirectView('admin/urgencias', null, [['El registro no existe', 'error', 'top-end']]);
            }

            $data = [
                'nombre' => $this->request->getPost('nombre'),
                'descripcion' => $this->request->getPost('descripcion'),
                'recargo' => $this->request->getPost('recargo'),
                'tiempo_espera' => $this->request->getPost('tiempo_espera'),
            ];

            // Checkbox de activo (si no viene en el post, es 0)
            $data['activo'] = $this->request->getPost('activo') ? 1 : 0;

            $rules = [
                'nombre' => 'required|max_length[100]',
                'recargo' => 'required|numeric|greater_than_equal_to[0]',
                'tiempo_espera' => 'required|max_length[255]',
            ];

            $this->validation->setRules($rules);

            if (!$this->validation->run($data)) {
                return redirectView('admin/urgencias', $this->validation, [['Errores de validación', 'error', 'top-end']]);
            }

            $this->urgenciaModel->update($id, $data);

            return redirectView('admin/urgencias', null, [['Actualizado exitosamente', 'success', 'top-end']]);

        } catch (\Exception $e) {
            return redirectView('admin/urgencias', null, [['Error al actualizar: ' . $e->getMessage(), 'error', 'top-end']]);
        }
    }

    public function eliminar()
    {
        try {
            $id = $this->request->getPost('id');

            if (!$id || !$this->urgenciaModel->find($id)) {
                return redirectView('admin/urgencias', null, [['El registro no existe', 'error', 'top-end']]);
            }

            $this->urgenciaModel->delete($id);

            return redirectView('admin/urgencias', null, [['Eliminado exitosamente', 'success', 'top-end']]);
        } catch (\Exception $e) {
            return redirectView('admin/urgencias', null, [['Error al eliminar', 'error', 'top-end']]);
        }
    }
}
