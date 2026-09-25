@extends('layouts.frontend')

@section('title', 'À propos - SolarShare')

@section('content')

    {{-- Hero --}}
    <div class="relative h-72 bg-green-900 overflow-hidden">
        <img
            src="https://images.unsplash.com/photo-1509391366360-2e959784a276?w=1400&h=400&fit=crop&auto=format"
            alt=""
            class="absolute inset-0 w-full h-full object-cover opacity-30"
        />
        <div class="relative flex items-center justify-center h-full text-center px-4">
            <div>
                <h1 class="text-4xl sm:text-5xl font-bold text-white mb-3" style="font-family: Fraunces, Georgia, serif">
                    À propos de SolarShare
                </h1>
                <p class="text-green-200 text-lg max-w-2xl">
                    Une mission, une communauté, un avenir plus vert pour la Tunisie.
                </p>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        {{-- Mission --}}
        <div class="grid lg:grid-cols-2 gap-12 items-center mb-20">
            <div>
                <span class="text-xs font-bold text-green-500 uppercase tracking-widest">Notre mission</span>
                <h2 class="text-3xl font-bold text-green-900 mt-2 mb-4" style="font-family: Fraunces, Georgia, serif">
                    Rendre l'énergie renouvelable accessible à tous
                </h2>
                <p class="text-gray-600 leading-relaxed mb-4">
                    SolarShare est née d'un constat simple : les équipements d'énergie renouvelable sont souvent trop coûteux pour être achetés individuellement, mais ils restent inutilisés une grande partie du temps chez ceux qui les possèdent.
                </p>
                <p class="text-gray-600 leading-relaxed">
                    Notre plateforme crée un pont entre ceux qui ont des équipements inutilisés et ceux qui en ont besoin, tout en favorisant la transition énergétique en Tunisie de manière collaborative et économique.
                </p>
            </div>
            <div class="rounded-2xl overflow-hidden shadow-xl">
                <img
                    src="https://images.unsplash.com/photo-1776131263960-56261224f009?w=700&h=500&fit=crop&auto=format"
                    alt="Énergie renouvelable"
                    class="w-full h-72 object-cover bg-green-100"
                />
            </div>
        </div>

        {{-- Values --}}
        <div class="mb-20">
            <h2 class="text-3xl font-bold text-green-900 text-center mb-10" style="font-family: Fraunces, Georgia, serif">
                Nos valeurs
            </h2>
            <div class="grid sm:grid-cols-3 gap-6">
                @php
                    $values = [
                        ['icon' => '🤝', 'title' => 'Communauté', 'desc' => 'Nous croyons au pouvoir du partage entre particuliers pour créer une économie plus solidaire et durable.'],
                        ['icon' => '🌿', 'title' => 'Durabilité', 'desc' => 'Chaque location évite un achat neuf et contribue à réduire l\'empreinte carbone de notre communauté.'],
                        ['icon' => '🛡️', 'title' => 'Confiance', 'desc' => 'Profils vérifiés, avis transparents et paiements sécurisés pour une expérience sereine pour tous.'],
                    ];
                @endphp
                @foreach($values as $v)
                    <div class="bg-green-50 rounded-2xl p-6 border border-green-100">
                        <div class="text-4xl mb-3">{{ $v['icon'] }}</div>
                        <h3 class="font-bold text-green-900 mb-2" style="font-family: Fraunces, Georgia, serif">{{ $v['title'] }}</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $v['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Stats --}}
        <div class="bg-gradient-to-br from-green-700 to-green-900 rounded-3xl p-10 text-white text-center mb-20">
            <h2 class="text-3xl font-bold mb-8" style="font-family: Fraunces, Georgia, serif">SolarShare en chiffres</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                @php
                    $stats = [
                        ['val' => '1 200+', 'label' => 'Utilisateurs actifs'],
                        ['val' => '500+', 'label' => 'Équipements'],
                        ['val' => '3 500+', 'label' => 'Locations réalisées'],
                        ['val' => '12+', 'label' => 'Villes couvertes'],
                    ];
                @endphp
                @foreach($stats as $stat)
                    <div>
                        <p class="text-4xl font-bold text-yellow-300 mb-1">{{ $stat['val'] }}</p>
                        <p class="text-sm text-green-200">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Team --}}
        <div class="mb-20">
            <h2 class="text-3xl font-bold text-green-900 text-center mb-10" style="font-family: Fraunces, Georgia, serif">Notre équipe</h2>
            <div class="grid sm:grid-cols-3 gap-6">
                @php
                    $team = [
                        ['name' => 'Sami Bouazizi', 'role' => 'Co-fondateur & CEO', 'initial' => 'S', 'location' => 'Tunis'],
                        ['name' => 'Ines Khemiri', 'role' => 'Co-fondatrice & CTO', 'initial' => 'I', 'location' => 'Ariana'],
                        ['name' => 'Youssef Gargouri', 'role' => 'Responsable opérations', 'initial' => 'Y', 'location' => 'Sousse'],
                    ];
                @endphp
                @foreach($team as $member)
                    <div class="bg-white rounded-2xl border border-green-100 p-6 text-center shadow-sm hover:shadow-md transition-shadow">
                        <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4">
                            {{ $member['initial'] }}
                        </div>
                        <h3 class="font-bold text-green-900 mb-1">{{ $member['name'] }}</h3>
                        <p class="text-sm text-green-600 mb-1">{{ $member['role'] }}</p>
                        <p class="text-xs text-gray-400">📍 {{ $member['location'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- CTA --}}
        <div class="text-center">
            <h2 class="text-3xl font-bold text-green-900 mb-4" style="font-family: Fraunces, Georgia, serif">
                Rejoignez notre communauté
            </h2>
            <p class="text-gray-500 mb-8 max-w-xl mx-auto">
                Ensemble, construisons une Tunisie plus verte et plus solidaire grâce au partage des équipements d'énergie renouvelable.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="px-8 py-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors shadow-md">
                    Créer un compte gratuit
                </a>
                <a href="{{ route('contact') }}" class="px-8 py-4 border-2 border-green-600 text-green-600 font-semibold rounded-xl hover:bg-green-50 transition-all">
                    Nous contacter
                </a>
            </div>
        </div>
    </div>

@endsection
