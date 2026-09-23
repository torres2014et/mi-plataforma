@php
    $restauranteActual = request()->route('restaurante');
    $chatRestauranteId = $restauranteActual?->id ?? 'null';
@endphp

<div
    x-data="chatbotWidget({{ $chatRestauranteId }})"
    x-cloak
    class="fixed bottom-5 right-5 z-50 flex flex-col items-end gap-3"
>
    {{-- Panel de chat --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="card w-[90vw] max-w-sm h-[28rem] flex flex-col overflow-hidden shadow-card-lg"
        @click.outside="open = false"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-white/[0.08] bg-gradient-to-r from-brand-600 to-brand-500">
            <div class="flex items-center gap-2 text-white font-bold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-6l-4 4v-4z" />
                </svg>
                Asistente Mi Plataforma
            </div>
            <button @click="open = false" class="text-white/80 hover:text-white transition-colors" aria-label="Cerrar chat">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Mensajes --}}
        <div x-ref="mensajesEl" class="flex-1 overflow-y-auto px-3 py-3 space-y-2.5 scrollbar-hide">
            <template x-for="(m, i) in mensajes" :key="i">
                <div :class="m.rol === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div
                        :class="m.rol === 'user'
                            ? 'bg-gradient-to-br from-brand-500 to-brand-600 text-white'
                            : 'bg-zinc-800 text-zinc-100 border border-white/[0.08]'"
                        class="max-w-[85%] rounded-2xl px-3.5 py-2 text-sm leading-snug whitespace-pre-line"
                        x-text="m.texto"
                    ></div>
                </div>
            </template>

            <div x-show="cargando" class="flex justify-start">
                <div class="bg-zinc-800 border border-white/[0.08] rounded-2xl px-3.5 py-2 text-sm text-zinc-400">
                    Escribiendo…
                </div>
            </div>
        </div>

        {{-- Input --}}
        <form @submit.prevent="enviar" class="flex items-center gap-2 px-3 py-3 border-t border-white/[0.08]">
            <input
                type="text"
                x-model="mensaje"
                placeholder="Escribe tu pregunta..."
                class="input flex-1"
                :disabled="cargando"
                maxlength="500"
            >
            <button type="submit" class="btn-primary !px-3.5 !py-2.5" :disabled="cargando || !mensaje.trim()" aria-label="Enviar mensaje">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </button>
        </form>
    </div>

    {{-- Botón flotante --}}
    <button
        @click="open = !open"
        class="w-14 h-14 rounded-full bg-gradient-to-br from-brand-500 to-brand-600 shadow-brand flex items-center justify-center text-white hover:shadow-brand-sm active:scale-95 transition-all duration-200"
        aria-label="Abrir asistente"
    >
        <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-6l-4 4v-4z" />
        </svg>
        <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>
