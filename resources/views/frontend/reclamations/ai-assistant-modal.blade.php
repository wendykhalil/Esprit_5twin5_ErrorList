<!-- AI Reclamation Assistant Modal -->
<div id="ai-assistant-modal" 
     x-show="isOpen" 
     x-cloak
     @keydown.escape.window="closeModal()"
     class="fixed inset-0 z-50 overflow-y-auto">
    
    <!-- Backdrop: Behind the dialog -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity z-40" 
         @click="closeModal()"
         :class="{ 'opacity-0 pointer-events-none': !isOpen }"></div>

    <!-- Modal Container: Centers the dialog above backdrop -->
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none z-50">
        <div class="bg-white rounded-lg shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col pointer-events-auto"
             @click.stop
             x-transition>

            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold">✨ Créer une Réclamation avec l'IA</h2>
                    <p class="text-sm text-blue-100 mt-1">Assistant conversationnel guidé</p>
                </div>
                <button @click="closeModal()" class="text-white hover:text-blue-100 focus:outline-none text-2xl">
                    ×
                </button>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- Step 1: Select Equipment -->
                <div x-show="currentStep === 'select_equipment'" class="space-y-4">
                    <div>
                        <p class="text-lg font-semibold text-gray-900 mb-4">
                            Pour quel équipement souhaitez-vous créer une réclamation ?
                        </p>
                        <select x-model="draft.selectedReservation"
                                @change="onEquipmentSelected()"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-base">
                            <option value="">-- Sélectionnez une réservation --</option>
                            <template x-for="res in reservations" :key="res.id">
                                <option :value="res.id" x-text="`#${res.id} - ${res.equipment_name} (${res.date_debut} au ${res.date_fin})`"></option>
                            </template>
                        </select>
                        <p x-show="!reservations.length" class="text-sm text-red-600 mt-2">
                            Aucune réservation disponible.
                        </p>
                    </div>
                </div>

                <!-- Step 2: Describe Problem -->
                <div x-show="currentStep === 'describe_problem'" class="space-y-4">
                    <div>
                        <p class="text-lg font-semibold text-gray-900 mb-4">
                            Que s'est-il passé avec cet équipement ? Décrivez le problème avec vos propres mots.
                        </p>
                        <textarea x-model="draft.clientExplanation"
                                  placeholder="Ex: Les câbles sont cassés, l'équipement ne fonctionne plus..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 h-32 resize-none text-base"
                                  minlength="20"
                                  maxlength="1000"></textarea>
                        <p class="text-xs text-gray-500 mt-1" x-text="`${draft.clientExplanation.length}/1000 caractères (minimum 20)`"></p>
                    </div>
                </div>

                <!-- Step 3: Upload Evidence -->
                <div x-show="currentStep === 'upload_evidence'" class="space-y-4">
                    <div>
                        <p class="text-lg font-semibold text-gray-900 mb-2">
                            Pouvez-vous ajouter une photo qui montre le problème ?
                        </p>
                        <p class="text-sm text-gray-600 mb-4">
                            (Optionnel - Formats: JPEG, PNG, WebP - Max 5 MB)
                        </p>

                        <!-- Upload Area -->
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 transition"
                             @dragover.prevent="dragOver = true"
                             @dragleave.prevent="dragOver = false"
                             @drop.prevent="handleFileDrop($event)"
                             :class="{ 'border-blue-500 bg-blue-50': dragOver }">
                            <input type="file"
                                   @change="handleFileUpload($event)"
                                   accept="image/jpeg,image/jpg,image/png,image/webp"
                                   class="hidden"
                                   id="evidence-upload" />
                            <label for="evidence-upload" class="cursor-pointer">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-sm text-gray-600">Glissez une image ou cliquez pour parcourir</p>
                                <p class="text-xs text-gray-500 mt-1">JPEG, PNG, WebP - Max 5 MB</p>
                            </label>
                        </div>

                        <!-- Image Preview -->
                        <div x-show="draft.attachmentPreview" class="mt-4 space-y-3">
                            <div class="bg-gray-100 p-4 rounded-lg flex gap-4">
                                <img :src="draft.attachmentPreview" alt="Preview" class="h-20 w-20 object-cover rounded">
                                <div class="flex-1 flex flex-col justify-center">
                                    <p class="text-sm font-semibold text-gray-900">Image téléchargée</p>
                                    <button @click="removeAttachment()" type="button" class="text-red-600 hover:text-red-800 text-sm font-semibold mt-2">
                                        ✕ Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Review & Confirm -->
                <div x-show="currentStep === 'review_confirm'" class="space-y-6">
                    <!-- Loading State -->
                    <div x-show="isLoading" class="flex items-center justify-center space-x-2 py-8">
                        <div class="w-3 h-3 bg-blue-600 rounded-full animate-bounce"></div>
                        <div class="w-3 h-3 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                        <div class="w-3 h-3 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0.4s;"></div>
                        <span class="ml-2 text-sm text-gray-600">Génération en cours...</span>
                    </div>

                    <!-- Generated Fields -->
                    <div x-show="!isLoading" class="space-y-4">
                        <p class="text-lg font-semibold text-gray-900">Vérifiez et modifiez si nécessaire</p>

                        <!-- Type de problème -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                Type de problème
                            </label>
                            <select x-model="draft.type_probleme"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="endommage">Endommagé</option>
                                <option value="manquant">Manquant</option>
                                <option value="malfonctionnant">Mal fonctionnant</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>

                        <!-- Titre -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                Titre du problème
                            </label>
                            <input type="text"
                                   x-model="draft.titre"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   maxlength="255" />
                            <p class="text-xs text-gray-500 mt-1" x-text="`${draft.titre.length}/255 caractères`"></p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-900 mb-2">
                                Description du problème
                            </label>
                            <textarea x-model="draft.description"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 h-24 resize-none"
                                      maxlength="2000"></textarea>
                            <p class="text-xs text-gray-500 mt-1" x-text="`${draft.description.length}/2000 caractères`"></p>
                        </div>

                        <!-- Equipment Info -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm font-semibold text-blue-900 mb-2">Informations de la réservation</p>
                            <p class="text-sm text-blue-800" x-text="`Équipement: ${draft.selectedEquipment.equipment_name}`"></p>
                            <p class="text-sm text-blue-800" x-text="`Période: ${draft.selectedEquipment.date_debut} au ${draft.selectedEquipment.date_fin}`"></p>
                        </div>

                        <!-- Evidence Preview -->
                        <div x-show="draft.attachmentPreview" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-sm font-semibold text-gray-900 mb-2">Preuve jointe</p>
                            <img :src="draft.attachmentPreview" alt="Evidence" class="h-24 w-24 object-cover rounded">
                        </div>
                    </div>
                </div>

                <!-- Error Display -->
                <div x-show="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <p class="font-semibold">Erreur</p>
                    <p x-text="error" class="text-sm"></p>
                </div>
            </div>

            <!-- Footer with Buttons -->
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600" x-text="`Étape ${getCurrentStepNumber()} de 4`"></p>
                    <p class="text-xs text-gray-500" x-text="currentStepLabel"></p>
                </div>
                <div class="flex gap-3">
                    <!-- Back Button (not on first step) -->
                    <button x-show="currentStep !== 'select_equipment'"
                            @click="goBack()"
                            class="px-4 py-2 border border-gray-300 text-gray-900 font-semibold rounded-lg hover:bg-gray-100">
                        ← Retour
                    </button>

                    <!-- Next/Generate Button -->
                    <button x-show="currentStep === 'select_equipment' && draft.selectedReservation"
                            @click="currentStep = 'describe_problem'"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                        Suivant →
                    </button>

                    <button x-show="currentStep === 'describe_problem' && draft.clientExplanation.length >= 20"
                            @click="proceedToEvidence()"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                        Suivant →
                    </button>

                    <button x-show="currentStep === 'upload_evidence' && !isLoading"
                            @click="proceedToGeneration()"
                            :disabled="isLoading"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 text-white font-semibold rounded-lg">
                        ✨ Générer ma réclamation
                    </button>

                    <!-- Confirm & Submit Button -->
                    <button x-show="currentStep === 'review_confirm' && !isLoading"
                            @click="submitReclamation()"
                            :disabled="isSubmitting"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold rounded-lg">
                        <span x-show="!isSubmitting">✓ Confirmer et envoyer</span>
                        <span x-show="isSubmitting">⏳ Envoi...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
