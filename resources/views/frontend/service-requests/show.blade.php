@extends('layouts.frontend')

@section('title', $serviceRequest->title . ' - SolarShare')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('service-requests.index') }}" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-amber-600 transition-colors" style="font-family: Outfit, sans-serif">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Retour aux demandes
        </a>

        @if($serviceRequest->status === 'pending')
            <form action="{{ route('service-requests.destroy', $serviceRequest) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment annuler cette demande ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-lg border border-red-200 text-red-600 font-medium hover:bg-red-50 transition-colors text-sm" style="font-family: Outfit, sans-serif">
                    Annuler la demande
                </button>
            </form>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
            {{ session('success') }}
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

                    @if($serviceRequest->equipment)
                    <div>
                        <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Équipement concerné</p>
                        <p class="font-medium text-amber-600">{{ $serviceRequest->equipment->name }} {{ $serviceRequest->equipment->brand ? '('.$serviceRequest->equipment->brand.')' : '' }}</p>
                    </div>
                    @endif

                    <div>
                        <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Prix estimé</p>
                        <p class="font-medium text-slate-900">{{ $serviceRequest->estimated_price ? number_format((float) $serviceRequest->estimated_price, 2) . ' TND' : 'En attente d\'évaluation' }}</p>
                    </div>
                </div>
            </div>

            {{-- Provider Details --}}
            <div>
                <h3 class="text-lg font-semibold text-slate-800 mb-4 border-b border-slate-100 pb-2" style="font-family: Outfit, sans-serif">Prestataire technique</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Nom</p>
                        <p class="font-medium text-slate-900">{{ $serviceRequest->serviceProvider->user->name ?? 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Spécialité</p>
                        <p class="font-medium text-slate-900">{{ $serviceRequest->serviceProvider->specialty }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Tarif horaire</p>
                        <p class="font-medium text-slate-900">{{ $serviceRequest->serviceProvider->hourly_rate ? number_format((float) $serviceRequest->serviceProvider->hourly_rate, 2) . ' TND/h' : 'Sur devis' }}</p>
                    </div>

                    @if($serviceRequest->serviceProvider->phone)
                    <div>
                        <p class="text-sm text-slate-500 mb-1" style="font-family: Outfit, sans-serif">Contact</p>
                        <p class="font-medium text-slate-900">{{ $serviceRequest->serviceProvider->phone }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Description Full Width --}}
            <div class="md:col-span-2 mt-2">
                <h3 class="text-lg font-semibold text-slate-800 mb-3 border-b border-slate-100 pb-2" style="font-family: Outfit, sans-serif">Description de la demande</h3>
                <p class="text-slate-700 whitespace-pre-wrap">{{ $serviceRequest->description }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
