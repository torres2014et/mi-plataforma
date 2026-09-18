@php
    $estadosOrden = ['pendiente', 'confirmado', 'en_preparacion', 'en_camino', 'entregado'];
    $estadoActual = $pedido->estado;
    $cancelado    = $estadoActual === 'cancelado';
    $posActual    = array_search($estadoActual, $estadosOrden);
    $labelsOrden  = [
        'pendiente'      => 'Recibido',
        'confirmado'     => 'Confirmado',
        'en_preparacion' => 'En preparación',
        'en_camino'      => 'En camino',
        'entregado'      => 'Entregado',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('cliente.pedidos.index') }}"
               class="flex items-center gap-1.5 text-zinc-400 hover:text-zinc-200 transition-colors text-sm font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
                Mis pedidos
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-zinc-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
            <span class="font-bold text-white">Pedido #{{ $pedido->id }}</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">

            @if(session('success'))
                <div class="px-4 py-3 rounded-xl bg-emerald-900/40 border border-emerald-800/50 text-emerald-400 text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Toast tiempo real --}}
            <div id="rt-toast" class="hidden px-4 py-3 rounded-xl bg-brand-500/20 border border-brand-500/40 text-brand-300 text-sm font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Estado actualizado — actualizando…
            </div>

            {{-- ── Estado y timeline ──────────────────────────── --}}
            <div class="card p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-black text-white text-base">Estado del pedido</h3>
                    <span class="{{ $pedido->estadoBadgeClass() }}">{{ $pedido->estadoLabel() }}</span>
                </div>

                @if($cancelado)
                    <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-red-950/40 border border-red-800/40">
                        <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-red-400 text-sm font-semibold">Este pedido fue cancelado.</p>
                    </div>
                @else
                    {{-- Timeline --}}
                    <div class="flex items-center">
                        @foreach($estadosOrden as $i => $estado)
                            @php
                                $pasado  = $posActual !== false && $i <= $posActual;
                                $actual  = $posActual !== false && $i === $posActual;
                            @endphp

                            {{-- Círculo del paso --}}
                            <div class="flex flex-col items-center flex-shrink-0">
                                <div @class([
                                    'w-8 h-8 rounded-full flex items-center justify-center transition-all',
                                    'bg-brand-500 shadow-brand-sm'  => $actual,
                                    'bg-brand-800/60 border border-brand-700' => $pasado && !$actual,
                                    'bg-zinc-800 border border-zinc-700' => !$pasado,
                                ])>
                                    @if($actual)
                                        <div class="w-3 h-3 bg-white rounded-full"></div>
                                    @elseif($pasado)
                                        <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @else
                                        <div class="w-2.5 h-2.5 bg-zinc-600 rounded-full"></div>
                                    @endif
                                </div>
                                <span @class([
                                    'text-[10px] font-bold mt-1.5 text-center w-16',
                                    'text-brand-400' => $actual,
                                    'text-zinc-500'  => $pasado && !$actual,
                                    'text-zinc-600'  => !$pasado,
                                ])>{{ $labelsOrden[$estado] }}</span>
                            </div>

                            {{-- Línea conectora --}}
                            @if(!$loop->last)
                                <div @class([
                                    'flex-1 h-0.5 mx-1 mb-5',
                                    'bg-brand-700' => $posActual !== false && $i < $posActual,
                                    'bg-zinc-700'  => $posActual === false || $i >= $posActual,
                                ])></div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ── Código de entrega (QR) ────────────────────── --}}
            @if($pedido->estado === 'en_camino')
                <div class="card p-6 text-center">
                    <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-zinc-500 mb-1">Código de entrega</p>
                    <h3 class="font-black text-white text-base">Muéstralo al domiciliario</h3>
                    <p class="text-zinc-500 text-sm mt-1 mb-5">Lo escaneará para confirmar que recibiste tu pedido.</p>

                    {{-- QR (imagen: sin depender de librería JS; fondo blanco para que escanee bien) --}}
                    <div class="inline-block bg-white p-4 rounded-2xl shadow-brand-sm mb-5">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=0&qzone=1&ecc=M&data={{ urlencode($pedido->codigo_confirmacion) }}"
                             alt="Código QR de entrega"
                             width="180" height="180"
                             class="block w-[180px] h-[180px]"
                             loading="eager">
                    </div>

                    {{-- Código en texto grande (por si lo dicta a mano) --}}
                    <div class="font-black text-3xl tracking-[0.35em] text-white pl-[0.35em]">{{ $pedido->codigo_confirmacion }}</div>
                    <p class="text-zinc-600 text-xs mt-1 mb-5">Por si lo tiene que escribir a mano</p>

                    {{-- Respaldo: cerrar manualmente --}}
                    <form action="{{ route('cliente.pedidos.confirmar', $pedido) }}" method="POST"
                          onsubmit="return confirm('¿Confirmas que ya recibiste tu pedido?');">
                        @csrf
                        <button type="submit"
                                class="text-zinc-500 hover:text-zinc-300 text-sm font-semibold underline underline-offset-2 transition-colors">
                            Sí, ya lo recibí (cerrar manualmente)
                        </button>
                    </form>
                </div>
            @endif

            {{-- ── Items del pedido ──────────────────────────── --}}
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-white/[0.08]">
                    <p class="font-black text-white">{{ $pedido->restaurante->nombre }}</p>
                </div>
                @foreach($pedido->items as $item)
                    <div class="flex items-center gap-4 px-5 py-3.5 border-b border-white/[0.04] last:border-0">
                        <span class="text-zinc-500 text-sm font-semibold w-6 shrink-0">×{{ $item->cantidad }}</span>
                        <span class="flex-1 text-zinc-200 text-sm font-medium">{{ $item->nombre_producto }}</span>
                        <span class="font-bold text-white text-sm">
                            ${{ number_format($item->subtotal, 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach
                <div class="px-5 py-4 bg-zinc-800/50 space-y-2">
                    @php $subtotalPedido = $pedido->items->sum('subtotal'); @endphp
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-400">Subtotal</span>
                        <span class="text-zinc-300 font-semibold">${{ number_format($subtotalPedido, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-400">Domicilio</span>
                        @if($pedido->costo_domicilio > 0)
                            <span class="text-zinc-300 font-semibold">${{ number_format($pedido->costo_domicilio, 0, ',', '.') }}</span>
                        @else
                            <span class="text-emerald-400 font-bold">Gratis</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-white/[0.08]">
                        <span class="font-bold text-zinc-200">Total pagado</span>
                        <span class="font-black text-xl text-white">${{ number_format($pedido->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- ── Mapa de entrega ───────────────────────────── --}}
            <div class="card overflow-hidden">
                {{-- Banner de estado --}}
                <div class="px-5 py-3.5 border-b border-white/[0.08] flex items-center gap-2.5">
                    @if($pedido->estado === 'en_camino')
                        <span class="relative flex h-2.5 w-2.5 shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-brand-500"></span>
                        </span>
                        <span class="text-sm font-bold text-white">Tu domiciliario está en camino</span>
                    @elseif($pedido->estado === 'entregado')
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                        <span class="text-sm font-bold text-emerald-400">¡Pedido entregado!</span>
                    @elseif($pedido->estado === 'en_preparacion')
                        <svg class="w-4 h-4 text-brand-400 shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span class="text-sm font-semibold text-zinc-300">Preparando tu pedido…</span>
                    @else
                        <svg class="w-4 h-4 text-zinc-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        <span class="text-sm font-semibold text-zinc-400">Dirección de entrega</span>
                    @endif
                </div>

                {{-- Mapa --}}
                <div class="relative" style="height:250px">
                    <div id="client-map" style="height:100%;width:100%;background:#18181b"></div>
                    <div id="client-map-status" class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-zinc-900/90">
                        <svg class="w-5 h-5 text-brand-400 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span id="client-map-txt" class="text-zinc-400 text-xs font-semibold">Cargando mapa…</span>
                    </div>
                </div>

                {{-- Dirección texto --}}
                <div class="px-5 py-3 border-t border-white/[0.06] flex items-start gap-2">
                    <svg class="w-4 h-4 text-brand-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                    </svg>
                    <p class="text-zinc-300 text-sm">{{ $pedido->direccion_entrega }}</p>
                </div>
            </div>

            {{-- ── Calificación ──────────────────────────────── --}}
            @if($pedido->estado === 'entregado')
                @if($pedido->calificacion)
                    <div class="card p-5">
                        <div class="flex items-center gap-3 mb-1">
                            <div class="flex gap-0.5">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $pedido->calificacion->estrellas ? 'text-amber-400' : 'text-zinc-700' }}"
                                         viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-zinc-400 text-sm font-semibold">Tu calificación</span>
                        </div>
                        @if($pedido->calificacion->comentario)
                            <p class="text-zinc-400 text-sm italic mt-1">"{{ $pedido->calificacion->comentario }}"</p>
                        @endif
                    </div>
                @else
                    <div class="card p-5" x-data="{ estrellas: 0, hover: 0 }">
                        <h3 class="font-bold text-white text-sm mb-4">¿Cómo estuvo tu pedido?</h3>
                        <form action="{{ route('cliente.pedidos.calificar', $pedido) }}" method="POST" class="space-y-4">
                            @csrf
                            {{-- Selector de estrellas --}}
                            <div>
                                <div class="flex gap-1.5 mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button type="button"
                                                @mouseenter="hover = {{ $i }}"
                                                @mouseleave="hover = 0"
                                                @click="estrellas = {{ $i }}"
                                                class="transition-transform active:scale-90">
                                            <svg class="w-8 h-8 transition-colors duration-100"
                                                 :class="(hover || estrellas) >= {{ $i }} ? 'text-amber-400' : 'text-zinc-700'"
                                                 viewBox="0 0 24 24" fill="currentColor">
                                                <path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z"/>
                                            </svg>
                                        </button>
                                    @endfor
                                </div>
                                <p class="text-xs text-zinc-600 h-4"
                                   x-text="['','Muy malo','Malo','Regular','Bueno','Excelente'][hover || estrellas] ?? ''"></p>
                                <input type="hidden" name="estrellas" :value="estrellas">
                            </div>
                            {{-- Comentario --}}
                            <textarea name="comentario" rows="2"
                                      placeholder="Cuéntanos tu experiencia (opcional)…"
                                      class="input resize-none text-sm"></textarea>
                            {{-- Botón --}}
                            <button type="submit"
                                    :disabled="estrellas === 0"
                                    :class="estrellas > 0 ? 'btn-primary w-full justify-center' : 'w-full justify-center py-2.5 px-4 rounded-xl font-bold text-sm bg-zinc-800 text-zinc-600 cursor-not-allowed'">
                                Enviar calificación
                            </button>
                        </form>
                    </div>
                @endif
            @endif

            {{-- ── Detalles de entrega ───────────────────────── --}}
            <div class="card p-5 space-y-3">
                <h3 class="font-bold text-white text-sm">Detalles del pedido</h3>

                @if($pedido->notas)
                    <div class="flex items-start gap-3">
                        <svg class="w-4 h-4 text-zinc-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                        </svg>
                        <p class="text-zinc-400 text-sm italic">{{ $pedido->notas }}</p>
                    </div>
                @endif

                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-zinc-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-zinc-500 text-xs">{{ $pedido->created_at->format('d \d\e F \d\e Y, H:i') }}</p>
                </div>
            </div>

        </div>
    </div>

@push('scripts')
@if(!in_array($pedido->estado, ['entregado', 'cancelado']))
<script>
    // Notificaciones del navegador (Nivel 1): suenan/aparecen mientras la
    // pestaña esté abierta (o el navegador en segundo plano). No necesita FCM.
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
    window.avisarPedido = function (titulo, cuerpo) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        try { new Notification(titulo, { body: cuerpo, icon: '/favicon.ico', tag: 'pedido-{{ $pedido->id }}', renotify: true }); } catch (e) {}
    };

    if (window.Echo) {
        window.Echo.private('pedido.{{ $pedido->id }}')
            .listen('.estado.actualizado', (e) => {
                // Aviso "salió" justo cuando el pedido entra a en_camino.
                if (e && e.estado === 'en_camino') {
                    window.avisarPedido('Tu pedido salió 🛵', 'Tu pedido ya va en camino a tu dirección.');
                }
                document.getElementById('rt-toast').classList.remove('hidden');
                setTimeout(() => window.location.reload(), 1500);
            });
    }
</script>
@endif

{{-- Mapa de seguimiento para el cliente --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    const mapEl    = document.getElementById('client-map');
    const statusEl = document.getElementById('client-map-status');
    const statusTx = document.getElementById('client-map-txt');
    if (!mapEl) return;

    // Failsafe global: pase lo que pase abajo (Leaflet, geocode, tiles…), el
    // spinner "Cargando mapa…" nunca se queda pegado para siempre.
    const failsafe = setTimeout(function () { hideStatus(); }, 9000);

    // Si Leaflet no cargó (sin internet o CDN caído), avisamos en vez de girar eterno.
    if (typeof L === 'undefined') {
        setStatus('No se pudo cargar el mapa. Revisa tu conexión.');
        clearTimeout(failsafe);
        setTimeout(hideStatus, 2500);
        return;
    }

    const UBATE        = [5.3127, -73.8180];
    const ESTADO       = @json($pedido->estado);
    const ADDR_ENTREGA = @json($pedido->direccion_entrega ?? '');
    const ADDR_REST    = @json($pedido->restaurante->direccion ?? '');
    const NOMBRE_REST  = @json($pedido->restaurante->nombre ?? '');
    const MEDIO        = @json($pedido->medio_transporte ?? 'moto');
    const SALIDA       = new Date(@json(optional($pedido->updated_at)->toIso8601String())).getTime();
    // Coordenadas ya guardadas en el pedido (app/checkout) y del restaurante:
    // se usan directo y solo se geocodifica la dirección como respaldo.
    const COORD_ENTREGA = @json($pedido->lat_entrega !== null && $pedido->lng_entrega !== null ? [(float) $pedido->lat_entrega, (float) $pedido->lng_entrega] : null);
    const COORD_REST    = @json(optional($pedido->restaurante)->lat !== null && optional($pedido->restaurante)->lng !== null ? [(float) $pedido->restaurante->lat, (float) $pedido->restaurante->lng] : null);

    /* ── Mapa base ── */
    const map = L.map('client-map', { zoomControl: true, scrollWheelZoom: false, attributionControl: false });
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
    // El mapa vive dentro de una card; forzar a Leaflet a recalcular su tamaño,
    // si no los tiles pueden salir grises o a medias.
    setTimeout(function () { map.invalidateSize(); }, 60);

    /* ── Íconos ── */
    const iconDestino = L.divIcon({
        html: `<div style="width:30px;height:30px;background:#F25C2E;border:3px solid white;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 14px rgba(242,92,46,.7)"><div style="width:8px;height:8px;background:white;border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%)"></div></div>`,
        iconSize:[30,30], iconAnchor:[15,30], popupAnchor:[0,-32], className:''
    });
    const iconRestaurante = L.divIcon({
        html: `<div style="width:28px;height:28px;background:#10B981;border:3px solid white;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 12px rgba(16,185,129,.6)"><div style="width:7px;height:7px;background:white;border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%)"></div></div>`,
        iconSize:[28,28], iconAnchor:[14,28], popupAnchor:[0,-30], className:''
    });
    const EMOJI_MEDIO = { moto:'🛵', bici:'🚲', auto:'🚗', a_pie:'🚶' };
    const iconDomi = L.divIcon({
        html: `<div style="width:34px;height:34px;background:#3B82F6;border:3px solid white;border-radius:50%;box-shadow:0 4px 14px rgba(59,130,246,.7);display:flex;align-items:center;justify-content:center;font-size:17px">${EMOJI_MEDIO[MEDIO] || '🛵'}</div>`,
        iconSize:[34,34], iconAnchor:[17,17], className:''
    });

    /* ── Simulación del movimiento (por tiempo, igual que la app Flutter) ── */
    const VEL_KMH     = { moto:28, bici:14, auto:22, a_pie:5 };  // km/h
    const FACTOR_DEMO = 0.35;                                    // ~3× más rápido que la vida real
    const MIN_VIAJE = 18000, MAX_VIAJE = 150000;                 // ms

    function duracionViaje(metros) {
        const ms = (VEL_KMH[MEDIO] || 28) * 1000 / 3600;        // m/s
        const real = ms > 0 ? (metros / ms) * 1000 : 0;
        return Math.min(Math.max(real * FACTOR_DEMO, MIN_VIAJE), MAX_VIAJE);
    }

    function animarDomiciliario(pts, metros) {
        if (!pts || pts.length < 2) return null;
        const cum = [0];
        for (let i = 1; i < pts.length; i++) cum.push(cum[i-1] + map.distance(pts[i-1], pts[i]));
        const total = cum[cum.length - 1];
        const dur   = duracionViaje(metros);

        const marker = L.marker(pts[0], { icon: iconDomi, zIndexOffset: 1000 }).addTo(map)
            .bindPopup('<div style="font-family:sans-serif;font-size:12px;color:#111"><b style="color:#3B82F6">🛵 Tu domiciliario</b><br>En camino…</div>');

        function puntoEn(objetivo) {
            if (objetivo <= 0) return pts[0];
            if (objetivo >= total) return pts[pts.length - 1];
            for (let i = 1; i < cum.length; i++) {
                if (cum[i] >= objetivo) {
                    const seg = cum[i] - cum[i-1];
                    const f = seg === 0 ? 0 : (objetivo - cum[i-1]) / seg;
                    const a = pts[i-1], b = pts[i];
                    return [a[0] + (b[0]-a[0])*f, a[1] + (b[1]-a[1])*f];
                }
            }
            return pts[pts.length - 1];
        }

        // Fase 3 — híbrido: la simulación por tiempo mueve el marcador hasta que
        // llega el primer GPS real del repartidor; ahí se apaga y manda el real.
        let gpsReal = false;
        const timer = setInterval(() => {
            if (gpsReal) { clearInterval(timer); return; }
            const transcurrido = Date.now() - SALIDA;
            const f = Math.min(Math.max(transcurrido / dur, 0), 1);
            marker.setLatLng(puntoEn(total * f));
            if (f >= 1) clearInterval(timer);
        }, 200);

        return {
            // La primera posición real apaga la simulación y toma el control.
            setReal(lat, lng) {
                gpsReal = true;
                clearInterval(timer);
                marker.setLatLng([lat, lng]);
            }
        };
    }

    function setStatus(msg) { if (statusTx) statusTx.textContent = msg; }
    function hideStatus() {
        if (!statusEl) return;
        statusEl.style.transition = 'opacity .3s';
        statusEl.style.opacity = '0';
        setTimeout(() => statusEl && statusEl.remove(), 350);
    }

    /* ── Geocodificar dirección ── */
    function geocode(address) {
        const q = encodeURIComponent(address + ', Ubaté, Cundinamarca, Colombia');
        // Acotado al recuadro de Ubaté (bounded=1) para que no caiga en pueblos
        // vecinos como Simijaca cuando la dirección es ambigua.
        const VIEWBOX = '-73.835,5.328,-73.795,5.289';
        return fetch(`https://nominatim.openstreetmap.org/search?q=${q}&format=json&limit=1&countrycodes=co&viewbox=${VIEWBOX}&bounded=1`, {
            headers: { 'Accept-Language': 'es' }
        })
        .then(r => r.json())
        .then(data => data.length > 0 ? [parseFloat(data[0].lat), parseFloat(data[0].lon)] : null)
        .catch(() => null);
    }

    /* ── Trazar ruta restaurante → puerta del cliente ── */
    function drawRoute(from, to) {
        const url = `https://router.project-osrm.org/route/v1/driving/${from[1]},${from[0]};${to[1]},${to[0]}?overview=full&geometries=geojson`;
        return fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.code === 'Ok' && data.routes[0]) {
                    const pts  = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                    const dist = (data.routes[0].distance / 1000).toFixed(1);
                    const mins = Math.round(data.routes[0].duration / 60);
                    L.polyline(pts, { color:'#000', weight:9, opacity:.2,  lineCap:'round' }).addTo(map);
                    L.polyline(pts, { color:'#F25C2E', weight:5, opacity:.9, lineCap:'round',
                        dashArray: ESTADO === 'en_camino' ? '12,8' : null }).addTo(map);
                    map.fitBounds(L.latLngBounds([from, to]), { padding:[45,45] });
                    return { dist, mins, pts, distMeters: data.routes[0].distance };
                }
                return null;
            })
            .catch(() => null);
    }

    /* ── Flujo principal ── */
    setStatus('Buscando dirección…');

    (async () => {
      try {
        // Preferir las coordenadas guardadas; geocodificar la dirección solo como respaldo.
        let coordEntrega = COORD_ENTREGA || await geocode(ADDR_ENTREGA) || UBATE;
        let coordRest    = COORD_REST   || (ADDR_REST ? await geocode(ADDR_REST) : null);

        const mostrarRuta = ['en_camino', 'entregado'].includes(ESTADO) && coordRest;

        if (mostrarRuta) {
            setStatus('Trazando ruta…');
            const info = await drawRoute(coordRest, coordEntrega);

            /* Pin restaurante (origen) */
            L.marker(coordRest, { icon: iconRestaurante }).addTo(map)
                .bindPopup(`<div style="font-family:sans-serif;font-size:12px;color:#111"><b style="color:#10B981">🍴 ${NOMBRE_REST}</b><br>Origen del pedido</div>`);

            /* Pin destino (tu puerta) */
            const popup = info
                ? `<div style="font-family:sans-serif;font-size:12px;color:#111"><b style="color:#F25C2E">🏠 Tu dirección</b><br>${ADDR_ENTREGA}<br><div style="margin-top:5px;font-weight:700">🗺 ${info.dist} km · ⏱ ~${info.mins} min</div></div>`
                : `<div style="font-family:sans-serif;font-size:12px;color:#111"><b style="color:#F25C2E">🏠 Tu dirección</b><br>${ADDR_ENTREGA}</div>`;
            L.marker(coordEntrega, { icon: iconDestino }).addTo(map).bindPopup(popup).openPopup();

            /* Domiciliario moviéndose: simulación por tiempo de arranque y, en
               cuanto llega su GPS real por websocket, el marcador lo sigue. */
            if (ESTADO === 'en_camino' && info && info.pts) {
                const domi = animarDomiciliario(info.pts, info.distMeters);
                let avisadoLlegar = false;
                if (domi && window.Echo) {
                    window.Echo.private('pedido.{{ $pedido->id }}')
                        .listen('.ubicacion', (e) => {
                            if (e && e.lat != null && e.lng != null) {
                                domi.setReal(e.lat, e.lng);
                                // Aviso "por llegar" cuando el domi queda a ≤250 m.
                                if (!avisadoLlegar && window.avisarPedido) {
                                    const d = L.latLng(e.lat, e.lng).distanceTo(coordEntrega);
                                    if (d <= 250) {
                                        avisadoLlegar = true;
                                        window.avisarPedido('Tu pedido está por llegar 📍', 'El domiciliario está muy cerca de tu dirección.');
                                    }
                                }
                            }
                        });
                }
            }

        } else {
            /* Solo pin del destino */
            map.setView(coordEntrega, 16);
            L.marker(coordEntrega, { icon: iconDestino }).addTo(map)
                .bindPopup(`<div style="font-family:sans-serif;font-size:12px;color:#111"><b style="color:#F25C2E">🏠 Tu dirección</b><br>${ADDR_ENTREGA}</div>`)
                .openPopup();
        }

      } catch (e) {
        // Ante cualquier fallo, al menos centra el mapa para no dejarlo vacío.
        try { map.setView(UBATE, 14); } catch (_) {}
      } finally {
        clearTimeout(failsafe);
        hideStatus();
      }
    })();
})();
</script>
@endpush

</x-app-layout>
