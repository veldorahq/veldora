<?php

declare(strict_types=1);

namespace Veldora\Framework\Database\Schema;

use PDO;
use Veldora\Framework\Database\Connection;

class Migrator
{
    /**
     * Create a new Migrator instance.
     */
    public function __construct(protected Connection $connection)
    {
    }

    /**
     * Run any pending migrations in the specified directory.
     *
     * @return array<string> The run migrations.
     */
    public function run(string $migrationDir): array
    {
        $this->ensureMigrationTableExists();

        $completed = $this->getCompletedMigrations();
        $files = $this->getMigrationFiles($migrationDir);

        $pending = array_diff(array_keys($files), $completed);

        if (empty($pending)) {
            return [];
        }

        sort($pending);

        $batch = $this->getNextBatchNumber();
        $ran = [];

        foreach ($pending as $migrationName) {
            $file = $files[$migrationName];
            require_once $file;

            $className = $this->resolveClassName($migrationName);
            
            if (class_exists($className)) {
                /** @var Migration $migration */
                $migration = new $className();
                $migration->up();

                $this->logMigration($migrationName, $batch);
                $ran[] = $migrationName;
            }
        }

        return $ran;
    }

    /**
     * Rollback the last migration batch.
     *
     * @return array<string> The rolled-back migrations.
     */
    public function rollback(string $migrationDir): array
    {
        $this->ensureMigrationTableExists();

        $lastBatch = $this->getLastBatch();
        if (empty($lastBatch)) {
            return [];
        }

        $files = $this->getMigrationFiles($migrationDir);
        $rolledBack = [];

        foreach ($lastBatch as $migrationRecord) {
            $migrationName = $migrationRecord['migration'];
            if (isset($files[$migrationName])) {
                require_once $files[$migrationName];

                $className = $this->resolveClassName($migrationName);
                if (class_exists($className)) {
                    /** @var Migration $migration */
                    $migration = new $className();
                    $migration->down();

                    $this->deleteMigrationRecord($migrationName);
                    $rolledBack[] = $migrationName;
                }
            }
        }

        return $rolledBack;
    }

    /**
     * Drop all tables in the database (Fresh setup).
     */
    public function fresh(): void
    {
        $pdo = $this->connection->getPdo();
        $driver = $this->connection->getDriver();

        if ($driver === 'sqlite') {
            $tablesQuery = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence';");
            if ($tablesQuery !== false) {
                $tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);
                foreach ($tables as $table) {
                    $pdo->exec("DROP TABLE IF EXISTS `{$table}`;");
                }
            }
        } else {
            // mysql
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0;');
            $tablesQuery = $pdo->query('SHOW TABLES;');
            if ($tablesQuery !== false) {
                $tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);
                foreach ($tables as $table) {
                    $pdo->exec("DROP TABLE IF EXISTS `{$table}`;");
                }
            }
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1;');
        }
    }

    /**
     * Extract class name from migration filename.
     */
    protected function resolveClassName(string $migrationName): string
    {
        $name = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $migrationName) ?: $migrationName;
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }

    /**
     * Create the migration tracking table if it does not exist.
     */
    protected function ensureMigrationTableExists(): void
    {
        $driver = $this->connection->getDriver();
        $pdo = $this->connection->getPdo();

        if ($driver === 'sqlite') {
            $sql = "CREATE TABLE IF NOT EXISTS `migrations` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `migration` VARCHAR(255) NOT NULL,
                `batch` INTEGER NOT NULL
            );";
        } else {
            // mysql
            $sql = "CREATE TABLE IF NOT EXISTS `migrations` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `migration` VARCHAR(255) NOT NULL,
                `batch` INT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        }

        $pdo->exec($sql);
    }

    /**
     * Get list of run migrations from the DB.
     *
     * @return array<string>
     */
    protected function getCompletedMigrations(): array
    {
        $pdo = $this->connection->getPdo();
        $stmt = $pdo->query('SELECT `migration` FROM `migrations`;');
        if ($stmt === false) {
            return [];
        }
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Scan migration directory.
     *
     * @return array<string, string> Filename to absolute path mapping.
     */
    protected function getMigrationFiles(string $migrationDir): array
    {
        if (!is_dir($migrationDir)) {
            return [];
        }

        $files = glob($migrationDir . '/*.php') ?: [];
        $migrationFiles = [];

        foreach ($files as $file) {
            $migrationFiles[basename($file, '.php')] = $file;
        }

        return $migrationFiles;
    }

    /**
     * Get next batch number.
     */
    protected function getNextBatchNumber(): int
    {
        $pdo = $this->connection->getPdo();
        $stmt = $pdo->query('SELECT MAX(`batch`) FROM `migrations`;');
        if ($stmt === false) {
            return 1;
        }
        $max = $stmt->fetchColumn();
        return $max !== null ? ((int) $max) + 1 : 1;
    }

    /**
     * Log migration execution.
     */
    protected function logMigration(string $migration, int $batch): void
    {
        $pdo = $this->connection->getPdo();
        $stmt = $pdo->prepare('INSERT INTO `migrations` (`migration`, `batch`) VALUES (?, ?);');
        $stmt->execute([$migration, $batch]);
    }

    /**
     * Get migrations of the last batch.
     *
     * @return array<array{migration: string, batch: int}>
     */
    protected function getLastBatch(): array
    {
        $pdo = $this->connection->getPdo();
        $stmt = $pdo->query('SELECT `migration`, `batch` FROM `migrations` WHERE `batch` = (SELECT MAX(`batch`) FROM `migrations`) ORDER BY `id` DESC;');
        if ($stmt === false) {
            return [];
        }
        /** @var array<array{migration: string, batch: int}> $results */
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    /**
     * Delete migration log record.
     */
    protected function deleteMigrationRecord(string $migration): void
    {
        $pdo = $this->connection->getPdo();
        $stmt = $pdo->prepare('DELETE FROM `migrations` WHERE `migration` = ?;');
        $stmt->execute([$migration]);
    }
}
