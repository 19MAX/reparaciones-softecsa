<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?? 'Horarios de Atención' ?>
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
        <li class="nav-item">Horarios de Atención</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title"><i class="fas fa-clock me-2"></i>Horarios de Atención</h4>
                    <button type="button" class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal"
                        title="Crear nuevo horario" data-bs-target="#addHorarioModal">
                        <i class="fa fa-plus"></i> Nuevo Horario
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="horarios-datatables" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Día</th>
                                <th>Hora Apertura</th>
                                <th>Hora Cierre</th>
                                <th>Estado</th>
                                <th style="width: 10%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($horarios)): ?>
                                <?php foreach ($horarios as $item): ?>
                                    <tr>
                                        <td class="fw-bold">
                                            <?= esc($diasSemana[$item['dia_semana']] ?? 'Desconocido') ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-black text-light">
                                                <i class="far fa-clock me-1"></i> <?= esc(date('h:i A', strtotime($item['hora_apertura']))) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-black text-light">
                                                <i class="far fa-clock me-1"></i> <?= esc(date('h:i A', strtotime($item['hora_cierre']))) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($item['abierto']): ?>
                                                <span class="badge badge-success">Abierto</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Cerrado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="form-button-action">
                                                <button type="button" class="btn btn-link btn-primary btn-lg btn-edit"
                                                    data-bs-toggle="modal" data-bs-target="#updateHorarioModal"
                                                    data-id="<?= $item['id'] ?>"
                                                    data-dia="<?= $item['dia_semana'] ?>"
                                                    data-apertura="<?= esc($item['hora_apertura']) ?>"
                                                    data-cierre="<?= esc($item['hora_cierre']) ?>"
                                                    data-abierto="<?= $item['abierto'] ?>"
                                                    title="Editar">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-link btn-danger btn-delete"
                                                    data-bs-toggle="modal" data-bs-target="#deleteHorarioModal"
                                                    data-id="<?= $item['id'] ?>"
                                                    data-dia="<?= esc($diasSemana[$item['dia_semana']] ?? '') ?>"
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

<!-- Modal Crear -->
<div class="modal fade" id="addHorarioModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title fw-bold text-white">Crear Horario de Atención</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-form" action="<?= base_url('admin/horarios-atencion/crear') ?>" method="post">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Día de la Semana <span class="text-danger">*</span></label>
                            <select class="form-select" name="dia_semana" required>
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($diasSemana as $key => $dia): ?>
                                    <option value="<?= $key ?>"><?= $dia ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Hora Apertura <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="hora_apertura" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Hora Cierre <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="hora_cierre" required>
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

<!-- Modal Editar -->
<div class="modal fade" id="updateHorarioModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark">Editar Horario de Atención</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-form" action="<?= base_url('admin/horarios-atencion/editar') ?>" method="post">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Día de la Semana <span class="text-danger">*</span></label>
                            <select class="form-select" name="dia_semana" id="edit-dia" required>
                                <?php foreach ($diasSemana as $key => $dia): ?>
                                    <option value="<?= $key ?>"><?= $dia ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Hora Apertura <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="hora_apertura" id="edit-apertura" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Hora Cierre <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="hora_cierre" id="edit-cierre" required>
                        </div>
                        <div class="col-md-4 mb-3 pt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="edit-abierto" name="abierto"
                                    value="1">
                                <label class="form-check-label fw-bold" for="edit-abierto">¿Día Abierto?</label>
                            </div>
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

<!-- Modal Eliminar -->
<div class="modal fade" id="deleteHorarioModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-danger modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Eliminar Horario</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                <h4 class="text-danger mt-3">¿Estás seguro?</h4>
                <p>Se eliminará el horario del día: <strong id="delete-dia-display"></strong></p>
                <form id="delete-form" action="<?= base_url('admin/horarios-atencion/eliminar') ?>" method="post">
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
        $('#horarios-datatables').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json' },
            order: [[0, 'asc']],
        });

        // Cargar Modal Editar
        $('body').on('click', '.btn-edit', function () {
            let id = $(this).data('id');
            let dia = $(this).data('dia');
            let apertura = $(this).data('apertura');
            let cierre = $(this).data('cierre');
            let abierto = $(this).data('abierto');

            $('#edit-id').val(id);
            $('#edit-dia').val(dia);
            $('#edit-apertura').val(apertura);
            $('#edit-cierre').val(cierre);

            if (abierto == 1) {
                $('#edit-abierto').prop('checked', true);
            } else {
                $('#edit-abierto').prop('checked', false);
            }
        });

        // Cargar Modal Eliminar
        $('body').on('click', '.btn-delete', function () {
            let id = $(this).data('id');
            let dia = $(this).data('dia');

            $('#delete-id').val(id);
            $('#delete-dia-display').text(dia);
        });
    });
</script>
<?= $this->endSection() ?>
