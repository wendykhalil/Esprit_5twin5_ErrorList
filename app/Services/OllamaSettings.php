<?php

namespace App\Services;

class OllamaSettings
{
    public static function baseUrl(): string
    {
        return rtrim((string) config('ollama.base_url'), '/');
    }

    public static function model(): string
    {
        return (string) config('ollama.model');
    }

    public static function timeout(): int
    {
        return (int) config('ollama.timeout', 180);
    }

    public static function keepAlive(): string|int
    {
        $value = (string) config('ollama.keep_alive', '-1');

        return $value === '-1' ? -1 : $value;
    }

    public static function numCtx(): int
    {
        return (int) config('ollama.num_ctx', 1024);
    }

    public static function numPredict(): int
    {
        return (int) config('ollama.num_predict', 220);
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public static function options(array $options = []): array
    {
        return array_merge([
            'num_ctx' => self::numCtx(),
            'num_predict' => self::numPredict(),
        ], $options);
    }

    /**
     * Fields shared by every Ollama request. Callers add prompt or messages.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public static function requestFields(array $options = []): array
    {
        return [
            'model' => self::model(),
            'stream' => false,
            'keep_alive' => self::keepAlive(),
            'options' => self::options($options),
        ];
    }
}
