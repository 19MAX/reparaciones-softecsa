<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // 1. Resumen de contadores básicos
        $totalClientes = $db->table('clientes')->countAllResults();
        
        $dispositivosPorReparar = $db->table('dispositivos_orden')
            ->whereIn('estado', ['pendiente', 'en_proceso', 'pausado'])
            ->countAllResults();
            
        $dispositivosReparados = $db->table('dispositivos_orden')
            ->whereIn('estado', ['listo', 'entregado'])
            ->countAllResults();
            
        $dineroRecaudado = $db->table('dispositivos_orden')
            ->selectSum('precio_total')
            ->whereIn('estado', ['listo', 'entregado'])
            ->get()->getRow()->precio_total ?? 0;

        // 2. Tipos de dispositivos más reparados
        $tiposMasReparados = $db->table('dispositivos_orden do')
            ->select('td.nombre, COUNT(do.id) as total')
            ->join('tipos_dispositivo td', 'td.id = do.tipo_dispositivo_id')
            ->groupBy('do.tipo_dispositivo_id')
            ->orderBy('total', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // 3. Técnico con más dispositivos reparados (Top Técnico)
        $topTecnico = $db->table('dispositivos_orden do')
            ->select('u.nombre, u.apellido, COUNT(do.id) as total')
            ->join('usuarios u', 'u.id = do.tecnico_id')
            ->whereIn('do.estado', ['listo', 'entregado'])
            ->groupBy('do.tecnico_id')
            ->orderBy('total', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        // 4. Comisiones de técnicos por mes (Mes actual)
        $mesActual = date('m');
        $anioActual = date('Y');
        $comisionesMes = $db->table('dispositivos_orden do')
            ->select('u.nombre, u.apellido, SUM(do.comision_tecnico) as total_comision')
            ->join('usuarios u', 'u.id = do.tecnico_id')
            ->where('MONTH(do.updated_at)', $mesActual)
            ->where('YEAR(do.updated_at)', $anioActual)
            ->whereIn('do.estado', ['listo', 'entregado'])
            ->groupBy('do.tecnico_id')
            ->get()->getResultArray();

        // 5. Órdenes recientes
        $ordenesRecientes = $db->table('ordenes o')
            ->select('o.*, c.nombres, c.apellidos')
            ->join('clientes c', 'c.id = o.cliente_id')
            ->orderBy('o.created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // 6. Ingresos por día (últimos 7 días) para el gráfico
        $ventasSieteDias = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-$i days"));
            $diaLabel = date('D', strtotime($fecha));
            
            $monto = $db->table('dispositivos_orden')
                ->selectSum('precio_total')
                ->where('DATE(updated_at)', $fecha)
                ->whereIn('estado', ['listo', 'entregado'])
                ->get()->getRow()->precio_total ?? 0;
                
            $ventasSieteDias[] = [
                'dia' => $diaLabel,
                'monto' => (float)$monto
            ];
        }

        $data = [
            'titulo' => 'Panel de Control',
            'stats' => [
                'totalClientes' => $totalClientes,
                'porReparar' => $dispositivosPorReparar,
                'reparados' => $dispositivosReparados,
                'recaudado' => $dineroRecaudado,
                'topTecnico' => $topTecnico,
                'tiposMasReparados' => $tiposMasReparados,
                'comisionesMes' => $comisionesMes,
                'ordenesRecientes' => $ordenesRecientes,
                'ventasSieteDias' => $ventasSieteDias
            ]
        ];

        return view('admin/dashboard', $data);
    }
}
