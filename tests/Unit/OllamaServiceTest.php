<?php

namespace Tests\Unit;

use App\Services\OllamaService;
use Tests\TestCase;

class OllamaServiceTest extends TestCase
{
    /**
     * Test OllamaService can be instantiated
     */
    public function test_ollama_service_instantiation(): void
    {
        $service = new OllamaService();
        $this->assertInstanceOf(OllamaService::class, $service);
    }

    /**
     * Test that service throws exception when Ollama is disabled
     */
    public function test_throws_exception_when_disabled(): void
    {
        config(['ollama.enabled' => false]);
        $service = new OllamaService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Ollama service is not enabled');

        $service->generateIncidentDescription('endommage', 'Test');
    }

    /**
     * Test that service has correct config structure
     */
    public function test_config_structure(): void
    {
        // Verify config keys exist
        $this->assertNotNull(config('ollama.enabled'));
        $this->assertNotNull(config('ollama.base_url'));
        $this->assertNotNull(config('ollama.model'));
        $this->assertNotNull(config('ollama.timeout'));
    }

    /**
     * Test config base URL format
     */
    public function test_config_base_url_format(): void
    {
        $baseUrl = config('ollama.base_url');
        $this->assertStringStartsWith('http', $baseUrl);
        $this->assertStringContainsString('127.0.0.1', $baseUrl);
        $this->assertStringContainsString('11434', $baseUrl);
    }

    /**
     * Test config model is set to qwen2.5:3b
     */
    public function test_config_model_is_qwen(): void
    {
        $model = config('ollama.model');
        $this->assertStringContainsString('qwen', strtolower($model));
        $this->assertStringContainsString('3b', $model);
    }

    /**
     * Test config timeout is reasonable
     */
    public function test_config_timeout_is_reasonable(): void
    {
        $timeout = config('ollama.timeout');
        $this->assertGreaterThanOrEqual(30, $timeout);
        $this->assertLessThanOrEqual(300, $timeout);
    }
}
