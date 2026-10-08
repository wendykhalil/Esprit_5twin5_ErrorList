@extends('layouts.backend')

@section('title', 'Ajouter un équipement - SolarShare Admin')

@section('content')

<div class="py-6">
    <div class="max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="mb-8 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 mb-2" style="font-family: Outfit, sans-serif">
                    Publier un équipement
                </h1>
                <p class="text-slate-500 text-sm" style="font-family: Outfit, sans-serif">
                    Partagez votre équipement avec la communauté SolarShare. Validation côté serveur après envoi.
                </p>
            </div>
            <a
                href="{{ route('admin.equipments') }}"
                class="shrink-0 px-4 py-2 border border-slate-200 rounded-lg text-sm font-semibold text-slate-600 hover:bg-white transition-colors"
                style="font-family: Outfit, sans-serif"
            >
                ← Retour à la liste
            </a>
        </div>

        @php
            $validationStep = 1;
            if ($errors->hasAny(['location'])) {
                $validationStep = 2;
            } elseif ($errors->hasAny(['image'])) {
                $validationStep = 3;
            } elseif ($errors->hasAny(['price_per_day', 'status', 'availability'])) {
                $validationStep = 4;
            } elseif ($errors->hasAny(['name', 'category_id', 'description', 'brand', 'power', 'capacity', 'condition'])) {
                $validationStep = 1;
            }
        @endphp

        {{-- Global validation errors --}}
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-5" role="alert">
                <p class="font-semibold text-red-800 mb-2">
                    Veuillez corriger les champs suivants :
                </p>

                <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Progress --}}
        <div class="mb-8 flex items-center justify-between overflow-x-auto">
            <div class="flex items-center gap-4 min-w-full">

                <div class="flex items-center flex-1">
                    <div class="step-indicator active-step">
                        <span class="step-number">1</span>
                    </div>
                    <div class="step-label">Informations</div>
                </div>

                <div class="step-connector"></div>

                <div class="flex items-center flex-1">
                    <div class="step-indicator">
                        <span class="step-number">2</span>
                    </div>
                    <div class="step-label">Localisation</div>
                </div>

                <div class="step-connector"></div>

                <div class="flex items-center flex-1">
                    <div class="step-indicator">
                        <span class="step-number">3</span>
                    </div>
                    <div class="step-label">Photo</div>
                </div>

                <div class="step-connector"></div>

                <div class="flex items-center flex-1">
                    <div class="step-indicator">
                        <span class="step-number">4</span>
                    </div>
                    <div class="step-label">Tarification</div>
                </div>

                <div class="step-connector"></div>

                <div class="flex items-center flex-1">
                    <div class="step-indicator">
                        <span class="step-number">5</span>
                    </div>
                    <div class="step-label">Confirmation</div>
                </div>

            </div>
        </div>

        {{-- Form --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">

            <form
                id="equipmentForm"
                method="POST"
                action="{{ route('admin.equipments.store') }}"
                data-validate-step-url="{{ route('admin.equipments.create.validate-step') }}"
                enctype="multipart/form-data"
                class="space-y-6"
                novalidate
            >
                @csrf

                <div id="wizardStepAlert" class="hidden mb-4 bg-red-50 border border-red-200 rounded-xl p-4" role="alert">
                    <p class="font-semibold text-red-800 mb-2">Veuillez corriger les champs de cette étape :</p>
                    <ul id="wizardStepAlertList" class="list-disc list-inside text-sm text-red-700 space-y-1"></ul>
                </div>

                {{-- STEP 1 --}}
                <div id="step-1" class="form-step">

                    <h2 class="text-2xl font-bold text-slate-900 mb-6"
                        style="font-family: Outfit, sans-serif">
                        Informations générales
                    </h2>

                    <div class="space-y-5">

                        {{-- Name --}}
                        <div>
                            <label for="name"
                                   class="block text-sm font-semibold text-slate-700 mb-2">
                                Nom de l'équipement
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Ex: Panneau solaire portable 200W"
                                class="w-full px-4 py-3 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400
                                @error('name') border-red-500 @else border-slate-200 @enderror"
                            >

                            <p class="mt-1 text-xs text-slate-500">3 à 255 caractères.</p>
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Category --}}
                        <div>
                            <label for="category_id"
                                   class="block text-sm font-semibold text-slate-700 mb-2">
                                Catégorie
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="category_id"
                                name="category_id"
                                class="w-full px-4 py-3 border rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-400
                                @error('category_id') border-red-500 @else border-slate-200 @enderror"
                            >
                                <option value="">
                                    Sélectionner une catégorie
                                </option>

                                @foreach ($categories as $category)
                                    <option
                                        value="{{ $category->id }}"
                                        {{ old('category_id') == $category->id ? 'selected' : '' }}
                                    >
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('category_id')
                                <p class="text-red-500 text-sm mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Brand --}}
                        <div>
                            <label for="brand"
                                   class="block text-sm font-semibold text-slate-700 mb-2">
                                Marque
                            </label>

                            <input
                                type="text"
                                id="brand"
                                name="brand"
                                value="{{ old('brand') }}"
                                placeholder="Ex: EcoFlow, Jackery, Bluetti..."
                                class="w-full px-4 py-3 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                            >

                            @error('brand')
                                <p class="text-red-500 text-sm mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="description"
                                   class="block text-sm font-semibold text-slate-700 mb-2">
                                Description
                                <span class="text-red-500">*</span>
                            </label>

                            <textarea
                                id="description"
                                name="description"
                                rows="5"
                                placeholder="Décrivez votre équipement : caractéristiques, état, accessoires inclus..."
                                class="w-full px-4 py-3 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none
                                @error('description') border-red-500 @else border-slate-200 @enderror"
                            >{{ old('description') }}</textarea>

                            <p class="mt-1 text-xs text-slate-500">10 à 1000 caractères.</p>
                            @error('description')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Power / Capacity --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <div>
                                <label for="power"
                                       class="block text-sm font-semibold text-slate-700 mb-2">
                                    Puissance (W)
                                </label>

                                <input
                                    type="number"
                                    step="0.01"
                                    id="power"
                                    name="power"
                                    value="{{ old('power') }}"
                                    placeholder="Ex: 200"
                                    class="w-full px-4 py-3 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                                >

                                @error('power')
                                    <p class="text-red-500 text-sm mt-1">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="capacity"
                                       class="block text-sm font-semibold text-slate-700 mb-2">
                                    Capacité (Wh)
                                </label>

                                <input
                                    type="number"
                                    step="0.01"
                                    id="capacity"
                                    name="capacity"
                                    value="{{ old('capacity') }}"
                                    placeholder="Ex: 512"
                                    class="w-full px-4 py-3 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                                >

                                @error('capacity')
                                    <p class="text-red-500 text-sm mt-1">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        </div>

                        {{-- Condition --}}
                        <div id="condition-wizard-group">
                            <label class="block text-sm font-semibold text-slate-700 mb-3">
                                État de l'équipement
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                                <label class="condition-option flex items-center justify-center gap-2 p-3 border-2 border-slate-200 rounded-lg cursor-pointer hover:border-amber-400">
                                    <input
                                        type="radio"
                                        name="condition"
                                        value="excellent"
                                        class="hidden"
                                        {{ old('condition', 'good') === 'excellent' ? 'checked' : '' }}
                                    >
                                    <span>✨ Excellent</span>
                                </label>

                                <label class="condition-option flex items-center justify-center gap-2 p-3 border-2 border-slate-200 rounded-lg cursor-pointer hover:border-amber-400">
                                    <input
                                        type="radio"
                                        name="condition"
                                        value="good"
                                        class="hidden"
                                        {{ old('condition', 'good') === 'good' ? 'checked' : '' }}
                                    >
                                    <span>👍 Bon</span>
                                </label>

                                <label class="condition-option flex items-center justify-center gap-2 p-3 border-2 border-slate-200 rounded-lg cursor-pointer hover:border-amber-400">
                                    <input
                                        type="radio"
                                        name="condition"
                                        value="used"
                                        class="hidden"
                                        {{ old('condition') === 'used' ? 'checked' : '' }}
                                    >
                                    <span>👌 Utilisé</span>
                                </label>

                            </div>

                            @error('condition')
                                <p class="text-red-500 text-sm mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- STEP 2 --}}
                <div id="step-2" class="form-step hidden">

                    <h2 class="text-2xl font-bold text-slate-900 mb-6"
                        style="font-family: Outfit, sans-serif">
                        Localisation
                    </h2>

                    <div class="space-y-5">

                        <div>
                            <label for="location"
                                   class="block text-sm font-semibold text-slate-700 mb-2">
                                Ville
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="location"
                                name="location"
                                class="w-full px-4 py-3 border rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-400
                                @error('location') border-red-500 @else border-slate-200 @enderror"
                            >
                                <option value="">Sélectionner une ville</option>

                                @foreach ([
                                    'Tunis',
                                    'Ariana',
                                    'Ben Arous',
                                    'Manouba',
                                    'Nabeul',
                                    'Bizerte',
                                    'Sousse',
                                    'Monastir',
                                    'Mahdia',
                                    'Sfax',
                                    'Gabès',
                                    'Médenine',
                                    'Tataouine'
                                ] as $city)

                                    <option
                                        value="{{ $city }}"
                                        {{ old('location') === $city ? 'selected' : '' }}
                                    >
                                        {{ $city }}
                                    </option>

                                @endforeach
                            </select>

                            @error('location')
                                <p class="text-red-500 text-sm mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="bg-amber-50 border border-green-200 rounded-lg p-4 flex gap-3">
                            <span class="text-2xl">📍</span>

                            <p class="text-sm text-green-800">
                                Votre localisation aide les utilisateurs proches
                                à trouver votre équipement plus facilement.
                            </p>
                        </div>

                    </div>
                </div>

                {{-- STEP 3 --}}
                <div id="step-3" class="form-step hidden">

                    <h2 class="text-2xl font-bold text-slate-900 mb-6"
                        style="font-family: Outfit, sans-serif">
                        Photo de l'équipement
                    </h2>

                    <div class="space-y-5">

                        <label
                            for="image"
                            class="block border-2 border-dashed border-amber-300 rounded-lg p-12 text-center hover:border-amber-500 transition-colors cursor-pointer bg-amber-50"
                        >
                            <div class="text-5xl mb-3">📸</div>

                            <p class="font-semibold text-slate-900 mb-1">
                                Ajouter une photo
                            </p>

                            <p class="text-sm text-slate-600 mb-4">
                                Cliquez pour sélectionner une image
                            </p>

                            <p class="text-xs text-slate-500">
                                JPG, JPEG, PNG ou WEBP · Maximum 2 Mo
                            </p>

                            <input
                                id="image"
                                type="file"
                                name="image"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                class="hidden"
                            >
                        </label>

                        <div id="selectedImageName"
                             class="hidden bg-slate-50 border border-slate-200 rounded-lg p-3 text-sm text-slate-700">
                        </div>

                        @error('image')
                            <p class="text-red-500 text-sm">
                                {{ $message }}
                            </p>
                        @enderror

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <p class="text-sm font-semibold text-yellow-900 mb-2">
                                💡 Conseils pour une meilleure photo
                            </p>

                            <ul class="text-sm text-yellow-800 space-y-1">
                                <li>• Utilisez un endroit bien éclairé.</li>
                                <li>• Montrez clairement l'équipement.</li>
                                <li>• Évitez les photos floues.</li>
                                <li>• Montrez les éventuels défauts visibles.</li>
                            </ul>
                        </div>

                    </div>
                </div>

                {{-- STEP 4 --}}
                <div id="step-4" class="form-step hidden">

                    <h2 class="text-2xl font-bold text-slate-900 mb-6"
                        style="font-family: Outfit, sans-serif">
                        Tarification et disponibilité
                    </h2>

                    <div class="space-y-5">

                        {{-- Price --}}
                        <div>
                            <label for="price_per_day"
                                   class="block text-sm font-semibold text-slate-700 mb-2">
                                Prix par jour
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <input
                                    type="number"
                                    step="0.01"
                                    id="price_per_day"
                                    name="price_per_day"
                                    value="{{ old('price_per_day') }}"
                                    placeholder="0"
                                    class="w-full px-4 py-3 pr-16 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-amber-400
                                    @error('price_per_day') border-red-500 @else border-slate-200 @enderror"
                                >

                                <span class="absolute right-3 top-3 text-slate-500 font-medium">
                                    TND
                                </span>
                            </div>

                            <p class="mt-1 text-xs text-slate-500">Tarif journalier en TND (0 à 99 999).</p>
                            @error('price_per_day')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Availability --}}
                        <div class="border border-slate-200 rounded-lg p-4">

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="availability"
                                    value="1"
                                    {{ old('availability', true) ? 'checked' : '' }}
                                    class="w-5 h-5 text-green-600 rounded border-slate-200 focus:ring-amber-400"
                                >

                                <div>
                                    <p class="font-semibold text-slate-800">
                                        Équipement disponible
                                    </p>

                                    <p class="text-sm text-slate-500">
                                        Les utilisateurs pourront voir cet équipement
                                        comme disponible à la location.
                                    </p>
                                </div>
                            </label>

                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="status"
                                   class="block text-sm font-semibold text-slate-700 mb-2">
                                Statut
                            </label>

                            <select
                                id="status"
                                name="status"
                                class="w-full px-4 py-3 border border-slate-200 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                            >
                                <option
                                    value="active"
                                    {{ old('status', 'active') === 'active' ? 'selected' : '' }}
                                >
                                    Actif
                                </option>

                                <option
                                    value="inactive"
                                    {{ old('status') === 'inactive' ? 'selected' : '' }}
                                >
                                    Inactif
                                </option>
                            </select>

                            @error('status')
                                <p class="text-red-500 text-sm mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Revenue estimation --}}
                        <div class="bg-amber-50 border border-green-200 rounded-lg p-6">

                            <p class="text-sm font-semibold text-slate-900 mb-4">
                                Estimation de revenus
                            </p>

                            <div class="grid grid-cols-3 gap-3">

                                <div class="bg-white border border-green-200 rounded-lg p-3 text-center">
                                    <p class="text-lg font-bold text-amber-700"
                                       id="estimate-week">
                                        0 TND
                                    </p>

                                    <p class="text-xs text-slate-600">
                                        1 semaine
                                    </p>
                                </div>

                                <div class="bg-white border border-green-200 rounded-lg p-3 text-center">
                                    <p class="text-lg font-bold text-amber-700"
                                       id="estimate-2weeks">
                                        0 TND
                                    </p>

                                    <p class="text-xs text-slate-600">
                                        2 semaines
                                    </p>
                                </div>

                                <div class="bg-white border border-green-200 rounded-lg p-3 text-center">
                                    <p class="text-lg font-bold text-amber-700"
                                       id="estimate-month">
                                        0 TND
                                    </p>

                                    <p class="text-xs text-slate-600">
                                        1 mois
                                    </p>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                {{-- STEP 5 --}}
                <div id="step-5" class="form-step hidden">

                    <h2 class="text-2xl font-bold text-slate-900 mb-6"
                        style="font-family: Outfit, sans-serif">
                        Prêt à publier !
                    </h2>

                    <div class="text-center mb-8">
                        <div class="text-6xl mb-4">🎉</div>

                        <p class="text-slate-600">
                            Vérifiez vos informations avant de publier votre équipement.
                        </p>
                    </div>

                    {{-- Summary --}}
                    <div class="bg-amber-50 border border-green-200 rounded-lg p-6 mb-6">

                        <div class="space-y-3">

                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">Nom</span>
                                <span class="font-semibold text-slate-900"
                                      id="summary-name">
                                    —
                                </span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">Catégorie</span>
                                <span class="font-semibold text-slate-900"
                                      id="summary-category">
                                    —
                                </span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">Localisation</span>
                                <span class="font-semibold text-slate-900"
                                      id="summary-location">
                                    —
                                </span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">État</span>
                                <span class="font-semibold text-slate-900"
                                      id="summary-condition">
                                    —
                                </span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">Prix</span>
                                <span class="font-semibold text-amber-700"
                                      id="summary-price">
                                    —
                                </span>
                            </div>

                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-4 px-6 bg-amber-500 hover:bg-amber-600 text-white font-bold text-lg rounded-lg transition-colors"
                    >
                        Publier mon équipement
                    </button>

                </div>

                {{-- Navigation --}}
                <div class="flex gap-3 pt-6 border-t border-slate-200">

                    <button
                        type="button"
                        id="prevBtn"
                        class="hidden px-6 py-3 border border-slate-200 text-slate-700 font-semibold rounded-lg hover:bg-gray-100 transition-colors"
                    >
                        Retour
                    </button>

                    <button
                        type="button"
                        id="nextBtn"
                        class="flex-1 py-3 px-6 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg transition-colors"
                    >
                        Continuer
                    </button>

                </div>

            </form>
        </div>
    </div>
</div>

<style>
    .step-indicator {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 9999px;
        background-color: #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #4b5563;
        font-size: 0.875rem;
        position: relative;
        z-index: 10;
        flex-shrink: 0;
    }

    .step-indicator.active-step,
    .step-indicator.completed-step {
        background-color: #16a34a;
        color: white;
    }

    .step-connector {
        flex: 1;
        height: 0.25rem;
        background-color: #d1d5db;
        margin-left: 0.5rem;
        margin-right: 0.5rem;
    }

    .step-connector.completed-connector {
        background-color: #16a34a;
    }

    .step-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: #4b5563;
        white-space: nowrap;
        margin-left: 0.5rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    let currentStep = {{ $validationStep }};
    const totalSteps = 5;

    const stepIndicators = document.querySelectorAll('.step-indicator');
    const stepConnectors = document.querySelectorAll('.step-connector');
    const formSteps = document.querySelectorAll('.form-step');

    const form = document.getElementById('equipmentForm');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const wizardStepAlert = document.getElementById('wizardStepAlert');
    const wizardStepAlertList = document.getElementById('wizardStepAlertList');
    const validateStepUrl = form.dataset.validateStepUrl;
    const csrfToken = form.querySelector('input[name="_token"]').value;

    const priceInput = document.getElementById('price_per_day');
    const imageInput = document.getElementById('image');

    function clearWizardStepErrors() {
        wizardStepAlert.classList.add('hidden');
        wizardStepAlertList.innerHTML = '';

        form.querySelectorAll('.wizard-client-error').forEach(function (el) {
            el.remove();
        });

        form.querySelectorAll('.border-red-500').forEach(function (el) {
            el.classList.remove('border-red-500');
            if (!el.classList.contains('border-slate-200')) {
                el.classList.add('border-slate-200');
            }
        });
    }

    function showWizardStepErrors(errors) {
        const messages = [];

        Object.keys(errors).forEach(function (field) {
            const fieldMessages = errors[field];
            if (!fieldMessages || !fieldMessages.length) {
                return;
            }

            messages.push(fieldMessages[0]);

            const stepRoot = document.getElementById('step-' + currentStep);
            const errorEl = document.createElement('p');
            errorEl.className = 'wizard-client-error text-red-500 text-sm mt-1';
            errorEl.textContent = fieldMessages[0];

            if (field === 'condition') {
                const group = stepRoot.querySelector('#condition-wizard-group');
                if (group) {
                    group.appendChild(errorEl);
                }
                return;
            }

            const control = stepRoot.querySelector('[name="' + field + '"]');

            if (control) {
                control.classList.add('border-red-500');
                control.classList.remove('border-slate-200');

                const wrapper = control.closest('div') || control.parentElement;
                wrapper.appendChild(errorEl);
            }
        });

        if (messages.length) {
            wizardStepAlertList.innerHTML = '';
            messages.forEach(function (msg) {
                const li = document.createElement('li');
                li.textContent = msg;
                wizardStepAlertList.appendChild(li);
            });
            wizardStepAlert.classList.remove('hidden');
        }
    }

    async function validateCurrentStepOnServer() {
        const formData = new FormData(form);
        formData.set('wizard_step', String(currentStep));

        const response = await fetch(validateStepUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData,
        });

        if (response.status === 422) {
            const payload = await response.json();
            showWizardStepErrors(payload.errors || {});
            return false;
        }

        if (!response.ok) {
            throw new Error('Validation request failed');
        }

        return true;
    }

    function updateStepIndicators() {

        stepIndicators.forEach((indicator, index) => {

            const stepNumber = index + 1;

            indicator.classList.remove(
                'active-step',
                'completed-step'
            );

            if (stepNumber === currentStep) {
                indicator.classList.add('active-step');
            }
            else if (stepNumber < currentStep) {
                indicator.classList.add('completed-step');
            }

        });

        stepConnectors.forEach((connector, index) => {

            connector.classList.remove('completed-connector');

            if (index < currentStep - 1) {
                connector.classList.add('completed-connector');
            }

        });
    }

    function showStep(step) {

        formSteps.forEach(function (element) {
            element.classList.add('hidden');
        });

        document
            .getElementById('step-' + step)
            .classList.remove('hidden');

        if (step === 1) {

            prevBtn.classList.add('hidden');
            nextBtn.classList.remove('hidden');

        }
        else if (step === totalSteps) {

            prevBtn.classList.remove('hidden');
            nextBtn.classList.add('hidden');

            updateSummary();

        }
        else {

            prevBtn.classList.remove('hidden');
            nextBtn.classList.remove('hidden');

        }

        updateStepIndicators();

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    nextBtn.addEventListener('click', async function () {
        if (currentStep >= totalSteps) {
            return;
        }

        if (currentStep <= 4) {
            clearWizardStepErrors();
            nextBtn.disabled = true;
            nextBtn.classList.add('opacity-70', 'cursor-wait');

            try {
                const valid = await validateCurrentStepOnServer();
                if (!valid) {
                    return;
                }
            } catch (error) {
                wizardStepAlertList.innerHTML =
                    '<li>Impossible de valider cette étape. Réessayez.</li>';
                wizardStepAlert.classList.remove('hidden');
                return;
            } finally {
                nextBtn.disabled = false;
                nextBtn.classList.remove('opacity-70', 'cursor-wait');
            }
        }

        currentStep++;
        showStep(currentStep);
        clearWizardStepErrors();
    });

    prevBtn.addEventListener('click', function () {

        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
            clearWizardStepErrors();
        }
    });

    function updateConditionDisplay() {

        document
            .querySelectorAll('input[name="condition"]')
            .forEach(function (radio) {

                const label = radio.closest('label');

                label.classList.remove(
                    'border-amber-500',
                    'bg-amber-50'
                );

                label.classList.add('border-slate-200');

                if (radio.checked) {

                    label.classList.remove('border-slate-200');

                    label.classList.add(
                        'border-amber-500',
                        'bg-amber-50'
                    );
                }

            });
    }

    document
        .querySelectorAll('input[name="condition"]')
        .forEach(function (radio) {

            radio.addEventListener(
                'change',
                updateConditionDisplay
            );

        });

    function updateRevenueEstimates() {

        const price =
            parseFloat(priceInput.value) || 0;

        document.getElementById('estimate-week').textContent =
            (price * 7).toFixed(2) + ' TND';

        document.getElementById('estimate-2weeks').textContent =
            (price * 14).toFixed(2) + ' TND';

        document.getElementById('estimate-month').textContent =
            (price * 30).toFixed(2) + ' TND';
    }

    priceInput.addEventListener(
        'input',
        updateRevenueEstimates
    );

    imageInput.addEventListener('change', function () {

        const display =
            document.getElementById('selectedImageName');

        if (this.files.length > 0) {

            display.textContent =
                '📸 ' + this.files[0].name;

            display.classList.remove('hidden');

        }
        else {

            display.classList.add('hidden');

        }
    });

    function updateSummary() {

        const name =
            document.getElementById('name').value;

        const category =
            document.getElementById('category_id');

        const location =
            document.getElementById('location');

        const price =
            document.getElementById('price_per_day').value;

        const condition =
            document.querySelector(
                'input[name="condition"]:checked'
            );

        document.getElementById('summary-name').textContent =
            name || '—';

        document.getElementById('summary-category').textContent =
            category.options[category.selectedIndex]?.text || '—';

        document.getElementById('summary-location').textContent =
            location.value || '—';

        document.getElementById('summary-condition').textContent =
            condition
                ? condition.closest('label').innerText.trim()
                : '—';

        document.getElementById('summary-price').textContent =
            price
                ? price + ' TND / jour'
                : '—';
    }

    updateConditionDisplay();
    updateRevenueEstimates();
    showStep(currentStep);
});
</script>

@endsection