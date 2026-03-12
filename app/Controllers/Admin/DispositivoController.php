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

    public function misReparaciones()
    {
        $usuarioId = session('id_usuario');
        $reparaciones = $this->dispositivoModel->getReparacionesUsuario($usuarioId);

        $data = [
            'titulo' => 'Mis Reparaciones',
            'reparaciones' => $reparaciones,
        ];

        return view('admin/dispositivos/mis_reparaciones', $data);
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

        // Lista de técnicos y admins activos para el select de asignación
        $usuarioModel = new UsuarioModel();
        $listaTecnicos = $usuarioModel->whereIn('rol', ['tecnico', 'admin'])->where('activo', 1)->findAll();

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

        // Lista de técnicos y admins activos para asignación
        $usuarioModel = new UsuarioModel();
        $listaTecnicos = $usuarioModel->whereIn('rol', ['tecnico', 'admin'])->where('activo', 1)->findAll();

        $data = [
            'titulo' => 'Detalle del Dispositivo — ' . $dispositivo['codigo_orden'],
            'dispositivo' => $dispositivo,
            'listaTecnicos' => $listaTecnicos,
        ];

        log_message('debug', 'Detalle dispositivo: ' . print_r($dispositivo, true));
        return view('admin/dispositivos/detalles', $data);
    }


    public function iniciarReparacion()
    {
        $dispositivoId = (int) $this->request->getPost('dispositivo_id');
        $estado = $this->request->getPost('estado') ?? 'en_proceso';
        $currentUserId = session('id_usuario');
        $currentUserRol = session('role');

        $dispositivosModel = model('DispositivosOrdenModel');
        $dispositivo = $dispositivosModel->find($dispositivoId);

        if (!$dispositivo) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dispositivo no encontrado.'])->setStatusCode(404);
        }

        // VALIDACIÓN: Si ya tiene técnico y NO es el usuario actual, solo el ADMIN puede intervenir
        if (!empty($dispositivo['tecnico_id']) && $dispositivo['tecnico_id'] != $currentUserId && $currentUserRol !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Este dispositivo está asignado a otro técnico. No puedes iniciar esta reparación.'
            ])->setStatusCode(403);
        }

        if ($dispositivo['estado'] !== 'pendiente') {
            return $this->response->setJSON(['success' => false, 'message' => 'Solo se puede iniciar un dispositivo en estado pendiente.'])->setStatusCode(422);
        }

        $db = \Config\Database::connect();
        $historialModel = new \App\Models\HistorialEstados();
        $db->transStart();

        // Si el Admin toma el dispositivo o no hay técnico, asignar al actual
        if (empty($dispositivo['tecnico_id']) || ($dispositivo['tecnico_id'] != $currentUserId && $currentUserRol === 'admin')) {
            $dispositivosModel->update($dispositivoId, ['tecnico_id' => $currentUserId]);
            $historialModel->insert([
                'dispositivo_orden_id' => $dispositivoId,
                'estado_anterior' => $dispositivo['estado'],
                'estado_nuevo' => $dispositivo['estado'],
                'usuario_id' => $currentUserId,
                'observacion' => 'Re-asignación manual/automática al iniciar reparación.',
            ]);
        }

        $dispositivosModel->update($dispositivoId, ['estado' => $estado]);
        $historialModel->insert([
            'dispositivo_orden_id' => $dispositivoId,
            'estado_anterior' => $dispositivo['estado'],
            'estado_nuevo' => $estado,
            'usuario_id' => $currentUserId,
            'observacion' => 'Su dispositivo está siendo atendido por nuestro técnico.',
        ]);

        $db->transComplete();
        model('App\Models\OrdenesModel')->recalcularEstado($dispositivo['orden_id']);

        return $this->response->setJSON(['success' => true, 'message' => 'Reparación iniciada correctamente.', 'estado' => $estado]);
    }


    public function finalizarReparacion()
    {
        log_message('debug', print_r($this->request->getPost(), true));
        $dispositivoId = (int) $this->request->getPost('dispositivo_id');
        $comentario = trim($this->request->getPost('comentario') ?? '');
        $notaTecnica = trim($this->request->getPost('nota_tecnica') ?? '');
        $problemasPost = $this->request->getPost('problemas');
        $cancelarManual = $this->request->getPost('cancelar') === 'true';

        if (!$dispositivoId || (empty($problemasPost) && !$cancelarManual) || (!is_array($problemasPost) && !$cancelarManual)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Datos incompletos.',
            ])->setStatusCode(422);
        }

        if (empty($comentario)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El comentario para el cliente es obligatorio.',
            ])->setStatusCode(422);
        }

        $dispositivosModel = model('DispositivosOrdenModel');
        $dispositivoProblemaModel = model('DispositivoProblemaModel');
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
        $currentUserId = session('id_usuario');
        $currentUserRol = session('role');

        $db->transStart();

        // ── LÓGICA DE TOMA DE CONTROL (Takeover) ──
        // Si el Admin finaliza y no es el técnico asignado, se le re-asigna
        if ($currentUserRol === 'admin' && $dispositivo['tecnico_id'] != $currentUserId) {
            $dispositivosModel->update($dispositivoId, ['tecnico_id' => $currentUserId]);
            $historialModel->insert([
                'dispositivo_orden_id' => $dispositivoId,
                'estado_anterior' => $dispositivo['estado'],
                'estado_nuevo' => $dispositivo['estado'],
                'usuario_id' => $currentUserId,
                'observacion' => 'El Administrador ha tomado el control y finalizado la reparación.',
            ]);
            // Actualizamos la variable local para los cálculos de comisión posteriores
            $dispositivo['tecnico_id'] = $currentUserId;
        }

        $totalManoObra = 0.00;
        $totalRepuesto = 0.00;
        $todosNoReparables = true;

        if (!$cancelarManual) {
            // ── Actualizar cada problema ──────────────────────────────────
            foreach ($problemasPost as $prob) {
                $probId = (int) ($prob['id'] ?? 0);
                $estadoProb = $prob['estado'] ?? 'resuelto';
                $mobraObra = (float) ($prob['precio_mano_obra'] ?? 0);
                $repuesto = (float) ($prob['precio_repuesto'] ?? 0);
                $observacion = trim(($prob['observacion'] ?? ''));

                if (!$probId || !in_array($estadoProb, $estadosValidos)) {
                    $db->transRollback();
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => "Problema ID {$probId} con datos inválidos.",
                    ])->setStatusCode(422);
                }

                if ($estadoProb === 'resuelto') {
                    $todosNoReparables = false;
                }

                $dispositivoProblemaModel->update($probId, [
                    'precio_mano_obra' => $mobraObra,
                    'precio_repuesto' => $repuesto,
                    'observacion' => $observacion ?: null,
                ]);

                $totalManoObra += $mobraObra;
                $totalRepuesto += $repuesto;
            }
        } else {
            $todosNoReparables = true;
        }

        // Determinar nuevo estado
        $nuevoEstado = ($cancelarManual || $todosNoReparables) ? 'cancelado' : 'listo';

        // Si se cancela, forzamos valores a 0
        if ($nuevoEstado === 'cancelado') {
            $totalManoObra = 0.00;
            $totalRepuesto = 0.00;
            $precioTotal = 0.00; // Opcional: podrías cobrar prioridad, pero user pidió 0 errores
            $comision = 0.00;

            // Zerar todos los problemas del dispositivo en la DB
            $db->table('dispositivo_problemas')
                ->where('dispositivo_orden_id', $dispositivoId)
                ->update([
                    'precio_mano_obra' => 0,
                    'precio_repuesto' => 0
                ]);
        } else {
            // ── Calcular precio total ─────────────────────────────────────
            $precioTotal = $totalManoObra + $totalRepuesto + (float) $dispositivo['costo_prioridad'];

            // ── Calcular comisión del técnico (solo sobre mano de obra y SI NO ES ADMIN) ───
            $comision = 0.00;
            if ($dispositivo['tecnico_id']) {
                $tecnico = $db->table('usuarios')
                    ->where('id', $dispositivo['tecnico_id'])
                    ->get()->getRowArray();

                // VALIDACIÓN: Si es admin, no recibe comisión
                if ($tecnico && $tecnico['rol'] !== 'admin') {
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
            }
        }

        // ── Actualizar dispositivo ────────────────────────────
        $dispositivosModel->update($dispositivoId, [
            'estado' => $nuevoEstado,
            'precio_total' => $precioTotal,
            'comision_tecnico' => $comision,
            'fecha_real_entrega' => $ahora,
        ]);

        // ── Historial con notas separadas ───────────────────
        $historialModel->insert([
            'dispositivo_orden_id' => $dispositivoId,
            'estado_anterior' => $dispositivo['estado'],
            'estado_nuevo' => $nuevoEstado,
            'usuario_id' => session('id_usuario'),
            'observacion' => $notaTecnica ?: 'Cambio de estado a ' . $nuevoEstado,
            'observacion_cliente' => $comentario,
        ]);

        $db->transComplete();

        // Recalcular estado de la orden
        $ordenModel = new \App\Models\OrdenesModel();
        $ordenModel->recalcularEstado($dispositivo['orden_id']);

        $msg = ($nuevoEstado === 'cancelado') ? 'Reparación cancelada/no reparable.' : 'Reparación finalizada. Dispositivo listo para entrega.';

        return $this->response->setJSON([
            'success' => true,
            'message' => $msg,
            'estado' => $nuevoEstado,
        ]);
    }

    public function entregarDispositivo()
    {
        $dispositivoId = (int) $this->request->getPost('dispositivo_id');

        if (!$dispositivoId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de dispositivo no proporcionado.',
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

        if ($dispositivo['estado'] !== 'listo') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solo se puede entregar un dispositivo que esté en estado "listo".',
            ])->setStatusCode(422);
        }

        $ahora = date('Y-m-d H:i:s');
        $currentUserId = session('id_usuario');
        $currentUserRol = session('role');

        $db->transStart();

        // ── LÓGICA DE TOMA DE CONTROL (Takeover) ──
        // Si el Admin entrega y no es el técnico asignado, se le re-asigna (aunque ya esté terminado)
        if ($currentUserRol === 'admin' && $dispositivo['tecnico_id'] != $currentUserId) {
            $dispositivosModel->update($dispositivoId, ['tecnico_id' => $currentUserId]);
            $historialModel->insert([
                'dispositivo_orden_id' => $dispositivoId,
                'estado_anterior' => $dispositivo['estado'],
                'estado_nuevo' => $dispositivo['estado'],
                'usuario_id' => $currentUserId,
                'observacion' => 'El Administrador ha tomado el control del dispositivo para su entrega final.',
            ]);
        }

        $dispositivosModel->update($dispositivoId, [
            'estado' => 'entregado',
            'fecha_real_entrega' => $ahora,
        ]);

        $historialModel->insert([
            'dispositivo_orden_id' => $dispositivoId,
            'estado_anterior' => $dispositivo['estado'],
            'estado_nuevo' => 'entregado',
            'usuario_id' => session('id_usuario'),
            'observacion' => 'Dispositivo entregado al cliente.',
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al actualizar el estado del dispositivo.',
            ])->setStatusCode(500);
        }

        // Recalcular estado de la orden
        $ordenModel = new \App\Models\OrdenesModel();
        $ordenModel->recalcularEstado($dispositivo['orden_id']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Dispositivo marcado como entregado.',
            'estado' => 'entregado',
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
        $currentUserRol = session('role');

        if ($currentUserRol !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'No tienes permisos para reasignar técnicos.']);
        }

        if (!$dispositivoId || !$tecnicoId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dispositivo y Técnico son requeridos']);
        }

        $dispositivosModel = model('DispositivosOrdenModel');
        $dispositivo = $dispositivosModel->find($dispositivoId);

        if (!$dispositivo) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dispositivo no encontrado']);
        }

        // VALIDACIÓN: No reasignar si ya terminó
        if (in_array($dispositivo['estado'], ['listo', 'entregado', 'cancelado'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se puede cambiar el técnico de un dispositivo ya finalizado.']);
        }

        $usuarioModel = new UsuarioModel();
        $tecnico = $usuarioModel->find($tecnicoId);

        if (!$tecnico || !in_array($tecnico['rol'], ['tecnico', 'admin'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Usuario no válido para reparación']);
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
    // ──────────────────────────────────────────────────────────
    // MÉTODO 1 · Vista principal del técnico
    // GET dispositivos/ver-tecnico/{id}
    // ──────────────────────────────────────────────────────────
    public function verTecnico(int $tecnicoId)
    {
        $db = \Config\Database::connect();

        // 1. Verificar técnico
        $tecnico = $db->table('usuarios')
            ->select('id, nombre, rol')
            ->where('id', $tecnicoId)
            ->where('rol', 'tecnico')
            ->where('activo', 1)
            ->get()->getRowArray();

        if (!$tecnico) {
            return redirect()->to(base_url('admin/tecnicos'))
                ->with('error', 'Técnico no encontrado.');
        }

        // 2. Config de comisión
        $tecnico['config'] = $db->table('tecnicos_config')
            ->where('usuario_id', $tecnicoId)
            ->get()->getRowArray();

        // 3. Dispositivos (siempre todos — el filtro de mes lo hace JS en cliente)
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
                'do.created_at  AS fecha_ingreso',
                'do.updated_at',
                'td.nombre      AS tipo_dispositivo',
                'm.nombre       AS marca',
                'COALESCE(mo.nombre, do.modelo_texto) AS modelo',
                'pr.nombre      AS prioridad',
                'pr.color_badge AS prioridad_color',
                'o.numero_orden',
                'o.id           AS orden_id',
                'c.nombres      AS cliente_nombre',
                'c.telefono     AS cliente_telefono',
            ])
            ->join('tipos_dispositivo td', 'td.id = do.tipo_dispositivo_id')
            ->join('marcas m', 'm.id  = do.marca_id')
            ->join('modelos mo', 'mo.id = do.modelo_id', 'left')
            ->join('prioridades pr', 'pr.id = do.prioridad_id', 'left')
            ->join('ordenes o', 'o.id  = do.orden_id')
            ->join('clientes c', 'c.id  = o.cliente_id')
            ->where('do.tecnico_id', $tecnicoId)
            ->orderBy("FIELD(do.estado,'en_proceso','pendiente','listo','entregado','cancelado')", '', false)
            ->orderBy('do.fecha_estimada_entrega', 'ASC')
            ->get()->getResultArray();

        // 4. Problemas de cada dispositivo
        foreach ($dispositivos as &$dev) {
            $dev['problemas'] = $db->table('dispositivo_problemas dp')
                ->select('p.nombre AS problema, dp.precio_mano_obra, dp.precio_repuesto')
                ->join('problemas p', 'p.id = dp.problema_id')
                ->where('dp.dispositivo_orden_id', $dev['id'])
                ->get()->getResultArray();
        }
        unset($dev);

        // 5. Contadores globales (sin filtro)
        $contadores = ['pendiente' => 0, 'en_proceso' => 0, 'listo' => 0, 'entregado' => 0, 'cancelado' => 0];
        foreach ($dispositivos as $dev) {
            if (isset($contadores[$dev['estado']]))
                $contadores[$dev['estado']]++;
        }

        // 6. Total comisiones global
        $totalComisiones = round(array_sum(array_column(
            array_filter($dispositivos, fn($d) => in_array($d['estado'], ['listo', 'entregado'])),
            'comision_tecnico'
        )), 2);

        // 7. Meses disponibles para el selector (basado en created_at = ingreso)
        $mesesDisponibles = $this->_getMesesDisponibles($db, $tecnicoId);

        return view('admin/dispositivos/ver_tecnico', [
            'titulo' => 'Dispositivos de ' . $tecnico['nombre'],
            'tecnico' => $tecnico,
            'dispositivos' => $dispositivos,
            'contadores' => $contadores,
            'total_comisiones' => $totalComisiones,
            'meses_disponibles' => $mesesDisponibles,
            'mes_activo' => 'todos',
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // MÉTODO 2 · API JSON — Comisiones por mes del técnico
    // GET dispositivos/ver-tecnico/{id}/comisiones
    //
    // Devuelve: [ { mes:'2026-03', label:'Marzo 2026',
    //               total_comision: 120.00, cantidad: 5 }, … ]
    // ──────────────────────────────────────────────────────────
    public function comisionesPorMes(int $tecnicoId)
    {
        $db = \Config\Database::connect();

        // Verificar técnico
        $existe = $db->table('usuarios')
            ->where('id', $tecnicoId)->where('rol', 'tecnico')->where('activo', 1)
            ->countAllResults();

        if (!$existe) {
            return $this->response->setJSON(['error' => 'Técnico no encontrado'])->setStatusCode(404);
        }

        $rows = $db->query("
            SELECT
                DATE_FORMAT(updated_at, '%Y-%m')   AS mes,
                DATE_FORMAT(updated_at, '%M %Y')   AS label_en,
                ROUND(SUM(comision_tecnico), 2)    AS total_comision,
                COUNT(*)                           AS cantidad
            FROM dispositivos_orden
            WHERE tecnico_id = ?
              AND estado IN ('listo', 'entregado')
              AND updated_at IS NOT NULL
            GROUP BY DATE_FORMAT(updated_at, '%Y-%m'),
                     DATE_FORMAT(updated_at, '%M %Y')
            ORDER BY mes DESC
        ", [$tecnicoId])->getResultArray();

        $mesesEs = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre',
        ];

        $resultado = array_map(function ($r) use ($mesesEs) {
            [$mesNombre, $anio] = explode(' ', $r['label_en'], 2);
            return [
                'mes' => $r['mes'],
                'label' => ($mesesEs[$mesNombre] ?? $mesNombre) . ' ' . $anio,
                'total_comision' => (float) $r['total_comision'],
                'cantidad' => (int) $r['cantidad'],
            ];
        }, $rows);

        return $this->response->setJSON($resultado);
    }

    // ──────────────────────────────────────────────────────────
    // MÉTODO 3 · API JSON — Dispositivos ingresados por mes
    // GET dispositivos/ver-tecnico/{id}/ingresos
    //
    // Devuelve: [ { mes:'2026-03', label:'Marzo 2026',
    //               cantidad: 8,
    //               por_estado: { en_proceso:2, listo:3, … } }, … ]
    // ──────────────────────────────────────────────────────────
    public function dispositivosPorMes(int $tecnicoId)
    {
        $db = \Config\Database::connect();

        $existe = $db->table('usuarios')
            ->where('id', $tecnicoId)->where('rol', 'tecnico')->where('activo', 1)
            ->countAllResults();

        if (!$existe) {
            return $this->response->setJSON(['error' => 'Técnico no encontrado'])->setStatusCode(404);
        }

        // Totales por mes
        $totales = $db->query("
            SELECT
                DATE_FORMAT(created_at, '%Y-%m')  AS mes,
                DATE_FORMAT(created_at, '%M %Y')  AS label_en,
                COUNT(*)                          AS cantidad
            FROM dispositivos_orden
            WHERE tecnico_id = ?
              AND created_at IS NOT NULL
            GROUP BY DATE_FORMAT(created_at, '%Y-%m'),
                     DATE_FORMAT(created_at, '%M %Y')
            ORDER BY mes DESC
        ", [$tecnicoId])->getResultArray();

        // Desglose por estado y mes
        $porEstado = $db->query("
            SELECT
                DATE_FORMAT(created_at, '%Y-%m') AS mes,
                estado,
                COUNT(*) AS cantidad
            FROM dispositivos_orden
            WHERE tecnico_id = ?
              AND created_at IS NOT NULL
            GROUP BY DATE_FORMAT(created_at, '%Y-%m'), estado
        ", [$tecnicoId])->getResultArray();

        // Indexar desglose por mes
        $desglose = [];
        foreach ($porEstado as $row) {
            $desglose[$row['mes']][$row['estado']] = (int) $row['cantidad'];
        }

        $mesesEs = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre',
        ];

        $resultado = array_map(function ($r) use ($mesesEs, $desglose) {
            [$mesNombre, $anio] = explode(' ', $r['label_en'], 2);
            return [
                'mes' => $r['mes'],
                'label' => ($mesesEs[$mesNombre] ?? $mesNombre) . ' ' . $anio,
                'cantidad' => (int) $r['cantidad'],
                'por_estado' => $desglose[$r['mes']] ?? [],
            ];
        }, $totales);

        return $this->response->setJSON($resultado);
    }

    // ──────────────────────────────────────────────────────────
    // Helper privado — Meses distintos con actividad
    // ──────────────────────────────────────────────────────────
    private function _getMesesDisponibles($db, int $tecnicoId): array
    {
        $mesesEs = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre',
        ];

        $rows = $db->query("
            SELECT
                DATE_FORMAT(created_at, '%Y-%m') AS valor,
                DATE_FORMAT(created_at, '%M %Y') AS label
            FROM dispositivos_orden
            WHERE tecnico_id = ?
              AND created_at IS NOT NULL
            GROUP BY DATE_FORMAT(created_at, '%Y-%m'),
                     DATE_FORMAT(created_at, '%M %Y')
            ORDER BY valor DESC
        ", [$tecnicoId])->getResultArray();

        return array_map(function ($m) use ($mesesEs) {
            [$mes, $anio] = explode(' ', $m['label'], 2);
            $m['label'] = ($mesesEs[$mes] ?? $mes) . ' ' . $anio;
            return $m;
        }, $rows);
    }
}
