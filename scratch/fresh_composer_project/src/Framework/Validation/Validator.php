<?php

declare(strict_types=1);

namespace Veldora\Framework\Validation;

use InvalidArgumentException;
use Veldora\Framework\Foundation\Application;
use Veldora\Framework\Database\Connection;

class Validator
{
    /**
     * The validation errors.
     *
     * @var array<string, array<string>>
     */
    protected array $errors = [];

    /**
     * The validated data parameters.
     *
     * @var array<string, mixed>
     */
    protected array $validated = [];

    /**
     * Create a new Validator instance.
     *
     * @param array<string, mixed> $data
     * @param array<string, string|array<mixed>> $rules
     * @param array<string, string> $messages
     */
    public function __construct(
        protected array $data,
        protected array $rules,
        protected array $messages = []
    ) {
        $this->runValidation();
    }

    /**
     * Create a new Validator instance.
     *
     * @param array<string, mixed> $data
     * @param array<string, string|array<mixed>> $rules
     * @param array<string, string> $messages
     */
    public static function make(array $data, array $rules, array $messages = []): self
    {
        return new self($data, $rules, $messages);
    }

    /**
     * Run the validation routines and return validated data or throw ValidationException.
     *
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function validate(): array
    {
        if ($this->fails()) {
            throw new ValidationException($this);
        }

        return $this->validated();
    }

    /**
     * Run the validation routines.
     */
    public function runValidation(): void
    {
        foreach ($this->rules as $attribute => $attributeRules) {
            $value = $this->data[$attribute] ?? null;

            // Normalize rules list
            if (is_string($attributeRules)) {
                $attributeRules = explode('|', $attributeRules);
            }

            // Check if nullable is present
            $isNullable = in_array('nullable', $attributeRules, true);
            if ($isNullable && ($value === null || $value === '')) {
                // If it is nullable and value is empty/null, skip checks
                $this->validated[$attribute] = null;
                continue;
            }

            $failed = false;

            foreach ($attributeRules as $rule) {
                if ($rule === 'nullable') {
                    continue;
                }

                if ($rule instanceof Rule) {
                    if (!$rule->passes($attribute, $value)) {
                        $this->addError($attribute, $rule->message());
                        $failed = true;
                    }
                    continue;
                }

                // Parse rule parameters e.g., min:5
                $parts = explode(':', $rule, 2);
                $ruleName = $parts[0];
                $parameters = isset($parts[1]) ? explode(',', $parts[1]) : [];

                $method = 'validate' . str_replace(' ', '', ucwords(str_replace('_', ' ', $ruleName)));

                if (method_exists($this, $method)) {
                    if (!$this->$method($attribute, $value, $parameters)) {
                        $this->addError($attribute, $this->getErrorMessage($attribute, $ruleName, $parameters));
                        $failed = true;
                    }
                } else {
                    throw new InvalidArgumentException("Validation rule [{$ruleName}] is not supported.");
                }
            }

            if (!$failed) {
                $this->validated[$attribute] = $value;
            }
        }
    }

    /**
     * Determine if validation failed.
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Determine if validation passed.
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Get the validation errors.
     *
     * @return array<string, array<string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get the validated parameters.
     *
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        return $this->validated;
    }

    /**
     * Add a validation error for a field.
     */
    protected function addError(string $attribute, string $message): void
    {
        $this->errors[$attribute][] = $message;
    }

    /**
     * Get error message based on rule and params.
     *
     * @param array<string> $parameters
     */
    protected function getErrorMessage(string $attribute, string $rule, array $parameters): string
    {
        $field = str_replace('_', ' ', $attribute);
        $field = ucwords($field);

        return match ($rule) {
            'required' => "The {$field} field is required.",
            'string' => "The {$field} field must be a string.",
            'email' => "The {$field} must be a valid email address.",
            'numeric' => "The {$field} must be a number.",
            'integer' => "The {$field} must be an integer.",
            'boolean' => "The {$field} field must be true or false.",
            'array' => "The {$field} must be an array.",
            'min' => "The {$field} must be at least {$parameters[0]}.",
            'max' => "The {$field} must not be greater than {$parameters[0]}.",
            'between' => "The {$field} must be between {$parameters[0]} and {$parameters[1]}.",
            'same' => "The {$field} and {$parameters[0]} must match.",
            'confirmed' => "The {$field} confirmation does not match.",
            'unique' => "The {$field} has already been taken.",
            'exists' => "The selected {$field} is invalid.",
            default => "The {$field} validation failed."
        };
    }

    // --- Validation Rules Implementation ---

    /**
     * Validate required.
     */
    protected function validateRequired(string $attribute, mixed $value, array $parameters): bool
    {
        if ($value === null) {
            return false;
        }
        if (is_string($value) && trim($value) === '') {
            return false;
        }
        if (is_array($value) && count($value) === 0) {
            return false;
        }
        return true;
    }

    /**
     * Validate string.
     */
    protected function validateString(string $attribute, mixed $value, array $parameters): bool
    {
        return is_string($value);
    }

    /**
     * Validate email.
     */
    protected function validateEmail(string $attribute, mixed $value, array $parameters): bool
    {
        if (!is_string($value)) {
            return false;
        }
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate numeric.
     */
    protected function validateNumeric(string $attribute, mixed $value, array $parameters): bool
    {
        return is_numeric($value);
    }

    /**
     * Validate integer.
     */
    protected function validateInteger(string $attribute, mixed $value, array $parameters): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validate boolean.
     */
    protected function validateBoolean(string $attribute, mixed $value, array $parameters): bool
    {
        return is_bool($value) || in_array($value, [0, 1, '0', '1', true, false, 'true', 'false'], true);
    }

    /**
     * Validate array.
     */
    protected function validateArray(string $attribute, mixed $value, array $parameters): bool
    {
        return is_array($value);
    }

    /**
     * Validate minimum length or value.
     */
    protected function validateMin(string $attribute, mixed $value, array $parameters): bool
    {
        if (empty($parameters)) {
            return false;
        }
        $min = (int) $parameters[0];

        if (is_string($value)) {
            return mb_strlen($value) >= $min;
        }
        if (is_numeric($value)) {
            return $value >= $min;
        }
        if (is_array($value)) {
            return count($value) >= $min;
        }
        return false;
    }

    /**
     * Validate maximum length or value.
     */
    protected function validateMax(string $attribute, mixed $value, array $parameters): bool
    {
        if (empty($parameters)) {
            return false;
        }
        $max = (int) $parameters[0];

        if (is_string($value)) {
            return mb_strlen($value) <= $max;
        }
        if (is_numeric($value)) {
            return $value <= $max;
        }
        if (is_array($value)) {
            return count($value) <= $max;
        }
        return false;
    }

    /**
     * Validate between range.
     */
    protected function validateBetween(string $attribute, mixed $value, array $parameters): bool
    {
        if (count($parameters) < 2) {
            return false;
        }
        $min = (int) $parameters[0];
        $max = (int) $parameters[1];

        if (is_string($value)) {
            $len = mb_strlen($value);
            return $len >= $min && $len <= $max;
        }
        if (is_numeric($value)) {
            return $value >= $min && $value <= $max;
        }
        if (is_array($value)) {
            $count = count($value);
            return $count >= $min && $count <= $max;
        }
        return false;
    }

    /**
     * Validate that two attributes match.
     */
    protected function validateSame(string $attribute, mixed $value, array $parameters): bool
    {
        if (empty($parameters)) {
            return false;
        }
        $other = $parameters[0];
        return $value === ($this->data[$other] ?? null);
    }

    /**
     * Validate that confirmation matches.
     */
    protected function validateConfirmed(string $attribute, mixed $value, array $parameters): bool
    {
        $confirmationField = $attribute . '_confirmation';
        return $value === ($this->data[$confirmationField] ?? null);
    }

    /**
     * Validate record exists in database table.
     */
    protected function validateExists(string $attribute, mixed $value, array $parameters): bool
    {
        if (count($parameters) < 1) {
            return false;
        }
        $table = $parameters[0];
        $column = $parameters[1] ?? $attribute;

        $app = Application::getInstance();
        if (!$app->has(Connection::class)) {
            return true; // Gracefully pass when database is not initialized (e.g. testing)
        }

        /** @var Connection $db */
        $db = $app->get(Connection::class);
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $statement = $db->query($sql, [$value]);
        $row = $statement->fetch();

        return (int) ($row['count'] ?? 0) > 0;
    }

    /**
     * Validate uniqueness in database table.
     */
    protected function validateUnique(string $attribute, mixed $value, array $parameters): bool
    {
        if (count($parameters) < 1) {
            return false;
        }
        $table = $parameters[0];
        $column = $parameters[1] ?? $attribute;
        
        $exceptId = $parameters[2] ?? null;
        $idColumn = $parameters[3] ?? 'id';

        $app = Application::getInstance();
        if (!$app->has(Connection::class)) {
            return true;
        }

        /** @var Connection $db */
        $db = $app->get(Connection::class);
        $bindings = [$value];

        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";

        if ($exceptId !== null && $exceptId !== '' && $exceptId !== 'NULL') {
            $sql .= " AND {$idColumn} != ?";
            $bindings[] = $exceptId;
        }

        $statement = $db->query($sql, $bindings);
        $row = $statement->fetch();

        return (int) ($row['count'] ?? 0) === 0;
    }
}
