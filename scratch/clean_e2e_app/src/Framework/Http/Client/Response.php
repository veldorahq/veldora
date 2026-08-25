<?php

declare(strict_types=1);

namespace Veldora\Framework\Http\Client;

use ArrayAccess;
use JsonSerializable;
use Stringable;

/**
 * @implements ArrayAccess<string, mixed>
 */
class Response implements ArrayAccess, JsonSerializable, Stringable
{
    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        protected int $status,
        protected string $body,
        protected array $headers = []
    ) {
    }

    /**
     * Get the HTTP status code of the response.
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * Get the body of the response.
     */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * Get the JSON decoded body of the response as an array or specific key.
     */
    public function json(?string $key = null, mixed $default = null): mixed
    {
        $decoded = json_decode($this->body, true);

        if (!is_array($decoded)) {
            return $default;
        }

        if ($key === null) {
            return $decoded;
        }

        return $decoded[$key] ?? $default;
    }

    /**
     * Get the headers of the response.
     *
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * Get a specific header from the response.
     */
    public function header(string $header): ?string
    {
        $normalized = strtolower($header);
        foreach ($this->headers as $key => $val) {
            if (strtolower($key) === $normalized) {
                return $val;
            }
        }

        return null;
    }

    /**
     * Determine if the response was successful (2xx).
     */
    public function successful(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    /**
     * Determine if the response was a 200 OK.
     */
    public function ok(): bool
    {
        return $this->status === 200;
    }

    /**
     * Determine if the response was a redirect (3xx).
     */
    public function redirect(): bool
    {
        return $this->status >= 300 && $this->status < 400;
    }

    /**
     * Determine if the response indicates a client error (4xx) or server error (5xx).
     */
    public function failed(): bool
    {
        return $this->serverError() || $this->clientError();
    }

    /**
     * Determine if the response was a client error (4xx).
     */
    public function clientError(): bool
    {
        return $this->status >= 400 && $this->status < 500;
    }

    /**
     * Determine if the response was a server error (5xx).
     */
    public function serverError(): bool
    {
        return $this->status >= 500;
    }

    // --- ArrayAccess & JSON ---

    public function offsetExists(mixed $offset): bool
    {
        $json = $this->json();
        return is_array($json) && array_key_exists($offset, $json);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->json((string) $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    public function offsetUnset(mixed $offset): void
    {
    }

    public function jsonSerialize(): mixed
    {
        return $this->json() ?: $this->body;
    }

    public function __toString(): string
    {
        return $this->body;
    }
}
