<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RepuestoModel;

class RepuestosController extends BaseController
{
    protected $repuestoModel;
    protected $validation;

    public function __construct()
    {
        $this->repuestoModel = new RepuestoModel();
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {
        $repuestos = $this->repuestoModel->orderBy('nombre', 'ASC')->findAll();

        $data = [
            'repuestos' => $repuestos,
            'titulo' => 'Catálogo de Repuestos'
        ];

        return view('admin/repuestos/index', $data);
    }

    public function crear()
    {
        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'valor'  => $this->request->getPost('valor'),
            'stock'  => $this->request->getPost('stock') ?? 0,
        ];

        if (!$this->validate($this->repuestoModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->repuestoModel->insert($data);

        return redirect()->to(base_url('admin/repuestos'))->with('success', 'Repuesto creado exitosamente');
    }

    public function editar()
    {
        $id = $this->request->getPost('id');
        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'valor'  => $this->request->getPost('valor'),
            'stock'  => $this->request->getPost('stock') ?? 0,
        ];

        if (!$this->validate($this->repuestoModel->getValidationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->repuestoModel->update($id, $data);

        return redirect()->to(base_url('admin/repuestos'))->with('success', 'Repuesto actualizado exitosamente');
    }

    public function eliminar()
    {
        $id = $this->request->getPost('id');
        $this->repuestoModel->delete($id);

        return redirect()->to(base_url('admin/repuestos'))->with('success', 'Repuesto eliminado exitosamente');
    }

    public function buscar()
    {
        $term = $this->request->getGet('term');
        $repuestos = $this->repuestoModel->like('nombre', $term)->limit(10)->findAll();

        return $this->response->setJSON($repuestos);
    }
}
