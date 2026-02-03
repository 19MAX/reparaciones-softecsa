<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ComisionTecnicoModel;
use App\Models\DispositivoModel;
use App\Models\DispositivoProblemasModel;
use App\Models\OrdenFinalizadaModel;
use App\Models\GarantiaModel;
use App\Models\HistorialGarantiasModel;
use App\Models\HistorialDispositivoModel;
use App\Models\DispositivoProblemaModel;
use App\Models\TipoGarantiaModel;
use App\Models\PoliticaRevisionModel;
use App\Models\UsuarioModel;
use App\Models\OrdenTrabajoModel;

class FinalizacionController extends BaseController
{
    protected $dispositivoModel;
    protected $ordenFinalizadaModel;
    protected $comisionModel;
    protected $garantiaModel;
    protected $historialGarantiasModel;
    protected $historialDispositivoModel;
    protected $problemaModel;
    protected $tipoGarantiaModel;
    protected $politicaRevisionModel;
    protected $usuarioModel;
    protected $ordenTrabajoModel;

    public function __construct()
    {
        $this->dispositivoModel = new DispositivoModel();
        $this->ordenFinalizadaModel = new OrdenFinalizadaModel();
        $this->comisionModel = new ComisionTecnicoModel();
        $this->garantiaModel = new GarantiaModel();
        $this->historialGarantiasModel = new HistorialGarantiasModel();
        $this->historialDispositivoModel = new HistorialDispositivoModel();
        $this->problemaModel = new DispositivoProblemasModel();
        $this->tipoGarantiaModel = new TipoGarantiaModel();
        $this->politicaRevisionModel = new PoliticaRevisionModel();
        $this->usuarioModel = new UsuarioModel();
        $this->ordenTrabajoModel = new OrdenTrabajoModel();
    }

    /**
     * Vista de finalización
     */
    public function finalizar($dispositivoId)
    {
        if (!$this->validarSesion()) {
            return redirect()->to(base_url('login'));
        }

        try {
            // Obtener dispositivo completo
            $dispositivo = $this->dispositivoModel
                ->select('
                    dispositivos.*,
                    td.nombre as tipo_dispositivo,
                    m.nombre as marca,
                    mod.nombre as modelo,
                    ot.codigo_orden,
                    ot.id as orden_id,
                    CONCAT(c.nombres, " ", c.apellidos) as cliente_nombre,
                    c.cedula as cliente_cedula,
                    c.telefono as cliente_telefono,
                    CONCAT(u.nombres, " ", u.apellidos) as tecnico_asignado,
                    u.id as tecnico_id
                ')
                ->join('tipos_dispositivo as td', 'td.id = dispositivos.tipo_dispositivo_id', 'left')
                ->join('marcas as m', 'm.id = dispositivos.marca_id', 'left')
                ->join('modelos as mod', 'mod.id = dispositivos.modelo_id', 'left')
                ->join('ordenes_trabajo as ot', 'ot.id = dispositivos.orden_id')
                ->join('clientes as c', 'c.id = ot.cliente_id')
                ->join('usuarios as u', 'u.id = dispositivos.tecnico_id', 'left')
                ->where('dispositivos.id', $dispositivoId)
                ->first();

            if (!$dispositivo) {
                session()->setFlashdata('error', 'Dispositivo no encontrado');
                return redirect()->to(base_url('admin/ordenes'));
            }

            // Obtener problemas
            $problemas = $this->problemaModel
                ->select('dispositivo_problemas.*, pc.nombre as problema')
                ->join('problemas_comunes as pc', 'pc.id = dispositivo_problemas.problema_comun_id', 'left')
                ->where('dispositivo_id', $dispositivoId)
                ->findAll();

            $problemasResueltos = array_filter($problemas, fn($p) => $p['fue_reparado'] === true);
            $problemasNoResueltos = array_filter($problemas, fn($p) => $p['fue_reparado'] === false);

            // Calcular costos
            $costosCalculados = $this->calcularCostosDispositivo($dispositivoId);

            // Obtener información del técnico
            $tecnicoInfo = $this->usuarioModel->find($dispositivo['tecnico_id']);

            // Obtener política de revisión activa
            $politicaRevision = $this->determinarCobroRevision($dispositivo, $problemasResueltos);

            // Tipos de garantía disponibles
            $tiposGarantia = $this->tipoGarantiaModel->where('activo', 1)->findAll();

            $data = [
                'titulo' => 'Finalizar y Entregar Dispositivo',
                'dispositivo' => $dispositivo,
                'problemas_resueltos' => array_values($problemasResueltos),
                'problemas_no_resueltos' => array_values($problemasNoResueltos),
                'costos_calculados' => $costosCalculados,
                'tecnico_info' => $tecnicoInfo,
                'politica_revision' => $politicaRevision,
                'debe_cobrar_revision' => $politicaRevision['debe_cobrar'],
                'puede_generar_garantia' => !empty($problemasResueltos),
                'tipos_garantia' => $tiposGarantia,
            ];

            return view('admin/dispositivos/finalizar', $data);

        } catch (\Exception $e) {
            log_message('error', '[FinalizacionController::finalizar] ' . $e->getMessage());
            session()->setFlashdata('error', 'Error al cargar la vista de finalización');
            return redirect()->to(base_url('admin/ordenes'));
        }
    }

    /**
     * Procesar finalización y entrega
     */
    public function procesarFinalizacion()
    {
        if (!$this->validarSesion()) {
            return redirect()->to(base_url('login'));
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $dispositivoId = $this->request->getPost('dispositivo_id');
            $ordenId = $this->request->getPost('orden_id');
            $usuarioId = session()->get('id_usuario');

            // Obtener datos del POST
            $manoObra = $this->request->getPost('mano_obra');
            $repuestos = $this->request->getPost('repuestos');
            $valorRevision = $this->request->getPost('cobrar_revision') ? $this->request->getPost('valor_revision') : 0;
            $totalFacturado = $manoObra + $repuestos + $valorRevision;
            $abonosRecibidos = $this->request->getPost('abonos_recibidos') ?? 0;
            $saldoPendiente = $totalFacturado - $abonosRecibidos;

            // 1. Obtener dispositivo y técnico
            $dispositivo = $this->dispositivoModel->find($dispositivoId);
            $tecnico = $this->usuarioModel->find($dispositivo['tecnico_id']);

            // 2. Calcular comisión del técnico
            $comisionCalculada = $this->calcularComision($tecnico, $manoObra);

            // 3. Registrar comisión
            $this->comisionModel->insert([
                'orden_finalizada_id' => null, // Se actualizará después
                'tecnico_id' => $dispositivo['tecnico_id'],
                'dispositivo_id' => $dispositivoId,
                'tipo_comision' => 'reparacion',
                'mano_obra_dispositivo' => $manoObra,
                'tipo_calculo' => $tecnico['tipo_comision'],
                'valor_comision_config' => $tecnico['valor_comision'],
                'comision_calculada' => $comisionCalculada,
                'tiempo_invertido' => $dispositivo['tiempo_diagnostico_minutos'],
                'pagado' => false,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // 4. Verificar si la orden tiene más dispositivos pendientes
            $dispositivosPendientes = $this->dispositivoModel
                ->where('orden_id', $ordenId)
                ->where('id !=', $dispositivoId)
                ->whereNotIn('estado_diagnostico', ['entregado'])
                ->countAllResults();

            // 5. Si no hay más dispositivos, crear orden finalizada
            $ordenFinalizadaId = null;
            if ($dispositivosPendientes == 0) {
                // Calcular totales de toda la orden
                $totalesOrden = $this->calcularTotalesOrden($ordenId);

                $ordenFinalizadaId = $this->ordenFinalizadaModel->insert([
                    'orden_id' => $ordenId,
                    'fecha_finalizacion' => date('Y-m-d H:i:s'),
                    'total_mano_obra' => $totalesOrden['mano_obra'],
                    'total_repuestos' => $totalesOrden['repuestos'],
                    'total' => $totalesOrden['total'],
                    'ganancia_total_tecnicos' => $totalesOrden['comisiones'],
                    'recargo_urgencia' => $totalesOrden['recargo_urgencia'],
                    'total_facturado' => $totalFacturado,
                    'abonos_recibidos' => $abonosRecibidos,
                    'saldo_pendiente' => $saldoPendiente,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // Actualizar estado de la orden
                $this->ordenTrabajoModel->update($ordenId, [
                    'estado_global' => 'finalizada'
                ]);

                // Actualizar comisiones con el ID de orden finalizada
                $this->comisionModel
                    ->where('dispositivo_id', $dispositivoId)
                    ->set(['orden_finalizada_id' => $ordenFinalizadaId])
                    ->update();
            }

            // 6. Actualizar dispositivo
            $this->dispositivoModel->update($dispositivoId, [
                'fecha_entrega_real' => date('Y-m-d'),
                'cobra_valor_revision' => $valorRevision > 0,
                'valor_revision_cobrado' => $valorRevision,
                'razon_cobro_revision' => $this->request->getPost('razon_cobro_revision')
            ]);

            // 7. Registrar en historial
            $this->historialDispositivoModel->insert([
                'usuario_id' => $usuarioId,
                'dispositivo_id' => $dispositivoId,
                'estado_anterior' => $dispositivo['estado_diagnostico'],
                'estado_nuevo' => 'entregado',
                'comentario' => 'Dispositivo entregado al cliente. ' .
                    'Total facturado: $' . number_format($totalFacturado, 2) . '. ' .
                    'Recibe: ' . $this->request->getPost('nombre_receptor') . '. ' .
                    'Observaciones: ' . $this->request->getPost('observaciones_entrega'),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // 8. Generar garantía si se solicitó
            if ($this->request->getPost('generar_garantia')) {
                $this->generarGarantia($dispositivo, $usuarioId);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción de base de datos');
            }

            session()->setFlashdata('success', 'Dispositivo finalizado y entregado correctamente');

            // Redirigir según si quedan dispositivos
            if ($dispositivosPendientes > 0) {
                return redirect()->to(base_url('admin/ordenes/editar/' . $ordenId));
            } else {
                return redirect()->to(base_url('admin/ordenes'));
            }

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[FinalizacionController::procesarFinalizacion] ' . $e->getMessage());
            session()->setFlashdata('error', 'Error al finalizar dispositivo: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    // ========== MÉTODOS AUXILIARES PRIVADOS ==========

    private function validarSesion()
    {
        if (!session()->get('id_usuario')) {
            return false;
        }

        $role = session()->get('role');
        return in_array($role, ['admin', 'recepcionista', 'tecnico']);
    }

    private function calcularCostosDispositivo($dispositivoId)
    {
        $problemas = $this->problemaModel
            ->where('dispositivo_id', $dispositivoId)
            ->where('fue_reparado', 1)
            ->findAll();

        $manoObra = 0;
        foreach ($problemas as $problema) {
            $manoObra += $problema['costo_reparacion'] ?? 0;
        }

        return [
            'mano_obra' => $manoObra,
            'repuestos' => 0 // Esto podría venir de otra tabla si tienes control de repuestos
        ];
    }

    private function determinarCobroRevision($dispositivo, $problemasResueltos)
    {
        $politica = $this->politicaRevisionModel
            ->where('es_politica_activa', 1)
            ->first();

        if (!$politica) {
            return [
                'debe_cobrar' => false,
                'valor' => 0,
                'razon' => ''
            ];
        }

        $debeCobrar = false;
        $razon = '';

        // Cliente rechazó la reparación
        if ($dispositivo['cliente_autoriza_reparacion'] === false && $politica['cobra_revision_si_rechaza_cliente']) {
            $debeCobrar = true;
            $razon = 'Cliente rechazó la reparación después del diagnóstico';
        }

        // No se pudo diagnosticar
        if ($dispositivo['diagnostico_encontrado'] === false && $politica['cobra_revision_si_no_hay_diagnostico']) {
            $debeCobrar = true;
            $razon = 'No se pudo determinar el problema';
        }

        // Reparación exitosa
        if (!empty($problemasResueltos) && $politica['cobra_revision_si_reparacion_exitosa']) {
            $debeCobrar = true;
            $razon = 'Cobro de revisión técnica incluido en reparación';
        }

        return [
            'debe_cobrar' => $debeCobrar,
            'valor' => $debeCobrar ? $politica['valor_revision_base'] : 0,
            'razon' => $razon
        ];
    }

    private function calcularComision($tecnico, $manoObra)
    {
        if ($tecnico['tipo_comision'] === 'porcentaje') {
            return ($manoObra * $tecnico['valor_comision']) / 100;
        } else {
            return $tecnico['valor_comision'];
        }
    }

    private function calcularTotalesOrden($ordenId)
    {
        $db = \Config\Database::connect();

        // Obtener todos los dispositivos de la orden
        $dispositivos = $this->dispositivoModel
            ->where('orden_id', $ordenId)
            ->findAll();

        $totalManoObra = 0;
        $totalRepuestos = 0;
        $totalComisiones = 0;

        foreach ($dispositivos as $dispositivo) {
            $costos = $this->calcularCostosDispositivo($dispositivo['id']);
            $totalManoObra += $costos['mano_obra'];
            $totalRepuestos += $costos['repuestos'];

            // Sumar comisiones
            $comisiones = $this->comisionModel
                ->selectSum('comision_calculada', 'total')
                ->where('dispositivo_id', $dispositivo['id'])
                ->first();

            $totalComisiones += $comisiones['total'] ?? 0;
        }

        // Obtener recargo por urgencia si aplica
        $orden = $this->ordenTrabajoModel
            ->select('ordenes_trabajo.*, u.recargo')
            ->join('urgencias as u', 'u.id = ordenes_trabajo.urgencia_id', 'left')
            ->where('ordenes_trabajo.id', $ordenId)
            ->first();

        $recargoUrgencia = $orden['recargo'] ?? 0;

        return [
            'mano_obra' => $totalManoObra,
            'repuestos' => $totalRepuestos,
            'total' => $totalManoObra + $totalRepuestos + $recargoUrgencia,
            'comisiones' => $totalComisiones,
            'recargo_urgencia' => $recargoUrgencia
        ];
    }

    private function generarGarantia($dispositivo, $usuarioId)
    {
        $tipoGarantiaId = $this->request->getPost('tipo_garantia_id');
        $problemaGarantizadoId = $this->request->getPost('problema_garantizado_id');

        if (!$tipoGarantiaId || !$problemaGarantizadoId) {
            return;
        }

        $tipoGarantia = $this->tipoGarantiaModel->find($tipoGarantiaId);

        $fechaInicio = date('Y-m-d');
        $fechaVencimiento = date('Y-m-d', strtotime("+{$tipoGarantia['dias']} days"));

        $garantiaId = $this->garantiaModel->insert([
            'dispositivo_id' => $dispositivo['id'],
            'orden_original_id' => $dispositivo['orden_id'],
            'tecnico_id' => $dispositivo['tecnico_id'],
            'tipo_garantia_id' => $tipoGarantiaId,
            'problema_reparado_id' => $problemaGarantizadoId,
            'fecha_inicio' => $fechaInicio,
            'fecha_vencimiento' => $fechaVencimiento,
            'dias_garantia' => $tipoGarantia['dias'],
            'estado' => 'activa',
            'condiciones_aceptadas_cliente' => true, // Ya entregó el dispositivo
            'texto_condiciones' => $tipoGarantia['condiciones'] ?? '',
            'exclusiones' => $tipoGarantia['exclusiones'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // Actualizar dispositivo con garantía
        $this->dispositivoModel->update($dispositivo['id'], [
            'tiene_garantia_activa' => true,
            'garantia_vence_en' => $fechaVencimiento
        ]);

        // Registrar en historial de garantías
        $this->historialGarantiasModel->insert([
            'garantia_id' => $garantiaId,
            'usuario_id' => $usuarioId,
            'accion' => 'creada',
            'estado_anterior' => null,
            'estado_nuevo' => 'activa',
            'motivo' => 'Garantía generada al momento de entrega',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}