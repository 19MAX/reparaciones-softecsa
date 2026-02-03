<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ChecklistDispositivoModel;
use App\Models\ClienteModel;
use App\Models\ConfiguracionModel;
use App\Models\DispositivoModel;
use App\Models\OrdenTrabajoModel;
use BaconQrCode\Common\ErrorCorrectionLevel;
use CodeIgniter\HTTP\ResponseInterface;
// --- IMPORTS CORRECTOS PARA QR CODE (Compatibilidad V4/V5) ---
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
class OrdenController extends BaseController
{

    public $configuracionModel;
    protected $urgenciaModel;
    protected $usuarioModel;
    protected $tipoDispositivoModel;
    protected $ordenTrabajoModel;
    protected $dispositivoModel;


    public function __construct()
    {
        $this->configuracionModel = new ConfiguracionModel();
        $this->urgenciaModel = new \App\Models\UrgenciaModel();
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->tipoDispositivoModel = new \App\Models\TipoDispositivoModel();
        $this->ordenTrabajoModel = new \App\Models\OrdenTrabajoModel();
        $this->dispositivoModel = new \App\Models\DispositivoModel();
    }

    public function index()
    {
        // Obtener todas las órdenes con información básica
        $ordenes = $this->ordenTrabajoModel
            ->select('
            ordenes_trabajo.id,
            ordenes_trabajo.codigo_orden,
            ordenes_trabajo.created_at,
            ordenes_trabajo.estado_global,
            ordenes_trabajo.urgencia_id,
            CONCAT(c.nombres, " ", c.apellidos) as cliente_nombre_completo,
            c.nombres as cliente_nombres,
            c.apellidos as cliente_apellidos,
            u.nombre as nombre_urgencia,
        ')
            ->join('clientes as c', 'c.id = ordenes_trabajo.cliente_id')
            ->join('urgencias as u', 'u.id = ordenes_trabajo.urgencia_id', 'left')
            ->orderBy('ordenes_trabajo.created_at', 'DESC')
            ->findAll();

        // Enriquecer cada orden con el resumen de dispositivos
        foreach ($ordenes as &$orden) {
            $orden['equipos_resumen'] = $this->generarResumenDispositivos($orden['id']);
            $orden['total_dispositivos'] = $this->contarDispositivos($orden['id']);
        }

        return view('admin/ordenes/index', [
            'titulo' => 'Gestión de Órdenes',
            'ordenes' => $ordenes
        ]);
    }

    /**
     * Generar resumen legible de los dispositivos de una orden
     * Ejemplo: "Laptop HP Pavilion, Celular Samsung Galaxy S21"
     */
    private function generarResumenDispositivos($ordenId)
    {

        $dispositivos = $this->dispositivoModel
            ->select('
            dispositivos.id,
            td.nombre as tipo_dispositivo,
            m.nombre as marca,
            mod.nombre as modelo
        ')
            ->join('tipos_dispositivo as td', 'td.id = dispositivos.tipo_dispositivo_id', 'left')
            ->join('marcas as m', 'm.id = dispositivos.marca_id', 'left')
            ->join('modelos as mod', 'mod.id = dispositivos.modelo_id', 'left')
            ->where('dispositivos.orden_id', $ordenId)
            ->findAll();

        if (empty($dispositivos)) {
            return 'Sin dispositivos';
        }

        $resumen = [];
        foreach ($dispositivos as $dispositivo) {
            $partes = [];

            // Tipo de dispositivo (siempre debe existir)
            if (!empty($dispositivo['tipo_dispositivo'])) {
                $partes[] = $dispositivo['tipo_dispositivo'];
            }

            // Marca
            if (!empty($dispositivo['marca'])) {
                $partes[] = $dispositivo['marca'];
            }

            // Modelo
            if (!empty($dispositivo['modelo'])) {
                $partes[] = $dispositivo['modelo'];
            }

            // Si solo hay tipo de dispositivo
            if (count($partes) === 1) {
                $resumen[] = $partes[0];
            } else {
                // Combinar todo
                $resumen[] = implode(' ', $partes);
            }
        }

        return implode(', ', $resumen);
    }

    /**
     * Contar dispositivos de una orden
     */
    private function contarDispositivos($ordenId)
    {
        return $this->dispositivoModel->where('orden_id', $ordenId)->countAllResults();
    }

    public function crear()
    {
        $data = [
            'titulo' => 'Nueva Orden de Trabajo',
            'urgencias' => $this->urgenciaModel->where('activo', 1)->findAll(),
            'tecnicos' => $this->usuarioModel->where('role', 'tecnico')->where('estado', 'activo')->findAll(),
            'tiposDispositivos' => $this->tipoDispositivoModel->where('activo', 1)->findAll(),

            // Traemos los items del checklist activos para dibujarlos en la vista
            // 'checklist_items' => $checklistModel->where('activo', 1)->findAll()
        ];

        return view('admin/ordenes/crear', $data);
    }

    public function guardar()
    {
        // 1. Verificar Sesión
        $usuarioId = session()->get('id_usuario');
        if (empty($usuarioId)) {
            return redirect()->to(base_url('login'))->with('mensaje', 'Tu sesión ha expirado.');
        }

        try {
            // 2. Obtener datos del formulario
            $clienteId = $this->request->getPost('cliente_id');
            $urgenciaId = $this->request->getPost('urgencia_id');
            $prioridadDispositivoId = $this->request->getPost('prioridad_dispositivo_id');
            $devices = $this->request->getPost('devices');
            $valorManoObraAproximado = $this->request->getPost('valor_mano_obra_aproximado') ?? 0.00;
            $valorRepuestoAproximado = $this->request->getPost('valor_repuesto_aproximado') ?? 0.00;

            // 2.1 Obtener el valor de la revisión desde configuración_empresa
            $configuracionModel = new ConfiguracionModel();
            $configuracion = $configuracionModel->first();
            $valorRevision = $configuracion['valor_revision'] ?? 0.00;

            // Preparar datos para repopular el formulario en caso de error
            $data = [
                'cliente_id' => $clienteId,
                'urgencia_id' => $urgenciaId,
                'devices' => $devices
            ];

            // 3. Validación
            $validation = \Config\Services::validation();
            $rules = [
                'cliente_id' => [
                    'label' => 'Cliente',
                    'rules' => 'required|is_not_unique[clientes.id]',
                ],
                'urgencia_id' => [
                    'label' => 'Prioridad/Urgencia',
                    'rules' => 'permit_empty|is_not_unique[urgencias.id]',
                ],
                'devices' => [
                    'label' => 'Dispositivos',
                    'rules' => 'required',
                ],
            ];

            $validation->setRules($rules);

            if (!$validation->run($data)) {
                return redirectView(
                    'admin/ordenes/crear',
                    $validation,
                    [['Corrija los errores del formulario', 'error', 'top-end']],
                    $data
                );
            }

            // Validación manual extra: Verificar que devices sea un array válido
            if (empty($devices) || !is_array($devices)) {
                return redirectView(
                    'admin/ordenes/crear',
                    null,
                    [['Debe agregar al menos un dispositivo', 'error', 'top-end']],
                    $data
                );
            }

            // ---------------------------------------------------
            // 4. LÓGICA DE GUARDADO (Transacción)
            // ---------------------------------------------------
            $db = \Config\Database::connect();
            $db->transStart();

            // Instanciar Modelos
            $ordenModel = new \App\Models\OrdenTrabajoModel();
            $dispositivoModel = new \App\Models\DispositivoModel();
            $dispositivoProblemasModel = new \App\Models\DispositivoProblemasModel(); // NUEVO
            $dispositivoAccesoriosModel = new \App\Models\DispositivoAccesorioModel(); // NUEVO
            $dispositivoCheckModel = new \App\Models\DispositivoCheckModel(); // NUEVO
            $historialModel = new \App\Models\HistorialDispositivoModel();

            // A. Generar código de orden
            $codigoOrden = 'ORD-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));

            // B. Insertar Orden (SIN dispositivos aún)
            $ordenData = [
                'codigo_orden' => $codigoOrden,
                'cliente_id' => $clienteId,
                'usuario_id' => $usuarioId,
                'urgencia_id' => $urgenciaId ?: null,
                'estado_global' => 'pendiente', // Estado inicial
                'es_reclamo_garantia' => false,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $ordenModel->insert($ordenData);
            $ordenId = $ordenModel->getInsertID();

            // C. Loop: Insertar cada Dispositivo
            foreach ($devices as $dev) {

                // C.1 Lógica Pass/Patrón
                $passwordFinal = '';
                $tipoPass = $dev['tipo_pass'] ?? 'ninguno';

                if ($tipoPass === 'patron') {
                    $passwordFinal = $dev['patron_data'] ?? '';
                } elseif (in_array($tipoPass, ['contrasena', 'contraseña', 'pin'])) {
                    $passwordFinal = $dev['pass_code'] ?? '';
                }

                // C.2 Obtener técnico específico del dispositivo
                $tecnicoDispositivo = !empty($dev['tecnico_id']) ? $dev['tecnico_id'] : null;

                // C.3 Insertar Dispositivo
                $dispositivoInsert = [
                    'orden_id' => $ordenId,
                    'tipo_dispositivo_id' => $dev['tipo_dispositivo_id'] ?? null,
                    'tecnico_id' => $tecnicoDispositivo,
                    'marca_id' => $dev['marca_id'] ?? null,
                    'modelo_id' => $dev['modelo_id'] ?? null,
                    'serie_imei' => $dev['serie_imei'] ?? null,
                    'tipo_pass' => $tipoPass,
                    'pass_code' => $passwordFinal,
                    'estado_diagnostico' => 'pendiente', // Estado inicial
                    'cliente_autoriza_reparacion' => null,
                    'requiere_cotizacion' => false,
                    'tiene_garantia_activa' => false,
                    'veces_reclamada_garantia' => 0,
                    'prioridad_dispositivo_id' => $prioridadDispositivoId ?? null,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $dispositivoModel->insert($dispositivoInsert);
                $dispositivoId = $dispositivoModel->getInsertID();

                // C.4 Insertar PROBLEMAS del dispositivo
                // IMPORTANTE: problema_reportado ahora es un ID o array de IDs
                if (isset($dev['problema_reportado'])) {
                    // Convertir a array si es un solo valor
                    $problemasIds = is_array($dev['problema_reportado'])
                        ? $dev['problema_reportado']
                        : [$dev['problema_reportado']];

                    foreach ($problemasIds as $problemaId) {
                        if (!empty($problemaId)) {
                            $dispositivoProblemasModel->insert([
                                'dispositivo_id' => $dispositivoId,
                                'problema_comun_id' => $problemaId,
                                'prioridad' => 'media', // Por defecto
                                'diagnostico_inicial' => $dev['observaciones'] ?? '', // Observaciones del cliente
                                'fue_reparado' => null, // En proceso
                                'es_reparacion_garantia' => false,
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                }

                // C.5 Insertar ACCESORIOS del dispositivo
                if (isset($dev['accesorios']) && is_array($dev['accesorios'])) {
                    foreach ($dev['accesorios'] as $accesorioId) {
                        if (!empty($accesorioId)) {
                            $dispositivoAccesoriosModel->insert([
                                'dispositivo_id' => $dispositivoId,
                                'accesorio_id' => $accesorioId,
                                'estado' => 'bueno', // Estado por defecto
                                'observacion' => null,
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                }

                // C.6 Insertar CHECKLIST del dispositivo
                if (isset($dev['checklist']) && is_array($dev['checklist'])) {
                    foreach ($dev['checklist'] as $checklistId) {
                        if (!empty($checklistId)) {
                            $dispositivoCheckModel->insert([
                                'dispositivo_id' => $dispositivoId,
                                'checklist_item_id' => $checklistId,
                                'observacion' => null,
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                }

                // C.7 Crear Historial Inicial del dispositivo
                $comentarioHistorial = 'Ingreso del equipo a taller.';
                if ($tecnicoDispositivo) {
                    $comentarioHistorial .= ' Asignado a técnico.';
                } else {
                    $comentarioHistorial .= ' Sin asignar.';
                }

                // Agregar información de problemas al historial
                if (isset($problemasIds) && count($problemasIds) > 0) {
                    $comentarioHistorial .= ' Problemas reportados: ' . count($problemasIds);
                }

                $historialModel->insert([
                    'dispositivo_id' => $dispositivoId,
                    'usuario_id' => $usuarioId,
                    'estado_anterior' => null,
                    'estado_nuevo' => 'recibida',
                    'comentario' => $comentarioHistorial,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // D. Completar la transacción
            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error de base de datos al confirmar la orden.');
            }

            // ÉXITO: Redirigimos al listado o a imprimir
            return redirectView(
                'admin/ordenes',
                null,
                [['Orden ' . $codigoOrden . ' generada exitosamente', 'success', 'top-end']],
                null
            );

        } catch (\Exception $e) {
            log_message('error', '[OrdenController::guardar] ' . $e->getMessage());

            return redirectView(
                'admin/ordenes/crear',
                null,
                [['Error del sistema: ' . $e->getMessage(), 'error', 'top-end']],
                $data ?? []
            );
        }
    }
    public function imprimir($id)
    {
        // 1. CARGAR MODELOS
        $ordenModel = new OrdenTrabajoModel();
        $dispositivoModel = new DispositivoModel(); // Asegúrate de tener este modelo
        $urgenciaModel = new \App\Models\UrgenciaModel();
        $configuracionModel = new ConfiguracionModel();
        $terminosModel = new \App\Models\TerminosCondicionesModel(); // <--- NUEVO

        // 2. OBTENER DATOS DE LA ORDEN
        $orden = $ordenModel->select('ordenes_trabajo.*, c.nombres, c.apellidos, c.telefono, c.email, c.cedula, u.nombre as nombre_urgencia')
            ->join('clientes as c', 'c.id = ordenes_trabajo.cliente_id')
            ->join('urgencias as u', 'u.id = ordenes_trabajo.urgencia_id', 'left')
            ->where('ordenes_trabajo.id', $id)
            ->first();

        if (!$orden) {
            return redirect()->back()->with('error', 'Orden no encontrada');
        }

        $urgencias = $urgenciaModel->where('activo', 1)->orderBy('recargo', 'ASC')->findAll();

        // 3. OBTENER DISPOSITIVOS
        $dispositivos = $dispositivoModel->select('dispositivos.*, td.nombre as nombre_tipo, td.icono')
            ->join('tipos_dispositivo as td', 'td.id = dispositivos.tipo_dispositivo_id', 'left')
            ->where('orden_id', $id)
            ->findAll();

        // ---------------------------------------------------------
        // LOGICA DE TÉRMINOS Y CONDICIONES ACUMULATIVOS
        // ---------------------------------------------------------

        // A. Extraemos los IDs de los tipos de dispositivo presentes en la orden
        // Ejemplo: Si hay 2 celulares y 1 laptop, esto devuelve [1, 2] (sin repetir)
        $tiposIds = [];
        foreach ($dispositivos as $disp) {
            // Asumo que agregaste la columna 'tipo_dispositivo_id' en la tabla dispositivos
            // Si tu columna se llama diferente, cámbialo aquí.
            if (!empty($disp['tipo_dispositivo_id'])) {
                $tiposIds[] = $disp['tipo_dispositivo_id'];
            }
        }
        $tiposIds = array_unique($tiposIds); // Eliminar duplicados de IDs

        // B. Construimos la consulta "Inteligente"
        // Queremos: (Activos) Y (Sean Generales O Sean de los Tipos encontrados)
        $builder = $terminosModel->builder();
        $builder->where('activo', 1);

        $builder->groupStart();
        $builder->where('tipo_dispositivo_id', null); // Términos Generales

        if (!empty($tiposIds)) {
            $builder->orWhereIn('tipo_dispositivo_id', $tiposIds); // Términos específicos
        }
        $builder->groupEnd();

        // ORDEN: primero generales (NULL), luego específicos
        $builder->orderBy('tipo_dispositivo_id IS NOT NULL', 'ASC', false);
        $terminos = $builder->get()->getResultArray();

        // ---------------------------------------------------------

        // 4. GENERAR EL QR
        $urlSeguimiento = base_url("consulta/orden/" . $orden['codigo_orden']);
        $builderQr = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $urlSeguimiento,
            encoding: new Encoding('UTF-8'),
            size: 100,
            margin: 0
        );
        $qrCodeBase64 = $builderQr->build()->getDataUri();

        // 5. CONFIGURACIÓN EMPRESA
        $configuracion = $configuracionModel->first();
        $rutaLogo = FCPATH . 'assets/img/logo.png'; // Ajusta si es necesario

        // 6. PREPARAR DATOS VISTA
        $data = [
            'orden' => $orden,
            'urgencias' => $urgencias,
            'dispositivos' => $dispositivos,
            'qr_code' => $qrCodeBase64,
            'logo_path' => $configuracion['logo_path'] ?? "",
            'nombre_empresa' => $configuracion['nombre_empresa'] ?? 'Mi Empresa',
            'telefono_empresa' => $configuracion['telefono'] ?? '',
            'direccion_empresa' => $configuracion['direccion'] ?? '',
            'email_empresa' => isset($configuracion['email']) ? $configuracion['email'] : '', // Validación extra
            'terminos' => $terminos // <--- PASAMOS LOS TÉRMINOS FILTRADOS
        ];

        // 7. RENDERIZAR PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', FCPATH);

        $dompdf = new Dompdf($options);
        $html = view('admin/ordenes/pdf_template', $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape'); // O 'portrait' si prefieres vertical
        $dompdf->render();

        return $dompdf->stream("Orden_" . $orden['codigo_orden'] . ".pdf", ["Attachment" => false]);
    }

    public function entregar($id)
    {
        $db = \Config\Database::connect();
        $usuarioId = session()->get('id_usuario');

        try {
            // Obtener orden
            $orden = $this->ordenTrabajoModel->find($id);

            if (!$orden) {
                return redirect()->back()->with('error', 'Orden no encontrada');
            }

            // Validar que la orden no esté ya entregada
            if ($orden['estado'] === 'entregado') {
                return redirect()->back()->with('error', 'Esta orden ya fue entregada');
            }

            // Obtener dispositivos de la orden
            $dispositivos = $this->dispositivoModel
                ->select('dispositivos.*, 
                          u.tipo_comision,
                          u.valor_comision')
                ->join('usuarios as u', 'u.id = dispositivos.tecnico_id', 'left')
                ->where('dispositivos.orden_id', $id)
                ->findAll();

            if (empty($dispositivos)) {
                return redirect()->back()->with('error', 'La orden no tiene dispositivos');
            }

            // VALIDACIÓN: Todos los dispositivos deben estar en "listo_retiro"
            $dispositivosNoListos = 0;
            foreach ($dispositivos as $disp) {
                if ($disp['estado_reparacion'] !== 'listo_retiro') {
                    $dispositivosNoListos++;
                }
            }

            if ($dispositivosNoListos > 0) {
                return redirect()->back()->with(
                    'error',
                    "No se puede entregar. Hay {$dispositivosNoListos} dispositivo(s) que no están en estado 'Listo para Retiro'"
                );
            }

            // Calcular totales sumando de los dispositivos
            $manoObraTotal = 0;
            $repuestosTotal = 0;
            $gananciaTotalTecnicos = 0;
            $tecnicosGanancias = [];

            foreach ($dispositivos as $dispositivo) {
                // Sumar costos
                $manoObraTotal += (float) ($dispositivo['mano_obra'] ?? 0);
                $repuestosTotal += (float) ($dispositivo['valor_repuestos'] ?? 0);

                // Calcular ganancia por técnico si tiene asignado
                if (!empty($dispositivo['tecnico_id']) && !empty($dispositivo['tipo_comision'])) {
                    $ganancia = 0;

                    if ($dispositivo['tipo_comision'] === 'porcentaje') {
                        // Porcentaje de la mano de obra DE ESTE DISPOSITIVO
                        $ganancia = (float) (($dispositivo['mano_obra'] * $dispositivo['valor_comision']) / 100);
                    } else {
                        // Monto fijo por dispositivo
                        $ganancia = (float) $dispositivo['valor_comision'];
                    }

                    $gananciaTotalTecnicos += $ganancia;

                    // Acumular por técnico
                    if (!isset($tecnicosGanancias[$dispositivo['tecnico_id']])) {
                        $tecnicosGanancias[$dispositivo['tecnico_id']] = 0;
                    }
                    $tecnicosGanancias[$dispositivo['tecnico_id']] += $ganancia;
                }
            }

            // Obtener valor de revisión y urgencia
            $configuracionModel = new \App\Models\ConfiguracionModel();
            $configuracion = $configuracionModel->first();
            $valorRevision = (float) ($configuracion['valor_revision'] ?? 0);

            $recargoUrgencia = 0;
            if ($orden['urgencia_id']) {
                $urgenciaModel = new \App\Models\UrgenciaModel();
                $urgencia = $urgenciaModel->find($orden['urgencia_id']);
                $recargoUrgencia = (float) ($urgencia['recargo'] ?? 0);
            }

            // Calcular total final
            $totalFinal = $manoObraTotal + $repuestosTotal + $valorRevision + $recargoUrgencia;

            $db->transStart();

            // 1. Actualizar orden de trabajo con totales
            $this->ordenTrabajoModel->update($id, [
                'mano_obra' => $manoObraTotal,
                'valor_repuestos' => $repuestosTotal,
                'total' => $totalFinal,
                'estado' => 'entregado',
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $usuarioId
            ]);

            // 2. Crear registro en ordenes_finalizadas (versión simplificada)
            $db->table('ordenes_finalizadas')->insert([
                'orden_id' => $id,
                'fecha_finalizacion' => date('Y-m-d H:i:s'),
                'mano_obra_total' => $manoObraTotal,
                'repuestos_total' => $repuestosTotal,
                'total' => $totalFinal,
                'ganancia_total_tecnicos' => $gananciaTotalTecnicos,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error al procesar la entrega');
            }

            // Mensaje con detalle de ganancias
            $mensajeGanancias = "Ganancias registradas: $" . number_format($gananciaTotalTecnicos, 2);
            if (count($tecnicosGanancias) > 0) {
                $mensajeGanancias .= " (" . count($tecnicosGanancias) . " técnico(s))";
            }

            return redirect()->to(base_url('admin/ordenes'))
                ->with('success', "Orden entregada exitosamente. Total: $" . number_format($totalFinal, 2) . ". " . $mensajeGanancias);

        } catch (\Exception $e) {
            log_message('error', '[Admin/OrdenController::entregar] ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al entregar la orden: ' . $e->getMessage());
        }
    }
}
