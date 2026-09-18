<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('restaurante.productos.index') }}"
               class="flex items-center gap-1.5 text-zinc-500 hover:text-zinc-200 transition-colors text-sm font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
                Mi Menú
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-zinc-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
            <h2 class="font-black text-white text-xl">Agregar producto</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="form-section">
                <form action="{{ route('restaurante.productos.store') }}" method="POST"
                      enctype="multipart/form-data" class="space-y-5"
                      x-data="{ preview: null }">
                    @csrf

                    {{-- Imagen --}}
                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-2">Foto del producto</label>
                        <div class="flex items-center gap-4">
                            <div class="w-24 h-24 rounded-xl overflow-hidden bg-gradient-to-br from-zinc-800 to-zinc-700 border border-white/10 flex items-center justify-center shrink-0">
                                <template x-if="preview">
                                    <img :src="preview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!preview">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-zinc-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                                    </svg>
                                </template>
                            </div>
                            <div class="flex-1">
                                <label class="cursor-pointer btn-secondary text-sm px-4 py-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                    </svg>
                                    Subir imagen
                                    <input type="file" name="imagen" accept="image/*" class="hidden"
                                           @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                                </label>
                                <p class="text-xs text-zinc-500 mt-1.5">JPG, PNG o WebP · máx. 2 MB</p>
                            </div>
                        </div>
                        @error('imagen') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-1">Nombre del producto <span class="text-brand-500">*</span></label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" required
                               placeholder="Ej: Hamburguesa Clásica"
                               class="input @error('nombre') !border-red-400 !ring-red-200 @enderror">
                        @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-1">Descripción</label>
                        <textarea name="descripcion" rows="3"
                                  placeholder="Describe brevemente el producto, ingredientes, tamaño..."
                                  class="input resize-none">{{ old('descripcion') }}</textarea>
                        @error('descripcion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-1">Categoría</label>
                        <input type="text" name="categoria" value="{{ old('categoria') }}"
                               placeholder="Ej: Hamburguesas, Bebidas, Postres..."
                               list="categorias-list"
                               class="input">
                        <datalist id="categorias-list">
                            @foreach($categorias as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                        @error('categoria') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-zinc-300 mb-1">Precio (COP) <span class="text-brand-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 text-sm font-bold">$</span>
                            <input type="number" name="precio" value="{{ old('precio') }}" required min="0" step="100"
                                   placeholder="15000"
                                   class="input pl-8 @error('precio') !border-red-400 @enderror">
                        </div>
                        @error('precio') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3 bg-zinc-800/50 rounded-xl px-4 py-3">
                        <input type="checkbox" name="disponible" id="disponible" value="1" checked
                               class="w-4 h-4 rounded accent-brand-500">
                        <label for="disponible" class="text-sm font-semibold text-zinc-300 cursor-pointer">
                            Disponible para pedidos
                        </label>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit" class="btn-primary flex-1 py-3 text-base">
                            Guardar producto
                        </button>
                        <a href="{{ route('restaurante.productos.index') }}" class="btn-secondary flex-1 py-3 text-center">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
