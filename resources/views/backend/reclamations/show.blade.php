@extends('layouts.backend')

@section('title', 'Réclamation #' . $reclamation->id)

@section('content')
<div>
    <!-- Header -->
    <div class="flex justify-between items-start mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $reclamation->title }}</h1>
            <p class="text-gray-600 mt-2">Réclamation #{{ $reclamation->id }}</p>
        </div>
        <div class="text-right">
            @php
                $statusClasses = [
                    'en_attente' => 'bg-yellow-100 text-yellow-800',
                    'en_cours' => 'bg-blue-100 text-blue-800',
                    'approuvee' => 'bg-green-100 text-green-800',
                    'rejetee' => 'bg-red-100 text-red-800',
                    'fermee' => 'bg-gray-100 text-gray-800',
                ];
                $statusLabels = [
                    'en_attente' => 'En attente',
                    'en_cours' => 'En cours',
                    'approuvee' => 'Approuvée',
                    'rejetee' => 'Rejetée',
                    'fermee' => 'Fermée',
                ];
            @endphp
            <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold {{ $statusClasses[$reclamation->status] ?? 'bg-gray-100 text-gray-800' }}">
                {{ $statusLabels[$reclamation->status] ?? $reclamation->status }}
            </span>
        </div>
    </div>

    <!-- Messages -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2">
            <!-- Reclamation Info -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Détails de la Réclamation</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-600">Description</p>
                        <p class="text-gray-900 mt-1">{{ $reclamation->description }}</p>
                    </div>
                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600">Client</p>
                        <p class="text-gray-900 mt-1">
                            <a href="{{ route('admin.users.show', $reclamation->user->id) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $reclamation->user->name }} ({{ $reclamation->user->email }})
                            </a>
                        </p>
                    </div>
                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600">Réservation</p>
                        <p class="text-gray-900 mt-1">
                            <a href="{{ route('admin.reservations.show', $reclamation->reservation->id) }}" class="text-blue-600 hover:text-blue-800">
                                #{{ $reclamation->reservation->id }} - {{ $reclamation->reservation->getEquipmentLabelAttribute() }}
                            </a>
                            <br>
                            <span class="text-sm">{{ $reclamation->reservation->date_debut->format('d/m/Y') }} au {{ $reclamation->reservation->date_fin->format('d/m/Y') }}</span>
                        </p>
                    </div>
                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600">Créée le</p>
                        <p class="text-gray-900 mt-1">{{ $reclamation->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Incidents -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Incidents ({{ count($reclamation->incidents) }})</h2>
                <div class="space-y-4">
                    @forelse ($reclamation->incidents as $incident)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $incident->equipment->name ?? 'Équipement #' . $incident->equipment_id }}</h3>
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
                                </div>
                            </div>
                            <p class="text-gray-700 mb-2">{{ $incident->description }}</p>
                            @if ($incident->damage_percentage)
                                <p class="text-xs text-gray-600 mb-3">Dommages : {{ $incident->damage_percentage }}%</p>
                            @endif
                            
                            <!-- Image Evidence Display -->
                            @if ($incident->hasAttachment())
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <p class="text-sm font-semibold text-gray-700 mb-2">📸 Preuve Jointe</p>
                                    <div class="bg-gray-50 rounded-lg p-2 inline-block">
                                        <a href="{{ $incident->getAttachmentUrl() }}" target="_blank" rel="noopener noreferrer" class="block">
                                            <img src="{{ $incident->getAttachmentUrl() }}" alt="Evidence photo" class="max-w-md h-auto rounded-lg shadow-sm hover:shadow-md transition">
                                        </a>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-2">Cliquez pour voir en pleine résolution</p>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-600">Aucun incident associé.</p>
                    @endforelse
                </div>
            </div>

            <!-- Admin Notes -->
            @if ($reclamation->admin_notes)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-2">Notes Administrateur</h3>
                    <p class="text-gray-700">{{ $reclamation->admin_notes }}</p>
                </div>
            @endif

            <!-- Rejection Reason -->
            @if ($reclamation->status === 'rejetee' && $reclamation->rejected_reason)
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-2">Motif du Rejet</h3>
                    <p class="text-gray-700">{{ $reclamation->rejected_reason }}</p>
                </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="lg:col-span-1">
            <!-- Summary -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Résumé</h3>
                <div class="space-y-4">
                    <div class="pb-4 border-b border-gray-200">
                        <p class="text-sm text-gray-600">Nombre d'incidents</p>
                        <p class="text-3xl font-bold text-gray-900">{{ count($reclamation->incidents) }}</p>
                    </div>

                    @if ($reclamation->status === 'approuvee')
                    @elseif ($reclamation->status === 'rejetee')
                        <div class="pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-600">Rejetée le</p>
                            @if ($reclamation->rejected_at)
                                <p class="text-gray-900">{{ $reclamation->rejected_at->format('d/m/Y à H:i') }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            @if ($reclamation->status !== 'approuvee' && $reclamation->status !== 'rejetee')
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('admin.reclamations.approve.form', $reclamation->id) }}" class="block w-full text-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">
                            ✓ Approuver
                        </a>
                        <a href="{{ route('admin.reclamations.reject.form', $reclamation->id) }}" class="block w-full text-center bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg">
                            ✕ Refuser
                        </a>
                    </div>
                </div>
            @endif

            <!-- Back -->
            <div class="bg-white rounded-lg shadow p-6">
                <a href="{{ route('admin.reclamations.index') }}" class="block text-center bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold py-2 px-4 rounded-lg">
                    Retour aux réclamations
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
