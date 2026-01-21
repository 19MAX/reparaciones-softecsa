<?php

namespace App\Controllers\Recepcionista;

use App\Controllers\BaseController;
use App\Models\ChecklistDispositivoModel;
use App\Models\ClienteModel;
use App\Models\ConfiguracionModel;
use App\Models\DispositivoModel;
use App\Models\OrdenTrabajoModel;
use App\Models\UrgenciaModel;
use CodeIgniter\HTTP\ResponseInterface;
// --- IMPORTS PARA QR CODE Y PDF ---
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
        $db = \Config\Database::connect();

        // Construimos la consulta
        $builder = $db->table('ordenes_trabajo as o');
        $builder->select('
            o.*,
            c.nombres,
            c.apellidos,
            u.nombre as nombre_prioridad,
            u.recargo as recargo_prioridad,
            u.descripcion as descripcion_prioridad,
            (SELECT GROUP_CONCAT(CONCAT(marca, " ", modelo) SEPARATOR ", ")
             FROM dispositivos d WHERE d.orden_id = o.id) as equipos_resumen
        ');

        $builder->join('clientes as c', 'c.id = o.cliente_id');
        $builder->join('urgencias as u', 'u.id = o.urgencia_id', 'left');

        $builder->orderBy('o.id', 'DESC');

        $ordenes = $builder->get()->getResultArray();

        $data = [
            'titulo' => 'Gestión de Órdenes',
            'ordenes' => $ordenes
        ];

        return view('recepcionista/ordenes/index', $data);
    }

    public function crear()
    {
        $data = [
            'titulo' => 'Nueva Orden de Trabajo',
            'urgencias' => $this->urgenciaModel->where('activo', 1)->findAll(),
            'tecnicos' => $this->usuarioModel->where('role', 'tecnico')->where('estado', 'activo')->findAll(),
            'tiposDispositivos' => $this->tipoDispositivoModel->where('activo', 1)->findAll(),
        ];

        return view('recepcionista/ordenes/crear', $data);
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
            $tecnicoId = $this->request->getPost('tecnico_id');
            $urgenciaId = $this->request->getPost('urgencia_id');
            $devices = $this->request->getPost('devices');
            $valor_mano_obra_Aproximado = $this->request->getPost('valor_mano_obra_aproximado') ?? 0.00;
            $valor_repuesto_Aproximado = $this->request->getPost('valor_repuesto_aproximado') ?? 0.00;

            // 2.1 Obtener el valor de la revisión desde configuración_empresa
            $configuracionModel = new ConfiguracionModel();
            $configuracion = $configuracionModel->first();
            $valorRevision = $configuracion['valor_revision'] ?? 0.00;

            // Preparar datos para repopular el formulario en caso de error
            $data = [
                'cliente_id' => $clienteId,
                'tecnico_id' => $tecnicoId,
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
                'tecnico_id' => [
                    'label' => 'Técnico',
                    'rules' => 'permit_empty|is_not_unique[usuarios.id]',
                ],
                'urgencia_id' => [
                    'label' => 'Prioridad/Urgencia',
                    'rules' => 'permit_empty|is_not_unique[urgencias.id]',
                ],
                'devices' => [
                    'label' => 'Dispositivos',
                    'rules' => 'required',
                ],
                'devices.*.tecnico_id' => [
                    'label' => 'Técnico del dispositivo',
                    'rules' => 'permit_empty|is_not_unique[usuarios.id]',
                ]
            ];

            $validation->setRules($rules);

            if (!$validation->run($data)) {
                return redirectView('recepcionista/ordenes/crear', $validation, [['Corrija los errores del formulario', 'error', 'top-end']], $data);
            }

            // Validación manual extra: Verificar que devices sea un array válido
            if (empty($devices) || !is_array($devices)) {
                return redirectView('recepcionista/ordenes/crear', null, [['Debe agregar al menos un dispositivo', 'error', 'top-end']], $data);
            }

            // ---------------------------------------------------
            // 4. LÓGICA DE GUARDADO (Transacción)
            // ---------------------------------------------------
            $db = \Config\Database::connect();
            $db->transStart();

            // Instanciar Modelos
            $ordenModel = new \App\Models\OrdenTrabajoModel();
            $dispositivoModel = new \App\Models\DispositivoModel();
            $checklistRelModel = new \App\Models\ChecklistDispositivoModel();
            $historialModel = new \App\Models\HistorialDispositivoModel();

            // A. Insertar Orden
            $codigoOrden = 'ORD-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));

            $ordenData = [
                'codigo_orden' => $codigoOrden,
                'cliente_id' => $clienteId,
                'usuario_id' => $usuarioId,
                'tecnico_id' => null,
                'urgencia_id' => $urgenciaId ?: null,
                'estado' => 'recibida',
                'created_at' => date('Y-m-d H:i:s'),
                'mano_obra' => 0,
                'valor_repuestos' => 0,
                'valor_revision' => $valorRevision,
                'mano_obra_aproximado' => $valor_mano_obra_Aproximado,
                'repuestos_aproximado' => $valor_repuesto_Aproximado,
                'total' => $valorRevision,
            ];

            $ordenModel->insert($ordenData);
            $ordenId = $ordenModel->getInsertID();

            // C. Insertar Dispositivos (Loop)
            foreach ($devices as $dev) {
                // Lógica Pass/Patrón
                $passwordFinal = '';
                $tipoPass = $dev['tipo_pass'] ?? 'ninguno';

                if ($tipoPass === 'patron') {
                    $passwordFinal = $dev['patron_data'] ?? '';
                } elseif (in_array($tipoPass, ['contrasena', 'pin'])) {
                    $passwordFinal = $dev['pass_code'] ?? '';
                }

                // Obtener el técnico específico de este dispositivo
                $tecnicoDispositivo = !empty($dev['tecnico_id']) ? $dev['tecnico_id'] : null;

                $dispositivoInsert = [
                    'orden_id' => $ordenId,
                    'tipo_dispositivo_id' => $dev['tipo_dispositivo_id'] ?? null,
                    'tecnico_id' => $tecnicoDispositivo,
                    'marca' => $dev['marca'],
                    'modelo' => $dev['modelo'],
                    'serie_imei' => $dev['serie_imei'] ?? null,
                    'problema_reportado' => $dev['problema_reportado'] ?? '',
                    'tipo_pass' => $tipoPass,
                    'pass_code' => $passwordFinal,
                    'estado_reparacion' => 'Dispositivo recibido e ingresado',
                    'observaciones' => $dev['observaciones'] ?? null,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $dispositivoModel->insert($dispositivoInsert);
                $dispositivoId = $dispositivoModel->getInsertID();

                // C. Crear Historial Inicial
                $historialModel->insert([
                    'dispositivo_id' => $dispositivoId,
                    'usuario_id' => $usuarioId,
                    'estado_anterior' => null,
                    'estado_nuevo' => 'recibida',
                    'comentario' => 'Ingreso del equipo a taller. ' . ($tecnicoDispositivo ? 'Asignado a técnico.' : 'Sin asignar.'),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error de base de datos al confirmar la orden.');
            }

            // ÉXITO: Redirigimos al listado
            return redirectView('recepcionista/ordenes', null, [['Orden ' . $codigoOrden . ' generada exitosamente', 'success', 'top-end']], null);

        } catch (\Exception $e) {
            log_message('error', '[Recepcionista/OrdenController::guardar] ' . $e->getMessage());
            return redirectView('recepcionista/ordenes/crear', null, [['Error del sistema: ' . $e->getMessage(), 'error', 'top-end']], $data ?? []);
        }
    }

    public function ver($id)
    {
        $db = \Config\Database::connect();

        // Obtener orden con datos del cliente
        $ordenModel = new OrdenTrabajoModel();
        $orden = $ordenModel->select('ordenes_trabajo.*, c.nombres, c.apellidos, c.telefono, c.email, c.cedula, u.nombre as nombre_urgencia, u.recargo')
            ->join('clientes as c', 'c.id = ordenes_trabajo.cliente_id')
            ->join('urgencias as u', 'u.id = ordenes_trabajo.urgencia_id', 'left')
            ->where('ordenes_trabajo.id', $id)
            ->first();

        if (!$orden) {
            return redirect()->back()->with('error', 'Orden no encontrada');
        }

        // Obtener dispositivos de la orden
        $dispositivoModel = new DispositivoModel();
        $dispositivos = $dispositivoModel->select('dispositivos.*, td.nombre as nombre_tipo, td.icono')
            ->join('tipos_dispositivo as td', 'td.id = dispositivos.tipo_dispositivo_id', 'left')
            ->where('orden_id', $id)
            ->findAll();

        $data = [
            'titulo' => 'Detalles de Orden - ' . $orden['codigo_orden'],
            'orden' => $orden,
            'dispositivos' => $dispositivos
        ];

        return view('recepcionista/ordenes/ver', $data);
    }

    public function imprimir($id)
    {
        // 1. CARGAR MODELOS
        $ordenModel = new OrdenTrabajoModel();
        $dispositivoModel = new DispositivoModel();
        $urgenciaModel = new \App\Models\UrgenciaModel();
        $configuracionModel = new ConfiguracionModel();
        $terminosModel = new \App\Models\TerminosCondicionesModel();

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

        // 4. LÓGICA DE TÉRMINOS Y CONDICIONES
        $tiposIds = [];
        foreach ($dispositivos as $disp) {
            if (!empty($disp['tipo_dispositivo_id'])) {
                $tiposIds[] = $disp['tipo_dispositivo_id'];
            }
        }
        $tiposIds = array_unique($tiposIds);

        $builder = $terminosModel->builder();
        $builder->where('activo', 1);
        $builder->groupStart();
        $builder->where('tipo_dispositivo_id', null);
        if (!empty($tiposIds)) {
            $builder->orWhereIn('tipo_dispositivo_id', $tiposIds);
        }
        $builder->groupEnd();
        $builder->orderBy('tipo_dispositivo_id IS NOT NULL', 'ASC', false);
        $terminos = $builder->get()->getResultArray();

        // 5. GENERAR EL QR
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

        // 6. CONFIGURACIÓN EMPRESA
        $configuracion = $configuracionModel->first();

        // 7. PREPARAR DATOS VISTA
        $data = [
            'orden' => $orden,
            'urgencias' => $urgencias,
            'dispositivos' => $dispositivos,
            'qr_code' => $qrCodeBase64,
            'logo_path' => $configuracion['logo_path'] ?? "",
            'nombre_empresa' => $configuracion['nombre_empresa'] ?? 'Mi Empresa',
            'telefono_empresa' => $configuracion['telefono'] ?? '',
            'direccion_empresa' => $configuracion['direccion'] ?? '',
            'email_empresa' => isset($configuracion['email']) ? $configuracion['email'] : '',
            'terminos' => $terminos
        ];

        // 8. RENDERIZAR PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', FCPATH);

        $dompdf = new Dompdf($options);
        $html = view('recepcionista/ordenes/pdf_template', $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->stream("Orden_" . $orden['codigo_orden'] . ".pdf", ["Attachment" => false]);
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
