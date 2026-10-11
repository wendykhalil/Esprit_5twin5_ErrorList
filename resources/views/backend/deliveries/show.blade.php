@extends('layouts.backend')

@section('title', 'Livraison #' . $delivery->id . ' - SolarShare Admin')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.deliveries.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
        ← Retour aux livraisons
    </a>
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

<div class="grid grid-cols-3 gap-6">
    <!-- Main content -->
    <div class="col-span-2">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Livraison #{{ $delivery->id }}</h1>
                    <p class="text-gray-600 mt-1">Réservation {{ $delivery->reservation->reference ?? 'N/A' }}</p>
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
                <span class="px-4 py-2 rounded-full text-sm font-semibold {{ $statusClasses }}">
                    {{ $statuses[$delivery->status] ?? ucfirst($delivery->status) }}
                </span>
            </div>

            <!-- Delivery Information -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Informations de livraison</h2>
                
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between py-3 border-b">
                        <dt class="font-medium text-gray-700">Client:</dt>
                        <dd class="text-gray-900">{{ $delivery->user->name }}</dd>
                    </div>
                    <div class="flex justify-between py-3 border-b">
                        <dt class="font-medium text-gray-700">Email:</dt>
                        <dd class="text-gray-900">{{ $delivery->user->email }}</dd>
                    </div>
                    <div class="flex justify-between py-3 border-b">
                        <dt class="font-medium text-gray-700">Téléphone:</dt>
                        <dd class="text-gray-900">{{ $delivery->user->phone ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Equipment Information -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Équipement</h2>
                
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between py-3 border-b">
                        <dt class="font-medium text-gray-700">Nom:</dt>
                        <dd class="text-gray-900">{{ $delivery->equipment->name }}</dd>
                    </div>
                    <div class="flex justify-between py-3 border-b">
                        <dt class="font-medium text-gray-700">Catégorie:</dt>
                        <dd class="text-gray-900">{{ $delivery->equipment->category?->name ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between py-3 border-b">
                        <dt class="font-medium text-gray-700">Propriétaire:</dt>
                        <dd class="text-gray-900">{{ $delivery->equipment->user->name }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Dates -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Dates</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600 font-medium mb-1">Livraison prévue</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $delivery->planned_delivery_date->format('d/m/Y') }}</p>
                        @if($delivery->actual_delivery_date)
                            <p class="text-sm text-green-700 mt-2">✓ Livrée le {{ $delivery->actual_delivery_date->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600 font-medium mb-1">Retour prévu</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $delivery->planned_return_date->format('d/m/Y') }}</p>
                        @if($delivery->actual_return_date)
                            <p class="text-sm text-green-700 mt-2">✓ Retournée le {{ $delivery->actual_return_date->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($delivery->notes || $delivery->delivery_notes || $delivery->return_notes)
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Notes</h2>
                    
                    @if($delivery->notes)
                        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-900 font-medium mb-1">Notes générales</p>
                            <p class="text-blue-800">{{ $delivery->notes }}</p>
                        </div>
                    @endif

                    @if($delivery->delivery_notes)
                        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                            <p class="text-sm text-green-900 font-medium mb-1">Notes de livraison</p>
                            <p class="text-green-800">{{ $delivery->delivery_notes }}</p>
                        </div>
                    @endif

                    @if($delivery->return_notes)
                        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                            <p class="text-sm text-amber-900 font-medium mb-1">Notes de retour</p>
                            <p class="text-amber-800">{{ $delivery->return_notes }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Sidebar: Status Update -->
    <div class="col-span-1">
        <div class="bg-white rounded-lg shadow-sm p-6 sticky top-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Mettre à jour le statut</h3>

            @if(count($validTransitions) > 0)
                <form action="{{ route('admin.deliveries.updateStatus', $delivery) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Nouveau statut
                        </label>
                        <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($validTransitions as $transition)
                                <option value="{{ $transition }}">
                                    {{ $statuses[$transition] ?? ucfirst($transition) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Notes (optionnel)
                        </label>
                        <textarea name="notes" id="notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ajouter des notes pour ce changement de statut..."></textarea>
                    </div>

                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition">
                        Mettre à jour
                    </button>
                </form>
            @else
                <div class="p-4 bg-gray-50 rounded-lg text-center">
                    <p class="text-sm text-gray-600">
                        Cette livraison est terminée. Aucune action disponible.
                    </p>
                </div>
            @endif

            <!-- Timeline -->
            <div class="mt-6 pt-6 border-t">
                <h4 class="text-sm font-semibold text-gray-900 mb-4">Timeline</h4>
                
                <div class="space-y-3 text-xs">
                    <div class="flex items-start">
                        <span class="inline-block w-2 h-2 rounded-full bg-slate-400 mt-2 mr-3 shrink-0"></span>
                        <div>
                            <p class="font-medium text-gray-900">À préparer</p>
                            <p class="text-gray-600">{{ $delivery->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    @if($delivery->status === 'prete' || in_array($delivery->status, ['remise_au_client', 'retour_recu', 'terminee']))
                        <div class="flex items-start">
                            <span class="inline-block w-2 h-2 rounded-full bg-blue-400 mt-2 mr-3 shrink-0"></span>
                            <div>
                                <p class="font-medium text-gray-900">Prête</p>
                            </div>
                        </div>
                    @endif

                    @if($delivery->actual_delivery_date)
                        <div class="flex items-start">
                            <span class="inline-block w-2 h-2 rounded-full bg-green-400 mt-2 mr-3 shrink-0"></span>
                            <div>
                                <p class="font-medium text-gray-900">Remise au client</p>
                                <p class="text-gray-600">{{ $delivery->actual_delivery_date->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($delivery->actual_return_date)
                        <div class="flex items-start">
                            <span class="inline-block w-2 h-2 rounded-full bg-amber-400 mt-2 mr-3 shrink-0"></span>
                            <div>
                                <p class="font-medium text-gray-900">Retour reçu</p>
                                <p class="text-gray-600">{{ $delivery->actual_return_date->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($delivery->status === 'terminee')
                        <div class="flex items-start">
                            <span class="inline-block w-2 h-2 rounded-full bg-gray-400 mt-2 mr-3 shrink-0"></span>
                            <div>
                                <p class="font-medium text-gray-900">Terminée</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
