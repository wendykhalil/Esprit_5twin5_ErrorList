@extends('layouts.backend')

@section('title', 'Approuver la Réclamation #' . $reclamation->id)

@section('content')
<div>
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Approuver la Réclamation</h1>
        <p class="text-gray-600 mt-2">Réclamation #{{ $reclamation->id }} de {{ $reclamation->user->name }}</p>
    </div>

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
                                        
                                        <!-- Image Evidence Display -->
                                        @if ($incident->hasAttachment())
                                            <div class="mt-3 pt-3 border-t border-gray-200">
                                                <p class="text-sm font-semibold text-gray-700 mb-2">📸 Preuve Jointe</p>
                                                <div class="bg-gray-50 rounded-lg p-2 inline-block">
                                                    <a href="{{ $incident->getAttachmentUrl() }}" target="_blank" rel="noopener noreferrer" class="block">
                                                        <img src="{{ $incident->getAttachmentUrl() }}" alt="Evidence photo" class="max-w-sm h-auto rounded-lg shadow-sm hover:shadow-md transition">
                                                    </a>
                                                </div>
                                                <p class="text-xs text-gray-600 mt-2">Cliquez pour voir en pleine résolution</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-right whitespace-nowrap">
                                        <p class="text-sm text-gray-600">Remboursement</p>
                                        <p class="text-xl font-bold text-green-600">
                                            {{ number_format($incident->proposed_refund_amount, 2, ',', ' ') }} TND
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

                    <!-- Compensation Code Configuration -->
                    <div class="border-t border-gray-200 pt-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Configuration du Code de Compensation</h3>

                        <!-- Type of Discount -->
                        <div class="mb-4">
                            <label for="type" class="block text-sm font-semibold text-gray-900 mb-2">
                                Type de Réduction <span class="text-red-600">*</span>
                            </label>
                            <select name="type" id="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('type') border-red-500] @enderror">
                                <option value="">-- Sélectionner un type --</option>
                                <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Pourcentage (%)</option>
                                <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Montant Fixe (TND)</option>
                            </select>
                            @error('type')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Choisir entre un pourcentage de remise ou un montant fixe en dinars</p>
                        </div>

                        <!-- Value -->
                        <div class="mb-4">
                            <label for="value" class="block text-sm font-semibold text-gray-900 mb-2">
                                Valeur <span class="text-red-600">*</span>
                            </label>
                            <input type="number" name="value" id="value" step="0.01" min="0.01" placeholder="Ex: 20.50 ou 15" value="{{ old('value') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('value') border-red-500] @enderror">
                            @error('value')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-gray-500 mt-1">Minimum: 0.01 (%). Si pourcentage, maximum 100%</p>
                        </div>

                        <!-- Days Valid -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="days_valid" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Jours de Validité <span class="text-red-600">*</span>
                                </label>
                                <input type="number" name="days_valid" id="days_valid" min="1" max="365" placeholder="30" value="{{ old('days_valid') }}" class="w-full px-4 py-2 border ] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500] @error('days_valid') border-red-500 @enderror">
                                @error('days_valid')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">1-365 jours à partir de maintenant</p>
                            </div>

                            <!-- Max Uses -->
                            <div>
                                <label for="max_uses" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Max. Utilisations <span class="text-red-600">*</span>
                                </label>
                                <input type="number" name="max_uses" id="max_uses" min="1" placeholder="1" value="{{ old('max_uses') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('max_uses') border-red-500] @enderror">
                                @error('max_uses')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Minimum: 1 utilisation</p>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    <div class="mb-6">
                        <label for="admin_notes" class="block text-sm font-semibold text-gray-900 mb-2">
                            Notes (optionnel)
                        </label>
                        <textarea name="admin_notes" id="admin_notes" rows="3" class="w-full px-4 py-2 border border-gray-300] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('admin_notes') border-red-500 @enderror" placeholder="Ajoutez des notes administratives...">{{ old('admin_notes') }}</textarea>
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
                            $payment = $reclamation->payment();
                        @endphp
                        @if ($payment)
                            <p class="text-lg font-bold text-gray-900">{{ number_format($payment->amount, 2, ',', ' ') }} TND</p>
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
                            <p class="text-lg font-bold text-gray-900">{{ number_format($totalRefunded, 2, ',', ' ') }} TND</p>
                        </div>

                        <div class="pb-4 border-b border-gray-200">
                            <p class="text-sm text-gray-600">Solde Remboursable</p>
                            @php
                                $remainingRefundable = $payment->amount - $totalRefunded;
                            @endphp
                            <p class="text-lg font-bold {{ $remainingRefundable >= $totalProposedRefund ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($remainingRefundable, 2, ',', ' ') }} TND
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
