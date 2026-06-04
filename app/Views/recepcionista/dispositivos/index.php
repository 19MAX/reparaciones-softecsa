<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h4 class="page-title">Dispositivos</h4>
    <ul class="breadcrumbs">
        <li class="nav-home">
            <a href="<?= base_url('recepcionista/dashboard') ?>">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Dispositivos</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title">Listado de Dispositivos</h4>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabla-dispositivos" class="table table-sm table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Dispositivo</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th width="130">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dispositivos as $d): ?>
                                <tr>

                                    <!-- Dispositivo -->
                                    <td>
                                        <div class="fw-semibold">
                                            <?= esc($d['tipo'] ?? 'N/A') ?>
                                        </div>

                                        <small class="text-muted d-block">
                                            <?= esc(trim(
                                                ($d['marca'] ?? '') . ' ' .
                                                    ($d['modelo'] ?? '') . ' ' .
                                                    ($d['modelo_texto'] ?? '')
                                            )) ?>
                                        </small>

                                        <small class="badge bg-secondary">
                                            Orden #<?= esc($d['numero_orden']) ?>
                                        </small>
                                    </td>

                                    <!-- Cliente -->
                                    <td>
                                        <?= esc($d['nombres'] . ' ' . $d['apellidos']) ?>
                                    </td>

                                    <!-- Estado -->
                                    <td>
                                        <?= estadoPill($d['estado']) ?>
                                    </td>

                                    <!-- Acciones -->
                                    <td>
                                        <div class="btn-group">

                                            <a href="<?= base_url('recepcionista/dispositivos/detalle/' . $d['id']) ?>"
                                                class="btn btn-sm btn-outline-info"
                                                data-bs-toggle="tooltip"
                                                title="Ver detalle">
                                                <i class="fa fa-eye"></i>
                                            </a>

                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                    type="button"
                                                    data-bs-toggle="dropdown">
                                                    <i class="fas fa-print"></i>
                                                </button>

                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="<?= base_url('recepcionista/dispositivos/imprimir/' . $d['id'] . '/ticket') ?>"
                                                            target="_blank">
                                                            <i class="fas fa-file-alt me-2"></i>Ticket
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="<?= base_url('recepcionista/dispositivos/imprimir/' . $d['id'] . '/carta') ?>"
                                                            target="_blank">
                                                            <i class="fas fa-file me-2"></i>Carta
                                                        </a>
                                                    </li>

                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="<?= base_url('recepcionista/dispositivos/imprimir/' . $d['id'] . '/completo') ?>"
                                                            target="_blank">
                                                            <i class="fas fa-file-invoice me-2"></i>Completo
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#tabla-dispositivos').DataTable({
            "scrollX": true,
            "pageLength": 10,
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json"
            },
            "order": [
                [1, '']
            ],
            "layout": {
                topStart: {
                    buttons: ['pageLength', 'copy', 'excel', 'pdf']
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>