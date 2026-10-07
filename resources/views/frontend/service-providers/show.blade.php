@extends('layouts.frontend')

@section('title', $serviceProvider->user->name . ' - SolarShare')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Back --}}
        <a href="{{ route('service-providers.index') }}" class="inline-flex items-center gap-2 text-green-600 hover:text-green-700 mb-8">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Retour aux professionnels
        </a>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-10">

            {{-- MAIN CONTENT --}}
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $serviceProvider->user->name }}</h1>
                        <p class="text-xl text-green-600 font-medium">{{ $serviceProvider->specialty }}</p>
                    </div>

                    <div class="prose max-w-none text-gray-600">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">À propos</h3>
                        <p class="whitespace-pre-line">{{ $serviceProvider->description }}</p>
                    </div>
                </div>
            </div>

            {{-- SIDEBAR --}}
            <div class="space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    
                    <div class="mb-6">
                        <p class="text-sm text-gray-500 mb-1">Tarif horaire</p>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($serviceProvider->hourly_rate, 2) }} <span class="text-lg font-normal text-gray-500">TND/h</span></p>
                    </div>

                    <div class="space-y-4 mb-8 pb-8 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <span class="text-gray-400">📍</span>
                            <span class="text-gray-700">{{ $serviceProvider->location }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-gray-400">⏳</span>
                            <span class="text-gray-700">{{ $serviceProvider->experience_years }} ans d'expérience</span>
                        </div>
                        @if($serviceProvider->phone)
                        <div class="flex items-center gap-3">
                            <span class="text-gray-400">📞</span>
                            <span class="text-gray-700">{{ $serviceProvider->phone }}</span>
                        </div>
                        @endif
                        <div class="flex items-center gap-3">
                            <span class="text-gray-400">ℹ️</span>
                            @if($serviceProvider->availability)
                                <span class="text-green-600 font-medium">Disponible actuellement</span>
                            @else
                                <span class="text-red-600 font-medium">Indisponible actuellement</span>
                            @endif
                        </div>
                    </div>

                    @auth
                        @if(auth()->id() === $serviceProvider->user_id)
                            <a href="{{ route('service-providers.edit', $serviceProvider) }}" 
                               class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition">
                                Modifier mon profil
                            </a>
                        @elseif($serviceProvider->availability)
                            <a href="{{ route('service-requests.create', $serviceProvider) }}" 
                               class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition">
                                Demander une intervention
                            </a>
                        @else
                            <button type="button" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition opacity-50 cursor-not-allowed">
                                Prestataire indisponible
                            </button>
                        @endif
                    @else
                        <a href="{{ route('login') }}" 
                           class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 transition">
                            Connectez-vous pour contacter
                        </a>
                    @endauth

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
