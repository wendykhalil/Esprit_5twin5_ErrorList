@extends('layouts.backend')

@section('title', 'Contrat ' . $contract->contract_number)

@section('content')
<div class="container mx-auto py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header with actions -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $contract->contract_number }}</h1>
                <p class="text-gray-600">
                    Contrat pour <strong>{{ $contract->equipment->name ?? $contract->equipment->title }}</strong>
                    de <strong>{{ $contract->user->name }}</strong>
                </p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-2">
                <a href="{{ route('admin.contracts.print', $contract) }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                    Imprimer
                </a>
                <a href="{{ route('admin.contracts.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Retour
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content (2 columns) -->
            <div class="lg:col-span-2">
                <!-- Contract Details Card -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b pb-2">Informations du Contrat</h2>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Numéro de Contrat</label>
                            <p class="text-gray-900 font-mono text-lg mt-1">{{ $contract->contract_number }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Statut</label>
                            <div class="mt-1">
                                @if($contract->status === 'active')
                                    <span class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        Actif
                                    </span>
                                @elseif($contract->status === 'completed')
                                    <span class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        Terminé
                                    </span>
                                @else
                                    <span class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        Annulé
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Date de Création</label>
                            <p class="text-gray-900 mt-1">{{ $contract->created_at->format('d/m/Y \à H:i') }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Dernière Mise à Jour</label>
                            <p class="text-gray-900 mt-1">{{ $contract->updated_at->format('d/m/Y \à H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Location Details -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b pb-2">Détails de la Location</h2>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Équipement</label>
                                <p class="text-gray-900 font-semibold mt-1">{{ $contract->equipment->name ?? $contract->equipment->title }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500">Catégorie</label>
                                <p class="text-gray-900 mt-1">
                                    @if($contract->equipment->category)
                                        {{ $contract->equipment->category->name }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Description</label>
                            <p class="text-gray-900 mt-1">
                                @if($contract->equipment->description)
                                    {{ $contract->equipment->description }}
                                @else
                                    Non disponible
                                @endif
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500">Date de Début</label>
                                <p class="text-gray-900 mt-1">{{ $contract->start_date->format('d/m/Y') }}</p>
                            </div>

                            <div>
                                <label class="text-sm font-medium text-gray-500">Date de Fin</label>
                                <p class="text-gray-900 mt-1">{{ $contract->end_date->format('d/m/Y') }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Durée</label>
                            <p class="text-gray-900 mt-1">{{ $contract->start_date->diffInDays($contract->end_date) }} jours</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Montant Total</label>
                            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($contract->amount, 2, ',', ' ') }} €</p>
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b pb-2">Conditions Générales</h2>
                    <div class="bg-gray-50 p-4 rounded border border-gray-200 text-gray-700 text-sm leading-relaxed whitespace-pre-wrap">
                        {{ $contract->terms_and_conditions }}
                    </div>
                </div>
            </div>

            <!-- Sidebar (1 column) -->
            <div>
                <!-- Renter Information -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Locataire</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nom</label>
                            <p class="text-gray-900 font-semibold mt-1">{{ $contract->user->name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Email</label>
                            <p class="text-gray-900 mt-1">
                                <a href="mailto:{{ $contract->user->email }}" class="text-blue-600 hover:underline">
                                    {{ $contract->user->email }}
                                </a>
                            </p>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-500">Rôle</label>
                            <p class="text-gray-900 mt-1">{{ ucfirst($contract->user->role) }}</p>
                        </div>

                        <a href="{{ route('admin.users.show', $contract->user) }}" class="block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center">
                            Voir le Profil
                        </a>
                    </div>
                </div>

                <!-- Reservation Link -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Réservation</h2>
                    
                    <p class="text-sm text-gray-600 mb-3">
                        Numéro: <strong>#{{ $contract->reservation->id }}</strong>
                    </p>

                    <p class="text-sm text-gray-600 mb-3">
                        Statut: <strong>{{ ucfirst($contract->reservation->statut) }}</strong>
                    </p>

                    <a href="{{ route('admin.reservations.show', $contract->reservation) }}" class="block px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition text-center">
                        Voir la Réservation
                    </a>
                </div>

                <!-- Status Update Form -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Modifier le Statut</h2>
                    
                    <form action="{{ route('admin.contracts.updateStatus', $contract) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nouveau Statut</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="active" {{ $contract->status === 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="completed" {{ $contract->status === 'completed' ? 'selected' : '' }}>Terminé</option>
                                <option value="cancelled" {{ $contract->status === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            Mettre à Jour
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
