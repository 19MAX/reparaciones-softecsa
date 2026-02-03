<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Dispositivos para Entregar<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
    <div>
        <h3 class="fw-bold mb-3">Dispositivos para Entregar</h3>
        <h6 class="op-7 mb-2">Dispositivos reparados listos para entrega</h6>
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
            <div class="card-header">
                <h4 class="card-title">Dispositivos Listos</h4>
            </div>
            <div class="card-body">
                <?php if (empty($dispositivos)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No hay dispositivos listos para entregar en este momento.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Cliente</th>
                                    <th>Teléfono</th>
                                    <th>Dispositivo</th>
                                    <th>IMEI/Serie</th>
                                    <th>Fecha Reparado</th>
                                    <th>Costo Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dispositivos as $dispositivo): ?>
                                    <tr>
                                        <td><strong><?= $dispositivo['codigo_orden'] ?></strong></td>
                                        <td><?= $dispositivo['cliente_nombre'] ?></td>
                                        <td>
                                            <?= $dispositivo['cliente_telefono'] ?>
                                            <?php if ($dispositivo['cliente_telefono_secundario']): ?>
                                                <br><small class="text-muted"><?= $dispositivo['cliente_telefono_secundario'] ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= $dispositivo['tipo_dispositivo'] ?>
                                            <?php if ($dispositivo['marca']): ?>
                                                <br><small class="text-muted"><?= $dispositivo['marca'] ?> <?= $dispositivo['modelo'] ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $dispositivo['serie_imei'] ?? 'N/A' ?></td>
                                        <td><?= date('d/m/Y', strtotime($dispositivo['fecha_reparado'])) ?></td>
                                        <td>
                                            <strong class="text-success">
                                                $<?= number_format($dispositivo['costo_total'], 2) ?>
                                            </strong>
                                            <br>
                                            <small class="text-muted">
                                                MO: $<?= number_format($dispositivo['mano_obra'], 2) ?><br>
                                                Rep: $<?= number_format($dispositivo['repuestos'], 2) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <button class="btn btn-success btn-sm btnEntregar" 
                                                    data-dispositivo="<?= $dispositivo['id'] ?>"
                                                    data-orden="<?= $dispositivo['codigo_orden'] ?>"
                                                    data-cliente="<?= $dispositivo['cliente_nombre'] ?>"
                                                    data-total="<?= $dispositivo['costo_total'] ?>">
                                                <i class="fas fa-hand-holding"></i> Entregar
                                            </button>
                                            <a href="<?= base_url('recepcionista/ver-orden/' . $dispositivo['orden_id']) ?>" 
                                               class="btn btn-info btn-sm" title="Ver orden">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de entrega -->
<div class="modal fade" id="modalEntrega" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Procesar Entrega de Dispositivo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEntrega">
                    <input type="hidden" id="dispositivo_id" name="dispositivo_id">
                    
                    <div class="alert alert-info">
                        <strong>Orden:</strong> <span id="infoOrden"></span><br>
                        <strong>Cliente:</strong> <span id="infoCliente"></span><br>
                        <strong>Total a Cobrar:</strong> <span id="infoTotal" class="text-success fw-bold"></span>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Monto Pagado *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" 
                                           id="monto_pagado" name="monto_pagado" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Método de Pago *</label>
                                <select class="form-select" name="metodo_pago" required>
                                    <option value="">Seleccione...</option>
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="tarjeta">Tarjeta</option>
                                    <option value="deposito">Depósito</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-warning">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="confirmarEntrega" required>
                                    <label class="form-check-label" for="confirmarEntrega">
                                        Confirmo que el cliente ha recibido el dispositivo y está conforme con la reparación
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="alertaCambio" class="alert alert-success" style="display: none;">
                        <strong>Cambio a devolver:</strong> <span id="cambio" class="fw-bold"></span>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarEntrega">
                    <i class="fas fa-check"></i> Confirmar Entrega
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const baseUrl = '<?= base_url() ?>';
let totalACobrar = 0;

$(document).ready(function() {
    // Abrir modal de entrega
    $('.btnEntregar').click(function() {
        const dispositivoId = $(this).data('dispositivo');
        const orden = $(this).data('orden');
        const cliente = $(this).data('cliente');
        const total = parseFloat($(this).data('total'));

        $('#dispositivo_id').val(dispositivoId);
        $('#infoOrden').text(orden);
        $('#infoCliente').text(cliente);
        $('#infoTotal').text('$' + total.toFixed(2));
        totalACobrar = total;
        
        $('#monto_pagado').val(total.toFixed(2));
        $('#formEntrega')[0].reset();
        $('#dispositivo_id').val(dispositivoId);
        $('#alertaCambio').hide();
        
        $('#modalEntrega').modal('show');
    });

    // Calcular cambio
    $('#monto_pagado').on('input', function() {
        const montoPagado = parseFloat($(this).val()) || 0;
        if (montoPagado > totalACobrar) {
            const cambio = montoPagado - totalACobrar;
            $('#cambio').text('$' + cambio.toFixed(2));
            $('#alertaCambio').show();
        } else {
            $('#alertaCambio').hide();
        }
    });

    // Confirmar entrega
    $('#btnConfirmarEntrega').click(function() {
        if (!$('#confirmarEntrega').is(':checked')) {
            Swal.fire('Atención', 'Debe confirmar la entrega del dispositivo', 'warning');
            return;
        }

        const montoPagado = parseFloat($('#monto_pagado').val()) || 0;
        if (montoPagado < totalACobrar) {
            Swal.fire({
                title: 'Pago Incompleto',
                text: 'El monto pagado es menor al total. ¿Desea continuar con un saldo pendiente?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    procesarEntrega();
                }
            });
        } else {
            procesarEntrega();
        }
    });
});

function procesarEntrega() {
    const formData = new FormData($('#formEntrega')[0]);
    
    $.ajax({
        url: baseUrl + '/recepcionista/procesar-entrega',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.status === 'success') {
                $('#modalEntrega').modal('hide');
                Swal.fire({
                    title: 'Entrega Exitosa',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al procesar la entrega', 'error');
        }
    });
}
</script>
<?= $this->endSection() ?>