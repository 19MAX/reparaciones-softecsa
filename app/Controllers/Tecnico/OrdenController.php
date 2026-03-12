<?php

namespace App\Controllers\Tecnico;

use App\Controllers\BaseController;
use App\Models\OrdenesModel;
use App\Models\DispositivosOrdenModel;
use App\Models\DispositivoProblemaModel;
use App\Models\ClienteModel;
use App\Models\TipoDispositivoModel;
use App\Models\PrioridadModel;
use App\Models\UsuarioModel;
use App\Models\HistorialEstados;

// Imports para QR y PDF
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class OrdenController extends BaseController
{
    protected $ordenModel;
    protected $dispositivoModel;
    protected $prioridadModel;
    protected $usuarioModel;
    protected $tipoDispositivoModel;

    public function __construct()
    {
        $this->ordenModel = new OrdenesModel();
        $this->dispositivoModel = new DispositivosOrdenModel();
        $this->prioridadModel = new PrioridadModel();
        $this->usuarioModel = new UsuarioModel();
        $this->tipoDispositivoModel = new TipoDispositivoModel();
    }

    public function crear()
    {
        $data = [
            'titulo' => 'Nueva Orden de Trabajo',
            'prioridades' => $this->prioridadModel->where('activo', 1)->findAll(),
            'tecnicos' => $this->usuarioModel->where('rol', 'tecnico')->where('activo', 1)->findAll(),
            'tiposDispositivos' => $this->tipoDispositivoModel->where('activo', 1)->findAll(),
        ];

        return view('tecnico/ordenes/crear', $data);
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

            $ordenId = $this->ordenModel->insert($ordenData);

            $historialModel = new HistorialEstados();
            $dispositivoProblemaModel = new DispositivoProblemaModel();

            foreach ($devices as $dev) {
                // Mapear los campos del formulario a la estructura de la base de datos (dispositivos_orden)
                $tipoSeguridad = 'sin_clave';
                if (isset($dev['tipo_pass'])) {
                    if (in_array($dev['tipo_pass'], ['patron', 'contrasena', 'pin', 'huella'])) {
                        $tipoSeguridad = $dev['tipo_pass'];
                    }
                }

                $claveAcceso = null;
                if ($tipoSeguridad === 'contrasena' || $tipoSeguridad === 'pin') {
                    $claveAcceso = $dev['pass_code'] ?? null;
                } elseif ($tipoSeguridad === 'patron') {
                    $claveAcceso = $dev['patron_data'] ?? null;
                }

                $dispData = [
                    'orden_id' => $ordenId,
                    'tipo_dispositivo_id' => $dev['tipo_dispositivo_id'] ?? null,
                    'marca_id' => $dev['marca_id'] ?? null,
                    'modelo_id' => (isset($dev['modelo_id']) && is_numeric($dev['modelo_id'])) ? $dev['modelo_id'] : null,
                    'modelo_texto' => (isset($dev['modelo_id']) && !is_numeric($dev['modelo_id'])) ? $dev['modelo_id'] : null,
                    'serie_imei' => $dev['serie_imei'] ?? null,
                    'tipo_seguridad' => $tipoSeguridad,
                    'clave_acceso' => $claveAcceso,
                    'relato_cliente' => $dev['observaciones'] ?? null,
                    'estado' => 'pendiente',
                    'prioridad_id' => !empty($dev['prioridad_dispositivo_id']) ? $dev['prioridad_dispositivo_id'] : null,
                    'costo_prioridad' => 0.00,
                    'tecnico_id' => !empty($dev['tecnico_id']) ? $dev['tecnico_id'] : null,
                ];

                if (!empty($dispData['prioridad_id'])) {
                    $prioInfo = $this->prioridadModel->find($dispData['prioridad_id']);
                    if ($prioInfo) {
                        $dispData['costo_prioridad'] = $prioInfo['costo_adicional'];
                        if ($prioInfo['tiempo_maximo_horas'] > 0) {
                            $dispData['fecha_estimada_entrega'] = date('Y-m-d H:i:s', strtotime("+{$prioInfo['tiempo_maximo_horas']} hours"));
                        }
                    }
                }

                $dispositivoId = $this->dispositivoModel->insert($dispData);

                if (!empty($dev['problema_reportado']) && is_array($dev['problema_reportado'])) {
                    foreach ($dev['problema_reportado'] as $probId) {
                        if (is_numeric($probId)) {
                            $dispositivoProblemaModel->insert([
                                'dispositivo_orden_id' => $dispositivoId,
                                'problema_id' => $probId,
                                'precio_mano_obra' => 0,
                                'precio_repuesto' => 0
                            ]);
                        }
                    }
                }

                $historialModel->insert([
                    'dispositivo_orden_id' => $dispositivoId,
                    'estado_anterior' => null,
                    'estado_nuevo' => 'pendiente',
                    'usuario_id' => session('id_usuario'),
                    'observacion' => 'Ingreso del dispositivo al sistema.',
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirectView('tecnico/ordenes/crear', null, [['Ocurrió un error al guardar la orden.', 'error', 'top-end']]);
            }

            // Fetch client data to get cedula for PDF printing
            $clienteModel = new \App\Models\ClienteModel();
            $clienteInfo = $clienteModel->find($clienteId);
            $cedulaCliente = $clienteInfo['cedula'] ?? '';

            $urlPdf = base_url('tecnico/ordenes/imprimir/' . $ordenId);

            return redirectView(
                'tecnico/dispositivos/pool',
                null,
                [['Orden ' . $numeroOrden . ' generada correctamente', 'success', 'center', $numeroOrden, $urlPdf, $cedulaCliente]],
                null
            );

        } catch (\Exception $e) {
            log_message('error', '[Tecnico/OrdenController::guardar] ' . $e->getMessage());
            return redirectView('tecnico/ordenes/crear', null, [['Error: ' . $e->getMessage(), 'error', 'top-end']]);
        }
    }

    public function imprimir(int $ordenId)
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
                'El presupuesto aprobado incluye únicamente los trabajos descritos en esta orden.',
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
        $options->set('chroot', FCPATH);

        $dompdf = new Dompdf($options);
        $html = view('admin/ordenes/pdf_orden', $data);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->stream(
            'Orden_' . $orden['numero_orden'] . '.pdf',
            ['Attachment' => false]
        );
    }
}
