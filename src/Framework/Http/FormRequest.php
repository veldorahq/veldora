<?php

declare(strict_types=1);

namespace Veldora\Framework\Http;

use RuntimeException;
use Veldora\Framework\Validation\Validator;

class FormRequest extends Request
{
    /**
     * The validated data cache.
     *
     * @var array<string, mixed>|null
     */
    protected ?array $validatedData = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<string>|string>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Validate the class instance after container injection.
     */
    public function validateResolved(): void
    {
        if (!$this->authorize()) {
            $this->failedAuthorization();
        }

        $rules = $this->rules();
        if (empty($rules)) {
            $this->validatedData = $this->all();
            return;
        }

        $validator = Validator::make($this->all(), $rules, $this->messages());

        if ($validator->fails()) {
            $this->failedValidation($validator);
        }

        $this->validatedData = $validator->validated();
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization(): never
    {
        if ($this->expectsJson()) {
            $response = new Response(json_encode([
                'message' => 'This action is unauthorized.',
            ]), 403, ['Content-Type' => 'application/json']);
            $response->send();
            exit(403);
        }

        abort(403, 'This action is unauthorized.');
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): never
    {
        $errors = $validator->errors();

        if ($this->expectsJson()) {
            $response = new Response(json_encode([
                'message' => 'The given data was invalid.',
                'errors' => $errors,
            ]), 422, ['Content-Type' => 'application/json']);
            $response->send();
            exit(422);
        }

        if (function_exists('session')) {
            session()->flash('errors', $errors);
            session()->flash('_old_input', $this->all());
        }

        $prevUrl = $this->header('Referer') ?: '/';
        header("Location: {$prevUrl}");
        exit(0);
    }

    /**
     * Get the validated input data.
     */
    public function validated(?string $key = null, mixed $default = null): mixed
    {
        if ($this->validatedData === null) {
            $this->validateResolved();
        }

        if ($key === null) {
            return $this->validatedData;
        }

        return $this->validatedData[$key] ?? $default;
    }
}
