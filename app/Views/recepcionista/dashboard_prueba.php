<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Dashboard Recepcionista<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
    <div>
        <h3 class="fw-bold mb-3">Dashboard Recepcionista</h3>
        <h6 class="op-7 mb-2">Gestión de órdenes y atención al cliente</h6>
    </div>
    <div class="ms-md-auto py-2 py-md-0">
        <a href="<?= base_url('recepcionista/crear-orden') ?>" class="btn btn-primary btn-round">
            <i class="fas fa-plus"></i> Nueva Orden
        </a>
    </div>
</div>

<!-- Tarjetas de resumen -->
<div class="row row-card-no-pd">
    <div class="col-12 col-sm-6 col-md-6 col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>Órdenes de Hoy</b></h6>
                        <p class="text-muted">Recibidas hoy</p>
                    </div>
                    <h4 class="text-info fw-bold"><?= $ordenes_hoy ?></h4>
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-info w-75" role="progressbar" aria-valuenow="75" aria-valuemin="0"
                        aria-valuemax="100"></div>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <p class="text-muted mb-0">Total</p>
                    <a href="<?= base_url('recepcionista/consultar-estado') ?>" class="text-info">Ver todas</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-6 col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>Pendientes de Entrega</b></h6>
                        <p class="text-muted">Dispositivos listos</p>
                    </div>
                    <h4 class="text-success fw-bold"><?= count($pendientes_entrega) ?></h4>
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-success w-50" role="progressbar" aria-valuenow="50" aria-valuemin="0"
                        aria-valuemax="100"></div>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <p class="text-muted mb-0">Para entregar</p>
                    <a href="<?= base_url('recepcionista/dispositivos-para-entregar') ?>" class="text-success">Gestionar</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-6 col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>Reclamos Pendientes</b></h6>
                        <p class="text-muted">Garantías activas</p>
                    </div>
                    <h4 class="text-warning fw-bold"><?= count($reclamos_pendientes) ?></h4>
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-warning w-30" role="progressbar" aria-valuenow="30" aria-valuemin="0"
                        aria-valuemax="100"></div>
                </div>
                <div class="d-flex justify-content-between mt-2">
                    <p class="text-muted mb-0">Activos</p>
                    <a href="<?= base_url('recepcionista/iniciar-reclamo-garantia') ?>" class="text-warning">Ver reclamos</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Accesos rápidos -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Accesos Rápidos</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('recepcionista/crear-orden') ?>" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-file-alt fa-2x mb-2"></i><br>
                            Nueva Orden
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('recepcionista/consultar-estado') ?>" class="btn btn-info btn-lg w-100">
                            <i class="fas fa-search fa-2x mb-2"></i><br>
                            Consultar Estado
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('recepcionista/dispositivos-para-entregar') ?>" class="btn btn-success btn-lg w-100">
                            <i class="fas fa-box-open fa-2x mb-2"></i><br>
                            Entregar Dispositivo
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('recepcionista/iniciar-reclamo-garantia') ?>" class="btn btn-warning btn-lg w-100">
                            <i class="fas fa-shield-alt fa-2x mb-2"></i><br>
                            Reclamo Garantía
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Órdenes recientes -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Órdenes Recientes de Hoy</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Cliente</th>
                                <th>Dispositivos</th>
                                <th>Estado</th>
                                <th>Urgencia</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ordenes_hoy)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay órdenes registradas hoy</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ordenes_hoy as $orden): ?>
                                    <tr>
                                        <td><strong><?= $orden['codigo_orden'] ?></strong></td>
                                        <td><?= $orden['cliente_nombre'] ?></td>
                                        <td><?= $orden['total_dispositivos'] ?></td>
                                        <td>
                                            <span class="badge badge-<?= $orden['estado_global'] == 'pendiente' ? 'warning' : 'info' ?>">
                                                <?= ucfirst($orden['estado_global']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $orden['urgencia_color'] ?>">
                                                <?= $orden['urgencia_nombre'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('recepcionista/ver-orden/' . $orden['id']) ?>" 
                                               class="btn btn-sm btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= base_url('recepcionista/imprimir-orden/' . $orden['id']) ?>" 
                                               class="btn btn-sm btn-secondary" title="Imprimir" target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>