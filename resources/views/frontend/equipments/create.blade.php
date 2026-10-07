@extends('layouts.frontend')

@section('title', 'Publier un équipement - SolarShare')

@section('content')

<div class="bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-green-900 mb-2"
                style="font-family: Fraunces, Georgia, serif">
                Publier un équipement
            </h1>

            <p class="text-gray-600 text-lg">
                Partagez votre équipement avec la communauté SolarShare
            </p>
        </div>

        {{-- Global validation errors --}}
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-5">
                <p class="font-semibold text-red-800 mb-2">
                    Veuillez corriger les erreurs suivantes :
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
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-8">

            <form
                id="equipmentForm"
                method="POST"
                action="{{ route('equipments.store') }}"
                enctype="multipart/form-data"
                class="space-y-6"
            >
                @csrf

                {{-- STEP 1 --}}
                <div id="step-1" class="form-step">

                    <h2 class="text-2xl font-bold text-green-900 mb-6"
                        style="font-family: Fraunces, Georgia, serif">
                        Informations générales
                    </h2>

                    <div class="space-y-5">

                        {{-- Name --}}
                        <div>
                            <label for="name"
                                   class="block text-sm font-semibold text-gray-700 mb-2">
                                Nom de l'équipement
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                placeholder="Ex: Panneau solaire portable 200W"
                                class="w-full px-4 py-3 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500
                                @error('name') border-red-500 @else border-gray-300 @enderror"
                            >

                            @error('name')
                                <p class="text-red-500 text-sm mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Category --}}
                        <div>
                            <label for="category_id"
                                   class="block text-sm font-semibold text-gray-700 mb-2">
                                Catégorie
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="category_id"
                                name="category_id"
                                required
                                class="w-full px-4 py-3 border rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500
                                @error('category_id') border-red-500 @else border-gray-300 @enderror"
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
                                   class="block text-sm font-semibold text-gray-700 mb-2">
                                Marque
                            </label>

                            <input
                                type="text"
                                id="brand"
                                name="brand"
                                value="{{ old('brand') }}"
                                placeholder="Ex: EcoFlow, Jackery, Bluetti..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
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
                                   class="block text-sm font-semibold text-gray-700 mb-2">
                                Description
                                <span class="text-red-500">*</span>
                            </label>

                            <textarea
                                id="description"
                                name="description"
                                required
                                rows="5"
                                maxlength="1000"
                                placeholder="Décrivez votre équipement : caractéristiques, état, accessoires inclus..."
                                class="w-full px-4 py-3 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 resize-none
                                @error('description') border-red-500 @else border-gray-300 @enderror"
                            >{{ old('description') }}</textarea>

                            @error('description')
                                <p class="text-red-500 text-sm mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Power / Capacity --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <div>
                                <label for="power"
                                       class="block text-sm font-semibold text-gray-700 mb-2">
                                    Puissance (W)
                                </label>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    id="power"
                                    name="power"
                                    value="{{ old('power') }}"
                                    placeholder="Ex: 200"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                                >

                                @error('power')
                                    <p class="text-red-500 text-sm mt-1">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label for="capacity"
                                       class="block text-sm font-semibold text-gray-700 mb-2">
                                    Capacité (Wh)
                                </label>

                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    id="capacity"
                                    name="capacity"
                                    value="{{ old('capacity') }}"
                                    placeholder="Ex: 512"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                                >

                                @error('capacity')
                                    <p class="text-red-500 text-sm mt-1">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                        </div>

                        {{-- Condition --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                État de l'équipement
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

                                <label class="condition-option flex items-center justify-center gap-2 p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-400">
                                    <input
                                        type="radio"
                                        name="condition"
                                        value="excellent"
                                        class="hidden"
                                        {{ old('condition', 'good') === 'excellent' ? 'checked' : '' }}
                                    >
                                    <span>✨ Excellent</span>
                                </label>

                                <label class="condition-option flex items-center justify-center gap-2 p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-400">
                                    <input
                                        type="radio"
                                        name="condition"
                                        value="good"
                                        class="hidden"
                                        {{ old('condition', 'good') === 'good' ? 'checked' : '' }}
                                    >
                                    <span>👍 Bon</span>
                                </label>

                                <label class="condition-option flex items-center justify-center gap-2 p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-400">
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

                    <h2 class="text-2xl font-bold text-green-900 mb-6"
                        style="font-family: Fraunces, Georgia, serif">
                        Localisation
                    </h2>

                    <div class="space-y-5">

                        <div>
                            <label for="location"
                                   class="block text-sm font-semibold text-gray-700 mb-2">
                                Ville
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="location"
                                name="location"
                                required
                                class="w-full px-4 py-3 border rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-green-500
                                @error('location') border-red-500 @else border-gray-300 @enderror"
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

                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex gap-3">
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

                    <h2 class="text-2xl font-bold text-green-900 mb-6"
                        style="font-family: Fraunces, Georgia, serif">
                        Photo de l'équipement
                    </h2>

                    <div class="space-y-5">

                        <label
                            for="image"
                            class="block border-2 border-dashed border-green-300 rounded-lg p-12 text-center hover:border-green-500 transition-colors cursor-pointer bg-green-50"
                        >
                            <div class="text-5xl mb-3">📸</div>

                            <p class="font-semibold text-green-900 mb-1">
                                Ajouter une photo
                            </p>

                            <p class="text-sm text-gray-600 mb-4">
                                Cliquez pour sélectionner une image
                            </p>

                            <p class="text-xs text-gray-500">
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
                             class="hidden bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-gray-700">
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

                    <h2 class="text-2xl font-bold text-green-900 mb-6"
                        style="font-family: Fraunces, Georgia, serif">
                        Tarification et disponibilité
                    </h2>

                    <div class="space-y-5">

                        {{-- Price --}}
                        <div>
                            <label for="price_per_day"
                                   class="block text-sm font-semibold text-gray-700 mb-2">
                                Prix par jour
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    id="price_per_day"
                                    name="price_per_day"
                                    value="{{ old('price_per_day') }}"
                                    required
                                    placeholder="0"
                                    class="w-full px-4 py-3 pr-16 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500
                                    @error('price_per_day') border-red-500 @else border-gray-300 @enderror"
                                >

                                <span class="absolute right-3 top-3 text-gray-500 font-medium">
                                    TND
                                </span>
                            </div>

                            @error('price_per_day')
                                <p class="text-red-500 text-sm mt-1">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Availability --}}
                        <div class="border border-gray-200 rounded-lg p-4">

                            <label class="flex items-center gap-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="availability"
                                    value="1"
                                    {{ old('availability', true) ? 'checked' : '' }}
                                    class="w-5 h-5 text-green-600 rounded border-gray-300 focus:ring-green-500"
                                >

                                <div>
                                    <p class="font-semibold text-gray-800">
                                        Équipement disponible
                                    </p>

                                    <p class="text-sm text-gray-500">
                                        Les utilisateurs pourront voir cet équipement
                                        comme disponible à la location.
                                    </p>
                                </div>
                            </label>

                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="status"
                                   class="block text-sm font-semibold text-gray-700 mb-2">
                                Statut
                            </label>

                            <select
                                id="status"
                                name="status"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
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
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6">

                            <p class="text-sm font-semibold text-green-900 mb-4">
                                Estimation de revenus
                            </p>

                            <div class="grid grid-cols-3 gap-3">

                                <div class="bg-white border border-green-200 rounded-lg p-3 text-center">
                                    <p class="text-lg font-bold text-green-700"
                                       id="estimate-week">
                                        0 TND
                                    </p>

                                    <p class="text-xs text-gray-600">
                                        1 semaine
                                    </p>
                                </div>

                                <div class="bg-white border border-green-200 rounded-lg p-3 text-center">
                                    <p class="text-lg font-bold text-green-700"
                                       id="estimate-2weeks">
                                        0 TND
                                    </p>

                                    <p class="text-xs text-gray-600">
                                        2 semaines
                                    </p>
                                </div>

                                <div class="bg-white border border-green-200 rounded-lg p-3 text-center">
                                    <p class="text-lg font-bold text-green-700"
                                       id="estimate-month">
                                        0 TND
                                    </p>

                                    <p class="text-xs text-gray-600">
                                        1 mois
                                    </p>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                {{-- STEP 5 --}}
                <div id="step-5" class="form-step hidden">

                    <h2 class="text-2xl font-bold text-green-900 mb-6"
                        style="font-family: Fraunces, Georgia, serif">
                        Prêt à publier !
                    </h2>

                    <div class="text-center mb-8">
                        <div class="text-6xl mb-4">🎉</div>

                        <p class="text-gray-600">
                            Vérifiez vos informations avant de publier votre équipement.
                        </p>
                    </div>

                    {{-- Summary --}}
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">

                        <div class="space-y-3">

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Nom</span>
                                <span class="font-semibold text-green-900"
                                      id="summary-name">
                                    —
                                </span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Catégorie</span>
                                <span class="font-semibold text-green-900"
                                      id="summary-category">
                                    —
                                </span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Localisation</span>
                                <span class="font-semibold text-green-900"
                                      id="summary-location">
                                    —
                                </span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">État</span>
                                <span class="font-semibold text-green-900"
                                      id="summary-condition">
                                    —
                                </span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Prix</span>
                                <span class="font-semibold text-green-700"
                                      id="summary-price">
                                    —
                                </span>
                            </div>

                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-4 px-6 bg-green-600 hover:bg-green-700 text-white font-bold text-lg rounded-lg transition-colors"
                    >
                        Publier mon équipement
                    </button>

                </div>

                {{-- Navigation --}}
                <div class="flex gap-3 pt-6 border-t border-gray-200">

                    <button
                        type="button"
                        id="prevBtn"
                        class="hidden px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition-colors"
                    >
                        Retour
                    </button>

                    <button
                        type="button"
                        id="nextBtn"
                        class="flex-1 py-3 px-6 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors"
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

    let currentStep = 1;
    const totalSteps = 5;

    const stepIndicators = document.querySelectorAll('.step-indicator');
    const stepConnectors = document.querySelectorAll('.step-connector');
    const formSteps = document.querySelectorAll('.form-step');

    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    const priceInput = document.getElementById('price_per_day');
    const imageInput = document.getElementById('image');

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

    function validateCurrentStep() {

        const currentStepElement =
            document.getElementById('step-' + currentStep);

        const requiredInputs =
            currentStepElement.querySelectorAll('[required]');

        for (const input of requiredInputs) {

            if (!input.checkValidity()) {
                input.reportValidity();
                return false;
            }
        }

        return true;
    }

    nextBtn.addEventListener('click', function () {

        if (!validateCurrentStep()) {
            return;
        }

        if (currentStep < totalSteps) {
            currentStep++;
            showStep(currentStep);
        }
    });

    prevBtn.addEventListener('click', function () {

        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    });

    function updateConditionDisplay() {

        document
            .querySelectorAll('input[name="condition"]')
            .forEach(function (radio) {

                const label = radio.closest('label');

                label.classList.remove(
                    'border-green-500',
                    'bg-green-50'
                );

                label.classList.add('border-gray-300');

                if (radio.checked) {

                    label.classList.remove('border-gray-300');

                    label.classList.add(
                        'border-green-500',
                        'bg-green-50'
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
    showStep(1);
});
</script>

@endsection