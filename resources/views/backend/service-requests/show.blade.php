@extends('layouts.backend')

@section('title', 'Intervention #' . $serviceRequest->id . ' - SolarShare Admin')

@section('content')

@php
    $statusBadges = [
        'pending' => ['bg-yellow-100 text-yellow-800 border-yellow-200', 'En attente d\'acceptation'],
        'accepted' => ['bg-blue-100 text-blue-800 border-blue-200', 'Acceptée (Devis émis)'],
        'in_progress' => ['bg-purple-100 text-purple-800 border-purple-200', 'Intervention en cours'],
        'completed' => ['bg-green-100 text-green-800 border-green-200', 'Terminée avec succès'],
        'rejected' => ['bg-red-100 text-red-800 border-red-200', 'Refusée par le prestataire'],
        'cancelled' => ['bg-slate-200 text-slate-800 border-slate-300', 'Annulée'],
    ];
    $badge = $statusBadges[$serviceRequest->status] ?? ['bg-gray-100 text-gray-800 border-gray-200', $serviceRequest->status];
@endphp

<div class="space-y-6">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl shadow-sm" style="font-family: Outfit, sans-serif">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl shadow-sm" style="font-family: Outfit, sans-serif">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl shadow-sm" style="font-family: Outfit, sans-serif">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Top navigation & Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.service-requests.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 mb-2 transition-colors" style="font-family: Outfit, sans-serif">
                ← Retour à la liste des interventions
            </a>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">
                    Intervention #{{ $serviceRequest->id }}
                </h2>
                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border {{ $badge[0] }}" style="font-family: Outfit, sans-serif">
                    {{ $badge[1] }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1" style="font-family: Outfit, sans-serif">
                Créée le {{ $serviceRequest->created_at->format('d/m/Y à H:i') }} • Dernière mise à jour le {{ $serviceRequest->updated_at->format('d/m/Y à H:i') }}
            </p>
        </div>
    </div>

    {{-- Cancellation notice banner if cancelled --}}
    @if($serviceRequest->status === 'cancelled')
        <div class="bg-slate-100 border border-slate-300 rounded-xl p-5 shadow-sm">
            <div class="flex items-start gap-3">
                <span class="text-2xl">⚠️</span>
                <div class="space-y-1">
                    <h4 class="font-bold text-slate-900 text-sm" style="font-family: Outfit, sans-serif">
                        Cette intervention a été annulée
                    </h4>
                    @if($serviceRequest->cancellation_reason)
                        <p class="text-sm text-slate-700">
                            <strong>Motif d'annulation :</strong> {{ $serviceRequest->cancellation_reason }}
                        </p>
                    @endif
                    <div class="text-xs text-slate-500 pt-1">
                        @if($serviceRequest->cancelled_at)
                            <span>Annulée le {{ $serviceRequest->cancelled_at->format('d/m/Y à H:i') }}</span>
                        @endif
                        @if($serviceRequest->cancelledBy)
                            <span>par l'administrateur <strong>{{ $serviceRequest->cancelledBy->name }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT (2 Cols): Details & Equipment & Timeline --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Main Intervention Info --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-5">
                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Titre de la demande</span>
                    <h3 class="text-xl font-bold text-slate-900 mt-1" style="font-family: Outfit, sans-serif">
                        {{ $serviceRequest->title }}
                    </h3>
                </div>

                <div>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Description du besoin technique</span>
                    <div class="mt-2 text-sm text-slate-700 whitespace-pre-line bg-slate-50 p-4 rounded-lg border border-slate-100">
                        {{ $serviceRequest->description }}
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-slate-100">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Lieu d'intervention</span>
                        <p class="text-sm font-medium text-slate-800 mt-1">📍 {{ $serviceRequest->address }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Date souhaitée</span>
                        <p class="text-sm font-medium text-slate-800 mt-1">
                            📅 {{ $serviceRequest->requested_date ? $serviceRequest->requested_date->format('d/m/Y à H:i') : 'Non renseignée' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- AI Technical Pre-diagnosis Audit Card --}}
            @if($serviceRequest->ai_pre_diagnosis)
                @php $ai = $serviceRequest->ai_pre_diagnosis; @endphp
                <div class="bg-white rounded-xl border border-amber-200 p-6 shadow-sm space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">✨</span>
                            <h4 class="text-base font-bold text-slate-900" style="font-family: Outfit, sans-serif">
                                Audit IA : Pré-diagnostic technique indicatif
                            </h4>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded border border-slate-200">
                                Source : {{ $ai['source'] ?? 'analysée' }}
                            </span>
                            @if(!empty($ai['severity']))
                                @php
                                    $severityBadges = [
                                        'faible' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'moyenne' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        'elevee' => 'bg-orange-100 text-orange-800 border-orange-200',
                                        'urgente' => 'bg-red-100 text-red-800 border-red-200',
                                    ];
                                    $sBadge = $severityBadges[$ai['severity']] ?? 'bg-slate-100 text-slate-800 border-slate-200';
                                @endphp
                                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full border {{ $sBadge }}">
                                    Gravité : {{ strtoupper($ai['severity']) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="sm:col-span-2">
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Diagnostic probable</span>
                            <p class="font-bold text-slate-900 mt-1">{{ $ai['diagnostic_probable'] ?? 'Non spécifié' }}</p>
                            @if(!empty($ai['short_summary']))
                                <p class="text-xs text-slate-600 mt-1">{{ $ai['short_summary'] }}</p>
                            @endif
                        </div>

                        <div>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Durée estimée</span>
                            <p class="text-sm font-medium text-slate-800 mt-1">⏱️ {{ $ai['estimated_duration'] ?? '1h à 2h' }}</p>
                        </div>

                        <div>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Spécialité recommandée</span>
                            <p class="text-sm font-medium text-slate-800 mt-1">🔧 {{ $ai['recommended_specialty'] ?? 'Non spécifiée' }}</p>
                        </div>

                        @if(!empty($ai['probable_components']))
                            <div class="sm:col-span-2">
                                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Composants ciblés</span>
                                <div class="flex flex-wrap gap-1.5 mt-1.5">
                                    @foreach($ai['probable_components'] as $comp)
                                        <span class="bg-slate-50 border border-slate-200 text-slate-700 px-2 py-0.5 rounded text-xs">{{ $comp }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(!empty($ai['safety_advice']))
                            <div class="sm:col-span-2 bg-red-50 border border-red-200 rounded-lg p-3 text-xs text-red-800">
                                <strong>⚠️ Conseil de sécurité généré :</strong> {{ $ai['safety_advice'] }}
                            </div>
                        @endif

                        @if(!empty($ai['indicative_cost_range']))
                            <div class="sm:col-span-2 bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-900 flex items-center justify-between">
                                <span>Fourchette financière indicative communiquée au client :</span>
                                <strong class="font-bold">{{ $ai['indicative_cost_range'] }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Equipment Information --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <h4 class="text-base font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">
                    Équipement concerné
                </h4>
                @if($serviceRequest->equipment)
                    <div class="flex items-start justify-between gap-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <div>
                            <h5 class="font-bold text-slate-900 text-sm" style="font-family: Outfit, sans-serif">
                                {{ $serviceRequest->equipment->name }}
                            </h5>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $serviceRequest->equipment->brand ?? 'Marque inconnue' }} • État : {{ $serviceRequest->equipment->condition ?? 'N/A' }}
                            </p>
                            <div class="flex flex-wrap gap-3 mt-3 text-xs text-slate-600">
                                @if($serviceRequest->equipment->power)
                                    <span>⚡ <strong>{{ $serviceRequest->equipment->power }}</strong> W</span>
                                @endif
                                @if($serviceRequest->equipment->capacity)
                                    <span>🔋 <strong>{{ $serviceRequest->equipment->capacity }}</strong> Ah/kWh</span>
                                @endif
                                @if($serviceRequest->equipment->location)
                                    <span>📍 {{ $serviceRequest->equipment->location }}</span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('admin.equipments.show', $serviceRequest->equipment) }}" class="shrink-0 text-xs font-semibold text-amber-600 hover:text-amber-700 bg-white px-3 py-1.5 rounded-lg border border-slate-200 transition-colors" style="font-family: Outfit, sans-serif">
                            Fiche équipement →
                        </a>
                    </div>
                @else
                    <div class="p-4 bg-slate-50 rounded-xl border border-dashed border-slate-200 text-center text-xs text-slate-400" style="font-family: Outfit, sans-serif">
                        Aucun équipement de la plateforme n'a été rattaché à cette demande (intervention générale).
                    </div>
                @endif
            </div>

            {{-- Visual Workflow History --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <h4 class="text-base font-bold text-slate-900 mb-4" style="font-family: Outfit, sans-serif">
                    Cycle de vie de l'intervention
                </h4>
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    {{-- Step 1 --}}
                    <div class="flex items-center gap-2 {{ in_array($serviceRequest->status, ['pending', 'accepted', 'in_progress', 'completed']) ? 'text-emerald-700 font-semibold' : 'text-slate-400' }}">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center {{ in_array($serviceRequest->status, ['pending', 'accepted', 'in_progress', 'completed']) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100' }}">1</span>
                        <span>Soumission client</span>
                    </div>
                    <span class="hidden sm:inline text-slate-300">→</span>

                    {{-- Step 2 --}}
                    <div class="flex items-center gap-2 {{ in_array($serviceRequest->status, ['accepted', 'in_progress', 'completed']) ? 'text-emerald-700 font-semibold' : ($serviceRequest->status === 'rejected' ? 'text-red-700 font-semibold' : 'text-slate-400') }}">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center {{ in_array($serviceRequest->status, ['accepted', 'in_progress', 'completed']) ? 'bg-emerald-100 text-emerald-800' : ($serviceRequest->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-slate-100') }}">2</span>
                        <span>Devis pro ({{ $serviceRequest->status === 'rejected' ? 'Refusé' : 'Accepté' }})</span>
                    </div>
                    <span class="hidden sm:inline text-slate-300">→</span>

                    {{-- Step 3 --}}
                    <div class="flex items-center gap-2 {{ in_array($serviceRequest->status, ['in_progress', 'completed']) ? 'text-emerald-700 font-semibold' : 'text-slate-400' }}">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center {{ in_array($serviceRequest->status, ['in_progress', 'completed']) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100' }}">3</span>
                        <span>Intervention en cours</span>
                    </div>
                    <span class="hidden sm:inline text-slate-300">→</span>

                    {{-- Step 4 --}}
                    <div class="flex items-center gap-2 {{ $serviceRequest->status === 'completed' ? 'text-emerald-700 font-semibold' : ($serviceRequest->status === 'cancelled' ? 'text-slate-700 font-semibold' : 'text-slate-400') }}">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center {{ $serviceRequest->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($serviceRequest->status === 'cancelled' ? 'bg-slate-200 text-slate-800' : 'bg-slate-100') }}">4</span>
                        <span>{{ $serviceRequest->status === 'cancelled' ? 'Annulée' : 'Terminée' }}</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- RIGHT (1 Col): Parties & Financial & Admin Actions --}}
        <div class="space-y-6">

            {{-- Financial Card --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Devis / Prix estimé</span>
                <div class="mt-2">
                    @if($serviceRequest->estimated_price !== null)
                        <p class="text-3xl font-extrabold text-slate-900" style="font-family: Outfit, sans-serif">
                            {{ number_format((float) $serviceRequest->estimated_price, 2) }} <span class="text-sm font-normal text-slate-500">TND</span>
                        </p>
                        <p class="text-xs text-slate-400 mt-1">
                            Fixé par le prestataire lors de l'acceptation de la demande.
                        </p>
                    @else
                        <p class="text-xl font-bold text-slate-400 italic" style="font-family: Outfit, sans-serif">
                            Non chiffré
                        </p>
                        <p class="text-xs text-slate-400 mt-1">
                            Le prestataire doit saisir ce montant pour accepter la demande.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Client Card --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-3">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Client demandeur</span>
                <div>
                    <h5 class="text-base font-bold text-slate-900" style="font-family: Outfit, sans-serif">
                        {{ $serviceRequest->user->name ?? 'N/A' }}
                    </h5>
                    <p class="text-xs text-slate-500 mt-0.5">✉️ {{ $serviceRequest->user->email ?? 'N/A' }}</p>
                    @if($serviceRequest->user->phone)
                        <p class="text-xs text-slate-500 mt-0.5">📞 {{ $serviceRequest->user->phone }}</p>
                    @endif
                </div>
            </div>

            {{-- Provider Card --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-3">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider" style="font-family: Outfit, sans-serif">Prestataire assigné</span>
                <div>
                    <h5 class="text-base font-bold text-slate-900" style="font-family: Outfit, sans-serif">
                        {{ $serviceRequest->serviceProvider->user->name ?? 'N/A' }}
                    </h5>
                    <p class="text-xs font-semibold text-emerald-600 mt-0.5">
                        {{ $serviceRequest->serviceProvider->specialty ?? 'N/A' }}
                    </p>
                    <p class="text-xs text-slate-500 mt-1">
                        Tarif horaire : {{ number_format((float) ($serviceRequest->serviceProvider->hourly_rate ?? 0), 2) }} TND/h
                    </p>
                    @if($serviceRequest->serviceProvider->phone)
                        <p class="text-xs text-slate-500 mt-0.5">📞 {{ $serviceRequest->serviceProvider->phone }}</p>
                    @endif
                </div>
                <div class="pt-2">
                    <a href="{{ route('admin.service-providers.show', $serviceRequest->serviceProvider) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 underline" style="font-family: Outfit, sans-serif">
                        Voir le profil prestataire →
                    </a>
                </div>
            </div>

            {{-- Admin Actions Panel --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-4">
                <h4 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2" style="font-family: Outfit, sans-serif">
                    Arbitrage administratif
                </h4>

                @if(in_array($serviceRequest->status, ['pending', 'accepted', 'in_progress']))
                    <p class="text-xs text-slate-500">
                        En tant qu'administrateur, vous pouvez arbitrer cette intervention en cas de litige ou de blocage opérationnel.
                    </p>

                    {{-- Cancel Action Form --}}
                    <div class="space-y-3 pt-2">
                        <button
                            type="button"
                            onclick="document.getElementById('cancelModal').classList.remove('hidden')"
                            class="w-full py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm"
                            style="font-family: Outfit, sans-serif"
                        >
                            ✕ Annuler l'intervention
                        </button>

                        {{-- Emergency Complete (only if in_progress) --}}
                        @if($serviceRequest->status === 'in_progress')
                            <form
                                action="{{ route('admin.service-requests.complete', $serviceRequest) }}"
                                method="POST"
                                onsubmit="return confirm('Confirmez-vous la clôture administrative de secours ? Assurez-vous que l\'intervention sur site a bien été achevée.');"
                            >
                                @csrf
                                @method('PATCH')
                                <button
                                    type="submit"
                                    class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm"
                                    style="font-family: Outfit, sans-serif"
                                >
                                    ✓ Clôturer l'intervention (Secours)
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    <div class="bg-slate-50 p-3 rounded-lg text-center text-xs text-slate-500" style="font-family: Outfit, sans-serif">
                        🔒 Cette intervention est dans un statut final (<strong>{{ $serviceRequest->status }}</strong>). Aucune modification administrative n'est requise.
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>

{{-- Cancel Modal --}}
@if(in_array($serviceRequest->status, ['pending', 'accepted', 'in_progress']))
    <div id="cancelModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900" style="font-family: Outfit, sans-serif">
                    Annulation administrative
                </h3>
                <button type="button" onclick="document.getElementById('cancelModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">
                    ✕
                </button>
            </div>

            <p class="text-xs text-slate-600 mb-4">
                Veuillez spécifier la raison administrative de l'annulation. Ce motif sera consigné dans l'historique et affiché au client et au prestataire.
            </p>

            <form action="{{ route('admin.service-requests.cancel', $serviceRequest) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <label for="cancellation_reason" class="block text-xs font-semibold text-slate-700 mb-1" style="font-family: Outfit, sans-serif">
                        Motif d'annulation <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        name="cancellation_reason"
                        id="cancellation_reason"
                        rows="4"
                        required
                        placeholder="Ex : Litige client sur la prestation, prestataire indisponible pour cause majeure, équipement incompatible..."
                        class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-400 text-slate-800"
                        style="font-family: Outfit, sans-serif"
                    >{{ old('cancellation_reason') }}</textarea>
                    <p class="text-xs text-slate-400 mt-1">Au moins 5 caractères requis.</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button
                        type="button"
                        onclick="document.getElementById('cancelModal').classList.add('hidden')"
                        class="px-4 py-2 border border-slate-200 text-slate-600 rounded-lg text-xs font-semibold hover:bg-slate-50 transition-colors"
                        style="font-family: Outfit, sans-serif"
                    >
                        Annuler
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm"
                        style="font-family: Outfit, sans-serif"
                    >
                        Confirmer l'annulation
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

@endsection
