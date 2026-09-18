<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-black text-white text-xl">Gestión de usuarios</h2>
                <p class="text-sm text-zinc-500 mt-0.5">Administrar todos los usuarios de la plataforma</p>
            </div>
            <span class="badge-orange">Admin</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="px-4 py-3 rounded-xl bg-emerald-900/40 border border-emerald-800/50 text-emerald-400 text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Tabs de roles --}}
            <div class="flex gap-1 p-1 bg-zinc-900 rounded-xl border border-white/[0.06] w-fit">
                @foreach(['cliente' => 'Clientes', 'restaurante' => 'Restaurantes', 'domiciliario' => 'Domiciliarios', 'admin' => 'Admins'] as $r => $label)
                    <a href="{{ route('admin.usuarios.index', ['rol' => $r]) }}"
                       class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-150 flex items-center gap-2
                              {{ $rol === $r ? 'bg-white/10 text-white' : 'text-zinc-500 hover:text-zinc-300' }}">
                        {{ $label }}
                        <span class="text-xs font-black {{ $rol === $r ? 'text-brand-400' : 'text-zinc-600' }}">
                            {{ $conteos[$r] }}
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- Tabla --}}
            <div class="card overflow-hidden">
                @if($usuarios->isEmpty())
                    <div class="flex flex-col items-center justify-center py-14 text-center px-6">
                        <div class="w-16 h-16 bg-zinc-800 rounded-2xl flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-zinc-600">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                            </svg>
                        </div>
                        <p class="font-semibold text-zinc-400">No hay usuarios en este rol</p>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/[0.06]">
                                <th class="text-left px-5 py-3 text-xs font-black text-zinc-500 uppercase tracking-wider">Usuario</th>
                                <th class="text-left px-5 py-3 text-xs font-black text-zinc-500 uppercase tracking-wider hidden sm:table-cell">Email</th>
                                <th class="text-left px-5 py-3 text-xs font-black text-zinc-500 uppercase tracking-wider hidden md:table-cell">Registrado</th>
                                @if($rol === 'restaurante')
                                    <th class="text-left px-5 py-3 text-xs font-black text-zinc-500 uppercase tracking-wider">Estado</th>
                                @elseif($rol === 'domiciliario')
                                    <th class="text-left px-5 py-3 text-xs font-black text-zinc-500 uppercase tracking-wider">Disponibilidad</th>
                                @endif
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usuarios as $usuario)
                                <tr class="border-b border-white/[0.04] last:border-0 hover:bg-white/[0.02] transition-colors">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white text-xs font-black shrink-0">
                                                {{ strtoupper(substr($usuario->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-zinc-200">{{ $usuario->name }}</p>
                                                @if($rol === 'restaurante' && $usuario->restaurante)
                                                    <p class="text-xs text-zinc-500">{{ $usuario->restaurante->nombre }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-zinc-400 hidden sm:table-cell">{{ $usuario->email }}</td>
                                    <td class="px-5 py-4 text-zinc-500 hidden md:table-cell">
                                        {{ $usuario->created_at->format('d M Y') }}
                                    </td>
                                    @if($rol === 'restaurante')
                                        <td class="px-5 py-4">
                                            @if($usuario->restaurante)
                                                @if($usuario->restaurante->activo)
                                                    <span class="badge-green">Activo</span>
                                                @else
                                                    <span class="badge-gray">Inactivo</span>
                                                @endif
                                            @else
                                                <span class="badge-gray">Sin perfil</span>
                                            @endif
                                        </td>
                                    @elseif($rol === 'domiciliario')
                                        <td class="px-5 py-4">
                                            @if($usuario->disponible)
                                                <span class="badge-green">Disponible</span>
                                            @else
                                                <span class="badge-gray">No disponible</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-5 py-4 text-right">
                                        @if($rol === 'restaurante' && $usuario->restaurante)
                                            <form action="{{ route('admin.restaurantes.toggle', $usuario->restaurante) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                        class="{{ $usuario->restaurante->activo ? 'btn-secondary' : 'btn-primary' }} text-xs py-1.5 px-3">
                                                    {{ $usuario->restaurante->activo ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($usuarios->hasPages())
                        <div class="px-5 py-4 border-t border-white/[0.06]">
                            {{ $usuarios->links() }}
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
