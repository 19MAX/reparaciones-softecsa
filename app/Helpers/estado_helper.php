<?php

/**
 * Helper: Estado Dispositivo
 * Uso: <?= estadoDispositivo($dispositivo['estado']) ?>
 *      <?= estadoDispositivo($dispositivo['estado'], 'pill') ?>
 *      <?= estadoPill($dispositivo['estado']) ?>   // alias
 *      <?= estadoLabel($dispositivo['estado']) ?>  // solo texto formateado
 */

if (! function_exists('estadoDispositivo')) {

    /**
     * Devuelve un <span> badge listo para renderizar.
     *
     * @param string $estado  Valor crudo del estado (ej: 'en_proceso')
     * @param string $tipo    'badge' (default) | 'pill'
     * @return string         HTML del badge
     */
    function estadoDispositivo(string $estado, string $tipo = 'badge'): string
    {
        $config = _estadoConfig($estado);

        $claseBase = $tipo === 'pill'
            ? 'estado-pill estado-pill--' . $estado
            : 'badge estado-badge--' . $estado;

        return sprintf(
            '<span class="%s">%s%s</span>',
            esc($claseBase),
            $config['icon'] ? '<i class="' . $config['icon'] . ' me-1"></i>' : '',
            esc($config['label'])
        );
    }
}

if (! function_exists('estadoPill')) {
    /** Alias — devuelve siempre estilo pill */
    function estadoPill(string $estado): string
    {
        return estadoDispositivo($estado, 'pill');
    }
}

if (! function_exists('estadoBadge')) {
    /** Alias explícito — devuelve siempre estilo badge */
    function estadoBadge(string $estado): string
    {
        return estadoDispositivo($estado, 'badge');
    }
}

if (! function_exists('estadoLabel')) {
    /** Devuelve solo el texto formateado, sin HTML */
    function estadoLabel(string $estado): string
    {
        return _estadoConfig($estado)['label'];
    }
}

if (! function_exists('estadoColor')) {
    /** Devuelve el color hex principal del estado (útil para JS/gráficas) */
    function estadoColor(string $estado): string
    {
        return _estadoConfig($estado)['color'];
    }
}

if (! function_exists('_estadoConfig')) {
    /**
     * Configuración centralizada de estados.
     * Agrega aquí nuevos estados sin tocar el resto del código.
     *
     * @internal
     */
    function _estadoConfig(string $estado): array
    {
        $estados = [
            'pendiente' => [
                'label' => 'Pendiente',
                'icon'  => 'fas fa-clock',
                'color' => '#856404',
            ],
            'en_proceso' => [
                'label' => 'En Proceso',
                'icon'  => 'fas fa-tools',
                'color' => '#055160',
            ],
            'pausado' => [
                'label' => 'Pausado',
                'icon'  => 'fas fa-pause-circle',
                'color' => '#495057',
            ],
            'listo' => [
                'label' => 'Listo',
                'icon'  => 'fas fa-check-circle',
                'color' => '#0f5132',
            ],
            'entregado' => [
                'label' => 'Entregado',
                'icon'  => 'fas fa-box-open',
                'color' => '#383d41',
            ],
            'cancelado' => [
                'label' => 'Cancelado',
                'icon'  => 'fas fa-times-circle',
                'color' => '#842029',
            ],
        ];

        return $estados[$estado] ?? [
            'label' => ucfirst(str_replace('_', ' ', $estado)),
            'icon'  => 'fas fa-circle',
            'color' => '#6c757d',
        ];
    }
}