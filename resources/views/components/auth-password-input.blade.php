@props(['disabled' => false])

<div class="relative" x-data="{ show: false }">
    <input
        @disabled($disabled)
        type="password"
        x-bind:type="show ? 'text' : 'password'"
        {{ $attributes->merge(['class' => 'block w-full pr-12']) }}
    >

    <button
        type="button"
        class="absolute inset-y-0 right-0 flex items-center px-3.5 text-[#3d5246] transition-colors hover:text-[#0C6030] focus:outline-none focus-visible:text-[#0C6030]"
        @click="show = !show"
        :aria-label="show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
        :aria-pressed="show"
    >
        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12S5.25 6.75 12 6.75 21.75 12 21.75 12 18.75 17.25 12 17.25 2.25 12 2.25 12Z" />
            <circle cx="12" cy="12" r="2.25" />
        </svg>
        <svg x-cloak x-show="show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 2.25 12S5.25 17.25 12 17.25a9.77 9.77 0 0 0 4.52-1.07M6.228 6.228A9.77 9.77 0 0 1 12 6.75c6.75 0 9.75 5.25 9.75 5.25a18.3 18.3 0 0 1-2.478 3.222M6.228 6.228 3 3m3.228 3.228 11.544 11.544M21 21l-3.228-3.228" />
        </svg>
    </button>
</div>
