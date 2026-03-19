<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?? 'Catálogo de Repuestos' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('admin/dashboard') ?>">
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
            <a href="#">Repuestos</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title"><i class="fas fa-tools me-2"></i>Catálogo de Repuestos</h4>
                    <button type="button" class="btn btn-success btn-round ms-auto" data-bs-toggle="modal"
                        title="Crear nuevo repuesto" data-bs-target="#addRepuestoModal">
                        <i class="fa fa-plus"></i> Nuevo Repuesto
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="repuestos-datatables" class="table table-bordered ">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Valor ($)</th>
                                <th>Stock</th>
                                <th style="width: 10%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($repuestos as $repuesto): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold">
                                            <?= esc($repuesto['nombre']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        $<?= number_format($repuesto['valor'], 2) ?>
                                    </td>
                                    <td>
                                        <?= esc($repuesto['stock']) ?>
                                    </td>
                                    <td>
                                        <div class="form-button-action">
                                            <button data-id="<?= $repuesto['id'] ?>"
                                                data-nombre="<?= $repuesto['nombre'] ?>" 
                                                data-valor="<?= $repuesto['valor'] ?>"
                                                data-stock="<?= $repuesto['stock'] ?>"
                                                type="button" title="Editar"
                                                class="btn btn-link btn-primary btn-lg btn-edit" data-bs-toggle="modal"
                                                data-bs-target="#updateRepuestoModal">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button data-id="<?= $repuesto['id'] ?>"
                                                data-nombre="<?= $repuesto['nombre'] ?>" type="button"
                                                data-bs-toggle="modal" title="Eliminar" data-bs-target="#deleteRepuestoModal"
                                                class="btn btn-link btn-danger btn-delete">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
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

<!-- Crear nuevo Repuesto -->
<div class="modal fade" id="addRepuestoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Crear Nuevo Repuesto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-form" action="<?= base_url('admin/repuestos/crear') ?>" method="post">
                    <div class="form-group mb-3">
                        <label>Nombre del Repuesto</label>
                        <input type="text" class="form-control" name="nombre" placeholder="Ej: Pantalla iPhone 11 OEM" required />
                    </div>
                    <div class="form-group mb-3">
                        <label>Valor de Venta ($)</label>
                        <input type="number" step="0.01" class="form-control" name="valor" placeholder="0.00" required />
                    </div>
                    <div class="form-group mb-3">
                        <label>Stock Inicial (Opcional)</label>
                        <input type="number" class="form-control" name="stock" value="0" />
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button form="add-form" type="submit" class="btn btn-primary">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- Editar -->
<div class="modal fade" id="updateRepuestoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Actualizar Repuesto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-form" action="<?= base_url('admin/repuestos/editar') ?>" method="post">
                    <input type="hidden" name="id" id="edit-id" />
                    <div class="form-group mb-3">
                        <label>Nombre del Repuesto</label>
                        <input type="text" class="form-control" id="edit-nombre" name="nombre" required />
                    </div>
                    <div class="form-group mb-3">
                        <label>Valor de Venta ($)</label>
                        <input type="number" step="0.01" class="form-control" id="edit-valor" name="valor" required />
                    </div>
                    <div class="form-group mb-3">
                        <label>Stock</label>
                        <input type="number" class="form-control" id="edit-stock" name="stock" />
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button form="edit-form" type="submit" class="btn btn-primary">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- Eliminar -->
<div class="modal fade" id="deleteRepuestoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-danger modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Verificación de eliminación</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="py-3 text-center">
                    <i class="fas fa-exclamation-triangle text-danger" style="font-size: 24px"></i>
                    <h4 class="text-danger mt-4">¿Eliminar repuesto?</h4>
                    <p>¿Estás seguro que deseas eliminar el repuesto <strong id="delete-nombre-display"></strong>?</p>
                </div>
                <form id="delete-form" action="<?= base_url('admin/repuestos/eliminar') ?>" method="post">
                    <input type="hidden" name="id" id="delete-id" />
                </form>
            </div>
            <div class="modal-footer">
                <button form="delete-form" type="submit" class="btn btn-danger">Confirmar</button>
                <button type="button" class="btn btn-link text-primary text-decoration-none" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function () {
        $('#repuestos-datatables').DataTable({
            scrollX: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json',
            },
            order: [[0, 'asc']]
        });

        $('body').on('click', '.btn-edit', function () {
            $('#edit-id').val($(this).data('id'));
            $('#edit-nombre').val($(this).data('nombre'));
            $('#edit-valor').val($(this).data('valor'));
            $('#edit-stock').val($(this).data('stock'));
        });

        $('body').on('click', '.btn-delete', function () {
            $('#delete-id').val($(this).data('id'));
            $('#delete-nombre-display').text($(this).data('nombre'));
        });
    });
</script>
<?= $this->endSection() ?>
