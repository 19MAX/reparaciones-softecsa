<?php

namespace App\Controllers\Recepcionista;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();

        $hoy = date('Y-m-d');

        $ordenesHoy = $db->table('ordenes')
            ->where('DATE(created_at)', $hoy)
            ->countAllResults();

        $ordenesActivas = $db->table('ordenes')
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->countAllResults();

        $ordenesListasRetiro = $db->table('ordenes')
            ->where('estado', 'listo_para_retiro')
            ->countAllResults();

        // Últimas órdenes
        $builder = $db->table('ordenes o');

        $builder->select("
            o.id,
            o.numero_orden,
            o.estado,
            o.created_at,
            c.nombres,
            c.apellidos,
            (
                SELECT GROUP_CONCAT(
                    CONCAT(m.nombre, ' ', mo.nombre)
                    SEPARATOR ', '
                )
                FROM dispositivos_orden d
                INNER JOIN marcas m ON m.id = d.marca_id
                INNER JOIN modelos mo ON mo.id = d.modelo_id
                WHERE d.orden_id = o.id
            ) as equipos_resumen
        ");

        $builder->join('clientes c', 'c.id = o.cliente_id');
        $builder->orderBy('o.created_at', 'DESC');
        $builder->limit(10);

        $ordenesRecientes = $builder->get()->getResultArray();

        return view('recepcionista/dashboard', [
            'titulo' => 'Dashboard Recepcionista',
            'ordenesHoy' => $ordenesHoy,
            'ordenesActivas' => $ordenesActivas,
            'ordenesListasRetiro' => $ordenesListasRetiro,
            'ordenesRecientes' => $ordenesRecientes
        ]);
    }
}
