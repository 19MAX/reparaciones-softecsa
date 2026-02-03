<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Consultar Estado<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
    <div>
        <h3 class="fw-bold mb-3">Consultar Estado de Orden</h3>
        <h6 class="op-7 mb-2">Buscar por código de orden, cédula o IMEI</h6>
    </div>
    <div class="ms-md-auto py-2 py-md-0">
        <a href="<?= base_url('recepcionista') ?>" class="btn btn-secondary btn-round">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="<?= base_url('recepcionista/consultar-estado') ?>">
                    <div class="row">
                        <div class="col-md-10">
                            <div class="form-group">
                                <label>Buscar</label>
                                <input type="text" class="form-control form-control-lg" 
                                       name="search" 
                                       value="<?= esc($search ?? '') ?>"
                                       placeholder="Código de orden, Cédula del cliente o IMEI del dispositivo">
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (isset($ordenes) && !empty($ordenes)): ?>
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Resultados de la Búsqueda</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Teléfono</th>
                                <th>Dispositivos</th>
                                <th>Estado Global</th>
                                <th>Urgencia</th>
                                <th>Fecha Estimada</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ordenes as $orden): ?>
                                <tr>
                                    <td><strong><?= $orden['codigo_orden'] ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($orden['fecha_creacion'])) ?></td>
                                    <td><?= $orden['cliente_nombre'] ?></td>
                                    <td><?= $orden['cliente_telefono'] ?></td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            <?= $orden['total_dispositivos'] ?> dispositivo(s)
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = 'secondary';
                                        switch ($orden['estado_global']) {
                                            case 'pendiente':
                                                $badgeClass = 'warning';
                                                break;
                                            case 'en_diagnostico':
                                                $badgeClass = 'info';
                                                break;
                                            case 'en_reparacion':
                                                $badgeClass = 'primary';
                                                break;
                                            case 'esperando_autorizacion':
                                                $badgeClass = 'warning';
                                                break;
                                            case 'reparado':
                                                $badgeClass = 'success';
                                                break;
                                            case 'entregado':
                                                $badgeClass = 'dark';
                                                break;
                                        }
                                        ?>
                                        <span class="badge badge-<?= $badgeClass ?>">
                                            <?= ucfirst(str_replace('_', ' ', $orden['estado_global'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $orden['urgencia_color'] ?? 'secondary' ?>">
                                            <?= $orden['urgencia_nombre'] ?? 'N/A' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $orden['fecha_estimada_entrega'] ? date('d/m/Y', strtotime($orden['fecha_estimada_entrega'])) : 'Por definir' ?>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('recepcionista/ver-orden/' . $orden['id']) ?>" 
                                           class="btn btn-sm btn-info" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= base_url('recepcionista/imprimir-orden/' . $orden['id']) ?>" 
                                           class="btn btn-sm btn-secondary" title="Imprimir" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
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
<?php elseif (isset($search) && !empty($search)): ?>
<div class="row mt-3">
    <div class="col-md-12">
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle"></i> No se encontraron resultados</h5>
            <p>No se encontraron órdenes con el criterio de búsqueda: <strong><?= esc($search) ?></strong></p>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>