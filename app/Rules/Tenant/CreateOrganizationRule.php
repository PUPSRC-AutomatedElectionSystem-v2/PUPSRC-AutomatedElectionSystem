<?php

namespace App\Rules\Tenant;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class CreateOrganizationRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $validator = Validator::make([$attribute => $value], [
            $attribute.'.short_name' => 'required|string|max:100',
            $attribute.'.name' => 'required|string|max:255',
            $attribute.'.category_name' => 'required|string|max:150',
            $attribute.'.should_copy_from_other_org' => 'sometimes|boolean',
            $attribute.'.allow_cross_membership' => 'sometimes|boolean',
            $attribute.'.theme' => 'sometimes|array',
            $attribute.'.order' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $fail($error);
            }
        }
    }
}
