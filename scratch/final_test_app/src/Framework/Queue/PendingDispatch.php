<?php

declare(strict_types=1);

namespace Veldora\Framework\Queue;

class PendingDispatch
{
    /**
     * Create a new PendingDispatch instance.
     */
    public function __construct(protected JobInterface $job)
    {
    }

    /**
     * Set the desired queue for the job.
     */
    public function onQueue(string $queue): static
    {
        $this->job->onQueue($queue);
        return $this;
    }

    /**
     * Set the desired delay for the job in seconds.
     */
    public function delay(int $seconds): static
    {
        $this->job->delay($seconds);
        return $this;
    }

    /**
     * Handle the destruction of the pending dispatch by pushing to the queue.
     */
    public function __destruct()
    {
        /** @var QueueManager $queueManager */
        $queueManager = app(QueueManager::class);
        $queueManager->push($this->job, $this->job->getQueue(), $this->job->getDelay());
    }
}
