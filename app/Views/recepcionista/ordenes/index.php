<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?? 'Órdenes de Trabajo' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('recepcionista/dashboard') ?>">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Taller</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#">Órdenes</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title"><i class="fas fa-toolbox me-2"></i>Listado de Órdenes</h4>

                    <a href="<?= base_url('recepcionista/ordenes/crear') ?>" class="btn btn-success btn-round ms-auto">
                        <i class="fa fa-plus me-2"></i> Nueva Orden
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <form method="GET" action="<?= base_url('recepcionista/ordenes') ?>" class="row g-2">
                            <div class="col-auto">
                                <input type="date" name="fecha_desde" class="form-control form-control-sm" value="<?= $fecha_desde ?? '' ?>" placeholder="Desde">
                            </div>
                            <div class="col-auto">
                                <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="<?= $fecha_hasta ?? '' ?>" placeholder="Hasta">
                            </div>
                            <div class="col-auto">
                                <select name="estado" class="form-select form-select-sm">
                                    <option value="">Todos los estados</option>
                                    <option value="recibida" <?= ($estado ?? '') === 'recibida' ? 'selected' : '' ?>>Recibida</option>
                                    <option value="en_proceso" <?= ($estado ?? '') === 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                                    <option value="listo" <?= ($estado ?? '') === 'listo' ? 'selected' : '' ?>>Listo</option>
                                    <option value="entregada" <?= ($estado ?? '') === 'entregada' ? 'selected' : '' ?>>Entregada</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filtrar</button>
                                <a href="<?= base_url('recepcionista/ordenes') ?>" class="btn btn-secondary btn-sm">Limpiar</a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="ordenes-datatables" class="table table-bordered ">
                        <thead>
                            <tr>
                                <th># Orden</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Dispositivos</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th style="width: 10%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php if (!empty($ordenes)): ?>
                                <?php foreach ($ordenes as $orden): ?>
                                    <tr>
                                        <td class="fw-bold text-primary">
                                            <?= esc($orden['codigo_orden']) ?>
                                        </td>
                                        <td>
                                            <?= formatear_fecha($orden['created_at'], 'solo_fecha') ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('H:i', strtotime($orden['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-bold">
                                                <?= esc($orden['nombres']) ?>         <?= esc($orden['apellidos']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 200px;"
                                                title="<?= esc($orden['equipos_resumen']) ?>">
                                                <?= esc($orden['equipos_resumen']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?=get_badge_urgencia($orden)?>
                                        </td>
                                        <td>
                                            <?=get_badge_estado_orden($orden['estado'])?>
                                        </td>
                                        <td>
                                            <div class="form-button-action">
                                                <div class="dropdown d-inline">
                                                    <a class="btn btn-link btn-secondary dropdown-toggle" href="#" role="button"
                                                        data-bs-toggle="dropdown" aria-expanded="false" title="Imprimir">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="<?= base_url('recepcionista/ordenes/imprimir/' . $orden['id'] . '/carta') ?>"
                                                                target="_blank">
                                                                Carta
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="<?= base_url('recepcionista/ordenes/imprimir/' . $orden['id'] . '/ticket') ?>"
                                                                target="_blank">
                                                                Ticket
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="<?= base_url('recepcionista/ordenes/imprimir/' . $orden['id']) ?>"
                                                                target="_blank">
                                                                Completo
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <!-- Enlace para ver el seguimiento público: -->
                                                <a href="<?= base_url('consulta/orden/' . $orden['codigo_orden']) ?>"
                                                    target="_blank" class="btn btn-link btn-info" data-bs-toggle="tooltip"
                                                    title="Ver Seguimiento Público">
                                                    <i class="fas fa-truck-moving"></i>
                                                </a>

                                                <a href="<?= base_url('recepcionista/ordenes/ver/' . $orden['id']) ?>"
                                                    class="btn btn-link btn-primary" data-bs-toggle="tooltip"
                                                    title="Ver Detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
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

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function () {
        // Inicializar DataTables con ordenamiento por la primera columna (ID) descendente
        $('#ordenes-datatables').DataTable({
            scrollX: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json',
            },
            order: [[1, 'desc']],
            layout: {
                topStart: {
                    buttons: ['pageLength', 'copy', 'excel', 'pdf', 'colvis']
                }
            }
        });
    });
</script>
<?= $this->endSection() ?>