<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Mis Reparaciones<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Mis Reparaciones</h4>
        <ul class="breadcrumbs">
            <li class="nav-home">
                <a href="<?= base_url('tecnico/dashboard') ?>">
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
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title mb-0" style="font-size: 1rem; font-weight: 700;">Listado de mis
                            Reparaciones</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabla-mis-reparaciones" class="table table-hover table-sm align-middle">
                            <thead class="table-light text-muted" style="font-size: 0.8rem; text-transform: uppercase;">
                                <tr>
                                    <th>Orden</th>
                                    <!-- <th>Fecha Ingreso</th> -->
                                    <th>Cliente</th>
                                    <th>Equipo</th>
                                    <th>Estado</th>
                                    <!-- <th>Est. Entrega</th> -->
                                    <th>Total</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 0.85rem;">
                                <?php foreach ($reparaciones as $rep): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= base_url('consulta/orden/' . $rep['numero_orden']) ?>"
                                                target="_blank" rel="noopener noreferrer" title="Ver seguimiento"><span
                                                    class="badge bg-light text-dark border"><?= esc($rep['numero_orden']) ?></span></a>
                                        </td>
                                        <!-- <td><?= date('d/m/y H:i', strtotime($rep['fecha_ingreso'])) ?></td> -->
                                        <td><?= esc($rep['cliente_nombre']) ?>     <?= esc($rep['cliente_apellido']) ?></td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark"><?= esc($rep['marca']) ?>
                                                    <?= esc($rep['modelo']) ?></span>
                                                <small class="text-muted"
                                                    style="font-size: 0.75rem;"><?= esc($rep['tipo_dispositivo']) ?></small>
                                                <?php if ($rep['serie_imei']): ?>
                                                    <small class="text-muted" style="font-size: 0.75rem;">SN:
                                                        <?= esc($rep['serie_imei']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?= estadoPill($rep['estado']) ?>
                                        </td>
                                        <!-- <td>
                                            <?= $rep['fecha_estimada_entrega'] ? date('d/m/y', strtotime($rep['fecha_estimada_entrega'])) : '—' ?>
                                        </td> -->
                                        <td class="fw-bold text-success">
                                            $<?= number_format($rep['precio_total'], 2) ?>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('tecnico/dispositivos/detalle/' . $rep['id']) ?>"
                                                class="btn btn-sm btn-primary">
                                                Ver Detalle
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