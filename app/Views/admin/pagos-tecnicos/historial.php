<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= esc($titulo) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('admin/dashboard') ?>"><i class="icon-home"></i></a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="<?= base_url('admin/pagos-tecnicos') ?>">Pagos a Técnicos</a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#"><?= esc($tecnico['nombre']) ?></a></li>
    </ul>
</div>

<!-- ── Tarjetas de resumen del técnico ────────────────────────────── -->
<div class="row row-card-no-pd mb-4">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>Total Reparaciones</b></h6>
                        <p class="text-muted mb-0">Historial completo</p>
                    </div>
                    <h4 class="text-secondary fw-bold"><?= $resumen_tecnico['total_reparaciones'] ?></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>Por Validar</b></h6>
                        <p class="text-muted mb-0">Pendientes</p>
                    </div>
                    <h4 class="text-warning fw-bold">$<?= number_format($resumen_tecnico['pendiente'], 2) ?></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>Validados</b></h6>
                        <p class="text-muted mb-0">Aprobados</p>
                    </div>
                    <h4 class="text-info fw-bold">$<?= number_format($resumen_tecnico['validado'], 2) ?></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>Total Pagado</b></h6>
                        <p class="text-muted mb-0">Confirmados</p>
                    </div>
                    <h4 class="text-success fw-bold">$<?= number_format($resumen_tecnico['pagado'], 2) ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Tabla historial ─────────────────────────────────────────────── -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Historial de Comisiones — <?= esc($tecnico['nombre']) ?>
                </h4>
                <a href="<?= base_url('admin/pagos-tecnicos') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="historial-datatables" class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Orden</th>
                                <th>Dispositivo</th>
                                <th>Cliente</th>
                                <th>M. Obra Base</th>
                                <th>Comisión</th>
                                <th>Fecha Reparación</th>
                                <th>Estado Pago</th>
                                <th>Fecha Pago</th>
                                <th>Validado por</th>
                                <th>Observación</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pagos)): ?>
                                <?php foreach ($pagos as $pago): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?= esc($pago['numero_orden']) ?></td>
                                        <td>
                                            <?= esc($pago['tipo_dispositivo']) ?>
                                            <?= esc($pago['marca']) ?>
                                            <?= esc($pago['modelo']) ?>
                                        </td>
                                        <td><?= esc($pago['cliente_nombre']) ?> <?= esc($pago['cliente_apellido']) ?></td>
                                        <td class="text-end">
                                            $<?= number_format($pago['mano_obra_base'], 2) ?>
                                            <?php if ($pago['tipo_comision'] === 'porcentaje' && $pago['porcentaje_aplicado']): ?>
                                                <br><small class="text-muted"><?= $pago['porcentaje_aplicado'] ?>%</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            $<?= number_format($pago['monto_comision'], 2) ?>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($pago['fecha_reparacion'])) ?>
                                            <br><small class="text-muted"><?= date('H:i', strtotime($pago['fecha_reparacion'])) ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $badges = ['pendiente' => 'bg-warning text-dark', 'validado' => 'bg-info', 'pagado' => 'bg-success'];
                                            $labels = ['pendiente' => 'Pendiente', 'validado' => 'Validado', 'pagado' => 'Pagado'];
                                            ?>
                                            <span class="badge <?= $badges[$pago['estado_pago']] ?? 'bg-secondary' ?>">
                                                <?= $labels[$pago['estado_pago']] ?? $pago['estado_pago'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($pago['fecha_pago']): ?>
                                                <?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?>
                                            <?php elseif ($pago['fecha_validacion']): ?>
                                                <small class="text-muted"><?= date('d/m/Y', strtotime($pago['fecha_validacion'])) ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">—</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= $pago['validado_por_nombre'] ? esc($pago['validado_por_nombre']) : '<small class="text-muted">—</small>' ?>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= $pago['observacion'] ? esc($pago['observacion']) : '—' ?></small>
                                        </td>
                                        <td>
                                            <?php if ($pago['estado_pago'] === 'pendiente'): ?>
                                                <button class="btn btn-xs btn-outline-info btn-validar"
                                                        data-id="<?= $pago['id'] ?>"
                                                        data-monto="<?= number_format($pago['monto_comision'], 2) ?>">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-xs btn-success btn-pagar"
                                                        data-id="<?= $pago['id'] ?>"
                                                        data-monto="<?= number_format($pago['monto_comision'], 2) ?>">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </button>
                                            <?php elseif ($pago['estado_pago'] === 'validado'): ?>
                                                <button class="btn btn-xs btn-success btn-pagar"
                                                        data-id="<?= $pago['id'] ?>"
                                                        data-monto="<?= number_format($pago['monto_comision'], 2) ?>">
                                                    <i class="fas fa-dollar-sign"></i> Pagar
                                                </button>
                                            <?php else: ?>
                                                <i class="fas fa-check-circle text-success"></i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        No hay registros de comisiones para este técnico
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

<!-- Modal -->
<div class="modal fade" id="modalAccion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="modalTexto"></p>
                <div class="mb-3">
                    <label class="form-label">Observación (opcional)</label>
                    <textarea id="modalObservacion" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn" id="btn-confirmar">Confirmar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {
    $('#historial-datatables').DataTable({
        scrollX: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json' },
        order: [[5, 'desc']],
        columnDefs: [{ orderable: false, targets: [10] }],
        layout: { topStart: { buttons: ['pageLength', 'copy', 'excel', 'pdf'] } }
    });

    let accion = '', pagoId = 0;

    $(document).on('click', '.btn-validar', function () {
        pagoId = $(this).data('id');
        accion = 'validar';
        $('#modalTitulo').text('Validar Comisión');
        $('#modalTexto').html(`¿Validar el pago de <strong>$${$(this).data('monto')}</strong>?`);
        $('#btn-confirmar').removeClass('btn-success').addClass('btn-info').text('Validar');
        new bootstrap.Modal(document.getElementById('modalAccion')).show();
    });

    $(document).on('click', '.btn-pagar', function () {
        pagoId = $(this).data('id');
        accion = 'pagar';
        $('#modalTitulo').text('Confirmar Pago');
        $('#modalTexto').html(`¿Confirmar el pago de <strong>$${$(this).data('monto')}</strong>?`);
        $('#btn-confirmar').removeClass('btn-info').addClass('btn-success').text('Marcar como Pagado');
        new bootstrap.Modal(document.getElementById('modalAccion')).show();
    });

    $('#btn-confirmar').on('click', function () {
        const observacion = $('#modalObservacion').val();
        const url = accion === 'validar'
            ? '<?= base_url('admin/pagos-tecnicos/validar') ?>'
            : '<?= base_url('admin/pagos-tecnicos/marcar-pagado') ?>';

        $(this).prop('disabled', true).text('Procesando...');

        $.post(url, { pago_id: pagoId, observacion })
            .done(function (res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalAccion')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + res.message);
                    $('#btn-confirmar').prop('disabled', false);
                }
            })
            .fail(function () {
                alert('Error de conexión.');
                $('#btn-confirmar').prop('disabled', false);
            });
    });

    document.getElementById('modalAccion').addEventListener('hidden.bs.modal', function () {
        $('#modalObservacion').val('');
        $('#btn-confirmar').prop('disabled', false);
    });
});
</script>
<?= $this->endSection() ?>
