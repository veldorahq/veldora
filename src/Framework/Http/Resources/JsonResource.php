<?php

declare(strict_types=1);

namespace Veldora\Framework\Http\Resources;

use ArrayAccess;
use JsonSerializable;
use Veldora\Framework\Database\Model;
use Veldora\Framework\Database\Paginator;
use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;

/**
 * @template T
 * @implements ArrayAccess<string, mixed>
 */
class JsonResource implements ArrayAccess, JsonSerializable
{
    /**
     * The wrapper that should be applied.
     */
    public static ?string $wrap = 'data';

    /**
     * Additional metadata to accompany the resource.
     *
     * @var array<string, mixed>
     */
    protected array $additional = [];

    /**
     * @param T $resource
     */
    public function __construct(public mixed $resource)
    {
    }

    /**
     * Create a new anonymous resource collection.
     */
    public static function collection(mixed $resource): ResourceCollection
    {
        return new ResourceCollection($resource, static::class);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(?Request $request = null): array
    {
        if ($this->resource === null) {
            return [];
        }

        if (is_array($this->resource)) {
            return $this->resource;
        }

        if ($this->resource instanceof Model) {
            return $this->resource->toArray();
        }

        return (array) $this->resource;
    }

    /**
     * Add additional metadata to the resource response.
     *
     * @param array<string, mixed> $data
     */
    public function additional(array $data): static
    {
        $this->additional = array_merge($this->additional, $data);
        return $this;
    }

    /**
     * Resolve the resource to an array including wrap key and additional data.
     *
     * @return array<string, mixed>
     */
    public function resolve(?Request $request = null): array
    {
        $data = $this->toArray($request);

        if (static::$wrap !== null) {
            $data = [static::$wrap => $data];
        }

        if (!empty($this->additional)) {
            $data = array_merge($data, $this->additional);
        }

        return $data;
    }

    /**
     * Create an HTTP response that represents the resource.
     */
    public function toResponse(?Request $request = null, int $status = 200): Response
    {
        $data = $this->resolve($request);
        return new Response(json_encode($data), $status, ['Content-Type' => 'application/json']);
    }

    // --- Dynamic Property Proxying ---

    public function __get(string $key): mixed
    {
        if ($this->resource instanceof Model) {
            return $this->resource->getAttribute($key);
        }

        if (is_array($this->resource)) {
            return $this->resource[$key] ?? null;
        }

        if (is_object($this->resource)) {
            return $this->resource->$key ?? null;
        }

        return null;
    }

    public function offsetExists(mixed $offset): bool
    {
        if ($this->resource instanceof ArrayAccess || is_array($this->resource)) {
            return isset($this->resource[$offset]);
        }

        return isset($this->resource->$offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get((string) $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    public function offsetUnset(mixed $offset): void
    {
    }

    public function jsonSerialize(): mixed
    {
        return $this->resolve();
    }
}
