<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?? 'Detalles del Dispositivo' ?>
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
            <a href="<?= base_url('recepcionista/ordenes/ver/' . $dispositivo['orden_id']) ?>">
                <?= esc($dispositivo['codigo_orden']) ?>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Dispositivo</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Información del Dispositivo -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="card-title mb-0">
                        <?php if (!empty($dispositivo['icono'])): ?>
                            <i class="<?= $dispositivo['icono'] ?> me-2"></i>
                        <?php endif; ?>
                        <?= esc($dispositivo['marca']) ?> <?= esc($dispositivo['modelo']) ?>
                    </h4>
                    <a href="<?= base_url('recepcionista/ordenes/ver/' . $dispositivo['orden_id']) ?>"
                        class="btn btn-primary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Volver a Orden
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" width="40%">Tipo de Dispositivo:</td>
                                <td class="fw-bold"><?= esc($dispositivo['nombre_tipo'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Marca:</td>
                                <td class="fw-bold"><?= esc($dispositivo['marca']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Modelo:</td>
                                <td class="fw-bold"><?= esc($dispositivo['modelo']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Serie/IMEI:</td>
                                <td class="fw-bold"><?= esc($dispositivo['serie_imei'] ?? 'N/A') ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted" width="40%">Cliente:</td>
                                <td class="fw-bold"><?= esc($dispositivo['cliente_nombres']) ?>
                                    <?= esc($dispositivo['cliente_apellidos']) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Orden:</td>
                                <td class="fw-bold">
                                    <a href="<?= base_url('recepcionista/ordenes/ver/' . $dispositivo['orden_id']) ?>">
                                        <?= esc($dispositivo['codigo_orden']) ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Estado Actual:</td>
                                <td>
                                    <?= get_badge_estado_dispositivo(esc($dispositivo['estado_reparacion'])) ?>
                                </td>
                            </tr>
                            <?php if (!empty($dispositivo['tecnico_nombres'])): ?>
                                <tr>
                                    <td class="text-muted">Técnico Asignado:</td>
                                    <td class="fw-bold">
                                        <i class="fas fa-user-cog me-1"></i>
                                        <?= esc($dispositivo['tecnico_nombres']) ?>
                                        <?= esc($dispositivo['tecnico_apellidos']) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <h6 class="fw-bold text-muted mb-2">Problema Reportado:</h6>
                            <div class="p-3 bg-light rounded">
                                <?= nl2br(esc($dispositivo['problema_reportado'])) ?>
                            </div>
                        </div>

                        <?php if (!empty($dispositivo['observaciones'])): ?>
                            <div class="mb-3">
                                <h6 class="fw-bold text-muted mb-2">Observaciones Iniciales:</h6>
                                <div class="p-3 bg-light rounded">
                                    <?= nl2br(esc($dispositivo['observaciones'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($dispositivo['tipo_pass'] !== 'ninguno'): ?>
                            <div class="mb-3">
                                <h6 class="fw-bold text-muted mb-2">Seguridad del Dispositivo:</h6>
                                <div class="p-3 bg-light rounded">
                                    <strong>Tipo de bloqueo:</strong>
                                    <span class="badge bg-info"><?= strtoupper($dispositivo['tipo_pass']) ?></span>
                                    <?php if (!empty($dispositivo['pass_code'])): ?>
                                        <br>
                                        <small class="text-muted">PIN/Patrón registrado</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial del Dispositivo -->
    <div class="col-md-4">
        <div class="card card-primary sticky-top" style="top: 20px;">
            <div class="card-header">
                <h4 class="card-title text-white mb-0">
                    <i class="fas fa-history me-2"></i>Historial y Comentarios
                </h4>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                <?php if (!empty($historial)): ?>
                    <div class="">
                        <?php foreach ($historial as $registro): ?>
                            <div class="timeline-item mb-3">
                                <div class="d-flex align-items-start">
                                    <div class="avatar avatar-sm bg-primary text-white rounded-circle p-2 me-2">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="mb-0 fw-bold">
                                                <?= esc($registro['usuario_nombres']) ?>
                                                <?= esc($registro['usuario_apellidos']) ?>
                                            </h6>
                                            <small class="text-muted">
                                                <?= formatear_fecha($registro['created_at'], 'completo') ?>
                                            </small>
                                        </div>

                                        <?php if ($registro['estado_anterior'] !== $registro['estado_nuevo']): ?>
                                            <div class="mb-1">
                                                <span class="badge badge-sm bg-secondary">
                                                    <?= esc($registro['estado_anterior'] ?? 'Nuevo') ?>
                                                </span>
                                                <i class="fas fa-arrow-right mx-1 text-muted"></i>
                                                <span class="badge badge-sm bg-primary">
                                                    <?= esc($registro['estado_nuevo']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($registro['comentario'])): ?>
                                            <div class="p-2 bg-light rounded mt-2 text-dark">
                                                <small><?= nl2br(esc($registro['comentario'])) ?></small>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($registro['es_visible_cliente']): ?>
                                            <div class="mt-1">
                                                <span class="badge badge-success badge-sm">
                                                    <i class="fas fa-eye me-1"></i>Visible para cliente
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($registro !== end($historial)): ?>
                                    <hr class="my-2">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                        <p>No hay historial registrado aún</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('styles') ?>

<?= $this->endSection() ?>