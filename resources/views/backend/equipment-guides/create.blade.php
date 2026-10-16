
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
                    dans l'utilisation d'un équipement.
                </p>
            </div>

            <a href="{{ route('admin.equipment-guides.index') }}"
               class="shrink-0 px-4 py-2 border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-white transition-colors">
                ← Retour à la liste
            </a>
        </div>

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-5"
                 role="alert">

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
                  class="space-y-6"
                  novalidate>

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
                            aria-invalid="{{ $errors->has('equipment_id') ? 'true' : 'false' }}"
                            class="w-full px-4 py-3 border rounded-lg bg-white focus:ring-2 focus:ring-amber-400 {{ $errors->has('equipment_id') ? 'border-red-500' : 'border-slate-200' }}">

                        <option value="">Sélectionner un équipement</option>

                        @foreach($equipments as $equipment)
                            <option value="{{ $equipment->id }}"
                                    @selected(old('equipment_id') == $equipment->id)>
                                {{ $equipment->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('equipment_id')
                        <p class="text-red-500 text-sm mt-1" role="alert">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- AI Usage Guide Generator --}}
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 space-y-4">

                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-amber-100 flex items-center justify-center text-2xl">
                            ✨
                        </div>

                        <div>
                            <h3 class="font-bold text-slate-900 text-lg">
                                Générateur de guide avec IA
                            </h3>

                            <p class="text-xs text-slate-500">
                                Assistant SolarShare — Ollama / Mistral
                            </p>
                        </div>
                    </div>

                    <p class="text-sm text-slate-600">
                        Sélectionnez un équipement puis générez automatiquement
                        un brouillon contenant le titre, le contexte d'utilisation,
                        les instructions et les précautions de sécurité.
                    </p>

                    <button type="button"
                            id="generateGuideAI"
                            class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-sm font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <span>✨</span>
                        <span id="generateGuideAIText">
                            Générer avec l'IA
                        </span>
                    </button>

                    <p id="guideAIStatus"
                       class="text-sm text-slate-600"
                       role="status"
                       aria-live="polite"></p>

                    <p class="text-xs text-amber-800">
                        Le contenu généré est un brouillon.
                        Vérifiez les informations techniques et les consignes
                        de sécurité avant toute publication.
                    </p>
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
                           minlength="5"
                           maxlength="150"
                           placeholder="Ex : Guide d'utilisation d'un panneau solaire portable"
                           aria-invalid="{{ $errors->has('title') ? 'true' : 'false' }}"
                           class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-amber-400 {{ $errors->has('title') ? 'border-red-500' : 'border-slate-200' }}">

                    <p class="text-xs text-slate-500 mt-1">
                        Entre 5 et 150 caractères.
                    </p>

                    @error('title')
                        <p class="text-red-500 text-sm mt-1" role="alert">
                            {{ $message }}
                        </p>
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
                           minlength="5"
                           maxlength="255"
                           placeholder="Ex : Camping, maison, agriculture..."
                           aria-invalid="{{ $errors->has('usage_context') ? 'true' : 'false' }}"
                           class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-amber-400 {{ $errors->has('usage_context') ? 'border-red-500' : 'border-slate-200' }}">

                    <p class="text-xs text-slate-500 mt-1">
                        Entre 5 et 255 caractères.
                    </p>

                    @error('usage_context')
                        <p class="text-red-500 text-sm mt-1" role="alert">
                            {{ $message }}
                        </p>
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
                              minlength="30"
                              maxlength="5000"
                              placeholder="1. Consulter la documentation du fabricant&#10;2. Vérifier l'état de l'équipement&#10;3. Suivre les recommandations d'utilisation&#10;4. Contacter un professionnel si nécessaire..."
                              aria-invalid="{{ $errors->has('instructions') ? 'true' : 'false' }}"
                              class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-amber-400 {{ $errors->has('instructions') ? 'border-red-500' : 'border-slate-200' }}">{{ old('instructions') }}</textarea>

                    <p class="text-xs text-slate-500 mt-1">
                        Décrivez les étapes d'utilisation clairement
                        (30 à 5000 caractères).
                    </p>

                    @error('instructions')
                        <p class="text-red-500 text-sm mt-1" role="alert">
                            {{ $message }}
                        </p>
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
                              maxlength="2000"
                              placeholder="Ex : Consulter la documentation du fabricant, vérifier l'état de l'équipement..."
                              aria-invalid="{{ $errors->has('safety_precautions') ? 'true' : 'false' }}"
                              class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-amber-400 {{ $errors->has('safety_precautions') ? 'border-red-500' : 'border-slate-200' }}">{{ old('safety_precautions') }}</textarea>

                    <p class="text-xs text-slate-500 mt-1">
                        Facultatif — maximum 2000 caractères.
                    </p>

                    @error('safety_precautions')
                        <p class="text-red-500 text-sm mt-1" role="alert">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Difficulty and status --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    {{-- Difficulty --}}
                    <div>
                        <label for="difficulty_level"
                               class="block text-sm font-semibold text-slate-700 mb-2">
                            Niveau de difficulté <span class="text-red-500">*</span>
                        </label>

                        <select id="difficulty_level"
                                name="difficulty_level"
                                required
                                aria-invalid="{{ $errors->has('difficulty_level') ? 'true' : 'false' }}"
                                class="w-full px-4 py-3 border rounded-lg bg-white focus:ring-2 focus:ring-amber-400 {{ $errors->has('difficulty_level') ? 'border-red-500' : 'border-slate-200' }}">

                            <option value="beginner"
                                    @selected(old('difficulty_level', 'beginner') === 'beginner')>
                                Débutant
                            </option>

                            <option value="intermediate"
                                    @selected(old('difficulty_level') === 'intermediate')>
                                Intermédiaire
                            </option>

                            <option value="advanced"
                                    @selected(old('difficulty_level') === 'advanced')>
                                Avancé
                            </option>
                        </select>

                        @error('difficulty_level')
                            <p class="text-red-500 text-sm mt-1" role="alert">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div>
                        <label for="status"
                               class="block text-sm font-semibold text-slate-700 mb-2">
                            Statut du guide <span class="text-red-500">*</span>
                        </label>

                        <select id="status"
                                name="status"
                                required
                                aria-invalid="{{ $errors->has('status') ? 'true' : 'false' }}"
                                class="w-full px-4 py-3 border rounded-lg bg-white focus:ring-2 focus:ring-amber-400 {{ $errors->has('status') ? 'border-red-500' : 'border-slate-200' }}">

                            <option value="draft"
                                    @selected(old('status', 'draft') === 'draft')>
                                Brouillon
                            </option>

                            <option value="pending"
                                    @selected(old('status') === 'pending')>
                                En attente
                            </option>

                            <option value="published"
                                    @selected(old('status') === 'published')>
                                Publié
                            </option>
                        </select>

                        @error('status')
                            <p class="text-red-500 text-sm mt-1" role="alert">
                                {{ $message }}
                            </p>
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
                           aria-invalid="{{ $errors->has('video_url') ? 'true' : 'false' }}"
                           class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-amber-400 {{ $errors->has('video_url') ? 'border-red-500' : 'border-slate-200' }}">

                    @error('video_url')
                        <p class="text-red-500 text-sm mt-1" role="alert">
                            {{ $message }}
                        </p>
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

{{-- AI Guide Generator JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('generateGuideAI');
    const buttonText = document.getElementById('generateGuideAIText');
    const statusMessage = document.getElementById('guideAIStatus');
    const equipmentSelect = document.getElementById('equipment_id');

    if (!button || !buttonText || !statusMessage || !equipmentSelect) {
        return;
    }

    const fields = [
        'title',
        'usage_context',
        'instructions',
        'safety_precautions',
        'difficulty_level'
    ];

    button.addEventListener('click', async function () {
        const equipmentId = equipmentSelect.value;

        if (!equipmentId) {
            statusMessage.textContent =
                'Veuillez sélectionner un équipement avant de générer le guide.';
            statusMessage.className = 'text-sm text-red-600';
            equipmentSelect.focus();
            return;
        }

        const hasExistingContent = fields.some(function (fieldName) {
            if (fieldName === 'difficulty_level') {
                return false;
            }

            const field = document.getElementById(fieldName);

            return field && field.value.trim() !== '';
        });

        if (
            hasExistingContent &&
            !window.confirm(
                'La génération IA remplacera le contenu des champs existants. Continuer ?'
            )
        ) {
            return;
        }

        button.disabled = true;
        buttonText.textContent = 'Génération en cours...';
        statusMessage.textContent =
            'Mistral prépare votre guide. Cela peut prendre quelques instants.';
        statusMessage.className = 'text-sm text-slate-600';

        try {
            const response = await fetch(
                @json(route('admin.equipment-guides.ai.generate')),
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token())
                    },
                    body: JSON.stringify({
                        equipment_id: equipmentId
                    })
                }
            );

            const result = await response.json();

            if (!response.ok || !result.success || !result.guide) {
                const validationError = result.errors
                    ? Object.values(result.errors).flat().join(' ')
                    : null;

                throw new Error(
                    validationError ||
                    result.message ||
                    'Une erreur est survenue pendant la génération.'
                );
            }

            if (equipmentSelect.value !== equipmentId) {
                throw new Error(
                    'L’équipement sélectionné a changé. Relancez la génération.'
                );
            }

            fields.forEach(function (fieldName) {
                const field = document.getElementById(fieldName);

                if (
                    field &&
                    Object.prototype.hasOwnProperty.call(
                        result.guide,
                        fieldName
                    )
                ) {
                    field.value = result.guide[fieldName] ?? '';

                    field.dispatchEvent(
                        new Event('input', { bubbles: true })
                    );

                    field.dispatchEvent(
                        new Event('change', { bubbles: true })
                    );
                }
            });

            // AI-generated guides must remain drafts until reviewed.
            const guideStatus = document.getElementById('status');

            if (guideStatus) {
                guideStatus.value = 'draft';
                guideStatus.dispatchEvent(
                    new Event('change', { bubbles: true })
                );
            }

            statusMessage.textContent =
                'Guide généré avec succès ! Vérifiez les informations techniques et les consignes de sécurité avant de l’enregistrer.';
            statusMessage.className = 'text-sm text-green-700';

        } catch (error) {
            statusMessage.textContent =
                error.message || 'Impossible de générer le guide.';
            statusMessage.className = 'text-sm text-red-600';

        } finally {
            button.disabled = false;
            buttonText.textContent = "Générer avec l'IA";
        }
    });
});
</script>

@endsection
