<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h4 class="page-title">Clientes</h4>
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
            <a href="#">Clientes</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title">Listado de Clientes</h4>
                    <button class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal"
                        data-bs-target="#modalCrearCliente">
                        <i class="fa fa-plus"></i>
                        Nuevo Cliente
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabla-clientes" class="display table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Cédula/RUC</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $cli): ?>
                                <tr>
                                    <td><?= esc($cli['cedula']) ?></td>
                                    <td><?= esc($cli['nombres']) ?></td>
                                    <td><?= esc($cli['apellidos']) ?></td>
                                    <td><?= esc($cli['telefono']) ?></td>
                                    <td><?= esc($cli['email']) ?></td>
                                    <td>
                                        <div class="form-button-action">
                                            <!-- Botón Ver Perfil -->
                                            <a href="<?= base_url('tecnico/clientes/ver/' . $cli['id']) ?>"
                                                class="btn btn-link btn-info btn-lg" data-bs-toggle="tooltip"
                                                title="Ver Perfil Completo">
                                                <i class="fa fa-eye"></i>
                                            </a>

                                            <!-- Botón Editar -->
                                            <button type="button" data-bs-toggle="modal" data-bs-target="#modalEditarCliente"
                                                title="" class="btn btn-link btn-primary btn-lg"
                                                data-original-title="Editar" onclick="llenarModalEditar(<?= htmlspecialchars(json_encode($cli)) ?>)">
                                                <i class="fa fa-edit"></i>
                                            </button>

                                            <!-- Botón Eliminar -->
                                            <button type="button" data-bs-toggle="modal" data-bs-target="#modalEliminarCliente"
                                                title="" class="btn btn-link btn-danger" data-original-title="Eliminar"
                                                onclick="llenarModalEliminar(<?= $cli['id'] ?>)">
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

<!-- Modal Crear Cliente -->
<div class="modal fade" id="modalCrearCliente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-dark">Nuevo Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('tecnico/clientes/crear') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-sm-12">
                            <div class="form-group form-group-default">
                                <label>Cédula/RUC <span class="text-danger">*</span></label>
                                <input name="cedula" type="text" class="form-control" required placeholder="Cédula" value="<?= old('cedula') ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group form-group-default">
                                <label>Nombres <span class="text-danger">*</span></label>
                                <input name="nombres" type="text" class="form-control" required placeholder="Nombres" value="<?= old('nombres') ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group form-group-default">
                                <label>Apellidos <span class="text-danger">*</span></label>
                                <input name="apellidos" type="text" class="form-control" required placeholder="Apellidos" value="<?= old('apellidos') ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group form-group-default">
                                <label>Teléfono <span class="text-danger">*</span></label>
                                <input name="telefono" type="text" class="form-control" required placeholder="098..." value="<?= old('telefono') ?>">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group form-group-default">
                                <label>Teléfono Secundario</label>
                                <input name="telefono_secundario" type="text" class="form-control" placeholder="Opcional" value="<?= old('telefono_secundario') ?>">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group form-group-default">
                                <label>Correo Electrónico</label>
                                <input name="email" type="email" class="form-control" placeholder="cliente@correo.com" value="<?= old('email') ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Cliente -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-dark">Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('tecnico/clientes/editar') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-sm-12">
                            <div class="form-group form-group-default">
                                <label>Cédula/RUC <span class="text-danger">*</span></label>
                                <input name="cedula" id="edit_cedula" type="text" class="form-control" required placeholder="Cédula">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group form-group-default">
                                <label>Nombres <span class="text-danger">*</span></label>
                                <input name="nombres" id="edit_nombres" type="text" class="form-control" required placeholder="Nombres">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group form-group-default">
                                <label>Apellidos <span class="text-danger">*</span></label>
                                <input name="apellidos" id="edit_apellidos" type="text" class="form-control" required placeholder="Apellidos">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group form-group-default">
                                <label>Teléfono <span class="text-danger">*</span></label>
                                <input name="telefono" id="edit_telefono" type="text" class="form-control" required placeholder="098...">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group form-group-default">
                                <label>Teléfono Secundario</label>
                                <input name="telefono_secundario" id="edit_telefono_secundario" type="text" class="form-control" placeholder="Opcional">
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group form-group-default">
                                <label>Correo Electrónico</label>
                                <input name="email" id="edit_email" type="email" class="form-control" placeholder="cliente@correo.com">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar Cliente -->
<div class="modal fade" id="modalEliminarCliente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title fw-bold">Eliminar Cliente</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('tecnico/clientes/eliminar') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-body text-center">
                    <i class="fas fa-exclamation-triangle text-danger mb-3" style="font-size: 3rem;"></i>
                    <p class="mb-0">¿Estás seguro de que deseas eliminar este cliente?</p>
                    <small class="text-muted">Si tiene órdenes asociadas, no se podrá eliminar.</small>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Sí, Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#tabla-clientes').DataTable({
            "pageLength": 10,
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json"
            }
        });
        
        <?php if (session()->has('last_action') && session('last_action') == 'crear' && session()->has('flashValidation')): ?>
            $('#modalCrearCliente').modal('show');
        <?php endif; ?>
        
        <?php if (session()->has('last_action') && session('last_action') == 'editar' && session()->has('flashValidation')): ?>
            <?php $lastData = session('last_data'); ?>
            if (<?= json_encode($lastData) ?>) {
                llenarModalEditar(<?= json_encode($lastData) ?>);
            }
            $('#modalEditarCliente').modal('show');
        <?php endif; ?>
    });

    function llenarModalEditar(cliente) {
        document.getElementById('edit_id').value = cliente.id;
        document.getElementById('edit_cedula').value = cliente.cedula;
        document.getElementById('edit_nombres').value = cliente.nombres;
        document.getElementById('edit_apellidos').value = cliente.apellidos;
        document.getElementById('edit_telefono').value = cliente.telefono;
        document.getElementById('edit_telefono_secundario').value = cliente.telefono_secundario || '';
        document.getElementById('edit_email').value = cliente.email || '';
    }

    function llenarModalEliminar(id) {
        document.getElementById('delete_id').value = id;
    }
</script>
<?= $this->endSection() ?>