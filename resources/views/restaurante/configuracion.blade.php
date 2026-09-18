<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-black text-white text-xl">Configuración del local</h2>
            <p class="text-sm text-zinc-500 mt-0.5">Información pública, imagen y horarios</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="flex items-center gap-3 px-4 py-3 bg-emerald-900/40 border border-emerald-800/50 text-emerald-400 text-sm font-semibold rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('restaurante.configuracion.update') }}" method="POST"
                  enctype="multipart/form-data" class="space-y-6"
                  x-data="{ preview: null }">
                @csrf @method('PUT')

                {{-- ── DATOS DEL LOCAL ────────────────────────────── --}}
                <div class="form-section">
                    <h3 class="form-section-title">Datos del local</h3>

                    {{-- Imagen del local --}}
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Foto del local</label>
                        <div class="flex items-center gap-4">
                            <div class="w-24 h-24 rounded-xl overflow-hidden bg-gradient-to-br from-zinc-800 to-zinc-700 border border-white/10 flex items-center justify-center shrink-0">
                                <template x-if="preview">
                                    <img :src="preview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!preview">
                                    @if($restaurante->imagen)
                                        <img src="{{ $restaurante->fotoUrl() }}"
                                             alt="{{ $restaurante->nombre }}" class="w-full h-full object-cover">
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-zinc-500">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                                        </svg>
                                    @endif
                                </template>
                            </div>
                            <div>
                                <label class="cursor-pointer btn-secondary text-sm px-4 py-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                    </svg>
                                    {{ $restaurante->imagen ? 'Cambiar foto' : 'Subir foto' }}
                                    <input type="file" name="imagen" accept="image/*" class="hidden"
                                           @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                                </label>
                                <p class="text-xs text-zinc-500 mt-1.5">JPG, PNG o WebP · máx. 3 MB</p>
                            </div>
                        </div>
                        @error('imagen') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Nombre --}}
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-1">Nombre del negocio <span class="text-brand-500">*</span></label>
                        <input type="text" name="nombre" value="{{ old('nombre', $restaurante->nombre) }}" required
                               class="input @error('nombre') !border-red-400 @enderror">
                        @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-1">Descripción</label>
                        <textarea name="descripcion" rows="3"
                                  placeholder="Cuéntales a los clientes sobre tu negocio..."
                                  class="input resize-none">{{ old('descripcion', $restaurante->descripcion) }}</textarea>
                        @error('descripcion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Costo de domicilio --}}
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-1">Costo de domicilio</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-500 font-bold text-sm pointer-events-none">$</span>
                            <input type="number" name="costo_domicilio" min="0" max="99999" step="100"
                                   value="{{ old('costo_domicilio', $restaurante->costo_domicilio ?? 0) }}"
                                   placeholder="0"
                                   class="input pl-7 @error('costo_domicilio') !border-red-400 @enderror">
                        </div>
                        <p class="text-zinc-600 text-xs mt-1">Escribe 0 si el domicilio es gratis para el cliente.</p>
                        @error('costo_domicilio') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Teléfono y dirección --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-1">Teléfono</label>
                            <input type="tel" name="telefono" value="{{ old('telefono', $restaurante->telefono) }}"
                                   placeholder="310 000 0000"
                                   class="input">
                            @error('telefono') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-zinc-300 mb-1">Dirección</label>
                            <input type="text" name="direccion" value="{{ old('direccion', $restaurante->direccion) }}"
                                   placeholder="Calle 5 #10-20, Ubaté"
                                   class="input">
                            @error('direccion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Ubicación en el mapa --}}
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-1">Ubicación del local en el mapa</label>
                        <p class="text-zinc-600 text-xs mb-2">Toca el mapa o arrastra el marcador para fijar el punto exacto desde donde salen los domicilios. Por defecto, el centro de Ubaté.</p>
                        <div class="rounded-xl overflow-hidden border border-white/10" style="height:280px">
                            <div id="rest-map" style="height:100%;width:100%;background:#18181b"></div>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <p class="text-xs text-zinc-500">Coordenadas: <span id="rest-coords" class="font-mono text-zinc-300">—</span></p>
                            <button type="button" id="rest-center" class="text-xs font-semibold text-brand-400 hover:text-brand-300 transition-colors">Centrar en Ubaté</button>
                        </div>
                        <input type="hidden" name="lat" id="rest-lat" value="{{ old('lat', $restaurante->lat) }}">
                        <input type="hidden" name="lng" id="rest-lng" value="{{ old('lng', $restaurante->lng) }}">
                        @error('lat') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        @error('lng') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Estado del local --}}
                    <div class="flex items-center justify-between bg-zinc-800/50 rounded-xl px-4 py-3 border border-white/8">
                        <div>
                            <p class="text-sm font-bold text-zinc-300">Local activo</p>
                            <p class="text-xs text-zinc-500 mt-0.5">Los clientes pueden ver y pedir en tu negocio</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" name="activo" value="1" class="sr-only peer"
                                   {{ old('activo', $restaurante->activo) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-zinc-700 peer-focus:outline-none rounded-full peer
                                        peer-checked:after:translate-x-full peer-checked:after:border-white
                                        after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                        after:bg-white after:border-gray-300 after:border after:rounded-full
                                        after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-500
                                        transition-colors duration-200"></div>
                        </label>
                    </div>
                </div>

                {{-- ── HORARIOS ──────────────────────────────────────── --}}
                <div class="form-section">
                    <h3 class="form-section-title">Horarios de atención</h3>

                    <div class="space-y-3">
                        @php
                            $nombresdia = [
                                'lunes'     => 'Lunes',
                                'martes'    => 'Martes',
                                'miercoles' => 'Miércoles',
                                'jueves'    => 'Jueves',
                                'viernes'   => 'Viernes',
                                'sabado'    => 'Sábado',
                                'domingo'   => 'Domingo',
                            ];
                            $horariosGuardados = $restaurante->horarios ?? [];
                        @endphp

                        @foreach($dias as $dia)
                            @php
                                $h = $horariosGuardados[$dia] ?? ['abierto' => false, 'apertura' => '08:00', 'cierre' => '20:00'];
                                $abierto  = old("horarios.$dia.abierto",  $h['abierto']  ?? false);
                                $apertura = old("horarios.$dia.apertura", $h['apertura'] ?? '08:00');
                                $cierre   = old("horarios.$dia.cierre",   $h['cierre']   ?? '20:00');
                            @endphp
                            <div class="flex items-center gap-4 py-2 border-b border-white/5 last:border-0" x-data="{ abierto: {{ $abierto ? 'true' : 'false' }} }">
                                {{-- Toggle día --}}
                                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                                    <input type="hidden" name="horarios[{{ $dia }}][abierto]" value="0">
                                    <input type="checkbox" name="horarios[{{ $dia }}][abierto]" value="1"
                                           class="sr-only peer" x-model="abierto"
                                           {{ $abierto ? 'checked' : '' }}>
                                    <div class="w-9 h-5 bg-zinc-700 rounded-full peer
                                                peer-checked:after:translate-x-full peer-checked:after:border-white
                                                after:content-[''] after:absolute after:top-[2px] after:left-[2px]
                                                after:bg-white after:border-gray-300 after:border after:rounded-full
                                                after:h-4 after:w-4 after:transition-all peer-checked:bg-brand-500
                                                transition-colors duration-200"></div>
                                </label>

                                {{-- Nombre del día --}}
                                <span class="text-sm font-semibold w-20 shrink-0"
                                      :class="abierto ? 'text-zinc-100' : 'text-zinc-600'">{{ $nombresdia[$dia] }}</span>

                                {{-- Horas --}}
                                <div class="flex items-center gap-2 flex-1" :class="abierto ? '' : 'opacity-30 pointer-events-none'">
                                    <input type="time" name="horarios[{{ $dia }}][apertura]"
                                           value="{{ $apertura }}"
                                           class="flex-1 border border-white/10 rounded-lg px-3 py-1.5 text-sm text-zinc-100 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-zinc-800">
                                    <span class="text-zinc-500 text-sm font-medium">—</span>
                                    <input type="time" name="horarios[{{ $dia }}][cierre]"
                                           value="{{ $cierre }}"
                                           class="flex-1 border border-white/10 rounded-lg px-3 py-1.5 text-sm text-zinc-100 focus:outline-none focus:ring-2 focus:ring-brand-400 bg-zinc-800">
                                </div>

                                {{-- Cerrado label --}}
                                <span class="text-xs font-semibold text-zinc-500 shrink-0 w-14 text-right" x-show="!abierto">Cerrado</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Botón guardar --}}
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary flex-1 py-3 text-base">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                        Guardar configuración
                    </button>
                    <a href="{{ route('restaurante.dashboard') }}" class="btn-secondary px-6 py-3">
                        Cancelar
                    </a>
                </div>
            </form>

        </div>
    </div>

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    const mapEl = document.getElementById('rest-map');
    if (!mapEl || typeof L === 'undefined') return;

    const UBATE   = [5.3085900, -73.8143000]; // centro de Ubaté
    const latI    = document.getElementById('rest-lat');
    const lngI    = document.getElementById('rest-lng');
    const coordsT = document.getElementById('rest-coords');

    const tieneCoords = latI.value && lngI.value;
    const inicio = tieneCoords ? [parseFloat(latI.value), parseFloat(lngI.value)] : UBATE;

    const map = L.map('rest-map', { zoomControl: true, scrollWheelZoom: false });
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19, subdomains: 'abcd'
    }).addTo(map);
    map.setView(inicio, 16);
    // El mapa vive dentro de la card del formulario; recalcular tamaño.
    setTimeout(function () { map.invalidateSize(); }, 60);

    const icon = L.divIcon({
        html: `<div style="width:30px;height:30px;background:#F25C2E;border:3px solid white;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 14px rgba(242,92,46,.7)"><div style="width:8px;height:8px;background:white;border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%)"></div></div>`,
        iconSize: [30, 30], iconAnchor: [15, 30], className: ''
    });
    const marker = L.marker(inicio, { icon, draggable: true }).addTo(map);

    function fijar(latlng) {
        const lat = latlng.lat.toFixed(7), lng = latlng.lng.toFixed(7);
        latI.value = lat; lngI.value = lng;
        coordsT.textContent = lat + ', ' + lng;
    }

    // Si aún no hay coordenadas, dejamos por defecto el centro de Ubaté ya fijado.
    if (tieneCoords) {
        coordsT.textContent = parseFloat(latI.value).toFixed(7) + ', ' + parseFloat(lngI.value).toFixed(7);
    } else {
        fijar(L.latLng(UBATE[0], UBATE[1]));
    }

    map.on('click', function (e) { marker.setLatLng(e.latlng); fijar(e.latlng); });
    marker.on('dragend', function () { const ll = marker.getLatLng(); map.panTo(ll); fijar(ll); });

    document.getElementById('rest-center').addEventListener('click', function () {
        const ll = L.latLng(UBATE[0], UBATE[1]);
        marker.setLatLng(ll); map.setView(ll, 16); fijar(ll);
    });
})();
</script>
@endpush

</x-app-layout>
