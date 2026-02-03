<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Finalizar y Entregar Dispositivo
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('admin/dashboard') ?>"><i class="icon-home"></i></a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item">
            <a href="<?= base_url('admin/ordenes') ?>">Órdenes</a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Finalizar Dispositivo</a></li>
    </ul>
</div>

<div class="row">
    <!-- Información del Dispositivo -->
    <div class="col-lg-4">
        <div class="card card-primary">
            <div class="card-header">
                <h4 class="card-title text-white">
                    <i class="fas fa-mobile-alt me-2"></i>Dispositivo
                </h4>
            </div>
            <div class="card-body">
                <h5>
                    <?= esc($dispositivo['tipo_dispositivo']) ?>
                </h5>
                <p class="text-muted">
                    <?= esc($dispositivo['marca']) ?>
                    <?= esc($dispositivo['modelo']) ?>
                </p>

                <hr>

                <div class="mb-2">
                    <strong>Orden:</strong>
                    <?= esc($dispositivo['codigo_orden']) ?>
                </div>
                <div class="mb-2">
                    <strong>Cliente:</strong>
                    <?= esc($dispositivo['cliente_nombre']) ?>
                </div>
                <div class="mb-2">
                    <strong>Teléfono:</strong>
                    <?= esc($dispositivo['cliente_telefono']) ?>
                </div>
                <div class="mb-2">
                    <strong>Técnico:</strong>
                    <?= esc($dispositivo['tecnico_asignado']) ?>
                </div>

                <?php if (!empty($dispositivo['serie_imei'])): ?>
                    <div class="mb-2">
                        <strong>Serie/IMEI:</strong> <code><?= esc($dispositivo['serie_imei']) ?></code>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Resumen de Trabajos -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Resumen de Trabajos</h5>
            </div>
            <div class="card-body">
                <h6 class="mb-3">Problemas Resueltos:</h6>
                <?php if (!empty($problemas_resueltos)): ?>
                    <ul class="list-unstyled">
                        <?php foreach ($problemas_resueltos as $problema): ?>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                <?= esc($problema['problema']) ?>
                                <?php if (!empty($problema['costo_reparacion'])): ?>
                                    <br><small class="text-muted ms-3">Costo: $
                                        <?= number_format($problema['costo_reparacion'], 2) ?>
                                    </small>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No hay problemas resueltos</p>
                <?php endif; ?>

                <?php if (!empty($problemas_no_resueltos)): ?>
                    <hr>
                    <h6 class="mb-3 text-warning">No Resueltos:</h6>
                    <ul class="list-unstyled">
                        <?php foreach ($problemas_no_resueltos as $problema): ?>
                            <li class="mb-2">
                                <i class="fas fa-times-circle text-danger me-1"></i>
                                <?= esc($problema['problema']) ?>
                                <?php if (!empty($problema['razon_no_reparado'])): ?>
                                    <br><small class="text-muted ms-3">
                                        <?= esc($problema['razon_no_reparado']) ?>
                                    </small>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Formulario de Finalización -->
    <div class="col-lg-8">
        <form id="formFinalizarDispositivo" action="<?= base_url('admin/dispositivos/procesar-finalizacion') ?>"
            method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="dispositivo_id" value="<?= $dispositivo['id'] ?>">
            <input type="hidden" name="orden_id" value="<?= $dispositivo['orden_id'] ?>">

            <!-- Costos y Facturación -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Costos y Facturación</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Mano de Obra</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="mano_obra" id="mano_obra" step="0.01"
                                    min="0" value="<?= $costos_calculados['mano_obra'] ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Repuestos</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="repuestos" id="repuestos" step="0.01"
                                    min="0" value="<?= $costos_calculados['repuestos'] ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Cobro de Revisión según Política -->
                    <?php if ($debe_cobrar_revision): ?>
                        <div class="alert alert-warning">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cobrar_revision" id="cobrar_revision"
                                    value="1" <?= $politica_revision['debe_cobrar'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="cobrar_revision">
                                    <strong>Cobrar Revisión Técnica</strong>
                                </label>
                            </div>
                            <div id="valor_revision_container"
                                style="display: <?= $politica_revision['debe_cobrar'] ? 'block' : 'none' ?>;">
                                <div class="input-group mt-2">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="valor_revision" id="valor_revision"
                                        step="0.01" value="<?= $politica_revision['valor'] ?>">
                                </div>
                                <small class="text-muted">
                                    <?= esc($politica_revision['razon']) ?>
                                </small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Subtotal y Total -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <strong id="subtotal">$0.00</strong>
                            </div>
                            <?php if ($debe_cobrar_revision): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Revisión Técnica:</span>
                                    <strong id="revision_display">$0.00</strong>
                                </div>
                            <?php endif; ?>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <h5>TOTAL A COBRAR:</h5>
                                <h5 class="text-success" id="total_final">$0.00</h5>
                            </div>
                        </div>
                    </div>

                    <!-- Método de Pago -->
                    <div class="mt-3">
                        <label class="form-label">Método de Pago</label>
                        <select name="metodo_pago" class="form-select" required>
                            <option value="">-- Seleccionar --</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="tarjeta">Tarjeta de Crédito/Débito</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>

                    <!-- Abonos/Pagos Parciales -->
                    <div class="mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="tiene_abonos" name="tiene_abonos">
                            <label class="form-check-label" for="tiene_abonos">
                                Cliente realizó abonos previos
                            </label>
                        </div>
                        <div id="abonos_container" style="display: none;" class="mt-2">
                            <label class="form-label">Total de Abonos Recibidos</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="abonos_recibidos" id="abonos_recibidos"
                                    step="0.01" min="0" value="0">
                            </div>
                            <div class="mt-2">
                                <strong>Saldo Pendiente: <span id="saldo_pendiente"
                                        class="text-danger">$0.00</span></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Garantía -->
            <?php if ($puede_generar_garantia): ?>
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Garantía</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="generar_garantia" id="generar_garantia"
                                value="1" checked>
                            <label class="form-check-label" for="generar_garantia">
                                <strong>Generar Garantía</strong>
                            </label>
                        </div>

                        <div id="garantia_fields">
                            <div class="mb-3">
                                <label class="form-label">Tipo de Garantía</label>
                                <select name="tipo_garantia_id" class="form-select">
                                    <option value="">-- Seleccionar --</option>
                                    <?php if (!empty($tipos_garantia)): ?>
                                        <?php foreach ($tipos_garantia as $tipo): ?>
                                            <option value="<?= $tipo['id'] ?>" <?= $tipo['es_default'] ? 'selected' : '' ?>>
                                                <?= esc($tipo['nombre']) ?> (
                                                <?= $tipo['dias'] ?> días)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Problema Cubierto por Garantía</label>
                                <select name="problema_garantizado_id" class="form-select">
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach ($problemas_resueltos as $problema): ?>
                                        <option value="<?= $problema['id'] ?>">
                                            <?= esc($problema['problema']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="alert alert-info">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    La garantía cubrirá únicamente el problema seleccionado.
                                    El cliente deberá firmar las condiciones al momento de la entrega.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Cálculo Automático de Comisiones (Informativo) -->
            <div class="card mb-3">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Comisiones del Técnico</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-secondary">
                        <strong>Técnico:</strong>
                        <?= esc($dispositivo['tecnico_asignado']) ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Tipo de Comisión:</strong></p>
                            <p>
                                <?= ucfirst($tecnico_info['tipo_comision']) ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Valor Configurado:</strong></p>
                            <p>
                                <?php if ($tecnico_info['tipo_comision'] === 'porcentaje'): ?>
                                    <?= $tecnico_info['valor_comision'] ?>%
                                <?php else: ?>
                                    $
                                    <?= number_format($tecnico_info['valor_comision'], 2) ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <div class="card bg-light mt-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <span>Base de Cálculo (Mano de Obra):</span>
                                <strong id="base_comision">$0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <h6>Comisión Calculada:</h6>
                                <h6 class="text-success" id="comision_calculada">$0.00</h6>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="comision_calculada" id="comision_calculada_hidden">
                </div>
            </div>

            <!-- Observaciones de Entrega -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-comment me-2"></i>Observaciones de Entrega</h5>
                </div>
                <div class="card-body">
                    <textarea class="form-control" name="observaciones_entrega" rows="3"
                        placeholder="Recomendaciones, advertencias o notas para el cliente..."></textarea>
                </div>
            </div>

            <!-- Confirmación del Cliente -->
            <div class="card mb-3">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-signature me-2"></i>Confirmación de Entrega</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="cliente_presente" id="cliente_presente"
                            value="1" required>
                        <label class="form-check-label" for="cliente_presente">
                            <strong>Confirmo que el cliente está presente y conforme con la reparación</strong>
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre de quien recibe</label>
                        <input type="text" class="form-control" name="nombre_receptor"
                            value="<?= esc($dispositivo['cliente_nombre']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cédula/Identificación</label>
                        <input type="text" class="form-control" name="cedula_receptor"
                            value="<?= esc($dispositivo['cliente_cedula']) ?>" required>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('admin/ordenes/editar/' . $dispositivo['orden_id']) ?>"
                            class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success btn-lg" id="btnFinalizar">
                            <i class="fas fa-check-circle me-2"></i>Finalizar y Entregar Dispositivo
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        const tipoComision = '<?= $tecnico_info['tipo_comision'] ?>';
        const valorComision = <?= $tecnico_info['valor_comision'] ?>;

        // Calcular totales en tiempo real
        function calcularTotales() {
            const manoObra = parseFloat($('#mano_obra').val()) || 0;
            const repuestos = parseFloat($('#repuestos').val()) || 0;
            const subtotal = manoObra + repuestos;

            let valorRevision = 0;
            if ($('#cobrar_revision').is(':checked')) {
                valorRevision = parseFloat($('#valor_revision').val()) || 0;
            }

            const totalFinal = subtotal + valorRevision;

            // Actualizar displays
            $('#subtotal').text('$' + subtotal.toFixed(2));
            $('#revision_display').text('$' + valorRevision.toFixed(2));
            $('#total_final').text('$' + totalFinal.toFixed(2));

            // Calcular comisión
            let comision = 0;
            if (tipoComision === 'porcentaje') {
                comision = (manoObra * valorComision) / 100;
            } else {
                comision = valorComision;
            }

            $('#base_comision').text('$' + manoObra.toFixed(2));
            $('#comision_calculada').text('$' + comision.toFixed(2));
            $('#comision_calculada_hidden').val(comision.toFixed(2));

            // Calcular saldo si hay abonos
            if ($('#tiene_abonos').is(':checked')) {
                const abonos = parseFloat($('#abonos_recibidos').val()) || 0;
                const saldo = totalFinal - abonos;
                $('#saldo_pendiente').text('$' + saldo.toFixed(2));
            }
        }

        // Event listeners
        $('#mano_obra, #repuestos, #valor_revision, #abonos_recibidos').on('input', calcularTotales);

        $('#cobrar_revision').on('change', function () {
            if ($(this).is(':checked')) {
                $('#valor_revision_container').slideDown();
            } else {
                $('#valor_revision_container').slideUp();
            }
            calcularTotales();
        });

        $('#tiene_abonos').on('change', function () {
            if ($(this).is(':checked')) {
                $('#abonos_container').slideDown();
            } else {
                $('#abonos_container').slideUp();
            }
            calcularTotales();
        });

        $('#generar_garantia').on('change', function () {
            if ($(this).is(':checked')) {
                $('#garantia_fields').slideDown();
            } else {
                $('#garantia_fields').slideUp();
            }
        });

        // Calcular al cargar
        calcularTotales();

        // Validación del formulario
        $('#formFinalizarDispositivo').on('submit', function (e) {
            e.preventDefault();

            if (!$('#cliente_presente').is(':checked')) {
                Swal.fire({
                    icon: 'error',
                    title: 'Confirmación requerida',
                    text: 'Debes confirmar que el cliente está presente'
                });
                return false;
            }

            Swal.fire({
                title: '¿Finalizar y entregar dispositivo?',
                html: `
                <p>Se realizarán las siguientes acciones:</p>
                <ul class="text-start">
                    <li>Se registrará el pago del cliente</li>
                    <li>Se calculará la comisión del técnico</li>
                    ${$('#generar_garantia').is(':checked') ? '<li>Se generará la garantía</li>' : ''}
                    <li>Se marcará el dispositivo como entregado</li>
                </ul>
                <p class="mt-3"><strong>Total a cobrar: ${$('#total_final').text()}</strong></p>
            `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, finalizar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#btnFinalizar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Procesando...');
                    this.submit();
                }
            });
        });
    });
</script>
<?= $this->endSection() ?>