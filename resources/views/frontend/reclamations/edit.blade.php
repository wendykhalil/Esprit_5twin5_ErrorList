@extends('layouts.frontend')

@section('title', 'Modifier Réclamation #' . $reclamation->id)

@section('content')
<div class="py-8">
    <div class="max-w-5xl mx-auto px-4">
        <!-- Breadcrumb -->
        <div class="mb-6 flex items-center text-sm text-gray-600">
            <a href="{{ route('reclamations.index') }}" class="text-blue-600 hover:text-blue-800">Réclamations</a>
            <span class="mx-2">/</span>
            <a href="{{ route('reclamations.show', $reclamation->id) }}" class="text-blue-600 hover:text-blue-800">Réclamation #{{ $reclamation->id }}</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900">Modifier</span>
        </div>

        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">Modifier la Réclamation</h1>
                    <p class="text-gray-600">Mettez à jour les détails de votre réclamation et gérez ses incidents.</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 mb-1">Réclamation #{{ $reclamation->id }}</p>
                    <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                        En attente
                    </span>
                </div>
            </div>
        </div>

        <!-- Messages -->
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="font-semibold text-red-900 mb-2">Erreurs de validation :</p>
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="text-red-800 text-sm">• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('reclamations.update', $reclamation->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Reclamation Information Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Informations de la Réclamation</h2>

                <!-- Title -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                    <input type="text" name="title" id="title" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror"
                        value="{{ old('title', $reclamation->title) }}" 
                        placeholder="Décrivez brièvement le problème"
                        required>
                    @error('title')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-0">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description Détaillée</label>
                    <textarea name="description" id="description" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                        rows="6" 
                        placeholder="Expliquez en détail la nature de la réclamation"
                        required>{{ old('description', $reclamation->description) }}</textarea>
                    @error('description')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Existing Incidents Section -->
            @if(count($reclamation->incidents) > 0)
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Incidents Existants ({{ count($reclamation->incidents) }})</h2>
                    
                    <div class="space-y-4">
                        @foreach ($reclamation->incidents as $incident)
                            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-blue-500">
                                <!-- Header -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ $incident->equipment->name ?? 'Équipement #' . $incident->equipment_id }}
                                        </h3>
                                        <p class="text-sm text-gray-600 mt-1">
                                            Type : 
                                            @php
                                                $typeLabels = [
                                                    'endommage' => 'Endommagé',
                                                    'manquant' => 'Manquant',
                                                    'malfonctionnant' => 'Malfoncti onnant',
                                                    'autre' => 'Autre',
                                                ];
                                                $typeColor = match($incident->type_probleme) {
                                                    'endommage' => 'bg-red-100 text-red-800',
                                                    'manquant' => 'bg-orange-100 text-orange-800',
                                                    'malfonctionnant' => 'bg-yellow-100 text-yellow-800',
                                                    'autre' => 'bg-gray-100 text-gray-800',
                                                    default => 'bg-gray-100 text-gray-800',
                                                };
                                            @endphp
                                            <span class="inline-block px-2 py-1 {{ $typeColor }} rounded text-sm font-medium">
                                                {{ $typeLabels[$incident->type_probleme] ?? $incident->type_probleme }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-600 mb-1">Remboursement proposé</p>
                                        <p class="text-lg font-semibold text-green-600">
                                            {{ number_format($incident->proposed_refund_amount, 2, ',', ' ') }} TND
                                        </p>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="mb-4">
                                    <p class="text-gray-700 text-sm">{{ $incident->description }}</p>
                                </div>

                                <!-- Evidence Image -->
                                @if ($incident->hasAttachment())
                                    <div class="mb-4 pt-4 border-t border-gray-200">
                                        <p class="text-xs font-semibold text-gray-700 mb-2">📸 Preuve Jointe</p>
                                        <a href="{{ $incident->getAttachmentUrl() }}" target="_blank" class="inline-block">
                                            <img src="{{ $incident->getAttachmentUrl() }}" alt="Evidence photo" 
                                                class="max-w-xs h-auto rounded-lg shadow-sm hover:shadow-md transition">
                                        </a>
                                    </div>
                                @else
                                    <div class="mb-4 pt-4 border-t border-gray-200">
                                        <p class="text-xs text-gray-600">Aucune photo de preuve jointe</p>
                                    </div>
                                @endif

                                <!-- Delete Checkbox -->
                                <div class="pt-4 border-t border-gray-200">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="incidents_to_remove[]" value="{{ $incident->id }}" 
                                            class="w-4 h-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                        <span class="ml-2 text-sm text-red-600 font-medium">Supprimer cet incident</span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- New Incidents Section -->
            <div class="mb-6">
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Ajouter des Incidents</h2>
                    <p class="text-sm text-gray-600">Vous pouvez ajouter plusieurs incidents pour le même équipement.</p>
                </div>

                <div id="newIncidents" x-data="{ incidents: [{}] }" class="space-y-4">
                    <template x-for="(incident, index) in incidents" :key="index">
                        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-300">
                            <!-- Header -->
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Incident #<span x-text="index + 1"></span>
                                </h3>
                                <button type="button" 
                                    @click="incidents.splice(index, 1)"
                                    class="text-red-600 hover:text-red-800 font-medium text-sm"
                                    x-show="incidents.length > 1">
                                    Supprimer
                                </button>
                            </div>

                            <!-- Type Probleme -->
                            <div class="mb-4">
                                <label :for="'new_incidents[' + index + '][type_probleme]'" class="block text-sm font-medium text-gray-700 mb-2">
                                    Type de Problème *
                                </label>
                                <select :name="'new_incidents[' + index + '][type_probleme]'" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required>
                                    <option value="">-- Choisir un type --</option>
                                    <option value="endommage">Endommagé</option>
                                    <option value="manquant">Manquant</option>
                                    <option value="malfonctionnant">Malfoncti onnant</option>
                                    <option value="autre">Autre</option>
                                </select>
                                @error('new_incidents.*.type_probleme')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Equipment ID (hidden) -->
                            <input type="hidden" :name="'new_incidents[' + index + '][equipment_id]'" value="{{ $equipments->first()->id ?? '' }}">

                            <!-- Description -->
                            <div class="mb-4">
                                <label :for="'new_incidents[' + index + '][description]'" class="block text-sm font-medium text-gray-700 mb-2">
                                    Description du Problème *
                                </label>
                                <textarea :name="'new_incidents[' + index + '][description]'" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                    rows="4" 
                                    placeholder="Décrivez le problème rencontré avec cet équipement"
                                    required></textarea>
                                @error('new_incidents.*.description')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Image Upload -->
                            <div class="mb-0">
                                <label :for="'new_incidents[' + index + '][image]'" class="block text-sm font-medium text-gray-700 mb-2">
                                    Photo de Preuve (optionnelle)
                                </label>
                                <input type="file" :name="'new_incidents[' + index + '][image]'" 
                                    accept="image/jpeg,image/jpg,image/png,image/webp" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <p class="text-xs text-gray-600 mt-1">Formats acceptés: JPG, PNG, WebP. Taille max: 5 MB</p>
                                @error('new_incidents.*.image')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </template>

                    <!-- Add Incident Button -->
                    <button type="button"
                        @click="incidents.push({})"
                        class="w-full py-3 border-2 border-dashed border-blue-300 rounded-lg text-blue-600 hover:bg-blue-50 font-semibold transition">
                        + Ajouter un Incident
                    </button>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-4 pt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                    Enregistrer les Modifications
                </button>
                <a href="{{ route('reclamations.show', $reclamation->id) }}" class="px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold rounded-lg transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Alpine.js script for dynamic incident management -->
<script src="//cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
