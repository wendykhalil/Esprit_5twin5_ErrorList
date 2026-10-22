<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\ReportsController;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\ConseillerEnergieService;
use App\Services\OllamaService;
use App\Services\OllamaSettings;
use App\Services\SolarShareAIService;
use App\Services\SupportTicketAssistant;
use App\Services\TechnicalServiceAIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

class OllamaModelConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['ollama.enabled' => true]);
    }

    public function test_every_config_entry_uses_the_same_mistral_model_and_url(): void
    {
        $this->assertSame('mistral:latest', config('ollama.model'));
        $this->assertSame(config('ollama.model'), config('solarshare.ollama.model'));
        $this->assertSame(config('ollama.model'), config('services.ollama.model'));
        $this->assertSame(config('ollama.base_url'), rtrim((string) config('services.ollama.url'), '/'));
        $this->assertSame(config('ollama.base_url'), rtrim((string) config('solarshare.ollama.base_url'), '/'));
        $this->assertSame(180, (int) config('ollama.timeout'));
        $this->assertSame((int) config('ollama.timeout'), (int) config('solarshare.ollama.timeout'));
        $this->assertSame('-1', config('ollama.keep_alive'));
        $this->assertSame(1024, (int) config('ollama.num_ctx'));
        $this->assertSame(220, (int) config('ollama.num_predict'));
        $this->assertNotSame('qwen2.5:3b', OllamaSettings::model());
        $this->assertNotSame('llama3.2:3b', OllamaSettings::model());
    }

    public function test_ollama_service_sends_the_central_model(): void
    {
        $this->fakeGenerate();

        app(OllamaService::class)->generateIncidentDescription('endommage', 'Panneau fissure');

        $this->assertModelSent('/api/generate');
    }

    public function test_technical_service_sends_the_central_model(): void
    {
        $this->fakeGenerate();

        (new TechnicalServiceAIService())->analyze('Panne', 'Le panneau ne produit rien');

        $this->assertModelSent('/api/generate');
    }

    public function test_solarshare_ai_service_sends_the_central_model(): void
    {
        Http::fake([
            '*' => Http::response(['message' => ['content' => '{"title":"Guide"}']], 200),
        ]);

        app(SolarShareAIService::class)->generateUsageGuide(['name' => 'Panneau']);

        $this->assertModelSent('/api/chat');
    }

    public function test_conseiller_sends_the_central_model(): void
    {
        Http::fake([
            '*' => Http::response(['message' => ['content' => 'texte']], 200),
        ]);

        app(ConseillerEnergieService::class)->bilan(1.5, 0.4, 1);

        $this->assertModelSent('/api/chat');
    }

    public function test_conseiller_is_unavailable_when_the_model_is_absent(): void
    {
        Http::fake([
            '*' => Http::response(['models' => [['name' => 'other:latest']]], 200),
        ]);

        $this->assertFalse(app(ConseillerEnergieService::class)->estDisponible());
    }

    public function test_support_assistant_sends_the_central_model(): void
    {
        Http::fake([
            '*' => Http::response(['message' => ['content' => '{}']], 200),
        ]);
        $ticket = SupportTicket::factory()->open()->create([
            'user_id' => User::factory()->create()->id,
        ]);

        try {
            app(SupportTicketAssistant::class)->analyze($ticket);
        } catch (\Throwable) {
        }

        $this->assertModelSent('/api/chat');
    }

    public function test_reports_controller_sends_the_central_model(): void
    {
        $this->fakeGenerate();
        $method = new ReflectionMethod(app(ReportsController::class), 'callOllamaDirectly');
        $method->setAccessible(true);
        $method->invoke(app(ReportsController::class), 'Resume les reservations.');

        $this->assertModelSent('/api/generate');
    }

    private function fakeGenerate(): void
    {
        Http::fake([
            '*' => Http::response(['response' => 'Description de test.'], 200),
        ]);
    }

    private function assertModelSent(string $path): void
    {
        Http::assertSent(function (Request $request) use ($path) {
            return str_contains($request->url(), $path)
                && $request['model'] === 'mistral:latest'
                && $request['model'] === OllamaSettings::model()
                && $request['keep_alive'] === OllamaSettings::keepAlive()
                && ($request['options']['num_ctx'] ?? null) === OllamaSettings::numCtx();
        });
    }
}
