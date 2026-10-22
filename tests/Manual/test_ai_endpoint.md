# Manual Testing Guide: AI-Powered Incident Description Generation

## Prerequisites

1. **Ollama Installation**: Ensure Ollama is installed and running on your system
   - Download from: https://ollama.ai
   - After installation, pull the current model:
     ```bash
     ollama pull mistral:latest
     ```
   - An earlier draft of this checklist named `qwen2.5:3b`. That name is historical and is not used by the application.

2. **Laravel Application**: Ensure the Laravel application is running
   ```bash
   php artisan serve
   ```

3. **Ollama Service**: Start Ollama service
   ```bash
   ollama serve
   ```
   - Ollama should be accessible at `http://127.0.0.1:11434`

## Test Steps

### Step 1: Verify Ollama is Running
```bash
curl http://127.0.0.1:11434/api/tags
```
Expected response: JSON list of available models including `mistral:latest`

### Step 2: Access the Create Reclamation Form
1. Go to http://127.0.0.1:8000/reclamations/create
2. Verify the form displays:
   - Reservation selection dropdown
   - Title input (for reclamation)
   - Description textarea (for reclamation)
   - **New**: Incidents section with:
     - ✓ Type de problème dropdown (Endommagé, Manquant, Malfonctionne, Autre)
     - ✓ Titre du problème input field
     - ✓ ✨ Générer la description avec l'IA button (purple/pink gradient)
     - Photo evidence upload
     - Description textarea

### Step 3: Test AI Generation Button
1. Select a reservation from the dropdown
2. In the incident form:
   - Select "Endommagé" from "Type de problème"
   - Enter "Panneau solaire cassé" in "Titre du problème"
3. Click "✨ Générer la description avec l'IA"
4. Observe:
   - Loading spinner appears (⏳ Génération en cours...)
   - Button becomes disabled
   - AI generates a professional French description
   - Description is inserted into the "Description du problème" textarea
   - Success message appears: "✓ Description générée avec succès..."
   - Message fades out after 3 seconds

### Step 4: Test Form Validation
1. Try clicking "✨ Générer la description avec l'IA" without selecting type or title
2. Verify error messages appear:
   - "Le type de problème est requis"
   - "Le titre du problème est requis"

### Step 5: Test Form Submission
1. Fill in all required fields:
   - Reservation: Select one
   - Reclamation Title: Enter any title
   - Reclamation Description: Enter any description
   - Incident Type de problème: Select from dropdown
   - Incident Titre du problème: Enter title
   - Incident Description: Either manually enter or generate with AI
2. Add another incident by clicking "+ Ajouter un incident"
3. Click "Soumettre la Réclamation"
4. Verify:
   - Form validates properly
   - Both incidents are submitted
   - Reclamation record is created with incidents
   - Redirect to reclamations.index page

### Step 6: Test Database Storage
1. Check the database:
   ```bash
   mysql -u root -p solarshare
   ```
2. Query the incidents table:
   ```sql
   SELECT id, titre, type_probleme, description FROM incidents ORDER BY id DESC LIMIT 5;
   ```
3. Verify:
   - `titre` column contains the problem title
   - `type_probleme` column contains the selected type
   - `description` column contains the generated/edited description

### Step 7: Test Error Handling
1. Stop the Ollama service (`Ctrl+C` in Ollama terminal)
2. Try clicking "✨ Générer la description avec l'IA"
3. Verify error message appears:
   - "Erreur de connexion. Assurez-vous que le service Ollama est en cours d'exécution sur http://127.0.0.1:11434"
4. Restart Ollama and verify AI generation works again

## Expected Results

All manual tests should pass, confirming:
- ✓ Form displays new fields (Type de problème, Titre du problème)
- ✓ AI generation button is functional and styled
- ✓ AI generates French descriptions appropriate to problem type
- ✓ User can edit generated descriptions before submission
- ✓ Form validates all new required fields
- ✓ Database stores titre field correctly
- ✓ Error handling displays appropriate messages
- ✓ Entire workflow from form to database works end-to-end

## Troubleshooting

### Issue: Ollama model not found
**Solution**: Pull the model with `ollama pull mistral:latest`

### Issue: Connection timeout to Ollama
**Solution**: 
1. Verify Ollama is running with `ollama serve`
2. Check `.env` file has correct OLLAMA_BASE_URL=http://127.0.0.1:11434
3. Clear Laravel config cache: `php artisan config:cache`

### Issue: Form doesn't show new fields
**Solution**:
1. Clear view cache: `php artisan view:clear`
2. Hard refresh browser (Ctrl+Shift+R)
3. Check browser console for JavaScript errors

### Issue: Generated description is too long
**Solution**: OllamaService has a configurable prompt that can be tuned in `app/Services/OllamaService.php`

## Files Modified

- `d:\desktop\laravel\laravel-Solarshare\solarshare\.env` - Added OLLAMA_* config
- `d:\desktop\laravel\laravel-Solarshare\solarshare\config\ollama.php` - Configuration file
- `d:\desktop\laravel\laravel-Solarshare\solarshare\app\Services\OllamaService.php` - Ollama integration
- `d:\desktop\laravel\laravel-Solarshare\solarshare\app\Http\Controllers\IncidentController.php` - Endpoint
- `d:\desktop\laravel\laravel-Solarshare\solarshare\app\Models\Incident.php` - Added titre to fillable
- `d:\desktop\laravel\laravel-Solarshare\solarshare\resources\views\frontend\reclamations\create.blade.php` - UI updates
- `d:\desktop\laravel\laravel-Solarshare\solarshare\app\Http\Requests\StoreReclamationRequest.php` - Validation
- Migration: `database/migrations/2026_10_17_add_titre_to_incidents_table.php` - Added titre column
