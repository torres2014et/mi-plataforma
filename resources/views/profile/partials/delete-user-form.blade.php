<section class="space-y-6">
    <header>
        <h3 class="form-section-title text-red-400">Eliminar cuenta</h3>
        <p class="mt-2 text-sm text-zinc-500">
            Una vez eliminada tu cuenta, todos los datos serán borrados permanentemente.
            Descarga cualquier información que desees conservar antes de proceder.
        </p>
    </header>

    <button type="button" class="btn-danger"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
        </svg>
        Eliminar mi cuenta
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-white mb-1">
                ¿Confirmas que deseas eliminar tu cuenta?
            </h2>

            <p class="text-sm text-zinc-400 mb-6">
                Esta acción es irreversible. Todos tus datos, pedidos e información serán eliminados permanentemente.
                Ingresa tu contraseña para confirmar.
            </p>

            <div class="mb-6">
                <label class="sr-only" for="password">Contraseña</label>
                <input id="password" name="password" type="password"
                       class="input w-3/4"
                       placeholder="Ingresa tu contraseña"/>
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-xs text-red-400" />
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="btn-secondary">
                    Cancelar
                </button>
                <button type="submit" class="btn-danger">
                    Sí, eliminar cuenta
                </button>
            </div>
        </form>
    </x-modal>
</section>
