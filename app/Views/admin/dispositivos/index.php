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
                                        <td class="fw-bold">
                                            <i class="fas fa-user-cog text-primary me-2"></i>
                                            <?= esc($tecnico['nombres']) ?> <?= esc($tecnico['apellidos']) ?>
                                        </td>
                                        <td>
                                            <?php if ($tecnico['tipo_comision'] === 'porcentaje'): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <?= $tecnico['valor_comision'] ?>%
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-dark">
                                                    $<?= number_format($tecnico['valor_comision'], 2) ?> fijo
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= $tecnico['total_dispositivos'] ?? 0 ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= $tecnico['dispositivos_activos'] ?? 0 ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                <?= $tecnico['dispositivos_listos'] ?? 0 ?>
                                            </span>
                                        </td>
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
<script>
    $(document).ready(function () {
        $('#tecnicos-table').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json',
            },
            order: [[0, 'asc']]
        });
    });
</script>
<?= $this->endSection() ?>
