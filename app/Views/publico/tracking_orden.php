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
    </style>
</head>

<body class="bg-gray-50 text-slate-800 min-h-screen flex flex-col items-center py-8 px-4">

    <div class="w-full max-w-lg mx-auto">

        <div class="text-center mb-10 fade-in-up">
            <div
                class="w-16 h-16 bg-white rounded-2xl shadow-sm mx-auto flex items-center justify-center mb-4 border border-gray-100">
                <i data-lucide="zap" class="w-8 h-8 text-slate-900"></i>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Estado de Reparación</h1>
            <p class="text-sm text-slate-500 mt-1">Orden <span
                    class="font-mono font-medium text-slate-700">#<?= esc($orden['codigo_orden']) ?></span></p>
        </div>

        <div
            class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6 mb-8 fade-in-up delay-100 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500">
            </div>

            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Estado Actual</p>
                    <h2 class="text-xl font-bold text-slate-900 mt-1 capitalize">
                        <?= str_replace('_', ' ', $orden['estado']) ?>
                    </h2>
                </div>
                <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                    <?php if ($orden['estado'] == 'entregado' || $orden['estado'] == 'listo_para_retiro'): ?>
                        <i data-lucide="check-circle-2" class="w-6 h-6 text-green-600"></i>
                    <?php else: ?>
                        <i data-lucide="loader-2" class="w-6 h-6 animate-spin"></i>
                    <?php endif; ?>
                </div>
            </div>

            <?php
            $steps = [
                'recibida' => 1,
                'en_diagnostico' => 2,
                'en_reparacion' => 3,
                'listo_para_retiro' => 4
            ];
            $currentStep = 1;
            if (in_array($orden['estado'], ['pendiente_aprobacion', 'esperando_repuesto']))
                $currentStep = 2;
            if (in_array($orden['estado'], ['pruebas_calidad']))
                $currentStep = 3;
            if (in_array($orden['estado'], ['entregado']))
                $currentStep = 5;
            if (isset($steps[$orden['estado']]))
                $currentStep = $steps[$orden['estado']];
            ?>

            <div class="relative flex items-center justify-between w-full mt-4 mb-2">
                <div class="absolute top-1/2 left-0 w-full h-1 bg-gray-100 -z-10 rounded-full"></div>
                <div class="absolute top-1/2 left-0 h-1 bg-slate-900 -z-10 rounded-full transition-all duration-1000"
                    style="width: <?= ($currentStep - 1) * 33 ?>%"></div>

                <?php foreach (['Recibido', 'Revisión', 'Reparando', 'Listo'] as $index => $label): ?>
                    <?php $stepNum = $index + 1; ?>
                    <div class="flex flex-col items-center">
                        <div
                            class="w-4 h-4 rounded-full border-2 <?= $currentStep >= $stepNum ? 'bg-slate-900 border-slate-900' : 'bg-white border-gray-300' ?> transition-colors duration-500">
                        </div>
                        <span
                            class="text-[10px] font-medium mt-2 <?= $currentStep >= $stepNum ? 'text-slate-900' : 'text-gray-400' ?>"><?= $label ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-6 p-3 bg-slate-50 rounded-xl border border-slate-100 text-center">
                <p class="text-sm text-slate-600">
                    <?php if ($currentStep == 1): ?>
                        Tu equipo está ingresado y esperando asignación técnica.
                    <?php elseif ($currentStep == 2): ?>
                        Estamos identificando la falla exacta y cotizando repuestos.
                    <?php elseif ($currentStep == 3): ?>
                        El técnico está trabajando en tu equipo en este momento.
                    <?php elseif ($currentStep >= 4): ?>
                        ¡Todo listo! Puedes pasar a recoger tu equipo.
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <div class="space-y-4 fade-in-up delay-200">
            <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider ml-1">Tus Dispositivos</h3>

            <?php foreach ($dispositivos as $dev): ?>
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col gap-4">

                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center shrink-0">
                            <?php
                            $icono = match ($dev['tipo']) {
                                'celular' => 'smartphone',
                                'tablet' => 'tablet',
                                'laptop' => 'laptop',
                                default => 'cpu'
                            };
                            ?>
                            <i data-lucide="<?= $icono ?>" class="text-slate-700 w-6 h-6"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-900 text-lg leading-tight"><?= esc($dev['modelo']) ?></h4>
                            <p class="text-sm text-slate-500"><?= esc($dev['marca']) ?></p>
                        </div>
                    </div>

                    <div class="h-px w-full bg-gray-50"></div>

                    <div>
                        <p class="text-xs font-bold text-slate-400 mb-2 flex items-center gap-1">
                            <i data-lucide="message-square" class="w-3 h-3"></i> INFORME TÉCNICO
                        </p>
                        <?php if (!empty($dev['estado_reparacion'])): ?>
                            <div
                                class="bg-blue-50/50 p-3 rounded-tr-2xl rounded-br-2xl rounded-bl-2xl border-l-2 border-blue-500 text-sm text-slate-700 leading-relaxed">
                                <?= esc($dev['estado_reparacion']) ?>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-400 italic">Esperando actualización del técnico...</p>
                        <?php endif; ?>
                    </div>

                    <div class="text-xs text-gray-400 mt-1">
                        <span class="font-medium text-gray-500">Motivo de ingreso:</span>
                        <?= esc($dev['problema_reportado']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-10 mb-6 text-center fade-in-up delay-300">
            <a href="https://wa.me/593999999999?text=Hola,%20consulto%20por%20la%20orden%20<?= $orden['codigo_orden'] ?>"
                class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 text-white rounded-full font-medium shadow-lg shadow-slate-200 hover:bg-slate-800 transition active:scale-95">
                <i data-lucide="message-circle" class="w-4 h-4"></i>
                Contactar por WhatsApp
            </a>
            <p class="mt-6 text-xs text-gray-300">
                <?= $nombre_empresa ?? 'Soporte Técnico' ?> &copy; <?= date('Y') ?>
            </p>
        </div>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>