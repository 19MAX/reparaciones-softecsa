<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Mis Reparaciones<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Mis Reparaciones</h4>
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
                <a href="#">Dispositivos</a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="#">Mis Reparaciones</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Listado de mis Reparaciones</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabla-mis-reparaciones" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Fecha Ingreso</th>
                                    <th>Cliente</th>
                                    <th>Equipo</th>
                                    <th>Estado</th>
                                    <th>Est. Entrega</th>
                                    <th>Total</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reparaciones as $rep): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-primary"><?= esc($rep['numero_orden']) ?></span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($rep['fecha_ingreso'])) ?></td>
                                        <td><?= esc($rep['cliente_nombre']) ?>     <?= esc($rep['cliente_apellido']) ?></td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold"><?= esc($rep['marca']) ?>
                                                    <?= esc($rep['modelo']) ?></span>
                                                <small class="text-muted"><?= esc($rep['tipo_dispositivo']) ?></small>
                                                <?php if ($rep['serie_imei']): ?>
                                                    <small class="text-muted">SN: <?= esc($rep['serie_imei']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?= estadoPill($rep['estado']) ?>
                                        </td>
                                        <td>
                                            <?= $rep['fecha_estimada_entrega'] ? date('d/m/Y', strtotime($rep['fecha_estimada_entrega'])) : '—' ?>
                                        </td>
                                        <td class="fw-bold text-success">
                                            $<?= number_format($rep['precio_total'], 2) ?>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('admin/dispositivos/detalle/' . $rep['id']) ?>"
                                                class="btn btn-link btn-primary" data-bs-toggle="tooltip"
                                                title="Ver Detalle">
                                                <i class="fa fa-eye"></i>
                                            </a>
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
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function () {
        $('#tabla-mis-reparaciones').DataTable({
            "pageLength": 10,
            "order": [[1, "desc"]], // Ordenar por fecha de ingreso por defecto
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json"
            },
            "scrollX": true
        });
    });
</script>
<?= $this->endSection() ?>