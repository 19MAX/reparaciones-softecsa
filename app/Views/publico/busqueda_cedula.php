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
            background-color: #f8fafc;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
    </style>
</head>

<body class="min-h-screen flex flex-col">

    <div class="bg-slate-900 pb-20 pt-10 px-4 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-20">
            <div class="absolute -top-20 -right-20 w-96 h-96 bg-blue-600 rounded-full blur-3xl"></div>
            <div class="absolute top-20 -left-20 w-72 h-72 bg-indigo-600 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-3xl mx-auto relative z-10 text-center">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Portal de Clientes</h1>
            <p class="text-slate-400 mb-8 text-sm md:text-base">
                Consulta el historial y estado de tus reparaciones ingresando tu cédula.
            </p>

            <form action="<?= base_url('consulta/mis-ordenes') ?>" method="get" class="relative max-w-lg mx-auto">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="search"
                            class="text-slate-400 w-5 h-5 group-focus-within:text-blue-500 transition-colors"></i>
                    </div>
                    <input type="text" name="cedula" value="<?= esc($cedula_buscada) ?>"
                        placeholder="Ingresa tu número de Cédula"
                        class="block w-full pl-11 pr-32 py-4 bg-white/10 border border-slate-700 rounded-full text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-slate-800 transition backdrop-blur-sm shadow-lg"
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
                <div class="mb-6 flex items-center justify-between fade-in">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">
                            Hola, <span class="text-blue-600"><?= esc($cliente['nombres']) ?></span>
                        </h2>
                        <p class="text-xs text-slate-500">Historial de servicios</p>
                    </div>
                    <span
                        class="text-xs font-semibold text-slate-600 bg-white px-3 py-1.5 rounded-full shadow-sm border border-slate-200">
                        <?= count($ordenes) ?> Orden(es)
                    </span>
                </div>
            <?php endif; ?>

            <?php if (!empty($ordenes)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($ordenes as $orden): ?>
                        <div
                            class="glass-card rounded-2xl p-6 shadow-sm hover:shadow-xl transition duration-300 group bg-white flex flex-col h-full">

                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-800 tracking-wide">
                                        #<?= esc($orden['codigo_orden']) ?>
                                    </span>
                                    <p class="text-[11px] text-gray-400 mt-1 flex items-center gap-1">
                                        <i data-lucide="calendar" class="w-3 h-3"></i>
                                        <?= date('d/m/Y', strtotime($orden['created_at'])) ?>
                                    </p>
                                </div>

                                <?php
                                // LÓGICA DE COLORES BASADA EN EL ENTERO DEL ESTADO (CONSTANTES)
                                $estadoInt = (int) $orden['estado'];

                                $badgeClases = match ($estadoInt) {
                                    ESTADO_ORDEN_ABIERTA, ESTADO_ORDEN_EN_PROCESO => 'bg-blue-50 text-blue-700 border-blue-100',
                                    ESTADO_ORDEN_ESPERANDO_REPUESTO => 'bg-orange-50 text-orange-700 border-orange-100',
                                    ESTADO_ORDEN_LISTA_RETIRO => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                    ESTADO_ORDEN_ENTREGADA => 'bg-slate-100 text-slate-700 border-slate-200', // Color neutro para entregado
                                    ESTADO_ORDEN_CANCELADA => 'bg-red-50 text-red-700 border-red-100',
                                    ESTADO_ORDEN_GARANTIA => 'bg-purple-50 text-purple-700 border-purple-100',
                                    default => 'bg-gray-50 text-gray-600 border-gray-100'
                                };
                                ?>
                                <span
                                    class="px-3 py-1 rounded-full text-[10px] font-bold border uppercase tracking-wider <?= $badgeClases ?>">
                                    <?= get_nombre_estado_orden($estadoInt) ?>
                                </span>
                            </div>

                            <div class="space-y-3 mb-6 flex-grow">
                                <?php if (!empty($orden['dispositivos'])): ?>
                                    <?php foreach ($orden['dispositivos'] as $dev): ?>
                                        <div
                                            class="flex items-start p-3 bg-gray-50/50 rounded-lg border border-gray-100 hover:border-gray-200 transition">
                                            <div class="p-2 bg-white rounded-md shadow-sm mr-3 text-slate-600 shrink-0">
                                                <?php
                                                // Si hay icono en BD, úsalo, sino fallback
                                                if (!empty($dev['icono'])) {
                                                    // Asumiendo que guardas clases como 'fa fa-mobile' o nombres lucide
                                                    // Si guardas nombres lucide directos:
                                                    echo "<i data-lucide='" . esc($dev['icono']) . "' class='w-5 h-5'></i>";
                                                } else {
                                                    // Fallback por tipo nombre
                                                    $tipoNombre = strtolower($dev['nombre_tipo'] ?? '');
                                                    $iconoDef = match (true) {
                                                        str_contains($tipoNombre, 'celular') => 'smartphone',
                                                        str_contains($tipoNombre, 'laptop') || str_contains($tipoNombre, 'portatil') => 'laptop',
                                                        str_contains($tipoNombre, 'tablet') => 'tablet',
                                                        str_contains($tipoNombre, 'impresora') => 'printer',
                                                        default => 'cpu'
                                                    };
                                                    echo "<i data-lucide='{$iconoDef}' class='w-5 h-5'></i>";
                                                }
                                                ?>
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <p class="font-semibold text-sm text-gray-800 truncate">
                                                    <?= esc($dev['marca']) ?>                 <?= esc($dev['modelo']) ?>
                                                </p>
                                                <p class="text-xs text-gray-500 truncate mt-0.5">
                                                    <?= esc($dev['problema_reportado']) ?>
                                                </p>
                                                <div class="mt-1.5">
                                                    <?= get_badge_estado_dispositivo((int) $dev['estado_reparacion']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-2 text-xs text-gray-400 italic">Sin dispositivos registrados</div>
                                <?php endif; ?>
                            </div>

                            <div class="pt-4 mt-auto border-t border-gray-100">
                                <a href="<?= base_url('consulta/orden/' . $orden['codigo_orden']) ?>"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-800 hover:text-white hover:border-slate-800 font-medium py-2.5 rounded-xl text-sm transition-all duration-300 group-hover:shadow-md">
                                    Ver Seguimiento Completo <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>

            <?php elseif ($cedula_buscada): ?>
                <div class="text-center bg-white rounded-2xl p-12 shadow-sm border border-gray-100 mt-6 max-w-md mx-auto">
                    <div class="bg-slate-50 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="search-x" class="w-10 h-10 text-slate-300"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">No encontramos registros</h3>
                    <p class="text-slate-500 text-sm mt-2 leading-relaxed">
                        No hay órdenes asociadas al número <span
                            class="font-mono bg-slate-100 px-2 py-0.5 rounded text-slate-700 font-bold"><?= esc($cedula_buscada) ?></span>.
                    </p>
                    <p class="text-slate-400 text-xs mt-6">
                        Por favor verifica el número o contáctanos si crees que es un error.
                    </p>
                </div>

            <?php else: ?>
                <div class="text-center mt-16 opacity-50">
                    <i data-lucide="search" class="w-12 h-12 mx-auto text-slate-300 mb-4"></i>
                    <p class="text-sm text-slate-500">Ingresa tu documento de identidad para buscar.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <footer class="text-center py-8 text-slate-400 text-xs mt-auto border-t border-slate-100 bg-white">
        <p>&copy; <?= date('Y') ?> Servicio Técnico Profesional.</p>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>