<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Nueva Orden de Trabajo<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
    <div>
        <h3 class="fw-bold mb-3">Nueva Orden de Trabajo</h3>
        <h6 class="op-7 mb-2">Registrar dispositivo para reparación</h6>
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
            <div class="card-body">
                <form id="formNuevaOrden">
                    <?= csrf_field() ?>

                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-user"></i> Datos del Cliente</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Cédula/RUC *</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="cedula" name="cedula"
                                                placeholder="Ingrese cédula" required>
                                            <button type="button" class="btn btn-info" id="btnBuscarCliente">
                                                <i class="fas fa-search"></i> Buscar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="datosCliente" style="display: none;">
                                <hr>
                                <input type="hidden" id="cliente_id" name="cliente_id">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Nombres *</label>
                                            <input type="text" class="form-control" id="nombres" name="nombres"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Apellidos *</label>
                                            <input type="text" class="form-control" id="apellidos" name="apellidos"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Teléfono *</label>
                                            <input type="text" class="form-control" id="telefono" name="telefono"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Teléfono Secundario</label>
                                            <input type="text" class="form-control" id="telefono_secundario"
                                                name="telefono_secundario">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" class="form-control" id="email" name="email">
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <button type="button" class="btn btn-success w-100" id="btnGuardarCliente"
                                            style="display: none;">
                                            <i class="fas fa-save"></i> Guardar Cliente
                                        </button>
                                        <button type="button" class="btn btn-warning w-100" id="btnActualizarCliente"
                                            style="display: none;">
                                            <i class="fas fa-edit"></i> Actualizar Cliente
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-exclamation-circle"></i> Información General</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Nivel de Urgencia *</label>
                                        <select class="form-select" name="urgencia_id" id="urgencia_id" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($urgencias as $urgencia): ?>
                                                <option value="<?= $urgencia['id'] ?>"
                                                    data-dias="<?= $urgencia['tiempo_espera'] ?>"
                                                    data-recargo="<?= $urgencia['recargo'] ?>">
                                                    <?= $urgencia['nombre'] ?>
                                                    (<?= $urgencia['tiempo_espera'] ?> días -
                                                    $<?= number_format($urgencia['recargo'], 2) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Mano de Obra Aprox.</label>
                                        <input type="number" step="0.01" class="form-control"
                                            name="valor_mano_obra_aproximado" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Repuestos Aprox.</label>
                                        <input type="number" step="0.01" class="form-control"
                                            name="valor_repuesto_aproximado" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Observaciones Generales</label>
                                        <textarea class="form-control" name="observaciones_generales" rows="3"
                                            placeholder="Observaciones adicionales..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-mobile-alt"></i> Dispositivos</h5>
                        </div>
                        <div class="card-body">
                            <button type="button" class="btn btn-sm btn-primary mb-3" id="btnAgregarDispositivo">
                                <i class="fas fa-plus"></i> Agregar Dispositivo
                            </button>

                            <div id="listaDispositivos"></div>
                        </div>
                    </div>

                    <?php if ($politicaRevision): ?>
                        <div class="card mb-3">
                            <div class="card-header bg-warning">
                                <h6 class="mb-0"><i class="fas fa-info-circle"></i> Política de Revisión</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning mb-0">
                                    <small>
                                        <strong>Valor de revisión:</strong>
                                        $<?= number_format($politicaRevision['valor_revision_base'], 2) ?><br>
                                        <?php if ($politicaRevision['cobra_revision_si_no_hay_diagnostico']): ?>
                                            ✓ Se cobra revisión si no se encuentra diagnóstico<br>
                                        <?php endif; ?>
                                        <?php if ($politicaRevision['cobra_revision_si_rechaza_cliente']): ?>
                                            ✓ Se cobra revisión si el cliente rechaza la reparación
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-save"></i> Guardar Orden
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<template id="templateDispositivo">
    <div class="card mb-2 dispositivo-item" style="border: 1px solid #e3e3e3;">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <h6>Dispositivo #<span class="device-number"></span></h6>
                <button type="button" class="btn btn-sm btn-danger btnEliminarDispositivo">
                    <i class="fas fa-trash"></i>
                </button>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tipo de Dispositivo *</label>
                        <select class="form-select tipo-dispositivo" name="devices[INDEX][tipo_dispositivo_id]"
                            required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($tiposDispositivos as $tipo): ?>
                                <option value="<?= $tipo['id'] ?>"><?= $tipo['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Marca</label>
                        <select class="form-select marca-dispositivo" name="devices[INDEX][marca_id]">
                            <option value="">Seleccione tipo primero</option>
                        </select>
                        <input type="text" class="form-control mt-1 marca-custom" name="devices[INDEX][marca_custom]"
                            placeholder="O escriba marca" style="display: none;">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Modelo</label>
                        <select class="form-select modelo-dispositivo" name="devices[INDEX][modelo_id]">
                            <option value="">Seleccione marca primero</option>
                        </select>
                        <input type="text" class="form-control mt-1 modelo-custom" name="devices[INDEX][modelo_custom]"
                            placeholder="O escriba modelo" style="display: none;">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>IMEI/Serie</label>
                        <input type="text" class="form-control" name="devices[INDEX][serie_imei]"
                            placeholder="Opcional">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Técnico Asignado</label>
                        <select class="form-select" name="devices[INDEX][tecnico_id]">
                            <option value="">Sin asignar</option>
                            <?php foreach ($tecnicos as $tecnico): ?>
                                <option value="<?= $tecnico['id'] ?>">
                                    <?= $tecnico['nombre_completo'] ?> (<?= $tecnico['especialidad'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Tipo de Contraseña</label>
                        <select class="form-select tipo-pass" name="devices[INDEX][tipo_pass]">
                            <option value="ninguno">Ninguno</option>
                            <option value="pin">PIN</option>
                            <option value="patron">Patrón</option>
                            <option value="password">Contraseña</option>
                            <option value="huella">Huella</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Contraseña/PIN</label>
                        <input type="text" class="form-control pass-code" name="devices[INDEX][pass_code]"
                            placeholder="Ingrese código">
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label>Problema Reportado *</label>
                        <select class="form-control problema-select" name="devices[INDEX][problema_reportado]" multiple
                            required>
                        </select>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label>Observaciones</label>
                        <textarea class="form-control" name="devices[INDEX][observaciones]" rows="2"
                            placeholder="Observaciones adicionales..."></textarea>
                    </div>
                </div>
            </div>

            <div class="row mt-3 box-accesorios" style="display: none;">
                <div class="col-12">
                    <div class="card bg-light border-0">
                        <div class="card-body py-2">
                            <h6 class="fw-bold text-info border-bottom pb-2">
                                <i class="fas fa-headphones"></i> Accesorios Recibidos
                            </h6>
                            <div class="row container-checks-accesorios ps-2">
                                <span class="text-muted small">Seleccione un tipo de dispositivo para cargar
                                    accesorios...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3 box-checklist" style="display: none;">
                <div class="col-12">
                    <div class="card bg-light border-0">
                        <div class="card-body py-2">
                            <h6 class="fw-bold text-secondary border-bottom pb-2">
                                <i class="fas fa-tasks"></i> Estado Físico (Checklist)
                            </h6>
                            <div class="row container-checks-checklist ps-2">
                                <span class="text-muted small">Seleccione un tipo de dispositivo para cargar el
                                    checklist...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row align-items-center">
                <!-- Botón para Accesorios y Checklist -->
                <div class="col-12">
                    <button type="button" class="btn btn-outline-primary btnAbrirModalAccesoriosChecklist w-100">
                        <i class="fas fa-clipboard-check me-2"></i>
                        Accesorios y Estado del Dispositivo
                        <span class="badge bg-info ms-2">
                            <i class="fas fa-headphones"></i>
                            <span class="count-accesorios">0</span>
                        </span>
                        <span class="badge bg-secondary ms-1">
                            <i class="fas fa-tasks"></i>
                            <span class="count-checklist">0</span>
                        </span>
                    </button>
                    <!-- Contenedores ocultos para los IDs seleccionados -->
                    <div class="container-inputs-accesorios" style="display:none;"></div>
                    <div class="container-inputs-checklist" style="display:none;"></div>
                </div>
            </div>

            <div class="container-inputs-accesorios d-none"></div>
            <div class="container-inputs-checklist d-none"></div>
        </div>
    </div>
</template>

<div class="modal fade" id="modalAccesorios" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-headphones"></i> Seleccionar Accesorios</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="bodyModalAccesorios">
                <div class="text-center py-3">
                    <div class="spinner-border text-info"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarSeleccionAccesorios">Confirmar
                    Selección</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalChecklist" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="fas fa-tasks"></i> Checklist de Estado Físico</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="bodyModalChecklist">
                <div class="text-center py-3">
                    <div class="spinner-border text-secondary"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarSeleccionChecklist">Confirmar
                    Selección</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal Unificado para Accesorios y Checklist -->
<div class="modal fade" id="modalAccesoriosChecklist" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-clipboard-check"></i>
                    Accesorios y Estado del Dispositivo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Accesorios -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="fas fa-headphones text-info"></i> Accesorios Entregados
                    </label>
                    <select id="selectAccesorios" class="form-control" multiple></select>
                    <small class="text-muted">Seleccione o escriba para crear nuevos accesorios</small>
                </div>

                <hr>

                <!-- Checklist -->
                <div>
                    <label class="form-label fw-bold">
                        <i class="fas fa-tasks text-secondary"></i> Estado Físico del Dispositivo
                    </label>
                    <select id="selectChecklist" class="form-control" multiple></select>
                    <small class="text-muted">Marque los aspectos a revisar o cree nuevos</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btnGuardarAccesoriosChecklist">
                    <i class="fas fa-check"></i> Confirmar Selección
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {

        // ==========================================================
        // 1. CONFIGURACIÓN Y UTILIDADES (HELPERS)
        // ==========================================================
        const CONFIG = {
            baseUrl: '<?= base_url() ?>',
            csrfName: '<?= csrf_token() ?>',
            deviceIndex: 0
        };

        // Instancias de Modales Bootstrap
        const modalAccesorios = new bootstrap.Modal(document.getElementById('modalAccesorios'));
        const modalChecklist = new bootstrap.Modal(document.getElementById('modalChecklist'));

        // Cache para no pedir lo mismo al servidor repetidamente
        const CACHE_DATA = {
            accesorios: {},
            checklist: {}
        };

        // --- Helpers de HTTP y DOM ---

        const getCsrfToken = () => document.querySelector(`input[name="${CONFIG.csrfName}"]`)?.value;

        const updateCsrfToken = (newToken) => {
            if (newToken) {
                document.querySelectorAll(`input[name="${CONFIG.csrfName}"]`).forEach(el => el.value = newToken);
            }
        };

        const toggleDisplay = (selectorOrElement, show) => {
            const el = typeof selectorOrElement === 'string' ? document.querySelector(selectorOrElement) : selectorOrElement;
            if (el) el.style.display = show ? 'block' : 'none';
        };

        // Cliente HTTP unificado
        const http = {
            async request(endpoint, method, body = null, isFormData = false) {
                const headers = {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': getCsrfToken()
                };

                if (!isFormData && body) {
                    headers['Content-Type'] = 'application/json';
                }

                const config = {
                    method: method,
                    headers: headers
                };

                if (body) {
                    config.body = isFormData ? body : JSON.stringify(body);
                }

                try {
                    const response = await fetch(`${CONFIG.baseUrl}${endpoint}`, config);
                    const data = await response.json();

                    // Actualizar token automáticamente si viene en la respuesta
                    if (data.token) updateCsrfToken(data.token);

                    return data;
                } catch (error) {
                    console.error(`Error en ${endpoint}:`, error);
                    throw error;
                }
            },
            get: (url) => http.request(url, 'GET'),
            post: (url, data) => http.request(url, 'POST', data),
            postForm: (url, formData) => http.request(url, 'POST', formData, true)
        };

        // ==========================================================
        // 2. MÓDULO DE CLIENTES
        // ==========================================================
        const ClientModule = {
            init() {
                document.getElementById('btnBuscarCliente').addEventListener('click', this.buscar);
                document.getElementById('btnGuardarCliente').addEventListener('click', this.crear);
                document.getElementById('btnActualizarCliente').addEventListener('click', this.actualizar);
            },

            getFormData() {
                return {
                    cedula: document.getElementById('cedula').value,
                    nombres: document.getElementById('nombres').value,
                    apellidos: document.getElementById('apellidos').value,
                    telefono: document.getElementById('telefono').value,
                    telefono_secundario: document.getElementById('telefono_secundario').value,
                    email: document.getElementById('email').value
                };
            },

            async buscar() {
                const cedula = document.getElementById('cedula').value;
                if (!cedula) return Swal.fire('Error', 'Ingrese una cédula', 'error');

                try {
                    const res = await http.post('/recepcionista/buscar-cliente', { cedula });
                    if (res.status === 'success') {
                        ClientModule.llenarDatos(res.persona);
                        toggleDisplay('#btnGuardarCliente', false);
                        toggleDisplay('#btnActualizarCliente', true);
                    } else {
                        ClientModule.limpiarDatos();
                        toggleDisplay('#datosCliente', true);
                        toggleDisplay('#btnGuardarCliente', true);
                        toggleDisplay('#btnActualizarCliente', false);
                        Swal.fire('Info', 'Cliente no encontrado. Complete los datos.', 'info');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Error de conexión al buscar cliente', 'error');
                }
            },

            async crear() {
                const data = ClientModule.getFormData();
                try {
                    const res = await http.post('/recepcionista/crear-cliente', data);
                    if (res.status === 'success') {
                        document.getElementById('cliente_id').value = res.client_data.id;
                        toggleDisplay('#btnGuardarCliente', false);
                        toggleDisplay('#btnActualizarCliente', true);
                        Swal.fire('Éxito', res.message, 'success');
                    } else {
                        Swal.fire('Error', JSON.stringify(res.errors), 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'No se pudo crear el cliente', 'error');
                }
            },

            async actualizar() {
                const data = ClientModule.getFormData();
                data.id = document.getElementById('cliente_id').value;
                try {
                    const res = await http.post('/recepcionista/actualizar-cliente', data);
                    if (res.status === 'success') Swal.fire('Éxito', res.message, 'success');
                    else Swal.fire('Error', res.message, 'error');
                } catch (e) {
                    Swal.fire('Error', 'No se pudo actualizar', 'error');
                }
            },

            llenarDatos(c) {
                toggleDisplay('#datosCliente', true);
                document.getElementById('cliente_id').value = c.id;
                document.getElementById('nombres').value = c.nombres;
                document.getElementById('apellidos').value = c.apellidos;
                document.getElementById('telefono').value = c.telefono;
                document.getElementById('telefono_secundario').value = c.telefono_secundario;
                document.getElementById('email').value = c.email;
            },

            limpiarDatos() {
                document.getElementById('cliente_id').value = '';
                document.getElementById('nombres').value = '';
                document.getElementById('apellidos').value = '';
                document.getElementById('telefono').value = '';
                document.getElementById('telefono_secundario').value = '';
                document.getElementById('email').value = '';
            }
        };

        // ==========================================================
        // 3. MÓDULO DE MODALES (ACCESORIOS Y CHECKLIST UNIFICADO)
        // ==========================================================
        const ModalManager = {
            activeCard: null,
            activeIndex: null,
            tomSelectAccesorios: null,
            tomSelectChecklist: null,

            init() {
                this.modalInstance = new bootstrap.Modal(document.getElementById('modalAccesoriosChecklist'));
                document.getElementById('btnGuardarAccesoriosChecklist').addEventListener('click', () => this.guardarSeleccion());
            },

            async abrir(btnTrigger) {
                this.activeCard = btnTrigger.closest('.dispositivo-item');
                const nameAttr = this.activeCard.querySelector('.tipo-dispositivo').name;
                const match = nameAttr.match(/\[(\d+)\]/);
                this.activeIndex = match ? match[1] : 0;

                const tipoId = this.activeCard.querySelector('.tipo-dispositivo').value;

                if (!tipoId) {
                    Swal.fire('Atención', 'Seleccione primero el Tipo de Dispositivo', 'warning');
                    return;
                }

                // Mostrar modal
                this.modalInstance.show();

                // Inicializar TomSelect si no existen
                if (!this.tomSelectAccesorios) {
                    this.inicializarTomSelectAccesorios();
                }
                if (!this.tomSelectChecklist) {
                    this.inicializarTomSelectChecklist();
                }

                // Limpiar y recargar con el tipo actual
                this.recargarDatos(tipoId);

                // Sincronizar selecciones previas
                this.sincronizarSelecciones();
            },

            inicializarTomSelectAccesorios() {
                const select = document.getElementById('selectAccesorios');
                const self = this;

                this.tomSelectAccesorios = new TomSelect(select, {
                    plugins: ['remove_button'],
                    valueField: 'value',
                    labelField: 'text',
                    searchField: ['text'],
                    multiple: true,
                    preload: true,
                    placeholder: 'Busque o escriba un accesorio...',

                    load: function (query, callback) {
                        const tipoId = self.activeCard?.querySelector('.tipo-dispositivo').value || '';
                        const url = `${CONFIG.baseUrl}recepcionista/buscar-accesorios`
                            + `?q=${encodeURIComponent(query)}`
                            + `&tipo=${encodeURIComponent(tipoId)}`;

                        fetch(url, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(res => res.json())
                            .then(data => callback(data))
                            .catch(() => callback());
                    },

                    render: {
                        option: function (data, escape) {
                            return `<div style="padding:6px 8px;">
                        <i class="fas fa-box text-info"></i> ${escape(data.text)}
                    </div>`;
                        },
                        no_results: function () {
                            return '<div style="padding:12px; text-align:center;">No se encontraron resultados</div>';
                        }
                    }
                });

                // Evento Enter para crear
                const input = this.tomSelectAccesorios.control_input;
                input.addEventListener('keydown', async function (e) {
                    if (e.key === 'Enter' && self.tomSelectAccesorios.isOpen &&
                        self.tomSelectAccesorios.currentResults.total === 0) {
                        e.preventDefault();
                        e.stopPropagation();

                        const valorInput = input.value.trim();
                        if (valorInput === '') return;

                        const tipoId = self.activeCard?.querySelector('.tipo-dispositivo').value || '';

                        self.tomSelectAccesorios.setTextboxValue('Creando...');
                        self.tomSelectAccesorios.lock();

                        const nuevoAccesorio = await self.crearAccesorio(valorInput, tipoId);

                        if (nuevoAccesorio) {
                            self.tomSelectAccesorios.addOption(nuevoAccesorio);
                            self.tomSelectAccesorios.addItem(nuevoAccesorio.value);
                            self.tomSelectAccesorios.setTextboxValue('');
                        }

                        self.tomSelectAccesorios.unlock();
                        self.tomSelectAccesorios.close();
                    }
                });
            },

            inicializarTomSelectChecklist() {
                const select = document.getElementById('selectChecklist');
                const self = this;

                this.tomSelectChecklist = new TomSelect(select, {
                    plugins: ['remove_button'],
                    valueField: 'value',
                    labelField: 'text',
                    searchField: ['text'],
                    multiple: true,
                    preload: true,
                    placeholder: 'Busque o escriba un aspecto a revisar...',

                    load: function (query, callback) {
                        const tipoId = self.activeCard?.querySelector('.tipo-dispositivo').value || '';
                        const url = `${CONFIG.baseUrl}recepcionista/buscar-checklist`
                            + `?q=${encodeURIComponent(query)}`
                            + `&tipo=${encodeURIComponent(tipoId)}`;

                        fetch(url, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(res => res.json())
                            .then(data => callback(data))
                            .catch(() => callback());
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
                            return '<div style="padding:12px; text-align:center;">No se encontraron resultados</div>';
                        }
                    }
                });

                // Evento Enter para crear
                const input = this.tomSelectChecklist.control_input;
                input.addEventListener('keydown', async function (e) {
                    if (e.key === 'Enter' && self.tomSelectChecklist.isOpen &&
                        self.tomSelectChecklist.currentResults.total === 0) {
                        e.preventDefault();
                        e.stopPropagation();

                        const valorInput = input.value.trim();
                        if (valorInput === '') return;

                        const tipoId = self.activeCard?.querySelector('.tipo-dispositivo').value || '';

                        self.tomSelectChecklist.setTextboxValue('Creando...');
                        self.tomSelectChecklist.lock();

                        const nuevoItem = await self.crearChecklistItem(valorInput, tipoId);

                        if (nuevoItem) {
                            self.tomSelectChecklist.addOption(nuevoItem);
                            self.tomSelectChecklist.addItem(nuevoItem.value);
                            self.tomSelectChecklist.setTextboxValue('');
                        }

                        self.tomSelectChecklist.unlock();
                        self.tomSelectChecklist.close();
                    }
                });
            },

            async crearAccesorio(nombre, tipoId) {
                try {
                    const response = await http.post('/recepcionista/crear-accesorio', {
                        nombre: nombre.trim(),
                        tipo_dispositivo_id: tipoId || null
                    });

                    if (response.status === 'success') {
                        return {
                            value: String(response.id),
                            text: response.nombre
                        };
                    } else {
                        Swal.fire('Error', response.message || 'No se pudo crear el accesorio', 'error');
                        return null;
                    }
                } catch (error) {
                    console.error('Error creando accesorio:', error);
                    Swal.fire('Error', 'Error de conexión', 'error');
                    return null;
                }
            },

            async crearChecklistItem(nombre, tipoId) {
                try {
                    const response = await http.post('/recepcionista/crear-checklist-item', {
                        nombre: nombre.trim(),
                        tipo_dispositivo_id: tipoId || null
                    });

                    if (response.status === 'success') {
                        return {
                            value: String(response.id),
                            text: response.nombre,
                            categoria: response.categoria,
                            es_critico: response.es_critico,
                            requiere_foto: response.requiere_foto
                        };
                    } else {
                        Swal.fire('Error', response.message || 'No se pudo crear el item', 'error');
                        return null;
                    }
                } catch (error) {
                    console.error('Error creando item:', error);
                    Swal.fire('Error', 'Error de conexión', 'error');
                    return null;
                }
            },

            recargarDatos(tipoId) {
                // Limpiar opciones y cache
                this.tomSelectAccesorios.clearOptions();
                this.tomSelectAccesorios.load('');

                this.tomSelectChecklist.clearOptions();
                this.tomSelectChecklist.load('');
            },

            sincronizarSelecciones() {
                // Leer valores previos de accesorios
                const accesoriosContainer = this.activeCard.querySelector('.container-inputs-accesorios');
                const accesoriosPrevios = Array.from(accesoriosContainer.querySelectorAll('input')).map(i => i.value);

                // Leer valores previos de checklist
                const checklistContainer = this.activeCard.querySelector('.container-inputs-checklist');
                const checklistPrevios = Array.from(checklistContainer.querySelectorAll('input')).map(i => i.value);

                // Aplicar selecciones
                this.tomSelectAccesorios.setValue(accesoriosPrevios, true);
                this.tomSelectChecklist.setValue(checklistPrevios, true);
            },

            guardarSeleccion() {
                if (!this.activeCard) return;

                // Obtener valores seleccionados
                const accesoriosSeleccionados = this.tomSelectAccesorios.getValue();
                const checklistSeleccionados = this.tomSelectChecklist.getValue();

                // Actualizar inputs hidden de accesorios
                const accesoriosContainer = this.activeCard.querySelector('.container-inputs-accesorios');
                accesoriosContainer.innerHTML = '';
                accesoriosSeleccionados.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.className = 'hidden-value-accesorios';
                    input.value = id;
                    accesoriosContainer.appendChild(input);
                });

                // Actualizar inputs hidden de checklist
                const checklistContainer = this.activeCard.querySelector('.container-inputs-checklist');
                checklistContainer.innerHTML = '';
                checklistSeleccionados.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.className = 'hidden-value-checklist';
                    input.value = id;
                    checklistContainer.appendChild(input);
                });

                // Actualizar badges
                this.activeCard.querySelector('.count-accesorios').textContent = accesoriosSeleccionados.length;
                this.activeCard.querySelector('.count-checklist').textContent = checklistSeleccionados.length;

                // Actualizar estilos del botón
                const btn = this.activeCard.querySelector('.btnAbrirModalAccesoriosChecklist');
                const totalSeleccionados = accesoriosSeleccionados.length + checklistSeleccionados.length;

                if (totalSeleccionados > 0) {
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-success', 'text-white');
                } else {
                    btn.classList.remove('btn-success', 'text-white');
                    btn.classList.add('btn-outline-primary');
                }

                // Cerrar modal
                this.modalInstance.hide();
            }
        };

        // ==========================================================
        // 4. MÓDULO DE DISPOSITIVOS (ACTUALIZADO)
        // ==========================================================
        const DeviceModule = {
            init() {
                document.getElementById('btnAgregarDispositivo').addEventListener('click', this.agregar);
                const lista = document.getElementById('listaDispositivos');

                // Delegación de eventos (Click)
                lista.addEventListener('click', (e) => {
                    const target = e.target;

                    // Eliminar
                    if (target.closest('.btnEliminarDispositivo')) {
                        target.closest('.dispositivo-item').remove();
                        this.renumerar();
                    }

                    // Abrir Modal Unificado
                    if (target.closest('.btnAbrirModalAccesoriosChecklist')) {
                        ModalManager.abrir(target.closest('.btnAbrirModalAccesoriosChecklist'));
                    }
                });

                // Delegación de eventos (Change)
                lista.addEventListener('change', async (e) => {
                    if (e.target.classList.contains('tipo-dispositivo')) {
                        await this.cargarMarcas(e.target);
                        // Resetear selecciones al cambiar tipo
                        this.resetSelecciones(e.target.closest('.dispositivo-item'));
                        // Resetear TomSelect de problemas al cambiar tipo
                        ProblemasModule.resetEnCard(e.target.closest('.dispositivo-item'));
                    }
                    if (e.target.classList.contains('marca-dispositivo')) {
                        await this.cargarModelos(e.target);
                    }
                });
            },

            agregar() {
                if (!document.getElementById('cliente_id').value) {
                    return Swal.fire('Error', 'Primero debe seleccionar un cliente', 'error');
                }

                const template = document.getElementById('templateDispositivo').innerHTML;
                const nuevoHtml = template.replace(/INDEX/g, CONFIG.deviceIndex);
                document.getElementById('listaDispositivos').insertAdjacentHTML('beforeend', nuevoHtml);

                // Actualizar número visual
                const items = document.querySelectorAll('.dispositivo-item');
                items[items.length - 1].querySelector('.device-number').textContent = CONFIG.deviceIndex + 1;

                CONFIG.deviceIndex++;

                // Inicializar TomSelect en la nueva card
                const nuevaCard = document.querySelectorAll('.dispositivo-item');
                ProblemasModule.inicializarEnCard(nuevaCard[nuevaCard.length - 1]);
            },

            renumerar() {
                document.querySelectorAll('.dispositivo-item').forEach((item, idx) => {
                    item.querySelector('.device-number').textContent = idx + 1;
                });
            },

            resetSelecciones(card) {
                card.querySelector('.container-inputs-accesorios').innerHTML = '';
                card.querySelector('.container-inputs-checklist').innerHTML = '';
                card.querySelector('.count-accesorios').textContent = '0';
                card.querySelector('.count-checklist').textContent = '0';

                // Resetear estilos del botón
                const btn = card.querySelector('.btnAbrirModalAccesoriosChecklist');
                btn.classList.remove('btn-success', 'text-white');
                btn.classList.add('btn-outline-primary');
            },

            async cargarMarcas(selectTipo) {
                const tipoId = selectTipo.value;
                const card = selectTipo.closest('.dispositivo-item');
                const marcaSelect = card.querySelector('.marca-dispositivo');
                const marcaCustom = card.querySelector('.marca-custom');

                if (!tipoId) return;

                try {
                    const marcas = await http.get(`/global/get-marcas-por-tipo/${tipoId}`);
                    let html = '<option value="">Seleccione...</option>';
                    marcas.forEach(m => html += `<option value="${m.id}">${m.nombre}</option>`);
                    html += '<option value="custom">Otra (escribir)</option>';
                    marcaSelect.innerHTML = html;
                    toggleDisplay(marcaCustom, false);
                } catch (e) {
                    console.error(e);
                }
            },

            async cargarModelos(selectMarca) {
                const marcaId = selectMarca.value;
                const card = selectMarca.closest('.dispositivo-item');
                const modeloSelect = card.querySelector('.modelo-dispositivo');
                const modeloCustom = card.querySelector('.modelo-custom');
                const marcaCustom = card.querySelector('.marca-custom');

                if (marcaId === 'custom') {
                    toggleDisplay(marcaCustom, true);
                    modeloSelect.innerHTML = '<option value="custom">Otro (escribir)</option>';
                    toggleDisplay(modeloCustom, true);
                    return;
                }

                toggleDisplay(marcaCustom, false);

                if (marcaId) {
                    try {
                        const modelos = await http.get(`/recepcionista/get-modelos-por-marca/${marcaId}`);
                        let html = '<option value="">Seleccione...</option>';
                        modelos.forEach(m => html += `<option value="${m.id}">${m.nombre}</option>`);
                        html += '<option value="custom">Otro (escribir)</option>';
                        modeloSelect.innerHTML = html;
                        toggleDisplay(modeloCustom, false);
                    } catch (e) {
                        console.error(e);
                    }
                }
            }
        };

        // ==========================================================
        // 5. MÓDULO DE ORDEN (SUBMIT ACTUALIZADO)
        // ==========================================================
        const OrderModule = {
            init() {
                document.getElementById('formNuevaOrden').addEventListener('submit', this.enviar);
            },

            async enviar(e) {
                e.preventDefault();

                const cards = document.querySelectorAll('.dispositivo-item');
                if (cards.length === 0) {
                    return Swal.fire('Error', 'Debe agregar al menos un dispositivo', 'error');
                }

                const formData = new FormData(e.target);
                const devicesData = [];

                // Construir array de dispositivos
                cards.forEach(card => {
                    const val = (sel) => card.querySelector(sel)?.value || '';

                    // Recolectar accesorios
                    const accesorios = Array.from(card.querySelectorAll('.hidden-value-accesorios'))
                        .map(i => i.value);

                    // Recolectar checklist
                    const checklist = Array.from(card.querySelectorAll('.hidden-value-checklist'))
                        .map(i => i.value);

                    // Recolectar problemas con TomSelect
                    const tsSelect = card.querySelector('.problema-select');
                    const tsInstance = tsSelect?.tomselect;
                    let problemasSeleccionados = [];

                    if (tsInstance && tsInstance.items.length > 0) {
                        tsInstance.items.forEach(val => {
                            const opcion = tsInstance.options[val];
                            if (opcion) {
                                problemasSeleccionados.push({
                                    id: opcion.value,
                                    nombre: opcion.text
                                });
                            }
                        });
                    }

                    devicesData.push({
                        tipo_dispositivo_id: val('.tipo-dispositivo'),
                        marca_id: val('.marca-dispositivo'),
                        modelo_id: val('.modelo-dispositivo'),
                        marca_custom: val('.marca-custom'),
                        modelo_custom: val('.modelo-custom'),
                        serie_imei: card.querySelector('input[name*="serie_imei"]').value,
                        tecnico_id: card.querySelector('select[name*="tecnico_id"]').value,
                        tipo_pass: val('.tipo-pass'),
                        pass_code: val('.pass-code'),
                        problemas: problemasSeleccionados,
                        observaciones: card.querySelector('textarea[name*="observaciones"]').value,
                        accesorios: accesorios,
                        checklist: checklist
                    });
                });

                // Validar que haya al menos un problema por dispositivo
                const sinProblemas = devicesData.filter(d => d.problemas.length === 0);
                if (sinProblemas.length > 0) {
                    return Swal.fire('Error', 'Todos los dispositivos deben tener al menos un problema reportado', 'error');
                }

                // Reemplazar el campo devices en el FormData
                formData.set('devices', JSON.stringify(devicesData));

                try {
                    const res = await http.postForm('/recepcionista/guardar-orden', formData);

                    if (res.status === 'success') {
                        const result = await Swal.fire({
                            title: 'Éxito',
                            text: res.message,
                            icon: 'success',
                            confirmButtonText: 'Imprimir Orden'
                        });

                        if (result.isConfirmed) {
                            window.open(res.redirect, '_blank');
                        }

                        window.location.href = CONFIG.baseUrl + '/recepcionista';
                    } else {
                        Swal.fire('Error', res.message || 'Error desconocido', 'error');
                    }
                } catch (e) {
                    console.error('Error al guardar:', e);
                    Swal.fire('Error', 'Error crítico al guardar la orden', 'error');
                }
            }
        };

        // ==========================================================
        // 6. MÓDULO DE PROBLEMAS COMUNES (TOMSELECT) - VERSIÓN SIMPLIFICADA
        // ==========================================================
        const ProblemasModule = {
            instancias: {},

            init() { },

            /**
             * Crear problema - función separada
             */
            async crearProblema(nombre, tipoId) {
                try {
                    const response = await http.post('/recepcionista/crear-problema-comun', {
                        nombre: nombre.trim(),
                        tipo_dispositivo_id: tipoId || null
                    });

                    if (response.status === 'success') {
                        return {
                            value: String(response.id),
                            text: response.nombre,
                            categoria: response.categoria,
                            tiempo: response.tiempo
                        };
                    } else {
                        Swal.fire('Error', response.message || 'No se pudo crear el problema', 'error');
                        return null;
                    }
                } catch (error) {
                    console.error('Error creando problema:', error);
                    Swal.fire('Error', 'Error de conexión al crear el problema', 'error');
                    return null;
                }
            },

            inicializarEnCard(card) {
                const select = card.querySelector('.problema-select');
                if (!select || select.tomselect) return;

                const tipoSelect = card.querySelector('.tipo-dispositivo');
                const self = this;

                const ts = new TomSelect(select, {
                    plugins: ['remove_button'],
                    valueField: 'value',
                    labelField: 'text',
                    searchField: ['text'],
                    multiple: true,
                    preload: true, // CAMBIADO: Cambiar de 'focus' a true para cargar al inicio
                    placeholder: 'Busque o escriba un problema...',
                    loadingClass: 'loading',

                    load: function (query, callback) {
                        // IMPORTANTE: Permitir búsqueda vacía para carga inicial
                        const tipoId = tipoSelect.value || '';
                        const url = `${CONFIG.baseUrl}recepcionista/buscar-problemas-comunes`
                            + `?q=${encodeURIComponent(query)}`
                            + `&tipo=${encodeURIComponent(tipoId)}`;

                        fetch(url, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                            .then(res => res.json())
                            .then(data => {
                                const opciones = data.map(item => ({
                                    value: String(item.value),
                                    text: item.text,
                                    categoria: item.categoria,
                                    tiempo: item.tiempo
                                }));
                                callback(opciones);
                            })
                            .catch(err => {
                                console.error('Error cargando problemas:', err);
                                callback();
                            });
                    },

                    render: {
                        option: function (data, escape) {
                            const colores = {
                                hardware: '#dc3545',
                                software: '#0dcaf0',
                                liquido: '#ffc107',
                                usuario: '#6c757d'
                            };
                            const etiquetas = {
                                hardware: 'Hardware',
                                software: 'Software',
                                liquido: 'Líquido',
                                usuario: 'Usuario'
                            };
                            const color = colores[data.categoria] || '#6c757d';
                            const etiqueta = etiquetas[data.categoria] || 'Nuevo';
                            const textColor = data.categoria === 'liquido' ? '#333' : '#fff';
                            const tiempo = data.tiempo
                                ? `<span style="color:#6c757d; font-size:0.75rem; margin-left:auto;">${data.tiempo} min</span>`
                                : '';

                            return `<div style="display:flex; align-items:center; gap:8px; padding:6px 8px;">
                        <span style="background:${color}; color:${textColor}; font-size:0.68rem; padding:2px 7px; border-radius:10px; white-space:nowrap; flex-shrink:0;">${etiqueta}</span>
                        <span style="flex-grow:1;">${escape(data.text)}</span>
                        ${tiempo}
                    </div>`;
                        },

                        no_results: function () {
                            return '<div style="padding:12px; text-align:center;">No se encontraron resultados</div>';
                        }
                    }
                });

                // EVENTO PERSONALIZADO: Detectar Enter para crear
                const wrapper = ts.wrapper;
                const input = ts.control_input;

                input.addEventListener('keydown', async function (e) {
                    if (e.key === 'Enter' && ts.isOpen && ts.currentResults.total === 0) {
                        e.preventDefault();
                        e.stopPropagation();

                        const valorInput = input.value.trim();
                        if (valorInput === '') return;

                        const tipoId = tipoSelect.value || '';

                        // Mostrar loading
                        ts.setTextboxValue('Creando...');
                        ts.lock();

                        // Crear el problema
                        const nuevoProblema = await self.crearProblema(valorInput, tipoId);

                        if (nuevoProblema) {
                            // Agregar al TomSelect
                            ts.addOption(nuevoProblema);
                            ts.addItem(nuevoProblema.value);
                            ts.setTextboxValue('');
                        }

                        ts.unlock();
                        ts.close();
                    }
                });

                // NUEVO: Evento cuando se abre el dropdown - recargar si cambió el tipo
                let ultimoTipoId = tipoSelect.value || '';
                ts.on('dropdown_open', function () {
                    const tipoActual = tipoSelect.value || '';
                    // Si cambió el tipo, recargar
                    if (tipoActual !== ultimoTipoId) {
                        ultimoTipoId = tipoActual;
                        ts.clearOptions();
                        ts.load(''); // Forzar recarga con query vacía
                    }
                });

                // Guardar instancia
                const nameAttr = select.name;
                const match = nameAttr.match(/\[(\d+)\]/);
                if (match) this.instancias[match[1]] = ts;
            },

            resetEnCard(card) {
                const select = card.querySelector('.problema-select');
                if (!select || !select.tomselect) return;

                const ts = select.tomselect;
                ts.clear();
                ts.clearOptions();
                ts.load('');
            }
        };
        // ==========================================================
        // INICIALIZACIÓN
        // ==========================================================
        ClientModule.init();
        ModalManager.init();
        DeviceModule.init();
        OrderModule.init();
        ProblemasModule.init();
    });
</script>
<?= $this->endSection() ?>