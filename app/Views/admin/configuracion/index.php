<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?? 'Configuración' ?>
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
        <li class="nav-item">Empresa</li>
    </ul>
</div>

<form action="<?= base_url('admin/configuracion/guardar') ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= esc($config['id'] ?? '') ?>">

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-building me-2"></i>Información Corporativa</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-muted">Nombre de la Empresa / Taller <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-store"></i></span>
                                <input type="text" class="form-control form-control-lg" name="nombre_empresa" 
                                       value="<?= esc($config['nombre_empresa'] ?? '') ?>" 
                                       placeholder="Ej: TechSolutions Reparaciones" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted">Correo Electrónico <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" name="email" 
                                       value="<?= esc($config['email'] ?? '') ?>" 
                                       placeholder="contacto@empresa.com" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted">Teléfono / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-phone"></i></span>
                                <input type="text" class="form-control" name="telefono" 
                                       value="<?= esc($config['telefono'] ?? '') ?>" 
                                       placeholder="+593 999 999 999">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-muted">Dirección Física</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt"></i></span>
                                <textarea class="form-control" name="direccion" rows="2" 
                                          placeholder="Calle Principal #123 y Secundaria"><?= esc($config['direccion'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-cogs me-2"></i>Parámetros Operativos</h5>
                </div>
                <div class="card-body bg-light">
                    <label class="form-label fw-bold text-dark">Costo Base de Revisión <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-success text-white fw-bold">$</span>
                        <input type="number" step="0.01" min="0" class="form-control fw-bold text-end" 
                               name="valor_revision" 
                               value="<?= esc($config['valor_revision'] ?? '0.00') ?>" required>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="fas fa-info-circle me-1"></i> Este valor se cargará por defecto al iniciar una nueva recepción.
                    </small>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-image me-2"></i>Logo / Marca</h5>
                </div>
                <div class="card-body text-center">
                    <div class="logo-preview-container mb-3 p-2 bg-white rounded border">
                        <img id="logo-preview" 
                             src="<?= !empty($config['logo_path']) ? base_url($config['logo_path']) : 'https://placehold.co/400x200?text=Subir+Logo' ?>" 
                             alt="Logo Preview" 
                             class="img-fluid rounded" 
                             style="max-height: 150px; object-fit: contain;">
                    </div>
                    
                    <div class="mb-3">
                        <label for="logo-input" class="btn btn-outline-secondary w-100 cursor-pointer">
                            <i class="fas fa-upload me-2"></i>Seleccionar Imagen
                        </label>
                        <input type="file" class="d-none" name="logo" id="logo-input" accept="image/*">
                    </div>
                    <small class="text-muted" style="font-size: 0.8rem;">Recomendado: PNG Transparente (300x100px)</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12 text-end">
            <button type="submit" class="btn btn-success btn-lg px-5 shadow">
                <i class="fas fa-save me-2"></i>Guardar Cambios
            </button>
        </div>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Previsualización de imagen moderna
        $('#logo-input').change(function(e) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#logo-preview').attr('src', e.target.result);
            }
            if(this.files[0]){
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
</script>
<?= $this->endSection() ?>