<?php

namespace App\Services;

/**
 * AIConversationService manages the multi-step conversational flow for reclamation creation
 * 
 * Steps:
 * - Step A: Select reservation
 * - Step B: Describe the problem (title/summary)
 * - Step C: Identify incidents (type, title, description)
 * - Step D: Generate descriptions with AI
 * - Step E: Upload evidence (optional)
 * - Step F: Review and submit
 */
class AIConversationService
{
    const STEP_SELECT_RESERVATION = 'select_reservation';
    const STEP_DESCRIBE_PROBLEM = 'describe_problem';
    const STEP_IDENTIFY_INCIDENTS = 'identify_incidents';
    const STEP_GENERATE_DESCRIPTIONS = 'generate_descriptions';
    const STEP_UPLOAD_EVIDENCE = 'upload_evidence';
    const STEP_REVIEW_SUBMIT = 'review_submit';

    /**
     * Steps in the conversation flow
     */
    public static function getSteps(): array
    {
        return [
            self::STEP_SELECT_RESERVATION => 'Sélectionner une réservation',
            self::STEP_DESCRIBE_PROBLEM => 'Décrire le problème',
            self::STEP_IDENTIFY_INCIDENTS => 'Identifier les incidents',
            self::STEP_GENERATE_DESCRIPTIONS => 'Générer les descriptions',
            self::STEP_UPLOAD_EVIDENCE => 'Ajouter des preuves',
            self::STEP_REVIEW_SUBMIT => 'Vérifier et envoyer',
        ];
    }

    /**
     * Get the initial prompt for step A
     */
    public static function getInitialPrompt(): string
    {
        return 'Bonjour ! Je suis votre assistant pour créer une réclamation. Commençons par sélectionner la réservation concernée.';
    }

    /**
     * Get the prompt template for problem description
     */
    public static function getProblemDescriptionPrompt(string $equipmentName, string $dates): string
    {
        return sprintf(
            'Très bien ! Vous créez une réclamation pour %s (%s). Pouvez-vous décrire brièvement le problème que vous avez rencontré ?',
            $equipmentName,
            $dates
        );
    }

    /**
     * Get the prompt for incident identification
     */
    public static function getIncidentIdentificationPrompt(): string
    {
        return 'Maintenant, identifions les incidents précis. Pour chaque incident, j\'aurai besoin de :' . "\n" .
               '• Type de problème (endommagé, manquant, mal fonctionnant, autre)' . "\n" .
               '• Titre du problème' . "\n" .
               '• Description détaillée' . "\n" .
               'Y a-t-il un ou plusieurs incidents ?';
    }

    /**
     * Get the prompt for evidence upload
     */
    public static function getEvidenceUploadPrompt(): string
    {
        return 'Vous pouvez maintenant ajouter une photo ou une preuve pour soutenir votre réclamation (optionnel). ' .
               'Les formats acceptés sont JPEG, PNG et WebP (max 5 MB).';
    }

    /**
     * Get the prompt for review
     */
    public static function getReviewPrompt(): string
    {
        return 'Veuillez vérifier votre réclamation ci-dessous. Si tout est correct, confirmez l\'envoi.';
    }

    /**
     * Validate problem type
     */
    public static function isValidProblemType(string $type): bool
    {
        return in_array($type, ['endommage', 'manquant', 'malfonctionnant', 'autre']);
    }

    /**
     * Get problem type labels for display
     */
    public static function getProblemTypeLabels(): array
    {
        return [
            'endommage' => 'Endommagé',
            'manquant' => 'Manquant',
            'malfonctionnant' => 'Mal fonctionnant',
            'autre' => 'Autre',
        ];
    }

    /**
     * Get label for problem type
     */
    public static function getProblemTypeLabel(string $type): string
    {
        return self::getProblemTypeLabels()[$type] ?? $type;
    }

    /**
     * Build a summary message for review
     */
    public static function buildReviewSummary(
        string $equipmentName,
        string $reservationLabel,
        string $reclamationTitle,
        array $incidents,
        ?array $attachments = null
    ): string {
        $summary = "**Résumé de votre réclamation :**\n\n";
        $summary .= "📋 Réservation: {$reservationLabel}\n";
        $summary .= "⚙️ Équipement: {$equipmentName}\n";
        $summary .= "📝 Titre: {$reclamationTitle}\n\n";

        $summary .= "**Incidents ({" . count($incidents) . "}) :**\n";
        foreach ($incidents as $i => $incident) {
            $label = self::getProblemTypeLabel($incident['type_probleme']);
            $summary .= "\n" . ($i + 1) . ". [{$label}] {$incident['titre']}\n";
            $summary .= "   {$incident['description']}\n";
            if (!empty($incident['attachment'])) {
                $summary .= "   📎 Preuve jointe\n";
            }
        }

        if ($attachments && count($attachments) > 0) {
            $summary .= "\n**Preuves jointes:**\n";
            foreach ($attachments as $attachment) {
                $summary .= "• {$attachment}\n";
            }
        }

        return $summary;
    }

    /**
     * Format a conversation message from the assistant
     */
    public static function formatAssistantMessage(string $message, ?string $step = null): array
    {
        return [
            'type' => 'assistant',
            'message' => $message,
            'step' => $step,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Format a conversation message from the user
     */
    public static function formatUserMessage(string $message): array
    {
        return [
            'type' => 'user',
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Format a system message
     */
    public static function formatSystemMessage(string $message, ?string $action = null): array
    {
        return [
            'type' => 'system',
            'message' => $message,
            'action' => $action,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
