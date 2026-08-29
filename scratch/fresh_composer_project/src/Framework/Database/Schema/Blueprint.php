<?php

declare(strict_types=1);

namespace Veldora\Framework\Database\Schema;

class Blueprint
{
    /**
     * The column definitions.
     *
     * @var array<array{name: string, type: string, length?: int, nullable: bool, default: mixed, auto_increment: bool, primary: bool}>
     */
    protected array $columns = [];

    /**
     * Create a new Blueprint instance.
     */
    public function __construct(protected string $table)
    {
    }

    /**
     * Add auto-increment primary key ID.
     */
    public function id(string $name = 'id'): self
    {
        $this->columns[] = [
            'name' => $name,
            'type' => 'id',
            'nullable' => false,
            'default' => null,
            'auto_increment' => true,
            'primary' => true,
        ];
        return $this;
    }

    /**
     * Add a VARCHAR column.
     */
    public function string(string $name, int $length = 255): self
    {
        $this->columns[] = [
            'name' => $name,
            'type' => 'string',
            'length' => $length,
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ];
        return $this;
    }

    /**
     * Add a TEXT column.
     */
    public function text(string $name): self
    {
        $this->columns[] = [
            'name' => $name,
            'type' => 'text',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ];
        return $this;
    }

    /**
     * Add an INTEGER column.
     */
    public function integer(string $name): self
    {
        $this->columns[] = [
            'name' => $name,
            'type' => 'integer',
            'nullable' => false,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ];
        return $this;
    }

    /**
     * Add a TIMESTAMP column.
     */
    public function timestamp(string $name, bool $nullable = true): self
    {
        $this->columns[] = [
            'name' => $name,
            'type' => 'timestamp',
            'nullable' => $nullable,
            'default' => null,
            'auto_increment' => false,
            'primary' => false,
        ];
        return $this;
    }

    /**
     * Add standard created_at and updated_at timestamps.
     */
    public function timestamps(): self
    {
        $this->timestamp('created_at');
        $this->timestamp('updated_at');
        return $this;
    }

    /**
     * Add a BOOLEAN column (stored as TINYINT/INTEGER).
     */
    public function boolean(string $name): self
    {
        $this->columns[] = [
            'name'          => $name,
            'type'          => 'boolean',
            'nullable'      => false,
            'default'       => null,
            'auto_increment'=> false,
            'primary'       => false,
        ];
        return $this;
    }

    /**
     * Add a softDeletes deleted_at timestamp.
     */
    public function softDeletes(): self
    {
        $this->timestamp('deleted_at');
        return $this;
    }

    /**
     * Mark the last added column as nullable.
     */
    public function nullable(): self
    {
        $last = array_key_last($this->columns);
        if ($last !== null) {
            $this->columns[$last]['nullable'] = true;
        }
        return $this;
    }

    /**
     * Set a default value on the last added column.
     */
    public function default(mixed $value): self
    {
        $last = array_key_last($this->columns);
        if ($last !== null) {
            $this->columns[$last]['default'] = $value;
        }
        return $this;
    }

    /**
     * Mark the last added column as unique.
     */
    public function unique(): self
    {
        $last = array_key_last($this->columns);
        if ($last !== null) {
            $this->columns[$last]['unique'] = true;
        }
        return $this;
    }

    /**
     * Add a remember_token string column (nullable, 100 chars).
     */
    public function rememberToken(): self
    {
        return $this->string('remember_token', 100)->nullable();
    }

    /**
     * Compile table schema creation to SQL.
     */
    public function toSql(string $driver): string
    {
        $columnStatements = [];
        $uniqueConstraints = [];

        foreach ($this->columns as $column) {
            $columnStatements[] = $this->compileColumn($column, $driver);

            // Collect UNIQUE constraints (SQLite handles inline; MySQL uses separate KEY)
            if (!empty($column['unique']) && $column['type'] !== 'id') {
                $uniqueConstraints[] = 'UNIQUE (`' . $column['name'] . '`)';
            }
        }

        $all = array_merge($columnStatements, $uniqueConstraints);

        return 'CREATE TABLE IF NOT EXISTS `' . $this->table . '` (' . implode(', ', $all) . ');';
    }

    /**
     * Compile a single column structure based on driver.
     *
     * @param array{name: string, type: string, length?: int, nullable: bool, default: mixed, auto_increment: bool, primary: bool, unique?: bool} $column
     */
    protected function compileColumn(array $column, string $driver): string
    {
        $name    = '`' . $column['name'] . '`';
        $typeSql = '';

        if ($driver === 'sqlite') {
            if ($column['type'] === 'id') {
                return "{$name} INTEGER PRIMARY KEY AUTOINCREMENT";
            }

            $typeSql = match ($column['type']) {
                'string'  => 'VARCHAR(' . ($column['length'] ?? 255) . ')',
                'text'    => 'TEXT',
                'integer' => 'INTEGER',
                'boolean' => 'INTEGER',
                'timestamp' => 'DATETIME',
                default   => 'TEXT',
            };
        } else {
            // MySQL
            if ($column['type'] === 'id') {
                return "{$name} BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY";
            }

            $typeSql = match ($column['type']) {
                'string'  => 'VARCHAR(' . ($column['length'] ?? 255) . ')',
                'text'    => 'TEXT',
                'integer' => 'INT',
                'boolean' => 'TINYINT(1)',
                'timestamp' => 'TIMESTAMP',
                default   => 'TEXT',
            };
        }

        $nullSql = $column['nullable'] ? ' NULL' : ' NOT NULL';

        $defaultSql = '';
        if ($column['default'] !== null) {
            if (is_bool($column['default'])) {
                $defaultSql = ' DEFAULT ' . ($column['default'] ? '1' : '0');
            } elseif (is_string($column['default'])) {
                $defaultSql = " DEFAULT '{$column['default']}'";
            } else {
                $defaultSql = ' DEFAULT ' . $column['default'];
            }
        } elseif ($column['nullable'] && in_array($column['type'], ['timestamp', 'string', 'text', 'boolean', 'integer'], true)) {
            $defaultSql = ' DEFAULT NULL';
        }

        return "{$name} {$typeSql}{$nullSql}{$defaultSql}";
    }
}
