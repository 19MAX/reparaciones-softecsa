<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TerminosCondicionesModel;
use App\Models\TipoDispositivoModel;
use CodeIgniter\HTTP\ResponseInterface;

class TerminosCondicionesController extends BaseController
{
    public $terminosModel;
    public $tipoDispositivoModel;
    public $validation;

    public function __construct()
    {
        $this->terminosModel = new TerminosCondicionesModel();
        $this->tipoDispositivoModel = new TipoDispositivoModel();
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {
        // Obtenemos los términos y hacemos JOIN con tipos para saber el nombre del dispositivo
        // Si tipo_dispositivo_id es NULL, el nombre será NULL (lo manejamos en la vista como "General")
        $terminos = $this->terminosModel
            ->select('terminos_condiciones.*, tipos_dispositivo.nombre as nombre_dispositivo')
            ->join('tipos_dispositivo', 'tipos_dispositivo.id = terminos_condiciones.tipo_dispositivo_id', 'left')
            ->findAll();

        // Necesitamos la lista de dispositivos para el Select del Modal de Crear/Editar
        $tiposDispositivos = $this->tipoDispositivoModel->where('activo', 1)->findAll();

        $data = [
            'terminos' => $terminos,
            'tiposDispositivos' => $tiposDispositivos,
            'titulo' => 'Términos y Condiciones'
        ];

        return view('admin/terminosCondiciones/index', $data);
    }

    public function crear()
    {
        $tipoId = $this->request->getPost('tipo_dispositivo_id');

        $data = [
            // Si viene vacío, guardamos NULL (Término General), si no, el ID
            'tipo_dispositivo_id' => empty($tipoId) ? null : $tipoId,
            'titulo' => $this->request->getPost('titulo'),
            'contenido' => $this->request->getPost('contenido'),
            'activo' => 1
        ];

        $rules = [
            'titulo' => 'required|max_length[150]',
            'contenido' => 'required',
        ];

        $this->validation->setRules($rules);

        if (!$this->validation->run($data)) {
            return redirectView('admin/terminos-condiciones', $this->validation, [['Errores de validación', 'error', 'top-end']]);
        }

        $this->terminosModel->insert($data);

        return redirectView('admin/terminos-condiciones', null, [['Término creado exitosamente', 'success', 'top-end']]);
    }

    public function editar()
    {
        try {
            $id = $this->request->getPost('id');
            $tipoId = $this->request->getPost('tipo_dispositivo_id');

            if (!$id || !$this->terminosModel->find($id)) {
                return redirectView('admin/terminos-condiciones', null, [['El registro no existe', 'error', 'top-end']]);
            }

            $data = [
                'tipo_dispositivo_id' => empty($tipoId) ? null : $tipoId,
                'titulo' => $this->request->getPost('titulo'),
                'contenido' => $this->request->getPost('contenido'),
            ];

            $rules = [
                'titulo' => 'required|max_length[150]',
                'contenido' => 'required',
            ];

            $this->validation->setRules($rules);

            if (!$this->validation->run($data)) {
                return redirectView('admin/terminos-condiciones', $this->validation, [['Errores de validación', 'error', 'top-end']]);
            }

            $this->terminosModel->update($id, $data);

            return redirectView('admin/terminos-condiciones', null, [['Actualizado exitosamente', 'success', 'top-end']]);

        } catch (\Exception $e) {
            log_message('error', 'Error al actualizar: ' . $e->getMessage());
            return redirectView('admin/terminos-condiciones', null, [['Error al actualizar', 'error', 'top-end']]);
        }
    }

    public function eliminar()
    {
        try {
            $id = $this->request->getPost('id');

            if (!$id || !$this->terminosModel->find($id)) {
                return redirectView('admin/terminos-condiciones', null, [['El registro no existe', 'error', 'top-end']]);
            }

            $this->terminosModel->delete($id);

            return redirectView('admin/terminos-condiciones', null, [['Eliminado exitosamente', 'success', 'top-end']]);
        } catch (\Exception $e) {
            return redirectView('admin/terminos-condiciones', null, [['Error al eliminar', 'error', 'top-end']]);
        }
    }
}
