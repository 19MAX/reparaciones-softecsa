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
use PhpParser\Node\Expr\Print_;
class OrdenController extends BaseController
{

    public $configuracionModel;

    public function __construct()
    {
        $this->configuracionModel = new ConfiguracionModel();
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
        // 1. Instanciamos los modelos necesarios
        $urgenciaModel = new \App\Models\UrgenciaModel();
        $usuarioModel = new \App\Models\UsuarioModel();
        $checklistModel = new \App\Models\ChecklistItemModel(); // <--- AGREGAR ESTO

        // 2. Preparamos los datos
        $data = [
            'titulo' => 'Nueva Orden de Trabajo',

            // Traemos las urgencias activas
            'urgencias' => $urgenciaModel->where('activo', 1)->findAll(),

            // Traemos los técnicos activos
            'tecnicos' => $usuarioModel->where('role', 'tecnico')->where('estado', 'activo')->findAll(),

            // Traemos los items del checklist activos para dibujarlos en la vista
            'checklist_items' => $checklistModel->where('activo', 1)->findAll(), // <--- AGREGAR ESTO
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

            // A. Insertar Orden
            $codigoOrden = 'ORD-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));

            $ordenData = [
                'codigo_orden' => $codigoOrden,
                'cliente_id' => $clienteId,
                'usuario_id' => $usuarioId, // Aquí usamos la variable validada al inicio
                'tecnico_id' => $tecnicoId ?: null,
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

            // B. Snapshot de Términos y Condiciones
            // $terminosActivos = $terminosModel->where('activo', 1)->findAll();
            // foreach ($terminosActivos as $idx => $term) {
            //     $ordenTerminosModel->insert([
            //         'orden_id' => $ordenId,
            //         'termino_id' => $term['id'],
            //         'orden' => $idx + 1
            //     ]);
            // }

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

                $dispositivoInsert = [
                    'orden_id' => $ordenId,
                    'tipo' => $dev['tipo'],
                    'marca' => $dev['marca'],
                    'modelo' => $dev['modelo'],
                    'serie_imei' => $dev['serie_imei'] ?? null,
                    'problema_reportado' => $dev['problema_reportado'] ?? '', // Mapeo correcto del name del input
                    'tipo_pass' => $tipoPass,
                    'pass_code' => $passwordFinal,
                    'estado_reparacion' => 'Dispositivo recibido e ingresado',
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $dispositivoModel->insert($dispositivoInsert);
                $dispositivoId = $dispositivoModel->getInsertID();

                // D. Insertar Checklist del dispositivo actual
                if (isset($dev['checklist']) && is_array($dev['checklist'])) {
                    foreach ($dev['checklist'] as $checkId) {
                        $checklistRelModel->insert([
                            'dispositivo_id' => $dispositivoId,
                            'checklist_item_id' => $checkId,
                            'estado' => 1,
                            'observacion' => null
                        ]);
                    }
                }
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
        // 1. OBTENER DATOS DE LA BD
        $ordenModel = new OrdenTrabajoModel();
        $orden = $ordenModel->select('ordenes_trabajo.*, c.nombres, c.apellidos, c.telefono, c.email, c.cedula,u.nombre as nombre_urgencia')
            ->join('clientes as c', 'c.id = ordenes_trabajo.cliente_id')
            ->join('urgencias as u', 'u.id = ordenes_trabajo.urgencia_id', 'left')
            ->where('ordenes_trabajo.id', $id)
            ->first();

        if (!$orden) {
            return redirect()->back()->with('error', 'Orden no encontrada');
        }

        $urgenciaModel = new \App\Models\UrgenciaModel();
        // Sugerencia: Ordenarlas por recargo ASC o ID para que salgan ordenadas (Baja, Media, Alta)
        $urgencias = $urgenciaModel->where('activo', 1)->orderBy('recargo', 'ASC')->findAll();

        $dispositivoModel = new DispositivoModel();
        $dispositivos = $dispositivoModel->where('orden_id', $id)->findAll();

        // 2. GENERAR EL QR (SINTAXIS v5 CONFIRMADA)
        // La URL que el cliente escaneará para ver el estado
        $urlSeguimiento = base_url("consulta/orden/" . $orden['codigo_orden']);

        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $urlSeguimiento, // El contenido del QR
            encoding: new Encoding('UTF-8'),
            // errorCorrectionLevel: ErrorCorrectionLevel::High, // Enum
            size: 100, // Tamaño pequeño para que entre en la cabecera
            margin: 0, // Sin margen extra para aprovechar espacio
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

        $result = $builder->build();

        // El QR sí necesitamos convertirlo a Base64 porque se genera en memoria "al vuelo"
        $qrCodeBase64 = $result->getDataUri();

        // 3. DATOS DEL NEGOCIO

        $configuracion = $this->configuracionModel->first();



        // Asegúrate de que esta ruta sea correcta en tu servidor
        $rutaLogo = FCPATH . 'assets/img/logo.png';

        // Validación opcional para evitar errores si la imagen no existe
        if (!file_exists($rutaLogo)) {
            // Si no existe, puedes poner null o una imagen por defecto
            // log_message('error', 'Logo no encontrado en: ' . $rutaLogo);
        }

        // 4. PREPARAR DATOS PARA LA VISTA
        $data = [
            'orden' => $orden,
            'urgencias' => $urgencias,
            'dispositivos' => $dispositivos,
            'qr_code' => $qrCodeBase64, // String Base64 para el <img src>
            'logo_path' => $configuracion['logo_path'] ?? "",     // Ruta física para que Dompdf la lea
            'nombre_empresa' => $configuracion['nombre_empresa'] ?? 'Mi Empresa',
            'telefono_empresa' => $configuracion['telefono'] ?? '000-000-0000',
            'direccion_empresa' => $configuracion['direccion'] ?? '',
            'email_empresa' => $configuracion['email'] ?? '',
        ];

        // var_dump($data);
        // exit;

        // 5. CONFIGURAR DOMPDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        // Permitir acceso a la carpeta public (importante para el logo físico)
        $options->set('chroot', FCPATH);

        $dompdf = new Dompdf($options);

        $html = view('admin/ordenes/pdf_template', $data);

        $dompdf->loadHtml($html);

        // Formato Horizontal (Landscape) - A4 o Letter
        $dompdf->setPaper('A4', 'landscape');

        $dompdf->render();

        // Descargar o mostrar en navegador
        return $dompdf->stream("Orden_" . $orden['codigo_orden'] . ".pdf", ["Attachment" => false]);
    }
}
