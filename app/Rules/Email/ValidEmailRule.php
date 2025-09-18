<?php

namespace App\Rules\Email;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidEmailRule implements ValidationRule
{
    private const PATTERN = EmailPattern::PREG;

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match(self::PATTERN, $value)) {
            $fail(__('validation.email', ['attribute' => $attribute]));
        }
    }
}
