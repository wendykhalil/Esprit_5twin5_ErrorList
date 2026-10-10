
@extends('layouts.backend')

@section('title', 'Modifier un guide - SolarShare Admin')

@section('content')

<div class="py-6" style="font-family: Outfit, sans-serif">
    <div class="max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 mb-2">
                    Modifier le guide
                </h1>

                <p class="text-slate-500 text-sm">
                    Modifiez les informations, les instructions
                    et le statut de publication du guide.
                </p>
            </div>

            <a href="{{ route('admin.equipment-guides.index') }}"
               class="px-4 py-2 border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-white">
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
                  action="{{ route('admin.equipment-guides.update', $equipmentGuide) }}"
                  class="space-y-6">

                @csrf
                @method('PUT')

                {{-- Equipment --}}
                <div>
                    <label for="equipment_id"
                           class="block text-sm font-semibold text-slate-700 mb-2">
                        Équipement associé *
                    </label>

                    <select id="equipment_id"
                            name="equipment_id"
                            required
                            class="w-full rounded-lg border border-slate-200 px-4 py-3">

                        <option value="">Sélectionner un équipement</option>

                        @foreach($equipments as $equipment)
                            <option value="{{ $equipment->id }}"
                                @selected(old('equipment_id', $equipmentGuide->equipment_id) == $equipment->id)>
                                {{ $equipment->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- Title --}}
                <div>
                    <label for="title"
                           class="block text-sm font-semibold text-slate-700 mb-2">
                        Titre du guide *
                    </label>

                    <input type="text"
                           id="title"
                           name="title"
                           required
                           maxlength="255"
                           value="{{ old('title', $equipmentGuide->title) }}"
                           class="w-full rounded-lg border border-slate-200 px-4 py-3">
                </div>

                {{-- Usage context --}}
                <div>
                    <label for="usage_context"
                           class="block text-sm font-semibold text-slate-700 mb-2">
                        Contexte d'utilisation *
                    </label>

                    <input type="text"
                           id="usage_context"
                           name="usage_context"
                           required
                           maxlength="255"
                           value="{{ old('usage_context', $equipmentGuide->usage_context) }}"
                           placeholder="Camping, maison, agriculture..."
                           class="w-full rounded-lg border border-slate-200 px-4 py-3">
                </div>

                {{-- Instructions --}}
                <div>
                    <label for="instructions"
                           class="block text-sm font-semibold text-slate-700 mb-2">
                        Instructions d'utilisation *
                    </label>

                    <textarea id="instructions"
                              name="instructions"
                              rows="7"
                              required
                              minlength="20"
                              class="w-full rounded-lg border border-slate-200 px-4 py-3">{{ old('instructions', $equipmentGuide->instructions) }}</textarea>
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
                              class="w-full rounded-lg border border-slate-200 px-4 py-3">{{ old('safety_precautions', $equipmentGuide->safety_precautions) }}</textarea>
                </div>

                {{-- Difficulty and status --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <div>
                        <label for="difficulty_level"
                               class="block text-sm font-semibold text-slate-700 mb-2">
                            Niveau de difficulté *
                        </label>

                        <select id="difficulty_level"
                                name="difficulty_level"
                                required
                                class="w-full rounded-lg border border-slate-200 px-4 py-3">

                            <option value="beginner"
                                @selected(old('difficulty_level', $equipmentGuide->difficulty_level) === 'beginner')>
                                Débutant
                            </option>

                            <option value="intermediate"
                                @selected(old('difficulty_level', $equipmentGuide->difficulty_level) === 'intermediate')>
                                Intermédiaire
                            </option>

                            <option value="advanced"
                                @selected(old('difficulty_level', $equipmentGuide->difficulty_level) === 'advanced')>
                                Avancé
                            </option>

                        </select>
                    </div>

                    <div>
                        <label for="status"
                               class="block text-sm font-semibold text-slate-700 mb-2">
                            Statut du guide *
                        </label>

                        <select id="status"
                                name="status"
                                required
                                class="w-full rounded-lg border border-slate-200 px-4 py-3">

                            <option value="draft"
                                @selected(old('status', $equipmentGuide->status) === 'draft')>
                                Brouillon
                            </option>

                            <option value="pending"
                                @selected(old('status', $equipmentGuide->status) === 'pending')>
                                En attente
                            </option>

                            <option value="published"
                                @selected(old('status', $equipmentGuide->status) === 'published')>
                                Publié
                            </option>

                        </select>
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
                           maxlength="255"
                           value="{{ old('video_url', $equipmentGuide->video_url) }}"
                           placeholder="https://www.youtube.com/watch?v=..."
                           class="w-full rounded-lg border border-slate-200 px-4 py-3">
                </div>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-slate-200">

                    <a href="{{ route('admin.equipment-guides.index') }}"
                       class="px-6 py-3 border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 text-center hover:bg-slate-50">
                        Annuler
                    </a>

                    <button type="submit"
                            class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-semibold">
                        Enregistrer les modifications
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>

@endsection
