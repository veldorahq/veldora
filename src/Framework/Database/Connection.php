<?php

declare(strict_types=1);

namespace Veldora\Framework\Database;

use InvalidArgumentException;
use PDO;
use PDOStatement;

class Connection
{
    /**
     * The active PDO connection instance.
     */
    protected ?PDO $pdo = null;

    /**
     * Create a new Connection instance.
     *
     * @param array<string, mixed> $config
     */
    public function __construct(protected array $config)
    {
    }

    /**
     * Get the active PDO instance, establishing connection if not already connected.
     */
    public function getPdo(): PDO
    {
        if ($this->pdo === null) {
            $this->pdo = $this->establishConnection();
        }

        return $this->pdo;
    }

    /**
     * Get the configured database driver name.
     */
    public function getDriverName(): string
    {
        return (string) ($this->config['driver'] ?? 'sqlite');
    }

    /**
     * Get the full database connection configuration.
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Connect to the configured driver.
     */
    protected function establishConnection(): PDO
    {
        $driver = $this->config['driver'] ?? 'sqlite';

        if ($driver === 'sqlite') {
            $database = $this->config['database'] ?? ':memory:';
            return new PDO("sqlite:{$database}", null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }

        if ($driver === 'mysql') {
            $host = $this->config['host'] ?? '127.0.0.1';
            $port = $this->config['port'] ?? 3306;
            $database = $this->config['database'] ?? '';
            $username = $this->config['username'] ?? 'root';
            $password = $this->config['password'] ?? '';
            $charset = $this->config['charset'] ?? 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

            return new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }

        throw new InvalidArgumentException("Unsupported database driver: [{$driver}].");
    }

    /**
     * Execute a prepared SQL statement with the given bindings.
     */
    public function query(string $sql, array $bindings = []): PDOStatement
    {
        $statement = $this->getPdo()->prepare($sql);
        $statement->execute($bindings);

        return $statement;
    }

    /**
     * Get a new QueryBuilder instance targeting a specific table.
     */
    public function table(string $table): QueryBuilder
    {
        return (new QueryBuilder($this))->table($table);
    }

    /**
     * Get the active database driver name.
     */
    public function getDriver(): string
    {
        return $this->config['driver'] ?? 'sqlite';
    }
}
