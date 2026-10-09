<x-auth-layout title="Connexion - SolarShare">
    <div class="mb-8">
        <h1 class="text-3xl text-[#0C6030]" style="font-family: Fraunces, Georgia, serif;">Connexion</h1>
        <p class="mt-2 text-sm leading-relaxed text-[#3d5246]">Accédez à votre compte SolarShare.</p>
    </div>

    <x-auth-session-status class="mb-5 rounded-xl border border-[#c9e6d2] bg-[#f3faf5] px-4 py-3 text-[#0C6030]" :status="session('status')" />

    @if ($errors->has('form') || $errors->has('role') || $errors->has('remember'))
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3" role="alert">
            <x-input-error :messages="array_merge($errors->get('form'), $errors->get('role'), $errors->get('remember'))" />
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('login') }}"
        class="space-y-5"
        novalidate
        x-data="{ submitting: false }"
        x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }"
    >
        @csrf

        <div>
            <x-input-label for="email" value="Email" class="text-[#163226]" />
            <x-text-input
                id="email"
                class="mt-1.5 block w-full rounded-xl border-[#c5d9cb] px-3.5 py-3 text-[#163226] shadow-none placeholder:text-[#8aa092]"
                type="text"
                name="email"
                inputmode="email"
                :value="old('email')"
                autofocus
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
                autocomplete="current-password"
                placeholder="Votre mot de passe"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center gap-2">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="rounded border-[#c5d9cb] text-[#0C6030] shadow-sm focus:ring-[#189C48]"
                >
                <span class="text-sm text-[#3d5246]">Se souvenir de moi</span>
            </label>

            @if (Route::has('password.request'))
                <a
                    href="{{ route('password.request') }}"
                    class="text-sm font-medium text-[#0C6030] underline-offset-4 transition-colors hover:text-[#189C48] hover:underline focus:outline-none focus-visible:underline"
                >
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        <button
            type="submit"
            class="w-full rounded-xl bg-[#0C6030] px-4 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#094d26] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#189C48] focus-visible:ring-offset-2"
            x-bind:class="submitting ? 'cursor-wait opacity-70' : ''"
            x-bind:aria-busy="submitting"
        >
            <span x-show="!submitting">Se connecter</span>
            <span x-cloak x-show="submitting">Connexion...</span>
        </button>
    </form>

    <p class="mt-8 border-t border-[#e3eee6] pt-6 text-center text-sm text-[#3d5246]">
        Pas encore de compte ?
        <a href="{{ route('register') }}" class="font-semibold text-[#0C6030] underline-offset-4 transition-colors hover:text-[#189C48] hover:underline">
            Créer un compte
        </a>
    </p>
</x-auth-layout>
