<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <title>Orden de Trabajo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #000;
            padding: 10px;
        }

        /* ============ ENCABEZADO ============ */
        .header {
            border: 2px solid #000;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 8px;
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            width: 60%;
            padding-right: 10px;
            vertical-align: middle;
        }

        .header-right {
            display: table-cell;
            width: 40%;
            padding-left: 10px;
            border-left: 1px solid #ddd;
            vertical-align: middle;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .h-logo {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
            text-align: center;
        }

        .logo {
            max-width: 70px;
            max-height: 70px;
        }

        .h-empresa {
            display: table-cell;
            vertical-align: middle;
            padding-left: 10px;
        }

        .empresa-nombre {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .empresa-info {
            font-size: 7.5px;
            line-height: 1.4;
        }

        .header-right-content {
            text-align: center;
        }

        .orden-codigo {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .orden-fecha {
            font-size: 7.5px;
            color: #666;
            margin-bottom: 5px;
        }

        .qr-code {
            width: 55px;
            height: 55px;
        }

        .qr-label {
            font-size: 6.5px;
            color: #666;
            margin-top: 2px;
        }

        /* ============ LAYOUT PRINCIPAL ============ */
        .main {
            display: table;
            width: 100%;
        }

        .main-row {
            display: table-row;
        }

        .col-left {
            display: table-cell;
            width: 60%;
            padding-right: 10px;
            border-right: 1px solid #ddd;
            vertical-align: top;
        }

        .col-right {
            display: table-cell;
            width: 40%;
            padding-left: 10px;
            vertical-align: top;
        }

        /* ============ SECCIONES ============ */
        .seccion {
            margin-bottom: 7px;
        }

        .sec-title {
            background: #000;
            color: white;
            padding: 2px 6px;
            font-weight: bold;
            font-size: 8px;
            margin-bottom: 0;
            border-radius: 2px;
        }

        /* ============ GRID 2 COLUMNAS (col-6 col-6) ============ */
        .grid-2col {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .grid-row {
            display: table-row;
        }

        .col-6 {
            display: table-cell;
            width: 50%;
            padding: 1px 3px;
            font-size: 7.5px;
            vertical-align: middle;
            background: #fafafa;
            border-bottom: 1px dotted #e0e0e0;
        }

        .col-6 b {
            color: #333;
            margin-right: 4px;
        }

        /* ============ TABLA DISPOSITIVOS ============ */
        .dispositivos-tabla {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px;
            margin-top: 1px;
        }

        .dispositivos-tabla th {
            background: #f0f0f0;
            border: 1px solid #ccc;
            padding: 2px 4px;
            font-weight: bold;
            text-align: left;
        }

        .dispositivos-tabla td {
            border: 1px solid #e0e0e0;
            padding: 2px 4px;
            vertical-align: top;
        }

        /* ============ DETALLES EQUIPOS ============ */
        .equipo-box {
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 5px;
            margin-bottom: 5px;
            background: #fafafa;
        }

        .equipo-titulo {
            font-weight: bold;
            font-size: 7.5px;
            margin-bottom: 3px;
            padding-bottom: 2px;
            border-bottom: 1px dotted #ddd;
        }

        .equipo-grid {
            display: table;
            width: 100%;
        }

        .equipo-col {
            display: table-cell;
            vertical-align: top;
            font-size: 7px;
            line-height: 1.5;
        }

        .equipo-izq {
            width: 50%;
            padding-right: 5px;
        }

        .equipo-der {
            width: 50%;
            padding-left: 5px;
            border-left: 1px dotted #ddd;
        }

        .equipo-label {
            font-weight: bold;
            font-size: 7px;
            margin-bottom: 1px;
        }

        /* ============ PRIORIDAD ============ */
        .prioridad-grid {
            display: table;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 3px;
            font-size: 7px;
            margin-top: 1px;
        }

        .prior-item {
            display: table-cell;
            padding: 4px;
            text-align: center;
            border-right: 1px solid #e0e0e0;
        }

        .prior-item:last-child {
            border-right: none;
        }

        .prior-nombre {
            font-weight: bold;
            display: block;
            margin-bottom: 1px;
        }

        .prior-tiempo {
            font-size: 6.5px;
            color: #666;
            display: block;
        }

        .prior-costo {
            font-size: 6.5px;
            display: block;
            margin-bottom: 2px;
        }

        .cb {
            width: 10px;
            height: 10px;
            border: 1.5px solid #333;
            border-radius: 2px;
            display: inline-block;
            text-align: center;
            line-height: 9px;
            font-size: 7px;
            font-weight: bold;
        }

        /* ============ TOTALES CON GRID 2 COLUMNAS ============ */
        .totales {
            border: 2px solid #333;
            border-radius: 4px;
            padding: 5px;
            background: #f8f8f8;
        }

        .precio-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }

        .precio-row {
            display: table-row;
        }

        .precio-col {
            display: table-cell;
            width: 50%;
            padding: 1px 3px;
            font-size: 7.5px;
            vertical-align: middle;
        }

        .precio-label {
            color: #555;
            display: inline-block;
            width: 55%;
        }

        .precio-valor {
            font-weight: bold;
            display: inline-block;
            width: 44%;
            text-align: right;
        }

        .estimado {
            color: #999;
            font-size: 7px;
        }

        .separador-precios {
            height: 1px;
            background: #333;
            margin: 4px 0;
        }

        .total-destaque {
            background: #333;
            color: white;
            padding: 3px 5px;
            margin: 3px -5px;
            border-radius: 2px;
            font-weight: bold;
            font-size: 9px;
            text-align: center;
        }

        .saldo-row {
            padding-top: 3px;
            border-top: 1px solid #ccc;
            margin-top: 3px;
        }

        .saldo-col {
            font-weight: bold;
            font-size: 8px;
        }

        /* ============ TABLA INFO ============ */
        .tabla-simple {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            margin-top: 2px;
        }

        .tabla-simple th {
            background: #f0f0f0;
            border: 1px solid #ccc;
            padding: 2px 3px;
            text-align: left;
        }

        .tabla-simple td {
            border: 1px solid #e0e0e0;
            padding: 2px 3px;
        }

        /* ============ TERMINOS ============ */
        .terminos-titulo {
            background: #333;
            color: white;
            padding: 2px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 8px;
            margin-bottom: 3px;
            border-radius: 2px;
        }

        .terminos ol {
            padding: 0 16px;
            font-size: 7px;
            line-height: 1.5;
        }

        .terminos li {
            margin-bottom: 2px;
        }

        .alerta-final {
            margin-top: 5px;
            padding: 4px;
            border: 2px solid #333;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-align: center;
        }

        /* ============ FIRMA ============ */
        .firma-area {
            margin-top: 12px;
            text-align: center;
        }

        .firma-linea {
            border-top: 2px solid #333;
            width: 70%;
            margin: 20px auto 0;
            padding-top: 3px;
            font-size: 7px;
            font-weight: bold;
        }

        /* ============ FOOTER ============ */
        .footer {
            margin-top: 5px;
            font-size: 6.5px;
            color: #999;
        }

        /* ============ CAJA INFO ============ */
        .caja-info {
            border: 1px solid #ccc;
            border-radius: 3px;
            padding: 5px;
            margin-bottom: 7px;
            background: #fafafa;
        }

        .caja-titulo {
            background: #555;
            color: white;
            padding: 2px 5px;
            font-weight: bold;
            font-size: 7.5px;
            margin: -5px -5px 3px -5px;
            text-align: center;
            border-radius: 2px 2px 0 0;
        }

        .caja-texto {
            font-size: 7px;
            line-height: 1.4;
            margin-bottom: 3px;
        }
    </style>
</head>

<body>
    <!-- ENCABEZADO -->
    <div class="header">
        <div class="header-left">
            <div class="header-content">
                <div class="h-logo">
                    <img src="https://static.vecteezy.com/system/resources/thumbnails/038/600/554/small_2x/adobe-photoshop-logos-adobe-icons-abstract-art-free-vector.jpg"
                        alt="Logo" class="logo">
                </div>
                <div class="h-empresa">
                    <div class="empresa-nombre">
                        <?= strtoupper($nombre_empresa) ?>
                    </div>
                    <div class="empresa-info">
                        <?= $direccion_empresa ?? '7 de Mayo y Olmedo' ?><br>
                        Tel: <?= $telefono_empresa ?? '099384789' ?><br>
                        Email: <?= $email_empresa ?? 'softec@gmail.com' ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="header-right">
            <div class="header-right-content">
                <div class="orden-codigo">
                    <?= $orden['codigo_orden'] ?>
                </div>
                <div class="orden-fecha"><?= formatear_fecha($orden['created_at']) ?></div>
                <img src="<?= $qr_code ?>" alt="QR" class="qr-code">
                <div class="qr-label">Seguimiento</div>
            </div>
        </div>
    </div>

    <!-- CONTENIDO -->
    <div class="main">
        <div class="main-row">
            <!-- IZQUIERDA -->
            <div class="col-left">
                <!-- CLIENTE CON GRID 2 COLUMNAS -->
                <div class="seccion">
                    <div class="sec-title">DATOS DEL CLIENTE</div>
                    <div class="grid-2col">
                        <div class="grid-row">
                            <div class="col-6"><b>Cliente:</b> <?= $orden['nombres'] ?> <?= $orden['apellidos'] ?>
                            </div>
                            <div class="col-6"><b>Cédula:</b> <?= $orden['cedula'] ?></div>
                        </div>
                        <div class="grid-row">
                            <div class="col-6"><b>Teléfono:</b> <?= $orden['telefono'] ?></div>
                            <div class="col-6"><b>Email:</b> <?= $orden['email'] ?></div>
                        </div>
                    </div>
                </div>

                <!-- DISPOSITIVOS -->
                <div class="seccion">
                    <div class="sec-title">DISPOSITIVOS RECIBIDOS</div>
                    <table class="dispositivos-tabla">
                        <thead>
                            <tr>
                                <th style="width: 4%;">#</th>
                                <th style="width: 14%;">Tipo</th>
                                <th style="width: 32%;">Marca/Modelo</th>
                                <th style="width: 26%;">IMEI O Serie</th>
                                <!-- <th style="width: 24%;">Accesorios</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dispositivos)): ?>
                                <?php foreach ($dispositivos as $idx => $dev): ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td><?= $dev['nombre_tipo'] ?></td>
                                        <td><?= $dev['marca'] . ' ' . $dev['modelo'] ?></td>
                                        <td><?= $dev['serie_imei'] ?></td>
                                        <!-- <td>Bateria, SIM, SD</td> -->
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center;">Sin dispositivos</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- DETALLES -->
                <div class="seccion">
                    <div class="sec-title">DETALLES DE LOS EQUIPOS</div>
                    <?php if (!empty($dispositivos)): ?>
                        <?php foreach ($dispositivos as $idx => $dev): ?>

                            <div class="equipo-box">
                                <div class="equipo-titulo">Equipo #
                                    <?= $idx + 1 ?>:
                                    <?= $dev['nombre_tipo'] ?> -
                                    <?= $dev['marca'] ?>
                                    <?= $dev['modelo'] ?>
                                </div>
                                <div class="equipo-grid">
                                    <div class="equipo-col equipo-izq">
                                        <div class="equipo-label">Observaciones Fisicas:</div>
                                        <?= $dev['observaciones'] ?>
                                    </div>
                                    <div class="equipo-col equipo-der">
                                        <div class="equipo-label">Problema Reportado:</div>
                                        <?= $dev['problema_reportado'] ?>
                                    </div>
                                </div>
                            </div>


                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="font-size: 7.5px; color: #666; text-align: center; padding: 5px;">
                            No hay detalles de equipos disponibles.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- PRIORIDAD -->
                <div class="seccion">
                    <div class="sec-title">PRIORIDAD DE SERVICIO</div>
                    <div class="prioridad-grid">
                        <?php if (!empty($urgencias)): ?>
                            <?php foreach ($urgencias as $urg): ?>
                                <?php
                                $marcado = ($orden['urgencia_id'] == $urg['id']) ? 'X' : '&nbsp;';
                                $costo_txt = $urg['recargo'] > 0 ? '+$' . number_format($urg['recargo'], 2) : '$0.00';
                                ?>
                                <div class="prior-item">
                                    <span class="prior-nombre"><?= strtoupper($urg['nombre']) ?></span>
                                    <span
                                        class="prior-tiempo"><?= isset($urg['tiempo_espera']) ? $urg['tiempo_espera'] : '-' ?></span>
                                    <span class="prior-costo"><?= $costo_txt ?></span>
                                    <span class="cb"><?= $marcado ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- TOTALES CON GRID 2 COLUMNAS -->
                <div class="totales">
                    <div class="precio-grid">
                        <div class="precio-row">
                            <div class="precio-col">
                                <span class="precio-label">Revision:</span>
                                <span class="precio-valor">$
                                    <?= number_format($orden['valor_revision'] ?? 0, 2) ?></span>
                            </div>
                            <div class="precio-col">
                                <span class="precio-label">Mano de Obra:</span>
                                <span class="precio-valor">$ <?= number_format($orden['mano_obra'], 2) ?></span>
                            </div>
                        </div>
                        <div class="precio-row">
                            <div class="precio-col">
                                <span class="precio-label estimado">M.O.
                                    Estimado:</span>
                                <span class="precio-valor estimado">~$
                                    <?= number_format($orden['mano_obra_aproximado'] ?? 0, 2) ?></span>
                            </div>
                            <div class="precio-col">
                                <span class="precio-label">Repuestos:</span>
                                <span class="precio-valor">$ <?= number_format($orden['valor_repuestos'], 2) ?></span>
                            </div>
                        </div>
                        <div class="precio-row">
                            <div class="precio-col">
                                <span class="precio-label estimado">Rep.
                                    Estimados:</span>
                                <span class="precio-valor estimado">~$
                                    <?= number_format($orden['repuestos_aproximado'] ?? 0, 2) ?></span>
                            </div>
                            <div class="precio-col">
                                <span class="precio-label">Cargo Urgencia:</span>
                                <span class="precio-valor">$
                                    <?= number_format($orden['cargo_prioridad'] ?? 0, 2) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="separador-precios"></div>
                    <?php
                    $gran_total = $orden['mano_obra'] + $orden['valor_repuestos'] + ($orden['cargo_prioridad'] ?? 0);
                    $saldo = $gran_total - ($orden['abono'] ?? 0);
                    ?>
                    <div class="total-destaque">TOTAL ESTIMADO: $ <?= number_format($gran_total, 2) ?></div>
                    <div class="precio-grid saldo-row">
                        <div class="precio-row">
                            <div class="precio-col saldo-col">
                                <span class="precio-label">Abono Inicial:</span>
                                <span class="precio-valor">$ <?= number_format($orden['abono'] ?? 0, 2) ?></span>
                            </div>
                            <div class="precio-col saldo-col">
                                <span class="precio-label">SALDO PENDIENTE:</span>
                                <span class="precio-valor">$ <?= number_format($saldo, 2) ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="footer">Generado: <?= date('d/m/Y H:i:s') ?></div>
            </div>

            <!-- DERECHA -->
            <div class="col-right">
                <!-- INFO -->
                <div class="caja-info">
                    <div class="caja-titulo">INFORMACIÓN DE PRIORIDADES</div>
                    <div class="caja-texto">
                        Tiempos aproximados desde aceptación del presupuesto:
                    </div>
                    <table class="tabla-simple">
                        <tr>
                            <th>Prioridad</th>
                            <th style="text-align: center;">Tiempo</th>
                            <th style="text-align: center;">Cargo</th>
                        </tr>
                        <?php if (!empty($urgencias)): ?>
                            <?php foreach ($urgencias as $urg): ?>
                                <tr>
                                    <td><?= esc($urg['nombre']) ?></td>
                                    <td style="text-align: center;"><?= esc($urg['tiempo_espera'] ?? "-") ?></td>
                                    <td style="text-align: center;">
                                        <?= ($urg['recargo'] > 0) ? '+$' . number_format($urg['recargo'], 2) : '$0.00' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </table>
                </div>

                <!-- TERMINOS -->
                <div class="terminos">
                    <div class="terminos-titulo">TÉRMINOS Y CONDICIONES</div>
                    <ol>
                        <?php if (!empty($terminos)): ?>
                            <?php foreach ($terminos as $termino): ?>
                                <li><?= esc($termino['contenido']) ?></li>
                            <?php endforeach; ?>
                        <?php endif; ?>

                    </ol>
                    <div class="alerta-final">
                        TODO EQUIPO NO RETIRADO EN 30 DÍAS SERA SUBASTADO
                    </div>
                </div>

                <!-- FIRMA -->
                <div class="firma-area">
                    <div class="firma-linea">
                        FIRMA DE CONFORMIDAD<br>
                        CI: <?= $orden['cedula'] ?><br>
                        <?= $orden['nombres'] ?> <?= $orden['apellidos'] ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>