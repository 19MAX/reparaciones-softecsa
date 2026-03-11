<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?? 'Problemas y Precios Base' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('admin/dashboard') ?>">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item">Configuración</li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item">Problemas y Precios</li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title"><i class="fas fa-tools me-2"></i>Problemas y Precios Base</h4>
                    <button type="button" class="btn btn-primary btn-round ms-auto" data-bs-toggle="modal"
                        title="Crear nuevo problema" data-bs-target="#addProblemaModal">
                        <i class="fa fa-plus"></i> Nuevo Problema
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="problemas-datatables" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Problema</th>
                                <th>Tipo Dispositivo</th>
                                <th>Tiempo (hrs)</th>
                                <th>Mano de Obra ($)</th>
                                <th>Repuesto ($)</th>
                                <th>Total ($)</th>
                                <th>Estado</th>
                                <th style="width: 10%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($problemas)): ?>
                                <?php foreach ($problemas as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= esc($item['nombre']) ?></div>
                                            <?php if (!empty($item['descripcion'])): ?>
                                                <small class="text-muted"><?= esc($item['descripcion']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                <?= esc($item['tipo_dispositivo_nombre'] ?? 'Sin tipo') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-black text-light">
                                                <i class="far fa-clock me-1"></i> <?= esc($item['tiempo_reparacion_horas']) ?>h
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-primary">
                                            $ <?= number_format($item['precio_mano_obra'] ?? 0, 2) ?>
                                        </td>
                                        <td class="text-end fw-bold text-warning">
                                            $ <?= number_format($item['precio_repuesto'] ?? 0, 2) ?>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            $ <?= number_format(($item['precio_mano_obra'] ?? 0) + ($item['precio_repuesto'] ?? 0), 2) ?>
                                        </td>
                                        <td>
                                            <?php if ($item['activo']): ?>
                                                <span class="badge badge-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="form-button-action">
                                                <button type="button" class="btn btn-link btn-success btn-lg btn-precios-modelo"
                                                    data-id="<?= $item['id'] ?>"
                                                    data-nombre="<?= esc($item['nombre']) ?>"
                                                    data-tipo="<?= $item['tipo_dispositivo_id'] ?>"
                                                    title="Precios por Modelo">
                                                    <i class="fas fa-tags"></i>
                                                </button>
                                                <button type="button" class="btn btn-link btn-primary btn-lg btn-edit"
                                                    data-bs-toggle="modal" data-bs-target="#updateProblemaModal"
                                                    data-id="<?= $item['id'] ?>"
                                                    data-nombre="<?= esc($item['nombre']) ?>"
                                                    data-descripcion="<?= esc($item['descripcion']) ?>"
                                                    data-tipo="<?= $item['tipo_dispositivo_id'] ?>"
                                                    data-tiempo="<?= $item['tiempo_reparacion_horas'] ?>"
                                                    data-mano-obra="<?= $item['precio_mano_obra'] ?? 0 ?>"
                                                    data-repuesto="<?= $item['precio_repuesto'] ?? 0 ?>"
                                                    data-activo="<?= $item['activo'] ?>"
                                                    title="Editar">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-link btn-danger btn-delete"
                                                    data-bs-toggle="modal" data-bs-target="#deleteProblemaModal"
                                                    data-id="<?= $item['id'] ?>"
                                                    data-nombre="<?= esc($item['nombre']) ?>"
                                                    title="Eliminar">
                                                    <i class="fa fa-times"></i>
                                                </button>
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

<!-- Modal Crear -->
<div class="modal fade" id="addProblemaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title fw-bold text-white">Crear Problema</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-form" action="<?= base_url('admin/problemas/crear') ?>" method="post">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre del Problema <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" required
                                placeholder="Ej: Pantalla rota, No enciende">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tipo de Dispositivo <span class="text-danger">*</span></label>
                            <select class="form-select" name="tipo_dispositivo_id" required>
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($tiposDispositivo as $tipo): ?>
                                    <option value="<?= $tipo['id'] ?>"><?= esc($tipo['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Tiempo Reparación (horas) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="0.5" class="form-control" name="tiempo_reparacion_horas"
                                    required value="1.00">
                                <span class="input-group-text">hrs</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Precio Mano de Obra</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" class="form-control" name="precio_mano_obra"
                                    value="0.00">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Precio Repuesto</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" class="form-control" name="precio_repuesto"
                                    value="0.00">
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="3"
                                placeholder="Detalles adicionales sobre este problema..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button form="add-form" type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="updateProblemaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark">Editar Problema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-form" action="<?= base_url('admin/problemas/editar') ?>" method="post">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre del Problema <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre" id="edit-nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Tipo de Dispositivo <span class="text-danger">*</span></label>
                            <select class="form-select" name="tipo_dispositivo_id" id="edit-tipo" required>
                                <?php foreach ($tiposDispositivo as $tipo): ?>
                                    <option value="<?= $tipo['id'] ?>"><?= esc($tipo['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Tiempo Reparación (horas) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.5" min="0.5" class="form-control"
                                    name="tiempo_reparacion_horas" id="edit-tiempo" required>
                                <span class="input-group-text">hrs</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Precio Mano de Obra</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" class="form-control"
                                    name="precio_mano_obra" id="edit-mano-obra">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Precio Repuesto</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0" class="form-control"
                                    name="precio_repuesto" id="edit-repuesto">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" id="edit-descripcion" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 mb-3 pt-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="edit-activo" name="activo"
                                    value="1">
                                <label class="form-check-label fw-bold" for="edit-activo">¿Problema Activo?</label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button form="edit-form" type="submit" class="btn btn-warning">Actualizar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Precios por Modelo -->
<div class="modal fade" id="preciosModeloModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title fw-bold text-white">
                    <i class="fas fa-tags me-2"></i>Precios por Modelo: <span id="pm-problema-nombre"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="pm-problema-id">
                <input type="hidden" id="pm-tipo-id">

                <!-- Formulario agregar precio por modelo -->
                <div class="card mb-3">
                    <div class="card-header py-2"><strong>Agregar Precio por Modelo</strong></div>
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3 mb-2">
                                <label class="form-label fw-bold">Marca <span class="text-danger">*</span></label>
                                <select class="form-select" id="pm-marca">
                                    <option value="">-- Seleccionar tipo primero --</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="form-label fw-bold">Modelo <span class="text-danger">*</span></label>
                                <select class="form-select" id="pm-modelo" disabled>
                                    <option value="">-- Seleccionar marca --</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label fw-bold">Mano de Obra</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" class="form-control" id="pm-mano-obra" value="0.00">
                                </div>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label class="form-label fw-bold">Repuesto</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" min="0" class="form-control" id="pm-repuesto" value="0.00">
                                </div>
                            </div>
                            <div class="col-md-2 mb-2">
                                <button type="button" class="btn btn-success btn-sm w-100" id="btn-guardar-precio-modelo">
                                    <i class="fa fa-plus me-1"></i> Agregar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de precios existentes -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm" id="tabla-precios-modelo">
                        <thead class="table-light">
                            <tr>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th class="text-end">Mano de Obra ($)</th>
                                <th class="text-end">Repuesto ($)</th>
                                <th class="text-end">Total ($)</th>
                                <th style="width: 80px" class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="pm-tbody">
                            <tr><td colspan="6" class="text-center text-muted">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="deleteProblemaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-danger modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Eliminar Problema</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                <h4 class="text-danger mt-3">¿Estás seguro?</h4>
                <p>Se eliminará el problema: <strong id="delete-nombre-display"></strong></p>
                <p class="text-muted small">También se eliminarán los precios base asociados.</p>
                <form id="delete-form" action="<?= base_url('admin/problemas/eliminar') ?>" method="post">
                    <input type="hidden" name="id" id="delete-id">
                </form>
            </div>
            <div class="modal-footer">
                <button form="delete-form" type="submit" class="btn btn-danger">Sí, Eliminar</button>
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const BASE = '<?= base_url('admin/problemas') ?>';

    $(document).ready(function () {
        $('#problemas-datatables').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json' },
            order: [[1, 'asc'], [0, 'asc']],
        });

        // ── Cargar Modal Editar ──
        $('body').on('click', '.btn-edit', function () {
            let btn = $(this);
            $('#edit-id').val(btn.data('id'));
            $('#edit-nombre').val(btn.data('nombre'));
            $('#edit-descripcion').val(btn.data('descripcion'));
            $('#edit-tipo').val(btn.data('tipo'));
            $('#edit-tiempo').val(btn.data('tiempo'));
            $('#edit-mano-obra').val(btn.data('mano-obra'));
            $('#edit-repuesto').val(btn.data('repuesto'));
            $('#edit-activo').prop('checked', btn.data('activo') == 1);
        });

        // ── Cargar Modal Eliminar ──
        $('body').on('click', '.btn-delete', function () {
            $('#delete-id').val($(this).data('id'));
            $('#delete-nombre-display').text($(this).data('nombre'));
        });

        // ══════════════════════════════════════════════════════════
        // ── Precios por Modelo ────────────────────────────────────
        // ══════════════════════════════════════════════════════════

        // Abrir modal de precios por modelo
        $('body').on('click', '.btn-precios-modelo', function () {
            let id   = $(this).data('id');
            let nombre = $(this).data('nombre');
            let tipoId = $(this).data('tipo');

            $('#pm-problema-id').val(id);
            $('#pm-tipo-id').val(tipoId);
            $('#pm-problema-nombre').text(nombre);

            // Reset selects
            $('#pm-marca').html('<option value="">Cargando marcas...</option>');
            $('#pm-modelo').html('<option value="">-- Seleccionar marca --</option>').prop('disabled', true);
            $('#pm-mano-obra').val('0.00');
            $('#pm-repuesto').val('0.00');

            // Cargar marcas por tipo
            $.getJSON(BASE + '/marcas-por-tipo/' + tipoId, function (data) {
                let opts = '<option value="">-- Seleccionar --</option>';
                data.forEach(function (m) {
                    opts += '<option value="' + m.id + '">' + m.nombre + '</option>';
                });
                $('#pm-marca').html(opts);
            });

            // Cargar tabla de precios existentes
            cargarPreciosModelo(id);

            $('#preciosModeloModal').modal('show');
        });

        // Cascada: marca → modelos
        $('#pm-marca').on('change', function () {
            let marcaId = $(this).val();
            if (!marcaId) {
                $('#pm-modelo').html('<option value="">-- Seleccionar marca --</option>').prop('disabled', true);
                return;
            }
            $('#pm-modelo').html('<option value="">Cargando...</option>').prop('disabled', true);

            $.getJSON(BASE + '/modelos-por-marca/' + marcaId, function (data) {
                let opts = '<option value="">-- Seleccionar --</option>';
                data.forEach(function (m) {
                    opts += '<option value="' + m.id + '">' + m.nombre + '</option>';
                });
                $('#pm-modelo').html(opts).prop('disabled', false);
            });
        });

        // Guardar precio por modelo
        $('#btn-guardar-precio-modelo').on('click', function () {
            let problemaId = $('#pm-problema-id').val();
            let modeloId   = $('#pm-modelo').val();
            let manoObra   = $('#pm-mano-obra').val();
            let repuesto   = $('#pm-repuesto').val();

            if (!modeloId) {
                Swal.fire('Atención', 'Debes seleccionar un modelo', 'warning');
                return;
            }

            $.post(BASE + '/guardar-precio-modelo', {
                problema_id: problemaId,
                modelo_id: modeloId,
                precio_mano_obra: manoObra,
                precio_repuesto: repuesto
            }, function (res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: res.message, toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
                    cargarPreciosModelo(problemaId);
                    // Reset
                    $('#pm-modelo').val('');
                    $('#pm-mano-obra').val('0.00');
                    $('#pm-repuesto').val('0.00');
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            }, 'json');
        });

        // Eliminar precio por modelo
        $('body').on('click', '.btn-eliminar-precio-modelo', function () {
            let precioId = $(this).data('id');
            let problemaId = $('#pm-problema-id').val();

            Swal.fire({
                title: '¿Eliminar este precio?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(BASE + '/eliminar-precio-modelo', { id: precioId }, function (res) {
                        if (res.success) {
                            Swal.fire({ icon: 'success', title: res.message, toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
                            cargarPreciosModelo(problemaId);
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    }, 'json');
                }
            });
        });
    });

    function cargarPreciosModelo(problemaId) {
        $.getJSON(BASE + '/precios-modelo/' + problemaId, function (res) {
            let tbody = '';
            if (res.success && res.precios.length > 0) {
                res.precios.forEach(function (p) {
                    let total = (parseFloat(p.precio_mano_obra) + parseFloat(p.precio_repuesto)).toFixed(2);
                    tbody += '<tr>';
                    tbody += '<td>' + (p.marca_nombre || '-') + '</td>';
                    tbody += '<td>' + (p.modelo_nombre || '-') + '</td>';
                    tbody += '<td class="text-end">$ ' + parseFloat(p.precio_mano_obra).toFixed(2) + '</td>';
                    tbody += '<td class="text-end">$ ' + parseFloat(p.precio_repuesto).toFixed(2) + '</td>';
                    tbody += '<td class="text-end fw-bold text-success">$ ' + total + '</td>';
                    tbody += '<td class="text-center">';
                    tbody += '<button class="btn btn-sm btn-danger btn-eliminar-precio-modelo" data-id="' + p.id + '" title="Eliminar">';
                    tbody += '<i class="fa fa-times"></i></button>';
                    tbody += '</td></tr>';
                });
            } else {
                tbody = '<tr><td colspan="6" class="text-center text-muted">No hay precios específicos por modelo</td></tr>';
            }
            $('#pm-tbody').html(tbody);
        });
    }
</script>
<?= $this->endSection() ?>
