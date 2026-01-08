<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DispositivoModel;
use App\Models\OrdenTrabajoModel;
use CodeIgniter\HTTP\ResponseInterface;

class ConsultaController extends BaseController
{
    public function verOrden($codigoOrden)
    {
        $ordenModel = new OrdenTrabajoModel();
        $dispositivoModel = new DispositivoModel();

        // 1. Buscar la orden por el Código (no por ID, por seguridad)
        $orden = $ordenModel->select('ordenes_trabajo.*, c.nombres, c.apellidos')
            ->join('clientes as c', 'c.id = ordenes_trabajo.cliente_id')
            ->where('ordenes_trabajo.codigo_orden', $codigoOrden)
            ->first();

        if (!$orden) {
            // Puedes crear una vista de error 404 personalizada bonita
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Orden no encontrada");
        }

        // 2. Obtener los dispositivos asociados y sus notas
        $dispositivos = $dispositivoModel->where('orden_id', $orden['id'])->findAll();

        // 3. Mapa de progreso para la barra visual (Basado en el ENUM que definimos antes)
        // El valor es el porcentaje de la barra de progreso
        $mapaProgreso = [
            'recibida' => 10,
            'en_diagnostico' => 25,
            'pendiente_aprobacion' => 40,
            'esperando_repuesto' => 50,
            'en_reparacion' => 70,
            'pruebas_calidad' => 85,
            'listo_para_retiro' => 100,
            'entregado' => 100,
            'cancelado' => 0, // Caso especial
            'irreparable' => 100 // Caso especial
        ];

        $progreso = $mapaProgreso[$orden['estado']] ?? 10;

        return view('publico/tracking_orden', [
            'orden' => $orden,
            'dispositivos' => $dispositivos,
            'progreso' => $progreso
        ]);
    }
    public function buscarPorCedula()
    {
        $cedula = $this->request->getGet('cedula'); // Obtenemos el parámetro de la URL
        $ordenes = [];
        $clienteInfo = null;

        if (!empty($cedula)) {
            $ordenModel = new \App\Models\OrdenTrabajoModel();

            // Buscamos órdenes + Datos del cliente + Nombre del dispositivo principal (opcional si quieres mostrarlo)
            $ordenes = $ordenModel->select('ordenes_trabajo.*, c.nombres, c.apellidos')
                ->join('clientes as c', 'c.id = ordenes_trabajo.cliente_id')
                ->where('c.cedula', $cedula)
                ->orderBy('ordenes_trabajo.created_at', 'DESC')
                ->findAll();

            // Si encontramos órdenes, tomamos los datos del cliente de la primera fila para el saludo
            if (!empty($ordenes)) {
                $clienteInfo = [
                    'nombres' => $ordenes[0]['nombres'],
                    'apellidos' => $ordenes[0]['apellidos']
                ];

                // Opcional: Cargar los dispositivos de cada orden para mostrarlos en la tarjeta
                $dispositivoModel = new \App\Models\DispositivoModel();
                foreach ($ordenes as &$orden) {
                    $orden['dispositivos'] = $dispositivoModel->where('orden_id', $orden['id'])->findAll();
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
