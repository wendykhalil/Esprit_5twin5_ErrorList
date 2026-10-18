@extends('layouts.frontend')

@section('title', 'Créer une Réclamation')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Nouvelle Réclamation</h1>
            <p class="text-gray-600 mt-2">Signalez un problème avec l'équipement de votre réservation</p>
        </div>

        <!-- Errors -->
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

        <!-- Validation Summary -->
        <div id="validation-summary" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 hidden">
            <p class="font-semibold mb-2">Veuillez corriger les erreurs suivantes :</p>
            <ul id="validation-list" class="list-disc list-inside text-sm"></ul>
        </div>

        <!-- Form -->
        <form id="reclamation-form" action="{{ route('reclamations.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-8" novalidate>
            @csrf

            <!-- Reservation Selection -->
            <div class="mb-6">
                <label for="reservation_id" class="block text-sm font-semibold text-gray-900 mb-2">
                    Sélectionnez une réservation <span class="text-red-600">*</span>
                </label>
                <select id="reservation_id" name="reservation_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('reservation_id') border-red-500 @enderror" aria-invalid="false" aria-describedby="error-reservation_id" onchange="updateEquipmentList()">
                    <option value="">-- Choisissez une réservation --</option>
                    @foreach ($reservations as $res)
                        <option value="{{ $res->id }}" data-equipment-id="{{ $res->equipment_id }}" @selected(old('reservation_id') == $res->id)>
                            #{{ $res->id }} - {{ $res->getEquipmentLabelAttribute() }} ({{ $res->date_debut->format('d/m/Y') }} au {{ $res->date_fin->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
                <p id="error-reservation_id" class="text-red-600 text-sm mt-1 hidden"></p>
                @error('reservation_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-semibold text-gray-900 mb-2">
                    Titre <span class="text-red-600">*</span>
                </label>
                <input type="text" id="title" name="title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror" placeholder="Titre général de la réclamation" value="{{ old('title') }}" aria-invalid="false" aria-describedby="error-title">
                <p id="error-title" class="text-red-600 text-sm mt-1 hidden"></p>
                @error('title')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Incidents Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Incidents <span class="text-red-600">*</span></h3>
                <p class="text-sm text-gray-600 mb-4">Ajoutez au moins un incident relatif à cette réclamation</p>

                <div id="incidents-container">
                    <!-- Incidents will be added here -->
                </div>

                <button type="button" id="add-incident-btn" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center mt-4">
                    <span class="text-lg">+</span> <span class="ml-2">Ajouter un incident</span>
                </button>

                <p id="error-incidents" class="text-red-600 text-sm mt-2 hidden"></p>
            </div>

            <!-- Errors for incidents -->
            @if ($errors->has('incidents'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    @foreach ($errors->get('incidents') as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Buttons -->
            <div class="flex gap-4 pt-6 border-t border-gray-200">
                <button type="submit" id="submit-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg">
                    Soumettre la Réclamation
                </button>
                <a href="{{ route('reclamations.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold py-2 px-6 rounded-lg">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    const FormValidator = (() => {
        const config = {
            maxTitle: 255,
            maxDescription: 5000,
            maxIncidentDescription: 2000,
            maxFileSize: 5 * 1024 * 1024, // 5 MB
            acceptedImageFormats: ['image/jpeg', 'image/png', 'image/webp'],
        };

        const state = {
            incidentCount: 0,
            errors: {},
            isSubmitting: false,
        };

        // ============ VALIDATION FUNCTIONS ============

        function validateReservation() {
            const select = document.getElementById('reservation_id');
            const value = select.value.trim();
            const errorEl = document.getElementById('error-reservation_id');

            if (!value || value === '') {
                showError('reservation_id', 'Veuillez sélectionner une réservation');
                return false;
            }

            clearError('reservation_id');
            return true;
        }

        function validateTitle() {
            const input = document.getElementById('title');
            const value = input.value.trim();
            const errorEl = document.getElementById('error-title');

            if (!value) {
                showError('title', 'Le titre est requis');
                return false;
            }

            if (value.length > config.maxTitle) {
                showError('title', `Le titre ne peut pas dépasser ${config.maxTitle} caractères`);
                return false;
            }

            clearError('title');
            return true;
        }

        function validateIncidents() {
            const container = document.getElementById('incidents-container');
            const incidents = container.querySelectorAll('[data-incident]');

            if (incidents.length === 0) {
                showError('incidents', 'Au moins un incident est requis');
                return false;
            }

            let allValid = true;

            incidents.forEach((form) => {
                const incidentNum = form.getAttribute('data-incident');
                const typeSelect = form.querySelector(`select[name*="[type_probleme]"]`);
                const titreInput = form.querySelector(`input[name*="[titre]"]`);
                const descriptionInput = form.querySelector(`textarea[name*="[description]"]`);
                const fileInput = form.querySelector(`input[name*="[image]"][type="file"]`);

                // Validate problem type
                const type = typeSelect.value.trim();
                if (!type) {
                    showIncidentError(incidentNum, 'type_probleme', 'Le type de problème est requis');
                    allValid = false;
                } else {
                    clearIncidentError(incidentNum, 'type_probleme');
                }

                // Validate problem title
                const titre = titreInput.value.trim();
                if (!titre) {
                    showIncidentError(incidentNum, 'titre', 'Le titre du problème est requis');
                    allValid = false;
                } else if (titre.length < 3) {
                    showIncidentError(incidentNum, 'titre', 'Le titre doit contenir au moins 3 caractères');
                    allValid = false;
                } else if (titre.length > 255) {
                    showIncidentError(incidentNum, 'titre', 'Le titre ne peut pas dépasser 255 caractères');
                    allValid = false;
                } else {
                    clearIncidentError(incidentNum, 'titre');
                }

                // Validate incident description
                const desc = descriptionInput.value.trim();
                if (!desc) {
                    showIncidentError(incidentNum, 'description', 'La description est requise');
                    allValid = false;
                } else if (desc.length > config.maxIncidentDescription) {
                    showIncidentError(incidentNum, 'description', `Maksimum ${config.maxIncidentDescription} caractères`);
                    allValid = false;
                } else {
                    clearIncidentError(incidentNum, 'description');
                }

                // Validate incident image if provided
                if (fileInput && fileInput.files && fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    if (!isValidImage(file)) {
                        showIncidentError(incidentNum, 'image', getFileErrorMessage(file));
                        allValid = false;
                    } else {
                        clearIncidentError(incidentNum, 'image');
                    }
                }
            });

            if (allValid) {
                clearError('incidents');
            } else if (incidents.length > 0) {
                // Only set the summary error if there's at least one incident
                showError('incidents', 'Veuillez corriger les erreurs dans les incidents');
            }

            return allValid;
        }

        function isValidImage(file) {
            if (!config.acceptedImageFormats.includes(file.type)) {
                return false;
            }
            if (file.size > config.maxFileSize) {
                return false;
            }
            return true;
        }

        function getFileErrorMessage(file) {
            if (!config.acceptedImageFormats.includes(file.type)) {
                return 'Format non supporté. Utilisez JPG, PNG ou WebP';
            }
            if (file.size > config.maxFileSize) {
                return 'Le fichier est trop volumineux. Taille maximale: 5 MB';
            }
            return 'Erreur fichier invalide';
        }

        // ============ ERROR DISPLAY FUNCTIONS ============

        function showError(field, message) {
            const errorEl = document.getElementById(`error-${field}`);
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');

                // Highlight the field
                const fieldEl = document.getElementById(field);
                if (fieldEl) {
                    fieldEl.setAttribute('aria-invalid', 'true');
                    fieldEl.classList.add('border-red-500', 'bg-red-50');
                }
            }

            state.errors[field] = message;
        }

        function clearError(field) {
            const errorEl = document.getElementById(`error-${field}`);
            if (errorEl) {
                errorEl.textContent = '';
                errorEl.classList.add('hidden');

                // Remove highlight
                const fieldEl = document.getElementById(field);
                if (fieldEl) {
                    fieldEl.setAttribute('aria-invalid', 'false');
                    fieldEl.classList.remove('border-red-500', 'bg-red-50');
                }
            }

            delete state.errors[field];
        }

        function showIncidentError(incidentNum, field, message) {
            const form = document.querySelector(`[data-incident="${incidentNum}"]`);
            if (!form) return;

            let errorEl = form.querySelector(`#error-incident_${incidentNum}_${field}`);
            if (!errorEl) {
                const input = form.querySelector(`[name*="[${field}]"]`);
                if (input) {
                    errorEl = document.createElement('p');
                    errorEl.id = `error-incident_${incidentNum}_${field}`;
                    errorEl.className = 'text-red-600 text-sm mt-1';
                    input.parentElement.appendChild(errorEl);
                }
            }

            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');

                // Highlight the input
                const input = form.querySelector(`[name*="[${field}]"]`);
                if (input) {
                    input.setAttribute('aria-invalid', 'true');
                    input.classList.add('border-red-500', 'bg-red-50');
                }
            }
        }

        function clearIncidentError(incidentNum, field) {
            const form = document.querySelector(`[data-incident="${incidentNum}"]`);
            if (!form) return;

            const errorEl = form.querySelector(`#error-incident_${incidentNum}_${field}`);
            if (errorEl) {
                errorEl.textContent = '';
                errorEl.classList.add('hidden');
            }

            // Remove highlight
            const input = form.querySelector(`[name*="[${field}]"]`);
            if (input) {
                input.setAttribute('aria-invalid', 'false');
                input.classList.remove('border-red-500', 'bg-red-50');
            }
        }

        function updateValidationSummary() {
            const summary = document.getElementById('validation-summary');
            const list = document.getElementById('validation-list');

            if (Object.keys(state.errors).length > 0) {
                list.innerHTML = Object.values(state.errors)
                    .map(error => `<li>${error}</li>`)
                    .join('');
                summary.classList.remove('hidden');

                // Focus first invalid field
                const firstError = Object.keys(state.errors)[0];
                const firstEl = document.getElementById(firstError);
                if (firstEl) {
                    firstEl.focus();
                    firstEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                summary.classList.add('hidden');
            }
        }

        // ============ INCIDENT MANAGEMENT ============

        function addIncidentForm() {
            state.incidentCount++;
            const container = document.getElementById('incidents-container');
            const html = `
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-6 incident-form" data-incident="${state.incidentCount}">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-sm font-semibold text-gray-900">Incident #${state.incidentCount}</h4>
                        <button type="button" class="text-red-600 hover:text-red-800 font-semibold" onclick="FormValidator.removeIncident(${state.incidentCount})">
                            Supprimer
                        </button>
                    </div>

                    <!-- Equipment -->
                    <div class="mb-4">
                        <label for="incidents_${state.incidentCount}_equipment_id" class="block text-sm font-semibold text-gray-900 mb-2">
                            Équipement <span class="text-red-600">*</span>
                        </label>
                        <input type="text" value="Équipement de la réservation" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100" disabled>
                        <input type="hidden" name="incidents[${state.incidentCount}][equipment_id]" value="" required>
                        <p class="text-xs text-gray-600 mt-2">Sera automatiquement lié à la réservation sélectionnée</p>
                    </div>

                    <!-- Type de problème -->
                    <div class="mb-4">
                        <label for="incidents_${state.incidentCount}_type_probleme" class="block text-sm font-semibold text-gray-900 mb-2">
                            Type de problème <span class="text-red-600">*</span>
                        </label>
                        <select name="incidents[${state.incidentCount}][type_probleme]" id="incidents_${state.incidentCount}_type_probleme" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">-- Choisissez un type --</option>
                            <option value="endommage">Endommagé</option>
                            <option value="manquant">Manquant</option>
                            <option value="malfonctionnant">Malfonctionne</option>
                            <option value="autre">Autre</option>
                        </select>
                        <p id="error-incident_${state.incidentCount}_type_probleme" class="text-red-600 text-sm mt-1 hidden"></p>
                    </div>

                    <!-- Titre du problème -->
                    <div class="mb-4">
                        <label for="incidents_${state.incidentCount}_titre" class="block text-sm font-semibold text-gray-900 mb-2">
                            Titre du problème <span class="text-red-600">*</span>
                        </label>
                        <input type="text" name="incidents[${state.incidentCount}][titre]" id="incidents_${state.incidentCount}_titre" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: Panneau solaire cassé, Connecteur endommagé..." maxlength="255" aria-invalid="false" aria-describedby="error-incident_${state.incidentCount}_titre" onblur="FormValidator.validateIncidentTitre(${state.incidentCount})">
                        <p class="text-xs text-gray-600 mt-2">Résumé du problème (3-255 caractères)</p>
                        <p id="error-incident_${state.incidentCount}_titre" class="text-red-600 text-sm mt-1 hidden"></p>
                    </div>

                    <!-- AI Generation Button -->
                    <div class="mb-4">
                        <button type="button" id="generate-ai_${state.incidentCount}" class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white font-semibold py-2 px-4 rounded-lg transition" onclick="FormValidator.generateAIDescription(${state.incidentCount})">
                            <span>✨</span>
                            <span>Générer la description avec l'IA</span>
                        </button>
                        <p id="loading-incident_${state.incidentCount}" class="text-purple-600 text-sm mt-2 hidden flex items-center gap-2">
                            <span class="inline-block animate-spin">⏳</span>
                            <span>Génération en cours...</span>
                        </p>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="incidents_${state.incidentCount}_description" class="block text-sm font-semibold text-gray-900 mb-2">
                            Description du problème <span class="text-red-600">*</span>
                        </label>
                        <textarea name="incidents[${state.incidentCount}][description]" id="incidents_${state.incidentCount}_description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Décrivez précisément le problème..." aria-invalid="false" aria-describedby="error-incident_${state.incidentCount}_description" onblur="FormValidator.validateIncidentDescription(${state.incidentCount})"></textarea>
                        <p id="error-incident_${state.incidentCount}_description" class="text-red-600 text-sm mt-1 hidden"></p>
                    </div>

                    <!-- Damage percentage (hidden, set to null) -->
                    <input type="hidden" name="incidents[${state.incidentCount}][damage_percentage]" value="">

                    <!-- Image Evidence Upload -->
                    <div class="mb-4">
                        <label for="incidents_${state.incidentCount}_image" class="block text-sm font-semibold text-gray-900 mb-2">
                            Photo/Preuve (optionnel)
                        </label>
                        <input type="file" name="incidents[${state.incidentCount}][image]" id="incidents_${state.incidentCount}_image" accept="image/jpeg,image/png,image/webp" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" aria-invalid="false" aria-describedby="error-incident_${state.incidentCount}_image" onchange="FormValidator.validateIncidentImage(${state.incidentCount})">
                        <p class="text-xs text-gray-600 mt-2">Formats acceptés: JPG, PNG, WebP. Taille maximale: 5 MB</p>
                        <p id="error-incident_${state.incidentCount}_image" class="text-red-600 text-sm mt-1 hidden"></p>
                        <div id="preview_${state.incidentCount}" class="mt-3 hidden">
                            <p class="text-xs text-gray-600 mb-2">Aperçu :</p>
                            <img id="preview_img_${state.incidentCount}" src="" alt="Preview" class="max-w-xs h-auto rounded-lg border border-gray-300">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            updateEquipmentList();
        }

        function removeIncident(incidentNumber) {
            const form = document.querySelector(`[data-incident="${incidentNumber}"]`);
            if (form) {
                form.remove();
            }
            // Revalidate incidents
            validateIncidents();
        }

        function validateIncidentDescription(incidentNum) {
            const form = document.querySelector(`[data-incident="${incidentNum}"]`);
            if (!form) return;

            const textarea = form.querySelector('textarea[name*="[description]"]');
            const value = textarea.value.trim();

            if (!value) {
                showIncidentError(incidentNum, 'description', 'La description est requise');
                return false;
            }

            if (value.length > config.maxIncidentDescription) {
                showIncidentError(incidentNum, 'description', `Maximum ${config.maxIncidentDescription} caractères`);
                return false;
            }

            clearIncidentError(incidentNum, 'description');
            return true;
        }

        function validateIncidentImage(incidentNum) {
            const form = document.querySelector(`[data-incident="${incidentNum}"]`);
            if (!form) return;

            const fileInput = form.querySelector('input[name*="[image]"][type="file"]');
            const previewContainer = form.querySelector(`#preview_${incidentNum}`);
            const previewImg = form.querySelector(`#preview_img_${incidentNum}`);

            if (!fileInput.files || fileInput.files.length === 0) {
                clearIncidentError(incidentNum, 'image');
                previewContainer.classList.add('hidden');
                return true;
            }

            const file = fileInput.files[0];

            if (!isValidImage(file)) {
                showIncidentError(incidentNum, 'image', getFileErrorMessage(file));
                fileInput.value = '';
                previewContainer.classList.add('hidden');
                return false;
            }

            clearIncidentError(incidentNum, 'image');

            // Show preview
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                previewContainer.classList.remove('hidden');
            };
            reader.readAsDataURL(file);

            return true;
        }

        function updateEquipmentList() {
            const reservationSelect = document.getElementById('reservation_id');
            const selectedOption = reservationSelect.options[reservationSelect.selectedIndex];
            const equipmentId = selectedOption.getAttribute('data-equipment-id');

            // Populate equipment_id for all incidents, even if it's "0" or falsy
            const incidents = document.querySelectorAll('input[name*="[equipment_id]"][type="hidden"]');
            incidents.forEach(input => {
                input.value = equipmentId || '';
            });
        }

        function validateIncidentType(incidentNum) {
            const form = document.querySelector(`[data-incident="${incidentNum}"]`);
            if (!form) return;

            const select = form.querySelector('select[name*="[type_probleme]"]');
            const value = select.value.trim();

            if (!value) {
                showIncidentError(incidentNum, 'type_probleme', 'Le type de problème est requis');
                return false;
            }

            clearIncidentError(incidentNum, 'type_probleme');
            return true;
        }

        function validateIncidentTitre(incidentNum) {
            const form = document.querySelector(`[data-incident="${incidentNum}"]`);
            if (!form) return;

            const input = form.querySelector('input[name*="[titre]"]');
            const value = input.value.trim();

            if (!value) {
                showIncidentError(incidentNum, 'titre', 'Le titre du problème est requis');
                return false;
            }

            if (value.length < 3) {
                showIncidentError(incidentNum, 'titre', 'Le titre doit contenir au moins 3 caractères');
                return false;
            }

            if (value.length > 255) {
                showIncidentError(incidentNum, 'titre', 'Le titre ne peut pas dépasser 255 caractères');
                return false;
            }

            clearIncidentError(incidentNum, 'titre');
            return true;
        }

        async function generateAIDescription(incidentNum) {
            // Validate inputs first
            if (!validateIncidentType(incidentNum)) {
                showIncidentError(incidentNum, 'type_probleme', 'Le type de problème est requis');
                return;
            }

            if (!validateIncidentTitre(incidentNum)) {
                return;
            }

            const form = document.querySelector(`[data-incident="${incidentNum}"]`);
            if (!form) return;

            const typeSelect = form.querySelector('select[name*="[type_probleme]"]');
            const titreInput = form.querySelector('input[name*="[titre]"]');
            const descriptionTextarea = form.querySelector('textarea[name*="[description]"]');
            const button = document.getElementById(`generate-ai_${incidentNum}`);
            const loadingEl = document.getElementById(`loading-incident_${incidentNum}`);

            if (!typeSelect || !titreInput || !descriptionTextarea) return;

            const problemType = typeSelect.value;
            const problemTitle = titreInput.value.trim();
            const reservationId = document.getElementById('reservation_id').value;
            
            // Extract reservation dates from the selected option
            const reservationSelect = document.getElementById('reservation_id');
            const selectedOption = reservationSelect.options[reservationSelect.selectedIndex];
            const reservationText = selectedOption.text; // e.g., "#123 - Equipment (01/01/2024 au 02/01/2024)"
            
            // Parse dates from the option text (format: "... (01/01/2024 au 02/01/2024)")
            let dateDebut = null;
            let dateFin = null;
            const dateMatch = reservationText.match(/\((\d{2}\/\d{2}\/\d{4})\s+au\s+(\d{2}\/\d{2}\/\d{4})\)/);
            if (dateMatch) {
                dateDebut = dateMatch[1];
                dateFin = dateMatch[2];
            }

            // Show loading state
            button.disabled = true;
            button.classList.add('opacity-50', 'cursor-not-allowed');
            loadingEl.classList.remove('hidden');

            try {
                const response = await fetch('{{ route("incidents.generate-description") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        reservation_id: reservationId,
                        problem_type: problemType,
                        problem_title: problemTitle,
                        date_debut: dateDebut,
                        date_fin: dateFin,
                    }),
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Check if textarea already has content
                    const existingContent = descriptionTextarea.value.trim();
                    
                    if (existingContent && existingContent.length > 10) {
                        // Ask user before replacing substantial existing content
                        const shouldReplace = confirm('Une description existe déjà. Voulez-vous la remplacer par la description générée ?');
                        if (!shouldReplace) {
                            button.disabled = false;
                            button.classList.remove('opacity-50', 'cursor-not-allowed');
                            loadingEl.classList.add('hidden');
                            return;
                        }
                    }
                    
                    // Insert generated description into textarea
                    descriptionTextarea.value = data.description;
                    descriptionTextarea.classList.remove('border-red-500', 'bg-red-50');
                    descriptionTextarea.classList.add('border-green-500', 'bg-green-50');

                    // Clear error if any
                    clearIncidentError(incidentNum, 'description');

                    // Show success message briefly
                    const successMsg = document.createElement('p');
                    successMsg.className = 'text-green-600 text-sm mt-2 flex items-center gap-2';
                    successMsg.innerHTML = '<span>✓</span><span>Description générée avec succès. Vous pouvez l\'éditer avant de soumettre.</span>';
                    descriptionTextarea.parentElement.appendChild(successMsg);

                    setTimeout(() => {
                        successMsg.remove();
                        descriptionTextarea.classList.remove('border-green-500', 'bg-green-50');
                    }, 3000);
                } else {
                    const errorMsg = data.message || 'Erreur lors de la génération de la description';
                    showIncidentError(incidentNum, 'description', errorMsg);
                }
            } catch (error) {
                console.error('AI Generation error:', error);
                showIncidentError(incidentNum, 'description', 'Erreur de connexion. Assurez-vous que le service Ollama est en cours d\'exécution sur http://127.0.0.1:11434');
            } finally {
                // Reset button state
                button.disabled = false;
                button.classList.remove('opacity-50', 'cursor-not-allowed');
                loadingEl.classList.add('hidden');
            }
        }

        // ============ FORM SUBMISSION ============

        function handleFormSubmit(e) {
            e.preventDefault();

            if (state.isSubmitting) return;
            state.isSubmitting = true;

            // Clear previous errors
            state.errors = {};

            // Validate all fields
            const isReservationValid = validateReservation();
            const isTitleValid = validateTitle();
            const areIncidentsValid = validateIncidents();

            updateValidationSummary();

            if (isReservationValid && isTitleValid && areIncidentsValid) {
                // All valid, submit the form
                document.getElementById('reclamation-form').submit();
            } else {
                state.isSubmitting = false;
            }
        }

        // ============ INPUT LISTENERS ============

        function setupInputListeners() {
            const form = document.getElementById('reclamation-form');

            document.getElementById('reservation_id').addEventListener('change', () => {
                validateReservation();
                updateEquipmentList();  // ← CRITICAL FIX: Update equipment when reservation changes
            });

            document.getElementById('title').addEventListener('blur', () => {
                validateTitle();
            });

            form.addEventListener('submit', handleFormSubmit);
        }

        // ============ PUBLIC API ============

        return {
            init() {
                setupInputListeners();
                addIncidentForm(); // Add initial incident
                document.getElementById('add-incident-btn').addEventListener('click', (e) => {
                    e.preventDefault();
                    addIncidentForm();
                });
            },
            addIncidentForm,
            removeIncident,
            validateIncidentDescription,
            validateIncidentImage,
            validateIncidentType,
            validateIncidentTitre,
            generateAIDescription,
            updateEquipmentList,
        };
    })();

    // Initialize on page load
    window.addEventListener('load', () => {
        FormValidator.init();
    });
</script>

@endsection
