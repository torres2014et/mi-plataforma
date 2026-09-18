<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi Plataforma — Domicilios en Ubaté</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html { scroll-behavior: smooth; }
        @keyframes orb { 0%,100%{transform:translate(0,0) scale(1)} 33%{transform:translate(40px,-30px) scale(1.08)} 66%{transform:translate(-25px,15px) scale(0.93)} }
        .orb-a { animation: orb 9s ease-in-out infinite; }
        .orb-b { animation: orb 13s ease-in-out infinite reverse; }
        @keyframes foodFloat {
            0%,100% { transform: translateY(0px) rotate(-4deg) scale(1); }
            33%     { transform: translateY(-24px) rotate(5deg) scale(1.06); }
            66%     { transform: translateY(-10px) rotate(-2deg) scale(0.97); }
        }
        @keyframes foodDrift {
            0%,100% { transform: translateY(0px) translateX(0px) rotate(8deg); }
            40%     { transform: translateY(-20px) translateX(10px) rotate(-7deg); }
            80%     { transform: translateY(-8px)  translateX(-4px) rotate(3deg); }
        }
        @keyframes foodSway {
            0%,100% { transform: translateY(0px) rotate(0deg); }
            25%     { transform: translateY(-18px) rotate(6deg); }
            75%     { transform: translateY(-6px)  rotate(-4deg); }
        }
        @keyframes sparkle {
            0%,100% { transform: scale(1) rotate(0deg); opacity: 0.20; }
            50%     { transform: scale(1.4) rotate(180deg); opacity: 0.40; }
        }
        @keyframes glowPulseBrand {
            0%,100% { filter: drop-shadow(0 0 16px rgba(242,92,46,0.45)) drop-shadow(0 0 34px rgba(242,92,46,0.20)); }
            50%     { filter: drop-shadow(0 0 34px rgba(242,92,46,0.85)) drop-shadow(0 0 60px rgba(242,92,46,0.40)); }
        }
        @keyframes glowPulseAmber {
            0%,100% { filter: drop-shadow(0 0 16px rgba(255,165,0,0.40)) drop-shadow(0 0 32px rgba(255,120,0,0.18)); }
            50%     { filter: drop-shadow(0 0 32px rgba(255,165,0,0.80)) drop-shadow(0 0 56px rgba(255,120,0,0.38)); }
        }
    </style>
</head>
<body class="font-sans antialiased overflow-x-hidden">

{{-- ══════════════════════════════════════════════
     HEADER
══════════════════════════════════════════════ --}}
<header x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 60"
        :class="scrolled ? 'bg-[#0D0D0F]/95 backdrop-blur-sm border-b border-white/[0.07] shadow-[0_1px_0_rgba(255,255,255,0.04)]' : 'bg-transparent'"
        class="fixed top-0 left-0 right-0 z-50 transition-all duration-500">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

        <a href="{{ route('welcome') }}" class="flex items-center gap-2.5 group">
            <x-application-logo class="w-9 h-9 transition-transform group-hover:scale-110 duration-300" />
            <div class="flex flex-col leading-none">
                <span class="font-black text-lg text-white">Mi Plataforma</span>
                <span class="text-xs font-medium tracking-wider text-white/40">UBATÉ · DELIVERY</span>
            </div>
        </a>

        <nav class="hidden md:flex items-center gap-1">
            <a href="#como-funciona" class="px-4 py-2 text-base font-medium rounded-xl text-white/55 hover:text-white hover:bg-white/10 transition-all duration-200">Cómo funciona</a>
            <a href="#restaurantes"  class="px-4 py-2 text-base font-medium rounded-xl text-white/55 hover:text-white hover:bg-white/10 transition-all duration-200">Restaurantes</a>
            <a href="#negocios"      class="px-4 py-2 text-base font-medium rounded-xl text-white/55 hover:text-white hover:bg-white/10 transition-all duration-200">Para negocios</a>
        </nav>

        <div class="flex items-center gap-2">
            @auth
                <a href="{{ url('/dashboard') }}" class="btn-primary">Mi panel →</a>
            @else
                <a href="{{ route('login') }}"    class="px-4 py-2 text-base font-semibold rounded-xl text-white/60 hover:text-white hover:bg-white/10 transition-all duration-200">Ingresar</a>
                <a href="{{ route('register') }}" class="btn-primary">Registrarse</a>
            @endauth
        </div>
    </div>
</header>

{{-- ══════════════════════════════════════════════
     HERO
══════════════════════════════════════════════ --}}
<section class="relative min-h-screen flex flex-col justify-center overflow-hidden"
         style="background-color:#0D0D0F;background-image:url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1920&q=80');background-size:cover;background-position:center">

    {{-- Dark overlay: muy oscuro en la izquierda (texto), más transparente en la derecha (se ve la foto) --}}
    <div class="absolute inset-0 pointer-events-none" style="background:linear-gradient(108deg,rgba(13,13,15,0.97) 0%,rgba(13,13,15,0.93) 40%,rgba(13,13,15,0.72) 65%,rgba(13,13,15,0.50) 100%)"></div>

    {{-- Overlay de color brand para coherencia visual --}}
    <div class="absolute pointer-events-none" style="top:-10%;right:-5%;width:700px;height:700px;background:radial-gradient(ellipse,rgba(242,92,46,0.18) 0%,transparent 65%);filter:blur(80px)"></div>
    <div class="absolute pointer-events-none" style="bottom:0;left:0;width:500px;height:350px;background:radial-gradient(ellipse,rgba(200,60,10,0.10) 0%,transparent 65%);filter:blur(60px)"></div>

    {{-- Dot grid --}}
    <div class="absolute inset-0 pointer-events-none" style="background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,0.025) 1px,transparent 0);background-size:32px 32px"></div>

    {{-- Viñeta inferior --}}
    <div class="absolute bottom-0 left-0 right-0 h-48 pointer-events-none" style="background:linear-gradient(to top,rgba(13,13,15,0.95) 0%,transparent 100%)"></div>

    {{-- Orbs animados --}}
    <div class="orb-a absolute top-1/3 left-1/4 w-[600px] h-[600px] rounded-full pointer-events-none" style="background:radial-gradient(circle,rgba(242,92,46,0.10) 0%,transparent 68%);filter:blur(50px)"></div>
    <div class="orb-b absolute bottom-1/4 right-1/5 w-[450px] h-[450px] rounded-full pointer-events-none" style="background:radial-gradient(circle,rgba(255,140,50,0.07) 0%,transparent 70%);filter:blur(70px)"></div>

    <div class="relative max-w-7xl mx-auto px-6 pt-32 pb-24 w-full">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

            {{-- Left: Copy --}}
            <div>
                <div class="inline-flex items-center gap-2.5 glass text-white text-sm font-semibold px-4 py-2 rounded-full mb-8">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                    </span>
                    Entregando ahora en Ubaté, Cundinamarca
                </div>

                <h1 class="font-black text-white leading-[0.98] mb-6 tracking-tight" style="font-size:clamp(3rem, 6.5vw, 7rem)">
                    El sabor de<br>
                    <span class="text-gradient">Ubaté,</span><br>
                    en tu puerta
                </h1>

                <p class="text-white/45 text-xl leading-relaxed mb-10 max-w-lg">
                    Conectamos los mejores restaurantes locales con los clientes de Ubaté. Sin intermediarios nacionales, con seguimiento en tiempo real.
                </p>

                {{-- Search bar — dark --}}
                <div class="flex items-center bg-zinc-900 border border-white/10 rounded-2xl overflow-hidden max-w-lg mb-8 focus-within:border-brand-500/50 transition-colors duration-200 shadow-[0_0_0_4px_rgba(242,92,46,0)] focus-within:shadow-[0_0_0_3px_rgba(242,92,46,0.12)]">
                    <div class="flex items-center gap-2 px-4 py-4 border-r border-white/10 shrink-0">
                        <svg class="w-4 h-4 text-brand-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                        <span class="text-base font-bold text-white">Ubaté</span>
                    </div>
                    <div class="flex-1 px-4 py-4">
                        <span class="text-base text-zinc-600">¿Qué se te antoja hoy?</span>
                    </div>
                    <a href="{{ route('register') }}" class="px-6 py-4 bg-brand-500 hover:bg-brand-600 text-white text-base font-bold transition-colors shrink-0">
                        Buscar
                    </a>
                </div>

                {{-- Quick tags --}}
                <div class="flex flex-wrap gap-2">
                    @foreach(['🍔 Hamburguesas', '🍕 Pizzas', '🥗 Ensaladas', '🍗 Pollo', '🥤 Bebidas'] as $tag)
                        <a href="{{ route('register') }}" class="px-4 py-1.5 glass text-white/60 text-sm font-semibold rounded-full hover:bg-white/15 hover:text-white transition-all duration-200">
                            {{ $tag }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Right: App mockup (pure CSS/HTML) --}}
            <div class="hidden lg:flex justify-center relative">
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="w-80 h-80 rounded-full" style="background:radial-gradient(circle,rgba(242,92,46,0.12) 0%,transparent 70%);filter:blur(40px)"></div>
                </div>

                <div style="transform:scale(1.3);transform-origin:center">
                <div class="relative w-[340px] animate-float">
                    {{-- Main tracking card --}}
                    <div class="bg-zinc-900 border border-white/10 rounded-3xl overflow-hidden shadow-[0_32px_80px_rgba(0,0,0,0.6)]">
                        {{-- Fake status bar --}}
                        <div class="bg-zinc-950 px-5 pt-4 pb-3 flex items-center justify-between">
                            <span class="text-[11px] text-zinc-600 font-semibold">9:41</span>
                            <div class="w-20 h-4 bg-zinc-900 rounded-full"></div>
                            <div class="flex items-center gap-1 text-zinc-600">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M1 9l2 2c4.97-4.97 13.03-4.97 18 0l2-2C16.93 2.93 7.08 2.93 1 9zm8 8l3 3 3-3c-1.65-1.66-4.34-1.66-6 0zm-4-4l2 2c2.76-2.76 7.24-2.76 10 0l2-2C15.14 9.14 8.87 9.14 5 13z"/></svg>
                                <svg class="w-3.5 h-2.5" viewBox="0 0 14 10" fill="none"><rect x="0.5" y="0.5" width="11" height="9" rx="1.5" stroke="currentColor"/><rect x="2" y="2" width="7" height="6" rx="0.5" fill="currentColor"/><path d="M12 3.5v3a1.5 1.5 0 000-3z" fill="currentColor"/></svg>
                            </div>
                        </div>

                        <div class="px-5 pb-6 pt-4 space-y-4">
                            {{-- Order header --}}
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-zinc-500 text-xs font-medium">Tu pedido</p>
                                    <p class="text-white font-black text-base mt-0.5">Combo Especial #47</p>
                                </div>
                                <span class="text-xs font-bold text-green-400 bg-green-400/10 border border-green-400/20 px-2.5 py-1 rounded-full">En camino</span>
                            </div>

                            {{-- Progress --}}
                            <div>
                                <div class="flex justify-between text-[10px] text-zinc-600 mb-2 font-medium">
                                    <span>Confirmado</span><span>Preparando</span><span>En camino</span><span>Entregado</span>
                                </div>
                                <div class="w-full bg-zinc-800 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-gradient-to-r from-brand-500 to-orange-400 h-full rounded-full" style="width:75%"></div>
                                </div>
                            </div>

                            {{-- Driver --}}
                            <div class="flex items-center justify-between bg-zinc-800/60 border border-white/[0.05] rounded-2xl p-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-gradient-to-br from-brand-600 to-orange-500 rounded-full flex items-center justify-center text-white font-black text-xs">C</div>
                                    <div>
                                        <p class="text-white text-xs font-bold">Carlos M.</p>
                                        <p class="text-zinc-500 text-[10px] font-medium">Domiciliario</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-brand-400 font-black text-sm">~8 min</p>
                                    <p class="text-zinc-700 text-[10px]">estimado</p>
                                </div>
                            </div>

                            {{-- Map mock --}}
                            <div class="h-32 rounded-2xl relative overflow-hidden bg-zinc-800">
                                <div class="absolute inset-0" style="background-image:linear-gradient(rgba(255,255,255,0.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.03) 1px,transparent 1px);background-size:20px 20px"></div>
                                {{-- Roads --}}
                                <div class="absolute top-1/2 left-0 right-0 h-px bg-zinc-700"></div>
                                <div class="absolute top-[35%] left-0 right-0 h-px bg-zinc-700/50"></div>
                                <div class="absolute left-1/3 top-0 bottom-0 w-px bg-zinc-700"></div>
                                <div class="absolute left-2/3 top-0 bottom-0 w-px bg-zinc-700"></div>
                                {{-- Route --}}
                                <svg class="absolute inset-0 w-full h-full" viewBox="0 0 340 128" fill="none" preserveAspectRatio="none">
                                    <path d="M 40 64 Q 100 64 120 45 Q 150 24 200 64" stroke="#f25c2e" stroke-width="2" stroke-dasharray="4 3" opacity="0.6"/>
                                </svg>
                                {{-- Moto marker --}}
                                <div class="absolute top-[46%] left-[56%] -translate-x-1/2 -translate-y-1/2">
                                    <div class="w-7 h-7 bg-brand-500 rounded-full border-2 border-white flex items-center justify-center shadow-brand-sm text-[10px]">🛵</div>
                                </div>
                                {{-- Home marker --}}
                                <div class="absolute top-1/2 right-6 -translate-y-1/2">
                                    <div class="w-6 h-6 bg-green-500 rounded-full border-2 border-white flex items-center justify-center text-[9px] shadow-sm">🏠</div>
                                </div>
                                {{-- Start marker --}}
                                <div class="absolute top-1/2 left-8 -translate-y-1/2">
                                    <div class="w-5 h-5 bg-zinc-600 rounded-full border border-white/30 flex items-center justify-center text-[8px]">🏪</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Floating speed badge --}}
                    <div class="absolute -bottom-5 -left-8 glass border border-white/20 rounded-2xl px-4 py-3 shadow-card-dark backdrop-blur-xl">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 bg-brand-500/20 border border-brand-500/30 rounded-xl flex items-center justify-center text-sm">⚡</div>
                            <div>
                                <p class="text-white font-black text-sm leading-none">28 min</p>
                                <p class="text-zinc-500 text-[10px] mt-0.5">Tiempo promedio</p>
                            </div>
                        </div>
                    </div>

                    {{-- Floating rating badge --}}
                    <div class="absolute -top-5 -right-6 glass border border-white/20 rounded-2xl px-4 py-3 shadow-card-dark backdrop-blur-xl">
                        <div class="flex items-center gap-2">
                            <span class="text-yellow-400">⭐</span>
                            <div>
                                <p class="text-white font-black text-sm leading-none">4.9</p>
                                <p class="text-zinc-500 text-[10px] mt-0.5">Rating</p>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scroll hint --}}
    <div class="relative flex justify-center pb-10">
        <div class="flex flex-col items-center gap-2 animate-bounce opacity-30">
            <span class="text-white text-xs tracking-widest uppercase">Descubre más</span>
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════
     STATS BAR
══════════════════════════════════════════════ --}}
<section class="border-y border-white/[0.07] bg-zinc-950/70 backdrop-blur-sm">
    <div class="max-w-5xl mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-white/[0.07]">
            @foreach([
                ['2+',   'Restaurantes locales'],
                ['<30',  'Minutos de entrega'],
                ['0%',   'Comisión inicial'],
                ['Live', 'Seguimiento en tiempo real'],
            ] as [$stat, $label])
                <div class="flex flex-col items-center justify-center py-8 gap-2 text-center">
                    <span class="text-3xl font-black" style="background:linear-gradient(135deg,#fff 0%,rgba(255,255,255,0.55) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">{{ $stat }}</span>
                    <span class="text-sm text-zinc-600 font-medium leading-tight max-w-[110px]">{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════
     RESTAURANTES DESTACADOS
══════════════════════════════════════════════ --}}
<section id="restaurantes" class="bg-[#0D0D0F] py-24 px-6">
    <div class="max-w-7xl mx-auto">

        <div class="flex items-end justify-between mb-4">
            <div>
                <p class="text-brand-500 text-sm font-bold uppercase tracking-widest">En la plataforma</p>
                <h2 class="text-5xl font-black text-white mt-1">Restaurantes en Ubaté</h2>
            </div>
            <a href="{{ route('register') }}" class="hidden sm:flex items-center gap-1.5 text-base font-bold text-brand-500 hover:text-brand-400 transition-colors">
                Ver todos <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>

        {{-- Category pills --}}
        <div class="flex gap-2 overflow-x-auto scrollbar-hide pb-6 mt-6">
            @foreach(['🍽️ Todos', '🍔 Hamburguesas', '🍕 Pizzas', '🥗 Saludable', '🍗 Pollo', '🥤 Bebidas', '🌮 Rápido'] as $i => $cat)
                <button class="px-5 py-2.5 rounded-full text-base font-semibold whitespace-nowrap transition-all duration-200
                    {{ $i === 0 ? 'bg-brand-500 text-white shadow-brand-sm' : 'bg-zinc-900 text-zinc-500 border border-white/10 hover:border-brand-500/40 hover:text-brand-400' }}">
                    {{ $cat }}
                </button>
            @endforeach
        </div>

        {{-- Restaurant cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

            {{-- Card 1 — warm orange gradient --}}
            <div class="group card rounded-3xl overflow-hidden cursor-pointer hover:-translate-y-1.5 transition-all duration-500">
                <div class="relative h-48 overflow-hidden" style="background-image:url('https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=800&q=70');background-size:cover;background-position:center">
                    <div class="absolute inset-0" style="background:linear-gradient(135deg,rgba(28,8,0,0.55) 0%,rgba(61,21,0,0.40) 55%,rgba(24,8,0,0.70) 100%)"></div>
                    <div class="absolute inset-0" style="background-image:radial-gradient(circle at 30% 55%,rgba(242,92,46,0.28) 0%,transparent 58%),radial-gradient(circle at 78% 20%,rgba(255,160,40,0.18) 0%,transparent 48%)"></div>
                    <div class="absolute inset-0 opacity-15" style="background-image:linear-gradient(rgba(255,255,255,0.07) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.07) 1px,transparent 1px);background-size:24px 24px"></div>
                    <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-zinc-900 to-transparent"></div>
                    <div class="absolute top-4 left-4 right-4 flex justify-between items-start">
                        <span class="bg-black/50 backdrop-blur-sm text-white text-sm font-bold px-3 py-1.5 rounded-full border border-white/10 flex items-center gap-1">
                            ⭐ 4.8 <span class="text-white/40 font-normal">(128)</span>
                        </span>
                        <span class="bg-green-500 text-white text-sm font-bold px-3 py-1.5 rounded-full">Abierto</span>
                    </div>
                    <div class="absolute bottom-4 right-4">
                        <span class="glass-dark text-white text-sm font-bold px-3 py-1.5 rounded-full">25–35 min</span>
                    </div>
                    <div class="absolute bottom-4 left-4">
                        <span class="text-white/30 text-[10px] font-black uppercase tracking-widest">Hamburguesas</span>
                    </div>
                </div>
                <div class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-black text-white text-2xl truncate group-hover:text-brand-400 transition-colors duration-200">BurgerHouse Ubaté</h3>
                            <p class="text-zinc-600 text-base mt-0.5">Hamburguesas · Perros calientes · Combos</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-brand-600 to-orange-500 flex items-center justify-center font-black text-white text-lg shrink-0 shadow-brand-sm">B</div>
                    </div>
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/[0.06]">
                        <span class="text-sm text-zinc-600">Mín: <strong class="text-zinc-300">$10.000</strong></span>
                        <span class="badge-orange">Envío gratis</span>
                    </div>
                </div>
            </div>

            {{-- Card 2 — purple gradient --}}
            <div class="group card rounded-3xl overflow-hidden cursor-pointer hover:-translate-y-1.5 transition-all duration-500">
                <div class="relative h-48 overflow-hidden" style="background-image:url('https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=70');background-size:cover;background-position:center">
                    <div class="absolute inset-0" style="background:linear-gradient(135deg,rgba(10,0,24,0.60) 0%,rgba(32,0,64,0.45) 55%,rgba(13,0,34,0.72) 100%)"></div>
                    <div class="absolute inset-0" style="background-image:radial-gradient(circle at 65% 35%,rgba(139,92,246,0.25) 0%,transparent 55%),radial-gradient(circle at 20% 70%,rgba(242,92,46,0.18) 0%,transparent 48%)"></div>
                    <div class="absolute inset-0 opacity-15" style="background-image:linear-gradient(rgba(255,255,255,0.06) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.06) 1px,transparent 1px);background-size:24px 24px"></div>
                    <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-zinc-900 to-transparent"></div>
                    <div class="absolute top-4 left-4 right-4 flex justify-between items-start">
                        <span class="bg-black/50 backdrop-blur-sm text-white text-sm font-bold px-3 py-1.5 rounded-full border border-white/10 flex items-center gap-1">
                            ⭐ 4.7 <span class="text-white/40 font-normal">(89)</span>
                        </span>
                        <span class="bg-green-500 text-white text-sm font-bold px-3 py-1.5 rounded-full">Abierto</span>
                    </div>
                    <div class="absolute bottom-4 right-4">
                        <span class="glass-dark text-white text-sm font-bold px-3 py-1.5 rounded-full">30–45 min</span>
                    </div>
                    <div class="absolute bottom-4 left-4">
                        <span class="text-white/30 text-[10px] font-black uppercase tracking-widest">Carnes · Parrilla</span>
                    </div>
                </div>
                <div class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-black text-white text-2xl truncate group-hover:text-brand-400 transition-colors duration-200">La Carnita Criolla</h3>
                            <p class="text-zinc-600 text-base mt-0.5">Carnes · Parrilla · Combos</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-700 to-violet-600 flex items-center justify-center font-black text-white text-lg shrink-0">L</div>
                    </div>
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/[0.06]">
                        <span class="text-sm text-zinc-600">Mín: <strong class="text-zinc-300">$15.000</strong></span>
                        <span class="badge-orange">Popular 🔥</span>
                    </div>
                </div>
            </div>

            {{-- Card 3 — green gradient --}}
            <div class="group card rounded-3xl overflow-hidden cursor-pointer hover:-translate-y-1.5 transition-all duration-500">
                <div class="relative h-48 overflow-hidden" style="background-image:url('https://images.unsplash.com/photo-1598103442097-8b74394b95c6?auto=format&fit=crop&w=800&q=70');background-size:cover;background-position:center">
                    <div class="absolute inset-0" style="background:linear-gradient(135deg,rgba(3,16,0,0.55) 0%,rgba(13,40,0,0.40) 55%,rgba(6,20,0,0.70) 100%)"></div>
                    <div class="absolute inset-0" style="background-image:radial-gradient(circle at 45% 30%,rgba(132,204,22,0.22) 0%,transparent 55%),radial-gradient(circle at 80% 70%,rgba(242,92,46,0.14) 0%,transparent 48%)"></div>
                    <div class="absolute inset-0 opacity-15" style="background-image:linear-gradient(rgba(255,255,255,0.06) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,0.06) 1px,transparent 1px);background-size:24px 24px"></div>
                    <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-zinc-900 to-transparent"></div>
                    <div class="absolute top-4 left-4 right-4 flex justify-between items-start">
                        <span class="bg-black/50 backdrop-blur-sm text-white text-sm font-bold px-3 py-1.5 rounded-full border border-white/10 flex items-center gap-1">
                            ⭐ 4.9 <span class="text-white/40 font-normal">(204)</span>
                        </span>
                        <span class="bg-green-500 text-white text-sm font-bold px-3 py-1.5 rounded-full">Abierto</span>
                    </div>
                    <div class="absolute bottom-4 right-4">
                        <span class="glass-dark text-white text-sm font-bold px-3 py-1.5 rounded-full">20–30 min</span>
                    </div>
                    <div class="absolute bottom-4 left-4">
                        <span class="text-white/30 text-[10px] font-black uppercase tracking-widest">Pollo asado</span>
                    </div>
                </div>
                <div class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-black text-white text-2xl truncate group-hover:text-brand-400 transition-colors duration-200">Pollos Don Luis</h3>
                            <p class="text-zinc-600 text-base mt-0.5">Pollo asado · Pernil · Acompañantes</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-lime-600 to-emerald-700 flex items-center justify-center font-black text-white text-lg shrink-0">P</div>
                    </div>
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/[0.06]">
                        <span class="text-sm text-zinc-600">Mín: <strong class="text-zinc-300">$12.000</strong></span>
                        <span class="badge-green">Más pedidos ⭐</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════
     CÓMO FUNCIONA
══════════════════════════════════════════════ --}}
<section id="como-funciona" class="bg-zinc-950 py-24 px-6 border-y border-white/[0.07]">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16">
            <p class="text-brand-500 text-sm font-bold uppercase tracking-widest">Sin complicaciones</p>
            <h2 class="text-5xl font-black text-white mt-2">Pide en 3 pasos</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">
            {{-- Connector --}}
            <div class="hidden md:block absolute top-14 left-[34%] right-[34%] h-px bg-gradient-to-r from-transparent via-brand-500/25 to-transparent z-0 pointer-events-none"></div>

            @foreach([
                ['1', 'Elige tu restaurante', 'Explora los negocios de Ubaté, su menú y precios directos sin intermediarios.', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['2', 'Haz tu pedido',        'Agrega lo que quieras, confirma tu dirección. Todo en menos de 2 minutos.',         'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                ['3', 'Recíbelo en casa',     'Un domiciliario local lo lleva a tu puerta. Síguelo en tiempo real.',                'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z'],
            ] as [$num, $title, $desc, $path])
                <div class="relative z-10 card p-8 text-center group hover:-translate-y-1 transition-all duration-300">
                    <div class="relative mb-6 flex justify-center">
                        <div class="w-16 h-16 rounded-2xl bg-brand-500/10 border border-brand-500/20 flex items-center justify-center group-hover:bg-brand-500/20 group-hover:border-brand-500/40 group-hover:shadow-[0_0_20px_rgba(242,92,46,0.15)] transition-all duration-300">
                            <svg class="w-7 h-7 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $path }}"/>
                            </svg>
                        </div>
                        <div class="absolute -top-3 -right-1 w-7 h-7 bg-brand-500 text-white rounded-full flex items-center justify-center text-xs font-black shadow-brand-xs border-2 border-zinc-950">{{ $num }}</div>
                    </div>
                    <h3 class="font-black text-white text-2xl mb-3">{{ $title }}</h3>
                    <p class="text-zinc-500 text-base leading-relaxed">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════
     TESTIMONIOS
══════════════════════════════════════════════ --}}
<section class="bg-[#0D0D0F] py-24 px-6">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-14">
            <p class="text-brand-500 text-sm font-bold uppercase tracking-widest">Lo que dicen</p>
            <h2 class="text-5xl font-black text-white mt-2">Clientes de Ubaté</h2>
            <p class="text-zinc-600 text-lg mt-3 max-w-lg mx-auto">Personas reales que ya piden con nosotros</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="card p-7 hover:-translate-y-1 transition-all duration-300">
                <div class="flex gap-1 mb-5">@for ($i = 0; $i < 5; $i++)<svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor</div>
                <p class="text-zinc-400 text-base leading-relaxed mb-6">"Me parece increíble que ahora pueda pedir comida sin salir de casa. El domiciliario llegó súper rápido y el combo venía tal y como lo pedí."</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-brand-600 to-orange-500 rounded-full flex items-center justify-center font-black text-white text-sm">LA</div>
                    <div><p class="text-base font-bold text-white">Laura Abril</p><p class="text-sm text-zinc-700">Ubaté, Cundinamarca</p></div>
                </div>
            </div>

            <div class="card p-7 md:-translate-y-3 hover:-translate-y-4 transition-all duration-300" style="border-color:rgba(242,92,46,0.18);box-shadow:0 0 0 1px rgba(242,92,46,0.12),0 4px 28px rgba(0,0,0,0.45)">
                <div class="flex gap-1 mb-5">@for ($i = 0; $i < 5; $i++)<svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor</div>
                <p class="text-zinc-400 text-base leading-relaxed mb-6">"Por fin una plataforma que apoya a los negocios del pueblo. El seguimiento en tiempo real da mucha tranquilidad. Ya pedí tres veces esta semana."</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-cyan-600 rounded-full flex items-center justify-center font-black text-white text-sm">CM</div>
                    <div><p class="text-base font-bold text-white">Carlos Moreno</p><p class="text-sm text-zinc-700">Ubaté, Cundinamarca</p></div>
                </div>
            </div>

            <div class="card p-7 hover:-translate-y-1 transition-all duration-300">
                <div class="flex gap-1 mb-5">@for ($i = 0; $i < 5; $i++)<svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor</div>
                <p class="text-zinc-400 text-base leading-relaxed mb-6">"El envío fue rápido y la comida llegó calientica. Además siento que estoy apoyando a los restaurantes del municipio. 100% recomendado."</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-emerald-600 to-teal-600 rounded-full flex items-center justify-center font-black text-white text-sm">SR</div>
                    <div><p class="text-base font-bold text-white">Sandra Rojas</p><p class="text-sm text-zinc-700">Ubaté, Cundinamarca</p></div>
                </div>
            </div>

        </div>

        <div class="mt-12 flex flex-col sm:flex-row items-center justify-center gap-6 text-center sm:text-left">
            <div>
                <p class="text-6xl font-black text-white">4.9</p>
                <div class="flex gap-1 justify-center sm:justify-start mt-1">@for ($i = 0; $i < 5; $i++)<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor</div>
            </div>
            <div class="hidden sm:block w-px h-12 bg-white/10"></div>
            <p class="text-zinc-600 text-base max-w-xs leading-relaxed">Calificación promedio basada en pedidos realizados en Ubaté durante el piloto.</p>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════
     PARA NEGOCIOS
══════════════════════════════════════════════ --}}
<section id="negocios" class="bg-zinc-950 py-24 px-6 overflow-hidden border-y border-white/[0.07]">
    <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">

            {{-- Dashboard mockup (pure CSS) --}}
            <div class="relative order-2 lg:order-1">
                <div class="absolute inset-0 rounded-3xl pointer-events-none" style="background:radial-gradient(ellipse at center,rgba(242,92,46,0.06) 0%,transparent 70%)"></div>

                <div class="relative bg-zinc-900 border border-white/10 rounded-3xl overflow-hidden shadow-[0_32px_80px_rgba(0,0,0,0.55)]">
                    {{-- Window chrome --}}
                    <div class="bg-zinc-950 px-5 py-3.5 border-b border-white/[0.07] flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <div class="w-3 h-3 rounded-full bg-red-500/70"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500/70"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500/70"></div>
                        </div>
                        <span class="text-zinc-600 text-xs font-medium">Panel del restaurante — BurgerHouse</span>
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                            <span class="text-green-400 text-[11px] font-bold">En vivo</span>
                        </div>
                    </div>

                    <div class="p-5 space-y-4">
                        {{-- Stat grid --}}
                        <div class="grid grid-cols-3 gap-3">
                            @foreach([
                                ['24',    'Pedidos hoy',  'from-brand-600 to-orange-500'],
                                ['$180K', 'Ingresos',     'from-purple-700 to-violet-600'],
                                ['4.9★',  'Rating',       'from-emerald-700 to-teal-600'],
                            ] as [$v, $l, $g])
                            <div class="bg-gradient-to-br {{ $g }} rounded-2xl p-4">
                                <p class="text-white font-black text-xl leading-none">{{ $v }}</p>
                                <p class="text-white/55 text-[11px] mt-1.5 font-semibold">{{ $l }}</p>
                            </div>
                            @endforeach
                        </div>

                        {{-- Bar chart --}}
                        <div class="bg-zinc-800/50 border border-white/[0.05] rounded-2xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-zinc-400 text-xs font-semibold">Pedidos esta semana</span>
                                <span class="text-brand-400 text-xs font-black">+24% ↑</span>
                            </div>
                            <div class="flex items-end gap-1.5 h-14">
                                @foreach([35,55,40,70,60,85,75] as $i => $h)
                                <div class="flex-1 rounded-t-md" style="height:{{ $h }}%;background:{{ $i === 5 ? 'linear-gradient(to top,#f25c2e,#fd7a42)' : 'rgba(255,255,255,0.07)' }}"></div>
                                @endforeach
                            </div>
                            <div class="flex mt-2">
                                @foreach(['L','M','X','J','V','S','D'] as $d)
                                <span class="text-zinc-700 text-[10px] flex-1 text-center font-medium">{{ $d }}</span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Orders list --}}
                        <div class="space-y-2">
                            @foreach([
                                ['Combo 2x1',           '$28.000', 'Preparando', 'text-yellow-400 bg-yellow-400/10 border-yellow-400/20'],
                                ['Hamburguesa clásica', '$15.000', 'Confirmado', 'text-blue-400 bg-blue-400/10 border-blue-400/20'],
                            ] as [$name, $price, $status, $cls])
                            <div class="flex items-center justify-between bg-zinc-800/40 border border-white/[0.04] rounded-xl px-4 py-3">
                                <div>
                                    <p class="text-white text-xs font-bold">{{ $name }}</p>
                                    <p class="text-zinc-600 text-[10px] mt-0.5">Hace 3 min</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-zinc-200 text-xs font-black">{{ $price }}</span>
                                    <span class="text-[10px] font-bold px-2 py-1 rounded-full border {{ $cls }}">{{ $status }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Floating savings badge --}}
                <div class="absolute -top-5 -left-5 bg-brand-500 rounded-2xl shadow-brand px-4 py-3 text-white">
                    <p class="text-[11px] font-semibold opacity-75">Ahorras vs. Rappi</p>
                    <p class="text-2xl font-black leading-tight">~30%</p>
                    <p class="text-[11px] opacity-60">en comisiones</p>
                </div>
            </div>

            {{-- Text --}}
            <div class="order-1 lg:order-2">
                <p class="text-brand-400 text-sm font-bold uppercase tracking-widest mb-3">Para negocios locales</p>
                <h2 class="text-5xl font-black text-white leading-tight mb-5">
                    Haz crecer tu restaurante<br>
                    <span class="text-gradient">sin pagar fortunas</span>
                </h2>
                <p class="text-zinc-500 text-lg leading-relaxed mb-8">
                    Las plataformas nacionales cobran hasta el 30% por pedido. Con Mi Plataforma controlás tus precios, tu menú y tu logística — directamente.
                </p>

                <div class="space-y-4 mb-10">
                    @foreach([
                        'Gestiona tu catálogo de productos en minutos',
                        'Recibe y confirma pedidos en tiempo real',
                        'Control total sin depender de intermediarios',
                        'Piloto inicial con establecimientos en Ubaté',
                    ] as $item)
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 bg-brand-500/12 border border-brand-500/25 rounded-full flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-zinc-300 text-base">{{ $item }}</p>
                    </div>
                    @endforeach
                </div>

                <a href="{{ route('login') }}" class="btn-primary px-8 py-4 text-base rounded-2xl">
                    Ingresar como restaurante
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════
     CTA FINAL
══════════════════════════════════════════════ --}}
<section class="relative py-28 px-6 overflow-hidden bg-[#0D0D0F]">
    {{-- Background atmosphere --}}
    <div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(ellipse 80% 60% at 50% 50%,rgba(242,92,46,0.10) 0%,transparent 70%)"></div>
    <div class="absolute inset-0 pointer-events-none" style="background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,0.03) 1px,transparent 0);background-size:32px 32px"></div>
    {{-- Decorative rings --}}
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] border border-white/[0.04] rounded-full pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] h-[400px] border border-white/[0.05] rounded-full pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[200px] h-[200px] border border-brand-500/10 rounded-full pointer-events-none"></div>

    <div class="relative max-w-3xl mx-auto text-center">
        <p class="text-brand-500/60 text-base font-bold uppercase tracking-widest mb-4">Comienza hoy</p>
        <h2 class="text-6xl font-black text-white mb-5 leading-tight">
            Empieza a pedir<br>en Ubaté ahora mismo
        </h2>
        <p class="text-zinc-600 text-xl mb-12 max-w-xl mx-auto leading-relaxed">
            Únete a la primera plataforma de delivery pensada para los negocios y clientes de Ubaté, Cundinamarca
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}"
               class="inline-flex items-center justify-center gap-2.5 px-10 py-[18px] bg-white text-gray-900 font-black rounded-2xl hover:shadow-brand hover:-translate-y-1 transition-all duration-300 text-lg">
                Crear cuenta gratis
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="{{ route('login') }}"
               class="inline-flex items-center justify-center gap-2 px-10 py-[18px] glass border border-white/12 text-white font-bold rounded-2xl hover:bg-white/12 transition-all duration-200 text-lg">
                Ya tengo cuenta
            </a>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════
     FOOTER
══════════════════════════════════════════════ --}}
<footer class="bg-zinc-950 px-6 pt-16 pb-8 border-t border-white/[0.07]">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-white/[0.07]">

            <div class="md:col-span-2">
                <div class="flex items-center gap-2.5 mb-4">
                    <x-application-logo class="w-9 h-9" />
                    <div>
                        <p class="text-white font-black text-xl leading-none">Mi Plataforma</p>
                        <p class="text-zinc-700 text-sm tracking-wider mt-0.5">UBATÉ · DELIVERY</p>
                    </div>
                </div>
                <p class="text-zinc-700 text-base leading-relaxed max-w-xs">
                    La primera plataforma de domicilios diseñada para los negocios y clientes de Ubaté, Cundinamarca. Sin intermediarios nacionales.
                </p>
            </div>

            <div>
                <p class="text-white font-bold text-sm uppercase tracking-widest mb-5">Plataforma</p>
                <div class="space-y-3">
                    <a href="{{ route('register') }}"   class="block text-zinc-600 text-base hover:text-white transition-colors">Registrarse</a>
                    <a href="{{ route('login') }}"      class="block text-zinc-600 text-base hover:text-white transition-colors">Iniciar sesión</a>
                    <a href="#como-funciona"            class="block text-zinc-600 text-base hover:text-white transition-colors">Cómo funciona</a>
                </div>
            </div>

            <div>
                <p class="text-white font-bold text-sm uppercase tracking-widest mb-5">Actores</p>
                <div class="space-y-3">
                    <a href="{{ route('register') }}" class="block text-zinc-600 text-base hover:text-white transition-colors">Para clientes</a>
                    <a href="{{ route('login') }}"    class="block text-zinc-600 text-base hover:text-white transition-colors">Para restaurantes</a>
                    <a href="{{ route('login') }}"    class="block text-zinc-600 text-base hover:text-white transition-colors">Para domiciliarios</a>
                </div>
            </div>
        </div>

        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-zinc-800 text-sm">© {{ date('Y') }} Mi Plataforma. Hecho con ❤️ en Ubaté, Cundinamarca.</p>
            <div class="flex items-center gap-1.5">
                <div class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-zinc-800 text-sm">Sistema operativo</span>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
