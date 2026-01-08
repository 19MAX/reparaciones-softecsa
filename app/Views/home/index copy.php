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
      filter: drop-shadow(0 0 20px rgba(0, 212, 255, 0.6));
    }
    
    .glow-cyan {
      filter: drop-shadow(0 0 20px rgba(0, 255, 242, 0.6));
    }
    
    .breathing-glow {
      animation: breathe 3s ease-in-out infinite;
    }
    
    @keyframes breathe {
      0%, 100% { opacity: 0.6; }
      50% { opacity: 1; }
    }
    
    .scroll-section {
      height: 400vh;
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
      background: radial-gradient(circle at 50% 50%, rgba(0, 212, 255, 0.1) 0%, transparent 70%);
      opacity: 0.3;
    }

    .video-container {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 80%;
      max-width: 600px;
      z-index: 5;
      opacity: 0;
    }

    .video-container video {
      width: 100%;
      height: auto;
      border-radius: 20px;
      box-shadow: 0 0 60px rgba(0, 212, 255, 0.4);
    }

    .navbar-glass {
      backdrop-filter: blur(10px);
      background: rgba(0, 0, 0, 0.8);
      border-bottom: 1px solid rgba(0, 212, 255, 0.2);
    }

    .service-card {
      background: linear-gradient(145deg, rgba(26, 26, 26, 0.8), rgba(0, 0, 0, 0.9));
      border: 1px solid rgba(0, 212, 255, 0.3);
      transition: all 0.3s ease;
    }

    .service-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 10px 40px rgba(0, 212, 255, 0.3);
      border-color: rgba(0, 212, 255, 0.6);
    }

    .faq-item {
      border-bottom: 1px solid rgba(0, 212, 255, 0.2);
      transition: all 0.3s ease;
    }

    .faq-item:hover {
      background: rgba(0, 212, 255, 0.05);
    }

    .faq-answer {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease;
    }

    .faq-answer.active {
      max-height: 500px;
    }

    @media (max-width: 768px) {
      .video-container {
        width: 90%;
      }
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar-glass fixed top-0 w-full z-50">
    <div class="max-w-7xl mx-auto px-6 py-4">
      <div class="flex items-center justify-between">
        <div class="text-2xl font-bold text-tech-blue">TechFix</div>
        <div class="hidden md:flex gap-8">
          <a href="#hero" class="text-gray-300 hover:text-tech-blue transition-colors">Inicio</a>
          <a href="#servicios" class="text-gray-300 hover:text-tech-blue transition-colors">Servicios</a>
          <a href="#marcas" class="text-gray-300 hover:text-tech-blue transition-colors">Marcas</a>
          <a href="#faq" class="text-gray-300 hover:text-tech-blue transition-colors">FAQ</a>
          <a href="#contacto" class="text-gray-300 hover:text-tech-blue transition-colors">Contacto</a>
        </div>
        <button id="menu-mobile" class="md:hidden text-tech-blue">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
    <!-- Selector de modo -->
    <div class="fixed top-24 left-1/2 -translate-x-1/2 z-40 flex gap-4 bg-gray-900/80 backdrop-blur-sm px-6 py-3 rounded-full border border-tech-blue/30">
      <button id="btn-laptops" class="px-6 py-2 rounded-full bg-tech-blue text-black font-semibold transition-all duration-300">
        1 - Laptops
      </button>
      <button id="btn-smartphones" class="px-6 py-2 rounded-full bg-transparent text-white border border-gray-600 font-semibold transition-all duration-300 hover:border-tech-cyan">
        2 - Smartphones
      </button>
    </div>

    <!-- Sección de Laptops -->
    <div id="section-laptops" class="scroll-section">
      <div class="sticky-container">
        <div class="tech-bg"></div>
        
        <!-- Video de Laptops -->
        <div id="laptop-video" class="video-container">
          <video src="<?=base_url()?>assets/videos/laptops_optimizado.mp4" muted preload="auto" playsinline></video>
        </div>

        <!-- Logos de marcas -->
        <div id="brand-hp" class="absolute" style="top: 20%; left: 15%; opacity: 0;">
          <div class="w-24 h-24 bg-gray-800 rounded-2xl flex items-center justify-center border border-tech-blue/30 glow-blue">
            <span class="text-2xl font-bold text-tech-blue">HP</span>
          </div>
        </div>

        <div id="brand-asus" class="absolute" style="top: 20%; right: 15%; opacity: 0;">
          <div class="w-24 h-24 bg-gray-800 rounded-2xl flex items-center justify-center border border-tech-blue/30 glow-blue">
            <span class="text-2xl font-bold text-tech-blue">ASUS</span>
          </div>
        </div>

        <div id="brand-dell" class="absolute" style="bottom: 25%; left: 15%; opacity: 0;">
          <div class="w-24 h-24 bg-gray-800 rounded-2xl flex items-center justify-center border border-tech-blue/30 glow-blue">
            <span class="text-2xl font-bold text-tech-blue">DELL</span>
          </div>
        </div>

        <div id="brand-lenovo" class="absolute" style="bottom: 25%; right: 15%; opacity: 0;">
          <div class="w-24 h-24 bg-gray-800 rounded-2xl flex items-center justify-center border border-tech-blue/30 glow-blue">
            <span class="text-xl font-bold text-tech-blue">Lenovo</span>
          </div>
        </div>

        <!-- Sistemas operativos -->
        <div id="os-windows" class="absolute" style="bottom: 15%; left: 35%; opacity: 0;">
          <div class="w-16 h-16 bg-gray-900 rounded-xl flex items-center justify-center border border-tech-cyan/30">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="#00fff2">
              <path d="M0 3.449L9.75 2.1v9.451H0m10.949-9.602L24 0v11.4H10.949M0 12.6h9.75v9.451L0 20.699M10.949 12.6H24V24l-12.9-1.801"/>
            </svg>
          </div>
        </div>

        <div id="os-linux" class="absolute" style="bottom: 15%; right: 35%; opacity: 0;">
          <div class="w-16 h-16 bg-gray-900 rounded-xl flex items-center justify-center border border-tech-cyan/30">
            <span class="text-2xl font-bold text-tech-cyan">🐧</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Sección de Smartphones -->
    <div id="section-smartphones" class="scroll-section hidden">
      <div class="sticky-container">
        <div class="tech-bg"></div>
        
        <!-- Video de Smartphones -->
        <div id="phone-video" class="video-container">
          <video src="<?=base_url()?>assets/videos/celulares_optimizado.mp4" muted preload="auto" playsinline></video>
        </div>

        <!-- Marcas de smartphones -->
        <div id="brand-samsung" class="absolute" style="top: 15%; left: 15%; opacity: 0;">
          <div class="w-28 h-28 bg-gray-800 rounded-2xl flex items-center justify-center border border-tech-cyan/30 glow-cyan">
            <span class="text-xl font-bold text-tech-cyan">Samsung</span>
          </div>
        </div>

        <div id="brand-xiaomi" class="absolute" style="top: 15%; right: 15%; opacity: 0;">
          <div class="w-28 h-28 bg-gray-800 rounded-2xl flex items-center justify-center border border-tech-cyan/30 glow-cyan">
            <span class="text-xl font-bold text-tech-cyan">Xiaomi</span>
          </div>
        </div>

        <div id="brand-apple" class="absolute" style="bottom: 30%; left: 15%; opacity: 0;">
          <div class="w-28 h-28 bg-gray-800 rounded-2xl flex items-center justify-center border border-tech-cyan/30 glow-cyan">
            <span class="text-3xl">🍎</span>
          </div>
        </div>

        <div id="brand-motorola" class="absolute" style="bottom: 30%; right: 15%; opacity: 0;">
          <div class="w-28 h-28 bg-gray-800 rounded-2xl flex items-center justify-center border border-tech-cyan/30 glow-cyan">
            <span class="text-lg font-bold text-tech-cyan">Motorola</span>
          </div>
        </div>

        <!-- Sistemas operativos móviles -->
        <div id="os-android" class="absolute" style="bottom: 15%; left: 30%; opacity: 0;">
          <div class="w-20 h-20 bg-gray-900 rounded-xl flex items-center justify-center border border-green-500/30">
            <span class="text-3xl">🤖</span>
          </div>
        </div>

        <div id="os-ios" class="absolute" style="bottom: 15%; right: 30%; opacity: 0;">
          <div class="w-20 h-20 bg-gray-900 rounded-xl flex items-center justify-center border border-blue-400/30">
            <span class="text-3xl">📱</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Sección de Servicios -->
  <section id="servicios" class="py-20 px-6 bg-black relative">
    <div class="absolute inset-0 bg-gradient-to-b from-transparent via-tech-blue/5 to-transparent"></div>
    <div class="max-w-7xl mx-auto relative z-10">
      <h2 class="text-4xl md:text-5xl font-bold text-center mb-4 text-tech-blue">Nuestros Servicios</h2>
      <p class="text-center text-gray-400 mb-16 max-w-2xl mx-auto">Soluciones profesionales para todos tus dispositivos tecnológicos</p>
      
      <div class="grid md:grid-cols-3 gap-8">
        <div class="service-card p-8 rounded-2xl">
          <div class="text-tech-blue text-4xl mb-4">💻</div>
          <h3 class="text-2xl font-bold mb-3 text-white">Reparación de Laptops</h3>
          <p class="text-gray-400 mb-4">Reparamos todas las marcas: HP, Dell, Lenovo, Asus y más. Desde pantallas rotas hasta actualizaciones de hardware.</p>
          <ul class="text-gray-500 space-y-2 text-sm">
            <li>• Cambio de pantallas</li>
            <li>• Actualización de RAM y SSD</li>
            <li>• Reparación de teclados</li>
            <li>• Limpieza profunda</li>
          </ul>
        </div>

        <div class="service-card p-8 rounded-2xl">
          <div class="text-tech-cyan text-4xl mb-4">📱</div>
          <h3 class="text-2xl font-bold mb-3 text-white">Reparación de Smartphones</h3>
          <p class="text-gray-400 mb-4">Servicio técnico especializado para iPhone, Samsung, Xiaomi, Motorola y todas las marcas.</p>
          <ul class="text-gray-500 space-y-2 text-sm">
            <li>• Cambio de pantallas táctiles</li>
            <li>• Reemplazo de baterías</li>
            <li>• Reparación de puertos de carga</li>
            <li>• Liberación de equipos</li>
          </ul>
        </div>

        <div class="service-card p-8 rounded-2xl">
          <div class="text-tech-blue text-4xl mb-4">⚙️</div>
          <h3 class="text-2xl font-bold mb-3 text-white">Soporte Técnico</h3>
          <p class="text-gray-400 mb-4">Instalación de software, eliminación de virus y optimización del sistema operativo.</p>
          <ul class="text-gray-500 space-y-2 text-sm">
            <li>• Instalación de Windows/Linux</li>
            <li>• Eliminación de malware</li>
            <li>• Recuperación de datos</li>
            <li>• Optimización del sistema</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- Sección de Marcas -->
  <section id="marcas" class="py-20 px-6 bg-gradient-to-b from-black to-gray-900">
    <div class="max-w-7xl mx-auto">
      <h2 class="text-4xl md:text-5xl font-bold text-center mb-4 text-tech-cyan">Trabajamos con las Mejores Marcas</h2>
      <p class="text-center text-gray-400 mb-16">Somos especialistas certificados</p>
      
      <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
        <div class="flex items-center justify-center p-6 bg-gray-800/50 rounded-xl border border-tech-blue/20 hover:border-tech-blue/50 transition-all">
          <span class="text-3xl font-bold text-tech-blue">HP</span>
        </div>
        <div class="flex items-center justify-center p-6 bg-gray-800/50 rounded-xl border border-tech-blue/20 hover:border-tech-blue/50 transition-all">
          <span class="text-3xl font-bold text-tech-blue">DELL</span>
        </div>
        <div class="flex items-center justify-center p-6 bg-gray-800/50 rounded-xl border border-tech-blue/20 hover:border-tech-blue/50 transition-all">
          <span class="text-3xl font-bold text-tech-blue">Lenovo</span>
        </div>
        <div class="flex items-center justify-center p-6 bg-gray-800/50 rounded-xl border border-tech-blue/20 hover:border-tech-blue/50 transition-all">
          <span class="text-3xl font-bold text-tech-blue">ASUS</span>
        </div>
        <div class="flex items-center justify-center p-6 bg-gray-800/50 rounded-xl border border-tech-cyan/20 hover:border-tech-cyan/50 transition-all">
          <span class="text-3xl font-bold text-tech-cyan">Samsung</span>
        </div>
        <div class="flex items-center justify-center p-6 bg-gray-800/50 rounded-xl border border-tech-cyan/20 hover:border-tech-cyan/50 transition-all">
          <span class="text-3xl font-bold text-tech-cyan">Xiaomi</span>
        </div>
        <div class="flex items-center justify-center p-6 bg-gray-800/50 rounded-xl border border-tech-cyan/20 hover:border-tech-cyan/50 transition-all">
          <span class="text-3xl">🍎</span>
        </div>
        <div class="flex items-center justify-center p-6 bg-gray-800/50 rounded-xl border border-tech-cyan/20 hover:border-tech-cyan/50 transition-all">
          <span class="text-2xl font-bold text-tech-cyan">Motorola</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Sección FAQ -->
  <section id="faq" class="py-20 px-6 bg-black">
    <div class="max-w-4xl mx-auto">
      <h2 class="text-4xl md:text-5xl font-bold text-center mb-4 text-tech-blue">Preguntas Frecuentes</h2>
      <p class="text-center text-gray-400 mb-16">Resolvemos tus dudas</p>
      
      <div class="space-y-4">
        <div class="faq-item p-6 rounded-xl bg-gray-900/50 border border-tech-blue/20 cursor-pointer" onclick="toggleFaq(this)">
          <div class="flex justify-between items-center">
            <h3 class="text-xl font-semibold text-white">¿Cuánto tiempo tarda una reparación?</h3>
            <svg class="faq-icon w-6 h-6 text-tech-blue transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>
          <div class="faq-answer mt-4">
            <p class="text-gray-400">La mayoría de reparaciones se completan en 24-48 horas. Reparaciones complejas pueden tomar de 3 a 5 días laborables. Te mantenemos informado en todo momento.</p>
          </div>
        </div>

        <div class="faq-item p-6 rounded-xl bg-gray-900/50 border border-tech-blue/20 cursor-pointer" onclick="toggleFaq(this)">
          <div class="flex justify-between items-center">
            <h3 class="text-xl font-semibold text-white">¿Ofrecen garantía en las reparaciones?</h3>
            <svg class="faq-icon w-6 h-6 text-tech-blue transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>
          <div class="faq-answer mt-4">
            <p class="text-gray-400">Sí, todas nuestras reparaciones incluyen garantía de 90 días en mano de obra y piezas instaladas. Usamos solo repuestos de calidad certificada.</p>
          </div>
        </div>

        <div class="faq-item p-6 rounded-xl bg-gray-900/50 border border-tech-blue/20 cursor-pointer" onclick="toggleFaq(this)">
          <div class="flex justify-between items-center">
            <h3 class="text-xl font-semibold text-white">¿Hacen diagnóstico gratuito?</h3>
            <svg class="faq-icon w-6 h-6 text-tech-blue transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>
          <div class="faq-answer mt-4">
            <p class="text-gray-400">Sí, el diagnóstico es completamente gratuito. Evaluamos tu equipo y te damos un presupuesto sin compromiso antes de realizar cualquier reparación.</p>
          </div>
        </div>

        <div class="faq-item p-6 rounded-xl bg-gray-900/50 border border-tech-blue/20 cursor-pointer" onclick="toggleFaq(this)">
          <div class="flex justify-between items-center">
            <h3 class="text-xl font-semibold text-white">¿Pueden recuperar mis datos?</h3>
            <svg class="faq-icon w-6 h-6 text-tech-blue transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>
          <div class="faq-answer mt-4">
            <p class="text-gray-400">Sí, contamos con herramientas profesionales para recuperación de datos de discos duros, SSD, memorias USB y tarjetas SD dañadas. Evaluamos cada caso específicamente.</p>
          </div>
        </div>

        <div class="faq-item p-6 rounded-xl bg-gray-900/50 border border-tech-blue/20 cursor-pointer" onclick="toggleFaq(this)">
          <div class="flex justify-between items-center">
            <h3 class="text-xl font-semibold text-white">¿Atienden a domicilio?</h3>
            <svg class="faq-icon w-6 h-6 text-tech-blue transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </div>
          <div class="faq-answer mt-4">
            <p class="text-gray-400">Sí, ofrecemos servicio a domicilio para instalaciones de software, configuraciones de red y soporte técnico. Contáctanos para coordinar una visita.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Sección de Contacto -->
  <section id="contacto" class="py-20 px-6 bg-gradient-to-t from-black to-gray-900">
    <div class="max-w-4xl mx-auto text-center">
      <h2 class="text-4xl md:text-5xl font-bold mb-4 text-tech-cyan">¿Listo para reparar tu equipo?</h2>
      <p class="text-gray-400 mb-8">Contáctanos y recibe atención personalizada</p>
      
      <div class="flex flex-col md:flex-row gap-6 justify-center items-center mb-12">
        <div class="flex items-center gap-3 text-gray-300">
          <div class="w-12 h-12 bg-tech-blue/20 rounded-full flex items-center justify-center">
            <span class="text-2xl">📞</span>
          </div>
          <div class="text-left">
            <p class="text-sm text-gray-500">Llámanos</p>
            <p class="font-semibold">+593 99 123 4567</p>
          </div>
        </div>

        <div class="flex items-center gap-3 text-gray-300">
          <div class="w-12 h-12 bg-tech-cyan/20 rounded-full flex items-center justify-center">
            <span class="text-2xl">✉️</span>
          </div>
          <div class="text-left">
            <p class="text-sm text-gray-500">Escríbenos</p>
            <p class="font-semibold">info@techfix.com</p>
          </div>
        </div>

        <div class="flex items-center gap-3 text-gray-300">
          <div class="w-12 h-12 bg-tech-blue/20 rounded-full flex items-center justify-center">
            <span class="text-2xl">📍</span>
          </div>
          <div class="text-left">
            <p class="text-sm text-gray-500">Visítanos</p>
            <p class="font-semibold">Guayaquil, Ecuador</p>
          </div>
        </div>
      </div>

      <button class="px-8 py-4 bg-tech-blue text-black font-bold rounded-full hover:bg-tech-cyan transition-all duration-300 transform hover:scale-105">
        Solicitar Diagnóstico Gratuito
      </button>
    </div>
  </section>

  <!-- Footer -->
  <footer class="py-8 px-6 bg-black border-t border-tech-blue/20">
    <div class="max-w-7xl mx-auto text-center text-gray-500">
      <p>&copy; 2026 TechFix. Todos los derechos reservados.</p>
      <p class="mt-2 text-sm">Reparación profesional de laptops y smartphones en Guayaquil</p>
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

      // Esperar a que los videos estén completamente cargados
      let videosLoaded = 0;
      const totalVideos = 2;

      function onVideoLoaded() {
        videosLoaded++;
        if (videosLoaded === totalVideos) {
          createLaptopAnimation();
        }
      }

      laptopVideo.addEventListener('loadedmetadata', onVideoLoaded);
      phoneVideo.addEventListener('loadedmetadata', onVideoLoaded);

      // Función para crear animación de laptops
      function createLaptopAnimation() {
        if (laptopTimeline) laptopTimeline.kill();
        
        // Asegurar que el video esté listo
        laptopVideo.pause();
        laptopVideo.currentTime = 0;
        
        const videoDuration = laptopVideo.duration || 4;
        
        laptopTimeline = gsap.timeline({
          scrollTrigger: {
            trigger: sectionLaptops,
            start: "top top",
            end: "bottom bottom",
            scrub: 0.5,
            onUpdate: (self) => {
              // Control preciso del video basado en el progreso del scroll
              laptopVideo.currentTime = videoDuration * self.progress;
            }
          }
        });

        // 0-25%: Aparición de video
        laptopTimeline
          .fromTo('#laptop-video', 
            { opacity: 0, scale: 0.8 },
            { opacity: 1, scale: 1, duration: 1, ease: "power2.out" }
          )
          // 25-50%: Aparición de marcas
          .fromTo(['#brand-hp', '#brand-asus', '#brand-dell', '#brand-lenovo'],
            { opacity: 0, y: 30 },
            { opacity: 1, y: 0, duration: 1, stagger: 0.15, ease: "power2.out" }
          )
          // 50-75%: Aparición de sistemas operativos
          .fromTo(['#os-windows', '#os-linux'],
            { opacity: 0, scale: 0.5 },
            { opacity: 1, scale: 1, duration: 0.8, stagger: 0.2, ease: "back.out(1.4)" }
          )
          // 75-100%: Estado estable
          .to('#laptop-video', {
            duration: 1,
            ease: "none"
          });
      }

      // Función para crear animación de smartphones
      function createSmartphoneAnimation() {
        if (smartphoneTimeline) smartphoneTimeline.kill();
        
        // Asegurar que el video esté listo
        phoneVideo.pause();
        phoneVideo.currentTime = 0;
        
        const videoDuration = phoneVideo.duration || 4;
        
        smartphoneTimeline = gsap.timeline({
          scrollTrigger: {
            trigger: sectionSmartphones,
            start: "top top",
            end: "bottom bottom",
            scrub: 0.5,
            onUpdate: (self) => {
              // Control preciso del video basado en el progreso del scroll
              phoneVideo.currentTime = videoDuration * self.progress;
            }
          }
        });

        // 0-25%: Aparición de video
        smartphoneTimeline
          .fromTo('#phone-video',
            { opacity: 0, scale: 0.8, y: 20 },
            { opacity: 1, scale: 1, y: 0, duration: 1, ease: "power2.out" }
          )
          // 25-50%: Aparición de marcas
          .fromTo(['#brand-samsung', '#brand-xiaomi', '#brand-apple', '#brand-motorola'],
            { opacity: 0, x: -30 },
            { opacity: 1, x: 0, duration: 1, stagger: 0.15, ease: "power2.out" }
          )
          // 50-75%: Aparición de sistemas operativos
          .fromTo(['#os-android', '#os-ios'],
            { opacity: 0, scale: 0.5, rotation: -15 },
            { opacity: 1, scale: 1, rotation: 0, duration: 0.8, stagger: 0.2, ease: "back.out(1.4)" }
          )
          // 75-100%: Estado estable
          .to('#phone-video', {
            duration: 1,
            ease: "none"
          });
      }

      // Función para cambiar modo
      function switchMode(mode) {
        if (mode === currentMode) return;
        
        currentMode = mode;
        
        // Actualizar botones
        if (mode === 'laptops') {
          btnLaptops.className = 'px-6 py-2 rounded-full bg-tech-blue text-black font-semibold transition-all duration-300';
          btnSmartphones.className = 'px-6 py-2 rounded-full bg-transparent text-white border border-gray-600 font-semibold transition-all duration-300 hover:border-tech-cyan';
          
          phoneVideo.pause();
          sectionSmartphones.classList.add('hidden');
          sectionLaptops.classList.remove('hidden');
          
          ScrollTrigger.refresh();
          createLaptopAnimation();
          window.scrollTo(0, 0);
        } else {
          btnSmartphones.className = 'px-6 py-2 rounded-full bg-tech-cyan text-black font-semibold transition-all duration-300';
          btnLaptops.className = 'px-6 py-2 rounded-full bg-transparent text-white border border-gray-600 font-semibold transition-all duration-300 hover:border-tech-blue';
          
          laptopVideo.pause();
          sectionLaptops.classList.add('hidden');
          sectionSmartphones.classList.remove('hidden');
          
          ScrollTrigger.refresh();
          createSmartphoneAnimation();
          window.scrollTo(0, 0);
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
              duration: 1,
              scrollTo: { y: target, offsetY: 80 },
              ease: "power2.inOut"
            });
          }
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
        item.style.transform = 'rotate(0deg)';
      });
      
      // Abrir el clickeado si no estaba activo
      if (!isActive) {
        answer.classList.add('active');
        icon.style.transform = 'rotate(180deg)';
      }
    }
  </script>
</body>
</html>