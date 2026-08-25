<?php

declare(strict_types=1);

namespace Veldora\Framework\Database\Schema;

use Veldora\Framework\Database\Connection;
use Veldora\Framework\Foundation\Application;

class Schema
{
    /**
     * The active connection instance.
     */
    protected static ?Connection $connection = null;

    /**
     * Set the connection instance manually.
     */
    public static function setConnection(Connection $connection): void
    {
        self::$connection = $connection;
    }

    /**
     * Resolve the connection instance from Application kernel.
     */
    protected static function getConnection(): Connection
    {
        if (self::$connection === null) {
            self::$connection = Application::getInstance()->get(Connection::class);
        }

        return self::$connection;
    }

    /**
     * Create a new table on the schema.
     */
    public static function create(string $table, \Closure $callback): void
    {
        $connection = self::getConnection();
        $blueprint = new Blueprint($table);
        
        $callback($blueprint);

        $sql = $blueprint->toSql($connection->getDriver());
        $connection->getPdo()->exec($sql);
    }

    /**
     * Drop a table from the schema if it exists.
     */
    public static function dropIfExists(string $table): void
    {
        $connection = self::getConnection();
        $sql = "DROP TABLE IF EXISTS `{$table}`;";
        $connection->getPdo()->exec($sql);
    }
}
