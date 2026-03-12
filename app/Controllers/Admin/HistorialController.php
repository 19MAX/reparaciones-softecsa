<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\HistorialEstados;
use CodeIgniter\HTTP\ResponseInterface;

class HistorialController extends BaseController
{
    public $historialModel;
    public $dispositivosOrdenModel;
    public $validation;

    public function __construct()
    {
        $this->historialModel = new HistorialEstados();
        $this->dispositivosOrdenModel = model('App\Models\DispositivosOrdenModel');
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {
        // 1. Obtener Historial Completo
        $historial = $this->historialModel->obtenerHistorialCompleto();

        // 2. Obtener lista de dispositivos para el Select del Modal
        $dispositivos = $this->dispositivosOrdenModel
            ->select('dispositivos_orden.id, dispositivos_orden.modelo_texto, m.nombre as marca, mo.nombre as modelo_nombre, o.numero_orden, c.nombres, c.apellidos')
            ->join('marcas m', 'm.id = dispositivos_orden.marca_id')
            ->join('modelos mo', 'mo.id = dispositivos_orden.modelo_id', 'left')
            ->join('ordenes o', 'o.id = dispositivos_orden.orden_id')
            ->join('clientes c', 'c.id = o.cliente_id')
            ->orderBy('o.created_at', 'DESC')
            ->findAll();

        $data = [
            'historial' => $historial,
            'dispositivos' => $dispositivos,
            'titulo' => 'Bitácora de Movimientos'
        ];

        return view('admin/historial/index', $data);
    }

    public function crear()
    {
        $rules = [
            'dispositivo_orden_id' => 'required|is_not_unique[dispositivos_orden.id]',
            'observacion' => 'required|min_length[5]',
            'estado_nuevo' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirectView('admin/historial', $this->validator, [['Errores de validación', 'error', 'top-end']]);
        }

        $dispositivo = $this->dispositivosOrdenModel->find($this->request->getPost('dispositivo_orden_id'));

        $data = [
            'dispositivo_orden_id' => $this->request->getPost('dispositivo_orden_id'),
            'usuario_id' => session()->get('id_usuario'),
            'estado_anterior' => $dispositivo['estado'],
            'estado_nuevo' => $this->request->getPost('estado_nuevo'),
            'observacion' => $this->request->getPost('observacion'),
            'observacion_cliente' => $this->request->getPost('observacion_cliente') ?: null
        ];

        $this->historialModel->insert($data);

        return redirectView('admin/historial', null, [['Nota agregada correctamente', 'success', 'top-end']]);
    }

    public function editar()
    {
        $id = $this->request->getPost('id');
        
        $registro = $this->historialModel->find($id);
        if (!$id || !$registro) {
            return redirectView('admin/historial', null, [['Registro no encontrado', 'error', 'top-end']]);
        }

        $dispositivo = $this->dispositivosOrdenModel->find($registro['dispositivo_orden_id']);
        if ($dispositivo && in_array($dispositivo['estado'], ['listo', 'entregado', 'cancelado'])) {
            return redirectView('admin/historial', null, [['No se puede editar el historial de un dispositivo finalizado o cancelado.', 'error', 'top-end']]);
        }

        $rules = [
            'observacion' => 'required|min_length[5]',
        ];

        if (!$this->validate($rules)) {
            return redirectView('admin/historial', $this->validator, [['La observación es obligatoria', 'error', 'top-end']]);
        }

        $data = [
            'observacion' => $this->request->getPost('observacion'),
            'observacion_cliente' => $this->request->getPost('observacion_cliente') ?: null
        ];

        $this->historialModel->update($id, $data);

        return redirectView('admin/historial', null, [['Nota actualizada', 'success', 'top-end']]);
    }

    public function eliminar()
    {
        $id = $this->request->getPost('id');

        if (session()->get('role') !== 'admin') {
            return redirectView('admin/historial', null, [['No tienes permisos para eliminar historial', 'error', 'top-end']]);
        }

        if ($this->historialModel->delete($id)) {
            return redirectView('admin/historial', null, [['Registro eliminado', 'success', 'top-end']]);
        }

        return redirectView('admin/historial', null, [['Error al eliminar', 'error', 'top-end']]);
    }
}
