<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Softec — ORD-2026-00004</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;700&display=swap"
        rel="stylesheet" />
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-primary:     #080a0f;
            --color-primary-700: #1e2638;
            --color-primary-800: #161c27;
            --color-primary-900: #0e1118;
        }

        /* ── Base pill ─────────────────────────────────────────── */
        .status-pill {
            @apply inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider;
        }

        /* ── Variantes por estado ──────────────────────────────── */
        .status-pendiente {
            @apply bg-amber-500/10 text-amber-300 border border-amber-500/30;
        }
        .status-proceso {
            @apply bg-blue-500/10 text-blue-300 border border-blue-500/30;
        }
        .status-pausado {
            @apply bg-orange-500/10 text-orange-300 border border-orange-500/30;
        }
        .status-listo {
            @apply bg-emerald-500/10 text-emerald-300 border border-emerald-500/30;
        }
        .status-entregado {
            @apply bg-violet-500/10 text-violet-300 border border-violet-500/30;
        }
        .status-cancelado {
            @apply bg-red-500/10 text-red-300 border border-red-500/30;
        }

        /* ── Punto animado (estado activo) ─────────────────────── */
        .pulse-dot {
            @apply animate-pulse;
        }

        /* ── Línea vertical del timeline ───────────────────────── */
        .timeline-line {
            @apply absolute left-3.5 top-0 bottom-0 w-px bg-primary-700;
            transform: translateX(-50%);
        }
    </style>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Space Grotesk", sans-serif;
        }

        .mono {
            font-family: "JetBrains Mono", monospace;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 4px;
        }

        ::-webkit-scrollbar-track {
            background: #080a0f;
        }

        ::-webkit-scrollbar-thumb {
            background: #1e2638;
            border-radius: 4px;
        }

        /* Noise texture overlay — z-index -1 para que nunca tape nada */
        body::before {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: -1;
            opacity: 0.4;
        }

        /* Glow effects */
        .glow-blue {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.25);
        }

        .glow-cyan {
            box-shadow: 0 0 20px rgba(34, 211, 238, 0.2);
        }

        .glow-amber {
            box-shadow: 0 0 10px rgba(251, 191, 36, 0.3);
        }

        /* ── TOGGLE: altura real medida por JS, transición ultra-rápida ── */
        .device-body {
            overflow: hidden;
            /* Duración corta + ease-out para apertura/cierre inmediato */
            transition: height 0.18s ease-out;
            height: 0;
        }

        /* Chevron rotation */
        .chevron {
            transition: transform 0.18s ease-out;
        }

        .chevron.open {
            transform: rotate(180deg);
        }

        /* Tab system */
        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
        }

        /* Progress steps responsive */
        .progress-step-label {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        /* Fade-in animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.4s ease both;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
        }

        /* Mobile tab active */
        .tab-btn.active-tab {
            background: rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.4);
            color: #60a5fa;
        }
    </style>
</head>

<body class="bg-primary text-slate-300 min-h-screen relative">
    <!-- ═══════════════════════════════════════════ HEADER
         z-index: 50 (Tailwind z-50) + background sólido para cubrir contenido al hacer scroll
    -->
    <header class="z-50 bg-primary border-b border-primary-800 sticky top-0" style="
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        background-color: rgba(8, 10, 15, 0.97);
      ">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-blue-600 glow-blue flex items-center justify-center">
                    <ion-icon class="w-4 h-4 text-white" name="construct"></ion-icon>
                </div>
                <div>
                    <span class="text-sm font-bold text-white uppercase tracking-wider">Softec</span>
                    <span
                        class="hidden sm:inline text-[9px] text-slate-600 font-bold uppercase tracking-[0.25em] ml-2"></span>
                </div>
            </div>
            <button
                class="cursor-pointer text-[10px] font-bold uppercase tracking-widest text-blue-400 border border-primary-700 bg-primary-800/50 px-4 py-1.5 rounded-full hover:bg-primary-700 transition-all">
                Portal
            </button>
        </div>
    </header>

    <!-- ═══════════════════════════════════════════ MAIN -->
    <main class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-10 space-y-6 sm:space-y-8">
        <!-- ── PAGE HEADING -->
        <section class="fade-in">
            <nav class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-5">
                <span>Órdenes</span>
                <ion-icon class="w-3 h-3" name="chevron-forward"></ion-icon>
                <span class="text-blue-400">Detalle</span>
            </nav>

            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                        <h1 class="text-3xl sm:text-4xl font-bold text-white">Orden</h1>
                        <span class="mono text-3xl sm:text-4xl font-bold text-cyan-400"
                            style="text-shadow: 0 0 20px rgba(34, 211, 238, 0.3)">
                            <?= $codigo_orden ?>
                        </span>
                    </div>
                    <p class="text-slate-500 mt-2 text-sm">
                        Cliente:
                        <span class="text-white font-semibold"><?= $cliente_nombre ?></span>
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button
                    title="Volver al buscador" onclick="window.location.href = '<?= base_url() ?>consulta/mis-ordenes'"
                        class="cursor-pointer px-4 py-2 bg-primary-800 border border-primary-700 text-[10px] font-bold text-slate-400 rounded-lg hover:bg-primary-700 transition-all uppercase tracking-widest">
                        <ion-icon class="w-4 h-4" name="arrow-back-outline"></ion-icon>
                    </button>
                    <button
                    title="Soporte" onclick="window.location.href = 'https://wa.me/+593989026071'"
                        class="cursor-pointer px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-[10px] font-bold rounded-lg transition-all glow-blue uppercase tracking-widest">
                        Soporte
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 mt-6">
                <div class="flex items-center gap-2 bg-primary-800 border border-primary-700 px-3 py-1.5 rounded-lg">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">Dispositivos</span>
                    <span class="text-white font-bold text-sm"><?= esc($resumen['total']) ?></span>
                </div>
                <div class="flex items-center gap-2 bg-primary-800 border border-primary-700 px-3 py-1.5 rounded-lg">
                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-400"></div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">Entregado</span>
                    <span class="text-white font-bold text-sm"><?= esc($resumen['estados']['entregado'] ?? 0) ?></span>
                </div>
                <div class="flex items-center gap-2 bg-primary-800 border border-primary-700 px-3 py-1.5 rounded-lg">
                    <div class="w-1.5 h-1.5 rounded-full bg-blue-400 pulse-dot"></div>
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tight">En Proceso</span>
                    <span class="text-white font-bold text-sm"><?= esc($resumen['estados']['en_proceso'] ?? 0) ?></span>
                </div>
            </div>
        </section>

        <!-- ── DEVICES LIST -->
        <section class="space-y-3 fade-in" style="animation-delay: 0.1s">

            <?php foreach ($dispositivos as $index => $dispositivo): ?>
                <?php
                $devID = "device-" . $dispositivo['id'];
                $bodyID = "body-" . $devID;
                $chevronID = "chevron-" . $devID;
                $tabID = $dispositivo['id'];

                $est = strtolower($dispositivo['estado']);
                // Determinamos el nivel de progreso
                $isPendiente = in_array($est, ['pendiente', 'en_proceso', 'listo', 'entregado']);
                $isProceso = in_array($est, ['en_proceso', 'listo', 'entregado']);
                $isListo = in_array($est, ['listo', 'entregado']);
                $isEntregado = ($est === 'entregado');

                // ID para las pestañas de JS
                $tabID = $dispositivo['id'];
                ?>
                <!-- ─────────────────────── DEVICE 1 -->
                <article class="bg-primary-900 border border-primary-800 rounded-2xl overflow-hidden" id="<?= $devID ?>">

                    <button onclick="toggleDevice('<?= $devID ?>')"
                        class="cursor-pointer w-full text-left p-4 sm:p-6 flex items-center gap-4 hover:bg-primary-800/40 transition-colors group"
                        aria-expanded="true">
                        <div
                            class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-primary-800 border border-primary-700 flex items-center justify-center flex-shrink-0">
                            <?php if ($dispositivo['tipo_dispositivo'] === 'CELULAR'): ?>
                                <ion-icon class="w-6 h-6 sm:w-7 sm:h-7 text-slate-500" name="phone-portrait-outline"></ion-icon>
                            <?php else: ?>
                                <ion-icon class="w-6 h-6 sm:w-7 sm:h-7 text-slate-500" name="laptop-outline"></ion-icon>
                            <?php endif; ?>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span
                                    class="text-[9px] font-bold text-slate-500 uppercase tracking-widest bg-primary-800 border border-primary-700 px-2 py-0.5 rounded"><?= $dispositivo['tipo_dispositivo'] ?></span>
                                <?= estado_pill($dispositivo['estado']) ?>
                            </div>
                            <h2 class="text-lg sm:text-xl font-bold text-white uppercase tracking-tight">
                                <?= $dispositivo['marca'] ?>     <?= $dispositivo['modelo'] ?>
                            </h2>
                            <!-- <div class="flex gap-4 mt-1">
                                <span class="text-[10px] text-slate-500 font-medium">1 problema ·
                                    <span class="text-amber-400">Cambio de display</span></span>
                            </div> -->
                        </div>

                        <div class="flex items-center gap-3 flex-shrink-0">
                            <span
                                class="hidden sm:block mono text-[10px] text-slate-600 font-bold"><?= date('d M Y', strtotime($dispositivo['fecha_ingreso'])) ?></span>
                            <div
                                class="w-7 h-7 rounded-lg bg-primary-800 border border-primary-700 flex items-center justify-center">
                                <svg class="w-4 h-4 text-slate-400 chevron open" id="<?= $chevronID ?>" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </button>
                    <?php
                    ?>
                    <div class="device-body" id="<?= $bodyID ?>">
                        <div class="border-t border-primary-800">
                            <!-- Progress Timeline -->
                            <div class="px-4 sm:px-8 py-8 bg-primary/30 border-b border-primary-800">
                                <p class="text-[9px] font-bold text-slate-600 uppercase tracking-widest mb-6">Progreso de la
                                    reparación</p>
                                <div class="relative flex items-start justify-between">
                                    <div class="absolute left-0 right-0 top-4 h-px flex" style="padding: 0 1.25rem">
                                        <div class="flex-1 h-px <?= $isProceso ? 'bg-blue-500/70' : 'bg-primary-700' ?>">
                                        </div>
                                        <div class="flex-1 h-px <?= $isListo ? 'bg-blue-500/70' : 'bg-primary-700' ?>">
                                        </div>
                                        <div
                                            class="flex-1 h-px <?= $isEntregado ? 'bg-emerald-500/70' : 'bg-primary-700' ?>">
                                        </div>
                                    </div>

                                    <div class="relative z-10 flex flex-col items-center gap-2 w-1/4">
                                        <div
                                            class="w-8 h-8 rounded-full border-2 border-primary flex items-center justify-center <?= $isPendiente ? 'bg-blue-600 glow-blue' : 'bg-primary-700' ?>">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <p
                                            class="progress-step-label <?= $isPendiente ? 'text-blue-400' : 'text-slate-600' ?>">
                                            Pendiente</p>
                                    </div>

                                    <div class="relative z-10 flex flex-col items-center gap-2 w-1/4">
                                        <div
                                            class="w-8 h-8 rounded-full border-2 border-primary flex items-center justify-center <?= $isProceso ? 'bg-blue-600 glow-blue' : 'bg-primary-700' ?>">
                                            <svg class="w-4 h-4 <?= $isProceso ? 'text-white' : 'text-slate-600' ?>"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <p
                                            class="progress-step-label <?= $isProceso ? 'text-blue-400' : 'text-slate-600' ?>">
                                            En Proceso</p>
                                    </div>

                                    <div class="relative z-10 flex flex-col items-center gap-2 w-1/4">
                                        <div
                                            class="w-8 h-8 rounded-full border-2 border-primary flex items-center justify-center <?= $isListo ? 'bg-emerald-500' : 'bg-primary-700' ?>">
                                            <svg class="w-4 h-4 <?= $isListo ? 'text-white' : 'text-slate-600' ?>"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <p
                                            class="progress-step-label <?= $isListo ? 'text-emerald-400' : 'text-slate-600' ?>">
                                            Listo</p>
                                    </div>

                                    <div class="relative z-10 flex flex-col items-center gap-2 w-1/4">
                                        <div
                                            class="w-8 h-8 rounded-full border-2 border-primary flex items-center justify-center <?= $isEntregado ? 'bg-emerald-600' : 'bg-primary-700' ?>">
                                            <svg class="w-4 h-4 <?= $isEntregado ? 'text-white' : 'text-slate-600' ?>"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                                            </svg>
                                        </div>
                                        <p
                                            class="progress-step-label <?= $isEntregado ? 'text-emerald-400' : 'text-slate-600' ?>">
                                            Entregado</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Date info row -->
                            <div class="grid grid-cols-3 divide-x divide-primary-800 border-b border-primary-800">
                                <div class="p-4 sm:p-5">
                                    <p class="text-[9px] font-bold text-slate-600 uppercase tracking-widest mb-1">Ingreso
                                    </p>
                                    <p class="mono text-xs sm:text-sm font-bold text-slate-300">
                                        <?= date('d M Y', strtotime($dispositivo['fecha_ingreso'])) ?><br
                                            class="sm:hidden" />
                                        <span
                                            class="text-slate-500"><?= date('H:i', strtotime($dispositivo['fecha_ingreso'])) ?></span>
                                    </p>
                                </div>
                                <div class="p-4 sm:p-5">
                                    <p class="text-[9px] font-bold text-slate-600 uppercase tracking-widest mb-1">Est.
                                        Entrega</p>
                                    <p class="mono text-xs sm:text-sm font-bold text-slate-400">
                                        <?= !empty($dispositivo['fecha_estimada_entrega']) ? date('d M Y', strtotime($dispositivo['fecha_estimada_entrega'])) : 'TBD' ?>
                                    </p>
                                </div>
                                <div class="p-4 sm:p-5">
                                    <p class="text-[9px] font-bold text-slate-600 uppercase tracking-widest mb-1">Entregado
                                    </p>
                                    <p
                                        class="text-xs sm:text-sm font-bold <?= $isEntregado ? 'text-emerald-400' : 'text-slate-600 italic' ?>">
                                        <?= $isEntregado ? date('d M Y', strtotime($dispositivo['fecha_real_entrega'])) : 'Pendiente' ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Mobile Tabs / Desktop Grid -->
                            <div>
                                <div class="flex border-b border-primary-800 sm:hidden">
                                    <button onclick="switchTab('<?= $tabID ?>', 'issues')" id="<?= $tabID ?>-tab-issues"
                                        class="cursor-pointer tab-btn active-tab flex-1 py-3 text-[10px] font-bold uppercase tracking-widest border-b-2 border-transparent transition-all">Problemas</button>
                                    </button>
                                    <button onclick="switchTab('<?= $tabID ?>', 'log')" id="<?= $tabID ?>-tab-log"
                                        class="cursor-pointer tab-btn flex-1 py-3 text-[10px] font-bold text-slate-500 uppercase tracking-widest border-b-2 border-transparent transition-all">Historial</button>
                                    </button>
                                </div>

                                <div class="sm:grid sm:grid-cols-5">
                                    <div class="sm:col-span-3 p-4 sm:p-6 sm:border-r border-primary-800 tab-panel active"
                                        id="<?= $tabID ?>-panel-issues">
                                        <div class="flex items-center gap-2 mb-4">
                                            <div
                                                class="w-6 h-6 rounded bg-amber-500/15 border border-amber-500/25 flex items-center justify-center glow-amber">
                                                <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" />
                                                </svg>
                                            </div>
                                            <h3 class="text-[11px] font-bold text-white uppercase tracking-wider">Problemas
                                                reportados</h3>
                                            <span
                                                class="text-[9px] font-bold text-amber-400 bg-amber-500/10 border border-amber-500/20 px-1.5 py-0.5 rounded"><?= count($dispositivo['problemas']) ?></span>
                                        </div>

                                        <div class="space-y-2">
                                            <?php foreach ($dispositivo['problemas'] as $p): ?>
                                                <div
                                                    class="p-3.5 rounded-xl bg-primary-800/50 border border-primary-700 hover:border-[#263045] transition-colors">
                                                    <div class="flex items-center gap-2.5">
                                                        <div class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"
                                                            style="box-shadow: 0 0 6px rgba(251, 191, 36, 0.5)"></div>
                                                        <span
                                                            class="text-slate-200 font-semibold text-sm"><?= $p['problema'] ?></span>
                                                    </div>
                                                    <?php if ($p['observacion']): ?>
                                                        <p class="text-xs text-slate-500 mt-1.5 ml-4 italic">
                                                            <?= $p['observacion'] ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <div class="mt-6">
                                            <div class="flex items-center gap-2 mb-3">
                                                <svg class="w-3.5 h-3.5 text-slate-600" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path
                                                        d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3 2a1 1 0 000 2h10a1 1 0 100-2H5zm0 4a1 1 0 100 2h7a1 1 0 100-2H5z" />
                                                </svg>
                                                <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                                    Observaciones del cliente
                                                </h4>
                                            </div>
                                            <div
                                                class="p-3 rounded-xl bg-primary border border-primary-800 text-slate-500 text-sm italic">
                                                <?= isset($dispositivo['relato_cliente']) ? $dispositivo['relato_cliente'] : 'Sin observaciones' ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-2 p-4 sm:p-6 bg-primary/20 tab-panel" id="<?= $tabID ?>-panel-log">
                                        <div class="flex items-center gap-2 mb-6">
                                            <div
                                                class="w-6 h-6 rounded bg-primary-800 border border-primary-700 flex items-center justify-center">
                                                <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </div>
                                            <h3 class="text-[11px] font-bold text-white uppercase tracking-wider">
                                                Historial
                                            </h3>
                                            <span
                                                class="text-[9px] font-bold text-slate-500 bg-primary-800 border border-primary-700 px-1.5 py-0.5 rounded"><?= count($dispositivo['historial']) ?></span>
                                        </div>
                                        <?= estado_timeline($dispositivo['historial']) ?>

                                        <?php if ($dispositivo['estado'] == 'entregado'): ?>
                                            <div
                                                class="mt-8 bg-emerald-500/5 border border-emerald-500/20 p-3 rounded-xl flex items-center justify-between">
                                                <span
                                                    class="text-[9px] font-bold text-slate-600 uppercase tracking-widest">Estado
                                                    Final</span>
                                                <span class="mono text-xs font-bold text-emerald-400">LISTO PARA RETIRO</span>
                                            </div>
                                        <?php elseif ($dispositivo['estado'] !== 'entregado'): ?>

                                            <div
                                                class="mt-8 bg-primary-800/30 border border-primary-700 p-3 rounded-xl flex items-center justify-between">
                                                <span class="text-[9px] font-bold text-slate-600 uppercase tracking-widest">Est.
                                                    Entrega</span>
                                                <span
                                                    class="mono text-xs font-bold text-rose-400"><?= !empty($dispositivo['fecha_estimada_entrega']) ? strtoupper(date('d M Y', strtotime($dispositivo['fecha_estimada_entrega']))) : 'TBD' ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    </main>

    <!-- ═══════════════════════════════════════════ FOOTER -->
    <footer class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 py-8 mt-6 border-t border-primary-800 text-center">
        <p class="mono text-[9px] font-bold text-slate-700 uppercase tracking-[0.3em]">
            © 2026 Softec
        </p>
    </footer>

    <script>
        // ── TOGGLE: mide la altura real del inner div, sin max-height trucado ──
        function toggleDevice(id) {
            const body = document.getElementById("body-" + id);
            const chevron = document.getElementById("chevron-" + id);
            const article = document.getElementById(id);
            const btn = article.querySelector("button");

            const isOpen = body.style.height !== "0px" && body.style.height !== "";

            if (isOpen) {
                body.style.height = body.scrollHeight + "px";
                body.getBoundingClientRect(); // forzar reflow
                body.style.height = "0px";
                chevron.classList.remove("open");
                btn.setAttribute("aria-expanded", "false");
            } else {
                body.style.height = body.scrollHeight + "px";
                chevron.classList.add("open");
                btn.setAttribute("aria-expanded", "true");
                body.addEventListener("transitionend", function onEnd() {
                    body.style.height = "auto";
                    body.removeEventListener("transitionend", onEnd);
                }, { once: true });
            }
        }

        window.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".device-body").forEach(function (body, index) {
                body.style.height = index === 0 ? "auto" : "0px";
            });
        });

        function switchTab(deviceId, tab) {
            const panels = ["issues", "log"];
            panels.forEach(function (p) {
                const panel = document.getElementById(deviceId + "-panel-" + p);
                const tabBtn = document.getElementById(deviceId + "-tab-" + p);
                if (p === tab) {
                    panel.classList.add("active");
                    tabBtn.classList.add("active-tab");
                    tabBtn.classList.remove("text-slate-500");
                } else {
                    panel.classList.remove("active");
                    tabBtn.classList.remove("active-tab");
                    tabBtn.classList.add("text-slate-500");
                }
            });
        }

        // En pantallas sm+ siempre mostrar ambos paneles
        function handleResize() {
            var isMobile = window.innerWidth < 640;
            document.querySelectorAll(".tab-panel").forEach(function (p) {
                p.style.display = isMobile ? "" : "block";
            });
        }
        window.addEventListener("resize", handleResize);
        handleResize();
    </script>
</body>

</html>