<?php

declare(strict_types=1);

namespace Veldora\Framework\Queue;

use Throwable;

interface JobInterface
{
    /**
     * Execute the job.
     */
    public function handle(): void;

    /**
     * Get the number of times the job has been attempted.
     */
    public function attempts(): int;

    /**
     * Increment the number of attempts.
     */
    public function incrementAttempts(): void;

    /**
     * Get the maximum number of attempts allowed.
     */
    public function maxTries(): int;

    /**
     * Get the timeout for the job in seconds.
     */
    public function timeout(): int;

    /**
     * Get the delay in seconds before the job becomes available.
     */
    public function getDelay(): int;

    /**
     * Set delay in seconds.
     */
    public function delay(int $seconds): static;

    /**
     * Get the queue name.
     */
    public function getQueue(): string;

    /**
     * Set the queue name.
     */
    public function onQueue(string $queue): static;

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception = null): void;
}
