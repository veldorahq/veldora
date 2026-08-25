<?php

declare(strict_types=1);

namespace Veldora\Framework\Queue\Drivers;

use Throwable;
use Veldora\Framework\Queue\JobInterface;
use Veldora\Framework\Queue\QueueDriverInterface;

class SyncDriver implements QueueDriverInterface
{
    /**
     * Push a new job onto the queue and execute it immediately.
     */
    public function push(JobInterface $job, string $queue = 'default', int $delay = 0): mixed
    {
        $job->onQueue($queue);
        $job->incrementAttempts();

        try {
            $job->handle();
        } catch (Throwable $e) {
            $job->failed($e);
            throw $e;
        }

        return null;
    }

    /**
     * Pop is not applicable for sync driver as jobs run synchronously.
     */
    public function pop(string $queue = 'default'): ?JobInterface
    {
        return null;
    }

    public function delete(JobInterface $job): void
    {
    }

    public function release(JobInterface $job, int $delay = 0): void
    {
    }

    public function logFailed(JobInterface $job, Throwable $exception): void
    {
    }

    public function size(string $queue = 'default'): int
    {
        return 0;
    }

    public function clear(string $queue = 'default'): int
    {
        return 0;
    }
}
