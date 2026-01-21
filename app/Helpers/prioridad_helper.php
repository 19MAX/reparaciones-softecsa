<?php

if (!function_exists('get_badge_urgencia')) {
    /**
     * Genera un badge visual para mostrar la urgencia
     * 
     * @param array $urgencia - Registro completo de la tabla urgencias
     * @return string HTML del badge
     */
    function get_badge_urgencia(?array $urgencia): string
    {

        // Si no hay registro o no existe el recargo
        if (
            empty($urgencia) || !isset($urgencia['urgencia_id'])
        ) {
            return '<span class="badge badge-count">Sin Prioridad</span>';
        }

        if (!$urgencia) {
            return '<span class="badge badge-count">Sin Prioridad</span>';
        }

        // Mapeo de badges según el nivel de urgencia (basado en recargo)
        $recargo = (float) $urgencia['recargo_prioridad'];

        if ($recargo === 0.00) {
            $badge_class = 'badge-secondary';
        } elseif ($recargo <= 5) {
            $badge_class = 'badge-success';
        } elseif ($recargo >= 10) {
            $badge_class = 'badge-warning';
        } else {
            $badge_class = 'badge-danger';
        }

        return sprintf(
            '<span class="badge %s" title="%s">%s%s</span>',
            $badge_class,
            esc($urgencia['descripcion_prioridad'] ?? ''),
            esc($urgencia['nombre_prioridad']),
            ($recargo > 0) ? ' (+$' . number_format($recargo, 2) . ')' : ''
        );
    }
}

if (!function_exists('get_select_urgencias')) {
    /**
     * Genera las opciones de <select> para urgencias
     * 
     * @param int|null $seleccionado - ID de la urgencia seleccionada
     * @param bool $incluir_vacio - Si incluir opción "Sin prioridad"
     * @return string HTML de las opciones
     */
    function get_select_urgencias(?int $seleccionado = null, bool $incluir_vacio = true): string
    {
        $urgenciaModel = new \App\Models\UrgenciaModel();
        $urgencias = $urgenciaModel->where('activo', 1)->orderBy('recargo', 'ASC')->findAll();

        $html = '';

        if ($incluir_vacio) {
            $selected = (is_null($seleccionado)) ? 'selected' : '';
            $html .= sprintf('<option value="" %s>Sin Prioridad</option>', $selected);
        }

        foreach ($urgencias as $urgencia) {
            $selected = ($seleccionado === (int) $urgencia['id']) ? 'selected' : '';

            $texto = esc($urgencia['nombre']);

            if (!empty($urgencia['tiempo_espera'])) {
                $texto .= ' (' . esc($urgencia['tiempo_espera']) . ')';
            }

            if ((float) $urgencia['recargo'] > 0) {
                $texto .= ' - Recargo: $' . number_format($urgencia['recargo'], 2);
            }

            $html .= sprintf(
                '<option value="%d" %s>%s</option>',
                $urgencia['id'],
                $selected,
                $texto
            );
        }

        return $html;
    }
}

if (!function_exists('get_nombre_urgencia')) {
    /**
     * Obtiene solo el nombre de una urgencia por su ID
     * 
     * @param int|null $urgencia_id
     * @return string
     */
    function get_nombre_urgencia(?int $urgencia_id): string
    {
        if (!$urgencia_id) {
            return 'Sin Prioridad';
        }

        $urgenciaModel = new \App\Models\UrgenciaModel();
        $urgencia = $urgenciaModel->find($urgencia_id);

        return $urgencia ? esc($urgencia['nombre']) : 'Desconocida';
    }
}

if (!function_exists('get_recargo_urgencia')) {
    /**
     * Obtiene el valor del recargo de una urgencia
     * 
     * @param int|null $urgencia_id
     * @return float
     */
    function get_recargo_urgencia(?int $urgencia_id): float
    {
        if (!$urgencia_id) {
            return 0.00;
        }

        $urgenciaModel = new \App\Models\UrgenciaModel();
        $urgencia = $urgenciaModel->find($urgencia_id);

        return $urgencia ? (float) $urgencia['recargo'] : 0.00;
    }
}

if (!function_exists('validar_limite_urgencias')) {
    /**
     * Valida que no haya más de 6 urgencias activas
     * Útil al crear/editar urgencias
     * 
     * @return bool
     */
    function validar_limite_urgencias(): bool
    {
        $urgenciaModel = new \App\Models\UrgenciaModel();
        $total = $urgenciaModel->where('activo', 1)->countAllResults();

        return $total < 6;
    }
}

if (!function_exists('get_urgencias_activas')) {
    /**
     * Obtiene todas las urgencias activas ordenadas por recargo
     * 
     * @return array
     */
    function get_urgencias_activas(): array
    {
        $urgenciaModel = new \App\Models\UrgenciaModel();
        return $urgenciaModel->where('activo', 1)->orderBy('recargo', 'ASC')->findAll();
    }
}

if (!function_exists('calcular_total_con_urgencia')) {
    /**
     * Calcula el total de una orden incluyendo el recargo por urgencia
     * 
     * @param float $subtotal - Mano de obra + Repuestos + Revisión
     * @param int|null $urgencia_id
     * @return float
     */
    function calcular_total_con_urgencia(float $subtotal, ?int $urgencia_id): float
    {
        $recargo = get_recargo_urgencia($urgencia_id);
        return $subtotal + $recargo;
    }
}