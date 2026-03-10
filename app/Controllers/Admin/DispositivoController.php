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


    public function detalleDispositivo(int $dispositivoId)
    {
        $dispositivosModel = model('DispositivosOrdenModel');

        $dispositivo = $dispositivosModel->getDetalleCompleto($dispositivoId);

        if (!$dispositivo) {
            return redirect()->to(base_url('ordenes'))
                ->with('error', 'Dispositivo no encontrado.');
        }

        $data = [
            'titulo' => 'Detalle del Dispositivo — ' . $dispositivo['codigo_orden'],
            'dispositivo' => $dispositivo,
        ];

        return view('admin/dispositivos/detalles', $data);
    }


    public function iniciarReparacion()
    {
        $dispositivoId = (int) $this->request->getPost('dispositivo_id');
        $estado = $this->request->getPost('estado') ?? 'en_proceso';

        $estadosPermitidos = ['en_proceso', 'listo'];

        if (!$dispositivoId || !in_array($estado, $estadosPermitidos)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos inválidos.',
            ])->setStatusCode(422);
        }

        $dispositivosModel = model('DispositivosOrdenModel');
        $historialModel = new \App\Models\HistorialEstados();
        $db = \Config\Database::connect();

        $dispositivo = $dispositivosModel->find($dispositivoId);

        if (!$dispositivo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Dispositivo no encontrado.',
            ])->setStatusCode(404);
        }

        if ($dispositivo['estado'] !== 'pendiente') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solo se puede iniciar un dispositivo en estado pendiente.',
            ])->setStatusCode(422);
        }

        // Mensaje por defecto visible para el cliente
        $mensajeDefecto = 'Su dispositivo está siendo atendido por nuestro técnico.';

        $db->transStart();

        $dispositivosModel->update($dispositivoId, [
            'estado' => $estado,
        ]);

        $historialModel->insert([
            'dispositivo_orden_id' => $dispositivoId,
            'estado_anterior' => $dispositivo['estado'],
            'estado_nuevo' => $estado,
            'usuario_id' => session('id_usuario'),
            'observacion' => $mensajeDefecto,
        ]);

        $db->transComplete();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Estado actualizado correctamente.',
            'estado' => $estado,
        ]);
    }

    public function finalizarReparacion()
    {
        log_message('debug', print_r($this->request->getPost(), true));
        $dispositivoId = (int) $this->request->getPost('dispositivo_id');
        $comentario = trim($this->request->getPost('comentario') ?? '');
        $problemasPost = $this->request->getPost('problemas');

        if (!$dispositivoId || empty($problemasPost) || !is_array($problemasPost)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos incompletos. Se requiere dispositivo y problemas.',
            ])->setStatusCode(422);
        }

        if (empty($comentario)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El comentario para el cliente es obligatorio.',
            ])->setStatusCode(422);
        }

        $dispositivosModel = model('DispositivosOrdenModel');
        $historialModel = new \App\Models\HistorialEstados();
        $db = \Config\Database::connect();

        $dispositivo = $dispositivosModel->find($dispositivoId);

        if (!$dispositivo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Dispositivo no encontrado.',
            ])->setStatusCode(404);
        }

        if (!in_array($dispositivo['estado'], ['pendiente', 'en_proceso'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El dispositivo ya fue finalizado o cancelado.',
            ])->setStatusCode(422);
        }

        $estadosValidos = ['resuelto', 'no_reparable'];
        $ahora = date('Y-m-d H:i:s');

        $db->transStart();

        $totalManoObra = 0.00;
        $totalRepuesto = 0.00;

        // ── Actualizar cada problema ──────────────────────────────────
        foreach ($problemasPost as $prob) {
            $probId = (int) ($prob['id'] ?? 0);
            $estado = $prob['estado'] ?? 'resuelto';
            $mobraObra = (float) ($prob['precio_mano_obra'] ?? 0);
            $repuesto = (float) ($prob['precio_repuesto'] ?? 0);
            $observacion = trim(($prob['observacion'] ?? ''));

            if (!$probId || !in_array($estado, $estadosValidos)) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Problema ID {$probId  } con datos inválidos.",
                ])->setStatusCode(422);
            }

            // Validar que el problema pertenece al dispositivo
            $existe = $db->table('dispositivo_problemas')
                ->where('id', $probId)
                ->where('dispositivo_orden_id', $dispositivoId)
                ->countAllResults();

            if (!$existe) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "Problema ID {$probId} no pertenece a este dispositivo.",
                ])->setStatusCode(422);
            }

            $db->table('dispositivo_problemas')
                ->where('id', $probId)
                ->update([
                    // 'estado' => $estado,
                    'precio_mano_obra' => $mobraObra,
                    'precio_repuesto' => $repuesto,
                    'observacion' => $observacion ?: null,
                    // 'fecha_fin' => $ahora,
                ]);

            $totalManoObra += $mobraObra;
            $totalRepuesto += $repuesto;
        }

        // ── Calcular precio total ─────────────────────────────────────
        $precioTotal = $totalManoObra + $totalRepuesto + (float) $dispositivo['costo_prioridad'];

        // ── Calcular comisión del técnico (solo sobre mano de obra) ───
        $comision = 0.00;
        if ($dispositivo['tecnico_id']) {
            $tecnicoConfig = $db->table('tecnicos_config')
                ->where('usuario_id', $dispositivo['tecnico_id'])
                ->get()->getRowArray();

            if ($tecnicoConfig) {
                $comision = $tecnicoConfig['tipo_comision'] === 'porcentaje'
                    ? $totalManoObra * ((float) $tecnicoConfig['valor_comision'] / 100)
                    : (float) $tecnicoConfig['valor_comision'];

                $comision = round($comision, 2);
            }
        }

        // ── Actualizar dispositivo → listo ────────────────────────────
        $dispositivosModel->update($dispositivoId, [
            'estado' => 'listo',
            'precio_total' => $precioTotal,
            'comision_tecnico' => $comision,
            'fecha_real_entrega' => $ahora,
        ]);

        // ── Historial con el comentario del técnico ───────────────────
        $historialModel->insert([
            'dispositivo_orden_id' => $dispositivoId,
            'estado_anterior' => $dispositivo['estado'],
            'estado_nuevo' => 'listo',
            'usuario_id' => session('id_usuario'),
            'observacion' => $comentario,
        ]);

        $db->transComplete();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Reparación finalizada. Dispositivo listo para entrega.',
            'estado' => 'listo',
            'precio_total' => $precioTotal,
            'comision_tecnico' => $comision,
            'fecha_real' => $ahora,
        ]);
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
