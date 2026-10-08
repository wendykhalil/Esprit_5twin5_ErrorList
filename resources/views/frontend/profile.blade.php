@extends('layouts.frontend')

@section('title', 'Mon profil - SolarShare')

@section('content')

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-4xl font-bold text-green-900 mb-8" style="font-family: Fraunces, Georgia, serif">Mon profil</h1>

            <div class="grid lg:grid-cols-3 gap-8">
                {{-- Sidebar --}}
                <aside class="lg:col-span-1">
                    <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-6 sticky top-24 z-30">
                        <div class="text-center mb-6">
                            <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center text-white text-4xl font-bold mx-auto mb-4">
                                U
                            </div>
                            <h2 class="text-xl font-bold text-green-900">Utilisateur</h2>
                            <p class="text-sm text-gray-500">Member depuis 2024</p>
                        </div>
                        <button class="w-full py-2.5 px-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors mb-3">
                            Modifier le profil
                        </button>
                        <button class="w-full py-2.5 px-4 border border-green-200 text-green-600 hover:bg-green-50 font-semibold rounded-lg transition-colors">
                            Déconnexion
                        </button>
                    </div>
                </aside>

                {{-- Main content --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Profile Info --}}
                    <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-6">
                        <h3 class="text-lg font-bold text-green-900 mb-4">Informations personnelles</h3>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Prénom</label>
                                <div class="px-4 py-3 bg-gray-50 rounded-lg text-gray-700">Utilisateur</div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Nom</label>
                                <div class="px-4 py-3 bg-gray-50 rounded-lg text-gray-700">SolarShare</div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                                <div class="px-4 py-3 bg-gray-50 rounded-lg text-gray-700">utilisateur@solarshare.tn</div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Téléphone</label>
                                <div class="px-4 py-3 bg-gray-50 rounded-lg text-gray-700">+216 71 123 456</div>
                            </div>
                        </div>
                    </div>

                    {{-- Statistics --}}
                    <div class="grid sm:grid-cols-3 gap-4">
                        @php
                            $stats = [
                                ['label' => 'Équipements loués', 'value' => '5'],
                                ['label' => 'Locations complétées', 'value' => '12'],
                                ['label' => 'Note moyenne', 'value' => '4.8★'],
                            ];
                        @endphp
                        @foreach($stats as $stat)
                            <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-6 text-center">
                                <p class="text-3xl font-bold text-green-700 mb-2">{{ $stat['value'] }}</p>
                                <p class="text-sm text-gray-600">{{ $stat['label'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Recent Rentals --}}
                    <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-6">
                        <h3 class="text-lg font-bold text-green-900 mb-4">Locations récentes</h3>
                        <div class="space-y-3">
                            @php
                                $rentals = [
                                    ['name' => 'Panneau solaire portable 200W', 'date' => '15-20 Sept 2024', 'status' => 'Complétée'],
                                    ['name' => 'Batterie solaire 1000Wh', 'date' => '10-12 Sept 2024', 'status' => 'Complétée'],
                                    ['name' => 'Station électrique portable', 'date' => '01-08 Sept 2024', 'status' => 'Complétée'],
                                ];
                            @endphp
                            @foreach($rentals as $rental)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $rental['name'] }}</p>
                                        <p class="text-sm text-gray-500">{{ $rental['date'] }}</p>
                                    </div>
                                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                        {{ $rental['status'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
