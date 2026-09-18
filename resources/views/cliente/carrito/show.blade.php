<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ url()->previous() }}"
               class="flex items-center gap-1.5 text-zinc-400 hover:text-zinc-200 transition-colors text-sm font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
                Volver
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-zinc-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
            <span class="font-bold text-white">Mi carrito</span>
            @if(count($items) > 0)
                <span class="badge-orange">{{ collect($items)->sum('cantidad') }} productos</span>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-900/40 border border-emerald-800/50 text-emerald-400 text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 px-4 py-3 rounded-xl bg-red-950/40 border border-red-800/50 text-red-400 text-sm space-y-1">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if(empty($items))
                {{-- Estado vacío --}}
                <div class="card p-16 text-center max-w-md mx-auto">
                    <div class="w-20 h-20 bg-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-zinc-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-white mb-2">Tu carrito está vacío</h3>
                    <p class="text-zinc-400 text-sm mb-6">Agrega productos desde el menú de un restaurante.</p>
                    <a href="{{ route('cliente.restaurantes.index') }}" class="btn-primary">
                        Ver restaurantes
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- ── Columna items (2/3) ────────────────── --}}
                    <div class="lg:col-span-2 space-y-3">
                        @if($restaurante)
                            <p class="text-zinc-500 text-xs font-semibold uppercase tracking-widest mb-4">
                                Pedido en {{ $restaurante->nombre }}
                            </p>
                        @endif

                        @foreach($items as $item)
                            <div class="card p-4 flex items-center gap-4">
                                {{-- Imagen --}}
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-zinc-800 shrink-0 flex items-center justify-center">
                                    @if($item['imagen'])
                                        <img src="{{ \Illuminate\Support\Str::startsWith($item['imagen'], 'http') ? $item['imagen'] : asset('storage/' . $item['imagen']) }}"
                                             alt="{{ $item['nombre'] }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <span class="text-2xl">🍽️</span>
                                    @endif
                                </div>

                                {{-- Nombre + precio --}}
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-white text-sm line-clamp-1">{{ $item['nombre'] }}</p>
                                    <p class="text-zinc-500 text-xs mt-0.5">
                                        ${{ number_format($item['precio'], 0, ',', '.') }} c/u
                                    </p>
                                </div>

                                {{-- Stepper --}}
                                <div class="flex items-center gap-1 shrink-0">
                                    <form action="{{ route('cliente.carrito.actualizar', $item['producto_id']) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="cantidad" value="{{ $item['cantidad'] - 1 }}">
                                        <button type="submit"
                                                class="w-8 h-8 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-300 flex items-center justify-center transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 12h-15"/>
                                            </svg>
                                        </button>
                                    </form>

                                    <span class="w-8 text-center font-black text-white text-sm">{{ $item['cantidad'] }}</span>

                                    <form action="{{ route('cliente.carrito.actualizar', $item['producto_id']) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="cantidad" value="{{ $item['cantidad'] + 1 }}">
                                        <button type="submit"
                                                class="w-8 h-8 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-300 flex items-center justify-center transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>

                                {{-- Subtotal --}}
                                <p class="font-black text-white text-sm w-20 text-right shrink-0">
                                    ${{ number_format($item['precio'] * $item['cantidad'], 0, ',', '.') }}
                                </p>

                                {{-- Eliminar --}}
                                <form action="{{ route('cliente.carrito.eliminar', $item['producto_id']) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="w-8 h-8 rounded-lg bg-red-950/40 hover:bg-red-900/60 text-red-500 flex items-center justify-center transition-colors shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>

                    {{-- ── Columna resumen (1/3) ──────────────── --}}
                    <div class="space-y-4">

                        {{-- Resumen --}}
                        <div class="card p-5 space-y-3">
                            <h3 class="font-black text-white text-base">Resumen</h3>
                            <div class="space-y-2 pt-1">
                                @foreach($items as $item)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-zinc-400 line-clamp-1 flex-1 mr-2">
                                            ×{{ $item['cantidad'] }} {{ $item['nombre'] }}
                                        </span>
                                        <span class="text-zinc-300 font-semibold shrink-0">
                                            ${{ number_format($item['precio'] * $item['cantidad'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="border-t border-white/[0.08] pt-3 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-zinc-400">Subtotal productos</span>
                                    <span class="text-zinc-300 font-semibold">
                                        ${{ number_format($subtotal, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-zinc-400">Costo de domicilio</span>
                                    @if($costo_domicilio > 0)
                                        <span class="text-zinc-300 font-semibold">
                                            ${{ number_format($costo_domicilio, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-emerald-400 font-bold">Gratis</span>
                                    @endif
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-white/[0.06]">
                                    <span class="font-bold text-zinc-200">Total</span>
                                    <span class="font-black text-xl text-white">
                                        ${{ number_format($total, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Formulario checkout --}}
                        <div class="card p-5">
                            <h3 class="font-black text-white text-base mb-4">¿A dónde te llevamos?</h3>
                            <form action="{{ route('cliente.carrito.checkout') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5 uppercase tracking-wide">
                                        Dirección de entrega
                                    </label>
                                    <input type="text"
                                           name="direccion_entrega"
                                           id="direccion-input"
                                           value="{{ old('direccion_entrega', $direccion) }}"
                                           placeholder="Calle 5 #10-20, Ubaté"
                                           required
                                           class="input @error('direccion_entrega') ring-2 ring-red-500 @enderror">
                                    @error('direccion_entrega')
                                        <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Mapa para fijar el punto EXACTO de entrega (guarda coordenadas,
                                     necesarias para el seguimiento en vivo del domiciliario). --}}
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <label class="block text-xs font-semibold text-zinc-400 uppercase tracking-wide">
                                            Punto en el mapa
                                        </label>
                                        <button type="button" id="btn-mi-ubicacion"
                                                style="color:#F25C2E"
                                                class="text-xs font-bold hover:underline">
                                            Usar mi ubicacion
                                        </button>
                                    </div>
                                    <div id="checkout-map" class="bg-zinc-800"
                                         style="height:190px;border-radius:12px;overflow:hidden"></div>
                                    <p id="checkout-map-hint" class="text-zinc-500 text-xs mt-1">
                                        Toca el mapa para marcar donde entregar.
                                    </p>
                                    <input type="hidden" name="lat_entrega" id="lat_entrega" value="{{ old('lat_entrega') }}">
                                    <input type="hidden" name="lng_entrega" id="lng_entrega" value="{{ old('lng_entrega') }}">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-zinc-400 mb-1.5 uppercase tracking-wide">
                                        Notas (opcional)
                                    </label>
                                    <textarea name="notas"
                                              rows="2"
                                              placeholder="Sin cebolla, extra salsa..."
                                              class="input resize-none">{{ old('notas') }}</textarea>
                                </div>
                                <button type="submit" class="btn-primary w-full justify-center py-3 flex-col gap-0.5">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Confirmar pedido · ${{ number_format($total, 0, ',', '.') }}
                                    </span>
                                    @if($costo_domicilio > 0)
                                        <span class="text-white/60 text-xs font-normal">Incluye ${{ number_format($costo_domicilio, 0, ',', '.') }} de domicilio</span>
                                    @else
                                        <span class="text-emerald-300 text-xs font-normal">Domicilio gratis</span>
                                    @endif
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            @endif

        </div>
    </div>

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    const el = document.getElementById('checkout-map');
    if (!el || typeof L === 'undefined') return;

    const UBATE = [5.3098, -73.8146];
    const map = L.map('checkout-map', { zoomControl: true, attributionControl: false }).setView(UBATE, 16);

    // Dos capas base: "Calles" (detallada y con nombres) y "Satélite" (fotos
    // reales: se ven los techos de las casas para marcar la tuya exacta).
    const calles = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 20, subdomains: 'abcd' });
    const satImg = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { maxZoom: 20 });
    const satVias = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}', { maxZoom: 20 });
    const satelite = L.layerGroup([satImg, satVias]);

    calles.addTo(map); // por defecto
    L.control.layers({ 'Calles': calles, 'Satélite': satelite }, null, { position: 'topright', collapsed: false }).addTo(map);
    setTimeout(() => map.invalidateSize(), 80);

    const icon = L.divIcon({
        html: `<div style="width:26px;height:26px;background:#F25C2E;border:3px solid #fff;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 3px 10px rgba(242,92,46,.7)"></div>`,
        iconSize: [26, 26], iconAnchor: [13, 26], className: ''
    });

    const latI = document.getElementById('lat_entrega');
    const lngI = document.getElementById('lng_entrega');
    const dirI = document.getElementById('direccion-input');
    const hint = document.getElementById('checkout-map-hint');
    let marker = null;

    // force=true (botón "Mi ubicación"): sobrescribe SIEMPRE la dirección.
    // force=false (toque/arrastre): solo la rellena si está vacía, para no pisar
    // lo que el usuario haya escrito a mano.
    // Arma una dirección lo más específica que da OSM: "Calle/Carrera + número,
    // barrio, municipio". En Ubaté no siempre hay número de casa, pero el PIN
    // (coordenadas) es exacto igual. force=true sobrescribe siempre (botón GPS).
    function reverse(ll, force) {
        if (force) dirI.value = `Ubicación marcada (${ll[0].toFixed(5)}, ${ll[1].toFixed(5)})`;
        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${ll[0]}&lon=${ll[1]}&format=json&zoom=18&addressdetails=1`, { headers: { 'Accept-Language': 'es' } })
            .then(r => r.json())
            .then(d => {
                if (!d) return;
                const a = d.address || {};
                const calle = [a.road, a.house_number].filter(Boolean).join(' ');
                const partes = [
                    calle,
                    a.neighbourhood || a.suburb || a.quarter || a.residential,
                    a.town || a.city || a.village || a.municipality,
                ].filter(Boolean);
                const texto = partes.length ? partes.join(', ') : (d.display_name || '');
                if (texto && (force || !dirI.value.trim())) dirI.value = texto;
            })
            .catch(() => {});
    }

    function fijado(ll) {
        hint.textContent = `Punto exacto fijado (${ll[0].toFixed(5)}, ${ll[1].toFixed(5)}). El domi llega aquí; podés arrastrar el pin o afinar el texto.`;
    }

    function setPoint(ll, geocode, force) {
        if (!marker) {
            marker = L.marker(ll, { icon, draggable: true }).addTo(map);
            marker.on('dragend', e => { const p = e.target.getLatLng(); setPoint([p.lat, p.lng], true, false); });
        } else {
            marker.setLatLng(ll);
        }
        map.setView(ll, 17);
        latI.value = ll[0].toFixed(6);
        lngI.value = ll[1].toFixed(6);
        fijado(ll);
        if (geocode) reverse(ll, force);
    }

    if (latI.value && lngI.value) setPoint([parseFloat(latI.value), parseFloat(lngI.value)], false, false);

    map.on('click', e => setPoint([e.latlng.lat, e.latlng.lng], true, false));

    document.getElementById('btn-mi-ubicacion').addEventListener('click', () => {
        if (!navigator.geolocation) { hint.textContent = 'Tu navegador no da ubicacion.'; return; }
        hint.textContent = 'Obteniendo tu ubicacion...';
        navigator.geolocation.getCurrentPosition(
            p => setPoint([p.coords.latitude, p.coords.longitude], true, true),
            (err) => { hint.textContent = 'No se pudo obtener tu ubicacion (permiso denegado o sin senal). Toca el mapa para marcar.'; },
            { enableHighAccuracy: true, timeout: 9000, maximumAge: 0 }
        );
    });
})();
</script>
@endpush

</x-app-layout>
