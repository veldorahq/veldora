<?php

declare(strict_types=1);

namespace Veldora\Framework\Queue;

interface QueueDriverInterface
{
    /**
     * Push a new job onto the queue.
     */
    public function push(JobInterface $job, string $queue = 'default', int $delay = 0): mixed;

    /**
     * Pop the next job off of the queue.
     */
    public function pop(string $queue = 'default'): ?JobInterface;

    /**
     * Acknowledge that a job was completed successfully and delete it.
     */
    public function delete(JobInterface $job): void;

    /**
     * Release a failed or reserved job back onto the queue.
     */
    public function release(JobInterface $job, int $delay = 0): void;

    /**
     * Log a failed job.
     */
    public function logFailed(JobInterface $job, \Throwable $exception): void;

    /**
     * Get the size of the queue.
     */
    public function size(string $queue = 'default'): int;

    /**
     * Clear all jobs from the queue.
     */
    public function clear(string $queue = 'default'): int;
}
