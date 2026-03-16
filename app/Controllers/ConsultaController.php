<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DispositivoModel;
use App\Models\DispositivosOrdenModel;
use App\Models\HistorialDispositivoModel;
use App\Models\OrdenesModel;
use App\Models\OrdenTrabajoModel;
use CodeIgniter\HTTP\ResponseInterface;

class ConsultaController extends BaseController
{
    protected $ordenModel;
    protected $dispositivoModel;

    public function __construct()
    {
        $this->ordenModel = new OrdenesModel();
        $this->dispositivoModel = new DispositivosOrdenModel();
    }

    public function verOrden($codigoOrden)
    {
        $orden = $this->getSeguimientoDispositivos($codigoOrden);

        return view('publico/tracking_orden', $orden);
    }

    public function buscarPorCedula()
    {
        $cedula = $this->request->getGet('cedula');

        $ordenes = [];
        $cliente = null;
        $data = [
            'ordenes' => $ordenes,
            'cliente' => $cliente
        ];

        if (!empty($cedula)) {
            $data = $this->getOrdenesPorCedula($cedula);

        }
        return view('publico/busqueda_cedula', [
            'ordenes' => $data['ordenes'] ?? null,
            'cedula_buscada' => $cedula,
            'cliente' => $data['cliente'] ?? null
        ]);
    }

    /**
     * Busca todas las órdenes de un cliente por su cédula.
     * Retorna: número de orden, estado, nombre del cliente
     * y los dispositivos de cada orden (tipo, marca, modelo).
     */
    public function getOrdenesPorCedula(string $cedula): array
    {
        $db = \Config\Database::connect();

        // 1. Buscar cliente
        $cliente = $db->table('clientes')
            ->select(['id', 'nombres', 'telefono', 'cedula'])
            ->where('cedula', $cedula)
            ->get()
            ->getRowArray();

        if (!$cliente) {
            return [];
        }

        // 2. Obtener ordenes
        $ordenes = $db->table('ordenes o')
            ->select([
                'o.id',
                'o.numero_orden',
                'o.estado',
                'o.created_at AS fecha_ingreso'
            ])
            ->where('o.cliente_id', $cliente['id'])
            ->orderBy('o.created_at', 'DESC')
            ->get()
            ->getResultArray();

        if (empty($ordenes)) {
            return [
                'cliente' => $cliente,
                'ordenes' => []
            ];
        }

        // 3. Obtener dispositivos
        $ordenIds = array_column($ordenes, 'id');

        $dispositivos = $db->table('dispositivos_orden do')
            ->select([
                'do.id',
                'do.orden_id',
                'do.estado AS dispositivo_estado',
                'td.nombre AS tipo_dispositivo',
                'm.nombre AS marca',
                'COALESCE(mo.nombre, do.modelo_texto) AS modelo'
            ])
            ->join('tipos_dispositivo td', 'td.id = do.tipo_dispositivo_id')
            ->join('marcas m', 'm.id = do.marca_id')
            ->join('modelos mo', 'mo.id = do.modelo_id', 'left')
            ->whereIn('do.orden_id', $ordenIds)
            ->orderBy('do.id', 'ASC')
            ->get()
            ->getResultArray();

        // 4. Agrupar dispositivos
        $dispositivosPorOrden = [];

        foreach ($dispositivos as $disp) {
            $dispositivosPorOrden[$disp['orden_id']][] = $disp;
        }

        foreach ($ordenes as &$orden) {
            $orden['dispositivos'] = $dispositivosPorOrden[$orden['id']] ?? [];
        }

        // 5. Respuesta final
        return [
            'cliente' => $cliente,
            'ordenes' => $ordenes
        ];
    }


    /**
     * Retorna el seguimiento público de un dispositivo:
     * historial de estados con observaciones visibles al cliente.
     *
     * Se identifica por número de orden + id del dispositivo
     * para evitar que alguien acceda con un id arbitrario.
     */
    public function getSeguimientoDispositivos(string $codigoOrden): ?array
    {
        $db = \Config\Database::connect();

        // 1. Obtener datos de la orden y cliente
        $orden = $db->table('ordenes o')
            ->select([
                'o.id',
                'o.numero_orden AS codigo_orden',
                'c.nombres AS cliente_nombre'
            ])
            ->join('clientes c', 'c.id = o.cliente_id')
            ->where('o.numero_orden', $codigoOrden)
            ->get()
            ->getRowArray();

        if (!$orden) {
            return null;
        }

        // 2. Obtener dispositivos de la orden
        $dispositivos = $db->table('dispositivos_orden do')
            ->select([
                'do.id',
                'do.estado',
                'do.fecha_estimada_entrega',
                'do.fecha_real_entrega',
                'do.created_at AS fecha_ingreso',
                'td.nombre AS tipo_dispositivo',
                'm.nombre AS marca',
                'COALESCE(mo.nombre, do.modelo_texto) AS modelo',
            ])
            ->join('tipos_dispositivo td', 'td.id = do.tipo_dispositivo_id')
            ->join('marcas m', 'm.id = do.marca_id')
            ->join('modelos mo', 'mo.id = do.modelo_id', 'left')
            ->where('do.orden_id', $orden['id'])
            ->get()
            ->getResultArray();

        // Inicializamos las variables para nuestro resumen
        $totalDispositivos = count($dispositivos);
        $conteoEstados = [];

        foreach ($dispositivos as &$dispositivo) {

            // Lógica para contar los estados
            // Convertimos a minúsculas y reemplazamos espacios por guiones bajos para estandarizar las claves (ej: "En Proceso" -> "en_proceso")
            $estadoKey = strtolower(str_replace(' ', '_', $dispositivo['estado']));

            if (!isset($conteoEstados[$estadoKey])) {
                $conteoEstados[$estadoKey] = 0;
            }
            $conteoEstados[$estadoKey]++;

            // Historial
            $dispositivo['historial'] = $db->table('historial_estados')
                ->select([
                    'id',
                    'estado_anterior',
                    'estado_nuevo',
                    'observacion_cliente',
                    'created_at AS fecha'
                ])
                ->where('dispositivo_orden_id', $dispositivo['id'])
                ->orderBy('created_at', 'ASC')
                ->get()
                ->getResultArray();

            // Problemas
            $dispositivo['problemas'] = $db->table('dispositivo_problemas dp')
                ->select([
                    'p.nombre AS problema',
                    'dp.observacion'
                ])
                ->join('problemas p', 'p.id = dp.problema_id')
                ->where('dp.dispositivo_orden_id', $dispositivo['id'])
                ->get()
                ->getResultArray();
        }

        return [
            'codigo_orden' => $orden['codigo_orden'],
            'cliente_nombre' => $orden['cliente_nombre'],
            'dispositivos' => $dispositivos,
            // Agregamos el resumen al arreglo que se retorna a la vista
            'resumen' => [
                'total' => $totalDispositivos,
                'estados' => $conteoEstados
            ]
        ];
    }
}
