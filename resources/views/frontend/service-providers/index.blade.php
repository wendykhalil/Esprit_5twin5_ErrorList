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
                    @if(auth()->user()->serviceProvider)
                        <a href="{{ route('service-providers.edit', auth()->user()->serviceProvider) }}"
                           class="inline-flex items-center justify-center px-5 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition">
                            Gérer mon profil
                        </a>
                    @else
                        <a href="{{ route('service-providers.create') }}"
                           class="inline-flex items-center justify-center px-5 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition">
                            Proposer mes services
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center justify-center px-5 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition">
                        Proposer mes services
                    </a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Flash Messages --}}
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

        <div class="flex flex-col md:flex-row gap-8">
            
            {{-- Filters --}}
            <div class="w-full md:w-64 flex-shrink-0">
                <form method="GET" action="{{ route('service-providers.index') }}" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h2 class="font-bold text-gray-900 mb-4">Filtres</h2>

                    <div class="mb-4">
                        <label for="specialty" class="block text-sm font-medium text-gray-700 mb-1">Spécialité</label>
                        <input type="text" name="specialty" id="specialty" value="{{ request('specialty') }}"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500">
                    </div>

                    <div class="mb-4">
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Localisation</label>
                        <input type="text" name="location" id="location" value="{{ request('location') }}"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-green-500">
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="availability" value="1" {{ request('availability') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="ml-2 text-sm text-gray-700">Disponible uniquement</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full py-2 bg-gray-900 hover:bg-gray-800 text-white font-medium rounded-lg transition">
                        Filtrer
                    </button>
                    
                    @if(request()->anyFilled(['specialty', 'location', 'availability']))
                        <a href="{{ route('service-providers.index') }}" class="block text-center mt-3 text-sm text-green-600 hover:text-green-700">
                            Réinitialiser
                        </a>
                    @endif
                </form>
            </div>

            {{-- List --}}
            <div class="flex-1">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @forelse($serviceProviders as $provider)
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $provider->user->name }}</h3>
                                    <p class="text-green-600 font-medium">{{ $provider->specialty }}</p>
                                </div>
                                @if($provider->availability)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Disponible
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Indisponible
                                    </span>
                                @endif
                            </div>

                            <div class="space-y-2 mb-6 flex-1">
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium text-gray-900">📍 Localisation :</span> {{ $provider->location }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium text-gray-900">⏳ Expérience :</span> {{ $provider->experience_years }} ans
                                </p>
                                <p class="text-sm text-gray-600">
                                    <span class="font-medium text-gray-900">💰 Tarif :</span> {{ number_format($provider->hourly_rate, 2) }} TND/h
                                </p>
                            </div>

                            <a href="{{ route('service-providers.show', $provider) }}"
                               class="w-full text-center py-2.5 border border-green-600 text-green-600 hover:bg-green-50 font-medium rounded-lg transition">
                                Voir le profil
                            </a>
                        </div>
                    @empty
                        <div class="col-span-full bg-white p-10 text-center rounded-2xl shadow-sm border border-gray-100">
                            <p class="text-gray-500 mb-4">Aucun professionnel ne correspond à vos critères.</p>
                            <a href="{{ route('service-providers.index') }}" class="text-green-600 font-medium hover:underline">
                                Voir tous les professionnels
                            </a>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8">
                    {{ $serviceProviders->links() }}
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
