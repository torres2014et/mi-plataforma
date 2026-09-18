<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-white text-xl">Mi Menú</h2>
                <p class="text-sm text-zinc-500 mt-0.5">{{ $productos->count() }} {{ $productos->count() === 1 ? 'producto' : 'productos' }} en tu catálogo</p>
            </div>
            <a href="{{ route('restaurante.productos.create') }}" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Agregar producto
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-emerald-900/40 border border-emerald-800/50 text-emerald-400 rounded-xl text-sm font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if($productos->isEmpty())
                <div class="card p-16 text-center">
                    <div class="w-20 h-20 bg-brand-500/10 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-brand-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
                        </svg>
                    </div>
                    <p class="font-bold text-zinc-100 text-lg">Tu menú está vacío</p>
                    <p class="text-zinc-500 text-sm mt-1.5 max-w-xs mx-auto">Agrega tu primer producto para que los clientes puedan encontrarte.</p>
                    <a href="{{ route('restaurante.productos.create') }}" class="btn-primary mt-6 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        Agregar primer producto
                    </a>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($categorias->push(null)->unique() as $cat)
                        @php $items = $productos->where('categoria', $cat); @endphp
                        @if($items->isNotEmpty())
                            <div>
                                <div class="flex items-center gap-3 mb-3">
                                    <span class="text-xs font-black text-zinc-500 uppercase tracking-widest">{{ $cat ?? 'Sin categoría' }}</span>
                                    <div class="flex-1 h-px bg-white/8"></div>
                                    <span class="text-xs text-zinc-600 font-medium">{{ $items->count() }}</span>
                                </div>
                                <div class="card overflow-hidden divide-y divide-white/[0.06]">
                                    @foreach($items as $producto)
                                        <div class="flex items-center gap-4 px-5 py-4 hover:bg-white/[0.03] transition-colors group">
                                            {{-- Thumbnail --}}
                                            <div class="w-14 h-14 rounded-xl overflow-hidden bg-zinc-800 shrink-0 flex items-center justify-center">
                                                @if($producto->imagen)
                                                    <img src="{{ $producto->fotoUrl() }}"
                                                         alt="{{ $producto->nombre }}" class="w-full h-full object-cover">
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-zinc-600">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                                    </svg>
                                                @endif
                                            </div>

                                            {{-- Info --}}
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <p class="font-bold text-zinc-100 truncate">{{ $producto->nombre }}</p>
                                                    @if(!$producto->disponible)
                                                        <span class="badge-gray">No disponible</span>
                                                    @endif
                                                </div>
                                                @if($producto->descripcion)
                                                    <p class="text-sm text-zinc-500 truncate mt-0.5">{{ $producto->descripcion }}</p>
                                                @endif
                                            </div>

                                            {{-- Precio + Acciones --}}
                                            <div class="flex items-center gap-2 shrink-0">
                                                <span class="text-base font-black text-zinc-100 mr-2">
                                                    ${{ number_format($producto->precio, 0, ',', '.') }}
                                                </span>

                                                <form action="{{ route('restaurante.productos.toggle', $producto) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <button type="submit"
                                                            class="text-xs px-3 py-1.5 rounded-lg font-semibold border transition-all duration-150 active:scale-95
                                                                   {{ $producto->disponible
                                                                       ? 'border-emerald-800/50 bg-emerald-900/40 text-emerald-400 hover:bg-emerald-900/60'
                                                                       : 'border-white/10 bg-zinc-800 text-zinc-500 hover:bg-zinc-700' }}">
                                                        <span class="flex items-center gap-1.5">
                                                            <span class="w-1.5 h-1.5 rounded-full {{ $producto->disponible ? 'bg-emerald-500' : 'bg-zinc-600' }}"></span>
                                                            {{ $producto->disponible ? 'Activo' : 'Inactivo' }}
                                                        </span>
                                                    </button>
                                                </form>

                                                <a href="{{ route('restaurante.productos.edit', $producto) }}"
                                                   class="btn-ghost text-xs px-3 py-1.5">
                                                    Editar
                                                </a>

                                                <form action="{{ route('restaurante.productos.destroy', $producto) }}" method="POST"
                                                      x-data x-on:submit.prevent="if(confirm('¿Eliminar «{{ $producto->nombre }}»?')) $el.submit()">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn-danger text-xs px-3 py-1.5">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
