<?php

namespace App\Exceptions\Email;

use Exception;
use Illuminate\Validation\ValidationException;

class InvalidEmailException extends Exception
{
    public function __construct(string $message = 'Invalid email provided.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function fromValidationException(ValidationException $e): self
    {
        $message = $e->validator->errors()->first('email') ?? 'Invalid email provided.';
        return new self($message, 0, $e);
    }
}
