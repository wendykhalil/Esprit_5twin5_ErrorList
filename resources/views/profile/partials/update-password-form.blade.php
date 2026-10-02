<section>
    <header class="mb-6">
        <h2 class="text-2xl font-semibold text-green-900 mb-2" style="font-family: Fraunces, Georgia, serif">
            {{ __('Sécurité') }}
        </h2>

        <p class="text-gray-700">
            {{ __('Modifiez votre mot de passe pour sécuriser votre compte.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-medium text-gray-900 mb-2">
                {{ __('Mot de passe actuel') }}
            </label>
            <input 
                id="update_password_current_password" 
                name="current_password" 
                type="password" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent focus:outline-none transition"
                autocomplete="current-password"
            />
            @error('current_password', 'updatePassword')
                <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-medium text-gray-900 mb-2">
                {{ __('Nouveau mot de passe') }}
            </label>
            <input 
                id="update_password_password" 
                name="password" 
                type="password" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent focus:outline-none transition"
                autocomplete="new-password"
            />
            @error('password', 'updatePassword')
                <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-medium text-gray-900 mb-2">
                {{ __('Confirmer le mot de passe') }}
            </label>
            <input 
                id="update_password_password_confirmation" 
                name="password_confirmation" 
                type="password" 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-600 focus:border-transparent focus:outline-none transition"
                autocomplete="new-password"
            />
            @error('password_confirmation', 'updatePassword')
                <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
            <button 
                type="submit"
                class="inline-flex items-center px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors"
            >
                {{ __('Enregistrer') }}
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600 font-medium"
                >{{ __('Mot de passe mis à jour avec succès.') }}</p>
            @endif
        </div>
    </form>
</section>
