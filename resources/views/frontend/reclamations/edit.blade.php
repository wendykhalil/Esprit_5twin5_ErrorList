@extends('layouts.frontend')

@section('title', 'Modifier Réclamation #' . $reclamation->id)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Modifier Réclamation</h1>
            <p class="text-gray-600 mt-2">Réclamation #{{ $reclamation->id }} - Statut: <span class="font-semibold">En attente</span></p>
        </div>

        <!-- Messages -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <p class="font-semibold mb-2">Erreurs de validation :</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('reclamations.update', $reclamation->id) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-8">
            @csrf
            @method('PUT')

            <!-- Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-semibold text-gray-900 mb-2">Titre</label>
                <input type="text" name="title" id="title" class="w-full px-4 py-2 border border-gray-300] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror"
                    value="{{ old('title', $reclamation->title) }}" required>
                @error('title')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-semibold text-gray-900 mb-2">Description</label>
                <textarea name="description" id="description" class="w-full px-4 py-2 border border-gray-300] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                    rows="5" required>{{ old('description', $reclamation->description) }}</textarea>
                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <hr class="my-8">

            <!-- Existing Incidents Section -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Incidents Existants ({{ count($reclamation->incidents) }})</h2>
                
                @forelse ($reclamation->incidents as $incident)
                    <div class="border border-gray-200 rounded-lg p-4 mb-4" x-data="{ removeIncident: false }">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900">{{ $incident->equipment->name ?? 'Équipement #' . $incident->equipment_id }}</h3>
                                <p class="text-sm text-gray-600 mt-1">
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
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">Remboursement proposé</p>
                                <p class="text-lg font-semibold text-green-600">{{ number_format($incident->proposed_refund_amount, 2, ',', ' ') }} TND</p>
                            </div>
                        </div>

                        <p class="text-gray-700 mb-3">{{ $incident->description }}</p>
                        
                        @if ($incident->damage_percentage)
                            <p class="text-sm text-gray-600 mb-3">Pourcentage de dommages : {{ $incident->damage_percentage }}%</p>
                        @endif

                        @if ($incident->hasAttachment())
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <p class="text-sm font-semibold text-gray-700 mb-2">📸 Preuve Jointe</p>
                                <img src="{{ $incident->getAttachmentUrl() }}" alt="Evidence photo" class="max-w-xs h-auto rounded-lg shadow-sm mb-2">
                            </div>
                        @endif

                        <!-- Remove Button -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="incidents_to_remove[]" value="{{ $incident->id }}" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm text-red-600 font-semibold">Supprimer cet incident</span>
                            </label>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-600 text-center py-4">Aucun incident existant.</p>
                @endforelse
            </div>

            <hr class="my-8">

            <!-- New Incidents Section -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Ajouter des Incidents</h2>
                <p class="text-sm text-gray-600 mb-4">Vous pouvez ajouter plusieurs incidents pour le même équipement.</p>

                <div id="newIncidents" x-data="{ incidents: [{}] }">
                    <template x-for="(incident, index) in incidents" :key="index">
                        <div class="border border-gray-300 rounded-lg p-4 mb-4 bg-gray-50">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="font-semibold text-gray-900">Incident #<span x-text="index + 1"></span></h3>
                                <button type="button" 
                                    @click="incidents.splice(index, 1)"
                                    class="text-red-600 hover:text-red-800 font-semibold text-sm"
                                    x-show="incidents.length > 1">
                                    Supprimer
                                </button>
                            </div>

                            <!-- Type Probleme -->
                            <div class="mb-4">
                                <label :for="'new_incidents[' + index + '][type_probleme]'" class="block text-sm font-semibold text-gray-700 mb-2">Type de Problème</label>
                                <select :name="'new_incidents[' + index + '][type_probleme]'" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Choisir --</option>
                                    <option value="endommage">Endommagé</option>
                                    <option value="manquant">Manquant</option>
                                    <option value="malfonctionnant">Malfoncti onnant</option>
                                    <option value="autre">Autre</option>
                                </select>
                                @error('new_incidents.*.type_probleme')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Equipment -->
                            <input type="hidden" :name="'new_incidents[' + index + '][equipment_id]'" value="{{ $equipments->first()->id }}">

                            <!-- Description -->
                            <div class="mb-4">
                                <label :for="'new_incidents[' + index + '][description]'" class="block text-sm font-semibold text-gray-700 mb-2">Description du Problème</label>
                                <textarea :name="'new_incidents[' + index + '][description]'" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" required></textarea>
                                @error('new_incidents.*.description')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Damage Percentage -->
                            <div class="mb-4">
                                <label :for="'new_incidents[' + index + '][damage_percentage]'" class="block text-sm font-semibold text-gray-700 mb-2">Pourcentage de Dommages (optionnel)</label>
                                <input type="number" :name="'new_incidents[' + index + '][damage_percentage]'" min="0" max="100" step="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0-100">
                                @error('new_incidents.*.damage_percentage')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Image Upload -->
                            <div class="mb-4">
                                <label :for="'new_incidents[' + index + '][image]'" class="block text-sm font-semibold text-gray-700 mb-2">Photo de Preuve (optionnelle)</label>
                                <input type="file" :name="'new_incidents[' + index + '][image]'" accept="image/jpeg,image/jpg,image/png,image/webp" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-600 mt-1">Formats acceptés: JPG, PNG, WebP. Taille max: 5 MB</p>
                                @error('new_incidents.*.image')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </template>

                    <button type="button"
                        @click="incidents.push({})"
                        class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-semibold py-2 px-4 rounded-lg mb-6">
                        + Ajouter un Incident
                    </button>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg">
                    Mettre à jour
                </button>
                <a href="{{ route('reclamations.show', $reclamation->id) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold py-2 px-6 rounded-lg">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Alpine.js script for incident management -->
<script src="//cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
