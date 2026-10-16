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

        <!-- Form -->
        <form action="{{ route('reclamations.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-8">
            @csrf

            <!-- Reservation Selection -->
            <div class="mb-6">
                <label for="reservation_id" class="block text-sm font-semibold text-gray-900 mb-2">
                    Sélectionnez une réservation <span class="text-red-600">*</span>
                </label>
                <select id="reservation_id" name="reservation_id" class="w-full px-4 py-2 border border-gray-300] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('reservation_id') border-red-500 @enderror" required onchange="updateEquipmentList()">
                    <option value="">-- Choisissez une réservation --</option>
                    @foreach ($reservations as $res)
                        <option value="{{ $res->id }}" data-equipment-id="{{ $res->equipment_id }}" @selected(old('reservation_id') == $res->id)>
                            #{{ $res->id }} - {{ $res->getEquipmentLabelAttribute() }} ({{ $res->date_debut->format('d/m/Y') }} au {{ $res->date_fin->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
                @error('reservation_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-semibold text-gray-900 mb-2">
                    Titre <span class="text-red-600">*</span>
                </label>
                <input type="text" id="title" name="title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500] @enderror" placeholder="Décrivez brièvement votre problème" value="{{ old('title') }}" required>
                @error('title')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-semibold text-gray-900 mb-2">
                    Description <span class="text-red-600">*</span>
                </label>
                <textarea id="description" name="description" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500] @enderror" placeholder="Décrivez les détails du problème..." required>{{ old('description') }}</textarea>
                @error('description')
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
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg">
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
    let incidentCount = 0;

    function addIncidentForm() {
        incidentCount++;
        const container = document.getElementById('incidents-container');
        const html = `
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-6 incident-form" data-incident="${incidentCount}">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-sm font-semibold text-gray-900">Incident #${incidentCount}</h4>
                    <button type="button" class="text-red-600 hover:text-red-800 font-semibold" onclick="removeIncident(${incidentCount})">
                        Supprimer
                    </button>
                </div>

                <!-- Equipment -->
                <div class="mb-4">
                    <label for="incidents_${incidentCount}_equipment_id" class="block text-sm font-semibold text-gray-900 mb-2">
                        Équipement <span class="text-red-600">*</span>
                    </label>
                    <input type="text" value="Équipement de la réservation" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100" disabled>
                    <input type="hidden" name="incidents[${incidentCount}][equipment_id]" value="" required>
                    <p class="text-xs text-gray-600 mt-2">Sera automatiquement lié à la réservation sélectionnée</p>
                </div>

                <!-- Type de problème (hidden, default to 'endommage') -->
                <input type="hidden" name="incidents[${incidentCount}][type_probleme]" value="endommage">

                <!-- Description -->
                <div class="mb-4">
                    <label for="incidents_${incidentCount}_description" class="block text-sm font-semibold text-gray-900 mb-2">
                        Description du problème <span class="text-red-600">*</span>
                    </label>
                    <textarea name="incidents[${incidentCount}][description]" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Décrivez précisément le problème..." required></textarea>
                </div>

                <!-- Damage percentage (hidden, set to null) -->
                <input type="hidden" name="incidents[${incidentCount}][damage_percentage]" value="">

                <!-- Image Evidence Upload -->
                <div class="mb-4">
                    <label for="incidents_${incidentCount}_image" class="block text-sm font-semibold text-gray-900 mb-2">
                        Photo/Preuve (optionnel)
                    </label>
                    <input type="file" name="incidents[${incidentCount}][image]" id="incidents_${incidentCount}_image" accept="image/jpeg,image/png,image/webp" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="previewImage(this, ${incidentCount})">
                    <p class="text-xs text-gray-600 mt-2">Formats acceptés: JPG, PNG, WebP. Taille maximale: 5 MB</p>
                    <div id="preview_${incidentCount}" class="mt-3 hidden">
                        <p class="text-xs text-gray-600 mb-2">Aperçu :</p>
                        <img id="preview_img_${incidentCount}" src="" alt="Preview" class="max-w-xs h-auto rounded-lg border border-gray-300">
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        // Update equipment_id for new incident if reservation is already selected
        updateEquipmentList();
    }

    function removeIncident(incidentNumber) {
        const form = document.querySelector(`[data-incident="${incidentNumber}"]`);
        if (form) {
            form.remove();
        }
    }

    function updateEquipmentList() {
        const reservationSelect = document.getElementById('reservation_id');
        const selectedOption = reservationSelect.options[reservationSelect.selectedIndex];

        // Get equipment_id from data-equipment-id attribute
        const equipmentId = selectedOption.getAttribute('data-equipment-id');

        if (equipmentId) {
            // Set equipment_id for all incidents to the reservation's equipment
            const incidents = document.querySelectorAll('input[name*="[equipment_id]"][type="hidden"]');
            incidents.forEach(input => {
                input.value = equipmentId;  // ✓ FIXED: Now assigns equipment_id, not reservation_id
            });
        }
    }

    function previewImage(input, incidentCount) {
        const preview = document.getElementById(`preview_${incidentCount}`);
        const previewImg = document.getElementById(`preview_img_${incidentCount}`);

        if (input.files && input.files[0]) {
            const file = input.files[0];

            // Validate file type
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                alert('Format non supporté. Utilisez JPG, PNG ou WebP.');
                input.value = '';
                preview.classList.add('hidden');
                return;
            }

            // Validate file size (5 MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('Le fichier est trop volumineux. Taille maximale: 5 MB');
                input.value = '';
                preview.classList.add('hidden');
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            preview.classList.add('hidden');
        }
    }

    // Add initial incident form
    window.addEventListener('load', function() {
        addIncidentForm();
    });

    document.getElementById('add-incident-btn').addEventListener('click', function(e) {
        e.preventDefault();
        addIncidentForm();
    });
</script>
@endsection
