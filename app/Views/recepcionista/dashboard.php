<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
Dashboard Recepcionista
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
    <div>
        <h3 class="fw-bold mb-3">Dashboard Recepcionista</h3>
        <h6 class="op-7 mb-2">Panel de Control</h6>
    </div>
</div>

<div class="row row-card-no-pd">
    <div class="col-12 col-sm-6 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>Órdenes Hoy</b></h6>
                        <p class="text-muted">Creadas hoy</p>
                    </div>
                    <h4 class="text-info fw-bold"><?= $ordenesHoy ?></h4>
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
                        <h6><b>Órdenes Activas</b></h6>
                        <p class="text-muted">En proceso</p>
                    </div>
                    <h4 class="text-success fw-bold"><?= $ordenesActivas ?></h4>
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-success w-75" role="progressbar"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>Listas para Retiro</b></h6>
                        <p class="text-muted">Pendientes de entrega</p>
                    </div>
                    <h4 class="text-warning fw-bold"><?= $ordenesListasRetiro ?></h4>
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-warning w-50" role="progressbar"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-center align-items-center" style="height: 100px;">
                    <a href="<?= base_url('recepcionista/ordenes/crear') ?>" class="btn btn-primary btn-lg">
                        <i class="fa fa-plus me-2"></i> Nueva Orden
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title"><i class="fas fa-clock me-2"></i>Órdenes Recientes</h4>
                    <a href="<?= base_url('recepcionista/ordenes') ?>" class="btn btn-primary btn-round ms-auto">
                        <i class="fa fa-list me-2"></i> Ver Todas
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Cliente / Equipo</th>
                                <th>Estado</th>
                                <th width="140">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php if (!empty($ordenesRecientes)): ?>
                                <?php foreach ($ordenesRecientes as $orden): ?>
                                    <tr>

                                        <!-- Orden y Fecha -->
                                        <td>
                                            <div class="fw-bold text-primary">
                                                #<?= esc($orden['numero_orden']) ?>
                                            </div>

                                            <small class="text-muted d-block">
                                                <?= formatear_fecha($orden['created_at'], 'solo_fecha') ?>
                                            </small>

                                            <small class="text-muted">
                                                <?= date('H:i', strtotime($orden['created_at'])) ?>
                                            </small>
                                        </td>

                                        <!-- Cliente y Equipos -->
                                        <td>
                                            <div class="fw-semibold">
                                                <?= esc($orden['nombres']) ?>
                                                <?= esc($orden['apellidos']) ?>
                                            </div>

                                            <small class="text-muted d-block"
                                                title="<?= esc($orden['equipos_resumen']) ?>">
                                                <?= esc($orden['equipos_resumen']) ?>
                                            </small>
                                        </td>

                                        <!-- Estado -->
                                        <td>
                                            <?= estadoPill($orden['estado']) ?>
                                        </td>

                                        <!-- Acciones -->
                                        <td>
                                            <div class="btn-group">

                                                <!-- Ver detalle -->
                                                <!-- <a href="<?= base_url('recepcionista/ordenes/ver/' . $orden['id']) ?>"
                                                    class="btn btn-sm btn-outline-info"
                                                    data-bs-toggle="tooltip"
                                                    title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a> -->

                                                <!-- Dropdown Imprimir -->
                                                <div class="dropdown">
                                                    <button
                                                        class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                        type="button"
                                                        data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-print"></i>
                                                    </button>

                                                    <ul class="dropdown-menu dropdown-menu-end">

                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="<?= base_url('recepcionista/ordenes/imprimir/' . $orden['id'] . '/ticket') ?>"
                                                                target="_blank">
                                                                <i class="fas fa-file-alt me-2"></i>
                                                                Ticket
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="<?= base_url('recepcionista/ordenes/imprimir/' . $orden['id'] . '/carta') ?>"
                                                                target="_blank">
                                                                <i class="fas fa-file me-2"></i>
                                                                Carta
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="<?= base_url('recepcionista/ordenes/imprimir/' . $orden['id'] . '/completo') ?>"
                                                                target="_blank">
                                                                <i class="fas fa-file-invoice me-2"></i>
                                                                Completo
                                                            </a>
                                                        </li>

                                                    </ul>
                                                </div>

                                            </div>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        No hay órdenes recientes
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