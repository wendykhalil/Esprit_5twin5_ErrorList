@extends('layouts.backend')

@section('title', 'Gestion des Livraisons - SolarShare Admin')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Livraisons</h1>
</div>

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

<div class="bg-white rounded-lg shadow-sm">
    @forelse($deliveries as $delivery)
        <div class="border-b last:border-b-0 p-5 hover:bg-gray-50 transition">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <a href="{{ route('admin.deliveries.show', $delivery) }}" class="text-lg font-semibold text-blue-600 hover:text-blue-800">
                        Livraison #{{ $delivery->id }} - {{ $delivery->reservation->reference ?? 'N/A' }}
                    </a>
                    
                    <div class="mt-2 text-sm text-gray-600 grid grid-cols-2 gap-4">
                        <div>
                            <span class="font-medium">Client:</span> {{ $delivery->user->name }}
                        </div>
                        <div>
                            <span class="font-medium">Équipement:</span> {{ $delivery->equipment->name }}
                        </div>
                        <div>
                            <span class="font-medium">Livraison prévue:</span> {{ $delivery->planned_delivery_date->format('d/m/Y') }}
                        </div>
                        <div>
                            <span class="font-medium">Retour prévu:</span> {{ $delivery->planned_return_date->format('d/m/Y') }}
                        </div>
                    </div>

                    <div class="mt-3">
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
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses }}">
                            {{ $statuses[$delivery->status] ?? ucfirst($delivery->status) }}
                        </span>

                        @if($delivery->actual_delivery_date)
                            <span class="inline-block ml-2 text-xs text-green-700">
                                ✓ Livrée le {{ $delivery->actual_delivery_date->format('d/m/Y H:i') }}
                            </span>
                        @endif

                        @if($delivery->actual_return_date)
                            <span class="inline-block ml-2 text-xs text-blue-700">
                                ✓ Retournée le {{ $delivery->actual_return_date->format('d/m/Y H:i') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="ml-4">
                    <a href="{{ route('admin.deliveries.show', $delivery) }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        Détails
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="p-8 text-center text-gray-500">
            <p>Aucune livraison trouvée.</p>
        </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $deliveries->links() }}
</div>
@endsection
