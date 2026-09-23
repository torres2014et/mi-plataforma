<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-white text-xl">Panel del restaurante</h2>
                <p class="text-sm text-zinc-500 mt-0.5">Hola, <span class="font-semibold text-zinc-300">{{ Auth::user()->name }}</span> · {{ $restaurante->nombre }}</p>
            </div>
            <a href="{{ route('restaurante.productos.create') }}" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nuevo producto
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- ── KPIs ─────────────────────────────────────────── --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- Pedidos hoy --}}
                <div class="stat-card card-interactive bg-gradient-to-br from-gray-900 to-gray-800" data-reveal data-reveal-delay="0">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 blur-2xl"></div>
                    <p class="text-white/50 text-xs font-semibold uppercase tracking-wide">Pedidos hoy</p>
                    <p class="text-3xl font-black text-white mt-1">{{ $pedidosHoy }}</p>
                    <p class="text-white/30 text-xs mt-2">
                        @if($pendientes > 0)
                            <span class="text-amber-400 font-semibold">{{ $pendientes }} sin terminar</span>
                        @else
                            Recibidos hoy
                        @endif
                    </p>
                </div>

                {{-- Ingresos hoy --}}
                <div class="stat-card card-interactive bg-gradient-to-br from-brand-600 to-brand-800" data-reveal data-reveal-delay="60">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 blur-2xl"></div>
                    <p class="text-white/60 text-xs font-semibold uppercase tracking-wide">Ingresos hoy</p>
                    <p class="text-3xl font-black text-white mt-1">${{ number_format($ingresosHoy, 0, ',', '.') }}</p>
                    <p class="text-white/40 text-xs mt-2">Solo pedidos entregados</p>
                </div>

                {{-- Ingresos del mes --}}
                <div class="stat-card card-interactive bg-gradient-to-br from-emerald-700 to-emerald-900" data-reveal data-reveal-delay="120">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 blur-2xl"></div>
                    <p class="text-white/60 text-xs font-semibold uppercase tracking-wide">Ingresos del mes</p>
                    <p class="text-3xl font-black text-white mt-1">${{ number_format($ingresosMes, 0, ',', '.') }}</p>
                    <p class="text-white/40 text-xs mt-2">Ticket prom. ${{ number_format($ticketPromedio, 0, ',', '.') }}</p>
                </div>

                {{-- Productos activos --}}
                <div class="stat-card card-interactive bg-gradient-to-br from-violet-700 to-violet-900" data-reveal data-reveal-delay="180">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 blur-2xl"></div>
                    <p class="text-white/60 text-xs font-semibold uppercase tracking-wide">Productos activos</p>
                    <p class="text-3xl font-black text-white mt-1">{{ $productosActivos }}</p>
                    <p class="text-white/40 text-xs mt-2">de {{ $totalProductos }} en el menú</p>
                </div>
            </div>

            {{-- ── Gráfica de ventas + Top productos ───────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                {{-- Ventas últimos 7 días --}}
                @php $maxIng = max(array_map(fn($d) => $d['ingresos'], $ventasSemana)) ?: 1; @endphp
                <div class="card p-6 lg:col-span-3">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="font-bold text-zinc-100">Ventas de los últimos 7 días</h3>
                        <span class="text-xs text-zinc-500 font-semibold">${{ number_format($ingresosSemana, 0, ',', '.') }} en total</span>
                    </div>

                    <div class="flex items-end justify-between gap-2 h-44">
                        @foreach($ventasSemana as $dia)
                            <div class="flex-1 flex flex-col items-center gap-2 h-full justify-end group">
                                <span class="text-[10px] font-bold {{ $dia['ingresos'] > 0 ? 'text-zinc-300' : 'text-zinc-700' }}">
                                    ${{ number_format($dia['ingresos'] / 1000, 0) }}k
                                </span>
                                <div class="w-full rounded-t-lg transition-all duration-300
                                            {{ $dia['esHoy'] ? 'bg-gradient-to-t from-brand-600 to-brand-400' : 'bg-zinc-700 group-hover:bg-zinc-600' }}"
                                     style="height: {{ max(4, ($dia['ingresos'] / $maxIng) * 100) }}%"
                                     title="{{ $dia['pedidos'] }} pedidos · ${{ number_format($dia['ingresos'], 0, ',', '.') }}">
                                </div>
                                <span class="text-[11px] font-bold {{ $dia['esHoy'] ? 'text-brand-400' : 'text-zinc-500' }}">{{ $dia['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Top productos --}}
                <div class="card p-6 lg:col-span-2">
                    <h3 class="font-bold text-zinc-100 mb-5">Productos más vendidos</h3>

                    @if($topProductos->isEmpty())
                        <div class="flex flex-col items-center justify-center text-center py-10">
                            <div class="w-12 h-12 bg-zinc-800 rounded-xl flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                            </div>
                            <p class="text-zinc-400 text-sm font-semibold">Aún no hay ventas</p>
                            <p class="text-zinc-600 text-xs mt-1">Cuando entregues pedidos verás aquí tu ranking.</p>
                        </div>
                    @else
                        @php $maxUnid = $topProductos->max('unidades') ?: 1; @endphp
                        <div class="space-y-4">
                            @foreach($topProductos as $i => $p)
                                <div>
                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-5 h-5 shrink-0 rounded-md text-[11px] font-black flex items-center justify-center
                                                {{ $i === 0 ? 'bg-brand-500 text-white' : 'bg-zinc-800 text-zinc-400' }}">{{ $i + 1 }}</span>
                                            <span class="text-sm font-semibold text-zinc-200 truncate">{{ $p->nombre }}</span>
                                        </div>
                                        <span class="text-xs text-zinc-500 shrink-0">{{ $p->unidades }} uds · <span class="text-zinc-300 font-semibold">${{ number_format($p->ingresos, 0, ',', '.') }}</span></span>
                                    </div>
                                    <div class="h-1.5 bg-zinc-800 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-400"
                                             style="width: {{ max(6, ($p->unidades / $maxUnid) * 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- ── Actividad reciente ──────────────────────────── --}}
            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-white/8 flex items-center justify-between">
                    <h3 class="font-bold text-zinc-100">Actividad reciente</h3>
                    <a href="{{ route('restaurante.pedidos.index') }}" class="text-xs font-semibold text-brand-400 hover:text-brand-300 transition-colors">Ver todos →</a>
                </div>

                @forelse($actividadReciente as $pedido)
                    <a href="{{ route('restaurante.pedidos.index') }}"
                       class="flex items-center gap-4 px-6 py-4 border-b border-white/[0.05] last:border-0 hover:bg-white/[0.02] transition-colors">
                        <div class="w-10 h-10 rounded-xl bg-zinc-800 flex items-center justify-center shrink-0 text-zinc-400 font-black text-sm">
                            #{{ $pedido->id }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-zinc-200 truncate">{{ $pedido->cliente->name ?? 'Cliente' }}</p>
                            <p class="text-zinc-500 text-xs mt-0.5">
                                {{ $pedido->items->sum('cantidad') }} producto(s) · {{ $pedido->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <span class="{{ $pedido->estadoBadgeClass() }}">{{ $pedido->estadoLabel() }}</span>
                        <span class="font-bold text-zinc-200 text-sm shrink-0 w-20 text-right">${{ number_format($pedido->total, 0, ',', '.') }}</span>
                    </a>
                @empty
                    <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                        <div class="w-16 h-16 bg-zinc-800 rounded-2xl flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z"/></svg>
                        </div>
                        <p class="font-semibold text-zinc-300">Sin pedidos todavía</p>
                        <p class="text-sm text-zinc-500 mt-1 max-w-xs">Cuando lleguen pedidos aparecerán aquí en tiempo real.</p>
                    </div>
                @endforelse
            </div>

            {{-- ── Acciones rápidas ────────────────────────────── --}}
            <div>
                <h3 class="text-xs font-bold text-zinc-500 uppercase tracking-widest mb-4">Acciones rápidas</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                    <a href="{{ route('restaurante.productos.index') }}"
                       class="card p-5 flex items-center gap-4 group hover:-translate-y-0.5">
                        <div class="w-12 h-12 bg-gradient-to-br from-brand-400 to-brand-600 rounded-xl flex items-center justify-center text-white shadow-brand-xs shrink-0 group-hover:scale-110 group-hover:shadow-brand-sm transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-zinc-100 group-hover:text-brand-400 transition-colors">Gestionar Menú</p>
                            <p class="text-zinc-500 text-xs mt-0.5">Agrega, edita y organiza tus productos</p>
                        </div>
                    </a>

                    <a href="{{ route('restaurante.pedidos.index') }}"
                       class="card p-5 flex items-center gap-4 group hover:-translate-y-0.5">
                        <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-amber-600 rounded-xl flex items-center justify-center text-white shadow-sm shrink-0 group-hover:scale-110 transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0M3.124 7.5A8.969 8.969 0 015.292 3m13.416 0a8.969 8.969 0 012.168 4.5"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-zinc-100 group-hover:text-amber-400 transition-colors">Ver Pedidos</p>
                            <p class="text-zinc-500 text-xs mt-0.5">Confirma y despacha cada pedido</p>
                        </div>
                    </a>

                    <a href="{{ route('restaurante.configuracion') }}"
                       class="card p-5 flex items-center gap-4 group hover:-translate-y-0.5">
                        <div class="w-12 h-12 bg-gradient-to-br from-slate-600 to-slate-800 rounded-xl flex items-center justify-center text-white shadow-sm shrink-0 group-hover:scale-110 transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.43l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-zinc-100 group-hover:text-zinc-300 transition-colors">Configuración</p>
                            <p class="text-zinc-500 text-xs mt-0.5">Horarios, datos del local e imagen</p>
                        </div>
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
