<?php

namespace App\Exceptions\Validation;

use Illuminate\Validation\ValidationException;

class InvalidDomainException extends ValidationException
{
    public static function forDomain(string $domain, string $attribute = 'domain'): self
    {
        $validator = validator([], []);
        $validator->errors()->add($attribute, 'Invalid domain format provided for: '.$domain);

        return new self($validator);
    }
}
