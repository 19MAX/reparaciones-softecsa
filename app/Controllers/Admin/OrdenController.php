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
    protected $prioridadModel;
    protected $usuarioModel;
    protected $tipoDispositivoModel;
    protected $ordenModel;
    protected $prioridadesModel;
    protected $dispositivosOrdenModel;
    protected $dispositivoProblemaModel;
    protected $accesoriosModel;
    protected $detallesModel;
    protected $historialEstadosModel;
    protected $dispositivosAccesoriosModel;
    protected $dispositivoDetallesModel;

    public function __construct()
    {
        $this->configuracionModel = new ConfiguracionModel();
        $this->prioridadModel = new \App\Models\PrioridadModel();
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->tipoDispositivoModel = new \App\Models\TipoDispositivoModel();

        $this->ordenModel = new \App\Models\OrdenesModel();
        $this->prioridadesModel = new \App\Models\PrioridadModel();
        $this->dispositivosOrdenModel = new \App\Models\DispositivosOrdenModel();
        $this->dispositivoProblemaModel = new \App\Models\DispositivoProblemaModel();
        $this->accesoriosModel = new \App\Models\AccesoriosCatalogoModel();
        $this->detallesModel = new \App\Models\DetallesCatalogoModel();
        $this->historialEstadosModel = new \App\Models\HistorialEstados();
        $this->dispositivosAccesoriosModel = new \App\Models\DispositivoAccesorios();
        $this->dispositivoDetallesModel = new \App\Models\DispositivoDetalles();
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

        return view('admin/ordenes/index', $data);
    }

    public function getDispositivosOrden(int $ordenId)
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


    public function crear()
    {
        $data = [
            'titulo' => 'Nueva Orden de Trabajo',
            'prioridades' => $this->prioridadModel->where('activo', 1)->findAll(),
            'tecnicos' => $this->usuarioModel->where('rol', 'tecnico')->where('activo', 1)->findAll(),
            'tiposDispositivos' => $this->tipoDispositivoModel->where('activo', 1)->findAll(),

            // Traemos los items del checklist activos para dibujarlos en la vista
            // 'checklist_items' => $checklistModel->where('activo', 1)->findAll()
        ];

        return view('admin/ordenes/crear', $data);
    }

    public function guardar()
    {
        // DEBUG
        // $valores = $this->request->getPost();
        // var_dump($valores);
        // exit;

        $clienteId = $this->request->getPost('cliente_id');
        $devices = $this->request->getPost('devices');
        if (!$clienteId || !is_array($devices) || empty($devices)) {
            return redirectView('admin/ordenes/crear', null, [['Debe seleccionar un cliente y al menos un dispositivo', 'error', 'top-end']], null);
        }

        try {

            $data = [
                'cliente_id' => $clienteId,
                'devices' => $devices
            ];

            // 3. Validación
            $validation = \Config\Services::validation();

            $rules = [
                'cliente_id' => [
                    'label' => 'Cliente',
                    'rules' => 'required|is_not_unique[clientes.id]', // Debe existir en la tabla clientes
                ],
                'devices' => [
                    'label' => 'Dispositivos',
                    'rules' => 'required', // Validamos manualmente que sea array después
                ],
                // Validamos que SI se envía un técnico en un dispositivo, este exista
                'devices.*.tecnico_id' => [
                    'label' => 'Técnico del dispositivo',
                    'rules' => 'permit_empty|is_not_unique[usuarios.id]',
                ]
            ];

            $validation->setRules($rules);

            if (!$validation->run($data)) {
                return redirectView('admin/ordenes/crear', $validation, [['Corrija los errores del formulario', 'error', 'top-end']], $data);
            }

            // ---------------------------------------------------
            // 4. LOGICA DE GUARDADO (Transacción)
            // ---------------------------------------------------
            $db = \Config\Database::connect();
            $db->transStart();

            $numeroOrden = $this->ordenModel->generarNumeroOrden();

            $ordenData = [
                'numero_orden' => $numeroOrden,
                'cliente_id' => $clienteId,
                'usuario_recepcion_id' => session('id_usuario'),
                'estado' => 'pendiente',
                'observaciones_generales' => '' ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $this->ordenModel->insert($ordenData);
            $ordenId = $this->ordenModel->getInsertID();

            if (!$ordenId) {
                throw new \RuntimeException('No se pudo crear la orden.');
            }
            // Procesar dispositivos
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
                    $prioridad = $this->prioridadesModel->find($prioridadId);
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

                    $tiempoTotalHoras += $tiempoHrs;   // SUMA conservadora
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
                $dispositivoId = $this->dispositivosOrdenModel->insert([
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

            } // fin foreach devices

            $db->transComplete();

            if ($db->transStatus() === false) {
                // Si falla la BD, forzamos excepción para caer en el catch
                throw new \Exception('Error de base de datos al confirmar la orden.');
            }

            // ÉXITO: Redirigimos al listado (o a imprimir)
            return redirectView('admin/ordenes', null, [['Orden ' . $numeroOrden . ' generada exitosamente', 'success', 'top-end']], null);

        } catch (\Exception $e) {
            log_message('error', '[OrdenController::guardar] ' . $e->getMessage());

            // Usamos redirectView para volver al formulario con el mensaje de error y los datos previos
            return redirectView('admin/ordenes/crear', null, [['Error del sistema: ' . $e->getMessage(), 'error', 'top-end']], $data ?? []);
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

    // public function imprimir($id)
    // {
    //     // 1. CARGAR MODELOS
    //     $ordenModel = new OrdenTrabajoModel();
    //     $dispositivoModel = new DispositivoModel(); // Asegúrate de tener este modelo
    //     $urgenciaModel = new \App\Models\UrgenciaModel();
    //     $configuracionModel = new ConfiguracionModel();
    //     $terminosModel = new \App\Models\TerminosCondicionesModel(); // <--- NUEVO

    //     // 2. OBTENER DATOS DE LA ORDEN
    //     $orden = $ordenModel->select('ordenes_trabajo.*, c.nombres, c.apellidos, c.telefono, c.email, c.cedula, u.nombre as nombre_urgencia')
    //         ->join('clientes as c', 'c.id = ordenes_trabajo.cliente_id')
    //         ->join('urgencias as u', 'u.id = ordenes_trabajo.urgencia_id', 'left')
    //         ->where('ordenes_trabajo.id', $id)
    //         ->first();

    //     if (!$orden) {
    //         return redirect()->back()->with('error', 'Orden no encontrada');
    //     }

    //     $urgencias = $urgenciaModel->where('activo', 1)->orderBy('recargo', 'ASC')->findAll();

    //     // 3. OBTENER DISPOSITIVOS
    //     $dispositivos = $dispositivoModel->select('dispositivos.*, td.nombre as nombre_tipo, td.icono')
    //         ->join('tipos_dispositivo as td', 'td.id = dispositivos.tipo_dispositivo_id', 'left')
    //         ->where('orden_id', $id)
    //         ->findAll();

    //     // ---------------------------------------------------------
    //     // LOGICA DE TÉRMINOS Y CONDICIONES ACUMULATIVOS
    //     // ---------------------------------------------------------

    //     // A. Extraemos los IDs de los tipos de dispositivo presentes en la orden
    //     // Ejemplo: Si hay 2 celulares y 1 laptop, esto devuelve [1, 2] (sin repetir)
    //     $tiposIds = [];
    //     foreach ($dispositivos as $disp) {
    //         // Asumo que agregaste la columna 'tipo_dispositivo_id' en la tabla dispositivos
    //         // Si tu columna se llama diferente, cámbialo aquí.
    //         if (!empty($disp['tipo_dispositivo_id'])) {
    //             $tiposIds[] = $disp['tipo_dispositivo_id'];
    //         }
    //     }
    //     $tiposIds = array_unique($tiposIds); // Eliminar duplicados de IDs

    //     // B. Construimos la consulta "Inteligente"
    //     // Queremos: (Activos) Y (Sean Generales O Sean de los Tipos encontrados)
    //     $builder = $terminosModel->builder();
    //     $builder->where('activo', 1);

    //     $builder->groupStart();
    //     $builder->where('tipo_dispositivo_id', null); // Términos Generales

    //     if (!empty($tiposIds)) {
    //         $builder->orWhereIn('tipo_dispositivo_id', $tiposIds); // Términos específicos
    //     }
    //     $builder->groupEnd();

    //     // ORDEN: primero generales (NULL), luego específicos
    //     $builder->orderBy('tipo_dispositivo_id IS NOT NULL', 'ASC', false);
    //     $terminos = $builder->get()->getResultArray();

    //     // ---------------------------------------------------------

    //     // 4. GENERAR EL QR
    //     $urlSeguimiento = base_url("consulta/orden/" . $orden['codigo_orden']);
    //     $builderQr = new Builder(
    //         writer: new PngWriter(),
    //         writerOptions: [],
    //         validateResult: false,
    //         data: $urlSeguimiento,
    //         encoding: new Encoding('UTF-8'),
    //         size: 100,
    //         margin: 0
    //     );
    //     $qrCodeBase64 = $builderQr->build()->getDataUri();

    //     // 5. CONFIGURACIÓN EMPRESA
    //     $configuracion = $configuracionModel->first();
    //     $rutaLogo = FCPATH . 'assets/img/logo.png'; // Ajusta si es necesario

    //     // 6. PREPARAR DATOS VISTA
    //     $data = [
    //         'orden' => $orden,
    //         'urgencias' => $urgencias,
    //         'dispositivos' => $dispositivos,
    //         'qr_code' => $qrCodeBase64,
    //         'logo_path' => $configuracion['logo_path'] ?? "",
    //         'nombre_empresa' => $configuracion['nombre_empresa'] ?? 'Mi Empresa',
    //         'telefono_empresa' => $configuracion['telefono'] ?? '',
    //         'direccion_empresa' => $configuracion['direccion'] ?? '',
    //         'email_empresa' => isset($configuracion['email']) ? $configuracion['email'] : '', // Validación extra
    //         'terminos' => $terminos // <--- PASAMOS LOS TÉRMINOS FILTRADOS
    //     ];

    //     // 7. RENDERIZAR PDF
    //     $options = new Options();
    //     $options->set('isRemoteEnabled', true);
    //     $options->set('isHtml5ParserEnabled', true);
    //     $options->set('chroot', FCPATH);

    //     $dompdf = new Dompdf($options);
    //     $html = view('admin/ordenes/pdf_template', $data);
    //     $dompdf->loadHtml($html);
    //     $dompdf->setPaper('A4', 'landscape'); // O 'portrait' si prefieres vertical
    //     $dompdf->render();

    //     return $dompdf->stream("Orden_" . $orden['codigo_orden'] . ".pdf", ["Attachment" => false]);
    // }

    public function entregar($id)
    {
        $db = \Config\Database::connect();
        $usuarioId = session()->get('id_usuario');

        try {
            // Obtener orden
            $orden = $this->ordenModel->find($id);

            if (!$orden) {
                return redirect()->back()->with('error', 'Orden no encontrada');
            }

            // Validar que la orden no esté ya entregada
            if ($orden['estado'] === 'entregado') {
                return redirect()->back()->with('error', 'Esta orden ya fue entregada');
            }

            // Obtener dispositivos de la orden
            $dispositivos = $this->dispositivosOrdenModel
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
            $this->ordenModel->update($id, [
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
