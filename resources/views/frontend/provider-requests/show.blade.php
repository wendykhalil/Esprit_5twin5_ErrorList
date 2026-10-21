@extends('layouts.frontend')

@section('title', 'Détails de la demande - SolarShare')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('provider.service-requests.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-amber-600 transition-colors" style="font-family: Outfit, sans-serif">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Retour aux demandes
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
            {{ session('error') }}
        </div>
    @endif

    @if($serviceRequest->status === 'cancelled')
        <div class="mb-6 bg-slate-100 border border-slate-300 text-slate-800 p-5 rounded-2xl shadow-sm">
            <div class="flex items-start gap-3">
                <span class="text-xl">⚠️</span>
                <div>
                    <h4 class="font-semibold text-slate-900">Intervention annulée</h4>
                    @if($serviceRequest->cancellation_reason)
                        <p class="text-sm text-slate-700 mt-1"><strong>Motif :</strong> {{ $serviceRequest->cancellation_reason }}</p>
                    @endif
                    @if($serviceRequest->cancelled_at)
                        <p class="text-xs text-slate-500 mt-1">Annulée le {{ $serviceRequest->cancelled_at->format('d/m/Y à H:i') }}
                            @if($serviceRequest->cancelledBy) par l'administration ({{ $serviceRequest->cancelledBy->name }}) @endif
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        {{-- Header Status --}}
        <div class="p-6 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">{{ $serviceRequest->title }}</h1>
                <p class="text-sm text-slate-500 mt-1" style="font-family: Outfit, sans-serif">Créée le {{ $serviceRequest->created_at->format('d/m/Y à H:i') }}</p>
            </div>
            <div>
                @php
                    $badges = [
                        'pending' => ['bg-yellow-100 text-yellow-800', 'En attente'],
                        'accepted' => ['bg-blue-100 text-blue-800', 'Acceptée'],
                        'in_progress' => ['bg-purple-100 text-purple-800', 'En cours'],
                        'completed' => ['bg-green-100 text-green-800', 'Terminée'],
                        'rejected' => ['bg-red-100 text-red-800', 'Rejetée'],
                        'cancelled' => ['bg-slate-200 text-slate-800', 'Annulée'],
                    ];
                    $badge = $badges[$serviceRequest->status] ?? ['bg-gray-100 text-gray-800', $serviceRequest->status];
                @endphp
                <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium {{ $badge[0] }}">
                    Statut: {{ $badge[1] }}
                </span>
            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Request Details --}}
            <div>
                <h3 class="text-lg font-semibold text-slate-800 mb-4 border-b border-slate-100 pb-2" style="font-family: Outfit, sans-serif">Détails de l'intervention</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Date souhaitée</p>
                        <p class="font-medium text-slate-900">{{ \Carbon\Carbon::parse($serviceRequest->requested_date)->format('d/m/Y') }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Adresse</p>
                        <p class="font-medium text-slate-900">{{ $serviceRequest->address }}</p>
                    </div>

                    @if(in_array($serviceRequest->status, ['accepted', 'in_progress', 'completed']) && $serviceRequest->estimated_price !== null)
                    <div>
                        <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Prix estimé</p>
                        <p class="font-medium text-slate-900">{{ number_format($serviceRequest->estimated_price, 2, ',', ' ') }} TND</p>
                    </div>
                    @endif

                    @if($serviceRequest->equipment)
                    <div>
                        <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Équipement concerné</p>
                        <p class="font-medium text-amber-600">{{ $serviceRequest->equipment->name }} {{ $serviceRequest->equipment->brand ? '('.$serviceRequest->equipment->brand.')' : '' }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Client Details --}}
            <div>
                <h3 class="text-lg font-semibold text-slate-800 mb-4 border-b border-slate-100 pb-2" style="font-family: Outfit, sans-serif">Client</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Nom</p>
                        <p class="font-medium text-slate-900">{{ $serviceRequest->user->name ?? 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Email</p>
                        <p class="font-medium text-slate-900">{{ $serviceRequest->user->email ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            {{-- Description Full Width --}}
            <div class="md:col-span-2 mt-2">
                <h3 class="text-lg font-semibold text-slate-800 mb-3 border-b border-slate-100 pb-2" style="font-family: Outfit, sans-serif">Description de la demande</h3>
                <p class="text-slate-700 whitespace-pre-wrap">{{ $serviceRequest->description }}</p>
            </div>

            {{-- AI Pre-Diagnosis Card (if available) --}}
            @if($serviceRequest->ai_pre_diagnosis)
                @php $ai = $serviceRequest->ai_pre_diagnosis; @endphp
                <div class="md:col-span-2 mt-2 bg-amber-50/60 border border-amber-200 rounded-xl p-5 shadow-sm space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-amber-200 pb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">✨</span>
                            <h4 class="font-bold text-slate-900 text-sm" style="font-family: Outfit, sans-serif">
                                Pré-diagnostic technique assisté par IA (Indicatif)
                            </h4>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(!empty($ai['severity']))
                                @php
                                    $severityBadges = [
                                        'faible' => 'bg-emerald-100 text-emerald-800',
                                        'moyenne' => 'bg-blue-100 text-blue-800',
                                        'elevee' => 'bg-orange-100 text-orange-800',
                                        'urgente' => 'bg-red-100 text-red-800',
                                    ];
                                    $sBadge = $severityBadges[$ai['severity']] ?? 'bg-slate-100 text-slate-800';
                                @endphp
                                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $sBadge }}">
                                    Gravité : {{ strtoupper($ai['severity']) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Hypothèse de panne</span>
                            <p class="font-bold text-slate-900 mt-0.5">{{ $ai['diagnostic_probable'] ?? 'Non spécifiée' }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Durée estimée</span>
                            <p class="font-medium text-slate-800 mt-0.5">{{ $ai['estimated_duration'] ?? '1h à 2h' }}</p>
                        </div>
                        @if(!empty($ai['probable_components']))
                            <div class="sm:col-span-2">
                                <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Composants probables</span>
                                <div class="flex flex-wrap gap-1.5 mt-1">
                                    @foreach($ai['probable_components'] as $comp)
                                        <span class="bg-white border border-slate-200 px-2 py-0.5 rounded text-xs text-slate-700">{{ $comp }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if(!empty($ai['safety_advice']))
                            <div class="sm:col-span-2 bg-red-50 border border-red-200 rounded-lg p-3 text-xs text-red-800">
                                <strong>⚠️ Consigne de sécurité communiquée au client :</strong> {{ $ai['safety_advice'] }}
                            </div>
                        @endif
                    </div>

                    @if(!empty($ai['indicative_cost_range']))
                        <div class="bg-white border border-amber-200 rounded-lg p-3 text-xs text-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <span class="text-slate-500">Fourchette indicative communiquée :</span>
                                <strong class="text-amber-800 ml-1">{{ $ai['indicative_cost_range'] }}</strong>
                            </div>
                            <span class="text-[11px] text-slate-400 italic">Vous fixez librement votre devis officiel ci-dessous.</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Provider Actions --}}
        @if(in_array($serviceRequest->status, ['pending', 'accepted', 'in_progress']))
            <div class="p-6 border-t border-slate-200 bg-slate-50 flex flex-wrap gap-3">
                @if($serviceRequest->status === 'pending')
                    <form action="{{ route('provider.service-requests.accept', $serviceRequest) }}" method="POST" class="flex flex-col gap-3" onsubmit="return confirm('Voulez-vous accepter cette demande ?');">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label for="estimated_price" class="block text-sm font-medium text-slate-700 mb-1" style="font-family: Outfit, sans-serif">Prix estimé (TND)</label>
                            <input type="number" name="estimated_price" id="estimated_price" step="0.01" min="0" required value="{{ old('estimated_price') }}" class="w-full sm:w-auto border-slate-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @error('estimated_price')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors shadow-sm" style="font-family: Outfit, sans-serif">
                                Accepter
                            </button>
                        </div>
                    </form>

                    <form action="{{ route('provider.service-requests.reject', $serviceRequest) }}" method="POST" class="flex items-end" onsubmit="return confirm('Voulez-vous rejeter cette demande ?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors shadow-sm" style="font-family: Outfit, sans-serif">
                            Rejeter
                        </button>
                    </form>
                @elseif($serviceRequest->status === 'accepted')
                    <form action="{{ route('provider.service-requests.start', $serviceRequest) }}" method="POST" class="inline-block" onsubmit="return confirm('Voulez-vous démarrer cette intervention ?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition-colors shadow-sm" style="font-family: Outfit, sans-serif">
                            Démarrer l'intervention
                        </button>
                    </form>
                @elseif($serviceRequest->status === 'in_progress')
                    <form action="{{ route('provider.service-requests.complete', $serviceRequest) }}" method="POST" class="inline-block" onsubmit="return confirm('Voulez-vous marquer cette intervention comme terminée ?');">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors shadow-sm" style="font-family: Outfit, sans-serif">
                            Terminer l'intervention
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
