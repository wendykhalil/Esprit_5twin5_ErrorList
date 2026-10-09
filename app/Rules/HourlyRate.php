<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Positive TND amount with at most two decimal places, within decimal(8, 2).
 */
class HourlyRate implements ValidationRule
{
    public const MAX = '999999.99';

    public const MESSAGE = 'Veuillez saisir un tarif horaire valide, positif et comportant au maximum deux décimales.';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (self::normalize($value) === null) {
            $fail(self::MESSAGE);
        }
    }

    public static function normalize(mixed $value): ?string
    {
        if (is_int($value) || (is_float($value) && is_finite($value))) {
            $cents = $value * 100;

            if (abs($cents - round($cents)) > 0.000001) {
                return null;
            }

            $value = number_format(round($cents) / 100, 2, '.', '');
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if (str_contains($value, ',') && ! str_contains($value, '.')) {
            $value = str_replace(',', '.', $value);
        }

        if (preg_match('/^(?:0|[1-9]\d{0,5})(?:\.\d{1,2})?$/', $value) !== 1) {
            return null;
        }

        if (! str_contains($value, '.')) {
            $value .= '.00';
        } else {
            [$whole, $fraction] = explode('.', $value, 2);
            $value = $whole.'.'.str_pad($fraction, 2, '0');
        }

        if (bccomp($value, '0', 2) !== 1 || bccomp($value, self::MAX, 2) === 1) {
            return null;
        }

        return $value;
    }
}
