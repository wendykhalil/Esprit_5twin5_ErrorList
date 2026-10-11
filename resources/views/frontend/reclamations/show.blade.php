@extends('layouts.frontend')

@section('title', 'Réclamation #' . $reclamation->id)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-start mb-4">
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
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Left Column: Details -->
            <div class="lg:col-span-2">
                <!-- Reclamation Details -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Détails de la Réclamation</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600">Description</p>
                            <p class="text-gray-900">{{ $reclamation->description }}</p>
                        </div>
                        <div class="pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-600">Réservation</p>
                            <p class="text-gray-900">
                                <strong>{{ $reclamation->reservation->getEquipmentLabelAttribute() }}</strong>
                                <br>
                                <span class="text-sm">{{ $reclamation->reservation->date_debut->format('d/m/Y') }} au {{ $reclamation->reservation->date_fin->format('d/m/Y') }}</span>
                            </p>
                        </div>
                        <div class="pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-600">Créée le</p>
                            <p class="text-gray-900">{{ $reclamation->created_at->format('d/m/Y à H:i') }}</p>
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
                                    @if ($incident->proposed_refund_amount > 0)
                                        <div class="text-right">
                                            <p class="text-sm text-gray-600">Remboursement proposé</p>
                                            <p class="text-lg font-semibold text-green-600">{{ number_format($incident->proposed_refund_amount, 2, ',', ' ') }} €</p>
                                            <p class="text-xs text-gray-500">({{ $incident->proposed_refund_percentage }}%)</p>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-gray-700 mb-3">{{ $incident->description }}</p>
                                @if ($incident->damage_percentage)
                                    <p class="text-sm text-gray-600">Pourcentage de dommages : {{ $incident->damage_percentage }}%</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-gray-600">Aucun incident associé.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Admin Notes (if any) -->
                @if ($reclamation->admin_notes)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                        <h3 class="font-semibold text-gray-900 mb-2">Notes Administrateur</h3>
                        <p class="text-gray-700">{{ $reclamation->admin_notes }}</p>
                    </div>
                @endif

                <!-- Rejection Reason (if rejected) -->
                @if ($reclamation->status === 'rejetee' && $reclamation->rejected_reason)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                        <h3 class="font-semibold text-gray-900 mb-2">Motif du Rejet</h3>
                        <p class="text-gray-700">{{ $reclamation->rejected_reason }}</p>
                    </div>
                @endif
            </div>

            <!-- Right Column: Summary -->
            <div class="lg:col-span-1">
                <!-- Summary Card -->
                <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Résumé</h3>

                    <div class="space-y-4">
                        <div class="pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-600">Nombre d'incidents</p>
                            <p class="text-2xl font-bold text-gray-900">{{ count($reclamation->incidents) }}</p>
                        </div>

                        <div class="pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-600">Remboursement proposé</p>
                            <p class="text-2xl font-bold text-green-600">{{ number_format($totalProposedRefund, 2, ',', ' ') }} €</p>
                        </div>

                        @if ($reclamation->status === 'approuvee')
                            <div class="pb-4 border-b border-gray-200">
                                <p class="text-sm text-gray-600">Remboursement approuvé</p>
                                <p class="text-2xl font-bold text-green-600">{{ number_format($reclamation->refund_amount ?? 0, 2, ',', ' ') }} €</p>
                            </div>

                            @if ($reclamation->refund_approved_at)
                                <div>
                                    <p class="text-sm text-gray-600">Date d'approbation</p>
                                    <p class="text-gray-900">{{ $reclamation->refund_approved_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            @endif
                        @elseif ($reclamation->status === 'rejetee')
                            @if ($reclamation->rejected_at)
                                <div>
                                    <p class="text-sm text-gray-600">Date du rejet</p>
                                    <p class="text-gray-900">{{ $reclamation->rejected_at->format('d/m/Y à H:i') }}</p>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Back Button -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <a href="{{ route('reclamations.index') }}" class="block text-center bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold py-2 px-4 rounded-lg">
                            Retour aux réclamations
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
