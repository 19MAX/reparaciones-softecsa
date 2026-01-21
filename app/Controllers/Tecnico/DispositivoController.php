<?php

namespace App\Controllers\Tecnico;

use App\Controllers\BaseController;
use App\Models\DispositivoModel;
use App\Models\HistorialDispositivoModel;
use CodeIgniter\HTTP\ResponseInterface;

class DispositivoController extends BaseController
{
    protected $dispositivoModel;
    protected $historialModel;

    public function __construct()
    {
        $this->dispositivoModel = new DispositivoModel();
        $this->historialModel = new HistorialDispositivoModel();
    }

    public function index()
    {
        $tecnicoId = session()->get('id_usuario');

        // Obtener solo dispositivos del técnico autenticado
        $dispositivos = $this->dispositivoModel
            ->select('dispositivos.*, 
                      td.nombre as nombre_tipo, 
                      td.icono,
                      o.codigo_orden,
                      o.estado as estado_orden,
                      c.nombres as cliente_nombres,
                      c.apellidos as cliente_apellidos')
            ->join('tipos_dispositivo as td', 'td.id = dispositivos.tipo_dispositivo_id', 'left')
            ->join('ordenes_trabajo as o', 'o.id = dispositivos.orden_id')
            ->join('clientes as c', 'c.id = o.cliente_id')
            ->where('dispositivos.tecnico_id', $tecnicoId)
            ->orderBy('dispositivos.created_at', 'DESC')
            ->findAll();

        $data = [
            'titulo' => 'Mis Dispositivos Asignados',
            'dispositivos' => $dispositivos
        ];

        return view('tecnico/dispositivos/index', $data);
    }

    public function ver($id)
    {
        $tecnicoId = session()->get('id_usuario');

        // Obtener dispositivo con validación de seguridad
        $dispositivo = $this->dispositivoModel
            ->select('dispositivos.*, 
                      td.nombre as nombre_tipo, 
                      td.icono,
                      o.codigo_orden,
                      o.estado as estado_orden,
                      o.id as orden_id,
                      c.nombres as cliente_nombres,
                      c.apellidos as cliente_apellidos,
                      c.email as cliente_email,
                      c.telefono as cliente_telefono')
            ->join('tipos_dispositivo as td', 'td.id = dispositivos.tipo_dispositivo_id', 'left')
            ->join('ordenes_trabajo as o', 'o.id = dispositivos.orden_id')
            ->join('clientes as c', 'c.id = o.cliente_id')
            ->where('dispositivos.id', $id)
            ->where('dispositivos.tecnico_id', $tecnicoId) // SEGURIDAD: Solo sus dispositivos
            ->first();

        if (!$dispositivo) {
            return redirect()->to(base_url('tecnico/dispositivos'))
                ->with('error', 'Dispositivo no encontrado o no tienes permisos para verlo');
        }

        // Obtener historial completo del dispositivo
        // Asegúrate que tu modelo Historial haga un join con la tabla usuarios para traer 'usuario_nombres'
        $historial = $this->historialModel->obtenerHistorialDispositivo($id);

        // NOTA: Ya no necesitamos pasar $estadosDisponibles porque usamos el helper en la vista
        // con la función get_select_estados_dispositivo()

        $data = [
            'titulo' => 'Detalles del Dispositivo',
            'dispositivo' => $dispositivo,
            'historial' => $historial
        ];

        return view('tecnico/dispositivos/ver', $data);
    }

    public function actualizarEstado()
    {
        $tecnicoId = session()->get('id_usuario');

        // Datos del formulario
        $dispositivoId = $this->request->getPost('dispositivo_id');
        $nuevoEstadoId = $this->request->getPost('estado'); // Esto ahora es un ID (int)
        $comentario = $this->request->getPost('comentario');
        $esVisibleCliente = $this->request->getPost('es_visible_cliente') ? 1 : 0;

        // Costos (opcionales)
        $manoObra = $this->request->getPost('mano_obra');
        $valorRepuestos = $this->request->getPost('valor_repuestos');

        // 1. Validar permiso (que el dispositivo sea del técnico)
        $dispositivoActual = $this->dispositivoModel
            ->where('id', $dispositivoId)
            ->where('tecnico_id', $tecnicoId)
            ->first();

        if (!$dispositivoActual) {
            return redirect()->back()->with('error', 'No tienes permisos para actualizar este dispositivo');
        }

        // 2. Reglas de Validación Actualizadas
        $validation = \Config\Services::validation();
        $rules = [
            'estado' => 'required|is_natural_no_zero', // Valida que sea un número entero > 0
            'comentario' => 'required|min_length[5]'   // Reduje un poco el mínimo para ser más flexible
        ];

        if (!$validation->setRules($rules)->run($this->request->getPost())) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        try {
            $db = \Config\Database::connect();
            $db->transStart();

            // 3. Preparar datos para actualizar Dispositivo
            $datosActualizacion = [
                'estado_reparacion' => $nuevoEstadoId, // Guardamos el ID del estado
            ];

            // Solo actualizamos costos si vienen en el POST (si el switch estaba activo)
            // Si están vacíos o son null, mantenemos el valor actual de la base de datos o ponemos 0
            if ($manoObra !== null && $manoObra !== '') {
                $datosActualizacion['mano_obra'] = $manoObra;
            }

            if ($valorRepuestos !== null && $valorRepuestos !== '') {
                $datosActualizacion['valor_repuestos'] = $valorRepuestos;
            }

            // Ejecutar Update en tabla Dispositivos
            $this->dispositivoModel->update($dispositivoId, $datosActualizacion);

            // 4. Preparar datos para el Historial
            // Obtenemos los nombres legibles usando el Helper para guardarlos en el historial
            // Esto es importante para que en el timeline se lea "En Reparación" y no "5"

            $nombreEstadoAnterior = get_nombre_estado_dispositivo((int) $dispositivoActual['estado_reparacion']);
            $nombreNuevoEstado = get_nombre_estado_dispositivo((int) $nuevoEstadoId);

            $this->historialModel->insert([
                'dispositivo_id' => $dispositivoId,
                'usuario_id' => $tecnicoId,
                'estado_anterior' => $nombreEstadoAnterior, // Guardamos Texto
                'estado_nuevo' => $nombreNuevoEstado,    // Guardamos Texto
                'comentario' => $comentario,
                'es_visible_cliente' => $esVisibleCliente,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error de base de datos al guardar la transacción.');
            }

            return redirect()->to(base_url('tecnico/dispositivos/ver/' . $dispositivoId))
                ->with('success', 'Estado actualizado correctamente.');

        } catch (\Exception $e) {
            log_message('error', '[Tecnico/DispositivoController::actualizarEstado] ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }
}
