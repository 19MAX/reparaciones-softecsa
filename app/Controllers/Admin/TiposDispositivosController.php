<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TipoDispositivoModel;

class TiposDispositivosController extends BaseController
{

    public $tipoDispositivoModel;
    public $validation;

    public function __construct()
    {
        $this->tipoDispositivoModel = new TipoDispositivoModel();
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {

        $tiposDispositivos = $this->tipoDispositivoModel->findAll();


        $data = [
            'tiposDispositivos' => $tiposDispositivos,
            'titulo' => 'Tipos de dispositivo'
        ];

        return view('admin/tipoDispositivo/index', $data);
    }

    public function crear()
    {
        $data = [
            'nombre' => $this->request->getPost('nombre'),
        ];

        $rules = [
            'nombre' => [
                'label' => 'Nombre',
                'rules' => 'required|max_length[100]',
            ],
        ];

        $this->validation->setRules($rules);

        // Ejecutar validación
        if (!$this->validation->run($data)) {
            return redirectView(
                'admin/tipos-dispositivos',
                $this->validation,
                [['Errores de validación', 'error', 'top-end']],
                $data,
            );
        }

        // Insertar en BD
        $this->tipoDispositivoModel->insert($data);

        return redirectView(
            'admin/tipos-dispositivos',
            null,
            [['Tipo creado exitosamente', 'success', 'top-end']]
        );
    }

    public function editar()
    {
        try {
            $id = $this->request->getPost('id');

            // Validar que exista el ID y el registro
            if (!$id || !$this->tipoDispositivoModel->find($id)) {
                return redirectView(
                    'admin/tipos-dispositivos',
                    null,
                    [['El tipo de dispositivo no existe', 'error', 'top-end']]
                );
            }

            $data = [
                'nombre' => $this->request->getPost('nombre'),
            ];

            $rules = [
                'nombre' => [
                    'label' => 'Nombre',
                    'rules' => 'required|max_length[100]',
                ],
            ];

            // Validación
            $this->validation->setRules($rules);

            if (!$this->validation->run($data)) {
                return redirectView(
                    'admin/tipos-dispositivos',
                    $this->validation,
                    [['Errores de validación', 'error', 'top-end']],
                    array_merge($data, ['id' => $id])
                );
            }

            // Actualizar registro
            $this->tipoDispositivoModel->update($id, $data);

            return redirectView(
                'admin/tipos-dispositivos',
                null,
                [['Tipo de dispositivo actualizado exitosamente', 'success', 'top-end']]
            );
        } catch (\Exception $e) {
            log_message('error', 'Error al actualizar tipo de dispositivo: ' . $e->getMessage());
            return redirectView(
                'admin/tipos-dispositivos',
                null,
                [['Ocurrió un error al actualizar el tipo de dispositivo', 'error', 'top-end']]
            );

        }
    }

    public function eliminar()
    {
        try {
            $id = $this->request->getPost('id');

            // Validar que exista el ID y el registro
            if (!$id || !$this->tipoDispositivoModel->find($id)) {
                return redirectView(
                    'admin/tipos-dispositivos',
                    null,
                    [['El tipo de dispositivo no existe', 'error', 'top-end']]
                );
            }

            // Eliminar registro
            $this->tipoDispositivoModel->delete($id);

            return redirectView(
                'admin/tipos-dispositivos',
                null,
                [['Tipo de dispositivo eliminado exitosamente', 'success', 'top-end']]
            );
        } catch (\Exception $e) {
            log_message('error', 'Error al eliminar tipo de dispositivo: ' . $e->getMessage());
            return redirectView(
                'admin/tipos-dispositivos',
                null,
                [['Ocurrió un error al eliminar el tipo de dispositivo', 'error', 'top-end']]
            );
        }
    }


}
