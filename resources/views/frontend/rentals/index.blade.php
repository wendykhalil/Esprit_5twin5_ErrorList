@extends('layouts.frontend')

@section('title', 'Mes réservations - SolarShare')

@section('content')

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <h1 class="text-4xl font-bold text-green-900 mb-8" style="font-family: Fraunces, Georgia, serif">Mes réservations</h1>

            <div class="flex gap-4 mb-8">
                @php
                    $tabs = [['name' => 'Actives', 'count' => 2], ['name' => 'Complétées', 'count' => 8], ['name' => 'Annulées', 'count' => 1]];
                @endphp
                @foreach($tabs as $tab)
                    <button class="px-6 py-3 font-semibold rounded-lg border {{ $loop->first ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50' }}">
                        {{ $tab['name'] }} <span class="ml-2 text-xs">{{ $tab['count'] }}</span>
                    </button>
                @endforeach
            </div>

            <div class="space-y-4">
                @php
                    $rentals = [
                        ['name' => 'Mini éolienne portable 400W', 'owner' => 'Lina T.', 'from' => '25 Sept', 'to' => '30 Sept', 'price' => '175 TND', 'status' => 'En cours'],
                        ['name' => 'Batterie solaire 1000Wh', 'owner' => 'Sonia K.', 'from' => '20 Sept', 'to' => '22 Sept', 'price' => '80 TND', 'status' => 'En cours'],
                        ['name' => 'Panneau solaire portable 200W', 'owner' => 'Ahmed B.', 'from' => '10 Sept', 'to' => '15 Sept', 'price' => '125 TND', 'status' => 'Complétée'],
                        ['name' => 'Station électrique portable 2000W', 'owner' => 'Karim M.', 'from' => '01 Sept', 'to' => '08 Sept', 'price' => '350 TND', 'status' => 'Complétée'],
                        ['name' => 'Panneau solaire 150W + régulateur', 'owner' => 'Mehdi R.', 'from' => '15 Août', 'to' => '18 Août', 'price' => '60 TND', 'status' => 'Complétée'],
                    ];
                @endphp
                @foreach($rentals as $rental)
                    <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-6 hover:shadow-md transition-shadow">
                        <div class="grid md:grid-cols-2 gap-6 items-center">
                            <div>
                                <h3 class="text-xl font-bold text-green-900 mb-2">{{ $rental['name'] }}</h3>
                                <p class="text-sm text-gray-600 mb-3">Propriétaire: <span class="font-semibold">{{ $rental['owner'] }}</span></p>
                                <div class="flex items-center gap-4 text-sm">
                                    <span class="text-gray-600">📅 {{ $rental['from'] }} - {{ $rental['to'] }}</span>
                                    <span class="font-bold text-green-700">{{ $rental['price'] }}</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between md:justify-end gap-4">
                                <span class="px-4 py-2 rounded-lg text-sm font-semibold {{ $rental['status'] === 'En cours' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $rental['status'] }}
                                </span>
                                <button class="px-6 py-2.5 border border-green-200 text-green-600 font-semibold rounded-lg hover:bg-green-50 transition-colors">
                                    Détails
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

@endsection
