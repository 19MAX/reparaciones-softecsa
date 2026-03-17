<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ticket <?= esc($orden['numero_orden']) ?></title>
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

        /* ══ BLOQUE GENÉRICO ══ */
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

        /* ══ TICKET HEADER ══ */
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

        /* ══ NÚMERO DE ORDEN ══ */
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
            font-weight: bold;
            line-height: 1;
        }

        .ticket-orden-num .estado-wrap {
            margin-top: 4px;
        }

        /* ══ QR ══ */
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

        /* ══ FILAS INFO ══ */
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

        /* ══ ENTREGA ══ */
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

        /* ══ ITEMS NORMALES (1-2 dispositivos) ══ */
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

        /* ══ ITEMS COMPACTOS (3+ dispositivos) ══ */
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

        /* ══ GRAN TOTAL ══ */
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

        /* ══ NOTA FINAL ══ */
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
    // Reutiliza la misma función si este archivo se renderiza de forma independiente.
// Si se incluye en el mismo contexto que orden_legal.php, elimina la declaración.
    if (!function_exists('estadoLabelPdf')) {
        function estadoLabelPdf(string $e): string
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
    }

    $total_general = array_sum(array_column($dispositivos, 'precio_total'));
    $es_sin_precio = ($total_general == 0);
    $num_dispositivos = count($dispositivos);
    $ticket_compacto = ($num_dispositivos > 2);
    ?>

    <!-- ══ ENCABEZADO TICKET ══ -->
    <div class="ticket-header">
        <h2>Ticket de Servicio</h2>
        <div class="empresa-name"><?= esc($empresa_config['nombre_empresa']) ?></div>
        <div class="tel">📞 <?= esc($empresa_config['telefono'] ?? '') ?></div>
    </div>

    <!-- ══ NÚMERO DE ORDEN ══ -->
    <div class="ticket-orden-num">
        <div class="label">Orden N°</div>
        <div class="num">#<?= esc($orden['numero_orden']) ?></div>
        <div class="estado-wrap">
            <span class="badge bg-<?= esc($orden['estado']) ?>"><?= estadoLabelPdf($orden['estado']) ?></span>
        </div>
    </div>

    <!-- ══ QR ══ -->
    <?php if (!empty($qr_code)): ?>
        <div class="qr-box">
            <img src="<?= $qr_code ?>" alt="QR">
            <div class="qr-lbl">Escanea para seguir tu equipo</div>
        </div>
    <?php endif; ?>

    <!-- ══ INFORMACIÓN GENERAL ══ -->
    <div class="bloque" style="background:transparent; border:1px solid #dde3ea; border-left:3px solid #1565c0;">
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

    <!-- ══ FECHA DE ENTREGA ══ -->
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

    <!-- ══ EQUIPOS ══ -->
    <div class="subtit">Equipos (<?= $num_dispositivos ?>)</div>

    <?php if ($ticket_compacto): ?>
        <!-- Vista compacta: 3+ dispositivos -->
        <?php foreach ($dispositivos as $i => $dev):
            $dev_sin_precio = ((float) $dev['precio_total'] == 0);
            ?>
            <div class="t-item-compact">
                <div class="tic-info">
                    <span>#<?= $i + 1 ?>         <?= esc($dev['marca']) ?>         <?= esc($dev['modelo'] ?? '') ?></span>
                    <small><?= estadoLabelPdf($dev['estado']) ?><?= !empty($dev['serie_imei']) ? ' · ' . esc($dev['serie_imei']) : '' ?></small>
                    <?php if ($dev['costo_prioridad'] > 0): ?>
                        <small style="color:#c62828;">(Prioridad: <?= esc($dev['prioridad']) ?>)</small>
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
                <div class="t-item-estado">Estado: <strong><?= estadoLabelPdf($dev['estado']) ?></strong></div>
                <?php if ($dev['estado'] === 'cancelado' && !empty($dev['comentario_cliente'])): ?>
                    <div style="font-size:5.5px; color:#b91c1c; font-style:italic; margin-top:1px;">
                        Info: <?= esc($dev['comentario_cliente']) ?>
                    </div>
                <?php endif; ?>
                <?php if ($dev['costo_prioridad'] > 0): ?>
                    <div class="t-item-estado" style="color:#c62828;">
                        Prioridad: <strong><?= esc($dev['prioridad']) ?></strong>
                    </div>
                <?php endif; ?>
                <div class="t-item-price <?= $dev_sin_precio ? 'pending' : '' ?>">
                    <?= $dev_sin_precio ? '⚠ Por diagnosticar' : '$' . number_format((float) $dev['precio_total'], 2) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- ══ GRAN TOTAL ══ -->
    <div class="gran-total">
        <div class="gt-lbl">Total Estimado</div>
        <?php if ($es_sin_precio): ?>
            <div class="gt-pending">⚠ Pendiente de diagnóstico</div>
        <?php else: ?>
            <div class="gt-val">$<?= number_format($total_general, 2) ?></div>
        <?php endif; ?>
    </div>

    <!-- ══ OBSERVACIONES ══ -->
    <?php if (!empty($orden['observaciones_generales'])): ?>
        <div style="background:#fffbf0; border:1px solid #ffe082; border-radius:3px; padding:4px 6px; margin-top:6px;">
            <div
                style="font-size:5.5px; text-transform:uppercase; letter-spacing:0.8px; color:#f9a825; font-weight:bold; margin-bottom:2px;">
                Nota</div>
            <div style="font-size:6.5px; color:#37474f;"><?= esc($orden['observaciones_generales']) ?></div>
        </div>
    <?php endif; ?>

    <!-- ══ NOTA FINAL ══ -->
    <div class="t-nota">
        Conserve este ticket para retirar su equipo.<br>
        Se requiere presentar este comprobante.<br>
        <?= esc($empresa_config['nombre_empresa']) ?>
    </div>

</body>

</html>