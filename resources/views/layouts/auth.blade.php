<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'SolarShare' }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,560;9..144,650&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            [x-cloak] { display: none !important; }

            .auth-shell input:not([type='checkbox']):not([type='radio']):not([type='hidden']):focus,
            .auth-shell input:not([type='checkbox']):not([type='radio']):not([type='hidden']):focus-visible {
                border-color: #189C48 !important;
                outline: none !important;
                box-shadow: 0 0 0 3px rgba(24, 156, 72, 0.22) !important;
            }
        </style>
    </head>
    <body class="auth-shell min-h-screen bg-white text-[#163226] antialiased">
        <div class="min-h-screen lg:grid lg:grid-cols-2">
            <aside class="relative hidden overflow-hidden border-r border-[#d5e6da] bg-[#f4faf6] lg:flex lg:min-h-screen lg:flex-col lg:justify-between lg:px-14 lg:py-12">
                <div class="pointer-events-none absolute -left-16 top-24 h-56 w-56 rounded-full bg-[#189C48]/10"></div>
                <div class="pointer-events-none absolute bottom-10 right-0 h-40 w-40 rounded-full bg-[#0C6030]/5"></div>

                <a href="{{ route('home') }}" class="relative inline-flex">
                    <img
                        src="{{ asset('images/solarshare-logo.png') }}"
                        alt="SolarShare"
                        class="h-14 w-auto"
                    >
                </a>

                <div class="relative max-w-md">
                    <h2 class="text-[2.6rem] leading-[1.15] text-[#0C6030]" style="font-family: Fraunces, Georgia, serif;">
                        L'énergie solaire, partagée.
                    </h2>
                    <p class="mt-5 text-base leading-relaxed text-[#3d5246]">
                        Équipements, réservations et services techniques réunis dans un seul espace.
                    </p>
                </div>

                <p class="relative text-sm text-[#5d7366]">Une plateforme pour produire, louer et entretenir ensemble.</p>
            </aside>

            <main class="flex min-h-screen flex-col justify-center px-5 py-10 sm:px-8">
                <div class="mx-auto w-full max-w-[26rem]">
                    <a href="{{ route('home') }}" class="mb-8 flex justify-center lg:hidden">
                        <img
                            src="{{ asset('images/solarshare-logo.png') }}"
                            alt="SolarShare"
                            class="h-12 w-auto"
                        >
                    </a>

                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
