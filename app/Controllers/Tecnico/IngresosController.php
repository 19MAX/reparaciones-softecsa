<?php

namespace App\Controllers\Tecnico;

use App\Controllers\BaseController;
use App\Models\PagosTecnicosModel;
use App\Models\TecnicosConfigModel;

class IngresosController extends BaseController
{
    public function index()
    {
        $tecnicoId = (int) session()->get('id_usuario');
        $db        = \Config\Database::connect();

        // Configuración de comisión del técnico
        $tecnicoConfig = $db->table('tecnicos_config')
            ->where('usuario_id', $tecnicoId)
            ->get()->getRowArray();

        // Historial de comisiones desde pagos_tecnicos
        $pagosModel = new PagosTecnicosModel();
        $pagos = $pagosModel->getHistorialTecnico($tecnicoId);

        // Calcular totales
        $inicioMes = date('Y-m-01 00:00:00');
        $finMes    = date('Y-m-t 23:59:59');
        $inicioAno = date('Y-01-01 00:00:00');
        $finAno    = date('Y-12-31 23:59:59');

        $totalMes      = 0.0;
        $totalAno      = 0.0;
        $totalGeneral  = 0.0;
        $totalPendiente = 0.0;
        $totalValidado  = 0.0;
        $totalPagado    = 0.0;

        foreach ($pagos as $pago) {
            $monto = (float) $pago['monto_comision'];
            $fecha = $pago['fecha_reparacion'];

            $totalGeneral += $monto;

            if ($fecha >= $inicioMes && $fecha <= $finMes) {
                $totalMes += $monto;
            }
            if ($fecha >= $inicioAno && $fecha <= $finAno) {
                $totalAno += $monto;
            }

            match ($pago['estado_pago']) {
                'pendiente' => $totalPendiente += $monto,
                'validado'  => $totalValidado  += $monto,
                'pagado'    => $totalPagado    += $monto,
                default     => null,
            };
        }

        $contadorReparaciones = count($pagos);
        $promedioReparacion   = $contadorReparaciones > 0
            ? $totalGeneral / $contadorReparaciones
            : 0.0;

        return view('tecnico/ingresos/index', [
            'titulo'              => 'Mis Ingresos',
            'tecnico_config'      => $tecnicoConfig,
            'pagos'               => $pagos,
            'totalMes'            => $totalMes,
            'totalAno'            => $totalAno,
            'totalGeneral'        => $totalGeneral,
            'totalPendiente'      => $totalPendiente,
            'totalValidado'       => $totalValidado,
            'totalPagado'         => $totalPagado,
            'promedioReparacion'  => $promedioReparacion,
        ]);
    }
}
