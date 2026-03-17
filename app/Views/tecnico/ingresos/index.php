<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
Mis Ingresos
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('tecnico/dashboard') ?>">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Mis Ingresos</a></li>
    </ul>
</div>

<!-- ── Tarjetas resumen ──────────────────────────────────────────── -->
<div class="row row-card-no-pd mb-4">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>Total Este Mes</b></h6>
                        <p class="text-muted mb-0"><?= date('F Y') ?></p>
                    </div>
                    <h4 class="text-success fw-bold">$<?= number_format($totalMes, 2) ?></h4>
                </div>
                <div class="progress progress-sm mt-2">
                    <div class="progress-bar bg-success w-100" role="progressbar"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>Total Este Año</b></h6>
                        <p class="text-muted mb-0"><?= date('Y') ?></p>
                    </div>
                    <h4 class="text-primary fw-bold">$<?= number_format($totalAno, 2) ?></h4>
                </div>
                <div class="progress progress-sm mt-2">
                    <div class="progress-bar bg-primary w-75" role="progressbar"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>Promedio/Reparación</b></h6>
                        <p class="text-muted mb-0">Media general</p>
                    </div>
                    <h4 class="text-info fw-bold">$<?= number_format($promedioReparacion, 2) ?></h4>
                </div>
                <div class="progress progress-sm mt-2">
                    <div class="progress-bar bg-info w-50" role="progressbar"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6><b>Mi Comisión</b></h6>
                        <?php if ($tecnico_config): ?>
                            <p class="text-muted mb-0">
                                <?php if ($tecnico_config['tipo_comision'] === 'porcentaje'): ?>
                                    <span class="badge bg-warning text-dark">
                                        <?= $tecnico_config['valor_comision'] ?>% de mano de obra
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-dark">
                                        $<?= number_format($tecnico_config['valor_comision'], 2) ?> fijo
                                    </span>
                                <?php endif; ?>
                            </p>
                        <?php else: ?>
                            <p class="text-muted mb-0"><span class="badge bg-secondary">Sin configurar</span></p>
                        <?php endif; ?>
                    </div>
                    <i class="fas fa-percentage fa-2x text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Tarjetas de estado de pagos ──────────────────────────────── -->
<div class="row row-card-no-pd mb-4">
    <div class="col-12 col-sm-4">
        <div class="card border-warning">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-warning fw-bold">Por Validar</small>
                        <p class="mb-0 text-muted" style="font-size:.75rem">El admin aún no revisa</p>
                    </div>
                    <h5 class="text-warning fw-bold mb-0">$<?= number_format($totalPendiente, 2) ?></h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card border-info">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-info fw-bold">Validados</small>
                        <p class="mb-0 text-muted" style="font-size:.75rem">Aprobados, pendiente pago</p>
                    </div>
                    <h5 class="text-info fw-bold mb-0">$<?= number_format($totalValidado, 2) ?></h5>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card border-success">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-success fw-bold">Pagados</small>
                        <p class="mb-0 text-muted" style="font-size:.75rem">Pagos confirmados</p>
                    </div>
                    <h5 class="text-success fw-bold mb-0">$<?= number_format($totalPagado, 2) ?></h5>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Tabla historial ───────────────────────────────────────────── -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-list-alt me-2"></i>Historial de Comisiones
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="ingresos-datatables" class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Orden</th>
                                <th>Dispositivo</th>
                                <th>Cliente</th>
                                <th>M. Obra Base</th>
                                <th>Mi Comisión</th>
                                <th>Total Reparación</th>
                                <th>Fecha Rep.</th>
                                <th>Estado Pago</th>
                                <th>Fecha Pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pagos)): ?>
                                <?php foreach ($pagos as $pago): ?>
                                    <tr>
                                        <td class="fw-bold text-primary">
                                            <?= esc($pago['numero_orden']) ?>
                                        </td>
                                        <td>
                                            <?= esc($pago['tipo_dispositivo']) ?>
                                            <?= esc($pago['marca']) ?>
                                            <?= esc($pago['modelo']) ?>
                                        </td>
                                        <td>
                                            <?= esc($pago['cliente_nombre']) ?> <?= esc($pago['cliente_apellido']) ?>
                                        </td>
                                        <td class="text-end">
                                            $<?= number_format($pago['mano_obra_base'], 2) ?>
                                            <?php if ($pago['tipo_comision'] === 'porcentaje' && $pago['porcentaje_aplicado']): ?>
                                                <br><small class="text-muted"><?= $pago['porcentaje_aplicado'] ?>%</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-success fs-6">
                                                $<?= number_format($pago['monto_comision'], 2) ?>
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold">
                                            $<?= number_format($pago['precio_total'], 2) ?>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($pago['fecha_reparacion'])) ?>
                                            <br>
                                            <small class="text-muted"><?= date('H:i', strtotime($pago['fecha_reparacion'])) ?></small>
                                        </td>
                                        <td>
                                            <?php
                                                $badges = [
                                                    'pendiente' => 'bg-warning text-dark',
                                                    'validado'  => 'bg-info',
                                                    'pagado'    => 'bg-success',
                                                ];
                                                $labels = [
                                                    'pendiente' => 'Por validar',
                                                    'validado'  => 'Validado',
                                                    'pagado'    => 'Pagado',
                                                ];
                                            ?>
                                            <span class="badge <?= $badges[$pago['estado_pago']] ?? 'bg-secondary' ?>">
                                                <?= $labels[$pago['estado_pago']] ?? $pago['estado_pago'] ?>
                                            </span>
                                            <?php if ($pago['observacion']): ?>
                                                <br><small class="text-muted" title="<?= esc($pago['observacion']) ?>">
                                                    <i class="fas fa-comment-alt"></i>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($pago['fecha_pago']): ?>
                                                <span class="text-success">
                                                    <?= date('d/m/Y', strtotime($pago['fecha_pago'])) ?>
                                                </span>
                                            <?php elseif ($pago['fecha_validacion']): ?>
                                                <small class="text-muted"><?= date('d/m/Y', strtotime($pago['fecha_validacion'])) ?></small>
                                            <?php else: ?>
                                                <small class="text-muted">—</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($pagos)): ?>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end">Totales:</th>
                                <th class="text-end">
                                    <span class="badge bg-success fs-6">
                                        $<?= number_format($totalGeneral, 2) ?>
                                    </span>
                                </th>
                                <th class="text-end">
                                    $<?= number_format(array_sum(array_column($pagos, 'precio_total')), 2) ?>
                                </th>
                                <th colspan="3"></th>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {
    $('#ingresos-datatables').DataTable({
        scrollX: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json',
        },
        order: [[6, 'desc']],
        layout: {
            topStart: {
                buttons: ['pageLength', 'copy', 'excel', 'pdf']
            }
        }
    });
});
</script>
<?= $this->endSection() ?>
