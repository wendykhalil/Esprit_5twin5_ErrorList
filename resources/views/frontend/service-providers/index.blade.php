@extends('layouts.frontend')

@section('title', 'Trouver un professionnel - SolarShare')

@section('content')
<div class="min-h-screen bg-gray-50">

    {{-- Header --}}
    <div class="bg-white border-b border-green-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-green-900 mb-2" style="font-family: Fraunces, Georgia, serif">
                        Trouver un professionnel
                    </h1>
                    <p class="text-gray-500">
                        {{ $serviceProviders->total() }}
                        {{ $serviceProviders->total() > 1 ? 'professionnels trouvés' : 'professionnel trouvé' }}
                    </p>
                </div>

                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.service-providers.index') }}"
                           class="inline-flex items-center justify-center px-5 py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl transition shrink-0">
                            Gérer les prestataires
                        </a>
                    @elseif(auth()->user()->isProvider() && auth()->user()->serviceProvider)
                        <a href="{{ route('service-providers.edit', auth()->user()->serviceProvider) }}"
                           class="inline-flex items-center justify-center px-5 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition shrink-0">
                            Gérer mon profil prestataire
                        </a>
                    @elseif(auth()->user()->isClient())
                        <a href="{{ route('service-providers.create') }}"
                           class="inline-flex items-center justify-center px-5 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition shrink-0">
                            Proposer mes services
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center justify-center px-5 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition shrink-0">
                        Proposer mes services
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-xl">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl">
                {{ session('error') }}
            </div>
        @endif
        @if(session('info'))
            <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-5 py-4 rounded-xl">
                {{ session('info') }}
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-8 items-start">

            {{-- Filtres (colonne fixe à gauche sur grand écran) --}}
            <aside class="w-full lg:w-64 shrink-0 lg:sticky lg:top-24 lg:z-30">
                <form
                    method="GET"
                    action="{{ route('service-providers.index') }}"
                    class="bg-white p-6 rounded-2xl shadow-sm border border-green-100 space-y-4"
                >
                    <h2 class="font-bold text-green-900 text-lg">Filtres</h2>

                    <div>
                        <label for="specialty" class="block text-sm font-semibold text-gray-700 mb-2">Spécialité</label>
                        <input
                            type="text"
                            name="specialty"
                            id="specialty"
                            value="{{ request('specialty') }}"
                            placeholder="Ex : installation, maintenance…"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
                        >
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-semibold text-gray-700 mb-2">Localisation</label>
                        <input
                            type="text"
                            name="location"
                            id="location"
                            value="{{ request('location') }}"
                            placeholder="Ex : Tunis, Sousse…"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
                        >
                    </div>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            name="availability"
                            value="1"
                            {{ request('availability') ? 'checked' : '' }}
                            class="w-4 h-4 accent-green-600 rounded border-gray-300"
                        >
                        <span class="text-sm font-medium text-gray-700">Disponible uniquement</span>
                    </label>

                    <button
                        type="submit"
                        class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition text-sm"
                    >
                        Filtrer
                    </button>

                    @if(request()->anyFilled(['specialty', 'location', 'availability']))
                        <a
                            href="{{ route('service-providers.index') }}"
                            class="block text-center text-sm text-green-600 hover:text-green-700 font-medium"
                        >
                            Réinitialiser les filtres
                        </a>
                    @endif
                </form>
            </aside>

            {{-- Liste des prestataires --}}
            <div class="flex-1 min-w-0 w-full">
                @if($serviceProviders->count() === 0)
                    <div class="bg-white border border-green-100 rounded-2xl text-center py-16 px-6">
                        <div class="text-5xl mb-4" aria-hidden="true">🔍</div>
                        <h3 class="text-xl font-bold text-green-900 mb-2">Aucun professionnel trouvé</h3>
                        <p class="text-gray-500 mb-6">Aucun prestataire ne correspond à vos critères pour le moment.</p>
                        <a href="{{ route('service-providers.index') }}" class="inline-block px-5 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition">
                            Voir tous les professionnels
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($serviceProviders as $provider)
                            <article class="bg-white p-6 rounded-2xl shadow-sm border border-green-100 flex flex-col h-full">
                                <div class="flex items-start justify-between gap-3 mb-4">
                                    <div class="min-w-0">
                                        <h3 class="text-lg font-bold text-gray-900 truncate">{{ $provider->user->name }}</h3>
                                        <p class="text-green-600 font-medium mt-0.5">{{ $provider->specialty }}</p>
                                    </div>
                                    @if($provider->availability)
                                        <span class="shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Disponible
                                        </span>
                                    @else
                                        <span class="shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                            Indisponible
                                        </span>
                                    @endif
                                </div>

                                @if($provider->description)
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-3 flex-1">
                                        {{ $provider->description }}
                                    </p>
                                @endif

                                <ul class="space-y-2 mb-6 text-sm text-gray-600">
                                    <li><span class="font-medium text-gray-800">Localisation :</span> {{ $provider->location }}</li>
                                    <li><span class="font-medium text-gray-800">Expérience :</span> {{ $provider->experience_years }} ans</li>
                                    <li><span class="font-medium text-gray-800">Tarif :</span> {{ number_format($provider->hourly_rate, 2) }} TND/h</li>
                                </ul>

                                <a
                                    href="{{ route('service-providers.show', $provider) }}"
                                    class="mt-auto w-full text-center py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition text-sm"
                                >
                                    Voir le profil
                                </a>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $serviceProviders->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
