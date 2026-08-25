<?php

declare(strict_types=1);

namespace Veldora\Framework\Validation;

interface Rule
{
    /**
     * Determine if the validation rule passes.
     */
    public function passes(string $attribute, mixed $value): bool;

    /**
     * Get the validation error message.
     */
    public function message(): string;
}
