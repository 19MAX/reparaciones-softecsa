<?php

namespace App\Controllers\Recepcionista;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        // Obtener estadísticas del día
        $hoy = date('Y-m-d');

        // Órdenes creadas hoy
        $ordenesHoy = $db->table('ordenes_trabajo')
            ->where('DATE(created_at)', $hoy)
            ->countAllResults();

        // Órdenes activas (no entregadas ni canceladas)
        $ordenesActivas = $db->table('ordenes_trabajo')
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->countAllResults();

        // Órdenes listas para retiro
        $ordenesListasRetiro = $db->table('ordenes_trabajo')
            ->where('estado', 'listo_para_retiro')
            ->countAllResults();

        // Últimas 10 órdenes
        $builder = $db->table('ordenes_trabajo as o');
        $builder->select('
            o.*,
            c.nombres,
            c.apellidos,
            u.nombre as nombre_prioridad,
            u.recargo as recargo_prioridad,
            u.descripcion as descripcion_prioridad,
            (SELECT GROUP_CONCAT(CONCAT(marca, " ", modelo) SEPARATOR ", ")
             FROM dispositivos d WHERE d.orden_id = o.id) as equipos_resumen
        ');
        $builder->join('clientes as c', 'c.id = o.cliente_id');
        $builder->join('urgencias as u', 'u.id = o.urgencia_id', 'left');
        $builder->orderBy('o.id', 'DESC');
        $builder->limit(10);

        $ordenesRecientes = $builder->get()->getResultArray();

        $data = [
            'titulo' => 'Dashboard Recepcionista',
            'ordenesHoy' => $ordenesHoy,
            'ordenesActivas' => $ordenesActivas,
            'ordenesListasRetiro' => $ordenesListasRetiro,
            'ordenesRecientes' => $ordenesRecientes
        ];

        return view('recepcionista/dashboard', $data);
    }
}
