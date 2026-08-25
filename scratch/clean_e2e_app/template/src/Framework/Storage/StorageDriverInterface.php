<?php

declare(strict_types=1);

namespace Veldora\Framework\Storage;

interface StorageDriverInterface
{
    /**
     * Determine if a file exists.
     */
    public function exists(string $path): bool;

    /**
     * Determine if a file is missing.
     */
    public function missing(string $path): bool;

    /**
     * Get the contents of a file.
     */
    public function get(string $path): ?string;

    /**
     * Write the contents of a file.
     */
    public function put(string $path, string $contents): bool;

    /**
     * Prepend to a file.
     */
    public function prepend(string $path, string $data): bool;

    /**
     * Append to a file.
     */
    public function append(string $path, string $data): bool;

    /**
     * Delete the file at a given path.
     *
     * @param string|array<string> $paths
     */
    public function delete(string|array $paths): bool;

    /**
     * Copy a file to a new location.
     */
    public function copy(string $from, string $to): bool;

    /**
     * Move a file to a new location.
     */
    public function move(string $from, string $to): bool;

    /**
     * Get the file size of a given file in bytes.
     */
    public function size(string $path): int;

    /**
     * Get the file's last modification time.
     */
    public function lastModified(string $path): int;

    /**
     * Get the URL for the file at the given path.
     */
    public function url(string $path): string;

    /**
     * Get an array of all files in a directory.
     *
     * @return array<string>
     */
    public function files(string $directory = '', bool $recursive = false): array;

    /**
     * Get all of the directories within a given directory.
     *
     * @return array<string>
     */
    public function directories(string $directory = '', bool $recursive = false): array;

    /**
     * Create a directory.
     */
    public function makeDirectory(string $path): bool;

    /**
     * Recursively delete a directory.
     */
    public function deleteDirectory(string $directory): bool;
}
