# UI and Form Submission Testing Checklist

## Test Environment Setup

### Prerequisites
- [ ] Laravel application running: `php artisan serve`
- [ ] Ollama service running: `ollama serve`
- [ ] Ollama model available: `qwen2.5:3b`
- [ ] Database migrations executed: `php artisan migrate`
- [ ] View cache cleared: `php artisan view:clear`
- [ ] Config cache cleared: `php artisan config:clear`
- [ ] Logged in as test user with active reservations

---

## Section 1: Form Display and UI Elements

### 1.1 Reclamation Form Page Load
- [ ] Navigate to `/reclamations/create`
- [ ] Page loads without JavaScript errors
- [ ] Page title: "Nouvelle Réclamation"
- [ ] Form is visible and properly styled
- [ ] All form sections are accessible

### 1.2 Reservation Dropdown
- [ ] Dropdown is visible with label "Sélectionnez une réservation"
- [ ] Multiple reservations are listed
- [ ] Each option shows: #ID - Equipment Name (dates)
- [ ] Default option shows: "-- Choisissez une réservation --"
- [ ] Dropdown is functional (can select options)

### 1.3 Reclamation Title Field
- [ ] Input field is visible with label "Titre"
- [ ] Input has placeholder text
- [ ] Field is required (marked with red asterisk)
- [ ] Input accepts text up to 255 characters

### 1.4 Reclamation Description Field
- [ ] Textarea is visible with label "Description"
- [ ] Textarea has placeholder text
- [ ] Field is required (marked with red asterisk)
- [ ] Can enter multiline text

### 1.5 Incidents Section Header
- [ ] Section title "Incidents" is visible
- [ ] Required marker (red asterisk) is shown
- [ ] Helper text: "Ajoutez au moins un incident relatif à cette réclamation"
- [ ] Button "+ Ajouter un incident" is visible and styled

---

## Section 2: Incident Form Fields (Initial)

### 2.1 Initial Incident Card
- [ ] First incident card appears on page load (#1)
- [ ] Card has gray background with border
- [ ] Card header shows "Incident #1" with "Supprimer" button
- [ ] All incident fields are visible

### 2.2 Equipment Field Display
- [ ] Label: "Équipement"
- [ ] Shows text: "Équipement de la réservation"
- [ ] Field is disabled (read-only)
- [ ] Helper text: "Sera automatiquement lié à la réservation sélectionnée"

### 2.3 Type de Problème Dropdown (NEW FIELD)
- [ ] Label: "Type de problème" with red asterisk (required)
- [ ] Dropdown is visible and functional
- [ ] Default option: "-- Choisissez un type --"
- [ ] Options visible:
  - [ ] Endommagé
  - [ ] Manquant
  - [ ] Malfonctionne
  - [ ] Autre
- [ ] Can select each option

### 2.4 Titre du Problème Input (NEW FIELD)
- [ ] Label: "Titre du problème" with red asterisk (required)
- [ ] Input field is visible
- [ ] Placeholder: "Ex: Panneau solaire cassé, Connecteur endommagé..."
- [ ] Helper text: "Résumé du problème (3-255 caractères)"
- [ ] Max length: 255 characters
- [ ] Can enter text and edit freely

### 2.5 AI Generation Button (NEW BUTTON)
- [ ] Button text: "✨ Générer la description avec l'IA"
- [ ] Button has gradient styling (purple to pink)
- [ ] Button is visible and clickable
- [ ] Button is NOT disabled on initial page load
- [ ] Loading indicator (#loading-incident_1) is hidden initially

### 2.6 Description Textarea
- [ ] Label: "Description du problème" with red asterisk (required)
- [ ] Textarea is visible and empty initially
- [ ] Placeholder: "Décrivez précisément le problème..."
- [ ] Textarea has 3 rows
- [ ] Can enter text freely

### 2.7 Image Upload Field
- [ ] Label: "Photo/Preuve (optionnel)"
- [ ] File input is visible
- [ ] Helper text: "Formats acceptés: JPG, PNG, WebP. Taille maximale: 5 MB"
- [ ] Preview section is hidden initially (#preview_1)

---

## Section 3: Form Validation

### 3.1 Required Field Validation
- [ ] Fill only reclamation title, leave description empty
- [ ] Click "Soumettre la Réclamation"
- [ ] Error appears: "Veuillez corriger les erreurs suivantes:"
- [ ] Description error shows: "La description est requise"
- [ ] Form does NOT submit

### 3.2 Incident Type Validation (NEW)
- [ ] Do NOT select a type from dropdown
- [ ] Click "✨ Générer la description avec l'IA" button
- [ ] Error appears under type dropdown: "Le type de problème est requis"
- [ ] AI generation does NOT proceed

### 3.3 Incident Titre Validation (NEW)
- [ ] Leave titre field empty
- [ ] Click "✨ Générer la description avec l'IA" button
- [ ] Error appears under titre field: "Le titre du problème est requis"
- [ ] AI generation does NOT proceed

### 3.4 Titre Minimum Length Validation
- [ ] Enter "ab" (2 characters) in titre field
- [ ] Click "✨ Générer la description avec l'IA" button
- [ ] Error appears: "Le titre doit contenir au moins 3 caractères"
- [ ] AI generation does NOT proceed

### 3.5 Titre Maximum Length Validation
- [ ] Enter more than 255 characters in titre field
- [ ] Click "✨ Générer la description avec l'IA" button
- [ ] Error appears: "Le titre ne peut pas dépasser 255 caractères"
- [ ] AI generation does NOT proceed

---

## Section 4: AI Generation Feature

### 4.1 Successful AI Generation
- [ ] Select reservation from dropdown
- [ ] In incident #1:
  - [ ] Select "Endommagé" from Type de problème
  - [ ] Enter "Panneau solaire cassé" in Titre du problème
- [ ] Click "✨ Générer la description avec l'IA"
- [ ] Loading state appears:
  - [ ] Button becomes disabled (opacity-50, cursor-not-allowed)
  - [ ] Loading indicator shows: "⏳ Génération en cours..."
- [ ] After 5-15 seconds, description is generated:
  - [ ] Generated text appears in description textarea
  - [ ] Text is in French
  - [ ] Text is professional and relevant to problem type
  - [ ] Text does NOT contain markdown formatting
- [ ] Success message appears: "✓ Description générée avec succès. Vous pouvez l'éditer avant de soumettre."
- [ ] Success message fades after 3 seconds
- [ ] Button is re-enabled
- [ ] Loading indicator is hidden

### 4.2 Different Problem Types
Test AI generation with different problem types:

#### 4.2a Endommagé
- [ ] Select "Endommagé"
- [ ] Enter "Écran LCD fissuré"
- [ ] Click AI button
- [ ] Generated description mentions damage/breakage

#### 4.2b Manquant
- [ ] Add new incident
- [ ] Select "Manquant"
- [ ] Enter "Vis de fixation manquantes"
- [ ] Click AI button
- [ ] Generated description mentions missing items

#### 4.2c Malfonctionnant
- [ ] Add new incident
- [ ] Select "Malfonctionnant"
- [ ] Enter "Batterie ne charge pas"
- [ ] Click AI button
- [ ] Generated description mentions malfunction

#### 4.2d Autre
- [ ] Add new incident
- [ ] Select "Autre"
- [ ] Enter "Problème non classifié"
- [ ] Click AI button
- [ ] Generated description is appropriate

### 4.3 AI Error Handling - Ollama Offline
- [ ] Stop Ollama service (Ctrl+C in Ollama terminal)
- [ ] In incident form, select type and title
- [ ] Click "✨ Générer la description avec l'IA"
- [ ] Loading state shows
- [ ] After timeout, error message appears:
  - [ ] "Erreur de connexion. Assurez-vous que le service Ollama est en cours d'exécution sur http://127.0.0.1:11434"
- [ ] Button is re-enabled
- [ ] Button can be clicked again after fixing
- [ ] Restart Ollama and verify AI works again

### 4.4 AI Editing Capability
- [ ] Generate description for an incident
- [ ] Manually edit the generated text in textarea
- [ ] Edit works normally (cursor, selection, deletion, insertion)
- [ ] Can delete entire text and type new text
- [ ] Changes are preserved

### 4.5 Multiple AI Generations
- [ ] Generate description for incident #1
- [ ] Generate description for incident #2
- [ ] Each loads independently
- [ ] Both descriptions are accurate for their respective problem types
- [ ] Generating one doesn't affect the other

---

## Section 5: Multiple Incidents

### 5.1 Add Incident Button
- [ ] Click "+ Ajouter un incident"
- [ ] New incident card appears (Incident #2)
- [ ] Card styling is identical to first incident
- [ ] Incident #2 has all required fields visible
- [ ] Delete button is present for Incident #2

### 5.2 Multiple Incidents Management
- [ ] Add 3 incidents total
- [ ] Each has different problem types
- [ ] Each has different titre values
- [ ] Delete the middle incident (#2)
- [ ] Incident #1 and #3 remain intact
- [ ] Remaining incidents can be edited independently

### 5.3 At Least One Incident Required
- [ ] Add one incident
- [ ] Delete the incident
- [ ] Try to submit form
- [ ] Error message: "Au moins un incident est requis"
- [ ] Form does NOT submit

---

## Section 6: Image Upload

### 6.1 Valid Image Upload
- [ ] Select a valid JPG/PNG/WebP image (< 5MB)
- [ ] Click file input for incident
- [ ] Select image file
- [ ] Image preview appears below upload field
- [ ] Preview shows thumbnail of selected image

### 6.2 Invalid Image Format
- [ ] Select a .txt or .pdf file
- [ ] Try to upload
- [ ] Error message: "Format non supporté. Utilisez JPG, PNG ou WebP"
- [ ] File is not accepted

### 6.3 File Too Large
- [ ] Select an image > 5MB
- [ ] Try to upload
- [ ] Error message: "Le fichier est trop volumineux. Taille maximale: 5 MB"
- [ ] File is not accepted

---

## Section 7: Form Submission

### 7.1 Valid Form Submission
- [ ] Fill all required fields:
  - [ ] Select reservation
  - [ ] Enter reclamation title
  - [ ] Enter reclamation description
  - [ ] Incident #1: Select type, enter titre, enter description
  - [ ] Incident #2: Select type, enter titre, enter description
- [ ] All validation passes
- [ ] Click "Soumettre la Réclamation"
- [ ] Form submits (no JavaScript validation errors)
- [ ] Page redirects to reclamation show page
- [ ] Success message appears: "Réclamation créée avec succès."

### 7.2 Database Verification
After successful submission:
- [ ] Check database for new reclamation:
  ```sql
  SELECT * FROM reclamations WHERE title = '[entered title]' LIMIT 1;
  ```
- [ ] Verify fields in reclamations table:
  - [ ] id (auto-generated)
  - [ ] reservation_id (matches selected)
  - [ ] user_id (matches current user)
  - [ ] title (matches entered)
  - [ ] description (matches entered)
  - [ ] status = 'en_attente'
  - [ ] created_at (current timestamp)

### 7.3 Incidents Database Verification
After successful submission:
- [ ] Check incidents table:
  ```sql
  SELECT id, titre, type_probleme, description FROM incidents 
  WHERE reclamation_id = [reclamation_id] ORDER BY id;
  ```
- [ ] Verify both incidents were created:
  - [ ] Both have correct reclamation_id
  - [ ] **NEW**: Both have titre field populated (not null)
  - [ ] Both have type_probleme (endommage, manquant, etc.)
  - [ ] Both have description (generated or manual)
  - [ ] Both have equipment_id matching reservation
  - [ ] created_at matches reclamation timestamp

### 7.4 Image Attachment Verification
If image was uploaded:
- [ ] Check database for attachment path:
  - [ ] Attachment column contains relative path (e.g., "incidents/xyz.jpg")
- [ ] Verify file exists in storage:
  ```bash
  ls storage/app/public/incidents/
  ```
- [ ] File is accessible via web URL

---

## Section 8: Browser Compatibility

### 8.1 Chrome/Edge
- [ ] Form displays correctly
- [ ] All fields functional
- [ ] AI button works
- [ ] Validation displays properly
- [ ] Form submits successfully

### 8.2 Firefox
- [ ] Form displays correctly
- [ ] All fields functional
- [ ] AI button works
- [ ] Validation displays properly
- [ ] Form submits successfully

---

## Section 9: Responsive Design

### 9.1 Desktop (1200px+)
- [ ] Form is well-laid out
- [ ] Incident cards are readable
- [ ] All buttons are easily clickable

### 9.2 Tablet (768px-1199px)
- [ ] Form is responsive
- [ ] Incident cards stack properly
- [ ] Fields are readable and editable

### 9.3 Mobile (< 768px)
- [ ] Form is mobile-friendly
- [ ] Incident cards are full width
- [ ] Buttons are touch-friendly
- [ ] Validation messages are visible

---

## Section 10: Error Recovery

### 10.1 Form Validation Recovery
- [ ] Submit form with missing fields
- [ ] Errors display
- [ ] Fill in missing field
- [ ] Errors clear for that field
- [ ] Form can be submitted again

### 10.2 AI Generation Retry
- [ ] AI generation fails (Ollama offline)
- [ ] Error message shows
- [ ] Fix the issue (restart Ollama)
- [ ] Click AI button again
- [ ] AI generation works

### 10.3 Incident Deletion and Re-add
- [ ] Add incident, then delete it
- [ ] Add another incident
- [ ] New incident has correct numbering
- [ ] New incident works independently

---

## Test Results Summary

### Overall Test Status
- [ ] All UI elements display correctly
- [ ] All validations work as expected
- [ ] AI generation functions properly
- [ ] Form submission succeeds
- [ ] Database stores all data including titre
- [ ] Multiple incidents can be added and managed
- [ ] Error handling is graceful

### Issues Found
(Document any issues discovered during testing)
- Issue #1: ___________________________
- Issue #2: ___________________________
- Issue #3: ___________________________

### Sign-off
- **Tester Name**: _____________________
- **Date**: _____________________
- **Status**: ☐ PASS ☐ FAIL ☐ PARTIAL

---

## Notes
Add any additional observations or recommendations here:
