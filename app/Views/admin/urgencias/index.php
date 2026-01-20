<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?? 'Urgencias' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('admin/dashboard') ?>">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item">Configuración</li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item">Niveles de Urgencia</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title"><i class="fas fa-stopwatch me-2"></i>Niveles de Urgencia</h4>
                    <button type="button" class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal"
                        title="Crear nueva urgencia" data-bs-target="#addUrgenciaModal">
                        <i class="fa fa-plus"></i> Nueva Urgencia
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="urgencias-datatables" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Nivel / Nombre</th>
                                <th>Descripción</th>
                                <th>Tiempo Espera</th>
                                <th>Recargo ($)</th>
                                <th>Estado</th>
                                <th style="width: 10%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($urgencias)): ?>
                                <?php foreach ($urgencias as $item): ?>
                                    <tr>
                                        <td class="fw-bold">
                                            <?= esc($item['nombre']) ?>
                                        </td>
                                        <td>
                                            <?= esc($item['descripcion']) ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-black text-light">
                                                <i class="far fa-clock me-1"></i> <?= esc($item['tiempo_espera']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            $ <?= number_format($item['recargo'], 2) ?>
                                        </td>
                                        <td>
                                            <?php if ($item['activo']): ?>
                                                <span class="badge badge-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="form-button-action">
                                                <button type="button" class="btn btn-link btn-primary btn-lg btn-edit"
                                                    data-bs-toggle="modal" data-bs-target="#updateUrgenciaModal"
                                                    data-id="<?= $item['id'] ?>" data-nombre="<?= esc($item['nombre']) ?>"
                                                    data-descripcion="<?= esc($item['descripcion']) ?>"
                                                    data-recargo="<?= $item['recargo'] ?>"
                                                    data-tiempo="<?= esc($item['tiempo_espera']) ?>"
                                                    data-activo="<?= $item['activo'] ?>" title="Editar">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-link btn-danger btn-delete"
                                                    data-bs-toggle="modal" data-bs-target="#deleteUrgenciaModal"
                                                    data-id="<?= $item['id'] ?>" data-nombre="<?= esc($item['nombre']) ?>"
                                                    title="Eliminar">
                                                    <i class="fa fa-times"></i>
                                                </button>
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

<div class="modal fade" id="addUrgenciaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title fw-bold text-white">Crear Nivel de Urgencia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-form" action="<?= base_url('admin/urgencias/crear') ?>" method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre del Nivel <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" required
                                placeholder="Ej: Express, Normal, Inmediata">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tiempo de Espera Estimado <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="tiempo_espera" required
                                placeholder="Ej: 24 horas, 2 horas">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Recargo Adicional ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" name="recargo" required
                                    value="0.00">
                            </div>
                            <small class="text-muted">Valor extra que se suma al costo base.</small>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="3"
                                placeholder="Detalles sobre este nivel de servicio..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button form="add-form" type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="updateUrgenciaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark">Editar Nivel de Urgencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-form" action="<?= base_url('admin/urgencias/editar') ?>" method="post">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre del Nivel <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" id="edit-nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tiempo de Espera <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="tiempo_espera" id="edit-tiempo" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Recargo ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" name="recargo" id="edit-recargo"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3 pt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="edit-activo" name="activo"
                                    value="1">
                                <label class="form-check-label fw-bold" for="edit-activo">¿Nivel Activo?</label>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" id="edit-descripcion" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button form="edit-form" type="submit" class="btn btn-warning">Actualizar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteUrgenciaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-danger modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Eliminar Urgencia</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                <h4 class="text-danger mt-3">¿Estás seguro?</h4>
                <p>Se eliminará el nivel: <strong id="delete-nombre-display"></strong></p>
                <form id="delete-form" action="<?= base_url('admin/urgencias/eliminar') ?>" method="post">
                    <input type="hidden" name="id" id="delete-id">
                </form>
            </div>
            <div class="modal-footer">
                <button form="delete-form" type="submit" class="btn btn-danger">Sí, Eliminar</button>
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        $('#urgencias-datatables').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json' },
            order: [[3, 'asc']], // Ordenar por Recargo (Menor a mayor)
        });

        // Cargar Modal Editar
        $('body').on('click', '.btn-edit', function () {
            let id = $(this).data('id');
            let nombre = $(this).data('nombre');
            let descripcion = $(this).data('descripcion');
            let recargo = $(this).data('recargo');
            let tiempo = $(this).data('tiempo');
            let activo = $(this).data('activo');

            $('#edit-id').val(id);
            $('#edit-nombre').val(nombre);
            $('#edit-descripcion').val(descripcion);
            $('#edit-recargo').val(recargo);
            $('#edit-tiempo').val(tiempo);

            // Manejo del checkbox
            if (activo == 1) {
                $('#edit-activo').prop('checked', true);
            } else {
                $('#edit-activo').prop('checked', false);
            }
        });

        // Cargar Modal Eliminar
        $('body').on('click', '.btn-delete', function () {
            let id = $(this).data('id');
            let nombre = $(this).data('nombre');

            $('#delete-id').val(id);
            $('#delete-nombre-display').text(nombre);
        });
    });
</script>
<?= $this->endSection() ?>