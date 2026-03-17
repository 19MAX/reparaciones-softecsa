<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
Pagos a Técnicos
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
        <li class="nav-item"><a href="#">Pagos a Técnicos</a></li>
    </ul>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- ── Tarjetas de resumen ─────────────────────────────────────────── -->
<div class="row row-card-no-pd mb-4">
    <div class="col-12 col-sm-6 col-md-4">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold text-warning mb-0">Por Validar</h6>
                        <small class="text-muted">Pendiente de revisión</small>
                    </div>
                    <h4 class="text-warning fw-bold mb-0">$<?= number_format($total_pendiente, 2) ?></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold text-info mb-0">Validados</h6>
                        <small class="text-muted">Aprobados, pendiente de pago</small>
                    </div>
                    <h4 class="text-info fw-bold mb-0">$<?= number_format($total_validado, 2) ?></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold text-success mb-0">Pagados</h6>
                        <small class="text-muted">Pagos confirmados</small>
                    </div>
                    <h4 class="text-success fw-bold mb-0">$<?= number_format($total_pagado, 2) ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Resumen por técnico ─────────────────────────────────────────── -->
<?php if (!empty($resumen)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>Resumen por Técnico
                </h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($resumen as $res): ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0">
                                        <a href="<?= base_url('admin/pagos-tecnicos/tecnico/' . $res['tecnico_id']) ?>" class="text-dark">
                                            <?= esc($res['tecnico_nombre']) ?>
                                        </a>
                                    </h6>
                                    <span class="badge bg-secondary"><?= $res['total_reparaciones'] ?> rep.</span>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge bg-warning text-dark">P: $<?= number_format($res['pendiente'], 2) ?></span>
                                    <span class="badge bg-info">V: $<?= number_format($res['validado'], 2) ?></span>
                                    <span class="badge bg-success">✓ $<?= number_format($res['pagado'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Filtros ─────────────────────────────────────────────────────── -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body py-2">
                <form method="GET" action="<?= base_url('admin/pagos-tecnicos') ?>" class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label form-label-sm mb-1">Técnico</label>
                        <select name="tecnico_id" class="form-select form-select-sm">
                            <option value="">Todos los técnicos</option>
                            <?php foreach ($tecnicos as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= $filtro_tecnico == $t['id'] ? 'selected' : '' ?>>
                                    <?= esc($t['nombre']) ?> <?= esc($t['apellido'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label form-label-sm mb-1">Estado de pago</label>
                        <select name="estado" class="form-select form-select-sm">
                            <option value="">Todos</option>
                            <option value="pendiente" <?= $filtro_estado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                            <option value="validado"  <?= $filtro_estado === 'validado'  ? 'selected' : '' ?>>Validado</option>
                            <option value="pagado"    <?= $filtro_estado === 'pagado'    ? 'selected' : '' ?>>Pagado</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter me-1"></i>Filtrar
                        </button>
                        <a href="<?= base_url('admin/pagos-tecnicos') ?>" class="btn btn-outline-secondary btn-sm ms-1">
                            <i class="fas fa-times me-1"></i>Limpiar
                        </a>
                    </div>
                    <?php if (!empty($pagos)): ?>
                    <div class="col-12 col-md-auto ms-auto">
                        <button type="button" class="btn btn-outline-info btn-sm" id="btn-validar-seleccionados" disabled>
                            <i class="fas fa-check-double me-1"></i>Validar Seleccionados
                        </button>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ── Tabla principal ─────────────────────────────────────────────── -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Registro de Comisiones
                    <span class="badge bg-secondary ms-2"><?= count($pagos) ?></span>
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="pagos-datatables" class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">
                                    <input type="checkbox" id="check-all" class="form-check-input">
                                </th>
                                <th>Técnico</th>
                                <th>Orden</th>
                                <th>Dispositivo</th>
                                <th>Cliente</th>
                                <th>M. Obra Base</th>
                                <th>Comisión</th>
                                <th>Fecha Rep.</th>
                                <th>Estado</th>
                                <th>Validado por</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pagos)): ?>
                                <?php foreach ($pagos as $pago): ?>
                                    <tr data-pago-id="<?= $pago['id'] ?>" data-estado="<?= $pago['estado_pago'] ?>">
                                        <td>
                                            <?php if ($pago['estado_pago'] === 'pendiente'): ?>
                                                <input type="checkbox" class="form-check-input check-pago" value="<?= $pago['id'] ?>">
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold">
                                            <a href="<?= base_url('admin/pagos-tecnicos/tecnico/' . $pago['tecnico_id']) ?>">
                                                <?= esc($pago['tecnico_nombre']) ?> <?= esc($pago['tecnico_apellido'] ?? '') ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="text-primary fw-bold"><?= esc($pago['numero_orden']) ?></span>
                                        </td>
                                        <td>
                                            <small><?= esc($pago['tipo_dispositivo']) ?> <?= esc($pago['marca']) ?> <?= esc($pago['modelo']) ?></small>
                                        </td>
                                        <td>
                                            <small><?= esc($pago['cliente_nombre']) ?> <?= esc($pago['cliente_apellido']) ?></small>
                                        </td>
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
                                            <small><?= date('d/m/Y H:i', strtotime($pago['fecha_reparacion'])) ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $badges = [
                                                'pendiente' => 'bg-warning text-dark',
                                                'validado'  => 'bg-info',
                                                'pagado'    => 'bg-success',
                                            ];
                                            $labels = [
                                                'pendiente' => 'Pendiente',
                                                'validado'  => 'Validado',
                                                'pagado'    => 'Pagado',
                                            ];
                                            $badge = $badges[$pago['estado_pago']] ?? 'bg-secondary';
                                            $label = $labels[$pago['estado_pago']] ?? $pago['estado_pago'];
                                            ?>
                                            <span class="badge <?= $badge ?>">
                                                <?= $label ?>
                                            </span>
                                            <?php if ($pago['fecha_pago']): ?>
                                                <br><small class="text-muted"><?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?></small>
                                            <?php elseif ($pago['fecha_validacion']): ?>
                                                <br><small class="text-muted"><?= date('d/m/Y', strtotime($pago['fecha_validacion'])) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($pago['validado_por_nombre']): ?>
                                                <small class="text-muted"><?= esc($pago['validado_por_nombre']) ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">—</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 flex-nowrap">
                                                <?php if ($pago['estado_pago'] === 'pendiente'): ?>
                                                    <button class="btn btn-xs btn-outline-info btn-validar"
                                                            data-id="<?= $pago['id'] ?>"
                                                            data-tecnico="<?= esc($pago['tecnico_nombre']) ?>"
                                                            data-monto="<?= number_format($pago['monto_comision'], 2) ?>"
                                                            title="Validar pago">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-xs btn-success btn-pagar"
                                                            data-id="<?= $pago['id'] ?>"
                                                            data-tecnico="<?= esc($pago['tecnico_nombre']) ?>"
                                                            data-monto="<?= number_format($pago['monto_comision'], 2) ?>"
                                                            title="Marcar como pagado">
                                                        <i class="fas fa-dollar-sign"></i>
                                                    </button>
                                                <?php elseif ($pago['estado_pago'] === 'validado'): ?>
                                                    <button class="btn btn-xs btn-success btn-pagar"
                                                            data-id="<?= $pago['id'] ?>"
                                                            data-tecnico="<?= esc($pago['tecnico_nombre']) ?>"
                                                            data-monto="<?= number_format($pago['monto_comision'], 2) ?>"
                                                            title="Marcar como pagado">
                                                        <i class="fas fa-dollar-sign"></i> Pagar
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-success"><i class="fas fa-check-circle"></i></span>
                                                <?php endif; ?>
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

<!-- ── Modal confirmar acción ─────────────────────────────────────── -->
<div class="modal fade" id="modalConfirmarPago" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPagoTitulo">Confirmar acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="modalPagoTexto"></p>
                <div class="mb-3">
                    <label class="form-label">Observación (opcional)</label>
                    <textarea id="modalPagoObservacion" class="form-control" rows="2" placeholder="Ej: Transferencia #12345"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-confirmar-accion">Confirmar</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {

    // DataTable
    $('#pagos-datatables').DataTable({
        scrollX: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json' },
        order: [[7, 'desc']],
        columnDefs: [
            { orderable: false, targets: [0, 10] }
        ],
        layout: {
            topStart: { buttons: ['pageLength', 'copy', 'excel', 'pdf'] }
        }
    });

    // ── Seleccionar todos ───────────────────────────────────────────
    $('#check-all').on('change', function () {
        $('.check-pago').prop('checked', this.checked);
        actualizarBotonLote();
    });

    $(document).on('change', '.check-pago', function () {
        actualizarBotonLote();
        if (!this.checked) $('#check-all').prop('checked', false);
    });

    function actualizarBotonLote() {
        const cantidad = $('.check-pago:checked').length;
        $('#btn-validar-seleccionados')
            .prop('disabled', cantidad === 0)
            .text(cantidad > 0 ? `Validar ${cantidad} Seleccionados` : 'Validar Seleccionados');
    }

    // ── Variables de estado del modal ───────────────────────────────
    let accionActual = '';
    let pagoIdActual = 0;

    // ── Botón Validar individual ────────────────────────────────────
    $(document).on('click', '.btn-validar', function () {
        pagoIdActual = $(this).data('id');
        accionActual = 'validar';
        $('#modalPagoTitulo').text('Validar Comisión');
        $('#modalPagoTexto').html(
            `¿Confirmas la validación del pago de <strong>$${$(this).data('monto')}</strong> a <strong>${$(this).data('tecnico')}</strong>?`
        );
        $('#btn-confirmar-accion').removeClass('btn-success').addClass('btn-info').text('Validar');
        new bootstrap.Modal(document.getElementById('modalConfirmarPago')).show();
    });

    // ── Botón Marcar Pagado individual ──────────────────────────────
    $(document).on('click', '.btn-pagar', function () {
        pagoIdActual = $(this).data('id');
        accionActual = 'pagar';
        $('#modalPagoTitulo').text('Confirmar Pago');
        $('#modalPagoTexto').html(
            `¿Confirmas que se realizó el pago de <strong>$${$(this).data('monto')}</strong> a <strong>${$(this).data('tecnico')}</strong>?`
        );
        $('#btn-confirmar-accion').removeClass('btn-info').addClass('btn-success').text('Marcar como Pagado');
        new bootstrap.Modal(document.getElementById('modalConfirmarPago')).show();
    });

    // ── Botón Validar en lote ───────────────────────────────────────
    $('#btn-validar-seleccionados').on('click', function () {
        accionActual = 'validar-lote';
        const cantidad = $('.check-pago:checked').length;
        $('#modalPagoTitulo').text('Validar en Lote');
        $('#modalPagoTexto').html(`¿Confirmas la validación de <strong>${cantidad}</strong> pago(s) seleccionados?`);
        $('#btn-confirmar-accion').removeClass('btn-success').addClass('btn-info').text('Validar Todos');
        new bootstrap.Modal(document.getElementById('modalConfirmarPago')).show();
    });

    // ── Confirmar acción del modal ──────────────────────────────────
    $('#btn-confirmar-accion').on('click', function () {
        const observacion = $('#modalPagoObservacion').val();
        const $btn = $(this).prop('disabled', true).text('Procesando...');

        let url, data;

        if (accionActual === 'validar') {
            url = '<?= base_url('admin/pagos-tecnicos/validar') ?>';
            data = { pago_id: pagoIdActual, observacion };
        } else if (accionActual === 'pagar') {
            url = '<?= base_url('admin/pagos-tecnicos/marcar-pagado') ?>';
            data = { pago_id: pagoIdActual, observacion };
        } else if (accionActual === 'validar-lote') {
            url = '<?= base_url('admin/pagos-tecnicos/validar-lote') ?>';
            data = {
                ids: $('.check-pago:checked').map(function() { return $(this).val(); }).get(),
                observacion
            };
        }

        $.post(url, data)
            .done(function (res) {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalConfirmarPago')).hide();
                    location.reload();
                } else {
                    alert('Error: ' + res.message);
                    $btn.prop('disabled', false);
                }
            })
            .fail(function () {
                alert('Error de conexión. Intenta nuevamente.');
                $btn.prop('disabled', false);
            });
    });

    // Limpiar modal al cerrar
    document.getElementById('modalConfirmarPago').addEventListener('hidden.bs.modal', function () {
        $('#modalPagoObservacion').val('');
        $('#btn-confirmar-accion').prop('disabled', false);
        pagoIdActual = 0;
        accionActual = '';
    });
});
</script>
<?= $this->endSection() ?>
