@extends('layouts.frontend')

@section('title', 'Mes Contrats')

@section('content')
<div class="container mx-auto py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Mes Contrats de Location</h1>
            <p class="text-gray-600">Gérez et consultez tous vos contrats de location d'équipements.</p>
        </div>

        <!-- Alert Messages -->
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-red-700 font-semibold">Une erreur est survenue</p>
                <ul class="text-red-600 mt-2">
                    @foreach($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Contracts List -->
        @forelse($contracts as $contract)
            <div class="bg-white rounded-lg shadow mb-4 overflow-hidden hover:shadow-lg transition">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between p-6">
                    <div class="flex-1 mb-4 md:mb-0">
                        <div class="flex items-center mb-2">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $contract->contract_number }}</h3>
                            @if($contract->status === 'active')
                                <span class="ml-3 px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Actif</span>
                            @elseif($contract->status === 'completed')
                                <span class="ml-3 px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">Terminé</span>
                            @else
                                <span class="ml-3 px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Annulé</span>
                            @endif
                            <!-- Acceptance Status Badge -->
                            @if($contract->isAccepted())
                                <span class="ml-2 px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800 flex items-center gap-1">
                                    ✓ Accepté
                                </span>
                            @else
                                <span class="ml-2 px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    En attente
                                </span>
                            @endif
                        </div>
                        <p class="text-gray-600 text-sm mb-2">
                            <strong>Équipement:</strong> {{ $contract->equipment->name ?? $contract->equipment->title ?? 'Non disponible' }}
                        </p>
                        <p class="text-gray-600 text-sm mb-2">
                            <strong>Période:</strong> {{ $contract->start_date->format('d/m/Y') }} - {{ $contract->end_date->format('d/m/Y') }}
                        </p>
                        <p class="text-gray-600 text-sm">
                            <strong>Montant:</strong> {{ number_format($contract->amount, 2, ',', ' ') }} TND
                            <strong class="ml-4">Créé le:</strong> {{ $contract->created_at->format('d/m/Y à H:i') }}
                        </p>
                        <!-- Show acceptance date if accepted -->
                        @if($contract->isAccepted())
                            <p class="text-green-600 text-sm mt-2">
                                <strong>Accepté le:</strong> {{ $contract->accepted_at->format('d/m/Y à H:i') }}
                            </p>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('contracts.show', $contract) }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                            Consulter
                        </a>
                        <a href="{{ route('contracts.print', $contract) }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition text-sm font-medium">
                            Imprimer
                        </a>
                        <a href="{{ route('contracts.download', $contract) }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition text-sm font-medium">
                            Télécharger
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <p class="text-gray-500 text-lg mb-4">Aucun contrat pour le moment</p>
                <p class="text-gray-400">Les contrats seront générés automatiquement après confirmation de votre paiement.</p>
                <a href="{{ route('reservations.index') }}" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Voir mes réservations
                </a>
            </div>
        @endforelse

        <!-- Pagination -->
        @if($contracts->count())
            <div class="mt-8">
                {{ $contracts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
