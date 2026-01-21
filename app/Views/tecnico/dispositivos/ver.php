<?= $this->extend('layout/main') ?>

<?= $this->section('title') ?>
<?= esc($dispositivo['marca']) ?> <?= esc($dispositivo['modelo']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('tecnico/dashboard') ?>">
                <i class="icon-home"></i>
            </a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('tecnico/dispositivos') ?>">Dispositivos por Técnico</a>
        </li>
        <li class="separator">
            <i class="icon-arrow-right"></i>
        </li>
        <li class="nav-item">
            <a href="#"><?= esc($dispositivo['marca']) ?> <?= esc($dispositivo['modelo']) ?>
                <span class="badge bg-dark ms-2 text-white">#<?= esc($dispositivo['codigo_orden']) ?></span></a>
        </li>
    </ul>
</div>

<div class="" x-data="deviceManager()">


    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success p-2 mb-3 small">
            <i class="fas fa-check me-1"></i> <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <div class="row gx-3">
        <div class="col-md-3">
            <div class="card card-round">
                <div class="card-body p-3">
                    <h6 class="fw-bold text-uppercase text-muted mb-3 text-xs">Ficha Técnica</h6>

                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">Tipo</span>
                            <span class="fw-bold"><?= esc($dispositivo['nombre_tipo'] ?? 'N/A') ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2">
                            <span class="text-muted">IMEI/Serie</span>
                            <span class="fw-bold text-break" style="max-width: 150px;">
                                <?= esc($dispositivo['serie_imei'] ?? 'N/A') ?>
                            </span>
                        </li>
                    </ul>

                    <div
                        class="mt-3 p-2 rounded border <?= $dispositivo['tipo_pass'] !== 'ninguno' ? 'border-warning bg-warning-subtle' : 'border-light bg-light' ?>">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-xs fw-bold text-uppercase">Seguridad</span>
                            <i
                                class="fas fa-lock text-xs <?= $dispositivo['tipo_pass'] !== 'ninguno' ? 'text-dark' : 'text-muted' ?>"></i>
                        </div>

                        <?php if ($dispositivo['tipo_pass'] === 'patron'): ?>
                            <div class="d-grid">
                                <button type="button" class="btn btn-dark btn-sm fw-bold" data-bs-toggle="modal"
                                    data-bs-target="#modalPatron">
                                    <i class="fas fa-th me-2"></i> Ver Patrón
                                </button>
                                <small class="text-center text-xs mt-1 text-muted">Secuencia:
                                    <?= esc($dispositivo['pass_code']) ?></small>
                            </div>
                        <?php elseif (in_array($dispositivo['tipo_pass'], ['pin', 'contrasena'])): ?>
                            <div class="text-center">
                                <span class="badge bg-dark text-white w-100 py-2 fs-6 text-wrap text-break">
                                    <?= esc($dispositivo['pass_code']) ?>
                                </span>
                                <small
                                    class="text-xs text-dark d-block mt-1 text-uppercase"><?= $dispositivo['tipo_pass'] ?></small>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted text-xs">Sin Bloqueo</div>
                        <?php endif; ?>
                    </div>

                    <h6 class="fw-bold text-uppercase text-muted mt-4 mb-2 text-xs">Cliente</h6>
                    <div class="bg-light p-3 rounded border">
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar avatar-sm me-2 flex-shrink-0">
                                <span class="avatar-title rounded-circle bg-dark text-white"><i
                                        class="fas fa-user"></i></span>
                            </div>
                            <div class="fw-bold text-sm text-dark lh-sm">
                                <?= esc($dispositivo['cliente_nombres']) ?>
                                <?= esc($dispositivo['cliente_apellidos']) ?>
                            </div>
                        </div>

                        <?php if (!empty($dispositivo['cliente_email'])): ?>
                            <div class="d-flex align-items-center mb-2 text-muted small">
                                <i class="far fa-envelope me-2"></i>
                                <span class="text-break"><?= esc($dispositivo['cliente_email']) ?></span>
                            </div>
                        <?php endif; ?>

                        <div
                            class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top border-white">
                            <span class="text-muted fw-bold small"><?= esc($dispositivo['cliente_telefono']) ?></span>
                            <a href="https://wa.me/<?= esc($dispositivo['cliente_telefono']) ?>" target="_blank"
                                class="btn btn-icon btn-round btn-success btn-xs" title="Abrir WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">

            <div class="row gx-3 mb-3">
                <div class="col-md-6">
                    <div class="card card-round h-100 mb-0">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2 text-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <h6 class="fw-bold mb-0 text-dark">Problema Reportado</h6>
                            </div>
                            <div class="bg-light p-2 rounded border"
                                style="max-height: 100px; overflow-y: auto; font-size: 0.9rem;">
                                <?= nl2br(esc($dispositivo['problema_reportado'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-round h-100 mb-0">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center mb-2 text-info">
                                <i class="fas fa-clipboard-list me-2"></i>
                                <h6 class="fw-bold mb-0 text-dark">Observaciones</h6>
                            </div>
                            <div class="bg-light p-2 rounded border"
                                style="max-height: 100px; overflow-y: auto; font-size: 0.9rem;">
                                <?= !empty($dispositivo['observaciones']) ? nl2br(esc($dispositivo['observaciones'])) : '<span class="text-muted fst-italic">Sin observaciones iniciales...</span>' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-round">
                <div class="card-header p-2 border-bottom">
                    <ul class="nav nav-pills nav-secondary nav-pills-no-bd" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active py-1 px-3 cursor-pointer" @click.prevent="activeTab = 'update'"
                                :class="{ 'active': activeTab === 'update' }">
                                <i class="fas fa-edit me-1"></i> Actualizar Estado
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link py-1 px-3 cursor-pointer" @click.prevent="activeTab = 'history'"
                                :class="{ 'active': activeTab === 'history' }">
                                <i class="fas fa-history me-1"></i> Historial de Movimientos
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-3">

                    <div x-show="activeTab === 'update'" x-transition.opacity>
                        <form action="<?= base_url('tecnico/dispositivos/actualizarEstado') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="dispositivo_id" value="<?= $dispositivo['id'] ?>">

                            <div class="row">
                                <div class="col-md-5 border-end">
                                    <label class="form-label fw-bold text-sm">Nuevo Estado <span
                                            class="text-danger">*</span></label>
                                    <select name="estado" class="form-select mb-3" required>
                                        <option value="">-- Seleccionar --</option>
                                        <?= get_select_estados_dispositivo() ?>
                                    </select>

                                    <div class="form-check form-switch mb-2 bg-light p-2 rounded border">
                                        <input class="form-check-input" type="checkbox" id="toggleCostos"
                                            x-model="showCosts">
                                        <label class="form-check-label fw-bold text-xs" for="toggleCostos">
                                            ¿Agregar cobros?
                                        </label>
                                    </div>

                                    <div x-show="showCosts" x-transition class="mt-3">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label text-xs">Mano de Obra</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" step="0.01" min="0" name="mano_obra"
                                                        class="form-control" x-model="manoObra">
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label text-xs">Repuestos</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" step="0.01" min="0" name="valor_repuestos"
                                                        class="form-control" x-model="repuestos">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-7 ps-md-4">
                                    <label class="form-label fw-bold text-sm">Informe Técnico <span
                                            class="text-danger">*</span></label>
                                    <textarea name="comentario" class="form-control" rows="4" required
                                        placeholder="Describe el avance, diagnóstico o solución..."></textarea>

                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="es_visible_cliente"
                                                value="1" id="visibleCheck">
                                            <label class="form-check-label text-xs" for="visibleCheck">
                                                Visible para cliente
                                            </label>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-sm px-4">
                                            <i class="fas fa-save me-1"></i> Guardar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div x-show="activeTab === 'history'" style="display:none">
                        <div class="horizontal-scroll-wrapper d-flex flex-nowrap overflow-auto pb-3"
                            id="historyContainer">

                            <?php if (!empty($historial)): ?>
                                <?php foreach ($historial as $registro): ?>
                                    <div class="card border mb-0 me-3 shadow-sm flex-shrink-0"
                                        style="min-width: 280px; max-width: 280px;">
                                        <div class="card-header p-2 d-flex justify-content-between align-items-center bg-light">
                                            <span
                                                class="badge <?= $registro['es_visible_cliente'] ? 'bg-success' : 'bg-secondary' ?> text-xs">
                                                <?= $registro['es_visible_cliente'] ? 'Público' : 'Privado' ?>
                                            </span>
                                            <small class="text-muted fw-bold text-xs">
                                                <?= date('d/m/y H:i', strtotime($registro['created_at'])) ?>
                                            </small>
                                        </div>
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold text-uppercase text-primary mb-2 text-sm">
                                                <?= esc($registro['estado_nuevo']) ?>
                                            </h6>

                                            <?php if ($registro['estado_anterior'] != $registro['estado_nuevo']): ?>
                                                <div class="text-xs text-muted mb-2">
                                                    <i class="fas fa-history me-1"></i> De: <?= esc($registro['estado_anterior']) ?>
                                                </div>
                                            <?php endif; ?>

                                            <p class="card-text small text-muted border-top pt-2 mt-2"
                                                style="min-height: 40px;">
                                                <?= nl2br(esc($registro['comentario'])) ?>
                                            </p>
                                        </div>
                                        <div class="card-footer p-2 text-center bg-white border-top">
                                            <small class="text-xs text-muted">Por:
                                                <?= esc($registro['usuario_nombres']) ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="w-100 text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i><br>Sin historial disponible.
                                </div>
                            <?php endif; ?>

                        </div>
                        <small class="text-muted text-center d-block mt-1 fst-italic text-xs">
                            <i class="fas fa-arrows-alt-h me-1"></i> Usa la rueda del ratón para desplazarte
                        </small>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($dispositivo['tipo_pass'] === 'patron'): ?>
    <div class="modal fade" id="modalPatron" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white p-2">
                    <h6 class="modal-title fs-6 mx-auto"><i class="fas fa-th me-2"></i>Patrón de Desbloqueo</h6>
                    <button type="button" class="btn-close btn-close-white m-0" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center bg-light">
                    <p class="mb-3 text-muted small">Referencia visual de puntos:</p>

                    <div class="d-inline-block p-3 bg-white rounded shadow-sm border">
                        <div class="pattern-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                            <?php
                            // Convertimos la secuencia (ej: "1235") en un array para saber qué puntos pintar
                            $puntos_activos = str_split((string) $dispositivo['pass_code']);
                            ?>
                            <?php for ($i = 1; $i <= 9; $i++): ?>
                                <?php $is_active = in_array((string) $i, $puntos_activos); ?>
                                <div class="pattern-dot d-flex align-items-center justify-content-center rounded-circle border"
                                    style="width: 40px; height: 40px; 
                                        <?= $is_active ? 'background-color: #31ce36; border-color: #31ce36; color: white;' : 'background-color: #eee; color: #ccc;' ?>">
                                    <span class="fw-bold"><?= $i ?></span>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mt-3">
                        <span class="badge bg-dark fs-5 letter-spacing-2"><?= esc($dispositivo['pass_code']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script src="//unpkg.com/alpinejs" defer></script>
<script>
    function deviceManager() {
        return {
            activeTab: 'update',
            showCosts: false,
            manoObra: '<?= $dispositivo['mano_obra'] ?? 0 ?>',
            repuestos: '<?= $dispositivo['valor_repuestos'] ?? 0 ?>'
        }
    }

    // Script para hacer scroll horizontal con la rueda del mouse
    document.addEventListener("DOMContentLoaded", function () {
        const scrollContainer = document.getElementById("historyContainer");
        if (scrollContainer) {
            scrollContainer.addEventListener("wheel", (evt) => {
                evt.preventDefault();
                scrollContainer.scrollLeft += evt.deltaY;
            });
        }
    });
</script>

<style>
    /* Estilos adicionales */
    .text-xs {
        font-size: 0.75rem;
    }

    .text-sm {
        font-size: 0.875rem;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    /* Scroll horizontal ocultando barra en algunos navegadores para estética */
    .horizontal-scroll-wrapper::-webkit-scrollbar {
        height: 8px;
    }

    .horizontal-scroll-wrapper::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 4px;
    }

    .horizontal-scroll-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
</style>

<?= $this->endSection() ?>