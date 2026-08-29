<?php

declare(strict_types=1);

namespace Veldora\Framework\Cache\Drivers;

use Veldora\Framework\Cache\CacheDriverInterface;

class FileDriver implements CacheDriverInterface
{
    public function __construct(protected string $directory)
    {
        $this->ensureDirectoryExists($this->directory);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $path = $this->path($key);

        if (!file_exists($path)) {
            return $default;
        }

        $content = @file_get_contents($path);
        if ($content === false || strlen($content) < 10) {
            return $default;
        }

        $expire = (int) substr($content, 0, 10);

        if ($expire !== 0 && time() >= $expire) {
            $this->forget($key);
            return $default;
        }

        $serialized = substr($content, 10);
        $value = @unserialize($serialized);

        return $value !== false || $serialized === serialize(false) ? $value : $default;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $path = $this->path($key);
        $this->ensureDirectoryExists(dirname($path));

        $expire = $ttl !== null ? time() + $ttl : 0;
        $expireHeader = sprintf('%010d', $expire);
        $payload = $expireHeader . serialize($value);

        return @file_put_contents($path, $payload, LOCK_EX) !== false;
    }

    public function forever(string $key, mixed $value): bool
    {
        return $this->set($key, $value, null);
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function forget(string $key): bool
    {
        $path = $this->path($key);

        if (file_exists($path)) {
            return @unlink($path);
        }

        return false;
    }

    public function flush(): bool
    {
        return $this->deleteDirectoryContents($this->directory);
    }

    public function remember(string $key, ?int $ttl, callable $callback): mixed
    {
        $val = $this->get($key);

        if ($val !== null) {
            return $val;
        }

        $val = $callback();
        $this->set($key, $val, $ttl);

        return $val;
    }

    public function increment(string $key, int $value = 1): int|bool
    {
        $current = (int) ($this->get($key, 0));
        $new = $current + $value;
        $this->forever($key, $new);

        return $new;
    }

    public function decrement(string $key, int $value = 1): int|bool
    {
        return $this->increment($key, -$value);
    }

    /**
     * Get the full path for the given cache key.
     */
    protected function path(string $key): string
    {
        $hash = sha1($key);
        $parts = array_slice(str_split($hash, 2), 0, 2);

        return rtrim($this->directory, '/\\') . '/' . implode('/', $parts) . '/' . $hash;
    }

    protected function ensureDirectoryExists(string $dir): void
    {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    protected function deleteDirectoryContents(string $dir): bool
    {
        if (!is_dir($dir)) {
            return true;
        }

        $items = scandir($dir);
        if ($items === false) {
            return false;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;

            if (is_dir($path)) {
                $this->deleteDirectoryContents($path);
                @rmdir($path);
            } else {
                @unlink($path);
            }
        }

        return true;
    }
}
