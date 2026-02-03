<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Mis Dispositivos Asignados
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('tecnico/dashboard') ?>"><i class="icon-home"></i></a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Mis Dispositivos</a></li>
    </ul>
</div>

<!-- Tarjetas de Resumen -->
<div class="row mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-warning bubble-shadow-small">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Pendientes</p>
                            <h4 class="card-title">
                                <?= $resumen['pendientes'] ?? 0 ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                            <i class="fas fa-wrench"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">En Proceso</p>
                            <h4 class="card-title">
                                <?= $resumen['en_proceso'] ?? 0 ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                            <p class="card-category">Completados Hoy</p>
                            <h4 class="card-title">
                                <?= $resumen['completados_hoy'] ?? 0 ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-primary bubble-shadow-small">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Comisiones Pendientes</p>
                            <h4 class="card-title">$
                                <?= number_format($resumen['comisiones_pendientes'] ?? 0, 2) ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Dispositivos -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title"><i class="fas fa-mobile-alt me-2"></i>Dispositivos Asignados</h4>
                    <div class="ms-auto">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="filtro_estado" id="todos" value="" checked>
                            <label class="btn btn-outline-primary btn-sm" for="todos">Todos</label>

                            <input type="radio" class="btn-check" name="filtro_estado" id="pendientes"
                                value="pendiente">
                            <label class="btn btn-outline-warning btn-sm" for="pendientes">Pendientes</label>

                            <input type="radio" class="btn-check" name="filtro_estado" id="en_revision"
                                value="en_revision">
                            <label class="btn btn-outline-info btn-sm" for="en_revision">En Revisión</label>

                            <input type="radio" class="btn-check" name="filtro_estado" id="diagnosticados"
                                value="diagnosticado">
                            <label class="btn btn-outline-success btn-sm" for="diagnosticados">Diagnosticados</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dispositivos-table" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Dispositivo</th>
                                <th>Cliente</th>
                                <th>Problemas</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Fecha Ingreso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dispositivos)): ?>
                                <?php foreach ($dispositivos as $dispositivo): ?>
                                    <tr data-estado="<?= $dispositivo['estado_diagnostico'] ?>">
                                        <td>
                                            <a href="<?= base_url('tecnico/ordenes/ver/' . $dispositivo['orden_id']) ?>"
                                                class="text-primary fw-bold">
                                                <?= esc($dispositivo['codigo_orden']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>
                                                    <?= esc($dispositivo['tipo_dispositivo']) ?>
                                                </strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?= esc($dispositivo['marca']) ?>
                                                    <?= esc($dispositivo['modelo']) ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <?= esc($dispositivo['cliente_nombre']) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $totalProblemas = $dispositivo['total_problemas'] ?? 0;
                                            $problemasReparados = $dispositivo['problemas_reparados'] ?? 0;
                                            ?>
                                            <?php if ($totalProblemas > 0): ?>
                                                <span
                                                    class="badge bg-<?= $problemasReparados == $totalProblemas ? 'success' : 'warning' ?>">
                                                    <?= $problemasReparados ?>/
                                                    <?= $totalProblemas ?> resueltos
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Sin problemas</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $estadoBadges = [
                                                'pendiente' => '<span class="badge bg-secondary"><i class="fas fa-clock me-1"></i>Pendiente</span>',
                                                'en_revision' => '<span class="badge bg-info"><i class="fas fa-search me-1"></i>En Revisión</span>',
                                                'diagnosticado' => '<span class="badge bg-primary"><i class="fas fa-check-circle me-1"></i>Diagnosticado</span>',
                                                'sin_diagnostico' => '<span class="badge bg-warning"><i class="fas fa-times-circle me-1"></i>Sin Diagnóstico</span>',
                                            ];
                                            echo $estadoBadges[$dispositivo['estado_diagnostico']] ?? $estadoBadges['pendiente'];
                                            ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($dispositivo['prioridad_nombre'])): ?>
                                                <span class="badge"
                                                    style="background-color: <?= $dispositivo['prioridad_color'] ?>">
                                                    <?= esc($dispositivo['prioridad_nombre']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Normal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($dispositivo['created_at'])) ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('H:i', strtotime($dispositivo['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <a href="<?= base_url('tecnico/dispositivos/trabajar/' . $dispositivo['id']) ?>"
                                                class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                                title="Trabajar en dispositivo">
                                                <i class="fas fa-tools"></i> Trabajar
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function () {
        const table = $('#dispositivos-table').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json',
            },
            order: [[6, 'asc']], // Ordenar por fecha de ingreso
            pageLength: 25,
        });

        // Filtrar por estado
        $('input[name="filtro_estado"]').on('change', function () {
            const estado = $(this).val();
            if (estado === '') {
                table.column(4).search('').draw();
            } else {
                table.column(4).search(estado).draw();
            }
        });

        // Tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
<?= $this->endSection() ?>