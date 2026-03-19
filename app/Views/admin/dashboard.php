<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
Panel de Control
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
    <div>
        <h3 class="fw-bold mb-3">Dashboard de Administración</h3>
        <h6 class="op-7 mb-2">Resumen general del taller y estadísticas operativas</h6>
    </div>
    <div class="ms-md-auto py-2 py-md-0">
        <a href="<?= base_url('admin/clientes') ?>" class="btn btn-label-info btn-round me-2">Gestionar Clientes</a>
        <a href="<?= base_url('admin/ordenes/crear') ?>" class="btn btn-primary btn-round">Nueva Orden</a>
    </div>
</div>

<!-- ── TARJETAS DE RESUMEN ───────────────────────── -->
<div class="row">
    <!-- RECAUDADO -->
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-secondary bubble-shadow-small">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Ingresos Totales</p>
                            <h4 class="card-title">$<?= number_format($stats['recaudado'], 2) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- MANO DE OBRA -->
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-primary bubble-shadow-small">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Mano de Obra</p>
                            <h4 class="card-title">$<?= number_format($stats['totalManoObra'], 2) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- REPUESTOS -->
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-warning bubble-shadow-small">
                            <i class="fas fa-box-open"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Gasto Repuestos</p>
                            <h4 class="card-title">$<?= number_format($stats['totalRepuestos'], 2) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- REPARADOS -->
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Reparados</p>
                            <h4 class="card-title"><?= number_format($stats['reparados']) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- GRÁFICO DE INGRESOS (7 DÍAS) -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <div class="card-head-row">
                    <div class="card-title">Ingresos de la Última Semana</div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="min-height: 300px">
                    <canvas id="incomeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- TOP TÉCNICO Y COMISIONES -->
    <div class="col-md-4">
        <div class="card card-primary bg-primary-gradient">
            <div class="card-body">
                <h5 class="mt-3 b-b1 pb-2 mb-4 fw-bold">Top Técnico del Taller</h5>
                <?php if ($stats['topTecnico']): ?>
                    <h1 class="mb-2 fw-bold"><?= esc($stats['topTecnico']['nombre']) ?></h1>
                    <p class="op-7">Con <?= $stats['topTecnico']['total'] ?> equipos listos/entregados</p>
                <?php else: ?>
                    <p class="op-7">Sin actividad registrada aún.</p>
                <?php endif; ?>

                <div class="separator-dashed"></div>

                <h5 class="mt-3 b-b1 pb-2 mb-3 fw-bold">Comisiones del Mes</h5>
                <ul class="list-unstyled">
                    <?php if (!empty($stats['comisionesMes'])): ?>
                        <?php foreach ($stats['comisionesMes'] as $com): ?>
                            <li class="d-flex justify-content-between pb-1 pt-1 border-bottom border-white border-opacity-10">
                                <small><?= esc($com['nombre']) ?>         <?= esc($com['apellido']) ?></small>
                                <span>$<?= number_format($com['total_comision'], 2) ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="op-7 text-white"><small>Sin comisiones este mes.</small></li>
                    <?php endif; ?>
                </ul>

                <div class="separator-dashed"></div>
                <div class="d-flex justify-content-between mt-3">
                    <h5 class="fw-bold">Gasto Repuestos (Mes)</h5>
                    <h5 class="fw-bold">$<?= number_format($stats['repuestosMes'], 2) ?></h5>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- ÓRDENES RECIENTES -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Órdenes Recientes</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">Código</th>
                                <th scope="col">Cliente</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['ordenesRecientes'] as $ord): ?>
                                <tr>
                                    <td>
                                        <div class="dropdown">
                                            <a class="fw-bold dropdown-toggle" href="#" role="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <?= esc($ord['numero_orden']) ?>
                                            </a>

                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="<?= base_url('admin/ordenes/imprimir/' . $ord['id'] . '/carta') ?>"
                                                        target="_blank">
                                                        Carta
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="<?= base_url('admin/ordenes/imprimir/' . $ord['id'] . '/ticket') ?>"
                                                        target="_blank">
                                                        Ticket
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="<?= base_url('admin/ordenes/imprimir/' . $ord['id']) ?>"
                                                        target="_blank">
                                                        Completo
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td><?= esc($ord['nombres']) ?>     <?= esc($ord['apellidos']) ?></td>
                                    <td>
                                        <span
                                            class="badge badge-<?= $ord['estado'] === 'entregado' ? 'success' : ($ord['estado'] === 'cancelado' ? 'danger' : 'info') ?>">
                                            <?= ucfirst($ord['estado']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?= date('d/m/y', strtotime($ord['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- DISPOSITIVOS MÁS FRECUENTES -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Tipos de Equipos más Reparados</div>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['tiposMasReparados'])): ?>
                    <div class="chart-container">
                        <canvas id="deviceTypeChart"></canvas>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">No hay datos disponibles.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ── GRÁFICO DE INGRESOS (LINE) ─────────────────────────
    const incomeCtx = document.getElementById('incomeChart').getContext('2d');
    const incomeData = <?= json_encode($stats['ventasSieteDias']) ?>;

    new Chart(incomeCtx, {
        type: 'line',
        data: {
            labels: incomeData.map(d => d.dia),
            datasets: [{
                label: 'Ingresos ($)',
                data: incomeData.map(d => d.monto),
                borderColor: '#1d7af3',
                backgroundColor: 'rgba(29, 122, 243, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // ── GRÁFICO DE TIPOS (PIE/DOUGHNUT) ─────────────────────
    <?php if (!empty($stats['tiposMasReparados'])): ?>
        const typeCtx = document.getElementById('deviceTypeChart').getContext('2d');
        const typeData = <?= json_encode($stats['tiposMasReparados']) ?>;

        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: typeData.map(d => d.nombre),
                datasets: [{
                    data: typeData.map(d => d.total),
                    backgroundColor: ['#1d7af3', '#f3545d', '#fdaf4b', '#59d05d', '#177dff']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    <?php endif; ?>
</script>
<?= $this->endSection() ?>