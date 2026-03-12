<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
Dashboard Técnico
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
    <div>
        <h3 class="fw-bold mb-3">Dashboard Técnico</h3>
        <h6 class="op-7 mb-2">Panel de Control - <?= esc($tecnico['nombres'] ?? $tecnico['nombre']) ?> <?= esc($tecnico['apellidos'] ?? '') ?></h6>
    </div>
</div>

<div class="row row-card-no-pd">
    <div class="col-12 col-sm-6 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>Total Asignados</b></h6>
                        <p class="text-muted">Dispositivos</p>
                    </div>
                    <h4 class="text-info fw-bold"><?= $totalDispositivos ?></h4>
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-info w-100" role="progressbar"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>En Proceso</b></h6>
                        <p class="text-muted">Activos / Pendientes</p>
                    </div>
                    <h4 class="text-warning fw-bold"><?= $dispositivosEnProceso ?></h4>
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-warning w-75" role="progressbar"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>Finalizados</b></h6>
                        <p class="text-muted">Este Mes</p>
                    </div>
                    <h4 class="text-success fw-bold"><?= $dispositivosFinalizadosMes ?></h4>
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-success w-50" role="progressbar"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>Ganancia Mes</b></h6>
                        <p class="text-muted">
                            <?php if (!empty($tecnico['config'])): ?>
                                <?php if ($tecnico['config']['tipo_comision'] === 'porcentaje'): ?>
                                    <?= $tecnico['config']['valor_comision'] ?>%
                                <?php else: ?>
                                    $<?= number_format($tecnico['config']['valor_comision'], 2) ?> fijo
                                <?php endif; ?>
                            <?php else: ?>
                                Sin configurar
                            <?php endif; ?>
                        </p>
                    </div>
                    <h4 class="text-primary fw-bold">$<?= number_format($gananciaMes, 2) ?></h4>
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-primary w-60" role="progressbar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title"><i class="fas fa-laptop me-2"></i>Mis Dispositivos Recientes</h4>
                    <div class="ms-auto d-flex gap-2">
                        <a href="<?= base_url('tecnico/dispositivos/pool') ?>" class="btn btn-warning btn-round btn-sm">
                            <i class="fa fa-list-ul me-2"></i> Pool
                        </a>
                        <a href="<?= base_url('tecnico/dispositivos/asignados') ?>" class="btn btn-primary btn-round btn-sm">
                            <i class="fa fa-laptop me-2"></i> Mis Asignados
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Dispositivo</th>
                                <th>Cliente</th>
                                <th>Ingreso</th>
                                <th>Estado Actual</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dispositivosRecientes)): ?>
                                <?php foreach ($dispositivosRecientes as $dispositivo): ?>
                                    <tr>
                                        <td class="fw-bold text-primary">
                                            <?= esc($dispositivo['codigo_orden']) ?>
                                        </td>
                                        <td>
                                            <?= esc($dispositivo['nombre_tipo']) ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= esc($dispositivo['marca']) ?> <?= esc($dispositivo['modelo']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?= esc($dispositivo['cliente_nombres']) ?>
                                            <?= esc($dispositivo['cliente_apellidos']) ?>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y H:i', strtotime($dispositivo['created_at'])) ?>
                                        </td>
                                        <td>
                                            <?= estadoPill($dispositivo['estado']) ?>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('tecnico/dispositivos/detalle/' . $dispositivo['id']) ?>"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Detalle
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No tienes dispositivos asignados aún
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>