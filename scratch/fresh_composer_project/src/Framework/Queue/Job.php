<?php

declare(strict_types=1);

namespace Veldora\Framework\Queue;

use Throwable;

abstract class Job implements JobInterface
{
    use Dispatchable;

    /**
     * Number of times the job has been attempted.
     */
    protected int $attempts = 0;

    /**
     * Maximum number of attempts allowed for this job.
     */
    protected int $maxTries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    protected int $timeout = 60;

    /**
     * The number of seconds to delay the job.
     */
    protected int $delay = 0;

    /**
     * The queue name this job should be sent to.
     */
    protected string $queue = 'default';

    /**
     * Execute the job.
     */
    abstract public function handle(): void;

    /**
     * Get the number of times the job has been attempted.
     */
    public function attempts(): int
    {
        return $this->attempts;
    }

    /**
     * Increment the number of attempts.
     */
    public function incrementAttempts(): void
    {
        $this->attempts++;
    }

    /**
     * Get the maximum number of attempts allowed.
     */
    public function maxTries(): int
    {
        return $this->maxTries;
    }

    /**
     * Get the timeout for the job in seconds.
     */
    public function timeout(): int
    {
        return $this->timeout;
    }

    /**
     * Get the delay in seconds before the job becomes available.
     */
    public function getDelay(): int
    {
        return $this->delay;
    }

    /**
     * Set delay in seconds.
     */
    public function delay(int $seconds): static
    {
        $this->delay = $seconds;
        return $this;
    }

    /**
     * Get the queue name.
     */
    public function getQueue(): string
    {
        return $this->queue;
    }

    /**
     * Set the queue name.
     */
    public function onQueue(string $queue): static
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception = null): void
    {
        // Hook for subclasses to clean up on failure
    }
}
