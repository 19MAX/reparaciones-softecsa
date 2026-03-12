<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('admin/dashboard') ?>">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item">
            <a href="<?= base_url('admin/tecnicos') ?>">Técnicos</a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item">
            <a href="#"><?= esc($tecnico['nombre']) ?></a>
        </li>
    </ul>
</div>

<!-- ══ INFO TÉCNICO ════════════════════════════════════════════ -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-tecnico">
                        <i class="fas fa-user-cog fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="mb-0 fw-bold"><?= esc($tecnico['nombre']) ?></h5>
                    </div>
                    <?php if ($tecnico['config']): ?>
                    <div class="text-end me-3">
                        <div class="text-muted" style="font-size:11px;">Tipo de comisión</div>
                        <span class="badge bg-secondary">
                            <?= $tecnico['config']['tipo_comision'] === 'porcentaje'
                                ? $tecnico['config']['valor_comision'] . '%'
                                : '$' . number_format($tecnico['config']['valor_comision'], 2) . ' fijo' ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <div class="text-end">
                        <div class="text-muted" style="font-size:11px;">Comisiones generadas</div>
                        <span class="fw-bold text-success fs-5">
                            $<?= number_format($total_comisiones, 2) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══ CARDS CONTADORES ════════════════════════════════════════ -->
<div class="row g-3 mb-4">

    <!-- Total -->
    <div class="col-6 col-md-2">
        <div class="card counter-card border-0 shadow-sm h-100 counter-total"
             onclick="filtrarEstado('todos')" style="cursor:pointer;">
            <div class="card-body text-center py-3 px-2">
                <div class="counter-icon mb-1"><i class="fas fa-layer-group"></i></div>
                <div class="counter-num"><?= count($dispositivos) ?></div>
                <div class="counter-label">Total</div>
            </div>
        </div>
    </div>

    <!-- En proceso -->
    <div class="col-6 col-md-2">
        <div class="card counter-card border-0 shadow-sm h-100 counter-en_proceso"
             onclick="filtrarEstado('en_proceso')" style="cursor:pointer;">
            <div class="card-body text-center py-3 px-2">
                <div class="counter-icon mb-1"><i class="fas fa-tools"></i></div>
                <div class="counter-num"><?= $contadores['en_proceso'] ?></div>
                <div class="counter-label">En Proceso</div>
            </div>
        </div>
    </div>

    <!-- Pendiente -->
    <div class="col-6 col-md-2">
        <div class="card counter-card border-0 shadow-sm h-100 counter-pendiente"
             onclick="filtrarEstado('pendiente')" style="cursor:pointer;">
            <div class="card-body text-center py-3 px-2">
                <div class="counter-icon mb-1"><i class="fas fa-clock"></i></div>
                <div class="counter-num"><?= $contadores['pendiente'] ?></div>
                <div class="counter-label">Pendientes</div>
            </div>
        </div>
    </div>

    <!-- Listo -->
    <div class="col-6 col-md-2">
        <div class="card counter-card border-0 shadow-sm h-100 counter-listo"
             onclick="filtrarEstado('listo')" style="cursor:pointer;">
            <div class="card-body text-center py-3 px-2">
                <div class="counter-icon mb-1"><i class="fas fa-check-circle"></i></div>
                <div class="counter-num"><?= $contadores['listo'] ?></div>
                <div class="counter-label">Listos</div>
            </div>
        </div>
    </div>

    <!-- Entregado -->
    <div class="col-6 col-md-2">
        <div class="card counter-card border-0 shadow-sm h-100 counter-entregado"
             onclick="filtrarEstado('entregado')" style="cursor:pointer;">
            <div class="card-body text-center py-3 px-2">
                <div class="counter-icon mb-1"><i class="fas fa-box-open"></i></div>
                <div class="counter-num"><?= $contadores['entregado'] ?></div>
                <div class="counter-label">Entregados</div>
            </div>
        </div>
    </div>

    <!-- Cancelado -->
    <div class="col-6 col-md-2">
        <div class="card counter-card border-0 shadow-sm h-100 counter-cancelado"
             onclick="filtrarEstado('cancelado')" style="cursor:pointer;">
            <div class="card-body text-center py-3 px-2">
                <div class="counter-icon mb-1"><i class="fas fa-times-circle"></i></div>
                <div class="counter-num"><?= $contadores['cancelado'] ?></div>
                <div class="counter-label">Cancelados</div>
            </div>
        </div>
    </div>

</div>

<!-- ══ LISTADO DE DISPOSITIVOS ═════════════════════════════════ -->
<div class="row g-3" id="lista-dispositivos">

    <?php if (empty($dispositivos)): ?>
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Este técnico no tiene dispositivos asignados.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php foreach ($dispositivos as $dev): ?>

    <?php
        $estadoConfig = match($dev['estado']) {
            'en_proceso' => ['color' => '#0d6efd', 'label' => 'En Proceso',  'icon' => 'fa-tools'],
            'pendiente'  => ['color' => '#6c757d', 'label' => 'Pendiente',   'icon' => 'fa-clock'],
            'listo'      => ['color' => '#198754', 'label' => 'Listo',       'icon' => 'fa-check-circle'],
            'entregado'  => ['color' => '#0dcaf0', 'label' => 'Entregado',   'icon' => 'fa-box-open'],
            'cancelado'  => ['color' => '#dc3545', 'label' => 'Cancelado',   'icon' => 'fa-times-circle'],
            default      => ['color' => '#6c757d', 'label' => ucfirst($dev['estado']), 'icon' => 'fa-circle'],
        };

        // Alerta si la fecha estimada ya pasó y no está entregado/cancelado
        $vencido = false;
        if (! empty($dev['fecha_estimada_entrega'])
            && ! in_array($dev['estado'], ['entregado', 'cancelado', 'listo'])
            && strtotime($dev['fecha_estimada_entrega']) < time()) {
            $vencido = true;
        }
    ?>

    <div class="col-12 col-md-6 col-xl-4 dispositivo-item" data-estado="<?= esc($dev['estado']) ?>">
        <div class="card border-0 shadow-sm h-100 dispositivo-card <?= $vencido ? 'card-vencida' : '' ?>">

            <!-- Header de la card -->
            <div class="card-header-custom" style="background:<?= $estadoConfig['color'] ?>;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="disp-titulo">
                            <?= esc($dev['marca']) ?> <?= esc($dev['modelo'] ?? '') ?>
                        </div>
                        <div class="disp-subtitulo">
                            <?= esc($dev['tipo_dispositivo']) ?>
                            <?php if ($dev['serie_imei'] ?? ''): ?>
                                · <?= esc($dev['serie_imei']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge-estado">
                            <i class="fas <?= $estadoConfig['icon'] ?> me-1"></i>
                            <?= $estadoConfig['label'] ?>
                        </span>
                        <?php if ($dev['prioridad']): ?>
                        <div class="mt-1">
                            <span class="badge bg-<?= esc($dev['prioridad_color'] ?? 'secondary') ?>" style="font-size:9px;">
                                ⚡ <?= esc($dev['prioridad']) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card-body py-3 px-3">

                <!-- Orden y cliente -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <a href="<?= base_url('admin/ordenes/' . $dev['orden_id']) ?>"
                       class="badge bg-light text-dark border text-decoration-none fw-bold"
                       style="font-size:11px;">
                        # <?= esc($dev['numero_orden']) ?>
                    </a>
                    <div class="text-end">
                        <div style="font-size:11px; font-weight:600;"><?= esc($dev['cliente_nombre']) ?></div>
                        <?php if ($dev['cliente_telefono']): ?>
                        <div style="font-size:10px; color:#888;"><?= esc($dev['cliente_telefono']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <hr class="my-2">

                <!-- Problemas -->
                <?php if (! empty($dev['problemas'])): ?>
                <div class="mb-2">
                    <?php foreach ($dev['problemas'] as $prob): ?>
                    <div class="problema-row">
                        <span class="problema-nombre"><?= esc($prob['problema']) ?></span>
                        <span class="problema-precio">
                            $<?= number_format((float)$prob['precio_mano_obra'] + (float)$prob['precio_repuesto'], 2) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <hr class="my-2">
                <?php endif; ?>

                <!-- Fechas -->
                <div class="row g-1 mb-2" style="font-size:10px;">
                    <div class="col-6">
                        <div class="text-muted">Ingreso</div>
                        <div class="fw-semibold"><?= date('d/m/Y', strtotime($dev['fecha_ingreso'])) ?></div>
                    </div>
                    <div class="col-6 text-end">
                        <div class="text-muted">Entrega estimada</div>
                        <div class="fw-semibold <?= $vencido ? 'text-danger' : '' ?>">
                            <?php if ($dev['fecha_estimada_entrega']): ?>
                                <?= date('d/m/Y H:i', strtotime($dev['fecha_estimada_entrega'])) ?>
                                <?php if ($vencido): ?> <i class="fas fa-exclamation-triangle text-danger"></i><?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer con precio y acción -->
            <div class="card-footer-custom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-size:9px; color:#aaa; line-height:1;">TOTAL</div>
                        <div class="precio-total">$<?= number_format((float)$dev['precio_total'], 2) ?></div>
                    </div>
                    <?php if ($dev['comision_tecnico'] !== null): ?>
                    <div class="text-end">
                        <div style="font-size:9px; color:#aaa; line-height:1;">COMISIÓN</div>
                        <div style="font-size:13px; font-weight:700; color:#28a745;">
                            $<?= number_format((float)$dev['comision_tecnico'], 2) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <a href="<?= base_url('admin/dispositivos/' . $dev['id']) ?>"
                       class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i> Ver
                    </a>
                </div>
            </div>

        </div>
    </div>

    <?php endforeach; ?>
</div>

<!-- ══ ESTILOS ═════════════════════════════════════════════════ -->
<style>
    /* Contadores */
    .counter-card { transition: transform .15s, box-shadow .15s; border-radius: 10px !important; }
    .counter-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.12) !important; }
    .counter-icon { font-size: 18px; }
    .counter-num  { font-size: 26px; font-weight: 800; line-height: 1; }
    .counter-label{ font-size: 10px; text-transform: uppercase; letter-spacing: .5px; margin-top: 2px; }

    .counter-total     { border-top: 3px solid #1e3a5f !important; }
    .counter-en_proceso{ border-top: 3px solid #0d6efd !important; color: #0d6efd; }
    .counter-pendiente { border-top: 3px solid #6c757d !important; color: #6c757d; }
    .counter-listo     { border-top: 3px solid #198754 !important; color: #198754; }
    .counter-entregado { border-top: 3px solid #0dcaf0 !important; color: #0dcaf0; }
    .counter-cancelado { border-top: 3px solid #dc3545 !important; color: #dc3545; }
    .counter-card.activo { box-shadow: 0 0 0 3px currentColor !important; transform: translateY(-2px); }

    /* Avatar técnico */
    .avatar-tecnico {
        width: 44px; height: 44px; border-radius: 50%;
        background: #1e3a5f; color: #fff;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    /* Cards dispositivo */
    .dispositivo-card { border-radius: 10px !important; transition: transform .15s, box-shadow .15s; }
    .dispositivo-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.1) !important; }
    .card-vencida { border: 1.5px solid #dc3545 !important; }

    .card-header-custom {
        padding: 10px 14px;
        border-radius: 10px 10px 0 0;
        color: #fff;
    }
    .disp-titulo   { font-size: 13px; font-weight: 700; }
    .disp-subtitulo{ font-size: 10px; opacity: .8; margin-top: 1px; }
    .badge-estado  { font-size: 10px; font-weight: 600; background: rgba(255,255,255,.2); padding: 2px 8px; border-radius: 8px; }

    .card-footer-custom {
        padding: 8px 14px;
        border-top: 1px solid #edf0f5;
        background: #f8fafc;
        border-radius: 0 0 10px 10px;
    }
    .precio-total { font-size: 15px; font-weight: 800; color: #1e3a5f; }

    /* Problemas */
    .problema-row {
        display: flex; justify-content: space-between; align-items: center;
        font-size: 10px; padding: 2px 0;
    }
    .problema-nombre { color: #444; }
    .problema-precio { font-weight: 600; color: #1e3a5f; }
</style>

<!-- ══ FILTRO POR ESTADO ═══════════════════════════════════════ -->
<script>
function filtrarEstado(estado) {
    const items    = document.querySelectorAll('.dispositivo-item');
    const contCards= document.querySelectorAll('.counter-card');

    // Marcar card activa
    contCards.forEach(c => c.classList.remove('activo'));
    const cardActiva = document.querySelector('.counter-' + estado);
    if (cardActiva) cardActiva.classList.add('activo');

    // Filtrar dispositivos
    items.forEach(item => {
        if (estado === 'todos' || item.dataset.estado === estado) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>

<?= $this->endSection() ?>