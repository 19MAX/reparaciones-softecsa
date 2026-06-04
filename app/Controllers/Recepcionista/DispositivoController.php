<?php

namespace App\Controllers\Recepcionista;

use App\Controllers\BaseController;
use App\Models\DispositivosOrdenModel;
use App\Models\HistorialEstados;
use CodeIgniter\HTTP\ResponseInterface;

class DispositivoController extends BaseController
{
    protected $dispositivoModel;
    protected $historialModel;

    public function __construct()
    {
        $this->dispositivoModel = new DispositivosOrdenModel();
        $this->historialModel = new HistorialEstados();
    }

    public function ver($id)
    {
        // Obtener dispositivo con información relacionada usando el modelo correcto
        $dispositivo = $this->dispositivoModel->getDetalleCompleto($id);

        if (!$dispositivo) {
            return redirect()->back()->with('error', 'Dispositivo no encontrado');
        }

        // Obtener historial de estados
        $db = \Config\Database::connect();
        $historial = $db->table('historial_estados he')
            ->select('he.*, u.nombre as usuario_nombre, u.apellido as usuario_apellido')
            ->join('usuarios u', 'u.id = he.usuario_id', 'left')
            ->where('he.dispositivo_orden_id', $id)
            ->orderBy('he.created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        $data = [
            'titulo' => 'Detalles del Dispositivo',
            'dispositivo' => $dispositivo,
            'historial' => $historial,
        ];

        return view('recepcionista/dispositivos/ver', $data);
    }

    public function entregar($id)
    {
        $usuarioId = session()->get('id_usuario');

        if (empty($usuarioId)) {
            return redirect()->to(base_url('login'))->with('mensaje', 'Tu sesión ha expirado.');
        }

        try {
            $dispositivo = $this->dispositivoModel->find($id);

            if (!$dispositivo) {
                return redirect()->back()->with('error', 'Dispositivo no encontrado');
            }

            // Update device to delivered state
            $this->dispositivoModel->update($id, [
                'estado' => 'entregado'
            ]);

            // Record in history
            $this->historialModel->insert([
                'dispositivo_orden_id' => $id,
                'estado_anterior' => $dispositivo['estado'],
                'estado_nuevo' => 'entregado',
                'usuario_id' => $usuarioId,
                'observacion' => 'Dispositivo entregado al cliente por recepción.',
            ]);

            return redirect()->back()->with('success', 'Dispositivo marcado como entregado exitosamente.');

        } catch (\Exception $e) {
            log_message('error', '[Recepcionista/DispositivoController::entregar] ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al procesar la entrega: ' . $e->getMessage());
        }
    }
}