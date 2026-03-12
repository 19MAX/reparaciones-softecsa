<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PrioridadModel;

class PrioridadController extends BaseController
{
    public $prioridadModel;
    public $validation;

    public function __construct()
    {
        $this->prioridadModel = new PrioridadModel();
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {
        $data = [
            'prioridades' => $this->prioridadModel->findAll(),
            'titulo' => 'Gestión de Prioridades'
        ];

        return view('admin/prioridades/index', $data);
    }

    public function crear()
    {
        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'descripcion' => $this->request->getPost('descripcion'),
            'costo_adicional' => $this->request->getPost('costo_adicional'),
            'tiempo_maximo_horas' => $this->request->getPost('tiempo_maximo_horas'),
            'activo' => 1
        ];

        $rules = [
            'nombre' => 'required|max_length[100]',
            'costo_adicional' => 'required|numeric|greater_than_equal_to[0]',
            'tiempo_maximo_horas' => 'required|numeric|greater_than_equal_to[0]',
        ];

        $this->validation->setRules($rules);

        if (!$this->validation->run($data)) {
            return redirectView('admin/prioridades', $this->validation, [['Errores de validación', 'error', 'top-end']]);
        }

        $this->prioridadModel->insert($data);

        return redirectView('admin/prioridades', null, [['Nivel de prioridad creado correctamente', 'success', 'top-end']]);
    }

    public function editar()
    {
        try {
            $id = $this->request->getPost('id');

            if (!$id || !$this->prioridadModel->find($id)) {
                return redirectView('admin/prioridades', null, [['El registro no existe', 'error', 'top-end']]);
            }

            $data = [
                'nombre' => $this->request->getPost('nombre'),
                'descripcion' => $this->request->getPost('descripcion'),
                'costo_adicional' => $this->request->getPost('costo_adicional'),
                'tiempo_maximo_horas' => $this->request->getPost('tiempo_maximo_horas'),
            ];

            // Checkbox de activo (si no viene en el post, es 0)
            $data['activo'] = $this->request->getPost('activo') ? 1 : 0;

            $rules = [
                'nombre' => 'required|max_length[100]',
                'costo_adicional' => 'required|numeric|greater_than_equal_to[0]',
                'tiempo_maximo_horas' => 'required|numeric|greater_than_equal_to[0]',
            ];

            $this->validation->setRules($rules);

            if (!$this->validation->run($data)) {
                return redirectView('admin/prioridades', $this->validation, [['Errores de validación', 'error', 'top-end']]);
            }

            $this->prioridadModel->update($id, $data);

            return redirectView('admin/prioridades', null, [['Actualizado exitosamente', 'success', 'top-end']]);

        } catch (\Exception $e) {
            return redirectView('admin/prioridades', null, [['Error al actualizar: ' . $e->getMessage(), 'error', 'top-end']]);
        }
    }

    public function eliminar()
    {
        try {
            $id = $this->request->getPost('id');

            if (!$id || !$this->prioridadModel->find($id)) {
                return redirectView('admin/prioridades', null, [['El registro no existe', 'error', 'top-end']]);
            }

            $this->prioridadModel->delete($id);

            return redirectView('admin/prioridades', null, [['Eliminado exitosamente', 'success', 'top-end']]);
        } catch (\Exception $e) {
            return redirectView('admin/prioridades', null, [['Error al eliminar', 'error', 'top-end']]);
        }
    }
}
