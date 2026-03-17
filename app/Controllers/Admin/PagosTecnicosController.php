<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PagosTecnicosModel;
use App\Models\UsuarioModel;

class PagosTecnicosController extends BaseController
{
    protected PagosTecnicosModel $pagosModel;

    public function __construct()
    {
        $this->pagosModel = new PagosTecnicosModel();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET admin/pagos-tecnicos
    // Vista principal: listado con filtros por técnico y estado
    // ──────────────────────────────────────────────────────────────────────────
    public function index()
    {
        $estadoPago = $this->request->getGet('estado') ?: null;
        $tecnicoId = (int) ($this->request->getGet('tecnico_id') ?: 0) ?: null;

        $pagos = $this->pagosModel->getListadoAdmin($estadoPago, $tecnicoId);
        $resumen = $this->pagosModel->getResumenPorTecnico();

        // Lista de técnicos activos para el filtro
        $usuarioModel = new UsuarioModel();
        $tecnicos = $usuarioModel->where('rol', 'tecnico')->where('activo', 1)->findAll();

        // Totales del listado actual
        $totalPendiente = array_sum(array_column(
            array_filter($pagos, fn($p) => $p['estado_pago'] === 'pendiente'),
            'monto_comision'
        ));
        $totalValidado = array_sum(array_column(
            array_filter($pagos, fn($p) => $p['estado_pago'] === 'validado'),
            'monto_comision'
        ));
        $totalPagado = array_sum(array_column(
            array_filter($pagos, fn($p) => $p['estado_pago'] === 'pagado'),
            'monto_comision'
        ));

        return view('admin/pagos-tecnicos/index', [
            'titulo' => 'Pagos a Técnicos',
            'pagos' => $pagos,
            'resumen' => $resumen,
            'tecnicos' => $tecnicos,
            'filtro_estado' => $estadoPago,
            'filtro_tecnico' => $tecnicoId,
            'total_pendiente' => $totalPendiente,
            'total_validado' => $totalValidado,
            'total_pagado' => $totalPagado,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST admin/pagos-tecnicos/validar
    // Cambia estado_pago a 'validado' (admin revisó y da el visto bueno)
    // ──────────────────────────────────────────────────────────────────────────
    public function validar()
    {
        $pagoId = (int) $this->request->getPost('pago_id');
        $observacion = trim($this->request->getPost('observacion') ?? '');
        $adminId = session('id_usuario');

        if (!$pagoId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de pago no válido.',
            ])->setStatusCode(422);
        }

        $pago = $this->pagosModel->find($pagoId);

        if (!$pago) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Pago no encontrado.',
            ])->setStatusCode(404);
        }

        if ($pago['estado_pago'] !== 'pendiente') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solo se pueden validar pagos en estado pendiente.',
            ])->setStatusCode(422);
        }

        $this->pagosModel->update($pagoId, [
            'estado_pago' => 'validado',
            'fecha_validacion' => date('Y-m-d H:i:s'),
            'validado_por' => $adminId,
            'observacion' => $observacion ?: $pago['observacion'],
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Pago validado correctamente.',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST admin/pagos-tecnicos/marcar-pagado
    // Cambia estado_pago a 'pagado' (confirmación del pago efectivo al técnico)
    // ──────────────────────────────────────────────────────────────────────────
    public function marcarPagado()
    {
        $pagoId = (int) $this->request->getPost('pago_id');
        $observacion = trim($this->request->getPost('observacion') ?? '');
        $adminId = session('id_usuario');

        if (!$pagoId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de pago no válido.',
            ])->setStatusCode(422);
        }

        $pago = $this->pagosModel->find($pagoId);

        if (!$pago) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Pago no encontrado.',
            ])->setStatusCode(404);
        }

        if ($pago['estado_pago'] === 'pagado') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Este pago ya fue marcado como pagado.',
            ])->setStatusCode(422);
        }

        $ahora = date('Y-m-d H:i:s');

        $this->pagosModel->update($pagoId, [
            'estado_pago' => 'pagado',
            'fecha_pago' => $ahora,
            // Si aún no fue validado previamente, se registra la validación aquí también
            'fecha_validacion' => $pago['fecha_validacion'] ?? $ahora,
            'validado_por' => $adminId,
            'observacion' => $observacion ?: $pago['observacion'],
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Pago marcado como pagado correctamente.',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST admin/pagos-tecnicos/validar-lote
    // Valida múltiples pagos pendientes de un técnico en un solo clic
    // ──────────────────────────────────────────────────────────────────────────
    public function validarLote()
    {
        $ids = $this->request->getPost('ids');
        $observacion = trim($this->request->getPost('observacion') ?? '');
        $adminId = session('id_usuario');
        $ahora = date('Y-m-d H:i:s');

        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Debes seleccionar al menos un pago.',
            ])->setStatusCode(422);
        }

        $db = \Config\Database::connect();
        $db->table('pagos_tecnicos')
            ->whereIn('id', array_map('intval', $ids))
            ->where('estado_pago', 'pendiente')
            ->update([
                'estado_pago' => 'validado',
                'fecha_validacion' => $ahora,
                'validado_por' => $adminId,
                'observacion' => $observacion ?: null,
                'updated_at' => $ahora,
            ]);

        $afectados = $db->affectedRows();

        return $this->response->setJSON([
            'success' => true,
            'message' => "Se validaron {$afectados} pago(s) correctamente.",
            'afectados' => $afectados,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET admin/pagos-tecnicos/tecnico/{id}
    // Historial de pagos de un técnico específico (vista detalle)
    // ──────────────────────────────────────────────────────────────────────────
    public function historialTecnico(int $tecnicoId)
    {
        $usuarioModel = new UsuarioModel();
        $tecnico = $usuarioModel->find($tecnicoId);

        if (!$tecnico || $tecnico['rol'] !== 'tecnico') {
            return redirect()->to(base_url('admin/pagos-tecnicos'))
                ->with('error', 'Técnico no encontrado.');
        }

        $pagos = $this->pagosModel->getHistorialTecnico($tecnicoId);
        $resumen = $this->pagosModel->getResumenPorTecnico();

        // Buscar el resumen específico de este técnico
        $resumenTecnico = current(array_filter($resumen, fn($r) => $r['tecnico_id'] == $tecnicoId)) ?: [
            'total_reparaciones' => 0,
            'total_comision' => 0,
            'pendiente' => 0,
            'validado' => 0,
            'pagado' => 0,
        ];

        return view('admin/pagos-tecnicos/historial', [
            'titulo' => 'Historial de Pagos — ' . $tecnico['nombre'],
            'tecnico' => $tecnico,
            'pagos' => $pagos,
            'resumen_tecnico' => $resumenTecnico,
        ]);
    }
}
