<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DispositivoModel;
use CodeIgniter\HTTP\ResponseInterface;

class DispositivoController extends BaseController
{
    protected $dispositivoModel;

    public function __construct()
    {
        $this->dispositivoModel = new DispositivoModel();
    }

    /**
     * Ver todos los dispositivos agrupados por técnico
     */
    public function index()
    {
        $db = \Config\Database::connect();

        // Obtener todos los técnicos con sus dispositivos
        $tecnicos = $db->query("
            SELECT 
                u.id as tecnico_id,
                u.nombres,
                u.apellidos,
                u.tipo_comision,
                u.valor_comision,
                COUNT(d.id) as total_dispositivos,
                SUM(CASE WHEN o.estado != 'entregado' AND o.estado != 'cancelado' THEN 1 ELSE 0 END) as dispositivos_activos,
                SUM(CASE WHEN d.estado_reparacion = 'listo_retiro' THEN 1 ELSE 0 END) as dispositivos_listos
            FROM usuarios u
            LEFT JOIN dispositivos d ON d.tecnico_id = u.id
            LEFT JOIN ordenes_trabajo o ON o.id = d.orden_id
            WHERE u.role = 'tecnico' AND u.estado = 'activo'
            GROUP BY u.id
            ORDER BY u.nombres, u.apellidos
        ")->getResultArray();

        $data = [
            'titulo' => 'Dispositivos por Técnico',
            'tecnicos' => $tecnicos
        ];

        return view('admin/dispositivos/index', $data);
    }

    /**
     * Ver dispositivos de un técnico específico
     */
    public function verTecnico($tecnicoId)
    {
        $db = \Config\Database::connect();

        // Obtener información del técnico
        $usuarioModel = new \App\Models\UsuarioModel();
        $tecnico = $usuarioModel->find($tecnicoId);

        if (!$tecnico || $tecnico['role'] !== 'tecnico') {
            return redirect()->to(base_url('admin/dispositivos'))
                ->with('error', 'Técnico no encontrado');
        }

        // Obtener dispositivos del técnico
        $dispositivos = $this->dispositivoModel
            ->select('dispositivos.*, 
                      td.nombre as nombre_tipo, 
                      td.icono,
                      o.id as orden_id,
                      o.codigo_orden,
                      o.estado as estado_orden,
                      c.nombres as cliente_nombres,
                      c.apellidos as cliente_apellidos,
                      c.telefono as cliente_telefono')
            ->join('tipos_dispositivo as td', 'td.id = dispositivos.tipo_dispositivo_id', 'left')
            ->join('ordenes_trabajo as o', 'o.id = dispositivos.orden_id')
            ->join('clientes as c', 'c.id = o.cliente_id')
            ->where('dispositivos.tecnico_id', $tecnicoId)
            ->orderBy('o.estado', 'ASC')
            ->orderBy('dispositivos.created_at', 'DESC')
            ->findAll();

        // Agrupar por orden
        $ordenes = [];
        foreach ($dispositivos as $disp) {
            $ordenId = $disp['orden_id'];
            if (!isset($ordenes[$ordenId])) {
                $ordenes[$ordenId] = [
                    'orden_id' => $ordenId,
                    'codigo_orden' => $disp['codigo_orden'],
                    'estado_orden' => $disp['estado_orden'],
                    'cliente_nombres' => $disp['cliente_nombres'],
                    'cliente_apellidos' => $disp['cliente_apellidos'],
                    'cliente_telefono' => $disp['cliente_telefono'],
                    'dispositivos' => [],
                    'todos_listos' => true,
                    'total_mano_obra' => 0,
                    'total_repuestos' => 0
                ];
            }

            $ordenes[$ordenId]['dispositivos'][] = $disp;
            $ordenes[$ordenId]['total_mano_obra'] += $disp['mano_obra'];
            $ordenes[$ordenId]['total_repuestos'] += $disp['valor_repuestos'];

            if ($disp['estado_reparacion'] !== 'listo_retiro') {
                $ordenes[$ordenId]['todos_listos'] = false;
            }
        }

        $data = [
            'titulo' => 'Dispositivos de ' . $tecnico['nombres'] . ' ' . $tecnico['apellidos'],
            'tecnico' => $tecnico,
            'ordenes' => array_values($ordenes)
        ];

        return view('admin/dispositivos/ver_tecnico', $data);
    }
}
