/**
 * AI Reclamation Assistant - Alpine.js Component
 * Simplified 4-step workflow: Select equipment → Describe problem → Upload evidence → Review & confirm
 */

function aiReclamationAssistant() {
    return {
        // State
        isOpen: false,
        currentStep: 'select_equipment', // Steps: select_equipment, describe_problem, upload_evidence, review_confirm
        isLoading: false,
        isSubmitting: false,
        error: null,
        dragOver: false,

        // Reservations
        reservations: [],

        // Draft reclamation data
        draft: {
            selectedReservation: null,
            selectedEquipment: null,
            clientExplanation: '',
            attachment: null,
            attachmentPreview: null,
            // AI-generated fields
            type_probleme: null,
            titre: null,
            description: null,
        },

        // Step labels
        stepLabels: {
            select_equipment: 'Sélectionner l\'équipement',
            describe_problem: 'Décrire le problème',
            upload_evidence: 'Ajouter une preuve',
            review_confirm: 'Vérifier et confirmer',
        },

        // Initialize
        async init() {
            this.loadReservations();
        },

        // Load eligible reservations
        async loadReservations() {
            try {
                const response = await fetch('/reclamations/ai-assistant/reservations', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                });

                if (!response.ok) {
                    throw new Error('Erreur lors du chargement des réservations');
                }

                const data = await response.json();
                this.reservations = data.reservations || [];

                if (!this.reservations.length) {
                    this.error = 'Aucune réservation disponible pour créer une réclamation.';
                }
            } catch (err) {
                console.error('Error loading reservations:', err);
                this.error = err.message || 'Erreur lors du chargement des réservations.';
            }
        },

        // Open modal
        openModal() {
            this.isOpen = true;
            this.init();
            document.body.style.overflow = 'hidden';
        },

        // Close modal
        closeModal() {
            const hasContent = this.draft.clientExplanation.trim() || this.draft.attachment;
            if (hasContent && this.currentStep !== 'review_confirm') {
                if (!confirm('Vous avez des données en cours. Êtes-vous sûr de vouloir fermer ?')) {
                    return;
                }
            }
            this.isOpen = false;
            this.resetState();
            document.body.style.overflow = '';
        },

        // Reset state
        resetState() {
            this.currentStep = 'select_equipment';
            this.error = null;
            this.isLoading = false;
            this.isSubmitting = false;
            this.draft = {
                selectedReservation: null,
                selectedEquipment: null,
                clientExplanation: '',
                attachment: null,
                attachmentPreview: null,
                type_probleme: null,
                titre: null,
                description: null,
            };
        },

        // Handle equipment selection
        onEquipmentSelected() {
            if (!this.draft.selectedReservation) return;

            const reservation = this.reservations.find(r => r.id == this.draft.selectedReservation);
            if (reservation) {
                this.draft.selectedEquipment = reservation;
                this.currentStep = 'describe_problem';
                this.error = null;
            }
        },

        // Proceed to upload evidence step
        proceedToEvidence() {
            if (!this.draft.clientExplanation.trim()) {
                this.error = 'Veuillez expliquer le problème.';
                return;
            }

            this.currentStep = 'upload_evidence';
            this.error = null;
        },

        // Proceed to generation step (after evidence upload or skip)
        async proceedToGeneration() {
            this.isLoading = true;
            this.error = null;

            try {
                const response = await fetch('/reclamations/ai-assistant/generate-reclamation-details', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        reservation_id: this.draft.selectedReservation,
                        explanation: this.draft.clientExplanation,
                    }),
                });

                if (!response.ok) {
                    const data = await response.json();
                    throw new Error(data.message || 'Erreur lors de la génération des détails');
                }

                const data = await response.json();
                this.draft.type_probleme = data.type_probleme;
                this.draft.titre = data.titre;
                this.draft.description = data.description;
                this.currentStep = 'review_confirm';
            } catch (err) {
                console.error('Error generating reclamation details:', err);
                this.error = err.message || 'Erreur lors de la génération des détails.';
            } finally {
                this.isLoading = false;
            }
        },

        // Go back to previous step
        goBack() {
            if (this.currentStep === 'describe_problem') {
                this.currentStep = 'select_equipment';
            } else if (this.currentStep === 'upload_evidence') {
                this.currentStep = 'describe_problem';
            } else if (this.currentStep === 'review_confirm') {
                this.currentStep = 'upload_evidence';
            }
        },

        // Handle file drop
        handleFileDrop(event) {
            this.dragOver = false;
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                this.uploadFile(files[0]);
            }
        },

        // Handle file input
        async handleFileUpload(event) {
            const files = event.target.files;
            if (files.length > 0) {
                await this.uploadFile(files[0]);
            }
        },

        // Upload file
        async uploadFile(file) {
            // Validate file size
            if (file.size > 5 * 1024 * 1024) {
                this.error = 'L\'image ne peut pas dépasser 5 MB.';
                return;
            }

            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                this.error = 'Format non valide. Acceptés: JPEG, PNG, WebP.';
                return;
            }

            this.isLoading = true;
            this.error = null;

            const formData = new FormData();
            formData.append('image', file);

            try {
                const response = await fetch('/reclamations/ai-assistant/upload-evidence', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: formData,
                });

                if (!response.ok) {
                    const data = await response.json();
                    throw new Error(data.message || 'Erreur lors du téléchargement');
                }

                const data = await response.json();

                // Store attachment information
                this.draft.attachment = data.path;
                this.draft.attachmentPreview = data.url;
            } catch (err) {
                console.error('Error uploading file:', err);
                this.error = err.message || 'Erreur lors du téléchargement.';
            } finally {
                this.isLoading = false;
            }
        },

        // Remove attachment
        removeAttachment() {
            this.draft.attachment = null;
            this.draft.attachmentPreview = null;
        },

        // Submit reclamation
        async submitReclamation() {
            if (this.isSubmitting) return;

            this.isSubmitting = true;
            this.error = null;

            try {
                const response = await fetch('/reclamations/ai-assistant/submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        reservation_id: this.draft.selectedReservation,
                        title: this.draft.titre,
                        incidents: [
                            {
                                equipment_id: this.draft.selectedEquipment.equipment_id,
                                type_probleme: this.draft.type_probleme,
                                titre: this.draft.titre,
                                description: this.draft.description,
                                damage_percentage: null,
                                attachment: this.draft.attachment,
                            },
                        ],
                    }),
                });

                if (!response.ok) {
                    const data = await response.json();
                    throw new Error(data.message || 'Erreur lors de la soumission');
                }

                const data = await response.json();

                // Success - redirect after a short delay
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            } catch (err) {
                console.error('Error submitting reclamation:', err);
                this.error = err.message || 'Erreur lors de la soumission.';
            } finally {
                this.isSubmitting = false;
            }
        },

        // Helper: Escape HTML
        escapeHtml(text) {
            if (!text || typeof text !== 'string') {
                return '';
            }
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        },

        // Helper: Get current step number
        getCurrentStepNumber() {
            const steps = ['select_equipment', 'describe_problem', 'upload_evidence', 'review_confirm'];
            return steps.indexOf(this.currentStep) + 1;
        },

        // Helper: Get current step label
        get currentStepLabel() {
            return this.stepLabels[this.currentStep] || '';
        },
    };
}

// Make globally available for Alpine.js
window.aiReclamationAssistant = aiReclamationAssistant;
