<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">

    <ul class="breadcrumbs ps-1 ms-0">
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
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Entregar</a>
        </li>
    </ul>

</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title">Dispositivos Listos para Entrega</h4>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($dispositivos)): ?>
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h5 class="text-muted">No hay dispositivos pendientes de entrega</h5>
                        <p class="text-muted">Todos los dispositivos han sido entregados.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table id="tabla-entregar" class="table table-sm table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th>Dispositivo</th>
                                    <th>Fecha Entrega</th>
                                    <th width="120">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dispositivos as $d): ?>
                                    <tr>

                                        <!-- Cliente -->
                                        <td>
                                            <div class="fw-semibold">
                                                <?= esc($d['nombres'] . ' ' . $d['apellidos']) ?>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-phone-alt me-1"></i>
                                                <?= esc($d['telefono'] ?? 'N/A') ?>
                                            </small>
                                        </td>

                                        <!-- Dispositivo -->
                                        <td>
                                            <div class="fw-semibold">
                                                <?= esc($d['tipo'] ?? 'N/A') ?>
                                            </div>
                                            <small class="text-muted">
                                                <?= esc(trim(
                                                    ($d['marca'] ?? '') . ' ' .
                                                        ($d['modelo'] ?? '') . ' ' .
                                                        ($d['modelo_texto'] ?? '')
                                                )) ?>
                                            </small>
                                        </td>

                                        <!-- Fecha -->
                                        <td>
                                            <?=
                                            !empty($d['fecha_estimada_entrega'])
                                                ? formatear_fecha($d['fecha_estimada_entrega'], 'solo_fecha')
                                                : '—'
                                            ?>
                                        </td>

                                        <!-- Acciones -->
                                        <td>
                                            <div class="btn-group">
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-info"
                                                    onclick="verDetalle(<?= $d['id'] ?>)"
                                                    data-bs-toggle="tooltip"
                                                    title="Ver detalle">
                                                    <i class="fa fa-eye"></i>
                                                </button>

                                                <button type="button"
                                                    class="btn btn-sm btn-outline-success"
                                                    onclick="confirmarEntrega(<?= $d['id'] ?>)"
                                                    data-bs-toggle="tooltip"
                                                    title="Entregar">
                                                    <i class="fa fa-check"></i>
                                                </button>
                                            </div>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Detalle del Dispositivo -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del Dispositivo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalDetalleContent">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2 text-muted">Cargando...</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        <?php if (!empty($dispositivos)): ?>
            $('#tabla-entregar').DataTable({
                "scrollX": true,
                "pageLength": 10,
                "language": {
                    "url": "https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json"
                },
                "order": [
                    [5, 'asc']
                ],
                "layout": {
                    topStart: {
                        buttons: ['pageLength', 'copy', 'excel', 'pdf', 'colvis']
                    }
                }
            });
        <?php endif; ?>
    });

    function verDetalle(dispositivoId) {
        const modal = new bootstrap.Modal(document.getElementById('modalDetalle'));
        document.getElementById('modalDetalleContent').innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2 text-muted">Cargando...</p>
            </div>
        `;
        modal.show();

        fetch(`<?= base_url('recepcionista/dispositivos/detalle') ?>/${dispositivoId}`)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const content = doc.querySelector('.row.g-3');
                if (content) {
                    document.getElementById('modalDetalleContent').innerHTML = content.outerHTML;
                    attachTabListeners();
                } else {
                    document.getElementById('modalDetalleContent').innerHTML = `
                        <div class="alert alert-danger">No se pudo cargar el detalle del dispositivo.</div>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('modalDetalleContent').innerHTML = `
                    <div class="alert alert-danger">Error al cargar el detalle: ${error.message}</div>
                `;
            });
    }

    function attachTabListeners() {
        const triggerTabList = document.querySelectorAll('#modalDetalleContent [data-bs-toggle="pill"]');
        triggerTabList.forEach(triggerEl => {
            triggerEl.addEventListener('shown.bs.tab', event => {});
        });
    }

    function confirmarEntrega(dispositivoId) {
        Swal.fire({
            title: '¿Está seguro de entregar este dispositivo?',
            text: 'Esta acción marcará el dispositivo como entregado.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d'
        }).then(result => {
            if (result.isConfirmed) {
                fetch('<?= base_url('recepcionista/dispositivos/entregar') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `dispositivo_id=${dispositivoId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: '¡Entregado!',
                                text: 'El dispositivo ha sido marcado como entregado.',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#198754'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'No se pudo entregar el dispositivo.',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error de conexión: ' + error.message,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc3545'
                        });
                    });
            }
        });
    }
</script>
<?= $this->endSection() ?>