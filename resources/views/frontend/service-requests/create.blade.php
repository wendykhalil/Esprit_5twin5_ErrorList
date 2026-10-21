@extends('layouts.frontend')

@section('title', 'Demander une intervention - SolarShare')

@section('content')
<div class="max-w-4xl mx-auto py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900" style="font-family: Outfit, sans-serif">Demander une intervention</h1>
        <p class="text-slate-500 mt-2" style="font-family: Outfit, sans-serif">Veuillez remplir ce formulaire. Votre demande sera envoyée avec le statut « En attente ».</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8">
        <h2 class="text-lg font-semibold text-slate-800 mb-4" style="font-family: Outfit, sans-serif">Prestataire sélectionné</h2>
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-amber-500 flex items-center justify-center text-white text-xl font-bold shrink-0">
                {{ substr($serviceProvider->user->name ?? 'U', 0, 1) }}
            </div>
            <div>
                <p class="font-medium text-slate-900" style="font-family: Outfit, sans-serif">{{ $serviceProvider->user->name ?? 'Utilisateur inconnu' }}</p>
                <p class="text-sm text-slate-500" style="font-family: Outfit, sans-serif">{{ $serviceProvider->specialty }} • {{ $serviceProvider->location }}</p>
            </div>
            <div class="mt-2 sm:mt-0 sm:ml-auto">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-100 text-slate-800 text-sm font-medium">
                    {{ $serviceProvider->hourly_rate ? number_format((float) $serviceProvider->hourly_rate, 2) . ' TND/h' : 'Tarif sur devis' }}
                </span>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
            <p class="font-medium">Le formulaire contient des erreurs.</p>
            <p class="mt-1">Vérifiez les champs indiqués ci-dessous. La demande n'a pas été envoyée.</p>
        </div>
    @endif

    <form
        action="{{ route('service-requests.store', $serviceProvider) }}"
        method="POST"
        class="intervention-form bg-white rounded-xl shadow-sm border border-slate-200 p-6"
        novalidate
        x-data="{ submitting: false }"
        x-on:submit="if (submitting) { $event.preventDefault() } else { submitting = true }"
    >
        @csrf

        @if ($errors->has('form') || $errors->has('status') || $errors->has('estimated_price') || $errors->has('user_id') || $errors->has('service_provider_id'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                @foreach (array_merge($errors->get('form'), $errors->get('status'), $errors->get('estimated_price'), $errors->get('user_id'), $errors->get('service_provider_id')) as $message)
                    <p>{{ $message }}</p>
                @endforeach
            </div>
        @endif

        <div class="space-y-6">
            <div x-data="{ showError: {{ $errors->has('title') ? 'true' : 'false' }} }">
                <label for="title" class="block text-sm font-medium text-slate-700 mb-1" style="font-family: Outfit, sans-serif">Titre de la demande <span class="text-red-700" aria-hidden="true">*</span></label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    @class([
                        'w-full px-4 py-2.5 rounded-lg border transition-colors',
                        'border-red-600' => $errors->has('title'),
                        'border-slate-200' => ! $errors->has('title'),
                    ])
                    x-bind:aria-invalid="showError ? 'true' : 'false'"
                    @if ($errors->has('title')) aria-describedby="title-error" @endif
                    x-on:input="showError = false"
                >
                @error('title')
                    <p id="title-error" x-show="showError" class="text-red-700 text-sm mt-1"><span class="font-semibold">Erreur.</span> {{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ showError: {{ $errors->has('requested_date') ? 'true' : 'false' }} }">
                <label for="requested_date" class="block text-sm font-medium text-slate-700 mb-1" style="font-family: Outfit, sans-serif">Date souhaitée <span class="text-red-700" aria-hidden="true">*</span></label>
                <input
                    type="date"
                    id="requested_date"
                    name="requested_date"
                    value="{{ old('requested_date') }}"
                    @class([
                        'w-full px-4 py-2.5 rounded-lg border transition-colors',
                        'border-red-600' => $errors->has('requested_date'),
                        'border-slate-200' => ! $errors->has('requested_date'),
                    ])
                    x-bind:aria-invalid="showError ? 'true' : 'false'"
                    @if ($errors->has('requested_date')) aria-describedby="requested_date-error" @endif
                    x-on:input="showError = false"
                >
                <p class="mt-1 text-xs text-slate-500">Aujourd'hui ou une date ultérieure.</p>
                @error('requested_date')
                    <p id="requested_date-error" x-show="showError" class="text-red-700 text-sm mt-1"><span class="font-semibold">Erreur.</span> {{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ showError: {{ $errors->has('address') ? 'true' : 'false' }} }">
                <label for="address" class="block text-sm font-medium text-slate-700 mb-1" style="font-family: Outfit, sans-serif">Adresse de l'intervention <span class="text-red-700" aria-hidden="true">*</span></label>
                <input
                    type="text"
                    id="address"
                    name="address"
                    value="{{ old('address') }}"
                    autocomplete="street-address"
                    @class([
                        'w-full px-4 py-2.5 rounded-lg border transition-colors',
                        'border-red-600' => $errors->has('address'),
                        'border-slate-200' => ! $errors->has('address'),
                    ])
                    x-bind:aria-invalid="showError ? 'true' : 'false'"
                    @if ($errors->has('address')) aria-describedby="address-error" @endif
                    x-on:input="showError = false"
                >
                @error('address')
                    <p id="address-error" x-show="showError" class="text-red-700 text-sm mt-1"><span class="font-semibold">Erreur.</span> {{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ showError: {{ $errors->has('equipment_id') ? 'true' : 'false' }} }">
                <label for="equipment_id" class="block text-sm font-medium text-slate-700 mb-1" style="font-family: Outfit, sans-serif">Équipement concerné <span class="font-normal text-slate-500">(facultatif)</span></label>
                <select
                    id="equipment_id"
                    name="equipment_id"
                    @class([
                        'w-full px-4 py-2.5 rounded-lg border transition-colors bg-white',
                        'border-red-600' => $errors->has('equipment_id'),
                        'border-slate-200' => ! $errors->has('equipment_id'),
                    ])
                    x-bind:aria-invalid="showError ? 'true' : 'false'"
                    @if ($errors->has('equipment_id')) aria-describedby="equipment_id-error" @endif
                    x-on:change="showError = false"
                >
                    <option value="">Aucun équipement spécifique</option>
                    @foreach($equipments as $equipment)
                        <option value="{{ $equipment->id }}" {{ (string) old('equipment_id') === (string) $equipment->id ? 'selected' : '' }}>
                            {{ $equipment->name }} {{ $equipment->brand ? ' - ' . $equipment->brand : '' }}
                        </option>
                    @endforeach
                </select>
                @error('equipment_id')
                    <p id="equipment_id-error" x-show="showError" class="text-red-700 text-sm mt-1"><span class="font-semibold">Erreur.</span> {{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ showError: {{ $errors->has('description') ? 'true' : 'false' }} }">
                <label for="description" class="block text-sm font-medium text-slate-700 mb-1" style="font-family: Outfit, sans-serif">Description détaillée <span class="text-red-700" aria-hidden="true">*</span></label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    @class([
                        'w-full px-4 py-2.5 rounded-lg border transition-colors',
                        'border-red-600' => $errors->has('description'),
                        'border-slate-200' => ! $errors->has('description'),
                    ])
                    x-bind:aria-invalid="showError ? 'true' : 'false'"
                    @if ($errors->has('description')) aria-describedby="description-error" @endif
                    x-on:input="showError = false"
                >{{ old('description') }}</textarea>
                <p class="mt-1 text-xs text-slate-500">30 caractères minimum, 2&nbsp;000 maximum.</p>
                @error('description')
                    <p id="description-error" x-show="showError" class="text-red-700 text-sm mt-1"><span class="font-semibold">Erreur.</span> {{ $message }}</p>
                @enderror
            </div>

            {{-- Assistant IA : Pré-diagnostic technique --}}
            <div x-data="aiDiagnosisHandler()" class="border border-amber-200 bg-amber-50/50 rounded-xl p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xl">✨</span>
                            <h3 class="text-base font-bold text-slate-900" style="font-family: Outfit, sans-serif">Assistant IA : Pré-diagnostic technique</h3>
                        </div>
                        <p class="text-xs text-slate-600 mt-1" style="font-family: Outfit, sans-serif">
                            Obtenez une analyse instantanée de la panne, les composants probables et des conseils de sécurité avant d'envoyer votre demande.
                        </p>
                    </div>
                    <button
                        type="button"
                        x-on:click="analyze()"
                        x-bind:disabled="loading"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all shrink-0 disabled:opacity-50 disabled:cursor-not-allowed"
                        style="font-family: Outfit, sans-serif"
                    >
                        <span x-show="!loading" class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            Analyser ma panne avec l'IA
                        </span>
                        <span x-cloak x-show="loading" class="flex items-center gap-1.5">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Analyse en cours...
                        </span>
                    </button>
                </div>

                {{-- Notification d'erreur --}}
                <div x-cloak x-show="error" class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg p-3">
                    <span class="font-bold">Attention :</span> <span x-text="error"></span>
                </div>

                {{-- Carte de résultat du pré-diagnostic --}}
                <div x-cloak x-show="diagnosis" class="bg-white border border-amber-200 rounded-xl p-5 shadow-sm space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-amber-700 bg-amber-100 px-2.5 py-0.5 rounded-full" style="font-family: Outfit, sans-serif">
                                Pré-diagnostic indicatif
                            </span>
                            <span class="text-xs text-slate-400" x-text="diagnosis?.source === 'ollama' ? 'Inférence IA locale' : 'Analyse experte SolarShare'"></span>
                        </div>
                        <div>
                            <template x-if="diagnosis?.severity">
                                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full"
                                      x-bind:class="{
                                          'bg-emerald-100 text-emerald-800': diagnosis.severity === 'faible',
                                          'bg-blue-100 text-blue-800': diagnosis.severity === 'moyenne',
                                          'bg-orange-100 text-orange-800': diagnosis.severity === 'elevee',
                                          'bg-red-100 text-red-800': diagnosis.severity === 'urgente'
                                      }"
                                      x-text="'Gravité : ' + diagnosis.severity.toUpperCase()">
                                </span>
                            </template>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-bold text-slate-900" style="font-family: Outfit, sans-serif" x-text="diagnosis?.diagnostic_probable"></h4>
                        <p class="text-xs text-slate-600 mt-1" x-text="diagnosis?.short_summary"></p>
                    </div>

                    {{-- Consigne de sécurité conservatrice --}}
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-xs text-red-800 flex items-start gap-2">
                        <span class="text-base shrink-0">⚠️</span>
                        <div>
                            <span class="font-bold">Consigne de sécurité impérative :</span>
                            <span x-text="diagnosis?.safety_advice"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                        <div>
                            <span class="font-semibold text-slate-500">Durée d'intervention estimée :</span>
                            <span class="font-bold text-slate-800" x-text="diagnosis?.estimated_duration"></span>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-500">Spécialité recommandée :</span>
                            <span class="font-bold text-slate-800" x-text="diagnosis?.recommended_specialty"></span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="font-semibold text-slate-500">Composants potentiellement concernés :</span>
                            <div class="flex flex-wrap gap-1.5 mt-1">
                                <template x-for="component in (diagnosis?.probable_components || [])" :key="component">
                                    <span class="inline-block bg-slate-100 text-slate-700 text-xs px-2 py-0.5 rounded-md border border-slate-200" x-text="component"></span>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Fourchette indicative en TND --}}
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs text-amber-900 flex items-start justify-between gap-3">
                        <div>
                            <span class="font-bold">Estimation indicative :</span>
                            <span class="font-semibold" x-text="diagnosis?.indicative_cost_range"></span>
                            <p class="text-[11px] text-amber-700 mt-0.5">
                                * Montant indicatif non contractuel. Seul le prestataire fixera le devis officiel lors de l'acceptation de votre demande.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex flex-col sm:flex-row sm:items-center justify-end gap-3">
            <a href="{{ route('service-providers.show', $serviceProvider) }}" class="px-6 py-2.5 rounded-lg border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition-colors text-center" style="font-family: Outfit, sans-serif">
                Annuler
            </a>
            <button
                type="submit"
                class="px-6 py-2.5 rounded-lg bg-amber-500 text-white font-medium hover:bg-amber-600 transition-colors shadow-sm"
                style="font-family: Outfit, sans-serif"
                x-bind:class="submitting ? 'cursor-wait opacity-70' : ''"
                x-bind:aria-busy="submitting"
            >
                <span x-show="!submitting">Envoyer la demande</span>
                <span x-cloak x-show="submitting">Envoi en cours...</span>
            </button>
        </div>
    </form>
</div>

<style>
    [x-cloak] { display: none !important; }

    .intervention-form input:focus,
    .intervention-form textarea:focus,
    .intervention-form select:focus {
        border-color: #f59e0b !important;
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.22) !important;
    }

    .intervention-form input[aria-invalid="true"],
    .intervention-form textarea[aria-invalid="true"],
    .intervention-form select[aria-invalid="true"] {
        border-color: #b91c1c !important;
    }

    .intervention-form input[aria-invalid="true"]:focus,
    .intervention-form textarea[aria-invalid="true"]:focus,
    .intervention-form select[aria-invalid="true"]:focus {
        border-color: #b91c1c !important;
        box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.18) !important;
    }
</style>

<script>
    function aiDiagnosisHandler() {
        return {
            loading: false,
            error: null,
            diagnosis: null,
            async analyze() {
                this.error = null;
                const titleInput = document.getElementById('title');
                const descInput = document.getElementById('description');
                const equipmentInput = document.getElementById('equipment_id');

                const title = titleInput ? titleInput.value.trim() : '';
                const description = descInput ? descInput.value.trim() : '';
                const equipmentId = equipmentInput && equipmentInput.value ? equipmentInput.value : null;

                if (!title || title.length < 3) {
                    this.error = "Veuillez renseigner un titre d'intervention (au moins 3 caractères).";
                    if (titleInput) titleInput.focus();
                    return;
                }

                if (!description || description.length < 20) {
                    this.error = "Veuillez décrire votre panne (au moins 20 caractères) pour que l'IA puisse l'analyser.";
                    if (descInput) descInput.focus();
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch("{{ route('service-requests.ai-diagnose') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({
                            service_provider_id: {{ $serviceProvider->id }},
                            title: title,
                            description: description,
                            equipment_id: equipmentId
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.error = data.message || "Une erreur est survenue lors de l'analyse.";
                        this.diagnosis = null;
                    } else if (data.success && data.diagnosis) {
                        this.diagnosis = data.diagnosis;
                    }
                } catch (err) {
                    this.error = "Impossible de joindre le service d'analyse IA. Veuillez vérifier votre connexion.";
                } finally {
                    this.loading = false;
                }
            }
        };
    }
</script>
@endsection
