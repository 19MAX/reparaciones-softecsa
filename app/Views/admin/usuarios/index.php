<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?? 'Sin titulo' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="#">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Tables</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Datatables</a>
        </li>
    </ul>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">

                    <h4 class="card-title">Basic</h4>

                    <button type="button" class="btn btn-success btn-round ms-auto" data-bs-toggle="modal"
                        title="Crear nuevo usuario" data-bs-target="#addUserModal">

                        <i class="fa fa-user-plus"></i>
                    </button>
                </div>

            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="basic-datatables" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Cédula</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <!-- <tfoot>
                            <tr>
                                <th>Cédula</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </tfoot> -->
                        <tbody>
                            <?php if (!empty($usuarios) && is_array($usuarios)): ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <tr>
                                        <td><?= esc($usuario['cedula']) ?></td>
                                        <td><?= esc($usuario['nombres']) ?></td>
                                        <td><?= esc($usuario['apellidos']) ?></td>
                                        <td><?= esc($usuario['role']) ?></td>
                                        <td><?= esc($usuario['estado']) ?></td>
                                        <td>

                                            <div class="form-button-action">
                                                <button data-user-id="<?= $usuario['id'] ?>"
                                                    data-cedula-user="<?= $usuario['cedula'] ?>"
                                                    data-nombres-user="<?= $usuario['nombres'] ?>"
                                                    data-apellidos-user="<?= $usuario['apellidos'] ?>"
                                                    data-role-user="<?= $usuario['role'] ?>"
                                                    data-estado-user="<?= $usuario['estado'] ?>" type="button"
                                                    title="Actualizar usuario" class="btn btn-link btn-primary btn-lg btn-edit"
                                                    data-bs-toggle="modal" title="Editar usuario"
                                                    data-bs-target="#editUserModal">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button data-user-id="<?= $usuario['id'] ?>"
                                                    data-nombres-user="<?= $usuario['nombres'] ?>" type="button"
                                                    data-bs-toggle="modal" title="Eliminar usuario"
                                                    data-bs-target="#deleteUserModal"
                                                    class="btn btn-link btn-danger btn-delete">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- Crear nuevo Usuario -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="exampleModalLabel">
                    Crear Nuevo Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body">
                <form id="add-form" action="<?= base_url('admin/usuarios/crear') ?>" method="post">
                    <div class="form-group">
                        <div class="row">
                            <div class="col">
                                <div class="form-floating form-floating-custom mb-3">
                                    <input type="number" class="form-control" id="add-cedula" name="cedula"
                                        placeholder="Ingresa el numero de cédula ..." required />
                                    <label for="add-cedula">Cédula</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating form-floating-custom mb-3">
                                    <input type="text" class="form-control" id="add-nombres" name="nombres"
                                        placeholder="Ingresa los nombres..." required />
                                    <label for="add-nombres">Nombres</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-floating form-floating-custom mb-3">
                                    <input type="text" class="form-control" id="add-apellidos" name="apellidos"
                                        placeholder="Ingresa los apellidos..." required />
                                    <label for="add-apellidos">Apellidos</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating form-floating-custom mb-3">
                                    <select class="form-select" id="add-role" name="role" required>
                                        <option selected disabled>Seleccionar el rol</option>
                                        <option value="admin">Admin</option>
                                        <option value="recepcionista">Recepcionista</option>
                                        <option value="tecnico">Técnico</option>
                                    </select>
                                    <label for="add-role">Selecciona el rol</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-floating form-floating-custom mb-3">
                                    <input type="text" class="form-control" id="add-password" name="password"
                                        placeholder="Ingresa la contraseña..." required />
                                    <label for="add-password">Ingresa la contraseña</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating form-floating-custom mb-3">
                                    <input type="password" class="form-control" id="add-repeat-password"
                                        name="repeatPassword" placeholder="Repite la contraseña..." required />
                                    <label for="add-repeat-password">Repite la contraseña</label>
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

<!-- Editar Usuario -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="exampleModalLabel">
                    Actualizar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"></span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-form" action="<?= base_url('admin/usuarios/editar') ?>" method="post">
                    <div class="form-group">
                        <div class="row">
                            <div class="col">
                                <div class="form-floating form-floating-custom mb-3">
                                    <input type="number" class="form-control" id="edit-cedula" name="cedula"
                                        placeholder="Ingresa el numero de cédula ..." />
                                    <label for="edit-cedula">Cédula</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating form-floating-custom mb-3">
                                    <input type="text" class="form-control" id="edit-nombres" name="nombres"
                                        placeholder="Ingresa los nombres..." />
                                    <label for="edit-nombres">Nombres</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-floating form-floating-custom mb-3">
                                    <input type="text" class="form-control" id="edit-apellidos" name="apellidos"
                                        placeholder="Ingresa los apellidos..." />
                                    <label for="edit-apellidos">Apellidos</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating form-floating-custom mb-3">
                                    <select class="form-select" id="edit-role" name="role" required="">
                                        <option value="admin">Admin</option>
                                        <option value="recepcionista">Recepcionista</option>
                                        <option value="tecnico">Técnico</option>
                                    </select>
                                    <label for="edit-role">Selecciona el rol</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-floating form-floating-custom mb-3">
                                    <input type="text" class="form-control" id="edit-password" name="password"
                                        placeholder="Ingresa la nueva contraseña..." />
                                    <label for="edit-password">Ingresa la nueva contraseña</label>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-floating form-floating-custom mb-3">
                                    <input type="password" class="form-control" id="edit-repeat-password"
                                        name="repeatPassword" placeholder="Repite la contraseña..." />
                                    <label for="edit-repeat-password">Repite la contraseña</label>
                                </div>
                            </div>

                            <input type="hidden" name="id_usuario" id="edit-id" value="" />

                        </div>
                    </div>
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

<!-- Eliminar Usuario -->
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
                    <h4 class="text-danger mt-4">Eliminar usuario!</h4>
                    <p>
                        Estas seguro que deseas al usuario <strong class="user-name text-danger" id="user-name">Nombre
                            del usuario</strong>?
                    </p>
                </div>
                <form id="delete-form" action="<?= base_url('admin/usuarios/eliminar') ?>" method="post">
                    <input type="hidden" name="id_usuario" id="delete-id" value="" />
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
<script>
    $(document).ready(function () {
        // Inicializar DataTables
        $('#basic-datatables').DataTable();

        $('body').on('click', '.btn-edit', function () {
            let id = $(this).data('user-id');
            let cedula = $(this).data('cedula-user');
            let nombres = $(this).data('nombres-user');
            let apellidos = $(this).data('apellidos-user');
            let role = $(this).data('role-user');

            $('#edit-id').val(id);
            $('#edit-cedula').val(cedula);
            $('#edit-nombres').val(nombres);
            $('#edit-apellidos').val(apellidos);
            $('#edit-role').val(role).change();

            $('#edit-password').val('');
            $('#edit-repeat-password').val('');
        });

        $('body').on('click', '.btn-delete', function () {
            let id = $(this).data('user-id');
            let nombre = $(this).data('nombres-user');

            $('#delete-id').val(id);
            $('#user-name').text(nombre);
        });
    });
</script>
<?= $this->endSection() ?>