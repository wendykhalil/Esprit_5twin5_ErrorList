
@extends('layouts.backend')

@section('title', 'Ajouter un guide - SolarShare Admin')

@section('content')

<div class="py-6" style="font-family: Outfit, sans-serif">
    <div class="max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 mb-2">
                    Ajouter un guide d'utilisation
                </h1>
                <p class="text-slate-500 text-sm">
                    Créez un guide pour accompagner les utilisateurs
                    dans l'installation et l'utilisation d'un équipement.
                </p>
            </div>

            <a href="{{ route('admin.equipment-guides.index') }}"
               class="shrink-0 px-4 py-2 border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-white transition-colors">
                ← Retour à la liste
            </a>
        </div>

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-5">
                <p class="font-semibold text-red-800 mb-2">
                    Veuillez corriger les erreurs suivantes :
                </p>
                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">

            <form method="POST"
                  action="{{ route('admin.equipment-guides.store') }}"
                  class="space-y-6">

                @csrf

                {{-- Equipment --}}
                <div>
                    <label for="equipment_id"
                           class="block text-sm font-semibold text-slate-700 mb-2">
                        Équipement associé <span class="text-red-500">*</span>
                    </label>

                    <select id="equipment_id"
                            name="equipment_id"
                            required
                            class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-amber-400">
                        <option value="">Sélectionner un équipement</option>

                        @foreach($equipments as $equipment)
                            <option value="{{ $equipment->id }}"
                                    @selected(old('equipment_id') == $equipment->id)>
                                {{ $equipment->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('equipment_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Title --}}
                <div>
                    <label for="title"
                           class="block text-sm font-semibold text-slate-700 mb-2">
                        Titre du guide <span class="text-red-500">*</span>
                    </label>

                    <input type="text"
                           id="title"
                           name="title"
                           value="{{ old('title') }}"
                           required
                           maxlength="255"
                           placeholder="Ex : Comment installer un panneau solaire portable"
                           class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-400">

                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Usage context --}}
                <div>
                    <label for="usage_context"
                           class="block text-sm font-semibold text-slate-700 mb-2">
                        Contexte d'utilisation <span class="text-red-500">*</span>
                    </label>

                    <input type="text"
                           id="usage_context"
                           name="usage_context"
                           value="{{ old('usage_context') }}"
                           required
                           maxlength="255"
                           placeholder="Ex : Camping, maison, agriculture..."
                           class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-400">

                    @error('usage_context')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Instructions --}}
                <div>
                    <label for="instructions"
                           class="block text-sm font-semibold text-slate-700 mb-2">
                        Instructions d'utilisation <span class="text-red-500">*</span>
                    </label>

                    <textarea id="instructions"
                              name="instructions"
                              rows="7"
                              required
                              minlength="20"
                              placeholder="1. Préparer l'équipement&#10;2. Installer les composants&#10;3. Vérifier les connexions&#10;4. Mettre en marche..."
                              class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-400">{{ old('instructions') }}</textarea>

                    <p class="text-xs text-slate-500 mt-1">
                        Décrivez les étapes d'utilisation de manière claire.
                    </p>

                    @error('instructions')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Safety precautions --}}
                <div>
                    <label for="safety_precautions"
                           class="block text-sm font-semibold text-slate-700 mb-2">
                        Précautions de sécurité
                    </label>

                    <textarea id="safety_precautions"
                              name="safety_precautions"
                              rows="4"
                              placeholder="Ex : Éviter l'humidité, vérifier les câbles, ne pas dépasser la puissance maximale..."
                              class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-400">{{ old('safety_precautions') }}</textarea>

                    @error('safety_precautions')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Difficulty and status --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div>
                        <label for="difficulty_level"
                               class="block text-sm font-semibold text-slate-700 mb-2">
                            Niveau de difficulté <span class="text-red-500">*</span>
                        </label>

                        <select id="difficulty_level"
                                name="difficulty_level"
                                required
                                class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-amber-400">
                            <option value="beginner" @selected(old('difficulty_level', 'beginner') === 'beginner')>
                                Débutant
                            </option>
                            <option value="intermediate" @selected(old('difficulty_level') === 'intermediate')>
                                Intermédiaire
                            </option>
                            <option value="advanced" @selected(old('difficulty_level') === 'advanced')>
                                Avancé
                            </option>
                        </select>

                        @error('difficulty_level')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status"
                               class="block text-sm font-semibold text-slate-700 mb-2">
                            Statut du guide <span class="text-red-500">*</span>
                        </label>

                        <select id="status"
                                name="status"
                                required
                                class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white focus:ring-2 focus:ring-amber-400">
                            <option value="draft" @selected(old('status', 'draft') === 'draft')>
                                Brouillon
                            </option>
                            <option value="pending" @selected(old('status') === 'pending')>
                                En attente
                            </option>
                            <option value="published" @selected(old('status') === 'published')>
                                Publié
                            </option>
                        </select>

                        @error('status')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Video URL --}}
                <div>
                    <label for="video_url"
                           class="block text-sm font-semibold text-slate-700 mb-2">
                        Lien vidéo (optionnel)
                    </label>

                    <input type="url"
                           id="video_url"
                           name="video_url"
                           value="{{ old('video_url') }}"
                           maxlength="255"
                           placeholder="https://www.youtube.com/watch?v=..."
                           class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-amber-400">

                    @error('video_url')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-slate-200">

                    <a href="{{ route('admin.equipment-guides.index') }}"
                       class="px-6 py-3 border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-50 text-center">
                        Annuler
                    </a>

                    <button type="submit"
                            class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-semibold transition-colors">
                        Enregistrer le guide
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection
