<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
Detalles del Cliente
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h4 class="page-title">Perfil de Cliente</h4>
    <ul class="breadcrumbs">
        <li class="nav-home">
            <a href="<?= base_url('admin/dashboard') ?>">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('admin/clientes') ?>">Clientes</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Perfil</a>
        </li>
    </ul>
</div>

<div class="row">
    <!-- Información del Cliente -->
    <div class="col-md-4">
        <div class="card card-profile">
            <div class="card-header" style="background-image: url('<?= base_url('assets/img/blogpost.jpg') ?>')">
                <div class="profile-picture">
                    <div class="avatar avatar-xl">
                        <span class="avatar-title rounded-circle border border-white bg-primary">
                            <?= strtoupper(substr($cliente['nombres'], 0, 1) . substr($cliente['apellidos'], 0, 1)) ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="user-profile text-center">
                    <div class="name"><?= esc($cliente['nombres'] . ' ' . $cliente['apellidos']) ?></div>
                    <div class="job">Cliente</div>
                    <div class="desc">Cédula/RUC: <?= esc($cliente['cedula']) ?></div>
                    
                    <div class="view-profile mt-4">
                        <ul class="list-group list-group-unbordered text-start">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-phone text-muted me-2"></i> Teléfono:</span>
                                <b><?= esc($cliente['telefono']) ?></b>
                            </li>
                            <?php if(!empty($cliente['telefono_secundario'])): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-phone-alt text-muted me-2"></i> Teléfono Sec.:</span>
                                <b><?= esc($cliente['telefono_secundario']) ?></b>
                            </li>
                            <?php endif; ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-envelope text-muted me-2"></i> Email:</span>
                                <b><?= esc($cliente['email'] ?? 'No especificado') ?></b>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-calendar-alt text-muted me-2"></i> Registrado:</span>
                                <b><?= date('d/m/Y', strtotime($cliente['created_at'])) ?></b>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Órdenes -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title"><i class="fas fa-history me-2"></i> Historial de Órdenes</h4>
                    <a href="<?= base_url('admin/ordenes/crear') ?>" class="btn btn-primary btn-round ms-auto btn-sm">
                        <i class="fa fa-plus"></i>
                        Nueva Orden
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tabla-ordenes-cliente">
                        <thead>
                            <tr>
                                <th>N° Orden</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Ver</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($ordenes)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Este cliente no tiene órdenes registradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($ordenes as $orden): ?>
                                    <tr>
                                        <td class="fw-bold">
                                            <a href="<?= base_url('admin/ordenes/editar/' . $orden['id']) ?>">
                                                <?= esc($orden['numero_orden']) ?>
                                            </a>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($orden['created_at'])) ?></td>
                                        <td><?= estadoPill($orden['estado']) ?></td>
                                        <td>
                                            <a href="<?= base_url('admin/ordenes/editar/' . $orden['id']) ?>" class="btn btn-icon btn-round btn-primary btn-sm">
                                                <i class="fa fa-eye"></i>
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

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#tabla-ordenes-cliente').DataTable({
            "pageLength": 5,
            "order": [[1, "desc"]],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json"
            },
            "bLengthChange": false
        });
    });
</script>
<?= $this->endSection() ?>