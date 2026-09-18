<x-guest-layout>
<div x-data="{
        show: false,
        loading: false,
        email: '{{ old('email') }}',
        emailValid: false,
        greeting: '',
        init() {
            const h = new Date().getHours();
            this.greeting = h < 12 ? 'Buenos días' : h < 19 ? 'Buenas tardes' : 'Buenas noches';
        }
    }"
    x-init="init()"
    x-cloak>

    {{-- Header con logo y saludo --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 shadow-brand mb-5 ring-4 ring-brand-500/20">
            <x-application-logo class="w-10 h-10" />
        </div>
        <h1 class="text-3xl font-black text-white" x-text="greeting + ', bienvenido'">Bienvenido de nuevo</h1>
        <p class="text-zinc-500 text-sm mt-1.5">Ingresa a tu cuenta para continuar</p>
    </div>

    {{-- Pills de confianza --}}
    <div class="flex items-center justify-center gap-2 mb-8 flex-wrap">
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1.5 rounded-full">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            Acceso seguro
        </span>
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-400 bg-brand-500/10 border border-brand-500/20 px-3 py-1.5 rounded-full">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016"/></svg>
            Restaurantes locales
        </span>
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-400 bg-blue-500/10 border border-blue-500/20 px-3 py-1.5 rounded-full">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            Tiempo real
        </span>
    </div>

    <x-auth-session-status class="mb-5 p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl text-sm" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5" @submit="loading = true">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-semibold text-zinc-300 mb-1.5">Correo electrónico</label>
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       x-model="email"
                       @input="emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)"
                       placeholder="tu@correo.com" required autofocus
                       class="w-full pl-11 pr-11 py-3 rounded-xl text-sm bg-zinc-800/80 border border-white/10 text-zinc-100 placeholder-zinc-600
                              focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent
                              transition-all duration-200">
                {{-- Check de validación --}}
                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 transition-all duration-200"
                     x-show="emailValid" x-transition>
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-red-400" />
        </div>

        {{-- Contraseña --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="text-sm font-semibold text-zinc-300">Contraseña</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-brand-400 hover:text-brand-300 transition-colors">
                        ¿Olvidaste la contraseña?
                    </a>
                @endif
            </div>
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <input id="password" type="password" x-bind:type="show ? 'text' : 'password'" name="password"
                       placeholder="••••••••" required
                       class="w-full pl-11 pr-11 py-3 rounded-xl text-sm bg-zinc-800/80 border border-white/10 text-zinc-100 placeholder-zinc-600
                              focus:outline-none focus:ring-2 focus:ring-brand-400 focus:border-transparent
                              transition-all duration-200">
                <button type="button" @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-brand-400 transition-colors p-1">
                    <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-red-400" />
        </div>

        {{-- Recordarme --}}
        <div class="flex items-center gap-2.5">
            <input id="remember_me" type="checkbox" name="remember"
                   class="w-4 h-4 rounded bg-zinc-800 border-zinc-600 text-brand-500 focus:ring-brand-400 focus:ring-offset-gray-950">
            <label for="remember_me" class="text-sm text-zinc-400 select-none cursor-pointer">Mantener sesión iniciada</label>
        </div>

        {{-- Botón submit --}}
        <button type="submit" class="btn-primary w-full justify-center py-3.5 text-sm font-bold" :disabled="loading">
            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span x-text="loading ? 'Ingresando...' : 'Iniciar sesión'">Iniciar sesión</span>
            <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>

        {{-- Acceso rápido de prueba --}}
        <div class="p-3.5 rounded-xl border border-dashed border-brand-500/25 bg-brand-500/[0.04]">
            <p class="text-xs text-zinc-500 font-semibold mb-2.5 text-center">🧪 Acceso rápido de prueba</p>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="quickLogin('cliente@test.com')"
                        class="py-2.5 rounded-lg text-xs font-bold bg-zinc-800/80 border border-white/10 text-zinc-300 hover:border-brand-500/40 hover:text-brand-400 transition-all duration-200">
                    👤 Cliente
                </button>
                <button type="button" onclick="quickLogin('vendedor@test.com')"
                        class="py-2.5 rounded-lg text-xs font-bold bg-zinc-800/80 border border-white/10 text-zinc-300 hover:border-brand-500/40 hover:text-brand-400 transition-all duration-200">
                    🏪 Restaurante
                </button>
                <button type="button" onclick="quickLogin('domiciliario@test.com')"
                        class="py-2.5 rounded-lg text-xs font-bold bg-zinc-800/80 border border-white/10 text-zinc-300 hover:border-brand-500/40 hover:text-brand-400 transition-all duration-200">
                    🛵 Domiciliario
                </button>
                <button type="button" onclick="quickLogin('admin@test.com')"
                        class="py-2.5 rounded-lg text-xs font-bold bg-zinc-800/80 border border-white/10 text-zinc-300 hover:border-brand-500/40 hover:text-brand-400 transition-all duration-200">
                    ⚙️ Admin
                </button>
            </div>
        </div>

        {{-- Divisor --}}
        <div class="relative flex items-center gap-3">
            <div class="flex-1 h-px bg-white/8"></div>
            <span class="text-xs text-zinc-600 font-medium">¿No tienes cuenta?</span>
            <div class="flex-1 h-px bg-white/8"></div>
        </div>

        <a href="{{ route('register') }}"
           class="flex items-center justify-center gap-2 w-full py-3.5 border border-white/10 bg-zinc-800/50
                  text-zinc-300 text-sm font-bold rounded-xl
                  hover:border-brand-500/40 hover:text-brand-400 hover:bg-zinc-800
                  transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Crear cuenta gratis
        </a>
    </form>

    {{-- Footer --}}
    <p class="text-center text-xs text-zinc-700 mt-8">
        © {{ date('Y') }} Mi Plataforma · Ubaté, Cundinamarca
    </p>
</div>

<script>
    function quickLogin(email) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'password';
        document.getElementById('email').dispatchEvent(new Event('input'));
        document.querySelector('form').submit();
    }
</script>
</x-guest-layout>
