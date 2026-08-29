<?php

declare(strict_types=1);

namespace Veldora\Framework\Queue;

use Throwable;

class Worker
{
    /**
     * Indicates if the worker should stop.
     */
    protected bool $shouldQuit = false;

    /**
     * Create a new Worker instance.
     */
    public function __construct(protected QueueManager $manager)
    {
    }

    /**
     * Process the next job on the queue.
     *
     * @return bool True if a job was processed, false if queue was empty
     */
    public function processNextJob(string $connection = 'default', string $queue = 'default', int $delay = 0): bool
    {
        $driver = $this->manager->connection($connection);
        $job = $driver->pop($queue);

        if ($job === null) {
            return false;
        }

        $this->process($driver, $job, $delay);
        return true;
    }

    /**
     * Process a single job.
     */
    public function process(QueueDriverInterface $driver, JobInterface $job, int $backoffDelay = 5): void
    {
        try {
            $job->handle();
            $driver->delete($job);
        } catch (Throwable $e) {
            $this->handleJobException($driver, $job, $e, $backoffDelay);
        }
    }

    /**
     * Handle an exception that occurred while processing a job.
     */
    protected function handleJobException(
        QueueDriverInterface $driver,
        JobInterface $job,
        Throwable $e,
        int $backoffDelay = 5
    ): void {
        if ($job->attempts() >= $job->maxTries()) {
            $driver->logFailed($job, $e);
        } else {
            $driver->release($job, $backoffDelay);
        }
    }

    /**
     * Run the worker loop.
     *
     * @param array{connection?: string, queue?: string, sleep?: int, maxJobs?: int, stopWhenEmpty?: bool} $options
     */
    public function daemon(array $options = []): int
    {
        $connection = $options['connection'] ?? 'default';
        $queue = $options['queue'] ?? 'default';
        $sleep = $options['sleep'] ?? 3;
        $maxJobs = $options['maxJobs'] ?? 0;
        $stopWhenEmpty = $options['stopWhenEmpty'] ?? false;

        $jobsProcessed = 0;

        while (!$this->shouldQuit) {
            $processed = $this->processNextJob($connection, $queue);

            if ($processed) {
                $jobsProcessed++;

                if ($maxJobs > 0 && $jobsProcessed >= $maxJobs) {
                    break;
                }
            } else {
                if ($stopWhenEmpty) {
                    break;
                }

                sleep($sleep);
            }
        }

        return $jobsProcessed;
    }

    /**
     * Stop the worker loop.
     */
    public function stop(): void
    {
        $this->shouldQuit = true;
    }
}
