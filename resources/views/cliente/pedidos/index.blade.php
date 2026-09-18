<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-white text-xl">Mis pedidos</h2>
                <p class="text-zinc-500 text-sm mt-0.5">Historial completo de tus órdenes</p>
            </div>
            <a href="{{ route('cliente.restaurantes.index') }}" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nuevo pedido
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($pedidos->isEmpty())
                <div class="card p-16 text-center">
                    <div class="w-20 h-20 bg-zinc-800 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-zinc-500">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-black text-white mb-2">Aún no tienes pedidos</h3>
                    <p class="text-zinc-400 text-sm mb-6">Cuando hagas tu primer pedido aparecerá aquí.</p>
                    <a href="{{ route('cliente.restaurantes.index') }}" class="btn-primary">
                        Ver restaurantes
                    </a>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($pedidos as $pedido)
                        <a href="{{ route('cliente.pedidos.show', $pedido) }}"
                           class="card p-5 flex items-center gap-4 group hover:-translate-y-0.5 block">
                            {{-- Icono --}}
                            <div class="w-11 h-11 bg-zinc-800 rounded-xl flex items-center justify-center shrink-0
                                        group-hover:bg-zinc-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-zinc-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                                </svg>
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-white text-sm truncate">
                                    {{ $pedido->restaurante->nombre ?? 'Restaurante' }}
                                </p>
                                <p class="text-zinc-500 text-xs mt-0.5">
                                    {{ $pedido->created_at->format('d M Y · H:i') }}
                                    · {{ $pedido->items->count() }} {{ $pedido->items->count() === 1 ? 'producto' : 'productos' }}
                                </p>
                            </div>

                            {{-- Badge + Total --}}
                            <div class="flex flex-col items-end gap-1.5 shrink-0">
                                <span class="{{ $pedido->estadoBadgeClass() }}">{{ $pedido->estadoLabel() }}</span>
                                <span class="font-black text-white text-sm">
                                    ${{ number_format($pedido->total, 0, ',', '.') }}
                                </span>
                            </div>

                            {{-- Flecha --}}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                 class="w-4 h-4 text-zinc-600 group-hover:text-zinc-400 shrink-0 transition-colors">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                            </svg>
                        </a>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $pedidos->links() }}
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
