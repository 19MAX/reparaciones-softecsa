<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistema</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-slate-50 flex items-center justify-center h-screen antialiased">

    <div x-data="loginForm()" class="w-full max-w-sm bg-white p-8 rounded-xl shadow-lg border border-slate-100">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Bienvenido</h1>
            <p class="text-slate-500 text-sm">Ingresa tus credenciales para continuar</p>
        </div>

        <div x-show="errorMessage" x-transition
            class="mb-4 p-3 bg-red-50 text-red-600 border border-red-100 rounded-lg text-sm text-center font-medium"
            x-text="errorMessage" style="display: none;">
        </div>

        <form @submit.prevent="submitLogin" class="space-y-5">

            <div>
                <label class="block text-slate-700 text-sm font-semibold mb-2" for="cedula">Cédula</label>
                <input x-model="formData.cedula" type="text" id="cedula"
                    class="w-full px-4 py-2 border rounded-lg bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all placeholder:text-slate-400"
                    :class="errors.cedula ? 'border-red-500' : 'border-slate-200'"
                    placeholder="Ingresa tu identificación">
                <span x-show="errors.cedula" x-text="errors.cedula"
                    class="text-xs text-red-500 font-medium mt-1 block"></span>
            </div>

            <div x-data="{ showPass: false }">
                <label class="block text-slate-700 text-sm font-semibold mb-2" for="password">Contraseña</label>
                <div class="relative">
                    <input x-model="formData.password" :type="showPass ? 'text' : 'password'" id="password"
                        class="w-full px-4 py-2 border rounded-lg bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all placeholder:text-slate-400"
                        :class="errors.password ? 'border-red-500' : 'border-slate-200'" placeholder="••••••••">

                    <button type="button" @click="showPass = !showPass"
                        class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                        <svg x-show="!showPass" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <svg x-show="showPass" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5" style="display:none;">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                <span x-show="errors.password" x-text="errors.password"
                    class="text-xs text-red-500 font-medium mt-1 block"></span>
            </div>

            <button type="submit" :disabled="loading"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md shadow-blue-500/20 active:scale-95 transition-all disabled:opacity-70 disabled:cursor-wait flex justify-center items-center gap-2">

                <svg x-show="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24" style="display: none;">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>

                <span x-text="loading ? 'Validando...' : 'Iniciar Sesión'"></span>
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="#" class="text-xs text-slate-500 hover:text-blue-600 transition-colors">¿Olvidaste tu
                contraseña?</a>
        </div>
    </div>

    <script>
        function loginForm() {
            return {
                formData: {
                    cedula: '',
                    password: '',
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                loading: false,
                errorMessage: '',
                errors: {},

                async submitLogin() {
                    this.loading = true;
                    this.errorMessage = '';
                    this.errors = {};

                    try {
                        // CORRECCIÓN AQUÍ: La ruta debe coincidir exactamente con tu RouteCollection
                        // group 'auth' + ruta 'login/process' = 'auth/login/process'
                        const response = await fetch('<?= base_url('auth/login/process') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(this.formData)
                        });

                        // Verificar si la respuesta es JSON válido
                        const data = await response.json();

                        // Actualizar token CSRF siempre
                        if (data.token) {
                            this.formData['<?= csrf_token() ?>'] = data.token;
                        }

                        // Lógica de redirección más estricta
                        if (data.status === 'success') {
                            // Solo redirigir si el status es EXPLICITAMENTE success
                            window.location.href = data.redirect;
                        } else {
                            // Manejo de errores
                            if (data.errors) {
                                this.errors = data.errors; // Errores de validación (campos vacíos)
                            } else if (data.message) {
                                this.errorMessage = data.message; // Error de credenciales o estado
                            } else {
                                this.errorMessage = "Error desconocido en el servidor.";
                            }
                        }

                    } catch (error) {
                        console.error('Error:', error);
                        this.errorMessage = 'Error de conexión. Revisa tu consola (F12).';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>

</body>

</html>