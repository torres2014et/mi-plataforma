<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-black text-white text-xl">Restaurantes en Ubaté</h2>
            <p class="text-sm text-zinc-500 mt-0.5">Pide a los mejores negocios locales</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            <style>
                @keyframes menuReveal { from { opacity:0; transform: translateY(18px); } to { opacity:1; transform: translateY(0); } }
                .menu-reveal { opacity:0; animation: menuReveal .55s cubic-bezier(.22,1,.36,1) both; }
            </style>

            @if($restaurantes->isEmpty())
                <div class="card p-16 text-center">
                    <div class="w-20 h-20 bg-gradient-to-br from-zinc-800 to-zinc-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-zinc-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-zinc-100 text-lg">Pronto habrá restaurantes disponibles</h3>
                    <p class="text-zinc-500 text-sm mt-2 max-w-xs mx-auto">Los negocios locales de Ubaté se están uniendo a la plataforma.</p>
                </div>
            @else
                @php
                    // Deriva un tipo de cocina + emoji a partir del nombre del restaurante
                    // (la columna `categoria` aún viene vacía; en Fase 2 la manda el backend).
                    $emojiDe = function (string $nombre): array {
                        $n = mb_strtolower($nombre);
                        $mapa = [
                            'pollo' => ['Pollo','🍗'],          'asad' => ['Asados','🍖'],
                            'pizz' => ['Pizza','🍕'],           'hamburg' => ['Burgers','🍔'], 'burger' => ['Burgers','🍔'],
                            'taco' => ['Mexicana','🌮'],        'mexic' => ['Mexicana','🌮'], 'burrito' => ['Mexicana','🌯'],
                            'sushi' => ['Sushi','🍣'],          'japon' => ['Sushi','🍣'],
                            'caf' => ['Café','☕'],             'pan' => ['Panadería','🥖'],
                            'postre' => ['Postres','🧁'],       'dulc' => ['Postres','🧁'], 'helad' => ['Heladería','🍦'],
                            'past' => ['Pastas','🍝'],          'italian' => ['Italiana','🍝'],
                            'carne' => ['Carnes','🥩'],         'parrill' => ['Parrilla','🥩'], 'asador' => ['Parrilla','🥩'],
                            'mar' => ['Mariscos','🦐'],         'pescad' => ['Mariscos','🐟'],
                            'arepa' => ['Arepas','🫓'],         'desayun' => ['Desayunos','🍳'],
                            'perro' => ['Perros','🌭'],         'sandwich' => ['Sándwiches','🥪'], 'sánduch' => ['Sándwiches','🥪'],
                            'jugo' => ['Jugos','🥤'],           'bebid' => ['Bebidas','🥤'],
                        ];
                        foreach ($mapa as $clave => $par) {
                            if (str_contains($n, $clave)) return ['tipo' => $par[0], 'emoji' => $par[1]];
                        }
                        return ['tipo' => 'Variado', 'emoji' => '🍽️'];
                    };
                    $tipos = [];
                    foreach ($restaurantes as $r) { $tipos[$r->id] = $emojiDe($r->nombre); }
                    $categorias = collect($tipos)->unique('tipo')->values();
                @endphp

                <div x-data="{
                    q: '',
                    filtro: 'todos',
                    lista: {{ $restaurantes->map(fn($r) => [
                        'id'    => $r->id,
                        'texto' => strtolower($r->nombre . ' ' . ($r->descripcion ?? '') . ' ' . ($r->direccion ?? '')),
                        'tipo'  => $tipos[$r->id]['tipo'],
                    ])->values()->toJson() }},
                    coincide(r) {
                        const okQ = !this.q.trim() || r.texto.includes(this.q.toLowerCase().trim());
                        const okF = this.filtro === 'todos' || r.tipo === this.filtro;
                        return okQ && okF;
                    },
                    visible(id) {
                        const r = this.lista.find(x => x.id == id);
                        return r ? this.coincide(r) : true;
                    },
                    setFiltro(f) {
                        this.filtro = f;
                        if (window.navigator.vibrate) window.navigator.vibrate(8);
                    },
                    get totalVisibles() { return this.lista.filter(r => this.coincide(r)).length; },
                    get hayResultados() { return this.totalVisibles > 0; }
                }">

                {{-- Barra de búsqueda --}}
                <div class="mb-8 flex items-center gap-4 flex-wrap">
                    <div class="relative flex-1 max-w-md">
                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" x-model="q" placeholder="Buscar por nombre, tipo o dirección..."
                               class="input pl-11 pr-10 py-3 shadow-sm w-full">
                        <button x-show="q" @click="q=''"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-zinc-700 hover:bg-zinc-600 flex items-center justify-center transition-colors">
                            <svg class="w-3 h-3 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <p x-show="q.trim()" class="text-sm text-zinc-500 shrink-0">
                        <span x-text="totalVisibles" class="font-bold text-zinc-300"></span>
                        <span x-text="totalVisibles === 1 ? ' resultado' : ' resultados'"></span>
                        para "<span x-text="q" class="text-brand-400"></span>"
                    </p>
                </div>

                {{-- Chips de categoría (filtro interactivo) --}}
                @if($categorias->count() > 1)
                    <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-1 mb-7">
                        <button type="button" @click="setFiltro('todos')"
                                :class="filtro === 'todos' ? 'bg-brand-500 text-white shadow-brand-sm scale-105' : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700'"
                                class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all active:scale-95">
                            🍽️ Todos
                        </button>
                        @foreach($categorias as $cat)
                            <button type="button" @click="setFiltro('{{ $cat['tipo'] }}')"
                                    :class="filtro === '{{ $cat['tipo'] }}' ? 'bg-brand-500 text-white shadow-brand-sm scale-105' : 'bg-zinc-800 text-zinc-300 hover:bg-zinc-700'"
                                    class="shrink-0 inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold whitespace-nowrap transition-all active:scale-95">
                                <span class="text-base leading-none">{{ $cat['emoji'] }}</span> {{ $cat['tipo'] }}
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @php $i = 0; @endphp
                    @foreach($restaurantes as $restaurante)
                        @php $delay = min($i * 55, 450); $i++; @endphp
                        <a href="{{ route('cliente.restaurantes.show', $restaurante) }}"
                           x-show="visible({{ $restaurante->id }})"
                           x-transition:enter="transition ease-out duration-200"
                           x-transition:enter-start="opacity-0 scale-95"
                           x-transition:enter-end="opacity-100 scale-100"
                           x-transition:leave="transition ease-in duration-150"
                           x-transition:leave-start="opacity-100 scale-100"
                           x-transition:leave-end="opacity-0 scale-95"
                           style="animation-delay: {{ $delay }}ms"
                           @animationend="$el.classList.remove('menu-reveal')"
                           class="card overflow-hidden hover:-translate-y-1 group menu-reveal">

                            <div class="relative h-44 overflow-hidden bg-zinc-800">
                                @if($restaurante->imagen)
                                    <img src="{{ $restaurante->fotoUrl() }}"
                                         alt="{{ $restaurante->nombre }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-50 to-amber-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-20 h-20 text-brand-300/60">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>

                                {{-- Badges sobre imagen --}}
                                <div class="absolute top-3 left-3 flex items-center gap-1.5">
                                    <span class="w-8 h-8 rounded-full bg-black/50 backdrop-blur-sm border border-white/15 flex items-center justify-center text-base shadow-sm"
                                          title="{{ $tipos[$restaurante->id]['tipo'] }}">{{ $tipos[$restaurante->id]['emoji'] }}</span>
                                    <span class="bg-emerald-500 text-white text-xs font-bold px-2.5 py-1 rounded-full shadow-sm flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 bg-white rounded-full inline-block"></span>
                                        Abierto
                                    </span>
                                </div>
                                @if($restaurante->total_productos > 0)
                                    <span class="absolute top-3 right-3 bg-black/50 backdrop-blur-sm text-white text-xs font-semibold px-2.5 py-1 rounded-full">
                                        {{ $restaurante->total_productos }} productos
                                    </span>
                                @endif
                            </div>

                            <div class="p-4">
                                <h3 class="font-extrabold text-zinc-100 text-base leading-tight group-hover:text-brand-400 transition-colors">
                                    {{ $restaurante->nombre }}
                                </h3>
                                <p class="text-zinc-500 text-xs mt-0.5 line-clamp-1">
                                    {{ $restaurante->descripcion ?: 'Restaurante local en Ubaté' }}
                                </p>

                                <div class="flex items-center justify-between mt-3 pt-3 border-t border-white/8">
                                    <div class="flex items-center gap-3 text-xs text-zinc-500">
                                        @if($restaurante->promedio_estrellas)
                                            <span class="flex items-center gap-1">
                                                <svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-amber-400">
                                                    <path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z"/>
                                                </svg>
                                                <span class="font-bold text-zinc-300">{{ number_format($restaurante->promedio_estrellas, 1) }}</span>
                                                <span class="text-zinc-600">({{ $restaurante->total_calificaciones }})</span>
                                            </span>
                                            <span class="text-zinc-700">|</span>
                                        @endif
                                        <span class="font-medium">25–35 min</span>
                                        <span class="text-zinc-700">|</span>
                                        @if($restaurante->costo_domicilio > 0)
                                            <span class="font-medium text-zinc-400">
                                                Domicilio ${{ number_format($restaurante->costo_domicilio, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="font-bold text-emerald-400">Domicilio gratis</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                {{-- Sin resultados --}}
                <div x-show="!hayResultados"
                     x-transition
                     class="card p-12 text-center mt-2">
                    <div class="w-16 h-16 bg-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <p class="font-bold text-zinc-300">Sin resultados</p>
                    <p class="text-zinc-600 text-sm mt-1">Prueba con otro nombre, dirección o categoría.</p>
                    <button @click="q=''; setFiltro('todos')" class="mt-4 text-xs font-semibold text-brand-400 hover:text-brand-300 transition-colors">
                        Limpiar filtros
                    </button>
                </div>

                </div>{{-- /x-data --}}
            @endif

        </div>
    </div>
</x-app-layout>
