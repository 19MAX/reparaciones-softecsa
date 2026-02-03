<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DispositivoAccesorioModel;
use App\Models\DispositivoCheckModel;
use App\Models\DispositivoModel;
use App\Models\DispositivoProblemasModel;
use App\Models\GarantiaModel;
use App\Models\HistorialDiagnosticoModel;
use App\Models\HistorialDispositivoModel;
use CodeIgniter\HTTP\ResponseInterface;

class DispositivoController extends BaseController
{
    protected $dispositivoModel;

    protected $problemaModel;
    protected $accesorioModel;
    protected $checkModel;
    protected $historialDiagnosticoModel;
    protected $historialEstadoModel;
    protected $garantiaModel;

    public function __construct()
    {
        $this->dispositivoModel = new DispositivoModel();
        $this->problemaModel = new DispositivoProblemasModel();
        $this->accesorioModel = new DispositivoAccesorioModel();
        $this->checkModel = new DispositivoCheckModel();
        $this->historialDiagnosticoModel = new HistorialDiagnosticoModel();
        $this->historialEstadoModel = new HistorialDispositivoModel();
        $this->garantiaModel = new GarantiaModel();
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


    public function detalle($dispositivoId)
    {
        // Verificar sesión
        if (!session()->get('id_usuario')) {
            return redirect()->to(base_url('login'));
        }

        try {
            // Obtener información completa del dispositivo
            $dispositivo = $this->obtenerDatosDispositivo($dispositivoId);

            if (!$dispositivo) {
                session()->setFlashdata('error', 'Dispositivo no encontrado');
                return redirect()->to(base_url('admin/ordenes'));
            }

            // Obtener problemas
            $problemas = $this->obtenerProblemas($dispositivoId);

            // Obtener accesorios
            $accesorios = $this->obtenerAccesorios($dispositivoId);

            // Obtener checklist
            $checklist = $this->obtenerChecklist($dispositivoId);

            // Obtener historial de diagnósticos
            $historialDiagnostico = $this->obtenerHistorialDiagnostico($dispositivoId);

            // Obtener historial de estados
            $historialEstados = $this->obtenerHistorialEstados($dispositivoId);

            // Obtener garantías
            $garantias = $this->obtenerGarantias($dispositivoId);

            $data = [
                'titulo' => 'Detalle del Dispositivo',
                'dispositivo' => $dispositivo,
                'problemas' => $problemas,
                'accesorios' => $accesorios,
                'checklist' => $checklist,
                'historial_diagnostico' => $historialDiagnostico,
                'historial_estados' => $historialEstados,
                'garantias' => $garantias,
            ];

            return view('admin/dispositivos/detalle', $data);

        } catch (\Exception $e) {
            log_message('error', '[DispositivoController::detalle] ' . $e->getMessage());
            session()->setFlashdata('error', 'Error al cargar el detalle del dispositivo');
            return redirect()->to(base_url('admin/ordenes'));
        }
    }

    // Métodos auxiliares privados

    private function obtenerDatosDispositivo($dispositivoId)
    {
        return $this->dispositivoModel
            ->select('
                dispositivos.*,
                td.nombre as tipo_dispositivo,
                m.nombre as marca,
                mod.nombre as modelo,
                CONCAT(u.nombres, " ", u.apellidos) as tecnico_asignado,
                ot.codigo_orden,
                CONCAT(c.nombres, " ", c.apellidos) as cliente_nombre
            ')
            ->join('tipos_dispositivo as td', 'td.id = dispositivos.tipo_dispositivo_id', 'left')
            ->join('marcas as m', 'm.id = dispositivos.marca_id', 'left')
            ->join('modelos as mod', 'mod.id = dispositivos.modelo_id', 'left')
            ->join('usuarios as u', 'u.id = dispositivos.tecnico_id', 'left')
            ->join('ordenes_trabajo as ot', 'ot.id = dispositivos.orden_id')
            ->join('clientes as c', 'c.id = ot.cliente_id')
            ->where('dispositivos.id', $dispositivoId)
            ->first();
    }

    private function obtenerProblemas($dispositivoId)
    {
        return $this->problemaModel
            ->select('
                dispositivo_problemas.*,
                pc.nombre as problema
            ')
            ->join('problemas_comunes as pc', 'pc.id = dispositivo_problemas.problema_comun_id', 'left')
            ->where('dispositivo_problemas.dispositivo_id', $dispositivoId)
            ->orderBy('dispositivo_problemas.prioridad', 'DESC')
            ->findAll();
    }

    private function obtenerAccesorios($dispositivoId)
    {
        return $this->accesorioModel
            ->select('
                dispositivo_accesorios.*,
                a.nombre as accesorio
            ')
            ->join('accesorios as a', 'a.id = dispositivo_accesorios.accesorio_id')
            ->where('dispositivo_accesorios.dispositivo_id', $dispositivoId)
            ->findAll();
    }

    private function obtenerChecklist($dispositivoId)
    {
        return $this->checkModel
            ->select('
                dispositivo_check.*,
                ci.nombre as item
            ')
            ->join('checklist_items as ci', 'ci.id = dispositivo_check.checklist_item_id')
            ->where('dispositivo_check.dispositivo_id', $dispositivoId)
            ->findAll();
    }

    private function obtenerHistorialDiagnostico($dispositivoId)
    {
        return $this->historialDiagnosticoModel
            ->select('
                historial_diagnostico.*,
                CONCAT(u.nombres, " ", u.apellidos) as tecnico
            ')
            ->join('usuarios as u', 'u.id = historial_diagnostico.tecnico_id')
            ->where('historial_diagnostico.dispositivo_id', $dispositivoId)
            ->orderBy('historial_diagnostico.fecha_inicio_diagnostico', 'DESC')
            ->findAll();
    }

    private function obtenerHistorialEstados($dispositivoId)
    {
        return $this->historialEstadoModel
            ->select('
                historial_dispositivo.*,
                CONCAT(u.nombres, " ", u.apellidos) as usuario
            ')
            ->join('usuarios as u', 'u.id = historial_dispositivo.usuario_id')
            ->where('historial_dispositivo.dispositivo_id', $dispositivoId)
            ->orderBy('historial_dispositivo.created_at', 'DESC')
            ->findAll();
    }

    private function obtenerGarantias($dispositivoId)
    {
        return $this->garantiaModel
            ->select('
                garantias.*,
                tg.nombre as tipo_garantia,
                dp.problema_comun_id,
                pc.nombre as problema_cubierto
            ')
            ->join('tipos_garantia as tg', 'tg.id = garantias.tipo_garantia_id')
            ->join('dispositivo_problemas as dp', 'dp.id = garantias.problema_reparado_id')
            ->join('problemas_comunes as pc', 'pc.id = dp.problema_comun_id', 'left')
            ->where('garantias.dispositivo_id', $dispositivoId)
            ->orderBy('garantias.fecha_inicio', 'DESC')
            ->findAll();
    }
}
