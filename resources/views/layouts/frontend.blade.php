<!DOCTYPE html>
<html lang="fr" class="frontend-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@6..72,400;6..72,600;6..72,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <title>@yield('title', 'SolarShare')</title>
    <style>
        body.frontend-body {
            display: block !important;
            overflow: visible !important;
            height: auto !important;
            min-height: 100vh;
        }

        #frontend-main {
            overflow: visible !important;
            height: auto !important;
            max-height: none !important;
            position: relative;
            z-index: 0;
        }

        /* Toujours au-dessus du contenu (cartes transform, panneaux sticky, etc.) */
        #frontend-site-nav {
            z-index: 1000 !important;
            isolation: isolate;
        }
    </style>
</head>

<body class="frontend-body bg-white">
    <!-- frontend-layout: sticky-nav v5 -->

    @unless(request()->routeIs('login', 'register'))
        @include('components.frontend.navbar')
    @endunless

    <main id="frontend-main">
        @yield('content')

        @unless(request()->routeIs('login', 'register'))
            @include('components.frontend.footer')
        @endunless
    </main>

    <script>
        document.body.classList.remove('overflow-y-hidden');
    </script>
</body>
</html>
