<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Orden #<?= esc($orden['codigo_orden']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
            transform: translateY(10px);
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        /* Estilos para badges personalizados si el helper devuelve HTML con clases bootstrap */
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-secondary {
            background-color: #e2e8f0;
            color: #475569;
        }

        .badge-info {
            background-color: #e0f2fe;
            color: #0284c7;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #d97706;
        }

        .badge-success {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .badge-black {
            background-color: #1e293b;
            color: #f8fafc;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .badge-primary {
            background-color: #dbeafe;
            color: #2563eb;
        }
    </style>
</head>

<body class="bg-gray-50 text-slate-800 min-h-screen flex flex-col items-center py-8 px-4">

    <div class="w-full max-w-xl mx-auto">

        <div class="text-center mb-10 fade-in-up">
            <div
                class="w-16 h-16 bg-white rounded-2xl shadow-sm mx-auto flex items-center justify-center mb-4 border border-gray-100">
                <i data-lucide="zap" class="w-8 h-8 text-slate-900"></i>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Seguimiento de Reparación</h1>
            <p class="text-sm text-slate-500 mt-1">Orden <span
                    class="font-mono font-medium text-slate-700">#<?= esc($orden['codigo_orden']) ?></span></p>
        </div>

        <div
            class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6 mb-8 fade-in-up delay-100 relative overflow-hidden">
            <div class="absolute top-0 left-0 h-1.5 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 transition-all duration-1000"
                style="width: <?= $progreso ?>%"></div>

            <div class="flex items-center justify-between mb-6 mt-2">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Estado Global</p>
                    <h2 class="text-xl font-bold text-slate-900 mt-1 capitalize flex items-center gap-2">
                        <?= get_nombre_estado_orden((int) $orden['estado']) ?>
                    </h2>
                </div>

                <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center">
                    <?php if ((int) $orden['estado'] === ESTADO_ORDEN_ENTREGADA): ?>
                        <i data-lucide="check-circle-2" class="w-6 h-6 text-green-600"></i>
                    <?php elseif ((int) $orden['estado'] === ESTADO_ORDEN_LISTA_RETIRO): ?>
                        <i data-lucide="package-check" class="w-6 h-6 text-blue-600"></i>
                    <?php elseif ((int) $orden['estado'] === ESTADO_ORDEN_CANCELADA): ?>
                        <i data-lucide="x-circle" class="w-6 h-6 text-red-500"></i>
                    <?php else: ?>
                        <i data-lucide="loader-2" class="w-6 h-6 text-indigo-600 animate-spin"></i>
                    <?php endif; ?>
                </div>
            </div>

            <?php
            $estadoActual = (int) $orden['estado'];
            // Definimos en qué paso visual estamos (1 al 4)
            $stepVisual = 1;
            if ($estadoActual >= ESTADO_ORDEN_EN_PROCESO)
                $stepVisual = 2;
            if ($estadoActual >= ESTADO_ORDEN_LISTA_RETIRO)
                $stepVisual = 3;
            if ($estadoActual == ESTADO_ORDEN_ENTREGADA)
                $stepVisual = 4;

            if ($estadoActual == ESTADO_ORDEN_CANCELADA)
                $stepVisual = 0; // Caso especial
            ?>

            <?php if ($estadoActual != ESTADO_ORDEN_CANCELADA): ?>
                <div class="relative flex items-center justify-between w-full mt-8 mb-2 px-2">
                    <div class="absolute top-1/2 left-0 w-full h-1 bg-gray-100 -z-10 rounded-full"></div>
                    <div class="absolute top-1/2 left-0 h-1 bg-slate-900 -z-10 rounded-full transition-all duration-1000"
                        style="width: <?= ($stepVisual - 1) * 33 ?>%"></div>

                    <?php
                    $labels = ['Recibido', 'Reparando', 'Listo', 'Entregado'];
                    foreach ($labels as $index => $label):
                        $stepNum = $index + 1;
                        $isActive = $stepVisual >= $stepNum;
                        ?>
                        <div class="flex flex-col items-center">
                            <div
                                class="w-4 h-4 rounded-full border-2 <?= $isActive ? 'bg-slate-900 border-slate-900 shadow-[0_0_0_4px_rgba(15,23,42,0.1)]' : 'bg-white border-gray-300' ?> transition-all duration-500">
                            </div>
                            <span
                                class="text-[10px] font-medium mt-2 <?= $isActive ? 'text-slate-900' : 'text-gray-400' ?>"><?= $label ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-red-50 text-red-700 p-3 rounded-lg text-sm text-center font-medium border border-red-100">
                    Esta orden ha sido cancelada.
                </div>
            <?php endif; ?>
        </div>

        <div class="space-y-6 fade-in-up delay-200">
            <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider ml-1">Detalle de Equipos</h3>

            <?php foreach ($dispositivos as $dev): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                    <div class="p-5 border-b border-gray-50">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-12 h-12 rounded-xl bg-slate-50 flex items-center justify-center shrink-0 text-2xl">
                                <?php if (!empty($dev['icono'])): ?>
                                    <i class="<?= $dev['icono'] ?> text-slate-700"></i>
                                <?php else: ?>
                                    <i data-lucide="smartphone" class="text-slate-700"></i>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-bold text-slate-900 text-lg leading-tight">
                                            <?= esc($dev['marca']) ?>     <?= esc($dev['modelo']) ?>
                                        </h4>
                                        <p
                                            class="text-xs text-slate-500 mt-1 bg-slate-100 inline-block px-2 py-0.5 rounded">
                                            <?= esc($dev['nombre_tipo'] ?? 'Dispositivo') ?>
                                        </p>
                                    </div>
                                    <div>
                                        <?= get_badge_estado_dispositivo((int) $dev['estado_reparacion']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-sm text-gray-600 bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <span class="font-semibold text-gray-900 text-xs uppercase block mb-1">Problema
                                Reportado:</span>
                            <?= esc($dev['problema_reportado']) ?>
                        </div>
                    </div>

                    <div class="p-5 bg-white">
                        <p class="text-xs font-bold text-slate-400 mb-4 flex items-center gap-1">
                            <i data-lucide="history" class="w-3 h-3"></i> HISTORIAL DE ACTIVIDAD
                        </p>

                        <?php if (!empty($dev['historial'])): ?>
                            <div class="relative pl-4 border-l-2 border-gray-100 space-y-6 ml-2">
                                <?php foreach ($dev['historial'] as $h): ?>
                                    <div class="relative">
                                        <div
                                            class="absolute -left-[21px] top-1 w-3 h-3 bg-white border-2 border-blue-500 rounded-full">
                                        </div>

                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start">
                                            <div>
                                                <p class="text-sm font-semibold text-slate-800">
                                                    <?= esc($h['estado_nuevo']) ?>
                                                </p>
                                                <?php if (!empty($h['comentario']) && $h['es_visible_cliente']): ?>
                                                    <p
                                                        class="text-sm text-gray-600 mt-1 bg-gray-50 p-2 rounded border border-gray-100 inline-block">
                                                        "<?= esc($h['comentario']) ?>"
                                                    </p>
                                                <?php endif; ?>

                                                <?php if ($h['estado_anterior']): ?>
                                                    <p class="text-[10px] text-gray-400 mt-1">
                                                        Cambio desde: <?= esc($h['estado_anterior']) ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-right mt-1 sm:mt-0">
                                                <span class="text-[10px] font-medium text-slate-400 block">
                                                    <?= date('d/m/Y', strtotime($h['created_at'])) ?>
                                                </span>
                                                <span class="text-[10px] text-slate-300">
                                                    <?= date('H:i A', strtotime($h['created_at'])) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4 text-gray-400 text-sm italic">
                                Sin movimientos registrados aún.
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-10 mb-6 text-center fade-in-up delay-300">
            <?php
            // Mensaje personalizado para WhatsApp
            $mensaje = "Hola, estoy consultando el estado de mi orden #{$orden['codigo_orden']}.";
            $telefonoEmpresa = '593999999999'; // Configura esto dinámicamente si puedes
            ?>
            <a href="https://wa.me/<?= $telefonoEmpresa ?>?text=<?= urlencode($mensaje) ?>" target="_blank"
                class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white rounded-full font-medium shadow-lg shadow-green-200 hover:bg-green-700 transition active:scale-95">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                Consultar por WhatsApp
            </a>

            <div class="mt-8 border-t border-gray-200 pt-6">
                <p class="text-xs text-gray-400">
                    Gracias por confiar en <strong><?= $nombre_empresa ?? 'Nuestro Servicio Técnico' ?></strong>
                </p>
                <p class="text-[10px] text-gray-300 mt-1">
                    La información mostrada se actualiza en tiempo real.
                </p>
            </div>
        </div>

    </div>

    <script>
        // Inicializar iconos
        lucide.createIcons();
    </script>
</body>

</html>