<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Orden <?= esc($orden['numero_orden']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 8.5px;
            color: #1a1a2e;
            background: #fff;
            padding: 10px 12px;
        }

        /* ══ LAYOUT PRINCIPAL 65/35 ══ */
        .page-wrap {
            display: table;
            width: 100%;
        }

        .col-legal {
            display: table-cell;
            width: 65%;
            vertical-align: top;
            padding-right: 12px;
        }

        .col-ticket {
            display: table-cell;
            width: 35%;
            vertical-align: top;
            padding-left: 12px;
            border-left: 1.5px dashed #b0bec5;
        }

        /* ══ BADGES ══ */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 2px;
            font-size: 6px;
            font-weight: bold;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .bg-pendiente {
            background: #607d8b;
        }

        .bg-en_proceso {
            background: #1565c0;
        }

        .bg-pausado {
            background: #f57f17;
        }

        .bg-listo {
            background: #2e7d32;
        }

        .bg-entregado {
            background: #00838f;
        }

        .bg-cancelado {
            background: #c62828;
        }

        /* ══ CABECERA ══ */
        .header-legal {
            display: table;
            width: 100%;
            border-bottom: 2px solid #1a1a2e;
            padding-bottom: 7px;
            margin-bottom: 8px;
        }

        .hl-logo {
            display: table-cell;
            width: 40px;
            vertical-align: middle;
        }

        .hl-logo img {
            width: 36px;
            height: auto;
        }

        .hl-empresa {
            display: table-cell;
            vertical-align: middle;
            padding-left: 7px;
        }

        .hl-empresa h1 {
            font-size: 10px;
            font-weight: bold;
            color: #1a1a2e;
        }

        .hl-empresa p {
            font-size: 6.5px;
            color: #607d8b;
            margin-top: 1px;
        }

        .hl-orden {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
        }

        .hl-orden .num {
            font-size: 16px;
            font-weight: bold;
            color: #1a1a2e;
            display: block;
            line-height: 1;
        }

        .hl-orden .fecha {
            font-size: 6.5px;
            color: #607d8b;
            display: block;
            margin: 2px 0 3px;
        }

        /* ══ BLOQUES ══ */
        .bloque {
            background: #f5f7fa;
            border-left: 3px solid #1a1a2e;
            padding: 5px 7px;
            margin-bottom: 7px;
            border-radius: 0 3px 3px 0;
        }

        .bloque-tit {
            font-size: 6px;
            font-weight: bold;
            color: #607d8b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 4px;
            padding-bottom: 2px;
            border-bottom: 1px solid #dde3ea;
        }

        .fila {
            display: table;
            width: 100%;
            margin-bottom: 2px;
        }

        .f-lbl {
            display: table-cell;
            width: 30%;
            font-size: 7px;
            color: #78909c;
            vertical-align: top;
        }

        .f-val {
            display: table-cell;
            width: 70%;
            font-size: 7.5px;
            font-weight: bold;
            color: #1a1a2e;
            vertical-align: top;
        }

        /* ══ SUBTÍTULOS ══ */
        .subtit {
            font-size: 6px;
            font-weight: bold;
            color: #607d8b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 1px solid #dde3ea;
            padding-bottom: 2px;
            margin-bottom: 5px;
            margin-top: 5px;
        }

        /* ══ DISPOSITIVOS ══ */
        .dcard {
            border: 1px solid #dde3ea;
            border-radius: 4px;
            margin-bottom: 7px;
            page-break-inside: avoid;
        }

        .dcard-head {
            background: #1a1a2e;
            color: #fff;
            padding: 4px 7px;
            border-radius: 3px 3px 0 0;
        }

        .dh-layout {
            display: table;
            width: 100%;
        }

        .dh-izq {
            display: table-cell;
            vertical-align: middle;
        }

        .dev-name {
            font-size: 8.5px;
            font-weight: bold;
        }

        .dev-sn {
            font-size: 6px;
            color: #b0bec5;
            margin-top: 1px;
        }

        .dh-der {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
            width: 65px;
        }

        .dcard-body {
            padding: 5px 7px;
        }

        .lista-servicios {
            list-style: none;
        }

        .lista-servicios li {
            display: table;
            width: 100%;
            border-bottom: 1px dashed #e8ecf0;
            padding: 2px 0;
        }

        .lista-servicios li:last-child {
            border-bottom: none;
        }

        .srv-desc {
            display: table-cell;
            width: 75%;
            font-size: 7px;
            color: #37474f;
            vertical-align: middle;
        }

        .srv-obs {
            font-size: 6px;
            color: #90a4ae;
            font-style: italic;
        }

        .srv-precio {
            display: table-cell;
            width: 25%;
            font-size: 7.5px;
            font-weight: bold;
            text-align: right;
            color: #1a1a2e;
            vertical-align: middle;
        }

        .srv-precio.pending {
            color: #f57f17;
            font-size: 6.5px;
        }

        .chip {
            display: inline-block;
            background: #e8edf5;
            color: #37474f;
            border-radius: 2px;
            padding: 1px 5px;
            font-size: 6px;
            margin: 1px 1px 1px 0;
        }

        .chip-w {
            background: #fff8e1;
            color: #7a5a00;
        }

        .chip-p {
            background: #ffebee;
            color: #c62828;
            font-weight: bold;
        }

        .d-total {
            display: table;
            width: 100%;
            background: #f0f4fa;
            border-top: 1px solid #dde3ea;
            padding: 3px 7px;
            border-radius: 0 0 3px 3px;
        }

        .d-total-lbl {
            display: table-cell;
            font-size: 6.5px;
            color: #607d8b;
            vertical-align: middle;
        }

        .d-total-val {
            display: table-cell;
            text-align: right;
            font-size: 8.5px;
            font-weight: bold;
            color: #1a1a2e;
            vertical-align: middle;
        }

        .d-total-val.pending {
            font-size: 7px;
            color: #f57f17;
        }

        /* ══ TÉRMINOS ══ */
        .terminos-wrap {
            border: 1px solid #dde3ea;
            border-radius: 4px;
            padding: 6px 8px;
            margin-top: 7px;
            background: #fafbfc;
            page-break-inside: avoid;
        }

        .terminos-lista {
            padding-left: 11px;
            margin-top: 3px;
        }

        /* ══ AUTORIZACIÓN ══ */
        .autorizacion-box {
            border: 1.5px solid #1a1a2e;
            border-radius: 4px;
            padding: 7px 10px;
            margin-top: 8px;
            background: #f5f7fa;
            page-break-inside: avoid;
        }

        .auth-tit {
            font-size: 7.5px;
            font-weight: bold;
            color: #1a1a2e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            text-align: center;
            border-bottom: 1px solid #dde3ea;
            padding-bottom: 4px;
        }

        .auth-texto {
            font-size: 7px;
            color: #37474f;
            line-height: 1.55;
            margin-bottom: 6px;
            text-align: justify;
        }

        .auth-texto strong {
            color: #1a1a2e;
        }

        .aviso-pending {
            background: #fff8e1;
            border: 1px solid #f9a825;
            border-radius: 3px;
            padding: 4px 7px;
            margin-bottom: 6px;
            font-size: 6.5px;
            color: #7a5a00;
            text-align: center;
        }

        .aviso-pending strong {
            display: block;
            font-size: 7px;
            margin-bottom: 1px;
        }

        .firma-unica {
            margin-top: 20px;
            width: 55%;
        }

        .f-line {
            border-top: 1px solid #1a1a2e;
            margin-bottom: 3px;
        }

        .f-nom {
            font-size: 7.5px;
            font-weight: bold;
            color: #1a1a2e;
        }

        .f-doc {
            font-size: 6px;
            color: #607d8b;
            margin-top: 1px;
        }

        .f-sub {
            font-size: 6px;
            color: #90a4ae;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 1px;
        }

        /* ══ TICKET DERECHO ══ */
        .ticket-header {
            text-align: center;
            padding-bottom: 7px;
            border-bottom: 1px solid #dde3ea;
            margin-bottom: 7px;
        }

        .ticket-header h2 {
            font-size: 6.5px;
            font-weight: bold;
            color: #90a4ae;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .ticket-header .empresa-name {
            font-size: 9px;
            font-weight: bold;
            color: #1a1a2e;
        }

        .ticket-header .tel {
            font-size: 7px;
            color: #607d8b;
            margin-top: 1px;
        }

        .ticket-orden-num {
            text-align: center;
            background: #1a1a2e;
            color: #fff;
            border-radius: 4px;
            padding: 6px;
            margin-bottom: 7px;
        }

        .ticket-orden-num .label {
            font-size: 5.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #90a4ae;
            margin-bottom: 2px;
        }

        .ticket-orden-num .num {
            font-size: 18px;
            font-weight: bold; line-height: 1;
        }

        .ticket-orden-num .estado-wrap {
            margin-top: 4px;
        }

        .qr-box {
            text-align: center;
            margin-bottom: 7px;
        }

        .qr-box img {
            width: 72px;
            height: 72px;
            border: 1px solid #dde3ea;
            border-radius: 3px;
        }

        .qr-lbl {
            font-size: 6px;
            color: #90a4ae;
            margin-top: 2px;
        }

        .t-info-row {
            display: table;
            width: 100%;
            margin-bottom: 2px;
        }

        .t-info-lbl {
            display: table-cell;
            width: 38%;
            font-size: 6px;
            color: #90a4ae;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            vertical-align: top;
        }

        .t-info-val {
            display: table-cell;
            width: 62%;
            font-size: 7px;
            font-weight: bold;
            color: #1a1a2e;
            vertical-align: top;
        }

        .entrega-box {
            border-radius: 3px;
            padding: 5px 7px;
            margin: 6px 0;
            text-align: center;
        }

        .entrega-box.confirmada {
            background: #e8f5e9;
            border: 1px solid #a5d6a7;
        }

        .entrega-box.pendiente {
            background: #f5f7fa;
            border: 1px solid #dde3ea;
        }

        .e-lbl {
            font-size: 5.5px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: bold;
        }

        .entrega-box.confirmada .e-lbl {
            color: #388e3c;
        }

        .entrega-box.pendiente .e-lbl {
            color: #90a4ae;
        }

        .e-fecha {
            font-size: 10px;
            font-weight: bold;
            margin-top: 1px;
        }

        .entrega-box.confirmada .e-fecha {
            color: #1b5e20;
        }

        .entrega-box.pendiente .e-fecha {
            color: #b0bec5;
            font-size: 8px;
        }

        /* Items normales (1-2 dispositivos) */
        .t-item {
            border-bottom: 1px dashed #dde3ea;
            padding: 4px 0;
            page-break-inside: avoid;
        }

        .t-item:last-child {
            border-bottom: none;
        }

        .t-item-tit {
            font-size: 7.5px;
            font-weight: bold;
            color: #1a1a2e;
        }

        .t-item-sn {
            font-size: 6px;
            color: #90a4ae;
            margin-top: 1px;
        }

        .t-item-estado {
            font-size: 6.5px;
            color: #607d8b;
            margin-top: 1px;
        }

        .t-item-price {
            text-align: right;
            font-size: 8px;
            font-weight: bold;
            color: #1a1a2e;
            margin-top: 2px;
        }

        .t-item-price.pending {
            font-size: 6.5px;
            color: #f57f17;
        }

        /* Items compactos (3+ dispositivos) */
        .t-item-compact {
            display: table;
            width: 100%;
            border-bottom: 1px dashed #dde3ea;
            padding: 3px 0;
        }

        .t-item-compact:last-child {
            border-bottom: none;
        }

        .tic-info {
            display: table-cell;
            vertical-align: middle;
        }

        .tic-info span {
            font-size: 7px;
            font-weight: bold;
            color: #1a1a2e;
        }

        .tic-info small {
            font-size: 6px;
            color: #90a4ae;
            display: block;
        }

        .tic-price {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
            font-size: 7.5px;
            font-weight: bold;
            color: #1a1a2e;
            white-space: nowrap;
        }

        .tic-price.pending {
            font-size: 6px;
            color: #f57f17;
        }

        /* Gran total */
        .gran-total {
            background: #1a1a2e;
            color: #fff;
            text-align: center;
            padding: 7px 6px;
            border-radius: 4px;
            margin-top: 7px;
        }

        .gt-lbl {
            font-size: 5.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #90a4ae;
            margin-bottom: 2px;
        }

        .gt-val {
            font-size: 14px;
            font-weight: bold;
        }

        .gt-pending {
            font-size: 7.5px;
            color: #ffcc02;
        }

        .t-nota {
            font-size: 6px;
            color: #90a4ae;
            text-align: center;
            margin-top: 7px;
            padding-top: 5px;
            border-top: 1px solid #eee;
            line-height: 1.5;
        }
    </style>
</head>

<body>

    <?php
    function estadoLabel(string $e): string
    {
        return match ($e) {
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En Proceso',
            'pausado' => 'Pausado',
            'listo' => 'Listo',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado',
            default => ucfirst($e),
        };
    }

    $total_general = array_sum(array_column($dispositivos, 'precio_total'));
    $total_prioridad = array_sum(array_column($dispositivos, 'costo_prioridad'));
    $es_sin_precio = ($total_general == 0);
    $num_dispositivos = count($dispositivos);
    $ticket_compacto = ($num_dispositivos > 2);

    // Tamaño de términos adaptativo
    $num_terminos = !empty($terminos) ? count($terminos) : 0;
    $termino_size = match (true) {
        $num_terminos <= 3 => '8px',
        $num_terminos <= 6 => '7.5px',
        $num_terminos <= 9 => '7px',
        default => '6.5px',
    };
    ?>

    <div class="page-wrap">

        <!-- ══ COL LEGAL 65% ══ -->
        <div class="col-legal">

            <div class="header-legal">
                <div class="hl-logo">
                    <?php if (!empty($empresa_config['logo_path']) && file_exists(FCPATH . $empresa_config['logo_path'])): ?>
                        <img src="<?= FCPATH . esc($empresa_config['logo_path']) ?>" alt="Logo">
                    <?php endif; ?>
                </div>
                <div class="hl-empresa">
                    <h1>ORDEN DE SERVICIO</h1>
                    <p><?= esc($empresa_config['nombre_empresa']) ?></p>
                    <p><?= esc($empresa_config['telefono'] ?? '') ?></p>
                </div>
                <div class="hl-orden">
                    <span class="num">#<?= esc($orden['numero_orden']) ?></span>
                    <span class="fecha"><?= date('d/m/Y — H:i', strtotime($orden['fecha_ingreso'])) ?></span>
                    <span class="badge bg-<?= esc($orden['estado']) ?>"><?= estadoLabel($orden['estado']) ?></span>
                </div>
            </div>

            <div class="bloque">
                <div class="bloque-tit">Datos del Cliente</div>
                <div class="fila">
                    <div class="f-lbl">Nombre</div>
                    <div class="f-val"><?= esc($orden['cliente_nombre']) ?></div>
                </div>
                <div class="fila">
                    <div class="f-lbl">Teléfono</div>
                    <div class="f-val"><?= esc($orden['cliente_telefono'] ?: '—') ?></div>
                </div>
                <div class="fila">
                    <div class="f-lbl">Doc. Identidad</div>
                    <div class="f-val"><?= esc($orden['cliente_cedula'] ?: '—') ?></div>
                </div>
            </div>

            <div class="subtit">Equipos Ingresados (<?= $num_dispositivos ?>)</div>

            <?php foreach ($dispositivos as $i => $dev):
                $dev_sin_precio = ((float) $dev['precio_total'] == 0);
                ?>
                <div class="dcard">
                    <div class="dcard-head">
                        <div class="dh-layout">
                            <div class="dh-izq">
                                <div class="dev-name">#<?= $i + 1 ?> &nbsp;<?= esc($dev['marca']) ?>
                                    <?= esc($dev['modelo'] ?? '') ?></div>
                                <?php if (!empty($dev['serie_imei'])): ?>
                                    <div class="dev-sn">S/N: <?= esc($dev['serie_imei']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="dh-der">
                                <span class="badge bg-<?= esc($dev['estado']) ?>"><?= estadoLabel($dev['estado']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="dcard-body">
                        <?php if (!empty($dev['problemas'])): ?>
                            <div class="subtit" style="margin-top:0;">Servicios / Problemas Reportados</div>
                            <ul class="lista-servicios">
                                <?php foreach ($dev['problemas'] as $prob):
                                    $prob_sin_precio = ((float) $prob['subtotal'] == 0);
                                    ?>
                                    <li>
                                        <div class="srv-desc">
                                            &bull; <?= esc($prob['problema']) ?>
                                            <?php if (!empty($prob['observacion'])): ?>
                                                <div class="srv-obs"><?= esc($prob['observacion']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="srv-precio <?= $prob_sin_precio ? 'pending' : '' ?>">
                                            <?= $prob_sin_precio ? 'Por diagnosticar' : '$' . number_format((float) $prob['subtotal'], 2) ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>

                                <?php if ($dev['costo_prioridad'] > 0): ?>
                                    <li style="border-top: 1px dashed #eee;">
                                        <div class="srv-desc" style="color: #c62828; font-weight: bold;">
                                            &bull; Cargo por Prioridad: <?= esc($dev['prioridad']) ?>
                                        </div>
                                        <div class="srv-precio" style="color: #c62828;">
                                            $<?= number_format((float) $dev['costo_prioridad'], 2) ?>
                                        </div>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if (!empty($dev['accesorios']) || !empty($dev['detalles']) || $dev['costo_prioridad'] > 0): ?>
                            <div style="margin-top:4px;">
                                <?php if (!empty($dev['accesorios'])): ?>
                                    <div class="subtit">Accesorios Recibidos</div>
                                    <?php foreach ($dev['accesorios'] as $acc): ?>
                                        <span
                                            class="chip"><?= esc($acc['accesorio']) ?><?= ($acc['cantidad'] > 1) ? ' ×' . $acc['cantidad'] : '' ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (!empty($dev['detalles'])): ?>
                                    <div class="subtit" style="margin-top:4px;">Estado Físico al Ingreso</div>
                                    <?php foreach ($dev['detalles'] as $det): ?>
                                        <span class="chip chip-w"><?= esc($det['detalle']) ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if ($dev['costo_prioridad'] > 0): ?>
                                    <span class="chip chip-p">PRIORIDAD: <?= strtoupper(esc($dev['prioridad'])) ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-total">
                        <div class="d-total-lbl">Estimado equipo #<?= $i + 1 ?></div>
                        <div class="d-total-val <?= $dev_sin_precio ? 'pending' : '' ?>">
                            <?= $dev_sin_precio ? 'Pendiente diagnóstico' : '$' . number_format((float) $dev['precio_total'], 2) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (!empty($terminos)): ?>
                <div class="terminos-wrap">
                    <div class="bloque-tit">Términos y Condiciones</div>
                    <ol class="terminos-lista">
                        <?php foreach ($terminos as $t): ?>
                            <li style="font-size:<?= $termino_size ?>; color:#546e7a; margin-bottom:2px; line-height:1.45;">
                                <?= esc($t) ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            <?php endif; ?>

            <div class="autorizacion-box">
                <div class="auth-tit">Autorización de Revisión y Diagnóstico</div>

                <?php if ($es_sin_precio): ?>
                    <div class="aviso-pending">
                        <strong>⚠ Sin presupuesto definido</strong>
                        Esta firma autoriza únicamente la revisión y diagnóstico del equipo. El presupuesto será comunicado
                        antes de proceder con cualquier reparación.
                    </div>
                <?php endif; ?>

                <div class="auth-texto">
                    Yo,
                    <strong><?= esc($orden['cliente_nombre']) ?></strong><?php if (!empty($orden['cliente_cedula'])): ?>,
                        portador del documento de identidad N°
                        <strong><?= esc($orden['cliente_cedula']) ?></strong><?php endif; ?>, declaro haber entregado el/los
                    equipo(s) detallado(s) en esta orden en las condiciones físicas descritas, y autorizo a
                    <strong><?= esc($empresa_config['nombre_empresa']) ?></strong> a realizar la revisión y diagnóstico
                    técnico correspondiente. Confirmo haber leído y aceptado los términos y condiciones establecidos en
                    este documento.<?php if (!$es_sin_precio): ?> El costo estimado de reparación asciende a
                        <strong>$<?= number_format($total_general, 2) ?></strong>.<?php endif; ?>
                    
                    <?php if ($total_prioridad > 0): ?>
                        <br><br>
                        <strong>ACEPTACIÓN DE TRABAJO PRIORITARIO:</strong> El cliente acepta y autoriza el cargo adicional por concepto de prioridad en la atención de su(s) equipo(s), entendiendo que este valor garantiza un tiempo de respuesta preferencial según lo estipulado en el detalle de la orden.
                    <?php endif; ?>
                </div>

                <div class="firma-unica">
                    <div class="f-line"></div>
                    <div class="f-nom"><?= esc($orden['cliente_nombre']) ?></div>
                    <?php if (!empty($orden['cliente_cedula'])): ?>
                        <div class="f-doc">C.I. <?= esc($orden['cliente_cedula']) ?></div>
                    <?php endif; ?>
                    <div class="f-sub">Firma del Cliente</div>
                </div>
            </div>

        </div><!-- /col-legal -->


        <!-- ══ COL TICKET 35% ══ -->
        <div class="col-ticket">

            <div class="ticket-header">
                <h2>Ticket de Servicio</h2>
                <div class="empresa-name"><?= esc($empresa_config['nombre_empresa']) ?></div>
                <div class="tel">📞 <?= esc($empresa_config['telefono'] ?? '') ?></div>
            </div>

            <div class="ticket-orden-num">
                <div class="label">Orden N°</div>
                <div class="num">#<?= esc($orden['numero_orden']) ?></div>
                <div class="estado-wrap">
                    <span class="badge bg-<?= esc($orden['estado']) ?>"><?= estadoLabel($orden['estado']) ?></span>
                </div>
            </div>

            <?php if (!empty($qr_code)): ?>
                <div class="qr-box">
                    <img src="<?= $qr_code ?>" alt="QR">
                    <div class="qr-lbl">Escanea para seguir tu equipo</div>
                </div>
            <?php endif; ?>

            <div class="bloque"
                style="background:transparent; border:1px solid #dde3ea; border-left:3px solid #1565c0;">
                <div class="bloque-tit">Información</div>
                <div class="t-info-row">
                    <div class="t-info-lbl">Cliente</div>
                    <div class="t-info-val"><?= esc($orden['cliente_nombre']) ?></div>
                </div>
                <div class="t-info-row">
                    <div class="t-info-lbl">Ingreso</div>
                    <div class="t-info-val"><?= date('d/m/Y H:i', strtotime($orden['fecha_ingreso'])) ?></div>
                </div>
            </div>

            <?php if (!empty($orden['fecha_estimada_entrega'])): ?>
                <div class="entrega-box confirmada">
                    <div class="e-lbl">✓ Entrega Estimada</div>
                    <div class="e-fecha"><?= date('d/m/Y', strtotime($orden['fecha_estimada_entrega'])) ?></div>
                </div>
            <?php else: ?>
                <div class="entrega-box pendiente">
                    <div class="e-lbl">Entrega Estimada</div>
                    <div class="e-fecha">Por confirmar</div>
                </div>
            <?php endif; ?>

            <div class="subtit">Equipos (<?= $num_dispositivos ?>)</div>

            <?php if ($ticket_compacto): ?>
                <!-- Vista compacta: 3+ dispositivos -->
                <?php foreach ($dispositivos as $i => $dev):
                    $dev_sin_precio = ((float) $dev['precio_total'] == 0);
                    ?>
                    <div class="t-item-compact">
                        <div class="tic-info">
                            <span>#<?= $i + 1 ?>         <?= esc($dev['marca']) ?>         <?= esc($dev['modelo'] ?? '') ?></span>
                            <small><?= estadoLabel($dev['estado']) ?><?= !empty($dev['serie_imei']) ? ' · ' . esc($dev['serie_imei']) : '' ?></small>
                            <?php if ($dev['costo_prioridad'] > 0): ?>
                                <small style="color: #c62828;">(Prioridad: <?= esc($dev['prioridad']) ?>)</small>
                            <?php endif; ?>
                        </div>
                        <div class="tic-price <?= $dev_sin_precio ? 'pending' : '' ?>">
                            <?= $dev_sin_precio ? 'Por diag.' : '$' . number_format((float) $dev['precio_total'], 2) ?>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <!-- Vista normal: 1-2 dispositivos -->
                <?php foreach ($dispositivos as $i => $dev):
                    $dev_sin_precio = ((float) $dev['precio_total'] == 0);
                    ?>
                    <div class="t-item">
                        <div class="t-item-tit">#<?= $i + 1 ?>         <?= esc($dev['marca']) ?>         <?= esc($dev['modelo'] ?? '') ?></div>
                        <?php if (!empty($dev['serie_imei'])): ?>
                            <div class="t-item-sn">S/N: <?= esc($dev['serie_imei']) ?></div>
                        <?php endif; ?>
                        <div class="t-item-estado">Estado: <strong><?= estadoLabel($dev['estado']) ?></strong></div>
                        <?php if ($dev['costo_prioridad'] > 0): ?>
                            <div class="t-item-estado" style="color: #c62828;">Prioridad: <strong><?= esc($dev['prioridad']) ?></strong></div>
                        <?php endif; ?>
                        <div class="t-item-price <?= $dev_sin_precio ? 'pending' : '' ?>">
                            <?= $dev_sin_precio ? '⚠ Por diagnosticar' : '$' . number_format((float) $dev['precio_total'], 2) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="gran-total">
                <div class="gt-lbl">Total Estimado</div>
                <?php if ($es_sin_precio): ?>
                    <div class="gt-pending">⚠ Pendiente de diagnóstico</div>
                <?php else: ?>
                    <div class="gt-val">$<?= number_format($total_general, 2) ?></div>
                <?php endif; ?>
            </div>

            <?php if (!empty($orden['observaciones_generales'])): ?>
                <div
                    style="background:#fffbf0; border:1px solid #ffe082; border-radius:3px; padding:4px 6px; margin-top:6px;">
                    <div
                        style="font-size:5.5px; text-transform:uppercase; letter-spacing:0.8px; color:#f9a825; font-weight:bold; margin-bottom:2px;">
                        Nota</div>
                    <div style="font-size:6.5px; color:#37474f;"><?= esc($orden['observaciones_generales']) ?></div>
                </div>
            <?php endif; ?>

            <div class="t-nota">
                Conserve este ticket para retirar su equipo.<br>
                Se requiere presentar este comprobante.<br>
                <?= esc($empresa_config['nombre_empresa']) ?>
            </div>

        </div><!-- /col-ticket -->

    </div>
</body>

</html>