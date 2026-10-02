<section class="bg-white rounded-xl shadow-md p-8 border-l-4 border-red-600">
    <header class="mb-6">
        <h2 class="text-2xl font-semibold text-red-600 mb-2" style="font-family: Fraunces, Georgia, serif">
            {{ __('Zone de Danger') }}
        </h2>
        <p class="text-gray-700">
            {{ __('Une fois votre compte supprimé, toutes vos ressources et données seront définitivement supprimées. Veuillez télécharger les données que vous souhaitez conserver avant de supprimer votre compte.') }}
        </p>
    </header>

    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition duration-200"
    >
        {{ __('Supprimer mon compte') }}
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <div class="p-8">
            <h2 class="text-2xl font-semibold text-red-600 mb-4" style="font-family: Fraunces, Georgia, serif">
                {{ __('Êtes-vous certain de vouloir supprimer votre compte ?') }}
            </h2>

            <p class="text-gray-700 mb-6">
                {{ __('Cette action est irréversible. Une fois votre compte supprimé, toutes vos ressources et données seront définitivement supprimées. Veuillez entrer votre mot de passe pour confirmer la suppression permanente de votre compte.') }}
            </p>

            <form method="post" action="{{ route('profile.destroy') }}" class="space-y-6">
                @csrf
                @method('delete')

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-900 mb-2">
                        {{ __('Mot de passe') }}
                    </label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent focus:outline-none transition"
                        placeholder="{{ __('Entrez votre mot de passe') }}"
                    />
                    @error('password', 'userDeletion')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-4">
                    <button
                        type="button"
                        x-on:click="$dispatch('close')"
                        class="px-6 py-2 bg-gray-300 text-gray-900 font-semibold rounded-lg hover:bg-gray-400 transition duration-200"
                    >
                        {{ __('Annuler') }}
                    </button>

                    <button
                        type="submit"
                        class="px-6 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition duration-200"
                    >
                        {{ __('Supprimer définitivement') }}
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
</section>
