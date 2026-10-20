@extends('layouts.backend')

@section('title', 'Réclamations')

@section('content')
<div>
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Réclamations</h1>
        <p class="text-gray-600 mt-2">Gérez toutes les réclamations des clients</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('admin.reclamations.index') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="status" class="block text-sm font-semibold text-gray-900 mb-2">Statut</label>
                <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Tous les statuts --</option>
                    <option value="en_attente" @selected(request('status') === 'en_attente')>En attente</option>
                    <option value="en_cours" @selected(request('status') === 'en_cours')>En cours</option>
                    <option value="approuvee" @selected(request('status') === 'approuvee')>Approuvée</option>
                    <option value="rejetee" @selected(request('status') === 'rejetee')>Rejetée</option>
                    <option value="fermee" @selected(request('status') === 'fermee')>Fermée</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg">
                    Filtrer
                </button>
                <a href="{{ route('admin.reclamations.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold py-2 px-6 rounded-lg">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <!-- Reclamations Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Équipement</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Incidents</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($reclamations as $reclamation)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            #{{ $reclamation->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <a href="{{ route('admin.users.show', $reclamation->user->id) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $reclamation->user->name }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ Str::limit($reclamation->title, 50) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $reclamation->reservation->getEquipmentLabelAttribute() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
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
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses[$reclamation->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $statusLabels[$reclamation->status] ?? $reclamation->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ count($reclamation->incidents) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex gap-2">
                                <a href="{{ route('admin.reclamations.show', $reclamation->id) }}" class="text-blue-600 hover:text-blue-800">
                                    Voir
                                </a>
                                @if ($reclamation->status !== 'approuvee' && $reclamation->status !== 'rejetee')
                                    <a href="{{ route('admin.reclamations.approve.form', $reclamation->id) }}" class="text-green-600 hover:text-green-800">
                                        Approuver
                                    </a>
                                    <a href="{{ route('admin.reclamations.reject.form', $reclamation->id) }}" class="text-red-600 hover:text-red-800">
                                        Refuser
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-600">
                            Aucune réclamation trouvée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if ($reclamations->hasPages())
        <div class="mt-8">
            {{ $reclamations->links() }}
        </div>
    @endif
</div>
@endsection
