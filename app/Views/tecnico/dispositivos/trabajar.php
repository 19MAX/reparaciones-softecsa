<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Trabajar Dispositivo
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('tecnico/dashboard') ?>"><i class="icon-home"></i></a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item">
            <a href="<?= base_url('tecnico/dispositivos') ?>">Dispositivos</a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Trabajar</a></li>
    </ul>
</div>

<div class="row">
    <!-- Información del Dispositivo -->
    <div class="col-lg-4">
        <div class="card card-primary">
            <div class="card-header">
                <h4 class="card-title text-white">
                    <i class="fas fa-mobile-alt me-2"></i>Información del Dispositivo
                </h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h5>
                        <?= esc($dispositivo['tipo_dispositivo']) ?>
                    </h5>
                    <p class="text-muted mb-1">
                        <?= esc($dispositivo['marca']) ?>
                        <?= esc($dispositivo['modelo']) ?>
                    </p>
                    <?php if (!empty($dispositivo['serie_imei'])): ?>
                        <small><strong>Serie/IMEI:</strong>
                            <?= esc($dispositivo['serie_imei']) ?>
                        </small>
                    <?php endif; ?>
                </div>

                <hr>

                <div class="mb-2">
                    <strong>Orden:</strong>
                    <a href="<?= base_url('tecnico/ordenes/ver/' . ($dispositivo['orden_id'] ?? null)) ?>">
                        <?= esc($dispositivo['codigo_orden']) ?>
                    </a>
                </div>
                <div class="mb-2">
                    <strong>Cliente:</strong>
                    <?= esc($dispositivo['cliente_nombre']) ?>
                </div>
                <div class="mb-2">
                    <strong>Estado Actual:</strong><br>
                    <span class="badge bg-<?=
                        $dispositivo['estado_diagnostico'] === 'pendiente' ? 'secondary' :
                        ($dispositivo['estado_diagnostico'] === 'en_revision' ? 'info' :
                            ($dispositivo['estado_diagnostico'] === 'diagnosticado' ? 'primary' : 'warning'))
                        ?>">
                        <?= ucfirst(str_replace('_', ' ', $dispositivo['estado_diagnostico'])) ?>
                    </span>
                </div>
                <div class="mb-2">
                    <strong>Fecha Ingreso:</strong>
                    <?= formatear_fecha($dispositivo['created_at']) ?>
                </div>

                <?php if ($dispositivo['tipo_pass'] !== 'ninguno'): ?>
                    <div class="alert alert-warning mt-3">
                        <strong><i class="fas fa-lock me-1"></i>Dispositivo Bloqueado</strong><br>
                        <small>Tipo:
                            <?= ucfirst($dispositivo['tipo_pass']) ?>
                        </small>
                        <?php if (!empty($dispositivo['pass_code'])): ?>
                            <br><small>Código:
                                <?= esc($dispositivo['pass_code']) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($dispositivo['tiene_garantia_activa']): ?>
                    <div class="alert alert-success">
                        <strong><i class="fas fa-shield-alt me-1"></i>Con Garantía Activa</strong><br>
                        <small>Vence:
                            <?= formatear_fecha($dispositivo['garantia_vence_en'], 'solo_fecha') ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Accesorios -->
        <?php if (!empty($accesorios)): ?>
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-plug me-2"></i>Accesorios</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($accesorios as $accesorio): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>
                                <?= esc($accesorio['accesorio']) ?>
                            </span>
                            <span class="badge bg-<?=
                                $accesorio['estado'] === 'bueno' ? 'success' :
                                ($accesorio['estado'] === 'regular' ? 'warning' : 'danger')
                                ?>">
                                <?= ucfirst($accesorio['estado']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Panel de Trabajo -->
    <div class="col-lg-8">
        <!-- Tabs de Trabajo -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-pills nav-secondary" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-diagnostico" data-bs-toggle="pill" href="#diagnostico"
                            role="tab">
                            <i class="fas fa-stethoscope me-1"></i>Diagnóstico
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-problemas" data-bs-toggle="pill" href="#problemas" role="tab">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Problemas (
                            <?= count($problemas) ?>)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-presupuesto" data-bs-toggle="pill" href="#presupuesto" role="tab">
                            <i class="fas fa-calculator me-1"></i>Presupuesto
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-historial" data-bs-toggle="pill" href="#historial" role="tab">
                            <i class="fas fa-history me-1"></i>Historial
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="pills-tabContent">
                    <!-- Tab Diagnóstico -->
                    <div class="tab-pane fade show active" id="diagnostico" role="tabpanel">
                        <h5 class="mb-4">Realizar Diagnóstico</h5>

                        <?php if ($dispositivo['estado_diagnostico'] === 'pendiente'): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Inicia el diagnóstico para registrar el tiempo invertido.
                            </div>
                            <button type="button" class="btn btn-primary btn-lg" id="btnIniciarDiagnostico">
                                <i class="fas fa-play me-2"></i>Iniciar Diagnóstico
                            </button>
                        <?php elseif ($dispositivo['estado_diagnostico'] === 'en_revision'): ?>

                            <form id="formDiagnostico" action="<?= base_url('tecnico/dispositivos/guardar-diagnostico') ?>"
                                method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="dispositivo_id" value="<?= $dispositivo['id'] ?>">

                                <div class="mb-3">
                                    <label class="form-label">¿Se encontró el problema?</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="diagnostico_encontrado"
                                            id="diagnostico_si" value="1" required>
                                        <label class="form-check-label" for="diagnostico_si">
                                            <i class="fas fa-check-circle text-success me-1"></i>Sí, encontré el problema
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="diagnostico_encontrado"
                                            id="diagnostico_no" value="0">
                                        <label class="form-check-label" for="diagnostico_no">
                                            <i class="fas fa-times-circle text-danger me-1"></i>No se pudo diagnosticar
                                        </label>
                                    </div>
                                </div>

                                <div id="diagnostico_encontrado_fields" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Diagnóstico Técnico (Interno)</label>
                                        <textarea class="form-control" name="detalle_tecnico" rows="4"
                                            placeholder="Describe detalladamente el problema encontrado, componentes afectados, causas..."></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Diagnóstico para Cliente</label>
                                        <textarea class="form-control" name="detalle_cliente" id="detalle_cliente" rows="3"
                                            placeholder="Explica el problema en términos simples para el cliente..."></textarea>
                                        <small class="text-muted">Este texto se mostrará al cliente</small>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Costo Estimado de Reparación</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control"
                                                        name="costo_estimado_reparacion" step="0.01" min="0"
                                                        placeholder="0.00">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Costo Estimado de Repuestos</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control"
                                                        name="costo_estimado_repuestos" step="0.01" min="0"
                                                        placeholder="0.00">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Observaciones Internas (Opcional)</label>
                                        <textarea class="form-control" name="observaciones_internas" rows="2"
                                            placeholder="Notas adicionales solo para el equipo técnico..."></textarea>
                                    </div>
                                </div>

                                <div id="diagnostico_no_encontrado_fields" style="display: none;">
                                    <div class="mb-3">
                                        <label class="form-label">Razón por la que no se pudo diagnosticar</label>
                                        <textarea class="form-control" name="razon_no_diagnostico" rows="3"
                                            placeholder="Explica por qué no fue posible diagnosticar el dispositivo..."></textarea>
                                    </div>

                                    <div class="alert alert-warning">
                                        <strong>Nota:</strong> Si no se puede diagnosticar, puedes solicitar cobro por el
                                        tiempo invertido.
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="solicitar_cobro_diagnostico"
                                            id="solicitar_cobro">
                                        <label class="form-check-label" for="solicitar_cobro">
                                            Solicitar cobro por diagnóstico
                                        </label>
                                    </div>

                                    <div id="cobro_diagnostico_fields" style="display: none;" class="mt-3">
                                        <div class="mb-3">
                                            <label class="form-label">Tiempo Invertido (minutos)</label>
                                            <input type="number" class="form-control" name="tiempo_cobro"
                                                placeholder="Ej: 60" min="1">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Valor Solicitado</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" name="valor_cobro" step="0.01"
                                                    placeholder="0.00">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Justificación del Cobro</label>
                                            <textarea class="form-control" name="justificacion_cobro" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" id="btnFinalizarSinGuardar">
                                        <i class="fas fa-stop me-2"></i>Finalizar Sin Guardar
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>Guardar Diagnóstico
                                    </button>
                                </div>
                            </form>

                        <?php else: ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Diagnóstico completado. Puedes actualizar los problemas y el presupuesto en las otras
                                pestañas.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab Problemas -->
                    <div class="tab-pane fade" id="problemas" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Problemas Reportados</h5>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                data-bs-target="#modalAgregarProblema">
                                <i class="fas fa-plus me-1"></i>Agregar Problema
                            </button>
                        </div>

                        <?php if (!empty($problemas)): ?>
                            <?php foreach ($problemas as $problema): ?>
                                <div class="card mb-3 border-<?= $problema['fue_reparado'] ? 'success' : 'warning' ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6>
                                                    <?php if ($problema['fue_reparado']): ?>
                                                        <i class="fas fa-check-circle text-success me-1"></i>
                                                    <?php elseif ($problema['fue_reparado'] === false): ?>
                                                        <i class="fas fa-times-circle text-danger me-1"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-tools text-warning me-1"></i>
                                                    <?php endif; ?>
                                                    <?= esc($problema['problema']) ?>
                                                </h6>
                                                <span
                                                    class="badge bg-<?= $problema['prioridad'] === 'alta' ? 'danger' : ($problema['prioridad'] === 'media' ? 'warning' : 'secondary') ?>">
                                                    <?= ucfirst($problema['prioridad']) ?>
                                                </span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-info"
                                                onclick="editarProblema(<?= $problema['id'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>

                                        <?php if (!empty($problema['diagnostico_inicial'])): ?>
                                            <div class="mt-2">
                                                <small class="text-muted"><strong>Cliente reporta:</strong></small>
                                                <p class="mb-1 small">
                                                    <?= nl2br(esc($problema['diagnostico_inicial'])) ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($problema['diagnostico_tecnico'])): ?>
                                            <div class="mt-2">
                                                <small class="text-muted"><strong>Diagnóstico técnico:</strong></small>
                                                <p class="mb-1 small">
                                                    <?= nl2br(esc($problema['diagnostico_tecnico'])) ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>

                                        <form action="<?= base_url('tecnico/dispositivos/actualizar-problema') ?>" method="POST"
                                            class="mt-3">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="problema_id" value="<?= $problema['id'] ?>">
                                            <input type="hidden" name="dispositivo_id" value="<?= $dispositivo['id'] ?>">

                                            <div class="row g-2">
                                                <div class="col-md-8">
                                                    <select name="estado_reparacion" class="form-select form-select-sm"
                                                        required>
                                                        <option value="">-- Estado de Reparación --</option>
                                                        <option value="reparado" <?= $problema['fue_reparado'] === true ? 'selected' : '' ?>>
                                                            Reparado
                                                        </option>
                                                        <option value="no_reparado" <?= $problema['fue_reparado'] === false ? 'selected' : '' ?>>
                                                            No se pudo reparar
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="number" name="costo_reparacion"
                                                        class="form-control form-control-sm" placeholder="Costo $" step="0.01"
                                                        value="<?= $problema['costo_reparacion'] ?>">
                                                </div>
                                            </div>

                                            <div class="mt-2">
                                                <textarea name="diagnostico_tecnico" class="form-control form-control-sm"
                                                    rows="2"
                                                    placeholder="Actualizar diagnóstico técnico..."><?= esc($problema['diagnostico_tecnico']) ?></textarea>
                                            </div>

                                            <div class="mt-2">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-save me-1"></i>Actualizar
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-info">
                                No hay problemas registrados. Agrega uno para empezar.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab Presupuesto -->
                    <div class="tab-pane fade" id="presupuesto" role="tabpanel">
                        <h5 class="mb-4">Presupuesto y Autorización</h5>

                        <form action="<?= base_url('tecnico/dispositivos/generar-presupuesto') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="dispositivo_id" value="<?= $dispositivo['id'] ?>">

                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Resumen de Costos</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <td><strong>Mano de Obra:</strong></td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" class="form-control" name="costo_mano_obra"
                                                            id="costo_mano_obra" step="0.01" min="0"
                                                            value="<?= $dispositivo['costo_diagnostico_estimado'] ?? 0 ?>"
                                                            placeholder="0.00">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Repuestos:</strong></td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" class="form-control" name="costo_repuestos"
                                                            id="costo_repuestos" step="0.01" min="0" placeholder="0.00">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="table-active">
                                                <td><strong>TOTAL:</strong></td>
                                                <td>
                                                    <h5 class="mb-0 text-success" id="total_presupuesto">$0.00</h5>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Observaciones para el Cliente</label>
                                <textarea class="form-control" name="observaciones_cliente" rows="3"
                                    placeholder="Información adicional sobre el presupuesto..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Fecha Estimada de Entrega</label>
                                <input type="date" class="form-control" name="fecha_estimada_entrega"
                                    value="<?= $dispositivo['fecha_estimada_entrega'] ?>" min="<?= date('Y-m-d') ?>">
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="requiere_cotizacion"
                                    id="requiere_cotizacion" <?= $dispositivo['requiere_cotizacion'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="requiere_cotizacion">
                                    Requiere cotización de repuestos externos
                                </label>
                            </div>

                            <hr>

                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane me-2"></i>Enviar Presupuesto al Cliente
                            </button>
                        </form>

                        <?php if ($dispositivo['cliente_autoriza_reparacion'] === true): ?>
                            <div class="alert alert-success mt-3">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Cliente autorizó la reparación</strong>
                                <br>Fecha:
                                <?= formatear_fecha($dispositivo['fecha_respuesta_cliente']) ?>
                            </div>

                            <div class="mt-3">
                                <h6>Registrar Garantía</h6>
                                <form action="<?= base_url('tecnico/dispositivos/registrar-garantia') ?>" method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="dispositivo_id" value="<?= $dispositivo['id'] ?>">

                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <select name="tipo_garantia_id" class="form-select" required>
                                                <option value="">-- Tipo de Garantía --</option>
                                                <?php if (!empty($tipos_garantia)): ?>
                                                    <?php foreach ($tipos_garantia as $tipo): ?>
                                                        <option value="<?= $tipo['id'] ?>">
                                                            <?= esc($tipo['nombre']) ?> (
                                                            <?= $tipo['dias_garantia'] ?> días)
                                                        </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <select name="problema_reparado_id" class="form-select" required>
                                                <option value="">-- Problema Cubierto --</option>
                                                <?php foreach ($problemas as $problema): ?>
                                                    <?php if ($problema['fue_reparado']): ?>
                                                        <option value="<?= $problema['id'] ?>">
                                                            <?= esc($problema['problema']) ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-shield-alt me-1"></i>Registrar Garantía
                                        </button>
                                    </div>
                                </form>
                            </div>

                        <?php elseif ($dispositivo['cliente_autoriza_reparacion'] === false): ?>
                            <div class="alert alert-danger mt-3">
                                <i class="fas fa-times-circle me-2"></i>
                                <strong>Cliente rechazó la reparación</strong>
                                <?php if (!empty($dispositivo['razon_rechazo'])): ?>
                                    <br>Razón:
                                    <?= esc($dispositivo['razon_rechazo']) ?>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-clock me-2"></i>
                                Esperando respuesta del cliente...
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab Historial -->
                    <div class="tab-pane fade" id="historial" role="tabpanel">
                        <h5 class="mb-4">Historial de Actividades</h5>

                        <?php if (!empty($historial_completo)): ?>
                            <div class="timeline">
                                <?php foreach ($historial_completo as $index => $evento): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-<?= $index === 0 ? 'primary' : 'secondary' ?>"></div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="mb-1">
                                                    <?php if ($evento['tipo'] === 'diagnostico'): ?>
                                                        <i class="fas fa-stethoscope me-1"></i>Diagnóstico Realizado
                                                    <?php elseif ($evento['tipo'] === 'estado'): ?>
                                                        <i class="fas fa-exchange-alt me-1"></i>Cambio de Estado
                                                    <?php elseif ($evento['tipo'] === 'problema'): ?>
                                                        <i class="fas fa-tools me-1"></i>Actualización de Problema
                                                    <?php endif; ?>
                                                </h6>
                                                <small class="text-muted">
                                                    <?= formatear_fecha($evento['fecha']) ?>
                                                </small>
                                            </div>
                                            <p class="mb-1"><strong>
                                                    <?= esc($evento['usuario']) ?>
                                                </strong></p>
                                            <p class="text-muted small mb-0">
                                                <?= nl2br(esc($evento['detalle'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No hay historial registrado.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar Problema -->
<div class="modal fade" id="modalAgregarProblema" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Problema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('tecnico/dispositivos/agregar-problema') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="dispositivo_id" value="<?= $dispositivo['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Problema</label>
                        <select name="problema_comun_id" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            <?php if (!empty($problemas_comunes)): ?>
                                <?php foreach ($problemas_comunes as $pc): ?>
                                    <option value="<?= $pc['id'] ?>">
                                        <?= esc($pc['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prioridad</label>
                        <select name="prioridad" class="form-select" required>
                            <option value="baja">Baja</option>
                            <option value="media" selected>Media</option>
                            <option value="alta">Alta</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Diagnóstico Técnico</label>
                        <textarea name="diagnostico_tecnico" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </div>
            </form>
        </div>
    </div>
</div>

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

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        // Mostrar/ocultar campos según diagnóstico encontrado
        // Reemplaza tu función actual por esta:
        $('input[name="diagnostico_encontrado"]').on('change', function () {
            const detalleCliente = $('textarea[name="detalle_cliente"]');
            const razonNoDiagnostico = $('textarea[name="razon_no_diagnostico"]');

            if ($(this).val() === '1') {
                $('#diagnostico_encontrado_fields').show();
                $('#diagnostico_no_encontrado_fields').hide();

                // Aplicar requeridos a los campos visibles
                detalleCliente.prop('required', true);
                razonNoDiagnostico.prop('required', false);
            } else {
                $('#diagnostico_encontrado_fields').hide();
                $('#diagnostico_no_encontrado_fields').show();

                // Quitar requeridos a los campos ocultos
                detalleCliente.prop('required', false);
                razonNoDiagnostico.prop('required', true); // Opcional, si quieres obligar a dar una razón
            }
        });

        // Mostrar campos de cobro por diagnóstico
        $('#solicitar_cobro').on('change', function () {
            if ($(this).is(':checked')) {
                $('#cobro_diagnostico_fields').show();
            } else {
                $('#cobro_diagnostico_fields').hide();
            }
        });

        // Calcular total presupuesto
        $('#costo_mano_obra, #costo_repuestos').on('input', function () {
            const manoObra = parseFloat($('#costo_mano_obra').val()) || 0;
            const repuestos = parseFloat($('#costo_repuestos').val()) || 0;
            const total = manoObra + repuestos;
            $('#total_presupuesto').text('$' + total.toFixed(2));
        });

        // Iniciar diagnóstico
        $('#btnIniciarDiagnostico').on('click', function () {
            Swal.fire({
                title: '¿Iniciar diagnóstico?',
                text: 'Se comenzará a contar el tiempo invertido',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Iniciar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('<?= base_url('tecnico/dispositivos/iniciar-diagnostico') ?>', {
                        dispositivo_id: <?= $dispositivo['id'] ?>,
                        <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                    }, function (response) {
                        if (response.success) {
                            location.reload();
                        }
                    });
                }
            });
        });

        // Tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
<?= $this->endSection() ?>