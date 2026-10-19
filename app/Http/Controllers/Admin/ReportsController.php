<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OllamaService;
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

        // Generate AI summary using Ollama
        $aiSummary = null;
        $aiError = null;
        
        // Only attempt AI summary if Ollama is enabled
        if (config('ollama.enabled', true)) {
            try {
                $aiSummary = $this->generateAISummary($stats, $startDate, $endDate);
            } catch (\Exception $e) {
                Log::error('AI summary generation failed', ['error' => $e->getMessage()]);
                $aiError = $e->getMessage();
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
     * Generate AI summary using Ollama
     * 
     * CRITICAL FIX (v2): Make prompt evidence-based.
     * Previously: Asked AI to identify "trends" with only snapshot data (no time-series)
     * Previously: Requested "recommendations" without sufficient supporting data
     * Now: Focus on current period statistics, equipment performance, problem types
     * No invented trends or unsupported recommendations
     */
    private function generateAISummary(array $stats, Carbon $startDate, Carbon $endDate): ?string
    {
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
{$topEquipmentList}

INSTRUCTIONS:
Génère un résumé professionnel (150-250 mots) basé EXCLUSIVEMENT sur les données fournies ci-dessus. Le résumé doit inclure:

1. Résumé factuel de la période (réservations totales, revenus, réclamations)
2. Analyse des équipements les plus demandés et leur performance en termes de revenus
3. Types de problèmes rencontrés (basé sur les données de réclamations fournies)
4. Taux de complétion des réservations

Ne pas inventer de tendances, de prévisions ou de recommandations non supportées par les données.
Ne pas suggérer de changements de politique sans données justificatives.
Reste factuel et ancré dans les chiffres fournis.
Réponds uniquement avec le résumé en français, sans titre ni introduction.
PROMPT;

        try {
            return $this->callOllamaDirectly($prompt);
        } catch (\Exception $e) {
            Log::error('Ollama summary generation failed: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'period_start' => $formattedStart,
                'period_end' => $formattedEnd,
            ]);
            throw $e;
        }
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
            $response = \Illuminate\Support\Facades\Http::timeout(config('ollama.timeout', 60))
                ->post(
                    config('ollama.base_url') . '/api/generate',
                    [
                        'model' => config('ollama.model'),
                        'prompt' => $prompt,
                        'stream' => false,
                    ]
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
}
