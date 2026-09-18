<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-black text-zinc-100 text-xl">¡Hola, {{ Auth::user()->name }}!</h2>
            <p class="text-sm text-zinc-500 mt-0.5">¿Qué se te antoja hoy en Ubaté?</p>
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
        .hero-bg    { background: linear-gradient(135deg,#1c0f09 0%,#0d0d0f 52%,#190a16 100%); }
        .hero-orb   { position:absolute; border-radius:9999px; filter: blur(64px); pointer-events:none; }
        .hero-orb-1 { width:300px; height:300px; background:#F25C2E; opacity:.55; top:-90px; right:-30px; animation: heroDrift1 15s ease-in-out infinite; }
        .hero-orb-2 { width:240px; height:240px; background:#c2410c; opacity:.45; bottom:-110px; left:8%;  animation: heroDrift2 19s ease-in-out infinite; }
        .hero-orb-3 { width:220px; height:220px; background:#7c3aed; opacity:.32; top:24%;  left:42%;    animation: heroDrift3 22s ease-in-out infinite; }
        .hero-emoji { position:absolute; font-size:1.55rem; opacity:.85; filter: drop-shadow(0 8px 12px rgba(0,0,0,.45)); animation: heroFloat 6s ease-in-out infinite; pointer-events:none; }
        @media (min-width:640px) { .hero-emoji { font-size:2.1rem; opacity:.9; } }
        .hero-dot   { animation: heroPulse 1.8s ease-in-out infinite; }
        .qa-reveal  { opacity:0; animation: heroReveal .55s cubic-bezier(.22,1,.36,1) forwards; }
    </style>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- ── Hero ─────────────────────────────────────────── --}}
            <div class="hero-card relative overflow-hidden rounded-3xl border border-white/10 shadow-2xl">
                <div class="hero-bg absolute inset-0"></div>
                <div class="hero-orb hero-orb-1"></div>
                <div class="hero-orb hero-orb-2"></div>
                <div class="hero-orb hero-orb-3"></div>
                {{-- textura de puntos --}}
                <div class="absolute inset-0 opacity-[0.04]" style="background-image:radial-gradient(circle at 1px 1px,#fff 1px,transparent 0);background-size:22px 22px"></div>

                {{-- emojis flotantes (ocultos en móvil para no estorbar) --}}
                <span class="hero-emoji" style="left:80%;top:16%;animation-delay:0s">🍔</span>
                <span class="hero-emoji" style="left:90%;top:54%;animation-delay:1.1s">🍕</span>
                <span class="hero-emoji" style="left:72%;top:78%;animation-delay:2.2s">🌮</span>

                <div class="relative z-10 p-8 sm:p-11 max-w-xl">
                    <span class="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.15em] text-brand-300 bg-brand-500/10 border border-brand-500/25 px-3 py-1.5 rounded-full">
                        <span class="hero-dot w-1.5 h-1.5 rounded-full bg-brand-400"></span>
                        Domicilios en Ubaté
                    </span>

                    <h3 class="mt-5 text-4xl sm:text-5xl font-black text-white leading-[1.05] tracking-tight">
                        El sabor de Ubaté,<br>
                        <span class="text-gradient">en tu puerta</span> 🛵
                    </h3>

                    <p class="text-white/55 text-sm sm:text-base mt-4 leading-relaxed max-w-md">
                        Pide a los mejores negocios locales y sigue tu domicilio en tiempo real. Sin comisiones abusivas.
                    </p>

                    {{-- Badges de confianza --}}
                    <div class="flex flex-wrap items-center gap-2 mt-6">
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1.5 rounded-full">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            Envío gratis
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-brand-300 bg-brand-500/10 border border-brand-500/20 px-3 py-1.5 rounded-full">
                            🏪 Negocios locales
                        </span>
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-300 bg-blue-500/10 border border-blue-500/20 px-3 py-1.5 rounded-full">
                            ⚡ Tiempo real
                        </span>
                    </div>

                    {{-- CTA --}}
                    <div class="flex flex-wrap items-center gap-3 mt-8">
                        <a href="{{ route('cliente.restaurantes.index') }}" class="btn-primary px-7 py-3.5 text-base">
                            Ver restaurantes
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                            </svg>
                        </a>
                        @if(($totalRestaurantes ?? 0) > 0)
                            <span class="text-white/45 text-sm font-semibold">
                                <span class="text-white font-black">{{ $totalRestaurantes }}</span>
                                {{ $totalRestaurantes === 1 ? 'restaurante activo' : 'restaurantes activos' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Pedido en curso ──────────────────────────────── --}}
            @if($pedidoActivo ?? null)
                <a href="{{ route('cliente.pedidos.show', $pedidoActivo) }}"
                   class="card block p-5 hover:-translate-y-0.5 group relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-brand-500/10 rounded-full -translate-y-1/2 translate-x-1/3 blur-2xl"></div>
                    <div class="relative flex items-center gap-4">
                        <span class="relative flex h-3 w-3 shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-brand-500"></span>
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-black text-white">Pedido #{{ $pedidoActivo->id }}</p>
                                <span class="{{ $pedidoActivo->estadoBadgeClass() }}">{{ $pedidoActivo->estadoLabel() }}</span>
                            </div>
                            <p class="text-zinc-400 text-sm font-semibold mt-0.5 truncate">{{ $pedidoActivo->restaurante->nombre }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="font-black text-white">${{ number_format($pedidoActivo->total, 0, ',', '.') }}</p>
                            <p class="text-brand-400 text-xs font-bold group-hover:translate-x-0.5 transition-transform">Seguir →</p>
                        </div>
                    </div>
                </a>
            @endif

            {{-- ── Acceso rápido ────────────────────────────────── --}}
            <div>
                <h3 class="text-xs font-black text-zinc-500 uppercase tracking-widest mb-4">Acceso rápido</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                    <a href="{{ route('cliente.restaurantes.index') }}"
                       class="qa-reveal card p-5 flex items-center gap-4 group hover:-translate-y-0.5" style="animation-delay:0ms">
                        <div class="w-12 h-12 bg-gradient-to-br from-brand-400 to-brand-600 rounded-xl flex items-center justify-center text-white shadow-brand-sm shrink-0
                                    group-hover:scale-110 transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-zinc-100 group-hover:text-brand-400 transition-colors">Ver Restaurantes</p>
                            <p class="text-zinc-500 text-xs mt-0.5">Explora el menú de cada local</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-zinc-600 group-hover:text-brand-400 ml-auto shrink-0 transition-colors">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </a>

                    <a href="{{ route('cliente.pedidos.index') }}"
                       class="qa-reveal card p-5 flex items-center gap-4 group hover:-translate-y-0.5" style="animation-delay:80ms">
                        <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl flex items-center justify-center text-white shadow-sm shrink-0
                                    group-hover:scale-110 transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-zinc-100 group-hover:text-amber-400 transition-colors">Mis Pedidos</p>
                            <p class="text-zinc-500 text-xs mt-0.5">Historial de tus órdenes</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-zinc-600 group-hover:text-amber-400 ml-auto shrink-0 transition-colors">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </a>

                    <a href="{{ route('cliente.pedidos.index') }}"
                       class="qa-reveal card p-5 flex items-center gap-4 group hover:-translate-y-0.5" style="animation-delay:160ms">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl flex items-center justify-center text-white shadow-sm shrink-0
                                    group-hover:scale-110 transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-zinc-100 group-hover:text-emerald-400 transition-colors">Seguir Pedido</p>
                            <p class="text-zinc-500 text-xs mt-0.5">Estado en tiempo real</p>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-zinc-600 group-hover:text-emerald-400 ml-auto shrink-0 transition-colors">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- ── Estado vacío (sin pedido activo) ──────────────── --}}
            @unless($pedidoActivo ?? null)
                <div class="card overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center justify-between">
                        <h3 class="font-bold text-zinc-100">Pedido en curso</h3>
                        <span class="badge-gray">Sin actividad</span>
                    </div>
                    <div class="flex flex-col items-center justify-center py-14 text-center px-6">
                        <div class="w-16 h-16 bg-gradient-to-br from-zinc-800 to-zinc-700 rounded-2xl flex items-center justify-center mb-4 border border-white/[0.06]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-zinc-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                            </svg>
                        </div>
                        <p class="font-semibold text-zinc-200">No tienes pedidos activos</p>
                        <p class="text-sm text-zinc-500 mt-1 max-w-xs">Cuando hagas un pedido podrás seguirlo en tiempo real desde aquí.</p>
                        <a href="{{ route('cliente.restaurantes.index') }}" class="btn-primary mt-5">
                            Hacer un pedido
                        </a>
                    </div>
                </div>
            @endunless

        </div>
    </div>
</x-app-layout>
