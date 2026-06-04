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
        $ordenesHoy = $db->table('ordenes')
            ->where('DATE(created_at)', $hoy)
            ->countAllResults();

        // Órdenes activas (no entregadas ni canceladas)
        $ordenesActivas = $db->table('ordenes')
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->countAllResults();

        // Órdenes listas para retiro
        $ordenesListasRetiro = $db->table('ordenes')
            ->where('estado', 'listo_para_retiro')
            ->countAllResults();

        // Últimas 10 órdenes
        $builder = $db->table('ordenes as o');
        $builder->select('
            o.*,
            c.nombres,
            c.apellidos,
            p.nombre as nombre_prioridad
        ');
        $builder->join('clientes as c', 'c.id = o.cliente_id');
        $builder->join('prioridades as p', 'p.id = o.prioridad_id', 'left');
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
