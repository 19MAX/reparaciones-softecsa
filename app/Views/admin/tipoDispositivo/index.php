<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?? 'Tipos de dispositivo' ?>
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
            <a href="#">Tipos de dispositivos</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title"><i class="fas fa-laptop me-2"></i>Tipos de dispositivos</h4>


                    <button type="button" class="btn btn-success btn-round ms-auto" data-bs-toggle="modal"
                        title="Crear nuevo dispositivo" data-bs-target="#addDispositivoModal">

                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="ordenes-datatables" class="table table-bordered ">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th style="width: 10%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php if (!isset($tiposDispositivos)): ?>
                            <?php endif; ?>
                            <?php foreach ($tiposDispositivos as $dispositivo): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold">
                                            <?= esc($dispositivo['nombre']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $dispositivo['activo'] ? 'success' : 'danger' ?>">
                                            <?= esc($dispositivo['activo'] ? 'Activo' : 'Inactivo') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="form-button-action">
                                            <button data-id="<?= $dispositivo['id'] ?>"
                                                data-nombre="<?= $dispositivo['nombre'] ?>" type="button" title="Actualizar"
                                                class="btn btn-link btn-primary btn-lg btn-edit" data-bs-toggle="modal"
                                                title="Editar" data-bs-target="#updateDispositivoModal">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button data-id="<?= $dispositivo['id'] ?>"
                                                data-nombre="<?= $dispositivo['nombre'] ?>" type="button"
                                                data-bs-toggle="modal" title="Eliminar" data-bs-target="#deleteUserModal"
                                                class="btn btn-link btn-danger btn-delete">
                                                <i class="fa fa-times"></i>
                                            </button>
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

<!-- Crear nuevo Tipo de dispositivo -->
<div class="modal fade" id="addDispositivoModal" tabindex="-1" role="dialog" aria-labelledby="addDispositivoModalTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="exampleModalLabel">
                    Crear Nuevo Dispositivo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body">
                <form id="add-form" action="<?= base_url('admin/tipos-dispositivos/crear') ?>" method="post">
                    <div class="form-group">
                        <div class="row">
                            <div class="col">
                                <div class="form-floating form-floating-custom mb-3">
                                    <input type="text" class="form-control" id="add-nombre" name="nombre"
                                        placeholder="Ingresa el nombre del dispositivo ..." required />
                                    <label for="add-cedula">Nombre</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button form="add-form" type="submit" class="btn btn-primary">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- Editar -->
<div class="modal fade" id="updateDispositivoModal" tabindex="-1" role="dialog"
    aria-labelledby="updateDispositivoModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="exampleModalLabel">
                    Actualizar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-form" action="<?= base_url('admin/tipos-dispositivos/editar') ?>" method="post">
                    <div class="form-group">
                        <div class="row">
                            <div class="col">
                                <div class="form-floating form-floating-custom mb-3">
                                    <input type="text" class="form-control" id="edit-nombre" name="nombre"
                                        placeholder="Ingresa el nombre..." />
                                    <label for="edit-nombre">Nombre</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="id" id="edit-id" value="" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button form="edit-form" type="submit" class="btn btn-primary">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- Eliminar -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModal"
    aria-hidden="true">
    <div class="modal-dialog modal-danger modal-dialog-centered modal-" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="modal-title-notification">
                    Verificación de eliminación
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body">
                <div class="py-3 text-center">
                    <i class="fas fa-bell text-danger" style="font-size: 24px"></i>
                    <h4 class="text-danger mt-4">Eliminar dispositivo!</h4>
                    <p>
                        Estas seguro que deseas el dispositivo <strong class="device-name text-danger"
                            id="device-name">Nombre
                            del dispositivo</strong>?
                    </p>
                </div>
                <form id="delete-form" action="<?= base_url('admin/tipos-dispositivos/eliminar') ?>" method="post">
                    <input type="hidden" name="id" id="delete-id" value="" />
                </form>
            </div>
            <div class="modal-footer">
                <button form="delete-form" type="submit" class="btn btn-secondary">Confirmar</button>
                <button type="button" class="btn btn-link text-primary text-decoration-none" data-bs-dismiss="modal">
                    Cancelar
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
                    buttons: ['pageLength']
                }
            }
        });

        // Manejo de eliminación con SweetAlert2
        $('body').on('click', '.btn-edit', function () {
            let id = $(this).data('id');
            let nombre = $(this).data('nombre');
            $('#edit-id').val(id);
            $('#edit-nombre').val(nombre);
        });

        $('body').on('click', '.btn-delete', function () {
            let id = $(this).data('id');
            let nombre = $(this).data('nombre');

            $('#delete-id').val(id);
            $('#device-name').text(nombre);
        });
    });
</script>
<?= $this->endSection() ?>