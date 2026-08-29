<?php

declare(strict_types=1);

namespace Veldora\Framework\Session;

interface SessionDriverInterface
{
    /**
     * Read the session data by ID.
     *
     * @return array<string, mixed>
     */
    public function read(string $id): array;

    /**
     * Write session data by ID.
     *
     * @param array<string, mixed> $data
     */
    public function write(string $id, array $data): void;

    /**
     * Destroy the session by ID.
     */
    public function destroy(string $id): void;
}
