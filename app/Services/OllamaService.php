<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    /**
     * Generate an incident description using Ollama Qwen2.5 3B model
     *
     * @param string $problemType Type of problem (endommage, manquant, etc.)
     * @param string $problemTitle Title of the problem
     * @param string|null $equipmentName Name of the equipment
     * @param string|null $dateDebut Reservation start date (d/m/Y format)
     * @param string|null $dateFin Reservation end date (d/m/Y format)
     * @param int|null $reservationId Reservation ID for reference
     * @return string|null Generated description or null if failed
     * @throws \Exception
     */
    public function generateIncidentDescription(
        string $problemType,
        string $problemTitle,
        ?string $equipmentName = null,
        ?string $dateDebut = null,
        ?string $dateFin = null,
        ?int $reservationId = null
    ): ?string {
        if (!config('ollama.enabled')) {
            throw new \Exception('Ollama service is not enabled.');
        }

        // Build the prompt for Qwen2.5 3B
        $prompt = $this->buildPrompt(
            $problemType,
            $problemTitle,
            $equipmentName,
            $dateDebut,
            $dateFin,
            $reservationId
        );

        try {
            $response = Http::timeout(config('ollama.timeout', 60))
                ->post(
                    config('ollama.base_url') . '/api/generate',
                    [
                        'model' => config('ollama.model'),
                        'prompt' => $prompt,
                        'stream' => false,
                    ]
                );

            if (!$response->successful()) {
                Log::error('Ollama API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Ollama API returned status ' . $response->status());
            }

            $data = $response->json();

            if (!isset($data['response'])) {
                Log::error('Ollama API invalid response', ['data' => $data]);
                throw new \Exception('Ollama API returned invalid response');
            }

            // Clean up the generated text
            $generated = trim($data['response']);

            // Remove markdown formatting if present
            $generated = preg_replace('/^#+\s+/', '', $generated); // Remove markdown headers
            $generated = preg_replace('/\*\*(.+?)\*\*/', '$1', $generated); // Remove bold
            $generated = preg_replace('/\*(.+?)\*/', '$1', $generated); // Remove italic

            return $generated;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Ollama connection failed', ['error' => $e->getMessage()]);
            throw new \Exception('Impossible de se connecter au service Ollama. Vérifiez qu\'Ollama est en cours d\'exécution sur ' . config('ollama.base_url'));
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Ollama request failed', ['error' => $e->getMessage()]);
            throw new \Exception('Erreur lors de la requête à Ollama: ' . $e->getMessage());
        }
    }

    /**
     * Build an enhanced prompt for Qwen2.5 3B that generates detailed, professional descriptions
     *
     * @param string $problemType
     * @param string $problemTitle
     * @param string|null $equipmentName
     * @param string|null $dateDebut
     * @param string|null $dateFin
     * @param int|null $reservationId
     * @return string
     */
    private function buildPrompt(
        string $problemType,
        string $problemTitle,
        ?string $equipmentName = null,
        ?string $dateDebut = null,
        ?string $dateFin = null,
        ?int $reservationId = null
    ): string {
        $problemTypeLabel = $this->getProblemTypeLabel($problemType);

        // Build context information
        $contextLines = [];
        
        if ($reservationId) {
            $contextLines[] = "Référence de réservation: #{$reservationId}";
        }
        
        if ($equipmentName) {
            $contextLines[] = "Équipement loué: {$equipmentName}";
        }
        
        if ($dateDebut && $dateFin) {
            $contextLines[] = "Période de location: du {$dateDebut} au {$dateFin}";
        }
        
        $contextLines[] = "Type de problème: {$problemTypeLabel}";
        $contextLines[] = "Titre/Résumé du problème: {$problemTitle}";
        
        $context = implode("\n", $contextLines);

        $prompt = <<<PROMPT
Tu es un assistant spécialisé en génération de descriptions professionnelles de réclamations pour une plateforme de location d'équipements.

CONTEXTE DE LA RÉCLAMATION:
{$context}

INSTRUCTIONS:
Génère une description professionnelle, détaillée et courtoise de la réclamation en français. La description doit:

1. Commencer par une phrase de politesse expliquant le but de la réclamation
2. Mentionner la référence de réservation, l'équipement loué et les dates de location (si disponibles)
3. Décrire précisément le problème indiqué par le titre, sans inventer de détails supplémentaires
4. Expliquer l'impact raisonnable du problème
5. Demander poliment à l'équipe de SolarShare d'enquêter et de fournir une solution
6. Se terminer par une formule de politesse

CONTRAINTES IMPORTANTES:
- La description doit faire entre 120 et 180 mots (paragraphe substantiel, pas une seule phrase)
- N'invente PAS de dates, de noms, de détails de livraison, ou de faits non fournis
- N'ajoute PAS de formatage Markdown, de tirets ou d'énumération
- Utilise uniquement les informations fournies dans le contexte
- Retourne UNIQUEMENT le texte de la description, sans introduction ni titre
- Sois précis mais prudent: si le problème est ambigu, décris-le basé sur le titre fourni sans ajouter d'hypothèses

EXEMPLE DE FORMAT (adapte pour ce cas):
Madame, Monsieur,

Je vous écris pour signaler un problème [description du problème]. Concernant ma réservation [référence], pour l'équipement [nom] du [date début] au [date fin], [explication du problème]. 

[Impact du problème et demande de solution].

Je vous remercie de votre attention et reste à votre disposition pour tout complément d'information.

Cordialement,

GÉNÈRE MAINTENANT LA DESCRIPTION:
PROMPT;

        return trim($prompt);
    }

    /**
     * Get human-readable label for problem type
     *
     * @param string $type
     * @return string
     */
    private function getProblemTypeLabel(string $type): string
    {
        return match ($type) {
            'endommage' => 'Endommagé',
            'manquant' => 'Manquant',
            'malfonctionnant' => 'Malfonctionnant',
            'autre' => 'Autre',
            default => $type,
        };
    }
}
