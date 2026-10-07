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
                        ['name' => 'Mini éolienne portable 400W', 'owner' => 'Lina T.', 'from' => '25 Sept', 'to' => '30 Sept', 'price' => 175, 'status' => 'En cours', 'paid' => false, 'id' => 1],
                        ['name' => 'Batterie solaire 1000Wh', 'owner' => 'Sonia K.', 'from' => '20 Sept', 'to' => '22 Sept', 'price' => 80, 'status' => 'En cours', 'paid' => false, 'id' => 2],
                        ['name' => 'Panneau solaire portable 200W', 'owner' => 'Ahmed B.', 'from' => '10 Sept', 'to' => '15 Sept', 'price' => 125, 'status' => 'Complétée', 'paid' => true, 'id' => 3],
                        ['name' => 'Station électrique portable 2000W', 'owner' => 'Karim M.', 'from' => '01 Sept', 'to' => '08 Sept', 'price' => 350, 'status' => 'Complétée', 'paid' => true, 'id' => 4],
                        ['name' => 'Panneau solaire 150W + régulateur', 'owner' => 'Mehdi R.', 'from' => '15 Août', 'to' => '18 Août', 'price' => 60, 'status' => 'Complétée', 'paid' => true, 'id' => 5],
                    ];
                @endphp
                @foreach($rentals as $rental)
                    <div class="bg-white rounded-2xl border border-green-100 shadow-sm p-6 hover:shadow-md transition-shadow">
                        <div class="grid md:grid-cols-2 gap-6 items-center">
                            <div>
                                <h3 class="text-xl font-bold text-green-900 mb-2">{{ $rental['name'] }}</h3>
                                <p class="text-sm text-gray-600 mb-3">Propriétaire: <span class="font-semibold">{{ $rental['owner'] }}</span></p>
                                <div class="flex items-center gap-4 text-sm mb-3">
                                    <span class="text-gray-600">📅 {{ $rental['from'] }} - {{ $rental['to'] }}</span>
                                    <span class="font-bold text-green-700">{{ number_format($rental['price'], 0) }} TND</span>
                                </div>
                                
                                {{-- Payment Status Badge --}}
                                @if (!$rental['paid'])
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 border border-red-200 rounded-full text-xs font-semibold text-red-700">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 0 1 5.11 2.522a6 6 0 0 1 8.367 8.368z" clip-rule="evenodd" />
                                        </svg>
                                        Paiement en attente
                                    </div>
                                @else
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 border border-green-200 rounded-full text-xs font-semibold text-green-700">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Payé
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between sm:justify-end gap-3">
                                <span class="px-4 py-2 rounded-lg text-sm font-semibold text-center {{ $rental['status'] === 'En cours' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                                    {{ $rental['status'] }}
                                </span>
                                <div class="flex gap-2">
                                    @if (!$rental['paid'] && $rental['status'] === 'En cours')
                                        <a 
                                            href="{{ route('payments.create', ['amount' => $rental['price'], 'equipment_id' => $rental['id'], 'description' => 'Location de ' . $rental['name']]) }}"
                                            class="px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors text-sm"
                                        >
                                            Payer maintenant
                                        </a>
                                    @else
                                        <button class="px-6 py-2.5 border border-green-200 text-green-600 font-semibold rounded-lg hover:bg-green-50 transition-colors">
                                            Détails
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

@endsection
