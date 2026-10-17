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

<!-- Filters and Search Section -->
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <form method="GET" action="{{ route('admin.deliveries.index') }}" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search Field -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                    Rechercher
                </label>
                <input 
                    type="text" 
                    name="search" 
                    id="search" 
                    placeholder="ID, client, équipement..." 
                    value="{{ $activeFilters['search'] ?? '' }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
            </div>

            <!-- Equipment Filter -->
            <div>
                <label for="equipment_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Filtrer par équipement
                </label>
                <select 
                    name="equipment_id" 
                    id="equipment_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Tous les équipements</option>
                    @foreach($equipmentList as $equipment)
                        <option 
                            value="{{ $equipment->id }}"
                            {{ ($activeFilters['equipment_id'] ?? '') == $equipment->id ? 'selected' : '' }}
                        >
                            {{ $equipment->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                    Filtrer par statut
                </label>
                <select 
                    name="status" 
                    id="status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">Tous les statuts</option>
                    @foreach($statuses as $statusKey => $statusLabel)
                        <option 
                            value="{{ $statusKey }}"
                            {{ ($activeFilters['status'] ?? '') === $statusKey ? 'selected' : '' }}
                        >
                            {{ $statusLabel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button 
                    type="submit" 
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition"
                >
                    Filtrer
                </button>
                <a 
                    href="{{ route('admin.deliveries.index') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium transition text-center"
                >
                    Réinitialiser
                </a>
            </div>
        </div>
    </form>

    <!-- Active Filters Display -->
    @if($activeFilters['search'] || $activeFilters['equipment_id'] || $activeFilters['status'])
        <div class="mt-4 pt-4 border-t">
            <p class="text-sm text-gray-600 mb-2">Filtres actifs:</p>
            <div class="flex flex-wrap gap-2">
                @if($activeFilters['search'])
                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                        Recherche: "{{ $activeFilters['search'] }}"
                    </span>
                @endif
                @if($activeFilters['equipment_id'])
                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                        Équipement: {{ $equipmentList->find($activeFilters['equipment_id'])?->name ?? 'N/A' }}
                    </span>
                @endif
                @if($activeFilters['status'])
                    <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                        Statut: {{ $statuses[$activeFilters['status']] ?? 'N/A' }}
                    </span>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Pagination Info -->
@if($deliveries->total() > 0)
    <div class="mb-4 text-sm text-gray-600">
        Affichage de {{ $deliveries->firstItem() }} à {{ $deliveries->lastItem() }} sur {{ $deliveries->total() }} livraison{{ $deliveries->total() !== 1 ? 's' : '' }}
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
        <div class="p-12 text-center text-gray-500">
            <p class="text-lg">Aucune livraison ne correspond à vos critères.</p>
            <p class="text-sm mt-2">Essayez d'ajuster vos filtres ou réinitialiser la recherche.</p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($deliveries->total() > 0)
    <div class="mt-6">
        {{ $deliveries->links() }}
    </div>
@endif
@endsection
