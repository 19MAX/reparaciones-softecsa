<!doctype html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/gsap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.14.1/dist/ScrollTrigger.min.js"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brand: {
              50: '#e6f9ff',
              100: '#ccf3ff',
              200: '#99e7ff',
              300: '#66dbff',
              400: '#33cfff',
              500: '#00d4ff',
              600: '#00a3cc',
              700: '#007a99',
              800: '#005266',
              900: '#002933',
            }
          }
        }
      }
    }
  </script>
</head>

<body class="bg-black text-white overflow-x-hidden">

  <!-- Loading Screen -->
  <div id="loading-screen" class="fixed inset-0 z-[100] bg-black flex flex-col items-center justify-center">
    <h1 class="text-6xl font-bold mb-8 text-brand-500">SOFTEC</h1>
    <div class="w-80 h-2 bg-gray-800 rounded-full overflow-hidden">
      <div id="loading-bar" class="h-full bg-gradient-to-r from-brand-500 to-brand-300 w-0 transition-all duration-300">
      </div>
    </div>
    <p id="loading-text" class="mt-4 text-gray-400 text-lg">Cargando 0%</p>
  </div>

  <!-- Navbar -->
  <nav class="fixed top-0 w-full z-50 bg-black/80 backdrop-blur-md border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
      <div class="text-2xl font-bold text-brand-500">SOFTEC</div>
      <div class="hidden md:flex items-center gap-8">
        <a href="#hero" class="text-gray-300 hover:text-brand-500 transition-colors">Inicio</a>
        <a href="#about" class="text-gray-300 hover:text-brand-500 transition-colors">Nosotros</a>
        <a href="#services" class="text-gray-300 hover:text-brand-500 transition-colors">Servicios</a>
        <a href="#repairs" class="text-gray-300 hover:text-brand-500 transition-colors">Reparaciones</a>
        <a href="#reviews" class="text-gray-300 hover:text-brand-500 transition-colors">Reseñas</a>
        <a href="#faq" class="text-gray-300 hover:text-brand-500 transition-colors">FAQ</a>
        <a href="#contact" class="text-gray-300 hover:text-brand-500 transition-colors">Contacto</a>
        <button id="theme-toggle" class="p-2 rounded-full bg-gray-800 hover:bg-gray-700 transition-colors">
          <svg id="theme-icon-dark" class="w-5 h-5 text-brand-500" fill="currentColor" viewBox="0 0 20 20">
            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
          </svg>
          <svg id="theme-icon-light" class="w-5 h-5 text-brand-500 hidden" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"
              clip-rule="evenodd"></path>
          </svg>
        </button>
      </div>
      <button id="mobile-menu" class="md:hidden text-brand-500">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
    </div>
  </nav>

  <!-- Hero Section with Parallax -->
  <section id="hero" class="relative h-[500vh]">
    <div class="sticky top-0 h-screen overflow-hidden">
      <!-- Video Background -->
      <div class="absolute inset-0 z-0">
        <video id="hero-video" class="w-full h-full object-cover" muted playsinline preload="auto">
          <source src="<?= base_url() ?>assets/videos/laptops_optimizado.mp4" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/50 to-transparent"></div>
      </div>

      <!-- Hero Content -->
      <div class="relative z-10 h-full flex items-center">
        <div class="max-w-7xl mx-auto px-6 w-full flex items-center justify-between">

          <!-- Left Side - Text Content -->
          <div class="max-w-2xl">
            <div id="hero-content" class="space-y-6">
              <h1 id="variant-name" class="text-7xl md:text-8xl font-bold uppercase tracking-tight">HP</h1>
              <p id="variant-subtitle" class="text-2xl md:text-3xl text-gray-300 font-light">LAPTOPS</p>
              <p id="variant-description" class="text-lg text-gray-400 max-w-xl leading-relaxed">
                Reparación profesional y certificada para laptops HP. Técnicos especializados con más de 10 años de
                experiencia.
              </p>
              <div class="flex gap-4 pt-4">
                <button
                  class="px-8 py-4 rounded-full border-2 border-white text-white font-semibold hover:bg-white hover:text-black transition-all duration-300">
                  AGENDAR
                </button>
                <button
                  class="px-8 py-4 rounded-full bg-brand-500 text-black font-semibold hover:bg-brand-400 transition-all duration-300">
                  COTIZAR
                </button>
              </div>
            </div>
          </div>

          <!-- Right Side - Variant Navigation -->
          <div class="hidden lg:flex flex-col items-center gap-8">
            <div id="variant-index" class="text-8xl font-bold text-brand-500">01</div>
            <div class="flex flex-col items-center gap-4">
              <button id="prev-variant"
                class="group flex flex-col items-center gap-2 hover:text-brand-500 transition-colors">
                <span class="text-xs uppercase tracking-widest text-gray-500 group-hover:text-brand-500">PREV</span>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                </svg>
              </button>
              <div class="h-24 w-px bg-gray-700"></div>
              <button id="next-variant"
                class="group flex flex-col items-center gap-2 hover:text-brand-500 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
                <span class="text-xs uppercase tracking-widest text-gray-500 group-hover:text-brand-500">NEXT</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Social Media Icons - Bottom Center -->
      <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex gap-6">
        <a href="#" class="text-gray-400 hover:text-brand-500 transition-colors">
          <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
            <path
              d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
          </svg>
        </a>
        <a href="#" class="text-gray-400 hover:text-brand-500 transition-colors">
          <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
            <path
              d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
          </svg>
        </a>
        <a href="#" class="text-gray-400 hover:text-brand-500 transition-colors">
          <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
            <path
              d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z" />
          </svg>
        </a>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section id="about" class="py-32 px-6 bg-black">
    <div class="max-w-7xl mx-auto">
      <div class="grid md:grid-cols-2 gap-16 items-center">
        <div>
          <h2 class="text-5xl font-bold mb-6 text-brand-500">Sobre Nosotros</h2>
          <p class="text-xl text-gray-400 mb-6 leading-relaxed">
            Softec es líder en reparación de dispositivos tecnológicos en Guaranda con más de 15 años de experiencia
            en el mercado.
          </p>
          <p class="text-lg text-gray-500 leading-relaxed">
            Nuestro equipo de técnicos certificados se especializa en reparaciones de laptops, smartphones, tablets y
            más. Utilizamos repuestos originales y ofrecemos garantía en todos nuestros servicios.
          </p>
        </div>
        <div class="grid grid-cols-2 gap-6">
          <div class="bg-gray-900 p-8 rounded-2xl border border-gray-800">
            <div class="text-4xl font-bold text-brand-500 mb-2">15+</div>
            <div class="text-gray-400">Años de Experiencia</div>
          </div>
          <div class="bg-gray-900 p-8 rounded-2xl border border-gray-800">
            <div class="text-4xl font-bold text-brand-500 mb-2">50K+</div>
            <div class="text-gray-400">Reparaciones</div>
          </div>
          <div class="bg-gray-900 p-8 rounded-2xl border border-gray-800">
            <div class="text-4xl font-bold text-brand-500 mb-2">98%</div>
            <div class="text-gray-400">Satisfacción</div>
          </div>
          <div class="bg-gray-900 p-8 rounded-2xl border border-gray-800">
            <div class="text-4xl font-bold text-brand-500 mb-2">24/7</div>
            <div class="text-gray-400">Atención</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Services Section -->
  <section id="services" class="py-32 px-6 bg-gray-950">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-16">
        <h2 class="text-5xl font-bold mb-4 text-brand-500">Nuestros Servicios</h2>
        <p class="text-xl text-gray-400">Soluciones completas para tus dispositivos</p>
      </div>
      <div class="grid md:grid-cols-3 gap-8">
        <div
          class="bg-black p-10 rounded-2xl border border-gray-800 hover:border-brand-500 transition-all duration-300 group">
          <div class="text-5xl mb-6">💻</div>
          <h3 class="text-2xl font-bold mb-4 group-hover:text-brand-500 transition-colors">Reparación de Laptops</h3>
          <ul class="space-y-3 text-gray-400">
            <li class="flex items-start gap-2">
              <span class="text-brand-500 mt-1">✓</span>
              <span>Cambio de pantallas</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-brand-500 mt-1">✓</span>
              <span>Upgrade de RAM y SSD</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-brand-500 mt-1">✓</span>
              <span>Reparación de teclados</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-brand-500 mt-1">✓</span>
              <span>Limpieza profunda</span>
            </li>
          </ul>
        </div>
        <div
          class="bg-black p-10 rounded-2xl border border-gray-800 hover:border-brand-500 transition-all duration-300 group">
          <div class="text-5xl mb-6">📱</div>
          <h3 class="text-2xl font-bold mb-4 group-hover:text-brand-500 transition-colors">Reparación de Smartphones
          </h3>
          <ul class="space-y-3 text-gray-400">
            <li class="flex items-start gap-2">
              <span class="text-brand-500 mt-1">✓</span>
              <span>Cambio de pantallas táctiles</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-brand-500 mt-1">✓</span>
              <span>Reemplazo de baterías</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-brand-500 mt-1">✓</span>
              <span>Reparación de cámaras</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-brand-500 mt-1">✓</span>
              <span>Liberación de equipos</span>
            </li>
          </ul>
        </div>
        <div
          class="bg-black p-10 rounded-2xl border border-gray-800 hover:border-brand-500 transition-all duration-300 group">
          <div class="text-5xl mb-6">🖨️</div>
          <h3 class="text-2xl font-bold mb-4 group-hover:text-brand-500 transition-colors">Reparación de Impresoras</h3>
          <ul class="space-y-3 text-gray-400">
            <li class="flex items-start gap-2">
              <span class="text-brand-500 mt-1">✓</span>
              <span>Mantenimiento preventivo</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-brand-500 mt-1">✓</span>
              <span>Reparación de atascos</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-brand-500 mt-1">✓</span>
              <span>Recarga de cartuchos</span>
            </li>
            <li class="flex items-start gap-2">
              <span class="text-brand-500 mt-1">✓</span>
              <span>Configuración de red</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- Repairs Section -->
  <section id="repairs" class="py-32 px-6 bg-black">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-16">
        <h2 class="text-5xl font-bold mb-4 text-brand-500">Tipos de Reparación</h2>
        <p class="text-xl text-gray-400">Especialistas en todas las marcas</p>
      </div>
      <div class="grid md:grid-cols-4 gap-6">
        <div class="bg-gray-900 p-6 rounded-xl border border-gray-800 hover:border-brand-500 transition-all">
          <div class="text-3xl font-bold text-brand-500 mb-2">HP</div>
          <p class="text-gray-400 text-sm">Laptops y computadoras</p>
        </div>
        <div class="bg-gray-900 p-6 rounded-xl border border-gray-800 hover:border-brand-500 transition-all">
          <div class="text-3xl font-bold text-brand-500 mb-2">DELL</div>
          <p class="text-gray-400 text-sm">Equipos empresariales</p>
        </div>
        <div class="bg-gray-900 p-6 rounded-xl border border-gray-800 hover:border-brand-500 transition-all">
          <div class="text-3xl font-bold text-brand-500 mb-2">ASUS</div>
          <p class="text-gray-400 text-sm">Gaming y ultrabooks</p>
        </div>
        <div class="bg-gray-900 p-6 rounded-xl border border-gray-800 hover:border-brand-500 transition-all">
          <div class="text-3xl font-bold text-brand-500 mb-2">Lenovo</div>
          <p class="text-gray-400 text-sm">ThinkPad y IdeaPad</p>
        </div>
        <div class="bg-gray-900 p-6 rounded-xl border border-gray-800 hover:border-brand-500 transition-all">
          <div class="text-3xl font-bold text-brand-500 mb-2">Samsung</div>
          <p class="text-gray-400 text-sm">Smartphones Galaxy</p>
        </div>
        <div class="bg-gray-900 p-6 rounded-xl border border-gray-800 hover:border-brand-500 transition-all">
          <div class="text-3xl font-bold text-brand-500 mb-2">iPhone</div>
          <p class="text-gray-400 text-sm">Todos los modelos</p>
        </div>
        <div class="bg-gray-900 p-6 rounded-xl border border-gray-800 hover:border-brand-500 transition-all">
          <div class="text-3xl font-bold text-brand-500 mb-2">Xiaomi</div>
          <p class="text-gray-400 text-sm">Redmi y Mi series</p>
        </div>
        <div class="bg-gray-900 p-6 rounded-xl border border-gray-800 hover:border-brand-500 transition-all">
          <div class="text-3xl font-bold text-brand-500 mb-2">Motorola</div>
          <p class="text-gray-400 text-sm">Moto G y Edge</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Reviews Section -->
  <section id="reviews" class="py-32 px-6 bg-gray-950">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-16">
        <h2 class="text-5xl font-bold mb-4 text-brand-500">Lo que dicen nuestros clientes</h2>
        <p class="text-xl text-gray-400">Miles de clientes satisfechos</p>
      </div>
      <div class="grid md:grid-cols-3 gap-8">
        <div class="bg-black p-8 rounded-2xl border border-gray-800">
          <div class="flex gap-1 mb-4">
            <span class="text-yellow-400">★</span>
            <span class="text-yellow-400">★</span>
            <span class="text-yellow-400">★</span>
            <span class="text-yellow-400">★</span>
            <span class="text-yellow-400">★</span>
          </div>
          <p class="text-gray-400 mb-6 italic">"Excelente servicio. Repararon mi laptop HP en menos de 24 horas. Muy
            profesionales y el precio justo."</p>
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-brand-500 flex items-center justify-center font-bold">MR</div>
            <div>
              <div class="font-semibold">María Rodríguez</div>
              <div class="text-sm text-gray-500">Guayaquil</div>
            </div>
          </div>
        </div>
        <div class="bg-black p-8 rounded-2xl border border-gray-800">
          <div class="flex gap-1 mb-4">
            <span class="text-yellow-400">★</span>
            <span class="text-yellow-400">★</span>
            <span class="text-yellow-400">★</span>
            <span class="text-yellow-400">★</span>
            <span class="text-yellow-400">★</span>
          </div>
          <p class="text-gray-400 mb-6 italic">"Mi iPhone quedó como nuevo después del cambio de pantalla. Garantía
            incluida y repuestos originales."</p>
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-brand-500 flex items-center justify-center font-bold">CS</div>
            <div>
              <div class="font-semibold">Carlos Sánchez</div>
              <div class="text-sm text-gray-500">Guayaquil</div>
            </div>
          </div>
        </div>
        <div class="bg-black p-8 rounded-2xl border border-gray-800">
          <div class="flex gap-1 mb-4">
            <span class="text-yellow-400">★</span>
            <span class="text-yellow-400">★</span>
            <span class="text-yellow-400">★</span>
            <span class="text-yellow-400">★</span>
            <span class="text-yellow-400">★</span>
          </div>
          <p class="text-gray-400 mb-6 italic">"Recuperaron todos mis datos de un disco duro que creía perdido.
            Increíble servicio técnico."</p>
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-brand-500 flex items-center justify-center font-bold">LM</div>
            <div>
              <div class="font-semibold">Laura Martínez</div>
              <div class="text-sm text-gray-500">Guayaquil</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ Section -->
  <section id="faq" class="py-32 px-6 bg-black">
    <div class="max-w-4xl mx-auto">
      <div class="text-center mb-16">
        <h2 class="text-5xl font-bold mb-4 text-brand-500">Preguntas Frecuentes</h2>
        <p class="text-xl text-gray-400">Resolvemos tus dudas</p>
      </div>
      <div class="space-y-4">
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
          <button class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-800 transition-colors"
            onclick="toggleFaq(this)">
            <span class="text-xl font-semibold">¿Cuánto tiempo tarda una reparación?</span>
            <svg class="w-6 h-6 transform transition-transform faq-icon" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <div class="faq-content hidden px-6 pb-6">
            <p class="text-gray-400">La mayoría de reparaciones se completan en 24-48 horas. Reparaciones complejas
              pueden tomar 3-5 días. Te mantenemos informado en todo momento.</p>
          </div>
        </div>
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
          <button class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-800 transition-colors"
            onclick="toggleFaq(this)">
            <span class="text-xl font-semibold">¿Ofrecen garantía?</span>
            <svg class="w-6 h-6 transform transition-transform faq-icon" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <div class="faq-content hidden px-6 pb-6">
            <p class="text-gray-400">Sí, todas nuestras reparaciones incluyen garantía de 90 días en mano de obra y
              repuestos instalados.</p>
          </div>
        </div>
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
          <button class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-800 transition-colors"
            onclick="toggleFaq(this)">
            <span class="text-xl font-semibold">¿El diagnóstico tiene costo?</span>
            <svg class="w-6 h-6 transform transition-transform faq-icon" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <div class="faq-content hidden px-6 pb-6">
            <p class="text-gray-400">No, el diagnóstico es completamente gratuito. Evaluamos tu equipo y te damos un
              presupuesto sin compromiso.</p>
          </div>
        </div>
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
          <button class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-800 transition-colors"
            onclick="toggleFaq(this)">
            <span class="text-xl font-semibold">¿Usan repuestos originales?</span>
            <svg class="w-6 h-6 transform transition-transform faq-icon" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <div class="faq-content hidden px-6 pb-6">
            <p class="text-gray-400">Sí, utilizamos únicamente repuestos originales o de calidad certificada para
              garantizar durabilidad.</p>
          </div>
        </div>
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
          <button class="w-full p-6 text-left flex justify-between items-center hover:bg-gray-800 transition-colors"
            onclick="toggleFaq(this)">
            <span class="text-xl font-semibold">¿Atienden a domicilio?</span>
            <svg class="w-6 h-6 transform transition-transform faq-icon" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
          </button>
          <div class="faq-content hidden px-6 pb-6">
            <p class="text-gray-400">Sí, ofrecemos servicio a domicilio para instalaciones y soporte técnico.
              Contáctanos para coordinar.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="py-32 px-6 bg-gray-950">
    <div class="max-w-7xl mx-auto">
      <div class="text-center mb-16">
        <h2 class="text-5xl font-bold mb-4 text-brand-500">Contáctanos</h2>
        <p class="text-xl text-gray-400">Estamos listos para ayudarte</p>
      </div>
      <div class="grid md:grid-cols-2 gap-12">
        <div class="space-y-8">
          <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-full bg-brand-500/20 flex items-center justify-center flex-shrink-0">
              <svg class="w-7 h-7 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                </path>
              </svg>
            </div>
            <div>
              <h3 class="text-xl font-semibold mb-2">Teléfono</h3>
              <p class="text-gray-400">+593 99 123 4567</p>
              <p class="text-gray-500 text-sm">Lun - Sáb: 9:00 - 19:00</p>
            </div>
          </div>
          <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-full bg-brand-500/20 flex items-center justify-center flex-shrink-0">
              <svg class="w-7 h-7 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                </path>
              </svg>
            </div>
            <div>
              <h3 class="text-xl font-semibold mb-2">Email</h3>
              <p class="text-gray-400">info@softecsa.com</p>
              <p class="text-gray-500 text-sm">Respuesta en 24 horas</p>
            </div>
          </div>
          <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-full bg-brand-500/20 flex items-center justify-center flex-shrink-0">
              <svg class="w-7 h-7 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-xl font-semibold mb-2">Ubicación</h3>
              <p class="text-gray-400">Guayaquil, Ecuador</p>
              <p class="text-gray-500 text-sm">Centro de la ciudad</p>
            </div>
          </div>
        </div>
        <div class="bg-black p-8 rounded-2xl border border-gray-800">
          <form class="space-y-6">
            <div>
              <label class="block text-sm font-medium mb-2">Nombre</label>
              <input type="text"
                class="w-full px-4 py-3 bg-gray-900 border border-gray-800 rounded-lg focus:outline-none focus:border-brand-500 transition-colors"
                placeholder="Tu nombre completo">
            </div>
            <div>
              <label class="block text-sm font-medium mb-2">Email</label>
              <input type="email"
                class="w-full px-4 py-3 bg-gray-900 border border-gray-800 rounded-lg focus:outline-none focus:border-brand-500 transition-colors"
                placeholder="tu@email.com">
            </div>
            <div>
              <label class="block text-sm font-medium mb-2">Mensaje</label>
              <textarea rows="4"
                class="w-full px-4 py-3 bg-gray-900 border border-gray-800 rounded-lg focus:outline-none focus:border-brand-500 transition-colors"
                placeholder="¿En qué podemos ayudarte?"></textarea>
            </div>
            <button type="submit"
              class="w-full px-6 py-4 bg-brand-500 text-black font-semibold rounded-lg hover:bg-brand-400 transition-colors">
              Enviar Mensaje
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="py-12 px-6 bg-black border-t border-gray-800">
    <div class="max-w-7xl mx-auto text-center">
      <div class="text-3xl font-bold text-brand-500 mb-4">SOFTEC</div>
      <p class="text-gray-400 mb-6">Reparación profesional de dispositivos tecnológicos</p>
      <div class="flex justify-center gap-6 mb-8">
        <a href="#hero" class="text-gray-500 hover:text-brand-500 transition-colors">Inicio</a>
        <a href="#about" class="text-gray-500 hover:text-brand-500 transition-colors">Nosotros</a>
        <a href="#services" class="text-gray-500 hover:text-brand-500 transition-colors">Servicios</a>
        <a href="#contact" class="text-gray-500 hover:text-brand-500 transition-colors">Contacto</a>
      </div>
      <p class="text-gray-600 text-sm">&copy; 2026 SOFTEC. Todos los derechos reservados.</p>
    </div>
  </footer>

  <script>
    // Configuración de variantes
    const variants = [
      {
        name: 'HP',
        subtitle: 'LAPTOPS',
        description: 'Reparación profesional y certificada para laptops HP. Técnicos especializados con más de 10 años de experiencia.',
        video: '<?= base_url() ?>assets/videos/laptops_optimizado.mp4'
      },
      {
        name: 'SAMSUNG',
        subtitle: 'SMARTPHONES',
        description: 'Servicio técnico especializado para dispositivos Samsung Galaxy. Repuestos originales y garantía extendida.',
        video: '<?= base_url() ?>assets/videos/celulares_optimizado.mp4'
      },
    ];

    let currentVariant = 0;
    let isChangingVariant = false;
    const VIDEO_DURATION = 4; // Duración en segundos

    // Elementos DOM
    const loadingScreen = document.getElementById('loading-screen');
    const loadingBar = document.getElementById('loading-bar');
    const loadingText = document.getElementById('loading-text');
    const heroVideo = document.getElementById('hero-video');
    const variantName = document.getElementById('variant-name');
    const variantSubtitle = document.getElementById('variant-subtitle');
    const variantDescription = document.getElementById('variant-description');
    const variantIndex = document.getElementById('variant-index');
    const heroContent = document.getElementById('hero-content');

    // Simulación de carga
    let loadProgress = 0;
    const loadInterval = setInterval(() => {
      loadProgress += Math.random() * 15;
      if (loadProgress >= 100) {
        loadProgress = 100;
        clearInterval(loadInterval);
        setTimeout(() => {
          loadingScreen.style.opacity = '20px';
          setTimeout(() => {
            loadingScreen.style.display = 'none';
            initParallax();
          }, 500);
        }, 300);
      }
      loadingBar.style.width = loadProgress + '%';
      loadingText.textContent = `Cargando ${Math.floor(loadProgress)}%`;
    }, 100);

    // Parallax con GSAP
    function initParallax() {
      gsap.registerPlugin(ScrollTrigger);

      // Pausar video inicialmente
      heroVideo.pause();
      heroVideo.currentTime = 0;

      // Crear timeline de parallax
      const tl = gsap.timeline({
        scrollTrigger: {
          trigger: '#hero',
          start: 'top top',
          end: 'bottom bottom',
          scrub: 1,
          onUpdate: (self) => {
            // Controlar video basado en scroll
            const progress = self.progress;
            heroVideo.currentTime = VIDEO_DURATION * progress;
          }
        }
      });

      // Animaciones de contenido
      tl.fromTo(heroContent,
        { opacity: 1, y: 0 },
        { opacity: 0, y: -50, duration: 0.3 }
      )
        .to(heroContent, { opacity: 1, y: 0, duration: 0.3 }, 0.7);
    }

    // Cambiar variante
    function changeVariant(direction) {
      if (isChangingVariant) return;
      isChangingVariant = true;

      // Calcular nueva variante
      if (direction === 'next') {
        currentVariant = (currentVariant + 1) % variants.length;
      } else {
        currentVariant = (currentVariant - 1 + variants.length) % variants.length;
      }

      const variant = variants[currentVariant];

      // Animación de salida
      gsap.to(heroContent, {
        opacity: 0,
        x: direction === 'next' ? -50 : 50,
        duration: 0.3,
        onComplete: () => {
          // Actualizar contenido
          variantName.textContent = variant.name;
          variantSubtitle.textContent = variant.subtitle;
          variantDescription.textContent = variant.description;
          variantIndex.textContent = String(currentVariant + 1).padStart(2, '0');

          // Cambiar video
          heroVideo.src = variant.video;
          heroVideo.load();
          heroVideo.currentTime = 0;

          // Reiniciar ScrollTrigger
          ScrollTrigger.refresh();

          // Animación de entrada
          gsap.fromTo(heroContent,
            { opacity: 0, x: direction === 'next' ? 50 : -50 },
            {
              opacity: 1,
              x: 0,
              duration: 0.3,
              onComplete: () => {
                isChangingVariant = false;
              }
            }
          );
        }
      });
    }

    // Event listeners para navegación de variantes
    document.getElementById('prev-variant').addEventListener('click', () => changeVariant('prev'));
    document.getElementById('next-variant').addEventListener('click', () => changeVariant('next'));

    // Toggle tema
    let isDarkMode = true;
    const themeToggle = document.getElementById('theme-toggle');
    const themeIconDark = document.getElementById('theme-icon-dark');
    const themeIconLight = document.getElementById('theme-icon-light');

    themeToggle.addEventListener('click', () => {
      isDarkMode = !isDarkMode;

      if (isDarkMode) {
        document.body.classList.remove('bg-white', 'text-black');
        document.body.classList.add('bg-black', 'text-white');
        themeIconDark.classList.remove('hidden');
        themeIconLight.classList.add('hidden');
      } else {
        document.body.classList.remove('bg-black', 'text-white');
        document.body.classList.add('bg-white', 'text-black');
        themeIconDark.classList.add('hidden');
        themeIconLight.classList.remove('hidden');
      }
    });

    // Toggle FAQ
    function toggleFaq(button) {
      const content = button.nextElementSibling;
      const icon = button.querySelector('.faq-icon');
      const isOpen = !content.classList.contains('hidden');

      // Cerrar todos los FAQs
      document.querySelectorAll('.faq-content').forEach(item => {
        item.classList.add('hidden');
      });
      document.querySelectorAll('.faq-icon').forEach(item => {
        item.style.transform = 'rotate(0deg)';
      });

      // Abrir el clickeado si no estaba abierto
      if (!isOpen) {
        content.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
      }
    }

    // Smooth scroll para navegación
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });

    // Animaciones de scroll para secciones
    gsap.utils.toArray('section:not(#hero)').forEach(section => {
      gsap.from(section.children, {
        scrollTrigger: {
          trigger: section,
          start: 'top 80%',
          end: 'top 20%',
          toggleActions: 'play none none reverse',
        },
        opacity: 0,
        y: 50,
        duration: 0.8,
        stagger: 0.2
      });
    });
  </script>
</body>

</html>