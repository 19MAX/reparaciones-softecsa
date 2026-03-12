<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DispositivoModel;
use App\Models\DispositivosOrdenModel;
use App\Models\UsuarioModel;
use CodeIgniter\HTTP\ResponseInterface;

class DispositivoController extends BaseController
{
    protected $dispositivoModel;

    public function __construct()
    {
        $this->dispositivoModel = new DispositivosOrdenModel();
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
            u.id AS tecnico_id,
            CONCAT(u.nombre, ' ', u.apellido) AS tecnico_nombre,
            tc.tipo_comision,
            tc.valor_comision,
            COUNT(do2.id) AS total_dispositivos,
            SUM(CASE WHEN do2.estado NOT IN ('listo','entregado','cancelado') THEN 1 ELSE 0 END) AS activos,
            SUM(CASE WHEN do2.estado = 'listo' THEN 1 ELSE 0 END) AS listos_para_retiro
        FROM usuarios u
        JOIN tecnicos_config tc ON tc.usuario_id = u.id
        LEFT JOIN dispositivos_orden do2 
            ON do2.tecnico_id = u.id 
            AND do2.estado NOT IN ('entregado', 'cancelado')
        WHERE u.rol = 'tecnico'
        AND u.activo = 1
        GROUP BY u.id, u.nombre, u.apellido, tc.tipo_comision, tc.valor_comision
        ORDER BY u.nombre ASC
    ")->getResultArray();

        // Dispositivos sin técnico asignado
        $sinAsignar = $db->query("
            SELECT 
                do2.id,
                do2.serie_imei,
                do2.estado,
                do2.created_at,
                td.nombre AS tipo_dispositivo,
                m.nombre AS marca,
                COALESCE(mo.nombre, do2.modelo_texto) AS modelo,
                o.numero_orden AS codigo_orden,
                o.id AS orden_id,
                c.nombres AS cliente_nombre,
                c.apellidos AS cliente_apellido
            FROM dispositivos_orden do2
            JOIN ordenes o ON o.id = do2.orden_id
            JOIN clientes c ON c.id = o.cliente_id
            JOIN tipos_dispositivo td ON td.id = do2.tipo_dispositivo_id
            JOIN marcas m ON m.id = do2.marca_id
            LEFT JOIN modelos mo ON mo.id = do2.modelo_id
            WHERE do2.tecnico_id IS NULL
              AND do2.estado NOT IN ('entregado','cancelado')
            ORDER BY do2.created_at ASC
        ")->getResultArray();

        // Lista de técnicos activos para el select de asignación
        $usuarioModel = new UsuarioModel();
        $listaTecnicos = $usuarioModel->where('rol', 'tecnico')->findAll();

        $data = [
            'titulo' => 'Dispositivos por Técnico',
            'tecnicos' => $tecnicos,
            'sinAsignar' => $sinAsignar,
            'listaTecnicos' => $listaTecnicos,
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

        // Lista de técnicos activos para asignación
        $usuarioModel = new UsuarioModel();
        $listaTecnicos = $usuarioModel->where('rol', 'tecnico')->findAll();

        $data = [
            'titulo' => 'Detalle del Dispositivo — ' . $dispositivo['codigo_orden'],
            'dispositivo' => $dispositivo,
            'listaTecnicos' => $listaTecnicos,
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
                    'message' => "Problema ID {$probId} con datos inválidos.",
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
     * Asignar técnico a un dispositivo (AJAX)
     */
    public function asignarTecnico()
    {
        $dispositivoId = (int) $this->request->getPost('dispositivo_id');
        $tecnicoId = (int) $this->request->getPost('tecnico_id');

        if (!$dispositivoId || !$tecnicoId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dispositivo y Técnico son requeridos']);
        }

        $dispositivosModel = model('DispositivosOrdenModel');
        $dispositivo = $dispositivosModel->find($dispositivoId);

        if (!$dispositivo) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dispositivo no encontrado']);
        }

        $usuarioModel = new UsuarioModel();
        $tecnico = $usuarioModel->find($tecnicoId);

        if (!$tecnico || $tecnico['rol'] !== 'tecnico') {
            return $this->response->setJSON(['success' => false, 'message' => 'Técnico no válido']);
        }

        $dispositivosModel->update($dispositivoId, ['tecnico_id' => $tecnicoId]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Técnico asignado correctamente',
            'tecnico_nombre' => $tecnico['nombre'] ?? $tecnico['nombres'] . ' ' . ($tecnico['apellidos'] ?? ''),
        ]);
    }

    /**
     * Ver dispositivos de un técnico específico
     */
    public function verTecnico($tecnicoId)
    {
        $tecnicoId = (int) $tecnicoId;
        $db = \Config\Database::connect();

        // ── 1. Verificar que el técnico existe ────────────────────────
        $tecnico = $db->table('usuarios')
            ->select('id, nombre, rol')
            ->where('id', $tecnicoId)
            ->where('rol', 'tecnico')
            ->where('activo', 1)
            ->get()->getRowArray();

        if (!$tecnico) {
            return redirect()->to(base_url('tecnicos'))
                ->with('error', 'Técnico no encontrado.');
        }

        // ── 2. Configuración de comisión del técnico ──────────────────
        $tecnico['config'] = $db->table('tecnicos_config')
            ->where('usuario_id', $tecnicoId)
            ->get()->getRowArray();

        // ── 3. Dispositivos asignados al técnico ──────────────────────
        $dispositivos = $db->table('dispositivos_orden do')
            ->select([
                'do.id',
                'do.estado',
                'do.precio_total',
                'do.costo_prioridad',
                'do.comision_tecnico',
                'do.tiempo_total_horas',
                'do.fecha_estimada_entrega',
                'do.fecha_real_entrega',
                'do.created_at              AS fecha_ingreso',
                // Dispositivo
                'td.nombre                  AS tipo_dispositivo',
                'm.nombre                   AS marca',
                'COALESCE(mo.nombre, do.modelo_texto) AS modelo',
                // Prioridad
                'pr.nombre                  AS prioridad',
                'pr.color_badge             AS prioridad_color',
                // Orden y cliente
                'o.numero_orden',
                'o.id                       AS orden_id',
                'c.nombres                  AS cliente_nombre',
                'c.telefono                 AS cliente_telefono',
            ])
            ->join('tipos_dispositivo td', 'td.id = do.tipo_dispositivo_id')
            ->join('marcas m', 'm.id  = do.marca_id')
            ->join('modelos mo', 'mo.id = do.modelo_id', 'left')
            ->join('prioridades pr', 'pr.id = do.prioridad_id', 'left')
            ->join('ordenes o', 'o.id  = do.orden_id')
            ->join('clientes c', 'c.id  = o.cliente_id')
            ->where('do.tecnico_id', $tecnicoId)
            ->orderBy("FIELD(do.estado,
            'en_proceso',
            'pendiente',
            'listo',
            'entregado',
            'cancelado')", '', false)   // Activos primero
            ->orderBy('do.fecha_estimada_entrega', 'ASC')
            ->get()->getResultArray();

        // ── 4. Enriquecer con los problemas de cada dispositivo ───────
        foreach ($dispositivos as &$dev) {
            $dev['problemas'] = $db->table('dispositivo_problemas dp')
                ->select('p.nombre AS problema, dp.precio_mano_obra, dp.precio_repuesto')
                ->join('problemas p', 'p.id = dp.problema_id')
                ->where('dp.dispositivo_orden_id', $dev['id'])
                ->get()->getResultArray();
        }
        unset($dev);

        // ── 5. Contadores por estado para el resumen ──────────────────
        $contadores = [
            'pendiente' => 0,
            'en_proceso' => 0,
            'listo' => 0,
            'entregado' => 0,
            'cancelado' => 0,
        ];
        foreach ($dispositivos as $dev) {
            if (isset($contadores[$dev['estado']])) {
                $contadores[$dev['estado']]++;
            }
        }

        // ── 6. Total comisiones generadas (solo listo/entregado) ──────
        $totalComisiones = array_sum(array_column(
            array_filter($dispositivos, fn($d) => in_array($d['estado'], ['listo', 'entregado'])),
            'comision_tecnico'
        ));

        $data = [
            'titulo' => 'Dispositivos de ' . $tecnico['nombre'],
            'tecnico' => $tecnico,
            'dispositivos' => $dispositivos,
            'contadores' => $contadores,
            'total_comisiones' => round($totalComisiones, 2),
        ];

        return view('admin/dispositivos/ver_tecnico', $data);
    }
}
