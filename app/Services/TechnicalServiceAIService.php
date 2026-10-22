<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TechnicalServiceAIService
{
    private string $baseUrl;
    private string $model;
    private int $timeout;
    private bool $enabled;

    public function __construct()
    {
        $this->baseUrl = OllamaSettings::baseUrl();
        $this->model = OllamaSettings::model();
        $this->timeout = OllamaSettings::timeout();
        $this->enabled = (bool) config('ollama.enabled', true);
    }

    /**
     * Calcule une empreinte SHA256 des données de la demande pour lier le diagnostic.
     */
    public static function computeFingerprint(
        int $providerId,
        ?int $equipmentId,
        string $title,
        string $description
    ): string {
        $cleanTitle = trim(preg_replace('/\s+/', ' ', $title));
        $cleanDesc = trim(preg_replace('/\s+/', ' ', $description));
        $eq = $equipmentId !== null ? (string) $equipmentId : '';

        return hash('sha256', "{$providerId}|{$eq}|{$cleanTitle}|{$cleanDesc}");
    }

    /**
     * Analyse une panne solaire et génère un pré-diagnostic technique indicatif en TND.
     *
     * @param string $title
     * @param string $description
     * @param string|null $equipmentName
     * @param string|null $equipmentCategory
     * @return array<string, mixed>
     */
    public function analyze(
        string $title,
        string $description,
        ?string $equipmentName = null,
        ?string $equipmentCategory = null
    ): array {
        if ($this->enabled) {
            try {
                $llmResult = $this->queryOllama($title, $description, $equipmentName, $equipmentCategory);
                if ($llmResult !== null) {
                    return $llmResult;
                }
            } catch (Exception $e) {
                Log::warning('TechnicalServiceAIService : Échec de l\'inférence Ollama, passage en mode heuristique.', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $this->generateHeuristicDiagnosis($title, $description, $equipmentName, $equipmentCategory);
    }

    /**
     * Interroge l'API Ollama locale avec prompt structuré, devise TND et consignes conservatrices.
     */
    private function queryOllama(
        string $title,
        string $description,
        ?string $equipmentName,
        ?string $equipmentCategory
    ): ?array {
        if (! app()->runningUnitTests() && ! app(OllamaAvailability::class)->isReady($this->baseUrl, $this->model)) {
            return null;
        }

        $prompt = $this->buildPrompt($title, $description, $equipmentName, $equipmentCategory);

        $response = Http::timeout($this->timeout)->post("{$this->baseUrl}/api/generate", array_merge(
            OllamaSettings::requestFields(['temperature' => 0.2]),
            [
                'prompt' => $prompt,
                'format' => 'json',
            ]
        ));

        if (!$response->successful()) {
            return null;
        }

        $rawBody = $response->json('response');
        if (!is_string($rawBody)) {
            return null;
        }

        $decoded = json_decode($rawBody, true);
        if (!is_array($decoded) || !isset($decoded['diagnostic_probable'])) {
            return null;
        }

        return $this->normalizeOutput($decoded, 'ollama');
    }

    /**
     * Construit le prompt système avec consignes de sécurité et devise TND.
     */
    private function buildPrompt(
        string $title,
        string $description,
        ?string $equipmentName,
        ?string $equipmentCategory
    ): string {
        $equipmentInfo = $equipmentName ? "Équipement concerné: {$equipmentName} ({$equipmentCategory})" : "Équipement non spécifié";

        return <<<PROMPT
Tu es un expert assistant technique spécialisé dans le pré-diagnostic d'installations solaires et photovoltaïques en Tunisie (monnaie : TND).
Analyse la déclaration d'incident suivante et retourne UNIQUEMENT un objet JSON valide.

Contexte de la panne :
Titre : {$title}
Description : {$description}
{$equipmentInfo}

CONSIGNES STRICTES DE SÉCURITÉ :
- Ne suggère JAMAIS à l'utilisateur d'ouvrir un onduleur, de manipuler des câbles haute tension CC (DC) ou de toucher des bornes de batteries.
- Les consignes de sécurité doivent rester strictement conservatrices : mise à distance en cas de surchauffe ou d'odeur suspecte, coupure d'urgence uniquement si sécurisée et prévue sans risque, et intervention exclusive d'un professionnel qualifié.

Le JSON doit respecter exactement la structure suivante (fourchettes obligatoirement en TND) :
{
  "diagnostic_probable": "Hypothèse technique principale courte",
  "severity": "faible|moyenne|elevee|urgente",
  "estimated_duration": "Ex: 1h à 2h",
  "probable_components": ["Composant 1", "Composant 2"],
  "safety_advice": "Conseil de sécurité conservateur adapté",
  "recommended_specialty": "Spécialité recommandée (ex: Onduleurs & Électronique, Câblage)",
  "short_summary": "Résumé de 2 phrases maximum",
  "indicative_cost_range": "Ex: 80 TND - 200 TND (indicatif hors pièces)"
}
PROMPT;
    }

    /**
     * Heuristique experte déterministe en devise TND.
     */
    private function generateHeuristicDiagnosis(
        string $title,
        string $description,
        ?string $equipmentName,
        ?string $equipmentCategory
    ): array {
        $text = mb_strtolower($title . ' ' . $description . ' ' . (string) $equipmentName . ' ' . (string) $equipmentCategory);

        if (str_contains($text, 'onduleur') || str_contains($text, 'inverter') || str_contains($text, 'erreur code') || str_contains($text, 'voyant rouge')) {
            return [
                'diagnostic_probable' => 'Défaut de conversion ou mise en sécurité de l\'onduleur',
                'severity' => 'elevee',
                'estimated_duration' => '1h30 à 3h',
                'probable_components' => ['Onduleur central/micro-onduleur', 'Disjoncteur différentiel AC/DC', 'Connecteurs'],
                'safety_advice' => 'Éloignez-vous de l\'équipement en cas de surchauffe, d\'odeur anormale ou de bruit suspect. Ne tentez aucune manipulation interne. Si possible et sans danger selon la notice de l\'installation, maintenez l\'installation isolée et attendez le technicien qualifié.',
                'recommended_specialty' => 'Onduleurs & Électronique de puissance',
                'short_summary' => 'L\'onduleur semble en arrêt de sécurité ou présente un défaut interne de conversion nécessitant des tests électriques sur site.',
                'indicative_cost_range' => '90 TND - 220 TND (fourchette indicative - devis officiel fixé par le prestataire)',
                'source' => 'heuristic',
            ];
        }

        if (str_contains($text, 'batterie') || str_contains($text, 'charge') || str_contains($text, 'stockage') || str_contains($text, 'autonomie')) {
            return [
                'diagnostic_probable' => 'Anomalie de régulation ou déséquilibre du parc de stockage',
                'severity' => 'elevee',
                'estimated_duration' => '2h à 4h',
                'probable_components' => ['Système de gestion de batterie (BMS)', 'Régulateur de charge', 'Cellules de stockage'],
                'safety_advice' => 'Ne touchez pas aux bornes ni aux câbles. En cas d\'odeur suspecte, d\'échauffement ou de déformation, évacuez la zone immédiate et faites appel immédiatement à un installateur certifié.',
                'recommended_specialty' => 'Stockage d\'énergie & Batteries solaires',
                'short_summary' => 'Le banc de stockage ne tient plus la charge ou le BMS a isolé les batteries suite à un défaut de tension.',
                'indicative_cost_range' => '120 TND - 300 TND (fourchette indicative - devis officiel fixé par le prestataire)',
                'source' => 'heuristic',
            ];
        }

        if (str_contains($text, 'panneau') || str_contains($text, 'toit') || str_contains($text, 'ombre') || str_contains($text, 'production faible')) {
            return [
                'diagnostic_probable' => 'Baisse de rendement photovoltaïque ou micro-coupure de chaîne (string)',
                'severity' => 'moyenne',
                'estimated_duration' => '1h à 2h30',
                'probable_components' => ['Cellules photovoltaïques', 'Boîte de jonction / Connecteurs MC4', 'Câblage extérieur'],
                'safety_advice' => 'Ne montez pas sur la toiture et ne manipulez aucun câblage exposé. Seul un technicien habilité muni d\'équipements de sécurité peut intervenir en hauteur.',
                'recommended_specialty' => 'Panneaux & Toitures photovoltaïques',
                'short_summary' => 'Une baisse de production anormale suggère une déconnexion partielle de string ou une dégradation de contact.',
                'indicative_cost_range' => '70 TND - 180 TND (fourchette indicative - devis officiel fixé par le prestataire)',
                'source' => 'heuristic',
            ];
        }

        return [
            'diagnostic_probable' => 'Dysfonctionnement électrique général sur l\'installation solaire',
            'severity' => 'moyenne',
            'estimated_duration' => '1h à 2h',
            'probable_components' => ['Coffret de protection AC/DC', 'Câblage de liaison', 'Organes de coupure'],
            'safety_advice' => 'Éloignez-vous de l\'équipement en cas de surchauffe, d\'odeur anormale ou de câbles endommagés. Si l\'installation peut être mise hors service en toute sécurité selon les procédures prévues, contactez un professionnel qualifié.',
            'recommended_specialty' => 'Maintenance électrique générale',
            'short_summary' => 'Les symptômes signalés requièrent une inspection visuelle et des mesures de tension par un intervenant qualifié.',
            'indicative_cost_range' => '60 TND - 150 TND (fourchette indicative - devis officiel fixé par le prestataire)',
            'source' => 'heuristic',
        ];
    }

    /**
     * Normalise le format retourné par le LLM.
     */
    private function normalizeOutput(array $data, string $source): array
    {
        $allowedSeverities = ['faible', 'moyenne', 'elevee', 'urgente'];
        $severity = mb_strtolower((string) ($data['severity'] ?? 'moyenne'));
        if (!in_array($severity, $allowedSeverities, true)) {
            $severity = 'moyenne';
        }

        $probableComponents = is_array($data['probable_components'] ?? null)
            ? array_values(array_filter(array_map('trim', $data['probable_components'])))
            : ['Équipement principal'];

        return [
            'diagnostic_probable' => trim((string) ($data['diagnostic_probable'] ?? 'Anomalie technique nécessitant inspection')),
            'severity' => $severity,
            'estimated_duration' => trim((string) ($data['estimated_duration'] ?? '1h à 2h')),
            'probable_components' => $probableComponents,
            'safety_advice' => trim((string) ($data['safety_advice'] ?? 'Éloignez-vous de l\'équipement en cas de surchauffe ou d\'odeur anormale. Contactez un professionnel qualifié.')),
            'recommended_specialty' => trim((string) ($data['recommended_specialty'] ?? 'Maintenance solaire')),
            'short_summary' => trim((string) ($data['short_summary'] ?? 'Pré-diagnostic généré à titre indicatif.')),
            'indicative_cost_range' => trim((string) ($data['indicative_cost_range'] ?? 'Non estimable sans devis du prestataire')),
            'source' => $source,
        ];
    }
}
