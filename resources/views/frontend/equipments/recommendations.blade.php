
@extends('layouts.frontend')

@section('title', 'Recommandations IA - SolarShare')

@section('content')
<div class="min-h-screen bg-slate-50 py-12 px-4">
    <div class="max-w-6xl mx-auto">

        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-2 bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-semibold mb-5">
                🤖 Propulsé par SolarShare AI
            </div>

            <h1 class="text-3xl md:text-5xl font-bold text-green-950 mb-4">
                Trouvez votre équipement idéal
            </h1>

            <p class="text-slate-500 max-w-2xl mx-auto">
                Décrivez votre projet et laissez notre assistant IA
                vous recommander les équipements disponibles
                les plus adaptés à vos besoins.
            </p>
        </div>

        <div class="bg-white rounded-3xl border border-green-100 shadow-xl p-6 md:p-9 mb-10">

            @if(session('recommendation_error'))
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
                    {{ session('recommendation_error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('equipments.ai.recommend.store') }}"
                id="recommendation-form"
                novalidate
            >
                @csrf

                <label for="needs" class="block font-bold text-slate-800 mb-3">
                    💬 Décrivez votre besoin
                </label>

                <textarea
                    id="needs"
                    name="needs"
                    rows="4"
                    minlength="10"
                    maxlength="1000"
                    required
                    aria-describedby="needs-error"
                    placeholder="Exemple : Je cherche un panneau solaire pour alimenter une petite installation domestique..."
                    class="w-full rounded-2xl border border-slate-200 bg-slate-50 p-4 focus:ring-2 focus:ring-green-400 focus:border-green-400 outline-none resize-y"
                >{{ old('needs', $needs ?? '') }}</textarea>

                <p id="needs-error"
                   class="hidden text-red-600 text-sm mt-2"
                   role="alert"></p>

                <div class="grid md:grid-cols-2 gap-5 mt-6">
                    <div>
                        <label for="budget" class="block font-semibold text-slate-700 mb-2">
                            💰 Budget maximum par jour (TND)
                        </label>

                        <input
                            id="budget"
                            type="number"
                            name="budget"
                            min="0"
                            max="1000000"
                            step="0.01"
                            value="{{ old('budget', $budget ?? '') }}"
                            placeholder="Optionnel"
                            aria-describedby="budget-error"
                            class="w-full rounded-xl border border-slate-200 p-3 focus:ring-2 focus:ring-green-400 outline-none"
                        >

                        <p id="budget-error"
                           class="hidden text-red-600 text-sm mt-2"
                           role="alert"></p>
                    </div>

                    <div>
                        <label for="location" class="block font-semibold text-slate-700 mb-2">
                            📍 Ville souhaitée
                        </label>

                        <input
                            id="location"
                            type="text"
                            name="location"
                            maxlength="100"
                            value="{{ old('location', $location ?? '') }}"
                            placeholder="Exemple : Tunis"
                            aria-describedby="location-error"
                            class="w-full rounded-xl border border-slate-200 p-3 focus:ring-2 focus:ring-green-400 outline-none"
                        >

                        <p id="location-error"
                           class="hidden text-red-600 text-sm mt-2"
                           role="alert"></p>
                    </div>
                </div>

                <div class="mt-7">
                    <button
                        id="recommendation-submit"
                        type="submit"
                        class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-4 px-6 rounded-2xl shadow-lg transition disabled:opacity-60"
                    >
                        ✨ Trouver mes équipements avec l'IA
                    </button>

                    <p id="recommendation-loading"
                       class="hidden text-center text-sm text-green-700 mt-4"
                       role="status">
                        🤖 SolarShare AI analyse les équipements...
                        Cela peut prendre quelques instants.
                    </p>
                </div>
            </form>
        </div>

        @isset($recommendations)
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-green-950">
                    ✨ Vos recommandations personnalisées
                </h2>

                <p class="text-sm text-slate-500 mt-2">
                    {{ $recommendations->count() }}
                    équipement(s) sélectionné(s) parmi les candidats disponibles.
                </p>
            </div>

            <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($recommendations as $recommendation)
                    @php
                        $equipment = $recommendation['equipment'];
                    @endphp

                    <article class="bg-white rounded-3xl border border-green-100 shadow-sm overflow-hidden hover:shadow-xl transition">

                        <div class="h-52 bg-green-50">
                            @if($equipment->image)
                                <img
                                    src="{{ asset('storage/' . $equipment->image) }}"
                                    alt="{{ $equipment->name }}"
                                    class="w-full h-full object-cover"
                                    loading="lazy"
                                >
                            @else
                                <div class="w-full h-full flex items-center justify-center text-6xl">
                                    ☀️
                                </div>
                            @endif
                        </div>

                        <div class="p-6">
                            <span class="text-xs font-semibold text-green-700 uppercase">
                                {{ $equipment->category?->name ?? 'Équipement' }}
                            </span>

                            <h3 class="text-xl font-bold text-green-950 mt-2">
                                {{ $equipment->name }}
                            </h3>

                            <p class="text-sm text-slate-500 mt-2">
                                📍 {{ $equipment->location ?? 'Lieu non précisé' }}
                            </p>

                            <div class="mt-4 flex items-baseline gap-2">
                                <span class="text-2xl font-bold text-green-700">
                                    {{ number_format((float) $equipment->price_per_day, 2) }}
                                    TND
                                </span>
                                <span class="text-sm text-slate-500">/ jour</span>
                            </div>

                            <div class="mt-5 bg-green-50 border border-green-100 rounded-2xl p-4">
                                <p class="font-bold text-green-900 text-sm mb-2">
                                    🤖 Pourquoi ce choix ?
                                </p>

                                <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                                    {{ $recommendation['reason'] }}
                                </p>
                            </div>

                            <a
                                href="{{ route('equipments.show', $equipment) }}"
                                class="mt-6 block text-center bg-green-700 hover:bg-green-800 text-white font-semibold py-3 rounded-xl transition"
                            >
                                Voir cet équipement →
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <p class="text-xs text-slate-500 mt-6 text-center">
                Les recommandations IA sont indicatives.
                Vérifiez les caractéristiques et la disponibilité
                pour vos dates avant toute réservation.
            </p>
        @endisset

        <div class="text-center mt-10">
            <a href="{{ route('equipments.index') }}"
               class="text-green-700 font-semibold hover:underline">
                ← Retour au catalogue
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('recommendation-form');
    const button = document.getElementById('recommendation-submit');
    const loading = document.getElementById('recommendation-loading');

    if (!form || !button || !loading) return;

    const fields = [
        document.getElementById('needs'),
        document.getElementById('budget'),
        document.getElementById('location')
    ];

    function showError(field, message) {
        const error = document.getElementById(field.id + '-error');

        if (!error) return;

        error.textContent = message;
        error.classList.remove('hidden');

        field.classList.remove('border-slate-200');
        field.classList.add('border-red-500');
        field.setAttribute('aria-invalid', 'true');
    }

    function clearError(field) {
        const error = document.getElementById(field.id + '-error');

        if (!error) return;

        error.textContent = '';
        error.classList.add('hidden');

        field.classList.remove('border-red-500');
        field.classList.add('border-slate-200');
        field.removeAttribute('aria-invalid');
    }

    function validateField(field) {
        const value = field.value.trim();

        clearError(field);

        if (field.id === 'needs') {
            if (value.length === 0) {
                showError(field, 'Veuillez décrire votre besoin.');
                return false;
            }

            if (value.length < 10) {
                showError(
                    field,
                    'Veuillez saisir au moins 10 caractères pour décrire votre besoin.'
                );
                return false;
            }

            if (value.length > 1000) {
                showError(field, 'Votre description ne doit pas dépasser 1000 caractères.');
                return false;
            }
        }

        if (field.id === 'budget' && value !== '') {
            const budget = Number(value);

            if (!Number.isFinite(budget) || field.validity.badInput) {
                showError(field, 'Veuillez saisir un budget valide.');
                return false;
            }

            if (budget < 0) {
                showError(field, 'Le budget ne peut pas être négatif.');
                return false;
            }

            if (budget > 1000000) {
                showError(field, 'Le budget maximum autorisé est de 1 000 000 TND.');
                return false;
            }

            if (!field.validity.stepMismatch) {
                return true;
            }

            showError(field, 'Veuillez saisir un montant avec deux décimales maximum.');
            return false;
        }

        if (field.id === 'location' && value.length > 100) {
            showError(field, 'Le nom de la ville ne doit pas dépasser 100 caractères.');
            return false;
        }

        return true;
    }

    fields.forEach(function (field) {
        if (!field) return;

        field.addEventListener('input', function () {
            validateField(field);
        });

        field.addEventListener('blur', function () {
            validateField(field);
        });
    });

    form.addEventListener('submit', function (event) {
        let valid = true;
        let firstInvalid = null;

        fields.forEach(function (field) {
            if (!field) return;

            if (!validateField(field)) {
                valid = false;

                if (!firstInvalid) {
                    firstInvalid = field;
                }
            }
        });

        if (!valid) {
            event.preventDefault();

            if (firstInvalid) {
                firstInvalid.focus();
            }

            return;
        }

        button.disabled = true;
        button.textContent = 'Analyse en cours...';
        loading.classList.remove('hidden');
    });
});
</script>
@endsection
