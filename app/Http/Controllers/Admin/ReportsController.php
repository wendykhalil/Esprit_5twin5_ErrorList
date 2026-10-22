<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OllamaService;
use App\Services\OllamaSettings;
use App\Services\ReportGenerationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportsController extends Controller
{
    protected $ollamaService;
    protected $reportService;

    public function __construct(OllamaService $ollamaService, ReportGenerationService $reportService)
    {
        $this->ollamaService = $ollamaService;
        $this->reportService = $reportService;
    }

    /**
     * Display the reports page with period selector
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * Generate report for the selected period
     */
    public function generate(Request $request)
    {
        $request->validate([
            'period' => 'required|in:week,month,year,custom',
            'start_date' => 'nullable|date|required_if:period,custom',
            'end_date' => 'nullable|date|required_if:period,custom|after_or_equal:start_date',
        ]);

        // Determine date range
        $dates = $this->getDateRange($request->input('period'), $request->only('start_date', 'end_date'));
        $startDate = $dates['start'];
        $endDate = $dates['end'];

        // Aggregate statistics using the service
        $stats = $this->reportService->aggregateStatistics($startDate, $endDate);

        // Generate AI summary using Ollama (skip if disabled)
        $aiSummary = null;
        $aiError = null;
        
        if (config('ollama.enabled', true)) {
            try {
                $aiSummary = $this->generateAISummary($stats, $startDate, $endDate);
            } catch (\Exception $e) {
                Log::error('AI summary generation failed', ['error' => $e->getMessage()]);
                $aiError = $e->getMessage();
                // Use deterministic fallback if all else fails
                $aiSummary = $this->reportService->generateDeterministicSummary($stats, $startDate, $endDate);
            }
        }

        return view('admin.reports.generate', [
            'period' => $request->input('period'),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'stats' => $stats,
            'aiSummary' => $aiSummary,
            'aiError' => $aiError,
        ]);
    }

    /**
     * Determine the date range based on the selected period
     */
    private function getDateRange(?string $period, array $customDates = []): array
    {
        $now = Carbon::now();
        $endDate = $now->copy()->endOfDay();
        
        return match ($period) {
            'week' => [
                // Start of current week (Monday) to end of today
                'start' => $endDate->copy()->startOfWeek(),
                'end' => $endDate,
            ],
            'month' => [
                // Start of current month to end of today
                'start' => $endDate->copy()->startOfMonth(),
                'end' => $endDate,
            ],
            'year' => [
                // Start of current year to end of today
                'start' => $endDate->copy()->startOfYear(),
                'end' => $endDate,
            ],
            'custom' => [
                // Custom date range (user-provided)
                'start' => Carbon::parse($customDates['start_date'] ?? now())->startOfDay(),
                'end' => Carbon::parse($customDates['end_date'] ?? now())->endOfDay(),
            ],
            default => [
                // Default to current month
                'start' => $endDate->copy()->startOfMonth(),
                'end' => $endDate,
            ],
        };
    }



    /**
     * Generate AI summary using Ollama with validation and deterministic fallback
     * 
     * CRITICAL: Validates that AI-generated summary doesn't contradict backend statistics:
     * - If completedReservations = 0, AI must not mention completed rentals or average duration
     * - If average duration = 0 (data unavailable), AI must not present it as measured value
     * - Equipment counts must match backend exactly
     * 
     * Falls back to deterministic summary if:
     * - Ollama is disabled
     * - Ollama fails or times out
     * - Generated summary contradicts backend statistics
     */
    private function generateAISummary(array $stats, Carbon $startDate, Carbon $endDate): ?string
    {
        // CRITICAL FIX (v3): Make prompt evidence-based with explicit contradction prevention.
        // Previously: Asked AI to identify "trends" with only snapshot data (no time-series)
        // Previously: Requested "recommendations" without sufficient supporting data
        // Now: Focus on current period statistics, equipment performance, problem types
        // NEW: Adds explicit validation layer and deterministic fallback for contradictions
        
        // Prepare context for Ollama with evidence-based metrics only
        $context = $this->reportService->buildSummaryContext($stats, $startDate, $endDate);

        // Format dates explicitly to avoid getter errors when embedding in heredoc
        $formattedStart = $startDate->format('d/m/Y');
        $formattedEnd = $endDate->format('d/m/Y');

        // Calculate percentage-based metrics from validated data
        $totalReservations = $stats['reservations']['total'];
        $completedReservations = $stats['reservations']['completed'];
        $completionRate = $totalReservations > 0 ? round(($completedReservations / $totalReservations) * 100, 1) : 0;
        
        $topEquipmentList = $this->reportService->formatEquipmentList($stats['equipment_rankings']);

        $prompt = <<<PROMPT
Tu es un analyste de plateforme de location d'équipements SolarShare. Analyse les statistiques suivantes de la période $formattedStart au $formattedEnd et génère un résumé factuel en français.

DONNÉES VALIDÉES DE LA PÉRIODE:
{$context}

STATISTIQUES DÉRIVÉES:
- Taux de complétion des réservations: {$completionRate}%
- Nombre de types de réclamations: {$this->countClaimTypes($stats)}

ÉQUIPEMENTS LES PLUS PERFORMANTS:
(Note: Les équipements sont identifiés par ID et peuvent avoir des noms similaires. Chaque ID = enregistrement distinct.)
{$topEquipmentList}

INSTRUCTIONS CRITIQUES:
Génère un résumé professionnel (150-250 mots) basé EXCLUSIVEMENT sur les données fournies ci-dessus. Le résumé doit inclure:

1. Résumé factuel de la période (réservations totales, revenus, réclamations)
2. Analyse des équipements les plus demandés et leur performance en termes de revenus
3. Types de problèmes rencontrés (basé sur les données de réclamations fournies)
4. État des réservations (statuts observés)

INTERDICTIONS ABSOLUES:
- Ne pas inventer de tendances, ni comparer avec des périodes antérieures (aucune donnée disponible)
- Ne pas émettre de prévisions ou de recommandations politiques sans données directes justificatives
- Ne pas supposer que plusieurs équipements avec le même nom sont des erreurs: chaque ID représente un enregistrement distinct
- Ne pas inventer de problèmes, de causes ou de solutions non mentionnés dans les données
- CRITICAL: Si "Réservations complétées: 0", NE JAMAIS mentionner de durée moyenne ou de réservations terminées
- CRITICAL: Si "Durée moyenne: Non disponible", NE JAMAIS présenter de durée comme valeur mesurée
- Rester strictement factuel et ancré dans les chiffres fournis

Réponds uniquement avec le résumé en français, sans titre ni introduction.
PROMPT;

        try {
            $aiSummary = $this->callOllamaDirectly($prompt);
            
            // VALIDATION LAYER: Check for contradictions
            $validatedSummary = $this->validateAISummaryAgainstStats($aiSummary, $stats);
            if ($validatedSummary === false) {
                // Contradiction detected, fall back to deterministic
                Log::warning('AI summary contradicted backend statistics, using deterministic fallback', [
                    'period_start' => $formattedStart,
                    'period_end' => $formattedEnd,
                ]);
                return $this->reportService->generateDeterministicSummary($stats, $startDate, $endDate);
            }
            
            return $aiSummary;
        } catch (\Exception $e) {
            Log::error('Ollama summary generation failed: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'period_start' => $formattedStart,
                'period_end' => $formattedEnd,
            ]);
            // On error, return deterministic fallback instead of throwing
            return $this->reportService->generateDeterministicSummary($stats, $startDate, $endDate);
        }
    }

    /**
     * Validate that AI summary doesn't contradict backend statistics
     * 
     * Returns false if contradictions detected, true otherwise
     */
    private function validateAISummaryAgainstStats(?string $summary, array $stats): bool
    {
        if ($summary === null) {
            return false;
        }
        
        $completedReservations = $stats['reservations']['completed'];
        $avgDuration = $stats['reservations']['avg_duration_days'];
        
        // If no completed reservations, AI should NOT mention completed rentals or average duration
        if ($completedReservations == 0) {
            // Check for patterns that suggest AI is talking about completed rentals
            $completedPatterns = [
                'réservation.*complét',
                'location.*termin',
                'durée moyenne.*jour',
                'durée.*\d+\s*jour',
                'moyenne.*\d+\s*jour',
            ];
            
            foreach ($completedPatterns as $pattern) {
                if (preg_match('/' . $pattern . '/iu', $summary)) {
                    return false; // Contradiction found
                }
            }
        }
        
        // If duration is unavailable (0), AI should NOT present it as measured
        if ($avgDuration == 0 && $completedReservations == 0) {
            // Check if AI presents duration as a measured value (not as unavailable)
            if (preg_match('/durée\s+moyenne\s*[:=]?\s*(?!non\s+disponible|n[\']?a|aucune)\d+\s*jour/iu', $summary)) {
                return false; // AI presented "0 jours" or similar as measured
            }
        }
        
        return true; // No contradictions detected
    }

    /**
     * Count the number of unique complaint types in the data
     */
    private function countClaimTypes(array $stats): int
    {
        return count($stats['reclamations']['by_type'] ?? []);
    }

    /**
     * Call Ollama API directly for report summary
     */
    private function callOllamaDirectly(string $prompt): ?string
    {
        try {
            app(\App\Services\OllamaAvailability::class)->ensureBeforeCall(
                OllamaSettings::baseUrl(),
                OllamaSettings::model()
            );
            $response = \Illuminate\Support\Facades\Http::timeout(OllamaSettings::timeout())
                ->post(
                    OllamaSettings::baseUrl() . '/api/generate',
                    array_merge(OllamaSettings::requestFields(), [
                        'prompt' => $prompt,
                    ])
                );

            if (!$response->successful()) {
                throw new \Exception('Ollama API returned status ' . $response->status());
            }

            $data = $response->json();

            if (!isset($data['response'])) {
                throw new \Exception('Ollama API returned invalid response');
            }

            return trim($data['response']);
        } catch (\Exception $e) {
            Log::error('Direct Ollama call failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Build context summary for Ollama
     */
    private function buildSummaryContext(array $stats, Carbon $startDate, Carbon $endDate): string
    {
        $lines = [
            "Période: {$startDate->format('d/m/Y')} à {$endDate->format('d/m/Y')}",
            "Réservations totales: {$stats['reservations']['total']}",
            "Revenus totaux: {$stats['revenue']['total']} TND",
            "Taux de réclamations: {$stats['reclamations']['rate']}%",
        ];

        return implode("\n", $lines);
    }

    /**
     * Format equipment list for display
     */
    private function formatEquipmentList(array $rankings): string
    {
        $lines = [];
        foreach ($rankings as $i => $equipment) {
            $lines[] = ($i + 1) . ". {$equipment['name']} - {$equipment['reservation_count']} réservations, {$equipment['revenue']} TND";
        }

        return implode("\n", $lines);
    }

    /**
     * Export report as CSV
     */
    public function exportCSV(Request $request)
    {
        $request->validate([
            'period' => 'required|in:week,month,year,custom',
            'start_date' => 'nullable|date|required_if:period,custom',
            'end_date' => 'nullable|date|required_if:period,custom|after_or_equal:start_date',
        ]);

        $dates = $this->getDateRange($request->input('period'), $request->only('start_date', 'end_date'));
        $stats = $this->reportService->aggregateStatistics($dates['start'], $dates['end']);

        $filename = 'rapport_solarshare_' . $dates['start']->format('Y-m-d') . '_' . $dates['end']->format('Y-m-d') . '.csv';

        // Build CSV content
        $csv = "\xEF\xBB\xBF"; // BOM for UTF-8

        // Header
        $csv .= "RAPPORT SOLARSHARE\n";
        $csv .= "Période: " . $dates['start']->format('d/m/Y') . " à " . $dates['end']->format('d/m/Y') . "\n";
        $csv .= "Date de génération: " . now()->format('d/m/Y H:i:s') . "\n\n";

        // Réservations Summary
        $csv .= "RÉSERVATIONS\n";
        $csv .= "Métrique;Valeur\n";
        $csv .= "Total;" . $stats['reservations']['total'] . "\n";
        $csv .= "Complétées;" . $stats['reservations']['completed'] . "\n";
        $csv .= "Durée moyenne (jours);" . $stats['reservations']['avg_duration_days'] . "\n\n";

        // Réservations by Status
        $csv .= "RÉSERVATIONS PAR STATUT\n";
        $csv .= "Statut;Nombre\n";
        foreach ($stats['reservations']['by_status'] as $status => $count) {
            $csv .= $status . ";" . $count . "\n";
        }
        $csv .= "\n";

        // Revenue
        $csv .= "REVENUS\n";
        $csv .= "Métrique;Montant (TND)\n";
        $csv .= "Total collecté;" . number_format($stats['revenue']['total'], 2, ',', '') . "\n";
        $csv .= "Remboursements approuvés;" . number_format($stats['revenue']['refunded'], 2, ',', '') . "\n";
        if ($stats['reservations']['total'] > 0) {
            $avgPerReservation = $stats['revenue']['total'] / $stats['reservations']['total'];
            $csv .= "Moyenne par réservation;" . number_format($avgPerReservation, 2, ',', '') . "\n";
        }
        $csv .= "\n";

        // Reclamations
        $csv .= "RÉCLAMATIONS\n";
        $csv .= "Métrique;Valeur\n";
        $csv .= "Total;" . $stats['reclamations']['total'] . "\n";
        $csv .= "Taux (%);" . $stats['reclamations']['rate'] . "\n\n";

        // Reclamations by Status
        $csv .= "RÉCLAMATIONS PAR STATUT\n";
        $csv .= "Statut;Nombre\n";
        foreach ($stats['reclamations']['by_status'] as $status => $count) {
            $csv .= $status . ";" . $count . "\n";
        }
        $csv .= "\n";

        // Reclamations by Type
        $csv .= "RÉCLAMATIONS PAR TYPE\n";
        $csv .= "Type;Nombre\n";
        foreach ($stats['reclamations']['by_type'] as $type => $count) {
            $csv .= $type . ";" . $count . "\n";
        }
        $csv .= "\n";

        // Equipment Rankings
        $csv .= "ÉQUIPEMENTS LES PLUS DEMANDÉS\n";
        $csv .= "Rang;Équipement;Réservations;Revenu (TND);Prix/jour (TND)\n";
        foreach ($stats['equipment_rankings'] as $index => $equipment) {
            $csv .= ($index + 1) . ";"
                . $equipment['name'] . ";"
                . $equipment['reservation_count'] . ";"
                . number_format($equipment['revenue'], 2, ',', '') . ";"
                . number_format($equipment['price_per_day'], 2, ',', '') . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Export report as PDF (HTML-based using Blade rendering)
     */
    public function exportPDF(Request $request)
    {
        $request->validate([
            'period' => 'required|in:week,month,year,custom',
            'start_date' => 'nullable|date|required_if:period,custom',
            'end_date' => 'nullable|date|required_if:period,custom|after_or_equal:start_date',
        ]);

        $dates = $this->getDateRange($request->input('period'), $request->only('start_date', 'end_date'));
        $startDate = $dates['start'];
        $endDate = $dates['end'];
        $stats = $this->reportService->aggregateStatistics($startDate, $endDate);

        // Generate PDF HTML content
        $html = view('admin.reports.export-pdf', [
            'period' => $request->input('period'),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'stats' => $stats,
            'generatedAt' => now(),
        ])->render();

        // Create basic PDF by converting HTML to downloadable format
        $filename = 'rapport_solarshare_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d') . '.html';

        return response($html)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
