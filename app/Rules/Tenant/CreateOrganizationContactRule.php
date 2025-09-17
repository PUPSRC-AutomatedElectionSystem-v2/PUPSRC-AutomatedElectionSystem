<?php

namespace App\Rules\Tenant;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class CreateOrganizationContactRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $validator = Validator::make([$attribute => $value], [
            $attribute.'.email' => 'required|email:rfc',
            $attribute.'.website' => 'nullable|url',
            $attribute.'.facebook' => 'nullable|string|max:255',
            $attribute.'.twitter' => 'nullable|string|max:255',
            $attribute.'.instagram' => 'nullable|string|max:255',
            $attribute.'.threads' => 'nullable|string|max:255',
            $attribute.'.discord' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $fail($error);
            }
        }
    }
}
