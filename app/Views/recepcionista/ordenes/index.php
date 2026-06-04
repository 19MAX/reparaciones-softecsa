<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?? 'Órdenes de Trabajo' ?>
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
            <a href="#">Taller</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Órdenes</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title"><i class="fas fa-toolbox me-2"></i>Listado de Órdenes</h4>

                    <a href="<?= base_url('recepcionista/ordenes/crear') ?>" class="btn btn-success btn-round ms-auto">
                        <i class="fa fa-plus me-2"></i> Nueva Orden
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="ordenes-datatables" class="display table table-hover table-striped  align-middle">
                        <thead>
                            <tr>
                                <th># Orden</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Dispositivos</th>
                                <th>Estado</th>
                                <th style="width: 10%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php if (!empty($ordenes)): ?>
                                <?php foreach ($ordenes as $orden): ?>
                                    <tr>
                                        <td class="fw-bold text-primary">
                                            <a href="<?= base_url('consulta/orden/' . $orden['numero_orden']) ?>"
                                                data-bs-toggle="tooltip" title="Ver Seguimiento Público">
                                                <?= esc($orden['numero_orden']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?= formatear_fecha($orden['created_at'], 'solo_fecha') ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('H:i', strtotime($orden['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-bold">
                                                <?= esc($orden['nombres']) ?>         <?= esc($orden['apellidos']) ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-info btn-ver-dispositivos"
                                                data-id="<?= $orden['id'] ?>" data-bs-toggle="modal"
                                                data-bs-target="#dispositivosModal">
                                                <?= $orden['total_dispositivos'] ?>
                                                <i class="fas fa-eye ms-1"></i>
                                            </button>
                                        </td>
                                        <td>
                                            <?= estadoPill($orden['estado']) ?>
                                        </td>
                                        <td>
                                            <div class="form-button-action">

                                                <div class="dropdown d-inline">
                                                    <a class="btn btn-link btn-secondary dropdown-toggle" href="#" role="button"
                                                        data-bs-toggle="dropdown" aria-expanded="false" title="Imprimir">
                                                        <i class="fas fa-print"></i>
                                                    </a>

                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="<?= base_url('recepcionista/ordenes/imprimir/' . $orden['id'] . '/carta') ?>"
                                                                target="_blank">
                                                                Carta
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="<?= base_url('recepcionista/ordenes/imprimir/' . $orden['id'] . '/ticket') ?>"
                                                                target="_blank">
                                                                Ticket
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="<?= base_url('recepcionista/ordenes/imprimir/' . $orden['id']) ?>"
                                                                target="_blank">
                                                                Completo
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
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
<div class="modal fade" id="dispositivosModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-info">
                <h5 class="modal-title text-white fw-bold">
                    Dispositivos de la Orden
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Dispositivo</th>
                                <th>Estado</th>
                                <th>Fecha Entrega</th>
                                <th>Precio</th>
                                <th style="width:120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-dispositivos-body">
                            <tr>
                                <td colspan="6" class="text-center">
                                    Cargando dispositivos...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        // Inicializar DataTables con ordenamiento por la primera columna (ID) descendente
        $('#ordenes-datatables').DataTable({
            scrollX: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json',
            },
            order: [[1, 'desc']],
            layout: {
                topStart: {
                    buttons: ['pageLength', 'copy', 'excel', 'pdf', 'colvis']
                }
            }
        });

        // Manejo de eliminación con SweetAlert2
        $('.btn-delete-orden').click(function (e) {
            e.preventDefault();
            let form = $(this).closest('form');

            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esto. Se borrarán los dispositivos asociados.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            })
        });

        const tablaBody = document.getElementById('tabla-dispositivos-body');

        document.querySelectorAll('.btn-ver-dispositivos').forEach(btn => {

            btn.addEventListener('click', function () {

                const ordenId = this.dataset.id;

                tablaBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center">
                        Cargando dispositivos...
                    </td>
                </tr>
            `;

                fetch(`<?= base_url('recepcionista/ordenes/dispositivo') ?>/${ordenId}`)
                    .then(response => response.json())
                    .then(data => {

                        if (!data.success) {
                            tablaBody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center text-danger">
                                    ${data.message}
                                </td>
                            </tr>
                        `;
                            return;
                        }

                        if (data.dispositivos.length === 0) {
                            tablaBody.innerHTML = `
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No hay dispositivos registrados.
                                </td>
                            </tr>
                        `;
                            return;
                        }

                        let filas = '';

                        data.dispositivos.forEach((d, index) => {

                            const fechaEntrega = d.fecha_real_entrega
                                ? d.fecha_real_entrega
                                : (d.fecha_estimada_entrega ?? '-');

                            filas += `
                            <tr>
                                <td>
                                    <strong>${d.tipo_dispositivo}</strong><br>
                                    <small class="text-muted">
                                        ${d.marca} ${d.modelo ?? ''}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        ${d.estado}
                                    </span>
                                </td>
                                <td>
                                    ${fechaEntrega ?? '-'}
                                </td>
                                <td>
                                    $ ${parseFloat(d.precio_total).toFixed(2)}
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('recepcionista/dispositivos/detalle') ?>/${d.id}" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        `;
                        });

                        tablaBody.innerHTML = filas;

                    })
                    .catch(error => {
                        tablaBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center text-danger">
                                Error al cargar los dispositivos.
                            </td>
                        </tr>
                    `;
                        console.error(error);
                    });

            });

        });
    });
</script>
<?= $this->endSection() ?>