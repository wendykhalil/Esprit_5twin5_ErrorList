
<x-auth-layout title="Vérification de l'email - SolarShare">

    <div class="text-center">

        <!-- Email Icon -->
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-[#e8f5ec]">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-10 w-10 text-[#0C6030]"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="1.5"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-8.69 5.53a2.25 2.25 0 0 1-2.42 0L2.25 6.75"
                />
            </svg>
        </div>

        <!-- Heading -->
        <h1
            class="text-3xl font-semibold text-[#0C6030]"
            style="font-family: Fraunces, Georgia, serif;"
        >
            Vérifiez votre adresse email
        </h1>

        <p class="mt-4 text-sm leading-7 text-[#3d5246]">
            Merci de vous être inscrit sur SolarShare !
            Pour sécuriser votre compte, veuillez confirmer
            votre adresse email en cliquant sur le lien
            de vérification.
        </p>

        @auth
            <div class="mt-5 rounded-xl border border-[#c5d9cb] bg-[#f3faf5] px-4 py-3">
                <p class="text-xs font-medium text-[#5d7366]">
                    Adresse email associée à votre compte
                </p>

                <p class="mt-1 break-all text-sm font-semibold text-[#0C6030]">
                    {{ auth()->user()->email }}
                </p>
            </div>
        @endauth

        <!-- Success Message -->
        @if (session('status') === 'verification-link-sent')
            <div
                class="mt-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"
                role="status"
            >
                Un nouveau lien de vérification a été envoyé.
                Consultez votre messagerie.
            </div>
        @endif

        <!-- Resend Verification -->
        <form
            method="POST"
            action="{{ route('verification.send') }}"
            class="mt-7"
            x-data="{ submitting: false }"
            x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }"
        >
            @csrf

            <button
                type="submit"
                class="w-full rounded-xl bg-[#0C6030] px-4 py-3 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#094d26] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#189C48] focus-visible:ring-offset-2"
                x-bind:disabled="submitting"
                x-bind:class="submitting ? 'cursor-wait opacity-70' : ''"
            >
                <span x-show="!submitting">
                    Renvoyer l'email de vérification
                </span>

                <span x-cloak x-show="submitting">
                    Envoi en cours...
                </span>
            </button>
        </form>

        <!-- Additional Instructions -->
        <div class="mt-6 rounded-xl border border-[#e3eee6] bg-[#f8fbf9] px-4 py-4 text-left">
            <p class="text-sm font-semibold text-[#163226]">
                Vous n'avez pas reçu l'email ?
            </p>

            <p class="mt-2 text-xs leading-6 text-[#5d7366]">
                Vérifiez votre dossier spam ou cliquez sur le
                bouton ci-dessus pour recevoir un nouveau lien.
            </p>
        </div>

        <!-- Logout -->
        <form
            method="POST"
            action="{{ route('logout') }}"
            class="mt-6"
        >
            @csrf

            <button
                type="submit"
                class="text-sm font-semibold text-[#0C6030] underline-offset-4 transition-colors hover:text-[#189C48] hover:underline"
            >
                Se déconnecter
            </button>
        </form>

    </div>

</x-auth-layout>
