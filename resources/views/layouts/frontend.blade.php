<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@6..72,400;6..72,600;6..72,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <title>@yield('title', 'SolarShare')</title>
</head>

<body class="min-h-screen flex flex-col bg-white">

    @unless(request()->routeIs('login', 'register'))
        @include('components.frontend.navbar')
    @endunless

    <main class="flex-1">
        @yield('content')
    </main>

    @unless(request()->routeIs('login', 'register'))
        @include('components.frontend.footer')
    @endunless

</body>
</html>