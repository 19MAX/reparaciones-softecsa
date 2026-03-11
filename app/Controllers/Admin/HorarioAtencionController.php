<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\HorarioAtencionModel;
use CodeIgniter\HTTP\ResponseInterface;

class HorarioAtencionController extends BaseController
{
    public $horarioModel;
    public $validation;

    public function __construct()
    {
        $this->horarioModel = new HorarioAtencionModel();
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {
        $data = [
            'horarios' => $this->horarioModel->getHorariosOrdenados(),
            'diasSemana' => HorarioAtencionModel::getDiasSemana(),
            'titulo' => 'Horarios de Atención',
        ];

        return view('admin/horarios-atencion/index', $data);
    }

    public function crear()
    {
        $data = [
            'dia_semana'    => $this->request->getPost('dia_semana'),
            'hora_apertura' => $this->request->getPost('hora_apertura'),
            'hora_cierre'   => $this->request->getPost('hora_cierre'),
            'abierto'       => 1,
        ];

        $rules = [
            'dia_semana'    => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[6]',
            'hora_apertura' => 'required',
            'hora_cierre'   => 'required',
        ];

        $this->validation->setRules($rules);

        if (!$this->validation->run($data)) {
            return redirectView('admin/horarios-atencion', $this->validation, [['Errores de validación', 'error', 'top-end']]);
        }

        // Verificar que no exista ya un horario para ese día
        $existe = $this->horarioModel->where('dia_semana', $data['dia_semana'])->first();
        if ($existe) {
            return redirectView('admin/horarios-atencion', null, [['Ya existe un horario para ese día', 'error', 'top-end']]);
        }

        $this->horarioModel->insert($data);

        return redirectView('admin/horarios-atencion', null, [['Horario creado correctamente', 'success', 'top-end']]);
    }

    public function editar()
    {
        try {
            $id = $this->request->getPost('id');

            if (!$id || !$this->horarioModel->find($id)) {
                return redirectView('admin/horarios-atencion', null, [['El registro no existe', 'error', 'top-end']]);
            }

            $data = [
                'dia_semana'    => $this->request->getPost('dia_semana'),
                'hora_apertura' => $this->request->getPost('hora_apertura'),
                'hora_cierre'   => $this->request->getPost('hora_cierre'),
            ];

            $data['abierto'] = $this->request->getPost('abierto') ? 1 : 0;

            $rules = [
                'dia_semana'    => 'required|integer|greater_than_equal_to[0]|less_than_equal_to[6]',
                'hora_apertura' => 'required',
                'hora_cierre'   => 'required',
            ];

            $this->validation->setRules($rules);

            if (!$this->validation->run($data)) {
                return redirectView('admin/horarios-atencion', $this->validation, [['Errores de validación', 'error', 'top-end']]);
            }

            // Verificar duplicado (otro registro con el mismo día)
            $existe = $this->horarioModel->where('dia_semana', $data['dia_semana'])->where('id !=', $id)->first();
            if ($existe) {
                return redirectView('admin/horarios-atencion', null, [['Ya existe un horario para ese día', 'error', 'top-end']]);
            }

            $this->horarioModel->update($id, $data);

            return redirectView('admin/horarios-atencion', null, [['Horario actualizado exitosamente', 'success', 'top-end']]);

        } catch (\Exception $e) {
            return redirectView('admin/horarios-atencion', null, [['Error al actualizar: ' . $e->getMessage(), 'error', 'top-end']]);
        }
    }

    public function eliminar()
    {
        try {
            $id = $this->request->getPost('id');

            if (!$id || !$this->horarioModel->find($id)) {
                return redirectView('admin/horarios-atencion', null, [['El registro no existe', 'error', 'top-end']]);
            }

            $this->horarioModel->delete($id);

            return redirectView('admin/horarios-atencion', null, [['Horario eliminado exitosamente', 'success', 'top-end']]);
        } catch (\Exception $e) {
            return redirectView('admin/horarios-atencion', null, [['Error al eliminar', 'error', 'top-end']]);
        }
    }
}
