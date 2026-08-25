<?php

declare(strict_types=1);

namespace Veldora\Framework\Queue;

use Closure;
use InvalidArgumentException;
use Veldora\Framework\Foundation\Application;
use Veldora\Framework\Queue\Drivers\DatabaseDriver;
use Veldora\Framework\Queue\Drivers\SyncDriver;

class QueueManager
{
    /**
     * The array of resolved queue drivers.
     *
     * @var array<string, QueueDriverInterface>
     */
    protected array $drivers = [];

    /**
     * The custom driver resolvers.
     *
     * @var array<string, Closure>
     */
    protected array $customCreators = [];

    /**
     * Create a new QueueManager instance.
     */
    public function __construct(protected ?Application $app = null)
    {
    }

    /**
     * Get a queue connection driver instance.
     */
    public function connection(?string $name = null): QueueDriverInterface
    {
        $name = $name ?: $this->getDefaultDriver();

        if (!isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->resolve($name);
        }

        return $this->drivers[$name];
    }

    /**
     * Get the driver (alias for connection).
     */
    public function driver(?string $name = null): QueueDriverInterface
    {
        return $this->connection($name);
    }

    /**
     * Push a new job onto the default queue.
     */
    public function push(JobInterface $job, string $queue = 'default', int $delay = 0): mixed
    {
        return $this->connection()->push($job, $queue, $delay);
    }

    /**
     * Push a new job onto the queue after a delay.
     */
    public function later(int $delay, JobInterface $job, string $queue = 'default'): mixed
    {
        return $this->connection()->push($job, $queue, $delay);
    }

    /**
     * Pop the next job off of the queue.
     */
    public function pop(string $queue = 'default'): ?JobInterface
    {
        return $this->connection()->pop($queue);
    }

    /**
     * Get the size of the queue.
     */
    public function size(string $queue = 'default'): int
    {
        return $this->connection()->size($queue);
    }

    /**
     * Get the default queue driver name.
     */
    public function getDefaultDriver(): string
    {
        return config('queue.default', 'sync');
    }

    /**
     * Register a custom driver creator Closure.
     */
    public function extend(string $driver, Closure $callback): static
    {
        $this->customCreators[$driver] = $callback;
        return $this;
    }

    /**
     * Resolve the given driver.
     */
    protected function resolve(string $name): QueueDriverInterface
    {
        if (isset($this->customCreators[$name])) {
            return ($this->customCreators[$name])($this->app);
        }

        return match ($name) {
            'sync' => new SyncDriver(),
            'database' => new DatabaseDriver(),
            default => throw new InvalidArgumentException("Queue driver [{$name}] is not defined."),
        };
    }
}
