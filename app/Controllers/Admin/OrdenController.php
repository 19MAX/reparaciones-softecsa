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


    public function __construct()
    {
        $this->configuracionModel = new ConfiguracionModel();
        $this->urgenciaModel = new \App\Models\UrgenciaModel();
        $this->usuarioModel = new \App\Models\UsuarioModel();
        $this->tipoDispositivoModel = new \App\Models\TipoDispositivoModel();
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
            u.nombre as prioridad,
            u.recargo,
            (SELECT GROUP_CONCAT(CONCAT(tipo, " ", marca, " ", modelo) SEPARATOR ", ")
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

        return view('admin/ordenes/index', $data);
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

        // 1. Verificar Sesión (Solución al error usuario_id cannot be null)
        $usuarioId = session()->get('id_usuario');

        if (empty($usuarioId)) {
            // Si no hay sesión, redirigir al login o mostrar error
            return redirect()->to(base_url('login'))->with('mensaje', 'Tu sesión ha expirado.');
        }

        // DEBUG
        // $valores = $this->request->getPost();
        // var_dump($valores);
        // exit;

        try {
            // 2. Obtener datos del formulario
            $clienteId = $this->request->getPost('cliente_id');
            $tecnicoId = $this->request->getPost('tecnico_id');
            $urgenciaId = $this->request->getPost('urgencia_id');
            $devices = $this->request->getPost('devices');
            $valor_mano_obra_Aproximado = $this->request->getPost('valor_mano_obra_aproximado') ?? 0.00;
            $valor_repuesto_Aproximado = $this->request->getPost('valor_repuesto_aproximado') ?? 0.00;
            // 2.1 Obtener el valor de la revison desde configuración_empresa
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
                    'rules' => 'required|is_not_unique[clientes.id]', // Debe existir en la tabla clientes
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
                // Usamos tu helper redirectView para volver a la vista de crear
                // Nota: Asegúrate de que 'admin/ordenes/crear' reciba de nuevo los datos para no perderlos
                return redirectView('admin/ordenes/crear', $validation, [['Corrija los errores del formulario', 'error', 'top-end']], $data);
            }

            // Validación manual extra: Verificar que devices sea un array válido
            if (empty($devices) || !is_array($devices)) {
                return redirectView('admin/ordenes/crear', null, [['Debe agregar al menos un dispositivo', 'error', 'top-end']], $data);
            }

            // ---------------------------------------------------
            // 4. LOGICA DE GUARDADO (Transacción)
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
                'usuario_id' => $usuarioId, // Aquí usamos la variable validada al inicio
                'tecnico_id' => null,
                'urgencia_id' => $urgenciaId ?: null,
                'estado' => 'abierta',
                'created_at' => date('Y-m-d H:i:s'),
                'mano_obra' => 0,
                'valor_repuestos' => 0,
                'valor_revision' => $valorRevision,
                'mano_obra_aproximado' => $valor_mano_obra_Aproximado,
                'repuestos_aproximado' => $valor_repuesto_Aproximado,
                'total' => $valorRevision, // Incluir el valor de la revisión
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
                    'tecnico_id' => $tecnicoDispositivo, // <--- AQUÍ SE GUARDA AHORA
                    'marca' => $dev['marca'],
                    'modelo' => $dev['modelo'],
                    'serie_imei' => $dev['serie_imei'] ?? null,
                    'problema_reportado' => $dev['problema_reportado'] ?? '', // Mapeo correcto del name del input
                    'tipo_pass' => $tipoPass,
                    'pass_code' => $passwordFinal,
                    'estado_reparacion' => 'Dispositivo recibido e ingresado',
                    'observaciones' => $dev['observaciones'] ?? null,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $dispositivoModel->insert($dispositivoInsert);
                $dispositivoId = $dispositivoModel->getInsertID();
                // C. Crear Historial Inicial (IMPORTANTE PARA TUS MÉTRICAS)
                $historialModel->insert([
                    'dispositivo_id' => $dispositivoId,
                    'usuario_id' => $usuarioId, // Quien recibe el equipo (Recepcionista)
                    'estado_anterior' => null,
                    'estado_nuevo' => 'recibida',
                    'comentario' => 'Ingreso del equipo a taller. ' . ($tecnicoDispositivo ? 'Asignado a técnico.' : 'Sin asignar.'),
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // D. Insertar Checklist del dispositivo actual
                // if (isset($dev['checklist']) && is_array($dev['checklist'])) {
                //     foreach ($dev['checklist'] as $checkId) {
                //         $checklistRelModel->insert([
                //             'dispositivo_id' => $dispositivoId,
                //             'checklist_item_id' => $checkId,
                //             'estado' => 1,
                //             'observacion' => null
                //         ]);
                //     }
                // }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                // Si falla la BD, forzamos excepción para caer en el catch
                throw new \Exception('Error de base de datos al confirmar la orden.');
            }

            // ÉXITO: Redirigimos al listado (o a imprimir)
            return redirectView('admin/ordenes', null, [['Orden ' . $codigoOrden . ' generada exitosamente', 'success', 'top-end']], null);

        } catch (\Exception $e) {
            log_message('error', '[OrdenController::guardar] ' . $e->getMessage());

            // Usamos redirectView para volver al formulario con el mensaje de error y los datos previos
            return redirectView('admin/ordenes/crear', null, [['Error del sistema: ' . $e->getMessage(), 'error', 'top-end']], $data ?? []);
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
}
