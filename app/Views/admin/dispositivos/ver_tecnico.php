<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('admin/dashboard') ?>"><i class="icon-home"></i></a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="<?= base_url('admin/tecnicos') ?>">Técnicos</a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#"><?= esc($tecnico['nombre']) ?></a></li>
    </ul>
</div>

<!-- ══ INFO TÉCNICO ══════════════════════════════════════════════ -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3 flex-wrap">

                    <div class="avatar-tecnico">
                        <i class="fas fa-user-cog"></i>
                    </div>

                    <div class="flex-grow-1">
                        <h5 class="mb-0 fw-bold"><?= esc($tecnico['nombre']) ?></h5>
                        <?php if ($tecnico['config']): ?>
                            <div class="text-muted" style="font-size:12px;">
                                Comisión:
                                <strong>
                                    <?= $tecnico['config']['tipo_comision'] === 'porcentaje'
                                        ? $tecnico['config']['valor_comision'] . '%'
                                        : '$' . number_format($tecnico['config']['valor_comision'], 2) . ' fijo' ?>
                                </strong>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Selector de período -->
                    <div class="d-flex align-items-center gap-2">
                        <label class="text-muted mb-0" style="font-size:12px; white-space:nowrap;">Período:</label>
                        <select id="filtroMes" class="form-select form-select-sm"
                            style="min-width:150px; font-size:12px;" onchange="filtrarPorMes(this.value)">
                            <option value="todos">Todos</option>
                            <?php foreach ($meses_disponibles as $mes): ?>
                                <option value="<?= esc($mes['valor']) ?>" <?= ($mes_activo === $mes['valor']) ? 'selected' : '' ?>>
                                    <?= esc($mes['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Total comisiones -->
                    <div class="stat-pill">
                        <div class="stat-pill-label">Comisiones</div>
                        <div class="stat-pill-value text-success" id="totalComisionesDisplay">
                            $<?= number_format($total_comisiones, 2) ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══ CONTADORES ════════════════════════════════════════════════ -->
<div class="row g-2 mb-3">

    <?php
    $counterDefs = [
        ['key' => 'todos', 'label' => 'Total', 'color' => '#1e3a5f'],
        ['key' => 'en_proceso', 'label' => 'En Proceso', 'color' => '#0d6efd'],
        ['key' => 'pendiente', 'label' => 'Pendiente', 'color' => '#6c757d'],
        ['key' => 'listo', 'label' => 'Listo', 'color' => '#198754'],
        ['key' => 'entregado', 'label' => 'Entregado', 'color' => '#0dcaf0'],
        ['key' => 'cancelado', 'label' => 'Cancelado', 'color' => '#dc3545'],
    ];
    $numeros = [
        'todos' => count($dispositivos),
        'en_proceso' => $contadores['en_proceso'],
        'pendiente' => $contadores['pendiente'],
        'listo' => $contadores['listo'],
        'entregado' => $contadores['entregado'],
        'cancelado' => $contadores['cancelado'],
    ];
    ?>

    <?php foreach ($counterDefs as $cd): ?>
        <div class="col-6 col-sm-4 col-md-2">
            <div class="counter-card" data-estado="<?= $cd['key'] ?>"
                style="border-top-color:<?= $cd['color'] ?>; --c:<?= $cd['color'] ?>;"
                onclick="filtrarEstado('<?= $cd['key'] ?>')">
                <div class="counter-num" id="cnt-<?= $cd['key'] ?>"><?= $numeros[$cd['key']] ?></div>
                <div class="counter-label" style="color:<?= $cd['color'] ?>;"><?= $cd['label'] ?></div>
            </div>
        </div>
    <?php endforeach; ?>

</div>

<!-- ══ DISPOSITIVOS ══════════════════════════════════════════════ -->
<div class="row g-3" id="lista-dispositivos">

    <?php if (empty($dispositivos)): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5 text-muted">
                    <p class="mb-0">Este técnico no tiene dispositivos asignados.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($dispositivos as $dev):

        $estadoConfig = match ($dev['estado']) {
            'en_proceso' => ['color' => '#0d6efd', 'label' => 'En Proceso'],
            'pendiente' => ['color' => '#6c757d', 'label' => 'Pendiente'],
            'listo' => ['color' => '#198754', 'label' => 'Listo'],
            'entregado' => ['color' => '#0dcaf0', 'label' => 'Entregado'],
            'cancelado' => ['color' => '#dc3545', 'label' => 'Cancelado'],
            default => ['color' => '#6c757d', 'label' => ucfirst($dev['estado'])],
        };

        $vencido = (
            !empty($dev['fecha_estimada_entrega'])
            && !in_array($dev['estado'], ['entregado', 'cancelado', 'listo'])
            && strtotime($dev['fecha_estimada_entrega']) < time()
        );

        // Mes basado en fecha de ingreso (created_at)
        $fechaRef = $dev['fecha_ingreso'] ?? $dev['updated_at'] ?? null;
        $mesDev = $fechaRef ? date('Y-m', strtotime($fechaRef)) : 'sin-fecha';

        $esComisionable = in_array($dev['estado'], ['listo', 'entregado']) ? '1' : '0';
        ?>

        <div class="col-12 col-md-6 col-xl-4 dispositivo-item" data-estado="<?= esc($dev['estado']) ?>"
            data-mes="<?= esc($mesDev) ?>" data-comision="<?= (float) ($dev['comision_tecnico'] ?? 0) ?>"
            data-comisionable="<?= $esComisionable ?>">

            <div class="dev-card <?= $vencido ? 'dev-card--vencida' : '' ?>">

                <!-- Header con color de estado -->
                <div class="dev-card__header" style="background:<?= $estadoConfig['color'] ?>;">
                    <div>
                        <div class="dev-card__device">
                            <?= esc($dev['marca']) ?>     <?= esc($dev['modelo'] ?? '') ?>
                        </div>
                        <div class="dev-card__tipo"><?= esc($dev['tipo_dispositivo']) ?></div>
                    </div>
                    <span class="dev-card__estado-badge"><?= $estadoConfig['label'] ?></span>
                </div>

                <div class="dev-card__body">

                    <!-- Orden y cliente -->
                    <div class="dev-card__row-top">
                        <a href="<?= base_url('admin/ordenes/' . $dev['orden_id']) ?>" class="dev-card__orden-link">
                            <?= esc($dev['numero_orden']) ?>
                        </a>
                        <div class="dev-card__cliente">
                            <span class="dev-card__cliente-nombre"><?= esc($dev['cliente_nombre']) ?></span>
                            <?php if ($dev['cliente_telefono']): ?>
                                <span class="dev-card__cliente-tel"><?= esc($dev['cliente_telefono']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($dev['prioridad']) && $dev['prioridad'] !== 'Normal'): ?>
                        <div class="mb-2">
                            <span class="badge bg-<?= esc($dev['prioridad_color'] ?? 'secondary') ?>"
                                style="font-size:9px;"><?= esc($dev['prioridad']) ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Problemas -->
                    <?php if (!empty($dev['problemas'])): ?>
                        <div class="dev-card__problemas">
                            <?php foreach ($dev['problemas'] as $prob): ?>
                                <div class="dev-card__problema">
                                    <span><?= esc($prob['problema']) ?></span>
                                    <span class="dev-card__prob-precio">
                                        $<?= number_format((float) $prob['precio_mano_obra'] + (float) $prob['precio_repuesto'], 2) ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Fechas -->
                    <div class="dev-card__fechas">
                        <div>
                            <div class="dev-card__fecha-label">Ingreso</div>
                            <div class="dev-card__fecha-val">
                                <?= $dev['fecha_ingreso'] ? date('d/m/Y', strtotime($dev['fecha_ingreso'])) : '—' ?>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="dev-card__fecha-label">Entrega est.</div>
                            <div class="dev-card__fecha-val <?= $vencido ? 'dev-card__fecha-val--vencida' : '' ?>">
                                <?= $dev['fecha_estimada_entrega']
                                    ? date('d/m/Y', strtotime($dev['fecha_estimada_entrega']))
                                    : '—' ?>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="dev-card__footer">
                    <div>
                        <div class="dev-card__footer-label">Total</div>
                        <div class="dev-card__precio">$<?= number_format((float) $dev['precio_total'], 2) ?></div>
                    </div>

                    <?php if ($dev['comision_tecnico'] !== null): ?>
                        <div class="text-center">
                            <div class="dev-card__footer-label">Comisión</div>
                            <div class="dev-card__comision">$<?= number_format((float) $dev['comision_tecnico'], 2) ?></div>
                        </div>
                    <?php endif; ?>

                    <a href="<?= base_url('admin/dispositivos/detalle/' . $dev['id']) ?>" class="dev-card__btn-ver">Ver</a>
                </div>

            </div>
        </div>

    <?php endforeach; ?>
</div>

<!-- Sin resultados -->
<div id="sin-resultados" class="mt-3" style="display:none;">
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-4 text-muted" style="font-size:13px;">
            No hay dispositivos para el filtro seleccionado.
        </div>
    </div>
</div>

<!-- ══ ESTILOS ══════════════════════════════════════════════════ -->
<style>
    .avatar-tecnico {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #1e3a5f;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .stat-pill {
        background: #f4f6fb;
        border-radius: 8px;
        padding: 5px 14px;
        text-align: center;
    }

    .stat-pill-label {
        font-size: 10px;
        color: #aaa;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .stat-pill-value {
        font-size: 15px;
        font-weight: 800;
    }

    /* Contadores */
    .counter-card {
        background: #fff;
        border-radius: 10px;
        border-top: 3px solid var(--c);
        box-shadow: 0 1px 4px rgba(0, 0, 0, .07);
        padding: 12px 10px;
        text-align: center;
        cursor: pointer;
        user-select: none;
        transition: transform .15s, box-shadow .15s;
    }

    .counter-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 16px rgba(0, 0, 0, .1);
    }

    .counter-card.activo {
        box-shadow: 0 0 0 2.5px var(--c), 0 4px 14px rgba(0, 0, 0, .08);
        transform: translateY(-2px);
    }

    .counter-num {
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
        color: #1e3a5f;
    }

    .counter-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-top: 3px;
        font-weight: 600;
    }

    /* Tarjeta */
    .dev-card {
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 1px 5px rgba(0, 0, 0, .08);
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: transform .15s, box-shadow .15s;
    }

    .dev-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(0, 0, 0, .1);
    }

    .dev-card--vencida {
        outline: 2px solid #dc3545;
    }

    .dev-card__header {
        padding: 9px 13px;
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
    }

    .dev-card__device {
        font-size: 13px;
        font-weight: 700;
    }

    .dev-card__tipo {
        font-size: 10px;
        opacity: .8;
        margin-top: 2px;
    }

    .dev-card__estado-badge {
        font-size: 10px;
        font-weight: 600;
        background: rgba(255, 255, 255, .2);
        padding: 2px 9px;
        border-radius: 20px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .dev-card__body {
        padding: 11px 13px;
        flex: 1;
    }

    .dev-card__row-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f0f0f0;
    }

    .dev-card__orden-link {
        font-size: 11px;
        font-weight: 700;
        color: #1e3a5f;
        text-decoration: none;
        background: #eef2f8;
        padding: 3px 10px;
        border-radius: 20px;
        flex-shrink: 0;
        transition: background .15s;
        white-space: nowrap;
    }

    .dev-card__orden-link:hover {
        background: #dde5f5;
        color: #1e3a5f;
    }

    .dev-card__cliente {
        text-align: right;
    }

    .dev-card__cliente-nombre {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #222;
        line-height: 1.2;
    }

    .dev-card__cliente-tel {
        display: block;
        font-size: 10px;
        color: #999;
        margin-top: 1px;
    }

    .dev-card__problemas {
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px solid #f0f0f0;
    }

    .dev-card__problema {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        padding: 1px 0;
        gap: 6px;
        color: #555;
    }

    .dev-card__prob-precio {
        font-weight: 700;
        color: #1e3a5f;
        flex-shrink: 0;
    }

    .dev-card__fechas {
        display: flex;
        justify-content: space-between;
        font-size: 10px;
    }

    .dev-card__fecha-label {
        color: #aaa;
        margin-bottom: 1px;
    }

    .dev-card__fecha-val {
        font-weight: 600;
        color: #333;
        font-size: 11px;
    }

    .dev-card__fecha-val--vencida {
        color: #dc3545;
    }

    .dev-card__footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 13px;
        border-top: 1px solid #f0f0f0;
        background: #fafbfc;
    }

    .dev-card__footer-label {
        font-size: 9px;
        color: #bbb;
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .dev-card__precio {
        font-size: 15px;
        font-weight: 800;
        color: #1e3a5f;
    }

    .dev-card__comision {
        font-size: 13px;
        font-weight: 700;
        color: #198754;
    }

    .dev-card__btn-ver {
        font-size: 11px;
        padding: 4px 13px;
        border: 1.5px solid #0d6efd;
        color: #0d6efd;
        border-radius: 20px;
        background: transparent;
        text-decoration: none;
        font-weight: 600;
        transition: all .15s;
        white-space: nowrap;
    }

    .dev-card__btn-ver:hover {
        background: #0d6efd;
        color: #fff;
    }
</style>

<!-- ══ JAVASCRIPT ════════════════════════════════════════════════ -->
<script>
    let filtroEstadoActual = 'todos';
    let filtroMesActual = 'todos';

    function filtrarEstado(estado) {
        filtroEstadoActual = estado;
        document.querySelectorAll('.counter-card').forEach(c => {
            c.classList.toggle('activo', c.dataset.estado === estado);
        });
        aplicarFiltros();
    }

    function filtrarPorMes(mes) {
        filtroMesActual = mes;
        aplicarFiltros();
    }

    function aplicarFiltros() {
        const items = document.querySelectorAll('.dispositivo-item');
        const cnt = { todos: 0, en_proceso: 0, pendiente: 0, listo: 0, entregado: 0, cancelado: 0 };
        let comision = 0;

        items.forEach(item => {
            const pasaEstado = filtroEstadoActual === 'todos' || item.dataset.estado === filtroEstadoActual;
            const pasaMes = filtroMesActual === 'todos' || item.dataset.mes === filtroMesActual;
            const visible = pasaEstado && pasaMes;

            item.style.display = visible ? '' : 'none';

            if (visible) {
                cnt.todos++;
                if (cnt[item.dataset.estado] !== undefined) cnt[item.dataset.estado]++;
                if (item.dataset.comisionable === '1') {
                    comision += parseFloat(item.dataset.comision || 0);
                }
            }
        });

        Object.keys(cnt).forEach(k => {
            const el = document.getElementById('cnt-' + k);
            if (el) el.textContent = cnt[k];
        });

        document.getElementById('totalComisionesDisplay').textContent =
            '$' + comision.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        document.getElementById('sin-resultados').style.display =
            cnt.todos === 0 ? '' : 'none';
    }

    document.addEventListener('DOMContentLoaded', () => filtrarEstado('todos'));
</script>

<?= $this->endSection() ?>