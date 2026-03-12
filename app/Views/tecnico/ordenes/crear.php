<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
Nueva Orden de Trabajo
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>

<link rel="stylesheet" href="<?= base_url("assets/css/patternLock.css") ?>">
<script src="<?= base_url("assets/js/patternLock.js") ?>"></script>

<script>
    const tiposDispositivosList = <?= json_encode($tiposDispositivos ?? []) ?>;
    const tecnicosList = <?= json_encode($tecnicos ?? []) ?>; // <--- NUEVO
</script>

<style>
    /* --- ESTILOS PERSONALIZADOS --- */

    /* 1. SelectGroup (Botones Grandes de Seguridad) */
    .selectgroup {
        display: flex;
        width: 100%;
        border-radius: 0.25rem;
        overflow: hidden;
    }

    .selectgroup-item {
        flex-grow: 1;
        position: relative;
    }

    .selectgroup-input {
        position: absolute;
        z-index: -1;
        opacity: 0;
    }

    .selectgroup-button {
        display: block;
        border: 1px solid #e4e6fc;
        text-align: center;
        padding: 0.6rem 1rem;
        cursor: pointer;
        position: relative;
        background-color: #fff;
        color: #6c757d;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .selectgroup.selectgroup-success .selectgroup-input:checked+.selectgroup-button {
        border-color: #31ce36;
        color: #31ce36;
        background: rgba(49, 206, 54, 0.15);
        font-size: 1rem;
    }

    /* 2. Sidebar Pegajoso */
    .sticky-sidebar {
        position: sticky;
        top: 20px;
        z-index: 90;
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0, 0, 0, .125);
    }

    .transition-icon {
        transition: transform 0.2s;
    }
</style>

<div x-data="ordenManager()" class="pb-5">

    <form id="main-form" class="row" action="<?= base_url('tecnico/ordenes/guardar') ?>" method="post"
        @submit.prevent="submitOrden">
        <div class="col-lg-8">
            <?= csrf_field() ?>
            <input type="hidden" name="cliente_id" :value="client?.id">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-dark m-0"><i class="fas fa-clipboard-list me-2"></i>Orden de Trabajo</h4>
                <span class="badge bg-light text-dark border">
                    <span x-text="devices.length"></span> Equipos
                </span>
            </div>

            <div class="accordion mb-3" id="devicesAccordion">
                <template x-for="(dev, index) in devices" :key="index">
                    <div class="accordion-item shadow-sm border-0 mb-3 rounded-3 overflow-hidden">
                        <h2 class="accordion-header" :id="'heading'+index">
                            <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse"
                                :data-bs-target="'#collapse'+index">
                                <span class="badge bg-primary me-2" x-text="index + 1"></span>
                                <span
                                    x-text="getNombreTipo(dev.tipo_dispositivo_id) + (dev.marca ? ': ' + dev.marca + ' ' + dev.modelo : ' - Nuevo Dispositivo')"></span>
                                <span class="badge bg-secondary ms-auto me-2" x-show="dev.tecnico_id"
                                    x-text="'Téc: ' + getNombreTecnico(dev.tecnico_id)">
                                </span>
                            </button>
                        </h2>
                        <div :id="'collapse'+index" class="accordion-collapse collapse show"
                            :data-bs-parent="'#devicesAccordion'">
                            <div class="accordion-body bg-white">
                                <div class="text-end mb-2" x-show="devices.length > 1">
                                    <button type="button"
                                        class="btn btn-sm text-danger link-danger text-decoration-none"
                                        @click="removeDevice(index)">
                                        <i class="fas fa-trash-alt"></i> Eliminar este equipo
                                    </button>
                                </div>
                                <!-- Inputs hidden para problemas (AGREGAR ESTO) -->
                                <template x-if="dev.problemas && dev.problemas.length > 0">
                                    <template x-for="(problemaId, pIndex) in dev.problemas" :key="'prob-' + pIndex">
                                        <input type="hidden"
                                            :name="'devices['+index+'][problema_reportado]['+pIndex+']'"
                                            :value="problemaId">
                                    </template>
                                </template>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">Tipo de dispositivo</label>
                                        <select class="form-select form-control"
                                            :name="'devices['+index+'][tipo_dispositivo_id]'"
                                            x-model="dev.tipo_dispositivo_id" @change="initMarcaSelect(index)" required>
                                            <option value="" disabled selected>Seleccione...</option>
                                            <template x-for="tipo in tiposList" :key="tipo.id">
                                                <option :value="tipo.id" x-text="tipo.nombre"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">Marca <span
                                                class="text-danger">*</span></label>
                                        <select :id="'marca-select-'+index" class="form-select form-control"
                                            :name="'devices['+index+'][marca_id]'"
                                            x-init="$nextTick(() => { if (dev.tipo_dispositivo_id) initMarcaSelect(index) })"
                                            required>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">Modelo</label>
                                        <select :id="'modelo-select-'+index" class="form-select form-control"
                                            :name="'devices['+index+'][modelo_id]'">
                                        </select>
                                    </div>
                                </div>

                                <div class="row mt-3">

                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Motivo de Ingreso <span
                                                class="text-danger">*</span></label>
                                        <select :id="'problema-select-'+index" class="form-control" multiple required>
                                        </select>
                                    </div>


                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">
                                            Técnico Responsable
                                        </label>
                                        <select class="form-select form-control"
                                            :name="'devices['+index+'][tecnico_id]'" x-model="dev.tecnico_id">
                                            <option value="">-- Sin asignar (Pendiente) --</option>
                                            <template x-for="tec in tecnicosList" :key="tec.id">
                                                <option :value="tec.id" x-text="tec.nombre + ' ' + tec.apellido">
                                                </option>
                                            </template>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mt-3">

                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Prioridad del dispositivo</label>
                                        <select class="form-select form-control" :name="'devices['+index+'][prioridad_dispositivo_id]'">
                                            <option value="" disabled selected>Seleccione la prioridad...</option>
                                            <?php if (!empty($prioridades)): ?>
                                                <?php foreach ($prioridades as $prioridad): ?>
                                                    <option value="<?= $prioridad['id'] ?>">
                                                        <?= esc($prioridad['nombre']) ?>
                                                        <?= ($prioridad['costo_adicional'] > 0) ? '(+$' . $prioridad['costo_adicional'] . ')' : '' ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Accesorios y Estado del
                                            Dispositivo</label>
                                        <button type="button" class="btn w-100 rounded-3"
                                            :class="(getAccesoriosCount(index) + getDetallesCount(index)) > 0 ? 'btn-success text-white' : 'btn-outline-success'"
                                            @click="openAccesoriosDetallesModal(index)">
                                            <i class="fas fa-clipboard-check me-2"></i>
                                            Gestionar
                                            <span class="badge bg-info ms-2">
                                                <i class="fas fa-headphones"></i>
                                                <span x-text="getAccesoriosCount(index)"></span>
                                            </span>
                                            <span class="badge bg-secondary ms-1">
                                                <i class="fas fa-tasks"></i>
                                                <span x-text="getDetallesCount(index)"></span>
                                            </span>
                                        </button>
                                        <!-- Inputs hidden para accesorios -->
                                        <template x-if="dev.accesorios && dev.accesorios.length > 0">
                                            <template x-for="(accesorioId, aIndex) in dev.accesorios"
                                                :key="'acc-' + aIndex">
                                                <input type="hidden"
                                                    :name="'devices['+index+'][accesorios]['+aIndex+']'"
                                                    :value="accesorioId">
                                            </template>
                                        </template>

                                        <!-- Inputs hidden para detalles -->
                                        <template x-if="dev.detalles && dev.detalles.length > 0">
                                            <template x-for="(detallesId, cIndex) in dev.detalles"
                                                :key="'chk-' + cIndex">
                                                <input type="hidden" :name="'devices['+index+'][detalles]['+cIndex+']'"
                                                    :value="detallesId">
                                            </template>
                                        </template>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <label class="form-label small fw-bold text-muted">Bloqueo de
                                        Pantalla</label>
                                    <div class="col-md-6">
                                        <div class="selectgroup w-100 selectgroup-success ">
                                            <label class="selectgroup-item">
                                                <input type="radio" :name="'devices['+index+'][tipo_pass]'"
                                                    value="ninguno" x-model="dev.tipo_pass" class="selectgroup-input">
                                                <span class="selectgroup-button"><i class="fas fa-lock-open me-1"></i>
                                                    Ninguna</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" :name="'devices['+index+'][tipo_pass]'"
                                                    value="patron" x-model="dev.tipo_pass" class="selectgroup-input">
                                                <span class="selectgroup-button"><i class="fas fa-th me-1"></i>
                                                    Patrón</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" :name="'devices['+index+'][tipo_pass]'"
                                                    value="contrasena" x-model="dev.tipo_pass"
                                                    class="selectgroup-input">
                                                <span class="selectgroup-button"><i class="fas fa-key me-1"></i>
                                                    Clave</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6" x-show="dev.tipo_pass === 'contrasena'" x-transition>
                                        <input type="text" class="form-control" :name="'devices['+index+'][pass_code]'"
                                            x-model="dev.pass_code" placeholder="Ingrese PIN o Contraseña numérica...">
                                    </div>

                                    <div class="col-md-6" x-show="dev.tipo_pass === 'patron'" x-transition>
                                        <button type="button" class="btn btn-outline-dark w-100 rounded-3"
                                            @click="openPatternModal(index)">
                                            <i class="fas fa-draw-polygon me-1"></i>
                                            <span
                                                x-text="dev.patron_data ? 'Patrón Guardado' : 'Dibujar Patrón'"></span>
                                        </button>
                                        <input type="hidden" :name="'devices['+index+'][patron_data]'"
                                            x-model="dev.patron_data">
                                    </div>

                                </div>

                                <div class="row mt-3">

                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Observaciones del
                                            cliente</label>
                                        <textarea class="form-control" :name="'devices['+index+'][observaciones]'"
                                            x-model="dev.observaciones" rows="1"
                                            placeholder="Ej: Después de la caída no encendió"></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Serie / IMEI</label>
                                        <input type="text" class="form-control" :name="'devices['+index+'][serie_imei]'"
                                            x-model="dev.serie_imei">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="row mb-5">
                <div class="col-md-8 mx-auto">
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-secondary py-2 flex-grow-1" @click="addDevice">
                            <i class="fas fa-plus me-1"></i> Agregar Nuevo Dispositivo
                        </button>
                        <button type="button" class="btn btn-outline-dark py-2 flex-grow-1" @click="cloneLastDevice">
                            <i class="fas fa-copy me-1"></i> Copiar Anterior
                        </button>
                    </div>
                    <small class="text-muted text-center d-block mt-2">Use "Copiar Anterior" si ingresa varios
                        equipos del mismo modelo.</small>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="sticky-sidebar">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body position-relative">
                        <button class="btn btn-sm btn-light text-primary position-absolute top-0 end-0 m-2"
                            @click="openModalClient('create')">
                            <i class="fas fa-user-plus"></i>
                        </button>
                        <label class="form-label small fw-bold">Cliente</label>
                        <div class="input-group">
                            <input type="text" class="form-control" x-model="searchCedula"
                                @keydown.enter.prevent="buscarCliente" placeholder="Cédula o RUC" :disabled="isLoading">
                            <button class="btn btn-primary" type="button" @click="buscarCliente" :disabled="isLoading">
                                <i class="fas" :class="isLoading ? 'fa-spinner fa-spin' : 'fa-search'"></i>
                            </button>
                        </div>
                        <div x-show="searchError" x-text="searchError" class="text-danger small mt-2"></div>
                    </div>
                </div>

                <div x-show="client" class="card shadow-sm border-0 border-start border-5 border-success mb-3"
                    x-transition>
                    <div class="card-body position-relative">
                        <button class="btn btn-sm btn-light text-primary position-absolute top-0 end-0 m-2"
                            @click="openModalClient('edit')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <h6 class="fw-bold text-dark" x-text="client?.nombres + ' ' + client?.apellidos"></h6>
                        <div class="small text-muted mt-2">
                            <div><i class="fas fa-id-card me-2 width-20"></i> <span x-text="client?.cedula"></span>
                            </div>
                            <div><i class="fas fa-phone me-2 width-20"></i> <span x-text="client?.telefono"></span>
                            </div>
                            <div x-show="client?.email"><i class="fas fa-envelope me-2 width-20"></i> <span
                                    x-text="client?.email"></span></div>
                        </div>
                        <button class="btn btn-sm btn-outline-danger w-100 mt-3" @click="resetClient">Cambiar
                            Cliente</button>
                    </div>
                </div>

                <div x-show="!client && searchExecuted && !isLoading" class="alert alert-warning text-center"
                    x-transition>
                    <i class="fas fa-user-slash fa-lg mb-2 text-warning"></i>
                    <p class="small mb-2">No encontrado. ¿Desea registrarlo?</p>
                    <button class="btn btn-dark w-100 btn-sm" @click="openModalClient('create')">Crear Nuevo
                        Cliente</button>
                </div>
            </div>

            <div class="accordion">

                <div class="card border-0 mb-3">
                    <div class="card-header bg-white rounded-3 collapsed" id="headingThree" data-bs-toggle="collapse"
                        data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        <span class="fw-semibold"><i class="fas fa-cog me-2"></i> Configuración Global</span>
                        <div class="span-mode"></div>
                    </div>
                    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                        <!-- <hr class="m-0"> -->
                        <div class="accordion-body bg-white rounded-bottom-3">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Prioridad de la orden</label>
                                <select class="form-select form-control" name="urgencia_id">
                                    <option value="" disabled selected>Seleccione la prioridad...</option>
                                    <?php if (!empty($prioridades)): ?>
                                        <?php foreach ($prioridades as $prioridad): ?>
                                            <option value="<?= $prioridad['id'] ?>">
                                                <?= esc($prioridad['nombre']) ?>
                                                <?= ($prioridad['costo_adicional'] > 0) ? '(+$' . $prioridad['costo_adicional'] . ')' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold text-primary">
                                    Asignar todo a:
                                </label>
                                <div class="input-group">
                                    <select class="form-select form-control" x-model="globalTechnician">
                                        <option value="">Seleccionar técnico...</option>
                                        <template x-for="tec in tecnicosList" :key="tec.id">
                                            <option :value="tec.id" x-text="tec.nombre"></option>
                                        </template>
                                    </select>
                                    <button class="btn btn-outline-primary" type="button" @click="applyTechnicianToAll"
                                        title="Aplicar este técnico a todos los dispositivos">
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 d-flex flex-column">
                    <div class="fw-semibold text-muted mb-2 small">Resumen</div>

                    <div class="flex-grow-1 overflow-auto small" style="max-height: 200px;">
                        <template x-for="(dev, index) in devices" :key="'res-'+index">
                            <div x-show="dev" class="d-flex justify-content-between py-2 border-bottom">
                                <div style="max-width:70%">
                                    <div class="fw-medium text-truncate"
                                        x-text="getNombreTipo(dev.tipo_dispositivo_id) || 'Nuevo equipo'">
                                    </div>
                                    <div class="text-muted small" x-text="getProblemasCount(index) + ' problema(s)'">
                                    </div>
                                </div>
                                <div class="fw-semibold text-end text-success"
                                    x-text="'$' + getDeviceTotal(index).toFixed(2)">
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="d-flex justify-content-between pt-2 mt-2 border-top">
                        <span class="fw-semibold">Total</span>
                        <span class="fw-bold text-success" x-text="'$' + getTotalOrden().toFixed(2)"></span>
                    </div>
                </div>
            </div>

            <div class="card">
                <button form="main-form" type="submit" class="btn btn-success btn-lg fw-bold px-5"
                    :disabled="!client?.id">
                    <i class="fas fa-save me-2"></i> CONFIRMAR ORDEN
                </button>
            </div>

        </div>
    </form>

    <div class="modal fade" id="clientModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"
                        x-text="modalMode === 'create' ? 'Registrar Nuevo Cliente' : 'Editar Datos'"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveClient">
                        <div class="mb-3">
                            <label class="small fw-bold">Cédula / RUC</label>
                            <input type="text" class="form-control" x-model="modalForm.cedula"
                                :readonly="modalMode==='edit'" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="small fw-bold">Nombres</label>
                                <input type="text" class="form-control" x-model="modalForm.nombres" required>
                            </div>
                            <div class="col">
                                <label class="small fw-bold">Apellidos</label>
                                <input type="text" class="form-control" x-model="modalForm.apellidos" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Teléfono Principal</label>
                            <input type="text" class="form-control" x-model="modalForm.telefono" required>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Teléfono Secundario (Opcional)</label>
                            <input type="text" class="form-control" x-model="modalForm.telefono_secundario">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Correo Electrónico</label>
                            <input type="email" class="form-control" x-model="modalForm.email">
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary fw-bold" :disabled="isSaving">
                                <span x-show="isSaving"><i class="fas fa-spinner fa-spin me-2"></i> Guardando...</span>
                                <span x-show="!isSaving">Guardar Cliente</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="patternModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="lock-wrapper mx-auto" id="patternLockDevice" style="max-width: 280px;">
                        <div class="lock" id="lock">
                            <canvas></canvas>
                            <div class="grid"></div>
                        </div>
                        <div class="info mt-3">
                            <div class="pattern empty">- - -</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer ">
                    <button type="button" class="btn btn-outline-danger text-decoration-none" @click="clearPattern">
                        <i class="fas fa-eraser me-1"></i> Limpiar
                    </button>
                    <button type="button" class="btn btn-primary" @click="savePattern">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Unificado para Accesorios y Detalles -->
    <div class="modal fade" id="modalAccesoriosDetalles" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-clipboard-check"></i>
                        Accesorios y Estado del Dispositivo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Accesorios -->
                    <div class="row">
                        <div class="col-md-6">

                            <label class="form-label fw-bold">
                                <i class="fas fa-headphones text-info"></i> Accesorios Entregados
                            </label>
                            <select id="selectAccesorios" class="form-control" multiple></select>
                            <small class="text-muted">Seleccione o escriba para crear nuevos accesorios</small>

                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                <i class="fas fa-tasks text-secondary"></i> Estado Físico del Dispositivo
                            </label>
                            <select id="selectDetalles" class="form-control" multiple></select>
                            <small class="text-muted">Marque los aspectos a revisar o cree nuevos</small>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-primary" id="btnGuardarAccesoriosDetalles">
                        <i class="fas fa-check"></i> Confirmar Selección
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ordenManager', () => ({
            // ------------------------------------------------------------------
            // ESTADO DE LA APLICACIÓN
            // ------------------------------------------------------------------
            patternLockInstance: null,
            csrfToken: '<?= csrf_token() ?>',
            csrfHash: '<?= csrf_hash() ?>',
            tiposList: tiposDispositivosList,
            searchCedula: '',
            searchExecuted: false,
            isLoading: false,
            isSaving: false,
            searchError: '',
            client: null,
            activeDeviceIndex: null,
            tecnicosList: tecnicosList,
            globalTechnician: '',

            // Accesorios y Detalles
            bsModalAccesoriosDetalles: null,
            tomSelectAccesorios: null,
            tomSelectDetalles: null,
            activeDeviceIndexForModal: null,
            deviceSelections: {},

            // Instancias de TomSelect
            tomSelectInstances: {
                marcas: {},
                modelos: {},
                problemas: {}
            },

            devices: [
                {
                    tipo_dispositivo_id: '',
                    marca_id: '',
                    modelo_id: '',
                    serie_imei: '',
                    tipo_pass: 'ninguno',
                    pass_code: '',
                    patron_data: '',
                    problema: '',
                    problemas: [],
                    problemas_data: [],
                    observaciones: '',
                    tecnico_id: '',
                    accesorios: [],
                    detalles: []
                }
            ],

            bsModalPattern: null,
            bsModalClient: null,
            modalForm: {},
            modalMode: 'create',

            init() {
                this.bsModalPattern = new bootstrap.Modal(document.getElementById('patternModal'));
                this.bsModalClient = new bootstrap.Modal(document.getElementById('clientModal'));

                this.initAccesoriosDetallesModal();
                document.getElementById('btnGuardarAccesoriosDetalles').addEventListener('click', () => {
                    this.guardarAccesoriosDetalles();
                });
                this.$nextTick(() => {
                    this.initProblemaSelect(0);
                });
            },

            hasTomSelect(elementId) {
                const element = document.getElementById(elementId);
                return element && (element.tomselect || this.tomSelectInstances.marcas[elementId] ||
                    this.tomSelectInstances.modelos[elementId] ||
                    this.tomSelectInstances.problemas[elementId]);
            },

            // ==========================================================
            // ACCESORIOS Y DETALLES
            // ==========================================================

            initAccesoriosDetallesModal() {
                this.bsModalAccesoriosDetalles = new bootstrap.Modal(
                    document.getElementById('modalAccesoriosDetalles')
                );
            },

            openAccesoriosDetallesModal(index) {
                const dev = this.devices[index];

                if (!dev.tipo_dispositivo_id) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Atención', 'Seleccione primero el Tipo de Dispositivo', 'warning');
                    } else {
                        alert('Seleccione primero el Tipo de Dispositivo');
                    }
                    return;
                }

                this.activeDeviceIndexForModal = index;
                this.bsModalAccesoriosDetalles.show();

                setTimeout(() => {
                    this.initTomSelectAccesorios(index);
                    this.initTomSelectDetalles(index);
                }, 300);
            },

            initTomSelectAccesorios(index) {
                const selectElement = document.getElementById('selectAccesorios');

                if (this.tomSelectAccesorios) {
                    try {
                        this.tomSelectAccesorios.destroy();
                    } catch (e) {
                        console.warn('Error destroying accesorios select:', e);
                    }
                    this.tomSelectAccesorios = null;
                }

                if (!selectElement) return;

                const dev = this.devices[index];
                const self = this;

                selectElement.classList.remove('form-control');

                this.tomSelectAccesorios = new TomSelect(selectElement, {
                    plugins: ['remove_button'],
                    valueField: 'value',
                    labelField: 'text',
                    searchField: ['text'],
                    multiple: true,
                    preload: true,
                    placeholder: 'Busque o escriba un accesorio...',
                    loadingClass: 'loading',
                    load: function (query, callback) {
                        self.buscarAccesorios(query, dev.tipo_dispositivo_id, callback);
                    },
                    render: {
                        option: function (data, escape) {
                            return `<div style="padding:6px 8px;">
                            <i class="fas fa-box text-info"></i> ${escape(data.text)}
                        </div>`;
                        },
                        no_results: function () {
                            return '<div style="padding:12px; text-align:center; color:#6c757d;">No se encontraron resultados. Presione Enter para crear nuevo accesorio.</div>';
                        }
                    }
                });

                const input = this.tomSelectAccesorios.control_input;
                input.addEventListener('keydown', async (e) => {
                    if (e.key === 'Enter' && self.tomSelectAccesorios.isOpen &&
                        self.tomSelectAccesorios.currentResults.total === 0) {
                        e.preventDefault();
                        e.stopPropagation();

                        const valorInput = input.value.trim();
                        if (valorInput === '') return;

                        await self.crearAccesorio(valorInput, dev.tipo_dispositivo_id, self.tomSelectAccesorios);
                    }
                });

                if (!this.deviceSelections[index]) {
                    this.deviceSelections[index] = { accesorios: [], detalles: [] };
                }

                this.$nextTick(() => {
                    if (this.deviceSelections[index].accesorios.length > 0) {
                        this.tomSelectAccesorios.setValue(this.deviceSelections[index].accesorios, true);
                    }
                });
            },

            initTomSelectDetalles(index) {
                const selectElement = document.getElementById('selectDetalles');

                if (this.tomSelectDetalles) {
                    try {
                        this.tomSelectDetalles.destroy();
                    } catch (e) {
                        console.warn('Error destroying detalles select:', e);
                    }
                    this.tomSelectDetalles = null;
                }

                if (!selectElement) return;

                const dev = this.devices[index];
                const self = this;

                selectElement.classList.remove('form-control');

                this.tomSelectDetalles = new TomSelect(selectElement, {
                    plugins: ['remove_button'],
                    valueField: 'value',
                    labelField: 'text',
                    searchField: ['text'],
                    multiple: true,
                    preload: true,
                    placeholder: 'Busque o escriba un aspecto a revisar...',
                    loadingClass: 'loading',
                    load: function (query, callback) {
                        self.buscarDetalles(query, dev.tipo_dispositivo_id, callback);
                    },
                    render: {
                        option: function (data, escape) {
                            const colores = {
                                fisico: '#6c757d',
                                funcional: '#0dcaf0',
                                estetico: '#ffc107',
                                accesorio: '#198754'
                            };
                            const etiquetas = {
                                fisico: 'Físico',
                                funcional: 'Funcional',
                                estetico: 'Estético',
                                accesorio: 'Accesorio'
                            };
                            const color = colores[data.categoria] || '#6c757d';
                            const etiqueta = etiquetas[data.categoria] || 'General';
                            const textColor = data.categoria === 'estetico' ? '#333' : '#fff';

                            const critico = data.es_critico
                                ? '<span class="badge bg-danger ms-2" style="font-size:0.65rem;">Crítico</span>'
                                : '';
                            const foto = data.requiere_foto
                                ? '<i class="fas fa-camera text-warning ms-1"></i>'
                                : '';

                            return `<div style="display:flex; align-items:center; gap:8px; padding:6px 8px;">
                            <span style="background:${color}; color:${textColor}; font-size:0.68rem; padding:2px 7px; border-radius:10px; white-space:nowrap; flex-shrink:0;">${etiqueta}</span>
                            <span style="flex-grow:1;">${escape(data.text)}</span>
                            ${critico}${foto}
                        </div>`;
                        },
                        no_results: function () {
                            return '<div style="padding:12px; text-align:center; color:#6c757d;">No se encontraron resultados. Presione Enter para crear nuevo item.</div>';
                        }
                    }
                });

                const input = this.tomSelectDetalles.control_input;
                input.addEventListener('keydown', async (e) => {
                    if (e.key === 'Enter' && self.tomSelectDetalles.isOpen &&
                        self.tomSelectDetalles.currentResults.total === 0) {
                        e.preventDefault();
                        e.stopPropagation();

                        const valorInput = input.value.trim();
                        if (valorInput === '') return;

                        await self.crearDetalles(valorInput, dev.tipo_dispositivo_id, self.tomSelectDetalles);
                    }
                });

                if (!this.deviceSelections[index]) {
                    this.deviceSelections[index] = { accesorios: [], detalles: [] };
                }

                this.$nextTick(() => {
                    if (this.deviceSelections[index].detalles.length > 0) {
                        this.tomSelectDetalles.setValue(this.deviceSelections[index].detalles, true);
                    }
                });
            },

            async buscarAccesorios(query, tipoId, callback) {
                try {
                    let url = `<?= base_url('global/buscar-accesorios') ?>?q=${encodeURIComponent(query)}`;
                    if (tipoId) url += `&tipo=${encodeURIComponent(tipoId)}`;

                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfHash
                        }
                    });

                    const data = await response.json();
                    const opciones = data.map(item => ({
                        value: String(item.id || item.value),
                        text: item.nombre || item.text
                    }));

                    callback(opciones);
                } catch (error) {
                    console.error('Error cargando accesorios:', error);
                    callback();
                }
            },

            async buscarDetalles(query, tipoId, callback) {
                try {
                    let url = `<?= base_url('global/buscar-detalles') ?>?q=${encodeURIComponent(query)}`;
                    if (tipoId) url += `&tipo=${encodeURIComponent(tipoId)}`;

                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfHash
                        }
                    });

                    const data = await response.json();
                    const opciones = data.map(item => ({
                        value: String(item.id || item.value),
                        text: item.nombre || item.text,
                        categoria: item.categoria,
                        es_critico: item.es_critico,
                        requiere_foto: item.requiere_foto
                    }));

                    callback(opciones);
                } catch (error) {
                    console.error('Error cargando detalles:', error);
                    callback();
                }
            },

            async crearAccesorio(nombre, tipoId, tsInstance) {
                tsInstance.setTextboxValue('Creando...');
                tsInstance.lock();

                try {
                    const response = await fetch('<?= base_url('global/crear-accesorio') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfHash
                        },
                        body: JSON.stringify({
                            nombre: nombre.trim(),
                            tipo_dispositivo_id: tipoId || null,
                            [this.csrfToken]: this.csrfHash
                        })
                    });

                    const data = await response.json();
                    if (data.token) this.csrfHash = data.token;

                    if (data.status === 'success') {
                        const nuevoAccesorio = { value: String(data.id), text: data.nombre };
                        tsInstance.addOption(nuevoAccesorio);
                        tsInstance.addItem(nuevoAccesorio.value);
                        tsInstance.setTextboxValue('');

                        if (typeof Swal !== 'undefined') {
                            showAlert('success', `El accesorio "${data.nombre}" fue creado exitosamente`, 'top-end');
                        }
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo crear el accesorio'));
                    }
                } catch (error) {
                    console.error('Error creando accesorio:', error);
                    alert('Error de conexión al crear el accesorio');
                } finally {
                    tsInstance.unlock();
                    tsInstance.close();
                }
            },

            async crearDetalles(nombre, tipoId, tsInstance) {
                tsInstance.setTextboxValue('Creando...');
                tsInstance.lock();

                try {
                    const response = await fetch('<?= base_url('global/crear-detalles') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfHash
                        },
                        body: JSON.stringify({
                            nombre: nombre.trim(),
                            tipo_dispositivo_id: tipoId || null,
                            [this.csrfToken]: this.csrfHash
                        })
                    });

                    const data = await response.json();
                    if (data.token) this.csrfHash = data.token;

                    if (data.status === 'success') {
                        const nuevoItem = {
                            value: String(data.id),
                            text: data.nombre,
                            categoria: data.categoria,
                            es_critico: data.es_critico,
                            requiere_foto: data.requiere_foto
                        };
                        tsInstance.addOption(nuevoItem);
                        tsInstance.addItem(nuevoItem.value);
                        tsInstance.setTextboxValue('');

                        if (typeof Swal !== 'undefined') {
                            showAlert('success', `El detalle "${data.nombre}" fue creado exitosamente`, 'top-end');
                        }
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo crear el item'));
                    }
                } catch (error) {
                    console.error('Error creando item:', error);
                    alert('Error de conexión al crear el item');
                } finally {
                    tsInstance.unlock();
                    tsInstance.close();
                }
            },

            guardarAccesoriosDetalles() {
                if (this.activeDeviceIndexForModal === null) return;

                const index = this.activeDeviceIndexForModal;

                const accesoriosSeleccionados = this.tomSelectAccesorios ?
                    this.tomSelectAccesorios.getValue() : [];
                const detallesSeleccionados = this.tomSelectDetalles ?
                    this.tomSelectDetalles.getValue() : [];

                if (!this.devices[index].accesorios) this.devices[index].accesorios = [];
                if (!this.devices[index].detalles) this.devices[index].detalles = [];

                this.devices[index].accesorios = Array.isArray(accesoriosSeleccionados) ?
                    accesoriosSeleccionados : (accesoriosSeleccionados ? [accesoriosSeleccionados] : []);

                this.devices[index].detalles = Array.isArray(detallesSeleccionados) ?
                    detallesSeleccionados : (detallesSeleccionados ? [detallesSeleccionados] : []);

                if (!this.deviceSelections[index]) {
                    this.deviceSelections[index] = { accesorios: [], detalles: [] };
                }

                this.deviceSelections[index].accesorios = [...this.devices[index].accesorios];
                this.deviceSelections[index].detalles = [...this.devices[index].detalles];

                this.$nextTick(() => {
                    this.devices = [...this.devices];
                });

                this.bsModalAccesoriosDetalles.hide();
                this.activeDeviceIndexForModal = null;

                if (typeof Swal !== 'undefined') {
                    showAlert('success', `${this.devices[index].accesorios.length} accesorios y ${this.devices[index].detalles.length} detalles cargados`, 'top-end');
                }
            },

            getAccesoriosCount(index) {
                return this.devices[index]?.accesorios?.length || 0;
            },

            getDetallesCount(index) {
                return this.devices[index]?.detalles?.length || 0;
            },

            // ------------------------------------------------------------------
            // ✅ FIX APLICADO: TOMSELECT - PROBLEMAS COMUNES
            // ------------------------------------------------------------------
            initProblemaSelect(index) {
                const selectElement = document.getElementById(`problema-select-${index}`);

                // Destruir instancia previa si existe
                if (selectElement && selectElement.tomselect) {
                    console.warn(`TomSelect ya existe en problema-select-${index}, destruyendo primero`);
                    this.destroyProblemaSelect(index);
                }

                this.$nextTick(() => {
                    setTimeout(() => {
                        const selectElement = document.getElementById(`problema-select-${index}`);
                        if (!selectElement) return;

                        // Guard adicional
                        if (selectElement.tomselect) {
                            console.warn(`TomSelect todavía existe en problema-select-${index}, saliendo`);
                            return;
                        }

                        const dev = this.devices[index];
                        const self = this;

                        selectElement.classList.remove('form-control');

                        const ts = new TomSelect(selectElement, {
                            plugins: ['remove_button'],
                            valueField: 'value',
                            labelField: 'text',
                            searchField: ['text'],
                            multiple: true,
                            preload: true,
                            placeholder: 'Busque o escriba un problema...',
                            loadingClass: 'loading',
                            load: function (query, callback) {
                                self.buscarProblemas(
                                    query,
                                    dev.tipo_dispositivo_id || null,
                                    dev.modelo_id || null,
                                    callback
                                );
                            },
                            render: {
                                option: function (data, escape) {
                                    const precio = (parseFloat(data.precio_mano_obra || 0) + parseFloat(data.precio_repuesto || 0));
                                    const precioStr = precio > 0
                                        ? `<span style="color:#198754; font-weight:600; margin-left:auto; white-space:nowrap;">$${precio.toFixed(2)}</span>`
                                        : `<span style="color:#6c757d; margin-left:auto; white-space:nowrap;">$0.00</span>`;

                                    const tiempoH = data.tiempo ? Math.round(data.tiempo / 60) || 1 : null;
                                    const tiempoStr = tiempoH
                                        ? `<span style="color:#6c757d; font-size:0.72rem; margin-left:8px;">${tiempoH} h</span>`
                                        : '';

                                    return `<div style="display:flex; align-items:center; gap:8px; padding:6px 8px;">
                                    <span style="flex-grow:1;">${escape(data.text)}</span>
                                    ${tiempoStr}
                                    ${precioStr}
                                </div>`;
                                },
                                no_results: function () {
                                    return '<div style="padding:12px; text-align:center; color:#6c757d;">No encontrado. Presione Enter para crear.</div>';
                                }
                            },
                            // ✅ FIX 1: Usar [...values] para forzar reactividad en Alpine
                            onChange: function (values) {
                                self.devices[index].problemas = [...values];
                                self.devices[index].problemas_data = values.map(v => {
                                    const opt = ts.options[v];
                                    return {
                                        problema_id: v,
                                        nombre: opt?.nombre ?? opt?.text ?? '',
                                        tiempo_reparacion: opt?.tiempo ?? 0,
                                        precio_mano_obra: opt?.precio_mano_obra ?? 0,
                                        precio_repuesto: opt?.precio_repuesto ?? 0,
                                    };
                                });
                                // ✅ FIX 1: Forzar re-render del resumen en Alpine
                                self.devices = [...self.devices];
                            }
                        });

                        // Evento para crear problema con Enter
                        const input = ts.control_input;
                        let ultimoTipoId = dev.tipo_dispositivo_id || null;
                        let ultimoModeloId = dev.modelo_id || null;

                        input.addEventListener('keydown', async (e) => {
                            if (e.key === 'Enter' && ts.isOpen && ts.currentResults.total === 0) {
                                e.preventDefault();
                                e.stopPropagation();

                                const valorInput = input.value.trim();
                                if (valorInput === '') return;

                                await self.crearProblema(valorInput, dev.tipo_dispositivo_id || null, ts);
                            }
                        });

                        // ✅ FIX 4: Limpiar items visualmente + opciones cuando cambia tipo o modelo
                        ts.on('dropdown_open', function () {
                            const tipoActual = self.devices[index].tipo_dispositivo_id || null;
                            const modeloActual = self.devices[index].modelo_id || null;

                            if (tipoActual !== ultimoTipoId || modeloActual !== ultimoModeloId) {
                                ultimoTipoId = tipoActual;
                                ultimoModeloId = modeloActual;
                                ts.clear(true);       // ✅ FIX 4: limpiar selección visualmente sin disparar onChange
                                ts.clearOptions();
                                ts.load('');
                            }
                        });

                        this.tomSelectInstances.problemas[index] = ts;
                    }, 100);
                });
            },

            async buscarProblemas(query, tipoId, modeloId, callback) {
                try {
                    let url = `<?= base_url('global/buscar-problemas-comunes') ?>?q=${encodeURIComponent(query)}`;
                    if (tipoId) url += `&tipo=${encodeURIComponent(tipoId)}`;
                    if (modeloId) url += `&modelo=${encodeURIComponent(modeloId)}`;

                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfHash
                        }
                    });

                    const data = await response.json();
                    const opciones = data.map(item => ({
                        value: String(item.value),
                        text: item.text,
                        nombre: item.nombre,
                        tiempo: item.tiempo,
                        precio_mano_obra: item.precio_mano_obra,
                        precio_repuesto: item.precio_repuesto,
                        precio_total: item.precio_total,
                    }));

                    callback(opciones);
                } catch (error) {
                    console.error('Error cargando problemas:', error);
                    callback();
                }
            },

            async crearProblema(nombre, tipoId, tsInstance) {
                if (!tipoId) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Tipo requerido',
                            text: 'Debes seleccionar un tipo de dispositivo antes de crear el problema'
                        });
                    } else {
                        alert('Debes seleccionar un tipo de dispositivo');
                    }
                    return;
                }

                tsInstance.setTextboxValue('Creando...');
                tsInstance.lock();

                try {
                    const response = await fetch('<?= base_url('global/crear-problema-comun') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfHash
                        },
                        body: JSON.stringify({
                            nombre: nombre.trim(),
                            tipo_dispositivo_id: tipoId || null,
                            [this.csrfToken]: this.csrfHash
                        })
                    });

                    const data = await response.json();
                    if (data.token) this.csrfHash = data.token;

                    if (data.status === 'success') {
                        const nuevoProblema = {
                            value: String(data.id),
                            text: data.nombre,
                            nombre: data.nombre,
                            tiempo: data.tiempo,
                            precio_mano_obra: 0,
                            precio_repuesto: 0,
                        };

                        tsInstance.addOption(nuevoProblema);
                        tsInstance.addItem(nuevoProblema.value);
                        tsInstance.setTextboxValue('');

                        if (typeof Swal !== 'undefined') {
                            showAlert('success', `El problema "${data.nombre}" fue creado exitosamente`, 'top-end');
                        }
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo crear el problema'));
                    }
                } catch (error) {
                    console.error('Error creando problema:', error);
                    alert('Error de conexión al crear el problema');
                } finally {
                    tsInstance.unlock();
                    tsInstance.close();
                }
            },

            destroyProblemaSelect(index) {
                if (this.tomSelectInstances.problemas[index]) {
                    try {
                        this.tomSelectInstances.problemas[index].destroy();
                    } catch (error) {
                        console.warn(`Error destroying problema select ${index}:`, error);
                    }
                    delete this.tomSelectInstances.problemas[index];
                }

                const selectElement = document.getElementById(`problema-select-${index}`);
                if (selectElement && selectElement.tomselect) {
                    try {
                        selectElement.tomselect.destroy();
                    } catch (error) {
                        console.warn(`Error destroying DOM tomselect for problema ${index}:`, error);
                    }
                }
            },

            // ✅ FIX 3: Corregido - buscarProblemas ahora recibe 4 argumentos correctamente
            initProblemaSelectWithValues(index, valores) {
                this.destroyProblemaSelect(index);

                this.$nextTick(() => {
                    setTimeout(() => {
                        const selectElement = document.getElementById(`problema-select-${index}`);
                        if (!selectElement) return;

                        const dev = this.devices[index];
                        const self = this;

                        selectElement.classList.remove('form-control');

                        const ts = new TomSelect(selectElement, {
                            plugins: ['remove_button'],
                            valueField: 'value',
                            labelField: 'text',
                            searchField: ['text'],
                            multiple: true,
                            preload: true,
                            placeholder: 'Busque o escriba un problema...',
                            loadingClass: 'loading',
                            load: function (query, callback) {
                                self.buscarProblemas(
                                    query,
                                    dev.tipo_dispositivo_id || null,
                                    dev.modelo_id || null,
                                    callback
                                );
                            },
                            render: {
                                option: function (data, escape) {
                                    const precio = (parseFloat(data.precio_mano_obra || 0) + parseFloat(data.precio_repuesto || 0));
                                    const precioStr = precio > 0
                                        ? `<span style="color:#198754; font-weight:600; margin-left:auto; white-space:nowrap;">$${precio.toFixed(2)}</span>`
                                        : `<span style="color:#6c757d; margin-left:auto; white-space:nowrap;">$0.00</span>`;

                                    const tiempoH = data.tiempo ? Math.round(data.tiempo / 60) || 1 : null;
                                    const tiempoStr = tiempoH
                                        ? `<span style="color:#6c757d; font-size:0.72rem; margin-left:8px;">${tiempoH} h</span>`
                                        : '';

                                    return `<div style="display:flex; align-items:center; gap:8px; padding:6px 8px;">
                                    <span style="flex-grow:1;">${escape(data.text)}</span>
                                    ${tiempoStr}
                                    ${precioStr}
                                </div>`;
                                },
                                no_results: function () {
                                    return '<div style="padding:12px; text-align:center; color:#6c757d;">No encontrado. Presione Enter para crear.</div>';
                                }
                            },
                            // ✅ FIX 1: Reactividad Alpine en initProblemaSelectWithValues
                            onChange: function (values) {
                                self.devices[index].problemas = [...values];
                                self.devices[index].problemas_data = values.map(v => {
                                    const opt = ts.options[v];
                                    return {
                                        problema_id: v,
                                        nombre: opt?.nombre ?? opt?.text ?? '',
                                        tiempo_reparacion: opt?.tiempo ?? 0,
                                        precio_mano_obra: opt?.precio_mano_obra ?? 0,
                                        precio_repuesto: opt?.precio_repuesto ?? 0,
                                    };
                                });
                                // ✅ Forzar re-render del resumen
                                self.devices = [...self.devices];
                            },
                            onInitialize: function () {
                                if (valores && valores.length > 0) {
                                    // ✅ FIX 3: 4 argumentos correctos - query, tipoId, modeloId, callback
                                    self.buscarProblemas(
                                        '',
                                        dev.tipo_dispositivo_id || null,
                                        dev.modelo_id || null,
                                        (opciones) => {
                                            if (!opciones) return;
                                            opciones.forEach(opcion => ts.addOption(opcion));
                                            valores.forEach(valor => ts.addItem(valor, true));
                                            self.devices[index].problemas = [...valores];
                                            self.devices = [...self.devices];
                                        }
                                    );
                                }
                            },
                        });

                        const input = ts.control_input;
                        let ultimoTipoId = dev.tipo_dispositivo_id || null;
                        let ultimoModeloId = dev.modelo_id || null;

                        input.addEventListener('keydown', async (e) => {
                            if (e.key === 'Enter' && ts.isOpen && ts.currentResults.total === 0) {
                                e.preventDefault();
                                e.stopPropagation();

                                const valorInput = input.value.trim();
                                if (valorInput === '') return;

                                await self.crearProblema(valorInput, dev.tipo_dispositivo_id || null, ts);
                            }
                        });

                        // ✅ FIX 4: limpiar items y opciones cuando cambia tipo o modelo
                        ts.on('dropdown_open', function () {
                            const tipoActual = self.devices[index].tipo_dispositivo_id || null;
                            const modeloActual = self.devices[index].modelo_id || null;

                            if (tipoActual !== ultimoTipoId || modeloActual !== ultimoModeloId) {
                                ultimoTipoId = tipoActual;
                                ultimoModeloId = modeloActual;
                                ts.clear(true);       // ✅ FIX 4: limpiar items visualmente
                                ts.clearOptions();
                                ts.load('');
                            }
                        });

                        this.tomSelectInstances.problemas[index] = ts;
                    }, 100);
                });
            },

            // ------------------------------------------------------------------
            // ✅ FIX APLICADO: TOMSELECT - MARCAS
            // ------------------------------------------------------------------
            initMarcaSelect(index) {
                const dev = this.devices[index];
                const tipoId = dev.tipo_dispositivo_id;

                // Limpiar valores previos
                dev.marca_id = '';
                dev.modelo_id = '';

                // Destruir instancias previas
                this.destroyMarcaSelect(index);
                this.destroyModeloSelect(index);

                // Reinicializar problemas cuando cambia el tipo (para recargar filtrados)
                this.destroyProblemaSelect(index);
                this.initProblemaSelect(index);

                if (!tipoId) return;

                this.$nextTick(() => {
                    setTimeout(() => {
                        const selectElement = document.getElementById(`marca-select-${index}`);
                        if (!selectElement) return;

                        const self = this;
                        selectElement.classList.remove('form-select', 'form-control');

                        const ts = new TomSelect(selectElement, {
                            valueField: 'value',
                            labelField: 'text',
                            searchField: ['text'],
                            preload: true,
                            placeholder: 'Busque o escriba una marca...',
                            loadingClass: 'loading',
                            create: false,
                            load: function (query, callback) {
                                self.buscarMarcas(query, tipoId, callback);
                            },
                            render: {
                                option: function (data, escape) {
                                    return `<div style="padding:6px 8px;"><span>${escape(data.text)}</span></div>`;
                                },
                                no_results: function () {
                                    return '<div style="padding:12px; text-align:center; color:#6c757d;">No se encontraron resultados. Presione Enter para crear nueva marca.</div>';
                                }
                            },
                            onItemAdd: function (value) {
                                self.devices[index].marca_id = value;
                                self.initModeloSelect(index, value);
                            }
                        });

                        const input = ts.control_input;
                        input.addEventListener('keydown', async (e) => {
                            if (e.key === 'Enter' && ts.isOpen && ts.currentResults.total === 0) {
                                e.preventDefault();
                                e.stopPropagation();

                                const valorInput = input.value.trim();
                                if (valorInput === '') return;

                                await self.crearMarca(valorInput, tipoId, ts);
                            }
                        });

                        this.tomSelectInstances.marcas[index] = ts;
                    }, 100);
                });
            },

            async buscarMarcas(query, tipoId, callback) {
                try {
                    const url = `<?= base_url('global/buscar-marcas') ?>`
                        + `?q=${encodeURIComponent(query)}`
                        + `&tipo=${encodeURIComponent(tipoId)}`;

                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfHash
                        }
                    });

                    const data = await response.json();
                    const opciones = data.map(item => ({
                        value: String(item.id || item.value),
                        text: item.nombre || item.text
                    }));

                    callback(opciones);
                } catch (error) {
                    console.error('Error cargando marcas:', error);
                    callback();
                }
            },

            async crearMarca(nombre, tipoId, tsInstance) {
                tsInstance.setTextboxValue('Creando...');
                tsInstance.lock();

                try {
                    const response = await fetch('<?= base_url('global/crear-marca') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfHash
                        },
                        body: JSON.stringify({
                            nombre: nombre,
                            tipo_dispositivo_id: tipoId,
                            [this.csrfToken]: this.csrfHash
                        })
                    });

                    const data = await response.json();
                    if (data.token) this.csrfHash = data.token;

                    if (data.status === 'success') {
                        const nuevaMarca = { value: String(data.id), text: data.nombre };
                        tsInstance.addOption(nuevaMarca);
                        tsInstance.addItem(nuevaMarca.value);
                        tsInstance.setTextboxValue('');

                        if (typeof Swal !== 'undefined') {
                            showAlert('success', `La marca "${data.nombre}" fue creada exitosamente`, 'top-end');
                        }
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo crear la marca'));
                    }
                } catch (error) {
                    console.error('Error creando marca:', error);
                    alert('Error de conexión al crear la marca');
                } finally {
                    tsInstance.unlock();
                    tsInstance.close();
                }
            },

            destroyMarcaSelect(index) {
                if (this.tomSelectInstances.marcas[index]) {
                    try {
                        this.tomSelectInstances.marcas[index].destroy();
                    } catch (error) {
                        console.warn(`Error destroying marca select ${index}:`, error);
                    }
                    delete this.tomSelectInstances.marcas[index];
                }

                const selectElement = document.getElementById(`marca-select-${index}`);
                if (selectElement && selectElement.tomselect) {
                    try {
                        selectElement.tomselect.destroy();
                    } catch (error) {
                        console.warn(`Error destroying DOM tomselect for marca ${index}:`, error);
                    }
                }
            },

            // ------------------------------------------------------------------
            // ✅ FIX APLICADO: TOMSELECT - MODELOS
            // ------------------------------------------------------------------
            initModeloSelect(index, marcaId) {
                this.destroyModeloSelect(index);

                if (!marcaId) return;

                this.$nextTick(() => {
                    setTimeout(() => {
                        const selectElement = document.getElementById(`modelo-select-${index}`);
                        if (!selectElement) return;

                        selectElement.classList.remove('form-select', 'form-control');
                        const self = this;

                        const ts = new TomSelect(selectElement, {
                            valueField: 'value',
                            labelField: 'text',
                            searchField: ['text'],
                            preload: true,
                            placeholder: 'Busque o escriba un modelo...',
                            loadingClass: 'loading',
                            create: false,
                            load: function (query, callback) {
                                self.buscarModelos(query, marcaId, callback);
                            },
                            render: {
                                option: function (data, escape) {
                                    return `<div style="padding:6px 8px;"><span>${escape(data.text)}</span></div>`;
                                },
                                no_results: function () {
                                    return '<div style="padding:12px; text-align:center; color:#6c757d;">No se encontraron resultados. Presione Enter para crear nuevo modelo.</div>';
                                }
                            },
                            // ✅ FIX 2: Al cambiar modelo, limpiar problemas seleccionados y recargar
                            onItemAdd: function (value) {
                                const modeloAnterior = self.devices[index].modelo_id;
                                self.devices[index].modelo_id = value;

                                // Si el modelo cambió y ya había problemas seleccionados, limpiarlos
                                if (modeloAnterior && modeloAnterior !== value && self.devices[index].problemas?.length > 0) {
                                    self.devices[index].problemas = [];
                                    self.devices[index].problemas_data = [];
                                    self.devices = [...self.devices];
                                }

                                // ✅ FIX 2: Forzar recarga del select de problemas con el nuevo modelo
                                const tsProblema = self.tomSelectInstances.problemas[index];
                                if (tsProblema) {
                                    tsProblema.clear(true);       // limpiar items seleccionados visualmente
                                    tsProblema.clearOptions();    // limpiar opciones del dropdown
                                    tsProblema.load('');          // recargar con nuevo modelo
                                }
                            }
                        });

                        const input = ts.control_input;
                        input.addEventListener('keydown', async (e) => {
                            if (e.key === 'Enter' && ts.isOpen && ts.currentResults.total === 0) {
                                e.preventDefault();
                                e.stopPropagation();

                                const valorInput = input.value.trim();
                                if (valorInput === '') return;

                                await self.crearModelo(valorInput, marcaId, ts);
                            }
                        });

                        this.tomSelectInstances.modelos[index] = ts;
                    }, 100);
                });
            },

            async buscarModelos(query, marcaId, callback) {
                try {
                    const url = `<?= base_url('global/buscar-modelos') ?>`
                        + `?q=${encodeURIComponent(query)}`
                        + `&marca=${encodeURIComponent(marcaId)}`;

                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfHash
                        }
                    });

                    const data = await response.json();
                    const opciones = data.map(item => ({
                        value: String(item.id || item.value),
                        text: item.nombre || item.text
                    }));

                    callback(opciones);
                } catch (error) {
                    console.error('Error cargando modelos:', error);
                    callback();
                }
            },

            async crearModelo(nombre, marcaId, tsInstance) {
                tsInstance.setTextboxValue('Creando...');
                tsInstance.lock();

                try {
                    const response = await fetch('<?= base_url('global/crear-modelo') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfHash
                        },
                        body: JSON.stringify({
                            nombre: nombre,
                            marca_id: marcaId,
                            [this.csrfToken]: this.csrfHash
                        })
                    });

                    const data = await response.json();
                    if (data.token) this.csrfHash = data.token;

                    if (data.status === 'success') {
                        const nuevoModelo = { value: String(data.id), text: data.nombre };
                        tsInstance.addOption(nuevoModelo);
                        tsInstance.addItem(nuevoModelo.value);
                        tsInstance.setTextboxValue('');

                        if (typeof Swal !== 'undefined') {
                            showAlert('success', `El modelo "${data.nombre}" fue creado exitosamente`, 'top-end');
                        }
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo crear el modelo'));
                    }
                } catch (error) {
                    console.error('Error creando modelo:', error);
                    alert('Error de conexión al crear el modelo');
                } finally {
                    tsInstance.unlock();
                    tsInstance.close();
                }
            },

            destroyModeloSelect(index) {
                if (this.tomSelectInstances.modelos[index]) {
                    try {
                        this.tomSelectInstances.modelos[index].destroy();
                    } catch (error) {
                        console.warn(`Error destroying modelo select ${index}:`, error);
                    }
                    delete this.tomSelectInstances.modelos[index];
                }

                const selectElement = document.getElementById(`modelo-select-${index}`);
                if (selectElement && selectElement.tomselect) {
                    try {
                        selectElement.tomselect.destroy();
                    } catch (error) {
                        console.warn(`Error destroying DOM tomselect for modelo ${index}:`, error);
                    }
                }
            },

            // ------------------------------------------------------------------
            // LÓGICA DE DISPOSITIVOS
            // ------------------------------------------------------------------
            getNombreTecnico(id) {
                if (!id) return '';
                let tec = this.tecnicosList.find(t => t.id == id);
                return tec ? tec.nombre : '';
            },

            applyTechnicianToAll() {
                if (!this.globalTechnician) return;
                this.devices.forEach(dev => {
                    dev.tecnico_id = this.globalTechnician;
                });
            },

            getNombreTipo(id) {
                if (!id) return '';
                let tipo = this.tiposList.find(t => t.id == id);
                return tipo ? tipo.nombre : '';
            },

            addDevice() {
                const newIndex = this.devices.length;
                this.devices.push({
                    tipo_dispositivo_id: '',
                    marca_id: '',
                    modelo_id: '',
                    serie_imei: '',
                    tipo_pass: 'ninguno',
                    pass_code: '',
                    patron_data: '',
                    problemas: [],
                    problemas_data: [],
                    observaciones: '',
                    tecnico_id: this.globalTechnician ? this.globalTechnician : '',
                    accesorios: [],
                    detalles: []
                });
                this.expandLastAccordion();
                this.$nextTick(() => {
                    this.initProblemaSelect(newIndex);
                });
            },

            cloneLastDevice() {
                const last = this.devices[this.devices.length - 1];
                const clone = JSON.parse(JSON.stringify(last));
                clone.serie_imei = '';
                clone.marca_id = '';
                clone.modelo_id = '';

                const problemasClonados = [...(clone.problemas || [])];
                const accesoriosClonados = [...(clone.accesorios || [])];
                const detallesClonados = [...(clone.detalles || [])];

                this.devices.push(clone);
                this.expandLastAccordion();

                this.$nextTick(() => {
                    setTimeout(() => {
                        const newIndex = this.devices.length - 1;

                        this.deviceSelections[newIndex] = {
                            accesorios: accesoriosClonados,
                            detalles: detallesClonados
                        };

                        this.devices[newIndex].accesorios = accesoriosClonados;
                        this.devices[newIndex].detalles = detallesClonados;

                        if (problemasClonados.length > 0) {
                            this.initProblemaSelectWithValues(newIndex, problemasClonados);
                        } else {
                            this.initProblemaSelect(newIndex);
                        }

                        if (clone.tipo_dispositivo_id) {
                            setTimeout(() => {
                                this.initMarcaSelect(newIndex);
                            }, 150);
                        }
                    }, 150);
                });
            },

            removeDevice(index) {
                if (this.devices.length > 1) {
                    this.destroyMarcaSelect(index);
                    this.destroyModeloSelect(index);
                    this.destroyProblemaSelect(index);

                    this.devices.splice(index, 1);

                    for (let i = index; i < this.devices.length + 1; i++) {
                        this.destroyMarcaSelect(i);
                        this.destroyModeloSelect(i);
                        this.destroyProblemaSelect(i);
                    }

                    this.$nextTick(() => {
                        setTimeout(() => {
                            for (let i = index; i < this.devices.length; i++) {
                                const dev = this.devices[i];
                                this.initProblemaSelect(i);
                                if (dev.tipo_dispositivo_id) {
                                    this.initMarcaSelect(i);
                                }
                            }
                        }, 150);
                    });
                }
            },

            reindexTomSelects() {
                Object.keys(this.tomSelectInstances.marcas).forEach(key => {
                    if (parseInt(key) >= this.devices.length) this.destroyMarcaSelect(key);
                });
                Object.keys(this.tomSelectInstances.modelos).forEach(key => {
                    if (parseInt(key) >= this.devices.length) this.destroyModeloSelect(key);
                });
                Object.keys(this.tomSelectInstances.problemas).forEach(key => {
                    if (parseInt(key) >= this.devices.length) this.destroyProblemaSelect(key);
                });
            },

            expandLastAccordion() {
                setTimeout(() => {
                    const last = this.devices.length - 1;
                    const el = document.getElementById('collapse' + last);
                    if (el) new bootstrap.Collapse(el, { toggle: true });
                }, 100);
            },

            // ------------------------------------------------------------------
            // MÉTODOS PARA EL RESUMEN
            // ------------------------------------------------------------------
            getDeviceTotal(index) {
                const dev = this.devices[index];
                if (!dev) return 0;
                if (!dev.problemas_data || dev.problemas_data.length === 0) return 0;
                return dev.problemas_data.reduce((sum, p) => {
                    return sum + parseFloat(p.precio_mano_obra || 0) + parseFloat(p.precio_repuesto || 0);
                }, 0);
            },

            getTotalOrden() {
                return this.devices.reduce((sum, _, i) => sum + this.getDeviceTotal(i), 0);
            },

            // ✅ FIX 1: getProblemasCount lee correctamente dev.problemas (array reactivo)
            getProblemasCount(index) {
                const dev = this.devices[index];
                if (!dev) return 0;
                return Array.isArray(dev.problemas) ? dev.problemas.length : 0;
            },

            // ------------------------------------------------------------------
            // CLIENTE, PATRÓN, ETC.
            // ------------------------------------------------------------------
            initPatternLock() {
                if (!this.patternLockInstance) {
                    this.patternLockInstance = new PatternLock('lock');
                }
            },

            openModalClient(mode) {
                this.modalMode = mode;
                if (mode === 'create') {
                    this.modalForm = {
                        cedula: this.searchCedula, nombres: '', apellidos: '',
                        telefono: '', telefono_secundario: '', email: ''
                    };
                } else {
                    this.modalForm = { ...this.client };
                }
                this.bsModalClient.show();
            },

            async buscarCliente() {
                if (this.searchCedula.length < 3) return;
                this.isLoading = true;
                this.searchError = '';
                this.searchExecuted = false;
                this.client = null;

                try {
                    const response = await fetch('<?= base_url('admin/clientes/buscarCedula') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfHash
                        },
                        body: JSON.stringify({
                            cedula: this.searchCedula,
                            [this.csrfToken]: this.csrfHash
                        })
                    });

                    const data = await response.json();

                    if (data.token) this.csrfHash = data.token;
                    else if (response.headers.get('X-CSRF-TOKEN')) this.csrfHash = response.headers.get('X-CSRF-TOKEN');

                    if (data.status === 'success') {
                        this.client = data.persona;
                        if (!this.client.email || !this.client.telefono) {
                            this.openModalClient('edit');
                        }
                    } else {
                        this.searchExecuted = true;
                    }
                } catch (error) {
                    console.error(error);
                    this.searchError = 'Error de conexión con el servidor.';
                } finally {
                    this.isLoading = false;
                }
            },

            async saveClient() {
                this.isSaving = true;
                let url = this.modalMode === 'create'
                    ? '<?= base_url('admin/clientes/crear-js') ?>'
                    : '<?= base_url('admin/clientes/actualizar-js') ?>';

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfHash
                        },
                        body: JSON.stringify({
                            ...this.modalForm,
                            id: (this.modalMode === 'edit') ? this.client.id : null,
                            [this.csrfToken]: this.csrfHash
                        })
                    });

                    const data = await response.json();
                    if (data.token) this.csrfHash = data.token;

                    if (data.status === 'success') {
                        this.client = data.client_data;
                        if (this.modalMode === 'create') {
                            this.searchCedula = this.client.cedula;
                        }
                        this.bsModalClient.hide();
                    } else {
                        let errorMsg = '';
                        if (typeof data.errors === 'object') {
                            errorMsg = Object.values(data.errors).join('\n');
                        } else {
                            errorMsg = data.errors;
                        }
                        alert('Error: \n' + errorMsg);
                    }
                } catch (error) {
                    console.error(error);
                    alert('Error del sistema al guardar cliente.');
                } finally {
                    this.isSaving = false;
                }
            },

            resetClient() {
                this.client = null;
                this.searchCedula = '';
                this.searchExecuted = false;
            },

            openPatternModal(index) {
                this.activeDeviceIndex = index;
                this.bsModalPattern.show();
                setTimeout(() => {
                    this.initPatternLock();
                    const currentPattern = this.devices[index].patron_data;
                    if (currentPattern) {
                        console.log('Patrón existente:', currentPattern);
                    } else {
                        this.patternLockInstance.reset();
                    }
                }, 300);
            },

            clearPattern() {
                if (this.patternLockInstance) {
                    this.patternLockInstance.reset();
                }
            },

            savePattern() {
                if (this.patternLockInstance && this.activeDeviceIndex !== null) {
                    const result = this.patternLockInstance.getPattern();
                    if (result.success) {
                        this.devices[this.activeDeviceIndex].patron_data = result.pattern;
                        this.bsModalPattern.hide();
                    } else {
                        alert(result.message);
                    }
                }
            },

            submitOrden(e) {
                e.target.submit();
            }
        }))
    });
</script>
<?= $this->endSection() ?>