<?php
// app/Helpers/estado_dispositivo_helper.php

if (!defined('ESTADO_DISPOSITIVO_INGRESADO')) {
    // Estados de Dispositivos
    define('ESTADO_DISPOSITIVO_INGRESADO', 1);
    define('ESTADO_DISPOSITIVO_EN_REVISION', 2);
    define('ESTADO_DISPOSITIVO_COTIZADO', 3);
    define('ESTADO_DISPOSITIVO_ESPERANDO_REPUESTO', 4);
    define('ESTADO_DISPOSITIVO_EN_REPARACION', 5);
    define('ESTADO_DISPOSITIVO_LISTO_RETIRO', 6);
    define('ESTADO_DISPOSITIVO_ENTREGADO', 7);
    define('ESTADO_DISPOSITIVO_GARANTIA', 8);
    define('ESTADO_DISPOSITIVO_NO_REPARADO', 9);
}

if (!function_exists('get_estados_dispositivo')) {
    function get_estados_dispositivo(): array
    {
        return [
            ESTADO_DISPOSITIVO_INGRESADO => [
                'nombre' => 'Ingresado',
                'badge' => 'badge-secondary'
            ],
            ESTADO_DISPOSITIVO_EN_REVISION => [
                'nombre' => 'En Revisión',
                'badge' => 'badge-info'
            ],
            ESTADO_DISPOSITIVO_COTIZADO => [
                'nombre' => 'Cotizado',
                'badge' => 'badge-warning'
            ],
            ESTADO_DISPOSITIVO_ESPERANDO_REPUESTO => [
                'nombre' => 'Esperando Repuesto',
                'badge' => 'badge-warning'
            ],
            ESTADO_DISPOSITIVO_EN_REPARACION => [
                'nombre' => 'En Reparación',
                'badge' => 'badge-primary'
            ],
            ESTADO_DISPOSITIVO_LISTO_RETIRO => [
                'nombre' => 'Listo para Retiro',
                'badge' => 'badge-success'
            ],
            ESTADO_DISPOSITIVO_ENTREGADO => [
                'nombre' => 'Entregado',
                'badge' => 'badge-black'
            ],
            ESTADO_DISPOSITIVO_GARANTIA => [
                'nombre' => 'En Garantía',
                'badge' => 'badge-danger'
            ],
            ESTADO_DISPOSITIVO_NO_REPARADO => [
                'nombre' => 'No Reparado',
                'badge' => 'badge-danger'
            ],
        ];
    }
}

if (!function_exists('get_estado_dispositivo')) {
    function get_estado_dispositivo(int $estado): ?array
    {
        $estados = get_estados_dispositivo();
        return $estados[$estado] ?? null;
    }
}

if (!function_exists('get_nombre_estado_dispositivo')) {
    function get_nombre_estado_dispositivo(int $estado): string
    {
        $info = get_estado_dispositivo($estado);
        return $info['nombre'] ?? 'Desconocido';
    }
}

if (!function_exists('get_badge_estado_dispositivo')) {
    function get_badge_estado_dispositivo(int $estado): string
    {
        switch ($estado) {
            case ESTADO_DISPOSITIVO_INGRESADO:
                return '<span class="badge badge-secondary">Ingresado</span>';

            case ESTADO_DISPOSITIVO_EN_REVISION:
                return '<span class="badge badge-info">En Revisión</span>';

            case ESTADO_DISPOSITIVO_COTIZADO:
                return '<span class="badge badge-warning">Cotizado</span>';

            case ESTADO_DISPOSITIVO_ESPERANDO_REPUESTO:
                return '<span class="badge badge-warning">Esperando Repuesto</span>';

            case ESTADO_DISPOSITIVO_EN_REPARACION:
                return '<span class="badge badge-primary">En Reparación</span>';

            case ESTADO_DISPOSITIVO_LISTO_RETIRO:
                return '<span class="badge badge-success">Listo para Retiro</span>';

            case ESTADO_DISPOSITIVO_ENTREGADO:
                return '<span class="badge badge-black">Entregado</span>';

            case ESTADO_DISPOSITIVO_GARANTIA:
                return '<span class="badge badge-danger">En Garantía</span>';

            case ESTADO_DISPOSITIVO_NO_REPARADO:
                return '<span class="badge badge-danger">No Reparado</span>';

            default:
                return '<span class="badge badge-secondary">Desconocido</span>';
        }
    }
}

if (!function_exists('get_select_estados_dispositivo')) {
    /**
     * Genera las opciones de <select> para cambiar estado
     */
    function get_select_estados_dispositivo(?int $estado_actual = null): string
    {
        $estados = get_estados_dispositivo();
        $html = '';

        foreach ($estados as $id => $info) {
            $selected = ($estado_actual === $id) ? 'selected' : '';
            $html .= sprintf(
                '<option value="%d" %s>%s</option>',
                $id,
                $selected,
                esc($info['nombre'])
            );
        }

        return $html;
    }
}