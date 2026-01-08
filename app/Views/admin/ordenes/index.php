<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?? 'Órdenes de Trabajo' ?>
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
                    <h4 class="card-title"><i class="fas fa-clipboard-list me-2"></i>Listado de Órdenes</h4>

                    <a href="<?= base_url('admin/ordenes/crear') ?>" class="btn btn-success btn-round ms-auto">
                        <i class="fa fa-plus me-2"></i> Nueva Orden
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="ordenes-datatables" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th># Orden</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Dispositivos (Resumen)</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th style="width: 10%">Acciones</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th># Orden</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Dispositivos</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?php if (!empty($ordenes) && is_array($ordenes)): ?>
                                <?php foreach ($ordenes as $orden): ?>
                                    <tr>
                                        <td class="fw-bold text-primary">
                                            <?= esc($orden['codigo_orden']) ?>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($orden['created_at'])) ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('H:i', strtotime($orden['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-bold">
                                                <?= esc($orden['nombres']) ?>
                                                <?= esc($orden['apellidos']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 200px;"
                                                title="<?= esc($orden['equipos_resumen']) ?>">
                                                <?= esc($orden['equipos_resumen']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($orden['recargo'] > 0): ?>
                                                <span class="badge badge-warning">
                                                    <?= esc($orden['prioridad']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-black text-dark bg-light border">
                                                    <?= esc($orden['prioridad']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $estadoClass = 'bg-secondary';
                                            $estadoLabel = $orden['estado'];

                                            if ($orden['estado'] == 'abierta')
                                                $estadoClass = 'bg-success';
                                            if ($orden['estado'] == 'en_reparacion')
                                                $estadoClass = 'bg-primary';
                                            if ($orden['estado'] == 'finalizada')
                                                $estadoClass = 'bg-dark';
                                            ?>
                                            <span class="badge <?= $estadoClass ?>">
                                                <?= strtoupper(str_replace('_', ' ', $orden['estado'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="form-button-action">
                                                <a href="<?= base_url('admin/ordenes/imprimir/' . $orden['id']) ?>"
                                                    target="_blank" class="btn btn-link btn-secondary" data-bs-toggle="tooltip"
                                                    title="Imprimir Ticket">
                                                    <i class="fas fa-print"></i>
                                                </a>

                                                <!-- Enlace para ver el seguimiento público: -->

                                                <a href="<?= base_url('consulta/orden/' . $orden['codigo_orden']) ?>"
                                                    target="_blank" class="btn btn-link btn-info" data-bs-toggle="tooltip"
                                                    title="Ver Seguimiento Público">
                                                    <i class="fas fa-truck-moving"></i>
                                                </a>


                                                <a href="<?= base_url('admin/ordenes/editar/' . $orden['id']) ?>"
                                                    class="btn btn-link btn-primary" data-bs-toggle="tooltip"
                                                    title="Gestionar Orden">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <form action="<?= base_url('admin/ordenes/eliminar') ?>" method="post"
                                                    class="d-inline delete-form">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id_orden" value="<?= $orden['id'] ?>">
                                                    <button type="button" class="btn btn-link btn-danger btn-delete-orden"
                                                        data-bs-toggle="tooltip" title="Eliminar">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        No hay órdenes de trabajo registradas.
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
        // Inicializar DataTables con ordenamiento por la primera columna (ID) descendente
        $('#ordenes-datatables').DataTable({
            "order": [[0, "desc"]], // Ordenar por #Orden descendente
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
            },
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
    });
</script>
<?= $this->endSection() ?>