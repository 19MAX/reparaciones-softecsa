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

        // Dispositivos asignados al técnico
        $totalDispositivos = $db->table('dispositivos')
            ->where('tecnico_id', $tecnicoId)
            ->countAllResults();

        // Dispositivos en proceso (no finalizados)
        $dispositivosEnProceso = $db->table('dispositivos d')
            ->join('ordenes_trabajo o', 'o.id = d.orden_id')
            ->where('d.tecnico_id', $tecnicoId)
            ->whereNotIn('o.estado', ['entregado', 'cancelado'])
            ->countAllResults();

        // Dispositivos finalizados este mes
        $inicioMes = date('Y-m-01 00:00:00');
        $finMes = date('Y-m-t 23:59:59');

        $dispositivosFinalizadosMes = $db->table('dispositivos d')
            ->join('ordenes_trabajo o', 'o.id = d.orden_id')
            ->where('d.tecnico_id', $tecnicoId)
            ->where('o.estado', 'entregado')
            ->where('o.created_at >=', $inicioMes)
            ->where('o.created_at <=', $finMes)
            ->countAllResults();

        // Ganancia del mes actual
        $usuarioModel = new \App\Models\UsuarioModel();
        $tecnico = $usuarioModel->find($tecnicoId);

        // Calcular ganancia basada en órdenes finalizadas este mes
        // Usando la nueva estructura de ordenes_finalizadas
        $ordenesFinalizadas = $db->query("
            SELECT 
                `of`.mano_obra_total,
                `of`.total,
                COUNT(DISTINCT d.id) as cantidad_dispositivos,
                SUM(d.mano_obra) as mano_obra_dispositivos
            FROM ordenes_finalizadas `of`
            INNER JOIN ordenes_trabajo ot ON ot.id = `of`.orden_id
            INNER JOIN dispositivos d ON d.orden_id = ot.id
            WHERE d.tecnico_id = ?
                AND `of`.fecha_finalizacion >= ?
                AND `of`.fecha_finalizacion <= ?
            GROUP BY `of`.id
        ", [$tecnicoId, $inicioMes, $finMes])->getResultArray();

        $gananciaMes = 0;
        foreach ($ordenesFinalizadas as $orden) {
            // Calcular ganancia de este técnico en esta orden
            if ($tecnico['tipo_comision'] === 'porcentaje') {
                // Porcentaje sobre la mano de obra de SUS dispositivos
                $gananciaMes += ($orden['mano_obra_dispositivos'] * $tecnico['valor_comision']) / 100;
            } else {
                // Monto fijo por dispositivo
                $gananciaMes += $tecnico['valor_comision'] * $orden['cantidad_dispositivos'];
            }
        }

        // Últimos 10 dispositivos asignados
        $builder = $db->table('dispositivos as d');
        $builder->select('
            d.*,
            td.nombre as nombre_tipo,
            td.icono,
            o.codigo_orden,
            o.estado as estado_orden,
            c.nombres as cliente_nombres,
            c.apellidos as cliente_apellidos
        ');
        $builder->join('tipos_dispositivo as td', 'td.id = d.tipo_dispositivo_id', 'left');
        $builder->join('ordenes_trabajo as o', 'o.id = d.orden_id');
        $builder->join('clientes as c', 'c.id = o.cliente_id');
        $builder->where('d.tecnico_id', $tecnicoId);
        $builder->orderBy('d.created_at', 'DESC');
        $builder->limit(10);

        $dispositivosRecientes = $builder->get()->getResultArray();

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
