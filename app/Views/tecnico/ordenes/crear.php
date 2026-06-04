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
    const tecnicosList = <?= json_encode($tecnicos ?? []) ?>;
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
    .btn-detalles-adicionales {
        color: #6c757d;
        font-size: 0.8rem;
        padding: 2px 8px;
        transition: color 0.2s;
    }
    .btn-detalles-adicionales:hover {
        color: #495057;
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
                                <span x-text="getNombreTipo(dev.tipo_dispositivo_id) + (dev.marca ? ': ' + dev.marca + ' ' + dev.modelo : ' - Nuevo Dispositivo')"></span>
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
                                        @click.prevent="removeDevice(index)">
                                        <i class="fas fa-trash-alt"></i> Eliminar este equipo
                                    </button>
                                </div>

                                <!-- Hidden: problemas -->
                                <template x-if="dev.problemas && dev.problemas.length > 0">
                                    <template x-for="(problemaId, pIndex) in dev.problemas" :key="'prob-' + pIndex">
                                        <input type="hidden"
                                            :name="'devices['+index+'][problema_reportado]['+pIndex+']'"
                                            :value="problemaId">
                                    </template>
                                </template>

                                <!-- Fila 1: Tipo / Marca / Modelo -->
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
                                        <label class="form-label small fw-bold text-muted">Marca <span class="text-danger">*</span></label>
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

                                <!-- Fila 2: Motivo / Técnico -->
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Motivo de Ingreso <span class="text-danger">*</span></label>
                                        <select :id="'problema-select-'+index" class="form-control" multiple required>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Técnico Responsable</label>
                                        <select class="form-select form-control"
                                            :name="'devices['+index+'][tecnico_id]'" x-model="dev.tecnico_id">
                                            <option value="">-- Sin asignar (Pendiente) --</option>
                                            <template x-for="tec in tecnicosList" :key="tec.id">
                                                <option :value="tec.id" x-text="tec.nombre + ' ' + tec.apellido"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>

                                <!-- Fila 3: Accesorios (visible siempre) / Observaciones -->
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">
                                            <i class="fas fa-headphones text-info me-1"></i> Accesorios Entregados
                                        </label>
                                        <select :id="'accesorio-select-inline-'+index" class="form-control" multiple></select>
                                        <!-- Hidden inputs accesorios -->
                                        <template x-if="dev.accesorios && dev.accesorios.length > 0">
                                            <template x-for="(accesorioId, aIndex) in dev.accesorios" :key="'acc-' + aIndex">
                                                <input type="hidden"
                                                    :name="'devices['+index+'][accesorios]['+aIndex+']'"
                                                    :value="accesorioId">
                                            </template>
                                        </template>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Observaciones del cliente</label>
                                        <textarea class="form-control" :name="'devices['+index+'][observaciones]'"
                                            x-model="dev.observaciones" rows="2"
                                            placeholder="Ej: Después de la caída no encendió"></textarea>
                                    </div>
                                </div>

                                <!-- Fila 4: Bloqueo de Pantalla -->
                                <div class="row mt-3">
                                    <label class="form-label small fw-bold text-muted">Bloqueo de Pantalla</label>
                                    <div class="col-md-6">
                                        <div class="selectgroup w-100 selectgroup-success">
                                            <label class="selectgroup-item">
                                                <input type="radio" :name="'devices['+index+'][tipo_pass]'"
                                                    value="ninguno" x-model="dev.tipo_pass" class="selectgroup-input">
                                                <span class="selectgroup-button"><i class="fas fa-lock-open me-1"></i> Ninguna</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" :name="'devices['+index+'][tipo_pass]'"
                                                    value="patron" x-model="dev.tipo_pass" class="selectgroup-input">
                                                <span class="selectgroup-button"><i class="fas fa-th me-1"></i> Patrón</span>
                                            </label>
                                            <label class="selectgroup-item">
                                                <input type="radio" :name="'devices['+index+'][tipo_pass]'"
                                                    value="contrasena" x-model="dev.tipo_pass" class="selectgroup-input">
                                                <span class="selectgroup-button"><i class="fas fa-key me-1"></i> Clave</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6" x-show="dev.tipo_pass === 'contrasena'" x-transition>
                                        <input type="text" class="form-control" :name="'devices['+index+'][pass_code]'"
                                            x-model="dev.pass_code" placeholder="Ingrese PIN o Contraseña numérica...">
                                    </div>
                                    <div class="col-md-6" x-show="dev.tipo_pass === 'patron'" x-transition>
                                        <button type="button" class="btn btn-outline-dark w-100 rounded-3"
                                            @click.prevent="openPatternModal(index)">
                                            <i class="fas fa-draw-polygon me-1"></i>
                                            <span x-text="dev.patron_data ? 'Patrón Guardado' : 'Dibujar Patrón'"></span>
                                        </button>
                                        <input type="hidden" :name="'devices['+index+'][patron_data]'"
                                            x-model="dev.patron_data">
                                    </div>
                                </div>

                                <!-- Hidden inputs detalles -->
                                <template x-if="dev.detalles && dev.detalles.length > 0">
                                    <template x-for="(detallesId, cIndex) in dev.detalles" :key="'chk-' + cIndex">
                                        <input type="hidden" :name="'devices['+index+'][detalles]['+cIndex+']'"
                                            :value="detallesId">
                                    </template>
                                </template>

                                <!-- Hidden inputs prioridad y serie desde modal -->
                                <input type="hidden" :name="'devices['+index+'][prioridad_dispositivo_id]'" :value="dev.prioridad_dispositivo_id">
                                <input type="hidden" :name="'devices['+index+'][serie_imei]'" :value="dev.serie_imei">

                                <!-- Botón detalles adicionales -->
                                <div class="row mt-2">
                                    <div class="col-12 text-end">
                                        <button type="button" class="btn btn-detalles-adicionales btn-link text-decoration-none"
                                            @click.prevent="openDetallesModal(index)">
                                            <i class="fas fa-ellipsis-h me-1"></i> Detalles adicionales
                                            <span class="badge bg-secondary ms-1"
                                                x-show="getDetallesCount(index) > 0 || dev.prioridad_dispositivo_id || dev.serie_imei"
                                                x-text="getDetallesCount(index) + (dev.prioridad_dispositivo_id ? 1 : 0) + (dev.serie_imei ? 1 : 0)">
                                            </span>
                                        </button>
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
                        <button type="button" class="btn btn-outline-secondary py-2 flex-grow-1" @click.prevent="addDevice">
                            <i class="fas fa-plus me-1"></i> Agregar Nuevo Dispositivo
                        </button>
                        <button type="button" class="btn btn-outline-dark py-2 flex-grow-1" @click.prevent="cloneLastDevice">
                            <i class="fas fa-copy me-1"></i> Copiar Anterior
                        </button>
                    </div>
                    <small class="text-muted text-center d-block mt-2">Use "Copiar Anterior" si ingresa varios equipos del mismo modelo.</small>
                </div>
            </div>
        </div>

        <!-- SIDEBAR -->
        <div class="col-lg-4">
            <div class="sticky-sidebar">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body position-relative">
                        <button type="button" class="btn btn-sm btn-light text-primary position-absolute top-0 end-0 m-2"
                            @click.prevent="openModalClient('create')">
                            <i class="fas fa-user-plus"></i>
                        </button>
                        <label class="form-label small fw-bold">Cliente</label>
                        <div class="input-group">
                            <input type="text" class="form-control" x-model="searchCedula"
                                @keydown.enter.prevent="buscarCliente" placeholder="Cédula o RUC" :disabled="isLoading">
                            <button class="btn btn-primary" type="button" @click.prevent="buscarCliente" :disabled="isLoading">
                                <i class="fas" :class="isLoading ? 'fa-spinner fa-spin' : 'fa-search'"></i>
                            </button>
                        </div>
                        <div x-show="searchError" x-text="searchError" class="text-danger small mt-2"></div>
                    </div>
                </div>

                <div x-show="client" class="card shadow-sm border-0 border-start border-5 border-success mb-3" x-transition>
                    <div class="card-body position-relative">
                        <button type="button" class="btn btn-sm btn-light text-primary position-absolute top-0 end-0 m-2"
                            @click.prevent="openModalClient('edit')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <h6 class="fw-bold text-dark" x-text="client?.nombres + ' ' + client?.apellidos"></h6>
                        <div class="small text-muted mt-2">
                            <div><i class="fas fa-id-card me-2 width-20"></i> <span x-text="client?.cedula"></span></div>
                            <div><i class="fas fa-phone me-2 width-20"></i> <span x-text="client?.telefono"></span></div>
                            <div x-show="client?.email"><i class="fas fa-envelope me-2 width-20"></i> <span x-text="client?.email"></span></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger w-100 mt-3" @click.prevent="resetClient">Cambiar Cliente</button>
                    </div>
                </div>

                <div x-show="!client && searchExecuted && !isLoading" class="alert alert-warning text-center" x-transition>
                    <i class="fas fa-user-slash fa-lg mb-2 text-warning"></i>
                    <p class="small mb-2">No encontrado. ¿Desea registrarlo?</p>
                    <button type="button" class="btn btn-dark w-100 btn-sm" @click.prevent="openModalClient('create')">Crear Nuevo Cliente</button>
                </div>
            </div>

            <!-- Resumen -->
            <div class="card border-0 shadow-sm rounded-3 mt-3">
                <div class="card-body p-3 d-flex flex-column">
                    <div class="fw-semibold text-muted mb-2 small">Resumen</div>
                    <div class="flex-grow-1 overflow-auto small" style="max-height: 200px;">
                        <template x-for="(dev, index) in devices" :key="'res-'+index">
                            <div x-show="dev" class="d-flex justify-content-between py-2 border-bottom">
                                <div style="max-width:70%">
                                    <div class="fw-medium text-truncate" x-text="getNombreTipo(dev.tipo_dispositivo_id) || 'Nuevo equipo'"></div>
                                    <div class="text-muted small" x-text="getProblemasCount(index) + ' problema(s)'"></div>
                                </div>
                                <div class="fw-semibold text-end text-success" x-text="'$' + getDeviceTotal(index).toFixed(2)"></div>
                            </div>
                        </template>
                    </div>
                    <div class="d-flex justify-content-between pt-2 mt-2 border-top">
                        <span class="fw-semibold">Total</span>
                        <span class="fw-bold text-success" x-text="'$' + getTotalOrden().toFixed(2)"></span>
                    </div>
                </div>
            </div>

            <div class="card border-0 mt-3">
                <button form="main-form" type="submit" class="btn btn-success btn-lg fw-bold px-5" :disabled="!client?.id">
                    <i class="fas fa-save me-2"></i> CONFIRMAR ORDEN
                </button>
            </div>
        </div>
    </form>

    <!-- ===================== MODAL CLIENTE ===================== -->
    <div class="modal fade" id="clientModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" x-text="modalMode === 'create' ? 'Registrar Nuevo Cliente' : 'Editar Datos'"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="saveClient">
                        <div class="mb-3">
                            <label class="small fw-bold">Cédula / RUC</label>
                            <input type="text" class="form-control" x-model="modalForm.cedula" :readonly="modalMode==='edit'" required>
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

    <!-- ===================== MODAL PATRÓN ===================== -->
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger text-decoration-none" @click.prevent="clearPattern">
                        <i class="fas fa-eraser me-1"></i> Limpiar
                    </button>
                    <button type="button" class="btn btn-primary" @click.prevent="savePattern">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== MODAL DETALLES ADICIONALES ===================== -->
    <div class="modal fade" id="modalDetallesAdicionales" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-sliders-h me-2"></i> Detalles Adicionales del Dispositivo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <!-- Estado físico -->
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label fw-bold">
                                <i class="fas fa-tasks text-secondary me-1"></i> Estado Físico del Dispositivo
                            </label>
                            <select id="selectDetalles" class="form-control" multiple></select>
                            <small class="text-muted">Marque los aspectos a revisar o cree nuevos</small>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Prioridad y Serie/IMEI -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                <i class="fas fa-exclamation-circle text-warning me-1"></i> Prioridad del dispositivo
                            </label>
                            <select class="form-select" id="modalPrioridadSelect">
                                <option value="">Sin prioridad especial</option>
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
                            <label class="form-label fw-bold">
                                <i class="fas fa-barcode text-secondary me-1"></i> Serie / IMEI
                            </label>
                            <input type="text" class="form-control" id="modalSerieImei" placeholder="Número de serie o IMEI">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-dark" id="btnGuardarDetallesAdicionales">
                        <i class="fas fa-check me-1"></i> Confirmar
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
            // ESTADO
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
            // Detalles modal
            bsModalDetalles: null,
            tomSelectDetalles: null,
            activeDeviceIndexForModal: null,
            // TomSelect instances
            tomSelectInstances: {
                marcas: {},
                modelos: {},
                problemas: {},
                accesoriosInline: {}
            },
            devices: [
                {
                    tipo_dispositivo_id: '',
                    marca_id: '',
                    modelo_id: '',
                    serie_imei: '',
                    prioridad_dispositivo_id: '',
                    tipo_pass: 'ninguno',
                    pass_code: '',
                    patron_data: '',
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
                this.bsModalDetalles = new bootstrap.Modal(document.getElementById('modalDetallesAdicionales'));

                document.getElementById('btnGuardarDetallesAdicionales').addEventListener('click', () => {
                    this.guardarDetallesAdicionales();
                });

                this.$nextTick(() => {
                    this.initProblemaSelect(0);
                    this.initAccesorioSelectInline(0);
                });
            },

            // ==========================================================
            // ACCESORIOS INLINE
            // ==========================================================
            initAccesorioSelectInline(index) {
                const selectElement = document.getElementById(`accesorio-select-inline-${index}`);
                if (!selectElement) return;

                if (selectElement.tomselect) {
                    try { selectElement.tomselect.destroy(); } catch (e) {}
                }
                if (this.tomSelectInstances.accesoriosInline[index]) {
                    try { this.tomSelectInstances.accesoriosInline[index].destroy(); } catch (e) {}
                    delete this.tomSelectInstances.accesoriosInline[index];
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
                    placeholder: 'Busque o escriba un accesorio...',
                    loadingClass: 'loading',
                    load: function (query, callback) {
                        self.buscarAccesorios(query, dev.tipo_dispositivo_id, callback);
                    },
                    render: {
                        option: function (data, escape) {
                            return `<div style="padding:6px 8px;"><i class="fas fa-box text-info me-1"></i> ${escape(data.text)}</div>`;
                        },
                        no_results: function () {
                            return '<div style="padding:12px; text-align:center; color:#6c757d;">No encontrado. Presione Enter para crear nuevo accesorio.</div>';
                        }
                    },
                    onChange: function (values) {
                        self.devices[index].accesorios = Array.isArray(values) ? [...values] : (values ? [values] : []);
                        self.devices = [...self.devices];
                    },
                    onInitialize: function () {
                        if (dev.accesorios && dev.accesorios.length > 0) {
                            self.buscarAccesorios('', dev.tipo_dispositivo_id, (opciones) => {
                                if (!opciones) return;
                                opciones.forEach(op => ts.addOption(op));
                                dev.accesorios.forEach(v => ts.addItem(v, true));
                            });
                        }
                    }
                });

                const input = ts.control_input;
                input.addEventListener('keydown', async (e) => {
                    if (e.key === 'Enter' && ts.isOpen && ts.currentResults.total === 0) {
                        e.preventDefault();
                        e.stopPropagation();
                        const val = input.value.trim();
                        if (!val) return;
                        await self.crearAccesorio(val, dev.tipo_dispositivo_id, ts);
                    }
                });

                this.tomSelectInstances.accesoriosInline[index] = ts;
            },

            destroyAccesorioSelectInline(index) {
                if (this.tomSelectInstances.accesoriosInline[index]) {
                    try { this.tomSelectInstances.accesoriosInline[index].destroy(); } catch (e) {}
                    delete this.tomSelectInstances.accesoriosInline[index];
                }
                const el = document.getElementById(`accesorio-select-inline-${index}`);
                if (el && el.tomselect) {
                    try { el.tomselect.destroy(); } catch (e) {}
                }
            },

            // ==========================================================
            // MODAL DETALLES ADICIONALES (Estado físico + Prioridad + Serie)
            // ==========================================================
            openDetallesModal(index) {
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
                this.bsModalDetalles.show();

                setTimeout(() => {
                    this.initTomSelectDetalles(index);
                    // Cargar prioridad y serie actuales
                    const prioridadSelect = document.getElementById('modalPrioridadSelect');
                    const serieInput = document.getElementById('modalSerieImei');
                    if (prioridadSelect) prioridadSelect.value = dev.prioridad_dispositivo_id || '';
                    if (serieInput) serieInput.value = dev.serie_imei || '';
                }, 300);
            },

            initTomSelectDetalles(index) {
                const selectElement = document.getElementById('selectDetalles');
                if (this.tomSelectDetalles) {
                    try { this.tomSelectDetalles.destroy(); } catch (e) {}
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
                            const colores = { fisico: '#6c757d', funcional: '#0dcaf0', estetico: '#ffc107', accesorio: '#198754' };
                            const etiquetas = { fisico: 'Físico', funcional: 'Funcional', estetico: 'Estético', accesorio: 'Accesorio' };
                            const color = colores[data.categoria] || '#6c757d';
                            const etiqueta = etiquetas[data.categoria] || 'General';
                            const textColor = data.categoria === 'estetico' ? '#333' : '#fff';
                            const critico = data.es_critico ? '<span class="badge bg-danger ms-2" style="font-size:0.65rem;">Crítico</span>' : '';
                            const foto = data.requiere_foto ? '<i class="fas fa-camera text-warning ms-1"></i>' : '';
                            return `<div style="display:flex; align-items:center; gap:8px; padding:6px 8px;">
                                <span style="background:${color}; color:${textColor}; font-size:0.68rem; padding:2px 7px; border-radius:10px; white-space:nowrap; flex-shrink:0;">${etiqueta}</span>
                                <span style="flex-grow:1;">${escape(data.text)}</span>
                                ${critico}${foto}
                            </div>`;
                        },
                        no_results: function () {
                            return '<div style="padding:12px; text-align:center; color:#6c757d;">No encontrado. Presione Enter para crear nuevo item.</div>';
                        }
                    }
                });

                const input = this.tomSelectDetalles.control_input;
                input.addEventListener('keydown', async (e) => {
                    if (e.key === 'Enter' && self.tomSelectDetalles.isOpen && self.tomSelectDetalles.currentResults.total === 0) {
                        e.preventDefault();
                        e.stopPropagation();
                        const val = input.value.trim();
                        if (!val) return;
                        await self.crearDetalles(val, dev.tipo_dispositivo_id, self.tomSelectDetalles);
                    }
                });

                this.$nextTick(() => {
                    if (this.devices[index].detalles && this.devices[index].detalles.length > 0) {
                        this.tomSelectDetalles.setValue(this.devices[index].detalles, true);
                    }
                });
            },

            guardarDetallesAdicionales() {
                if (this.activeDeviceIndexForModal === null) return;
                const index = this.activeDeviceIndexForModal;

                const detallesSeleccionados = this.tomSelectDetalles ? this.tomSelectDetalles.getValue() : [];
                this.devices[index].detalles = Array.isArray(detallesSeleccionados)
                    ? detallesSeleccionados
                    : (detallesSeleccionados ? [detallesSeleccionados] : []);

                const prioridadSelect = document.getElementById('modalPrioridadSelect');
                const serieInput = document.getElementById('modalSerieImei');
                if (prioridadSelect) this.devices[index].prioridad_dispositivo_id = prioridadSelect.value;
                if (serieInput) this.devices[index].serie_imei = serieInput.value;

                this.devices = [...this.devices];
                this.bsModalDetalles.hide();
                this.activeDeviceIndexForModal = null;

                if (typeof Swal !== 'undefined') {
                    showAlert('success', 'Detalles adicionales guardados', 'top-end');
                }
            },

            getDetallesCount(index) {
                return this.devices[index]?.detalles?.length || 0;
            },

            // ==========================================================
            // FETCH: Accesorios y Detalles
            // ==========================================================
            async buscarAccesorios(query, tipoId, callback) {
                try {
                    let url = `<?= base_url('global/buscar-accesorios') ?>?q=${encodeURIComponent(query)}`;
                    if (tipoId) url += `&tipo=${encodeURIComponent(tipoId)}`;
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfHash }
                    });
                    const data = await response.json();
                    callback(data.map(item => ({ value: String(item.id || item.value), text: item.nombre || item.text })));
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
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfHash }
                    });
                    const data = await response.json();
                    callback(data.map(item => ({
                        value: String(item.id || item.value),
                        text: item.nombre || item.text,
                        categoria: item.categoria,
                        es_critico: item.es_critico,
                        requiere_foto: item.requiere_foto
                    })));
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
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfHash },
                        body: JSON.stringify({ nombre: nombre.trim(), tipo_dispositivo_id: tipoId || null, [this.csrfToken]: this.csrfHash })
                    });
                    const data = await response.json();
                    if (data.token) this.csrfHash = data.token;
                    if (data.status === 'success') {
                        const nuevo = { value: String(data.id), text: data.nombre };
                        tsInstance.addOption(nuevo);
                        tsInstance.addItem(nuevo.value);
                        tsInstance.setTextboxValue('');
                        if (typeof Swal !== 'undefined') showAlert('success', `Accesorio "${data.nombre}" creado`, 'top-end');
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
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfHash },
                        body: JSON.stringify({ nombre: nombre.trim(), tipo_dispositivo_id: tipoId || null, [this.csrfToken]: this.csrfHash })
                    });
                    const data = await response.json();
                    if (data.token) this.csrfHash = data.token;
                    if (data.status === 'success') {
                        const nuevo = { value: String(data.id), text: data.nombre, categoria: data.categoria, es_critico: data.es_critico, requiere_foto: data.requiere_foto };
                        tsInstance.addOption(nuevo);
                        tsInstance.addItem(nuevo.value);
                        tsInstance.setTextboxValue('');
                        if (typeof Swal !== 'undefined') showAlert('success', `Detalle "${data.nombre}" creado`, 'top-end');
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

            // ------------------------------------------------------------------
            // TOMSELECT - PROBLEMAS
            // ------------------------------------------------------------------
            initProblemaSelect(index) {
                const selectElement = document.getElementById(`problema-select-${index}`);
                if (selectElement && selectElement.tomselect) {
                    this.destroyProblemaSelect(index);
                }
                this.$nextTick(() => {
                    setTimeout(() => {
                        const selectElement = document.getElementById(`problema-select-${index}`);
                        if (!selectElement || selectElement.tomselect) return;

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
                                self.buscarProblemas(query, dev.tipo_dispositivo_id || null, dev.modelo_id || null, callback);
                            },
                            render: {
                                option: function (data, escape) {
                                    const precio = (parseFloat(data.precio_mano_obra || 0) + parseFloat(data.precio_repuesto || 0));
                                    const precioStr = precio > 0
                                        ? `<span style="color:#198754; font-weight:600; margin-left:auto; white-space:nowrap;">$${precio.toFixed(2)}</span>`
                                        : `<span style="color:#6c757d; margin-left:auto; white-space:nowrap;">$0.00</span>`;
                                    const tiempoH = data.tiempo ? Math.round(data.tiempo / 60) || 1 : null;
                                    const tiempoStr = tiempoH ? `<span style="color:#6c757d; font-size:0.72rem; margin-left:8px;">${tiempoH} h</span>` : '';
                                    return `<div style="display:flex; align-items:center; gap:8px; padding:6px 8px;">
                                        <span style="flex-grow:1;">${escape(data.text)}</span>
                                        ${tiempoStr}${precioStr}
                                    </div>`;
                                },
                                no_results: function () {
                                    return '<div style="padding:12px; text-align:center; color:#6c757d;">No encontrado. Presione Enter para crear.</div>';
                                }
                            },
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
                                self.devices = [...self.devices];
                            }
                        });

                        const input = ts.control_input;
                        let ultimoTipoId = dev.tipo_dispositivo_id || null;
                        let ultimoModeloId = dev.modelo_id || null;

                        input.addEventListener('keydown', async (e) => {
                            if (e.key === 'Enter' && ts.isOpen && ts.currentResults.total === 0) {
                                e.preventDefault();
                                e.stopPropagation();
                                const val = input.value.trim();
                                if (!val) return;
                                await self.crearProblema(val, dev.tipo_dispositivo_id || null, ts);
                            }
                        });

                        ts.on('dropdown_open', function () {
                            const tipoActual = self.devices[index].tipo_dispositivo_id || null;
                            const modeloActual = self.devices[index].modelo_id || null;
                            if (tipoActual !== ultimoTipoId || modeloActual !== ultimoModeloId) {
                                ultimoTipoId = tipoActual;
                                ultimoModeloId = modeloActual;
                                ts.clear(true);
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
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfHash }
                    });
                    const data = await response.json();
                    callback(data.map(item => ({
                        value: String(item.value),
                        text: item.text,
                        nombre: item.nombre,
                        tiempo: item.tiempo,
                        precio_mano_obra: item.precio_mano_obra,
                        precio_repuesto: item.precio_repuesto,
                        precio_total: item.precio_total,
                    })));
                } catch (error) {
                    console.error('Error cargando problemas:', error);
                    callback();
                }
            },

            async crearProblema(nombre, tipoId, tsInstance) {
                if (!tipoId) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Tipo requerido', text: 'Debes seleccionar un tipo de dispositivo antes de crear el problema' });
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
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfHash },
                        body: JSON.stringify({ nombre: nombre.trim(), tipo_dispositivo_id: tipoId || null, [this.csrfToken]: this.csrfHash })
                    });
                    const data = await response.json();
                    if (data.token) this.csrfHash = data.token;
                    if (data.status === 'success') {
                        const nuevo = { value: String(data.id), text: data.nombre, nombre: data.nombre, tiempo: data.tiempo, precio_mano_obra: 0, precio_repuesto: 0 };
                        tsInstance.addOption(nuevo);
                        tsInstance.addItem(nuevo.value);
                        tsInstance.setTextboxValue('');
                        if (typeof Swal !== 'undefined') showAlert('success', `Problema "${data.nombre}" creado`, 'top-end');
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
                    try { this.tomSelectInstances.problemas[index].destroy(); } catch (e) {}
                    delete this.tomSelectInstances.problemas[index];
                }
                const el = document.getElementById(`problema-select-${index}`);
                if (el && el.tomselect) {
                    try { el.tomselect.destroy(); } catch (e) {}
                }
            },

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
                                self.buscarProblemas(query, dev.tipo_dispositivo_id || null, dev.modelo_id || null, callback);
                            },
                            render: {
                                option: function (data, escape) {
                                    const precio = (parseFloat(data.precio_mano_obra || 0) + parseFloat(data.precio_repuesto || 0));
                                    const precioStr = precio > 0
                                        ? `<span style="color:#198754; font-weight:600; margin-left:auto; white-space:nowrap;">$${precio.toFixed(2)}</span>`
                                        : `<span style="color:#6c757d; margin-left:auto; white-space:nowrap;">$0.00</span>`;
                                    const tiempoH = data.tiempo ? Math.round(data.tiempo / 60) || 1 : null;
                                    const tiempoStr = tiempoH ? `<span style="color:#6c757d; font-size:0.72rem; margin-left:8px;">${tiempoH} h</span>` : '';
                                    return `<div style="display:flex; align-items:center; gap:8px; padding:6px 8px;">
                                        <span style="flex-grow:1;">${escape(data.text)}</span>
                                        ${tiempoStr}${precioStr}
                                    </div>`;
                                },
                                no_results: function () {
                                    return '<div style="padding:12px; text-align:center; color:#6c757d;">No encontrado. Presione Enter para crear.</div>';
                                }
                            },
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
                                self.devices = [...self.devices];
                            },
                            onInitialize: function () {
                                if (valores && valores.length > 0) {
                                    self.buscarProblemas('', dev.tipo_dispositivo_id || null, dev.modelo_id || null, (opciones) => {
                                        if (!opciones) return;
                                        opciones.forEach(op => ts.addOption(op));
                                        valores.forEach(v => ts.addItem(v, true));
                                        self.devices[index].problemas = [...valores];
                                        self.devices = [...self.devices];
                                    });
                                }
                            }
                        });

                        const input = ts.control_input;
                        let ultimoTipoId = dev.tipo_dispositivo_id || null;
                        let ultimoModeloId = dev.modelo_id || null;

                        input.addEventListener('keydown', async (e) => {
                            if (e.key === 'Enter' && ts.isOpen && ts.currentResults.total === 0) {
                                e.preventDefault();
                                e.stopPropagation();
                                const val = input.value.trim();
                                if (!val) return;
                                await self.crearProblema(val, dev.tipo_dispositivo_id || null, ts);
                            }
                        });

                        ts.on('dropdown_open', function () {
                            const tipoActual = self.devices[index].tipo_dispositivo_id || null;
                            const modeloActual = self.devices[index].modelo_id || null;
                            if (tipoActual !== ultimoTipoId || modeloActual !== ultimoModeloId) {
                                ultimoTipoId = tipoActual;
                                ultimoModeloId = modeloActual;
                                ts.clear(true);
                                ts.clearOptions();
                                ts.load('');
                            }
                        });

                        this.tomSelectInstances.problemas[index] = ts;
                    }, 100);
                });
            },

            // ------------------------------------------------------------------
            // TOMSELECT - MARCAS
            // ------------------------------------------------------------------
            initMarcaSelect(index) {
                const dev = this.devices[index];
                const tipoId = dev.tipo_dispositivo_id;
                dev.marca_id = '';
                dev.modelo_id = '';

                this.destroyMarcaSelect(index);
                this.destroyModeloSelect(index);
                this.destroyProblemaSelect(index);
                this.initProblemaSelect(index);

                // Reiniciar accesorio inline cuando cambia el tipo
                this.destroyAccesorioSelectInline(index);
                this.$nextTick(() => {
                    setTimeout(() => this.initAccesorioSelectInline(index), 150);
                });

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
                                    return `<div style="padding:6px 8px;">${escape(data.text)}</div>`;
                                },
                                no_results: function () {
                                    return '<div style="padding:12px; text-align:center; color:#6c757d;">No encontrado. Presione Enter para crear nueva marca.</div>';
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
                                const val = input.value.trim();
                                if (!val) return;
                                await self.crearMarca(val, tipoId, ts);
                            }
                        });

                        this.tomSelectInstances.marcas[index] = ts;
                    }, 100);
                });
            },

            async buscarMarcas(query, tipoId, callback) {
                try {
                    const url = `<?= base_url('global/buscar-marcas') ?>?q=${encodeURIComponent(query)}&tipo=${encodeURIComponent(tipoId)}`;
                    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfHash } });
                    const data = await response.json();
                    callback(data.map(item => ({ value: String(item.id || item.value), text: item.nombre || item.text })));
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
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfHash },
                        body: JSON.stringify({ nombre, tipo_dispositivo_id: tipoId, [this.csrfToken]: this.csrfHash })
                    });
                    const data = await response.json();
                    if (data.token) this.csrfHash = data.token;
                    if (data.status === 'success') {
                        const nuevo = { value: String(data.id), text: data.nombre };
                        tsInstance.addOption(nuevo);
                        tsInstance.addItem(nuevo.value);
                        tsInstance.setTextboxValue('');
                        if (typeof Swal !== 'undefined') showAlert('success', `Marca "${data.nombre}" creada`, 'top-end');
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
                    try { this.tomSelectInstances.marcas[index].destroy(); } catch (e) {}
                    delete this.tomSelectInstances.marcas[index];
                }
                const el = document.getElementById(`marca-select-${index}`);
                if (el && el.tomselect) {
                    try { el.tomselect.destroy(); } catch (e) {}
                }
            },

            // ------------------------------------------------------------------
            // TOMSELECT - MODELOS
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
                                    return `<div style="padding:6px 8px;">${escape(data.text)}</div>`;
                                },
                                no_results: function () {
                                    return '<div style="padding:12px; text-align:center; color:#6c757d;">No encontrado. Presione Enter para crear nuevo modelo.</div>';
                                }
                            },
                            onItemAdd: function (value) {
                                const modeloAnterior = self.devices[index].modelo_id;
                                self.devices[index].modelo_id = value;
                                if (modeloAnterior && modeloAnterior !== value && self.devices[index].problemas?.length > 0) {
                                    self.devices[index].problemas = [];
                                    self.devices[index].problemas_data = [];
                                    self.devices = [...self.devices];
                                }
                                const tsProblema = self.tomSelectInstances.problemas[index];
                                if (tsProblema) {
                                    tsProblema.clear(true);
                                    tsProblema.clearOptions();
                                    tsProblema.load('');
                                }
                            }
                        });

                        const input = ts.control_input;
                        input.addEventListener('keydown', async (e) => {
                            if (e.key === 'Enter' && ts.isOpen && ts.currentResults.total === 0) {
                                e.preventDefault();
                                e.stopPropagation();
                                const val = input.value.trim();
                                if (!val) return;
                                await self.crearModelo(val, marcaId, ts);
                            }
                        });

                        this.tomSelectInstances.modelos[index] = ts;
                    }, 100);
                });
            },

            async buscarModelos(query, marcaId, callback) {
                try {
                    const url = `<?= base_url('global/buscar-modelos') ?>?q=${encodeURIComponent(query)}&marca=${encodeURIComponent(marcaId)}`;
                    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfHash } });
                    const data = await response.json();
                    callback(data.map(item => ({ value: String(item.id || item.value), text: item.nombre || item.text })));
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
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfHash },
                        body: JSON.stringify({ nombre, marca_id: marcaId, [this.csrfToken]: this.csrfHash })
                    });
                    const data = await response.json();
                    if (data.token) this.csrfHash = data.token;
                    if (data.status === 'success') {
                        const nuevo = { value: String(data.id), text: data.nombre };
                        tsInstance.addOption(nuevo);
                        tsInstance.addItem(nuevo.value);
                        tsInstance.setTextboxValue('');
                        if (typeof Swal !== 'undefined') showAlert('success', `Modelo "${data.nombre}" creado`, 'top-end');
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
                    try { this.tomSelectInstances.modelos[index].destroy(); } catch (e) {}
                    delete this.tomSelectInstances.modelos[index];
                }
                const el = document.getElementById(`modelo-select-${index}`);
                if (el && el.tomselect) {
                    try { el.tomselect.destroy(); } catch (e) {}
                }
            },

            // ------------------------------------------------------------------
            // LÓGICA DE DISPOSITIVOS
            // ------------------------------------------------------------------
            getNombreTecnico(id) {
                if (!id) return '';
                const tec = this.tecnicosList.find(t => t.id == id);
                return tec ? tec.nombre : '';
            },

            getNombreTipo(id) {
                if (!id) return '';
                const tipo = this.tiposList.find(t => t.id == id);
                return tipo ? tipo.nombre : '';
            },

            addDevice() {
                const newIndex = this.devices.length;
                this.devices.push({
                    tipo_dispositivo_id: '',
                    marca_id: '',
                    modelo_id: '',
                    serie_imei: '',
                    prioridad_dispositivo_id: '',
                    tipo_pass: 'ninguno',
                    pass_code: '',
                    patron_data: '',
                    problemas: [],
                    problemas_data: [],
                    observaciones: '',
                    tecnico_id: '',
                    accesorios: [],
                    detalles: []
                });
                this.expandLastAccordion();
                this.$nextTick(() => {
                    setTimeout(() => {
                        this.initProblemaSelect(newIndex);
                        this.initAccesorioSelectInline(newIndex);
                    }, 150);
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
                        this.devices[newIndex].accesorios = accesoriosClonados;
                        this.devices[newIndex].detalles = detallesClonados;

                        if (problemasClonados.length > 0) {
                            this.initProblemaSelectWithValues(newIndex, problemasClonados);
                        } else {
                            this.initProblemaSelect(newIndex);
                        }

                        this.initAccesorioSelectInline(newIndex);

                        if (clone.tipo_dispositivo_id) {
                            setTimeout(() => this.initMarcaSelect(newIndex), 150);
                        }
                    }, 150);
                });
            },

            removeDevice(index) {
                if (this.devices.length > 1) {
                    this.destroyMarcaSelect(index);
                    this.destroyModeloSelect(index);
                    this.destroyProblemaSelect(index);
                    this.destroyAccesorioSelectInline(index);
                    this.devices.splice(index, 1);

                    for (let i = index; i < this.devices.length + 1; i++) {
                        this.destroyMarcaSelect(i);
                        this.destroyModeloSelect(i);
                        this.destroyProblemaSelect(i);
                        this.destroyAccesorioSelectInline(i);
                    }

                    this.$nextTick(() => {
                        setTimeout(() => {
                            for (let i = index; i < this.devices.length; i++) {
                                const dev = this.devices[i];
                                this.initProblemaSelect(i);
                                this.initAccesorioSelectInline(i);
                                if (dev.tipo_dispositivo_id) this.initMarcaSelect(i);
                            }
                        }, 150);
                    });
                }
            },

            expandLastAccordion() {
                setTimeout(() => {
                    const last = this.devices.length - 1;
                    const el = document.getElementById('collapse' + last);
                    if (el) new bootstrap.Collapse(el, { toggle: true });
                }, 100);
            },

            // ------------------------------------------------------------------
            // RESUMEN
            // ------------------------------------------------------------------
            getDeviceTotal(index) {
                const dev = this.devices[index];
                if (!dev || !dev.problemas_data || dev.problemas_data.length === 0) return 0;
                return dev.problemas_data.reduce((sum, p) => sum + parseFloat(p.precio_mano_obra || 0) + parseFloat(p.precio_repuesto || 0), 0);
            },

            getTotalOrden() {
                return this.devices.reduce((sum, _, i) => sum + this.getDeviceTotal(i), 0);
            },

            getProblemasCount(index) {
                const dev = this.devices[index];
                if (!dev) return 0;
                return Array.isArray(dev.problemas) ? dev.problemas.length : 0;
            },

            // ------------------------------------------------------------------
            // CLIENTE
            // ------------------------------------------------------------------
            openModalClient(mode) {
                this.modalMode = mode;
                if (mode === 'create') {
                    this.modalForm = { cedula: this.searchCedula, nombres: '', apellidos: '', telefono: '', telefono_secundario: '', email: '' };
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
                    const response = await fetch('<?= base_url('global/buscar-cliente') ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfHash },
                        body: JSON.stringify({ cedula: this.searchCedula, [this.csrfToken]: this.csrfHash })
                    });
                    const data = await response.json();
                    if (data.token) this.csrfHash = data.token;
                    if (data.status === 'success') {
                        this.client = data.persona;
                        if (!this.client.email || !this.client.telefono) this.openModalClient('edit');
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
                const url = this.modalMode === 'create'
                    ? '<?= base_url('global/crear-cliente') ?>'
                    : '<?= base_url('global/actualizar-cliente') ?>';
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrfHash },
                        body: JSON.stringify({ ...this.modalForm, id: (this.modalMode === 'edit') ? this.client.id : null, [this.csrfToken]: this.csrfHash })
                    });
                    const data = await response.json();
                    if (data.token) this.csrfHash = data.token;
                    if (data.status === 'success') {
                        this.client = data.client_data;
                        if (this.modalMode === 'create') this.searchCedula = this.client.cedula;
                        this.bsModalClient.hide();
                    } else {
                        const errorMsg = typeof data.errors === 'object' ? Object.values(data.errors).join('\n') : data.errors;
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

            // ------------------------------------------------------------------
            // PATRÓN
            // ------------------------------------------------------------------
            initPatternLock() {
                if (!this.patternLockInstance) {
                    this.patternLockInstance = new PatternLock('lock');
                }
            },

            openPatternModal(index) {
                this.activeDeviceIndex = index;
                this.bsModalPattern.show();
                setTimeout(() => {
                    this.initPatternLock();
                    if (!this.devices[index].patron_data) this.patternLockInstance.reset();
                }, 300);
            },

            clearPattern() {
                if (this.patternLockInstance) this.patternLockInstance.reset();
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