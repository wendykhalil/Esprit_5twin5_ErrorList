@extends('layouts.backend')

@section('title', 'Gestion des Contrats')

@section('content')
<div class="container mx-auto py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestion des Contrats</h1>
            <p class="text-gray-600">Consultez et gérez tous les contrats de location de la plateforme.</p>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <p class="text-green-700">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form method="GET" action="{{ route('admin.contracts.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rechercher par N° de Contrat</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="CONT-2026-..." 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Tous les statuts --</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Terminé</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                    </select>
                </div>

                <!-- User Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Utilisateur</label>
                    <input type="text" name="user_id" value="{{ request('user_id') }}" placeholder="ID Utilisateur" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Filtrer
                    </button>
                    <a href="{{ route('admin.contracts.index') }}" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition text-center">
                        Réinitialiser
                    </a>
                </div>
            </form>
        </div>

        <!-- Contracts Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">N° de Contrat</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Locataire</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Équipement</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Période</th>
                        <th class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Montant</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Statut</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($contracts as $contract)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-mono text-gray-900">
                                <a href="{{ route('admin.contracts.show', $contract) }}" class="text-blue-600 hover:underline">
                                    {{ $contract->contract_number }}
                                </a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $contract->user->name }}<br>
                                <span class="text-xs text-gray-500">{{ $contract->user->email }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $contract->equipment->name ?? $contract->equipment->title }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $contract->start_date->format('d/m/Y') }}<br>
                                <span class="text-xs text-gray-500">au {{ $contract->end_date->format('d/m/Y') }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">
                                {{ number_format($contract->amount, 2, ',', ' ') }} €
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($contract->status === 'active')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Actif
                                    </span>
                                @elseif($contract->status === 'completed')
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Terminé
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Annulé
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('admin.contracts.show', $contract) }}" class="text-blue-600 hover:underline font-medium">
                                    Consulter
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                Aucun contrat trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($contracts->count())
            <div class="mt-6">
                {{ $contracts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
