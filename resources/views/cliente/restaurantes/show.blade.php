<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('cliente.restaurantes.index') }}"
               class="flex items-center gap-1.5 text-zinc-500 hover:text-zinc-200 transition-colors text-sm font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
                Restaurantes
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-zinc-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
            <span class="font-semibold text-zinc-200">{{ $restaurante->nombre }}</span>
        </div>
    </x-slot>

    <div class="pb-24" x-data="carritoMenu()">

        <style>
            @keyframes menuReveal   { from { opacity:0; transform: translateY(18px); } to { opacity:1; transform: translateY(0); } }
            @keyframes menuKenburns { from { transform: scale(1.03); } to { transform: scale(1.13); } }
            @keyframes menuPop      { 0%{transform:scale(1)} 40%{transform:scale(1.45)} 100%{transform:scale(1)} }
            @keyframes menuFloatUp  { 0%{opacity:0;transform:translateY(4px) scale(.7)} 25%{opacity:1} 100%{opacity:0;transform:translateY(-46px) scale(1.15)} }
            .menu-reveal   { opacity:0; animation: menuReveal .55s cubic-bezier(.22,1,.36,1) forwards; }
            .menu-kenburns { animation: menuKenburns 18s ease-out forwards; transform-origin: center; }
            .menu-pop      { animation: menuPop .45s ease-out; }
            .menu-floatup  { animation: menuFloatUp 1s ease-out forwards; }
            .menu-card     { transition: transform .18s ease, box-shadow .25s ease, border-color .25s ease; }
            .menu-card:active { transform: scale(.985); }
        </style>

        {{-- ── Banner del restaurante ────────────────────────── --}}
        <div class="relative h-56 sm:h-64 overflow-hidden bg-zinc-700">
            @if($restaurante->imagen)
                <img src="{{ $restaurante->fotoUrl() }}"
                     alt="{{ $restaurante->nombre }}" class="w-full h-full object-cover menu-kenburns">
            @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-orange-100 to-amber-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="0.75" stroke="currentColor" class="w-40 h-40 text-brand-300/40">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                    </svg>
                </div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-6 max-w-6xl mx-auto">
                <div class="flex items-end gap-4">
                    <div class="w-16 h-16 bg-white rounded-2xl overflow-hidden shadow-lg border-2 border-white shrink-0 flex items-center justify-center">
                        @if($restaurante->imagen)
                            <img src="{{ $restaurante->fotoUrl() }}" alt="" class="w-full h-full object-cover">
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-brand-400">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                            </svg>
                        @endif
                    </div>
                    <div>
                        <h1 class="text-2xl font-extrabold text-white">{{ $restaurante->nombre }}</h1>
                        <div class="flex items-center gap-4 mt-1 flex-wrap">
                            @if($promedio)
                                <span class="flex items-center gap-1.5 text-white/80 text-sm">
                                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 text-amber-400">
                                        <path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z"/>
                                    </svg>
                                    {{ number_format($promedio, 1) }} · {{ $totalCal }} {{ $totalCal === 1 ? 'reseña' : 'reseñas' }}
                                </span>
                            @endif
                            <span class="text-white/60 text-sm">25–35 min</span>
                            @if($restaurante->direccion)
                                <span class="text-white/60 text-sm hidden sm:flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                    </svg>
                                    {{ $restaurante->direccion }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Contenido ─────────────────────────────────────── --}}
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">

            @if($restaurante->descripcion)
                <p class="text-zinc-500 text-sm mb-6">{{ $restaurante->descripcion }}</p>
            @endif

            {{-- Ubicación del restaurante --}}
            @php
                $rLat = $restaurante->lat ?? 5.3085900;
                $rLng = $restaurante->lng ?? -73.8143000;
            @endphp
            <div class="card overflow-hidden mb-8"
                 data-rlat="{{ $rLat }}" data-rlng="{{ $rLng }}"
                 data-rnombre="{{ $restaurante->nombre }}">
                <div class="px-5 py-3.5 border-b border-white/[0.08] flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-brand-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                    </svg>
                    <span class="text-sm font-bold text-white">Ubicación del restaurante</span>
                </div>
                <div class="relative" style="height:220px">
                    <div id="rest-loc-map" style="height:100%;width:100%;background:#18181b"></div>
                </div>
                @if($restaurante->direccion)
                    <div class="px-5 py-3 border-t border-white/[0.06] flex items-start gap-2">
                        <svg class="w-4 h-4 text-brand-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        <p class="text-zinc-300 text-sm">{{ $restaurante->direccion }}</p>
                    </div>
                @endif
            </div>

            @if($productos->isEmpty())
                <div class="card p-12 text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-zinc-800 to-zinc-700 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-zinc-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/>
                        </svg>
                    </div>
                    <p class="font-semibold text-zinc-300">Menú no disponible todavía</p>
                    <p class="text-zinc-500 text-sm mt-1">Este restaurante aún no ha publicado sus productos.</p>
                </div>
            @else
                @if($productos->count() > 1)
                    {{-- Barra de categorías sticky (con scroll-spy) --}}
                    <div x-data="catNav()"
                         class="sticky top-32 z-20 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 mb-6
                                bg-zinc-950/90 backdrop-blur-md border-b border-white/[0.07]">
                        <div class="flex gap-2 overflow-x-auto scrollbar-hide py-3">
                            @foreach($productos as $categoria => $items)
                                <button type="button" @click="ir('cat-{{ $loop->index }}')"
                                        :class="activa === 'cat-{{ $loop->index }}'
                                                ? 'bg-brand-500 text-white shadow-brand-sm'
                                                : 'bg-zinc-800 text-zinc-400 hover:text-zinc-200'"
                                        class="shrink-0 px-4 py-1.5 rounded-full text-sm font-bold whitespace-nowrap transition-colors">
                                    {{ $categoria ?: 'Otros' }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="space-y-8">
                    @php $idx = 0; @endphp
                    @foreach($productos as $categoria => $items)
                        <div id="cat-{{ $loop->index }}" data-cat class="scroll-mt-36">
                            <div class="menu-reveal flex items-center gap-3 mb-4 pb-2 border-b border-white/10">
                                <h3 class="font-extrabold text-zinc-100 text-lg">{{ $categoria ?: 'Otros' }}</h3>
                                <span class="text-xs font-semibold text-zinc-500 bg-zinc-800 px-2 py-0.5 rounded-full">{{ count($items) }}</span>
                            </div>
                            <div class="space-y-3">
                                @foreach($items as $producto)
                                    @php $delay = min($idx * 55, 450); $idx++; @endphp
                                    <div class="card-interactive menu-card menu-reveal p-4 flex items-center gap-4 hover:-translate-y-0.5 group relative cursor-pointer"
                                         @click="abrirSheet({ id: {{ $producto->id }}, nombre: {{ json_encode($producto->nombre) }}, precio: {{ $producto->precio }}, descripcion: {{ json_encode($producto->descripcion ?? '') }}, imagen: {{ json_encode($producto->fotoUrl() ?? '') }} })"
                                         :class="justAdded === {{ $producto->id }} ? 'ring-2 ring-brand-500/60 shadow-brand' : ''"
                                         style="animation-delay: {{ $delay }}ms">
                                        <div class="w-20 h-20 rounded-xl overflow-hidden bg-gradient-to-br from-zinc-800 to-zinc-700 shrink-0 flex items-center justify-center">
                                            @if($producto->imagen)
                                                <img src="{{ $producto->fotoUrl() }}"
                                                     alt="{{ $producto->nombre }}"
                                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-zinc-500">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold text-zinc-100">{{ $producto->nombre }}</h4>
                                            @if($producto->descripcion)
                                                <p class="text-zinc-500 text-sm mt-0.5 line-clamp-2">{{ $producto->descripcion }}</p>
                                            @endif
                                            <p class="font-extrabold text-zinc-100 text-lg mt-2">
                                                ${{ number_format($producto->precio, 0, ',', '.') }}
                                            </p>
                                        </div>
                                        <div class="relative shrink-0">
                                            {{-- "+1" que flota hacia arriba al agregar --}}
                                            <span x-show="justAdded === {{ $producto->id }}" x-cloak
                                                  class="menu-floatup pointer-events-none absolute -top-5 left-1/2 -translate-x-1/2 text-brand-400 font-black text-sm">+1</span>
                                            <button
                                                @click.stop="agregar({{ $producto->id }}, {{ json_encode($producto->nombre) }}, {{ $producto->precio }}, {{ json_encode($producto->imagen ?? '') }})"
                                                :disabled="agregando === {{ $producto->id }}"
                                                :class="agregando === {{ $producto->id }} ? 'opacity-70 cursor-wait' : ''"
                                                class="btn-primary w-10 h-10 p-0 rounded-xl">
                                                {{-- + (reposo) --}}
                                                <svg x-show="agregando !== {{ $producto->id }} && justAdded !== {{ $producto->id }}"
                                                     class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"/>
                                                </svg>
                                                {{-- spinner (agregando) --}}
                                                <svg x-show="agregando === {{ $producto->id }}" x-cloak
                                                     class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                                </svg>
                                                {{-- ✓ (agregado) --}}
                                                <svg x-show="justAdded === {{ $producto->id }}" x-cloak
                                                     class="menu-pop w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4.5 12.75l6 6 9-13.5"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>

        {{-- ── Barra flotante del carrito ────────────────────── --}}
        <div x-show="totalItems > 0"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4"
             class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50"
             style="display: none;">
            <a href="{{ route('cliente.carrito.show') }}"
               class="btn-primary px-6 py-3.5 shadow-brand flex items-center gap-3 rounded-2xl">
                <span class="w-6 h-6 bg-white/25 rounded-full flex items-center justify-center text-xs font-black"
                      :class="bump ? 'menu-pop' : ''" @animationend="bump = false"
                      x-text="totalItems"></span>
                <span class="font-bold">Ver carrito</span>
                <span class="font-black" x-text="'$' + totalFormateado"></span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                </svg>
            </a>
        </div>

        {{-- ── Hoja de detalle del producto ─────────────────── --}}
        <div x-show="sheet" x-cloak class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center" style="display:none">
            {{-- Fondo --}}
            <div class="absolute inset-0 bg-black/70" @click="cerrarSheet()"
                 x-show="sheet"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

            {{-- Hoja --}}
            <div class="relative w-full sm:max-w-md bg-zinc-900 border-t sm:border border-white/10 rounded-t-3xl sm:rounded-3xl overflow-hidden shadow-2xl"
                 x-show="sheet"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-6"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-6"
                 @click.stop>
                {{-- Imagen --}}
                <div class="relative h-48 bg-gradient-to-br from-zinc-800 to-zinc-700">
                    <template x-if="sheet && sheet.imagen">
                        <img :src="sheet.imagen" alt="" class="w-full h-full object-cover">
                    </template>
                    <template x-if="sheet && !sheet.imagen">
                        <div class="w-full h-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-20 h-20 text-zinc-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/>
                            </svg>
                        </div>
                    </template>
                    <div class="absolute inset-0 bg-gradient-to-t from-zinc-900 via-zinc-900/10 to-transparent"></div>
                    {{-- Asa --}}
                    <div class="absolute top-3 left-1/2 -translate-x-1/2 w-10 h-1.5 rounded-full bg-white/50"></div>
                    {{-- Cerrar --}}
                    <button @click="cerrarSheet()"
                            class="absolute top-3 right-3 w-9 h-9 rounded-full bg-black/40 backdrop-blur flex items-center justify-center text-white hover:bg-black/60 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Contenido --}}
                <div class="p-5">
                    <h3 class="font-black text-white text-xl leading-tight" x-text="sheet?.nombre"></h3>
                    <p class="text-brand-400 font-black text-lg mt-1" x-text="'$' + fmt(sheet?.precio)"></p>
                    <p class="text-zinc-400 text-sm mt-3 leading-relaxed" x-show="sheet?.descripcion" x-text="sheet?.descripcion"></p>

                    <div class="flex items-center gap-3 mt-6">
                        {{-- Selector de cantidad --}}
                        <div class="flex items-center gap-1 bg-zinc-800 rounded-xl p-1 shrink-0">
                            <button @click="sheetQty = Math.max(1, sheetQty - 1)"
                                    class="w-9 h-9 rounded-lg hover:bg-zinc-700 active:scale-90 transition text-white flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 12h-15"/></svg>
                            </button>
                            <span class="w-8 text-center font-black text-white text-lg" x-text="sheetQty"></span>
                            <button @click="sheetQty = Math.min(20, sheetQty + 1)"
                                    class="w-9 h-9 rounded-lg hover:bg-zinc-700 active:scale-90 transition text-white flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            </button>
                        </div>
                        {{-- Agregar --}}
                        <button @click="agregarDesdeSheet()" class="btn-primary flex-1 justify-center py-3.5">
                            <span class="font-bold">Agregar</span>
                            <span class="font-black" x-text="'$' + fmt(sheetTotal)"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Modal conflicto de restaurante ───────────────── --}}
        <div x-show="conflicto"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 px-4"
             style="display: none;">
            <div class="card p-6 max-w-sm w-full space-y-4"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <h3 class="font-black text-lg text-white">¿Cambiar de restaurante?</h3>
                <p class="text-zinc-400 text-sm leading-relaxed">
                    Ya tienes productos de
                    <span x-text="restauranteConflicto" class="font-semibold text-white"></span>
                    en tu carrito. Si agregas este producto, el carrito anterior se vaciará.
                </p>
                <div class="flex gap-3 justify-end pt-1">
                    <button @click="conflicto = false" class="btn-secondary">Cancelar</button>
                    <button @click="confirmarAgregar()" class="btn-primary">Sí, vaciar y agregar</button>
                </div>
            </div>
        </div>

        {{-- ── Reseñas ─────────────────────────────────────────── --}}
        @if($calificaciones->isNotEmpty())
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-xs font-black text-zinc-500 uppercase tracking-widest">Reseñas</span>
                    <div class="flex-1 h-px bg-white/8"></div>
                    @if($promedio)
                        <div class="flex items-center gap-1.5">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 text-amber-400">
                                <path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z"/>
                            </svg>
                            <span class="font-black text-white text-sm">{{ number_format($promedio, 1) }}</span>
                            <span class="text-zinc-500 text-xs">/5 · {{ $totalCal }} {{ $totalCal === 1 ? 'reseña' : 'reseñas' }}</span>
                        </div>
                    @endif
                </div>
                <div class="space-y-3">
                    @foreach($calificaciones as $cal)
                        <div class="card p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-600 to-orange-500 flex items-center justify-center text-white font-black text-xs shrink-0">
                                        {{ strtoupper(substr($cal->cliente->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-zinc-200 text-sm font-bold">{{ $cal->cliente->name }}</p>
                                        <p class="text-zinc-600 text-xs">{{ $cal->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-0.5 shrink-0">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-3.5 h-3.5 {{ $i <= $cal->estrellas ? 'text-amber-400' : 'text-zinc-700' }}"
                                             viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z"/>
                                        </svg>
                                    @endfor
                                </div>
                            </div>
                            @if($cal->comentario)
                                <p class="text-zinc-400 text-sm mt-2.5 leading-relaxed pl-10">"{{ $cal->comentario }}"</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    @push('scripts')
    {{-- Mapa de ubicación del restaurante --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    (function () {
        const el = document.getElementById('rest-loc-map');
        if (!el || typeof L === 'undefined') return;
        const cont  = el.closest('[data-rlat]');
        const lat   = parseFloat(cont.dataset.rlat);
        const lng   = parseFloat(cont.dataset.rlng);
        const nombre = cont.dataset.rnombre || 'Restaurante';

        const map = L.map('rest-loc-map', { zoomControl: true, scrollWheelZoom: false, attributionControl: false });
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 19, subdomains: 'abcd'
        }).addTo(map);
        L.control.attribution({ prefix: false })
            .addAttribution('© <a href="https://www.openstreetmap.org/copyright" style="color:#aaa">OSM</a> © <a href="https://carto.com/" style="color:#aaa">CARTO</a>')
            .addTo(map);
        map.setView([lat, lng], 16);
        setTimeout(function () { map.invalidateSize(); }, 60);

        const icon = L.divIcon({
            html: `<div style="width:30px;height:30px;background:#F25C2E;border:3px solid white;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 14px rgba(242,92,46,.7)"><div style="width:8px;height:8px;background:white;border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%)"></div></div>`,
            iconSize: [30, 30], iconAnchor: [15, 30], popupAnchor: [0, -30], className: ''
        });
        L.marker([lat, lng], { icon }).addTo(map)
            .bindPopup(`<div style="font-family:sans-serif;font-size:12px;color:#111"><b style="color:#F25C2E">🍴 ${nombre}</b><br>Aquí preparan tu pedido</div>`);
    })();
    </script>
    <script>
    // Barra de categorías: resalta la sección visible y hace scroll suave.
    function catNav() {
        return {
            activa: '',
            init() {
                const secciones = Array.from(document.querySelectorAll('[data-cat]'));
                if (!secciones.length) return;
                this.activa = secciones[0].id;

                const obs = new IntersectionObserver((entries) => {
                    entries.forEach(e => { if (e.isIntersecting) this.activa = e.target.id; });
                }, { rootMargin: '-150px 0px -60% 0px', threshold: 0 });

                secciones.forEach(s => obs.observe(s));
            },
            ir(id) {
                const el = document.getElementById(id);
                if (!el) return;
                const y = el.getBoundingClientRect().top + window.scrollY - 140;
                window.scrollTo({ top: y, behavior: 'smooth' });
                this.activa = id;
            },
        };
    }

    function carritoMenu() {
        return {
            totalItems: {{ $carritoCount ?? 0 }},
            totalPrecio: {{ $carritoTotal ?? 0 }},
            agregando: null,
            justAdded: null,
            bump: false,
            conflicto: false,
            restauranteConflicto: '',
            pendingArgs: null,
            sheet: null,
            sheetQty: 1,

            fmt(n) {
                return new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 }).format(n || 0);
            },

            get totalFormateado() {
                return this.fmt(this.totalPrecio);
            },

            get sheetTotal() {
                return this.sheet ? this.sheet.precio * this.sheetQty : 0;
            },

            // Abre la hoja de detalle del producto tocado.
            abrirSheet(p) {
                this.sheet = p;
                this.sheetQty = 1;
                if (window.navigator.vibrate) window.navigator.vibrate(8);
            },

            cerrarSheet() {
                this.sheet = null;
            },

            agregarDesdeSheet() {
                if (!this.sheet) return;
                const s = this.sheet;
                this.agregar(s.id, s.nombre, s.precio, s.imagen, this.sheetQty);
                this.cerrarSheet();
            },

            // Dispara el deleite visual: ✓ + "+1" en el botón y rebote del carrito.
            flash(id) {
                if (window.navigator.vibrate) window.navigator.vibrate(12);
                this.justAdded = id;
                this.bump = false;
                this.$nextTick(() => { this.bump = true; });
                setTimeout(() => { if (this.justAdded === id) this.justAdded = null; }, 1100);
            },

            agregar(productoId, nombre, precio, imagen, cantidad = 1) {
                this.agregando = productoId;

                fetch('{{ route('cliente.carrito.agregar') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ producto_id: productoId, cantidad }),
                })
                .then(async res => {
                    const data = await res.json();
                    if (res.status === 409 && data.conflicto) {
                        this.conflicto = true;
                        this.restauranteConflicto = data.restaurante;
                        this.pendingArgs = { productoId, nombre, precio, imagen, cantidad };
                    } else if (data.ok) {
                        this.totalItems = data.total_items;
                        this.totalPrecio += precio * cantidad;
                        this.flash(productoId);
                    }
                })
                .finally(() => {
                    this.agregando = null;
                });
            },

            confirmarAgregar() {
                if (!this.pendingArgs) return;
                this.conflicto = false;
                const { productoId, nombre, precio, imagen, cantidad } = this.pendingArgs;
                this.pendingArgs = null;
                this.totalItems = 0;
                this.totalPrecio = 0;

                fetch('{{ route('cliente.carrito.agregar') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ producto_id: productoId, cantidad, confirmar: true }),
                })
                .then(async res => {
                    const data = await res.json();
                    if (data.ok) {
                        this.totalItems = data.total_items;
                        this.totalPrecio = precio * cantidad;
                        this.flash(productoId);
                    }
                });
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
