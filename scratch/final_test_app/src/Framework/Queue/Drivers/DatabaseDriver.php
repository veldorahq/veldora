<?php

declare(strict_types=1);

namespace Veldora\Framework\Queue\Drivers;

use PDO;
use Throwable;
use Veldora\Framework\Database\Connection;
use Veldora\Framework\Queue\DatabaseJobWrapper;
use Veldora\Framework\Queue\JobInterface;
use Veldora\Framework\Queue\QueueDriverInterface;

class DatabaseDriver implements QueueDriverInterface
{
    protected string $table = 'jobs';
    protected string $failedTable = 'failed_jobs';

    /**
     * Create a new DatabaseDriver instance.
     */
    public function __construct(protected ?Connection $connection = null)
    {
    }

    /**
     * Get the PDO connection.
     */
    protected function getPdo(): PDO
    {
        if ($this->connection !== null) {
            return $this->connection->getPdo();
        }

        /** @var Connection $conn */
        $conn = app(Connection::class);
        return $conn->getPdo();
    }

    /**
     * Ensure database queue tables exist.
     */
    public function ensureTablesExist(): void
    {
        $pdo = $this->getPdo();
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS {$this->table} (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    queue TEXT NOT NULL,
                    payload TEXT NOT NULL,
                    attempts INTEGER NOT NULL DEFAULT 0,
                    reserved_at INTEGER DEFAULT NULL,
                    available_at INTEGER NOT NULL,
                    created_at INTEGER NOT NULL
                );
                CREATE INDEX IF NOT EXISTS idx_jobs_queue_available ON {$this->table} (queue, reserved_at, available_at);

                CREATE TABLE IF NOT EXISTS {$this->failedTable} (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    queue TEXT NOT NULL,
                    payload TEXT NOT NULL,
                    exception TEXT NOT NULL,
                    failed_at TEXT NOT NULL
                );
            ");
        } else {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS {$this->table} (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    queue VARCHAR(255) NOT NULL,
                    payload LONGTEXT NOT NULL,
                    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
                    reserved_at INT UNSIGNED DEFAULT NULL,
                    available_at INT UNSIGNED NOT NULL,
                    created_at INT UNSIGNED NOT NULL,
                    INDEX idx_jobs_queue (queue, reserved_at, available_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

                CREATE TABLE IF NOT EXISTS {$this->failedTable} (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    queue VARCHAR(255) NOT NULL,
                    payload LONGTEXT NOT NULL,
                    exception LONGTEXT NOT NULL,
                    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }
    }

    /**
     * Push a new job onto the queue.
     */
    public function push(JobInterface $job, string $queue = 'default', int $delay = 0): mixed
    {
        $this->ensureTablesExist();
        $pdo = $this->getPdo();

        $job->onQueue($queue);
        $payload = serialize($job);
        $now = time();
        $availableAt = $now + $delay;

        $stmt = $pdo->prepare("
            INSERT INTO {$this->table} (queue, payload, attempts, reserved_at, available_at, created_at)
            VALUES (:queue, :payload, 0, NULL, :available_at, :created_at)
        ");

        $stmt->execute([
            ':queue' => $queue,
            ':payload' => $payload,
            ':available_at' => $availableAt,
            ':created_at' => $now,
        ]);

        return (int) $pdo->lastInsertId();
    }

    /**
     * Pop the next available job off the queue.
     */
    public function pop(string $queue = 'default'): ?JobInterface
    {
        $this->ensureTablesExist();
        $pdo = $this->getPdo();
        $now = time();
        $expiration = $now - 90; // jobs reserved more than 90 seconds ago are considered stale

        // Begin transaction to safely reserve a job
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                SELECT * FROM {$this->table}
                WHERE queue = :queue
                  AND (reserved_at IS NULL OR reserved_at <= :expiration)
                  AND available_at <= :now
                ORDER BY id ASC
                LIMIT 1
            ");

            $stmt->execute([
                ':queue' => $queue,
                ':expiration' => $expiration,
                ':now' => $now,
            ]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                $pdo->commit();
                return null;
            }

            // Reserve the job
            $attempts = ((int) $row['attempts']) + 1;
            $updateStmt = $pdo->prepare("
                UPDATE {$this->table}
                SET reserved_at = :reserved_at, attempts = :attempts
                WHERE id = :id
            ");

            $updateStmt->execute([
                ':reserved_at' => $now,
                ':attempts' => $attempts,
                ':id' => $row['id'],
            ]);

            $pdo->commit();

            /** @var JobInterface $unserializedJob */
            $unserializedJob = unserialize($row['payload']);
            
            // Sync attempts
            while ($unserializedJob->attempts() < $attempts) {
                $unserializedJob->incrementAttempts();
            }

            return new DatabaseJobWrapper($unserializedJob, (int) $row['id'], $this, $queue);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Delete a completed job from the queue.
     */
    public function delete(JobInterface $job): void
    {
        $this->ensureTablesExist();
        $pdo = $this->getPdo();

        $id = $job instanceof DatabaseJobWrapper ? $job->getDatabaseId() : null;
        if ($id !== null) {
            $stmt = $pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $stmt->execute([':id' => $id]);
        }
    }

    /**
     * Release a reserved job back onto the queue with optional delay.
     */
    public function release(JobInterface $job, int $delay = 0): void
    {
        $this->ensureTablesExist();
        $pdo = $this->getPdo();

        $id = $job instanceof DatabaseJobWrapper ? $job->getDatabaseId() : null;
        if ($id !== null) {
            $stmt = $pdo->prepare("
                UPDATE {$this->table}
                SET reserved_at = NULL, available_at = :available_at
                WHERE id = :id
            ");
            $stmt->execute([
                ':available_at' => time() + $delay,
                ':id' => $id,
            ]);
        }
    }

    /**
     * Move a job to the failed_jobs table.
     */
    public function logFailed(JobInterface $job, Throwable $exception): void
    {
        $this->ensureTablesExist();
        $pdo = $this->getPdo();

        $id = $job instanceof DatabaseJobWrapper ? $job->getDatabaseId() : null;
        $underlying = $job instanceof DatabaseJobWrapper ? $job->getUnderlyingJob() : $job;
        $queue = $job->getQueue();
        $payload = serialize($underlying);
        $exceptionStr = (string) $exception;

        // Delete from jobs
        if ($id !== null) {
            $delStmt = $pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $delStmt->execute([':id' => $id]);
        }

        // Insert into failed_jobs
        $stmt = $pdo->prepare("
            INSERT INTO {$this->failedTable} (queue, payload, exception, failed_at)
            VALUES (:queue, :payload, :exception, :failed_at)
        ");

        $stmt->execute([
            ':queue' => $queue,
            ':payload' => $payload,
            ':exception' => $exceptionStr,
            ':failed_at' => date('Y-m-d H:i:s'),
        ]);

        $job->failed($exception);
    }

    /**
     * Get the count of pending jobs in the queue.
     */
    public function size(string $queue = 'default'): int
    {
        $this->ensureTablesExist();
        $pdo = $this->getPdo();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$this->table} WHERE queue = :queue");
        $stmt->execute([':queue' => $queue]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Clear all pending jobs in the queue.
     */
    public function clear(string $queue = 'default'): int
    {
        $this->ensureTablesExist();
        $pdo = $this->getPdo();

        $stmt = $pdo->prepare("DELETE FROM {$this->table} WHERE queue = :queue");
        $stmt->execute([':queue' => $queue]);

        return $stmt->rowCount();
    }

    /**
     * Get all failed jobs.
     *
     * @return array<array{id: int, queue: string, payload: string, exception: string, failed_at: string}>
     */
    public function getFailedJobs(): array
    {
        $this->ensureTablesExist();
        $pdo = $this->getPdo();

        $stmt = $pdo->query("SELECT * FROM {$this->failedTable} ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Retry a failed job by ID (or all if 'all').
     */
    public function retryFailed(int|string $id): int
    {
        $this->ensureTablesExist();
        $pdo = $this->getPdo();

        if ($id === 'all') {
            $failed = $this->getFailedJobs();
            $retried = 0;
            foreach ($failed as $row) {
                $job = unserialize($row['payload']);
                if ($job instanceof JobInterface) {
                    $this->push($job, $row['queue']);
                    $pdo->exec("DELETE FROM {$this->failedTable} WHERE id = " . (int) $row['id']);
                    $retried++;
                }
            }
            return $retried;
        }

        $stmt = $pdo->prepare("SELECT * FROM {$this->failedTable} WHERE id = :id");
        $stmt->execute([':id' => (int) $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $job = unserialize($row['payload']);
            if ($job instanceof JobInterface) {
                $this->push($job, $row['queue']);
                $delStmt = $pdo->prepare("DELETE FROM {$this->failedTable} WHERE id = :id");
                $delStmt->execute([':id' => (int) $id]);
                return 1;
            }
        }

        return 0;
    }
}
