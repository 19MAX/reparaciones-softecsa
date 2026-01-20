<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?? 'Historial Global' ?>
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
        <li class="nav-item">Taller</li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item">Bitácora Global</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title"><i class="fas fa-clipboard-list me-2"></i>Historial de Movimientos</h4>
                    <button type="button" class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal"
                        data-bs-target="#addNotaModal">
                        <i class="fa fa-plus"></i> Nota Manual
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="historial-datatables" class="table table-bordered table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Orden / Dispositivo</th>
                                <th>Usuario</th>
                                <th>Movimiento</th>
                                <th>Comentario</th>
                                <th>Visibilidad</th>
                                <th style="width: 5%">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($historial)): ?>
                                <?php foreach ($historial as $log): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= date('d/m/Y', strtotime($log['created_at'])) ?></div>
                                            <small
                                                class="text-muted"><?= date('H:i A', strtotime($log['created_at'])) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark mb-1">
                                                <?= esc($log['codigo_orden']) ?>
                                            </span>
                                            <div class="small fw-bold text-primary">
                                                <?= esc($log['marca']) ?>         <?= esc($log['modelo']) ?>
                                            </div>
                                            <div class="small text-muted">
                                                <i class="fas fa-user me-1"></i><?= esc($log['cliente_nombre']) ?>
                                                <?= esc($log['cliente_apellido']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs me-2">
                                                    <span class="avatar-title rounded-circle border border-white bg-secondary">
                                                        <?= substr($log['usuario_nombre'], 0, 1) ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="fw-bold" style="font-size: 12px;">
                                                        <?= esc($log['usuario_nombre']) ?></div>
                                                    <small class="text-muted"
                                                        style="font-size: 10px;"><?= ucfirst($log['usuario_rol']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($log['estado_anterior'] == $log['estado_nuevo']): ?>
                                                <span class="badge badge-info"><?= esc($log['estado_nuevo']) ?></span>
                                                <div class="text-muted fst-italic" style="font-size: 11px;">(Nota informativa)</div>
                                            <?php else: ?>
                                                <div class="d-flex flex-column align-items-center">
                                                    <span class="badge badge-outline-secondary mb-1"
                                                        style="font-size: 10px;"><?= esc($log['estado_anterior'] ?? 'Inicio') ?></span>
                                                    <i class="fas fa-arrow-down text-muted" style="font-size: 10px;"></i>
                                                    <span class="badge badge-success mt-1"><?= esc($log['estado_nuevo']) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <p class="text-muted mb-0" style="font-size: 13px; line-height: 1.4;">
                                                <?= nl2br(esc($log['comentario'])) ?>
                                            </p>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($log['es_visible_cliente']): ?>
                                                <i class="fas fa-eye text-success" data-bs-toggle="tooltip"
                                                    title="Cliente puede ver esto"></i>
                                            <?php else: ?>
                                                <i class="fas fa-eye-slash text-muted" data-bs-toggle="tooltip"
                                                    title="Uso interno solamente"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-link btn-secondary dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item btn-edit" href="#" data-id="<?= $log['id'] ?>"
                                                            data-comentario="<?= esc($log['comentario']) ?>"
                                                            data-visible="<?= $log['es_visible_cliente'] ?>"
                                                            data-bs-toggle="modal" data-bs-target="#editNotaModal">
                                                            Editar Nota
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger btn-delete" href="#"
                                                            data-id="<?= $log['id'] ?>" data-bs-toggle="modal"
                                                            data-bs-target="#deleteNotaModal">
                                                            Eliminar
                                                        </a>
                                                    </li>
                                                </ul>
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

<div class="modal fade" id="addNotaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Agregar Nota al Historial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-form" action="<?= base_url('admin/historial/crear') ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Seleccionar Orden / Dispositivo</label>
                        <select class="form-select select2-modal" name="dispositivo_id" required style="width: 100%">
                            <option value="">Buscar por Orden o Cliente...</option>
                            <?php if (!empty($dispositivos)): ?>
                                <?php foreach ($dispositivos as $disp): ?>
                                    <option value="<?= $disp['id'] ?>">
                                        [<?= $disp['codigo_orden'] ?>] <?= $disp['marca'] ?>         <?= $disp['modelo'] ?> -
                                        <?= $disp['nombres'] ?>         <?= $disp['apellidos'] ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado de Referencia</label>
                        <select class="form-select" name="estado_nuevo" required>
                            <option value="recibida">Recibida</option>
                            <option value="en_diagnostico">En Diagnóstico</option>
                            <option value="pendiente_aprobacion">Pendiente Aprobación</option>
                            <option value="en_reparacion">En Reparación</option>
                            <option value="pruebas_calidad">Pruebas de Calidad</option>
                            <option value="listo_para_retiro">Listo para Retiro</option>
                            <option value="entregado">Entregado</option>
                        </select>
                        <small class="text-muted">Selecciona el estado actual del equipo.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Comentario</label>
                        <textarea class="form-control" name="comentario" rows="3" required
                            placeholder="Escribe los detalles aquí..."></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="es_visible_cliente" id="add-visible">
                        <label class="form-check-label" for="add-visible">¿Visible para el Cliente?</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button form="add-form" type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editNotaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark">Editar Nota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-form" action="<?= base_url('admin/historial/editar') ?>" method="post">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Comentario</label>
                        <textarea class="form-control" name="comentario" id="edit-comentario" rows="4"
                            required></textarea>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="es_visible_cliente" id="edit-visible">
                        <label class="form-check-label" for="edit-visible">¿Visible para el Cliente?</label>
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

<div class="modal fade" id="deleteNotaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-danger modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <i class="fas fa-trash-alt text-danger mb-3" style="font-size: 3rem;"></i>
                <h4 class="text-danger fw-bold">¿Eliminar Registro?</h4>
                <p>Esta acción eliminará la nota de la bitácora. Solo un administrador debería hacer esto.</p>
                <form id="delete-form" action="<?= base_url('admin/historial/eliminar') ?>" method="post">
                    <input type="hidden" name="id" id="delete-id">
                </form>
                <div class="mt-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button form="delete-form" type="submit" class="btn btn-danger px-4">Sí, Eliminar</button>
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
        $('#historial-datatables').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json' },
            order: [[0, 'desc']], // Ordenar por fecha
        });

        // Inicializar Select2 si lo usas (Recomendado para muchos dispositivos)
        // $('.select2-modal').select2({ dropdownParent: $('#addNotaModal') });

        // Lógica Modal Editar
        $('body').on('click', '.btn-edit', function () {
            let id = $(this).data('id');
            let comentario = $(this).data('comentario');
            let visible = $(this).data('visible');

            $('#edit-id').val(id);
            $('#edit-comentario').val(comentario);

            if (visible == 1) {
                $('#edit-visible').prop('checked', true);
            } else {
                $('#edit-visible').prop('checked', false);
            }
        });

        // Lógica Modal Eliminar
        $('body').on('click', '.btn-delete', function () {
            let id = $(this).data('id');
            $('#delete-id').val(id);
        });
    });
</script>
<?= $this->endSection() ?>