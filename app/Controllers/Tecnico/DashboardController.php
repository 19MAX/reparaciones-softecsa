<?php

namespace App\Controllers\Tecnico;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $tecnicoId = session()->get('id_usuario');

        $usuarioModel = new \App\Models\UsuarioModel();
        $tecnico = $usuarioModel->find($tecnicoId);
        
        $tecnico['config'] = $db->table('tecnicos_config')
            ->where('usuario_id', $tecnicoId)
            ->get()->getRowArray();

        // Total asignados
        $totalDispositivos = $db->table('dispositivos_orden')
            ->where('tecnico_id', $tecnicoId)
            ->countAllResults();

        // En Proceso (en_proceso, pendiente)
        $dispositivosEnProceso = $db->table('dispositivos_orden')
            ->where('tecnico_id', $tecnicoId)
            ->whereIn('estado', ['en_proceso', 'pendiente'])
            ->countAllResults();

        // Finalizados este mes
        $inicioMes = date('Y-m-01 00:00:00');
        $finMes = date('Y-m-t 23:59:59');

        $dispositivosFinalizadosMes = $db->table('dispositivos_orden')
            ->where('tecnico_id', $tecnicoId)
            ->whereIn('estado', ['listo', 'entregado'])
            ->where('updated_at >=', $inicioMes)
            ->where('updated_at <=', $finMes)
            ->countAllResults();

        // Ganancia del mes actual
        $resultadoGanancia = $db->table('dispositivos_orden')
            ->selectSum('comision_tecnico')
            ->where('tecnico_id', $tecnicoId)
            ->whereIn('estado', ['listo', 'entregado'])
            ->where('updated_at >=', $inicioMes)
            ->where('updated_at <=', $finMes)
            ->get()->getRow();

        $gananciaMes = $resultadoGanancia->comision_tecnico ?? 0;

        // Últimos 10 dispositivos asignados
        $dispositivosRecientes = $db->table('dispositivos_orden do')
            ->select('
                do.id,
                do.estado,
                do.created_at,
                td.nombre as nombre_tipo,
                m.nombre as marca,
                COALESCE(mo.nombre, do.modelo_texto) as modelo,
                o.numero_orden as codigo_orden,
                c.nombres as cliente_nombres,
                c.apellidos as cliente_apellidos
            ')
            ->join('tipos_dispositivo td', 'td.id = do.tipo_dispositivo_id', 'left')
            ->join('marcas m', 'm.id = do.marca_id', 'left')
            ->join('modelos mo', 'mo.id = do.modelo_id', 'left')
            ->join('ordenes o', 'o.id = do.orden_id')
            ->join('clientes c', 'c.id = o.cliente_id')
            ->where('do.tecnico_id', $tecnicoId)
            ->orderBy('do.created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        $data = [
            'titulo' => 'Dashboard Técnico',
            'totalDispositivos' => $totalDispositivos,
            'dispositivosEnProceso' => $dispositivosEnProceso,
            'dispositivosFinalizadosMes' => $dispositivosFinalizadosMes,
            'gananciaMes' => $gananciaMes,
            'dispositivosRecientes' => $dispositivosRecientes,
            'tecnico' => $tecnico
        ];

        return view('tecnico/dashboard', $data);
    }
}
