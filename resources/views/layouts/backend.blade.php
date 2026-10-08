<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <title>@yield('title', 'SolarShare Admin')</title>
</head>

<body class="min-h-screen bg-slate-50">

    @include('components.backend.sidebar')

    {{-- Scroll sur la page entière (navbar + contenu), pas seulement dans <main> --}}
    <div class="min-w-0 lg:ml-64">
        @include('components.backend.navbar')

        <main>
            <div class="p-4 lg:p-6">
                @yield('content')
            </div>
        </main>
    </div>

</body>
</html>
