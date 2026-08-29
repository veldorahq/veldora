<?php

declare(strict_types=1);

namespace Veldora\Framework\Http\Resources;

use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;
use Veldora\Framework\Database\Paginator;
use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;

/**
 * @implements IteratorAggregate<int, JsonResource>
 */
class ResourceCollection implements IteratorAggregate, Countable, JsonSerializable
{
    /**
     * The resource class to map collection items with.
     *
     * @var class-string<JsonResource>
     */
    protected string $collects;

    /**
     * Additional metadata to accompany the collection.
     *
     * @var array<string, mixed>
     */
    protected array $additional = [];

    /**
     * @param mixed $resource Array or Paginator
     * @param class-string<JsonResource> $collects
     */
    public function __construct(public mixed $resource, string $collects = JsonResource::class)
    {
        $this->collects = $collects;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array<mixed>
     */
    public function toArray(?Request $request = null): array
    {
        $items = $this->resource instanceof Paginator ? $this->resource->items() : (array) $this->resource;

        return array_map(function ($item) use ($request) {
            $instance = new $this->collects($item);
            return $instance->toArray($request);
        }, $items);
    }

    /**
     * Add additional metadata to the collection response.
     *
     * @param array<string, mixed> $data
     */
    public function additional(array $data): static
    {
        $this->additional = array_merge($this->additional, $data);
        return $this;
    }

    /**
     * Resolve the resource collection to an array with pagination metadata if present.
     *
     * @return array<string, mixed>
     */
    public function resolve(?Request $request = null): array
    {
        $data = ['data' => $this->toArray($request)];

        if ($this->resource instanceof Paginator) {
            $data['links'] = [
                'first' => $this->resource->url(1),
                'last' => $this->resource->url($this->resource->lastPage()),
                'prev' => $this->resource->previousPageUrl(),
                'next' => $this->resource->nextPageUrl(),
            ];

            $data['meta'] = [
                'current_page' => $this->resource->currentPage(),
                'from' => (($this->resource->currentPage() - 1) * $this->resource->perPage()) + 1,
                'last_page' => $this->resource->lastPage(),
                'path' => $this->resource->url($this->resource->currentPage()),
                'per_page' => $this->resource->perPage(),
                'to' => min($this->resource->currentPage() * $this->resource->perPage(), $this->resource->total()),
                'total' => $this->resource->total(),
            ];
        }

        if (!empty($this->additional)) {
            $data = array_merge($data, $this->additional);
        }

        return $data;
    }

    /**
     * Create an HTTP response for the collection.
     */
    public function toResponse(?Request $request = null, int $status = 200): Response
    {
        $data = $this->resolve($request);
        return new Response(json_encode($data), $status, ['Content-Type' => 'application/json']);
    }

    public function getIterator(): Traversable
    {
        $items = $this->resource instanceof Paginator ? $this->resource->items() : (array) $this->resource;
        $mapped = array_map(fn ($item) => new $this->collects($item), $items);

        return new \ArrayIterator($mapped);
    }

    public function count(): int
    {
        return count($this->resource instanceof Paginator ? $this->resource->items() : (array) $this->resource);
    }

    public function jsonSerialize(): mixed
    {
        return $this->resolve();
    }
}
