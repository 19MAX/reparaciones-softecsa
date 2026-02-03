<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DispositivoModel;
use App\Models\TecnicoModel;
use App\Models\ProblemasComunesModel;
use App\Models\DispositivoProblemasModel;
use App\Models\HistorialDiagnosticoModel;
use App\Models\GarantiaModel;
use App\Models\TipoGarantiaModel;
use App\Models\ReclamoGarantiaModel;
use App\Models\ComisionTecnicoModel;
use App\Models\DispositivoImagenModel;
use App\Models\SolicitudCobroDiagnosticoModel;
use App\Models\PoliticaRevisionModel;

class TecnicoController extends BaseController
{
    protected $dispositivoModel;
    protected $tecnicoModel;
    protected $problemasModel;
    protected $dispositivoProblemasModel;
    protected $historialDiagnosticoModel;
    protected $garantiaModel;
    protected $tipoGarantiaModel;
    protected $reclamoModel;
    protected $comisionModel;
    protected $imagenModel;
    protected $solicitudCobroModel;
    protected $politicaRevisionModel;

    public function __construct()
    {
        $this->dispositivoModel = new DispositivoModel();
        $this->tecnicoModel = new TecnicoModel();
        $this->problemasModel = new ProblemasComunesModel();
        $this->dispositivoProblemasModel = new DispositivoProblemasModel();
        $this->historialDiagnosticoModel = new HistorialDiagnosticoModel();
        $this->garantiaModel = new GarantiaModel();
        $this->tipoGarantiaModel = new TipoGarantiaModel();
        $this->reclamoModel = new ReclamoGarantiaModel();
        $this->comisionModel = new ComisionTecnicoModel();
        $this->imagenModel = new DispositivoImagenModel();
        $this->solicitudCobroModel = new SolicitudCobroDiagnosticoModel();
        $this->politicaRevisionModel = new PoliticaRevisionModel();
    }

    // Dashboard principal del técnico
    public function index()
    {
        $tecnicoId = session()->get('user_id');

        // Obtener dispositivos asignados por estado
        $pendientes = $this->dispositivoModel->getDispositivosPorTecnico($tecnicoId, 'pendiente');
        $enProceso = $this->dispositivoModel->getDispositivosPorTecnico($tecnicoId, 'en_diagnostico');
        $enReparacion = $this->dispositivoModel->getDispositivosPorTecnico($tecnicoId, 'en_reparacion');
        $esperandoCliente = $this->dispositivoModel
            ->where('tecnico_id', $tecnicoId)
            ->where('estado_diagnostico', 'diagnosticado')
            ->where('cliente_autoriza_reparacion', null)
            ->findAll();

        // Comisiones pendientes de cobro
        $comisionesPendientes = $this->comisionModel->getResumenComisiones($tecnicoId);

        // Reclamos de garantía pendientes de evaluar
        $reclamosPendientes = $this->reclamoModel->getReclamosPorTecnico($tecnicoId, 'pendiente_evaluacion');

        // Rendimiento del mes
        $rendimiento = $this->tecnicoModel->getRendimientoTecnico(
            $tecnicoId,
            date('Y-m-01'),
            date('Y-m-t')
        );

        $data = [
            'titulo' => 'Dashboard Técnico',
            'pendientes' => $pendientes,
            'en_proceso' => $enProceso,
            'en_reparacion' => $enReparacion,
            'esperando_cliente' => $esperandoCliente,
            'comisiones' => $comisionesPendientes,
            'reclamos_pendientes' => $reclamosPendientes,
            'rendimiento' => $rendimiento
        ];

        return view('tecnico/dashboard', $data);
    }

    // Listar todos los trabajos del técnico
    public function misTrabajos()
    {
        $tecnicoId = session()->get('user_id');
        $filtroEstado = $this->request->getGet('estado');

        $dispositivos = $this->dispositivoModel->getDispositivosPorTecnico($tecnicoId, $filtroEstado);

        $data = [
            'titulo' => 'Mis Trabajos',
            'dispositivos' => $dispositivos,
            'filtro_estado' => $filtroEstado
        ];

        return view('tecnico/mis_trabajos', $data);
    }

    // Ver dispositivos disponibles sin asignar (pool común)
    public function dispositivosDisponibles()
    {
        $dispositivos = $this->dispositivoModel->getDispositivosSinAsignar();

        $data = [
            'titulo' => 'Trabajos Disponibles',
            'dispositivos' => $dispositivos
        ];

        return view('tecnico/dispositivos_disponibles', $data);
    }

    // Tomar un dispositivo del pool
    public function tomarDispositivo()
    {
        $dispositivoId = $this->request->getPost('dispositivo_id');
        $tecnicoId = session()->get('user_id');

        // Verificar que el dispositivo no esté asignado
        $dispositivo = $this->dispositivoModel->find($dispositivoId);

        if ($dispositivo && !$dispositivo['tecnico_id']) {
            $updated = $this->dispositivoModel->update($dispositivoId, [
                'tecnico_id' => $tecnicoId,
                'estado_reparacion' => 'asignado'
            ]);

            if ($updated) {
                // Registrar en historial
                $this->registrarHistorial($dispositivoId, 'asignado', 'pendiente', 'Técnico tomó el trabajo');

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Dispositivo asignado exitosamente'
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'No se pudo asignar el dispositivo'
        ]);
    }

    // Ver detalle completo de un dispositivo
    public function verDispositivo($dispositivoId)
    {
        $tecnicoId = session()->get('user_id');
        $dispositivo = $this->dispositivoModel->getDispositivoCompleto($dispositivoId);

        if (!$dispositivo) {
            return redirect()->to('tecnico')->with('error', 'Dispositivo no encontrado');
        }

        // Verificar que el dispositivo esté asignado a este técnico
        if ($dispositivo['tecnico_id'] != $tecnicoId) {
            return redirect()->to('tecnico')->with('error', 'No tienes permiso para ver este dispositivo');
        }

        // Obtener historial de diagnósticos previos de este problema
        $problemasRelacionados = [];
        if (!empty($dispositivo['problemas'])) {
            $problemaId = $dispositivo['problemas'][0]['problema_comun_id'] ?? null;
            if ($problemaId) {
                $problemasRelacionados = $this->obtenerSolucionesAnteriores($problemaId);
            }
        }

        $data = [
            'titulo' => 'Dispositivo #' . $dispositivo['id'],
            'dispositivo' => $dispositivo,
            'soluciones_anteriores' => $problemasRelacionados
        ];

        return view('tecnico/dispositivo_detalle', $data);
    }

    // Iniciar diagnóstico
    public function iniciarDiagnostico()
    {
        $dispositivoId = $this->request->getPost('dispositivo_id');
        $tecnicoId = session()->get('user_id');

        $updated = $this->dispositivoModel->update($dispositivoId, [
            'estado_diagnostico' => 'en_revision',
            'estado_reparacion' => 'en_diagnostico'
        ]);

        if ($updated) {
            // Crear registro en historial_diagnostico
            $this->historialDiagnosticoModel->insert([
                'dispositivo_id' => $dispositivoId,
                'tecnico_id' => $tecnicoId,
                'fecha_inicio_diagnostico' => date('Y-m-d H:i:s')
            ]);

            $this->registrarHistorial($dispositivoId, 'en_diagnostico', 'asignado', 'Inicio de diagnóstico');

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Diagnóstico iniciado'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error al iniciar diagnóstico'
        ]);
    }

    // Guardar diagnóstico
    public function guardarDiagnostico()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'dispositivo_id' => 'required|integer',
            'diagnostico_encontrado' => 'required|in_list[0,1]',
            'diagnostico_detalle' => 'required|min_length[20]',
            'diagnostico_cliente' => 'required|min_length[20]',
            'tiempo_invertido' => 'required|integer'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors()
            ]);
        }

        $dispositivoId = $this->request->getPost('dispositivo_id');
        $diagnosticoEncontrado = $this->request->getPost('diagnostico_encontrado');
        $tecnicoId = session()->get('user_id');

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Actualizar dispositivo
            $dataDispositivo = [
                'estado_diagnostico' => $diagnosticoEncontrado ? 'diagnosticado' : 'sin_diagnostico',
                'tiempo_diagnostico_minutos' => $this->request->getPost('tiempo_invertido'),
                'diagnostico_encontrado' => $diagnosticoEncontrado,
                'diagnostico_detalle' => $this->request->getPost('diagnostico_detalle'),
                'diagnostico_cliente' => $this->request->getPost('diagnostico_cliente'),
                'costo_diagnostico_estimado' => $this->request->getPost('costo_estimado') ?? 0
            ];

            if ($diagnosticoEncontrado) {
                $dataDispositivo['estado_reparacion'] = 'esperando_autorizacion';
            }

            $this->dispositivoModel->update($dispositivoId, $dataDispositivo);

            // Actualizar historial_diagnostico
            $historial = $this->historialDiagnosticoModel
                ->where('dispositivo_id', $dispositivoId)
                ->where('tecnico_id', $tecnicoId)
                ->orderBy('id', 'DESC')
                ->first();

            if ($historial) {
                $this->historialDiagnosticoModel->update($historial['id'], [
                    'fecha_fin_diagnostico' => date('Y-m-d H:i:s'),
                    'tiempo_invertido_minutos' => $this->request->getPost('tiempo_invertido'),
                    'diagnostico_encontrado' => $diagnosticoEncontrado,
                    'detalle_tecnico' => $this->request->getPost('diagnostico_detalle'),
                    'detalle_cliente' => $this->request->getPost('diagnostico_cliente'),
                    'costo_estimado_reparacion' => $this->request->getPost('costo_mano_obra') ?? 0,
                    'costo_estimado_repuestos' => $this->request->getPost('costo_repuestos') ?? 0
                ]);
            }

            // Si encontró problema, actualizar/crear en dispositivo_problemas
            if ($diagnosticoEncontrado) {
                $problemaData = [
                    'dispositivo_id' => $dispositivoId,
                    'problema_comun_id' => $this->request->getPost('problema_comun_id'),
                    'problema_custom' => $this->request->getPost('problema_custom'),
                    'diagnostico_tecnico' => $this->request->getPost('diagnostico_detalle'),
                    'prioridad' => $this->request->getPost('prioridad') ?? 'media'
                ];

                // Verificar si ya existe el problema
                $problemaExistente = $this->dispositivoProblemasModel
                    ->where('dispositivo_id', $dispositivoId)
                    ->first();

                if ($problemaExistente) {
                    $this->dispositivoProblemasModel->update($problemaExistente['id'], $problemaData);
                } else {
                    $this->dispositivoProblemasModel->insert($problemaData);
                }
            }

            // Si NO encontró diagnóstico, evaluar si solicita cobro
            if (!$diagnosticoEncontrado && $this->request->getPost('solicitar_cobro')) {
                $this->crearSolicitudCobro($dispositivoId, $tecnicoId);
            }

            $this->registrarHistorial(
                $dispositivoId,
                $diagnosticoEncontrado ? 'esperando_autorizacion' : 'sin_diagnostico',
                'en_diagnostico',
                $diagnosticoEncontrado ? 'Diagnóstico completado' : 'No se encontró diagnóstico'
            );

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error al guardar diagnóstico'
                ]);
            }

            // TODO: Notificar a recepcionista para contactar cliente

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Diagnóstico guardado exitosamente',
                'diagnostico_encontrado' => $diagnosticoEncontrado
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en guardarDiagnostico: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error del sistema: ' . $e->getMessage()
            ]);
        }
    }

    // Buscar problemas comunes (AJAX para autocompletar)
    public function buscarProblemas()
    {
        $query = $this->request->getGet('q');
        $tipoDispositivoId = $this->request->getGet('tipo_dispositivo_id');

        $problemas = $this->problemasModel->buscarProblemas($query, $tipoDispositivoId);

        return $this->response->setJSON($problemas);
    }

    // Iniciar reparación (después de autorización del cliente)
    public function iniciarReparacion()
    {
        $dispositivoId = $this->request->getPost('dispositivo_id');

        // Verificar que cliente haya autorizado
        $dispositivo = $this->dispositivoModel->find($dispositivoId);

        if (!$dispositivo['cliente_autoriza_reparacion']) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Cliente no ha autorizado la reparación'
            ]);
        }

        $updated = $this->dispositivoModel->update($dispositivoId, [
            'estado_reparacion' => 'en_reparacion'
        ]);

        if ($updated) {
            $this->registrarHistorial($dispositivoId, 'en_reparacion', 'esperando_autorizacion', 'Inicio de reparación');

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Reparación iniciada'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error al iniciar reparación'
        ]);
    }

    // Finalizar reparación
    public function finalizarReparacion()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'dispositivo_id' => 'required|integer',
            'fue_reparado' => 'required|in_list[0,1]',
            'tiempo_invertido' => 'required|integer',
            'costo_mano_obra' => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors()
            ]);
        }

        $dispositivoId = $this->request->getPost('dispositivo_id');
        $fueReparado = $this->request->getPost('fue_reparado');
        $tecnicoId = session()->get('user_id');

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Actualizar dispositivo
            $dataDispositivo = [
                'estado_reparacion' => $fueReparado ? 'reparado' : 'no_reparado'
            ];

            if (!$fueReparado) {
                $dataDispositivo['razon_rechazo'] = $this->request->getPost('razon_no_reparado');
            }

            $this->dispositivoModel->update($dispositivoId, $dataDispositivo);

            // Actualizar dispositivo_problemas
            $problema = $this->dispositivoProblemasModel
                ->where('dispositivo_id', $dispositivoId)
                ->first();

            if ($problema) {
                $this->dispositivoProblemasModel->update($problema['id'], [
                    'fue_reparado' => $fueReparado,
                    'razon_no_reparado' => $this->request->getPost('razon_no_reparado'),
                    'costo_reparacion' => $this->request->getPost('costo_mano_obra'),
                    'tiempo_invertido' => $this->request->getPost('tiempo_invertido')
                ]);
            }

            $this->registrarHistorial(
                $dispositivoId,
                $fueReparado ? 'reparado' : 'no_reparado',
                'en_reparacion',
                $fueReparado ? 'Reparación completada' : 'No se pudo reparar'
            );

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error al finalizar reparación'
                ]);
            }

            // Si fue reparado, redirigir a asignar garantía
            if ($fueReparado) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Reparación finalizada. Asigna garantía.',
                    'redirect' => base_url('tecnico/asignar-garantia/' . $dispositivoId)
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Dispositivo marcado como no reparado'
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en finalizarReparacion: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error del sistema: ' . $e->getMessage()
            ]);
        }
    }

    // Vista para asignar garantía
    public function asignarGarantia($dispositivoId)
    {
        $tecnicoId = session()->get('user_id');
        $dispositivo = $this->dispositivoModel->getDispositivoCompleto($dispositivoId);

        if (!$dispositivo || $dispositivo['tecnico_id'] != $tecnicoId) {
            return redirect()->to('tecnico')->with('error', 'Acceso denegado');
        }

        if ($dispositivo['estado_reparacion'] !== 'reparado') {
            return redirect()->to('tecnico')->with('error', 'Dispositivo no está reparado');
        }

        // Obtener tipos de garantía disponibles según el problema
        $problema = $dispositivo['problemas'][0] ?? null;
        $tiposGarantia = $this->tipoGarantiaModel->getTiposPorProblema($problema['problema_comun_id'] ?? null);

        $data = [
            'titulo' => 'Asignar Garantía',
            'dispositivo' => $dispositivo,
            'tipos_garantia' => $tiposGarantia
        ];

        return view('tecnico/asignar_garantia', $data);
    }

    // Guardar garantía
    public function guardarGarantia()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'dispositivo_id' => 'required|integer',
            'tipo_garantia_id' => 'required|integer',
            'condiciones_seleccionadas' => 'permit_empty'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors()
            ]);
        }

        $dispositivoId = $this->request->getPost('dispositivo_id');
        $tipoGarantiaId = $this->request->getPost('tipo_garantia_id');
        $tecnicoId = session()->get('user_id');

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $dispositivo = $this->dispositivoModel->find($dispositivoId);
            $tipoGarantia = $this->tipoGarantiaModel->find($tipoGarantiaId);

            // Calcular fechas
            $fechaInicio = date('Y-m-d'); // Asumiendo entrega inmediata, o puedes dejarlo null hasta entrega
            $fechaVencimiento = date('Y-m-d', strtotime('+' . $tipoGarantia['dias_garantia'] . ' days'));

            // Obtener problema reparado
            $problema = $this->dispositivoProblemasModel
                ->where('dispositivo_id', $dispositivoId)
                ->where('fue_reparado', 1)
                ->first();

            // Construir texto de condiciones
            $condicionesSeleccionadas = $this->request->getPost('condiciones_seleccionadas') ?? [];
            $textoCondiciones = $tipoGarantia['descripcion_cliente'];

            if (!empty($condicionesSeleccionadas)) {
                $textoCondiciones .= "\n\nCondiciones que debe cumplir:\n";
                foreach ($condicionesSeleccionadas as $condicion) {
                    $textoCondiciones .= "- " . $condicion . "\n";
                }
            }

            // Crear garantía
            $garantiaData = [
                'dispositivo_id' => $dispositivoId,
                'orden_original_id' => $dispositivo['orden_id'],
                'tecnico_id' => $tecnicoId,
                'tipo_garantia_id' => $tipoGarantiaId,
                'problema_reparado_id' => $problema['id'] ?? null,
                'fecha_inicio' => $fechaInicio,
                'fecha_vencimiento' => $fechaVencimiento,
                'dias_garantia' => $tipoGarantia['dias_garantia'],
                'estado' => 'activa',
                'condiciones_aceptadas_cliente' => false, // Se marca true al entregar
                'texto_condiciones' => $textoCondiciones,
                'exclusiones' => $tipoGarantia['exclusiones']
            ];

            $garantiaId = $this->garantiaModel->insert($garantiaData);

            // Actualizar dispositivo
            $this->dispositivoModel->update($dispositivoId, [
                'estado_reparacion' => 'listo_entrega',
                'tiene_garantia_activa' => true,
                'garantia_vence_en' => $fechaVencimiento
            ]);

            $this->registrarHistorial($dispositivoId, 'listo_entrega', 'reparado', 'Garantía asignada - Listo para entregar');

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error al guardar garantía'
                ]);
            }

            // TODO: Notificar a recepcionista

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Garantía asignada exitosamente',
                'garantia_id' => $garantiaId
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en guardarGarantia: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error del sistema: ' . $e->getMessage()
            ]);
        }
    }

    // Ver reclamos de garantía asignados
    public function reclamosGarantia()
    {
        $tecnicoId = session()->get('user_id');
        $filtroEstado = $this->request->getGet('estado');

        $reclamos = $this->reclamoModel->getReclamosPorTecnico($tecnicoId, $filtroEstado);

        $data = [
            'titulo' => 'Reclamos de Garantía',
            'reclamos' => $reclamos,
            'filtro_estado' => $filtroEstado
        ];

        return view('tecnico/reclamos_garantia', $data);
    }

    // Ver detalle de reclamo para evaluar
    public function evaluarReclamo($reclamoId)
    {
        $tecnicoId = session()->get('user_id');
        $db = \Config\Database::connect();

        $reclamo = $db->table('reclamos_garantia rg')
            ->select('rg.*, d.*, g.tipo_garantia_id, g.texto_condiciones, g.exclusiones,
                      c.nombres as cliente_nombre, c.apellidos as cliente_apellido, c.telefono,
                      ot.codigo_orden, tg.nombre as tipo_garantia_nombre')
            ->join('dispositivos d', 'd.id = rg.dispositivo_id')
            ->join('garantias g', 'g.id = rg.garantia_id')
            ->join('ordenes_trabajo ot', 'ot.id = d.orden_id')
            ->join('clientes c', 'c.id = ot.cliente_id')
            ->join('tipos_garantia tg', 'tg.id = g.tipo_garantia_id')
            ->where('rg.id', $reclamoId)
            ->where('g.tecnico_id', $tecnicoId)
            ->get()
            ->getRowArray();

        if (!$reclamo) {
            return redirect()->to('tecnico/reclamos-garantia')->with('error', 'Reclamo no encontrado');
        }

        // Obtener reparación original
        $reparacionOriginal = $this->dispositivoProblemasModel
            ->where('dispositivo_id', $reclamo['dispositivo_id'])
            ->where('fue_reparado', 1)
            ->where('es_reparacion_garantia', 0)
            ->first();

        // Obtener imágenes del reclamo
        $imagenesReclamo = $this->imagenModel
            ->where('reclamo_garantia_id', $reclamoId)
            ->findAll();

        $data = [
            'titulo' => 'Evaluar Reclamo #' . $reclamoId,
            'reclamo' => $reclamo,
            'reparacion_original' => $reparacionOriginal,
            'imagenes' => $imagenesReclamo
        ];

        return view('tecnico/evaluar_reclamo', $data);
    }

    // Guardar evaluación de reclamo
    public function guardarEvaluacionReclamo()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'reclamo_id' => 'required|integer',
            'problema_es_mismo' => 'required|in_list[0,1]',
            'problema_cubierto' => 'required|in_list[0,1]',
            'evaluacion_tecnica' => 'required|min_length[20]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors()
            ]);
        }

        $reclamoId = $this->request->getPost('reclamo_id');
        $problemaCubierto = $this->request->getPost('problema_cubierto');
        $tecnicoId = session()->get('user_id');

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $reclamo = $this->reclamoModel->find($reclamoId);

            // Actualizar reclamo
            $dataReclamo = [
                'tecnico_evaluador_id' => $tecnicoId,
                'fecha_evaluacion' => date('Y-m-d H:i:s'),
                'problema_es_mismo' => $this->request->getPost('problema_es_mismo'),
                'evaluacion_tecnica' => $this->request->getPost('evaluacion_tecnica'),
                'problema_cubierto_garantia' => $problemaCubierto,
                'evidencia_mal_uso' => $this->request->getPost('evidencia_mal_uso') ?? false,
                'descripcion_evidencia' => $this->request->getPost('descripcion_evidencia'),
                'estado_reclamo' => $problemaCubierto ? 'aprobado' : 'rechazado'
            ];

            if (!$problemaCubierto) {
                $dataReclamo['razon_rechazo_garantia'] = $this->request->getPost('razon_rechazo');
            }

            $this->reclamoModel->update($reclamoId, $dataReclamo);

            // Si está aprobado, marcar garantía como utilizada y crear nuevo trabajo
            if ($problemaCubierto) {
                //Marcar garantía original como utilizada
                // Marcar garantía original como utilizada
                $this->garantiaModel->update($reclamo['garantia_id'], [
                    'fue_utilizada' => true,
                    'estado' => 'utilizada'
                ]);


                // // Crear nuevo problema en dispositivo_problemas
                $this->dispositivoProblemasModel->insert([
                    'dispositivo_id' => $reclamo['dispositivo_id'],
                    'problema_comun_id' => null, // Mismo problema que antes
                    'diagnostico_inicial' => $reclamo['problema_reportado_cliente'],
                    'diagnostico_tecnico' => $this->request->getPost('evaluacion_tecnica'),
                    'es_reparacion_garantia' => true,
                    'reclamo_garantia_id' => $reclamoId,
                    'costo_reparacion' => 0 // Sin costo para cliente
                ]);

                // Actualizar dispositivo
                $this->dispositivoModel->update($reclamo['dispositivo_id'], [
                    'estado_reparacion' => 'en_reparacion',
                    'veces_reclamada_garantia' => $this->dispositivoModel->find($reclamo['dispositivo_id'])['veces_reclamada_garantia'] + 1
                ]);

                $this->registrarHistorial(
                    $reclamo['dispositivo_id'],
                    'en_reparacion',
                    'entregado',
                    'Reclamo de garantía aprobado - Nueva reparación'
                );
            } else {
                // Si se rechaza, garantía sigue activa
                $this->dispositivoModel->update($reclamo['dispositivo_id'], [
                    'estado_reparacion' => 'entregado' // Vuelve a estado anterior
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Error al guardar evaluación'
                ]);
            }

            // TODO: Notificar a recepcionista del resultado

            return $this->response->setJSON([
                'status' => 'success',
                'message' => $problemaCubierto ? 'Reclamo aprobado - Procede a reparar' : 'Reclamo rechazado - Cliente será notificado',
                'aprobado' => $problemaCubierto
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en guardarEvaluacionReclamo: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error del sistema: ' . $e->getMessage()
            ]);
        }
    }

    // Mis comisiones
    public function misComisiones()
    {
        $tecnicoId = session()->get('user_id');
        $mes = $this->request->getGet('mes') ?? date('m');
        $anio = $this->request->getGet('anio') ?? date('Y');

        $resumen = $this->comisionModel->getResumenComisiones($tecnicoId, $mes, $anio);
        $detalle = $this->comisionModel->getComisionesPorTecnico($tecnicoId);

        $data = [
            'titulo' => 'Mis Comisiones',
            'resumen' => $resumen,
            'comisiones' => $detalle,
            'mes' => $mes,
            'anio' => $anio
        ];

        return view('tecnico/mis_comisiones', $data);
    }

    // Subir foto (AJAX)
    public function subirFoto()
    {
        $validation = \Config\Services::validation();

        $rules = [
            'foto' => 'uploaded[foto]|max_size[foto,2048]|is_image[foto]',
            'dispositivo_id' => 'required|integer',
            'tipo' => 'required|in_list[ingreso,salida,diagnostico,evidencia_garantia,reparacion]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors()
            ]);
        }

        $foto = $this->request->getFile('foto');
        $dispositivoId = $this->request->getPost('dispositivo_id');
        $tipo = $this->request->getPost('tipo');

        if ($foto->isValid() && !$foto->hasMoved()) {
            // Generar nombre único
            $nombreArchivo = $dispositivoId . '_' . $tipo . '_' . time() . '.' . $foto->getExtension();

            // Mover a carpeta uploads
            $foto->move(WRITEPATH . 'uploads/dispositivos', $nombreArchivo);

            // Guardar en BD
            $imagenId = $this->imagenModel->insert([
                'dispositivo_id' => $dispositivoId,
                'tipo' => $tipo,
                'ruta_imagen' => 'uploads/dispositivos/' . $nombreArchivo,
                'descripcion' => $this->request->getPost('descripcion'),
                'tomada_por_usuario_id' => session()->get('user_id')
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Foto subida exitosamente',
                'imagen_id' => $imagenId,
                'url' => base_url('writable/uploads/dispositivos/' . $nombreArchivo)
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Error al subir foto'
        ]);
    }

    // MÉTODOS AUXILIARES PRIVADOS

    private function registrarHistorial($dispositivoId, $estadoNuevo, $estadoAnterior, $comentario)
    {
        $historialModel = new \App\Models\HistorialClienteModel();

        return $historialModel->insert([
            'dispositivo_id' => $dispositivoId,
            'usuario_id' => session()->get('user_id'),
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'comentario' => $comentario,
            'es_visible_cliente' => true
        ]);
    }

    private function obtenerSolucionesAnteriores($problemaId)
    {
        $db = \Config\Database::connect();

        return $db->table('dispositivo_problemas dp')
            ->select('dp.diagnostico_tecnico, dp.tiempo_invertido, dp.costo_reparacion,
                  u.nombres as tecnico_nombre')
            ->join('dispositivos d', 'd.id = dp.dispositivo_id')
            ->join('usuarios u', 'u.id = d.tecnico_id')
            ->where('dp.problema_comun_id', $problemaId)
            ->where('dp.fue_reparado', 1)
            ->orderBy('dp.created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResultArray();
    }

    private function crearSolicitudCobro($dispositivoId, $tecnicoId)
    {
        $dispositivo = $this->dispositivoModel->find($dispositivoId);

        $solicitudData = [
            'dispositivo_id' => $dispositivoId,
            'tecnico_id' => $tecnicoId,
            'tiempo_invertido_minutos' => $dispositivo['tiempo_diagnostico_minutos'],
            'justificacion' => $this->request->getPost('justificacion_cobro'),
            'valor_solicitado' => $this->request->getPost('valor_solicitado'),
            'estado' => 'pendiente'
        ];

        return $this->solicitudCobroModel->insert($solicitudData);
    }
}