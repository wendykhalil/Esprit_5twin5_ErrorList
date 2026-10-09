<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * bcrypt only uses the first 72 bytes. Reject longer values instead of hashing a truncated password.
 */
class PasswordWithinHashLimit implements ValidationRule
{
    public const MAX_BYTES = 72;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        if (strlen($value) > self::MAX_BYTES) {
            $fail('Le mot de passe ne doit pas dépasser '.self::MAX_BYTES.' octets.');
        }
    }
}
