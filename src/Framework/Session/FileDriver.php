<?php

declare(strict_types=1);

namespace Veldora\Framework\Session;

class FileDriver implements SessionDriverInterface
{
    /**
     * Create a new FileDriver instance.
     */
    public function __construct(protected string $path)
    {
    }

    /**
     * Read the session data by ID.
     *
     * @return array<string, mixed>
     */
    public function read(string $id): array
    {
        $file = $this->path . '/' . $id;

        if (file_exists($file)) {
            $content = file_get_contents($file);
            if ($content !== false && $content !== '') {
                $decoded = unserialize($content);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        return [];
    }

    /**
     * Write session data by ID.
     *
     * @param array<string, mixed> $data
     */
    public function write(string $id, array $data): void
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }

        $file = $this->path . '/' . $id;
        file_put_contents($file, serialize($data));
    }

    /**
     * Destroy the session by ID.
     */
    public function destroy(string $id): void
    {
        $file = $this->path . '/' . $id;
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
