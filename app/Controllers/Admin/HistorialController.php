<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\HistorialDispositivoModel;
use CodeIgniter\HTTP\ResponseInterface;

class HistorialController extends BaseController
{
    public $historialModel;
    public $dispositivosModel;
    public $validation;

    public function __construct()
    {
        $this->historialModel = new HistorialDispositivoModel();
        // Cargamos el modelo de dispositivos (asegúrate de tener este archivo creado)
        // Si no tienes la clase creada, usa: $this->dispositivosModel = new \App\Models\DispositivosModel();
        // O usa db connect builder si prefieres no crear el modelo aún.
        $this->dispositivosModel = model('App\Models\DispositivosModel');
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {
        // 1. Obtener Historial Completo
        $historial = $this->historialModel->obtenerHistorialCompleto();

        // 2. Obtener lista de dispositivos para el Select del Modal
        // Hacemos JOIN con ordenes y clientes para que el select diga: "ORD-001 - Samsung S20 - Juan Perez"
        $dispositivos = $this->dispositivosModel
            ->select('dispositivos.id, dispositivos.marca, dispositivos.modelo, ordenes_trabajo.codigo_orden, clientes.nombres, clientes.apellidos')
            ->join('ordenes_trabajo', 'ordenes_trabajo.id = dispositivos.orden_id')
            ->join('clientes', 'clientes.id = ordenes_trabajo.cliente_id')
            ->orderBy('ordenes_trabajo.created_at', 'DESC')
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
        // Validamos que el ID exista en la tabla dispositivos
        $rules = [
            'dispositivo_id' => 'required|is_not_unique[dispositivos.id]',
            'comentario' => 'required|min_length[5]',
            'estado_nuevo' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirectView('admin/historial', $this->validator, [['Errores de validación', 'error', 'top-end']]);
        }

        // Datos a insertar
        $data = [
            'dispositivo_id' => $this->request->getPost('dispositivo_id'),
            // Usamos el ID de la sesión del usuario actual
            'usuario_id' => session()->get('id_usuario'),
            'estado_anterior' => $this->request->getPost('estado_nuevo'), // En notas manuales, mantenemos el estado
            'estado_nuevo' => $this->request->getPost('estado_nuevo'),
            'comentario' => $this->request->getPost('comentario'),
            'es_visible_cliente' => $this->request->getPost('es_visible_cliente') ? 1 : 0
        ];

        $this->historialModel->insert($data);

        return redirectView('admin/historial', null, [['Nota agregada correctamente', 'success', 'top-end']]);
    }

    public function editar()
    {
        $id = $this->request->getPost('id');

        if (!$id || !$this->historialModel->find($id)) {
            return redirectView('admin/historial', null, [['Registro no encontrado', 'error', 'top-end']]);
        }

        $rules = [
            'comentario' => 'required|min_length[5]',
        ];

        if (!$this->validate($rules)) {
            return redirectView('admin/historial', $this->validator, [['El comentario es obligatorio', 'error', 'top-end']]);
        }

        $data = [
            'comentario' => $this->request->getPost('comentario'),
            'es_visible_cliente' => $this->request->getPost('es_visible_cliente') ? 1 : 0
        ];

        $this->historialModel->update($id, $data);

        return redirectView('admin/historial', null, [['Nota actualizada', 'success', 'top-end']]);
    }

    public function eliminar()
    {
        $id = $this->request->getPost('id');

        // Aquí podrías agregar una validación extra: solo ADMIN puede borrar historial
        if (session()->get('role') !== 'admin') {
            return redirectView('admin/historial', null, [['No tienes permisos para eliminar historial', 'error', 'top-end']]);
        }

        if ($this->historialModel->delete($id)) {
            return redirectView('admin/historial', null, [['Registro eliminado', 'success', 'top-end']]);
        }

        return redirectView('admin/historial', null, [['Error al eliminar', 'error', 'top-end']]);
    }
}
