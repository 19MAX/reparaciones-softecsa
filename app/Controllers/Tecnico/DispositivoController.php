<?php

namespace App\Controllers\Tecnico;

use App\Controllers\BaseController;
use App\Models\DispositivosOrdenModel;
use App\Models\HistorialEstados;

class DispositivoController extends BaseController
{
    public function asignados()
    {
        $tecnicoId = session('id_usuario');
        $db = \Config\Database::connect();

        // 1. Verificar técnico
        $tecnico = $db->table('usuarios')
            ->select('id, nombre, rol')
            ->where('id', $tecnicoId)
            ->where('rol', 'tecnico')
            ->where('activo', 1)
            ->get()->getRowArray();

        if (!$tecnico) {
            return redirect()->to(base_url('tecnico/dashboard'))
                ->with('error', 'Técnico no válido.');
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

        // 5. Contadores globales
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

        $mesesDisponibles = $this->_getMesesDisponibles($db, $tecnicoId);

        return view('tecnico/dispositivos/asignados', [
            'titulo' => 'Mis Dispositivos Asignados',
            'tecnico' => $tecnico,
            'dispositivos' => $dispositivos,
            'contadores' => $contadores,
            'total_comisiones' => $totalComisiones,
            'meses_disponibles' => $mesesDisponibles,
            'mes_activo' => 'todos',
        ]);
    }

    public function misReparaciones()
    {
        $usuarioId = session('id_usuario');
        $dispositivosModel = new DispositivosOrdenModel();
        $reparaciones = $dispositivosModel->getReparacionesUsuario($usuarioId);

        $data = [
            'titulo' => 'Mis Reparaciones',
            'reparaciones' => $reparaciones,
        ];

        return view('tecnico/dispositivos/mis_reparaciones', $data);
    }

    public function pool()
    {
        $db = \Config\Database::connect();
        
        // Dispositivos sin asignar (para que el técnico los pueda tomar)
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

        $data = [
            'titulo' => 'Pool de Dispositivos',
            'sinAsignar' => $sinAsignar,
        ];

        return view('tecnico/dispositivos/pool', $data);
    }

    public function detalle(int $dispositivoId)
    {
        $dispositivosModel = model('DispositivosOrdenModel');
        $dispositivo = $dispositivosModel->getDetalleCompleto($dispositivoId);

        if (!$dispositivo) {
            return redirect()->to(base_url('tecnico/dispositivos/pool'))
                ->with('error', 'Dispositivo no encontrado.');
        }

        $data = [
            'titulo' => 'Detalle del Dispositivo — ' . $dispositivo['codigo_orden'],
            'dispositivo' => $dispositivo,
        ];

        return view('tecnico/dispositivos/detalle', $data);
    }

    public function iniciarReparacion()
    {
        $dispositivoId = (int) $this->request->getPost('dispositivo_id');
        $estado = $this->request->getPost('estado') ?? 'en_proceso';
        $currentUserId = session('id_usuario');

        $dispositivosModel = model('DispositivosOrdenModel');
        $dispositivo = $dispositivosModel->find($dispositivoId);

        if (!$dispositivo) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dispositivo no encontrado.'])->setStatusCode(404);
        }

        // VALIDACIÓN: Si ya tiene técnico y NO es el usuario actual, el técnico no puede intervenir
        if (!empty($dispositivo['tecnico_id']) && $dispositivo['tecnico_id'] != $currentUserId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Este dispositivo ya está asignado a otro técnico. No puedes iniciar esta reparación.'
            ])->setStatusCode(403);
        }

        if ($dispositivo['estado'] !== 'pendiente') {
            return $this->response->setJSON(['success' => false, 'message' => 'Solo se puede iniciar un dispositivo en estado pendiente.'])->setStatusCode(422);
        }

        $db = \Config\Database::connect();
        $historialModel = new \App\Models\HistorialEstados();
        $db->transStart();

        // Si no hay técnico, asignar al actual
        if (empty($dispositivo['tecnico_id'])) {
            $dispositivosModel->update($dispositivoId, ['tecnico_id' => $currentUserId]);
            $historialModel->insert([
                'dispositivo_orden_id' => $dispositivoId,
                'estado_anterior' => $dispositivo['estado'],
                'estado_nuevo' => $dispositivo['estado'],
                'usuario_id' => $currentUserId,
                'observacion' => 'El técnico ha tomado la reparación del dispositivo.',
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

        (new \App\Services\ReparacionEmailService())
            ->iniciarReparacion($dispositivoId, $estado, 'Su dispositivo está siendo atendido por nuestro técnico');
        $db->transComplete();

        return $this->response->setJSON(['success' => true, 'message' => 'Reparación iniciada correctamente.', 'estado' => $estado]);
    }

    public function finalizarReparacion()
    {
        $dispositivoId = (int) $this->request->getPost('dispositivo_id');
        $comentario = trim($this->request->getPost('comentario') ?? '');
        $notaTecnica = trim($this->request->getPost('nota_tecnica') ?? '');
        $problemasPost = $this->request->getPost('problemas');
        $cancelarManual = $this->request->getPost('cancelar') === 'true';
        $currentUserId = session('id_usuario');

        if (!$dispositivoId || (empty($problemasPost) && !$cancelarManual) || (!is_array($problemasPost) && !$cancelarManual)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos.'])->setStatusCode(422);
        }

        if (empty($comentario)) {
            return $this->response->setJSON(['success' => false, 'message' => 'El comentario para el cliente es obligatorio.'])->setStatusCode(422);
        }

        $dispositivosModel = model('DispositivosOrdenModel');
        $dispositivoProblemaModel = model('DispositivoProblemaModel');
        $historialModel = new \App\Models\HistorialEstados();
        $db = \Config\Database::connect();

        $dispositivo = $dispositivosModel->find($dispositivoId);

        if (!$dispositivo) {
            return $this->response->setJSON(['success' => false, 'message' => 'Dispositivo no encontrado.'])->setStatusCode(404);
        }

        if ($dispositivo['tecnico_id'] != $currentUserId) {
            return $this->response->setJSON(['success' => false, 'message' => 'No tienes permisos para finalizar esta reparación.'])->setStatusCode(403);
        }

        if (!in_array($dispositivo['estado'], ['pendiente', 'en_proceso'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'El dispositivo ya fue finalizado o cancelado.'])->setStatusCode(422);
        }

        $estadosValidos = ['resuelto', 'no_reparable'];
        $ahora = date('Y-m-d H:i:s');

        $db->transStart();

        $totalManoObra = 0.00;
        $totalRepuesto = 0.00;
        $todosNoReparables = true;

        if (!$cancelarManual) {
            foreach ($problemasPost as $prob) {
                $probId = (int) ($prob['id'] ?? 0);
                $estadoProb = $prob['estado'] ?? 'resuelto';
                $mobraObra = (float) ($prob['precio_mano_obra'] ?? 0);
                $repuesto = (float) ($prob['precio_repuesto'] ?? 0);
                $observacion = trim(($prob['observacion'] ?? ''));

                if (!$probId || !in_array($estadoProb, $estadosValidos)) {
                    $db->transRollback();
                    return $this->response->setJSON(['success' => false, 'message' => "Problema ID {$probId} con datos inválidos."])->setStatusCode(422);
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

        $nuevoEstado = ($cancelarManual || $todosNoReparables) ? 'cancelado' : 'listo';

        if ($nuevoEstado === 'cancelado') {
            $totalManoObra = 0.00;
            $totalRepuesto = 0.00;
            $precioTotal = 0.00;
            $comision = 0.00;

            $db->table('dispositivo_problemas')
                ->where('dispositivo_orden_id', $dispositivoId)
                ->update(['precio_mano_obra' => 0, 'precio_repuesto' => 0]);
        } else {
            $precioTotal = $totalManoObra + $totalRepuesto + (float) $dispositivo['costo_prioridad'];
            $comision = 0.00;
            $tecnicoConfig = $db->table('tecnicos_config')
                ->where('usuario_id', $currentUserId)
                ->get()->getRowArray();

            if ($tecnicoConfig) {
                $comision = $tecnicoConfig['tipo_comision'] === 'porcentaje'
                    ? $totalManoObra * ((float) $tecnicoConfig['valor_comision'] / 100)
                    : (float) $tecnicoConfig['valor_comision'];
                $comision = round($comision, 2);
            }
        }

        $dispositivosModel->update($dispositivoId, [
            'estado' => $nuevoEstado,
            'precio_total' => $precioTotal,
            'comision_tecnico' => $comision,
            'fecha_real_entrega' => $ahora,
        ]);

        $historialModel->insert([
            'dispositivo_orden_id' => $dispositivoId,
            'estado_anterior' => $dispositivo['estado'],
            'estado_nuevo' => $nuevoEstado,
            'usuario_id' => $currentUserId,
            'observacion' => $notaTecnica ?: 'Cambio de estado a ' . $nuevoEstado,
            'observacion_cliente' => $comentario,
        ]);

        $db->transComplete();

        $ordenModel = new \App\Models\OrdenesModel();
        $ordenModel->recalcularEstado($dispositivo['orden_id']);

        $msg = ($nuevoEstado === 'cancelado') ? 'Reparación cancelada/no reparable.' : 'Reparación finalizada. Dispositivo listo para entrega.';

        (new \App\Services\ReparacionEmailService())
            ->enviarFinalizacion($dispositivoId, $nuevoEstado, $comentario);
        return $this->response->setJSON(['success' => true, 'message' => $msg, 'estado' => $nuevoEstado]);
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
        (new \App\Services\ReparacionEmailService())
            ->enviarEntrega($dispositivoId);
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Dispositivo marcado como entregado.',
            'estado' => 'entregado',
            'fecha_real' => $ahora,
        ]);
    }

    private function _getMesesDisponibles($db, int $tecnicoId): array
    {
        $mesesEs = [
            'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo', 'April' => 'Abril',
            'May' => 'Mayo', 'June' => 'Junio', 'July' => 'Julio', 'August' => 'Agosto',
            'September' => 'Septiembre', 'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre',
        ];

        $rows = $db->query("
            SELECT
                DATE_FORMAT(created_at, '%Y-%m') AS valor,
                DATE_FORMAT(created_at, '%M %Y') AS label
            FROM dispositivos_orden
            WHERE tecnico_id = ?
              AND created_at IS NOT NULL
            GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%M %Y')
            ORDER BY valor DESC
        ", [$tecnicoId])->getResultArray();

        return array_map(function ($m) use ($mesesEs) {
            [$mes, $anio] = explode(' ', $m['label'], 2);
            $m['label'] = ($mesesEs[$mes] ?? $mes) . ' ' . $anio;
            return $m;
        }, $rows);
    }
}
