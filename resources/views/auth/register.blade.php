<x-auth-layout title="Inscription - SolarShare">
    <div class="mb-8">
        <h1 class="text-3xl text-[#0C6030]" style="font-family: Fraunces, Georgia, serif;">Créer un compte</h1>
        <p class="mt-2 text-sm leading-relaxed text-[#3d5246]">Rejoignez SolarShare pour partager et gérer l'énergie solaire.</p>
    </div>

    @if ($errors->has('form') || $errors->has('role'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3" role="alert">
            <x-input-error :messages="array_merge($errors->get('form'), $errors->get('role'))" />
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('register') }}"
        class="space-y-5"
        novalidate
        x-data="{ submitting: false }"
        x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }"
    >
        @csrf

        <div>
            <x-input-label for="name" value="Nom" class="text-[#163226]" />
            <x-text-input
                id="name"
                class="mt-1.5 block w-full rounded-xl border-[#c5d9cb] px-3.5 py-3 text-[#163226] shadow-none placeholder:text-[#8aa092]"
                type="text"
                name="name"
                :value="old('name')"
                autofocus
                autocomplete="name"
                placeholder="Votre nom"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Email" class="text-[#163226]" />
            <x-text-input
                id="email"
                class="mt-1.5 block w-full rounded-xl border-[#c5d9cb] px-3.5 py-3 text-[#163226] shadow-none placeholder:text-[#8aa092]"
                type="text"
                name="email"
                inputmode="email"
                :value="old('email')"
                autocomplete="username"
                placeholder="vous@exemple.com"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" value="Mot de passe" class="text-[#163226]" />
            <x-auth-password-input
                id="password"
                class="mt-1.5 rounded-xl border-[#c5d9cb] px-3.5 py-3 text-[#163226] shadow-none placeholder:text-[#8aa092]"
                name="password"
                autocomplete="new-password"
                placeholder="8 caractères minimum"
            />
            <p class="mt-1.5 text-xs text-[#5d7366]">8 caractères minimum, 72 octets maximum.</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" class="text-[#163226]" />
            <x-auth-password-input
                id="password_confirmation"
                class="mt-1.5 rounded-xl border-[#c5d9cb] px-3.5 py-3 text-[#163226] shadow-none placeholder:text-[#8aa092]"
                name="password_confirmation"
                autocomplete="new-password"
                placeholder="Répétez le mot de passe"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button
            type="submit"
            class="w-full rounded-xl bg-[#0C6030] px-4 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#094d26] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#189C48] focus-visible:ring-offset-2"
            x-bind:class="submitting ? 'cursor-wait opacity-70' : ''"
            x-bind:aria-busy="submitting"
        >
            <span x-show="!submitting">Créer mon compte</span>
            <span x-cloak x-show="submitting">Création du compte...</span>
        </button>
    </form>

    <p class="mt-8 border-t border-[#e3eee6] pt-6 text-center text-sm text-[#3d5246]">
        Déjà inscrit ?
        <a href="{{ route('login') }}" class="font-semibold text-[#0C6030] underline-offset-4 transition-colors hover:text-[#189C48] hover:underline">
            Se connecter
        </a>
    </p>
</x-auth-layout>
