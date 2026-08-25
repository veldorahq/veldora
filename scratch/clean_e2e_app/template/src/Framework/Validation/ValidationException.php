<?php

declare(strict_types=1);

namespace Veldora\Framework\Validation;

use Exception;

class ValidationException extends Exception
{
    /**
     * Create a new ValidationException instance.
     */
    public function __construct(protected Validator $validator)
    {
        parent::__construct('The given data was invalid.');
    }

    /**
     * Get the validator instance.
     */
    public function getValidator(): Validator
    {
        return $this->validator;
    }

    /**
     * Get the validation errors.
     *
     * @return array<string, array<string>>
     */
    public function getErrors(): array
    {
        return $this->validator->errors();
    }
}
