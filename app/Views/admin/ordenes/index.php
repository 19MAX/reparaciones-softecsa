<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= $titulo ?? 'Órdenes de Trabajo' ?>
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
            <a href="#">Órdenes</a>
        </li>
    </ul>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <h4 class="card-title"><i class="fas fa-toolbox me-2"></i>Listado de Órdenes</h4>

                    <a href="<?= base_url('admin/ordenes/crear') ?>" class="btn btn-success btn-round ms-auto">
                        <i class="fa fa-plus me-2"></i> Nueva Orden
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="ordenes-datatables" class="table table-bordered ">
                        <thead>
                            <tr>
                                <th># Orden</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Dispositivos</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th style="width: 10%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php if (!empty($ordenes)): ?>
                                <?php foreach ($ordenes as $orden): ?>
                                    <tr>
                                        <td class="fw-bold text-primary">
                                            <?= esc($orden['codigo_orden']) ?>
                                        </td>
                                        <td>
                                            <?= formatear_fecha($orden['created_at'], 'solo_fecha') ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('H:i', strtotime($orden['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-bold">
                                                <?= esc($orden['cliente_nombre_completo']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info btn-ver-dispositivos"
                                                data-orden-id="<?= $orden['id'] ?>" data-bs-toggle="tooltip"
                                                title="Ver detalles de dispositivos">
                                                <i class="fas fa-mobile-alt me-1"></i>
                                                <?= $orden['total_dispositivos'] ?>
                                                <?= $orden['total_dispositivos'] == 1 ? 'dispositivo' : 'dispositivos' ?>
                                            </button>
                                        </td>
                                        <td>

                                        </td>
                                        <td>

                                        </td>
                                        <td>
                                            <div class="form-button-action">
                                                <a href="<?= base_url('admin/ordenes/imprimir/' . $orden['id']) ?>"
                                                    target="_blank" class="btn btn-link btn-secondary" data-bs-toggle="tooltip"
                                                    title="Imprimir Ticket">
                                                    <i class="fas fa-print"></i>
                                                </a>

                                                <!-- Enlace para ver el seguimiento público: -->

                                                <a href="<?= base_url('consulta/orden/' . $orden['codigo_orden']) ?>"
                                                    target="_blank" class="btn btn-link btn-info" data-bs-toggle="tooltip"
                                                    title="Ver Seguimiento Público">
                                                    <i class="fas fa-truck-moving"></i>
                                                </a>


                                                <a href="<?= base_url('admin/ordenes/editar/' . $orden['id']) ?>"
                                                    class="btn btn-link btn-primary" data-bs-toggle="tooltip"
                                                    title="Gestionar Orden">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <form action="<?= base_url('admin/ordenes/eliminar') ?>" method="post"
                                                    class="d-inline delete-form">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id_orden" value="<?= $orden['id'] ?>">
                                                    <button type="button" class="btn btn-link btn-danger btn-delete-orden"
                                                        data-bs-toggle="tooltip" title="Eliminar">
                                                        <i class="fa fa-times"></i>
                                                    </button>
                                                </form>
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
<!-- Modal para ver dispositivos -->
<div class="modal fade" id="modalDispositivos" tabindex="-1" aria-labelledby="modalDispositivosLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalDispositivosLabel">
                    <i class="fas fa-mobile-alt me-2"></i>Dispositivos de la Orden
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body" id="contenidoDispositivos">
                <div class="text-center py-5">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3">Cargando dispositivos...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        // Inicializar DataTables
        $('#ordenes-datatables').DataTable({
            scrollX: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.3.6/i18n/es-ES.json',
            },
            order: [[1, 'desc']],
            layout: {
                topStart: {
                    buttons: ['pageLength', 'copy', 'excel', 'pdf', 'colvis']
                }
            }
        });

        // Inicializar tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Ver dispositivos en modal
        $('.btn-ver-dispositivos').click(function () {
            const ordenId = $(this).data('orden-id');
            const modal = new bootstrap.Modal(document.getElementById('modalDispositivos'));

            modal.show();
            $('#contenidoDispositivos').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3">Cargando dispositivos...</p>
                </div>
            `);

            fetch(`<?= base_url('global/dispositivos/') ?>${ordenId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarDispositivos(data.dispositivos);
                    } else {
                        mostrarError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarError('Error al cargar los dispositivos');
                });
        });

        // Función mejorada para mostrar dispositivos en tabla
        function mostrarDispositivos(dispositivos) {
            let html = `
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="tablaDispositivos">
                        <thead class="table-info">
                            <tr>
                                <th>Dispositivo</th>
                                <th>Serie/IMEI</th>
                                <th>Estado</th>
                                <th>Técnico</th>
                                <th>Problemas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            dispositivos.forEach(dispositivo => {
                const estadoBadge = obtenerBadgeEstado(dispositivo.estado_diagnostico);
                const tipoPass = dispositivo.tipo_pass !== 'ninguno'
                    ? `<i class="fas fa-lock text-warning ms-1" data-bs-toggle="tooltip" title="Protegido: ${dispositivo.tipo_pass}"></i>`
                    : '';

                const dispositivoNombre = `${dispositivo.tipo_dispositivo || 'N/A'} ${dispositivo.marca || ''} ${dispositivo.modelo || ''}`.trim();

                const cantidadProblemas = dispositivo.problemas ? dispositivo.problemas.length : 0;
                const badgeProblemas = cantidadProblemas > 0
                    ? `<span class="badge bg-danger">${cantidadProblemas}</span>`
                    : `<span class="badge bg-success">0</span>`;

                html += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-mobile-alt text-primary me-2"></i>
                                <div>
                                    <strong>${dispositivoNombre}</strong>
                                    ${tipoPass}
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-secondary">${dispositivo.serie_imei || 'Sin registrar'}</span>
                        </td>
                        <td>${estadoBadge}</td>
                        <td>
                            ${dispositivo.tecnico_asignado
                        ? `<i class="fas fa-user-cog me-1 text-success"></i>${dispositivo.tecnico_nombre}`
                        : `<span class="text-muted"><i class="fas fa-user-slash me-1"></i>Sin asignar</span>`
                    }
                        </td>
                        <td class="text-center">${badgeProblemas}</td>
                        <td>
                            <button class="btn btn-sm btn-primary btn-ver-detalle" 
                                    data-dispositivo='${JSON.stringify(dispositivo).replace(/'/g, "&#39;")}'>
                                <i class="fas fa-eye me-1"></i>Ver Detalle
                            </button>
                        </td>
                    </tr>
                `;
            });

            html += `
                        </tbody>
                    </table>
                </div>
            `;

            $('#contenidoDispositivos').html(html);

            // Inicializar tooltips en la tabla
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Event listener para ver detalle
            $('.btn-ver-detalle').click(function () {
                const dispositivo = JSON.parse($(this).attr('data-dispositivo'));
                mostrarDetalleCompleto(dispositivo);
            });
        }

        // Nueva función para mostrar detalle completo en un modal secundario o redirigir
        function mostrarDetalleCompleto(dispositivo) {
            // Opción 1: Redirigir a una nueva página
            window.location.href = `<?= base_url('admin/dispositivos/detalle/') ?>${dispositivo.id}`;

            // Opción 2: Mostrar en un segundo modal (comentado)
            /*
            let detalleHtml = generarDetalleHTML(dispositivo);
            
            Swal.fire({
                title: `<i class="fas fa-mobile-alt me-2"></i>${dispositivo.tipo_dispositivo || 'Dispositivo'} ${dispositivo.marca || ''} ${dispositivo.modelo || ''}`,
                html: detalleHtml,
                width: '80%',
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    container: 'detalle-dispositivo-modal'
                }
            });
            */
        }

        // Función auxiliar para generar HTML de detalle (si usas modal en vez de redirección)
        function generarDetalleHTML(dispositivo) {
            const tipoPass = dispositivo.tipo_pass !== 'ninguno'
                ? `<div class="alert alert-warning">
                    <i class="fas fa-lock me-2"></i><strong>Dispositivo protegido:</strong> ${dispositivo.tipo_pass}
                   </div>`
                : '';

            let html = `
                <div class="text-start">
                    ${tipoPass}
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Serie/IMEI:</strong> ${dispositivo.serie_imei || 'No registrado'}</p>
                            <p><strong>Estado:</strong> ${obtenerBadgeEstado(dispositivo.estado_diagnostico)}</p>
                        </div>
                        <div class="col-md-6">
                            ${dispositivo.tecnico_asignado
                    ? `<p><strong>Técnico asignado:</strong> <i class="fas fa-user-cog me-1"></i>${dispositivo.tecnico_asignado}</p>`
                    : '<p class="text-muted"><i class="fas fa-user-slash me-1"></i>Sin técnico asignado</p>'
                }
                        </div>
                    </div>
            `;

            // Diagnóstico para cliente
            if (dispositivo.diagnostico_cliente) {
                html += `
                    <div class="alert alert-info mb-3">
                        <strong><i class="fas fa-comment-dots me-2"></i>Diagnóstico para cliente:</strong><br>
                        ${dispositivo.diagnostico_cliente}
                    </div>
                `;
            }

            // Problemas reportados
            if (dispositivo.problemas && dispositivo.problemas.length > 0) {
                html += `
                    <h6 class="text-danger mt-3"><i class="fas fa-exclamation-triangle me-2"></i>Problemas Reportados:</h6>
                    <ul class="list-group mb-3">
                        ${dispositivo.problemas.map(p => `
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><strong>${p.problema || 'Sin especificar'}</strong></span>
                                    <span class="badge bg-${p.prioridad === 'alta' ? 'danger' : p.prioridad === 'media' ? 'warning' : 'secondary'}">
                                        ${p.prioridad}
                                    </span>
                                </div>
                                ${p.diagnostico_inicial ? `<small class="text-muted">${p.diagnostico_inicial}</small>` : ''}
                            </li>
                        `).join('')}
                    </ul>
                `;
            }

            // Accesorios
            if (dispositivo.accesorios && dispositivo.accesorios.length > 0) {
                html += `
                    <h6 class="mt-3"><i class="fas fa-plug me-2"></i>Accesorios:</h6>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        ${dispositivo.accesorios.map(a => `
                            <span class="badge bg-success">
                                ${a.accesorio} 
                                <i class="fas fa-circle text-${a.estado === 'bueno' ? 'success' : a.estado === 'regular' ? 'warning' : 'danger'} ms-1" 
                                   style="font-size: 0.6em;" title="Estado: ${a.estado}"></i>
                            </span>
                        `).join('')}
                    </div>
                `;
            }

            // Checklist
            if (dispositivo.checklist && dispositivo.checklist.length > 0) {
                html += `
                    <h6 class="mt-3"><i class="fas fa-tasks me-2"></i>Verificaciones:</h6>
                    <div class="row">
                        ${dispositivo.checklist.map(c => `
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" checked disabled>
                                    <label class="form-check-label">${c.item}</label>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            html += '</div>';
            return html;
        }

        function mostrarError(mensaje) {
            $('#contenidoDispositivos').html(`
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>${mensaje}
                </div>
            `);
        }

        function obtenerBadgeEstado(estado) {
            const estados = {
                'pendiente': { class: 'secondary', icon: 'clock', text: 'Pendiente' },
                'en_revision': { class: 'info', icon: 'search', text: 'En Revisión' },
                'diagnosticado': { class: 'primary', icon: 'check-circle', text: 'Diagnosticado' },
                'sin_diagnostico': { class: 'warning', icon: 'times-circle', text: 'Sin Diagnóstico' }
            };
            const config = estados[estado] || estados['pendiente'];
            return `<span class="badge bg-${config.class}">
                    <i class="fas fa-${config.icon} me-1"></i>${config.text}
                </span>`;
        }

        $('.btn-delete-orden').click(function (e) {
            e.preventDefault();
            let form = $(this).closest('form');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esto. Se borrarán los dispositivos asociados.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            })
        });
    });
</script>
<?= $this->endSection() ?>