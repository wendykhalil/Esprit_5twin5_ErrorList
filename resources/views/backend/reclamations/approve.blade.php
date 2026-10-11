@extends('layouts.backend')

@section('title', 'Approuver la Réclamation #' . $reclamation->id)

@section('content')
<div>
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Approuver la Réclamation</h1>
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
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Résumé de la Réclamation</h2>
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

            <!-- Incidents for Approval -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Incidents à Approuver</h2>
                <form action="{{ route('admin.reclamations.approve', $reclamation->id) }}" method="POST">
                    @csrf

                    <div class="space-y-4 mb-6">
                        @forelse ($reclamation->incidents as $incident)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" id="incident_{{ $incident->id }}" name="approved_incidents[]" value="{{ $incident->id }}" class="w-4 h-4 text-green-600" checked>
                                            <label for="incident_{{ $incident->id }}" class="font-semibold text-gray-900">
                                                {{ $incident->equipment->name ?? 'Équipement #' . $incident->equipment_id }}
                                            </label>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-2">
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
                                        <p class="text-gray-700 mt-2">{{ $incident->description }}</p>
                                    </div>
                                    <div class="text-right whitespace-nowrap">
                                        <p class="text-sm text-gray-600">Remboursement</p>
                                        <p class="text-xl font-bold text-green-600">
                                            {{ number_format($incident->proposed_refund_amount, 2, ',', ' ') }} €
                                        </p>
                                        @if ($incident->proposed_refund_percentage > 0)
                                            <p class="text-xs text-gray-500">({{ $incident->proposed_refund_percentage }}%)</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-600">Aucun incident.</p>
                        @endforelse
                    </div>

                    <!-- Total -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                        <p class="text-sm text-gray-600 mb-2">Montant Total à Rembourser</p>
                        <p class="text-3xl font-bold text-green-600">{{ number_format($totalProposedRefund, 2, ',', ' ') }} €</p>
                    </div>

                    <!-- Admin Notes -->
                    <div class="mb-6">
                        <label for="admin_notes" class="block text-sm font-semibold text-gray-900 mb-2">
                            Notes (optionnel)
                        </label>
                        <textarea name="admin_notes" id="admin_notes" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('admin_notes') border-red-500 @enderror" placeholder="Ajoutez des notes administratives...">{{ old('admin_notes') }}</textarea>
                        @error('admin_notes')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4 pt-6 border-t border-gray-200">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg">
                            ✓ Confirmer l'Approbation
                        </button>
                        <a href="{{ route('admin.reclamations.show', $reclamation->id) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold py-2 px-6 rounded-lg">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Vérifications</h3>
                <div class="space-y-4">
                    <div class="pb-4 border-b border-gray-200">
                        <p class="text-sm text-gray-600">Paiement Original</p>
                        @php
                            $payment = $reclamation->payment;
                        @endphp
                        @if ($payment)
                            <p class="text-lg font-bold text-gray-900">{{ number_format($payment->amount, 2, ',', ' ') }} €</p>
                        @else
                            <p class="text-red-600 font-semibold">Aucun paiement trouvé</p>
                        @endif
                    </div>

                    @if ($payment)
                        <div class="pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-600">Remboursements Précédents</p>
                            @php
                                $totalRefunded = $payment->transactions()
                                    ->where('type', 'refund')
                                    ->where('status', 'completed')
                                    ->sum('amount');
                            @endphp
                            <p class="text-lg font-bold text-gray-900">{{ number_format($totalRefunded, 2, ',', ' ') }} €</p>
                        </div>

                        <div class="pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-600">Solde Remboursable</p>
                            @php
                                $remainingRefundable = $payment->amount - $totalRefunded;
                            @endphp
                            <p class="text-lg font-bold {{ $remainingRefundable >= $totalProposedRefund ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($remainingRefundable, 2, ',', ' ') }} €
                            </p>
                        </div>

                        @if ($totalProposedRefund > $remainingRefundable)
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                <p class="text-sm text-red-700 font-semibold">⚠️ Montant insuffisant !</p>
                                <p class="text-xs text-red-600 mt-1">Le remboursement proposé dépasse le solde disponible.</p>
                            </div>
                        @else
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                <p class="text-sm text-green-700 font-semibold">✓ Approbation Possible</p>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
