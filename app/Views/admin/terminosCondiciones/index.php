<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?? 'Términos y Condiciones' ?>
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
        <li class="nav-item"><a href="#">Configuración</a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Términos y Condiciones</a></li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title"><i class="fas fa-gavel me-2"></i>Listado de Términos</h4>
                    <button type="button" class="btn btn-success btn-round ms-auto" data-bs-toggle="modal"
                        title="Crear nuevo término" data-bs-target="#addTerminoModal">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="terminos-datatables" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Aplicable a</th>
                                <th>Título</th>
                                <th>Contenido (Extracto)</th>
                                <th>Estado</th>
                                <th style="width: 10%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($terminos)): ?>
                                <?php foreach ($terminos as $term): ?>
                                    <tr>
                                        <td>
                                            <?php if (empty($term['tipo_dispositivo_id'])): ?>
                                                <span class="badge badge-info">General (Todos)</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">
                                                    <?= esc($term['nombre_dispositivo']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= esc($term['titulo']) ?>
                                        </td>
                                        <td>
                                            <small>
                                                <?= esc($term['contenido']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $term['activo'] ? 'success' : 'danger' ?>">
                                                <?= $term['activo'] ? 'Activo' : 'Inactivo' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="form-button-action">
                                                <button data-id="<?= $term['id'] ?>" data-titulo="<?= esc($term['titulo']) ?>"
                                                    data-tipo="<?= $term['tipo_dispositivo_id'] ?>"
                                                    data-contenido="<?= esc($term['contenido']) ?>" type="button"
                                                    class="btn btn-link btn-primary btn-lg btn-edit" data-bs-toggle="modal"
                                                    title="Editar" data-bs-target="#updateTerminoModal">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <button data-id="<?= $term['id'] ?>" data-titulo="<?= esc($term['titulo']) ?>"
                                                    type="button" data-bs-toggle="modal" title="Eliminar"
                                                    data-bs-target="#deleteTerminoModal"
                                                    class="btn btn-link btn-danger btn-delete">
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

<div class="modal fade" id="addTerminoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Crear Término y Condición</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-form" action="<?= base_url('admin/terminos-condiciones/crear') ?>" method="post">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Aplicable a (Dispositivo)</label>
                            <select class="form-select" name="tipo_dispositivo_id">
                                <option value="">General (Aplica a todos los dispositivos)</option>
                                <?php foreach ($tiposDispositivos as $tipo): ?>
                                    <option value="<?= $tipo['id'] ?>">
                                        <?= esc($tipo['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" name="titulo" required placeholder="Título">
                                <label>Título</label>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Contenido</label>
                            <textarea class="form-control" name="contenido" rows="5" required
                                placeholder="Escribe aquí los términos..."></textarea>
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

<div class="modal fade" id="updateTerminoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Actualizar Término</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-form" action="<?= base_url('admin/terminos-condiciones/editar') ?>" method="post">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Aplicable a (Dispositivo)</label>
                            <select class="form-select" name="tipo_dispositivo_id" id="edit-tipo">
                                <option value="">General (Aplica a todos los dispositivos)</option>
                                <?php foreach ($tiposDispositivos as $tipo): ?>
                                    <option value="<?= $tipo['id'] ?>">
                                        <?= esc($tipo['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" name="titulo" id="edit-titulo" required
                                    placeholder="Título">
                                <label>Título</label>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Contenido</label>
                            <textarea class="form-control" name="contenido" id="edit-contenido" rows="5"
                                required></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button form="edit-form" type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteTerminoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-danger modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Verificación de eliminación</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-exclamation-triangle text-danger" style="font-size: 24px"></i>
                <h4 class="text-danger mt-4">¿Estás seguro?</h4>
                <p>Vas a eliminar el término: <strong class="text-danger" id="delete-titulo-display"></strong></p>
                <form id="delete-form" action="<?= base_url('admin/terminos-condiciones/eliminar') ?>" method="post">
                    <input type="hidden" name="id" id="delete-id" />
                </form>
            </div>
            <div class="modal-footer">
                <button form="delete-form" type="submit" class="btn btn-secondary">Sí, Eliminar</button>
                <button type="button" class="btn btn-link text-primary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        $('#terminos-datatables').DataTable({
            scrollX: true,
            language: { url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json' },
            order: [[1, 'asc']], // Ordenar por Título
        });

        // Lógica para llenar el Modal de Edición
        $('body').on('click', '.btn-edit', function () {
            let id = $(this).data('id');
            let titulo = $(this).data('titulo');
            let tipo = $(this).data('tipo'); // ID del tipo de dispositivo o vacio
            let contenido = $(this).data('contenido');

            $('#edit-id').val(id);
            $('#edit-titulo').val(titulo);
            $('#edit-contenido').val(contenido);
            $('#edit-tipo').val(tipo).change(); // .change() por si usas select2
        });

        // Lógica para llenar el Modal de Eliminación
        $('body').on('click', '.btn-delete', function () {
            let id = $(this).data('id');
            let titulo = $(this).data('titulo');

            $('#delete-id').val(id);
            $('#delete-titulo-display').text(titulo);
        });
    });
</script>
<?= $this->endSection() ?>