<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DispositivoModel;
use App\Models\HistorialDispositivoModel;
use App\Models\OrdenTrabajoModel;
use CodeIgniter\HTTP\ResponseInterface;

class ConsultaController extends BaseController
{
    public function verOrden($codigoOrden)
    {
        $ordenModel = new OrdenTrabajoModel();
        $dispositivoModel = new DispositivoModel();
        $historialModel = new HistorialDispositivoModel();

        // 1. Buscar la orden
        $orden = $ordenModel->select('ordenes_trabajo.*, c.nombres, c.apellidos')
            ->join('clientes as c', 'c.id = ordenes_trabajo.cliente_id')
            ->where('ordenes_trabajo.codigo_orden', $codigoOrden)
            ->first();

        if (!$orden) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Orden no encontrada");
        }

        // 2. Obtener dispositivos + Tipo de Dispositivo (Iconos/Nombres)
        $dispositivos = $dispositivoModel->select('dispositivos.*, td.nombre as nombre_tipo, td.icono')
            ->join('tipos_dispositivo as td', 'td.id = dispositivos.tipo_dispositivo_id', 'left')
            ->where('orden_id', $orden['id'])
            ->findAll();

        // 3. Inyectar el HISTORIAL a cada dispositivo
        foreach ($dispositivos as &$disp) {
            // Buscamos el historial visible para el cliente (o todo, según tu preferencia)
            // Asumimos que quieres mostrar solo lo marcado como "visible_cliente" o todo.
            // Aquí traigo todo, pero tú puedes filtrar ->where('es_visible_cliente', 1)
            $disp['historial'] = $historialModel
                ->select('historial_dispositivos.*, u.nombres as tecnico_nombre')
                ->join('usuarios as u', 'u.id = historial_dispositivos.usuario_id', 'left')
                ->where('dispositivo_id', $disp['id'])
                ->orderBy('created_at', 'DESC') // Lo más reciente primero
                ->findAll();
        }

        // 4. Calcular Progreso Visual basado en tus Constantes (Helpers)
        // Definimos un porcentaje visual aproximado para la barra
        $estado = (int) $orden['estado'];
        $progreso = 10; // Default: Abierta

        switch ($estado) {
            case ESTADO_ORDEN_ABIERTA:
                $progreso = 10;
                break;
            case ESTADO_ORDEN_EN_PROCESO:
                $progreso = 40;
                break;
            case ESTADO_ORDEN_ESPERANDO_REPUESTO:
                $progreso = 50;
                break;
            case ESTADO_ORDEN_LISTA_RETIRO:
                $progreso = 90;
                break;
            case ESTADO_ORDEN_ENTREGADA:
                $progreso = 100;
                break;
            case ESTADO_ORDEN_GARANTIA:
                $progreso = 100;
                break; // Barra llena pero color distinto
            case ESTADO_ORDEN_CANCELADA:
                $progreso = 100;
                break; // Barra llena pero roja
        }

        return view('publico/tracking_orden', [
            'orden' => $orden,
            'dispositivos' => $dispositivos,
            'progreso' => $progreso
        ]);
    }
    public function buscarPorCedula()
    {
        $cedula = $this->request->getGet('cedula');
        $ordenes = [];
        $clienteInfo = null;

        if (!empty($cedula)) {
            $ordenModel = new \App\Models\OrdenTrabajoModel();

            // 1. Buscar Órdenes
            $ordenes = $ordenModel->select('ordenes_trabajo.*, c.nombres, c.apellidos')
                ->join('clientes as c', 'c.id = ordenes_trabajo.cliente_id')
                ->where('c.cedula', $cedula)
                ->orderBy('ordenes_trabajo.created_at', 'DESC')
                ->findAll();

            if (!empty($ordenes)) {
                $clienteInfo = [
                    'nombres' => $ordenes[0]['nombres'],
                    'apellidos' => $ordenes[0]['apellidos']
                ];

                $dispositivoModel = new \App\Models\DispositivoModel();

                // 2. Cargar dispositivos con su TIPO e ICONO
                foreach ($ordenes as &$orden) {
                    $orden['dispositivos'] = $dispositivoModel
                        ->select('dispositivos.*, td.nombre as nombre_tipo, td.icono') // <--- AGREGADO
                        ->join('tipos_dispositivo as td', 'td.id = dispositivos.tipo_dispositivo_id', 'left') // <--- AGREGADO
                        ->where('orden_id', $orden['id'])
                        ->findAll();
                }
            }
        }

        return view('publico/busqueda_cedula', [
            'ordenes' => $ordenes,
            'cedula_buscada' => $cedula,
            'cliente' => $clienteInfo
        ]);
    }
}
