<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Órdenes de Servicio</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        surface: {
                            950: "#080a0f",
                            900: "#0e1118",
                            800: "#161c27",
                            700: "#1e2638",
                            600: "#263045",
                        },
                        brand: { 400: "#60a5fa", 500: "#3b82f6", 600: "#2563eb" },
                        cyan:    { 400: "#22d3ee", 500: "#06b6d4" },
                        amber:   { 400: "#fbbf24", 500: "#f59e0b" },
                        emerald: { 400: "#34d399", 500: "#10b981" },
                        rose:    { 400: "#fb7185", 500: "#f43f5e" },
                    },
                },
            },
        };
    </script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Space Grotesk', sans-serif; background: #080a0f; }
        .mono { font-family: 'JetBrains Mono', monospace; }

        /* Noise texture */
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none; z-index: 0; opacity: 0.4;
        }

        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: #080a0f; }
        ::-webkit-scrollbar-thumb { background: #1e2638; border-radius: 4px; }

        .glow-blue  { box-shadow: 0 0 20px rgba(59,130,246,0.25); }
        .glow-cyan  { box-shadow: 0 0 20px rgba(34,211,238,0.2); }

        /* Fade-in stagger */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeUp 0.45s ease both; }
        .fade-in-1 { animation: fadeUp 0.45s 0.05s ease both; }
        .fade-in-2 { animation: fadeUp 0.45s 0.12s ease both; }
        .fade-in-3 { animation: fadeUp 0.45s 0.20s ease both; }
        .fade-in-4 { animation: fadeUp 0.45s 0.28s ease both; }
        .fade-in-5 { animation: fadeUp 0.45s 0.36s ease both; }

        /* Orb blobs */
        .orb {
            position: absolute;
            border-radius: 9999px;
            filter: blur(80px);
            opacity: 0.12;
            pointer-events: none;
        }

        /* Card hover */
        .order-card {
            transition: border-color 0.25s, box-shadow 0.25s, transform 0.25s;
        }
        .order-card:hover {
            border-color: rgba(59,130,246,0.35);
            box-shadow: 0 0 30px rgba(59,130,246,0.08);
            transform: translateY(-2px);
        }

        /* Search input focus ring override */
        .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(59,130,246,0.5);
        }

        /* Pulse */
        .pulse-dot { animation: pulse 2s cubic-bezier(0.4,0,0.6,1) infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        /* Status pills */
        .sp { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:999px; font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:.12em; }
        .sp-blue    { background:rgba(59,130,246,.12); border:1px solid rgba(59,130,246,.3); color:#60a5fa; }
        .sp-green   { background:rgba(52,211,153,.12); border:1px solid rgba(52,211,153,.3); color:#34d399; }
        .sp-amber   { background:rgba(251,191,36,.1);  border:1px solid rgba(251,191,36,.25); color:#fbbf24; }
        .sp-slate   { background:rgba(148,163,184,.08);border:1px solid rgba(148,163,184,.2); color:#94a3b8; }
        .sp-rose    { background:rgba(251,113,133,.1); border:1px solid rgba(251,113,133,.25);color:#fb7185; }

        /* Dev badge */
        .dev-badge { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:6px; font-size:9px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; }
    </style>
</head>

<body class="min-h-screen flex flex-col text-slate-300">

    <!-- ══════════════════════════════════ HEADER / SEARCH -->
    <div class="relative overflow-hidden bg-surface-950 border-b border-surface-800 pb-24 pt-12 px-4">
        <!-- Ambient orbs -->
        <div class="orb w-96 h-96 bg-brand-500 -top-20 -right-24"></div>
        <div class="orb w-72 h-72 bg-cyan-500 top-16 -left-20"></div>

        <!-- Logo bar -->
        <div class="max-w-3xl mx-auto relative z-10 mb-10 flex items-center justify-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-brand-600 glow-blue flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
            </div>
            <div>
                <span class="text-sm font-bold text-white uppercase tracking-wider">RepairTracker</span>
                <span class="text-[9px] text-slate-600 font-bold uppercase tracking-[0.25em] ml-2">Service Portal</span>
            </div>
        </div>

        <div class="max-w-3xl mx-auto relative z-10 text-center fade-in">
            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-2 tracking-tight">Portal de Clientes</h1>
            <p class="text-slate-500 mb-8 text-sm">
                Consulta el historial y estado de tus reparaciones ingresando tu cédula.
            </p>

            <form action="<?= base_url('consulta/mis-ordenes') ?>" method="get" class="relative max-w-lg mx-auto">
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="search" class="text-slate-500 w-4 h-4 group-focus-within:text-brand-400 transition-colors"></i>
                    </div>
                    <input
                        type="text" name="cedula" value="<?= esc($cedula_buscada) ?>"
                        placeholder="Ingresa tu número de Cédula"
                        class="search-input block w-full pl-11 pr-32 py-3.5 bg-surface-800/80 border border-surface-700 rounded-full text-white placeholder-slate-600 focus:border-brand-500/50 transition backdrop-blur-sm text-sm"
                        required>
                    <button type="submit"
                        class="absolute right-1.5 top-1.5 bottom-1.5 bg-brand-600 hover:bg-brand-500 text-white px-5 rounded-full font-bold transition glow-blue text-[11px] uppercase tracking-widest">
                        Consultar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════════════════ CONTENT -->
    <div class="flex-grow -mt-12 px-4 pb-12 relative z-20">
        <div class="max-w-4xl mx-auto">

            <?php if (isset($cliente)): ?>
            <!-- Client greeting card -->
            <div class="fade-in-1 mb-6 bg-surface-900 border border-surface-800 rounded-2xl p-5 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-white">
                        Hola, <span class="text-cyan-400"><?= esc($cliente['nombres']) ?></span>
                    </h2>
                    <p class="text-[10px] text-slate-600 font-bold uppercase tracking-widest mt-0.5">Historial de servicios</p>
                </div>
                <span class="mono text-[10px] font-bold text-slate-500 bg-surface-800 border border-surface-700 px-3 py-1.5 rounded-full">
                    <?= count($ordenes) ?> Orden(es)
                </span>
            </div>
            <?php endif; ?>

            <?php if (!empty($ordenes)): ?>
            <!-- Orders grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($ordenes as $i => $orden): ?>
                <div class="order-card bg-surface-900 border border-surface-800 rounded-2xl p-5 flex flex-col fade-in-<?= min($i+2, 5) ?>">

                    <!-- Order header -->
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <span class="mono text-[10px] font-bold text-slate-500 bg-surface-800 border border-surface-700 px-2.5 py-1 rounded-lg tracking-widest">
                                #<?= esc($orden['numero_orden']) ?>
                            </span>
                            <p class="text-[10px] text-slate-600 mt-2 flex items-center gap-1 font-medium">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                <?= date('d/m/Y', strtotime($orden['fecha_ingreso'])) ?>
                            </p>
                        </div>
                        <?= estadoPill($orden['estado']) ?>
                    </div>

                    <!-- Devices -->
                    <div class="space-y-2 mb-5 flex-grow">
                        <?php if (!empty($orden['dispositivos'])): ?>
                            <?php foreach ($orden['dispositivos'] as $dev): ?>
                            <div class="flex items-center p-3 bg-surface-800/60 border border-surface-700 hover:border-surface-600 rounded-xl transition-colors">
                                <div class="w-8 h-8 rounded-lg bg-surface-700 border border-surface-600 flex items-center justify-center text-slate-400 shrink-0 mr-3">
                                    <?php
                                    if (!empty($dev['icono'])) {
                                        echo "<i data-lucide='" . esc($dev['icono']) . "' class='w-4 h-4'></i>";
                                    } else {
                                        $tipoNombre = strtolower($dev['tipo_dispositivo'] ?? '');
                                        $iconoDef = match (true) {
                                            str_contains($tipoNombre, 'celular')  => 'smartphone',
                                            str_contains($tipoNombre, 'laptop') || str_contains($tipoNombre, 'portatil') => 'laptop',
                                            str_contains($tipoNombre, 'tablet')   => 'tablet',
                                            str_contains($tipoNombre, 'impresora')=> 'printer',
                                            default => 'cpu'
                                        };
                                        echo "<i data-lucide='{$iconoDef}' class='w-4 h-4'></i>";
                                    }
                                    ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm text-slate-200 truncate uppercase tracking-tight">
                                        <?= esc($dev['marca']) ?> <?= esc($dev['modelo']) ?>
                                    </p>
                                    <div class="mt-1">
                                        <?= get_badge_estado_dispositivo((int) $dev['dispositivo_estado']) ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-[11px] text-slate-600 italic">Sin dispositivos registrados</div>
                        <?php endif; ?>
                    </div>

                    <!-- CTA -->
                    <div class="pt-4 border-t border-surface-800">
                        <a href="<?= base_url('consulta/orden/' . $orden['numero_orden']) ?>"
                            class="w-full inline-flex items-center justify-center gap-2 bg-surface-800 border border-surface-700 text-slate-300 hover:bg-brand-600 hover:text-white hover:border-brand-600 font-bold py-2.5 rounded-xl text-[11px] transition-all duration-300 uppercase tracking-widest">
                            Ver Seguimiento Completo
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>

            <?php elseif ($cedula_buscada): ?>
            <!-- No results -->
            <div class="fade-in text-center bg-surface-900 border border-surface-800 rounded-2xl p-12 mt-6 max-w-md mx-auto">
                <div class="w-20 h-20 rounded-2xl bg-surface-800 border border-surface-700 flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="search-x" class="w-8 h-8 text-slate-600"></i>
                </div>
                <h3 class="text-lg font-bold text-white">No encontramos registros</h3>
                <p class="text-slate-500 text-sm mt-2 leading-relaxed">
                    No hay órdenes asociadas al número
                    <span class="mono bg-surface-800 border border-surface-700 px-2 py-0.5 rounded text-slate-300 text-xs font-bold ml-1"><?= esc($cedula_buscada) ?></span>
                </p>
                <p class="text-slate-600 text-xs mt-6">
                    Verifica el número o contáctanos si crees que es un error.
                </p>
            </div>

            <?php else: ?>
            <!-- Empty state -->
            <div class="fade-in text-center mt-20 opacity-60">
                <div class="w-16 h-16 rounded-2xl bg-surface-800 border border-surface-700 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="search" class="w-7 h-7 text-slate-600"></i>
                </div>
                <p class="text-sm text-slate-600 font-medium">Ingresa tu documento de identidad para buscar.</p>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- ══════════════════════════════════ FOOTER -->
    <footer class="relative z-10 border-t border-surface-800 py-8 text-center">
        <p class="mono text-[9px] font-bold text-slate-700 uppercase tracking-[0.3em]">
            &copy; <?= date('Y') ?> RepairTracker Pro — Enterprise Service Infrastructure
        </p>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>