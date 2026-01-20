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
            /* REMPLAZAR EL PADDING PARA LOS MARGENES DE PDF */
            padding: 10px;
        }

        /* ENCABEZADO COMPACTO */
        .header {
            border: 2px solid #000;
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }

        .h-orden-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .h-orden-codigo {
            font-size: 16px;
            font-weight: bold;
            color: #c00;
            margin-bottom: 3px;
        }

        /* LAYOUT PRINCIPAL */
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
            border-right: 1px dashed #999;
            vertical-align: top;
        }

        .col-right {
            display: table-cell;
            width: 40%;
            padding-left: 10px;
            vertical-align: top;
        }

        /* SECCIÓN */
        .seccion {
            margin-bottom: 6px;
        }

        .sec-title {
            background: #333;
            color: white;
            padding: 2px 5px;
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 4px;
        }

        /* TABLA CLIENTE MEJORADA */
        .cliente-tabla {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        .cliente-tabla td {
            padding: 2px 4px;
            vertical-align: middle;
        }

        .cliente-tabla .label {
            font-weight: bold;
            width: 15%;
            white-space: nowrap;
        }

        .cliente-tabla .valor {
            /* border-bottom: 1px solid #000; */
            padding: 0 3px;
        }

        /* DISPOSITIVOS - TABLA COMPACTA */
        .dispositivos-tabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 8px;
        }

        .dispositivos-tabla th {
            background: #f0f0f0;
            border: 1px solid #999;
            padding: 2px 3px;
            font-weight: bold;
            text-align: left;
        }

        .dispositivos-tabla td {
            border: 1px solid #ccc;
            padding: 2px 3px;
            vertical-align: top;
        }

        /* TABLA DE CHECKS - 4 COLUMNAS */
        .tabla-checks {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            font-size: 7.5px;
        }

        .tabla-checks th {
            background: #e8e8e8;
            border: 1px solid #999;
            padding: 2px;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }

        .tabla-checks td {
            border: 1px solid #ccc;
            padding: 2px;
            vertical-align: middle;
            text-align: center;
        }

        .tabla-checks td.item-nombre {
            text-align: left;
            padding-left: 4px;
            font-weight: normal;
            width: 22%;
        }

        .tabla-checks td.check-col {
            width: 3%;
        }

        .tabla-header {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 7.5px;
        }

        .tabla-header th {
            /* background: #e8e8e8; */
            /* border: 1px solid #999; */
            padding: 0;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }

        .tabla-header td {
            /* border: 1px solid #ccc; */
            padding: 1px;
            vertical-align: middle;
            text-align: center;
        }


        .tabla-header td.item-header {
            text-align: left;
            padding-left: 4px;
            font-weight: normal;
            width: 33%;
        }

        .tabla-header td.item-header img {
            max-width: 100px;
            max-height: 100px;
        }

        .tabla-header td.item-center {
            font-size: medium;
            text-align: center;
            font-weight: bold;
            width: 33%;
        }

        .tabla-header td.check-header {
            width: 17%;
        }

        .tabla-header td.check-right {
            width: 17%;
            text-align: right;
        }

        /* Checkbox mejorado para PDF */
        .cb {
            width: 10px;
            height: 10px;
            border: 1.5px solid #000;
            display: inline-block;
            vertical-align: middle;
            /* Nuevas propiedades para centrar la X */
            text-align: center;
            line-height: 9px;
            font-size: 8px;
            font-weight: bold;
        }

        /* Equipo container */
        .equipo-box {
            border: 1px solid #ccc;
            padding: 4px;
            margin-bottom: 5px;
            background: #fafafa;
            page-break-inside: avoid;
        }

        .equipo-titulo {
            font-weight: bold;
            font-size: 8px;
            margin-bottom: 3px;
            color: #333;
        }

        .equipo-obs {
            margin-top: 3px;
            font-size: 7px;
        }

        .obs-linea {
            border-bottom: 1px solid #999;
            display: inline-block;
            width: 85%;
            padding-left: 3px;
        }

        /* TOTALES EN DOS COLUMNAS */
        .totales {
            border: 1px solid #000;
            padding: 5px;
            margin-top: 6px;
        }

        .totales-tabla {
            width: 100%;
            border-collapse: collapse;
        }

        .totales-tabla td {
            padding: 2px 3px;
            vertical-align: middle;
            font-size: 8.5px;
        }

        .totales-tabla .label-col {
            width: 23%;
            font-weight: normal;
        }

        .totales-tabla .valor-col {
            width: 27%;
            border-bottom: 1px solid #000;
            text-align: right;
            padding-right: 5px;
        }

        .totales-tabla .separador {
            width: 0%;
        }

        .totales-tabla tr.fila-total {
            border-top: 2px solid #000;
        }

        .totales-tabla tr.fila-total td {
            font-weight: bold;
            font-size: 9.5px;
            color: #c00;
            padding-top: 3px;
        }

        .totales-tabla tr.fila-saldo td {
            font-weight: bold;
            font-size: 9px;
        }

        /* TÉRMINOS */
        .terminos-title {
            background: #333;
            color: white;
            padding: 3px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 5px;
        }

        .terminos ol {
            padding: 0px 20px;
            font-size: 10px;
            line-height: 1.35;
            text-align: justify;
        }

        .terminos li {
            margin-bottom: 3px;
        }

        .nota-roja {
            margin-top: 6px;
            padding: 4px;
            background: #ffe0e0;
            border: 1px solid #c00;
            font-size: 7.5px;
            font-weight: bold;
            text-align: center;
            color: #c00;
        }

        /* FIRMA */
        .firma {
            margin-top: 15px;
            text-align: center;
        }

        .firma-linea {
            border-top: 2px solid #000;
            width: 75%;
            margin: 30px auto 0;
            padding-top: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        /* PIE */
        .footer {
            margin-top: 5px;
            font-size: 7px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>

<body>
    <!-- ENCABEZADO -->
    <div class="header">


        <table class="tabla-header">
            <thead>
                <tr>
                    <!-- <th style="width: 19%; text-align: left; padding-left: 4px;"></th>
                    <th style="width: 19%; text-align: left; padding-left: 4px;"></th>
                    <th style="width: 19%; text-align: left; padding-left: 4px;"></th>
                    <th style="width: 3%;">SÍ</th> -->
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="item-header">
                        <img src="https://static.vecteezy.com/system/resources/thumbnails/038/600/554/small_2x/adobe-photoshop-logos-adobe-icons-abstract-art-free-vector.jpg"
                            alt="Logo">
                    </td>

                    <td class="item-center">
                        <strong>
                            <?= strtoupper($nombre_empresa) ?>
                        </strong>
                        <div>
                            Dirección: <?= $direccion_empresa ?><br>
                            Email:
                            <?= $email_empresa ?><br>
                            Telf:
                            <?= $telefono_empresa ?>
                        </div>
                    </td>

                    <td class="check-header">
                        <div class="h-orden-title">ORDEN DE TRABAJO</div>
                        <div class="h-orden-codigo">
                            <?= $orden['codigo_orden'] ?>
                        </div>
                    </td>
                    <td class="check-right">
                        <div>
                            <img style="width: 100px; height: 100px;" src="<?= $qr_code ?>" alt="QR">
                            <div>Seguimiento</div>
                        </div>
                    </td>

                </tr>
            </tbody>
        </table>

        <!-- <div class="header-row">
            <div class="h-logo">
                <img src="" alt="Logo">
            </div>
            <div class="h-empresa">
                <strong><?= strtoupper($nombre_empresa) ?></strong>
                <div>
                    <?= $direccion_empresa ?><br>
                    Email: <?= $email_empresa ?><br>
                    Telf: <?= $telefono_empresa ?>
                </div>
            </div>
            <div class="h-orden">
                <div class="h-orden-title">ORDEN DE TRABAJO</div>
                <div class="h-orden-codigo"><?= $orden['codigo_orden'] ?></div>
                <div class="h-qr">
                    <img src="<?= $qr_code ?>" alt="QR">
                    <div class="h-qr-label">Seguimiento</div>
                </div>
            </div>
        </div> -->
    </div>

    <!-- CONTENIDO -->
    <div class="main">
        <div class="main-row">
            <!-- COLUMNA IZQUIERDA -->
            <div class="col-left">
                <!-- CLIENTE MEJORADO -->
                <div class="seccion">
                    <div class="sec-title">DATOS DEL CLIENTE</div>
                    <table class="cliente-tabla">
                        <tr>
                            <td class="label">Cliente:</td>
                            <td class="valor" style="width: 35%;"><?= $orden['nombres'] ?> <?= $orden['apellidos'] ?>
                            </td>
                            <td class="label" style="width: 15%;">Fecha de ingreso:</td>
                            <td class="valor" style="width: 35%;"><?=formatear_fecha($orden['created_at']) ?></td>
                        </tr>
                        <tr>
                            <td class="label">Cédula:</td>
                            <td class="valor" style="width: 35%;"><?= $orden['cedula'] ?></td>
                            <td class="label" style="width: 15%;">Teléfono:</td>
                            <td class="valor" style="width: 35%;"><?= $orden['telefono'] ?></td>
                        </tr>
                        <tr>
                            <td class="label">Email:</td>
                            <td class="valor"><?= $orden['email'] ?></td>
                            <!-- <td class="label">Fecha de entrega:</td>
                            <td class="valor"></td> -->
                        </tr>
                    </table>
                </div>

                <!-- DISPOSITIVOS -->
                <div class="seccion">
                    <div class="sec-title">DISPOSITIVOS</div>
                    <table class="dispositivos-tabla">
                        <thead>
                            <tr>
                                <th style="width: 8%;">#</th>
                                <th style="width: 15%;">Tipo</th>
                                <th style="width: 25%;">Marca/Modelo</th>
                                <th style="width: 20%;">IMEI/Serie</th>
                                <th style="width: 32%;">Problema Reportado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dispositivos)): ?>
                                <?php foreach ($dispositivos as $idx => $dev): ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td><?= $dev['tipo'] ?></td>
                                        <td><?= $dev['marca'] . ' ' . $dev['modelo'] ?></td>
                                        <td><?= $dev['serie_imei'] ?></td>
                                        <td><?= $dev['problema_reportado'] ?></td>
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

                <!-- CHECKLIST POR DISPOSITIVO - 4 COLUMNAS -->
                <div class="seccion">
                    <div class="sec-title">ESTADO DE LOS EQUIPOS</div>
                    <?php if (!empty($dispositivos)): ?>
                        <?php foreach ($dispositivos as $idx => $dev): ?>
                            <div class="equipo-box">
                                <div class="equipo-titulo">
                                    Equipo #<?= $idx + 1 ?>: <?= $dev['tipo'] ?> - <?= $dev['marca'] ?>         <?= $dev['modelo'] ?>
                                </div>
                                <table class="tabla-checks">
                                    <thead>
                                        <tr>
                                            <!-- <th colspan="3" style="width: 25%;">Item 1</th>
                                            <th colspan="3" style="width: 25%;">Item 2</th>
                                            <th colspan="3" style="width: 25%;">Item 3</th>
                                            <th colspan="3" style="width: 25%;">Item 4</th> -->
                                        </tr>
                                        <tr>
                                            <th style="width: 19%; text-align: left; padding-left: 4px;"></th>
                                            <th style="width: 3%;">SÍ</th>
                                            <th style="width: 3%;">NO</th>
                                            <th style="width: 19%; text-align: left; padding-left: 4px;"></th>
                                            <th style="width: 3%;">SÍ</th>
                                            <th style="width: 3%;">NO</th>
                                            <th style="width: 19%; text-align: left; padding-left: 4px;"></th>
                                            <th style="width: 3%;">SÍ</th>
                                            <th style="width: 3%;">NO</th>
                                            <th style="width: 19%; text-align: left; padding-left: 4px;"></th>
                                            <th style="width: 3%;">SÍ</th>
                                            <th style="width: 3%;">NO</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="item-nombre">Batería</td>
                                            <td class="check-col"><span class="cb"></span></td>
                                            <td class="check-col"><span class="cb"></span></td>

                                            <td class="item-nombre">Enciende</td>
                                            <td class="check-col"><span class="cb"></span></td>
                                            <td class="check-col"><span class="cb"></span></td>

                                            <td class="item-nombre">Cargador</td>
                                            <td class="check-col"><span class="cb"></span></td>
                                            <td class="check-col"><span class="cb"></span></td>

                                            <td class="item-nombre">Carcasa Rota</td>
                                            <td class="check-col"><span class="cb"></span></td>
                                            <td class="check-col"><span class="cb"></span></td>
                                        </tr>
                                        <tr>
                                            <td class="item-nombre">Riesgos</td>
                                            <td class="check-col"><span class="cb"></span></td>
                                            <td class="check-col"><span class="cb"></span></td>

                                            <td class="item-nombre">Equipo Abierto</td>
                                            <td class="check-col"><span class="cb"></span></td>
                                            <td class="check-col"><span class="cb"></span></td>

                                            <td class="item-nombre">Pantalla Rota</td>
                                            <td class="check-col"><span class="cb"></span></td>
                                            <td class="check-col"><span class="cb"></span></td>

                                            <td class="item-nombre">Accesorios</td>
                                            <td class="check-col"><span class="cb"></span></td>
                                            <td class="check-col"><span class="cb"></span></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="equipo-obs">
                                    <strong>Observaciones:</strong>
                                    <span class="obs-linea">&nbsp;</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 10px; font-style: italic;">
                            Sin dispositivos registrados
                        </div>
                    <?php endif; ?>
                </div>

                <!-- PRIORIDAD DE LA ORDEN -->
                <div class="seccion">
                    <div class="sec-title">PRIORIDAD DE LA ORDEN</div>
                    <table class="tabla-checks" style="margin-top: 4px;">
                        <tr>
                            <?php if (!empty($urgencias)): ?>
                                <?php foreach ($urgencias as $urg): ?>
                                    <?php
                                    $marcado = ($orden['urgencia_id'] == $urg['id']) ? 'X' : '&nbsp;';
                                    $costo = $urg['recargo'] > 0 ? '(+$' . number_format($urg['recargo'], 2) . ')' : '($0.00)';
                                    ?>

                                    <td class="item-nombre" style="width: auto; white-space: nowrap;">
                                        <?= esc($urg['nombre']) ?>         <?= $costo ?>
                                    </td>

                                    <td class="check-col">
                                        <span class="cb"><?= $marcado ?></span>
                                    </td>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <td>No hay urgencias configuradas</td>
                            <?php endif; ?>
                        </tr>
                    </table>
                </div>

                <!-- TOTALES EN DOS COLUMNAS -->
                <div class="totales">
                    <table class="totales-tabla">
                        <tr>
                            <td class="label-col">Revisión:</td>
                            <td class="valor-col">$ <?= number_format($orden['valor_revision'] ?? 0, 2) ?></td>
                            <td class="separador"></td>
                            <td class="label-col">Mano de Obra:</td>
                            <td class="valor-col">$ <?= number_format($orden['mano_obra'], 2) ?></td>
                        </tr>
                        <tr>
                            <td class="label-col">Mano de O. Estimada:</td>
                            <td class="valor-col">$ <?= number_format($orden['mano_obra_aproximado'] ?? 0, 2) ?></td>
                            <td class="separador"></td>
                            <td class="label-col">Repuestos:</td>
                            <td class="valor-col">$ <?= number_format($orden['valor_repuestos'], 2) ?></td>
                        </tr>
                        <tr>
                            <td class="label-col">Rep. Estimados:</td>
                            <td class="valor-col">$ <?= number_format($orden['repuestos_aproximado'] ?? 0, 2) ?></td>
                            <td class="separador"></td>
                            <td class="label-col">Cargo Urgencia:</td>
                            <td class="valor-col">$ <?= number_format($urgencia['cargo_prioridad'] ?? 0, 2) ?></td>
                        </tr>
                        <tr class="fila-total">
                            <td colspan="2"></td>
                            <td class="separador"></td>
                            <td class="label-col">TOTAL:</td>
                            <td class="valor-col">$
                                <?= number_format(($orden['mano_obra'] + $orden['valor_repuestos'] + ($orden['cargo_prioridad'] ?? 0)), 2) ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="5" style="height: 5px;"></td>
                        </tr>
                        <tr>
                            <td class="label-col">Abono:</td>
                            <td class="valor-col">$ <?= number_format($orden['abono'] ?? 0, 2) ?></td>
                            <td class="separador"></td>
                            <td class="label-col" style="font-weight: bold;">Saldo Pendiente:</td>
                            <td class="valor-col" style="font-weight: bold;">$
                                <?= number_format((($orden['mano_obra'] + $orden['valor_repuestos'] + ($orden['cargo_prioridad'] ?? 0)) - ($orden['abono'] ?? 0)), 2) ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="footer">
                    Generado: <?= date('d/m/Y H:i:s') ?>
                </div>
            </div>

            <!-- COLUMNA DERECHA -->
            <div class="col-right">
                <!-- INFORMACIÓN DE PRIORIDADES -->
                <div style="border: 1px solid #333; padding: 5px; margin-bottom: 8px; background: #f9f9f9;">
                    <div
                        style="background: #555; color: white; padding: 2px 5px; font-weight: bold; font-size: 9px; margin-bottom: 4px; text-align: center;">
                        INFORMACIÓN DE PRIORIDADES
                    </div>
                    <div style="font-size: 8px; line-height: 1.4;">
                        <p style="margin-bottom: 4px; text-align: justify;">
                            <strong>El cliente puede seleccionar la prioridad de su orden según sus necesidades de
                                tiempo.</strong>
                            Los tiempos de entrega y cargos adicionales son los siguientes:
                        </p>

                        <table style="width: 100%; border-collapse: collapse; margin-top: 4px; font-size: 7.5px;">
                            <tr style="background: #e8e8e8;">
                                <th style="border: 1px solid #999; padding: 3px; text-align: left;">Prioridad</th>
                                <th style="border: 1px solid #999; padding: 3px; text-align: center;">Tiempo</th>
                                <th style="border: 1px solid #999; padding: 3px; text-align: center;">Cargo</th>
                            </tr>

                            <?php foreach ($urgencias as $urg): ?>
                                <tr>
                                    <td style="border: 1px solid #ccc; padding: 3px;">
                                        <strong><?= strtoupper($urg['nombre']) ?></strong>
                                    </td>
                                    <td style="border: 1px solid #ccc; padding: 3px; text-align: center;">
                                        <?= esc($urg['tiempo_espera'] ?? "0 Días") ?>
                                    </td>
                                    <td
                                        style="border: 1px solid #ccc; padding: 3px; text-align: center; font-weight: bold;">
                                        <?php if ($urg['recargo'] > 0): ?>
                                            <span style="color: #c00;">+$<?= number_format($urg['recargo'], 2) ?></span>
                                        <?php else: ?>
                                            $0.00
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        </table>
                        <p style="margin-top: 4px; font-size: 7px; font-style: italic; color: #666;">
                            * Los tiempos son aproximados y pueden variar según disponibilidad.
                        </p>
                    </div>
                </div>
                <div class="terminos">
                    <div class="terminos-title">TÉRMINOS Y CONDICIONES</div>
                    <ol>
                        <li>La empresa considerará en abandono todo equipo luego de 30 días de su ingreso, no haciéndose
                            responsable por pérdida o extravío.</li>
                        <li>Garantía de 15 días continuos desde la entrega, solo por el motivo de reparación original.
                        </li>
                        <li>El equipo solo será retirado por el titular de esta orden. Caso contrario, se requiere
                            autorización firmada y copia de cédula del titular.</li>
                        <li>La garantía aplica solo si la etiqueta ANTI-FRAUDE no ha sido removida ni el equipo revisado
                            por terceros.</li>
                        <li>El costo de revisión se exonera si se ordena la reparación. De lo contrario, debe cancelarse
                            para retirar el equipo.</li>
                        <li>Garantía NO aplica a daños por humedad, fallas eléctricas o golpes.</li>
                        <li>La empresa no se responsabiliza por fallas distintas al motivo de reparación.</li>
                        <li>DISPLAYS Y TÁCTILES NO TIENEN GARANTÍA. Deben probarse en el local antes de retirarse.</li>
                        <li>Casos de garantía cubiertos en 15 días hábiles. NO HAY GARANTÍA para equipos recibidos
                            apagados o mojados.</li>
                        <li>El cliente acepta estos términos y autoriza revisar/reparar el equipo al firmar.</li>
                    </ol>
                    <div class="nota-roja">
                        ⚠ TODO EQUIPO PASADOS 30 DÍAS DE INGRESO Y REPARACIÓN SERÁ SUBASTADO
                    </div>
                </div>
                <div class="firma">
                    <div class="firma-linea">
                        FIRMA DE CONFORMIDAD<br>
                        CI: <?= $orden['cedula'] ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>