<?php

declare(strict_types=1);

namespace Veldora\Framework\Queue;

use Throwable;

class DatabaseJobWrapper implements JobInterface
{
    /**
     * Create a new DatabaseJobWrapper instance.
     */
    public function __construct(
        protected JobInterface $job,
        protected int|string $databaseId,
        protected QueueDriverInterface $driver,
        protected string $queueName = 'default'
    ) {
    }

    public function handle(): void
    {
        $this->job->handle();
    }

    public function attempts(): int
    {
        return $this->job->attempts();
    }

    public function incrementAttempts(): void
    {
        $this->job->incrementAttempts();
    }

    public function maxTries(): int
    {
        return $this->job->maxTries();
    }

    public function timeout(): int
    {
        return $this->job->timeout();
    }

    public function getDelay(): int
    {
        return $this->job->getDelay();
    }

    public function delay(int $seconds): static
    {
        $this->job->delay($seconds);
        return $this;
    }

    public function getQueue(): string
    {
        return $this->queueName;
    }

    public function onQueue(string $queue): static
    {
        $this->queueName = $queue;
        $this->job->onQueue($queue);
        return $this;
    }

    public function failed(?Throwable $exception = null): void
    {
        $this->job->failed($exception);
    }

    public function getDatabaseId(): int|string
    {
        return $this->databaseId;
    }

    public function getUnderlyingJob(): JobInterface
    {
        return $this->job;
    }
}
