<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Detalle del Dispositivo<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    /* ── Paleta y variables ───────────────────────── */
    :root {
        --surface: #ffffff;
        --surface-2: #f7f8fa;
        --border: #e8eaed;
        --text-primary: #1a1d23;
        --text-muted: #8a92a0;
        --accent: #2563eb;
        --radius: 10px;
    }

    /* ── Layout ────────────────────────────────────── */
    .dv-wrapper {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 20px;
        max-width: 1200px;
    }

    /* ── Card base ─────────────────────────────────── */
    .dv-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
    }

    /* ── Header del dispositivo ────────────────────── */
    .dv-device-header {
        padding: 20px;
        border-bottom: 1px solid var(--border);
    }

    .dv-device-name {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.3;
        margin: 0 0 6px;
    }

    .dv-imei {
        font-size: .78rem;
        color: var(--text-muted);
        font-family: monospace;
        letter-spacing: .03em;
    }

    /* ── Info rows ──────────────────────────────────── */
    .dv-info-rows {
        padding: 0;
    }

    .dv-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 13px 20px;
        border-bottom: 1px solid var(--border);
        gap: 12px;
    }

    .dv-info-row:last-child {
        border-bottom: none;
    }

    .dv-label {
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--text-muted);
        white-space: nowrap;
        flex-shrink: 0;
    }

    .dv-value {
        font-size: .9rem;
        font-weight: 600;
        color: var(--text-primary);
        text-align: right;
    }

    .dv-value.muted {
        font-weight: 400;
        color: var(--text-muted);
        font-style: italic;
    }

    .dv-value a {
        color: var(--accent);
        text-decoration: none;
    }

    .dv-value a:hover {
        text-decoration: underline;
    }

    /* ── Cliente link ───────────────────────────────── */
    .dv-cliente-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        color: var(--accent);
        font-size: .9rem;
        font-weight: 600;
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        text-decoration: none;
        transition: opacity .15s;
    }

    .dv-cliente-btn:hover {
        opacity: .75;
        text-decoration: underline;
    }

    .dv-cliente-btn i {
        font-size: .75rem;
    }

    /* ── Acción principal ───────────────────────────── */
    .dv-action-area {
        padding: 16px 20px;
        border-top: 1px solid var(--border);
        background: var(--surface-2);
    }

    .dv-btn-action {
        width: 100%;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: .88rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: opacity .15s, transform .1s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .dv-btn-action:hover {
        opacity: .88;
        transform: translateY(-1px);
    }

    .dv-btn-action:active {
        transform: translateY(0);
    }

    .btn-start {
        background: #2563eb;
        color: #fff;
    }

    .btn-finish {
        background: #10b981;
        color: #fff;
    }

    .btn-deliver {
        background: #0ea5e9;
        color: #fff;
    }

    .dv-delivered-badge {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        background: #d1fae5;
        color: #065f46;
        text-align: center;
        font-size: .85rem;
        font-weight: 600;
    }

    /* ── Clave badge ────────────────────────────────── */
    .dv-lock-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 6px;
        font-size: .78rem;
        font-weight: 600;
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        cursor: pointer;
        transition: background .15s;
    }

    .dv-lock-btn:hover {
        background: #fde68a;
    }

    /* ── Tabs ───────────────────────────────────────── */
    .dv-tabs {
        display: flex;
        gap: 0;
        padding: 0 20px;
        border-bottom: 1px solid var(--border);
        background: var(--surface);
        overflow-x: auto;
    }

    .dv-tab {
        padding: 14px 16px;
        font-size: .83rem;
        font-weight: 600;
        color: var(--text-muted);
        border: none;
        background: none;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        transition: color .15s, border-color .15s;
        white-space: nowrap;
    }

    .dv-tab.active {
        color: var(--accent);
        border-bottom-color: var(--accent);
    }

    .dv-tab:hover:not(.active) {
        color: var(--text-primary);
    }

    .dv-tab-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #fee2e2;
        color: #b91c1c;
        font-size: .68rem;
        font-weight: 700;
        margin-left: 5px;
    }

    /* ── Tab panes ──────────────────────────────────── */
    .dv-tab-body {
        padding: 20px;
        min-height: 220px;
    }

    .dv-pane {
        display: none;
    }

    .dv-pane.active {
        display: block;
    }

    /* ── Problemas table ────────────────────────────── */
    .dv-problems-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .85rem;
    }

    .dv-problems-table th {
        text-align: left;
        padding: 8px 10px;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--text-muted);
        background: var(--surface-2);
        border-bottom: 1px solid var(--border);
    }

    .dv-problems-table td {
        padding: 11px 10px;
        border-bottom: 1px solid var(--border);
        color: var(--text-primary);
        vertical-align: middle;
    }

    .dv-problems-table tr:last-child td {
        border-bottom: none;
    }

    .dv-problems-table .amount {
        text-align: right;
        color: var(--text-muted);
        font-variant-numeric: tabular-nums;
    }

    /* ── Relato cliente ─────────────────────────────── */
    .dv-relato {
        margin-top: 16px;
        padding: 12px 14px;
        background: var(--surface-2);
        border-radius: 8px;
        border-left: 3px solid var(--border);
    }

    .dv-relato .relato-label {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--text-muted);
        margin-bottom: 5px;
    }

    .dv-relato p {
        font-size: .88rem;
        color: var(--text-primary);
        margin: 0;
    }

    /* ── Cobros ─────────────────────────────────────── */
    .dv-cobro-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
        font-size: .88rem;
    }

    .dv-cobro-row:last-child {
        border-bottom: none;
    }

    .dv-cobro-row.total {
        padding-top: 12px;
        font-weight: 700;
        font-size: 1rem;
    }

    .dv-cobro-row.total .dv-cobro-amount {
        color: #10b981;
        font-size: 1.15rem;
    }

    .dv-cobro-amount {
        font-weight: 600;
        font-variant-numeric: tabular-nums;
    }

    .dv-cobro-priority {
        color: #ef4444;
    }

    /* ── Timeline ───────────────────────────────────── */
    .dv-timeline {
        list-style: none;
        padding: 0;
        margin: 0;
        position: relative;
    }

    .dv-timeline::before {
        content: '';
        position: absolute;
        left: 17px;
        top: 8px;
        bottom: 0;
        width: 2px;
        background: var(--border);
    }

    .dv-tl-item {
        display: flex;
        gap: 14px;
        padding-bottom: 20px;
        position: relative;
    }

    .dv-tl-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--border);
        border: 2px solid var(--surface);
        margin-top: 5px;
        flex-shrink: 0;
        position: relative;
        z-index: 1;
        margin-left: 12px;
    }

    .dv-tl-dot.current {
        background: var(--accent);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .15);
    }

    .dv-tl-dot.success {
        background: #10b981;
    }

    .dv-tl-dot.warning {
        background: #f59e0b;
    }

    .dv-tl-dot.danger {
        background: #ef4444;
    }

    .dv-tl-content {
        flex: 1;
    }

    .dv-tl-state {
        font-size: .85rem;
        font-weight: 700;
        color: var(--text-primary);
    }

    .dv-tl-arrow {
        color: var(--text-muted);
        font-size: .75rem;
        margin: 0 4px;
    }

    .dv-tl-prev {
        color: var(--text-muted);
        font-weight: 400;
    }

    .dv-tl-meta {
        font-size: .75rem;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .dv-tl-obs {
        font-size: .82rem;
        color: var(--text-primary);
        margin-top: 5px;
        padding: 8px 10px;
        background: var(--surface-2);
        border-radius: 6px;
    }

    /* ── Accesorios / detalles ──────────────────────── */
    .dv-badge-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .dv-badge-item {
        padding: 5px 12px;
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: .83rem;
        color: var(--text-primary);
    }

    /* ── Empty state ────────────────────────────────── */
    .dv-empty {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-muted);
        font-size: .88rem;
    }

    /* ── Campos internos del modal Bootstrap ───────── */
    .dv-prob-card {
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 14px;
        margin-bottom: 12px;
    }

    .dv-prob-name {
        font-size: .88rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 12px;
    }

    .dv-field-group {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }

    .dv-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .dv-field label {
        font-size: .73rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--text-muted);
    }

    .dv-field-hint {
        font-size: .7rem;
        color: var(--text-muted);
        margin-top: 2px;
    }

    .dv-input {
        padding: 7px 10px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: .85rem;
        color: var(--text-primary);
        background: var(--surface);
        outline: none;
        transition: border-color .15s;
        width: 100%;
        box-sizing: border-box;
    }

    .dv-input:focus {
        border-color: var(--accent);
    }

    .dv-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238a92a0' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        padding-right: 28px;
    }

    .dv-obs-field {
        resize: vertical;
        min-height: 60px;
    }

    .dv-final-comment {
        margin-top: 12px;
    }

    .dv-final-comment>label {
        display: block;
        font-size: .73rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--text-muted);
        margin-bottom: 6px;
    }

    /* ── Modal info cliente ─────────────────────────── */
    .cliente-info-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
        font-size: .88rem;
    }

    .cliente-info-row:last-child {
        border-bottom: none;
    }

    .cliente-info-label {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--text-muted);
        min-width: 90px;
        flex-shrink: 0;
        padding-top: 1px;
    }

    .cliente-info-value {
        color: var(--text-primary);
        font-weight: 500;
    }

    /* ── Movimiento (compartido con historial global) ── */
    .movement-cell {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .movement-row {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .movement-arrow {
        color: #adb5bd;
        font-size: 12px;
        flex-shrink: 0;
    }

    .movement-label {
        font-size: 10px;
        color: #adb5bd;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    /* ── Breadcrumb override ────────────────────────── */
    .page-header {
        margin-bottom: 16px;
    }

    /* ── Responsive ─────────────────────────────────── */
    @media (max-width: 900px) {
        .dv-wrapper {
            grid-template-columns: 1fr;
        }

        .dv-field-group {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Breadcrumb -->
<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home"><a href="<?= base_url('tecnico/dashboard') ?>"><i class="icon-home"></i></a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="<?= base_url('tecnico/dispositivos/pool') ?>">Pool de Dispositivos</a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Detalle Dispositivo</a></li>
    </ul>
</div>

<?php
$estado = $dispositivo['estado'] ?? '';
$tienePass = $dispositivo['tipo_pass'] !== 'sin_clave'
    && !empty($dispositivo['clave_acceso'])
    && !empty($dispositivo['tipo_pass']);
?>

<div class="dv-wrapper">

    <!-- ══ Columna izquierda ══ -->
    <div>
        <div class="dv-card">

            <!-- Encabezado -->
            <div class="dv-device-header">
                <p class="dv-device-name">
                    <?= esc($dispositivo['tipo_dispositivo']) ?>
                    <?= esc($dispositivo['marca']) ?>
                    <?= !empty($dispositivo['modelo']) ? esc($dispositivo['modelo']) : '' ?>
                </p>

                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-top:8px;">

                    <!-- ── Estado usando el helper ── -->
                    <?= estadoPill($estado) ?>

                    <!-- Clave (solo si existe) -->
                    <?php if ($tienePass): ?>
                        <button class="dv-lock-btn" data-bs-toggle="modal" data-bs-target="#modalClave">
                            <i class="fas fa-lock" style="font-size:.7rem;"></i>
                            <?= ucfirst(esc($dispositivo['tipo_pass'])) ?>
                        </button>
                    <?php endif; ?>

                    <!-- IMEI si existe -->
                    <?php if (!empty($dispositivo['serie_imei'])): ?>
                        <span class="dv-imei"><?= esc($dispositivo['serie_imei']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Filas de info -->
            <div class="dv-info-rows">

                <div class="dv-info-row">
                    <span class="dv-label">Orden</span>
                    <span class="dv-value">
                        <span class="badge bg-light text-dark border">
                            <?= esc($dispositivo['codigo_orden']) ?>
                        </span>
                    </span>
                </div>

                <!-- Cliente — abre modal con info completa -->
                <div class="dv-info-row">
                    <span class="dv-label">Cliente</span>
                    <span class="dv-value">
                        <button class="dv-cliente-btn" data-bs-toggle="modal" data-bs-target="#modalCliente">
                            <?= esc($dispositivo['cliente_nombre']) ?>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </span>
                </div>

                <div class="dv-info-row">
                    <span class="dv-label">Técnico</span>
                    <span class="dv-value" id="seccion-tecnico">
                        <?php if ($dispositivo['tecnico_nombre']): ?>
                            <span><?= esc($dispositivo['tecnico_nombre']) ?></span>
                        <?php else: ?>
                            <span class="muted" style="font-size:.8rem;">Sin asignar</span>
                        <?php endif; ?>
                    </span>
                </div>

                <?php if (!empty($dispositivo['prioridad'])): ?>
                    <div class="dv-info-row">
                        <span class="dv-label">Prioridad</span>
                        <span class="dv-value"><?= esc($dispositivo['prioridad']) ?></span>
                    </div>
                <?php endif; ?>

                <div class="dv-info-row">
                    <span class="dv-label">Entrega estimada</span>
                    <span class="dv-value <?= empty($dispositivo['fecha_estimada_entrega']) ? 'muted' : '' ?>">
                        <?= !empty($dispositivo['fecha_estimada_entrega'])
                            ? formatear_fecha($dispositivo['fecha_estimada_entrega'], 'solo_fecha')
                            : '—' ?>
                    </span>
                </div>

                <?php if (!empty($dispositivo['fecha_real_entrega'])): ?>
                    <div class="dv-info-row">
                        <span class="dv-label">Entrega real</span>
                        <span
                            class="dv-value"><?= formatear_fecha($dispositivo['fecha_real_entrega'], 'solo_fecha') ?></span>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Acción principal -->
            <div class="dv-action-area">
                <?php if ($estado === 'pendiente'): ?>
                    <button class="dv-btn-action btn-start" onclick="iniciarReparacion(<?= $dispositivo['id'] ?>)">
                        <i class="fas fa-play me-1"></i> Iniciar Reparación
                    </button>
                <?php elseif ($estado === 'en_proceso'): ?>
                    <button class="dv-btn-action btn-finish" onclick="abrirModalFinalizar(event)">
                        <i class="fas fa-check me-1"></i> Finalizar Reparación
                    </button>
                <?php elseif ($estado === 'listo'): ?>
                    <div class="dv-delivered-badge" style="background:#d1fae5;color:#065f46;">
                        <i class="fas fa-check-double me-1"></i> Listo para entrega
                    </div>
                <?php elseif ($estado === 'entregado'): ?>
                    <div class="dv-delivered-badge"><i class="fas fa-check-circle me-1"></i> Dispositivo entregado</div>
                <?php else: ?>
                    <div class="dv-delivered-badge" style="background:#f3f4f6;color:#6b7280;">Reparación concluida</div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- ══ Columna derecha — tabs ══ -->
    <div>
        <div class="dv-card">

            <div class="dv-tabs" role="tablist">
                <button class="dv-tab active" onclick="switchTab('problemas', this)">
                    Problemas
                    <?php if (!empty($dispositivo['problemas'])): ?>
                        <span class="dv-tab-count"><?= count($dispositivo['problemas']) ?></span>
                    <?php endif; ?>
                </button>
                <button class="dv-tab" onclick="switchTab('accesorios', this)">Accesorios</button>
                <button class="dv-tab" onclick="switchTab('historial', this)">Historial</button>
                <button class="dv-tab" onclick="switchTab('cobros', this)">Cobro</button>
            </div>

            <div class="dv-tab-body">

                <!-- PROBLEMAS -->
                <div id="pane-problemas" class="dv-pane active">
                    <?php if (!empty($dispositivo['problemas'])): ?>
                        <table class="dv-problems-table">
                            <thead>
                                <tr>
                                    <th>Problema</th>
                                    <th class="amount">Mano de Obra</th>
                                    <th class="amount">Repuesto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dispositivo['problemas'] as $p): ?>
                                    <tr>
                                        <td><?= esc($p['problema']) ?></td>
                                        <td class="amount">$<?= number_format($p['default_mano_obra'], 2) ?></td>
                                        <td class="amount">$<?= number_format($p['default_repuesto'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (!empty($dispositivo['relato_cliente'])): ?>
                            <div class="dv-relato">
                                <p class="relato-label">El cliente reporta</p>
                                <p><?= esc($dispositivo['relato_cliente']) ?></p>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="dv-empty">Sin problemas registrados</div>
                    <?php endif; ?>
                </div>

                <!-- ACCESORIOS -->
                <div id="pane-accesorios" class="dv-pane">
                    <?php if (!empty($dispositivo['accesorios']) || !empty($dispositivo['detalles'])): ?>
                        <?php if (!empty($dispositivo['accesorios'])): ?>
                            <p class="dv-label mb-2">Accesorios entregados</p>
                            <div class="dv-badge-list mb-3">
                                <?php foreach ($dispositivo['accesorios'] as $acc): ?>
                                    <span class="dv-badge-item"><?= esc($acc['accesorio']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($dispositivo['detalles'])): ?>
                            <p class="dv-label mb-2 mt-3">Estado físico</p>
                            <div class="dv-badge-list">
                                <?php foreach ($dispositivo['detalles'] as $d): ?>
                                    <span class="dv-badge-item"><?= esc($d['detalle']) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="dv-empty">Sin accesorios registrados</div>
                    <?php endif; ?>
                </div>

                <!-- HISTORIAL -->
                <div id="pane-historial" class="dv-pane">
                    <?php if (!empty($dispositivo['historial'])): ?>
                        <?php
                        // Más reciente primero
                        $historialOrdenado = array_reverse($dispositivo['historial']);
                        $total = count($historialOrdenado);
                        ?>
                        <ul class="dv-timeline">
                            <?php foreach ($historialOrdenado as $i => $h):
                                $hn = strtolower($h['estado_nuevo'] ?? '');
                                $esUltimo = ($i === $total - 1); // el más antiguo = ingreso original
                                $dotClass = match (true) {
                                    $i === 0 && str_contains($hn, 'entregad') => 'success',
                                    $i === 0 && str_contains($hn, 'cancel') => 'danger',
                                    $i === 0 => 'current',
                                    str_contains($hn, 'entregad') => 'success',
                                    str_contains($hn, 'cancel') => 'danger',
                                    str_contains($hn, 'espera') => 'warning',
                                    default => '',
                                };

                                $estadoAnterior = $h['estado_anterior'] ?? null;
                                $estadoNuevo = $h['estado_nuevo'] ?? 'pendiente';
                                $hasChange = $estadoAnterior && $estadoAnterior !== $estadoNuevo;
                                ?>
                                <li class="dv-tl-item">
                                    <div class="dv-tl-dot <?= $dotClass ?>"></div>
                                    <div class="dv-tl-content">

                                        <!-- Movimiento con las clases del historial global -->
                                        <div class="movement-cell" style="align-items: flex-start;">
                                            <div class="movement-row">
                                                <?php if ($hasChange): ?>
                                                    <?= estadoPill($estadoAnterior) ?>
                                                    <i class="fas fa-arrow-right movement-arrow"></i>
                                                    <?= estadoPill($estadoNuevo) ?>
                                                <?php else: ?>
                                                    <?= estadoPill($estadoNuevo) ?>
                                                <?php endif; ?>
                                            </div>
                                            <span class="movement-label">
                                                <?php if ($hasChange): ?>
                                                    cambio de estado
                                                <?php elseif ($esUltimo): ?>
                                                    ingreso al sistema
                                                <?php else: ?>
                                                    nota manual
                                                <?php endif; ?>
                                            </span>
                                        </div>

                                        <!-- Meta: usuario + fecha -->
                                        <div class="dv-tl-meta" style="margin-top: 5px;">
                                            <?= esc($h['usuario']) ?>
                                            <?php if (!empty($h['fecha'])): ?>
                                                &nbsp;·&nbsp;
                                                <i class="fas fa-clock" style="font-size:.65rem;"></i>
                                                <?= formatear_fecha($h['fecha']) ?>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Nota técnica interna -->
                                        <?php if (!empty($h['observacion'])): ?>
                                            <div class="dv-tl-obs" style="margin-top: 6px;">
                                                <small
                                                    style="color:#8a92a0;font-weight:700;text-transform:uppercase;font-size:.65rem;display:block;margin-bottom:2px;">
                                                    Nota Técnica
                                                </small>
                                                <?= nl2br(esc($h['observacion'])) ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Comentario para el cliente -->
                                        <?php if (!empty($h['observacion_cliente'])): ?>
                                            <div class="dv-tl-obs"
                                                style="border-left:3px solid #10b981;background:#ecfdf5;margin-top:4px;">
                                                <small
                                                    style="color:#059669;font-weight:700;text-transform:uppercase;font-size:.65rem;display:block;margin-bottom:2px;">
                                                    <i class="fas fa-comment me-1"></i>Para el Cliente
                                                </small>
                                                <?= nl2br(esc($h['observacion_cliente'])) ?>
                                            </div>
                                        <?php endif; ?>

                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="dv-empty">Sin historial de cambios</div>
                    <?php endif; ?>
                </div>

                <!-- COBROS -->
                <div id="pane-cobros" class="dv-pane">
                    <?php
                    $totalManoObra = 0;
                    $totalRepuestos = 0;
                    foreach ($dispositivo['problemas'] as $p) {
                        $totalManoObra += (float) $p['precio_mano_obra'];
                        $totalRepuestos += (float) $p['precio_repuesto'];
                    }
                    $finalizado = in_array($estado, ['listo', 'entregado']);
                    ?>
                    <?php if ($finalizado): ?>
                        <div class="dv-cobro-row">
                            <span style="color:var(--text-muted);">Mano de Obra</span>
                            <span class="dv-cobro-amount">$<?= number_format($totalManoObra, 2) ?></span>
                        </div>
                        <div class="dv-cobro-row">
                            <span style="color:var(--text-muted);">Repuestos</span>
                            <span class="dv-cobro-amount">$<?= number_format($totalRepuestos, 2) ?></span>
                        </div>
                        <?php if ((float) $dispositivo['costo_prioridad'] > 0): ?>
                            <div class="dv-cobro-row">
                                <span style="color:var(--text-muted);">Cargo prioridad
                                    (<?= esc($dispositivo['prioridad']) ?>)</span>
                                <span
                                    class="dv-cobro-amount dv-cobro-priority">+$<?= number_format($dispositivo['costo_prioridad'], 2) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="dv-cobro-row total">
                            <span>Total cobrado</span>
                            <span class="dv-cobro-amount"
                                style="color:#10b981;">$<?= number_format($dispositivo['precio_total'], 2) ?></span>
                        </div>
                    <?php elseif (!empty($dispositivo['precio_total'])): ?>
                        <div class="dv-cobro-row total">
                            <span>Precio estimado</span>
                            <span class="dv-cobro-amount"
                                style="color:#3b82f6;">$<?= number_format($dispositivo['precio_total'], 2) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="dv-empty">Sin precio definido aún</div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

</div><!-- /dv-wrapper -->


<!-- ══════════════════════════════════════════
     Modal — Info del Cliente (Bootstrap 5)
════════════════════════════════════════════ -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div
                        style="width:38px;height:38px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-user" style="color:#2563eb;font-size:.9rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" style="font-size:.95rem;">
                            <?= esc($dispositivo['cliente_nombre']) ?>
                        </h5>
                        <small class="text-muted" style="font-size:.75rem;">Datos del cliente</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <div style="border-top: 1px solid var(--border); margin-bottom: 4px;"></div>

                <?php if (!empty($dispositivo['cliente_cedula'])): ?>
                    <div class="cliente-info-row">
                        <span class="cliente-info-label">Cédula / RUC</span>
                        <span class="cliente-info-value"><?= esc($dispositivo['cliente_cedula']) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($dispositivo['cliente_telefono'])): ?>
                    <div class="cliente-info-row">
                        <span class="cliente-info-label">Teléfono</span>
                        <span class="cliente-info-value">
                            <a href="tel:<?= esc($dispositivo['cliente_telefono']) ?>" class="text-decoration-none"
                                style="color:var(--accent);">
                                <i class="fas fa-phone me-1" style="font-size:.75rem;"></i>
                                <?= esc($dispositivo['cliente_telefono']) ?>
                            </a>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($dispositivo['cliente_whatsapp'] ?? $dispositivo['cliente_telefono'])): ?>
                    <div class="cliente-info-row">
                        <span class="cliente-info-label">WhatsApp</span>
                        <span class="cliente-info-value">
                            <a href="https://wa.me/<?= preg_replace('/\D/', '', $dispositivo['cliente_whatsapp'] ?? $dispositivo['cliente_telefono']) ?>"
                                target="_blank" class="text-decoration-none" style="color:#25D366;">
                                <i class="fab fa-whatsapp me-1"></i>
                                Abrir chat
                            </a>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($dispositivo['cliente_email'])): ?>
                    <div class="cliente-info-row">
                        <span class="cliente-info-label">Email</span>
                        <span class="cliente-info-value">
                            <a href="mailto:<?= esc($dispositivo['cliente_email']) ?>" class="text-decoration-none"
                                style="color:var(--accent);">
                                <?= esc($dispositivo['cliente_email']) ?>
                            </a>
                        </span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($dispositivo['cliente_direccion'])): ?>
                    <div class="cliente-info-row">
                        <span class="cliente-info-label">Dirección</span>
                        <span class="cliente-info-value"><?= esc($dispositivo['cliente_direccion']) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($dispositivo['cliente_referencia'])): ?>
                    <div class="cliente-info-row">
                        <span class="cliente-info-label">Referencia</span>
                        <span class="cliente-info-value text-muted"
                            style="font-style:italic;"><?= esc($dispositivo['cliente_referencia']) ?></span>
                    </div>
                <?php endif; ?>

            </div>
            <div class="modal-footer border-0 pt-0">
                <a href="<?= base_url('admin/clientes/ver/' . ($dispositivo['cliente_id'] ?? '')) ?>"
                    class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-external-link-alt me-1"></i> Ver perfil completo
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════
     Modal — Finalizar Reparación (Bootstrap 5)
════════════════════════════════════════════ -->
<div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">

            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-check-circle me-2 text-success"></i>Finalizar Reparación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formFinalizar">
                <div class="modal-body" style="max-height: 65vh; overflow-y: auto;">
                    <input type="hidden" name="dispositivo_id" value="<?= $dispositivo['id'] ?>">

                    <?php foreach ($dispositivo['problemas'] as $index => $prob): ?>
                        <div class="dv-prob-card">
                            <p class="dv-prob-name">
                                <i class="fas fa-wrench me-1 text-muted" style="font-size:.8rem;"></i>
                                <?= esc($prob['problema']) ?>
                            </p>
                            <input type="hidden" name="problemas[<?= $index ?>][id]" value="<?= $prob['id'] ?>">

                            <div class="row g-2 mb-2">
                                <div class="col-12 col-sm-6">
                                    <div class="dv-field">
                                        <label>Estado del Problema</label>
                                        <select name="problemas[<?= $index ?>][estado]" class="dv-input dv-select">
                                            <option value="resuelto">Resuelto</option>
                                            <option value="no_reparable">No reparable</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-12 col-sm-6">
                                    <div class="dv-field">
                                        <label>Mano de Obra ($)</label>
                                        <input type="number" step="0.01" name="problemas[<?= $index ?>][precio_mano_obra]"
                                            class="dv-input" value="<?= $prob['default_mano_obra'] ?>">
                                        <span class="dv-field-hint">Sugerido:
                                            $<?= number_format($prob['default_mano_obra'], 2) ?></span>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="dv-field">
                                        <label>Repuesto ($)</label>
                                        <input type="number" step="0.01" name="problemas[<?= $index ?>][precio_repuesto]"
                                            class="dv-input" value="<?= $prob['default_repuesto'] ?>">
                                        <span class="dv-field-hint">Sugerido:
                                            $<?= number_format($prob['default_repuesto'], 2) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="dv-field">
                                <label>Observación técnica</label>
                                <input type="text" name="problemas[<?= $index ?>][observacion]" class="dv-input"
                                    placeholder="Opcional — detalles de la solución aplicada">
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="dv-final-comment">
                        <label><i class="fas fa-comment-alt me-1 text-success"></i> Comentario para el Cliente <span
                                class="text-danger">*</span></label>
                        <textarea name="comentario" class="dv-input dv-obs-field" rows="3" required
                            placeholder="Ej: Se realizó cambio de pantalla, el equipo funciona correctamente."></textarea>
                    </div>

                    <div class="dv-final-comment">
                        <label><i class="fas fa-lock me-1 text-muted"></i> Nota Técnica Interna</label>
                        <textarea name="nota_tecnica" class="dv-input dv-obs-field" rows="2"
                            placeholder="Ej: Se usó repuesto de marca X / Detalle interno..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top d-flex flex-wrap gap-2 justify-content-between">
                    <button type="button" class="btn btn-danger btn-sm"
                        onclick="cancelarReparacion(<?= $dispositivo['id'] ?>)">
                        <i class="fas fa-times me-1"></i> Cancelar Reparación
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success btn-sm" id="btnGuardarFinalizar">
                            <i class="fas fa-save me-1"></i> Guardar y Finalizar
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>


<!-- ══════════════════════════════════════════
     Modal — Clave de acceso (Bootstrap 5)
════════════════════════════════════════════ -->
<?php if ($tienePass): ?>
    <div class="modal fade" id="modalClave" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:360px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" style="font-size:.9rem;">
                        <i class="fas fa-lock me-2 text-warning"></i>Clave de acceso —
                        <?= ucfirst(esc($dispositivo['tipo_pass'])) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="text-align:center;">
                    <?php if ($dispositivo['tipo_pass'] === 'patron'): ?>
                        <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:12px;">
                            Secuencia: <strong><?= esc($dispositivo['clave_acceso']) ?></strong>
                        </p>
                        <div style="display:inline-grid;grid-template-columns:repeat(3,56px);gap:10px;">
                            <?php
                            $puntos = array_map('trim', explode(',', $dispositivo['clave_acceso']));
                            for ($i = 1; $i <= 9; $i++):
                                $activo = in_array((string) $i, $puntos);
                                $orden = $activo ? (array_search((string) $i, $puntos) + 1) : $i;
                                ?>
                                <div
                                    style="width:56px;height:56px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:1rem;border:2px solid <?= $activo ? '#10b981' : '#e8eaed' ?>;background:<?= $activo ? 'rgba(16,185,129,.12)' : '#f7f8fa' ?>;color:<?= $activo ? '#065f46' : '#c4c9d4' ?>;">
                                    <?= $orden ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <p style="font-size:.75rem;color:var(--text-muted);margin-top:10px;">Verde = orden del trazo</p>
                    <?php else: ?>
                        <div style="padding:16px 0;">
                            <div
                                style="font-size:2rem;font-weight:800;letter-spacing:.1em;color:var(--text-primary);font-family:monospace;">
                                <?= esc($dispositivo['clave_acceso']) ?>
                            </div>
                            <p style="font-size:.8rem;color:var(--text-muted);margin-top:6px;">
                                <?= ucfirst(esc($dispositivo['tipo_pass'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>


<script>
    /* ── Tabs ────────────────────────────────────── */
    function switchTab(name, btn) {
        document.querySelectorAll('.dv-pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.dv-tab').forEach(b => b.classList.remove('active'));
        document.getElementById('pane-' + name).classList.add('active');
        btn.classList.add('active');
    }

    /* ── Abrir modal finalizar con validación ── */
    function abrirModalFinalizar(e) {
        const tecnicoIdAsignado = <?= json_encode($dispositivo['tecnico_id']) ?>;
        const currentUserId = <?= json_encode(session('id_usuario')) ?>;

        if (tecnicoIdAsignado && tecnicoIdAsignado != currentUserId) {
            showAlert('error', 'Este dispositivo está asignado a otro técnico. No puedes finalizar su reparación.', 'center');
            return;
        }

        const modal = new bootstrap.Modal(document.getElementById('modalFinalizar'));
        modal.show();
    }

    /* ── Iniciar reparación ──────────────────────── */
    function iniciarReparacion(id) {
        const tecnicoIdAsignado = <?= json_encode($dispositivo['tecnico_id']) ?>;
        const currentUserId = <?= json_encode(session('id_usuario')) ?>;

        let title = '¿Iniciar reparación?';
        let text = 'El dispositivo pasará a estado "En Proceso".';
        let icon = 'question';

        if (tecnicoIdAsignado && tecnicoIdAsignado != currentUserId) {
            showAlert('error', 'Este dispositivo ya está asignado a otro técnico.', 'center'); 
            return;
        } else if (!tecnicoIdAsignado) {
            title = 'Tomar Reparación'; 
            text = 'No hay técnico asignado. Al iniciar, quedarás como responsable de la reparación.'; 
            icon = 'info';
        }

        Swal.fire({
            title, text, icon, showCancelButton: true, confirmButtonText: 'Sí, iniciar', cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2563eb'
        })
            .then(res => {
                if (!res.isConfirmed) return;
                fetch('<?= base_url('tecnico/dispositivos/reparacion/iniciar') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `dispositivo_id=${id}&estado=en_proceso`
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { showAlert('success', data.message || 'Reparación iniciada', 'top-end'); setTimeout(() => location.reload(), 1000); }
                        else showAlert('error', data.message, 'center');
                    });
            });
    }

    /* ── Entregar dispositivo (NO PERMITIDO PARA TECNICO DESDE AQUI, PERO DEJAMOS LA LOGICA) ────────────────────── */
    function entregarDispositivo(id) {
        // En teoria, el técnico no puede entregar, si se requiere, se llamaria al endpoint
        showAlert('warning', 'La entrega de dispositivos debe ser realizada por recepción o administración.', 'center');
    }

    /* ── Cancelar reparación ─────────────────────── */
    function cancelarReparacion(id) {
        // Ocultar modal de finalizar antes de mostrar Swal
        const modalFinalizar = bootstrap.Modal.getInstance(document.getElementById('modalFinalizar'));
        if (modalFinalizar) modalFinalizar.hide();

        setTimeout(() => {
            Swal.fire({
                title: '¿Cancelar reparación?',
                html: `
        <div style="text-align:left;">
          <label style="font-size:.8rem;font-weight:700;color:#8a92a0;text-transform:uppercase;">Motivo para el Cliente <span style="color:red">*</span></label>
          <textarea id="swal-comentario" class="swal2-textarea" style="margin:8px 0;width:100%;box-sizing:border-box;" placeholder="Ej: No se consiguió el repuesto..."></textarea>
          <label style="font-size:.8rem;font-weight:700;color:#8a92a0;text-transform:uppercase;display:block;margin-top:8px;">Nota Técnica Interna</label>
          <textarea id="swal-nota-tecnica" class="swal2-textarea" style="margin:8px 0;width:100%;box-sizing:border-box;" placeholder="Ej: Se intentó reparar X pero falló Y..."></textarea>
        </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Cancelar reparación',
                cancelButtonText: 'Volver',
                confirmButtonColor: '#ef4444',
                preConfirm: () => {
                    const comentario = document.getElementById('swal-comentario').value;
                    const nota_tecnica = document.getElementById('swal-nota-tecnica').value;
                    if (!comentario) { Swal.showValidationMessage('El motivo para el cliente es obligatorio'); return false; }
                    return { comentario, nota_tecnica };
                }
            }).then(res => {
                if (!res.isConfirmed) return;
                const formData = new FormData();
                formData.append('dispositivo_id', id);
                formData.append('cancelar', 'true');
                formData.append('comentario', res.value.comentario);
                formData.append('nota_tecnica', res.value.nota_tecnica);
                fetch('<?= base_url('tecnico/dispositivos/reparacion/finalizar') ?>', { method: 'POST', body: formData })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { showAlert('success', 'Reparación cancelada', 'top-end'); setTimeout(() => location.reload(), 1000); }
                        else showAlert('error', data.message, 'center');
                    });
            });
        }, 300);
    }

    /* ── Finalizar reparación ────────────────────── */
    document.getElementById('formFinalizar')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('btnGuardarFinalizar');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Procesando...';

        fetch('<?= base_url('tecnico/dispositivos/reparacion/finalizar') ?>', {
            method: 'POST', body: new FormData(this)
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalFinalizar'))?.hide();
                    showAlert('success', data.message || 'Reparación finalizada', 'top-end');
                    setTimeout(() => location.reload(), 1100);
                } else {
                    showAlert('error', data.message, 'center');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save me-1"></i> Guardar y Finalizar';
                }
            })
            .catch(() => {
                showAlert('error', 'Error de conexión', 'center');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i> Guardar y Finalizar';
            });
    });
</script>

<?= $this->endSection() ?>