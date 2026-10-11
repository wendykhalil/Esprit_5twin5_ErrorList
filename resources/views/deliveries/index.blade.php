@extends('layouts.frontend')

@section('title', 'Mes Livraisons')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Mes Livraisons</h1>
        <p class="text-gray-600 mb-6">Suivi du statut de vos équipements livrés</p>

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
                {{ session('error') }}
            </div>
        @endif

        @forelse($deliveries as $delivery)
            <div class="mb-4 bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition">
                <a href="{{ route('deliveries.show', $delivery) }}" class="block p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">
                                {{ $delivery->equipment->name }}
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">
                                Réservation {{ $delivery->reservation->reference ?? '#' . $delivery->reservation->id }}
                            </p>
                        </div>
                        
                        @php
                            $statusClasses = match($delivery->status) {
                                'a_preparer' => 'bg-slate-100 text-slate-800',
                                'prete' => 'bg-blue-100 text-blue-800',
                                'remise_au_client' => 'bg-green-100 text-green-800',
                                'retour_recu' => 'bg-amber-100 text-amber-800',
                                'terminee' => 'bg-gray-100 text-gray-800',
                                default => 'bg-gray-100 text-gray-800',
                            };
                        @endphp
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses }}">
                            {{ $statuses[$delivery->status] ?? ucfirst($delivery->status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                        <div>
                            <p class="text-gray-600 font-medium">Livraison prévue</p>
                            <p class="text-gray-900 font-semibold">{{ $delivery->planned_delivery_date->format('d/m/Y') }}</p>
                            @if($delivery->actual_delivery_date)
                                <p class="text-xs text-green-700 mt-1">
                                    ✓ Livrée le {{ $delivery->actual_delivery_date->format('d/m/Y H:i') }}
                                </p>
                            @endif
                        </div>
                        <div>
                            <p class="text-gray-600 font-medium">Retour prévu</p>
                            <p class="text-gray-900 font-semibold">{{ $delivery->planned_return_date->format('d/m/Y') }}</p>
                            @if($delivery->actual_return_date)
                                <p class="text-xs text-green-700 mt-1">
                                    ✓ Retournée le {{ $delivery->actual_return_date->format('d/m/Y H:i') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Progress bar -->
                    @php
                        $progressSteps = [
                            'a_preparer' => 0,
                            'prete' => 25,
                            'remise_au_client' => 50,
                            'retour_recu' => 75,
                            'terminee' => 100,
                        ];
                        $progress = $progressSteps[$delivery->status] ?? 0;
                    @endphp
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                    </div>
                </a>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                <p class="text-gray-600 mb-4">Vous n'avez pas encore de livraisons.</p>
                <p class="text-sm text-gray-500">
                    Les livraisons apparaîtront ici une fois que l'administrateur aura confirmé une réservation.
                </p>
            </div>
        @endforelse

        @if($deliveries->hasPages())
            <div class="mt-6">
                {{ $deliveries->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
