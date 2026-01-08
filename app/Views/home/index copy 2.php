<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/ScrollTrigger.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/ScrollToPlugin.min.js"></script>
  <style type="text/tailwindcss">
    @theme {
      --color-tech-blue: #00d4ff;
      --color-tech-cyan: #00fff2;
      --color-tech-purple: #a855f7;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      background: #000;
      color: #fff;
      font-family: system-ui, -apple-system, sans-serif;
      overflow-x: hidden;
    }
    
    .glow-blue {
      box-shadow: 0 0 30px rgba(0, 212, 255, 0.4);
    }
    
    .glow-cyan {
      box-shadow: 0 0 30px rgba(0, 255, 242, 0.4);
    }
    
    .breathing-glow {
      animation: breathe 3s ease-in-out infinite;
    }
    
    @keyframes breathe {
      0%, 100% { opacity: 0.7; transform: scale(1); }
      50% { opacity: 1; transform: scale(1.05); }
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
    }
    
    .float-animation {
      animation: float 6s ease-in-out infinite;
    }
    
    .scroll-section {
      height: 500vh;
      position: relative;
    }
    
    .sticky-container {
      position: sticky;
      top: 0;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }
    
    .tech-bg {
      position: absolute;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.15) 0%, transparent 70%);
      opacity: 0.5;
    }
    
    .grid-pattern {
      position: absolute;
      width: 100%;
      height: 100%;
      background-image: 
        linear-gradient(rgba(0, 212, 255, 0.1) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 212, 255, 0.1) 1px, transparent 1px);
      background-size: 50px 50px;
      opacity: 0.2;
    }
    
    .video-container {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 70%;
      max-width: 700px;
      z-index: 5;
      opacity: 0;
      border-radius: 30px;
      overflow: hidden;
    }
    
    .video-container video {
      width: 100%;
      height: auto;
      display: block;
      border-radius: 30px;
      box-shadow: 
        0 0 60px rgba(0, 212, 255, 0.6),
        0 20px 60px rgba(0, 0, 0, 0.8);
    }
    
    .navbar-glass {
      backdrop-filter: blur(20px);
      background: rgba(0, 0, 0, 0.85);
      border-bottom: 1px solid rgba(0, 212, 255, 0.3);
      transition: all 0.3s ease;
    }
    
    .service-card {
      background: linear-gradient(145deg, rgba(20, 20, 30, 0.8), rgba(10, 10, 20, 0.9));
      border: 2px solid rgba(0, 212, 255, 0.2);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }
    
    .service-card::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(0, 212, 255, 0.1) 0%, transparent 70%);
      opacity: 0;
      transition: opacity 0.4s ease;
    }
    
    .service-card:hover::before {
      opacity: 1;
    }
    
    .service-card:hover {
      transform: translateY(-15px) scale(1.02);
      box-shadow: 0 20px 60px rgba(0, 212, 255, 0.4);
      border-color: rgba(0, 212, 255, 0.8);
    }
    
    .brand-badge {
      background: linear-gradient(135deg, rgba(30, 30, 40, 0.9), rgba(15, 15, 25, 0.95));
      border: 2px solid rgba(0, 212, 255, 0.3);
      transition: all 0.3s ease;
    }
    
    .brand-badge:hover {
      transform: translateY(-5px) scale(1.05);
      border-color: rgba(0, 212, 255, 0.7);
      box-shadow: 0 10px 30px rgba(0, 212, 255, 0.3);
    }
    
    .faq-item {
      border-bottom: 1px solid rgba(0, 212, 255, 0.2);
      transition: all 0.3s ease;
      background: rgba(20, 20, 30, 0.5);
    }
    
    .faq-item:hover {
      background: rgba(0, 212, 255, 0.05);
      border-color: rgba(0, 212, 255, 0.4);
    }
    
    .faq-answer {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .faq-answer.active {
      max-height: 500px;
    }
    
    .contact-card {
      background: linear-gradient(135deg, rgba(0, 212, 255, 0.1), rgba(0, 255, 242, 0.1));
      border: 2px solid rgba(0, 212, 255, 0.3);
      transition: all 0.3s ease;
    }
    
    .contact-card:hover {
      transform: translateY(-5px);
      border-color: rgba(0, 212, 255, 0.6);
      box-shadow: 0 10px 30px rgba(0, 212, 255, 0.2);
    }
    
    .cta-button {
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
    }
    
    .cta-button::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      transform: translate(-50%, -50%);
      transition: width 0.6s ease, height 0.6s ease;
    }
    
    .cta-button:hover::before {
      width: 300px;
      height: 300px;
    }
    
    @media (max-width: 768px) {
      .video-container {
        width: 85%;
      }
      
      .scroll-section {
        height: 400vh;
      }
    }
  </style>
</head>

<body>
  <!-- Navbar -->
  <nav class="navbar-glass fixed top-0 w-full z-50">
    <div class="max-w-7xl mx-auto px-6 py-4">
      <div class="flex items-center justify-between">
        <div class="text-2xl font-bold text-tech-blue flex items-center gap-2">
          <span class="text-3xl">⚡</span>
          TechFix
        </div>
        <div class="hidden md:flex gap-8">
          <a href="#hero" class="text-gray-300 hover:text-tech-blue transition-colors font-medium">Inicio</a>
          <a href="#servicios" class="text-gray-300 hover:text-tech-blue transition-colors font-medium">Servicios</a>
          <a href="#marcas" class="text-gray-300 hover:text-tech-blue transition-colors font-medium">Marcas</a>
          <a href="#faq" class="text-gray-300 hover:text-tech-blue transition-colors font-medium">FAQ</a>
          <a href="#contacto" class="text-gray-300 hover:text-tech-blue transition-colors font-medium">Contacto</a>
        </div>
        <button id="menu-mobile" class="md:hidden text-tech-blue">
          <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
        </button>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section id="hero" class="pt-20">
    <!-- Selector de modo mejorado -->
    <div
      class="fixed top-24 left-1/2 -translate-x-1/2 z-40 flex gap-4 bg-gray-900/90 backdrop-blur-md px-8 py-4 rounded-full border-2 border-tech-blue/40 shadow-lg shadow-tech-blue/20">
      <button id="btn-laptops"
        class="px-8 py-3 rounded-full bg-tech-blue text-black font-bold transition-all duration-300 shadow-lg shadow-tech-blue/50">
        💻 Laptops
      </button>
      <button id="btn-smartphones"
        class="px-8 py-3 rounded-full bg-transparent text-white border-2 border-gray-600 font-bold transition-all duration-300 hover:border-tech-cyan hover:text-tech-cyan">
        📱 Smartphones
      </button>
    </div>

    <!-- Sección de Laptops -->
    <div id="section-laptops" class="scroll-section">
      <div class="sticky-container">
        <div class="tech-bg"></div>
        <div class="grid-pattern"></div>

        <!-- Video de Laptops -->
        <div id="laptop-video" class="video-container">
          <video src="<?=base_url()?>assets/videos/laptops_optimizado.mp4" muted
            preload="auto" playsinline></video>
        </div>

        <!-- Texto hero -->
        <div id="hero-text-laptop" class="absolute top-1/4 left-1/2 -translate-x-1/2 text-center opacity-0 z-10">
          <h1
            class="text-5xl md:text-7xl font-bold mb-4 bg-gradient-to-r from-tech-blue to-tech-cyan bg-clip-text text-transparent">
            Reparación Profesional
          </h1>
          <p class="text-xl md:text-2xl text-gray-300">de Laptops en Guayaquil</p>
        </div>

        <!-- Logos de marcas con diseño mejorado -->
        <div id="brand-hp" class="absolute" style="top: 18%; left: 12%; opacity: 0;">
          <div class="brand-badge w-28 h-28 rounded-3xl flex items-center justify-center glow-blue breathing-glow">
            <span class="text-3xl font-bold text-tech-blue">HP</span>
          </div>
        </div>

        <div id="brand-asus" class="absolute" style="top: 18%; right: 12%; opacity: 0;">
          <div class="brand-badge w-28 h-28 rounded-3xl flex items-center justify-center glow-blue breathing-glow">
            <span class="text-3xl font-bold text-tech-blue">ASUS</span>
          </div>
        </div>

        <div id="brand-dell" class="absolute" style="bottom: 22%; left: 12%; opacity: 0;">
          <div class="brand-badge w-28 h-28 rounded-3xl flex items-center justify-center glow-blue breathing-glow">
            <span class="text-3xl font-bold text-tech-blue">DELL</span>
          </div>
        </div>

        <div id="brand-lenovo" class="absolute" style="bottom: 22%; right: 12%; opacity: 0;">
          <div class="brand-badge w-28 h-28 rounded-3xl flex items-center justify-center glow-blue breathing-glow">
            <span class="text-2xl font-bold text-tech-blue">Lenovo</span>
          </div>
        </div>

        <!-- Sistemas operativos -->
        <div id="os-windows" class="absolute" style="bottom: 12%; left: 32%; opacity: 0;">
          <div
            class="w-20 h-20 bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl flex items-center justify-center border-2 border-tech-cyan/40 float-animation">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="#00fff2">
              <path
                d="M0 3.449L9.75 2.1v9.451H0m10.949-9.602L24 0v11.4H10.949M0 12.6h9.75v9.451L0 20.699M10.949 12.6H24V24l-12.9-1.801" />
            </svg>
          </div>
        </div>

        <div id="os-linux" class="absolute" style="bottom: 12%; right: 32%; opacity: 0;">
          <div
            class="w-20 h-20 bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl flex items-center justify-center border-2 border-tech-cyan/40 float-animation">
            <span class="text-4xl">🐧</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Sección de Smartphones -->
    <div id="section-smartphones" class="scroll-section hidden">
      <div class="sticky-container">
        <div class="tech-bg"></div>
        <div class="grid-pattern"></div>

        <!-- Video de Smartphones -->
        <div id="phone-video" class="video-container">
          <video src="<?=base_url()?>assets/videos/celulares_optimizado.mp4" muted
            preload="auto" playsinline></video>
        </div>

        <!-- Texto hero -->
        <div id="hero-text-phone" class="absolute top-1/4 left-1/2 -translate-x-1/2 text-center opacity-0 z-10">
          <h1
            class="text-5xl md:text-7xl font-bold mb-4 bg-gradient-to-r from-tech-cyan to-tech-purple bg-clip-text text-transparent">
            Reparación Experta
          </h1>
          <p class="text-xl md:text-2xl text-gray-300">de Smartphones en Guayaquil</p>
        </div>

        <!-- Marcas de smartphones -->
        <div id="brand-samsung" class="absolute" style="top: 15%; left: 10%; opacity: 0;">
          <div class="brand-badge w-32 h-32 rounded-3xl flex items-center justify-center glow-cyan breathing-glow">
            <span class="text-2xl font-bold text-tech-cyan">Samsung</span>
          </div>
        </div>

        <div id="brand-xiaomi" class="absolute" style="top: 15%; right: 10%; opacity: 0;">
          <div class="brand-badge w-32 h-32 rounded-3xl flex items-center justify-center glow-cyan breathing-glow">
            <span class="text-2xl font-bold text-tech-cyan">Xiaomi</span>
          </div>
        </div>

        <div id="brand-apple" class="absolute" style="bottom: 28%; left: 10%; opacity: 0;">
          <div class="brand-badge w-32 h-32 rounded-3xl flex items-center justify-center glow-cyan breathing-glow">
            <span class="text-5xl">🍎</span>
          </div>
        </div>

        <div id="brand-motorola" class="absolute" style="bottom: 28%; right: 10%; opacity: 0;">
          <div class="brand-badge w-32 h-32 rounded-3xl flex items-center justify-center glow-cyan breathing-glow">
            <span class="text-xl font-bold text-tech-cyan">Motorola</span>
          </div>
        </div>

        <!-- Sistemas operativos móviles -->
        <div id="os-android" class="absolute" style="bottom: 12%; left: 28%; opacity: 0;">
          <div
            class="w-24 h-24 bg-gradient-to-br from-green-900 to-green-950 rounded-2xl flex items-center justify-center border-2 border-green-500/40 float-animation">
            <span class="text-5xl">🤖</span>
          </div>
        </div>

        <div id="os-ios" class="absolute" style="bottom: 12%; right: 28%; opacity: 0;">
          <div
            class="w-24 h-24 bg-gradient-to-br from-blue-900 to-blue-950 rounded-2xl flex items-center justify-center border-2 border-blue-400/40 float-animation">
            <span class="text-5xl">📱</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Sección de Servicios -->
  <section id="servicios" class="py-32 px-6 bg-black relative">
    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-tech-blue/5 to-transparent"></div>
    <div class="max-w-7xl mx-auto relative z-10">
      <div class="text-center mb-20">
        <h2
          class="text-5xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-tech-blue to-tech-cyan bg-clip-text text-transparent">
          Nuestros Servicios
        </h2>
        <p class="text-xl text-gray-400 max-w-2xl mx-auto">
          Soluciones profesionales con garantía de 90 días
        </p>
      </div>

      <div class="grid md:grid-cols-3 gap-10">
        <div class="service-card p-10 rounded-3xl">
          <div class="text-6xl mb-6 float-animation">💻</div>
          <h3 class="text-3xl font-bold mb-4 text-white">Reparación de Laptops</h3>
          <p class="text-gray-400 mb-6 text-lg leading-relaxed">
            Servicio técnico especializado para todas las marcas. Diagnóstico gratuito en 24 horas.
          </p>
          <ul class="text-gray-500 space-y-3">
            <li class="flex items-start gap-3">
              <span class="text-tech-blue text-xl">✓</span>
              <span>Cambio de pantallas LCD/LED</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="text-tech-blue text-xl">✓</span>
              <span>Actualización RAM y SSD</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="text-tech-blue text-xl">✓</span>
              <span>Reparación de teclados</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="text-tech-blue text-xl">✓</span>
              <span>Limpieza y mantenimiento profundo</span>
            </li>
          </ul>
        </div>

        <div class="service-card p-10 rounded-3xl">
          <div class="text-6xl mb-6 float-animation" style="animation-delay: 0.2s;">📱</div>
          <h3 class="text-3xl font-bold mb-4 text-white">Reparación de Smartphones</h3>
          <p class="text-gray-400 mb-6 text-lg leading-relaxed">
            Expertos en iPhone, Samsung, Xiaomi y más. Repuestos originales garantizados.
          </p>
          <ul class="text-gray-500 space-y-3">
            <li class="flex items-start gap-3">
              <span class="text-tech-cyan text-xl">✓</span>
              <span>Cambio de pantallas táctiles</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="text-tech-cyan text-xl">✓</span>
              <span>Reemplazo de baterías premium</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="text-tech-cyan text-xl">✓</span>
              <span>Reparación de puertos de carga</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="text-tech-cyan text-xl">✓</span>
              <span>Liberación y desbloqueo</span>
            </li>
          </ul>
        </div>

        <div class="service-card p-10 rounded-3xl">
          <div class="text-6xl mb-6 float-animation" style="animation-delay: 0.4s;">⚙️</div>
          <h3 class="text-3xl font-bold mb-4 text-white">Soporte Técnico</h3>
          <p class="text-gray-400 mb-6 text-lg leading-relaxed">
            Instalación, optimización y recuperación de datos con tecnología avanzada.
          </p>
          <ul class="text-gray-500 space-y-3">
            <li class="flex items-start gap-3">
              <span class="text-tech-blue text-xl">✓</span>
              <span>Instalación Windows/Linux</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="text-tech-blue text-xl">✓</span>
              <span>Eliminación de virus y malware</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="text-tech-blue text-xl">✓</span>
              <span>Recuperación de datos perdidos</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="text-tech-blue text-xl">✓</span>
              <span>Optimización de rendimiento</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- Sección de Marcas -->
  <section id="marcas" class="py-32 px-6 bg-gradient-to-b from-black via-gray-900 to-black">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-20">
        <h2
          class="text-5xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-tech-cyan to-tech-purple bg-clip-text text-transparent">
          Marcas Certificadas
        </h2>
        <p class="text-xl text-gray-400">Especialistas en las principales marcas del mercado</p>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
        <div class="brand-badge flex items-center justify-center p-8 rounded-3xl">
          <span class="text-4xl font-bold text-tech-blue">HP</span>
        </div>
        <div class="brand-badge flex items-center justify-center p-8 rounded-3xl">
          <span class="text-4xl font-bold text-tech-blue">DELL</span>
        </div>
        <div class="brand-badge flex items-center justify-center p-8 rounded-3xl">
          <span class="text-4xl font-bold text-tech-blue">Lenovo</span>
        </div>
        <div class="brand-badge flex items-center justify-center p-8 rounded-3xl">
          <span class="text-4xl font-bold text-tech-blue">ASUS</span>
        </div>
        <div class="brand-badge flex items-center justify-center p-8 rounded-3xl">
          <span class="text-4xl font-bold text-tech-cyan">Samsung</span>
        </div>
        <div class="brand-badge flex items-center justify-center p-8 rounded-3xl">
          <span class="text-4xl font-bold text-tech-cyan">Xiaomi</span>
        </div>
        <div class="brand-badge flex items-center justify-center p-8 rounded-3xl">
          <span class="text-5xl">🍎</span>
        </div>
        <div class="brand-badge flex items-center justify-center p-8 rounded-3xl">
          <span class="text-3xl font-bold text-tech-cyan">Motorola</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Sección FAQ -->
  <section id="faq" class="py-32 px-6 bg-black">
    <div class="max-w-4xl mx-auto">
      <div class="text-center mb-20">
        <h2
          class="text-5xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-tech-blue to-tech-cyan bg-clip-text text-transparent">
          Preguntas Frecuentes
        </h2>
        <p class="text-xl text-gray-400">Todo lo que necesitas saber</p>
      </div>

      <div class="space-y-6">
        <div class="faq-item p-8 rounded-2xl border-2 cursor-pointer" onclick="toggleFaq(this)">
          <div class="flex justify-between items-center">
            <h3 class="text-2xl font-bold text-white pr-4">¿Cuánto tiempo tarda una reparación?</h3>
            <svg class="faq-icon w-8 h-8 text-tech-blue transition-transform flex-shrink-0" fill="none"
              stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>
          <div class="faq-answer mt-6">
            <p class="text-gray-400 text-lg leading-relaxed">
              La mayoría de reparaciones se completan en 24-48 horas. Las reparaciones complejas pueden tomar de 3 a 5
              días laborables. Te mantenemos informado constantemente del progreso mediante WhatsApp.
            </p>
          </div>
        </div>

        <div class="faq-item p-8 rounded-2xl border-2 cursor-pointer" onclick="toggleFaq(this)">
          <div class="flex justify-between items-center">
            <h3 class="text-2xl font-bold text-white pr-4">¿Ofrecen garantía en las reparaciones?</h3>
            <svg class="faq-icon w-8 h-8 text-tech-blue transition-transform flex-shrink-0" fill="none"
              stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>
          <div class="faq-answer mt-6">
            <p class="text-gray-400 text-lg leading-relaxed">
              Sí, todas nuestras reparaciones incluyen garantía de 90 días en mano de obra y piezas instaladas.
              Utilizamos únicamente repuestos originales o de calidad certificada para asegurar la durabilidad.
            </p>
          </div>
        </div>

        <div class="faq-item p-8 rounded-2xl border-2 cursor-pointer" onclick="toggleFaq(this)">
          <div class="flex justify-between items-center">
            <h3 class="text-2xl font-bold text-white pr-4">¿El diagnóstico tiene costo?</h3>
            <svg class="faq-icon w-8 h-8 text-tech-blue transition-transform flex-shrink-0" fill="none"
              stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>
          <div class="faq-answer mt-6">
            <p class="text-gray-400 text-lg leading-relaxed">
              No, el diagnóstico es completamente gratuito y sin compromiso. Evaluamos tu equipo de forma profesional y
              te proporcionamos un presupuesto detallado antes de realizar cualquier reparación.
            </p>
          </div>
        </div>

        <div class="faq-item p-8 rounded-2xl border-2 cursor-pointer" onclick="toggleFaq(this)">
          <div class="flex justify-between items-center">
            <h3 class="text-2xl font-bold text-white pr-4">¿Pueden recuperar mis datos?</h3>
            <svg class="faq-icon w-8 h-8 text-tech-blue transition-transform flex-shrink-0" fill="none"
              stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>
          <div class="faq-answer mt-6">
            <p class="text-gray-400 text-lg leading-relaxed">
              Sí, contamos con tecnología profesional de última generación para recuperación de datos de discos duros,
              SSD, memorias USB y tarjetas SD dañadas. Evaluamos cada caso específicamente y te informamos sobre la
              viabilidad de recuperación.
            </p>
          </div>
        </div>

        <div class="faq-item p-8 rounded-2xl border-2 cursor-pointer" onclick="toggleFaq(this)">
          <div class="flex justify-between items-center">
            <h3 class="text-2xl font-bold text-white pr-4">¿Atienden a domicilio?</h3>
            <svg class="faq-icon w-8 h-8 text-tech-blue transition-transform flex-shrink-0" fill="none"
              stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>
          <div class="faq-answer mt-6">
            <p class="text-gray-400 text-lg leading-relaxed">
              Sí, ofrecemos servicio a domicilio para instalaciones de software, configuraciones de red, soporte técnico
              y mantenimiento preventivo. Contáctanos para coordinar una visita en el horario que mejor te convenga.
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Sección de Contacto -->
  <section id="contacto" class="py-32 px-6 bg-gradient-to-t from-black via-gray-900 to-black">
    <div class="max-w-5xl mx-auto">
      <div class="text-center mb-16">
        <h2
          class="text-5xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-tech-cyan to-tech-purple bg-clip-text text-transparent">
          ¿Listo para Reparar tu Equipo?
        </h2>
        <p class="text-xl text-gray-400">Contáctanos ahora y recibe atención personalizada inmediata</p>
      </div>

      <div class="grid md:grid-cols-3 gap-8 mb-16">
        <div class="contact-card p-8 rounded-3xl text-center">
          <div
            class="w-20 h-20 bg-gradient-to-br from-tech-blue to-tech-cyan rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-tech-blue/30">
            <span class="text-4xl">📞</span>
          </div>
          <p class="text-sm text-gray-500 mb-2 uppercase tracking-wider">Llámanos</p>
          <p class="text-2xl font-bold text-white mb-2">+593 99 123 4567</p>
          <p class="text-gray-400 text-sm">Lun - Sáb: 9:00 - 19:00</p>
        </div>

        <div class="contact-card p-8 rounded-3xl text-center">
          <div
            class="w-20 h-20 bg-gradient-to-br from-tech-cyan to-tech-purple rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-tech-cyan/30">
            <span class="text-4xl">✉️</span>
          </div>
          <p class="text-sm text-gray-500 mb-2 uppercase tracking-wider">Escríbenos</p>
          <p class="text-2xl font-bold text-white mb-2">info@techfix.com</p>
          <p class="text-gray-400 text-sm">Respuesta en 24h</p>
        </div>

        <div class="contact-card p-8 rounded-3xl text-center">
          <div
            class="w-20 h-20 bg-gradient-to-br from-tech-purple to-tech-blue rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-tech-purple/30">
            <span class="text-4xl">📍</span>
          </div>
          <p class="text-sm text-gray-500 mb-2 uppercase tracking-wider">Visítanos</p>
          <p class="text-2xl font-bold text-white mb-2">Guayaquil</p>
          <p class="text-gray-400 text-sm">Centro de la ciudad</p>
        </div>
      </div>

      <div class="text-center">
        <button
          class="cta-button px-12 py-5 bg-gradient-to-r from-tech-blue to-tech-cyan text-black text-xl font-bold rounded-full hover:shadow-2xl hover:shadow-tech-blue/50 transition-all duration-300 transform hover:scale-105">
          <span class="relative z-10">Solicitar Diagnóstico Gratuito</span>
        </button>
        <p class="mt-6 text-gray-500 text-sm">Sin compromiso • Respuesta inmediata • Atención 24/7</p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="py-12 px-6 bg-black border-t-2 border-tech-blue/20">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-8">
        <div class="text-4xl font-bold text-tech-blue flex items-center justify-center gap-3 mb-4">
          <span class="text-5xl">⚡</span>
          TechFix
        </div>
        <p class="text-gray-400 text-lg">Tu aliado tecnológico de confianza</p>
      </div>

      <div class="flex justify-center gap-8 mb-8">
        <a href="#hero" class="text-gray-400 hover:text-tech-blue transition-colors">Inicio</a>
        <a href="#servicios" class="text-gray-400 hover:text-tech-blue transition-colors">Servicios</a>
        <a href="#marcas" class="text-gray-400 hover:text-tech-blue transition-colors">Marcas</a>
        <a href="#faq" class="text-gray-400 hover:text-tech-blue transition-colors">FAQ</a>
        <a href="#contacto" class="text-gray-400 hover:text-tech-blue transition-colors">Contacto</a>
      </div>

      <div class="text-center text-gray-500 border-t border-gray-800 pt-8">
        <p class="mb-2">&copy; 2026 TechFix. Todos los derechos reservados.</p>
        <p class="text-sm">Reparación profesional de laptops y smartphones en Guayaquil, Ecuador</p>
      </div>
    </div>
  </footer>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);

      let currentMode = 'laptops';
      let laptopTimeline, smartphoneTimeline;

      const btnLaptops = document.getElementById('btn-laptops');
      const btnSmartphones = document.getElementById('btn-smartphones');
      const sectionLaptops = document.getElementById('section-laptops');
      const sectionSmartphones = document.getElementById('section-smartphones');
      const laptopVideo = document.querySelector('#laptop-video video');
      const phoneVideo = document.querySelector('#phone-video video');

      // Configuración optimizada para videos de 8 segundos
      const VIDEO_DURATION = 8;

      // Esperar a que los videos estén cargados
      let videosLoaded = 0;
      const totalVideos = 2;

      function onVideoLoaded() {
        videosLoaded++;
        if (videosLoaded === totalVideos) {
          createLaptopAnimation();

          // Animaciones de entrada para el navbar
          gsap.from('.navbar-glass', {
            y: -100,
            opacity: 0,
            duration: 1,
            ease: "power3.out"
          });
        }
      }

      laptopVideo.addEventListener('loadeddata', onVideoLoaded);
      phoneVideo.addEventListener('loadeddata', onVideoLoaded);

      // Forzar carga si tarda mucho
      setTimeout(() => {
        if (videosLoaded < totalVideos) {
          console.log('Forzando inicio de animaciones');
          createLaptopAnimation();
        }
      }, 3000);

      // Función para crear animación de laptops
      function createLaptopAnimation() {
        if (laptopTimeline) laptopTimeline.kill();

        laptopVideo.pause();
        laptopVideo.currentTime = 0;

        laptopTimeline = gsap.timeline({
          scrollTrigger: {
            trigger: sectionLaptops,
            start: "top top",
            end: "bottom bottom",
            scrub: 1,
            onUpdate: (self) => {
              const progress = self.progress;
              laptopVideo.currentTime = Math.min(VIDEO_DURATION * progress, VIDEO_DURATION - 0.1);
            }
          }
        });

        // Timeline mejorado con 5 fases distintas
        laptopTimeline
          // FASE 1 (0-20%): Aparición del texto hero
          .fromTo('#hero-text-laptop',
            { opacity: 0, y: -50, scale: 0.8 },
            { opacity: 1, y: 0, scale: 1, duration: 1, ease: "power2.out" }
          )

          // FASE 2 (20-40%): Aparición del video
          .fromTo('#laptop-video',
            { opacity: 0, scale: 0.7, rotationY: -15 },
            { opacity: 1, scale: 1, rotationY: 0, duration: 1.2, ease: "power2.out" },
            "+=0.2"
          )

          // FASE 3 (40-60%): Desvanecimiento del texto y aparición de marcas
          .to('#hero-text-laptop',
            { opacity: 0, y: 50, duration: 0.6, ease: "power2.in" },
            "-=0.4"
          )
          .fromTo(['#brand-hp', '#brand-asus', '#brand-dell', '#brand-lenovo'],
            { opacity: 0, scale: 0.5, rotation: -10 },
            {
              opacity: 1,
              scale: 1,
              rotation: 0,
              duration: 0.8,
              stagger: 0.15,
              ease: "back.out(1.7)"
            },
            "-=0.3"
          )

          // FASE 4 (60-80%): Aparición de sistemas operativos
          .fromTo(['#os-windows', '#os-linux'],
            { opacity: 0, scale: 0.3, y: 50 },
            {
              opacity: 1,
              scale: 1,
              y: 0,
              duration: 0.8,
              stagger: 0.2,
              ease: "elastic.out(1, 0.5)"
            },
            "-=0.2"
          )

          // FASE 5 (80-100%): Zoom del video y desvanecimiento
          .to('#laptop-video', {
            scale: 1.1,
            opacity: 0.7,
            duration: 1,
            ease: "power1.inOut"
          })
          .to(['#brand-hp', '#brand-asus', '#brand-dell', '#brand-lenovo', '#os-windows', '#os-linux'], {
            opacity: 0.6,
            scale: 0.95,
            duration: 1,
            ease: "power1.inOut"
          }, "-=1");
      }

      // Función para crear animación de smartphones
      function createSmartphoneAnimation() {
        if (smartphoneTimeline) smartphoneTimeline.kill();

        phoneVideo.pause();
        phoneVideo.currentTime = 0;

        smartphoneTimeline = gsap.timeline({
          scrollTrigger: {
            trigger: sectionSmartphones,
            start: "top top",
            end: "bottom bottom",
            scrub: 1,
            onUpdate: (self) => {
              const progress = self.progress;
              phoneVideo.currentTime = Math.min(VIDEO_DURATION * progress, VIDEO_DURATION - 0.1);
            }
          }
        });

        // Timeline mejorado para smartphones
        smartphoneTimeline
          // FASE 1 (0-20%): Aparición del texto hero
          .fromTo('#hero-text-phone',
            { opacity: 0, y: -50, scale: 0.8 },
            { opacity: 1, y: 0, scale: 1, duration: 1, ease: "power2.out" }
          )

          // FASE 2 (20-40%): Aparición del video
          .fromTo('#phone-video',
            { opacity: 0, scale: 0.7, rotationX: 15 },
            { opacity: 1, scale: 1, rotationX: 0, duration: 1.2, ease: "power2.out" },
            "+=0.2"
          )

          // FASE 3 (40-60%): Desvanecimiento del texto y aparición de marcas
          .to('#hero-text-phone',
            { opacity: 0, y: 50, duration: 0.6, ease: "power2.in" },
            "-=0.4"
          )
          .fromTo(['#brand-samsung', '#brand-xiaomi', '#brand-apple', '#brand-motorola'],
            { opacity: 0, x: -50, rotation: 10 },
            {
              opacity: 1,
              x: 0,
              rotation: 0,
              duration: 0.8,
              stagger: 0.15,
              ease: "back.out(1.7)"
            },
            "-=0.3"
          )

          // FASE 4 (60-80%): Aparición de sistemas operativos
          .fromTo(['#os-android', '#os-ios'],
            { opacity: 0, scale: 0.3, rotation: -20 },
            {
              opacity: 1,
              scale: 1,
              rotation: 0,
              duration: 0.8,
              stagger: 0.2,
              ease: "elastic.out(1, 0.5)"
            },
            "-=0.2"
          )

          // FASE 5 (80-100%): Zoom y desvanecimiento
          .to('#phone-video', {
            scale: 1.15,
            opacity: 0.7,
            duration: 1,
            ease: "power1.inOut"
          })
          .to(['#brand-samsung', '#brand-xiaomi', '#brand-apple', '#brand-motorola', '#os-android', '#os-ios'], {
            opacity: 0.6,
            scale: 0.95,
            duration: 1,
            ease: "power1.inOut"
          }, "-=1");
      }

      // Función para cambiar modo
      function switchMode(mode) {
        if (mode === currentMode) return;

        currentMode = mode;

        // Actualizar botones con animación
        if (mode === 'laptops') {
          gsap.to(btnLaptops, {
            backgroundColor: '#00d4ff',
            color: '#000',
            scale: 1.05,
            duration: 0.3
          });
          gsap.to(btnSmartphones, {
            backgroundColor: 'transparent',
            color: '#fff',
            scale: 1,
            duration: 0.3
          });

          phoneVideo.pause();
          sectionSmartphones.classList.add('hidden');
          sectionLaptops.classList.remove('hidden');

          ScrollTrigger.refresh();
          createLaptopAnimation();

          gsap.to(window, {
            duration: 0.8,
            scrollTo: { y: 0 },
            ease: "power2.inOut"
          });
        } else {
          gsap.to(btnSmartphones, {
            backgroundColor: '#00fff2',
            color: '#000',
            scale: 1.05,
            duration: 0.3
          });
          gsap.to(btnLaptops, {
            backgroundColor: 'transparent',
            color: '#fff',
            scale: 1,
            duration: 0.3
          });

          laptopVideo.pause();
          sectionLaptops.classList.add('hidden');
          sectionSmartphones.classList.remove('hidden');

          ScrollTrigger.refresh();
          createSmartphoneAnimation();

          gsap.to(window, {
            duration: 0.8,
            scrollTo: { y: 0 },
            ease: "power2.inOut"
          });
        }
      }

      // Event listeners
      btnLaptops.addEventListener('click', () => switchMode('laptops'));
      btnSmartphones.addEventListener('click', () => switchMode('smartphones'));

      // Smooth scroll para links del navbar
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) {
            gsap.to(window, {
              duration: 1.2,
              scrollTo: { y: target, offsetY: 80 },
              ease: "power3.inOut"
            });
          }
        });
      });

      // Animaciones de scroll para las secciones
      gsap.utils.toArray('.service-card').forEach((card, i) => {
        gsap.from(card, {
          scrollTrigger: {
            trigger: card,
            start: "top 80%",
            end: "top 50%",
            scrub: 1
          },
          opacity: 0,
          y: 100,
          rotation: 5,
          ease: "power2.out"
        });
      });

      // Animación de las brand badges
      gsap.utils.toArray('.brand-badge').forEach((badge, i) => {
        gsap.from(badge, {
          scrollTrigger: {
            trigger: badge,
            start: "top 85%",
            end: "top 60%",
            scrub: 1
          },
          opacity: 0,
          scale: 0.5,
          rotation: 180,
          ease: "back.out(1.7)"
        });
      });
    });

    // Función para toggle FAQ
    function toggleFaq(element) {
      const answer = element.querySelector('.faq-answer');
      const icon = element.querySelector('.faq-icon');
      const isActive = answer.classList.contains('active');

      // Cerrar todos los FAQs
      document.querySelectorAll('.faq-answer').forEach(item => {
        item.classList.remove('active');
      });
      document.querySelectorAll('.faq-icon').forEach(item => {
        gsap.to(item, { rotation: 0, duration: 0.3 });
      });

      // Abrir el clickeado si no estaba activo
      if (!isActive) {
        answer.classList.add('active');
        gsap.to(icon, { rotation: 180, duration: 0.3 });
      }
    }
  </script>
</body>

</html>