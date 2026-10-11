@extends('layouts.backend')

@section('title', 'Refuser la Réclamation #' . $reclamation->id)

@section('content')
<div>
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Refuser la Réclamation</h1>
        <p class="text-gray-600 mt-2">Réclamation #{{ $reclamation->id }} de {{ $reclamation->user->name }}</p>
    </div>

    <!-- Errors -->
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <p class="font-semibold mb-2">Erreurs de validation :</p>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Reclamation Details -->
        <div class="lg:col-span-2">
            <!-- Summary -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Détails de la Réclamation</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600">Titre</p>
                        <p class="text-gray-900 font-semibold">{{ $reclamation->title }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Description</p>
                        <p class="text-gray-900">{{ $reclamation->description }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Client</p>
                        <p class="text-gray-900">{{ $reclamation->user->name }} ({{ $reclamation->user->email }})</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Réservation</p>
                        <p class="text-gray-900">
                            {{ $reclamation->reservation->getEquipmentLabelAttribute() }} - {{ $reclamation->reservation->date_debut->format('d/m/Y') }} au {{ $reclamation->reservation->date_fin->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Incidents Overview -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Incidents Impliqués</h2>
                <div class="space-y-3">
                    @forelse ($reclamation->incidents as $incident)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <p class="font-semibold text-gray-900">{{ $incident->equipment->name ?? 'Équipement #' . $incident->equipment_id }}</p>
                            <p class="text-sm text-gray-600 mt-1">
                                Type : <span class="font-semibold">
                                    @php
                                        $typeLabels = [
                                            'endommage' => 'Endommagé',
                                            'manquant' => 'Manquant',
                                            'malfonctionnant' => 'Malfoncti onnant',
                                            'autre' => 'Autre',
                                        ];
                                    @endphp
                                    {{ $typeLabels[$incident->type_probleme] ?? $incident->type_probleme }}
                                </span>
                            </p>
                            <p class="text-gray-700 text-sm mt-2">{{ $incident->description }}</p>
                        </div>
                    @empty
                        <p class="text-gray-600">Aucun incident.</p>
                    @endforelse
                </div>
            </div>

            <!-- Rejection Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Motif du Rejet</h2>
                <form action="{{ route('admin.reclamations.reject', $reclamation->id) }}" method="POST">
                    @csrf

                    <!-- Rejection Reason -->
                    <div class="mb-6">
                        <label for="rejected_reason" class="block text-sm font-semibold text-gray-900 mb-2">
                            Motif du Rejet <span class="text-red-600">*</span>
                        </label>
                        <textarea name="rejected_reason" id="rejected_reason" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('rejected_reason') border-red-500 @enderror" placeholder="Expliquez pourquoi vous refusez cette réclamation..." required>{{ old('rejected_reason') }}</textarea>
                        @error('rejected_reason')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Admin Notes (optional) -->
                    <div class="mb-6">
                        <label for="admin_notes" class="block text-sm font-semibold text-gray-900 mb-2">
                            Notes Internes (optionnel)
                        </label>
                        <textarea name="admin_notes" id="admin_notes" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('admin_notes') border-red-500 @enderror" placeholder="Notes personnelles sur le rejet...">{{ old('admin_notes') }}</textarea>
                        @error('admin_notes')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4 pt-6 border-t border-gray-200">
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg">
                            ✕ Confirmer le Rejet
                        </button>
                        <a href="{{ route('admin.reclamations.show', $reclamation->id) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold py-2 px-6 rounded-lg">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Warning -->
        <div class="lg:col-span-1">
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 sticky top-4">
                <h3 class="text-lg font-semibold text-red-900 mb-4">⚠️ Attention</h3>
                <p class="text-sm text-red-700 mb-4">
                    Le rejet de cette réclamation :
                </p>
                <ul class="text-sm text-red-700 space-y-2 list-disc list-inside">
                    <li>Signifiera que le client n'a pas droit au remboursement</li>
                    <li>Sera enregistré dans le dossier de la réclamation</li>
                    <li>Pourra être contesté par le client</li>
                    <li>Ne peut pas être annulé directement</li>
                </ul>
                <p class="text-xs text-red-600 mt-4 italic">
                    Assurez-vous que le motif du rejet est clair et justifié avant de confirmer.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
