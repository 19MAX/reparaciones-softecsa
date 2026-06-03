<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
Pool de Dispositivos (Sin Asignar)
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-header">
    <h4 class="page-title">Dispositivos Sin Asignar</h4>
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('tecnico/dashboard') ?>"><i class="icon-home"></i></a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Pool</a></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h4 class="card-title mb-0" style="font-size: 1rem; font-weight: 700;">Dispositivos Pendientes de
                    Reparación</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle" id="tabla-pool">
                        <thead class="table-light text-muted" style="font-size: 0.8rem; text-transform: uppercase;">
                            <tr>
                                <th>Orden</th>
                                <th>Ingreso</th>
                                <th>Equipo</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.85rem;">
                            <?php foreach ($sinAsignar as $dev): ?>
                                <tr>
                                    <td>
                                        <a href="<?= base_url('consulta/orden/' . $dev['codigo_orden']) ?>" target="_blank"
                                            rel="noopener noreferrer" title="Ver seguimiento"><span
                                                class="badge bg-light text-dark border"><?= esc($dev['codigo_orden']) ?></span></a>
                                    </td>
                                    <td>
                                        <?= date('d/m/y H:i', strtotime($dev['created_at'])) ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= esc($dev['marca']) ?>     <?= esc($dev['modelo']) ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <?= esc($dev['tipo_dispositivo']) ?></div>
                                    </td>
                                    <td>
                                        <?= esc($dev['cliente_nombre']) ?>     <?= esc($dev['cliente_apellido']) ?>
                                    </td>
                                    <td>
                                        <?= estadoPill($dev['estado']) ?>
                                    </td>
                                    <td>
                                        <div class="form-button-action">
                                            <!-- Print dropdown -->
                                            <div class="dropdown d-inline">
                                                <a class="btn btn-link btn-secondary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="Imprimir">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="<?= base_url('admin/ordenes/imprimir/' . $dev['id'] . '/carta') ?>" target="_blank">Carta</a></li>
                                                    <li><a class="dropdown-item" href="<?= base_url('admin/ordenes/imprimir/' . $dev['id'] . '/ticket') ?>" target="_blank">Ticket</a></li>
                                                    <li><a class="dropdown-item" href="<?= base_url('admin/ordenes/imprimir/' . $dev['id']) ?>" target="_blank">Completo</a></li>
                                                </ul>
                                            </div>
                                            <a href="<?= base_url('tecnico/dispositivos/detalle/' . $dev['id']) ?>" class="btn btn-link btn-primary" title="Ver Detalle / Tomar Reparación">
                                                <i class="fas fa-edit"></i>
                                            </a>
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function () {
        $('#tabla-pool').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json' },
            order: [[1, 'asc']], // Más antiguos primero
        });
    });
</script>
<?= $this->endSection() ?>