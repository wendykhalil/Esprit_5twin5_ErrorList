<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoUnsafeCharacters implements ValidationRule
{
    public function __construct(
        private readonly string $message,
        private readonly bool $allowNewlines = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $pattern = $this->allowNewlines
            ? '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u'
            : '/[\x00-\x1F\x7F]/u';

        if (preg_match($pattern, $value) === 1) {
            $fail($this->message);
        }
    }
}
