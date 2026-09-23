<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-white text-xl">Panel de Administración</h2>
                <p class="text-sm text-zinc-500 mt-0.5">Visión general de la plataforma · Ubaté</p>
            </div>
            <span class="badge-orange">Admin</span>
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
        .hero-bg    { background: linear-gradient(135deg,#0a1018 0%,#0d0d0f 50%,#140a16 100%); }
        .hero-orb   { position:absolute; border-radius:9999px; filter: blur(64px); pointer-events:none; }
        .hero-orb-1 { width:300px; height:300px; background:#2563eb; opacity:.45; top:-90px; right:-30px; animation: heroDrift1 15s ease-in-out infinite; }
        .hero-orb-2 { width:240px; height:240px; background:#7c3aed; opacity:.4; bottom:-110px; left:6%; animation: heroDrift2 19s ease-in-out infinite; }
        .hero-orb-3 { width:220px; height:220px; background:#F25C2E; opacity:.32; top:24%; left:44%; animation: heroDrift3 22s ease-in-out infinite; }
        .hero-emoji { position:absolute; font-size:1.6rem; opacity:.85; filter: drop-shadow(0 8px 12px rgba(0,0,0,.45)); animation: heroFloat 6s ease-in-out infinite; pointer-events:none; }
        @media (min-width:640px) { .hero-emoji { font-size:2.1rem; opacity:.9; } }
        .hero-dot   { animation: heroPulse 1.8s ease-in-out infinite; }
        .admin-reveal { opacity:0; animation: heroReveal .6s cubic-bezier(.22,1,.36,1) both; }
    </style>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- ── Hero ─────────────────────────────────────────── --}}
            <div class="hero-card relative overflow-hidden rounded-3xl border border-white/10 shadow-2xl">
                <div class="hero-bg absolute inset-0"></div>
                <div class="hero-orb hero-orb-1"></div>
                <div class="hero-orb hero-orb-2"></div>
                <div class="hero-orb hero-orb-3"></div>
                <div class="absolute inset-0 opacity-[0.04]" style="background-image:radial-gradient(circle at 1px 1px,#fff 1px,transparent 0);background-size:22px 22px"></div>

                <span class="hero-emoji" style="left:82%;top:18%;animation-delay:0s">📊</span>
                <span class="hero-emoji" style="left:91%;top:58%;animation-delay:1.3s">⚙️</span>

                <div class="relative z-10 p-8 sm:p-10 max-w-xl">
                    <span class="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.15em] text-blue-300 bg-blue-500/10 border border-blue-500/25 px-3 py-1.5 rounded-full">
                        <span class="hero-dot w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                        Centro de control · Ubaté
                    </span>

                    <h3 class="mt-5 text-4xl sm:text-5xl font-black text-white leading-[1.05] tracking-tight">
                        Hola, <span class="text-gradient">{{ explode(' ', auth()->user()->name)[0] }}</span> 🛡️
                    </h3>

                    <p class="text-white/55 text-sm sm:text-base mt-4 leading-relaxed max-w-md">
                        Supervisa la actividad de la plataforma y gestiona los usuarios y negocios de Ubaté desde un solo lugar.
                    </p>

                    <div class="flex flex-wrap items-center gap-3 mt-8">
                        <a href="{{ route('admin.usuarios.index') }}" class="btn-primary px-7 py-3.5 text-base">
                            Gestionar usuarios
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                            </svg>
                        </a>
                        <span class="text-white/45 text-sm font-semibold">
                            <span class="text-white font-black">{{ $stats['usuarios'] }}</span> usuarios ·
                            <span class="text-white font-black">{{ $stats['restaurantes'] }}</span> negocios
                        </span>
                    </div>
                </div>
            </div>

            {{-- Stat cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                <div class="stat-card card-interactive admin-reveal bg-gradient-to-br from-blue-600 to-blue-800" style="animation-delay:0ms">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 blur-2xl"></div>
                    <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center mb-4 border border-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white/90">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                        </svg>
                    </div>
                    <p class="text-white/60 text-xs font-semibold uppercase tracking-wide">Usuarios totales</p>
                    <p class="text-3xl font-black text-white mt-1">{{ $stats['usuarios'] }}</p>
                    <p class="text-white/40 text-xs mt-2">Registros en la plataforma</p>
                </div>

                <div class="stat-card card-interactive admin-reveal bg-gradient-to-br from-brand-600 to-brand-800" style="animation-delay:80ms">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 blur-2xl"></div>
                    <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center mb-4 border border-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white/90">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                        </svg>
                    </div>
                    <p class="text-white/60 text-xs font-semibold uppercase tracking-wide">Restaurantes activos</p>
                    <p class="text-3xl font-black text-white mt-1">{{ $stats['restaurantes'] }}</p>
                    <p class="text-white/40 text-xs mt-2">Negocios registrados</p>
                </div>

                <div class="stat-card card-interactive admin-reveal bg-gradient-to-br from-violet-600 to-violet-800" style="animation-delay:160ms">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 blur-2xl"></div>
                    <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center mb-4 border border-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white/90">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                        </svg>
                    </div>
                    <p class="text-white/60 text-xs font-semibold uppercase tracking-wide">Pedidos hoy</p>
                    <p class="text-3xl font-black text-white mt-1">{{ $stats['pedidosHoy'] }}</p>
                    <p class="text-white/40 text-xs mt-2">
                        {{ $stats['pedidosHoy'] === 0 ? 'Sin actividad todavía' : 'Pedidos del día' }}
                    </p>
                </div>

                <div class="stat-card card-interactive admin-reveal bg-gradient-to-br from-emerald-600 to-emerald-800" style="animation-delay:240ms">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 blur-2xl"></div>
                    <div class="w-10 h-10 bg-white/15 rounded-xl flex items-center justify-center mb-4 border border-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-white/90">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                        </svg>
                    </div>
                    <p class="text-white/60 text-xs font-semibold uppercase tracking-wide">Ingresos hoy</p>
                    <p class="text-3xl font-black text-white mt-1">
                        ${{ number_format($stats['ingresosHoy'], 0, ',', '.') }}
                    </p>
                    <p class="text-white/40 text-xs mt-2">Solo pedidos entregados</p>
                </div>
            </div>

            {{-- Actividad reciente --}}
            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-white/[0.08] flex items-center justify-between">
                    <h3 class="font-bold text-white">Actividad reciente</h3>
                    @if($actividadReciente->isNotEmpty())
                        <span class="badge-gray">Últimos {{ $actividadReciente->count() }} pedidos</span>
                    @else
                        <span class="badge-gray">Sin registros</span>
                    @endif
                </div>

                @if($actividadReciente->isEmpty())
                    <div class="flex flex-col items-center justify-center py-14 text-center px-6">
                        <div class="w-16 h-16 bg-zinc-800 rounded-2xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-zinc-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                            </svg>
                        </div>
                        <p class="font-semibold text-zinc-400">No hay actividad registrada todavía</p>
                        <p class="text-sm text-zinc-600 mt-1 max-w-xs">Los pedidos de la plataforma aparecerán aquí.</p>
                    </div>
                @else
                    @foreach($actividadReciente as $pedido)
                        <div class="flex items-center gap-4 px-6 py-4 border-b border-white/[0.04] last:border-0">
                            {{-- Badge estado --}}
                            <span class="{{ $pedido->estadoBadgeClass() }} shrink-0">
                                {{ $pedido->estadoLabel() }}
                            </span>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-zinc-200 truncate">
                                    {{ $pedido->cliente->name }}
                                    <span class="text-zinc-500 font-normal">en</span>
                                    {{ $pedido->restaurante->nombre }}
                                </p>
                                <p class="text-xs text-zinc-600 mt-0.5">
                                    Pedido #{{ $pedido->id }} · {{ $pedido->created_at->diffForHumans() }}
                                </p>
                            </div>

                            {{-- Total --}}
                            <span class="font-bold text-zinc-300 text-sm shrink-0">
                                ${{ number_format($pedido->total, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
