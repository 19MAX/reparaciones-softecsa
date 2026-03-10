<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Detalle del Dispositivo<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('admin/dashboard') ?>"><i class="icon-home"></i></a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="<?= base_url('admin/ordenes') ?>">Órdenes</a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Detalle Dispositivo</a></li>
    </ul>
</div>

<div class="row">

    <!-- ── Encabezado del dispositivo ── -->
    <div class="col-md-5">


        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-1">
                            <i class="fas fa-mobile-alt me-2 text-primary"></i>
                            <?= esc($dispositivo['tipo_dispositivo']) ?>
                            <?= esc($dispositivo['marca']) ?>
                            <?= esc($dispositivo['modelo']) ?>
                        </h4>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            <span class="badge badge-secondary">
                                <i class="fas fa-barcode me-1"></i><?= esc($dispositivo['serie_imei'] ?: 'Sin IMEI') ?>
                            </span>
                            <span
                                class="badge <?= $dispositivo['tipo_pass'] !== 'ninguno' ? 'badge-warning' : 'badge-success' ?>">
                                <i
                                    class="fas fa-<?= $dispositivo['tipo_pass'] !== 'ninguno' ? 'lock' : 'lock-open' ?> me-1"></i>
                                <?= $dispositivo['tipo_pass'] !== 'ninguno' ? ucfirst($dispositivo['tipo_pass']) : 'Sin bloqueo' ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">

                    <div>
                        <div class="text-muted small text-uppercase mb-1"
                            style="letter-spacing:.04em;font-size:.72rem;">
                            Orden
                        </div>
                        <a href="<?= base_url('admin/ordenes/editar/' . $dispositivo['orden_id']) ?>" class="fw-bold">
                            <?= esc($dispositivo['codigo_orden']) ?>
                        </a>
                    </div>

                </li>
                <li class="list-group-item">

                    <div>
                        <div class="text-muted small text-uppercase mb-1"
                            style="letter-spacing:.04em;font-size:.72rem;">
                            Cliente
                        </div>
                        <span class="fw-bold"><?= esc($dispositivo['cliente_nombre']) ?></span>
                    </div>
                </li>
                <li class="list-group-item">

                    <div>
                        <div class="text-muted small text-uppercase mb-1"
                            style="letter-spacing:.04em;font-size:.72rem;">
                            Técnico
                        </div>
                        <?php if ($dispositivo['tecnico_nombre']): ?>
                            <span class="fw-bold">
                                <i class="fas fa-user-cog text-success me-1"></i>
                                <?= esc($dispositivo['tecnico_nombre']) ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">
                                <i class="fas fa-user-slash me-1"></i>Sin asignar
                            </span>
                        <?php endif; ?>
                    </div>
                </li>
                <li class="list-group-item">

                    <div>
                        <div class="row">

                            <!-- Entrega estimada -->
                            <div class="col-6">
                                <div class="text-muted small text-uppercase mb-1"
                                    style="letter-spacing:.04em;font-size:.72rem;">
                                    Entrega estimada
                                </div>
                                <?php if (!empty($dispositivo['fecha_estimada_entrega'])): ?>
                                    <span class="fw-bold">
                                        <?= formatear_fecha($dispositivo['fecha_estimada_entrega'], 'solo_fecha') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </div>

                            <!-- Entrega real -->
                            <div class="col-6">
                                <div class="text-muted small text-uppercase mb-1"
                                    style="letter-spacing:.04em;font-size:.72rem;">
                                    Entrega real
                                </div>
                                <?php if (!empty($dispositivo['fecha_entrega_real'])): ?>
                                    <span class="fw-bold">
                                        <?= formatear_fecha($dispositivo['fecha_entrega_real'], 'solo_fecha') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>

                </li>
                <!-- Estado de reparación -->
                <li class="list-group-item">
                    <div>
                        <div class="text-muted small text-uppercase mb-1"
                            style="letter-spacing:.04em;font-size:.72rem;">
                            Estado
                        </div>
                        <?php
                        $estado = strtolower($dispositivo['estado'] ?? '');
                        [$badgeClass, $icon] = match (true) {
                            str_contains($estado, 'entregad') => ['badge-success', 'fa-check-circle'],
                            str_contains($estado, 'finaliz') => ['badge-success', 'fa-check-circle'],
                            str_contains($estado, 'reparac') => ['badge-info', 'fa-tools'],
                            str_contains($estado, 'diagnos') => ['badge-primary', 'fa-search'],
                            str_contains($estado, 'espera') => ['badge-warning', 'fa-clock'],
                            str_contains($estado, 'cancel') => ['badge-danger', 'fa-times-circle'],
                            default => ['badge-secondary', 'fa-circle'],
                        };
                        ?>
                        <span class="badge <?= $badgeClass ?>" style="font-size:.82rem;padding:.4em .7em;">
                            <i class="fas <?= $icon ?> me-1"></i>
                            <?= esc($dispositivo['estado'] ?? 'Sin estado') ?>
                        </span>
                    </div>
                </li>

                <li class="list-group-item bg-light">
                    <div class="d-grid gap-2">
                        <?php if ($dispositivo['estado'] === 'pendiente'): ?>
                            <button type="button" class="btn btn-primary btn-sm"
                                onclick="iniciarReparacion(<?= $dispositivo['id'] ?>)">
                                <i class="fas fa-play me-2"></i>Iniciar Reparación
                            </button>
                        <?php elseif ($dispositivo['estado'] === 'en_proceso'): ?>
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalFinalizar">
                                <i class="fas fa-check-double me-2"></i>Finalizar Reparación
                            </button>
                        <?php else: ?>
                            <div class="alert alert-secondary mb-0 py-2 text-center">
                                <small><i class="fas fa-info-circle me-1"></i> Reparación concluida</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </li>

                <!-- Precio -->
                <li class="list-group-item">
                    <div>
                        <div class="text-muted small text-uppercase mb-1"
                            style="letter-spacing:.04em;font-size:.72rem;">
                            Precio a cobrar
                        </div>

                        <!-- Total destacado -->
                        <div class="fw-bold fs-5 text-success mb-1">
                            <?php if (!empty($dispositivo['precio_total'])): ?>
                                $<?= number_format($dispositivo['precio_total'], 2) ?>
                            <?php else: ?>
                                <span class="text-muted fs-6 fw-normal">Sin precio definido</span>
                            <?php endif; ?>
                        </div>

                        <!-- Desglose repuestos + mano de obra -->
                        <?php if (!empty($dispositivo['precio_repuesto']) || !empty($dispositivo['precio_mano_obra'])): ?>
                            <div class="d-flex gap-3 flex-wrap">
                                <?php if (!empty($dispositivo['precio_repuesto'])): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-cog me-1"></i>Repuestos:
                                        <strong
                                            class="text-dark">$<?= number_format($dispositivo['precio_repuesto'], 2) ?></strong>
                                    </small>
                                <?php endif; ?>
                                <?php if (!empty($dispositivo['precio_mano_obra'])): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-wrench me-1"></i>Mano de obra:
                                        <strong
                                            class="text-dark">$<?= number_format($dispositivo['precio_mano_obra'], 2) ?></strong>
                                    </small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </li>
            </ul>
        </div>

    </div>
    <!-- ══════════════════════════════════════════════════
     NAV PILLS — pestañas principales
═══════════════════════════════════════════════════ -->
    <div class="col-md-7">

        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs nav-line nav-color-secondary" id="pills-dispositivo" role="tablist">

                    <li class="nav-item">
                        <a class="nav-link active" id="tab-problemas-btn" data-bs-toggle="pill" href="#tab-problemas"
                            role="tab" aria-controls="tab-problemas" aria-selected="true">
                            <i class="fas fa-exclamation-triangle"></i>
                            Problemas
                            <?php if (!empty($problemas)): ?>
                                <span class="badge badge-danger ms-1"><?= count($problemas) ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" id="tab-accesorios-btn" data-bs-toggle="pill" href="#tab-accesorios"
                            role="tab" aria-controls="tab-accesorios" aria-selected="false">
                            <i class="fas fa-plug"></i>
                            Accesorios
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" id="tab-estados-btn" data-bs-toggle="pill" href="#tab-estados" role="tab"
                            aria-controls="tab-estados" aria-selected="false">
                            <i class="fas fa-exchange-alt"></i>
                            Estados
                        </a>
                    </li>


                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content mt-2" id="pills-dispositivo-content">

                    <!-- ── TAB: PROBLEMAS ── -->
                    <div class="tab-pane fade show active" id="tab-problemas" role="tabpanel"
                        aria-labelledby="tab-problemas-btn">

                        <?php if (!empty($dispositivo['problemas'])): ?>
                            <!-- Problemas técnicos -->
                            <div class="mb-3">
                                <p class="text-muted small text-uppercase mb-2"
                                    style="letter-spacing:.05em;font-size:.72rem;">
                                    <i class="fas fa-exclamation-triangle me-1 text-danger"></i>Problemas reportados
                                </p>
                                <div>
                                    <?php foreach ($dispositivo['problemas'] as $problema): ?>
                                        <span class="badge badge-danger"
                                            style="font-size:.82rem;padding:.45em .75em;white-space:normal;text-align:left;">
                                            <?= esc($problema['problema']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Lo que el cliente describe -->
                            <div class="mt-3 p-3 rounded" style="background:rgba(0,0,0,.03);border-left:3px solid #6c757d;">
                                <p class="text-muted small text-uppercase mb-1"
                                    style="letter-spacing:.05em;font-size:.72rem;">
                                    <i class="fas fa-comment me-1"></i>El cliente reporta
                                </p>
                                <?php if (!isset($dispositivo['relato_cliente'])): ?>
                                    <span class="text-muted fst-italic">Sin información del cliente</span>
                                <?php else: ?>
                                    <p class="mb-0"><?= esc($dispositivo['relato_cliente']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
                                No hay problemas registrados
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ── TAB: ACCESORIOS + CHECKLIST ── -->
                    <div class="tab-pane fade" id="tab-accesorios" role="tabpanel" aria-labelledby="tab-accesorios-btn">
                        <div class="row">

                            <div class="col-md-6">
                                <?php if (!empty($dispositivo['accesorios'])): ?>
                                    <p class="text-muted small text-uppercase mb-2"
                                        style="letter-spacing:.05em;font-size:.72rem;">
                                        <i class="fas fa-plug text-success me-1"></i>Accesorios entregados
                                    </p>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($dispositivo['accesorios'] as $acc): ?>
                                            <li class="list-group-item px-0 py-2 d-flex align-items-center gap-2">
                                                <i class="fas fa-circle text-success" style="font-size:.45rem;"></i>
                                                <?= esc($acc['accesorio']) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <div class="text-center py-5 text-muted">
                                        <i class="fas fa-plug fa-3x mb-3 d-block opacity-50"></i>
                                        No hay accesorios registrados
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($dispositivo['detalles'])): ?>
                                <div class="col-md-6">
                                    <p class="text-muted small text-uppercase mb-2"
                                        style="letter-spacing:.05em;font-size:.72rem;">
                                        <i class="fas fa-tasks text-info me-1"></i>Detalles del estado físico
                                    </p>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ($dispositivo['detalles'] as $detalle): ?>
                                            <li class="list-group-item px-0 py-2">
                                                <div class="d-flex align-items-start gap-2">
                                                    <i class="fas fa-check-circle text-success mt-1 flex-shrink-0"></i>
                                                    <div>
                                                        <span><?= esc($detalle['detalle']) ?></span>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>

                    <!-- ── TAB: HISTORIAL DE ESTADOS (timeline de la plantilla) ── -->
                    <div class="tab-pane fade" id="tab-estados" role="tabpanel" aria-labelledby="tab-estados-btn">

                        <?php if (!empty($dispositivo['historial'])): ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <ul class="timeline">
                                        <?php foreach ($dispositivo['historial'] as $index => $estado):
                                            $nombre = strtolower($estado['estado_nuevo']);
                                            $badgeClass = match (true) {
                                                $index === 0 => '',
                                                str_contains($nombre, 'entregad') => 'success',
                                                str_contains($nombre, 'cancel') => 'danger',
                                                str_contains($nombre, 'espera') => 'warning',
                                                default => 'info',
                                            };
                                            $badgeIcon = match (true) {
                                                $index === 0 => 'fas fa-star',
                                                str_contains($nombre, 'entregad') => 'fas fa-check',
                                                str_contains($nombre, 'cancel') => 'icon-close',
                                                str_contains($nombre, 'diagnos') => 'fas fa-search',
                                                str_contains($nombre, 'reparac') => 'fas fa-tools',
                                                default => 'fas fa-exchange-alt',
                                            };
                                            ?>
                                            <li <?= $index % 2 !== 0 ? 'class="timeline-inverted"' : '' ?>>
                                                <div class="timeline-badge <?= $badgeClass ?>">
                                                    <i class="<?= $badgeIcon ?>"></i>
                                                </div>
                                                <div class="timeline-panel">
                                                    <div class="timeline-heading">
                                                        <h4 class="timeline-title">
                                                            <?php if ($estado['estado_anterior']): ?>
                                                                <span class="text-muted fw-normal"
                                                                    style="font-size:.85em;"><?= esc($estado['estado_anterior']) ?></span>
                                                                <i class="fas fa-arrow-right mx-2 text-muted"
                                                                    style="font-size:.7em;"></i>
                                                            <?php endif; ?>
                                                            <?= esc($estado['estado_nuevo']) ?>
                                                        </h4>
                                                        <p>
                                                            <small class="text-muted">
                                                                <i class="fas fa-user me-1"></i><?= esc($estado['usuario']) ?>
                                                                &nbsp;·&nbsp;
                                                                <i
                                                                    class="far fa-clock me-1"></i><?= formatear_fecha($estado['fecha']) ?>
                                                            </small>
                                                        </p>
                                                    </div>
                                                    <?php if (!empty($estado['observacion'])): ?>
                                                        <div class="timeline-body">
                                                            <p><?= nl2br(esc($estado['observacion'])) ?></p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-exchange-alt fa-3x mb-3 d-block opacity-50"></i>
                                No hay cambios de estado registrados
                            </div>
                        <?php endif; ?>
                    </div>

                </div><!-- /tab-content -->
            </div><!-- /card-body -->
        </div><!-- /card -->

    </div>

</div>
<div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Finalizar Reparación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formFinalizar">
                <div class="modal-body">
                    <input type="hidden" name="dispositivo_id" value="<?= $dispositivo['id'] ?>">

                    <h6 class="fw-bold mb-3 border-bottom pb-2">Resolución de Problemas</h6>
                    <?php foreach ($dispositivo['problemas'] as $index => $prob): ?>
                        <div class="card mb-3 border-start border-info" style="border-left-width: 4px !important;">
                            <div class="card-body py-2">
                                <p class="mb-2 fw-bold text-primary"><?= esc($prob['problema']) ?></p>
                                <input type="hidden" name="problemas[<?= $index ?>][id]" value="<?= $prob['id'] ?>">
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="small">Estado</label>
                                        <select name="problemas[<?= $index ?>][estado]" class="form-select form-select-sm">
                                            <option value="resuelto">Resuelto</option>
                                            <option value="no_reparable">No reparable</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small">Mano de Obra ($)</label>
                                        <input type="number" step="0.01" name="problemas[<?= $index ?>][precio_mano_obra]" class="form-control form-control-sm" value="0.00">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="small">Repuesto ($)</label>
                                        <input type="number" step="0.01" name="problemas[<?= $index ?>][precio_repuesto]"
                                            class="form-control form-control-sm input-precio" value="0.00">
                                    </div>
                                    <div class="col-12 mt-2">
                                        <input type="text" name="problemas[<?= $index ?>][observacion]"
                                            class="form-control form-control-sm"
                                            placeholder="Observación técnica (opcional)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="mt-4">
                        <label class="fw-bold">Comentario final para el cliente</label>
                        <textarea name="comentario" class="form-control" rows="3" required
                            placeholder="Ej: Se realizó cambio de pantalla y limpieza interna. El equipo funciona correctamente."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success" id="btnGuardarFinalizar">Guardar y Finalizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function iniciarReparacion(id) {
        if (!confirm('¿Deseas cambiar el estado a "En Proceso"?')) return;

        fetch('<?= base_url('admin/dispositivos/reparacion/iniciar') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `dispositivo_id=${id}&estado=en_proceso`
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
    }

    document.getElementById('formFinalizar')?.addEventListener('submit', function (e) {
        e.preventDefault();

        const btn = document.getElementById('btnGuardarFinalizar');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

        const formData = new FormData(this);

        console.log('Datos a enviar:', Object.fromEntries(formData.entries()));

        fetch('<?= base_url('admin/dispositivos/reparacion/finalizar') ?>', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Reparación finalizada con éxito');
                    location.reload();
                } else {
                    alert(data.message);
                    btn.disabled = false;
                    btn.innerText = 'Guardar y Finalizar';
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
            });
    });
</script>
<?= $this->endSection() ?>