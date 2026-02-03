<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Detalle de Orden<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
    <div>
        <h3 class="fw-bold mb-3">Orden <?= $orden['codigo_orden'] ?></h3>
        <h6 class="op-7 mb-2">Detalle completo de la orden</h6>
    </div>
    <div class="ms-md-auto py-2 py-md-0">
        <a href="<?= base_url('recepcionista/imprimir-orden/' . $orden['id']) ?>" 
           class="btn btn-secondary btn-round me-2" target="_blank">
            <i class="fas fa-print"></i> Imprimir
        </a>
        <a href="<?= base_url('recepcionista/consultar-estado') ?>" class="btn btn-primary btn-round">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Información del Cliente -->
    <div class="col-md-4">
        <div class="card card-profile">
            <div class="card-header" style="background: linear-gradient(195deg, #42424a, #191919);">
                <div class="profile-picture">
                    <div class="avatar avatar-xl">
                        <img src="<?= base_url('assets/img/profile.jpg') ?>" alt="..." class="avatar-img rounded-circle">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="user-profile text-center">
                    <div class="name"><?= $orden['cliente_nombres'] ?> <?= $orden['cliente_apellidos'] ?></div>
                    <div class="job">Cliente</div>
                    <div class="desc mt-3">
                        <strong>Cédula:</strong> <?= $orden['cliente_cedula'] ?><br>
                        <strong>Teléfono:</strong> <?= $orden['cliente_telefono'] ?>
                        <?php if ($orden['cliente_telefono_secundario']): ?>
                            <br><strong>Tel. Secundario:</strong> <?= $orden['cliente_telefono_secundario'] ?>
                        <?php endif; ?>
                        <?php if ($orden['cliente_email']): ?>
                            <br><strong>Email:</strong> <?= $orden['cliente_email'] ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de la Orden -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Información de la Orden</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Código:</th>
                                <td><strong><?= $orden['codigo_orden'] ?></strong></td>
                            </tr>
                            <tr>
                                <th>Fecha de Creación:</th>
                                <td><?= date('d/m/Y H:i', strtotime($orden['fecha_creacion'])) ?></td>
                            </tr>
                            <tr>
                                <th>Estado Global:</th>
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
                            </tr>
                            <tr>
                                <th>Urgencia:</th>
                                <td>
                                    <span class="badge badge-<?= $orden['urgencia_color'] ?? 'secondary' ?>">
                                        <?= $orden['urgencia_nombre'] ?? 'N/A' ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="50%">Fecha Est. Entrega:</th>
                                <td>
                                    <?= $orden['fecha_estimada_entrega'] ? date('d/m/Y', strtotime($orden['fecha_estimada_entrega'])) : 'Por definir' ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Mano de Obra Aprox.:</th>
                                <td>$<?= number_format($orden['mano_obra_aproximado'] ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <th>Repuestos Aprox.:</th>
                                <td>$<?= number_format($orden['repuestos_aproximado'] ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <th>Atendido por:</th>
                                <td><?= $orden['usuario_nombre'] ?? 'N/A' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if ($orden['observaciones_generales']): ?>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong><i class="fas fa-comment"></i> Observaciones:</strong><br>
                            <?= nl2br(esc($orden['observaciones_generales'])) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Dispositivos -->
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Dispositivos en esta Orden</h4>
            </div>
            <div class="card-body">
                <?php if (isset($orden['dispositivos']) && !empty($orden['dispositivos'])): ?>
                    <?php foreach ($orden['dispositivos'] as $index => $dispositivo): ?>
                        <div class="card mb-3 border">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    Dispositivo #<?= $index + 1 ?>: 
                                    <?= $dispositivo['tipo_nombre'] ?>
                                    <?php if ($dispositivo['marca_nombre'] || $dispositivo['marca_custom']): ?>
                                        - <?= $dispositivo['marca_nombre'] ?? $dispositivo['marca_custom'] ?>
                                    <?php endif; ?>
                                    <?php if ($dispositivo['modelo_nombre'] || $dispositivo['modelo_custom']): ?>
                                        <?= $dispositivo['modelo_nombre'] ?? $dispositivo['modelo_custom'] ?>
                                    <?php endif; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th width="40%">IMEI/Serie:</th>
                                                <td><?= $dispositivo['serie_imei'] ?? 'N/A' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Estado Reparación:</th>
                                                <td>
                                                    <?php
                                                    $estadoBadge = 'secondary';
                                                    switch ($dispositivo['estado_reparacion']) {
                                                        case 'pendiente':
                                                            $estadoBadge = 'warning';
                                                            break;
                                                        case 'en_diagnostico':
                                                            $estadoBadge = 'info';
                                                            break;
                                                        case 'en_reparacion':
                                                            $estadoBadge = 'primary';
                                                            break;
                                                        case 'reparado':
                                                            $estadoBadge = 'success';
                                                            break;
                                                        case 'entregado':
                                                            $estadoBadge = 'dark';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge badge-<?= $estadoBadge ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $dispositivo['estado_reparacion'])) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Estado Diagnóstico:</th>
                                                <td>
                                                    <span class="badge badge-<?= $dispositivo['estado_diagnostico'] == 'completado' ? 'success' : 'warning' ?>">
                                                        <?= ucfirst($dispositivo['estado_diagnostico']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Técnico Asignado:</th>
                                                <td><?= $dispositivo['tecnico_nombre'] ?? 'Sin asignar' ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <th width="45%">Tipo Contraseña:</th>
                                                <td><?= ucfirst($dispositivo['tipo_pass']) ?></td>
                                            </tr>
                                            <?php if ($dispositivo['pass_code']): ?>
                                            <tr>
                                                <th>Código:</th>
                                                <td><code><?= $dispositivo['pass_code'] ?></code></td>
                                            </tr>
                                            <?php endif; ?>
                                            <tr>
                                                <th>Fecha Estimada:</th>
                                                <td>
                                                    <?= $dispositivo['fecha_estimada_entrega'] ? date('d/m/Y', strtotime($dispositivo['fecha_estimada_entrega'])) : 'Por definir' ?>
                                                </td>
                                            </tr>
                                            <?php if ($dispositivo['fecha_entrega_real']): ?>
                                            <tr>
                                                <th>Fecha Entrega Real:</th>
                                                <td><?= date('d/m/Y', strtotime($dispositivo['fecha_entrega_real'])) ?></td>
                                            </tr>
                                            <?php endif; ?>
                                        </table>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <strong>Problema Reportado:</strong>
                                        <p class="text-muted"><?= nl2br(esc($dispositivo['problema_reportado'])) ?></p>
                                    </div>
                                    <?php if ($dispositivo['observaciones']): ?>
                                    <div class="col-md-12">
                                        <strong>Observaciones:</strong>
                                        <p class="text-muted"><?= nl2br(esc($dispositivo['observaciones'])) ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Autorización del cliente -->
                                <?php if ($dispositivo['estado_reparacion'] == 'esperando_autorizacion'): ?>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="alert alert-warning">
                                            <h6><i class="fas fa-exclamation-triangle"></i> Esperando Autorización del Cliente</h6>
                                            <div class="mt-3">
                                                <button class="btn btn-success btn-sm btnAutorizar" 
                                                        data-dispositivo="<?= $dispositivo['id'] ?>">
                                                    <i class="fas fa-check"></i> Cliente Autoriza
                                                </button>
                                                <button class="btn btn-danger btn-sm btnRechazar" 
                                                        data-dispositivo="<?= $dispositivo['id'] ?>">
                                                    <i class="fas fa-times"></i> Cliente Rechaza
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php elseif (isset($dispositivo['cliente_autoriza_reparacion'])): ?>
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="alert alert-<?= $dispositivo['cliente_autoriza_reparacion'] ? 'success' : 'danger' ?>">
                                            <?php if ($dispositivo['cliente_autoriza_reparacion']): ?>
                                                <i class="fas fa-check-circle"></i> Cliente autorizó la reparación
                                            <?php else: ?>
                                                <i class="fas fa-times-circle"></i> Cliente rechazó la reparación
                                                <?php if ($dispositivo['razon_rechazo']): ?>
                                                    <br><strong>Razón:</strong> <?= esc($dispositivo['razon_rechazo']) ?>
                                                <?php endif; ?>
                                                <?php if ($dispositivo['cobra_valor_revision']): ?>
                                                    <br><strong>Cobro revisión:</strong> $<?= number_format($dispositivo['valor_revision_cobrado'], 2) ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No hay dispositivos registrados en esta orden.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para rechazo -->
<div class="modal fade" id="modalRechazo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Rechazo del Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formRechazo">
                    <input type="hidden" id="dispositivo_id_rechazo" name="dispositivo_id">
                    <div class="form-group">
                        <label>Razón del Rechazo *</label>
                        <textarea class="form-control" name="razon_rechazo" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarRechazo">Confirmar Rechazo</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const baseUrl = '<?= base_url() ?>';

$(document).ready(function() {
    // Autorizar reparación
    $('.btnAutorizar').click(function() {
        const dispositivoId = $(this).data('dispositivo');
        
        Swal.fire({
            title: '¿Confirmar autorización?',
            text: 'El cliente autoriza la reparación de este dispositivo',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, autorizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                registrarAutorizacion(dispositivoId, 1, '');
            }
        });
    });

    // Rechazar reparación
    $('.btnRechazar').click(function() {
        const dispositivoId = $(this).data('dispositivo');
        $('#dispositivo_id_rechazo').val(dispositivoId);
        $('#modalRechazo').modal('show');
    });

    // Confirmar rechazo
    $('#btnConfirmarRechazo').click(function() {
        const formData = new FormData($('#formRechazo')[0]);
        registrarAutorizacion(
            formData.get('dispositivo_id'),
            0,
            formData.get('razon_rechazo')
        );
    });
});

function registrarAutorizacion(dispositivoId, autoriza, razon) {
    $.ajax({
        url: baseUrl + '/recepcionista/registrar-autorizacion',
        method: 'POST',
        data: {
            dispositivo_id: dispositivoId,
            autoriza: autoriza,
            razon_rechazo: razon,
            <?= csrf_token() ?>: $('input[name="<?= csrf_token() ?>"]').val()
        },
        success: function(response) {
            if (response.status === 'success') {
                Swal.fire('Éxito', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al registrar la respuesta', 'error');
        }
    });
}
</script>
<?= $this->endSection() ?>