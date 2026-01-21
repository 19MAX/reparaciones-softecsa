<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?? 'Detalles de Orden' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('recepcionista/dashboard') ?>">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('recepcionista/ordenes') ?>">Órdenes</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#"><?= esc($orden['codigo_orden']) ?></a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>Orden <?= esc($orden['codigo_orden']) ?>
                    </h4>
                    <div>
                        <?php if ((int) $orden['estado'] !== ESTADO_ORDEN_ENTREGADA && (int) $orden['estado'] !== ESTADO_ORDEN_CANCELADA): ?>

                            <form action="<?= base_url('recepcionista/ordenes/entregar/' . $orden['id']) ?>" method="POST"
                                class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-success btn-sm me-2"
                                    onclick="return confirm('¿Confirmar entrega? Esta acción finalizará la orden.')">
                                    <i class="fas fa-check-circle me-1"></i> Entregar al Cliente
                                </button>
                            </form>

                        <?php endif; ?>

                        <a href="<?= base_url('recepcionista/ordenes/imprimir/' . $orden['id']) ?>" target="_blank"
                            class="btn btn-secondary btn-sm me-2">
                            <i class="fas fa-print me-1"></i> Imprimir
                        </a>
                        <a href="<?= base_url('recepcionista/ordenes') ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body">
                                <h5 class="fw-bold mb-3">
                                    <i class="fas fa-user text-primary me-2"></i>Información del Cliente
                                </h5>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="text-muted" width="40%">Nombre:</td>
                                        <td class="fw-bold"><?= esc($orden['nombres']) ?>
                                            <?= esc($orden['apellidos']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Cédula/RUC:</td>
                                        <td class="fw-bold"><?= esc($orden['cedula']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Teléfono:</td>
                                        <td class="fw-bold"><?= esc($orden['telefono']) ?></td>
                                    </tr>
                                    <?php if (!empty($orden['email'])): ?>
                                        <tr>
                                            <td class="text-muted">Email:</td>
                                            <td class="fw-bold"><?= esc($orden['email']) ?></td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card bg-light border-0 mb-3">
                            <div class="card-body">
                                <h5 class="fw-bold mb-3">
                                    <i class="fas fa-info-circle text-success me-2"></i>Información de la Orden
                                </h5>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="text-muted" width="40%">Fecha de Ingreso:</td>
                                        <td class="fw-bold"><?= formatear_fecha($orden['created_at']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Estado:</td>
                                        <td>
                                            <?= get_badge_estado_orden((int) $orden['estado']) ?>
                                        </td>
                                    </tr>
                                    <?php if (!empty($orden['nombre_urgencia'])): ?>
                                        <tr>
                                            <td class="text-muted">Prioridad:</td>
                                            <td>
                                                <span
                                                    class="badge <?= $orden['recargo'] > 0 ? 'badge-warning' : 'bg-light text-dark border' ?>">
                                                    <?= esc($orden['nombre_urgencia']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td class="text-muted">Total Estimado:</td>
                                        <td class="fw-bold text-success">$<?= number_format($orden['total'], 2) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-laptop text-info me-2"></i>Dispositivos
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Tipo</th>
                                        <th>Marca/Modelo</th>
                                        <th>Serie/IMEI</th>
                                        <th>Problema Reportado</th>
                                        <th>Estado Actual</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($dispositivos)): ?>
                                        <?php foreach ($dispositivos as $index => $dispositivo): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td>
                                                    <?php if (!empty($dispositivo['icono'])): ?>
                                                        <i class="<?= $dispositivo['icono'] ?> me-1"></i>
                                                    <?php endif; ?>
                                                    <?= esc($dispositivo['nombre_tipo'] ?? $dispositivo['tipo']) ?>
                                                </td>
                                                <td class="fw-bold">
                                                    <?= esc($dispositivo['marca']) ?>         <?= esc($dispositivo['modelo']) ?>
                                                </td>
                                                <td>
                                                    <small
                                                        class="text-muted"><?= esc($dispositivo['serie_imei'] ?? 'N/A') ?></small>
                                                </td>
                                                <td>
                                                    <span class="d-inline-block text-truncate" style="max-width: 250px;"
                                                        title="<?= esc($dispositivo['problema_reportado']) ?>">
                                                        <?= esc($dispositivo['problema_reportado']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?= get_badge_estado_dispositivo((int) $dispositivo['estado_reparacion']) ?>
                                                </td>
                                                <td>
                                                    <a href="<?= base_url('recepcionista/dispositivos/ver/' . $dispositivo['id']) ?>"
                                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                                        title="Ver Detalles y Historial">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                                No hay dispositivos asociados a esta orden
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
    </div>
</div>
<?= $this->endSection() ?>