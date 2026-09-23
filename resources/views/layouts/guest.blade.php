<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Mi Plataforma') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Splash del scooter (una sola vez por sesión de navegador — ver script abajo). */
        #mp-splash{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;background:#0D0D0F;overflow:hidden;transition:opacity .45s ease;}
        html.mp-no-splash #mp-splash{display:none!important;}
        #mp-splash .orb{position:absolute;border-radius:9999px;filter:blur(60px);animation:mpOrb 9s ease-in-out infinite;}
        #mp-splash .orb-a{top:-6rem;left:-6rem;width:18rem;height:18rem;background:rgba(242,92,46,.22);}
        #mp-splash .orb-b{bottom:-6rem;right:-4rem;width:20rem;height:20rem;background:rgba(249,115,22,.12);animation-duration:13s;animation-direction:reverse;}
        @keyframes mpOrb{0%,100%{transform:translate(0,0) scale(1)}33%{transform:translate(30px,-20px) scale(1.08)}66%{transform:translate(-20px,12px) scale(.93)}}
        #mp-splash .mp-linea{position:absolute;height:3px;border-radius:9999px;background:linear-gradient(90deg,transparent,rgba(242,92,46,.7),transparent);opacity:0;animation:mpLinea 1.1s ease-out forwards;}
        @keyframes mpLinea{0%{opacity:0;transform:translateX(30px) scaleX(.3)}25%{opacity:1}100%{opacity:0;transform:translateX(-70px) scaleX(1)}}
        #mp-splash .mp-scooter{font-size:4rem;line-height:1;display:inline-block;transform:translateX(-160px) scale(.8) rotate(-8deg);opacity:0;animation:mpScooterIn 1s cubic-bezier(.22,1.4,.36,1) forwards;filter:drop-shadow(0 10px 18px rgba(242,92,46,.35));}
        @keyframes mpScooterIn{0%{transform:translateX(-160px) scale(.8) rotate(-8deg);opacity:0}55%{transform:translateX(12px) scale(1.06) rotate(0);opacity:1}75%{transform:translateX(-6px) scale(1)}100%{transform:translateX(0) scale(1)}}
        #mp-splash .mp-fade{opacity:0;transform:translateY(10px);animation:mpFadeUp .6s ease-out forwards;}
        @keyframes mpFadeUp{to{opacity:1;transform:translateY(0)}}
    </style>
    <script>
        // Evita el "flash" del splash en páginas repetidas dentro de la misma
        // pestaña: si ya se mostró en esta sesión, lo ocultamos por CSS antes
        // de pintar (ver clase `mp-no-splash` arriba).
        try {
            if (sessionStorage.getItem('mp_splash_shown')) {
                document.documentElement.classList.add('mp-no-splash');
            }
        } catch (e) {}
    </script>
</head>
<body class="font-sans antialiased">

{{-- Splash tipo app (scooter acelerando), una sola vez por sesión del navegador —
     misma idea que `app_movil/lib/features/splash/splash_screen.dart`. --}}
<div id="mp-splash">
    <div class="orb orb-a"></div>
    <div class="orb orb-b"></div>
    <div class="relative flex flex-col items-center">
        <div class="relative h-20 flex items-center justify-center">
            <div class="mp-linea" style="width:70px;top:38%;animation-delay:.05s"></div>
            <div class="mp-linea" style="width:50px;top:55%;animation-delay:.15s"></div>
            <div class="mp-linea" style="width:38px;top:68%;animation-delay:.25s"></div>
            <span class="mp-scooter">🛵</span>
        </div>
        <div class="mp-fade mt-5" style="animation-delay:.95s">
            <x-application-logo class="w-14 h-14 mx-auto" />
        </div>
        <p class="mp-fade mt-4 text-white font-black text-xl" style="animation-delay:1.25s">Mi Plataforma</p>
        <p class="mp-fade mt-1 text-brand-500 text-xs tracking-[0.2em] font-bold" style="animation-delay:1.4s">UBATÉ · DELIVERY</p>
    </div>
</div>
<script>
    (function () {
        var el = document.getElementById('mp-splash');
        if (!el) return;
        var yaVisto = false;
        try { yaVisto = !!sessionStorage.getItem('mp_splash_shown'); } catch (e) {}
        if (yaVisto) { el.remove(); return; }
        try { sessionStorage.setItem('mp_splash_shown', '1'); } catch (e) {}
        setTimeout(function () {
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 450);
        }, 2100);
    })();
</script>

<div class="min-h-screen flex">

    {{-- ── Panel izquierdo — Imagen + Marca ──────────── --}}
    <div class="hidden lg:flex lg:w-5/12 relative flex-col overflow-hidden">
        {{-- Imagen --}}
        <img src="https://images.unsplash.com/photo-1544145945-f90425340c7e?auto=format&fit=crop&w=900&q=90"
             alt="" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-br from-gray-950/95 via-brand-950/80 to-brand-800/70"></div>

        {{-- Blur orbs --}}
        <div class="absolute bottom-1/3 right-0 w-80 h-80 bg-brand-500/20 rounded-full blur-3xl"></div>
        <div class="absolute top-1/4 left-0 w-60 h-60 bg-orange-500/10 rounded-full blur-2xl"></div>

        {{-- Contenido --}}
        <div class="relative z-10 flex flex-col justify-between h-full p-10">
            <a href="{{ route('welcome') }}" class="flex items-center gap-3 group">
                <x-application-logo class="w-10 h-10 transition-transform group-hover:scale-110 duration-300" />
                <div>
                    <p class="text-white font-black text-xl leading-none">Mi Plataforma</p>
                    <p class="text-white/40 text-[10px] tracking-widest mt-0.5">UBATÉ · DELIVERY</p>
                </div>
            </a>

            <div>
                <h2 class="text-4xl font-black text-white leading-tight mb-3">
                    El sabor de<br>
                    <span class="text-gradient">Ubaté</span>,<br>
                    en tu puerta
                </h2>
                <p class="text-white/50 text-sm leading-relaxed mb-10 max-w-xs">
                    La plataforma de domicilios pensada para los negocios y clientes del municipio.
                </p>

                <div class="space-y-3">
                    @foreach([
                        ['🏪', 'Restaurantes locales', 'Sin comisiones abusivas'],
                        ['🔴', 'Seguimiento en tiempo real', 'Rastrea cada pedido al instante'],
                        ['🛵', 'Domiciliarios de Ubaté', 'Personas que conocen el municipio'],
                    ] as [$icon, $title, $sub])
                        <div class="flex items-center gap-3.5 glass rounded-2xl px-4 py-3.5 group hover:bg-white/20 transition-colors">
                            <span class="text-xl w-8 text-center shrink-0">{{ $icon }}</span>
                            <div>
                                <p class="text-white text-sm font-bold leading-none">{{ $title }}</p>
                                <p class="text-white/50 text-xs mt-1">{{ $sub }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <p class="text-white/25 text-xs">© {{ date('Y') }} Mi Plataforma · Ubaté</p>
        </div>
    </div>

    {{-- ── Panel derecho — Formulario ─────────────────── --}}
    <div class="flex-1 flex flex-col bg-gray-950 relative overflow-hidden">
        {{-- Orbs decorativos de fondo --}}
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-brand-500/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/2 left-0 w-64 h-64 bg-orange-500/5 rounded-full blur-2xl pointer-events-none"></div>

        <div class="lg:hidden relative z-10 flex items-center justify-between px-6 pt-6 pb-2 border-b border-white/5">
            <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
                <x-application-logo class="w-9 h-9" />
                <div>
                    <p class="text-white font-black text-base leading-none">Mi Plataforma</p>
                    <p class="text-brand-500 text-[10px] tracking-wider font-semibold mt-0.5">UBATÉ · DELIVERY</p>
                </div>
            </a>
            <a href="{{ route('welcome') }}" class="text-xs text-white/30 hover:text-white/60 transition-colors">← Inicio</a>
        </div>

        <div class="flex-1 flex items-center justify-center px-6 py-10 relative z-10">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </div>
    </div>

</div>
</body>
</html>
