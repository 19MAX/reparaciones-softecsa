<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Órdenes de Servicio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body class="min-h-screen flex flex-col">

    <div class="bg-slate-900 pb-20 pt-10 px-4 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-10">
            <div class="absolute -top-20 -right-20 w-96 h-96 bg-blue-500 rounded-full blur-3xl"></div>
            <div class="absolute top-20 -left-20 w-72 h-72 bg-purple-500 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-3xl mx-auto relative z-10 text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Portal de Clientes</h1>
            <p class="text-slate-400 mb-8 text-sm md:text-base">Consulta el historial y estado de tus reparaciones
                ingresando tu cédula.</p>

            <form action="<?= base_url('consulta/mis-ordenes') ?>" method="get" class="relative max-w-lg mx-auto">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="search" class="text-slate-400 w-5 h-5"></i>
                    </div>
                    <input type="text" name="cedula" value="<?= esc($cedula_buscada) ?>"
                        placeholder="Ingresa tu número de Cédula"
                        class="block w-full pl-11 pr-4 py-4 bg-white/10 border border-slate-700 rounded-full text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white/20 transition backdrop-blur-sm shadow-lg"
                        required>
                    <button type="submit"
                        class="absolute right-2 top-2 bottom-2 bg-blue-600 hover:bg-blue-500 text-white px-6 rounded-full font-medium transition shadow-md text-sm">
                        Consultar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="flex-grow -mt-10 px-4 pb-10 relative z-20">
        <div class="max-w-4xl mx-auto">

            <?php if (isset($cliente)): ?>
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-slate-800">
                        Hola, <span class="text-blue-600"><?= esc($cliente['nombres']) ?></span>
                    </h2>
                    <span class="text-sm text-slate-500 bg-white px-3 py-1 rounded-full shadow-sm">
                        <?= count($ordenes) ?> Orden(es) encontrada(s)
                    </span>
                </div>
            <?php endif; ?>

            <?php if (!empty($ordenes)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($ordenes as $orden): ?>
                        <div
                            class="glass-card rounded-2xl p-6 shadow-lg hover:shadow-xl transition duration-300 group border border-gray-100 bg-white">

                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <span
                                        class="inline-block px-2 py-1 bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-wider rounded">
                                        <?= esc($orden['codigo_orden']) ?>
                                    </span>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <?= date('d M Y, h:i A', strtotime($orden['created_at'])) ?>
                                    </p>
                                </div>
                                <?php
                                // Colores para el estado
                                $estadoConfig = match ($orden['estado']) {
                                    'recibida', 'en_diagnostico' => ['bg-gray-100', 'text-gray-600'],
                                    'en_reparacion', 'pruebas_calidad' => ['bg-blue-100', 'text-blue-700'],
                                    'pendiente_aprobacion', 'esperando_repuesto' => ['bg-yellow-100', 'text-yellow-700'],
                                    'listo_para_retiro', 'entregado' => ['bg-green-100', 'text-green-700'],
                                    default => ['bg-red-100', 'text-red-700']
                                };
                                ?>
                                <span
                                    class="px-3 py-1 rounded-full text-xs font-bold <?= $estadoConfig[0] ?> <?= $estadoConfig[1] ?> border border-opacity-20 uppercase">
                                    <?= str_replace('_', ' ', $orden['estado']) ?>
                                </span>
                            </div>

                            <div class="space-y-3 mb-6">
                                <?php if (!empty($orden['dispositivos'])): ?>
                                    <?php foreach ($orden['dispositivos'] as $dev): ?>
                                        <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-100">
                                            <div class="p-2 bg-white rounded-md shadow-sm mr-3 text-slate-600">
                                                <?php
                                                $icono = match ($dev['tipo']) {
                                                    'celular' => 'smartphone',
                                                    'tablet' => 'tablet',
                                                    'laptop' => 'laptop',
                                                    default => 'cpu'
                                                };
                                                ?>
                                                <i data-lucide="<?= $icono ?>" class="w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <p class="font-bold text-sm text-gray-800"><?= esc($dev['marca']) ?>
                                                    <?= esc($dev['modelo']) ?></p>
                                                <p class="text-xs text-gray-500 truncate w-40"><?= esc($dev['problema_reportado']) ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <div class="pt-4 border-t border-gray-100 flex justify-between items-center">
                                <div class="text-right">
                                </div>
                                <a href="<?= base_url('consulta/orden/' . $orden['codigo_orden']) ?>"
                                    class="w-full text-center bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 hover:text-blue-600 hover:border-blue-200 font-medium py-2 rounded-lg text-sm transition flex items-center justify-center gap-2 group-hover:border-blue-300">
                                    Ver Detalles y Progreso <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>

            <?php elseif ($cedula_buscada): ?>
                <div class="text-center bg-white rounded-2xl p-10 shadow-lg border border-gray-100 mt-6">
                    <div class="bg-gray-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="search-x" class="w-10 h-10 text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800">No encontramos órdenes</h3>
                    <p class="text-gray-500 text-sm mt-2">No hay registros asociados a la cédula <span
                            class="font-mono bg-gray-100 px-1 rounded"><?= esc($cedula_buscada) ?></span>.</p>
                    <p class="text-gray-400 text-xs mt-4">Verifica que el número esté correcto e intenta nuevamente.</p>
                </div>
            <?php else: ?>
                <div class="text-center mt-10 opacity-60">
                    <p class="text-sm text-slate-500">Ingresa tu documento de identidad para comenzar la búsqueda.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <footer class="text-center py-6 text-slate-400 text-xs mt-auto">
        <p>&copy; <?= date('Y') ?> Servicio Técnico Profesional.</p>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>