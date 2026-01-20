<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ConfiguracionEmpresaModel;
use CodeIgniter\HTTP\ResponseInterface;

class ConfiguracionController extends BaseController
{
    public $configModel;
    public $validation;

    public function __construct()
    {
        $this->configModel = new ConfiguracionEmpresaModel();
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {
        // Buscamos el primer (y único) registro de configuración
        $config = $this->configModel->first();

        $data = [
            'config' => $config, // Puede ser null si es la primera vez
            'titulo' => 'Configuración de Empresa'
        ];

        return view('admin/configuracion/index', $data);
    }

    public function guardar()
    {
        $id = $this->request->getPost('id');

        // Reglas de validación actualizadas
        $rules = [
            'nombre_empresa' => 'required|max_length[150]',
            'email'          => 'required|valid_email|max_length[150]',
            'valor_revision' => 'required|numeric|greater_than_equal_to[0]',
            'telefono'       => 'permit_empty|max_length[30]',
            'direccion'      => 'permit_empty|max_length[255]',
            'logo'           => 'is_image[logo]|mime_in[logo,image/jpg,image/jpeg,image/png]|max_size[logo,2048]'
        ];

        $this->validation->setRules($rules);

        if (!$this->validation->withRequest($this->request)->run()) {
            return redirectView('admin/configuracion', $this->validation, [['Errores de validación', 'error', 'top-end']]);
        }

        // Datos a guardar
        $data = [
            'nombre_empresa' => $this->request->getPost('nombre_empresa'),
            'email'          => $this->request->getPost('email'),
            'telefono'       => $this->request->getPost('telefono'),
            'direccion'      => $this->request->getPost('direccion'),
            'valor_revision' => $this->request->getPost('valor_revision'),
        ];

        // Manejo del Logo
        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/empresa', $newName);
            $data['logo_path'] = 'uploads/empresa/' . $newName;

            // Eliminar logo viejo si existe
            if ($id) {
                $oldConfig = $this->configModel->find($id);
                if (!empty($oldConfig['logo_path']) && file_exists($oldConfig['logo_path'])) {
                    unlink($oldConfig['logo_path']);
                }
            }
        }

        try {
            if (empty($id)) {
                $this->configModel->insert($data);
                $mensaje = 'Configuración inicial guardada correctamente';
            } else {
                $this->configModel->update($id, $data);
                $mensaje = 'Datos de la empresa actualizados';
            }
            return redirectView('admin/configuracion', null, [[$mensaje, 'success', 'top-end']]);
        } catch (\Exception $e) {
            return redirectView('admin/configuracion', null, [['Error del sistema: ' . $e->getMessage(), 'error', 'top-end']]);
        }
    }
}
