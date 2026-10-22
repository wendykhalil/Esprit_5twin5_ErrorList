<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class OllamaAvailability
{
    public function isReady(string $baseUrl, string $model, int $timeout = 3): bool
    {
        try {
            $response = Http::connectTimeout(2)
                ->timeout($timeout)
                ->get(rtrim($baseUrl, '/').'/api/tags');
        } catch (ConnectionException) {
            return false;
        }

        if (! $response->successful()) {
            return false;
        }

        $names = collect($response->json('models') ?? [])
            ->pluck('name')
            ->filter(fn ($name) => is_string($name) && $name !== '')
            ->values()
            ->all();

        return $this->modelIsListed($names, $model);
    }

    /**
     * @param  list<string>  $names
     */
    public function modelIsListed(array $names, string $model): bool
    {
        $wanted = $this->normalize($model);
        if ($wanted === '') {
            return false;
        }

        foreach ($names as $name) {
            if ($this->normalize($name) === $wanted) {
                return true;
            }
        }

        return false;
    }

    public function ensure(string $baseUrl, string $model): void
    {
        if ($this->isReady($baseUrl, $model)) {
            return;
        }

        throw new ConnectionException('Ollama ou le modèle '.$model.' est indisponible.');
    }

    /**
     * PHPUnit remplace déjà les appels HTTP. La vérification réelle reste active hors tests.
     */
    public function ensureBeforeCall(string $baseUrl, string $model): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $this->ensure($baseUrl, $model);
    }

    private function normalize(string $name): string
    {
        $name = strtolower(trim($name));
        if ($name === '') {
            return '';
        }

        if (! str_contains($name, ':')) {
            $name .= ':latest';
        }

        return $name;
    }
}
