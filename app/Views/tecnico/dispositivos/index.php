<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
Mis Dispositivos Asignados
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('tecnico/dashboard') ?>">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Mis Dispositivos</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title"><i class="fas fa-laptop me-2"></i>Dispositivos Asignados a Mí</h4>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dispositivos-datatables" class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Orden</th>
                                <th>Tipo</th>
                                <th>Dispositivo</th>
                                <th>Cliente</th>
                                <th>Problema Reportado</th>
                                <th>Estado Actual</th>
                                <th>Fecha Asignación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dispositivos)): ?>
                                <?php foreach ($dispositivos as $dispositivo): ?>
                                    <tr>
                                        <td class="fw-bold text-primary">
                                            <?= esc($dispositivo['codigo_orden']) ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($dispositivo['icono'])): ?>
                                                <i class="<?= $dispositivo['icono'] ?> fa-lg text-primary me-1"></i>
                                            <?php endif; ?>
                                            <br>
                                            <small><?= esc($dispositivo['nombre_tipo'] ?? $dispositivo['tipo']) ?></small>
                                        </td>
                                        <td class="fw-bold">
                                            <?= esc($dispositivo['marca']) ?>         <?= esc($dispositivo['modelo']) ?>
                                            <?php if (!empty($dispositivo['serie_imei'])): ?>
                                                <br>
                                                <small class="text-muted">
                                                    S/N: <?= esc($dispositivo['serie_imei']) ?>
                                                </small>
                                            <?php endif; ?>
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

                                            <br>
                                            <small class="text-muted">
                                                Orden: <?=get_badge_estado_orden($dispositivo['estado_orden'])?>
                                            </small>
                                        </td>
                                        <td>
                                            <small><?= formatear_fecha($dispositivo['created_at'], 'solo_fecha') ?></small>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('tecnico/dispositivos/ver/' . $dispositivo['id']) ?>"
                                                class="btn btn-sm btn-primary btn-block">
                                                <i class="fas fa-eye"></i> Ver y Actualizar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        No tienes dispositivos asignados actualmente
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

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function () {
        $('#dispositivos-datatables').DataTable({
            scrollX: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json',
            },
            order: [[6, 'desc']], // Ordenar por fecha descendente
            layout: {
                topStart: {
                    buttons: ['pageLength', 'copy', 'excel', 'pdf']
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>