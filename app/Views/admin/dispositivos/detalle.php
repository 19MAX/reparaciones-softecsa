<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Detalle del Dispositivo<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('admin/dashboard') ?>"><i class="icon-home"></i></a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="<?= base_url('admin/ordenes') ?>">Órdenes</a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Detalle Dispositivo</a></li>
    </ul>
</div>

<div class="row">
    <!-- Información General del Dispositivo -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-mobile-alt me-2"></i>
                        <?= esc($dispositivo['tipo_dispositivo']) ?> 
                        <?= esc($dispositivo['marca']) ?> 
                        <?= esc($dispositivo['modelo']) ?>
                    </h4>
                    <a href="<?= base_url('admin/ordenes') ?>" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Serie/IMEI:</th>
                                <td><span class="badge bg-secondary"><?= esc($dispositivo['serie_imei'] ?: 'No registrado') ?></span></td>
                            </tr>
                            <tr>
                                <th>Estado:</th>
                            </tr>
                            <tr>
                                <th>Bloqueo:</th>
                                <td>
                                    <?php if ($dispositivo['tipo_pass'] !== 'ninguno'): ?>
                                        <i class="fas fa-lock text-warning me-1"></i>
                                        <?= ucfirst($dispositivo['tipo_pass']) ?>
                                    <?php else: ?>
                                        <i class="fas fa-lock-open text-success me-1"></i>Sin bloqueo
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Fecha Ingreso:</th>
                                <td><?= formatear_fecha($dispositivo['created_at']) ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Orden:</th>
                                <td><a href="<?= base_url('admin/ordenes/editar/' . $dispositivo['orden_id']) ?>"><?= esc($dispositivo['codigo_orden']) ?></a></td>
                            </tr>
                            <tr>
                                <th>Cliente:</th>
                                <td><?= esc($dispositivo['cliente_nombre']) ?></td>
                            </tr>
                            <tr>
                                <th>Técnico Asignado:</th>
                                <td>
                                    <?php if ($dispositivo['tecnico_asignado']): ?>
                                        <i class="fas fa-user-cog text-success me-1"></i><?= esc($dispositivo['tecnico_asignado']) ?>
                                    <?php else: ?>
                                        <span class="text-muted"><i class="fas fa-user-slash me-1"></i>Sin asignar</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if (!empty($dispositivo['fecha_estimada_entrega'])): ?>
                            <tr>
                                <th>Fecha Estimada:</th>
                                <td><?= formatear_fecha($dispositivo['fecha_estimada_entrega'], 'solo_fecha') ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <?php if (!empty($dispositivo['diagnostico_cliente'])): ?>
                <div class="alert alert-info">
                    <h6><i class="fas fa-comment-dots me-2"></i>Diagnóstico para Cliente</h6>
                    <p class="mb-0"><?= nl2br(esc($dispositivo['diagnostico_cliente'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if ($dispositivo['tiene_garantia_activa']): ?>
                <div class="alert alert-success">
                    <h6><i class="fas fa-shield-alt me-2"></i>Garantía Activa</h6>
                    <p class="mb-0">Vence el: <?= formatear_fecha($dispositivo['garantia_vence_en'], 'solo_fecha') ?></p>
                    <?php if ($dispositivo['veces_reclamada_garantia'] > 0): ?>
                    <small>Reclamada <?= $dispositivo['veces_reclamada_garantia'] ?> <?= $dispositivo['veces_reclamada_garantia'] == 1 ? 'vez' : 'veces' ?></small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <!-- Problemas Reportados -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Problemas Reportados</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($problemas)): ?>
                    <?php foreach ($problemas as $problema): ?>
                    <div class="card mb-2 <?= $problema['fue_reparado'] ? 'border-success' : 'border-warning' ?>">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0">
                                    <?php if ($problema['fue_reparado']): ?>
                                        <i class="fas fa-check-circle text-success me-1"></i>
                                    <?php else: ?>
                                        <i class="fas fa-tools text-warning me-1"></i>
                                    <?php endif; ?>
                                    <?= esc($problema['problema']) ?>
                                </h6>
                                <span class="badge bg-<?= $problema['prioridad'] === 'alta' ? 'danger' : ($problema['prioridad'] === 'media' ? 'warning' : 'secondary') ?>">
                                    <?= ucfirst($problema['prioridad']) ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($problema['diagnostico_inicial'])): ?>
                            <div class="mb-2">
                                <small class="text-muted"><strong>Cliente reporta:</strong></small>
                                <p class="mb-0 small"><?= nl2br(esc($problema['diagnostico_inicial'])) ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($problema['diagnostico_tecnico'])): ?>
                            <div class="mb-2">
                                <small class="text-muted"><strong>Diagnóstico técnico:</strong></small>
                                <p class="mb-0 small"><?= nl2br(esc($problema['diagnostico_tecnico'])) ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <div>
                                    <?php if ($problema['fue_reparado']): ?>
                                        <span class="badge bg-success">Reparado</span>
                                    <?php elseif ($problema['fue_reparado'] === false): ?>
                                        <span class="badge bg-danger">No reparado</span>
                                        <?php if (!empty($problema['razon_no_reparado'])): ?>
                                            <small class="d-block text-muted"><?= esc($problema['razon_no_reparado']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-warning">En proceso</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($problema['costo_reparacion'])): ?>
                                <div class="text-end">
                                    <strong class="text-success">$<?= number_format($problema['costo_reparacion'], 2) ?></strong>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($problema['es_reparacion_garantia']): ?>
                            <div class="mt-2">
                                <span class="badge bg-info"><i class="fas fa-shield-alt me-1"></i>Reparación por garantía</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center py-3">No hay problemas registrados</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Accesorios y Checklist -->
    <div class="col-md-6">
        <!-- Accesorios -->
        <?php if (!empty($accesorios)): ?>
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-plug me-2"></i>Accesorios Entregados</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Accesorio</th>
                                <th>Estado</th>
                                <th>Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($accesorios as $accesorio): ?>
                            <tr>
                                <td><?= esc($accesorio['accesorio']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $accesorio['estado'] === 'bueno' ? 'success' : ($accesorio['estado'] === 'regular' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($accesorio['estado']) ?>
                                    </span>
                                </td>
                                <td><small><?= esc($accesorio['observacion'] ?: '-') ?></small></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Checklist -->
        <?php if (!empty($checklist)): ?>
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Verificaciones Realizadas</h5>
            </div>
            <div class="card-body">
                <?php foreach ($checklist as $item): ?>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" checked disabled>
                    <label class="form-check-label">
                        <?= esc($item['item']) ?>
                        <?php if (!empty($item['observacion'])): ?>
                        <br><small class="text-muted"><?= esc($item['observacion']) ?></small>
                        <?php endif; ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Historial de Diagnósticos -->
<?php if (!empty($historial_diagnostico)): ?>
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Diagnósticos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Técnico</th>
                                <th>Tiempo (min)</th>
                                <th>¿Encontrado?</th>
                                <th>Detalle</th>
                                <th>Costo Estimado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial_diagnostico as $diagnostico): ?>
                            <tr>
                                <td><?= formatear_fecha($diagnostico['fecha_inicio_diagnostico']) ?></td>
                                <td><i class="fas fa-user-cog me-1"></i><?= esc($diagnostico['tecnico']) ?></td>
                                <td><span class="badge bg-secondary"><?= $diagnostico['tiempo_invertido_minutos'] ?> min</span></td>
                                <td>
                                    <?php if ($diagnostico['diagnostico_encontrado']): ?>
                                        <span class="badge bg-success"><i class="fas fa-check"></i> Sí</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="fas fa-times"></i> No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalDiagnostico<?= $diagnostico['id'] ?>">
                                        Ver detalle
                                    </button>
                                </td>
                                <td>
                                    <?php if (!empty($diagnostico['costo_estimado_reparacion'])): ?>
                                        <strong class="text-success">$<?= number_format($diagnostico['costo_estimado_reparacion'], 2) ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            
                            <!-- Modal para detalle de diagnóstico -->
                            <div class="modal fade" id="modalDiagnostico<?= $diagnostico['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-info text-white">
                                            <h5 class="modal-title">Detalle del Diagnóstico</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <h6>Detalle Técnico:</h6>
                                            <p><?= nl2br(esc($diagnostico['detalle_tecnico'])) ?></p>
                                            
                                            <h6 class="mt-3">Detalle para Cliente:</h6>
                                            <p><?= nl2br(esc($diagnostico['detalle_cliente'])) ?></p>
                                            
                                            <?php if (!empty($diagnostico['observaciones_internas'])): ?>
                                            <h6 class="mt-3">Observaciones Internas:</h6>
                                            <p class="text-muted"><?= nl2br(esc($diagnostico['observaciones_internas'])) ?></p>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($diagnostico['costo_estimado_repuestos'])): ?>
                                            <div class="alert alert-warning mt-3">
                                                <strong>Costo estimado de repuestos:</strong> $<?= number_format($diagnostico['costo_estimado_repuestos'], 2) ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Historial de Cambios de Estado -->
<?php if (!empty($historial_estados)): ?>
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Historial de Estados</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <?php foreach ($historial_estados as $index => $estado): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-<?= $index === 0 ? 'primary' : 'secondary' ?>"></div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">
                                    <?php if ($estado['estado_anterior']): ?>
                                        <?= esc($estado['estado_anterior']) ?> 
                                        <i class="fas fa-arrow-right mx-2"></i>
                                    <?php endif; ?>
                                    <strong><?= esc($estado['estado_nuevo']) ?></strong>
                                </h6>
                                <small class="text-muted"><?= formatear_fecha($estado['created_at']) ?></small>
                            </div>
                            <p class="mb-0"><i class="fas fa-user me-1"></i><?= esc($estado['usuario']) ?></p>
                            <?php if (!empty($estado['comentario'])): ?>
                            <p class="text-muted small mb-0"><?= nl2br(esc($estado['comentario'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Garantías -->
<?php if (!empty($garantias)): ?>
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Garantías</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Vencimiento</th>
                                <th>Estado</th>
                                <th>Problema Cubierto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($garantias as $garantia): ?>
                            <tr>
                                <td><?= esc($garantia['tipo_garantia']) ?></td>
                                <td><?= formatear_fecha($garantia['fecha_inicio'], 'solo_fecha') ?></td>
                                <td><?= formatear_fecha($garantia['fecha_vencimiento'], 'solo_fecha') ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $garantia['estado'] === 'activa' ? 'success' : 
                                        ($garantia['estado'] === 'vencida' ? 'secondary' : 
                                        ($garantia['estado'] === 'utilizada' ? 'info' : 'danger')) 
                                    ?>">
                                        <?= ucfirst($garantia['estado']) ?>
                                    </span>
                                </td>
                                <td><?= esc($garantia['problema_cubierto']) ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalGarantia<?= $garantia['id'] ?>">
                                        Ver detalle
                                    </button>
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
<?php endif; ?>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 20px;
    height: calc(100% - 10px);
    width: 2px;
    background: #ddd;
}

.timeline-marker {
    position: absolute;
    left: -26px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}
</style>

<?= $this->endSection() ?>