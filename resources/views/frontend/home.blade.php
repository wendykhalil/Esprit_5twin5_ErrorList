@extends('layouts.frontend')

@section('title', 'Accueil - SolarShare')

@section('content')

    {{-- Hero --}}
    <section class="relative min-h-[88vh] flex items-center overflow-hidden bg-green-950">
        <img
            src="https://images.unsplash.com/photo-1509391366360-2e959784a276?w=1600&h=900&fit=crop&auto=format"
            alt="Panneaux solaires dans la nature"
            class="absolute inset-0 w-full h-full object-cover opacity-40"
        />
        <div class="absolute inset-0 bg-gradient-to-r from-green-950/90 via-green-950/60 to-transparent" />
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 bg-green-500/20 border border-green-400/30 text-green-300 text-sm font-medium px-4 py-1.5 rounded-full mb-6">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                    Une communauté qui partage pour une énergie plus responsable
                </div>
                <h1 class="text-5xl sm:text-6xl font-bold text-white leading-tight mb-6" style="font-family: Fraunces, Georgia, serif">
                    L'énergie renouvelable à portée de <span class="text-green-400">tous</span>
                </h1>
                <p class="text-xl text-green-100 leading-relaxed mb-10 max-w-xl">
                    Louez et partagez facilement des équipements d'énergie renouvelable entre particuliers.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a
                        href="{{ route('equipments.index') }}"
                        class="px-8 py-4 bg-green-500 hover:bg-green-400 text-white font-semibold rounded-xl transition-colors text-center shadow-lg shadow-green-900/30"
                    >
                        Voir les équipements
                    </a>
                    <a
                        href="{{ route('equipments.create') }}"
                        class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl border border-white/20 transition-colors text-center backdrop-blur-sm"
                    >
                        Partager mon équipement
                    </a>
                </div>

                <div class="flex items-center gap-8 mt-12 pt-8 border-t border-white/10">
                    <div>
                        <p class="text-2xl font-bold text-white">500+</p>
                        <p class="text-sm text-green-300">Équipements</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white">1 200+</p>
                        <p class="text-sm text-green-300">Utilisateurs</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white">4.8★</p>
                        <p class="text-sm text-green-300">Note moyenne</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Search --}}
    <section class="bg-white shadow-sm border-b border-green-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <form method="GET" action="{{ route('equipments.index') }}" class="flex flex-col md:flex-row gap-3">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        type="text"
                        name="q"
                        placeholder="Que recherchez-vous ?"
                        class="w-full pl-10 pr-4 py-3.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent"
                    />
                </div>
                <div class="md:w-48 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                    <input
                        type="text"
                        name="location"
                        placeholder="Où ?"
                        class="w-full pl-10 pr-4 py-3.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent"
                    />
                </div>
                <select
                    name="category"
                    class="md:w-52 px-4 py-3.5 border border-gray-200 rounded-xl text-sm text-gray-600 focus:outline-none focus:ring-2 focus:ring-green-400 focus:border-transparent bg-white"
                >
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat['label'] }}">{{ $cat['label'] }}</option>
                    @endforeach
                </select>
                <button
                    type="submit"
                    class="px-8 py-3.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors flex items-center gap-2 whitespace-nowrap justify-center"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Rechercher
                </button>
            </form>
        </div>
    </section>

    {{-- Categories --}}
    <section class="py-20 bg-green-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-green-900 mb-3">Explorer par catégorie</h2>
                <p class="text-gray-500 max-w-lg mx-auto">Trouvez l'équipement qui correspond à vos besoins parmi nos différentes catégories</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach($categories as $cat)
                    <a
                        href="{{ route('equipments.index', ['category' => $cat['label']]) }}"
                        class="bg-white rounded-2xl p-6 text-center hover:shadow-md hover:-translate-y-1 transition-all duration-200 border border-green-100 group"
                    >
                        <div class="text-4xl mb-3">{{ $cat['icon'] }}</div>
                        <h3 class="text-sm font-semibold text-green-900 mb-1 group-hover:text-green-600 transition-colors">{{ $cat['label'] }}</h3>
                        <p class="text-xs text-gray-400">{{ $cat['count'] }} équipements</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Popular Equipment --}}
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-12">
                <div>
                    <h2 class="text-4xl font-bold text-green-900 mb-2">Équipements populaires</h2>
                    <p class="text-gray-500">Les équipements les plus appréciés par notre communauté</p>
                </div>
                <a href="{{ route('equipments.index') }}" class="hidden sm:flex items-center gap-2 text-green-600 font-medium hover:text-green-700 transition-colors">
                    Voir tout
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $all = \App\Data\EquipmentData::getAll();
                @endphp
                @foreach(array_slice($all, 0, 6) as $eq)
                    <x-frontend.equipment-card :equipment="$eq" />
                @endforeach
            </div>
            <div class="text-center mt-10">
                <a href="{{ route('equipments.index') }}" class="px-8 py-3.5 border-2 border-green-600 text-green-600 font-semibold rounded-xl hover:bg-green-600 hover:text-white transition-all">
                    Voir tous les équipements
                </a>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section class="py-20 bg-green-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-green-900 mb-3">Comment ça marche ?</h2>
                <p class="text-gray-500 max-w-lg mx-auto">Louer un équipement sur SolarShare est simple et rapide en 4 étapes.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $steps = [
                        ['icon' => '🔍', 'step' => '01', 'title' => 'Recherchez', 'desc' => 'Parcourez notre catalogue d\'équipements renouvelables et utilisez les filtres pour trouver ce qu\'il vous faut.'],
                        ['icon' => '📋', 'step' => '02', 'title' => 'Consultez', 'desc' => 'Vérifiez les détails, la disponibilité, les avis et les informations du propriétaire avant de réserver.'],
                        ['icon' => '📅', 'step' => '03', 'title' => 'Réservez', 'desc' => 'Sélectionnez vos dates, confirmez votre réservation et effectuez le paiement sécurisé en ligne.'],
                        ['icon' => '♻️', 'step' => '04', 'title' => 'Utilisez & retournez', 'desc' => 'Récupérez l\'équipement, utilisez-le et retournez-le en bon état à la fin de la période de location.'],
                    ];
                @endphp
                @foreach($steps as $index => $s)
                    <div class="flex flex-col">
                        <div class="bg-white rounded-2xl p-6 border border-green-100 hover:shadow-md transition-shadow">
                            <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center text-2xl mb-4">
                                {{ $s['icon'] }}
                            </div>
                            <div class="text-xs font-bold text-green-400 mb-1">ÉTAPE {{ $s['step'] }}</div>
                            <h3 class="text-lg font-bold text-green-900 mb-2" style="font-family: Fraunces, Georgia, serif">{{ $s['title'] }}</h3>
                            <p class="text-sm text-gray-500 leading-relaxed">{{ $s['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Why SolarShare --}}
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="lg:grid lg:grid-cols-2 lg:gap-16 items-center">
                <div>
                    <h2 class="text-4xl font-bold text-green-900 mb-4">Pourquoi choisir SolarShare ?</h2>
                    <p class="text-gray-500 leading-relaxed mb-10">
                        SolarShare facilite l'accès à l'énergie renouvelable pour tous les Tunisiens. Rejoignez une communauté engagée pour un avenir plus vert.
                    </p>
                    <div class="space-y-5">
                        @php
                            $features = [
                                ['icon' => '💰', 'title' => 'Économisez de l\'argent', 'desc' => 'Accédez aux équipements sans les acheter. Des tarifs jusqu\'à 80% moins chers que la location traditionnelle.'],
                                ['icon' => '🌿', 'title' => 'Accès facile', 'desc' => 'Des milliers d\'équipements près de chez vous. Trouvez et réservez en quelques clics, disponible 24h/24.'],
                                ['icon' => '🤝', 'title' => 'Partagez', 'desc' => 'Mettez votre équipement inutilisé à disposition de la communauté et générez un revenu supplémentaire.'],
                                ['icon' => '☀️', 'title' => 'Énergie renouvelable', 'desc' => 'Participez à la transition énergétique en favorisant l\'accès aux équipements solaires et éoliens.'],
                                ['icon' => '🛡️', 'title' => 'Location sécurisée', 'desc' => 'Paiements sécurisés, profils vérifiés et système d\'avis transparent pour une expérience de confiance.'],
                            ];
                        @endphp
                        @foreach($features as $f)
                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center text-xl shrink-0">
                                    {{ $f['icon'] }}
                                </div>
                                <div>
                                    <h4 class="font-semibold text-green-900 mb-1">{{ $f['title'] }}</h4>
                                    <p class="text-sm text-gray-500 leading-relaxed">{{ $f['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-12 lg:mt-0 relative">
                    <div class="rounded-3xl overflow-hidden shadow-2xl">
                        <img
                            src="https://images.unsplash.com/photo-1776131263960-56261224f009?w=700&h=600&fit=crop&auto=format"
                            alt="Utilisation de panneaux solaires portables"
                            class="w-full h-96 lg:h-[500px] object-cover bg-green-100"
                        />
                    </div>
                    <div class="absolute -bottom-6 -left-6 bg-white rounded-2xl p-4 shadow-lg border border-green-100">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-2xl">♻️</div>
                            <div>
                                <p class="text-sm font-bold text-green-900">Économie circulaire</p>
                                <p class="text-xs text-gray-500">Partagez, réutilisez, économisez</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Become owner CTA --}}
    <section class="py-20 bg-gradient-to-br from-green-700 to-green-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="text-5xl mb-6">🌞</div>
            <h2 class="text-4xl font-bold text-white mb-4">Vous avez un équipement inutilisé ?</h2>
            <p class="text-xl text-green-200 mb-10 max-w-2xl mx-auto">
                Partagez-le avec la communauté et gagnez un revenu supplémentaire tout en contribuant à la transition énergétique.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a
                    href="{{ route('equipments.create') }}"
                    class="px-8 py-4 bg-yellow-400 hover:bg-yellow-300 text-green-900 font-bold rounded-xl transition-colors shadow-lg"
                >
                    Partager mon équipement
                </a>
                <a
                    href="{{ route('how-it-works') }}"
                    class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl border border-white/20 transition-colors"
                >
                    En savoir plus
                </a>
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-green-900 mb-3">Ce que disent nos utilisateurs</h2>
                <p class="text-gray-500">Des milliers de Tunisiens font déjà confiance à SolarShare</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @php
                    $testimonials = [
                        ['name' => 'Rania Saidi', 'location' => 'Tunis', 'avatar' => 'R', 'rating' => 5, 'comment' => 'J\'ai loué un panneau solaire pendant les coupures d\'électricité cet été. Service impeccable, matériel en parfait état. Je recommande vivement SolarShare !'],
                        ['name' => 'Omar Belhaj', 'location' => 'Sousse', 'avatar' => 'O', 'rating' => 5, 'comment' => 'Grâce à SolarShare, j\'ai pu alimenter mon stand lors d\'un événement en plein air sans générer de bruit ni de pollution. Excellent concept !'],
                        ['name' => 'Nour Mansouri', 'location' => 'Sfax', 'avatar' => 'N', 'rating' => 4, 'comment' => 'Plateforme simple et intuitive. J\'ai trouvé une station électrique en quelques minutes. Les prix sont très compétitifs par rapport aux options traditionnelles.'],
                    ];
                @endphp
                @foreach($testimonials as $t)
                    <div class="bg-green-50 rounded-2xl p-6 border border-green-100">
                        <div class="flex gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $t['rating'] ? 'text-yellow-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                        <p class="text-gray-600 leading-relaxed my-4 italic">"{{ $t['comment'] }}"</p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center text-white font-bold">
                                {{ $t['avatar'] }}
                            </div>
                            <div>
                                <p class="font-semibold text-green-900 text-sm">{{ $t['name'] }}</p>
                                <p class="text-xs text-gray-400">{{ $t['location'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="py-20 bg-green-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold text-green-900 mb-4">Commencez à partager l'énergie autrement</h2>
            <p class="text-gray-500 mb-10 max-w-xl mx-auto">Rejoignez la communauté SolarShare et accédez à des équipements d'énergie renouvelable près de chez vous.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('equipments.index') }}" class="px-8 py-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition-colors shadow-md">
                    Explorer les équipements
                </a>
                <a href="{{ route('register') }}" class="px-8 py-4 border-2 border-green-600 text-green-600 font-semibold rounded-xl hover:bg-green-600 hover:text-white transition-all">
                    Créer un compte
                </a>
            </div>
        </div>
    </section>

@endsection