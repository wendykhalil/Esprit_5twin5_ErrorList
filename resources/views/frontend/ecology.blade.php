@extends('layouts.frontend')

@section('title', 'Notre impact écologique - SolarShare')

@section('content')
    @php
        $formatStat = fn ($value) => number_format($value, 0, ',', ' ');
        $imageUrl = fn ($badge) => $badge->image
            ? (str_starts_with($badge->image, 'images/') ? asset($badge->image) : Storage::url($badge->image))
            : null;
        $criteria = [
            'kwh_total' => 'Atteindre un total de kWh partagé',
            'co2_total' => 'Éviter un total de kg de CO2',
            'reservations_terminees' => 'Terminer des locations',
            'retours_sans_dommage' => 'Effectuer des retours sans dommage',
            'releves_declares' => 'Déclarer des relevés d’énergie',
        ];
    @endphp

    <section class="relative overflow-hidden" style="background: radial-gradient(circle at 72% 45%, rgba(255,255,255,.45), transparent 28%), linear-gradient(110deg, #d8e6b5 0%, #afc982 52%, #9abb6a 100%);">
        <div class="relative mx-auto grid min-h-[27rem] max-w-7xl items-center gap-8 px-4 py-10 sm:px-6 lg:grid-cols-2 lg:px-12">
            <div class="relative z-10 max-w-md">
                <p class="text-xs font-bold uppercase tracking-[0.25em] text-green-900/60">Notre énergie collective</p>
                <h1 class="mt-4 text-4xl font-bold leading-[1.05] text-green-950 sm:text-5xl" style="font-family: Fraunces, Georgia, serif">Louer, c’est déjà <span class="text-green-700">agir</span>.</h1>
                <p class="mt-5 max-w-sm text-base leading-relaxed text-green-950/70">Chaque location donne une seconde vie à un équipement et rend l’énergie responsable plus accessible.</p>
                <a href="{{ route('impact.index') }}" class="mt-7 inline-flex rounded-xl bg-green-700 px-6 py-3 font-semibold text-white shadow-lg shadow-green-950/20 transition hover:bg-green-800">Voir plus</a>
            </div>
            <div class="relative hidden h-[23rem] lg:block">
                <div class="absolute left-0 top-10 h-56 w-40 -rotate-6 rounded-[1.4rem] p-3 shadow-xl" style="background: rgba(255,255,255,.58);">
                    <div class="flex h-full flex-col justify-between rounded-[1rem] p-3" style="background: linear-gradient(135deg, #86efac, #15803d);">
                        <span class="text-3xl">🌱</span><div><p class="text-xs font-bold text-green-950">Partager</p><p class="mt-1 text-[10px] text-green-950/70">un geste simple</p></div>
                    </div>
                </div>
                <div class="absolute left-28 top-2 z-10 h-56 w-40 rotate-6 rounded-[1.4rem] p-3 shadow-xl" style="background: rgba(255,255,255,.68);">
                    <div class="flex h-full flex-col justify-between rounded-[1rem] p-3" style="background: linear-gradient(135deg, #d9f99d, #22c55e);">
                        <span class="text-3xl">☀️</span><div><p class="text-xs font-bold text-green-950">Produire</p><p class="mt-1 text-[10px] text-green-950/70">une énergie utile</p></div>
                    </div>
                </div>
                <svg viewBox="0 0 330 300" class="absolute right-[-2rem] top-0 h-full w-[23rem]" role="img" aria-label="Planète verte entourée de nature">
                    <defs><radialGradient id="eco-globe" cx="35%" cy="25%"><stop offset="0" stop-color="#e3f5b9"/><stop offset=".55" stop-color="#78b64d"/><stop offset="1" stop-color="#2f6b3c"/></radialGradient></defs>
                    <circle cx="190" cy="153" r="104" fill="url(#eco-globe)"/>
                    <path d="M105 138c37-26 65-22 87-7 22 15 48 2 83-24 13 26 20 53 17 79-34 5-51 24-75 40-39 25-86-7-105-39-10-17-13-33-7-49Z" fill="#2d743d"/>
                    <path d="M128 112c25 8 38 0 51-15 15-16 31-13 43-4-10 22-27 34-51 34-17 0-31-5-43-15Zm72 86c25-13 44-11 60 3-15 20-34 30-58 35-16-10-17-24-2-38Z" fill="#d9efb0" opacity=".9"/>
                    <g fill="#3d8147"><path d="M82 269c-5-39 13-61 39-73 5 31-7 56-39 73Z"/><path d="M82 269c22-25 42-31 63-23-14 26-36 33-63 23Z"/></g>
                    <path d="M82 269v28" stroke="#2f6b3c" stroke-width="6" stroke-linecap="round"/>
                    <g fill="#f4bd43"><circle cx="91" cy="75" r="5"/><circle cx="286" cy="91" r="4"/><circle cx="274" cy="252" r="4"/></g>
                </svg>
            </div>
        </div>
    </section>

    <section class="bg-green-50 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-green-600">L’impact en mouvement</p>
                <h2 class="mt-3 text-4xl font-bold text-green-950" style="font-family: Fraunces, Georgia, serif">Des gestes concrets, des chiffres réels</h2>
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach([
                    ['key' => 'kwh', 'label' => 'kWh produits', 'suffix' => ' kWh', 'icon' => '☀️'],
                    ['key' => 'co2', 'label' => 'kg de CO2 évités', 'suffix' => ' kg', 'icon' => '🌿'],
                    ['key' => 'reservations', 'label' => 'locations terminées', 'suffix' => '', 'icon' => '🤝'],
                    ['key' => 'badges', 'label' => 'badges décernés', 'suffix' => '', 'icon' => '🏅'],
                ] as $stat)
                    <div x-data="{ value: 0, target: {{ $stats[$stat['key']] }} }" x-init="setTimeout(() => { const step = Math.max(1, target / 35); const timer = setInterval(() => { value = Math.min(target, value + step); if (value >= target) clearInterval(timer) }, 25) }, 150)" class="rounded-3xl border border-green-100 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between"><span class="text-3xl">{{ $stat['icon'] }}</span><span class="text-xs font-semibold uppercase tracking-wider text-green-500">SolarShare</span></div>
                        <p class="mt-7 text-3xl font-bold text-green-950"><span x-text="Math.round(value).toLocaleString('fr-FR')">0</span>{{ $stat['suffix'] }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
            @if(array_sum($stats) === 0)
                <p class="mt-8 text-center text-sm text-green-800">La communauté commence son parcours : soyez parmi les premiers à mesurer votre impact.</p>
            @endif
        </div>
    </section>

    <section class="bg-white py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-12 text-center"><h2 class="text-4xl font-bold text-green-950" style="font-family: Fraunces, Georgia, serif">Comment ça contribue ?</h2><p class="mx-auto mt-3 max-w-xl text-slate-500">Un parcours simple pour transformer chaque location en action positive.</p></div>
            <div class="grid gap-6 md:grid-cols-4">
                @foreach([['icon' => '🔎', 'title' => 'Je loue', 'text' => 'Je trouve l’équipement adapté à mon besoin.'], ['icon' => '⚡', 'title' => 'Je l’utilise', 'text' => 'Je produis ou consomme une énergie plus responsable.'], ['icon' => '📈', 'title' => 'Je déclare', 'text' => 'Je partage mon relevé d’énergie avec précision.'], ['icon' => '🏆', 'title' => 'Je progresse', 'text' => 'Je gagne des badges en faisant grandir mon impact.']] as $step)
                    <div class="rounded-3xl border border-green-100 bg-green-50 p-6 text-center"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-3xl shadow-sm">{{ $step['icon'] }}</div><h3 class="mt-5 text-lg font-bold text-green-950">{{ $step['title'] }}</h3><p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $step['text'] }}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-green-950 py-20 text-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8">
            <div><p class="text-sm font-semibold uppercase tracking-[0.2em] text-orange-300">Donner du sens aux données</p><h2 class="mt-3 text-4xl font-bold" style="font-family: Fraunces, Georgia, serif">Chaque kilo de CO2 évité raconte une histoire.</h2><p class="mt-5 leading-relaxed text-green-100">Nos équivalences rendent l’impact plus concret et plus facile à partager avec la communauté.</p></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-3xl border border-white/10 bg-white/10 p-6"><p class="text-4xl font-bold text-orange-300">{{ $formatStat($stats['co2'] / max(1, $equivalences['kg_co2_par_arbre'])) }}</p><p class="mt-2 text-green-100">arbres plantés sur un an</p></div>
                <div class="rounded-3xl border border-white/10 bg-white/10 p-6"><p class="text-4xl font-bold text-green-300">{{ $formatStat($stats['co2'] / max(1, $equivalences['kg_co2_par_km'])) }}</p><p class="mt-2 text-green-100">km en voiture évités</p></div>
            </div>
        </div>
        <p class="mx-auto mt-10 max-w-7xl px-4 text-xs text-green-300/70 sm:px-6 lg:px-8">Valeurs indicatives à remplacer par une source officielle.</p>
    </section>

    <section class="bg-green-50 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-10 flex flex-col justify-between gap-3 sm:flex-row sm:items-end"><div><p class="text-sm font-semibold uppercase tracking-[0.2em] text-green-600">La communauté récompensée</p><h2 class="mt-2 text-4xl font-bold text-green-950" style="font-family: Fraunces, Georgia, serif">La vitrine des badges</h2></div><p class="max-w-md text-sm text-slate-500">Chaque badge célèbre une manière différente de contribuer.</p></div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($badges as $badge)
                    <div class="rounded-3xl border-2 p-6 shadow-sm {{ $badge->niveau === 'or' ? 'border-yellow-300 bg-yellow-50' : ($badge->niveau === 'argent' ? 'border-slate-300 bg-white' : 'border-orange-200 bg-orange-50') }}">
                        <div class="flex items-center gap-4">@if($imageUrl($badge))<img src="{{ $imageUrl($badge) }}" alt="{{ $badge->nom }}" class="h-16 w-16 rounded-2xl object-cover">@else<div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-3xl">🏅</div>@endif<div><h3 class="font-bold text-green-950">{{ $badge->nom }}</h3><span class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $badge->niveau }}</span></div></div>
                        <p class="mt-5 text-sm leading-relaxed text-slate-600">{{ $badge->description }}</p><p class="mt-4 text-xs font-semibold text-green-700">{{ $criteria[$badge->critere] ?? $badge->critere }} · seuil {{ $badge->seuil }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Les premiers badges seront bientôt disponibles.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="bg-green-600 py-16">
        <div class="mx-auto flex max-w-5xl flex-col items-center justify-between gap-6 px-4 text-center sm:px-6 lg:flex-row lg:text-left">
            <div><h2 class="text-3xl font-bold text-white" style="font-family: Fraunces, Georgia, serif">Rejoignez la communauté</h2><p class="mt-2 text-green-100">Louez, mesurez et faites grandir votre impact avec SolarShare.</p></div>
            @auth
                <a href="{{ route('impact.index') }}" class="rounded-xl bg-white px-7 py-3.5 font-semibold text-green-700 shadow-lg transition hover:bg-green-50">Voir mon impact</a>
            @else
                <a href="{{ route('impact.index') }}" class="rounded-xl bg-white px-7 py-3.5 font-semibold text-green-700 shadow-lg transition hover:bg-green-50">Voir plus</a>
            @endauth
        </div>
    </section>
@endsection
