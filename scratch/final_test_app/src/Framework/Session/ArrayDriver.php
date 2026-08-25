<?php

declare(strict_types=1);

namespace Veldora\Framework\Session;

class ArrayDriver implements SessionDriverInterface
{
    /**
     * The in-memory session items store.
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $storage = [];

    /**
     * Read the session data by ID.
     *
     * @return array<string, mixed>
     */
    public function read(string $id): array
    {
        return $this->storage[$id] ?? [];
    }

    /**
     * Write session data by ID.
     *
     * @param array<string, mixed> $data
     */
    public function write(string $id, array $data): void
    {
        $this->storage[$id] = $data;
    }

    /**
     * Destroy the session by ID.
     */
    public function destroy(string $id): void
    {
        unset($this->storage[$id]);
    }
}
