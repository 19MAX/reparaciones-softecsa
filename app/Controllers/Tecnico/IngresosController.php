<?php

namespace App\Controllers\Tecnico;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class IngresosController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $tecnicoId = session()->get('id_usuario');
        
        // Obtener configuración del técnico
        $usuarioModel = new \App\Models\UsuarioModel();
        $tecnico = $usuarioModel->find($tecnicoId);
        
        // Fechas para filtros
        $inicioMes = date('Y-m-01 00:00:00');
        $finMes = date('Y-m-t 23:59:59');
        $inicioAno = date('Y-01-01 00:00:00');
        $finAno = date('Y-12-31 23:59:59');
        
        // Obtener reparaciones finalizadas del técnico
        // Ahora usamos la tabla simplificada que tiene orden_id
        $reparaciones = $db->query("
            SELECT 
                `of`.*,
                ot.codigo_orden,
                c.nombres as cliente_nombres,
                c.apellidos as cliente_apellidos,
                COUNT(DISTINCT d.id) as cantidad_dispositivos,
                SUM(d.mano_obra) as mano_obra_dispositivos,
                SUM(d.valor_repuestos) as repuestos_dispositivos
            FROM ordenes_finalizadas `of`
            INNER JOIN ordenes_trabajo ot ON ot.id = `of`.orden_id
            INNER JOIN clientes c ON c.id = ot.cliente_id
            INNER JOIN dispositivos d ON d.orden_id = ot.id
            WHERE d.tecnico_id = ?
            GROUP BY `of`.id
            ORDER BY `of`.fecha_finalizacion DESC
        ", [$tecnicoId])->getResultArray();
        
        // Calcular estadísticas
        $totalMes = 0;
        $totalAno = 0;
        $totalGeneral = 0;
        $contadorReparaciones = 0;
        
        foreach ($reparaciones as &$reparacion) {
            // Calcular ganancia de ESTE técnico en esta orden
            $ganancia = 0;
            
            if ($tecnico['tipo_comision'] === 'porcentaje') {
                // Porcentaje sobre la mano de obra de SUS dispositivos
                $ganancia = ($reparacion['mano_obra_dispositivos'] * $tecnico['valor_comision']) / 100;
            } else {
                // Monto fijo por cada dispositivo suyo
                $ganancia = $tecnico['valor_comision'] * $reparacion['cantidad_dispositivos'];
            }
            
            $reparacion['ganancia_calculada'] = $ganancia;
            $totalGeneral += $ganancia;
            $contadorReparaciones++;
            
            // Sumar a mes actual
            if ($reparacion['fecha_finalizacion'] >= $inicioMes && $reparacion['fecha_finalizacion'] <= $finMes) {
                $totalMes += $ganancia;
            }
            
            // Sumar a año actual
            if ($reparacion['fecha_finalizacion'] >= $inicioAno && $reparacion['fecha_finalizacion'] <= $finAno) {
                $totalAno += $ganancia;
            }
        }
        
        $promedioReparacion = $contadorReparaciones > 0 ? $totalGeneral / $contadorReparaciones : 0;
        
        $data = [
            'titulo' => 'Mis Ingresos',
            'tecnico' => $tecnico,
            'totalMes' => $totalMes,
            'totalAno' => $totalAno,
            'promedioReparacion' => $promedioReparacion,
            'reparaciones' => $reparaciones
        ];
        
        return view('tecnico/ingresos/index', $data);
    }
}
