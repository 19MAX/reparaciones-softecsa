<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?><?= $titulo ?? 'Historial' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Bitácora de Movimientos</h4>
        <ul class="breadcrumbs">
            <li class="nav-home"><a href="<?= base_url('admin/dashboard') ?>"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item">Taller</li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item">Historial</li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Registros del Sistema</h4>
                        <button class="btn btn-primary btn-round ms-auto btn-sm"
                            data-bs-toggle="modal" data-bs-target="#addNotaModal">
                            <i class="fa fa-plus"></i> Nota Manual
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="historial-datatables" class="display table table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Dispositivo / Orden</th>
                                    <th>Usuario</th>
                                    <th class="text-center">Movimiento</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historial as $log): ?>
                                <?php
                                    $estadoAnterior    = $log['estado_anterior'] ?? null;
                                    $estadoNuevo       = $log['estado_nuevo'] ?? 'pendiente';
                                    $hasChange         = $estadoAnterior && $estadoAnterior !== $estadoNuevo;
                                    $estadoDispositivo = $log['dispositivo_estado'] ?? $estadoNuevo;
                                    $puedeEditar       = !in_array($estadoDispositivo, ['listo', 'entregado', 'cancelado']);
                                ?>
                                <tr>
                                    <!-- Fecha -->
                                    <td style="white-space: nowrap; vertical-align: middle;">
                                        <span class="fw-bold" style="font-size: 0.82rem;">
                                            <?= date('d/m/y', strtotime($log['created_at'])) ?>
                                        </span><br>
                                        <small class="text-muted"><?= date('H:i', strtotime($log['created_at'])) ?></small>
                                    </td>

                                    <!-- Dispositivo -->
                                    <td style="vertical-align: middle;">
                                        <span class="fw-bold text-primary d-block" style="font-size: 0.85rem;">
                                            <?= esc($log['marca']) ?> <?= esc($log['modelo']) ?>
                                        </span>
                                        <small class="text-muted"><?= esc($log['numero_orden']) ?></small>
                                    </td>

                                    <!-- Usuario -->
                                    <td style="vertical-align: middle;">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xs me-2">
                                                <span class="avatar-title rounded-circle border border-white bg-secondary">
                                                    <?= substr($log['usuario_nombre'] ?? 'U', 0, 1) ?>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="fw-bold" style="font-size: 12px;">
                                                    <?= esc($log['usuario_nombre'] ?? 'Usuario') ?>
                                                </div>
                                                <small class="text-muted" style="font-size: 10px;">
                                                    <?= ucfirst($log['usuario_rol'] ?? '') ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Movimiento -->
                                    <td style="vertical-align: middle;">
                                        <div class="movement-cell">
                                            <?php if ($hasChange): ?>
                                                <div class="movement-row">
                                                    <?= estadoPill($estadoAnterior) ?>
                                                    <i class="fas fa-arrow-right movement-arrow"></i>
                                                    <?= estadoPill($estadoNuevo) ?>
                                                </div>
                                                <span class="movement-label">cambio de estado</span>
                                            <?php else: ?>
                                                <div class="movement-row">
                                                    <?= estadoPill($estadoNuevo) ?>
                                                </div>
                                                <span class="movement-label">
                                                    <?= $estadoAnterior ? 'sin cambio' : 'nota manual' ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="text-center" style="vertical-align: middle;">
                                        <div class="dropdown">
                                            <button class="btn btn-link btn-secondary dropdown-toggle p-0"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item btn-view-obs" href="#"
                                                        data-obs="<?= esc($log['observacion']) ?>"
                                                        data-obs-cliente="<?= esc($log['observacion_cliente'] ?? '') ?>"
                                                        data-user="<?= esc($log['usuario_nombre']) ?>"
                                                        data-fecha="<?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>">
                                                        <i class="fa fa-eye me-2"></i> Ver Observación
                                                    </a>
                                                </li>
                                                <?php if ($puedeEditar): ?>
                                                <li>
                                                    <a class="dropdown-item btn-edit" href="#"
                                                        data-id="<?= $log['id'] ?>"
                                                        data-observacion="<?= esc($log['observacion']) ?>"
                                                        data-observacion_cliente="<?= esc($log['observacion_cliente'] ?? '') ?>">
                                                        <i class="fa fa-pen me-2"></i> Editar Nota
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                                <?php if (session()->get('role') === 'admin'): ?>
                                                <li>
                                                    <a class="dropdown-item text-danger btn-delete" href="#"
                                                        data-id="<?= $log['id'] ?>">
                                                        <i class="fa fa-trash me-2"></i> Eliminar
                                                    </a>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
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
</div>

<!-- Modal — Ver Observación -->
<div class="modal fade" id="modalDetalleObs" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Detalles del Registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="d-flex justify-content-between mb-3 bg-light p-2 rounded">
                    <small class="text-muted"><i class="fa fa-user me-1"></i><span id="det-user"></span></small>
                    <small class="text-muted"><i class="fa fa-clock me-1"></i><span id="det-fecha"></span></small>
                </div>
                <label class="fw-bold small text-uppercase text-muted">Nota Técnica (Interna)</label>
                <div class="p-3 border rounded mb-3" style="font-size: 0.9rem; background: #fcfcfc;">
                    <p id="det-obs" class="mb-0"></p>
                </div>
                <label class="fw-bold small text-uppercase text-success">Comentario para el Cliente</label>
                <div class="p-3 border rounded" style="font-size: 0.9rem; background: #f0fdf4;">
                    <p id="det-obs-cliente" class="mb-0 text-muted fst-italic">Sin comentario para cliente.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal — Agregar Nota Manual -->
<div class="modal fade" id="addNotaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Nueva Entrada Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/historial/crear') ?>" method="post">
                <div class="modal-body">
                    <div class="form-group p-0 mb-3">
                        <label class="fw-bold small">Dispositivo / Orden</label>
                        <select class="form-select select2-modal" name="dispositivo_orden_id" required style="width: 100%">
                            <option value="">Seleccionar equipo...</option>
                            <?php foreach ($dispositivos as $disp): ?>
                                <option value="<?= $disp['id'] ?>">
                                    [<?= $disp['numero_orden'] ?>] <?= $disp['marca'] ?> <?= $disp['modelo_nombre'] ?? $disp['modelo_texto'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group p-0 mb-3">
                        <label class="fw-bold small">Estado</label>
                        <select class="form-select" name="estado_nuevo" required>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="pausado">Pausado</option>
                            <option value="listo">Listo</option>
                            <option value="entregado">Entregado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="form-group p-0 mb-3">
                        <label class="fw-bold small">Nota Técnica Interna</label>
                        <textarea class="form-control" name="observacion" rows="3" required
                            placeholder="Escribe detalles de la acción realizada..."></textarea>
                    </div>
                    <div class="form-group p-0">
                        <label class="fw-bold small">Comentario para el Cliente <span class="text-muted fw-normal">(Opcional)</span></label>
                        <textarea class="form-control" name="observacion_cliente" rows="2"
                            placeholder="Información que el cliente podrá ver..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal — Editar Nota -->
<div class="modal fade" id="editNotaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">
                    <i class="fa fa-pen me-2 text-primary"></i>Editar Nota
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('admin/historial/editar') ?>" method="post">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="form-group p-0 mb-3">
                        <label class="fw-bold small">Nota Técnica Interna</label>
                        <textarea class="form-control" name="observacion" id="edit-observacion" rows="4"
                            placeholder="Detalles técnicos internos..." required></textarea>
                    </div>
                    <div class="form-group p-0">
                        <label class="fw-bold small">Comentario para el Cliente <span class="text-muted fw-normal">(Opcional)</span></label>
                        <textarea class="form-control" name="observacion_cliente" id="edit-observacion-cliente" rows="3"
                            placeholder="Información visible para el cliente..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {

    $('#historial-datatables').DataTable({
        pageLength: 15,
        order: [[0, 'desc']],
        language: { url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json' },
        columnDefs: [{ orderable: false, targets: [3, 4] }]
    });

    $(document).on('click', '.btn-view-obs', function (e) {
        e.preventDefault();
        const obsCliente = $(this).data('obs-cliente');
        $('#det-user').text($(this).data('user'));
        $('#det-fecha').text($(this).data('fecha'));
        $('#det-obs').text($(this).data('obs') || 'Sin nota técnica.');
        if (obsCliente) {
            $('#det-obs-cliente').text(obsCliente).removeClass('text-muted fst-italic');
        } else {
            $('#det-obs-cliente').text('Sin comentario para cliente.').addClass('text-muted fst-italic');
        }
        $('#modalDetalleObs').modal('show');
    });

    $(document).on('click', '.btn-edit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#edit-id').val($(this).data('id'));
        $('#edit-observacion').val($(this).data('observacion'));
        $('#edit-observacion-cliente').val($(this).data('observacion_cliente'));
        $(this).closest('.dropdown').find('[data-bs-toggle="dropdown"]').dropdown('hide');
        setTimeout(() => { $('#editNotaModal').modal('show'); }, 150);
    });

    $(document).on('click', '.btn-delete', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        Swal.fire({
            title: '¿Eliminar de la bitácora?',
            text: 'Esta acción no se puede deshacer y afecta la trazabilidad.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                const f = document.createElement('form');
                f.method = 'POST';
                f.action = '<?= base_url('admin/historial/eliminar') ?>';
                const i = document.createElement('input');
                i.type = 'hidden'; i.name = 'id'; i.value = id;
                f.appendChild(i);
                document.body.appendChild(f);
                f.submit();
            }
        });
    });

});
</script>
<?= $this->endSection() ?>