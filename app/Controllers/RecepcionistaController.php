<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ClienteModel;
use App\Models\OrdenTrabajoModel;
use App\Models\DispositivoModel;
use App\Models\TipoDispositivoModel;
use App\Models\MarcaModel;
use App\Models\ModeloDispositivoModel;
use App\Models\UrgenciaModel;
use App\Models\TecnicoModel;
use App\Models\ChecklistItemModel;
use App\Models\AccesorioModel;
use App\Models\ProblemasComunesModel;
use App\Models\GarantiaModel;
use App\Models\ReclamoGarantiaModel;
use App\Models\PoliticaRevisionModel;

class RecepcionistaController extends BaseController
{
    protected $clienteModel;
    protected $ordenModel;
    protected $dispositivoModel;
    protected $tipoDispositivoModel;
    protected $marcaModel;
    protected $modeloModel;
    protected $urgenciaModel;
    protected $tecnicoModel;
    protected $checklistItemModel;
    protected $accesorioModel;
    protected $problemasModel;
    protected $garantiaModel;
    protected $reclamoModel;
    protected $politicaRevisionModel;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
        $this->ordenModel = new OrdenTrabajoModel();
        $this->dispositivoModel = new DispositivoModel();
        $this->tipoDispositivoModel = new TipoDispositivoModel();
        $this->marcaModel = new MarcaModel();
        $this->modeloModel = new ModeloDispositivoModel();
        $this->urgenciaModel = new UrgenciaModel();
        $this->tecnicoModel = new TecnicoModel();
        $this->checklistItemModel = new ChecklistItemModel();
        $this->accesorioModel = new AccesorioModel();
        $this->problemasModel = new ProblemasComunesModel();
        $this->garantiaModel = new GarantiaModel();
        $this->reclamoModel = new ReclamoGarantiaModel();
        $this->politicaRevisionModel = new PoliticaRevisionModel();
    }

    // Vista principal del recepcionista
    public function index()
    {
        $data = [
            'titulo' => 'Dashboard Recepcionista',
            'ordenes_hoy' => $this->ordenModel->getOrdenesHoy(),
            'pendientes_entrega' => $this->ordenModel->getPendientesEntrega(),
            'reclamos_pendientes' => $this->reclamoModel->getPendientes()
        ];

        return view('recepcionista/dashboard_prueba', $data);
    }

    // Vista para crear nueva orden
    public function crearOrden()
    {
        $data = [
            'titulo' => 'Nueva Orden de Trabajo',
            'tiposDispositivos' => $this->tipoDispositivoModel->where('activo', 1)->findAll(),
            'urgencias' => $this->urgenciaModel->where('activo', 1)->orderBy('orden_prioridad')->findAll(),
            'tecnicos' => $this->tecnicoModel->getTecnicosActivos(),
            'accesorios' => $this->accesorioModel->where('activo', 1)->findAll(),
            'politicaRevision' => $this->politicaRevisionModel->getPoliticaActiva()
        ];

        return view('recepcionista/crear_orden', $data);
    }

    // Guardar orden (AJAX o POST)
    public function guardarOrden()
    {
        // Validación
        $validation = \Config\Services::validation();

        $rules = [
            'cliente_id' => 'required|integer',
            'urgencia_id' => 'required|integer',
            'devices' => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors()
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Crear orden
            $ordenData = [
                'codigo_orden' => $this->ordenModel->generarCodigo(),
                'cliente_id' => $this->request->getPost('cliente_id'),
                'usuario_id' => session()->get('user_id'),
                'urgencia_id' => $this->request->getPost('urgencia_id'),
                'estado_global' => 'pendiente',
                'mano_obra_aproximado' => $this->request->getPost('valor_mano_obra_aproximado') ?? 0,
                'repuestos_aproximado' => $this->request->getPost('valor_repuesto_aproximado') ?? 0,
                'observaciones_generales' => $this->request->getPost('observaciones_generales')
            ];

            $ordenId = $this->ordenModel->insert($ordenData);

            // Obtener datos de urgencia para calcular fecha estimada
            $urgencia = $this->urgenciaModel->find($ordenData['urgencia_id']);

            // Procesar dispositivos
            $devices = $this->request->getPost('devices');
            $fechasEstimadas = [];

            foreach ($devices as $device) {
                // Calcular fecha estimada para este dispositivo
                $fechaEstimada = date('Y-m-d', strtotime('+' . $urgencia['tiempo_espera'] . ' days'));
                $fechasEstimadas[] = $fechaEstimada;

                $dispositivoData = [
                    'orden_id' => $ordenId,
                    'tipo_dispositivo_id' => $device['tipo_dispositivo_id'],
                    'marca_id' => $device['marca_id'] ?? null,
                    'modelo_id' => $device['modelo_id'] ?? null,
                    'marca_custom' => $device['marca_custom'] ?? $device['marca'] ?? null,
                    'modelo_custom' => $device['modelo_custom'] ?? $device['modelo'] ?? null,
                    'serie_imei' => $device['serie_imei'] ?? null,
                    'tipo_pass' => $device['tipo_pass'] ?? 'ninguno',
                    'pass_code' => $device['pass_code'] ?? null,
                    'patron_data' => $device['patron_data'] ?? null,
                    'problema_reportado' => $device['problema_reportado'],
                    'observaciones' => $device['observaciones'] ?? null,
                    'tecnico_id' => $device['tecnico_id'] ?? null,
                    'estado_reparacion' => 'pendiente',
                    'estado_diagnostico' => 'pendiente',
                    'fecha_estimada_entrega' => $fechaEstimada
                ];

                $dispositivoId = $this->dispositivoModel->insert($dispositivoData);

                // Guardar problema reportado en dispositivo_problemas
                if (!empty($device['problema_comun_id'])) {
                    $this->guardarProblemaDispositivo($dispositivoId, $device);
                }

                // Guardar checklist si existe
                if (!empty($device['checklist'])) {
                    $this->guardarChecklistDispositivo($dispositivoId, $device['checklist']);
                }

                // Guardar accesorios si existen
                if (!empty($device['accesorios'])) {
                    $this->guardarAccesoriosDispositivo($dispositivoId, $device['accesorios']);
                }
            }

            // Actualizar fecha estimada de la orden (la más lejana)
            $this->ordenModel->update($ordenId, [
                'fecha_estimada_entrega' => max($fechasEstimadas)
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error al guardar la orden'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Orden creada exitosamente',
                'orden_id' => $ordenId,
                'codigo_orden' => $ordenData['codigo_orden'],
                'redirect' => base_url('recepcionista/imprimir-orden/' . $ordenId)
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en guardarOrden: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error del sistema: ' . $e->getMessage()
            ]);
        }
    }

    // Buscar cliente por cédula (AJAX)
    public function buscarCliente()
    {
        $cedula = $this->request->getJSON()->cedula;
        $cliente = $this->clienteModel->where('cedula', $cedula)->first();

        if ($cliente) {
            return $this->response->setJSON([
                'status' => 'success',
                'persona' => $cliente,
                'token' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status' => 'not_found',
            'message' => 'Cliente no encontrado',
            'token' => csrf_hash()
        ]);
    }

    // Crear cliente (AJAX)
    public function crearCliente()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'cedula' => 'required|is_unique[clientes.cedula]|min_length[10]|max_length[13]',
            'nombres' => 'required|min_length[3]',
            'apellidos' => 'required|min_length[3]',
            'telefono' => 'required|min_length[10]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors(),
                'token' => csrf_hash()
            ]);
        }

        $data = [
            'cedula' => $this->request->getJSON()->cedula,
            'nombres' => $this->request->getJSON()->nombres,
            'apellidos' => $this->request->getJSON()->apellidos,
            'telefono' => $this->request->getJSON()->telefono,
            'telefono_secundario' => $this->request->getJSON()->telefono_secundario ?? null,
            'email' => $this->request->getJSON()->email ?? null
        ];

        $clienteId = $this->clienteModel->insert($data);

        if ($clienteId) {
            $cliente = $this->clienteModel->find($clienteId);
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Cliente registrado exitosamente',
                'client_data' => $cliente,
                'token' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error al registrar cliente',
            'token' => csrf_hash()
        ]);
    }

    // Actualizar cliente (AJAX)
    public function actualizarCliente()
    {
        $clienteId = $this->request->getJSON()->id;

        $validation = \Config\Services::validation();

        $rules = [
            'nombres' => 'required|min_length[3]',
            'apellidos' => 'required|min_length[3]',
            'telefono' => 'required|min_length[10]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors(),
                'token' => csrf_hash()
            ]);
        }

        $data = [
            'nombres' => $this->request->getJSON()->nombres,
            'apellidos' => $this->request->getJSON()->apellidos,
            'telefono' => $this->request->getJSON()->telefono,
            'telefono_secundario' => $this->request->getJSON()->telefono_secundario ?? null,
            'email' => $this->request->getJSON()->email ?? null
        ];

        if ($this->clienteModel->update($clienteId, $data)) {
            $cliente = $this->clienteModel->find($clienteId);
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Cliente actualizado exitosamente',
                'client_data' => $cliente,
                'token' => csrf_hash()
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error al actualizar cliente',
            'token' => csrf_hash()
        ]);
    }

    // Obtener marcas por tipo de dispositivo (AJAX)
    public function getMarcasPorTipo($tipoId)
    {
        $marcas = $this->marcaModel
            ->where('tipo_dispositivo_id', $tipoId)
            ->where('activo', 1)
            ->orderBy('nombre')
            ->findAll();

        return $this->response->setJSON($marcas);
    }


    public function getMarcasPorTipoGlobal($tipo_id)
    {
        $marcas = $this->marcaModel->where('tipo_dispositivo_id', $tipo_id)->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'marcas' => $marcas
        ]);

    }

    public function getModelosPorMarcaGlobal($marca_id)
    {
        $modelos = $this->modeloModel->where('marca_id', $marca_id)->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'modelos' => $modelos
        ]);
    }

    // Obtener modelos por marca (AJAX)
    public function getModelosPorMarca($marcaId)
    {
        $modelos = $this->modeloModel
            ->where('marca_id', $marcaId)
            ->where('activo', 1)
            ->orderBy('nombre')
            ->findAll();

        return $this->response->setJSON($modelos);
    }

    // Obtener accesorios por tipo de dispositivo (AJAX)
    public function getAccesoriosPorTipo($tipoId = null)
    {
        $accesorios = $this->accesorioModel->getAccesoriosPorTipo($tipoId);
        return $this->response->setJSON($accesorios);
    }

    // Obtener checklist por tipo de dispositivo (AJAX)
    public function getChecklistPorTipo($tipoId = null)
    {
        $checklist = $this->checklistItemModel->getItemsPorTipo($tipoId);
        return $this->response->setJSON($checklist);
    }

    // Consultar estado de orden
    public function consultarEstado()
    {
        $search = $this->request->getGet('search');
        $ordenes = [];

        if ($search) {
            $ordenes = $this->ordenModel->buscarOrden($search);
        }

        $data = [
            'titulo' => 'Consultar Estado',
            'ordenes' => $ordenes,
            'search' => $search
        ];

        return view('recepcionista/consultar_estado', $data);
    }

    // Ver detalle de orden
    public function verOrden($ordenId)
    {
        $orden = $this->ordenModel->getOrdenCompleta($ordenId);

        if (!$orden) {
            return redirect()->to('recepcionista')->with('error', 'Orden no encontrada');
        }

        $data = [
            'titulo' => 'Orden #' . $orden['codigo_orden'],
            'orden' => $orden
        ];

        return view('recepcionista/orden_detalle', $data);
    }

    // Registrar autorización del cliente
    public function registrarAutorizacion()
    {
        $dispositivoId = $this->request->getPost('dispositivo_id');
        $autoriza = $this->request->getPost('autoriza'); // 1 o 0
        $razon = $this->request->getPost('razon_rechazo');

        $data = [
            'cliente_autoriza_reparacion' => $autoriza,
            'fecha_respuesta_cliente' => date('Y-m-d H:i:s'),
            'razon_rechazo' => $razon
        ];

        // Si cliente rechaza, evaluar si cobra revisión
        if (!$autoriza) {
            $dispositivo = $this->dispositivoModel->find($dispositivoId);
            $politica = $this->politicaRevisionModel->getPoliticaActiva();

            $evaluacion = $this->evaluarCobroRevision($dispositivo, $politica);

            $data['cobra_valor_revision'] = $evaluacion['cobra'];
            $data['valor_revision_cobrado'] = $evaluacion['valor'] ?? 0;
            $data['razon_cobro_revision'] = $evaluacion['razon'];
        }

        if ($this->dispositivoModel->update($dispositivoId, $data)) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Respuesta registrada exitosamente',
                'cobra_revision' => $data['cobra_valor_revision'] ?? false,
                'valor_revision' => $data['valor_revision_cobrado'] ?? 0
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error al registrar respuesta'
        ]);
    }

    // Iniciar reclamo de garantía
    public function iniciarReclamoGarantia()
    {
        $data = [
            'titulo' => 'Reclamo de Garantía'
        ];

        return view('recepcionista/reclamo_garantia', $data);
    }

    // Buscar dispositivo para reclamo (AJAX)
    public function buscarDispositivoGarantia()
    {
        $search = $this->request->getPost('search'); // IMEI o código orden

        $dispositivo = $this->dispositivoModel->buscarParaGarantia($search);

        if ($dispositivo) {
            // Verificar si tiene garantía activa
            $garantia = $this->garantiaModel->getGarantiaActiva($dispositivo['id']);

            return $this->response->setJSON([
                'status' => 'success',
                'dispositivo' => $dispositivo,
                'garantia' => $garantia
            ]);
        }

        return $this->response->setJSON([
            'status' => 'not_found',
            'message' => 'Dispositivo no encontrado'
        ]);
    }

    // Guardar reclamo de garantía
    public function guardarReclamoGarantia()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'garantia_id' => 'required|integer',
            'dispositivo_id' => 'required|integer',
            'problema_reportado' => 'required|min_length[10]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors()
            ]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $garantia = $this->garantiaModel->find($this->request->getPost('garantia_id'));

            // Crear nueva orden para el reclamo
            $nuevaOrden = [
                'codigo_orden' => $this->ordenModel->generarCodigo(),
                'cliente_id' => $garantia['cliente_id'], // Obtener del dispositivo original
                'usuario_id' => session()->get('user_id'),
                'es_reclamo_garantia' => true,
                'orden_original_id' => $garantia['orden_original_id'],
                'estado_global' => 'en_evaluacion'
            ];

            $nuevaOrdenId = $this->ordenModel->insert($nuevaOrden);

            // Crear reclamo
            $reclamoData = [
                'garantia_id' => $this->request->getPost('garantia_id'),
                'dispositivo_id' => $this->request->getPost('dispositivo_id'),
                'orden_reclamo_id' => $nuevaOrdenId,
                'fecha_reclamo' => date('Y-m-d H:i:s'),
                'usuario_recepcion_id' => session()->get('user_id'),
                'problema_reportado_cliente' => $this->request->getPost('problema_reportado'),
                'estado_reclamo' => 'pendiente_evaluacion'
            ];

            $reclamoId = $this->reclamoModel->insert($reclamoData);

            // Asignar al mismo técnico original
            $this->dispositivoModel->update($this->request->getPost('dispositivo_id'), [
                'tecnico_id' => $garantia['tecnico_id'],
                'estado_reparacion' => 'en_evaluacion_garantia',
                'veces_reclamada_garantia' => $this->dispositivoModel->find($this->request->getPost('dispositivo_id'))['veces_reclamada_garantia'] + 1
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error al crear reclamo'
                ]);
            }

            // TODO: Notificar al técnico

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Reclamo creado exitosamente',
                'reclamo_id' => $reclamoId,
                'orden_id' => $nuevaOrdenId
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en guardarReclamoGarantia: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error del sistema: ' . $e->getMessage()
            ]);
        }
    }

    // Solicitar excepción de garantía vencida
    public function solicitarExcepcionGarantia()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'garantia_id' => 'required|integer',
            'dispositivo_id' => 'required|integer',
            'justificacion' => 'required|min_length[20]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors()
            ]);
        }

        $garantia = $this->garantiaModel->find($this->request->getPost('garantia_id'));
        $diasVencida = (strtotime('now') - strtotime($garantia['fecha_vencimiento'])) / 86400;

        $data = [
            'garantia_vencida_id' => $this->request->getPost('garantia_id'),
            'dispositivo_id' => $this->request->getPost('dispositivo_id'),
            'dias_vencida' => ceil($diasVencida),
            'justificacion_empleado' => $this->request->getPost('justificacion'),
            'solicitante_id' => session()->get('user_id'),
            'estado' => 'pendiente'
        ];

        $excepcionId = model('ExcepcionGarantiaModel')->insert($data);

        if ($excepcionId) {
            // TODO: Notificar a admin

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Solicitud enviada al administrador',
                'excepcion_id' => $excepcionId
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error al crear solicitud'
        ]);
    }

    // Dispositivos listos para entregar
    public function dispositivosParaEntregar()
    {
        $dispositivos = $this->dispositivoModel->getListosParaEntregar();

        $data = [
            'titulo' => 'Dispositivos para Entregar',
            'dispositivos' => $dispositivos
        ];

        return view('recepcionista/entregar_dispositivo', $data);
    }

    // Procesar entrega
    public function procesarEntrega()
    {
        $dispositivoId = $this->request->getPost('dispositivo_id');
        $montoPagado = $this->request->getPost('monto_pagado');
        $metodoPago = $this->request->getPost('metodo_pago');

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Actualizar dispositivo
            $this->dispositivoModel->update($dispositivoId, [
                'estado_reparacion' => 'entregado',
                'fecha_entrega_real' => date('Y-m-d')
            ]);

            // Actualizar orden si todos los dispositivos están entregados
            $dispositivo = $this->dispositivoModel->find($dispositivoId);
            $todosEntregados = $this->dispositivoModel->verificarTodosEntregados($dispositivo['orden_id']);

            if ($todosEntregados) {
                $this->ordenModel->update($dispositivo['orden_id'], [
                    'estado_global' => 'entregada'
                ]);

                // Crear registro en ordenes_finalizadas si no existe
                $this->finalizarOrden($dispositivo['orden_id'], $montoPagado, $metodoPago);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error al procesar entrega'
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Dispositivo entregado exitosamente'
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en procesarEntrega: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error del sistema: ' . $e->getMessage()
            ]);
        }
    }

    // Imprimir orden (PDF)
    public function imprimirOrden($ordenId)
    {
        $orden = $this->ordenModel->getOrdenCompleta($ordenId);

        if (!$orden) {
            return redirect()->back()->with('error', 'Orden no encontrada');
        }

        // TODO: Generar PDF con librería (TCPDF, FPDF, Dompdf)
        // Por ahora retornar vista HTML para impresión

        $data = [
            'orden' => $orden
        ];

        return view('recepcionista/imprimir_orden', $data);
    }

    // MÉTODOS AUXILIARES PRIVADOS

    private function guardarProblemaDispositivo($dispositivoId, $deviceData)
    {
        $problemaData = [
            'dispositivo_id' => $dispositivoId,
            'problema_comun_id' => $deviceData['problema_comun_id'] ?? null,
            'problema_custom' => $deviceData['problema_custom'] ?? null,
            'diagnostico_inicial' => $deviceData['problema_reportado'],
            'prioridad' => 'media'
        ];

        return model('DispositivoProblemasModel')->insert($problemaData);
    }

    private function guardarChecklistDispositivo($dispositivoId, $checklist)
    {
        $checklistModel = new \App\Models\ChecklistRespuestaModel();

        foreach ($checklist as $item) {
            $checklistModel->insert([
                'dispositivo_id' => $dispositivoId,
                'checklist_item_id' => $item['item_id'],
                'estado' => $item['estado'],
                'observacion' => $item['observacion'] ?? null,
                'requiere_atencion' => $item['requiere_atencion'] ?? false
            ]);
        }
    }

    private function guardarAccesoriosDispositivo($dispositivoId, $accesorios)
    {
        $accesorioModel = new \App\Models\DispositivoAccesorioModel();

        foreach ($accesorios as $accesorio) {
            $accesorioModel->insert([
                'dispositivo_id' => $dispositivoId,
                'accesorio_id' => $accesorio['id'],
                'estado' => $accesorio['estado'],
                'observacion' => $accesorio['observacion'] ?? null
            ]);
        }
    }

    private function evaluarCobroRevision($dispositivo, $politica)
    {
        // Implementar lógica de evaluación según política activa
        // (Ya la describimos en respuestas anteriores)

        if (!$dispositivo['diagnostico_encontrado']) {
            if ($politica['cobra_revision_si_no_hay_diagnostico']) {
                return [
                    'cobra' => true,
                    'valor' => $politica['valor_revision_base'],
                    'razon' => 'No se encontró diagnóstico pero política indica cobrar'
                ];
            }
            return [
                'cobra' => false,
                'razon' => 'No se encontró diagnóstico - No se cobra según política'
            ];
        }

        if ($dispositivo['diagnostico_encontrado'] && !$dispositivo['cliente_autoriza_reparacion']) {
            if ($politica['cobra_revision_si_rechaza_cliente']) {
                return [
                    'cobra' => true,
                    'valor' => $politica['valor_revision_base'],
                    'razon' => 'Cliente rechazó reparación después de diagnóstico'
                ];
            }
        }

        return ['cobra' => false, 'razon' => 'No aplica cobro'];
    }

    private function finalizarOrden($ordenId, $montoPagado, $metodoPago)
    {
        // Calcular totales
        $dispositivos = $this->dispositivoModel->where('orden_id', $ordenId)->findAll();

        $totalManoObra = 0;
        $totalRepuestos = 0;

        foreach ($dispositivos as $disp) {
            $problemas = model('DispositivoProblemasModel')
                ->where('dispositivo_id', $disp['id'])
                ->where('fue_reparado', true)
                ->findAll();

            foreach ($problemas as $prob) {
                $totalManoObra += $prob['costo_reparacion'] ?? 0;
                // $totalRepuestos += obtener de otra tabla si tienes
            }
        }

        $orden = $this->ordenModel->find($ordenId);
        $urgencia = $this->urgenciaModel->find($orden['urgencia_id']);
        $recargoUrgencia = $urgencia['recargo'] ?? 0;

        $totalFacturado = $totalManoObra + $totalRepuestos + $recargoUrgencia;

        $ordenFinalizadaData = [
            'orden_id' => $ordenId,
            'fecha_finalizacion' => date('Y-m-d H:i:s'),
            'total_mano_obra' => $totalManoObra,
            'total_repuestos' => $totalRepuestos,
            'recargo_urgencia' => $recargoUrgencia,
            'total_facturado' => $totalFacturado,
            'abonos_recibidos' => $montoPagado,
            'saldo_pendiente' => $totalFacturado - $montoPagado
        ];

        return model('OrdenFinalizadaModel')->insert($ordenFinalizadaData);
    }


}