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
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/dispositivos') ?>">Dispositivos por Técnico</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#"><?= esc($tecnico['nombres']) ?> <?= esc($tecnico['apellidos']) ?></a>
        </li>
    </ul>
</div>

<!-- Información del Técnico -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="text-white mb-1">
                            <i class="fas fa-user-cog me-2"></i>
                            <?= esc($tecnico['nombres']) ?> <?= esc($tecnico['apellidos']) ?>
                        </h4>
                        <p class="mb-0">
                            Comisión:
                            <?php if ($tecnico['tipo_comision'] === 'porcentaje'): ?>
                                <strong><?= $tecnico['valor_comision'] ?>%</strong> de mano de obra
                            <?php else: ?>
                                <strong>$<?= number_format($tecnico['valor_comision'], 2) ?></strong> fijo por dispositivo
                            <?php endif; ?>
                        </p>
                    </div>
                    <a href="<?= base_url('admin/dispositivos') ?>" class="btn btn-light">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Órdenes y Dispositivos -->
<?php if (!empty($ordenes)): ?>
    <?php foreach ($ordenes as $orden): ?>
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header <?= $orden['estado_orden'] === 'entregado' ? 'bg-dark text-white' : 'bg-light' ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1 <?= $orden['estado_orden'] === 'entregado' ? 'text-white' : '' ?>">
                                    <i class="fas fa-file-alt me-2"></i>
                                    Orden: <?= esc($orden['codigo_orden']) ?>
                                </h5>
                                <p class="mb-0 <?= $orden['estado_orden'] === 'entregado' ? 'text-white-50' : 'text-muted' ?>">
                                    Cliente: <?= esc($orden['cliente_nombres']) ?>         <?= esc($orden['cliente_apellidos']) ?>
                                    | Tel: <?= esc($orden['cliente_telefono']) ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <div>
                                    <?= get_badge_estado_orden($orden['estado_orden']) ?>
                                </div>
                                <br>
                                <?php if ($orden['estado_orden'] !== 'entregado' && $orden['estado_orden'] !== 'cancelado'): ?>
                                    <?php if ($orden['todos_listos']): ?>
                                        <a href="<?= base_url('admin/ordenes/entregar/' . $orden['orden_id']) ?>"
                                            class="btn btn-success btn-sm"
                                            onclick="return confirm('¿Confirmar entrega de orden <?= esc($orden['codigo_orden']) ?>?\n\nTotal mano de obra: $<?= number_format($orden['total_mano_obra'], 2) ?>\nTotal repuestos: $<?= number_format($orden['total_repuestos'], 2) ?>')">
                                            <i class="fas fa-check-circle me-1"></i> Entregar Orden
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-secondary btn-sm" disabled
                                            title="No todos los dispositivos están listos">
                                            <i class="fas fa-clock me-1"></i> Esperando Finalización
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Dispositivo</th>
                                        <th>Serie/IMEI</th>
                                        <th>Estado</th>
                                        <th>Mano Obra</th>
                                        <th>Repuestos</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orden['dispositivos'] as $disp): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($disp['icono'])): ?>
                                                    <i class="<?= $disp['icono'] ?> me-1"></i>
                                                <?php endif; ?>
                                                <?= esc($disp['nombre_tipo']) ?>
                                            </td>
                                            <td class="fw-bold">
                                                <?= esc($disp['marca']) ?>             <?= esc($disp['modelo']) ?>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= esc($disp['serie_imei'] ?? 'N/A') ?></small>
                                            </td>
                                            <td>
                                                <?= get_badge_estado_dispositivo(esc($disp['estado_reparacion'])) ?>

                                            </td>
                                            <td>$<?= number_format($disp['mano_obra'], 2) ?></td>
                                            <td>$<?= number_format($disp['valor_repuestos'], 2) ?></td>
                                            <td class="fw-bold">
                                                $<?= number_format($disp['mano_obra'] + $disp['valor_repuestos'], 2) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="4" class="text-end">TOTALES:</th>
                                        <th>$<?= number_format($orden['total_mano_obra'], 2) ?></th>
                                        <th>$<?= number_format($orden['total_repuestos'], 2) ?></th>
                                        <th>$<?= number_format($orden['total_mano_obra'] + $orden['total_repuestos'], 2) ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3 d-block"></i>
                    <h5 class="text-muted">Este técnico no tiene dispositivos asignados</h5>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>