<?php

namespace App\Controllers\Recepcionista;

use App\Controllers\BaseController;
use App\Models\DispositivosOrdenModel;
use App\Models\HistorialEstados;
use App\Models\UsuarioModel;
use App\Models\OrdenesModel;
use App\Models\ClienteModel;
use App\Models\ConfiguracionModel;
use App\Models\TerminosCondicionesModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\PngWriter;

class DispositivoController extends BaseController
{
    protected $dispositivoModel;
    protected $historialModel;
    protected $usuarioModel;
    protected $ordenModel;
    protected $clienteModel;
    protected $configuracionModel;
    protected $terminosModel;

    public function __construct()
    {
        $this->dispositivoModel = new DispositivosOrdenModel();
        $this->historialModel = new HistorialEstados();
        $this->usuarioModel = new UsuarioModel();
        $this->ordenModel = new OrdenesModel();
        $this->clienteModel = new ClienteModel();
        $this->configuracionModel = new ConfiguracionModel();
        $this->terminosModel = new TerminosCondicionesModel();
    }

    public function entregarIndex()
    {
        $db = \Config\Database::connect();

        $dispositivos = $db->table('dispositivos_orden do')
            ->select([
                'do.id',
                'do.serie_imei',
                'do.fecha_estimada_entrega',
                'td.nombre as tipo',
                'm.nombre as marca',
                'mo.nombre as modelo',
                'do.modelo_texto',
                'o.numero_orden',
                'o.id as orden_id',
                'c.nombres',
                'c.apellidos',
                'c.telefono',
            ])
            ->join('ordenes o', 'o.id = do.orden_id')
            ->join('clientes c', 'c.id = o.cliente_id')
            ->join('tipos_dispositivo td', 'td.id = do.tipo_dispositivo_id', 'left')
            ->join('marcas m', 'm.id = do.marca_id', 'left')
            ->join('modelos mo', 'mo.id = do.modelo_id', 'left')
            ->where('do.estado', 'listo')
            ->orderBy('do.fecha_estimada_entrega', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'titulo' => 'Entregar Dispositivos',
            'dispositivos' => $dispositivos,
        ];

        return view('recepcionista/dispositivos/entregar', $data);
    }

    public function index()
    {
        $db = \Config\Database::connect();

        $dispositivos = $db->table('dispositivos_orden do')
            ->select([
                'do.id',
                'do.serie_imei',
                'do.estado',
                'do.created_at',
                'td.nombre as tipo',
                'm.nombre as marca',
                'mo.nombre as modelo',
                'do.modelo_texto',
                'o.numero_orden',
                'c.nombres',
                'c.apellidos',
            ])
            ->join('ordenes o', 'o.id = do.orden_id')
            ->join('clientes c', 'c.id = o.cliente_id')
            ->join('tipos_dispositivo td', 'td.id = do.tipo_dispositivo_id', 'left')
            ->join('marcas m', 'm.id = do.marca_id', 'left')
            ->join('modelos mo', 'mo.id = do.modelo_id', 'left')
            ->orderBy('do.created_at', 'DESC')
            ->get()
            ->getResultArray();

        $data = [
            'titulo' => 'Dispositivos',
            'dispositivos' => $dispositivos,
        ];

        return view('recepcionista/dispositivos/index', $data);
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

    public function detalle(int $dispositivoId)
    {

        $dispositivo = $this->dispositivoModel->getDetalleCompleto($dispositivoId);

        if (!$dispositivo) {
            return redirect()->to(base_url('ordenes'))
                ->with('error', 'Dispositivo no encontrado.');
        }

        // Lista de técnicos y admins activos para asignación
        $listaTecnicos = $this->usuarioModel->whereIn('rol', ['tecnico', 'admin'])->where('activo', 1)->findAll();

        $data = [
            'titulo' => 'Detalle del Dispositivo — ' . $dispositivo['codigo_orden'],
            'dispositivo' => $dispositivo,
            'listaTecnicos' => $listaTecnicos,
        ];

        return view('recepcionista/dispositivos/detalles', $data);
    }
    public function entregar()
    {
        $dispositivoId = (int) $this->request->getPost('dispositivo_id');

        if (!$dispositivoId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de dispositivo no proporcionado.',
            ])->setStatusCode(422);
        }

        $db = \Config\Database::connect();

        $dispositivo = $this->dispositivoModel->find($dispositivoId);

        if (!$dispositivo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Dispositivo no encontrado.',
            ])->setStatusCode(404);
        }

        if ($dispositivo['estado'] !== 'listo') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Solo se puede entregar un dispositivo que esté en estado "listo".',
            ])->setStatusCode(422);
        }

        $ahora = date('Y-m-d H:i:s');

        $db->transStart();

        $this->dispositivoModel->update($dispositivoId, [
            'estado' => 'entregado',
            'fecha_real_entrega' => $ahora,
        ]);

        $this->historialModel->insert([
            'dispositivo_orden_id' => $dispositivoId,
            'estado_anterior' => $dispositivo['estado'],
            'estado_nuevo' => 'entregado',
            'usuario_id' => session('id_usuario'),
            'observacion' => 'Dispositivo entregado al cliente.',
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al actualizar el estado del dispositivo.',
            ])->setStatusCode(500);
        }

        // Recalcular estado de la orden
        $ordenModel = new \App\Models\OrdenesModel();
        $ordenModel->recalcularEstado($dispositivo['orden_id']);
        (new \App\Services\ReparacionEmailService())
            ->enviarEntrega($dispositivoId);
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Dispositivo marcado como entregado.',
            'estado' => 'entregado',
            'fecha_real' => $ahora,
        ]);
    }
}