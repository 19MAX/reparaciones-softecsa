<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
Dashboard Técnico
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
    <div>
        <h3 class="fw-bold mb-3">Dashboard Técnico</h3>
        <h6 class="op-7 mb-2">Panel de Control - <?= esc($tecnico['nombres']) ?> <?= esc($tecnico['apellidos']) ?></h6>
    </div>
    <!-- <div class="ms-md-auto py-2 py-md-0">
        <a href="#" class="btn btn-label-info btn-round me-2">Manage</a>
        <a href="#" class="btn btn-primary btn-round">Add Customer</a>
    </div> -->
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
                        <p class="text-muted">Activos</p>
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
                            <?php if ($tecnico['tipo_comision'] === 'porcentaje'): ?>
                                <?= $tecnico['valor_comision'] ?>%
                            <?php else: ?>
                                $<?= number_format($tecnico['valor_comision'], 2) ?> fijo
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
                    <div class="ms-auto">
                        <a href="<?= base_url('tecnico/dispositivos') ?>" class="btn btn-primary btn-round me-2">
                            <i class="fa fa-list me-2"></i> Ver Todos
                        </a>
                        <a href="<?= base_url('tecnico/ingresos') ?>" class="btn btn-success btn-round">
                            <i class="fa fa-dollar-sign me-2"></i> Mis Ingresos
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
                                <th>Problema</th>
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
                                            <?php if (!empty($dispositivo['icono'])): ?>
                                                <i class="<?= $dispositivo['icono'] ?> me-1"></i>
                                            <?php endif; ?>
                                            <?= esc($dispositivo['nombre_tipo'] ?? $dispositivo['tipo']) ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= esc($dispositivo['marca']) ?>         <?= esc($dispositivo['modelo']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?= esc($dispositivo['cliente_nombres']) ?>
                                            <?= esc($dispositivo['cliente_apellidos']) ?>
                                        </td>
                                        <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 250px;"
                                                title="<?= esc($dispositivo['problema_reportado']) ?>">
                                                <?= esc($dispositivo['problema_reportado']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?= get_badge_estado_dispositivo(esc($dispositivo['estado_reparacion'])) ?>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('tecnico/dispositivos/ver/' . $dispositivo['id']) ?>"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> Ver y Actualizar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
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