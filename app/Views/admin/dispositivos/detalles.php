<?= $this->extend('layout/main') ?>
<?= $this->section('title') ?>Detalle del Dispositivo<?= $this->endSection() ?>
<?= $this->section('content') ?>

<?php
$estado = $dispositivo['estado'] ?? '';
$tipoPass = $dispositivo['tipo_pass'] ?? 'sin_clave';
$tienePass = $tipoPass !== 'sin_clave'
    && $tipoPass !== 'huella'
    && !empty($dispositivo['clave_acceso']);

/* ── Descifrar clave si existe ─────────────────────────────── */
$claveVisible = null;
if ($tienePass) {
    try {
        $key = hex2bin(substr(hash('sha256', env('encryption.key')), 0, 64));
        $payload = base64_decode($dispositivo['clave_acceso']);
        [$iv, $cifrado] = explode('::', $payload, 2);
        $claveVisible = openssl_decrypt($cifrado, 'AES-256-CBC', $key, 0, $iv);
        if ($claveVisible === false) {
            $claveVisible = null; // fallo de descifrado
        }
    } catch (\Throwable $e) {
        $claveVisible = null;
    }
}
?>

<!-- Breadcrumb -->
<div class="page-header">
    <ul class="breadcrumbs ps-1 ms-0">
        <li class="nav-home">
            <a href="<?= base_url('admin/dashboard') ?>"><i class="icon-home"></i></a>
        </li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="<?= base_url('admin/ordenes') ?>">Órdenes</a></li>
        <li class="separator"><i class="icon-arrow-right"></i></li>
        <li class="nav-item"><a href="#">Detalle Dispositivo</a></li>
    </ul>
</div>

<div class="row g-3">

    <!-- ══ Columna izquierda ══ -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-1 fw-bold">
                    <?= esc($dispositivo['tipo_dispositivo']) ?>
                    <?= esc($dispositivo['marca']) ?>
                    <?= !empty($dispositivo['modelo']) ? esc($dispositivo['modelo']) : '' ?>
                </h5>
                <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                    <?= estadoPill($estado) ?>

                    <?php if ($tienePass): ?>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalClave">
                            <i class="fas fa-lock me-1" style="font-size:.7rem;"></i>
                            <?= ucfirst(esc($tipoPass)) ?>
                        </button>
                    <?php endif; ?>

                    <?php if (!empty($dispositivo['serie_imei'])): ?>
                        <span class="badge bg-secondary"><?= esc($dispositivo['serie_imei']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body">
                <!-- Info rows -->
                <ul class="list-group list-group-flush mb-3">

                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted small fw-semibold">Orden</span>
                        <a href="<?= base_url('admin/ordenes/editar/' . $dispositivo['orden_id']) ?>">
                            <?= esc($dispositivo['codigo_orden']) ?>
                        </a>
                    </li>

                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted small fw-semibold">Cliente</span>
                        <button class="btn btn-link btn-sm p-0 text-decoration-none" data-bs-toggle="modal"
                            data-bs-target="#modalCliente">
                            <?= esc(trim($dispositivo['cliente_nombre'] . ' ' . $dispositivo['cliente_apellido'])) ?>
                            <i class="fas fa-chevron-right ms-1 small"></i>
                        </button>
                    </li>

                    <!-- Técnico -->
                    <li class="list-group-item px-0" id="seccion-tecnico">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small fw-semibold">Técnico</span>
                            <div>
                                <?php if ($dispositivo['tecnico_nombre']): ?>
                                    <span><?= esc($dispositivo['tecnico_nombre']) ?></span>
                                    <?php if (session('role') === 'admin' && !in_array($estado, ['listo', 'entregado', 'cancelado'])): ?>
                                        <button class="btn btn-link btn-sm p-0 ms-2 text-warning"
                                            onclick="document.getElementById('edit-tecnico-form').classList.remove('d-none'); this.classList.add('d-none');"
                                            title="Cambiar técnico">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    <?php endif; ?>
                                <?php elseif (session('role') === 'admin' && !in_array($estado, ['listo', 'entregado', 'cancelado'])): ?>
                                    <div class="d-flex gap-2 align-items-center">
                                        <select id="select-tecnico-detalle" class="form-select form-select-sm">
                                            <option value="">Asignar técnico</option>
                                            <?php foreach ($listaTecnicos as $tec): ?>
                                                <option value="<?= $tec['id'] ?>">
                                                    <?= esc($tec['nombre'] ?? $tec['nombres'] . ' ' . ($tec['apellidos'] ?? '')) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-primary btn-sm" id="btn-asignar-tecnico-detalle"
                                            data-dispositivo="<?= $dispositivo['id'] ?>">
                                            Asignar
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">Sin asignar</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if (session('role') === 'admin' && $dispositivo['tecnico_id'] && !in_array($estado, ['listo', 'entregado', 'cancelado'])): ?>
                            <div id="edit-tecnico-form" class="d-none d-flex gap-2 align-items-center mt-2">
                                <select id="select-tecnico-detalle-edit" class="form-select form-select-sm">
                                    <option value="">Seleccionar nuevo...</option>
                                    <?php foreach ($listaTecnicos as $tec): ?>
                                        <option value="<?= $tec['id'] ?>" <?= $dispositivo['tecnico_id'] == $tec['id'] ? 'selected' : '' ?>>
                                            <?= esc($tec['nombre'] ?? $tec['nombres'] . ' ' . ($tec['apellidos'] ?? '')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-warning btn-sm" id="btn-reasignar-tecnico-detalle"
                                    data-dispositivo="<?= $dispositivo['id'] ?>">
                                    Cambiar
                                </button>
                            </div>
                        <?php endif; ?>
                    </li>

                    <?php if (!empty($dispositivo['prioridad'])): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted small fw-semibold">Prioridad</span>
                            <span><?= esc($dispositivo['prioridad']) ?></span>
                        </li>
                    <?php endif; ?>

                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span class="text-muted small fw-semibold">Entrega estimada</span>
                        <span class="<?= empty($dispositivo['fecha_estimada_entrega']) ? 'text-muted' : '' ?>">
                            <?= !empty($dispositivo['fecha_estimada_entrega'])
                                ? formatear_fecha($dispositivo['fecha_estimada_entrega'], 'solo_fecha')
                                : '—' ?>
                        </span>
                    </li>

                    <?php if (!empty($dispositivo['fecha_real_entrega'])): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted small fw-semibold">Entrega real</span>
                            <span><?= formatear_fecha($dispositivo['fecha_real_entrega'], 'solo_fecha') ?></span>
                        </li>
                    <?php endif; ?>

                </ul>

                <!-- Acción principal -->
                <div class="d-grid">
                    <?php if ($estado === 'pendiente'): ?>
                        <button class="btn btn-primary" onclick="iniciarReparacion(<?= $dispositivo['id'] ?>)">
                            <i class="fas fa-play me-1"></i> Iniciar Reparación
                        </button>
                    <?php elseif ($estado === 'en_proceso'): ?>
                        <button class="btn btn-success" onclick="abrirModalFinalizar(event)">
                            <i class="fas fa-check me-1"></i> Finalizar Reparación
                        </button>
                    <?php elseif ($estado === 'listo'): ?>
                        <button class="btn btn-info text-white" onclick="entregarDispositivo(<?= $dispositivo['id'] ?>)">
                            <i class="fas fa-box-open me-1"></i> Entregar al Cliente
                        </button>
                    <?php elseif ($estado === 'entregado'): ?>
                        <div class="alert alert-success mb-0 py-2 text-center">
                            <i class="fas fa-check-circle me-1"></i> Dispositivo entregado
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary mb-0 py-2 text-center">
                            Reparación concluida
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ Columna derecha — tabs ══ -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs nav-line nav-color-secondary" id="line-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="line-problemas-tab" data-bs-toggle="pill" href="#line-problemas"
                            role="tab" aria-selected="true">
                            Problemas
                            <?php if (!empty($dispositivo['problemas'])): ?>
                                <span class="badge bg-primary ms-1"><?= count($dispositivo['problemas']) ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="line-accesorios-tab" data-bs-toggle="pill" href="#line-accesorios"
                            role="tab" aria-selected="false">Accesorios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="line-historial-tab" data-bs-toggle="pill" href="#line-historial"
                            role="tab" aria-selected="false">Historial</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="line-cobro-tab" data-bs-toggle="pill" href="#line-cobro" role="tab"
                            aria-selected="false">Cobro</a>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="line-tabContent">

                    <!-- ── PROBLEMAS ── -->
                    <div class="tab-pane fade show active" id="line-problemas" role="tabpanel">
                        <?php if (!empty($dispositivo['problemas'])):
                            $hasRepuestos = false;
                            foreach ($dispositivo['problemas'] as $p) {
                                if ((float) $p['precio_repuesto'] > 0 || (float) $p['default_repuesto'] > 0) {
                                    $hasRepuestos = true;
                                    break;
                                }
                            }
                            $isReparado = in_array($estado, ['listo', 'entregado', 'cancelado']);
                            ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Problema</th>
                                            <th class="text-end">Mano de Obra</th>
                                            <?php if ($hasRepuestos): ?>
                                                <th class="text-end">Repuesto</th>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dispositivo['problemas'] as $p):
                                            $displayMO = $isReparado ? (float) $p['precio_mano_obra'] : (float) $p['default_mano_obra'];
                                            $displayRep = $isReparado ? (float) $p['precio_repuesto'] : (float) $p['default_repuesto'];
                                            ?>
                                            <tr>
                                                <td><?= esc($p['problema']) ?></td>
                                                <td class="text-end">$<?= number_format($displayMO, 2) ?></td>
                                                <?php if ($hasRepuestos): ?>
                                                    <td class="text-end">$<?= number_format($displayRep, 2) ?></td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if (!empty($dispositivo['relato_cliente'])): ?>
                                <div class="alert alert-light border mt-2">
                                    <small class="text-muted fw-semibold d-block mb-1">El cliente reporta</small>
                                    <?= esc($dispositivo['relato_cliente']) ?>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <p class="text-muted text-center py-3">Sin problemas registrados</p>
                        <?php endif; ?>
                    </div>

                    <!-- ── ACCESORIOS ── -->
                    <div class="tab-pane fade" id="line-accesorios" role="tabpanel">
                        <?php if (!empty($dispositivo['accesorios']) || !empty($dispositivo['detalles'])): ?>
                            <?php if (!empty($dispositivo['accesorios'])): ?>
                                <p class="text-muted small fw-semibold mb-2">Accesorios entregados</p>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <?php foreach ($dispositivo['accesorios'] as $acc): ?>
                                        <span class="badge bg-light text-dark border"><?= esc($acc['accesorio']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($dispositivo['detalles'])): ?>
                                <p class="text-muted small fw-semibold mb-2 mt-2">Estado físico</p>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($dispositivo['detalles'] as $d): ?>
                                        <span class="badge bg-light text-dark border"><?= esc($d['detalle']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">Sin accesorios registrados</p>
                        <?php endif; ?>
                    </div>

                    <!-- ── HISTORIAL ── -->
                    <div class="tab-pane fade" id="line-historial" role="tabpanel" style="max-height: 250px; overflow-y: auto;">
                        <?php if (!empty($dispositivo['historial'])):
                            $historialOrdenado = array_reverse($dispositivo['historial']);
                            $total = count($historialOrdenado);
                            ?>
                            <!-- Timeline — estilos mínimos propios -->
                            <style>
                                .tl {
                                    list-style: none;
                                    padding: 0;
                                    margin: 0;
                                }

                                .tl-item {
                                    display: flex;
                                    gap: 14px;
                                    padding-bottom: 20px;
                                    position: relative;
                                }

                                .tl-item:not(:last-child)::before {
                                    content: '';
                                    position: absolute;
                                    left: 7px;
                                    top: 22px;
                                    width: 2px;
                                    bottom: 0;
                                    background: #e9ecef;
                                }

                                .tl-dot {
                                    width: 16px;
                                    height: 16px;
                                    border-radius: 50%;
                                    flex-shrink: 0;
                                    margin-top: 3px;
                                    border: 2px solid #dee2e6;
                                    background: #fff;
                                }

                                .tl-dot.success {
                                    border-color: #198754;
                                    background: #198754;
                                }

                                .tl-dot.danger {
                                    border-color: #dc3545;
                                    background: #dc3545;
                                }

                                .tl-dot.warning {
                                    border-color: #ffc107;
                                    background: #ffc107;
                                }

                                .tl-dot.current {
                                    border-color: #0d6efd;
                                    background: #0d6efd;
                                }

                                .tl-obs {
                                    background: #f8f9fa;
                                    border-left: 3px solid #dee2e6;
                                    padding: 6px 10px;
                                    border-radius: 4px;
                                    font-size: .82rem;
                                }

                                .tl-obs.cliente {
                                    border-left-color: #198754;
                                    background: #f0fdf4;
                                }
                            </style>

                            <ul class="tl">
                                <?php foreach ($historialOrdenado as $i => $h):
                                    $hn = strtolower($h['estado_nuevo'] ?? '');
                                    $esUltimo = ($i === $total - 1);
                                    $dotClass = match (true) {
                                        $i === 0 && str_contains($hn, 'entregad') => 'success',
                                        $i === 0 && str_contains($hn, 'cancel') => 'danger',
                                        $i === 0 => 'current',
                                        str_contains($hn, 'entregad') => 'success',
                                        str_contains($hn, 'cancel') => 'danger',
                                        str_contains($hn, 'espera') => 'warning',
                                        default => '',
                                    };
                                    $estadoAnterior = $h['estado_anterior'] ?? null;
                                    $estadoNuevo = $h['estado_nuevo'] ?? 'pendiente';
                                    $hasChange = $estadoAnterior && $estadoAnterior !== $estadoNuevo;
                                    ?>
                                    <li class="tl-item">
                                        <div class="tl-dot <?= $dotClass ?>"></div>
                                        <div class="flex-grow-1">

                                            <!-- Cambio de estado -->
                                            <div class="d-flex align-items-center flex-wrap gap-1 mb-1">
                                                <?php if ($hasChange): ?>
                                                    <?= estadoPill($estadoAnterior) ?>
                                                    <i class="fas fa-arrow-right text-muted small"></i>
                                                    <?= estadoPill($estadoNuevo) ?>
                                                <?php else: ?>
                                                    <?= estadoPill($estadoNuevo) ?>
                                                <?php endif; ?>
                                                <span class="text-muted small ms-1">
                                                    <?php if ($hasChange): ?>cambio de estado
                                                    <?php elseif ($esUltimo): ?>ingreso al sistema
                                                    <?php else: ?>nota manual
                                                    <?php endif; ?>
                                                </span>
                                            </div>

                                            <!-- Meta -->
                                            <div class="text-muted" style="font-size:.75rem;">
                                                <?= esc($h['usuario']) ?>
                                                <?php if (!empty($h['fecha'])): ?>
                                                    &nbsp;·&nbsp;<i class="fas fa-clock"></i>
                                                    <?= formatear_fecha($h['fecha']) ?>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Nota técnica -->
                                            <?php if (!empty($h['observacion'])): ?>
                                                <div class="tl-obs mt-2">
                                                    <small class="text-muted fw-bold text-uppercase d-block mb-1"
                                                        style="font-size:.65rem;">Nota Técnica</small>
                                                    <?= nl2br(esc($h['observacion'])) ?>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Comentario cliente -->
                                            <?php if (!empty($h['observacion_cliente'])): ?>
                                                <div class="tl-obs cliente mt-1">
                                                    <small class="fw-bold text-uppercase d-block mb-1"
                                                        style="font-size:.65rem; color:#198754;">
                                                        <i class="fas fa-comment me-1"></i>Para el Cliente
                                                    </small>
                                                    <?= nl2br(esc($h['observacion_cliente'])) ?>
                                                </div>
                                            <?php endif; ?>

                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                        <?php else: ?>
                            <p class="text-muted text-center py-3">Sin historial de cambios</p>
                        <?php endif; ?>
                    </div>

                    <!-- ── COBRO ── -->
                    <div class="tab-pane fade" id="line-cobro" role="tabpanel">
                        <?php
                        $totalManoObra = 0;
                        $totalRepuestos = 0;
                        foreach ($dispositivo['problemas'] as $p) {
                            $totalManoObra += (float) $p['precio_mano_obra'];
                            $totalRepuestos += (float) $p['precio_repuesto'];
                        }
                        $finalizado = in_array($estado, ['listo', 'entregado']);
                        ?>

                        <?php if ($finalizado): ?>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between px-0">
                                    <span class="text-muted">Mano de Obra</span>
                                    <strong>$<?= number_format($totalManoObra, 2) ?></strong>
                                </li>

                                <?php if ($totalRepuestos > 0): ?>
                                    <li class="list-group-item px-0">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Total Repuestos</span>
                                            <strong>$<?= number_format($totalRepuestos, 2) ?></strong>
                                        </div>
                                        <div class="ps-3 border-start border-2">
                                            <small class="text-muted fw-semibold d-block mb-1">Detalle</small>
                                            <?php foreach ($dispositivo['problemas'] as $p): ?>
                                                <?php foreach ($p['repuestos'] as $rep): ?>
                                                    <div class="d-flex justify-content-between align-items-center mb-1"
                                                        style="font-size:.82rem;">
                                                        <span class="text-muted">
                                                            <?= esc($rep['nombre']) ?>
                                                            <small>(<?= $rep['cantidad'] ?> ×
                                                                $<?= number_format($rep['valor_unitario'], 2) ?>)</small>
                                                        </span>
                                                        <span class="fw-bold">$<?= number_format($rep['valor_total'], 2) ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </li>
                                <?php endif; ?>

                                <?php if ((float) $dispositivo['costo_prioridad'] > 0): ?>
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted">
                                            Cargo prioridad (<?= esc($dispositivo['prioridad']) ?>)
                                        </span>
                                        <strong
                                            class="text-warning">+$<?= number_format($dispositivo['costo_prioridad'], 2) ?></strong>
                                    </li>
                                <?php endif; ?>

                                <li class="list-group-item d-flex justify-content-between px-0 fw-bold fs-5">
                                    <span>Total cobrado</span>
                                    <span class="text-success">$<?= number_format($dispositivo['precio_total'], 2) ?></span>
                                </li>
                            </ul>

                        <?php elseif (!empty($dispositivo['precio_total'])): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom fw-semibold">
                                <span class="text-muted">Precio estimado</span>
                                <span
                                    class="text-primary fs-5">$<?= number_format($dispositivo['precio_total'], 2) ?></span>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center py-3">Sin precio definido aún</p>
                        <?php endif; ?>
                    </div>

                </div><!-- /tab-content -->
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     Modal — Info del Cliente
════════════════════════════════════════════ -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                        style="width:38px;height:38px;">
                        <i class="fas fa-user text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 fs-6"><?= esc(trim($dispositivo['cliente_nombre'] . ' ' . $dispositivo['cliente_apellido'])) ?></h5>
                        <small class="text-muted">Datos del cliente</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <hr class="mt-0">
                <ul class="list-group list-group-flush">
                    <?php if (!empty($dispositivo['cliente_cedula'])): ?>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted small">Cédula / RUC</span>
                            <span><?= esc($dispositivo['cliente_cedula']) ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($dispositivo['cliente_telefono'])): ?>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted small">Teléfono</span>
                            <a href="tel:<?= esc($dispositivo['cliente_telefono']) ?>" class="text-decoration-none">
                                <i class="fas fa-phone me-1 small"></i><?= esc($dispositivo['cliente_telefono']) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php
                    $wa = $dispositivo['cliente_whatsapp'] ?? $dispositivo['cliente_telefono'] ?? null;
                    if (!empty($wa)):
                        ?>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted small">WhatsApp</span>
                            <a href="https://wa.me/<?= preg_replace('/\D/', '', $wa) ?>" target="_blank"
                                class="text-decoration-none text-success">
                                <i class="fab fa-whatsapp me-1"></i>Abrir chat
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($dispositivo['cliente_email'])): ?>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted small">Email</span>
                            <a href="mailto:<?= esc($dispositivo['cliente_email']) ?>" class="text-decoration-none">
                                <?= esc($dispositivo['cliente_email']) ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($dispositivo['cliente_direccion'])): ?>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted small">Dirección</span>
                            <span><?= esc($dispositivo['cliente_direccion']) ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($dispositivo['cliente_referencia'])): ?>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted small">Referencia</span>
                            <span class="text-muted fst-italic"><?= esc($dispositivo['cliente_referencia']) ?></span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="modal-footer border-0 pt-0">
                <a href="<?= base_url('admin/clientes/ver/' . ($dispositivo['cliente_id'] ?? '')) ?>"
                    class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-external-link-alt me-1"></i> Ver perfil completo
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     Modal — Finalizar Reparación
════════════════════════════════════════════ -->
<div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-check-circle me-2 text-success"></i>Finalizar Reparación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formFinalizar">
                <div class="modal-body" style="max-height:65vh;overflow-y:auto;">
                    <input type="hidden" name="dispositivo_id" value="<?= $dispositivo['id'] ?>">

                    <?php foreach ($dispositivo['problemas'] as $index => $prob): ?>
                        <div class="card mb-3 border-start border-4 border-primary shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <p class="mb-0">
                                        <span class="badge bg-primary me-2">Problema <?= $index + 1 ?></span>
                                        <strong><?= esc($prob['problema']) ?></strong>
                                    </p>
                                </div>
                                <input type="hidden" name="problemas[<?= $index ?>][id]" value="<?= $prob['id'] ?>">

                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-bold small">
                                            <i class="fas fa-info-circle me-1"></i> Estado
                                        </label>
                                        <select name="problemas[<?= $index ?>][estado]" class="form-select form-select-sm">
                                            <option value="resuelto">Resuelto</option>
                                            <option value="no_reparable">No reparable</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-bold small">
                                            <i class="fas fa-hand-holding-usd me-1"></i> Mano de Obra ($)
                                        </label>
                                        <input type="number" step="0.01" name="problemas[<?= $index ?>][precio_mano_obra]"
                                            class="form-control form-control-sm" value="<?= $prob['default_mano_obra'] ?>">
                                        <div class="form-text text-primary">
                                            Sugerido: $<?= number_format($prob['default_mano_obra'], 2) ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Repuestos -->
                                <div class="bg-light p-3 rounded-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-bold mb-0 small">
                                            <i class="fas fa-box-open me-1 text-secondary"></i> REPUESTOS UTILIZADOS
                                        </label>
                                        <div class="text-end">
                                            <span class="text-muted small">Subtotal:</span>
                                            <span class="fw-bold ms-1" id="repuesto-total-display-<?= $index ?>">
                                                $<?= number_format($prob['default_repuesto'], 2) ?>
                                            </span>
                                            <input type="hidden" name="problemas[<?= $index ?>][precio_repuesto]"
                                                id="repuesto-total-<?= $index ?>" value="<?= $prob['default_repuesto'] ?>">
                                        </div>
                                    </div>

                                    <div id="lista-repuestos-<?= $index ?>"></div>

                                    <div class="input-group input-group-sm mt-2">
                                        <div class="position-relative flex-grow-1">
                                            <input type="text" class="form-control form-control-sm search-repuesto-input"
                                                placeholder="Buscar repuesto o escribir nuevo..."
                                                data-index="<?= $index ?>">
                                            <div class="dropdown-menu w-100 p-1" id="search-results-<?= $index ?>"
                                                style="display:none;position:absolute;z-index:999;max-height:180px;overflow-y:auto;">
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm"
                                            onclick="agregarRepuestoManual(<?= $index ?>)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-bold small">
                                        <i class="fas fa-comment-medical me-1"></i> Nota técnica del problema
                                    </label>
                                    <input type="text" name="problemas[<?= $index ?>][observacion]"
                                        class="form-control form-control-sm"
                                        placeholder="Detalles sobre la reparación de este problema...">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-comment-alt me-1 text-success"></i>
                            Comentario para el Cliente <span class="text-danger">*</span>
                        </label>
                        <textarea name="comentario" class="form-control" rows="3" required
                            placeholder="Ej: Se realizó cambio de pantalla, el equipo funciona correctamente."></textarea>
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-bold">
                            <i class="fas fa-lock me-1 text-muted"></i> Nota Técnica Interna
                        </label>
                        <textarea name="nota_tecnica" class="form-control" rows="2"
                            placeholder="Ej: Se usó repuesto de marca X / Detalle interno..."></textarea>
                    </div>
                </div>

                <div class="modal-footer d-flex flex-wrap gap-2 justify-content-between">
                    <button type="button" class="btn btn-danger btn-sm"
                        onclick="cancelarReparacion(<?= $dispositivo['id'] ?>)">
                        <i class="fas fa-times me-1"></i> Cancelar Reparación
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success btn-sm" id="btnGuardarFinalizar">
                            <i class="fas fa-save me-1"></i> Guardar y Finalizar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     Modal — Clave de acceso
════════════════════════════════════════════ -->
<?php if ($tienePass && $claveVisible !== null): ?>
    <div class="modal fade" id="modalClave" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:360px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold fs-6">
                        <i class="fas fa-lock me-2 text-warning"></i>Clave de acceso —
                        <?= ucfirst(esc($tipoPass)) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body text-center">
                    <?php if ($tipoPass === 'patron'): ?>
                        <p class="text-muted small mb-3">
                            Secuencia: <strong><?= esc($claveVisible) ?></strong>
                        </p>
                        <?php $puntos = array_map('trim', explode(',', $claveVisible)); ?>
                        <div class="d-inline-grid gap-2" style="grid-template-columns:repeat(3,56px);">
                            <?php for ($i = 1; $i <= 9; $i++):
                                $activo = in_array((string) $i, $puntos);
                                $orden = $activo ? (array_search((string) $i, $puntos) + 1) : $i;
                                ?>
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-6 border border-2"
                                    style="width:56px;height:56px;
                                            border-color:<?= $activo ? '#198754' : '#dee2e6' ?> !important;
                                            background:<?= $activo ? 'rgba(25,135,84,.12)' : '#f8f9fa' ?>;
                                            color:<?= $activo ? '#0a3622' : '#adb5bd' ?>;">
                                    <?= $orden ?>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <p class="text-muted small mt-2">Verde = orden del trazo</p>

                    <?php else: ?>
                        <div class="py-3">
                            <div class="fw-bold text-body font-monospace" style="font-size:2rem;letter-spacing:.1em;">
                                <?= esc($claveVisible) ?>
                            </div>
                            <p class="text-muted small mt-2"><?= ucfirst(esc($tipoPass)) ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="modal-footer border-0">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<?php elseif ($tienePass && $claveVisible === null): ?>
    <!-- Si el descifrado falló, mostrar aviso en lugar del modal -->
    <div class="modal fade" id="modalClave" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:360px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold fs-6">
                        <i class="fas fa-lock me-2 text-warning"></i>Clave de acceso
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        No se pudo descifrar la clave. Verifica la configuración de <code>encryption.key</code>.
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    /* ── Repuestos Logic ─────────────────────────────────── */
    let timerRepuesto;

    document.addEventListener('input', function (e) {
        if (!e.target.classList.contains('search-repuesto-input')) return;
        clearTimeout(timerRepuesto);
        const index = e.target.dataset.index;
        const query = e.target.value.trim();
        const resultsDiv = document.getElementById('search-results-' + index);

        if (query.length < 2) { resultsDiv.style.display = 'none'; return; }

        timerRepuesto = setTimeout(() => {
            fetch(`<?= base_url('admin/repuestos/buscar') ?>?term=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(data => {
                    resultsDiv.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const a = document.createElement('a');
                            a.href = '#';
                            a.className = 'dropdown-item small';
                            a.innerHTML = `<strong>${item.nombre}</strong> <span class="text-muted ms-2">$${item.valor}</span>`;
                            a.onclick = (ev) => { ev.preventDefault(); selectRepuesto(index, item); };
                            resultsDiv.appendChild(a);
                        });
                        resultsDiv.style.display = 'block';
                    } else {
                        resultsDiv.style.display = 'none';
                    }
                });
        }, 300);
    });

    function selectRepuesto(index, item) {
        agregarRepuestoAForm(index, item.id, item.nombre, item.valor);
        document.querySelector(`.search-repuesto-input[data-index="${index}"]`).value = '';
        document.getElementById('search-results-' + index).style.display = 'none';
    }

    function agregarRepuestoManual(index) {
        const input = document.querySelector(`.search-repuesto-input[data-index="${index}"]`);
        const nombre = input.value.trim();
        if (!nombre) { showAlert('warning', 'Escribe el nombre del repuesto', 'top-end'); return; }
        agregarRepuestoAForm(index, null, nombre, 0);
        input.value = '';
    }

    function agregarRepuestoAForm(index, id, nombre, valor) {
        const container = document.getElementById('lista-repuestos-' + index);
        const rIndex = container.children.length;

        const saveToCatalogHtml = !id ? `
        <div class="mt-1">
            <label class="form-check-label small">
                <input type="checkbox" class="form-check-input"
                       name="problemas[${index}][repuestos][${rIndex}][guardar_catalogo]" value="1">
                Guardar en catálogo
            </label>
        </div>` : '';

        const html = `
        <div class="mb-2" id="repuesto-wrapper-${index}-${rIndex}">
            <div class="d-flex align-items-center gap-2" id="repuesto-${index}-${rIndex}">
                <input type="hidden" name="problemas[${index}][repuestos][${rIndex}][repuesto_id]" value="${id || ''}">
                <input type="text" name="problemas[${index}][repuestos][${rIndex}][nombre]"
                       class="form-control form-control-sm flex-grow-1" value="${nombre}" placeholder="Nombre">
                <input type="number" name="problemas[${index}][repuestos][${rIndex}][cantidad]"
                       class="form-control form-control-sm rep-cantidad" value="1"
                       oninput="recalcularRepuesto(${index})" min="1" style="width:60px;">
                <input type="number" step="0.01" name="problemas[${index}][repuestos][${rIndex}][valor_unitario]"
                       class="form-control form-control-sm rep-valor" value="${valor}"
                       oninput="recalcularRepuesto(${index})" style="width:80px;">
                <span class="rep-total fw-bold text-nowrap">$${parseFloat(valor).toFixed(2)}</span>
                <button type="button" class="btn btn-link text-danger p-0"
                        onclick="quitarRepuesto(${index}, ${rIndex})">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            ${saveToCatalogHtml}
        </div>`;

        container.insertAdjacentHTML('beforeend', html);
        recalcularRepuesto(index);
    }

    function recalcularRepuesto(index) {
        const container = document.getElementById('lista-repuestos-' + index);
        let total = 0;
        Array.from(container.children).forEach(wrapper => {
            const item = wrapper.querySelector(`#repuesto-${index}-${Array.from(container.children).indexOf(wrapper)}`);
            if (!item) return;
            const cant = parseFloat(item.querySelector('.rep-cantidad').value) || 0;
            const valor = parseFloat(item.querySelector('.rep-valor').value) || 0;
            const subtotal = cant * valor;
            total += subtotal;
            item.querySelector('.rep-total').innerText = '$' + subtotal.toFixed(2);
        });
        document.getElementById('repuesto-total-' + index).value = total.toFixed(2);
        document.getElementById('repuesto-total-display-' + index).innerText = '$' + total.toFixed(2);
    }

    function quitarRepuesto(index, rIndex) {
        document.getElementById(`repuesto-wrapper-${index}-${rIndex}`)?.remove();
        recalcularRepuesto(index);
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.position-relative')) {
            document.querySelectorAll('[id^="search-results-"]').forEach(d => d.style.display = 'none');
        }
    });

    /* ── Abrir modal finalizar ─────────────────────────── */
    function abrirModalFinalizar(e) {
        const tecnicoIdAsignado = <?= json_encode($dispositivo['tecnico_id']) ?>;
        const currentUserId = <?= json_encode(session('id_usuario')) ?>;
        const userRole = <?= json_encode(session('role')) ?>;
        const tecnicoNombre = <?= json_encode($dispositivo['tecnico_nombre'] ?? 'otro técnico') ?>;

        const abrir = () => new bootstrap.Modal(document.getElementById('modalFinalizar')).show();

        if (tecnicoIdAsignado && tecnicoIdAsignado != currentUserId) {
            if (userRole !== 'admin') {
                showAlert('error', 'Este dispositivo está asignado a ' + tecnicoNombre + '.', 'center');
                return;
            }
            Swal.fire({
                title: 'Intervenir Reparación',
                text: 'Asignado a ' + tecnicoNombre + '. Si continúas, pasarás a ser el encargado.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, tomar y finalizar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#f59e0b'
            }).then(res => { if (res.isConfirmed) abrir(); });
        } else {
            abrir();
        }
    }

    /* ── Asignar técnico ─────────────────────────────── */
    document.getElementById('btn-asignar-tecnico-detalle')?.addEventListener('click', function () {
        const tecnicoId = document.getElementById('select-tecnico-detalle').value;
        if (!tecnicoId) { showAlert('warning', 'Selecciona un técnico', 'top-end'); return; }
        asignarTecnico(this.dataset.dispositivo, tecnicoId);
    });

    document.getElementById('btn-reasignar-tecnico-detalle')?.addEventListener('click', function () {
        const tecnicoId = document.getElementById('select-tecnico-detalle-edit').value;
        if (!tecnicoId) { showAlert('warning', 'Selecciona un nuevo técnico', 'top-end'); return; }
        asignarTecnico(this.dataset.dispositivo, tecnicoId);
    });

    function asignarTecnico(dispositivoId, tecnicoId) {
        fetch('<?= base_url('admin/dispositivos/asignar-tecnico') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `dispositivo_id=${dispositivoId}&tecnico_id=${tecnicoId}`
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) { showAlert('success', 'Técnico actualizado', 'top-end'); setTimeout(() => location.reload(), 1000); }
                else showAlert('error', data.message, 'top-end');
            });
    }

    /* ── Iniciar reparación ──────────────────────────── */
    function iniciarReparacion(id) {
        const tecnicoIdAsignado = <?= json_encode($dispositivo['tecnico_id']) ?>;
        const currentUserId = <?= json_encode(session('id_usuario')) ?>;
        const userRole = <?= json_encode(session('role')) ?>;
        const tecnicoNombre = <?= json_encode($dispositivo['tecnico_nombre'] ?? 'otro técnico') ?>;

        let title = '¿Iniciar reparación?';
        let text = 'El dispositivo pasará a estado "En Proceso".';
        let icon = 'question';

        if (tecnicoIdAsignado && tecnicoIdAsignado != currentUserId) {
            if (userRole !== 'admin') {
                showAlert('error', 'Este dispositivo está asignado a ' + tecnicoNombre + '.', 'center');
                return;
            }
            title = 'Intervenir Reparación';
            text = 'Asignado a ' + tecnicoNombre + '. Si continúas, se te re-asignará.';
            icon = 'warning';
        } else if (!tecnicoIdAsignado) {
            title = 'Asignación Automática';
            text = 'No hay técnico asignado. Quedarás como responsable.';
            icon = 'info';
        }

        Swal.fire({
            title, text, icon, showCancelButton: true,
            confirmButtonText: 'Sí, tomar control', cancelButtonText: 'Cancelar',
            confirmButtonColor: icon === 'warning' ? '#f59e0b' : '#0d6efd'
        })
            .then(res => {
                if (!res.isConfirmed) return;
                fetch('<?= base_url('admin/dispositivos/reparacion/iniciar') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `dispositivo_id=${id}&estado=en_proceso`
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { showAlert('success', data.message || 'Reparación iniciada', 'top-end'); setTimeout(() => location.reload(), 1000); }
                        else showAlert('error', data.message, 'center');
                    });
            });
    }

    /* ── Entregar dispositivo ────────────────────────── */
    function entregarDispositivo(id) {
        const tecnicoIdAsignado = <?= json_encode($dispositivo['tecnico_id']) ?>;
        const currentUserId = <?= json_encode(session('id_usuario')) ?>;
        const userRole = <?= json_encode(session('role')) ?>;
        const tecnicoNombre = <?= json_encode($dispositivo['tecnico_nombre'] ?? 'otro técnico') ?>;

        let title = '¿Entregar al cliente?';
        let text = 'Esta acción marcará el dispositivo como entregado.';
        let icon = 'question';

        if (tecnicoIdAsignado && tecnicoIdAsignado != currentUserId && userRole === 'admin') {
            title = 'Intervenir para Entrega';
            text = 'Reparado por ' + tecnicoNombre + '. Tú quedarás como responsable de la entrega.';
            icon = 'warning';
        }

        Swal.fire({
            title, text, icon, showCancelButton: true,
            confirmButtonText: 'Confirmar entrega', cancelButtonText: 'Cancelar',
            confirmButtonColor: icon === 'warning' ? '#f59e0b' : '#0dcaf0'
        })
            .then(res => {
                if (!res.isConfirmed) return;
                fetch('<?= base_url('admin/dispositivos/entregar') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `dispositivo_id=${id}`
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { showAlert('success', 'Dispositivo entregado', 'top-end'); setTimeout(() => location.reload(), 1000); }
                        else showAlert('error', data.message, 'center');
                    });
            });
    }

    /* ── Cancelar reparación ─────────────────────────── */
    function cancelarReparacion(id) {
        const modalFinalizar = bootstrap.Modal.getInstance(document.getElementById('modalFinalizar'));
        if (modalFinalizar) modalFinalizar.hide();

        setTimeout(() => {
            Swal.fire({
                title: '¿Cancelar reparación?',
                html: `
        <div style="text-align:left;">
          <label style="font-size:.8rem;font-weight:700;color:#8a92a0;text-transform:uppercase;">Motivo para el Cliente <span style="color:red">*</span></label>
          <textarea id="swal-comentario" class="swal2-textarea" style="margin:8px 0;width:100%;box-sizing:border-box;" placeholder="Ej: No se consiguió el repuesto..."></textarea>
          <label style="font-size:.8rem;font-weight:700;color:#8a92a0;text-transform:uppercase;display:block;margin-top:8px;">Nota Técnica Interna</label>
          <textarea id="swal-nota-tecnica" class="swal2-textarea" style="margin:8px 0;width:100%;box-sizing:border-box;" placeholder="Ej: Se intentó reparar X pero falló Y..."></textarea>
        </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Cancelar reparación',
                cancelButtonText: 'Volver',
                confirmButtonColor: '#dc3545',
                preConfirm: () => {
                    const comentario = document.getElementById('swal-comentario').value;
                    const nota_tecnica = document.getElementById('swal-nota-tecnica').value;
                    if (!comentario) { Swal.showValidationMessage('El motivo es obligatorio'); return false; }
                    return { comentario, nota_tecnica };
                }
            }).then(res => {
                if (!res.isConfirmed) return;
                const fd = new FormData();
                fd.append('dispositivo_id', id);
                fd.append('cancelar', 'true');
                fd.append('comentario', res.value.comentario);
                fd.append('nota_tecnica', res.value.nota_tecnica);
                fetch('<?= base_url('admin/dispositivos/reparacion/finalizar') ?>', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) { showAlert('success', 'Reparación cancelada', 'top-end'); setTimeout(() => location.reload(), 1000); }
                        else showAlert('error', data.message, 'center');
                    });
            });
        }, 300);
    }

    /* ── Finalizar reparación ────────────────────────── */
    document.getElementById('formFinalizar')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = document.getElementById('btnGuardarFinalizar');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Procesando...';

        fetch('<?= base_url('admin/dispositivos/reparacion/finalizar') ?>', {
            method: 'POST', body: new FormData(this)
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalFinalizar'))?.hide();
                    showAlert('success', data.message || 'Reparación finalizada', 'top-end');
                    setTimeout(() => location.reload(), 1100);
                } else {
                    showAlert('error', data.message, 'center');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save me-1"></i> Guardar y Finalizar';
                }
            })
            .catch(() => {
                showAlert('error', 'Error de conexión', 'center');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i> Guardar y Finalizar';
            });
    });
</script>

<?= $this->endSection() ?>