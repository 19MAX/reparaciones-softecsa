<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
Detalles del Cliente
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h4 class="page-title">Perfil de Cliente</h4>
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
            <a href="<?= base_url('recepcionista/clientes') ?>">Clientes</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Perfil</a>
        </li>
    </ul>
</div>

<div class="row">
    <!-- Información del Cliente -->
    <div class="col-md-4">
        <div class="card card-profile">
            <div class="card-header" style="background-image: url('<?= base_url('assets/img/blogpost.jpg') ?>')">
                <div class="profile-picture">
                    <div class="avatar avatar-xl">
                        <span class="avatar-title rounded-circle border border-white bg-primary">
                            <?= strtoupper(substr($cliente['nombres'], 0, 1) . substr($cliente['apellidos'], 0, 1)) ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="user-profile text-center">
                    <div class="name"><?= esc($cliente['nombres'] . ' ' . $cliente['apellidos']) ?></div>
                    <div class="job">Cliente</div>
                    <div class="desc">Cédula/RUC: <?= esc($cliente['cedula']) ?></div>

                    <div class="view-profile mt-4">
                        <ul class="list-group list-group-unbordered text-start">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-phone text-muted me-2"></i> Teléfono:</span>
                                <b><?= esc($cliente['telefono']) ?></b>
                            </li>
                            <?php if(!empty($cliente['telefono_secundario'])): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-phone-alt text-muted me-2"></i> Teléfono Sec.:</span>
                                <b><?= esc($cliente['telefono_secundario']) ?></b>
                            </li>
                            <?php endif; ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-envelope text-muted me-2"></i> Email:</span>
                                <b><?= esc($cliente['email'] ?? 'No especificado') ?></b>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-calendar-alt text-muted me-2"></i> Registrado:</span>
                                <b><?= date('d/m/Y', strtotime($cliente['created_at'])) ?></b>
                            </li>
                        </ul>
                    </div>

                    <button type="button" class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalEditarCliente" onclick="llenarModalEditar(<?= htmlspecialchars(json_encode($cliente)) ?>)">
                        <i class="fa fa-edit me-1"></i> Editar Cliente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Órdenes -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title"><i class="fas fa-history me-2"></i> Historial de Órdenes</h4>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tabla-ordenes-cliente">
                        <thead>
                            <tr>
                                <th>N° Orden</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Ver</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($ordenes)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Este cliente no tiene órdenes registradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($ordenes as $orden): ?>
                                    <tr>
                                        <td class="fw-bold">
                                            <a href="<?= base_url('recepcionista/ordenes/ver/' . $orden['id']) ?>">
                                                <?= esc($orden['numero_orden']) ?>
                                            </a>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($orden['created_at'])) ?></td>
                                        <td><?= estadoPill($orden['estado']) ?></td>
                                        <td>
                                            <a href="<?= base_url('recepcionista/ordenes/ver/' . $orden['id']) ?>" class="btn btn-icon btn-round btn-primary btn-sm">
                                                <i class="fa fa-eye"></i>
                                            </a>
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

<!-- Modal Editar Cliente -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-dark">Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditarCliente" method="POST">
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#tabla-ordenes-cliente').DataTable({
            "pageLength": 5,
            "order": [[1, "desc"]],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json"
            },
            "bLengthChange": false
        });

        // Submit form via AJAX
        $('#formEditarCliente').submit(function(e) {
            e.preventDefault();

            const formData = {
                id: $('#edit_id').val(),
                cedula: $('#edit_cedula').val(),
                nombres: $('#edit_nombres').val(),
                apellidos: $('#edit_apellidos').val(),
                telefono: $('#edit_telefono').val(),
                telefono_secundario: $('#edit_telefono_secundario').val(),
                email: $('#edit_email').val(),
            };

            fetch('<?= base_url('global/actualizar-cliente') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    $('#modalEditarCliente').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: 'Cliente actualizado correctamente.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else if (data.status === 'error') {
                    if (data.errors) {
                        let errorMsg = '';
                        for (let field in data.errors) {
                            errorMsg += data.errors[field] + '\n';
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de validación',
                            text: errorMsg
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.errors || 'Ocurrió un error al actualizar.'
                        });
                    }
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error de conexión.'
                });
            });
        });
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
</script>
<?= $this->endSection() ?>