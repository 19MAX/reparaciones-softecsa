<?php

/**
 * EstadoHelper - CodeIgniter 4
 * Helper para formateo visual de estados de dispositivos en reparación.
 *
 * Uso: helper('estado') en tu controlador o vista.
 * Requiere Tailwind CSS CDN con @theme personalizado (primary-700, primary-800, primary-900).
 */

if (!function_exists('estado_label')) {
    /**
     * Convierte un estado en su etiqueta legible.
     * ej: "en_proceso" → "En Proceso"
     */
    function estado_label(string $estado): string
    {
        $labels = [
            'pendiente'  => 'Pendiente',
            'en_proceso' => 'En Proceso',
            'pausado'    => 'Pausado',
            'listo'      => 'Listo',
            'entregado'  => 'Entregado',
            'cancelado'  => 'Cancelado',
        ];

        return $labels[$estado] ?? ucwords(str_replace('_', ' ', $estado));
    }
}

if (!function_exists('estado_label_upper')) {
    /**
     * Igual que estado_label() pero en mayúsculas.
     * ej: "en_proceso" → "EN PROCESO"
     */
    function estado_label_upper(string $estado): string
    {
        return strtoupper(estado_label($estado));
    }
}

if (!function_exists('estado_clases')) {
    /**
     * Devuelve un array con las clases CSS para cada parte del estado.
     * Incluye: pill (badge), dot (indicador), icon_color, border, bg, text.
     *
     * @return array{pill: string, dot: string, icon_color: string, border: string, bg: string, text: string, glow: string}
     */
    function estado_clases(string $estado): array
    {
        $mapa = [
            'pendiente' => [
                'pill'       => 'status-pill status-pendiente',
                'dot'        => 'bg-amber-400',
                'icon_color' => 'text-amber-400',
                'border'     => 'border-amber-500/60',
                'bg'         => 'bg-amber-500/10',
                'text'       => 'text-amber-300',
                'glow'       => 'rgba(251, 191, 36, 0.15)',
            ],
            'en_proceso' => [
                'pill'       => 'status-pill status-proceso',
                'dot'        => 'bg-blue-400 pulse-dot',
                'icon_color' => 'text-blue-400',
                'border'     => 'border-blue-500/60',
                'bg'         => 'bg-blue-600/10',
                'text'       => 'text-blue-300',
                'glow'       => 'rgba(96, 165, 250, 0.15)',
            ],
            'pausado' => [
                'pill'       => 'status-pill status-pausado',
                'dot'        => 'bg-orange-400',
                'icon_color' => 'text-orange-400',
                'border'     => 'border-orange-500/60',
                'bg'         => 'bg-orange-500/10',
                'text'       => 'text-orange-300',
                'glow'       => 'rgba(251, 146, 60, 0.15)',
            ],
            'listo' => [
                'pill'       => 'status-pill status-listo',
                'dot'        => 'bg-emerald-400',
                'icon_color' => 'text-emerald-400',
                'border'     => 'border-emerald-500/60',
                'bg'         => 'bg-emerald-500/10',
                'text'       => 'text-emerald-300',
                'glow'       => 'rgba(52, 211, 153, 0.20)',
            ],
            'entregado' => [
                'pill'       => 'status-pill status-entregado',
                'dot'        => 'bg-violet-400',
                'icon_color' => 'text-violet-400',
                'border'     => 'border-violet-500/60',
                'bg'         => 'bg-violet-500/10',
                'text'       => 'text-violet-300',
                'glow'       => 'rgba(167, 139, 250, 0.15)',
            ],
            'cancelado' => [
                'pill'       => 'status-pill status-cancelado',
                'dot'        => 'bg-red-400',
                'icon_color' => 'text-red-400',
                'border'     => 'border-red-500/60',
                'bg'         => 'bg-red-500/10',
                'text'       => 'text-red-300',
                'glow'       => 'rgba(248, 113, 113, 0.15)',
            ],
        ];

        return $mapa[$estado] ?? [
            'pill'       => 'status-pill status-pendiente',
            'dot'        => 'bg-slate-400',
            'icon_color' => 'text-slate-400',
            'border'     => 'border-slate-600',
            'bg'         => 'bg-slate-700/20',
            'text'       => 'text-slate-400',
            'glow'       => 'rgba(148, 163, 184, 0.10)',
        ];
    }
}

if (!function_exists('estado_pill')) {
    /**
     * Renderiza el badge/pill completo de un estado como HTML.
     * Incluye el punto de color animado para estados activos.
     *
     * @param string $estado   El estado del dispositivo (ej: "en_proceso")
     * @param bool   $animated Si true, agrega pulse-dot al indicador (solo en_proceso)
     * @return string HTML del pill
     */
    function estado_pill(string $estado, bool $animated = true): string
    {
        $clases = estado_clases($estado);
        $label  = estado_label_upper($estado);
        $dotClass = $clases['dot'];

        // Solo animar si el estado es activo
        $activosAnimados = ['en_proceso'];
        if ($animated && in_array($estado, $activosAnimados)) {
            $dotClass .= ' pulse-dot';
        }
        return sprintf(
            '<span class="%s"><span class="w-1.5 h-1.5 rounded-full %s"></span>%s</span>',
            htmlspecialchars($clases['pill']),
            htmlspecialchars($dotClass),
            htmlspecialchars($label)
        );
    }
}

if (!function_exists('estado_icono_svg')) {
    /**
     * Devuelve el SVG del ícono correspondiente al estado (para el timeline).
     */
    function estado_icono_svg(string $estado): string
    {
        $iconos = [
            'pendiente' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'en_proceso' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
            'pausado' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            'listo' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>',
            'entregado' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>',
            'cancelado' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
        ];

        $path = $iconos[$estado] ?? $iconos['pendiente'];

        return sprintf(
            '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">%s</svg>',
            $path
        );
    }
}

if (!function_exists('estado_timeline_item')) {
    /**
     * Renderiza un ítem completo del historial de timeline.
     *
     * @param array $entrada Entrada del historial:
     *   - estado_anterior (string|null)
     *   - estado_nuevo    (string)
     *   - observacion_cliente (string|null)
     *   - fecha           (string)
     * @param bool  $esUltimo Si es el ítem más reciente (mayor opacidad, sin atenuación)
     * @return string HTML del ítem de timeline
     */
    function estado_timeline_item(array $entrada, bool $esUltimo = false): string
    {
        $estadoNuevo    = $entrada['estado_nuevo']    ?? 'pendiente';
        $estadoAnterior = $entrada['estado_anterior'] ?? null;
        $observacion    = $entrada['observacion_cliente'] ?? null;
        $fecha          = $entrada['fecha'] ?? '';

        $clases     = estado_clases($estadoNuevo);
        $icono      = estado_icono_svg($estadoNuevo);
        $fechaFmt   = estado_formato_fecha($fecha);
        $opacidad   = $esUltimo ? '' : ($estadoNuevo === 'pendiente' ? 'opacity-50' : 'opacity-90');
        $glow       = $esUltimo ? "box-shadow: 0 0 10px {$clases['glow']};" : '';

        // Transición de estado anterior → nuevo
        $transicion = '';
        if (!empty($estadoAnterior)) {
            $transicion = sprintf(
                '<span class="text-xs font-semibold text-slate-300">%s</span>
                 <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 7l5 5-5 5"/>
                 </svg>
                 %s',
                htmlspecialchars(estado_label($estadoAnterior)),
                estado_pill($estadoNuevo)
            );
        } else {
            // Primer estado (inicio)
            $transicion = sprintf(
                '<span class="text-xs font-semibold text-slate-400">Inicio</span>
                 <svg class="w-3 h-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 7l5 5-5 5"/>
                 </svg>
                 %s',
                estado_pill($estadoNuevo)
            );
        }

        // Bloque de observación (solo si existe)
        $bloqueObs = '';
        if (!empty(trim($observacion ?? ''))) {
            $bloqueObs = sprintf(
                '<div class="mt-2 p-2.5 rounded-lg bg-primary-800/40 border-l-2 %s text-[11px] text-slate-300">"%s"</div>',
                htmlspecialchars($clases['border']),
                htmlspecialchars($observacion)
            );
        }

        return sprintf(
            '<div class="relative flex items-start gap-4 %s">
                <div class="relative z-10 w-7 h-7 rounded-full %s border-2 %s flex items-center justify-center flex-shrink-0" style="%s">
                    <span class="%s">%s</span>
                </div>
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-2">%s</div>
                    <p class="mono text-[9px] text-slate-600 mt-1">%s</p>
                    %s
                </div>
            </div>',
            $opacidad,
            htmlspecialchars($clases['bg']),
            htmlspecialchars($clases['border']),
            $glow,
            htmlspecialchars($clases['icon_color']),
            $icono,
            $transicion,
            htmlspecialchars($fechaFmt),
            $bloqueObs
        );
    }
}

if (!function_exists('estado_timeline')) {
    /**
     * Renderiza el timeline completo del historial de un dispositivo.
     *
     * @param array $historial Array de entradas del historial (más reciente primero o último)
     * @param bool  $ordenReciente Si true, el último elemento del array es el más reciente
     * @return string HTML del timeline completo
     */
    function estado_timeline(array $historial, bool $ordenReciente = true): string
    {
        if (empty($historial)) {
            return '<p class="text-xs text-slate-600 italic">Sin historial disponible.</p>';
        }

        // Ordenar: más reciente primero en la visualización
        $items = $ordenReciente ? array_reverse($historial) : $historial;
        $html  = '<div class="relative space-y-6 pl-1"><div class="timeline-line"></div>';

        foreach ($items as $index => $entrada) {
            $esUltimo = ($index === 0); // El primero tras el reverse es el más reciente
            $html .= estado_timeline_item($entrada, $esUltimo);
        }

        $html .= '</div>';
        return $html;
    }
}

if (!function_exists('estado_formato_fecha')) {
    /**
     * Formatea una fecha de BD al formato "12 mar 2026 · 14:00".
     *
     * @param string $fecha Fecha en formato "Y-m-d H:i:s" o similar
     * @return string Fecha formateada
     */
    function estado_formato_fecha(string $fecha): string
    {
        if (empty($fecha)) return '—';

        try {
            $dt = new \DateTime($fecha);

            $meses = [
                1  => 'ene', 2  => 'feb', 3  => 'mar', 4  => 'abr',
                5  => 'may', 6  => 'jun', 7  => 'jul', 8  => 'ago',
                9  => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic',
            ];

            $dia  = $dt->format('j');
            $mes  = $meses[(int) $dt->format('n')];
            $anio = $dt->format('Y');
            $hora = $dt->format('H:i');

            return "{$dia} {$mes} {$anio} · {$hora}";
        } catch (\Exception $e) {
            return $fecha;
        }
    }
}