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
                'urgencia_id' => $this->request->getPost('urgencia_id') ?: null,
            ];

            $ordenId = $this->ordenModel->insert($ordenData);

            $historialModel = new HistorialEstados();
            $dispositivoProblemaModel = new DispositivoProblemaModel();

            foreach ($devices as $dev) {
                $dispData = [
                    'orden_id' => $ordenId,
                    'tipo_dispositivo_id' => $dev['tipo_dispositivo_id'] ?? null,
                    'marca_id' => $dev['marca_id'] ?? null,
                    'modelo_id' => (isset($dev['modelo_id']) && is_numeric($dev['modelo_id'])) ? $dev['modelo_id'] : null,
                    'modelo_texto' => (isset($dev['modelo_id']) && !is_numeric($dev['modelo_id'])) ? $dev['modelo_id'] : null,
                    'serie_imei' => $dev['serie_imei'] ?? null,
                    'tipo_pass' => $dev['tipo_pass'] ?? 'ninguno',
                    'pass_code' => $dev['pass_code'] ?? null,
                    'patron_data' => $dev['patron_data'] ?? null,
                    'observaciones' => $dev['observaciones'] ?? null,
                    'accesorios' => isset($dev['accesorios']) ? json_encode($dev['accesorios']) : null,
                    'detalles_dispositivo' => isset($dev['detalles']) ? json_encode($dev['detalles']) : null,
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
                return redirect()->back()->withInput()->with('error', 'Ocurrió un error al guardar la orden.');
            }

            return redirect()->to(base_url('tecnico/dispositivos/pool'))->with('success', 'Orden creada correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
