<?php

namespace App\Controllers\Tecnico;

use App\Controllers\BaseController;
use App\Models\ComisionTecnicoModel;
use App\Models\DispositivoAccesorioModel;
use App\Models\DispositivoCheckModel;
use App\Models\DispositivoModel;
use App\Models\DispositivoProblemasModel;
use App\Models\GarantiaModel;
use App\Models\HistorialDiagnosticoModel;
use App\Models\HistorialDispositivoModel;
use App\Models\HistorialGarantiasModel;
use App\Models\ProblemasComunesModel;
use App\Models\SolicitudCobroDiagnosticoModel;
use App\Models\TipoGarantiaModel;
use CodeIgniter\HTTP\ResponseInterface;

class DispositivoController extends BaseController
{
    protected $dispositivoModel;
    protected $problemaModel;
    protected $accesorioModel;
    protected $checkModel;
    protected $historialDiagnosticoModel;
    protected $historialDispositivo;
    protected $garantiaModel;
    protected $problemasComunesModel;
    protected $tipoGarantiaModel;
    protected $solicitudCobroModel;
    protected $comisionModel;

    public function __construct()
    {
        $this->dispositivoModel = new DispositivoModel();
        $this->problemaModel = new DispositivoProblemasModel();
        $this->accesorioModel = new DispositivoAccesorioModel();
        $this->checkModel = new DispositivoCheckModel();
        $this->historialDiagnosticoModel = new HistorialDiagnosticoModel();
        $this->historialDispositivo = new HistorialDispositivoModel();
        $this->garantiaModel = new GarantiaModel();
        $this->problemasComunesModel = new ProblemasComunesModel();
        $this->tipoGarantiaModel = new TipoGarantiaModel();
        $this->solicitudCobroModel = new SolicitudCobroDiagnosticoModel();
        $this->comisionModel = new ComisionTecnicoModel();
    }

    /**
     * Index - Listado de dispositivos asignados al técnico
     */
    public function index()
    {
        if (!$this->validarSesionTecnico()) {
            return redirect()->to(base_url('login'));
        }

        $tecnicoId = session()->get('id_usuario');

        try {
            // Obtener dispositivos asignados
            $dispositivos = $this->dispositivoModel
                ->select('
                    dispositivos.*,
                    td.nombre as tipo_dispositivo,
                    m.nombre as marca,
                    mod.nombre as modelo,
                    ot.codigo_orden,
                    ot.id as orden_id,
                    CONCAT(c.nombres, " ", c.apellidos) as cliente_nombre,
                    u.color_hex as prioridad_color,
                    u.nombre as prioridad_nombre,
                    (SELECT COUNT(*) FROM dispositivo_problemas WHERE dispositivo_id = dispositivos.id) as total_problemas,
                    (SELECT COUNT(*) FROM dispositivo_problemas WHERE dispositivo_id = dispositivos.id AND fue_reparado = 1) as problemas_reparados
                ')
                ->join('tipos_dispositivo as td', 'td.id = dispositivos.tipo_dispositivo_id', 'left')
                ->join('marcas as m', 'm.id = dispositivos.marca_id', 'left')
                ->join('modelos as mod', 'mod.id = dispositivos.modelo_id', 'left')
                ->join('ordenes_trabajo as ot', 'ot.id = dispositivos.orden_id')
                ->join('clientes as c', 'c.id = ot.cliente_id')
                ->join('urgencias as u', 'u.id = dispositivos.prioridad_dispositivo_id', 'left')
                ->where('dispositivos.tecnico_id', $tecnicoId)
                ->whereNotIn('ot.estado_global', ['entregada', 'cancelada'])
                ->orderBy('dispositivos.created_at', 'DESC')
                ->findAll();

            // Obtener resumen
            $resumen = $this->obtenerResumenTecnico($tecnicoId);

            $data = [
                'titulo' => 'Mis Dispositivos Asignados',
                'dispositivos' => $dispositivos,
                'resumen' => $resumen,
            ];

            return view('tecnico/dispositivos/index_prueba', $data);

        } catch (\Exception $e) {
            log_message('error', '[TecnicoDispositivoController::index] ' . $e->getMessage());
            session()->setFlashdata('error', 'Error al cargar dispositivos');
            return redirect()->to(base_url('tecnico/dashboard'));
        }
    }

    /**
     * Trabajar - Vista principal para trabajar en un dispositivo
     */
    public function trabajar($dispositivoId)
    {
        if (!$this->validarSesionTecnico()) {
            return redirect()->to(base_url('login'));
        }

        $tecnicoId = session()->get('id_usuario');

        try {
            // Verificar que el dispositivo esté asignado al técnico
            $dispositivo = $this->dispositivoModel
                ->select('
                    dispositivos.*,
                    td.nombre as tipo_dispositivo,
                    m.nombre as marca,
                    mod.nombre as modelo,
                    ot.codigo_orden,
                    ot.id as orden_id,
                    CONCAT(c.nombres, " ", c.apellidos) as cliente_nombre
                ')
                ->join('tipos_dispositivo as td', 'td.id = dispositivos.tipo_dispositivo_id', 'left')
                ->join('marcas as m', 'm.id = dispositivos.marca_id', 'left')
                ->join('modelos as mod', 'mod.id = dispositivos.modelo_id', 'left')
                ->join('ordenes_trabajo as ot', 'ot.id = dispositivos.orden_id')
                ->join('clientes as c', 'c.id = ot.cliente_id')
                ->where('dispositivos.id', $dispositivoId)
                ->where('dispositivos.tecnico_id', $tecnicoId)
                ->first();

            if (!$dispositivo) {
                session()->setFlashdata('error', 'Dispositivo no encontrado o no está asignado a ti');
                return redirect()->to(base_url('tecnico/dispositivos'));
            }

            // Obtener datos relacionados
            $problemas = $this->obtenerProblemas($dispositivoId);
            $accesorios = $this->obtenerAccesorios($dispositivoId);
            $checklist = $this->obtenerChecklist($dispositivoId);
            $historialCompleto = $this->obtenerHistorialCompleto($dispositivoId);
            $problemas_comunes = $this->problemasComunesModel->where('activo', 1)->findAll();
            $tipos_garantia = $this->tipoGarantiaModel->where('activo', 1)->findAll();

            $data = [
                'titulo' => 'Trabajar en Dispositivo',
                'dispositivo' => $dispositivo,
                'problemas' => $problemas,
                'accesorios' => $accesorios,
                'checklist' => $checklist,
                'historial_completo' => $historialCompleto,
                'problemas_comunes' => $problemas_comunes,
                'tipos_garantia' => $tipos_garantia,
            ];

            return view('tecnico/dispositivos/trabajar', $data);

        } catch (\Exception $e) {
            log_message('error', '[TecnicoDispositivoController::trabajar] ' . $e->getMessage());
            session()->setFlashdata('error', 'Error al cargar el dispositivo');
            return redirect()->to(base_url('tecnico/dispositivos'));
        }
    }

    /**
     * Iniciar diagnóstico
     */
    public function iniciarDiagnostico()
    {
        if (!$this->validarSesionTecnico()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sesión inválida']);
        }

        $dispositivoId = $this->request->getPost('dispositivo_id');
        $tecnicoId = session()->get('id_usuario');

        try {
            // Verificar que el dispositivo esté asignado al técnico
            $dispositivo = $this->dispositivoModel->find($dispositivoId);

            if (!$dispositivo || $dispositivo['tecnico_id'] != $tecnicoId) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Dispositivo no encontrado o no autorizado'
                ]);
            }

            // Actualizar estado del dispositivo
            $this->dispositivoModel->update($dispositivoId, [
                'estado_diagnostico' => 'en_revision'
            ]);

            // Registrar en historial del dispositivo
            $this->historialDispositivo->insert([
                'usuario_id' => $tecnicoId,
                'dispositivo_id' => $dispositivoId,
                'estado_anterior' => $dispositivo['estado_diagnostico'],
                'estado_nuevo' => 'en_revision',
                'comentario' => 'Inicio de diagnóstico',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Diagnóstico iniciado correctamente'
            ]);

        } catch (\Exception $e) {
            log_message('error', '[TecnicoDispositivoController::iniciarDiagnostico] ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al iniciar diagnóstico'
            ]);
        }
    }

    /**
     * Guardar diagnóstico
     */
    public function guardarDiagnostico()
    {
        if (!$this->validarSesionTecnico()) {
            return redirect()->to(base_url('login'));
        }

        $tecnicoId = session()->get('id_usuario');
        $dispositivoId = $this->request->getPost('dispositivo_id');

        try {
            $diagnosticoEncontrado = $this->request->getPost('diagnostico_encontrado');

            // Calcular tiempo invertido (desde que cambió a 'en_revision')
            $tiempoInvertido = $this->calcularTiempoInvertido($dispositivoId);

            if ($diagnosticoEncontrado == '1') {
                // Diagnóstico encontrado
                $dataDiagnostico = [
                    'dispositivo_id' => $dispositivoId,
                    'tecnico_id' => $tecnicoId,
                    'fecha_inicio_diagnostico' => $this->obtenerFechaInicioDiagnostico($dispositivoId),
                    'fecha_fin_diagnostico' => date('Y-m-d H:i:s'),
                    'tiempo_invertido_minutos' => $tiempoInvertido,
                    'diagnostico_encontrado' => true,
                    'detalle_tecnico' => $this->request->getPost('detalle_tecnico'),
                    'detalle_cliente' => $this->request->getPost('detalle_cliente'),
                    'costo_estimado_reparacion' => $this->request->getPost('costo_estimado_reparacion'),
                    'costo_estimado_repuestos' => $this->request->getPost('costo_estimado_repuestos'),
                    'observaciones_internas' => $this->request->getPost('observaciones_internas'),
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $this->historialDiagnosticoModel->insert($dataDiagnostico);

                // Actualizar dispositivo
                $this->dispositivoModel->update($dispositivoId, [
                    'estado_diagnostico' => 'diagnosticado',
                    'diagnostico_encontrado' => true,
                    'diagnostico_detalle' => $this->request->getPost('detalle_tecnico'),
                    'diagnostico_cliente' => $this->request->getPost('detalle_cliente'),
                    'tiempo_diagnostico_minutos' => $tiempoInvertido,
                    'costo_diagnostico_estimado' => $this->request->getPost('costo_estimado_reparacion')
                ]);

                // Registrar cambio de estado
                $this->historialDispositivo->insert([
                    'usuario_id' => $tecnicoId,
                    'dispositivo_id' => $dispositivoId,
                    'estado_anterior' => 'en_revision',
                    'estado_nuevo' => 'diagnosticado',
                    'comentario' => 'Diagnóstico completado exitosamente',
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                session()->setFlashdata('success', 'Diagnóstico guardado correctamente');

            } else {
                // Diagnóstico no encontrado
                $dataDiagnostico = [
                    'dispositivo_id' => $dispositivoId,
                    'tecnico_id' => $tecnicoId,
                    'fecha_inicio_diagnostico' => $this->obtenerFechaInicioDiagnostico($dispositivoId),
                    'fecha_fin_diagnostico' => date('Y-m-d H:i:s'),
                    'tiempo_invertido_minutos' => $tiempoInvertido,
                    'diagnostico_encontrado' => false,
                    'detalle_tecnico' => $this->request->getPost('razon_no_diagnostico'),
                    'detalle_cliente' => 'No se pudo determinar el problema',
                    'observaciones_internas' => $this->request->getPost('razon_no_diagnostico'),
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $this->historialDiagnosticoModel->insert($dataDiagnostico);

                // Actualizar dispositivo
                $this->dispositivoModel->update($dispositivoId, [
                    'estado_diagnostico' => 'sin_diagnostico',
                    'diagnostico_encontrado' => false,
                    'diagnostico_detalle' => $this->request->getPost('razon_no_diagnostico'),
                    'tiempo_diagnostico_minutos' => $tiempoInvertido
                ]);

                // Registrar cambio de estado
                $this->historialDispositivo->insert([
                    'usuario_id' => $tecnicoId,
                    'dispositivo_id' => $dispositivoId,
                    'estado_anterior' => 'en_revision',
                    'estado_nuevo' => 'sin_diagnostico',
                    'comentario' => 'No se pudo diagnosticar el dispositivo',
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // Verificar si se solicita cobro por diagnóstico
                if ($this->request->getPost('solicitar_cobro_diagnostico')) {
                    $this->solicitudCobroModel->insert([
                        'dispositivo_id' => $dispositivoId,
                        'tecnico_id' => $tecnicoId,
                        'tiempo_invertido_minutos' => $this->request->getPost('tiempo_cobro'),
                        'justificacion' => $this->request->getPost('justificacion_cobro'),
                        'valor_solicitado' => $this->request->getPost('valor_cobro'),
                        'estado' => 'pendiente',
                        'created_at' => date('Y-m-d H:i:s')
                    ]);

                    session()->setFlashdata('info', 'Solicitud de cobro enviada para aprobación');
                }

                session()->setFlashdata('warning', 'Diagnóstico registrado como no encontrado');
            }

            return redirect()->to(base_url('tecnico/dispositivos/trabajar/' . $dispositivoId));

        } catch (\Exception $e) {
            log_message('error', '[TecnicoDispositivoController::guardarDiagnostico] ' . $e->getMessage());
            session()->setFlashdata('error', 'Error al guardar diagnóstico: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Agregar problema al dispositivo
     */
    public function agregarProblema()
    {
        if (!$this->validarSesionTecnico()) {
            return redirect()->to(base_url('login'));
        }

        $tecnicoId = session()->get('id_usuario');
        $dispositivoId = $this->request->getPost('dispositivo_id');

        try {
            // Verificar permisos
            $dispositivo = $this->dispositivoModel->find($dispositivoId);
            if (!$dispositivo || $dispositivo['tecnico_id'] != $tecnicoId) {
                session()->setFlashdata('error', 'No tienes permiso para modificar este dispositivo');
                return redirect()->back();
            }

            $dataProblema = [
                'dispositivo_id' => $dispositivoId,
                'problema_comun_id' => $this->request->getPost('problema_comun_id'),
                'prioridad' => $this->request->getPost('prioridad'),
                'diagnostico_inicial' => 'Agregado por técnico durante revisión',
                'diagnostico_tecnico' => $this->request->getPost('diagnostico_tecnico'),
                'fue_reparado' => null,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->problemaModel->insert($dataProblema);

            // Registrar en historial
            $problemaInfo = $this->problemasComunesModel->find($this->request->getPost('problema_comun_id'));
            $this->historialDispositivo->insert([
                'usuario_id' => $tecnicoId,
                'dispositivo_id' => $dispositivoId,
                'estado_anterior' => null,
                'estado_nuevo' => 'problema_agregado',
                'comentario' => 'Problema agregado: ' . $problemaInfo['nombre'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            session()->setFlashdata('success', 'Problema agregado correctamente');
            return redirect()->to(base_url('tecnico/dispositivos/trabajar/' . $dispositivoId));

        } catch (\Exception $e) {
            log_message('error', '[TecnicoDispositivoController::agregarProblema] ' . $e->getMessage());
            session()->setFlashdata('error', 'Error al agregar problema');
            return redirect()->back();
        }
    }

    /**
     * Actualizar problema
     */
    public function actualizarProblema()
    {
        if (!$this->validarSesionTecnico()) {
            return redirect()->to(base_url('login'));
        }

        $tecnicoId = session()->get('id_usuario');
        $problemaId = $this->request->getPost('problema_id');
        $dispositivoId = $this->request->getPost('dispositivo_id');

        try {
            // Verificar permisos
            $dispositivo = $this->dispositivoModel->find($dispositivoId);
            if (!$dispositivo || $dispositivo['tecnico_id'] != $tecnicoId) {
                session()->setFlashdata('error', 'No tienes permiso para modificar este dispositivo');
                return redirect()->back();
            }

            $estadoReparacion = $this->request->getPost('estado_reparacion');
            $fueReparado = $estadoReparacion === 'reparado' ? true : ($estadoReparacion === 'no_reparado' ? false : null);

            $dataProblema = [
                'diagnostico_tecnico' => $this->request->getPost('diagnostico_tecnico'),
                'fue_reparado' => $fueReparado,
                'costo_reparacion' => $this->request->getPost('costo_reparacion'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Si no se reparó y hay razón
            if ($fueReparado === false) {
                $dataProblema['razon_no_reparado'] = $this->request->getPost('razon_no_reparado');
            }

            $this->problemaModel->update($problemaId, $dataProblema);

            // Registrar en historial
            $problema = $this->problemaModel->find($problemaId);
            $this->historialDispositivo->insert([
                'usuario_id' => $tecnicoId,
                'dispositivo_id' => $dispositivoId,
                'estado_anterior' => null,
                'estado_nuevo' => 'problema_actualizado',
                'comentario' => 'Problema actualizado: ' . ($fueReparado ? 'Reparado' : 'No reparado'),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            session()->setFlashdata('success', 'Problema actualizado correctamente');
            return redirect()->to(base_url('tecnico/dispositivos/trabajar/' . $dispositivoId));

        } catch (\Exception $e) {
            log_message('error', '[TecnicoDispositivoController::actualizarProblema] ' . $e->getMessage());
            session()->setFlashdata('error', 'Error al actualizar problema');
            return redirect()->back();
        }
    }

    /**
     * Generar presupuesto
     */
    public function generarPresupuesto()
    {
        if (!$this->validarSesionTecnico()) {
            return redirect()->to(base_url('login'));
        }

        $tecnicoId = session()->get('id_usuario');
        $dispositivoId = $this->request->getPost('dispositivo_id');

        try {
            // Verificar permisos
            $dispositivo = $this->dispositivoModel->find($dispositivoId);
            if (!$dispositivo || $dispositivo['tecnico_id'] != $tecnicoId) {
                session()->setFlashdata('error', 'No tienes permiso para modificar este dispositivo');
                return redirect()->back();
            }

            $costoManoObra = $this->request->getPost('costo_mano_obra');
            $costoRepuestos = $this->request->getPost('costo_repuestos');
            $total = $costoManoObra + $costoRepuestos;

            // Actualizar dispositivo con presupuesto
            $this->dispositivoModel->update($dispositivoId, [
                'costo_diagnostico_estimado' => $total,
                'fecha_estimada_entrega' => $this->request->getPost('fecha_estimada_entrega'),
                'requiere_cotizacion' => $this->request->getPost('requiere_cotizacion') ? true : false
            ]);

            // Registrar en historial
            $this->historialDispositivo->insert([
                'usuario_id' => $tecnicoId,
                'dispositivo_id' => $dispositivoId,
                'estado_anterior' => null,
                'estado_nuevo' => 'presupuesto_generado',
                'comentario' => 'Presupuesto generado: $' . number_format($total, 2) .
                    ' - Observaciones: ' . $this->request->getPost('observaciones_cliente'),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // TODO: Aquí podrías enviar notificación al cliente o al admin

            session()->setFlashdata('success', 'Presupuesto enviado correctamente');
            return redirect()->to(base_url('tecnico/dispositivos/trabajar/' . $dispositivoId));

        } catch (\Exception $e) {
            log_message('error', '[TecnicoDispositivoController::generarPresupuesto] ' . $e->getMessage());
            session()->setFlashdata('error', 'Error al generar presupuesto');
            return redirect()->back();
        }
    }

    /**
     * Registrar garantía
     */
    public function registrarGarantia()
    {
        if (!$this->validarSesionTecnico()) {
            return redirect()->to(base_url('login'));
        }

        $tecnicoId = session()->get('id_usuario');
        $dispositivoId = $this->request->getPost('dispositivo_id');

        try {
            // Verificar permisos y que el cliente haya autorizado
            $dispositivo = $this->dispositivoModel->find($dispositivoId);
            if (!$dispositivo || $dispositivo['tecnico_id'] != $tecnicoId) {
                session()->setFlashdata('error', 'No tienes permiso');
                return redirect()->back();
            }

            if (!$dispositivo['cliente_autoriza_reparacion']) {
                session()->setFlashdata('error', 'El cliente debe autorizar la reparación primero');
                return redirect()->back();
            }

            // Obtener tipo de garantía
            $tipoGarantiaId = $this->request->getPost('tipo_garantia_id');
            $tipoGarantia = $this->tipoGarantiaModel->find($tipoGarantiaId);

            if (!$tipoGarantia) {
                session()->setFlashdata('error', 'Tipo de garantía no válido');
                return redirect()->back();
            }

            // Calcular fechas
            $fechaInicio = date('Y-m-d');
            $fechaVencimiento = date('Y-m-d', strtotime("+{$tipoGarantia['dias']} days"));

            // Crear garantía
            $dataGarantia = [
                'dispositivo_id' => $dispositivoId,
                'orden_original_id' => $dispositivo['orden_id'],
                'tecnico_id' => $tecnicoId,
                'tipo_garantia_id' => $tipoGarantiaId,
                'problema_reparado_id' => $this->request->getPost('problema_reparado_id'),
                'fecha_inicio' => $fechaInicio,
                'fecha_vencimiento' => $fechaVencimiento,
                'dias_garantia' => $tipoGarantia['dias'],
                'estado' => 'activa',
                'condiciones_aceptadas_cliente' => false, // Se actualizará cuando el cliente firme
                'texto_condiciones' => $tipoGarantia['condiciones'] ?? '',
                'exclusiones' => $tipoGarantia['exclusiones'] ?? '',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $garantiaId = $this->garantiaModel->insert($dataGarantia);

            // Actualizar dispositivo
            $this->dispositivoModel->update($dispositivoId, [
                'tiene_garantia_activa' => true,
                'garantia_vence_en' => $fechaVencimiento
            ]);

            // Registrar en historial de garantías
            $historialGarantiasModel = new HistorialGarantiasModel();
            $historialGarantiasModel->insert([
                'garantia_id' => $garantiaId,
                'usuario_id' => $tecnicoId,
                'accion' => 'creada',
                'estado_anterior' => null,
                'estado_nuevo' => 'activa',
                'motivo' => 'Garantía creada por reparación autorizada',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Registrar en historial del dispositivo
            $this->historialDispositivo->insert([
                'usuario_id' => $tecnicoId,
                'dispositivo_id' => $dispositivoId,
                'estado_anterior' => null,
                'estado_nuevo' => 'garantia_registrada',
                'comentario' => 'Garantía registrada: ' . $tipoGarantia['nombre'] . ' (' . $tipoGarantia['dias'] . ' días)',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            session()->setFlashdata('success', 'Garantía registrada correctamente');
            return redirect()->to(base_url('tecnico/dispositivos/trabajar/' . $dispositivoId));

        } catch (\Exception $e) {
            log_message('error', '[TecnicoDispositivoController::registrarGarantia] ' . $e->getMessage());
            session()->setFlashdata('error', 'Error al registrar garantía: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    // ========== MÉTODOS AUXILIARES PRIVADOS ==========

    private function validarSesionTecnico()
    {
        if (!session()->get('id_usuario')) {
            return false;
        }

        $role = session()->get('role');
        return in_array($role, ['tecnico', 'admin']);
    }

    private function obtenerResumenTecnico($tecnicoId)
    {
        $db = \Config\Database::connect();

        // Pendientes
        $pendientes = $this->dispositivoModel
            ->where('tecnico_id', $tecnicoId)
            ->where('estado_diagnostico', 'pendiente')
            ->countAllResults();

        // En proceso
        $enProceso = $this->dispositivoModel
            ->where('tecnico_id', $tecnicoId)
            ->whereIn('estado_diagnostico', ['en_revision', 'diagnosticado'])
            ->countAllResults();

        // Completados hoy
        $completadosHoy = $db->table('dispositivos')
            ->join('dispositivo_problemas', 'dispositivo_problemas.dispositivo_id = dispositivos.id')
            ->where('dispositivos.tecnico_id', $tecnicoId)
            ->where('DATE(dispositivo_problemas.updated_at)', date('Y-m-d'))
            ->where('dispositivo_problemas.fue_reparado', 1)
            ->countAllResults();

        // Comisiones pendientes
        $comisionesPendientes = $db->table('comisiones_tecnicos')
            ->selectSum('comision_calculada')
            ->where('tecnico_id', $tecnicoId)
            ->where('pagado', 0)
            ->get()
            ->getRow()
            ->comision_calculada ?? 0;

        return [
            'pendientes' => $pendientes,
            'en_proceso' => $enProceso,
            'completados_hoy' => $completadosHoy,
            'comisiones_pendientes' => $comisionesPendientes
        ];
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

    private function obtenerHistorialCompleto($dispositivoId)
    {
        $historial = [];

        // Historial de diagnósticos
        $diagnosticos = $this->historialDiagnosticoModel
            ->select('
                historial_diagnostico.*,
                CONCAT(u.nombres, " ", u.apellidos) as usuario,
                historial_diagnostico.fecha_inicio_diagnostico as fecha
            ')
            ->join('usuarios as u', 'u.id = historial_diagnostico.tecnico_id')
            ->where('historial_diagnostico.dispositivo_id', $dispositivoId)
            ->findAll();

        foreach ($diagnosticos as $diag) {
            $historial[] = [
                'tipo' => 'diagnostico',
                'fecha' => $diag['fecha'],
                'usuario' => $diag['usuario'],
                'detalle' => $diag['detalle_tecnico']
            ];
        }

        // Historial de estados
        $estados = $this->historialDispositivo
            ->select('
                historial_dispositivo.*,
                CONCAT(u.nombres, " ", u.apellidos) as usuario
            ')
            ->join('usuarios as u', 'u.id = historial_dispositivo.usuario_id')
            ->where('historial_dispositivo.dispositivo_id', $dispositivoId)
            ->findAll();

        foreach ($estados as $estado) {
            $historial[] = [
                'tipo' => 'estado',
                'fecha' => $estado['created_at'],
                'usuario' => $estado['usuario'],
                'detalle' => $estado['comentario']
            ];
        }

        // Ordenar por fecha descendente
        usort($historial, function ($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });

        return $historial;
    }

    private function calcularTiempoInvertido($dispositivoId)
    {
        // Obtener la fecha cuando cambió a 'en_revision'
        $historial = $this->historialDispositivo
            ->where('dispositivo_id', $dispositivoId)
            ->where('estado_nuevo', 'en_revision')
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($historial) {
            $inicio = strtotime($historial['created_at']);
            $fin = time();
            $diferencia = $fin - $inicio;
            return round($diferencia / 60); // Minutos
        }

        return 0;
    }

    private function obtenerFechaInicioDiagnostico($dispositivoId)
    {
        $historial = $this->historialDispositivo
            ->where('dispositivo_id', $dispositivoId)
            ->where('estado_nuevo', 'en_revision')
            ->orderBy('created_at', 'DESC')
            ->first();

        return $historial ? $historial['created_at'] : date('Y-m-d H:i:s');
    }
}
