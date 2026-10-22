<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    /**
     * Generate complete reclamation details (type, title, description) from client explanation
     *
     * @param string $clientExplanation Client's free-form explanation of the problem
     * @param string|null $equipmentName Name of the equipment
     * @param string|null $dateDebut Reservation start date (d/m/Y format)
     * @param string|null $dateFin Reservation end date (d/m/Y format)
     * @param int|null $reservationId Reservation ID for reference
     * @return array Array with keys: type_probleme, titre, description
     * @throws \Exception
     */
    public function generateReclamationDetails(
        string $clientExplanation,
        ?string $equipmentName = null,
        ?string $dateDebut = null,
        ?string $dateFin = null,
        ?int $reservationId = null
    ): array {
        if (!config('ollama.enabled')) {
            throw new \Exception('Ollama service is not enabled.');
        }

        // Build the prompt to generate all three fields
        $prompt = $this->buildReclamationDetailsPrompt(
            $clientExplanation,
            $equipmentName,
            $dateDebut,
            $dateFin,
            $reservationId
        );

        try {
            $this->ensureReady();
            $response = Http::timeout(OllamaSettings::timeout())
                ->post(
                    OllamaSettings::baseUrl() . '/api/generate',
                    array_merge(OllamaSettings::requestFields(), [
                        'prompt' => $prompt,
                    ])
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

            // Parse the structured response
            return $this->parseReclamationDetailsResponse($data['response']);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Ollama connection failed', ['error' => $e->getMessage()]);
            throw new \Exception('Impossible de se connecter au service Ollama. Vérifiez qu\'Ollama est en cours d\'exécution sur ' . config('ollama.base_url'));
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Ollama request failed', ['error' => $e->getMessage()]);
            throw new \Exception('Erreur lors de la requête à Ollama: ' . $e->getMessage());
        }
    }

    /**
     * Generate an incident description using the configured Ollama model
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

        // Build the prompt for the configured model
        $prompt = $this->buildPrompt(
            $problemType,
            $problemTitle,
            $equipmentName,
            $dateDebut,
            $dateFin,
            $reservationId
        );

        try {
            $this->ensureReady();
            $response = Http::timeout(OllamaSettings::timeout())
                ->post(
                    OllamaSettings::baseUrl() . '/api/generate',
                    array_merge(OllamaSettings::requestFields(), [
                        'prompt' => $prompt,
                    ])
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
     * Build a prompt to generate reclamation details from client explanation
     *
     * @param string $clientExplanation
     * @param string|null $equipmentName
     * @param string|null $dateDebut
     * @param string|null $dateFin
     * @param int|null $reservationId
     * @return string
     */
    private function ensureReady(): void
    {
        app(OllamaAvailability::class)->ensureBeforeCall(
            OllamaSettings::baseUrl(),
            OllamaSettings::model()
        );
    }

    private function buildReclamationDetailsPrompt(
        string $clientExplanation,
        ?string $equipmentName = null,
        ?string $dateDebut = null,
        ?string $dateFin = null,
        ?int $reservationId = null
    ): string {
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
        
        $contextLines[] = "Explication du client: {$clientExplanation}";
        
        $context = implode("\n", $contextLines);

        $prompt = <<<PROMPT
Tu es un assistant spécialisé en analyse de réclamations pour une plateforme de location d'équipements SolarShare.

CONTEXTE:
{$context}

TÂCHE:
Analyse l'explication du client et génère une réclamation structurée. Tu dois répondre EXACTEMENT dans ce format JSON (valide et parsable):

{
  "type_probleme": "endommage|manquant|malfonctionnant|autre",
  "titre": "Titre concis (3-255 caractères)",
  "description": "Description professionnelle (60-90 mots, sans formatage Markdown)"
}

RÈGLES STRICTES:
1. Le type_probleme DOIT être l'une de ces valeurs exactes: "endommage", "manquant", "malfonctionnant", "autre"
2. Le titre doit être une seule ligne, sans énumération, sans tirets
3. La description doit être un paragraphe professionnel et courtois
4. N'INVENTE JAMAIS de détails non mentionnés par le client
5. N'AJOUTE PAS de formatage Markdown (* ** # etc.)
6. Réponds UNIQUEMENT avec le JSON valide, aucun autre texte avant ou après

EXEMPLE:
{
  "type_probleme": "endommage",
  "titre": "Câbles de connexion endommagés",
  "description": "L'équipement loué présente des câbles de connexion endommagés qui empêchent son fonctionnement correct. Suite à l'explication fournie, les dommages semblent résulter d'une manipulation inadvertante. Je vous demande de bien vouloir étudier cette réclamation et de proposer une solution adaptée."
}

ANALYSE ET GÉNÈRE MAINTENANT:
PROMPT;

        return trim($prompt);
    }

    /**
     * Parse the structured reclamation details response from Ollama
     *
     * @param string $response
     * @return array
     * @throws \Exception
     */
    private function parseReclamationDetailsResponse(string $response): array
    {
        $response = trim($response);

        // Try to extract JSON from the response
        if (preg_match('/\{[\s\S]*\}/', $response, $matches)) {
            $jsonStr = $matches[0];
        } else {
            throw new \Exception('Impossible de générer les détails de la réclamation. Format invalide.');
        }

        $data = json_decode($jsonStr, true);

        if (!is_array($data)) {
            throw new \Exception('Impossible de générer les détails de la réclamation. JSON invalide.');
        }

        // Validate required fields
        $required = ['type_probleme', 'titre', 'description'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("Champ manquant dans la génération: {$field}");
            }
        }

        // Validate type_probleme is one of the allowed values
        $allowedTypes = ['endommage', 'manquant', 'malfonctionnant', 'autre'];
        if (!in_array($data['type_probleme'], $allowedTypes)) {
            throw new \Exception("Type de problème invalide: {$data['type_probleme']}");
        }

        // Validate title length
        $titleLength = strlen($data['titre']);
        if ($titleLength < 3 || $titleLength > 255) {
            throw new \Exception("Le titre doit faire entre 3 et 255 caractères (reçu: {$titleLength})");
        }

        // Validate description length
        $descLength = strlen($data['description']);
        if ($descLength < 20 || $descLength > 2000) {
            throw new \Exception("La description doit faire entre 20 et 2000 caractères (reçue: {$descLength})");
        }

        return [
            'type_probleme' => $data['type_probleme'],
            'titre' => $data['titre'],
            'description' => $data['description'],
        ];
    }

    /**
     * Build an enhanced prompt that generates detailed, professional descriptions
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
- La description doit faire entre 60 et 90 mots (un paragraphe court, pas une seule phrase)
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
