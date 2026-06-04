<?php

namespace App\Controllers\Recepcionista;

use App\Controllers\BaseController;
use App\Models\ConfiguracionModel;
use App\Models\DispositivoAccesorios;
use App\Models\DispositivoDetalles;
use App\Models\DispositivoModel;
use App\Models\DispositivoProblemaModel;
use App\Models\DispositivosOrdenModel;
use App\Models\HistorialEstados;
use App\Models\OrdenesModel;
use App\Models\PrioridadModel;
use App\Models\ProblemaModel;
use App\Models\TipoDispositivoModel;
use App\Models\UsuarioModel;
// --- IMPORTS PARA QR CODE Y PDF ---
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;

class OrdenController extends BaseController
{
    public $configuracionModel;
    protected $urgenciaModel;
    protected $usuarioModel;
    protected $tipoDispositivoModel;
    protected $ordenModel;
    protected $dispositivoModel;
    protected $prioridadModel;
    protected $dispositivoProblemaModel;
    protected $dispositivosAccesoriosModel;
    protected $dispositivoDetallesModel;
    protected $historialEstadosModel;
    protected $problemaModel;

    public function __construct()
    {
        $this->ordenModel = new OrdenesModel();
        $this->dispositivoModel = new DispositivosOrdenModel();
        $this->prioridadModel = new PrioridadModel();
        $this->usuarioModel = new UsuarioModel();
        $this->tipoDispositivoModel = new TipoDispositivoModel();
        $this->dispositivoProblemaModel = new DispositivoProblemaModel();
        $this->dispositivosAccesoriosModel = new DispositivoAccesorios();
        $this->dispositivoDetallesModel = new DispositivoDetalles();
        $this->historialEstadosModel = new HistorialEstados();
        $this->problemaModel = new ProblemaModel();
    }

    public function index()
    {
        $db = \Config\Database::connect();

        $ordenes = $db->table('ordenes o')
            ->select([
                'o.id',
                'o.numero_orden',
                'o.estado',
                'o.created_at',
                'c.nombres',
                'c.apellidos',
                'COUNT(do.id)    AS total_dispositivos',
            ])
            ->join('clientes c', 'c.id = o.cliente_id')
            ->join('dispositivos_orden do', 'do.orden_id = o.id', 'left')
            ->groupBy('o.id')
            ->orderBy('o.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'titulo' => 'Gestión de Órdenes',
            'ordenes' => $ordenes,
        ];

        return view('recepcionista/ordenes/index', $data);
    }

    public function crear()
    {
        $data = [
            'titulo' => 'Nueva Orden de Trabajo',
            'prioridades' => $this->prioridadModel->where('activo', 1)->findAll(),
            'tecnicos' => $this->usuarioModel->where('rol', 'tecnico')->where('activo', 1)->findAll(),
            'tiposDispositivos' => $this->tipoDispositivoModel->where('activo', 1)->findAll(),
        ];

        return view('recepcionista/ordenes/crear', $data);
    }

    public function guardar()
    {
        $clienteId = $this->request->getPost('cliente_id');
        $devices = $this->request->getPost('devices');

        if (!$clienteId || !is_array($devices) || empty($devices)) {
            return redirect()->back()->withInput()->with('error', 'Debe seleccionar un cliente y al menos un dispositivo.');
        }

        try {
            $db = \Config\Database::connect();
            $db->transStart();

            $numeroOrden = $this->ordenModel->generarNumeroOrden();

            $ordenData = [
                'numero_orden' => $numeroOrden,
                'cliente_id' => $clienteId,
                'usuario_recepcion_id' => session('id_usuario'),
                'estado' => 'pendiente',
            ];

            $this->ordenModel->insert($ordenData);
            $ordenId = $this->ordenModel->getInsertID();

            if (!$ordenId) {
                throw new \RuntimeException('No se pudo crear la orden.');
            }

            foreach ($devices as $dev) {
                // --- 5.1 Limpiar y tipar datos del dispositivo -------------
                $tipoId = (int) ($dev['tipo_dispositivo_id'] ?? 0);
                $marcaId = (int) ($dev['marca_id'] ?? 0);
                $modeloId = !empty($dev['modelo_id']) ? (int) $dev['modelo_id'] : null;
                $tecnicoId = !empty($dev['tecnico_id']) ? (int) $dev['tecnico_id'] : null;
                $prioridadId = !empty($dev['prioridad_dispositivo_id']) ? (int) $dev['prioridad_dispositivo_id'] : null;
                $serieImei = !empty($dev['serie_imei']) ? trim($dev['serie_imei']) : null;
                $tipoPass = $dev['tipo_pass'] ?? 'sin_clave';
                $passCode = null;
                $observaciones = trim($dev['observaciones'] ?? '');
                $problemaIds = $dev['problema_reportado'] ?? [];
                $accesorioIds = $dev['accesorios'] ?? [];
                $detalleIds = $dev['detalles'] ?? [];

                if ($tipoId === 0 || $marcaId === 0) {
                    throw new \RuntimeException('Tipo de dispositivo y marca son obligatorios.');
                }

                if (empty($problemaIds)) {
                    throw new \RuntimeException('Debe indicar al menos un problema por dispositivo.');
                }

                // --- 5.2 Manejar contraseña/patrón -------------------------
                if ($tipoPass !== 'sin_clave' && $tipoPass !== 'huella') {
                    $rawPass = $tipoPass === 'patron'
                        ? ($dev['patron_data'] ?? '')
                        : ($dev['pass_code'] ?? '');

                    // Cifrar con AES-256-CBC usando APP_KEY como clave
                    if (!empty($rawPass)) {
                        $key = hex2bin(substr(hash('sha256', env('encryption.key')), 0, 64));
                        $iv = openssl_random_pseudo_bytes(16);
                        $cifrado = openssl_encrypt($rawPass, 'AES-256-CBC', $key, 0, $iv);
                        $passCode = base64_encode($iv . '::' . $cifrado);
                    }
                }

                // --- 5.3 Obtener costo de prioridad ------------------------
                $costoPrioridad = 0.00;
                if ($prioridadId) {
                    $prioridad = $this->prioridadModel->find($prioridadId);
                    $costoPrioridad = (float) ($prioridad['costo_adicional'] ?? 0);
                }

                // --- 5.4 Calcular precios y tiempo por cada problema -------
                $problemasData = [];
                $tiempoTotalHoras = 0.00;
                $totalManoObra = 0.00;
                $totalRepuesto = 0.00;

                foreach ($problemaIds as $probId) {
                    $probId = (int) $probId;

                    // Obtener datos del problema del catálogo
                    $problema = $db->table('problemas')
                        ->where('id', $probId)
                        ->where('activo', 1)
                        ->get()->getRowArray();

                    if (!$problema) {
                        throw new \RuntimeException("Problema ID {$probId} no encontrado o inactivo.");
                    }

                    // Buscar precio: primero por modelo específico, luego genérico
                    $precio = null;
                    if ($modeloId) {
                        $precio = $db->table('precios_base')
                            ->where('problema_id', $probId)
                            ->where('modelo_id', $modeloId)
                            ->get()->getRowArray();
                    }
                    // Fallback: precio genérico sin modelo
                    if (!$precio) {
                        $precio = $db->table('precios_base')
                            ->where('problema_id', $probId)
                            ->where('modelo_id IS NULL', null, false)
                            ->get()->getRowArray();
                    }

                    $precioMO = (float) ($precio['precio_mano_obra'] ?? 0);
                    $precioRep = (float) ($precio['precio_repuesto'] ?? 0);
                    $tiempoHrs = (float) $problema['tiempo_reparacion_horas'];

                    $tiempoTotalHoras += $tiempoHrs;
                    $totalManoObra += $precioMO;
                    $totalRepuesto += $precioRep;

                    $problemasData[] = [
                        'problema_id' => $probId,
                        'tiempo_reparacion_horas' => $tiempoHrs,
                        'precio_mano_obra' => $precioMO,
                        'precio_repuesto' => $precioRep,
                        'observacion' => null,
                    ];
                }

                $precioTotal = $totalManoObra + $totalRepuesto + $costoPrioridad;

                // --- 5.5 Calcular fecha estimada de entrega ----------------
                $fechaEstimada = $this->calcularFechaEntrega(
                    $tecnicoId,
                    $tiempoTotalHoras,
                    $prioridadId,
                    $db
                );

                // --- 5.6 Insertar dispositivo_orden ------------------------
                $dispositivoId = $this->dispositivoModel->insert([
                    'orden_id' => $ordenId,
                    'tipo_dispositivo_id' => $tipoId,
                    'marca_id' => $marcaId,
                    'modelo_id' => $modeloId,
                    'modelo_texto' => null,
                    'serie_imei' => $serieImei,
                    'prioridad_id' => $prioridadId,
                    'tecnico_id' => $tecnicoId,
                    'relato_cliente' => $observaciones ?: null,
                    'tipo_seguridad' => $tipoPass,
                    'clave_acceso' => $passCode,
                    'costo_prioridad' => $costoPrioridad,
                    'precio_total' => $precioTotal,
                    'tiempo_total_horas' => $tiempoTotalHoras,
                    'comision_tecnico' => null,
                    'fecha_estimada_entrega' => $fechaEstimada,
                    'fecha_real_entrega' => null,
                    'estado' => 'pendiente',
                ]);

                if (!$dispositivoId) {
                    throw new \RuntimeException('No se pudo registrar el dispositivo.');
                }

                // --- 5.7 Insertar problemas (dispositivo_problemas) --------
                foreach ($problemasData as &$pd) {
                    $pd['dispositivo_orden_id'] = $dispositivoId;
                }
                unset($pd);

                if (!$this->dispositivoProblemaModel->insertBatch($problemasData)) {
                    throw new \RuntimeException('No se pudieron registrar los problemas del dispositivo.');
                }

                // --- 5.8 Insertar accesorios -------------------------------
                if (!empty($accesorioIds)) {
                    $accesoriosInsert = array_map(fn($aid) => [
                        'dispositivo_orden_id' => $dispositivoId,
                        'accesorio_id' => (int) $aid,
                        'accesorio_texto' => null,
                        'cantidad' => 1,
                        'observacion' => null,
                    ], $accesorioIds);

                    if (!$this->dispositivosAccesoriosModel->insertBatch($accesoriosInsert)) {
                        throw new \RuntimeException('No se pudieron registrar los accesorios.');
                    }
                }

                // --- 5.9 Insertar detalles físicos -------------------------
                if (!empty($detalleIds)) {
                    $detallesInsert = array_map(fn($did) => [
                        'dispositivo_orden_id' => $dispositivoId,
                        'detalle_id' => (int) $did,
                        'detalle_texto' => null,
                    ], $detalleIds);

                    if (!$this->dispositivoDetallesModel->insertBatch($detallesInsert)) {
                        throw new \RuntimeException('No se pudieron registrar los detalles físicos.');
                    }
                }

                // --- 5.10 Registrar historial de estado inicial ------------
                $this->historialEstadosModel->insert([
                    'dispositivo_orden_id' => $dispositivoId,
                    'estado_anterior' => null,
                    'estado_nuevo' => 'pendiente',
                    'usuario_id' => session('id_usuario'),
                    'observacion' => 'Ingreso del dispositivo al sistema.',
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirectView('recepcionista/ordenes/crear', null, [['Ocurrió un error al guardar la orden.', 'error', 'top-end']]);
            }

            // Fetch client data to get cedula for PDF printing
            $clienteModel = new \App\Models\ClienteModel();
            $clienteInfo = $clienteModel->find($clienteId);
            $cedulaCliente = $clienteInfo['cedula'] ?? '';

            $urlPdf = base_url('tecnico/ordenes/imprimir/' . $ordenId . '/ticket');

            (new \App\Services\ReparacionEmailService())
                ->enviarIngresoOrden($ordenId, $dispositivoId);

            return redirectView(
                'recepcionista/ordenes',
                null,
                [
                    [
                        'Orden ' . $numeroOrden . ' generada correctamente',
                        'success',
                        'center',
                        base_url('recepcionista/ordenes/imprimir/' . $ordenId . '/ticket'),
                        base_url('recepcionista/ordenes/imprimir/' . $ordenId . '/carta'),
                        base_url('recepcionista/ordenes/imprimir/' . $ordenId)
                    ]
                ],
                null
            );

        } catch (\Exception $e) {
            log_message('error', '[Recepcionista/OrdenController::guardar] ' . $e->getMessage());
            return redirectView('recepcionista/ordenes/crear', null, [['Error: ' . $e->getMessage(), 'error', 'top-end']]);
        }
    }


    private function calcularFechaEntrega(?int $tecnicoId, float $horasNecesarias, ?int $prioridadId, $db): string
    {
        // 1. Determinar carga inicial
        $horasCarga = 0;
        if ($tecnicoId) {
            $carga = $db->table('dispositivos_orden')
                ->selectSum('tiempo_total_horas', 'total')
                ->whereIn('estado', ['pendiente', 'en_proceso'])
                ->where('tecnico_id', $tecnicoId)
                ->get()->getRow();
            $horasCarga = (float) ($carga->total ?? 0);
        } else {
            // COLA GENERAL: Si no hay técnico, sumamos todo lo pendiente del taller
            $cargaGeneral = $db->table('dispositivos_orden')
                ->selectSum('tiempo_total_horas', 'total')
                ->whereIn('estado', ['pendiente'])
                ->where('tecnico_id', null)
                ->get()->getRow();
            $horasCarga = (float) ($cargaGeneral->total ?? 24); // Mínimo 24h de diagnóstico si está vacío
        }

        $horasTotales = $horasCarga + $horasNecesarias;

        // 2. Motor de tiempo laboral (Iterativo)
        $fechaActual = new \DateTime();
        $horasRestantes = $horasTotales;

        // Traer horarios de la base de datos para no hacer consultas en el loop
        $horarios = $db->table('horarios_atencion')->get()->getResultArray();
        $configHoras = [];
        foreach ($horarios as $h) {
            $configHoras[$h['dia_semana']] = $h;
        }

        while ($horasRestantes > 0) {
            $diaActual = (int) $fechaActual->format('w');

            // Si el día está cerrado, saltar al siguiente día a las 00:00
            if (!isset($configHoras[$diaActual]) || $configHoras[$diaActual]['abierto'] == 0) {
                $fechaActual->modify('+1 day')->setTime(0, 0);
                continue;
            }

            $apertura = new \DateTime($fechaActual->format('Y-m-d') . ' ' . $configHoras[$diaActual]['hora_apertura']);
            $cierre = new \DateTime($fechaActual->format('Y-m-d') . ' ' . $configHoras[$diaActual]['hora_cierre']);

            // Si la hora actual es antes de abrir, empezamos a contar desde la apertura
            if ($fechaActual < $apertura) {
                $fechaActual = clone $apertura;
            }

            // Si ya pasó la hora de cierre, saltar al día siguiente
            if ($fechaActual >= $cierre) {
                $fechaActual->modify('+1 day')->setTime(0, 0);
                continue;
            }

            // Calcular cuánto tiempo queda disponible hoy
            $intervaloA_Cierre = $fechaActual->diff($cierre);
            $horasDisponiblesHoy = $intervaloA_Cierre->h + ($intervaloA_Cierre->i / 60);

            if ($horasRestantes <= $horasDisponiblesHoy) {
                // Terminamos dentro del horario de hoy
                $fechaActual->modify("+" . round($horasRestantes * 60) . " minutes");
                $horasRestantes = 0;
            } else {
                // Usamos lo que queda de hoy y saltamos al siguiente
                $horasRestantes -= $horasDisponiblesHoy;
                $fechaActual->modify('+1 day')->setTime(0, 0);
            }
        }

        return $fechaActual->format('Y-m-d H:i:s');
    }

    public function getDispositivoOrden($ordenId)
   {
        $db = \Config\Database::connect();

        // Verificar que la orden existe
        $orden = $db->table('ordenes')->where('id', $ordenId)->get()->getRowArray();

        if (!$orden) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Orden no encontrada.',
            ])->setStatusCode(404);
        }

        $dispositivos = $db->table('dispositivos_orden do')
            ->select([
                'do.id',
                'do.estado',
                'do.precio_total',
                'do.fecha_real_entrega',
                'do.fecha_estimada_entrega',
                'td.nombre   AS tipo_dispositivo',
                'm.nombre    AS marca',
                // Si tiene modelo en catálogo lo usa, si no usa el texto libre
                'COALESCE(mo.nombre, do.modelo_texto) AS modelo',
            ])
            ->join('tipos_dispositivo td', 'td.id = do.tipo_dispositivo_id')
            ->join('marcas m', 'm.id  = do.marca_id')
            ->join('modelos mo', 'mo.id = do.modelo_id', 'left')
            ->where('do.orden_id', $ordenId)
            ->orderBy('do.id', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'orden_id' => $ordenId,
            'dispositivos' => $dispositivos,
        ]);
    }

    public function imprimir(int $ordenId, ?string $tipoImpresion = null)
    {
        $db = \Config\Database::connect();

        // 1. Orden + cliente
        $orden = $db->table('ordenes o')
            ->select([
                'o.id',
                'o.numero_orden',
                'o.estado',
                'o.observaciones_generales',
                'o.created_at AS fecha_ingreso',
                'c.nombres    AS cliente_nombre',
                'c.apellidos  AS cliente_apellido',
                'c.telefono   AS cliente_telefono',
                'c.email      AS cliente_email',
                'c.cedula AS cliente_cedula',
                'u.nombre     AS recepcionista',
            ])
            ->join('clientes c', 'c.id = o.cliente_id')
            ->join('usuarios u', 'u.id = o.usuario_recepcion_id')
            ->where('o.id', $ordenId)
            ->get()->getRowArray();

        if (!$orden) {
            return redirect()->to(base_url('tecnico/dispositivos/pool'))
                ->with('error', 'Orden no encontrada.');
        }

        // 2. Dispositivos de la orden
        $dispositivos = $db->table('dispositivos_orden do')
            ->select([
                'do.id',
                'do.estado',
                'do.serie_imei',
                'do.tipo_seguridad',
                'do.relato_cliente',
                'do.precio_total',
                'do.costo_prioridad',
                'do.tiempo_total_horas',
                'do.fecha_estimada_entrega',
                'do.fecha_real_entrega',
                'do.created_at             AS fecha_ingreso',
                'td.nombre                 AS tipo_dispositivo',
                'm.nombre                  AS marca',
                'COALESCE(mo.nombre, do.modelo_texto) AS modelo',
                'pr.nombre                 AS prioridad',
                'pr.color_badge            AS prioridad_color',
                'u.nombre                  AS tecnico',
            ])
            ->join('tipos_dispositivo td', 'td.id = do.tipo_dispositivo_id')
            ->join('marcas m', 'm.id  = do.marca_id')
            ->join('modelos mo', 'mo.id = do.modelo_id', 'left')
            ->join('prioridades pr', 'pr.id = do.prioridad_id', 'left')
            ->join('usuarios u', 'u.id  = do.tecnico_id', 'left')
            ->where('do.orden_id', $ordenId)
            ->orderBy('do.id', 'ASC')
            ->get()->getResultArray();

        // 3. Enriquecer cada dispositivo con sus relaciones
        foreach ($dispositivos as &$dev) {
            $devId = $dev['id'];

            // Problemas con precios
            $dev['problemas'] = $db->table('dispositivo_problemas dp')
                ->select([
                    'p.nombre                                      AS problema',
                    'dp.precio_mano_obra',
                    'dp.precio_repuesto',
                    '(dp.precio_mano_obra + dp.precio_repuesto)   AS subtotal',
                    'dp.observacion',
                ])
                ->join('problemas p', 'p.id = dp.problema_id')
                ->where('dp.dispositivo_orden_id', $devId)
                ->get()->getResultArray();

            // Accesorios
            $dev['accesorios'] = $db->table('dispositivo_accesorios da')
                ->select('COALESCE(ac.nombre, da.accesorio_texto) AS accesorio, da.cantidad')
                ->join('accesorios_catalogo ac', 'ac.id = da.accesorio_id', 'left')
                ->where('da.dispositivo_orden_id', $devId)
                ->get()->getResultArray();

            // Detalles físicos
            $dev['detalles'] = $db->table('dispositivo_detalles dd')
                ->select('COALESCE(dc.nombre, dd.detalle_texto) AS detalle')
                ->join('detalles_catalogo dc', 'dc.id = dd.detalle_id', 'left')
                ->where('dd.dispositivo_orden_id', $devId)
                ->get()->getResultArray();

            // Última observación para el cliente (útil para cancelaciones)
            $ultimaObs = $db->table('historial_estados')
                ->select('observacion_cliente')
                ->where('dispositivo_orden_id', $devId)
                ->where('observacion_cliente IS NOT NULL', null, false)
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()->getRowArray();

            $dev['comentario_cliente'] = $ultimaObs['observacion_cliente'] ?? null;
        }
        unset($dev);

        // 4. Configuración de empresa
        $empresaConfigModel = new \App\Models\ConfiguracionModel();
        $empresaConfig = $empresaConfigModel->getConfig();

        // 5. QR Code con el número de orden
        $urlSeguimiento = base_url("consulta/orden/" . $orden['numero_orden']);
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

        // 6. Términos y condiciones
        $terminos = [];
        if (!empty($empresaConfig['terminos_condiciones'])) {
            $decoded = json_decode($empresaConfig['terminos_condiciones'], true);
            $terminos = is_array($decoded) ? $decoded : [];
        }

        if (empty($terminos)) {
            $terminos = [
                'El taller no se hace responsable por daños preexistentes no reportados al momento del ingreso del equipo.',
                'El cliente debe retirar su equipo dentro de los 30 días posteriores a la notificación de reparación completada.',
                'Los equipos no retirados en el plazo indicado podrán generar costos de almacenamiento.',
                'La garantía de reparación cubre únicamente la falla reparada y tiene una duración de 30 días.',
                'El retiro del equipo implica la aceptación del trabajo realizado y el monto cobrado.',
            ];
        }

        // 7. Preparar datos para la vista
        $data = [
            'orden' => $orden,
            'dispositivos' => $dispositivos,
            'qr_code' => $qrCodeBase64,
            'empresa_config' => $empresaConfig,
            'terminos' => $terminos,
        ];

        // 8. Renderizar PDF con Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', [FCPATH]);

        $dompdf = new Dompdf($options);

        // ── 9. Definir variables por defecto (A4 Horizontal) ───
        $vista = 'admin/ordenes/pdf_orden';
        $tamanioPapel = 'A4';
        $orientacion = 'landscape';

        // ── 10. Modificar según el tipo de impresión ───────────
        if ($tipoImpresion === 'ticket') {
            $vista = 'admin/pdf/orden_ticket';
            // 226.77 puntos = 80mm (ancho ideal para ticketeras térmicas)
            // 800 puntos de alto (lo puedes aumentar si la orden es muy larga)
            $tamanioPapel = [0, 0, 226.77, 800];
            $orientacion = 'portrait';
        } elseif ($tipoImpresion === 'carta') {
            $vista = 'admin/pdf/orden_carta';
            $tamanioPapel = 'carta';
            $orientacion = 'portrait';
        }

        // ── 11. Renderizado Unificado (DRY) ────────────────────
        $html = view($vista, $data);

        $dompdf->loadHtml($html);
        $dompdf->setPaper($tamanioPapel, $orientacion);
        $dompdf->render();

        // ── 12. Generar y retornar el PDF ──────────────────────
        $pdf = $dompdf->output();

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader(
                'Content-Disposition',
                'inline; filename="Orden_' . $orden['numero_orden'] . '.pdf"'
            )
            ->setBody($pdf);
    }

    public function entregar($id)
    {
        $db = \Config\Database::connect();
        $usuarioId = session()->get('id_usuario');

        // Ruta a la que volveremos (listado de órdenes)
        $rutaRetorno = 'recepcionista/ordenes';

        try {
            // 1. Obtener orden
            $orden = $this->ordenTrabajoModel->find($id);

            if (!$orden) {
                return redirectView($rutaRetorno, null, [['La orden solicitada no existe', 'error', 'top-end']]);
            }

            // 2. Validar estado (usando tu constante helper)
            if ((int) $orden['estado'] === ESTADO_ORDEN_ENTREGADA) {
                return redirectView($rutaRetorno, null, [['Esta orden ya fue entregada anteriormente', 'warning', 'top-end']]);
            }

            // 3. Obtener dispositivos y calcular totales
            $dispositivos = $this->dispositivoModel
                ->select('dispositivos.*, u.tipo_comision, u.valor_comision')
                ->join('usuarios as u', 'u.id = dispositivos.tecnico_id', 'left')
                ->where('dispositivos.orden_id', $id)
                ->findAll();

            if (empty($dispositivos)) {
                return redirectView($rutaRetorno, null, [['La orden no tiene dispositivos asociados', 'error', 'top-end']]);
            }

            // 4. Validación: Todos deben estar listos
            $dispositivosNoListos = 0;
            foreach ($dispositivos as $disp) {
                if (
                    (int) $disp['estado_reparacion'] !== ESTADO_DISPOSITIVO_LISTO_RETIRO &&
                    (int) $disp['estado_reparacion'] !== ESTADO_DISPOSITIVO_ENTREGADO
                ) {
                    $dispositivosNoListos++;
                }
            }

            if ($dispositivosNoListos > 0) {
                return redirectView(
                    $rutaRetorno,
                    null,
                    [
                        [
                            "No se puede entregar. Hay {$dispositivosNoListos} dispositivo(s) que no están listos para retiro.",
                            'error',
                            'top-end'
                        ]
                    ]
                );
            }

            // --- CÁLCULOS (Igual que antes) ---
            $manoObraTotal = 0;
            $repuestosTotal = 0;
            $gananciaTotalTecnicos = 0;

            foreach ($dispositivos as $dispositivo) {
                $manoObraTotal += (float) ($dispositivo['mano_obra'] ?? 0);
                $repuestosTotal += (float) ($dispositivo['valor_repuestos'] ?? 0);

                if (!empty($dispositivo['tecnico_id']) && !empty($dispositivo['tipo_comision'])) {
                    $ganancia = 0;
                    if ($dispositivo['tipo_comision'] === 'porcentaje') {
                        $ganancia = (float) (($dispositivo['mano_obra'] * $dispositivo['valor_comision']) / 100);
                    } else {
                        $ganancia = (float) $dispositivo['valor_comision'];
                    }
                    $gananciaTotalTecnicos += $ganancia;
                }
            }

            // Costos extra
            $configuracionModel = new \App\Models\ConfiguracionModel();
            $config = $configuracionModel->first();
            // ELIMINAMOS O COMENTAMOS ESTA LÍNEA QUE COBRABA LA REVISIÓN SIEMPRE
            // $valorRevision = (float) ($config['valor_revision'] ?? 0); 

            // LOGICA CORREGIDA:
            // Si ya se reparó, la revisión suele ser $0 o absorberse en la mano de obra.
            // Si quieres que explícitamente sea 0 al entregar:
            $valorRevision = 0.00;

            $recargoUrgencia = 0;
            if (!empty($orden['urgencia_id'])) {
                $urgenciaModel = new \App\Models\UrgenciaModel();
                $urgencia = $urgenciaModel->find($orden['urgencia_id']);
                $recargoUrgencia = (float) ($urgencia['recargo'] ?? 0);
            }

            $totalFinal = $manoObraTotal + $repuestosTotal + $valorRevision + $recargoUrgencia;

            // --- TRANSACCIÓN ---
            $db->transStart();

            // Actualizar Orden
            $this->ordenTrabajoModel->update($id, [
                'mano_obra' => $manoObraTotal,
                'valor_repuestos' => $repuestosTotal,
                'total' => $totalFinal,
                'estado' => ESTADO_ORDEN_ENTREGADA,
                // 'updated_at' => date('Y-m-d H:i:s'),
                // 'updated_by' => $usuarioId
            ]);

            // Insertar en Finalizadas
            $db->table('ordenes_finalizadas')->insert([
                'orden_id' => $id,
                'fecha_finalizacion' => date('Y-m-d H:i:s'),
                'mano_obra_total' => $manoObraTotal,
                'repuestos_total' => $repuestosTotal,
                'total' => $totalFinal,
                'ganancia_total_tecnicos' => $gananciaTotalTecnicos,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Actualizar Dispositivos a Entregado
            $this->dispositivoModel->where('orden_id', $id)
                ->set(['estado_reparacion' => ESTADO_DISPOSITIVO_ENTREGADO])
                ->update();

            $db->transComplete();

            if ($db->transStatus() === false) {
                // Obtener error de BD para logs
                $errorBD = $db->error();
                throw new \Exception('Error en BD: ' . $errorBD['message']);
            }

            // --- ÉXITO ---
            return redirectView(
                $rutaRetorno,
                null,
                [
                    [
                        "Orden #{$orden['codigo_orden']} entregada exitosamente. Total: $" . number_format($totalFinal, 2),
                        'success',
                        'top-end'
                    ]
                ]
            );

        } catch (\Exception $e) {
            // Loguear el error completo para ti (Desarrollador)
            log_message('critical', "[OrdenController::entregar] Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());

            // Redirigir con mensaje para el usuario
            return redirectView(
                $rutaRetorno,
                null,
                [
                    [
                        'Error al procesar la entrega: ' . $e->getMessage(),
                        'error',
                        'top-end'
                    ]
                ]
            );
        }
    }
}
