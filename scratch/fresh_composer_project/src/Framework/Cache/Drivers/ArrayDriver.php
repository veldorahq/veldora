<?php

declare(strict_types=1);

namespace Veldora\Framework\Cache\Drivers;

use Veldora\Framework\Cache\CacheDriverInterface;

class ArrayDriver implements CacheDriverInterface
{
    /**
     * The array of stored values.
     *
     * @var array<string, array{value: mixed, expires_at: ?int}>
     */
    protected array $storage = [];

    public function get(string $key, mixed $default = null): mixed
    {
        if (!isset($this->storage[$key])) {
            return $default;
        }

        $item = $this->storage[$key];

        if ($item['expires_at'] !== null && time() >= $item['expires_at']) {
            $this->forget($key);
            return $default;
        }

        return $item['value'];
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $expiresAt = $ttl !== null ? time() + $ttl : null;

        $this->storage[$key] = [
            'value' => $value,
            'expires_at' => $expiresAt,
        ];

        return true;
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
        if (isset($this->storage[$key])) {
            unset($this->storage[$key]);
            return true;
        }

        return false;
    }

    public function flush(): bool
    {
        $this->storage = [];
        return true;
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
}
