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
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Mis Ingresos</a>
        </li>
    </ul>
</div>

<div class="row row-card-no-pd mb-4">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6><b>Total Este Mes</b></h6>
                        <p class="text-muted"><?= date('F Y') ?></p>
                    </div>
                    <h4 class="text-success fw-bold">$<?= number_format($totalMes, 2) ?></h4>
                </div>
                <div class="progress progress-sm">
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
                        <p class="text-muted"><?= date('Y') ?></p>
                    </div>
                    <h4 class="text-primary fw-bold">$<?= number_format($totalAno, 2) ?></h4>
                </div>
                <div class="progress progress-sm">
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
                        <p class="text-muted">Media</p>
                    </div>
                    <h4 class="text-info fw-bold">$<?= number_format($promedioReparacion, 2) ?></h4>
                </div>
                <div class="progress progress-sm">
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
                        <p class="text-muted mb-0">
                            <?php if ($tecnico['tipo_comision'] === 'porcentaje'): ?>
                                <span class="badge bg-warning text-dark">
                                    <?= $tecnico['valor_comision'] ?>% de mano de obra
                                </span>
                            <?php else: ?>
                                <span class="badge bg-dark">
                                    $<?= number_format($tecnico['valor_comision'], 2) ?> fijo/reparación
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <i class="fas fa-percentage fa-2x text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">
                    <i class="fas fa-list-alt me-2"></i>Historial de Reparaciones Finalizadas
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="ingresos-datatables" class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Código Orden</th>
                                <th>Fecha Finalización</th>
                                <th>Cliente</th>
                                <th>Dispositivos</th>
                                <th>Mano de Obra</th>
                                <th>Mi Ganancia</th>
                                <th>Total Orden</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($reparaciones)): ?>
                                <?php foreach ($reparaciones as $reparacion): ?>
                                    <tr>
                                        <td class="fw-bold text-primary">
                                            <?= esc($reparacion['codigo_orden']) ?>
                                        </td>
                                        <td>
                                            <?= formatear_fecha($reparacion['fecha_finalizacion'], 'solo_fecha') ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('H:i', strtotime($reparacion['fecha_finalizacion'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?= esc($reparacion['cliente_nombres']) ?> <?= esc($reparacion['cliente_apellidos']) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?= $reparacion['cantidad_dispositivos'] ?> 
                                                dispositivo<?= ($reparacion['cantidad_dispositivos'] > 1) ? 's' : '' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold">$<?= number_format($reparacion['mano_obra_dispositivos'], 2) ?></span>
                                            <br>
                                            <small class="text-muted">Mis dispositivos</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-success fs-6">
                                                $<?= number_format($reparacion['ganancia_calculada'], 2) ?>
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                <?php if ($tecnico['tipo_comision'] === 'porcentaje'): ?>
                                                    (<?= $tecnico['valor_comision'] ?>% de $<?= number_format($reparacion['mano_obra_dispositivos'], 2) ?>)
                                                <?php else: ?>
                                                    ($<?= number_format($tecnico['valor_comision'], 2) ?> × <?= $reparacion['cantidad_dispositivos'] ?>)
                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <td class="fw-bold">
                                            $<?= number_format($reparacion['total'], 2) ?>
                                            <br>
                                            <small class="text-muted">Total orden</small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                        <p>No hay reparaciones finalizadas aún</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($reparaciones)): ?>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="5" class="text-end">Totales:</th>
                                <th>
                                    <span class="badge bg-success fs-6">
                                        $<?= number_format(array_sum(array_column($reparaciones, 'ganancia_calculada')), 2) ?>
                                    </span>
                                </th>
                                <th>
                                    $<?= number_format(array_sum(array_column($reparaciones, 'total')), 2) ?>
                                </th>
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
            order: [[1, 'desc']], // Ordenar por fecha descendente
            layout: {
                topStart: {
                    buttons: ['pageLength', 'copy', 'excel', 'pdf']
                }
            },
            footerCallback: function (row, data, start, end, display) {
                // No hacer cálculos adicionales ya que los totales están en el HTML
            }
        });
    });
</script>
<?= $this->endSection() ?>
