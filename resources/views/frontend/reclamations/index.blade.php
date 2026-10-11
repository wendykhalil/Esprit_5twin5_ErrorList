@extends('layouts.frontend')

@section('title', 'Mes Réclamations')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Mes Réclamations</h1>
                <p class="text-gray-600 mt-2">Consultez et gérez vos réclamations</p>
            </div>
            <a href="{{ route('reclamations.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">
                + Nouvelle Réclamation
            </a>
        </div>

        <!-- Messages -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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

        <!-- Reclamations List -->
        @forelse ($reclamations as $reclamation)
            <div class="bg-white rounded-lg shadow mb-4 p-6 hover:shadow-lg transition">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <a href="{{ route('reclamations.show', $reclamation->id) }}" class="hover:text-blue-600">
                                {{ $reclamation->title }}
                            </a>
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Réservation : <span class="font-semibold">{{ $reclamation->reservation->getEquipmentLabelAttribute() }}</span>
                        </p>
                    </div>
                    <div class="text-right">
                        <!-- Status badge -->
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
                        <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold {{ $statusClasses[$reclamation->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ $statusLabels[$reclamation->status] ?? $reclamation->status }}
                        </span>
                    </div>
                </div>

                <p class="text-gray-700 mb-4">{{ Str::limit($reclamation->description, 150) }}</p>

                <div class="flex justify-between items-center text-sm">
                    <div class="text-gray-600">
                        <p>{{ count($reclamation->incidents) }} incident(s)</p>
                        @if ($reclamation->refund_amount)
                            <p class="font-semibold text-green-600">Remboursement : {{ number_format($reclamation->refund_amount, 2, ',', ' ') }} €</p>
                        @endif
                    </div>
                    <div class="text-gray-500">
                        {{ $reclamation->created_at->format('d/m/Y à H:i') }}
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('reclamations.show', $reclamation->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                        Voir les détails →
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <div class="text-gray-400 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucune réclamation</h3>
                <p class="text-gray-600 mb-6">Vous n'avez pas encore créé de réclamation.</p>
                <a href="{{ route('reclamations.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg inline-block">
                    Créer une réclamation
                </a>
            </div>
        @endforelse

        <!-- Pagination -->
        @if ($reclamations->hasPages())
            <div class="mt-8">
                {{ $reclamations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
