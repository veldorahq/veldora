<?php

declare(strict_types=1);

namespace Veldora\Framework\Database;

use Closure;
use PDO;
use Throwable;
use Veldora\Framework\Foundation\Application;

/**
 * Class DB
 *
 * Facade providing static access to the database connection, query builder,
 * raw SQL execution, and database transactions.
 */
final class DB
{
    /**
     * Get the default database connection instance.
     */
    public static function connection(): Connection
    {
        return Application::getInstance()->get(Connection::class);
    }

    /**
     * Get the underlying PDO instance.
     */
    public static function getPdo(): PDO
    {
        return self::connection()->getPdo();
    }

    /**
     * Begin a new QueryBuilder instance for a specific table.
     */
    public static function table(string $table): QueryBuilder
    {
        return (new QueryBuilder(self::connection()))->table($table);
    }

    /**
     * Execute a database transaction.
     * Automatically commits on success and rolls back on exception.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     * @throws Throwable
     */
    public static function transaction(callable $callback): mixed
    {
        self::beginTransaction();

        try {
            $result = $callback();
            self::commit();
            return $result;
        } catch (Throwable $e) {
            self::rollBack();
            throw $e;
        }
    }

    /**
     * Start a new database transaction.
     */
    public static function beginTransaction(): bool
    {
        $pdo = self::getPdo();
        if (!$pdo->inTransaction()) {
            return $pdo->beginTransaction();
        }
        return true;
    }

    /**
     * Commit the active database transaction.
     */
    public static function commit(): bool
    {
        $pdo = self::getPdo();
        if ($pdo->inTransaction()) {
            return $pdo->commit();
        }
        return true;
    }

    /**
     * Rollback the active database transaction.
     */
    public static function rollBack(): bool
    {
        $pdo = self::getPdo();
        if ($pdo->inTransaction()) {
            return $pdo->rollBack();
        }
        return true;
    }

    /**
     * Execute a raw SELECT query with parameter bindings.
     *
     * @param array<mixed> $bindings
     * @return array<array<string, mixed>>
     */
    public static function select(string $query, array $bindings = []): array
    {
        $stmt = self::getPdo()->prepare($query);
        $stmt->execute($bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Execute a raw INSERT statement.
     *
     * @param array<mixed> $bindings
     */
    public static function insert(string $query, array $bindings = []): bool
    {
        $stmt = self::getPdo()->prepare($query);
        return $stmt->execute($bindings);
    }

    /**
     * Execute a raw UPDATE statement and return the number of affected rows.
     *
     * @param array<mixed> $bindings
     */
    public static function update(string $query, array $bindings = []): int
    {
        $stmt = self::getPdo()->prepare($query);
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }

    /**
     * Execute a raw DELETE statement and return the number of affected rows.
     *
     * @param array<mixed> $bindings
     */
    public static function delete(string $query, array $bindings = []): int
    {
        $stmt = self::getPdo()->prepare($query);
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }

    /**
     * Execute a general raw SQL statement (DDL/DML).
     *
     * @param array<mixed> $bindings
     */
    public static function statement(string $query, array $bindings = []): bool
    {
        $stmt = self::getPdo()->prepare($query);
        return $stmt->execute($bindings);
    }

    /**
     * Execute a raw SELECT query and return the first row.
     *
     * @param array<mixed> $bindings
     * @return array<string, mixed>|null
     */
    public static function selectOne(string $query, array $bindings = []): ?array
    {
        $rows = self::select($query, $bindings);
        return $rows[0] ?? null;
    }

    /**
     * Allow instance calls via db() helper to forward to static methods.
     *
     * @param string $name
     * @param array<mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        return forward_static_call_array([self::class, $name], $arguments);
    }
}
