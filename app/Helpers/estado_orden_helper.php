<?php
// app/Helpers/estado_orden_helper.php

if (!defined('ESTADO_ORDEN_ABIERTA')) {
    // Estados de Órdenes
    define('ESTADO_ORDEN_ABIERTA', 1);
    define('ESTADO_ORDEN_EN_PROCESO', 2);
    define('ESTADO_ORDEN_ESPERANDO_REPUESTO', 3);
    define('ESTADO_ORDEN_LISTA_RETIRO', 4);
    define('ESTADO_ORDEN_ENTREGADA', 5);
    define('ESTADO_ORDEN_GARANTIA', 6);
    define('ESTADO_ORDEN_CANCELADA', 7);
}

if (!function_exists('get_estados_orden')) {
    function get_estados_orden(): array
    {
        return [
            ESTADO_ORDEN_ABIERTA => [
                'nombre' => 'Abierta',
                'badge' => 'badge-secondary'
            ],
            ESTADO_ORDEN_EN_PROCESO => [
                'nombre' => 'En Proceso',
                'badge' => 'badge-info'
            ],
            ESTADO_ORDEN_ESPERANDO_REPUESTO => [
                'nombre' => 'Esperando Repuesto',
                'badge' => 'badge-warning'
            ],
            ESTADO_ORDEN_LISTA_RETIRO => [
                'nombre' => 'Lista para Retiro',
                'badge' => 'badge-success'
            ],
            ESTADO_ORDEN_ENTREGADA => [
                'nombre' => 'Entregada',
                'badge' => 'badge-black'
            ],
            ESTADO_ORDEN_GARANTIA => [
                'nombre' => 'En Garantía',
                'badge' => 'badge-danger'
            ],
            ESTADO_ORDEN_CANCELADA => [
                'nombre' => 'Cancelada',
                'badge' => 'badge-danger'
            ],
        ];
    }
}

if (!function_exists('get_estado_orden')) {
    function get_estado_orden(int $estado): ?array
    {
        $estados = get_estados_orden();
        return $estados[$estado] ?? null;
    }
}

if (!function_exists('get_nombre_estado_orden')) {
    function get_nombre_estado_orden(int $estado): string
    {
        $info = get_estado_orden($estado);
        return $info['nombre'] ?? 'Desconocido';
    }
}

if (!function_exists('get_badge_estado_orden')) {
    function get_badge_estado_orden(int $estado): string
    {
        switch ($estado) {
            case ESTADO_ORDEN_ABIERTA:
                return '<span class="badge badge-secondary">Abierta</span>';

            case ESTADO_ORDEN_EN_PROCESO:
                return '<span class="badge badge-info">En Proceso</span>';

            case ESTADO_ORDEN_ESPERANDO_REPUESTO:
                return '<span class="badge badge-warning">Esperando Repuesto</span>';

            case ESTADO_ORDEN_LISTA_RETIRO:
                return '<span class="badge badge-success">Lista para Retiro</span>';

            case ESTADO_ORDEN_ENTREGADA:
                return '<span class="badge badge-black">Entregada</span>';

            case ESTADO_ORDEN_GARANTIA:
                return '<span class="badge badge-danger">En Garantía</span>';

            case ESTADO_ORDEN_CANCELADA:
                return '<span class="badge badge-danger">Cancelada</span>';

            default:
                return '<span class="badge badge-secondary">Desconocido</span>';
        }
    }
}

if (!function_exists('calcular_estado_orden_automatico')) {
    /**
     * Calcula el estado de la orden según el estado de sus dispositivos
     *
     * LÓGICA SIMPLIFICADA:
     * - Si TODOS están entregados → Entregada
     * - Si TODOS están listos para retiro → Lista para Retiro
     * - Si ALGUNO está esperando repuesto → Esperando Repuesto
     * - Si ALGUNO está en garantía → En Garantía
     * - Si ALGUNO está en revisión/reparación → En Proceso
     * - Por defecto → Abierta
     */
    function calcular_estado_orden_automatico(array $dispositivos): int
    {
        if (empty($dispositivos)) {
            return ESTADO_ORDEN_ABIERTA;
        }

        $estados = array_column($dispositivos, 'estado_reparacion');
        $estados_unicos = array_unique($estados);

        // Todos entregados
        if (count($estados_unicos) === 1 && $estados[0] === ESTADO_DISPOSITIVO_ENTREGADO) {
            return ESTADO_ORDEN_ENTREGADA;
        }

        // Todos listos para retiro
        if (count($estados_unicos) === 1 && $estados[0] === ESTADO_DISPOSITIVO_LISTO_RETIRO) {
            return ESTADO_ORDEN_LISTA_RETIRO;
        }

        // Alguno en garantía
        if (in_array(ESTADO_DISPOSITIVO_GARANTIA, $estados)) {
            return ESTADO_ORDEN_GARANTIA;
        }

        // Alguno esperando repuesto
        if (in_array(ESTADO_DISPOSITIVO_ESPERANDO_REPUESTO, $estados)) {
            return ESTADO_ORDEN_ESPERANDO_REPUESTO;
        }

        // Alguno en proceso (revisión o reparación)
        if (
            array_intersect($estados, [
                ESTADO_DISPOSITIVO_EN_REVISION,
                ESTADO_DISPOSITIVO_COTIZADO,
                ESTADO_DISPOSITIVO_EN_REPARACION
            ])
        ) {
            return ESTADO_ORDEN_EN_PROCESO;
        }

        return ESTADO_ORDEN_ABIERTA;
    }
}

if (!function_exists('actualizar_estado_orden_auto')) {
    /**
     * Actualiza automáticamente el estado de la orden en la BD
     * Llamar después de cambiar el estado de un dispositivo
     */
    function actualizar_estado_orden_auto(int $orden_id): bool
    {
        $dispositivoModel = new \App\Models\DispositivoModel();
        $ordenModel = new \App\Models\OrdenTrabajoModel();

        $dispositivos = $dispositivoModel->where('orden_id', $orden_id)->findAll();
        $nuevo_estado = calcular_estado_orden_automatico($dispositivos);

        return $ordenModel->update($orden_id, [
            'estado' => $nuevo_estado,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}