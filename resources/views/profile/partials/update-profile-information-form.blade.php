<section>
    <header class="mb-6">
        <h3 class="form-section-title">Información personal</h3>
        <p class="mt-2 text-sm text-zinc-500">
            Actualiza tu nombre y correo electrónico asociado a la cuenta.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-sm font-semibold text-zinc-300 mb-1.5">Nombre completo</label>
            <input id="name" name="name" type="text"
                   value="{{ old('name', $user->name) }}"
                   class="input" required autofocus autocomplete="name"
                   placeholder="Tu nombre completo"/>
            <x-input-error class="mt-2 text-xs text-red-400" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="block text-sm font-semibold text-zinc-300 mb-1.5">Correo electrónico</label>
            <input id="email" name="email" type="email"
                   value="{{ old('email', $user->email) }}"
                   class="input" required autocomplete="username"
                   placeholder="tu@correo.com"/>
            <x-input-error class="mt-2 text-xs text-red-400" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl">
                    <p class="text-sm text-amber-400">
                        Tu correo no ha sido verificado.
                        <button form="send-verification"
                                class="underline font-semibold hover:text-amber-300 transition-colors ml-1">
                            Reenviar verificación
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1.5 text-xs text-emerald-400 font-medium">
                            Se envió un nuevo enlace de verificación a tu correo.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="btn-primary">Guardar cambios</button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                   x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-emerald-400 font-semibold flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    Guardado
                </p>
            @endif
        </div>
    </form>
</section>
