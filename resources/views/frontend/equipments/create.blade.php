@extends('layouts.frontend')

@section('title', 'Publier un équipement - SolarShare')

@section('content')
<div class="bg-gray-50 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-green-900 mb-2" style="font-family: Fraunces, Georgia, serif">
                Publier un équipement
            </h1>
            <p class="text-gray-600 text-lg">Partagez votre équipement avec la communauté SolarShare</p>
        </div>

        <!-- Step Progress Indicator -->
        <div class="mb-8 flex items-center justify-between overflow-x-auto">
            <div class="flex items-center gap-4 min-w-full">
                <div class="flex items-center flex-1">
                    <div class="step-indicator completed-step">
                        <span class="step-number">1</span>
                    </div>
                    <div class="step-label">Informations</div>
                </div>
                <div class="step-connector completed-connector"></div>

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
                    <div class="step-label">Photos</div>
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

        <!-- Form Card -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-200 p-8">
            <form id="equipmentForm" method="POST" action="#" class="space-y-6">
                @csrf

                <!-- Step 1: Informations Générales -->
                <div id="step-1" class="form-step">
                    <h2 class="text-2xl font-bold text-green-900 mb-6" style="font-family: Fraunces, Georgia, serif">
                        Informations générales
                    </h2>

                    <div class="space-y-5">
                        <!-- Nom de l'équipement -->
                        <div>
                            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                Nom de l'équipement <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                required
                                placeholder="Ex: Panneau solaire portable 200W"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                        </div>

                        <!-- Catégorie -->
                        <div>
                            <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">
                                Catégorie <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="category" 
                                name="category" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white"
                            >
                                <option value="">Sélectionner une catégorie</option>
                                <option value="panels">Panneaux solaires</option>
                                <option value="batteries">Batteries</option>
                                <option value="stations">Stations électriques</option>
                                <option value="wind">Équipements éoliens</option>
                                <option value="other">Autres équipements</option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                Description <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                id="description" 
                                name="description" 
                                required
                                rows="5"
                                placeholder="Décrivez votre équipement en détail : caractéristiques, état, accessoires inclus..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"
                            ></textarea>
                            <p class="text-xs text-gray-400 mt-1">0/500 caractères</p>
                        </div>

                        <!-- État de l'équipement -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                État de l'équipement
                            </label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <label class="flex items-center gap-2 p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-400 transition-colors">
                                    <input type="radio" name="condition" value="neuf" class="hidden">
                                    <span class="text-sm">🌟 Neuf</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-400 transition-colors">
                                    <input type="radio" name="condition" value="excellent" class="hidden">
                                    <span class="text-sm">✨ Excellent</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 border-2 border-green-500 rounded-lg cursor-pointer bg-green-50">
                                    <input type="radio" name="condition" value="bon" checked class="hidden">
                                    <span class="text-sm">👍 Bon</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-400 transition-colors">
                                    <input type="radio" name="condition" value="acceptable" class="hidden">
                                    <span class="text-sm">👌 Acceptable</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Localisation -->
                <div id="step-2" class="form-step hidden">
                    <h2 class="text-2xl font-bold text-green-900 mb-6" style="font-family: Fraunces, Georgia, serif">
                        Localisation
                    </h2>

                    <div class="space-y-5">
                        <!-- Ville -->
                        <div>
                            <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">
                                Ville <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="city" 
                                name="city" 
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white"
                            >
                                <option value="">Sélectionner une ville</option>
                                <option value="tunis">Tunis</option>
                                <option value="ariana">Ariana</option>
                                <option value="benarous">Ben Arous</option>
                                <option value="manouba">Manouba</option>
                                <option value="nabeul">Nabeul</option>
                                <option value="bizerte">Bizerte</option>
                                <option value="sousse">Sousse</option>
                                <option value="monastir">Monastir</option>
                                <option value="mahdia">Mahdia</option>
                                <option value="sfax">Sfax</option>
                                <option value="gabes">Gabès</option>
                                <option value="medenine">Médenine</option>
                                <option value="tataouine">Tataouine</option>
                            </select>
                        </div>

                        <!-- Adresse -->
                        <div>
                            <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                                Adresse (optionnel)
                            </label>
                            <input 
                                type="text" 
                                id="address" 
                                name="address"
                                placeholder="Quartier, rue..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                            <p class="text-xs text-gray-500 mt-1">L'adresse exacte n'est partagée qu'après confirmation de la réservation</p>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex gap-3">
                            <span class="text-2xl">📍</span>
                            <p class="text-sm text-green-800">Votre localisation aide les utilisateurs proches à trouver votre équipement plus facilement.</p>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Photos -->
                <div id="step-3" class="form-step hidden">
                    <h2 class="text-2xl font-bold text-green-900 mb-6" style="font-family: Fraunces, Georgia, serif">
                        Photos de l'équipement
                    </h2>

                    <div class="space-y-5">
                        <!-- Upload Area -->
                        <div class="border-2 border-dashed border-green-300 rounded-lg p-12 text-center hover:border-green-500 transition-colors cursor-pointer bg-green-50">
                            <div class="text-5xl mb-3">📸</div>
                            <p class="font-semibold text-green-900 mb-1">Ajouter des photos</p>
                            <p class="text-sm text-gray-600 mb-4">Faites glisser vos photos ici ou cliquez pour parcourir</p>
                            <p class="text-xs text-gray-500 mb-4">JPG, PNG · Max 5 Mo par photo · 3 à 10 photos recommandées</p>
                            <button type="button" class="px-6 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                                Parcourir les fichiers
                            </button>
                            <input type="file" name="images" multiple accept="image/*" class="hidden">
                        </div>

                        <!-- Tips Box -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <p class="text-sm font-semibold text-yellow-900 mb-2">💡 Conseils pour de meilleures photos</p>
                            <ul class="text-sm text-yellow-800 space-y-1">
                                <li>• Prenez des photos dans un endroit bien éclairé</li>
                                <li>• Montrez l'équipement sous plusieurs angles</li>
                                <li>• Incluez les accessoires fournis</li>
                                <li>• Signalez les éventuels défauts visibles</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Tarification -->
                <div id="step-4" class="form-step hidden">
                    <h2 class="text-2xl font-bold text-green-900 mb-6" style="font-family: Fraunces, Georgia, serif">
                        Tarification
                    </h2>

                    <div class="space-y-5">
                        <!-- Price and Period -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="price" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Prix <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input 
                                        type="number" 
                                        id="price" 
                                        name="price" 
                                        required
                                        min="1"
                                        placeholder="0"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    >
                                    <span class="absolute right-3 top-3 text-gray-500 font-medium">TND</span>
                                </div>
                            </div>
                            <div>
                                <label for="period" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Période <span class="text-red-500">*</span>
                                </label>
                                <select 
                                    id="period" 
                                    name="period" 
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white"
                                >
                                    <option value="day">Jour</option>
                                    <option value="week">Semaine</option>
                                    <option value="month">Mois</option>
                                </select>
                            </div>
                        </div>

                        <!-- Minimum Duration -->
                        <div>
                            <label for="minDays" class="block text-sm font-semibold text-gray-700 mb-2">
                                Durée minimale (jours)
                            </label>
                            <input 
                                type="number" 
                                id="minDays" 
                                name="minDays" 
                                value="1"
                                min="1" 
                                max="365"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                        </div>

                        <!-- Revenue Estimation -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                            <p class="text-sm font-semibold text-green-900 mb-4">Estimation de revenus</p>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="bg-white border border-green-200 rounded-lg p-3 text-center">
                                    <p class="text-lg font-bold text-green-700" id="estimate-week">0 TND</p>
                                    <p class="text-xs text-gray-600">1 semaine</p>
                                </div>
                                <div class="bg-white border border-green-200 rounded-lg p-3 text-center">
                                    <p class="text-lg font-bold text-green-700" id="estimate-2weeks">0 TND</p>
                                    <p class="text-xs text-gray-600">2 semaines</p>
                                </div>
                                <div class="bg-white border border-green-200 rounded-lg p-3 text-center">
                                    <p class="text-lg font-bold text-green-700" id="estimate-month">0 TND</p>
                                    <p class="text-xs text-gray-600">1 mois</p>
                                </div>
                            </div>
                            <p class="text-xs text-gray-600 mt-3">* Après déduction de 15% de frais de service SolarShare</p>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Confirmation -->
                <div id="step-5" class="form-step hidden">
                    <h2 class="text-2xl font-bold text-green-900 mb-6" style="font-family: Fraunces, Georgia, serif">
                        Prêt à publier !
                    </h2>

                    <div class="text-center mb-8">
                        <div class="text-6xl mb-4">🎉</div>
                        <p class="text-gray-600 mb-6">Vérifiez vos informations avant de publier votre équipement.</p>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Nom</span>
                                <span class="font-semibold text-green-900" id="summary-name">—</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Catégorie</span>
                                <span class="font-semibold text-green-900" id="summary-category">—</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Ville</span>
                                <span class="font-semibold text-green-900" id="summary-city">—</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Prix</span>
                                <span class="font-semibold text-green-700" id="summary-price">—</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-4 px-6 bg-green-600 hover:bg-green-700 text-white font-bold text-lg rounded-lg transition-colors">
                        Publier mon équipement
                    </button>
                    <p class="text-xs text-gray-500 text-center mt-3">Votre annonce sera vérifiée et publiée dans un délai de 24h.</p>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex gap-3 pt-6 border-t border-gray-200">
                    <button type="button" id="prevBtn" class="hidden px-6 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-100 transition-colors">
                        Retour
                    </button>
                    <button type="button" id="nextBtn" class="flex-1 py-3 px-6 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors">
                        Continuer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .step-indicator {
        @apply w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-600 text-sm relative z-10;
    }

    .step-indicator.active-step {
        @apply bg-green-600 text-white;
    }

    .step-indicator.completed-step {
        @apply bg-green-600 text-white;
    }

    .step-connector {
        @apply flex-1 h-1 bg-gray-300 mx-2;
    }

    .step-connector.completed-connector {
        @apply bg-green-600;
    }

    .step-label {
        @apply text-xs font-medium text-gray-600 whitespace-nowrap;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentStep = 1;
        const totalSteps = 5;

        const stepIndicators = document.querySelectorAll('.step-indicator');
        const stepConnectors = document.querySelectorAll('.step-connector');
        const formSteps = document.querySelectorAll('.form-step');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const form = document.getElementById('equipmentForm');
        const priceInput = document.getElementById('price');

        function updateStepIndicators() {
            stepIndicators.forEach((indicator, index) => {
                const stepNum = index + 1;
                indicator.classList.remove('active-step', 'completed-step');
                
                if (stepNum === currentStep) {
                    indicator.classList.add('active-step');
                } else if (stepNum < currentStep) {
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
            formSteps.forEach(el => el.classList.add('hidden'));
            document.getElementById('step-' + step).classList.remove('hidden');

            if (step === 1) {
                prevBtn.classList.add('hidden');
                nextBtn.classList.remove('hidden');
                nextBtn.textContent = 'Continuer';
            } else if (step === totalSteps) {
                prevBtn.classList.remove('hidden');
                nextBtn.classList.add('hidden');
            } else {
                prevBtn.classList.remove('hidden');
                nextBtn.classList.remove('hidden');
                nextBtn.textContent = 'Continuer';
            }

            updateStepIndicators();
        }

        function updateRevenueEstimates() {
            const price = parseFloat(priceInput.value) || 0;
            const serviceFeeFactor = 0.85;

            document.getElementById('estimate-week').textContent = Math.round(price * 7 * serviceFeeFactor) + ' TND';
            document.getElementById('estimate-2weeks').textContent = Math.round(price * 14 * serviceFeeFactor) + ' TND';
            document.getElementById('estimate-month').textContent = Math.round(price * 30 * serviceFeeFactor) + ' TND';
        }

        function updateConditionDisplay() {
            const selected = document.querySelector('input[name="condition"]:checked');
            document.querySelectorAll('input[name="condition"]').forEach(radio => {
                const label = radio.closest('label');
                label.classList.remove('border-green-500', 'bg-green-50');
                label.classList.add('border-gray-300');
            });
            if (selected) {
                const label = selected.closest('label');
                label.classList.remove('border-gray-300');
                label.classList.add('border-green-500', 'bg-green-50');
            }
        }

        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        });

        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentStep < totalSteps) {
                currentStep++;
                if (currentStep === totalSteps) {
                    updateSummary();
                }
                showStep(currentStep);
            }
        });

        priceInput.addEventListener('input', updateRevenueEstimates);

        document.querySelectorAll('input[name="condition"]').forEach(radio => {
            radio.addEventListener('change', updateConditionDisplay);
        });

        document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
            indicator.addEventListener('click', function(e) {
                e.preventDefault();
                const stepNum = index + 1;
                if (stepNum <= currentStep) {
                    currentStep = stepNum;
                    showStep(currentStep);
                }
            });
        });

        function updateSummary() {
            document.getElementById('summary-name').textContent = document.getElementById('name').value || '—';
            document.getElementById('summary-category').textContent = document.getElementById('category').value || '—';
            document.getElementById('summary-city').textContent = document.getElementById('city').value || '—';
            const price = document.getElementById('price').value;
            const period = document.getElementById('period').value;
            const periodLabels = { day: 'jour', week: 'semaine', month: 'mois' };
            document.getElementById('summary-price').textContent = price ? price + ' TND / ' + periodLabels[period] : '—';
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Votre équipement a été publié avec succès ! Il sera vérifié et apparaîtra dans un délai de 24h.');
        });

        showStep(1);
    });
</script>

@endsection
