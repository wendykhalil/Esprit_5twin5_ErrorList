@extends('layouts.frontend')

@section('title', 'Contrat ' . $contract->contract_number)

@section('content')
<div class="container mx-auto py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header with actions -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $contract->contract_number }}</h1>
                <p class="text-gray-600">
                    Contrat de location pour <strong>{{ $contract->equipment->name ?? $contract->equipment->title }}</strong>
                </p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-2">
                <a href="{{ route('contracts.print', $contract) }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                    Imprimer
                </a>
                <a href="{{ route('contracts.download', $contract) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Télécharger
                </a>
                <a href="{{ route('contracts.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Retour
                </a>
            </div>
        </div>

        <!-- Acceptance Status Alert -->
        @if($contract->isAccepted())
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3">
                <div class="shrink-0 text-green-600 text-xl">✓</div>
                <div>
                    <p class="text-green-700 font-semibold">Contrat Accepté</p>
                    <p class="text-green-600 text-sm mt-1">
                        Vous avez accepté ce contrat le <strong>{{ $contract->accepted_at->format('d/m/Y à H:i') }}</strong>
                    </p>
                </div>
            </div>
        @else
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-yellow-700 font-semibold">⚠ Contrat en Attente d'Acceptation</p>
                <p class="text-yellow-600 text-sm mt-1">
                    Veuillez lire attentivement les conditions générales ci-dessous, puis accepter ce contrat pour créer votre livraison.
                </p>
            </div>
        @endif

        <!-- Contract Details Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8">
                <!-- Left Column -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b pb-2">Informations du Contrat</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Numéro de Contrat</label>
                            <p class="text-gray-900 font-mono">{{ $contract->contract_number }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Statut</label>
                            @if($contract->status === 'active')
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium mt-1 bg-green-100 text-green-800">Actif</span>
                            @elseif($contract->status === 'completed')
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium mt-1 bg-blue-100 text-blue-800">Terminé</span>
                            @else
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium mt-1 bg-red-100 text-red-800">Annulé</span>
                            @endif
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Statut d'Acceptation</label>
                            @if($contract->isAccepted())
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium mt-1 bg-purple-100 text-purple-800">
                                    ✓ Accepté le {{ $contract->accepted_at->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="inline-block px-3 py-1 rounded-full text-sm font-medium mt-1 bg-yellow-100 text-yellow-800">
                                    En attente d'acceptation
                                </span>
                            @endif
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Date de Création</label>
                            <p class="text-gray-900">{{ $contract->created_at->format('d/m/Y à H:i') }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Dernière Mise à Jour</label>
                            <p class="text-gray-900">{{ $contract->updated_at->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b pb-2">Détails de la Location</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Équipement</label>
                            <p class="text-gray-900 font-semibold">{{ $contract->equipment->name ?? $contract->equipment->title }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Locataire</label>
                            <p class="text-gray-900">{{ $contract->user->name ?? $contract->user->email }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Période de Location</label>
                            <p class="text-gray-900">
                                {{ $contract->start_date->format('d/m/Y') }} au {{ $contract->end_date->format('d/m/Y') }}
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                ({{ $contract->start_date->diffInDays($contract->end_date) }} jours)
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Montant Total</label>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($contract->amount, 2, ',', ' ') }} TND</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms and Conditions Section -->
            <div class="border-t p-8 bg-gray-50">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Conditions Générales</h2>
                <div class="bg-white p-6 rounded border border-gray-200 text-gray-700 text-sm leading-relaxed whitespace-pre-wrap">
                    {{ $contract->terms_and_conditions }}
                </div>
            </div>

            <!-- Actions and Related Delivery Section -->
            <div class="border-t p-8 bg-white">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Acceptance Action -->
                    <div>
                        @if(!$contract->isAccepted())
                            <form method="POST" action="{{ route('contracts.accept', $contract) }}" class="flex flex-col gap-2">
                                @csrf
                                <p class="text-gray-600 text-sm mb-3">
                                    En acceptant ce contrat, vous confirmez avoir lu et accepté les conditions générales.
                                </p>
                                <button type="submit" class="w-full px-4 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                                    ✓ Accepter le Contrat
                                </button>
                            </form>
                        @else
                            <div>
                                <p class="text-green-600 font-semibold mb-3">
                                    ✓ Contrat Accepté
                                </p>
                                <button disabled class="w-full px-4 py-3 bg-green-600 text-white font-semibold rounded-lg opacity-50 cursor-not-allowed">
                                    ✓ Contrat Accepté
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- Related Delivery Information -->
                    <div>
                        <p class="text-gray-600 text-sm font-semibold mb-3">Livraison Associée</p>
                        @if($contract->reservation->delivery)
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <p class="text-blue-900 font-semibold mb-2">
                                    📦 Livraison #{{ $contract->reservation->delivery->id }}
                                </p>
                                <p class="text-blue-700 text-sm mb-3">
                                    Statut: <span class="font-semibold">{{ $contract->reservation->delivery->status }}</span>
                                </p>
                                <a href="{{ route('deliveries.show', $contract->reservation->delivery) }}" class="inline-block px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                                    Voir Livraison
                                </a>
                            </div>
                        @else
                            <div class="bg-gray-100 p-4 rounded-lg border border-gray-300">
                                <p class="text-gray-600 text-sm">
                                    @if($contract->isAccepted())
                                        La livraison est en cours de création...
                                    @else
                                        La livraison sera créée après acceptation du contrat.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Navigation -->
                <div class="mt-6 flex flex-col md:flex-row gap-2">
                    <a href="{{ route('reservations.show', $contract->reservation) }}" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center">
                        Voir la Réservation
                    </a>
                    <a href="{{ route('contracts.index') }}" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition text-center">
                        Mes Contrats
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
