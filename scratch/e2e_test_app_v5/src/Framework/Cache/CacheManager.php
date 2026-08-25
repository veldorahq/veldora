<?php

declare(strict_types=1);

namespace Veldora\Framework\Cache;

use InvalidArgumentException;
use Veldora\Framework\Cache\Drivers\ArrayDriver;
use Veldora\Framework\Cache\Drivers\FileDriver;
use Veldora\Framework\Foundation\Application;

class CacheManager
{
    /**
     * The array of resolved cache stores.
     *
     * @var array<string, CacheDriverInterface>
     */
    protected array $stores = [];

    /**
     * The registered custom driver creators.
     *
     * @var array<string, callable>
     */
    protected array $customCreators = [];

    /**
     * The default cache store name.
     */
    protected ?string $defaultStore = null;

    public function __construct(protected Application $app)
    {
    }

    /**
     * Get a cache store instance by name.
     */
    public function store(?string $name = null): CacheDriverInterface
    {
        $name = $name ?: $this->getDefaultDriver();

        if (!isset($this->stores[$name])) {
            $this->stores[$name] = $this->resolve($name);
        }

        return $this->stores[$name];
    }

    /**
     * Resolve the given store.
     */
    protected function resolve(string $name): CacheDriverInterface
    {
        if (isset($this->customCreators[$name])) {
            return ($this->customCreators[$name])($this->app);
        }

        $config = $this->getConfig($name);
        $driver = $config['driver'] ?? $name;

        return match ($driver) {
            'array' => new ArrayDriver(),
            'file' => new FileDriver($config['path'] ?? $this->app->basePath('storage/framework/cache/data')),
            default => throw new InvalidArgumentException("Cache driver [{$driver}] is not supported."),
        };
    }

    /**
     * Register a custom driver creator Closure.
     */
    public function extend(string $driver, callable $callback): static
    {
        $this->customCreators[$driver] = $callback;
        return $this;
    }

    /**
     * Manually set a store instance.
     */
    public function setDriver(string $name, CacheDriverInterface $driver): static
    {
        $this->stores[$name] = $driver;
        return $this;
    }

    /**
     * Set the default cache driver name.
     */
    public function setDefaultDriver(string $name): static
    {
        $this->defaultStore = $name;
        return $this;
    }

    /**
     * Get the default cache driver name.
     */
    public function getDefaultDriver(): string
    {
        if ($this->defaultStore !== null) {
            return $this->defaultStore;
        }

        if (function_exists('config')) {
            return (string) config('cache.default', 'file');
        }

        return 'file';
    }

    /**
     * Get the cache store configuration.
     *
     * @return array<string, mixed>
     */
    protected function getConfig(string $name): array
    {
        if (function_exists('config')) {
            return (array) config("cache.stores.{$name}", ['driver' => $name]);
        }

        return ['driver' => $name];
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param array<mixed> $parameters
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->store()->$method(...$parameters);
    }
}
