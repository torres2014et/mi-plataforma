<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="font-black text-white text-xl">Hola, {{ auth()->user()->name }}</h2>
                <p class="text-sm text-zinc-500 mt-0.5">Panel del domiciliario · Ubaté</p>
            </div>
            <div class="flex items-center gap-3">
                @if(auth()->user()->disponible)
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full bg-emerald-500/15 text-emerald-400 border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Disponible
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-full bg-zinc-800 text-zinc-500 border border-zinc-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-500"></span>
                        No disponible
                    </span>
                @endif
                <form action="{{ route('domiciliario.disponibilidad') }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="{{ auth()->user()->disponible ? 'btn-secondary' : 'btn-primary' }} text-sm">
                        {{ auth()->user()->disponible ? 'Pausar' : 'Activarme' }}
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <style>
        @keyframes heroReveal  { from { opacity:0; transform: translateY(22px); } to { opacity:1; transform: translateY(0); } }
        @keyframes heroFloat   { 0%,100% { transform: translateY(0) rotate(-5deg); } 50% { transform: translateY(-18px) rotate(6deg); } }
        @keyframes heroDrift1  { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-30px,20px) scale(1.12); } }
        @keyframes heroDrift2  { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(26px,-22px) scale(1.15); } }
        @keyframes heroDrift3  { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(20px,24px) scale(1.1); } }
        @keyframes heroPulse   { 0%,100% { opacity:1; } 50% { opacity:.35; } }

        .hero-card  { animation: heroReveal .7s cubic-bezier(.22,1,.36,1) both; }
        .hero-bg    { background: linear-gradient(135deg,#0a1410 0%,#0d0d0f 50%,#160d09 100%); }
        .hero-orb   { position:absolute; border-radius:9999px; filter: blur(64px); pointer-events:none; }
        .hero-orb-1 { width:300px; height:300px; background:#F25C2E; opacity:.5; top:-90px; right:-30px; animation: heroDrift1 15s ease-in-out infinite; }
        .hero-orb-2 { width:240px; height:240px; background:#059669; opacity:.42; bottom:-110px; left:6%; animation: heroDrift2 19s ease-in-out infinite; }
        .hero-orb-3 { width:220px; height:220px; background:#7c3aed; opacity:.3; top:24%; left:44%; animation: heroDrift3 22s ease-in-out infinite; }
        .hero-emoji { position:absolute; font-size:1.6rem; opacity:.85; filter: drop-shadow(0 8px 12px rgba(0,0,0,.45)); animation: heroFloat 6s ease-in-out infinite; pointer-events:none; }
        @media (min-width:640px) { .hero-emoji { font-size:2.1rem; opacity:.9; } }
        .hero-dot   { animation: heroPulse 1.8s ease-in-out infinite; }
    </style>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            @if(session('success'))
                <div class="px-4 py-3 rounded-xl bg-emerald-900/40 border border-emerald-800/50 text-emerald-400 text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="px-4 py-3 rounded-xl bg-red-950/50 border border-red-800/50 text-red-400 text-sm font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            {{-- ── Hero ─────────────────────────────────────────── --}}
            @php $disp = auth()->user()->disponible; @endphp
            <div class="hero-card relative overflow-hidden rounded-3xl border border-white/10 shadow-2xl">
                <div class="hero-bg absolute inset-0"></div>
                <div class="hero-orb hero-orb-1"></div>
                <div class="hero-orb hero-orb-2"></div>
                <div class="hero-orb hero-orb-3"></div>
                <div class="absolute inset-0 opacity-[0.04]" style="background-image:radial-gradient(circle at 1px 1px,#fff 1px,transparent 0);background-size:22px 22px"></div>

                <span class="hero-emoji" style="left:82%;top:18%;animation-delay:0s">🛵</span>
                <span class="hero-emoji" style="left:91%;top:58%;animation-delay:1.3s">📦</span>

                <div class="relative z-10 p-8 sm:p-10 max-w-xl">
                    <span class="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.15em] px-3 py-1.5 rounded-full border
                                 {{ $disp ? 'text-emerald-300 bg-emerald-500/10 border-emerald-500/25' : 'text-zinc-400 bg-zinc-500/10 border-white/15' }}">
                        <span class="hero-dot w-1.5 h-1.5 rounded-full {{ $disp ? 'bg-emerald-400' : 'bg-zinc-400' }}"></span>
                        {{ $disp ? 'En ruta · Ubaté' : 'En pausa · Ubaté' }}
                    </span>

                    <h3 class="mt-5 text-4xl sm:text-5xl font-black text-white leading-[1.05] tracking-tight">
                        Hola, <span class="text-gradient">{{ explode(' ', auth()->user()->name)[0] }}</span> 🛵
                    </h3>

                    <p class="text-white/55 text-sm sm:text-base mt-4 leading-relaxed max-w-md">
                        {{ $disp
                            ? 'Estás disponible. Toma un pedido de la lista de abajo y empieza a rodar por Ubaté.'
                            : 'Actívate para empezar a recibir y aceptar pedidos en Ubaté.' }}
                    </p>

                    <form action="{{ route('domiciliario.disponibilidad') }}" method="POST" class="mt-7">
                        @csrf @method('PATCH')
                        <button type="submit" class="{{ $disp ? 'btn-secondary' : 'btn-primary' }} px-7 py-3.5 text-base">
                            {{ $disp ? 'Pausar disponibilidad' : 'Activarme ahora' }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Stat cards --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="stat-card bg-gradient-to-br from-emerald-600 to-emerald-800">
                    <div class="absolute top-0 right-0 w-28 h-28 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 blur-2xl"></div>
                    <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center mb-4 border border-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white/90">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                        </svg>
                    </div>
                    <p class="text-white/60 text-xs font-semibold uppercase tracking-wide">Entregas hoy</p>
                    <p class="text-3xl font-black text-white mt-1">{{ $entregasHoy }}</p>
                    <p class="text-white/40 text-xs mt-2">Completadas hoy</p>
                </div>

                <div class="stat-card bg-gradient-to-br from-violet-600 to-violet-800">
                    <div class="absolute top-0 right-0 w-28 h-28 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 blur-2xl"></div>
                    <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center mb-4 border border-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white/90">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                        </svg>
                    </div>
                    <p class="text-white/60 text-xs font-semibold uppercase tracking-wide">Ganancias hoy</p>
                    <p class="text-3xl font-black text-white mt-1">${{ number_format($gananciasHoy, 0, ',', '.') }}</p>
                    <p class="text-white/40 text-xs mt-2">Solo entregas completadas</p>
                </div>
            </div>

            {{-- Pedido activo --}}
            <div>
                <p class="text-xs font-black text-zinc-500 uppercase tracking-widest mb-4">Pedido actual</p>

                @if($pedidoActivo)
                    <div class="card p-5">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-black text-white">Pedido #{{ $pedidoActivo->id }}</span>
                                    <span class="{{ $pedidoActivo->estadoBadgeClass() }}">{{ $pedidoActivo->estadoLabel() }}</span>
                                </div>
                                <p class="text-zinc-400 text-sm font-semibold">{{ $pedidoActivo->restaurante->nombre }}</p>
                                <p class="text-zinc-500 text-xs mt-0.5">Cliente: {{ $pedidoActivo->cliente->name }}</p>
                            </div>
                            <span class="font-black text-2xl text-white shrink-0">
                                ${{ number_format($pedidoActivo->total, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex items-start gap-2 p-3 rounded-xl bg-zinc-800/60 border border-white/[0.06]">
                            <svg class="w-4 h-4 text-brand-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                            </svg>
                            <p class="text-zinc-300 text-sm">{{ $pedidoActivo->direccion_entrega }}</p>
                        </div>

                        {{-- Botones de navegación --}}
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a id="btn-gmaps"
                               href="https://www.google.com/maps/search/?api=1&query={{ urlencode($pedidoActivo->direccion_entrega . ', Ubaté, Cundinamarca, Colombia') }}"
                               target="_blank" rel="noopener"
                               class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 active:scale-95 text-white text-sm font-bold transition-all duration-150">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                </svg>
                                Google Maps
                            </a>
                            <a id="btn-waze"
                               href="https://waze.com/ul?q={{ urlencode($pedidoActivo->direccion_entrega . ', Ubaté') }}&navigate=yes"
                               target="_blank" rel="noopener"
                               class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold active:scale-95 transition-all duration-150"
                               style="background:#33CCFF;color:#1a1a2e">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 1C6.49 1 2 5.49 2 11c0 3.86 2.13 7.26 5.36 9.12L7 22l1.64-1.64C9.69 20.79 10.83 21 12 21c5.51 0 10-4.49 10-10S17.51 1 12 1zm0 18c-.97 0-1.91-.16-2.8-.44l-.5-.16-.44.44-.56.56.18-.96.12-.64-.56-.34C5.14 16.54 4 13.86 4 11c0-4.41 3.59-8 8-8s8 3.59 8 8-3.59 8-8 8z"/>
                                    <circle cx="9" cy="10" r="1.5"/><circle cx="15" cy="10" r="1.5"/>
                                    <path d="M8.5 13.5s.5 2 3.5 2 3.5-2 3.5-2" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                                </svg>
                                Waze
                            </a>
                        </div>

                        {{-- Mapa de entrega --}}
                        <div class="mt-3 rounded-xl overflow-hidden border border-white/[0.08] relative" style="height:280px">
                            <div id="delivery-map" style="height:100%;width:100%;background:#18181b"></div>
                            <div id="map-status" class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-zinc-900/90">
                                <svg class="w-5 h-5 text-brand-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                <span id="map-status-text" class="text-zinc-400 text-xs font-semibold">Cargando mapa...</span>
                            </div>
                        </div>
                        <p class="text-zinc-600 text-xs mt-1.5 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Permite el acceso al GPS para ver la ruta desde tu ubicación
                        </p>

                        @if($pedidoActivo->items->isNotEmpty())
                            <div class="mt-4 space-y-1.5 pl-4 border-l-2 border-zinc-700/60">
                                @foreach($pedidoActivo->items as $item)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-zinc-400">×{{ $item->cantidad }} {{ $item->nombre_producto }}</span>
                                        <span class="text-zinc-300 font-semibold">${{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Voy a recoger: avisa al restaurante que vas en camino a recogerlo --}}
                        @if($pedidoActivo->estado !== 'en_camino')
                            @if(is_null($pedidoActivo->recogiendo_at))
                                <form action="{{ route('domiciliario.pedidos.recoger', $pedidoActivo) }}" method="POST" class="mt-4">
                                    @csrf
                                    <button type="submit" class="btn-primary w-full justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                        Voy a recoger
                                    </button>
                                </form>
                            @else
                                <div class="mt-4 flex items-center gap-2 p-3 rounded-xl bg-brand-500/10 border border-brand-500/30 text-brand-400 text-sm font-semibold">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Vas en camino a recoger — el restaurante ya lo sabe.
                                </div>
                            @endif
                        @endif

                        {{-- Confirmar entrega con QR --}}
                        @if($pedidoActivo->estado === 'en_camino')
                            <button type="button" id="btn-confirmar-entrega"
                                    class="btn-primary w-full justify-center mt-4">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/>
                                </svg>
                                Confirmar entrega (escanear QR)
                            </button>

                            {{-- Modal del escáner --}}
                            <div id="modal-scanner" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/80 p-4">
                                <div class="card w-full max-w-sm p-5 relative">
                                    <button type="button" id="cerrar-scanner"
                                            class="absolute top-3 right-3 w-8 h-8 flex items-center justify-center rounded-lg text-zinc-500 hover:text-white hover:bg-white/10 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                    <h3 class="font-black text-white text-base mb-1">Escanear QR del cliente</h3>
                                    <p class="text-zinc-500 text-sm mb-4">Apunta la cámara a la pantalla del cliente.</p>

                                    <div id="qr-reader" class="rounded-xl overflow-hidden mb-2 bg-black" style="min-height:220px"></div>
                                    <p id="scanner-hint" class="text-zinc-600 text-xs mb-3"></p>

                                    <div class="relative flex items-center gap-3 my-3">
                                        <div class="flex-1 h-px bg-white/8"></div>
                                        <span class="text-xs text-zinc-600">o escribe el código</span>
                                        <div class="flex-1 h-px bg-white/8"></div>
                                    </div>

                                    <form id="form-confirmar" method="POST"
                                          action="{{ route('domiciliario.pedidos.confirmar', $pedidoActivo) }}" class="flex gap-2">
                                        @csrf
                                        <input id="codigo-input" type="text" name="codigo" maxlength="6"
                                               placeholder="CÓDIGO" autocomplete="off"
                                               class="input flex-1 uppercase tracking-[0.3em] text-center font-bold">
                                        <button type="submit" class="btn-primary text-sm shrink-0">Confirmar</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="card p-10 text-center">
                        <div class="w-16 h-16 bg-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-zinc-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                            </svg>
                        </div>
                        <p class="font-semibold text-zinc-400">Sin pedido asignado</p>
                        <p class="text-sm text-zinc-600 mt-1">
                            @if(!auth()->user()->disponible)
                                Actívate para comenzar a recibir pedidos.
                            @else
                                Espera a que un restaurante te asigne un pedido.
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            {{-- Pedidos disponibles para aceptar --}}
            <div>
                <p class="text-xs font-black text-zinc-500 uppercase tracking-widest mb-4">Pedidos disponibles</p>

                @if($disponibles->isEmpty())
                    <div class="card p-8 text-center">
                        <p class="text-zinc-500 text-sm">No hay pedidos disponibles ahora mismo.</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($disponibles as $p)
                            <div class="card p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-black text-white">#{{ $p->id }}</span>
                                            <span class="{{ $p->estadoBadgeClass() }}">{{ $p->estadoLabel() }}</span>
                                        </div>
                                        <p class="text-zinc-400 text-sm font-semibold mt-0.5">{{ $p->restaurante->nombre }}</p>
                                        <p class="text-zinc-500 text-xs mt-0.5 truncate">{{ $p->direccion_entrega }}</p>
                                    </div>
                                    <span class="font-black text-lg text-white shrink-0">${{ number_format($p->total, 0, ',', '.') }}</span>
                                </div>
                                <form action="{{ route('domiciliario.pedidos.aceptar', $p) }}" method="POST" class="mt-3 flex gap-2">
                                    @csrf
                                    <select name="medio_transporte" class="input py-2 flex-1 text-sm">
                                        <option value="moto"  class="bg-zinc-900">Moto</option>
                                        <option value="bici"  class="bg-zinc-900">Bicicleta</option>
                                        <option value="auto"  class="bg-zinc-900">Carro</option>
                                        <option value="a_pie" class="bg-zinc-900">A pie</option>
                                    </select>
                                    <button type="submit" class="btn-primary text-sm shrink-0">Aceptar</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Historial --}}
            @if($historial->isNotEmpty())
                <div>
                    <p class="text-xs font-black text-zinc-500 uppercase tracking-widest mb-4">Historial reciente</p>
                    <div class="card overflow-hidden">
                        @foreach($historial as $pedido)
                            <div class="flex items-center gap-4 px-5 py-4 border-b border-white/[0.05] last:border-0">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-zinc-300 text-sm">#{{ $pedido->id }}</span>
                                        <span class="text-zinc-600 text-xs">·</span>
                                        <span class="text-zinc-400 text-sm truncate">{{ $pedido->restaurante->nombre }}</span>
                                    </div>
                                    <p class="text-zinc-600 text-xs mt-0.5">{{ $pedido->updated_at->format('d M Y, H:i') }}</p>
                                </div>
                                <span class="{{ $pedido->estadoBadgeClass() }}">{{ $pedido->estadoLabel() }}</span>
                                <span class="font-bold text-zinc-300 text-sm shrink-0">
                                    ${{ number_format($pedido->total, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>

@if($pedidoActivo)
@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    const mapEl     = document.getElementById('delivery-map');
    const statusEl  = document.getElementById('map-status');
    const statusTxt = document.getElementById('map-status-text');
    if (!mapEl) return;

    const UBATE   = [5.3127, -73.8180];
    const address = @json($pedidoActivo->direccion_entrega ?? '');

    /* ── Mapa base ── */
    const map = L.map('delivery-map', { zoomControl: true, scrollWheelZoom: false, attributionControl: false });
    const _calles = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 20, subdomains: 'abcd' });
    const _satImg = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 20 });
    const _satVias = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}', { maxZoom: 20 });
    const _satelite = L.layerGroup([_satImg, _satVias]);
    _calles.addTo(map);
    L.control.layers({ 'Calles': _calles, 'Satélite': _satelite }, null, { position: 'topright', collapsed: false }).addTo(map);
    L.control.attribution({ prefix: false })
        .addAttribution('© <a href="https://www.openstreetmap.org/copyright" style="color:#aaa">OSM</a> © <a href="https://carto.com/" style="color:#aaa">CARTO</a>')
        .addTo(map);
    map.setView(UBATE, 14);

    /* ── Íconos ── */
    const destIcon = L.divIcon({
        html: `<div style="width:30px;height:30px;background:#F25C2E;border:3px solid white;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 14px rgba(242,92,46,.7)"><div style="width:8px;height:8px;background:white;border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%)"></div></div>`,
        iconSize:[30,30], iconAnchor:[15,30], popupAnchor:[0,-32], className:''
    });
    const myIcon = L.divIcon({
        html: `<div style="width:18px;height:18px;background:#3B82F6;border:3px solid white;border-radius:50%;box-shadow:0 0 0 4px rgba(59,130,246,.3)"></div>`,
        iconSize:[18,18], iconAnchor:[9,9], className:''
    });

    let destCoords = null;
    let myCoords   = null;

    function setStatus(msg) { if (statusTxt) statusTxt.textContent = msg; }

    function hideStatus() {
        if (!statusEl) return;
        statusEl.style.transition = 'opacity .3s';
        statusEl.style.opacity = '0';
        setTimeout(() => statusEl && statusEl.remove(), 350);
    }

    /* ── Actualizar URLs de los botones con coordenadas reales ── */
    function updateNavButtons() {
        if (!destCoords) return;
        const [dLat, dLon] = destCoords;
        const gmaps = document.getElementById('btn-gmaps');
        const waze  = document.getElementById('btn-waze');

        if (gmaps) {
            gmaps.href = myCoords
                ? `https://www.google.com/maps/dir/${myCoords[0]},${myCoords[1]}/${dLat},${dLon}`
                : `https://www.google.com/maps/dir/?api=1&destination=${dLat},${dLon}&travelmode=driving`;
        }
        if (waze) {
            waze.href = `https://waze.com/ul?ll=${dLat},${dLon}&navigate=yes`;
        }
    }

    /* ── Trazar ruta con OSRM ── */
    function drawRoute(from, to) {
        setStatus('Trazando ruta...');
        const url = `https://router.project-osrm.org/route/v1/driving/${from[1]},${from[0]};${to[1]},${to[0]}?overview=full&geometries=geojson`;

        return fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.code === 'Ok' && data.routes[0]) {
                    const pts = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                    /* Sombra */
                    L.polyline(pts, { color:'#000', weight:9, opacity:.22, lineCap:'round', lineJoin:'round' }).addTo(map);
                    /* Ruta naranja */
                    L.polyline(pts, { color:'#F25C2E', weight:5, opacity:.9, lineCap:'round', lineJoin:'round' }).addTo(map);

                    const dist = (data.routes[0].distance / 1000).toFixed(1);
                    const mins = Math.round(data.routes[0].duration / 60);
                    map.fitBounds(L.latLngBounds([from, to]), { padding:[45,45] });

                    /* Popup en destino con distancia y tiempo estimado */
                    L.marker(to, { icon: destIcon }).addTo(map)
                        .bindPopup(`<div style="font-family:sans-serif;font-size:12px;color:#111;min-width:150px">
                            <b style="color:#F25C2E;font-size:13px">📦 Entrega aquí</b><br>
                            <span>${address}</span><br>
                            <div style="margin-top:6px;display:flex;gap:10px;font-weight:700">
                                <span>🗺 ${dist} km</span>
                                <span>⏱ ~${mins} min</span>
                            </div>
                        </div>`)
                        .openPopup();
                } else {
                    map.setView(to, 16);
                    L.marker(to, { icon: destIcon }).addTo(map)
                        .bindPopup(`<div style="font-family:sans-serif;font-size:12px;color:#111"><b style="color:#F25C2E">📦 Entrega aquí</b><br>${address}</div>`)
                        .openPopup();
                }
            })
            .catch(() => {
                map.setView(to, 16);
                L.marker(to, { icon: destIcon }).addTo(map);
            });
    }

    /* ── PASO 1: Geocodificar dirección de entrega ── */
    setStatus('Buscando dirección...');
    const query = encodeURIComponent(address + ', Ubaté, Cundinamarca, Colombia');

    fetch(`https://nominatim.openstreetmap.org/search?q=${query}&format=json&limit=1&countrycodes=co`, {
        headers: { 'Accept-Language': 'es' }
    })
    .then(r => r.json())
    .then(data => {
        destCoords = (data.length > 0)
            ? [parseFloat(data[0].lat), parseFloat(data[0].lon)]
            : UBATE;

        updateNavButtons();

        /* ── PASO 2: Pedir ubicación GPS del domiciliario ── */
        setStatus('Obteniendo tu ubicación GPS...');

        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(
                pos => {
                    myCoords = [pos.coords.latitude, pos.coords.longitude];
                    L.marker(myCoords, { icon: myIcon }).addTo(map)
                        .bindPopup('<div style="font-family:sans-serif;font-size:12px;color:#111"><b>📍 Tú estás aquí</b></div>');
                    updateNavButtons();
                    drawRoute(myCoords, destCoords).finally(hideStatus);
                },
                () => {
                    /* GPS denegado — solo mostrar destino */
                    map.setView(destCoords, 16);
                    L.marker(destCoords, { icon: destIcon }).addTo(map)
                        .bindPopup(`<div style="font-family:sans-serif;font-size:12px;color:#111"><b style="color:#F25C2E">📦 Entrega aquí</b><br>${address}</div>`)
                        .openPopup();
                    hideStatus();
                },
                { timeout: 9000, maximumAge: 60000, enableHighAccuracy: true }
            );
        } else {
            map.setView(destCoords, 16);
            L.marker(destCoords, { icon: destIcon }).addTo(map);
            hideStatus();
        }
    })
    .catch(() => {
        map.setView(UBATE, 14);
        hideStatus();
    });
})();
</script>

@if($pedidoActivo->estado === 'en_camino')
{{-- Fase 3 — GPS real: emitir la posición del domiciliario en vivo mientras va
     en camino. El backend la difunde por websocket al cliente (app o web). --}}
<script>
(function () {
    if (!('geolocation' in navigator)) return;
    const URL  = @json(route('domiciliario.pedidos.ubicacion', $pedidoActivo));
    const CSRF = @json(csrf_token());
    let ultimo = 0;

    function enviar(pos) {
        const ahora = Date.now();
        if (ahora - ultimo < 3000) return;   // como mucho 1 envío cada 3 s
        ultimo = ahora;
        fetch(URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
        }).catch(() => {});
    }

    // watchPosition emite cada vez que el GPS detecta movimiento.
    navigator.geolocation.watchPosition(
        enviar,
        () => {},   // sin permiso: el cliente ve la simulación, no se rompe nada
        { enableHighAccuracy: true, maximumAge: 4000, timeout: 20000 }
    );
})();
</script>

{{-- Escáner de QR para confirmar la entrega --}}
<script src="https://unpkg.com/html5-qrcode" defer></script>
<script>
(function () {
    const btn   = document.getElementById('btn-confirmar-entrega');
    const modal = document.getElementById('modal-scanner');
    const form  = document.getElementById('form-confirmar');
    const input = document.getElementById('codigo-input');
    const hint  = document.getElementById('scanner-hint');
    if (!btn || !modal) return;

    let scanner = null;

    function parar() {
        if (scanner) {
            scanner.stop().then(() => scanner.clear()).catch(() => {});
            scanner = null;
        }
    }

    function abrir() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        if (!window.Html5Qrcode) {
            if (hint) hint.textContent = 'Cámara no disponible — escribe el código a mano.';
            return;
        }
        scanner = new Html5Qrcode('qr-reader');
        scanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: 220 },
            (texto) => {                 // QR leído con éxito
                input.value = (texto || '').trim();
                parar();
                form.submit();
            },
            () => {}                      // sin lectura en este frame: ignorar
        ).catch(() => {
            if (hint) hint.textContent = 'No se pudo abrir la cámara (necesita HTTPS). Escribe el código a mano.';
        });
    }

    function cerrar() {
        parar();
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    btn.addEventListener('click', abrir);
    document.getElementById('cerrar-scanner').addEventListener('click', cerrar);
    modal.addEventListener('click', (e) => { if (e.target === modal) cerrar(); });
})();
</script>
@endif
@endpush
@endif

</x-app-layout>
