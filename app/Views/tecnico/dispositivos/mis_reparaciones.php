<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>Mis Reparaciones<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-inner">
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
                        <table id="tabla-mis-reparaciones" class="table table-sm table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Cliente / Equipo</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                    <th width="140">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reparaciones as $rep): ?>
                                    <tr>

                                        <!-- Orden -->
                                        <td>
                                            <a href="<?= base_url('consulta/orden/' . $rep['numero_orden']) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-decoration-none">

                                                <div class="fw-bold text-primary">
                                                    #<?= esc($rep['numero_orden']) ?>
                                                </div>

                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($rep['fecha_ingreso'])) ?>
                                                </small>
                                            </a>
                                        </td>

                                        <!-- Cliente + Equipo -->
                                        <td>
                                            <div class="fw-semibold">
                                                <?= esc($rep['cliente_nombre']) ?>
                                                <?= esc($rep['cliente_apellido']) ?>
                                            </div>

                                            <small class="text-muted d-block">
                                                <?= esc($rep['tipo_dispositivo']) ?>
                                            </small>

                                            <small class="d-block">
                                                <?= esc(trim(
                                                    ($rep['marca'] ?? '') . ' ' .
                                                        ($rep['modelo'] ?? '')
                                                )) ?>
                                            </small>

                                            <?php if (!empty($rep['serie_imei'])): ?>
                                                <small class="text-muted d-block">
                                                    SN: <?= esc($rep['serie_imei']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Estado -->
                                        <td>
                                            <?= estadoPill($rep['estado']) ?>
                                        </td>

                                        <!-- Total -->
                                        <td>
                                            <span class="fw-bold text-success">
                                                $<?= number_format($rep['precio_total'], 2) ?>
                                            </span>
                                        </td>

                                        <!-- Acciones -->
                                        <td>
                                            <div class="btn-group">

                                                <!-- Ver detalle -->
                                                <a href="<?= base_url('tecnico/dispositivos/detalle/' . $rep['id']) ?>"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="tooltip"
                                                    title="Ver detalle">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <!-- Imprimir -->
                                                <div class="dropdown">
                                                    <button
                                                        class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                        type="button"
                                                        data-bs-toggle="dropdown"
                                                        aria-expanded="false">
                                                        <i class="fas fa-print"></i>
                                                    </button>

                                                    <ul class="dropdown-menu dropdown-menu-end">

                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="<?= base_url('tecnico/ordenes/imprimir/' . $rep['id'] . '/ticket') ?>"
                                                                target="_blank">
                                                                <i class="fas fa-file-alt me-2"></i>
                                                                Ticket
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="<?= base_url('tecnico/ordenes/imprimir/' . $rep['id'] . '/carta') ?>"
                                                                target="_blank">
                                                                <i class="fas fa-file me-2"></i>
                                                                Carta
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="<?= base_url('tecnico/ordenes/imprimir/' . $rep['id'] . '/completo') ?>"
                                                                target="_blank">
                                                                <i class="fas fa-file-invoice me-2"></i>
                                                                Completo
                                                            </a>
                                                        </li>

                                                    </ul>
                                                </div>

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
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#tabla-mis-reparaciones').DataTable({
            "pageLength": 10,
            "order": [
                [1, "desc"]
            ], // Ordenar por fecha de ingreso por defecto
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json"
            },
            "scrollX": true
        });
    });
</script>
<?= $this->endSection() ?>