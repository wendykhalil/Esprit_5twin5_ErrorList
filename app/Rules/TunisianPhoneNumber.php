<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TunisianPhoneNumber implements ValidationRule
{
    public const MESSAGE = 'Le numéro de téléphone professionnel est invalide.';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (self::normalize($value) === null) {
            $fail(self::MESSAGE);
        }
    }

    public static function normalize(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $compact = preg_replace('/[\s.\-()]/', '', trim($value)) ?? '';

        if (str_starts_with($compact, '+216')) {
            $national = substr($compact, 4);
        } elseif (str_starts_with($compact, '00216')) {
            $national = substr($compact, 5);
        } elseif (str_starts_with($compact, '216') && strlen($compact) === 11) {
            $national = substr($compact, 3);
        } else {
            $national = $compact;
        }

        if (preg_match('/^[234579]\d{7}$/', $national) !== 1) {
            return null;
        }

        return '+216'.$national;
    }
}
