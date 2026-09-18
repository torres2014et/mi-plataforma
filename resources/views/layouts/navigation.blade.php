<nav x-data="{ open: false }" class="bg-gray-950 border-b border-white/5 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            {{-- Logo + Nav links --}}
            <div class="flex items-center gap-8">
                <a href="{{ route('welcome') }}" class="flex items-center gap-2.5 shrink-0 group">
                    <x-application-logo class="w-8 h-8 transition-transform group-hover:scale-110 duration-300" />
                    <div class="hidden sm:block">
                        <p class="text-white font-black text-base leading-none tracking-tight">Mi Plataforma</p>
                        <p class="text-brand-500 text-[9px] tracking-widest font-bold mt-0.5">UBATÉ · DELIVERY</p>
                    </div>
                </a>

                {{-- Links según rol --}}
                <div class="hidden sm:flex items-center gap-1">
                    @auth
                        @if(auth()->user()->hasRole('cliente'))
                            <a href="{{ route('cliente.dashboard') }}"
                               class="px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150
                                      {{ request()->routeIs('cliente.dashboard') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                                Inicio
                            </a>
                            <a href="{{ route('cliente.restaurantes.index') }}"
                               class="px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150
                                      {{ request()->routeIs('cliente.restaurantes.*') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                                Restaurantes
                            </a>
                            <a href="{{ route('cliente.pedidos.index') }}"
                               class="px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150
                                      {{ request()->routeIs('cliente.pedidos.*') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                                Mis pedidos
                            </a>
                        @elseif(auth()->user()->hasRole('restaurante'))
                            <a href="{{ route('restaurante.dashboard') }}"
                               class="px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150
                                      {{ request()->routeIs('restaurante.dashboard') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                                Inicio
                            </a>
                            <a href="{{ route('restaurante.productos.index') }}"
                               class="px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150
                                      {{ request()->routeIs('restaurante.productos.*') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                                Mi Menú
                            </a>
                            @php
                                $notifPedidos = auth()->user()->unreadNotifications()
                                    ->where('type', \App\Notifications\NuevoPedido::class)
                                    ->count();
                            @endphp
                            <a href="{{ route('restaurante.pedidos.index') }}"
                               class="relative px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150
                                      {{ request()->routeIs('restaurante.pedidos.*') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                                Pedidos
                                @if($notifPedidos > 0)
                                    <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-brand-500 text-white text-[9px] font-black rounded-full flex items-center justify-center shadow-brand-xs">
                                        {{ $notifPedidos > 9 ? '9+' : $notifPedidos }}
                                    </span>
                                @endif
                            </a>
                            <a href="{{ route('restaurante.configuracion') }}"
                               class="px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150
                                      {{ request()->routeIs('restaurante.configuracion*') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                                Configuración
                            </a>
                        @elseif(auth()->user()->hasRole('domiciliario'))
                            <a href="{{ route('domiciliario.dashboard') }}"
                               class="px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150
                                      {{ request()->routeIs('domiciliario.dashboard') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                                Inicio
                            </a>
                        @elseif(auth()->user()->hasRole('admin'))
                            <a href="{{ route('admin.dashboard') }}"
                               class="px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150
                                      {{ request()->routeIs('admin.dashboard') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('admin.usuarios.index') }}"
                               class="px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150
                                      {{ request()->routeIs('admin.usuarios.*') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                                Usuarios
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            {{-- Lado derecho --}}
            <div class="hidden sm:flex sm:items-center gap-3">
                @auth
                    @php
                        $role = auth()->user()->getRoleNames()->first();
                        $roleConfig = [
                            'cliente'      => ['label' => 'Cliente',      'class' => 'bg-blue-500/15 text-blue-300 border-blue-500/20'],
                            'restaurante'  => ['label' => 'Restaurante',  'class' => 'bg-brand-500/15 text-brand-300 border-brand-500/20'],
                            'domiciliario' => ['label' => 'Domiciliario', 'class' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/20'],
                            'admin'        => ['label' => 'Admin',        'class' => 'bg-purple-500/15 text-purple-300 border-purple-500/20'],
                        ];
                        $rc = $roleConfig[$role] ?? ['label' => $role, 'class' => 'bg-white/10 text-white/60 border-white/10'];
                    @endphp
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full border {{ $rc['class'] }}">
                        {{ $rc['label'] }}
                    </span>

                    {{-- Dropdown usuario --}}
                    <x-dropdown align="right" width="52">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl
                                           text-white/80 hover:text-white hover:bg-white/10
                                           transition-all duration-150 focus:outline-none group">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-black
                                            bg-gradient-to-br from-brand-500 to-brand-700 shadow-brand-xs">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-semibold max-w-24 truncate">{{ Auth::user()->name }}</span>
                                <svg class="w-3.5 h-3.5 text-white/40 group-hover:text-white/70 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <div class="px-4 py-3 border-b border-white/8">
                                <p class="text-xs text-zinc-500 font-medium">Sesión iniciada como</p>
                                <p class="text-sm font-bold text-white truncate mt-0.5">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-zinc-500 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Mi perfil
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="flex items-center gap-2 !text-red-600 hover:!bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Cerrar sesión
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endauth
            </div>

            {{-- Botón menú móvil --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = !open"
                        class="p-2 rounded-lg text-white/50 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Menú móvil --}}
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden border-t border-white/5 bg-gray-900">
        <div class="pt-2 pb-3 px-4 space-y-1">
            @auth
                @if(auth()->user()->hasRole('cliente'))
                    <a href="{{ route('cliente.dashboard') }}"
                       class="block px-3 py-2.5 rounded-xl text-sm font-semibold
                              {{ request()->routeIs('cliente.dashboard') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                        Inicio
                    </a>
                    <a href="{{ route('cliente.restaurantes.index') }}"
                       class="block px-3 py-2.5 rounded-xl text-sm font-semibold
                              {{ request()->routeIs('cliente.restaurantes.*') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                        Restaurantes
                    </a>
                    <a href="{{ route('cliente.pedidos.index') }}"
                       class="block px-3 py-2.5 rounded-xl text-sm font-semibold
                              {{ request()->routeIs('cliente.pedidos.*') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                        Mis pedidos
                    </a>
                @elseif(auth()->user()->hasRole('restaurante'))
                    <a href="{{ route('restaurante.dashboard') }}"
                       class="block px-3 py-2.5 rounded-xl text-sm font-semibold
                              {{ request()->routeIs('restaurante.dashboard') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                        Inicio
                    </a>
                    <a href="{{ route('restaurante.productos.index') }}"
                       class="block px-3 py-2.5 rounded-xl text-sm font-semibold
                              {{ request()->routeIs('restaurante.productos.*') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                        Mi Menú
                    </a>
                    <a href="{{ route('restaurante.pedidos.index') }}"
                       class="relative block px-3 py-2.5 rounded-xl text-sm font-semibold
                              {{ request()->routeIs('restaurante.pedidos.*') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                        Pedidos
                        @if(isset($notifPedidos) && $notifPedidos > 0)
                            <span class="ml-1.5 inline-flex items-center justify-center w-4 h-4 bg-brand-500 text-white text-[9px] font-black rounded-full">
                                {{ $notifPedidos > 9 ? '9+' : $notifPedidos }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('restaurante.configuracion') }}"
                       class="block px-3 py-2.5 rounded-xl text-sm font-semibold
                              {{ request()->routeIs('restaurante.configuracion*') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                        Configuración
                    </a>
                @elseif(auth()->user()->hasRole('domiciliario'))
                    <a href="{{ route('domiciliario.dashboard') }}"
                       class="block px-3 py-2.5 rounded-xl text-sm font-semibold text-white/60 hover:text-white hover:bg-white/10">
                        Inicio
                    </a>
                @elseif(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}"
                       class="block px-3 py-2.5 rounded-xl text-sm font-semibold
                              {{ request()->routeIs('admin.dashboard') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.usuarios.index') }}"
                       class="block px-3 py-2.5 rounded-xl text-sm font-semibold
                              {{ request()->routeIs('admin.usuarios.*') ? 'text-white bg-white/10' : 'text-white/60 hover:text-white hover:bg-white/10' }}">
                        Usuarios
                    </a>
                @endif
            @endauth
        </div>
        <div class="pt-3 pb-4 border-t border-white/5 px-4">
            @auth
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white text-sm font-black">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-white/40">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <div class="space-y-1">
                    <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-sm text-white/60 hover:text-white hover:bg-white/10 rounded-xl transition-colors">Mi perfil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button onclick="event.preventDefault(); this.closest('form').submit();"
                                class="w-full text-left px-3 py-2 text-sm text-red-400 hover:text-red-300 hover:bg-red-500/10 rounded-xl transition-colors">
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</nav>
