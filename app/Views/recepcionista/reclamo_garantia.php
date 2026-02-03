<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Reclamo de Garantía<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
    <div>
        <h3 class="fw-bold mb-3">Reclamo de Garantía</h3>
        <h6 class="op-7 mb-2">Gestionar reclamos por garantía de reparaciones</h6>
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
            <div class="card-header bg-warning">
                <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Buscar Dispositivo</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-10">
                        <div class="form-group">
                            <label>Buscar por IMEI o Código de Orden</label>
                            <input type="text" class="form-control form-control-lg" 
                                   id="searchDispositivo" 
                                   placeholder="Ingrese IMEI del dispositivo o código de orden">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-warning btn-lg w-100" id="btnBuscarDispositivo">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resultado de búsqueda -->
<div id="resultadoBusqueda" style="display: none;">
    <div class="row mt-3">
        <!-- Información del dispositivo -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-mobile-alt"></i> Información del Dispositivo</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Orden Original:</th>
                            <td><strong id="infoOrden"></strong></td>
                        </tr>
                        <tr>
                            <th>Cliente:</th>
                            <td id="infoCliente"></td>
                        </tr>
                        <tr>
                            <th>Teléfono:</th>
                            <td id="infoTelefono"></td>
                        </tr>
                        <tr>
                            <th>Dispositivo:</th>
                            <td id="infoDispositivo"></td>
                        </tr>
                        <tr>
                            <th>IMEI/Serie:</th>
                            <td id="infoIMEI"></td>
                        </tr>
                        <tr>
                            <th>Reparación Original:</th>
                            <td id="infoReparacion"></td>
                        </tr>
                        <tr>
                            <th>Fecha Entrega:</th>
                            <td id="infoFechaEntrega"></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Información de garantía -->
        <div class="col-md-6">
            <div class="card" id="cardGarantia">
                <div class="card-header text-white" id="headerGarantia">
                    <h5 class="mb-0"><i class="fas fa-certificate"></i> Estado de Garantía</h5>
                </div>
                <div class="card-body">
                    <div id="garantiaInfo"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de reclamo -->
    <div class="row mt-3" id="formReclamoContainer" style="display: none;">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Registrar Reclamo</h5>
                </div>
                <div class="card-body">
                    <form id="formReclamo">
                        <input type="hidden" id="garantia_id" name="garantia_id">
                        <input type="hidden" id="dispositivo_id" name="dispositivo_id">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-info-circle"></i> 
                                    Describa detalladamente el problema que presenta el dispositivo. 
                                    Esta información será enviada al técnico para su evaluación.
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Problema Reportado por el Cliente *</label>
                                    <textarea class="form-control" name="problema_reportado" 
                                              id="problema_reportado" rows="5" required
                                              placeholder="Describa en detalle el problema que presenta el dispositivo..."></textarea>
                                    <small class="form-text text-muted">Mínimo 10 caracteres</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Observaciones Adicionales</label>
                                    <textarea class="form-control" name="observaciones_recepcion" 
                                              rows="3" placeholder="Observaciones adicionales del recepcionista..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-danger btn-lg w-100">
                                    <i class="fas fa-paper-plane"></i> Registrar Reclamo de Garantía
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Solicitud de excepción -->
    <div class="row mt-3" id="formExcepcionContainer" style="display: none;">
        <div class="col-md-12">
            <div class="card border-warning">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-user-shield"></i> Solicitar Excepción de Garantía</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle"></i> Garantía Vencida</strong><br>
                        La garantía de este dispositivo ha vencido, pero puede solicitar una excepción al administrador 
                        si considera que hay razones justificadas para cubrir la reparación.
                    </div>

                    <form id="formExcepcion">
                        <input type="hidden" id="garantia_vencida_id" name="garantia_id">
                        <input type="hidden" id="dispositivo_vencido_id" name="dispositivo_id">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Problema Reportado *</label>
                                    <textarea class="form-control" name="problema_reportado_excepcion" 
                                              rows="3" required></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Justificación para Excepción *</label>
                                    <textarea class="form-control" name="justificacion" 
                                              rows="4" required
                                              placeholder="Explique por qué considera que se debe hacer una excepción a la garantía vencida..."></textarea>
                                    <small class="form-text text-muted">Mínimo 20 caracteres</small>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-warning btn-lg w-100">
                                    <i class="fas fa-paper-plane"></i> Enviar Solicitud al Administrador
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const baseUrl = '<?= base_url() ?>';
let dispositivoActual = null;
let garantiaActual = null;

$(document).ready(function() {
    // Buscar dispositivo
    $('#btnBuscarDispositivo').click(function() {
        const search = $('#searchDispositivo').val().trim();
        if (!search) {
            Swal.fire('Error', 'Ingrese un IMEI o código de orden', 'error');
            return;
        }

        $.ajax({
            url: baseUrl + '/recepcionista/buscar-dispositivo-garantia',
            method: 'POST',
            data: {
                search: search,
                <?= csrf_token() ?>: $('input[name="<?= csrf_token() ?>"]').val()
            },
            success: function(response) {
                if (response.status === 'success') {
                    mostrarResultado(response.dispositivo, response.garantia);
                } else {
                    Swal.fire('No encontrado', response.message, 'warning');
                    $('#resultadoBusqueda').hide();
                }
            },
            error: function() {
                Swal.fire('Error', 'Error al buscar dispositivo', 'error');
            }
        });
    });

    // Enviar reclamo
    $('#formReclamo').submit(function(e) {
        e.preventDefault();
        
        const problema = $('#problema_reportado').val().trim();
        if (problema.length < 10) {
            Swal.fire('Error', 'El problema debe tener al menos 10 caracteres', 'error');
            return;
        }

        $.ajax({
            url: baseUrl + '/recepcionista/guardar-reclamo-garantia',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        title: 'Reclamo Registrado',
                        text: 'El reclamo ha sido registrado exitosamente y asignado al técnico.',
                        icon: 'success',
                        confirmButtonText: 'Ver Orden'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = baseUrl + '/recepcionista/ver-orden/' + response.orden_id;
                        } else {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error al guardar el reclamo', 'error');
            }
        });
    });

    // Enviar solicitud de excepción
    $('#formExcepcion').submit(function(e) {
        e.preventDefault();
        
        const justificacion = $('[name="justificacion"]').val().trim();
        if (justificacion.length < 20) {
            Swal.fire('Error', 'La justificación debe tener al menos 20 caracteres', 'error');
            return;
        }

        $.ajax({
            url: baseUrl + '/recepcionista/solicitar-excepcion-garantia',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        title: 'Solicitud Enviada',
                        text: response.message,
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error al enviar solicitud', 'error');
            }
        });
    });
});

function mostrarResultado(dispositivo, garantia) {
    dispositivoActual = dispositivo;
    garantiaActual = garantia;

    // Llenar información del dispositivo
    $('#infoOrden').text(dispositivo.codigo_orden);
    $('#infoCliente').text(dispositivo.cliente_nombre);
    $('#infoTelefono').text(dispositivo.cliente_telefono);
    $('#infoDispositivo').text(dispositivo.tipo_dispositivo + ' ' + (dispositivo.marca || '') + ' ' + (dispositivo.modelo || ''));
    $('#infoIMEI').text(dispositivo.serie_imei || 'N/A');
    $('#infoReparacion').text(dispositivo.problema_reparado);
    $('#infoFechaEntrega').text(new Date(dispositivo.fecha_entrega).toLocaleDateString('es-EC'));

    // Mostrar resultado
    $('#resultadoBusqueda').show();
    $('#formReclamoContainer').hide();
    $('#formExcepcionContainer').hide();

    if (garantia) {
        const diasRestantes = garantia.dias_restantes;
        const vencida = diasRestantes < 0;

        if (vencida) {
            // Garantía vencida
            $('#headerGarantia').removeClass('bg-success').addClass('bg-danger text-white');
            $('#garantiaInfo').html(`
                <div class="alert alert-danger">
                    <h6 class="mb-2"><i class="fas fa-times-circle"></i> Garantía Vencida</h6>
                    <p class="mb-1"><strong>Fecha de vencimiento:</strong> ${new Date(garantia.fecha_vencimiento).toLocaleDateString('es-EC')}</p>
                    <p class="mb-1"><strong>Días vencida:</strong> ${Math.abs(diasRestantes)} días</p>
                    <p class="mb-0"><strong>Duración garantía:</strong> ${garantia.duracion_dias} días</p>
                </div>
            `);
            
            // Mostrar formulario de excepción
            $('#garantia_vencida_id').val(garantia.id);
            $('#dispositivo_vencido_id').val(dispositivo.id);
            $('#formExcepcionContainer').show();
        } else {
            // Garantía activa
            $('#headerGarantia').removeClass('bg-danger').addClass('bg-success text-white');
            $('#garantiaInfo').html(`
                <div class="alert alert-success">
                    <h6 class="mb-2"><i class="fas fa-check-circle"></i> Garantía Activa</h6>
                    <p class="mb-1"><strong>Fecha de vencimiento:</strong> ${new Date(garantia.fecha_vencimiento).toLocaleDateString('es-EC')}</p>
                    <p class="mb-1"><strong>Días restantes:</strong> ${diasRestantes} días</p>
                    <p class="mb-1"><strong>Duración garantía:</strong> ${garantia.duracion_dias} días</p>
                    <p class="mb-0"><strong>Veces reclamada:</strong> ${dispositivo.veces_reclamada || 0} vez(es)</p>
                </div>
            `);
            
            // Mostrar formulario de reclamo
            $('#garantia_id').val(garantia.id);
            $('#dispositivo_id').val(dispositivo.id);
            $('#formReclamoContainer').show();
        }
    } else {
        // Sin garantía
        $('#headerGarantia').removeClass('bg-success bg-danger').addClass('bg-secondary text-white');
        $('#garantiaInfo').html(`
            <div class="alert alert-secondary">
                <h6><i class="fas fa-ban"></i> Sin Garantía</h6>
                <p class="mb-0">Este dispositivo no tiene garantía activa.</p>
            </div>
        `);
    }
}
</script>
<?= $this->endSection() ?>