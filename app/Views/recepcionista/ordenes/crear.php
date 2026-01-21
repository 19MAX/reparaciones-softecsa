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

    .selectgroup-input:checked+.selectgroup-button {
        background-color: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
        z-index: 10;
    }

    /* Borde entre botones */
    .selectgroup-item:not(:first-child) .selectgroup-button {
        border-left: 0;
    }

    /* 2. Sidebar Pegajoso */
    .sticky-sidebar {
        position: sticky;
        top: 20px;
        z-index: 90;
    }

    /* 3. Ajustes Accordion */
    .accordion-button:not(.collapsed) {
        background-color: #f0f4ff;
        color: #0d6efd;
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

    <form id="main-form" class="row" action="<?= base_url('recepcionista/ordenes/guardar') ?>" method="post"
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
                    <div class="accordion-item shadow-sm border-0 mb-3 rounded overflow-hidden">
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
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="p-2 bg-light rounded border border-light">
                                            <label class="form-label small fw-bold text-muted mb-1">
                                                <i class="fas fa-user-cog me-1"></i> Técnico Responsable
                                            </label>
                                            <select class="form-select form-select-sm"
                                                :name="'devices['+index+'][tecnico_id]'" x-model="dev.tecnico_id">
                                                <option value="">-- Sin asignar (Pendiente) --</option>
                                                <template x-for="tec in tecnicosList" :key="tec.id">
                                                    <option :value="tec.id" x-text="tec.nombres + ' ' + tec.apellidos">
                                                    </option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end mb-2" x-show="devices.length > 1">
                                    <button type="button"
                                        class="btn btn-sm text-danger link-danger text-decoration-none"
                                        @click="removeDevice(index)">
                                        <i class="fas fa-trash-alt"></i> Eliminar este equipo
                                    </button>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-muted">Tipo</label>
                                        <select class="form-select form-select-sm"
                                            :name="'devices['+index+'][tipo_dispositivo_id]'"
                                            x-model="dev.tipo_dispositivo_id" required>
                                            <option value="" disabled selected>Seleccione...</option>
                                            <template x-for="tipo in tiposList" :key="tipo.id">
                                                <option :value="tipo.id" x-text="tipo.nombre"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small fw-bold text-muted">Equipo <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control" placeholder="Marca"
                                                :name="'devices['+index+'][marca]'" x-model="dev.marca" required>
                                            <input type="text" class="form-control" placeholder="Modelo"
                                                :name="'devices['+index+'][modelo]'" x-model="dev.modelo" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">Serie / IMEI</label>
                                        <input type="text" class="form-control form-control-sm"
                                            :name="'devices['+index+'][serie_imei]'" x-model="dev.serie_imei">
                                    </div>

                                    <div class="col-12 mt-4">
                                        <label class="form-label small fw-bold text-muted">Bloqueo de
                                            Pantalla</label>
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-6">
                                                <div class="selectgroup w-100">
                                                    <label class="selectgroup-item">
                                                        <input type="radio" :name="'devices['+index+'][tipo_pass]'"
                                                            value="ninguno" x-model="dev.tipo_pass"
                                                            class="selectgroup-input">
                                                        <span class="selectgroup-button"><i
                                                                class="fas fa-lock-open me-1"></i> Ninguna</span>
                                                    </label>
                                                    <label class="selectgroup-item">
                                                        <input type="radio" :name="'devices['+index+'][tipo_pass]'"
                                                            value="patron" x-model="dev.tipo_pass"
                                                            class="selectgroup-input">
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
                                                <input type="text" class="form-control"
                                                    :name="'devices['+index+'][pass_code]'" x-model="dev.pass_code"
                                                    placeholder="Ingrese PIN o Contraseña numérica...">
                                            </div>

                                            <div class="col-md-6" x-show="dev.tipo_pass === 'patron'" x-transition>
                                                <button type="button" class="btn btn-outline-dark w-100 border-dashed"
                                                    @click="openPatternModal(index)">
                                                    <i class="fas fa-draw-polygon me-1"></i>
                                                    <span
                                                        x-text="dev.patron_data ? 'Patrón Guardado (Editar)' : 'Dibujar Patrón'"></span>
                                                </button>
                                                <input type="hidden" :name="'devices['+index+'][patron_data]'"
                                                    x-model="dev.patron_data">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <label class="form-label small fw-bold text-muted">Motivo de Ingreso <span
                                                class="text-danger">*</span></label>
                                        <textarea class="form-control bg-light"
                                            :name="'devices['+index+'][problema_reportado]'" rows="2"
                                            x-model="dev.problema" placeholder="Describa el problema detalladamente..."
                                            required></textarea>
                                    </div>

                                    <div class="col-12 mt-3">
                                        <label class="form-label small fw-bold text-muted">Observaciones / Estado Físico
                                            y Accesorios</label>
                                        <textarea class="form-control" :name="'devices['+index+'][observaciones]'"
                                            x-model="dev.observaciones" rows="3"
                                            placeholder="Ej: Pantalla rayada, tapa trasera rota, se recibe sin cargador, con funda..."></textarea>
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
                        <div class="span-title fw-bold">Configuración Global</div>
                        <div class="span-mode"></div>
                    </div>
                    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                        <div class="card-body bg-white rounded-3">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Prioridad de la orden</label>
                                <select class="form-select" name="urgencia_id">
                                    <option value="" disabled selected>Seleccione la prioridad...</option>
                                    <?php if (!empty($urgencias)): ?>
                                        <?php foreach ($urgencias as $urgencia): ?>
                                            <option value="<?= $urgencia['id'] ?>">
                                                <?= esc($urgencia['nombre']) ?>
                                                <?= ($urgencia['recargo'] > 0) ? '(+$' . $urgencia['recargo'] . ')' : '' ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-primary">
                                    <i class="fas fa-users-cog"></i> Asignar todo a:
                                </label>
                                <div class="input-group">
                                    <select class="form-select" x-model="globalTechnician">
                                        <option value="">Seleccionar técnico...</option>
                                        <template x-for="tec in tecnicosList" :key="tec.id">
                                            <option :value="tec.id" x-text="tec.nombres"></option>
                                        </template>
                                    </select>
                                    <button class="btn btn-outline-primary" type="button" @click="applyTechnicianToAll"
                                        title="Aplicar este técnico a todos los dispositivos">
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                </div>
                                <div class="form-text small">
                                    Selecciona un técnico y pulsa el botón para asignarlo a todos los equipos listados.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 mb-3">
                    <div class="card-header bg-white rounded-3 collapsed" id="headingOne" data-bs-toggle="collapse"
                        data-bs-target="#headingOne" aria-expanded="false" aria-controls="headingOne">
                        <div class="span-title fw-bold">
                            Agregar precios aproximados
                        </div>
                        <div class="span-mode"></div>
                    </div>
                    <div id="headingOne" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                        <div class="card-body bg-white rounded-3">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Valor aproximado mano de obra</label>
                                <input name="valor_mano_obra_aproximado" type="number"
                                    class="form-control form-control-lg fw-bold" value="">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Valor aproximado del repuesto</label>
                                <input name="valor_repuesto_aproximado" type="number"
                                    class="form-control form-control-lg fw-bold" value="">
                            </div>
                        </div>
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

    <?= $this->endSection() ?>

    <?= $this->section('scripts') ?>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ordenManager', () => ({
                // ------------------------------------------------------------------
                // ESTADO DE LA APLICACIÓN
                // ------------------------------------------------------------------

                patternLockInstance: null,

                // CSRF Token (Vital para CodeIgniter 4)
                csrfToken: '<?= csrf_token() ?>',
                csrfHash: '<?= csrf_hash() ?>',

                // Lista de Tipos traída desde PHP
                tiposList: tiposDispositivosList,

                // Estado Cliente
                searchCedula: '',
                searchExecuted: false,
                isLoading: false,
                isSaving: false,
                searchError: '',
                client: null, // Objeto: {id, nombres, apellidos, cedula, ...}

                // Estado Dispositivos
                activeDeviceIndex: null, // Índice del dispositivo editado en modal

                // Nuevas variables
                tecnicosList: tecnicosList,
                globalTechnician: '', // Para el select del sidebar

                devices: [
                    {
                        // Se cambiaron los valores iniciales para adaptarse a la BD
                        tipo_dispositivo_id: '',
                        marca: '',
                        modelo: '',
                        serie_imei: '',
                        tipo_pass: 'ninguno',
                        pass_code: '',
                        patron_data: '',
                        problema: '',
                        observaciones: '', // Nuevo campo Textarea
                        tecnico_id: '' // <--- IMPORTANTE: Campo vacío inicial
                    }
                ],

                // Control de Modales (Se eliminó el de Checklist)
                bsModalPattern: null,
                bsModalClient: null,
                modalForm: {},
                modalMode: 'create',

                init() {
                    // Inicializar instancias de Bootstrap Modal
                    this.bsModalPattern = new bootstrap.Modal(document.getElementById('patternModal'));
                    this.bsModalClient = new bootstrap.Modal(document.getElementById('clientModal'));
                },

                initPatternLock() {
                    // Inicializar la instancia del PatternLock cuando se abre el modal
                    if (!this.patternLockInstance) {
                        this.patternLockInstance = new PatternLock('lock');
                    }
                },

                // ------------------------------------------------------------------
                // LÓGICA DE DISPOSITIVOS
                // ------------------------------------------------------------------


                // --- NUEVOS MÉTODOS ---

                // Obtener nombre técnico para el badge del acordeón
                getNombreTecnico(id) {
                    if (!id) return '';
                    let tec = this.tecnicosList.find(t => t.id == id);
                    return tec ? tec.nombres : '';
                },

                // Función mágica: Aplicar el técnico global a todos los dispositivos
                applyTechnicianToAll() {
                    if (!this.globalTechnician) return;

                    // Recorremos todos los dispositivos y les asignamos el ID seleccionado
                    this.devices.forEach(dev => {
                        dev.tecnico_id = this.globalTechnician;
                    });

                    // Opcional: Feedback visual (puedes usar SweetAlert o console)
                    // alert('Técnico asignado a todos los dispositivos');
                },


                // Helper para mostrar el nombre del tipo en el Header del acordeón
                getNombreTipo(id) {
                    if (!id) return '';
                    let tipo = this.tiposList.find(t => t.id == id);
                    return tipo ? tipo.nombre : '';
                },

                // Actualizamos addDevice para que herede el global si está seleccionado
                addDevice() {
                    this.devices.push({
                        tipo_dispositivo_id: '',
                        marca: '', modelo: '', serie_imei: '',
                        tipo_pass: 'ninguno', pass_code: '', patron_data: '', problema: '',
                        observaciones: '',
                        // Si ya seleccionó un global, lo preasignamos al nuevo dispositivo
                        tecnico_id: this.globalTechnician ? this.globalTechnician : ''
                    });
                    this.expandLastAccordion();
                },

                cloneLastDevice() {
                    // Copia profunda del último elemento
                    const last = this.devices[this.devices.length - 1];
                    const clone = JSON.parse(JSON.stringify(last));

                    // Limpiar campos únicos
                    clone.serie_imei = '';
                    // clone.problema = ''; // Opcional: limpiar problema si es diferente

                    this.devices.push(clone);
                    this.expandLastAccordion();
                },

                removeDevice(index) {
                    if (this.devices.length > 1) {
                        this.devices.splice(index, 1);
                    }
                },

                expandLastAccordion() {
                    // Esperar a que Alpine renderice el DOM y abrir el acordeón
                    setTimeout(() => {
                        const last = this.devices.length - 1;
                        const el = document.getElementById('collapse' + last);
                        if (el) new bootstrap.Collapse(el, { toggle: true });
                    }, 100);
                },

                // ------------------------------------------------------------------
                // LÓGICA DE CLIENTE (AJAX FULL) - INTACTA
                // ------------------------------------------------------------------
                openModalClient(mode) {
                    this.modalMode = mode;
                    if (mode === 'create') {
                        // Prellenar con lo buscado
                        this.modalForm = {
                            cedula: this.searchCedula, nombres: '', apellidos: '',
                            telefono: '', telefono_secundario: '', email: ''
                        };
                    } else {
                        // Copiar cliente actual
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

                        // IMPORTANTE: Actualizar Token CSRF para la siguiente petición
                        if (data.token) this.csrfHash = data.token;
                        else if (response.headers.get('X-CSRF-TOKEN')) this.csrfHash = response.headers.get('X-CSRF-TOKEN');

                        if (data.status === 'success') {
                            this.client = data.persona;

                            // Si faltan datos críticos, sugerir edición
                            if (!this.client.email || !this.client.telefono) {
                                this.openModalClient('edit');
                            }
                        } else {
                            this.searchExecuted = true; // Mostrar alerta "No encontrado"
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

                    // 1. Decidir la URL y el ID dependiendo del modo
                    let url = '';
                    if (this.modalMode === 'create') {
                        url = '<?= base_url('admin/clientes/crear-js') ?>';
                    } else {
                        // Si es edición, usamos otra ruta
                        url = '<?= base_url('admin/clientes/actualizar-js') ?>';
                    }

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
                                // Si es editar, aseguramos que vaya el ID
                                id: (this.modalMode === 'edit') ? this.client.id : null,
                                [this.csrfToken]: this.csrfHash
                            })
                        });

                        const data = await response.json();

                        // Actualizar Token CSRF
                        if (data.token) this.csrfHash = data.token;

                        if (data.status === 'success') {
                            // Actualizar cliente en vista
                            this.client = data.client_data;
                            // Si estábamos creando, ponemos la cédula en el buscador
                            if (this.modalMode === 'create') {
                                this.searchCedula = this.client.cedula;
                            }
                            this.bsModalClient.hide();
                        } else {
                            // Mostrar errores (puedes mejorar esto mostrando los errores bajo cada input)
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

                // ------------------------------------------------------------------
                // UTILIDADES (Patrón de desbloqueo)
                // ------------------------------------------------------------------
                openPatternModal(index) {
                    this.activeDeviceIndex = index;
                    this.bsModalPattern.show();

                    // Esperar a que el modal esté visible para inicializar
                    setTimeout(() => {
                        this.initPatternLock();

                        // Si ya hay un patrón guardado, mostrarlo
                        const currentPattern = this.devices[index].patron_data;
                        if (currentPattern) {
                            // Opcional: podrías pre-dibujar el patrón existente
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
                            // Guardar el patrón en el dispositivo activo
                            this.devices[this.activeDeviceIndex].patron_data = result.pattern;

                            // Cerrar el modal
                            this.bsModalPattern.hide();
                        } else {
                            // Mostrar error si no cumple con el mínimo
                            alert(result.message);
                        }
                    }
                },

                submitOrden(e) {
                    // Envio final del formulario
                    e.target.submit();
                }
            }))
        });
    </script>
    <?= $this->endSection() ?>