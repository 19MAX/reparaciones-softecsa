<?php

namespace App\Controllers\Recepcionista;

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

    public function ver($id)
    {
        // Obtener dispositivo con información relacionada
        $dispositivo = $this->dispositivoModel
            ->select('dispositivos.*, 
                      td.nombre as nombre_tipo, 
                      td.icono,
                      o.codigo_orden,
                      o.estado as estado_orden,
                      c.nombres as cliente_nombres,
                      c.apellidos as cliente_apellidos,
                      tec.nombres as tecnico_nombres,
                      tec.apellidos as tecnico_apellidos')
            ->join('tipos_dispositivo as td', 'td.id = dispositivos.tipo_dispositivo_id', 'left')
            ->join('ordenes_trabajo as o', 'o.id = dispositivos.orden_id')
            ->join('clientes as c', 'c.id = o.cliente_id')
            ->join('usuarios as tec', 'tec.id = dispositivos.tecnico_id', 'left')
            ->where('dispositivos.id', $id)
            ->first();

        if (!$dispositivo) {
            return redirect()->back()->with('error', 'Dispositivo no encontrado');
        }

        // Obtener historial completo del dispositivo
        $historial = $this->historialModel->obtenerHistorialDispositivo($id);

        $data = [
            'titulo' => 'Detalles del Dispositivo',
            'dispositivo' => $dispositivo,
            'historial' => $historial
        ];

        return view('recepcionista/dispositivos/ver', $data);
    }
}
