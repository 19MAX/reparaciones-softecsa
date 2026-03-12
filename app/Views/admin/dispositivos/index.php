<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
Dispositivos por Técnico
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
            <a href="#">Dispositivos por Técnico</a>
        </li>
    </ul>
</div>

<!-- Dispositivos sin asignar -->
<?php if (!empty($sinAsignar)): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-warning">
                <div class="card-header bg-warning">
                    <h4 class="card-title mb-0 text-dark">
                        <i class="fas fa-exclamation-triangle me-2"></i>Dispositivos Sin Técnico Asignado
                        <span class="badge bg-danger ms-2"><?= count($sinAsignar) ?></span>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm" id="sin-asignar-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Orden</th>
                                    <th>Dispositivo</th>
                                    <th>Cliente</th>
                                    <th>Estado</th>
                                    <th>Asignar Técnico</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sinAsignar as $disp): ?>
                                    <tr id="row-disp-<?= $disp['id'] ?>">
                                        <td>
                                            <a href="<?= base_url('admin/dispositivos/detalle/' . $disp['id']) ?>"
                                                class="fw-bold">
                                                <?= esc($disp['codigo_orden']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?= esc($disp['tipo_dispositivo']) ?>
                                            <?= esc($disp['marca']) ?>
                                            <?= esc($disp['modelo']) ?>
                                        </td>
                                        <td><?= esc($disp['cliente_nombre'] . ' ' . $disp['cliente_apellido']) ?></td>
                                        <td><span class="badge badge-warning"><?= esc($disp['estado']) ?></span></td>
                                        <td>
                                            <div class="d-flex gap-2 align-items-center">
                                                <select class="form-select form-select-sm select-tecnico"
                                                    data-dispositivo="<?= $disp['id'] ?>" style="min-width: 160px;">
                                                    <option value="">-- Seleccionar --</option>
                                                    <?php foreach ($listaTecnicos as $tec): ?>
                                                        <option value="<?= $tec['id'] ?>">
                                                            <?= esc($tec['nombre'] ?? $tec['nombres'] . ' ' . ($tec['apellidos'] ?? '')) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="button" class="btn btn-sm btn-success btn-asignar"
                                                    data-dispositivo="<?= $disp['id'] ?>" title="Asignar">
                                                    <i class="fas fa-user-check"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">
                    <i class="fas fa-users-cog me-2"></i>Técnicos y Sus Dispositivos Asignados
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tecnicos-table">
                        <thead class="table-light">
                            <tr>
                                <th>Técnico</th>
                                <th>Comisión</th>
                                <th>Total Dispositivos</th>
                                <th>Activos</th>
                                <th>Listos para Retiro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($tecnicos)): ?>
                                <?php foreach ($tecnicos as $tecnico): ?>
                                    <tr>
                                        <td><?= esc($tecnico['tecnico_nombre']) ?></td>
                                        <td>
                                            <?php if ($tecnico['tipo_comision'] === 'porcentaje'): ?>
                                                <?= esc($tecnico['valor_comision']) ?>%
                                            <?php else: ?>
                                                $<?= esc(number_format($tecnico['valor_comision'], 2)) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($tecnico['total_dispositivos']) ?></td>
                                        <td><?= esc($tecnico['activos']) ?></td>
                                        <td><?= esc($tecnico['listos_para_retiro']) ?></td>
                                        <td>
                                            <a href="<?= base_url('admin/dispositivos/ver-tecnico/' . $tecnico['tecnico_id']) ?>"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye me-1"></i> Ver Dispositivos
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        <p>No hay técnicos registrados</p>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        $('#tecnicos-table').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json' },
            order: [[0, 'asc']]
        });

        // Asignar técnico desde la tabla de sin asignar
        $('body').on('click', '.btn-asignar', function () {
            let dispId = $(this).data('dispositivo');
            let tecnicoId = $('select.select-tecnico[data-dispositivo="' + dispId + '"]').val();

            if (!tecnicoId) {
                Swal.fire('Atención', 'Selecciona un técnico primero', 'warning');
                return;
            }

            $.post('<?= base_url('admin/dispositivos/asignar-tecnico') ?>', {
                dispositivo_id: dispId,
                tecnico_id: tecnicoId
            }, function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: res.message, toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
                    $('#row-disp-' + dispId).fadeOut(400, function () { $(this).remove(); });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }, 'json');
        });
    });
</script>
<?= $this->endSection() ?>