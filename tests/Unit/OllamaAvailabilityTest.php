<?php

namespace Tests\Unit;

use App\Services\OllamaAvailability;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OllamaAvailabilityTest extends TestCase
{
    public function test_model_match_accepts_the_configured_tag(): void
    {
        $availability = new OllamaAvailability();

        $this->assertTrue($availability->modelIsListed(['mistral:latest'], 'mistral:latest'));
        $this->assertTrue($availability->modelIsListed(['mistral:latest'], 'mistral'));
        $this->assertFalse($availability->modelIsListed(['mistral:latest'], 'other:latest'));
    }

    public function test_is_ready_requires_the_named_model(): void
    {
        Http::fake([
            'http://ollama:11434/api/tags' => Http::response([
                'models' => [
                    ['name' => 'mistral:latest'],
                ],
            ]),
        ]);

        $availability = new OllamaAvailability();

        $this->assertTrue($availability->isReady('http://ollama:11434', 'mistral:latest'));
        $this->assertFalse($availability->isReady('http://ollama:11434', 'other:latest'));
    }

    public function test_ensure_throws_when_the_configured_model_is_absent(): void
    {
        Http::fake([
            'http://ollama:11434/api/tags' => Http::response([
                'models' => [
                    ['name' => 'other:latest'],
                ],
            ]),
        ]);

        $this->expectException(ConnectionException::class);

        app(OllamaAvailability::class)->ensure('http://ollama:11434', 'mistral:latest');
    }

    public function test_ensure_before_call_does_not_contact_ollama_during_tests(): void
    {
        Http::fake();

        app(OllamaAvailability::class)->ensureBeforeCall('http://ollama:11434', 'mistral:latest');

        Http::assertNothingSent();
    }
}
