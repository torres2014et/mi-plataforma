<x-guest-layout>
    <div class="mb-6 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 shadow-brand mb-5 ring-4 ring-brand-500/20">
            <x-application-logo class="w-10 h-10" />
        </div>
        <h1 class="text-3xl font-black text-white">Crea tu cuenta</h1>
        <p class="text-zinc-500 text-sm mt-1.5">Únete a la plataforma de domicilios de Ubaté</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5"
          x-data="{ rol: '{{ old('role', 'cliente') }}', showPass: false, showPassConfirm: false }">
        @csrf

        {{-- Selector de rol --}}
        <div>
            <label class="block text-sm font-semibold text-zinc-300 mb-2">¿Cómo quieres unirte?</label>
            <div class="grid grid-cols-3 gap-2">
                <label class="cursor-pointer">
                    <input type="radio" name="role" value="cliente" x-model="rol" class="sr-only">
                    <div :class="rol === 'cliente'
                            ? 'border-brand-500 bg-brand-500/15 text-brand-300'
                            : 'border-white/10 bg-zinc-800/60 text-zinc-400 hover:border-white/20'"
                         class="border-2 rounded-xl p-3 text-center transition-all duration-150">
                        <div class="text-2xl mb-1">🛒</div>
                        <div class="text-xs font-bold">Cliente</div>
                        <div class="text-xs text-zinc-500 mt-0.5">Pedir comida</div>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="role" value="domiciliario" x-model="rol" class="sr-only">
                    <div :class="rol === 'domiciliario'
                            ? 'border-brand-500 bg-brand-500/15 text-brand-300'
                            : 'border-white/10 bg-zinc-800/60 text-zinc-400 hover:border-white/20'"
                         class="border-2 rounded-xl p-3 text-center transition-all duration-150">
                        <div class="text-2xl mb-1">🛵</div>
                        <div class="text-xs font-bold">Domiciliario</div>
                        <div class="text-xs text-zinc-500 mt-0.5">Hacer entregas</div>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="role" value="vendedor" x-model="rol" class="sr-only">
                    <div :class="rol === 'vendedor'
                            ? 'border-brand-500 bg-brand-500/15 text-brand-300'
                            : 'border-white/10 bg-zinc-800/60 text-zinc-400 hover:border-white/20'"
                         class="border-2 rounded-xl p-3 text-center transition-all duration-150">
                        <div class="text-2xl mb-1">🏪</div>
                        <div class="text-xs font-bold">Vendedor</div>
                        <div class="text-xs text-zinc-500 mt-0.5">Vender productos</div>
                    </div>
                </label>
            </div>
            <x-input-error :messages="$errors->get('role')" class="mt-1.5 text-xs text-red-400" />
        </div>

        {{-- Nombre del negocio (solo vendedor) --}}
        <div x-show="rol === 'vendedor'" x-transition>
            <label for="nombre_negocio" class="block text-sm font-semibold text-zinc-300 mb-1.5">Nombre del negocio *</label>
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H3m7-10h4m-4 4h4"/>
                </svg>
                <input id="nombre_negocio" type="text" name="nombre_negocio"
                       value="{{ old('nombre_negocio') }}"
                       placeholder="Ej: El Pollo Don Luis"
                       class="input pl-11 py-3"/>
            </div>
            <x-input-error :messages="$errors->get('nombre_negocio')" class="mt-1.5 text-xs text-red-400" />
        </div>

        {{-- Nombre completo --}}
        <div>
            <label for="name" class="block text-sm font-semibold text-zinc-300 mb-1.5">Nombre completo</label>
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <input id="name" type="text" name="name"
                       value="{{ old('name') }}"
                       placeholder="Tu nombre completo"
                       class="input pl-11 py-3" required autofocus/>
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1.5 text-xs text-red-400" />
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-semibold text-zinc-300 mb-1.5">Correo electrónico</label>
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <input id="email" type="email" name="email"
                       value="{{ old('email') }}"
                       placeholder="tu@correo.com"
                       class="input pl-11 py-3" required/>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5 text-xs text-red-400" />
        </div>

        {{-- Teléfono y dirección --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="telefono" class="block text-sm font-semibold text-zinc-300 mb-1.5">
                    Teléfono <span class="text-zinc-600 font-normal">(10 díg.)</span>
                </label>
                <input id="telefono" type="tel" name="telefono"
                       value="{{ old('telefono') }}"
                       placeholder="3XX XXX XXXX"
                       maxlength="10" pattern="[0-9]{10}"
                       class="input py-3"/>
                <x-input-error :messages="$errors->get('telefono')" class="mt-1.5 text-xs text-red-400" />
            </div>
            <div>
                <label for="direccion" class="block text-sm font-semibold text-zinc-300 mb-1.5">Dirección</label>
                <input id="direccion" type="text" name="direccion"
                       value="{{ old('direccion') }}"
                       placeholder="Calle 5 #10-20"
                       class="input py-3"/>
                <x-input-error :messages="$errors->get('direccion')" class="mt-1.5 text-xs text-red-400" />
            </div>
        </div>

        {{-- Campos extra domiciliario --}}
        <div x-show="rol === 'domiciliario'" x-transition
             class="space-y-4 bg-zinc-800/50 rounded-xl p-4 border border-white/8">
            <p class="text-xs font-bold text-zinc-500 uppercase tracking-wide">Datos del domiciliario</p>

            <div>
                <label for="cedula" class="block text-sm font-semibold text-zinc-300 mb-1.5">Cédula *</label>
                <input id="cedula" type="text" name="cedula"
                       value="{{ old('cedula') }}"
                       placeholder="1.234.567.890"
                       class="input py-3"/>
                <x-input-error :messages="$errors->get('cedula')" class="mt-1.5 text-xs text-red-400" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="tipo_vehiculo" class="block text-sm font-semibold text-zinc-300 mb-1.5">Tipo de vehículo *</label>
                    <select id="tipo_vehiculo" name="tipo_vehiculo"
                            class="input py-3">
                        <option value="" class="bg-zinc-900">Selecciona...</option>
                        <option value="Moto"      class="bg-zinc-900" {{ old('tipo_vehiculo') === 'Moto'      ? 'selected' : '' }}>Moto</option>
                        <option value="Bicicleta" class="bg-zinc-900" {{ old('tipo_vehiculo') === 'Bicicleta' ? 'selected' : '' }}>Bicicleta</option>
                        <option value="Carro"     class="bg-zinc-900" {{ old('tipo_vehiculo') === 'Carro'     ? 'selected' : '' }}>Carro</option>
                        <option value="A pie"     class="bg-zinc-900" {{ old('tipo_vehiculo') === 'A pie'     ? 'selected' : '' }}>A pie</option>
                    </select>
                    <x-input-error :messages="$errors->get('tipo_vehiculo')" class="mt-1.5 text-xs text-red-400" />
                </div>
                <div>
                    <label for="placa_vehiculo" class="block text-sm font-semibold text-zinc-300 mb-1.5">Placa</label>
                    <input id="placa_vehiculo" type="text" name="placa_vehiculo"
                           value="{{ old('placa_vehiculo') }}"
                           placeholder="ABC 123"
                           class="input py-3"/>
                    <x-input-error :messages="$errors->get('placa_vehiculo')" class="mt-1.5 text-xs text-red-400" />
                </div>
            </div>
        </div>

        {{-- Contraseña --}}
        <div>
            <label for="password" class="block text-sm font-semibold text-zinc-300 mb-1.5">Contraseña</label>
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <input id="password" name="password"
                       type="password" x-bind:type="showPass ? 'text' : 'password'"
                       placeholder="Mínimo 8 caracteres"
                       class="input pl-11 pr-11 py-3" required/>
                <button type="button" @click="showPass = !showPass"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-brand-400 transition-colors p-1">
                    <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5 text-xs text-red-400" />
        </div>

        {{-- Confirmar contraseña --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-zinc-300 mb-1.5">Confirmar contraseña</label>
            <div class="relative">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <input id="password_confirmation" name="password_confirmation"
                       type="password" x-bind:type="showPassConfirm ? 'text' : 'password'"
                       placeholder="Repite tu contraseña"
                       class="input pl-11 pr-11 py-3" required/>
                <button type="button" @click="showPassConfirm = !showPassConfirm"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-500 hover:text-brand-400 transition-colors p-1">
                    <svg x-show="!showPassConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showPassConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5 text-xs text-red-400" />
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-3.5 text-sm font-bold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Crear cuenta
        </button>

        <div class="relative flex items-center gap-3">
            <div class="flex-1 h-px bg-white/8"></div>
            <span class="text-xs text-zinc-600 font-medium">¿Ya tienes cuenta?</span>
            <div class="flex-1 h-px bg-white/8"></div>
        </div>

        <a href="{{ route('login') }}"
           class="flex items-center justify-center gap-2 w-full py-3.5 border border-white/10 bg-zinc-800/50
                  text-zinc-300 text-sm font-bold rounded-xl
                  hover:border-brand-500/40 hover:text-brand-400 hover:bg-zinc-800
                  transition-all duration-200">
            Iniciar sesión
        </a>
    </form>

    <p class="text-center text-xs text-zinc-700 mt-8">
        © {{ date('Y') }} Mi Plataforma · Ubaté, Cundinamarca
    </p>
</x-guest-layout>
