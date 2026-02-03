<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrdenTrabajoModel;
use App\Models\DispositivoModel;
use App\Models\ClienteModel;
use App\Models\TecnicoModel;
use App\Models\ComisionTecnicoModel;
use App\Models\OrdenFinalizadaModel;
use App\Models\SolicitudCobroDiagnosticoModel;
use App\Models\ExcepcionGarantiaModel;
use App\Models\GarantiaModel;
use App\Models\ReclamoGarantiaModel;
use App\Models\ProblemasComunesModel;
use App\Models\ChecklistItemModel;
use App\Models\PoliticaRevisionModel;
use App\Models\UrgenciaModel;
use App\Models\TipoDispositivoModel;
use App\Models\MarcaModel;
use App\Models\ModeloDispositivoModel;
use App\Models\AccesorioModel;
use App\Models\TipoGarantiaModel;
use App\Models\DispositivoProblemasModel;
use App\Models\HistorialClienteModel;

class AdminController extends BaseController
{
    protected $ordenModel;
    protected $dispositivoModel;
    protected $clienteModel;
    protected $tecnicoModel;
    protected $comisionModel;
    protected $ordenFinalizadaModel;
    protected $solicitudCobroModel;
    protected $excepcionGarantiaModel;
    protected $garantiaModel;
    protected $reclamoModel;
    protected $problemasModel;
    protected $checklistItemModel;
    protected $politicaRevisionModel;
    protected $urgenciaModel;
    protected $tipoDispositivoModel;
    protected $marcaModel;
    protected $modeloModel;
    protected $accesorioModel;
    protected $tipoGarantiaModel;
    protected $dispositivoProblemasModel;
    protected $historialClienteModel;

    public function __construct()
    {
        $this->ordenModel = new OrdenTrabajoModel();
        $this->dispositivoModel = new DispositivoModel();
        $this->clienteModel = new ClienteModel();
        $this->tecnicoModel = new TecnicoModel();
        $this->comisionModel = new ComisionTecnicoModel();
        $this->ordenFinalizadaModel = new OrdenFinalizadaModel();
        $this->solicitudCobroModel = new SolicitudCobroDiagnosticoModel();
        $this->excepcionGarantiaModel = new ExcepcionGarantiaModel();
        $this->garantiaModel = new GarantiaModel();
        $this->reclamoModel = new ReclamoGarantiaModel();
        $this->problemasModel = new ProblemasComunesModel();
        $this->checklistItemModel = new ChecklistItemModel();
        $this->politicaRevisionModel = new PoliticaRevisionModel();
        $this->urgenciaModel = new UrgenciaModel();
        $this->tipoDispositivoModel = new TipoDispositivoModel();
        $this->marcaModel = new MarcaModel();
        $this->modeloModel = new ModeloDispositivoModel();
        $this->accesorioModel = new AccesorioModel();
        $this->tipoGarantiaModel = new TipoGarantiaModel();
        $this->dispositivoProblemasModel = new DispositivoProblemasModel();
        $this->historialClienteModel = new HistorialClienteModel();
    }

    // ========================================
    // DASHBOARD EJECUTIVO
    // ========================================

    public function index()
    {
        // Métricas del día
        $hoy = date('Y-m-d');
        $ordenesHoy = $this->ordenModel->where('DATE(created_at)', $hoy)->countAllResults();
        
        $dispositivosEnProceso = $this->dispositivoModel
            ->whereIn('estado_reparacion', ['pendiente', 'asignado', 'en_diagnostico', 'en_reparacion'])
            ->countAllResults();
        
        $listosEntrega = $this->dispositivoModel
            ->whereIn('estado_reparacion', ['reparado', 'no_reparado', 'listo_entrega'])
            ->where('estado_reparacion !=', 'entregado')
            ->countAllResults();
        
        $entregadosHoy = $this->dispositivoModel
            ->where('estado_reparacion', 'entregado')
            ->where('DATE(updated_at)', $hoy)
            ->countAllResults();

        // Financiero del día
        $ingresosHoy = $this->ordenFinalizadaModel
            ->selectSum('total_facturado')
            ->where('DATE(fecha_finalizacion)', $hoy)
            ->first();

        // Financiero del mes
        $ingresosMes = $this->ordenFinalizadaModel->getIngresosDelMes();

        // Comisiones pendientes
        $comisionesPendientes = $this->comisionModel
            ->selectSum('comision_calculada')
            ->where('pagado', 0)
            ->first();

        // Alertas importantes
        $garantiasPorVencer = $this->garantiaModel->getGarantiasPorVencer(7);
        $solicitudesPendientes = $this->solicitudCobroModel->getSolicitudesPendientes();
        $excepcionesPendientes = $this->excepcionGarantiaModel->getExcepcionesPendientes();
        $ordenesProblematicas = $this->ordenModel->getOrdenesProblematicas();

        // Rendimiento técnicos (top 5)
        $tecnicosTop = $this->getTecnicosTopRendimiento(5);

        $data = [
            'titulo' => 'Dashboard Administrativo',
            'metricas' => [
                'ordenes_hoy' => $ordenesHoy,
                'en_proceso' => $dispositivosEnProceso,
                'listos_entrega' => $listosEntrega,
                'entregados_hoy' => $entregadosHoy
            ],
            'financiero' => [
                'ingresos_hoy' => $ingresosHoy['total_facturado'] ?? 0,
                'ingresos_mes' => $ingresosMes['total_facturado'] ?? 0,
                'cobrado_mes' => $ingresosMes['total_cobrado'] ?? 0,
                'pendiente_mes' => $ingresosMes['total_pendiente'] ?? 0,
                'comisiones_pendientes' => $comisionesPendientes['comision_calculada'] ?? 0
            ],
            'alertas' => [
                'garantias_por_vencer' => count($garantiasPorVencer),
                'solicitudes_cobro' => count($solicitudesPendientes),
                'excepciones_garantia' => count($excepcionesPendientes),
                'ordenes_sin_avance' => count($ordenesProblematicas)
            ],
            'garantias_proximas' => $garantiasPorVencer,
            'tecnicos_top' => $tecnicosTop
        ];

        return view('admin/dashboard', $data);
    }

    // ========================================
    // APROBACIONES PENDIENTES
    // ========================================

    public function aprobarSolicitudes()
    {
        $tipo = $this->request->getGet('tipo') ?? 'cobros'; // cobros | excepciones

        if ($tipo === 'cobros') {
            $pendientes = $this->solicitudCobroModel->getSolicitudesPendientes();
        } else {
            $pendientes = $this->excepcionGarantiaModel->getExcepcionesPendientes();
        }

        $data = [
            'titulo' => 'Aprobaciones Pendientes',
            'tipo' => $tipo,
            'solicitudes' => $pendientes
        ];

        return view('admin/aprobar_solicitudes', $data);
    }

    // Aprobar/Rechazar solicitud de cobro por diagnóstico
    public function procesarSolicitudCobro()
    {
        $solicitudId = $this->request->getPost('solicitud_id');
        $decision = $this->request->getPost('decision'); // aprobar | rechazar
        $comentario = $this->request->getPost('comentario_admin');
        $valorAjustado = $this->request->getPost('valor_ajustado'); // Si admin cambia el monto

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $solicitud = $this->solicitudCobroModel->find($solicitudId);

            // Actualizar solicitud
            $dataSolicitud = [
                'estado' => $decision === 'aprobar' ? 'aprobada' : 'rechazada',
                'admin_revisor_id' => session()->get('user_id'),
                'comentario_admin' => $comentario,
                'fecha_revision' => date('Y-m-d H:i:s')
            ];

            $this->solicitudCobroModel->update($solicitudId, $dataSolicitud);

            if ($decision === 'aprobar') {
                $valorFinal = $valorAjustado ?? $solicitud['valor_solicitado'];

                // Actualizar dispositivo
                $this->dispositivoModel->update($solicitud['dispositivo_id'], [
                    'cobra_valor_revision' => true,
                    'valor_revision_cobrado' => $valorFinal,
                    'razon_cobro_revision' => 'Aprobado por administrador - ' . $comentario
                ]);

                // Crear comisión para el técnico
                $tecnico = $this->tecnicoModel->find($solicitud['tecnico_id']);
                
                $comisionCalculada = $this->comisionModel->calcularComision(
                    $valorFinal,
                    $tecnico['tipo_comision'],
                    $tecnico['valor_comision']
                );

                $this->comisionModel->insert([
                    'tecnico_id' => $solicitud['tecnico_id'],
                    'dispositivo_id' => $solicitud['dispositivo_id'],
                    'tipo_comision' => 'diagnostico',
                    'mano_obra_dispositivo' => $valorFinal,
                    'tipo_calculo' => $tecnico['tipo_comision'],
                    'valor_comision_config' => $tecnico['valor_comision'],
                    'comision_calculada' => $comisionCalculada,
                    'tiempo_invertido' => $solicitud['tiempo_invertido_minutos'],
                    'observacion' => 'Diagnóstico sin reparación - Aprobado por admin',
                    'pagado' => false
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error al procesar solicitud'
                ]);
            }

            // TODO: Notificar al técnico de la decisión

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $decision === 'aprobar' ? 'Solicitud aprobada' : 'Solicitud rechazada'
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en procesarSolicitudCobro: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error del sistema: ' . $e->getMessage()
            ]);
        }
    }

    // Aprobar/Rechazar excepción de garantía vencida
    public function procesarExcepcionGarantia()
    {
        $excepcionId = $this->request->getPost('excepcion_id');
        $decision = $this->request->getPost('decision'); // aprobar_gratis | aprobar_descuento_50 | rechazar
        $comentario = $this->request->getPost('comentario_admin');

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $excepcion = $this->excepcionGarantiaModel->find($excepcionId);

            // Actualizar excepción
            $dataExcepcion = [
                'estado' => in_array($decision, ['aprobar_gratis', 'aprobar_descuento_50']) ? 'aprobada' : 'rechazada',
                'admin_revisor_id' => session()->get('user_id'),
                'decision_admin' => $decision,
                'comentario_admin' => $comentario,
                'fecha_revision' => date('Y-m-d H:i:s')
            ];

            $this->excepcionGarantiaModel->update($excepcionId, $dataExcepcion);

            if (in_array($decision, ['aprobar_gratis', 'aprobar_descuento_50'])) {
                // Reactivar garantía o crear nueva orden con descuento
                $garantia = $this->garantiaModel->find($excepcion['garantia_vencida_id']);

                if ($decision === 'aprobar_gratis') {
                    // Tratar como reclamo normal de garantía
                    // Crear nueva orden, asignar al mismo técnico, etc.
                    $this->crearOrdenReclamoExcepcion($excepcion, $garantia, 0);
                } else {
                    // Crear orden con 50% de descuento
                    $this->crearOrdenReclamoExcepcion($excepcion, $garantia, 0.5);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error al procesar excepción'
                ]);
            }

            // TODO: Notificar a recepcionista y cliente

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Excepción procesada exitosamente'
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en procesarExcepcionGarantia: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error del sistema: ' . $e->getMessage()
            ]);
        }
    }

    // ========================================
    // GESTIÓN DE TÉCNICOS
    // ========================================

    public function gestionarTecnicos()
    {
        $tecnicos = $this->tecnicoModel->getTecnicosActivos();

        // Obtener rendimiento de cada técnico
        $tecnicosConRendimiento = [];
        foreach ($tecnicos as $tecnico) {
            $rendimiento = $this->tecnicoModel->getRendimientoTecnico($tecnico['id']);
            $especialidad = $this->tecnicoModel->getEspecialidadTecnico($tecnico['id']);
            
            $tecnicosConRendimiento[] = array_merge($tecnico, [
                'rendimiento' => $rendimiento,
                'especialidades' => $especialidad
            ]);
        }

        $data = [
            'titulo' => 'Gestión de Técnicos',
            'tecnicos' => $tecnicosConRendimiento
        ];

        return view('admin/gestionar_tecnicos', $data);
    }

    // Ver detalle de rendimiento de un técnico
    public function verRendimientoTecnico($tecnicoId)
    {
        $tecnico = $this->tecnicoModel->find($tecnicoId);

        if (!$tecnico) {
            return redirect()->to('admin/tecnicos')->with('error', 'Técnico no encontrado');
        }

        $mes = $this->request->getGet('mes') ?? date('m');
        $anio = $this->request->getGet('anio') ?? date('Y');

        $fechaInicio = "$anio-$mes-01";
        $fechaFin = date('Y-m-t', strtotime($fechaInicio));

        $rendimiento = $this->tecnicoModel->getRendimientoTecnico($tecnicoId, $fechaInicio, $fechaFin);
        $especialidades = $this->tecnicoModel->getEspecialidadTecnico($tecnicoId);
        $comisiones = $this->comisionModel->getResumenComisiones($tecnicoId, $mes, $anio);

        // Obtener reclamos de garantía
        $reclamos = $this->reclamoModel->getReclamosPorTecnico($tecnicoId);

        $data = [
            'titulo' => 'Rendimiento: ' . $tecnico['nombres'] . ' ' . $tecnico['apellidos'],
            'tecnico' => $tecnico,
            'rendimiento' => $rendimiento,
            'especialidades' => $especialidades,
            'comisiones' => $comisiones,
            'reclamos' => $reclamos,
            'mes' => $mes,
            'anio' => $anio
        ];

        return view('admin/rendimiento_tecnico', $data);
    }

    // Pagar comisiones
    public function pagarComisiones()
    {
        $tecnicoId = $this->request->getPost('tecnico_id');
        $comisionesIds = $this->request->getPost('comisiones_ids'); // Array de IDs
        $metodoPago = $this->request->getPost('metodo_pago');
        $fechaPago = $this->request->getPost('fecha_pago') ?? date('Y-m-d');

        if (empty($comisionesIds)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Seleccione al menos una comisión'
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            foreach ($comisionesIds as $comisionId) {
                $this->comisionModel->update($comisionId, [
                    'pagado' => true,
                    'fecha_pago' => $fechaPago,
                    'metodo_pago' => $metodoPago
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error al registrar pagos'
                ]);
            }

            // TODO: Generar comprobante/recibo

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Comisiones pagadas exitosamente'
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en pagarComisiones: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error del sistema: ' . $e->getMessage()
            ]);
        }
    }

    // ========================================
    // REPORTES Y ESTADÍSTICAS
    // ========================================

    public function reportes()
    {
        $tipo = $this->request->getGet('tipo') ?? 'general'; // general | problemas | checklist | garantias | financiero

        $data = [
            'titulo' => 'Reportes y Estadísticas',
            'tipo' => $tipo
        ];

        switch ($tipo) {
            case 'problemas':
                $data['estadisticas'] = $this->problemasModel->getEstadisticasProblemas();
                break;

            case 'checklist':
                $data['estadisticas'] = $this->checklistItemModel->getEstadisticasChecklist();
                break;

            case 'garantias':
                $data['estadisticas'] = $this->getEstadisticasGarantias();
                break;

            case 'financiero':
                $data['estadisticas'] = $this->getEstadisticasFinancieras();
                break;

            default:
                $data['estadisticas'] = $this->getEstadisticasGenerales();
        }

        return view('admin/reportes', $data);
    }

    // ========================================
    // CONFIGURACIÓN DEL SISTEMA
    // ========================================

    public function configuracion()
    {
        $seccion = $this->request->getGet('seccion') ?? 'general';

        $data = [
            'titulo' => 'Configuración del Sistema',
            'seccion' => $seccion
        ];

        switch ($seccion) {
            case 'politicas':
                $data['politicas'] = $this->politicaRevisionModel->findAll();
                break;

            case 'urgencias':
                $data['urgencias'] = $this->urgenciaModel->findAll();
                break;

            case 'tipos_dispositivo':
                $data['tipos'] = $this->tipoDispositivoModel->findAll();
                break;

            case 'marcas':
                $data['marcas'] = $this->marcaModel->getMarcasConConteo();
                break;

            case 'accesorios':
                $data['accesorios'] = $this->accesorioModel->findAll();
                break;

            case 'tipos_garantia':
                $data['tipos_garantia'] = $this->tipoGarantiaModel->findAll();
                break;

            case 'checklist':
                $data['items'] = $this->checklistItemModel->findAll();
                break;

            case 'problemas':
                $data['problemas'] = $this->problemasModel->findAll();
                break;
        }

        return view('admin/configuracion', $data);
    }

    // Activar política de revisión
    public function activarPolitica()
    {
        $politicaId = $this->request->getPost('politica_id');

        if ($this->politicaRevisionModel->activarPolitica($politicaId)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Política activada exitosamente'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error al activar política'
        ]);
    }

    // Guardar/Editar urgencia
    public function guardarUrgencia()
    {
        $urgenciaId = $this->request->getPost('id');

        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'descripcion' => $this->request->getPost('descripcion'),
            'recargo' => $this->request->getPost('recargo'),
            'tiempo_espera' => $this->request->getPost('tiempo_espera'),
            'color_hex' => $this->request->getPost('color_hex'),
            'orden_prioridad' => $this->request->getPost('orden_prioridad'),
            'activo' => $this->request->getPost('activo') ?? 1
        ];

        if ($urgenciaId) {
            $result = $this->urgenciaModel->update($urgenciaId, $data);
            $message = 'Urgencia actualizada';
        } else {
            $result = $this->urgenciaModel->insert($data);
            $message = 'Urgencia creada';
        }

        if ($result) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error al guardar urgencia',
            'errors' => $this->urgenciaModel->errors()
        ]);
    }

    // Guardar/Editar tipo de dispositivo
    public function guardarTipoDispositivo()
    {
        $tipoId = $this->request->getPost('id');

        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'icono' => $this->request->getPost('icono'),
            'activo' => $this->request->getPost('activo') ?? 1
        ];

        if ($tipoId) {
            $result = $this->tipoDispositivoModel->update($tipoId, $data);
            $message = 'Tipo actualizado';
        } else {
            $result = $this->tipoDispositivoModel->insert($data);
            $message = 'Tipo creado';
        }

        if ($result) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error al guardar tipo',
            'errors' => $this->tipoDispositivoModel->errors()
        ]);
    }

    // Guardar marca
    public function guardarMarca()
    {
        $marcaId = $this->request->getPost('id');

        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'tipo_dispositivo_id' => $this->request->getPost('tipo_dispositivo_id'),
            'activo' => $this->request->getPost('activo') ?? 1
        ];

        if ($marcaId) {
            $result = $this->marcaModel->update($marcaId, $data);
            $message = 'Marca actualizada';
        } else {
            $result = $this->marcaModel->insert($data);
            $message = 'Marca creada';
        }

        if ($result) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error al guardar marca',
            'errors' => $this->marcaModel->errors()
        ]);
    }

    // Guardar modelo
    public function guardarModelo()
    {
        $modeloId = $this->request->getPost('id');

        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'marca_id' => $this->request->getPost('marca_id'),
            'activo' => $this->request->getPost('activo') ?? 1
        ];

        if ($modeloId) {
            $result = $this->modeloModel->update($modeloId, $data);
            $message = 'Modelo actualizado';
        } else {
            $result = $this->modeloModel->insert($data);
            $message = 'Modelo creado';
        }

        if ($result) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error al guardar modelo',
            'errors' => $this->modeloModel->errors()
        ]);
    }

    // Guardar item de checklist
    public function guardarChecklistItem()
    {
        $itemId = $this->request->getPost('id');

        $data = [
            'tipo_dispositivo_id' => $this->request->getPost('tipo_dispositivo_id') ?: null,
            'categoria' => $this->request->getPost('categoria'),
            'nombre' => $this->request->getPost('nombre'),
            'descripcion' => $this->request->getPost('descripcion'),
            'es_critico' => $this->request->getPost('es_critico') ?? 0,
            'requiere_foto' => $this->request->getPost('requiere_foto') ?? 0,
            'orden_visualizacion' => $this->request->getPost('orden_visualizacion') ?? 0,
            'activo' => $this->request->getPost('activo') ?? 1
        ];

        if ($itemId) {
            $result = $this->checklistItemModel->update($itemId, $data);
            $message = 'Item actualizado';
        } else {
            $result = $this->checklistItemModel->insert($data);
            $message = 'Item creado';
        }

        if ($result) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error al guardar item',
            'errors' => $this->checklistItemModel->errors()
        ]);
    }

    // Guardar problema común
    public function guardarProblemaComun()
    {
        $problemaId = $this->request->getPost('id');

        $data = [
            'tipo_dispositivo_id' => $this->request->getPost('tipo_dispositivo_id') ?: null,
            'nombre' => $this->request->getPost('nombre'),
            'categoria' => $this->request->getPost('categoria'),
            'descripcion_tecnica' => $this->request->getPost('descripcion_tecnica'),
            'solucion_sugerida' => $this->request->getPost('solucion_sugerida'),
            'tiempo_promedio_reparacion' => $this->request->getPost('tiempo_promedio_reparacion'),
            'costo_promedio' => $this->request->getPost('costo_promedio'),
            'activo' => $this->request->getPost('activo') ?? 1
        ];

        if ($problemaId) {
            $result = $this->problemasModel->update($problemaId, $data);
            $message = 'Problema actualizado';
        } else {
            $result = $this->problemasModel->insert($data);
            $message = 'Problema creado';
        }

        if ($result) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => $message
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error al guardar problema',
            'errors' => $this->problemasModel->errors()
        ]);
    }

    // ========================================
    // ÓRDENES PROBLEMÁTICAS
    // ========================================

    public function ordenesProblematicas()
    {
        $ordenes = $this->ordenModel->getOrdenesProblematicas();

        $data = [
            'titulo' => 'Órdenes sin Avance',
            'ordenes' => $ordenes
        ];

        return view('admin/ordenes_problematicas', $data);
    }

    // Reasignar técnico de un dispositivo
    public function reasignarTecnico()
    {
        $dispositivoId = $this->request->getPost('dispositivo_id');
        $nuevoTecnicoId = $this->request->getPost('tecnico_id');
        $motivo = $this->request->getPost('motivo');

        $dispositivo = $this->dispositivoModel->find($dispositivoId);
        $tecnicoAnterior = $dispositivo['tecnico_id'];
if ($this->dispositivoModel->update($dispositivoId, ['tecnico_id' => $nuevoTecnicoId])) {
        // Registrar en historial
        $this->historialClienteModel->registrarCambio(
            $dispositivoId,
            session()->get('user_id'),
            'tecnico_' . $tecnicoAnterior,
            'tecnico_' . $nuevoTecnicoId,
            'Reasignado por admin: ' . $motivo,
            false // No visible para cliente
        );

        // TODO: Notificar a ambos técnicos

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Técnico reasignado exitosamente'
        ]);
    }

    return $this->response->setJSON([
        'status' => 'error',
        'message' => 'Error al reasignar técnico'
    ]);
}

// ========================================
// FINANZAS
// ========================================

public function finanzas()
{
    $mes = $this->request->getGet('mes') ?? date('m');
    $anio = $this->request->getGet('anio') ?? date('Y');

    $ingresosMes = $this->ordenFinalizadaModel->getIngresosDelMes($mes, $anio);
    
    // Órdenes sin cobrar
    $ordenesPendientes = $this->getOrdenesSinCobrar();

    // Comisiones del mes
    $comisionesMes = $this->comisionModel
        ->selectSum('comision_calculada')
        ->where('MONTH(created_at)', $mes)
        ->where('YEAR(created_at)', $anio)
        ->first();

    $data = [
        'titulo' => 'Gestión Financiera',
        'ingresos' => $ingresosMes,
        'ordenes_pendientes' => $ordenesPendientes,
        'comisiones_mes' => $comisionesMes['comision_calculada'] ?? 0,
        'mes' => $mes,
        'anio' => $anio
    ];

    return view('admin/finanzas', $data);
}

// ========================================
// ACCESO A FUNCIONES DE OTROS ROLES
// ========================================

// Admin puede hacer TODO lo que hacen recepcionista y técnico
// Simplemente redirige a las vistas correspondientes con permisos elevados

public function crearOrdenComoAdmin()
{
    // Mismo flujo que recepcionista pero con override de permisos
    $recepcionistaController = new \App\Controllers\RecepcionistaController();
    return $recepcionistaController->crearOrden();
}

public function repararComoAdmin($dispositivoId)
{
    // Mismo flujo que técnico
    $tecnicoController = new \App\Controllers\TecnicoController();
    return $tecnicoController->verDispositivo($dispositivoId);
}

// ========================================
// MÉTODOS AUXILIARES PRIVADOS
// ========================================

private function getTecnicosTopRendimiento($limite = 5)
{
    $db = \Config\Database::connect();

    return $db->table('usuarios u')
        ->select('u.id, u.nombres, u.apellidos,
                  COUNT(DISTINCT d.id) as total_trabajos,
                  SUM(CASE WHEN d.estado_reparacion = "reparado" THEN 1 ELSE 0 END) as reparados,
                  ROUND(SUM(CASE WHEN d.estado_reparacion = "reparado" THEN 1 ELSE 0 END) * 100.0 / COUNT(DISTINCT d.id), 2) as tasa_exito')
        ->join('dispositivos d', 'd.tecnico_id = u.id', 'left')
        ->where('u.role', 'tecnico')
        ->where('u.activo', 1)
        ->where('MONTH(d.created_at)', date('m'))
        ->where('YEAR(d.created_at)', date('Y'))
        ->groupBy('u.id')
        ->orderBy('tasa_exito', 'DESC')
        ->limit($limite)
        ->get()
        ->getResultArray();
}

private function getEstadisticasGarantias()
{
    $db = \Config\Database::connect();

    return [
        'total_activas' => $this->garantiaModel->where('estado', 'activa')->countAllResults(),
        'total_utilizadas' => $this->garantiaModel->where('fue_utilizada', 1)->countAllResults(),
        'tasa_reclamos' => $this->calcularTasaReclamos(),
        'por_tecnico' => $this->getReclamosPorTecnico()
    ];
}

private function calcularTasaReclamos()
{
    $totalGarantias = $this->garantiaModel->countAllResults();
    $totalReclamos = $this->reclamoModel->countAllResults();

    if ($totalGarantias > 0) {
        return round(($totalReclamos / $totalGarantias) * 100, 2);
    }

    return 0;
}

private function getReclamosPorTecnico()
{
    $db = \Config\Database::connect();

    return $db->table('garantias g')
        ->select('u.nombres, u.apellidos,
                  COUNT(DISTINCT g.id) as garantias_dadas,
                  COUNT(DISTINCT rg.id) as reclamos_recibidos,
                  ROUND(COUNT(DISTINCT rg.id) * 100.0 / NULLIF(COUNT(DISTINCT g.id), 0), 2) as tasa_reclamo')
        ->join('usuarios u', 'u.id = g.tecnico_id')
        ->join('reclamos_garantia rg', 'rg.garantia_id = g.id', 'left')
        ->groupBy('u.id')
        ->orderBy('tasa_reclamo', 'DESC')
        ->get()
        ->getResultArray();
}

private function getEstadisticasFinancieras()
{
    $mesActual = date('m');
    $anioActual = date('Y');

    $ingresos = $this->ordenFinalizadaModel->getIngresosDelMes($mesActual, $anioActual);

    // Calcular costos
    $comisiones = $this->comisionModel
        ->selectSum('comision_calculada')
        ->where('MONTH(created_at)', $mesActual)
        ->where('YEAR(created_at)', $anioActual)
        ->first();

    $gananciaNeta = ($ingresos['total_facturado'] ?? 0) - ($comisiones['comision_calculada'] ?? 0);

    return [
        'ingresos_totales' => $ingresos['total_facturado'] ?? 0,
        'cobrado' => $ingresos['total_cobrado'] ?? 0,
        'pendiente_cobro' => $ingresos['total_pendiente'] ?? 0,
        'comisiones_tecnicos' => $comisiones['comision_calculada'] ?? 0,
        'ganancia_neta' => $gananciaNeta,
        'margen_ganancia' => ($ingresos['total_facturado'] ?? 0) > 0 
            ? round(($gananciaNeta / $ingresos['total_facturado']) * 100, 2) 
            : 0
    ];
}

private function getEstadisticasGenerales()
{
    $mesActual = date('m');
    $anioActual = date('Y');

    return [
        'ordenes_mes' => $this->ordenModel
            ->where('MONTH(created_at)', $mesActual)
            ->where('YEAR(created_at)', $anioActual)
            ->countAllResults(),
        
        'dispositivos_mes' => $this->dispositivoModel
            ->where('MONTH(created_at)', $mesActual)
            ->where('YEAR(created_at)', $anioActual)
            ->countAllResults(),
        
        'tasa_reparacion' => $this->calcularTasaReparacionMes($mesActual, $anioActual),
        
        'tiempo_promedio_reparacion' => $this->calcularTiempoPromedioReparacion($mesActual, $anioActual),
        
        'problemas_mas_comunes' => $this->problemasModel->getProblemasMasComunes(10)
    ];
}

private function calcularTasaReparacionMes($mes, $anio)
{
    $total = $this->dispositivoModel
        ->where('MONTH(created_at)', $mes)
        ->where('YEAR(created_at)', $anio)
        ->whereIn('estado_reparacion', ['reparado', 'no_reparado'])
        ->countAllResults();

    $reparados = $this->dispositivoModel
        ->where('MONTH(created_at)', $mes)
        ->where('YEAR(created_at)', $anio)
        ->where('estado_reparacion', 'reparado')
        ->countAllResults();

    return $total > 0 ? round(($reparados / $total) * 100, 2) : 0;
}

private function calcularTiempoPromedioReparacion($mes, $anio)
{
    $db = \Config\Database::connect();

    $resultado = $db->table('dispositivos')
        ->select('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as horas_promedio')
        ->where('MONTH(created_at)', $mes)
        ->where('YEAR(created_at)', $anio)
        ->where('estado_reparacion', 'reparado')
        ->get()
        ->getRowArray();

    return round($resultado['horas_promedio'] ?? 0, 1);
}

private function getOrdenesSinCobrar()
{
    $db = \Config\Database::connect();

    return $db->table('ordenes_finalizadas of')
        ->select('of.*, ot.codigo_orden, c.nombres, c.apellidos, c.telefono')
        ->join('ordenes_trabajo ot', 'ot.id = of.orden_id')
        ->join('clientes c', 'c.id = ot.cliente_id')
        ->where('of.saldo_pendiente >', 0)
        ->orderBy('of.fecha_finalizacion', 'ASC')
        ->get()
        ->getResultArray();
}

private function crearOrdenReclamoExcepcion($excepcion, $garantia, $descuento = 0)
{
    // Crear nueva orden para reclamo de garantía vencida aprobada
    $dispositivo = $this->dispositivoModel->find($excepcion['dispositivo_id']);

    $nuevaOrden = [
        'codigo_orden' => $this->ordenModel->generarCodigo(),
        'cliente_id' => $garantia['cliente_id'], // Obtener del dispositivo
        'usuario_id' => session()->get('user_id'),
        'es_reclamo_garantia' => true,
        'orden_original_id' => $garantia['orden_original_id'],
        'estado_global' => 'en_proceso'
    ];

    $nuevaOrdenId = $this->ordenModel->insert($nuevaOrden);

    // Actualizar dispositivo
    $this->dispositivoModel->update($excepcion['dispositivo_id'], [
        'tecnico_id' => $garantia['tecnico_id'],
        'estado_reparacion' => 'en_reparacion'
    ]);

    // Si hay descuento, registrarlo
    if ($descuento > 0) {
        // TODO: Agregar lógica de descuento en la orden finalizada
    }

    return $nuevaOrdenId;
}}