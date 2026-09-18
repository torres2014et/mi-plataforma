@php use App\Models\Pedido; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h2 class="font-black text-white text-xl">Pedidos</h2>
                @if($pedidosActivos->count() > 0)
                    <span class="badge-orange">{{ $pedidosActivos->count() }} activos</span>
                @endif
            </div>
        </div>
    </x-slot>

    {{-- Banner tiempo real --}}
    <div x-data="pedidosRT({{ $restaurante->id }})"
         x-show="visible"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-cloak
         class="fixed top-16 inset-x-0 z-50 flex justify-center px-4 pt-3 pointer-events-none">
        <div class="pointer-events-auto flex items-center gap-3 bg-zinc-900 border border-brand-500/50 shadow-brand rounded-2xl px-5 py-3.5 max-w-sm w-full">
            <span class="relative flex h-2.5 w-2.5 shrink-0">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-brand-500"></span>
            </span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-white leading-tight" x-text="mensaje"></p>
                <p class="text-xs text-zinc-400 mt-0.5">Recargando en <span x-text="cuenta"></span>s…</p>
            </div>
            <button @click="recargar()" class="btn-primary text-xs py-1.5 px-3 shrink-0">Ver ahora</button>
        </div>
    </div>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            @if(session('success'))
                <div class="px-4 py-3 rounded-xl bg-emerald-900/40 border border-emerald-800/50 text-emerald-400 text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            {{-- ── Pedidos activos ────────────────────────────── --}}
            <div>
                <p class="text-xs font-black text-zinc-500 uppercase tracking-widest mb-4">
                    Activos ({{ $pedidosActivos->count() }})
                </p>

                @forelse($pedidosActivos as $pedido)
                    <div class="card p-5 mb-3" x-data="{ abierto: true }">

                        {{-- Fila superior --}}
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2.5 flex-wrap mb-1">
                                    <span class="font-black text-white">Pedido #{{ $pedido->id }}</span>
                                    <span class="{{ $pedido->estadoBadgeClass() }}">{{ $pedido->estadoLabel() }}</span>
                                </div>
                                <p class="text-zinc-400 text-sm font-semibold">{{ $pedido->cliente->name }}</p>
                                <p class="text-zinc-500 text-xs mt-0.5">{{ $pedido->created_at->diffForHumans() }}</p>
                                <p class="text-zinc-500 text-xs mt-0.5 flex items-center gap-1">
                                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                    </svg>
                                    {{ $pedido->direccion_entrega }}
                                </p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="font-black text-2xl text-white">
                                    ${{ number_format($pedido->total, 0, ',', '.') }}
                                </p>
                                <p class="text-zinc-500 text-xs">{{ $pedido->items->count() }} productos</p>
                            </div>
                        </div>

                        {{-- Items expandibles --}}
                        <div class="mt-4">
                            <button @click="abierto = !abierto"
                                    class="text-xs text-zinc-500 hover:text-zinc-300 transition-colors font-semibold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 transition-transform" :class="abierto ? 'rotate-90' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                                </svg>
                                <span x-text="abierto ? 'Ocultar productos' : 'Ver productos'"></span>
                            </button>

                            <div x-show="abierto" class="mt-3 space-y-1.5 pl-4 border-l-2 border-zinc-700/60">
                                @foreach($pedido->items as $item)
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-zinc-400">×{{ $item->cantidad }} {{ $item->nombre_producto }}</span>
                                        <span class="font-semibold text-zinc-300">
                                            ${{ number_format($item->subtotal, 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach
                                @if($pedido->notas)
                                    <p class="text-zinc-600 text-xs italic pt-1">Nota: {{ $pedido->notas }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Mapa en vivo del domiciliario (cuando el pedido va en camino) --}}
                        @if($pedido->estado === 'en_camino' && $pedido->lat_entrega && $pedido->lng_entrega && $restaurante->lat && $restaurante->lng)
                            <div class="mt-4">
                                <p class="text-xs font-black text-zinc-500 uppercase tracking-widest mb-2">Domiciliario en vivo</p>
                                <div id="rest-map-{{ $pedido->id }}" style="height:200px;border-radius:12px;overflow:hidden" class="bg-zinc-800"></div>
                                <p id="rest-map-hint-{{ $pedido->id }}" class="text-zinc-500 text-xs mt-1">Esperando senal GPS del domiciliario...</p>
                                @php
                                    $mapaDatos = [
                                        'id' => $pedido->id,
                                        'rest' => [(float) $restaurante->lat, (float) $restaurante->lng],
                                        'dest' => [(float) $pedido->lat_entrega, (float) $pedido->lng_entrega],
                                        'medio' => $pedido->medio_transporte ?? 'moto',
                                    ];
                                @endphp
                                <script type="application/json" id="rest-map-data-{{ $pedido->id }}">{!! json_encode($mapaDatos) !!}</script>
                            </div>
                        @endif

                        {{-- Botones de acción --}}
                        @php $transiciones = Pedido::transicionesValidas()[$pedido->estado] ?? []; @endphp
                        @if(count($transiciones) > 0)
                            <div class="mt-4 border-t border-white/[0.06] pt-4 space-y-3">

                                {{-- Asignación cuando el pedido está en preparación --}}
                                @if($pedido->estado === 'en_preparacion')
                                    <form action="{{ route('restaurante.pedidos.asignar', $pedido) }}" method="POST"
                                          class="flex items-center gap-2 flex-wrap">
                                        @csrf @method('PATCH')
                                        @if($pedido->domiciliario_id)
                                            {{-- Un domiciliario ya aceptó el pedido desde la app: solo despachar --}}
                                            <input type="hidden" name="domiciliario_id" value="{{ $pedido->domiciliario_id }}">
                                            <p class="text-xs text-emerald-400 bg-emerald-900/30 border border-emerald-800/40 px-3 py-2 rounded-lg flex-1">
                                                Aceptado por {{ $pedido->domiciliario->name ?? 'domiciliario' }}
                                            </p>
                                            <button type="submit" class="btn-primary text-sm">
                                                Enviar a domicilio
                                            </button>
                                        @elseif($domiciliariosDisponibles->isNotEmpty())
                                            <select name="domiciliario_id" class="input text-sm py-2 flex-1 min-w-40">
                                                <option value="">Seleccionar domiciliario…</option>
                                                @foreach($domiciliariosDisponibles as $dom)
                                                    <option value="{{ $dom->id }}">{{ $dom->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn-primary text-sm">
                                                Enviar a domicilio
                                            </button>
                                        @else
                                            <p class="text-xs text-amber-400 bg-amber-900/30 border border-amber-800/40 px-3 py-2 rounded-lg flex-1">
                                                Aún ningún domiciliario ha aceptado este pedido.
                                            </p>
                                        @endif
                                    </form>
                                @endif

                                <div class="flex gap-2 flex-wrap">
                                    @foreach($transiciones as $nuevoEstado)
                                        @if($nuevoEstado === 'en_camino') @continue @endif
                                        <form action="{{ route('restaurante.pedidos.updateEstado', $pedido) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="estado" value="{{ $nuevoEstado }}">
                                            <button type="submit"
                                                    @class([
                                                        'text-sm' => true,
                                                        'btn-danger'   => $nuevoEstado === 'cancelado',
                                                        'btn-primary'  => $nuevoEstado !== 'cancelado',
                                                    ])>
                                                {{ Pedido::labelAccion($nuevoEstado) }}
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>
                @empty
                    <div class="card p-12 text-center">
                        <div class="w-16 h-16 bg-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-zinc-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z"/>
                            </svg>
                        </div>
                        <p class="font-semibold text-zinc-300">Sin pedidos activos</p>
                        <p class="text-zinc-500 text-sm mt-1">Los pedidos nuevos aparecerán aquí.</p>
                    </div>
                @endforelse
            </div>

            {{-- ── Historial ──────────────────────────────────── --}}
            @if($pedidosHistorial->total() > 0)
                <div>
                    <p class="text-xs font-black text-zinc-500 uppercase tracking-widest mb-4">
                        Historial
                    </p>

                    <div class="card overflow-hidden">
                        @foreach($pedidosHistorial as $pedido)
                            <div class="flex items-center gap-4 px-5 py-4 border-b border-white/[0.05] last:border-0">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-zinc-300 text-sm">#{{ $pedido->id }}</span>
                                        <span class="text-zinc-600 text-xs">·</span>
                                        <span class="text-zinc-400 text-sm truncate">{{ $pedido->cliente->name }}</span>
                                    </div>
                                    <p class="text-zinc-600 text-xs mt-0.5">{{ $pedido->created_at->format('d M Y, H:i') }}</p>
                                </div>
                                <span class="{{ $pedido->estadoBadgeClass() }}">{{ $pedido->estadoLabel() }}</span>
                                <span class="font-bold text-zinc-300 text-sm shrink-0">
                                    ${{ number_format($pedido->total, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $pedidosHistorial->links() }}
                    </div>
                </div>
            @endif

        </div>
    </div>

@push('scripts')
<script>
function pedidosRT(restauranteId) {
    return {
        visible: false,
        mensaje: '',
        cuenta: 5,
        _timer: null,
        _interval: null,

        init() {
            if (typeof window.Echo === 'undefined') return;

            window.Echo.private(`restaurante.${restauranteId}`)
                .listen('.pedido.nuevo', (e) => {
                    const total = new Intl.NumberFormat('es-CO').format(e.total);
                    this.mostrar(`📦 Nuevo pedido de ${e.cliente} · $${total}`);
                })
                .listen('.domiciliario.acepto', (e) => {
                    const EMOJI = { moto:'🛵', bici:'🚲', auto:'🚗', a_pie:'🚶' };
                    const icono = EMOJI[e.medio] || '🛵';
                    this.mostrar(`${icono} ${e.domiciliario} va en camino a recoger el pedido #${e.pedido_id}`);
                });
        },

        mostrar(msg) {
            this.mensaje = msg;
            this.cuenta  = 5;
            this.visible = true;

            clearInterval(this._interval);
            clearTimeout(this._timer);

            this._interval = setInterval(() => {
                this.cuenta--;
                if (this.cuenta <= 0) {
                    clearInterval(this._interval);
                    this.recargar();
                }
            }, 1000);
        },

        recargar() {
            clearInterval(this._interval);
            clearTimeout(this._timer);
            window.location.reload();
        },
    };
}
</script>

{{-- Mapa en vivo del domiciliario para el restaurante (mismo evento 'ubicacion') --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof L === 'undefined') return;
    document.querySelectorAll('[id^="rest-map-data-"]').forEach(function (el) {
        let cfg; try { cfg = JSON.parse(el.textContent); } catch (e) { return; }
        const mapEl = document.getElementById('rest-map-' + cfg.id);
        const hint  = document.getElementById('rest-map-hint-' + cfg.id);
        if (!mapEl) return;

        const map = L.map(mapEl, { zoomControl: true, attributionControl: false }).setView(cfg.dest, 15);
        const calles = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 20, subdomains: 'abcd' });
        const satImg = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 20 });
        const satVias = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}', { maxZoom: 20 });
        calles.addTo(map);
        L.control.layers({ 'Calles': calles, 'Satelite': L.layerGroup([satImg, satVias]) }, null, { position: 'topright', collapsed: true }).addTo(map);
        setTimeout(function () { map.invalidateSize(); }, 120);

        const pin = (c) => L.divIcon({ html: '<div style="width:24px;height:24px;background:' + c + ';border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 3px 9px rgba(0,0,0,.4)"></div>', iconSize:[24,24], iconAnchor:[12,24], className:'' });
        const EMOJI = { moto:'🛵', bici:'🚲', auto:'🚗', a_pie:'🚶' };
        const iconDomi = L.divIcon({ html: '<div style="width:32px;height:32px;background:#3B82F6;border:3px solid #fff;border-radius:50%;box-shadow:0 4px 14px rgba(59,130,246,.7);display:flex;align-items:center;justify-content:center;font-size:16px">' + (EMOJI[cfg.medio] || '🛵') + '</div>', iconSize:[32,32], iconAnchor:[16,16], className:'' });

        L.marker(cfg.rest, { icon: pin('#10B981') }).addTo(map).bindPopup('🍴 Restaurante');
        L.marker(cfg.dest, { icon: pin('#F25C2E') }).addTo(map).bindPopup('🏠 Entrega');

        fetch('https://router.project-osrm.org/route/v1/driving/' + cfg.rest[1] + ',' + cfg.rest[0] + ';' + cfg.dest[1] + ',' + cfg.dest[0] + '?overview=full&geometries=geojson')
            .then(r => r.json())
            .then(d => {
                if (d.code === 'Ok' && d.routes[0]) {
                    const pts = d.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                    L.polyline(pts, { color:'#000', weight:8, opacity:.2 }).addTo(map);
                    L.polyline(pts, { color:'#F25C2E', weight:4, opacity:.9, dashArray:'10,8' }).addTo(map);
                }
                map.fitBounds(L.latLngBounds([cfg.rest, cfg.dest]), { padding:[40,40] });
            })
            .catch(() => map.fitBounds(L.latLngBounds([cfg.rest, cfg.dest]), { padding:[40,40] }));

        let domi = null;
        if (window.Echo) {
            window.Echo.private('pedido.' + cfg.id).listen('.ubicacion', function (e) {
                if (!e || e.lat == null) return;
                const ll = [e.lat, e.lng];
                if (!domi) domi = L.marker(ll, { icon: iconDomi, zIndexOffset: 1000 }).addTo(map).bindPopup('🛵 Domiciliario');
                else domi.setLatLng(ll);
                if (hint) hint.textContent = 'Domiciliario en vivo: ' + e.lat.toFixed(5) + ', ' + e.lng.toFixed(5);
            });
        }
    });
});
</script>
@endpush

</x-app-layout>
