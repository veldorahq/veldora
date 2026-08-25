<?php

declare(strict_types=1);

namespace Veldora\Framework\Database;

use PDO;
use RuntimeException;

class QueryBuilder
{
    /**
     * The table name.
     */
    protected string $table = '';

    /**
     * The columns to select.
     *
     * @var array<string>
     */
    protected array $columns = ['*'];

    /**
     * The table joins.
     *
     * @var array<array{type: string, table: string, first: string, operator: string, second: string}>
     */
    protected array $joins = [];

    /**
     * The where constraints.
     *
     * @var array<array{type: string, raw?: bool, sql?: string, column?: string, operator?: string, value?: mixed, values?: array<mixed>}>
     */
    protected array $wheres = [];

    /**
     * The group by columns.
     *
     * @var array<string>
     */
    protected array $groups = [];

    /**
     * The having clauses.
     *
     * @var array<array{type: string, column: string, operator: string, value: mixed}>
     */
    protected array $havings = [];

    /**
     * The order by clauses.
     *
     * @var array<array{column: string, direction: string}>
     */
    protected array $orders = [];

    /**
     * The maximum number of records to return.
     */
    protected ?int $limit = null;

    /**
     * The offset to start returning records from.
     */
    protected ?int $offset = null;

    /**
     * The query bindings.
     *
     * @var array<mixed>
     */
    protected array $bindings = [];

    /**
     * The model class to hydrate records into.
     */
    protected ?string $modelClass = null;

    /**
     * Create a new QueryBuilder instance.
     */
    public function __construct(protected Connection $connection)
    {
    }

    /**
     * Set the model class for hydration.
     */
    public function setModelClass(?string $modelClass): self
    {
        $this->modelClass = $modelClass;
        return $this;
    }

    /**
     * Get the model class.
     */
    public function getModelClass(): ?string
    {
        return $this->modelClass;
    }

    /**
     * Get the connection instance.
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }

    /**
     * Set the table target.
     */
    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set the columns to select.
     *
     * @param array<string>|string $columns
     */
    public function select(array|string $columns = ['*']): self
    {
        $this->columns = is_array($columns) ? $columns : [$columns];
        return $this;
    }

    /**
     * Add an INNER JOIN clause.
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = [
            'type' => strtoupper($type),
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
        ];

        return $this;
    }

    /**
     * Add a LEFT JOIN clause.
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * Add a RIGHT JOIN clause.
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    /**
     * Add a basic WHERE constraint.
     */
    public function where(string $column, string $operator, mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'AND',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ];

        $this->bindings[] = $value;

        return $this;
    }

    /**
     * Add an OR WHERE constraint.
     */
    public function orWhere(string $column, string $operator, mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'OR',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ];

        $this->bindings[] = $value;

        return $this;
    }

    /**
     * Add a WHERE IN constraint.
     *
     * @param array<mixed> $values
     */
    public function whereIn(string $column, array $values, string $boolean = 'AND', bool $not = false): self
    {
        $type = strtoupper($boolean);
        $operator = $not ? 'NOT IN' : 'IN';

        if (empty($values)) {
            // WHERE 0 = 1 if empty IN array
            $this->wheres[] = [
                'type' => $type,
                'raw' => true,
                'sql' => $not ? '1 = 1' : '0 = 1',
            ];
            return $this;
        }

        $this->wheres[] = [
            'type' => $type,
            'column' => $column,
            'operator' => $operator,
            'values' => array_values($values),
        ];

        foreach ($values as $val) {
            $this->bindings[] = $val;
        }

        return $this;
    }

    /**
     * Add a WHERE NOT IN constraint.
     *
     * @param array<mixed> $values
     */
    public function whereNotIn(string $column, array $values, string $boolean = 'AND'): self
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add an OR WHERE IN constraint.
     *
     * @param array<mixed> $values
     */
    public function orWhereIn(string $column, array $values): self
    {
        return $this->whereIn($column, $values, 'OR');
    }

    /**
     * Add an OR WHERE NOT IN constraint.
     *
     * @param array<mixed> $values
     */
    public function orWhereNotIn(string $column, array $values): self
    {
        return $this->whereIn($column, $values, 'OR', true);
    }

    /**
     * Add a WHERE NULL constraint.
     */
    public function whereNull(string $column, string $boolean = 'AND', bool $not = false): self
    {
        $type = strtoupper($boolean);
        $operator = $not ? 'IS NOT NULL' : 'IS NULL';

        $this->wheres[] = [
            'type' => $type,
            'column' => $column,
            'operator' => $operator,
        ];

        return $this;
    }

    /**
     * Add a WHERE NOT NULL constraint.
     */
    public function whereNotNull(string $column, string $boolean = 'AND'): self
    {
        return $this->whereNull($column, $boolean, true);
    }

    /**
     * Add an OR WHERE NULL constraint.
     */
    public function orWhereNull(string $column): self
    {
        return $this->whereNull($column, 'OR');
    }

    /**
     * Add an OR WHERE NOT NULL constraint.
     */
    public function orWhereNotNull(string $column): self
    {
        return $this->whereNull($column, 'OR', true);
    }

    /**
     * Add a WHERE BETWEEN constraint.
     *
     * @param array{0: mixed, 1: mixed} $values
     */
    public function whereBetween(string $column, array $values, string $boolean = 'AND', bool $not = false): self
    {
        $type = strtoupper($boolean);
        $operator = $not ? 'NOT BETWEEN' : 'BETWEEN';

        $this->wheres[] = [
            'type' => $type,
            'column' => $column,
            'operator' => $operator,
            'values' => [$values[0], $values[1]],
        ];

        $this->bindings[] = $values[0];
        $this->bindings[] = $values[1];

        return $this;
    }

    /**
     * Add a WHERE NOT BETWEEN constraint.
     *
     * @param array{0: mixed, 1: mixed} $values
     */
    public function whereNotBetween(string $column, array $values, string $boolean = 'AND'): self
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    /**
     * Add a GROUP BY clause.
     */
    public function groupBy(string ...$columns): self
    {
        foreach ($columns as $column) {
            $this->groups[] = $column;
        }

        return $this;
    }

    /**
     * Add a HAVING clause.
     */
    public function having(string $column, string $operator, mixed $value = null, string $boolean = 'AND'): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->havings[] = [
            'type' => strtoupper($boolean),
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ];

        $this->bindings[] = $value;

        return $this;
    }

    /**
     * Add an OR HAVING clause.
     */
    public function orHaving(string $column, string $operator, mixed $value = null): self
    {
        return $this->having($column, $operator, $value, 'OR');
    }

    /**
     * Add an ORDER BY clause.
     */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
        $this->orders[] = [
            'column' => $column,
            'direction' => $direction,
        ];

        return $this;
    }

    /**
     * Set the limit constraint.
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set the offset constraint.
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Compile the SELECT query to SQL.
     */
    public function toSql(): string
    {
        if ($this->table === '') {
            throw new RuntimeException('No table selected in query builder.');
        }

        $sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->sanitizeName($this->table);

        if (!empty($this->joins)) {
            $sql .= ' ' . $this->compileJoins();
        }

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        if (!empty($this->groups)) {
            $sql .= ' GROUP BY ' . implode(', ', array_map([$this, 'sanitizeName'], $this->groups));
        }

        if (!empty($this->havings)) {
            $sql .= ' HAVING ' . $this->compileHavings();
        }

        if (!empty($this->orders)) {
            $sql .= ' ORDER BY ' . $this->compileOrders();
        }

        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return $sql;
    }

    /**
     * Compile JOIN clauses.
     */
    protected function compileJoins(): string
    {
        $joins = [];
        foreach ($this->joins as $join) {
            $table = $this->sanitizeName($join['table']);
            $first = $this->sanitizeName($join['first']);
            $second = $this->sanitizeName($join['second']);
            $joins[] = "{$join['type']} JOIN {$table} ON {$first} {$join['operator']} {$second}";
        }
        return implode(' ', $joins);
    }

    /**
     * Compile the where constraints into SQL.
     */
    protected function compileWheres(): string
    {
        $sql = '';

        foreach ($this->wheres as $index => $where) {
            $prefix = $index === 0 ? '' : ' ' . $where['type'] . ' ';

            if (!empty($where['raw'])) {
                $sql .= $prefix . $where['sql'];
                continue;
            }

            $column = $this->sanitizeName($where['column']);
            $operator = $where['operator'];

            if ($operator === 'IN' || $operator === 'NOT IN') {
                $placeholders = implode(', ', array_fill(0, count($where['values'] ?? []), '?'));
                $sql .= $prefix . "{$column} {$operator} ({$placeholders})";
            } elseif ($operator === 'BETWEEN' || $operator === 'NOT BETWEEN') {
                $sql .= $prefix . "{$column} {$operator} ? AND ?";
            } elseif ($operator === 'IS NULL' || $operator === 'IS NOT NULL') {
                $sql .= $prefix . "{$column} {$operator}";
            } else {
                $sql .= $prefix . "{$column} {$operator} ?";
            }
        }

        return $sql;
    }

    /**
     * Compile the having constraints into SQL.
     */
    protected function compileHavings(): string
    {
        $sql = '';
        foreach ($this->havings as $index => $having) {
            $prefix = $index === 0 ? '' : ' ' . $having['type'] . ' ';
            $column = $this->sanitizeName($having['column']);
            $sql .= $prefix . "{$column} {$having['operator']} ?";
        }
        return $sql;
    }

    /**
     * Compile the orders into SQL.
     */
    protected function compileOrders(): string
    {
        $parts = [];
        foreach ($this->orders as $order) {
            $parts[] = $this->sanitizeName($order['column']) . ' ' . $order['direction'];
        }

        return implode(', ', $parts);
    }

    /**
     * Sanitize table/column identifiers to prevent injection.
     */
    protected function sanitizeName(string $name): string
    {
        if ($name === '*' || str_contains($name, '(') || str_contains($name, ' ') || str_contains($name, 'as')) {
            return $name;
        }

        // Allow dot notation for table.column
        $parts = explode('.', $name);
        $escaped = [];
        foreach ($parts as $part) {
            $escaped[] = '`' . preg_replace('/[^a-zA-Z0-9_]/', '', $part) . '`';
        }
        return implode('.', $escaped);
    }

    /**
     * Execute the SELECT query and return results.
     *
     * @return array<mixed>
     */
    public function get(): array
    {
        $sql = $this->toSql();
        $pdo = $this->connection->getPdo();

        $stmt = $pdo->prepare($sql);
        $stmt->execute($this->bindings);

        /** @var array<array<string, mixed>> $results */
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($this->modelClass !== null && class_exists($this->modelClass)) {
            $class = $this->modelClass;
            $dummy = new $class();
            return array_map(fn ($row) => $dummy->newFromBuilder($row), $results);
        }

        return $results;
    }

    /**
     * Execute the query and return the first result.
     */
    public function first(): mixed
    {
        $this->limit(1);
        $results = $this->get();

        return $results[0] ?? null;
    }

    /**
     * Retrieve the "count" result of the query.
     */
    public function count(string $columns = '*'): int
    {
        $previousColumns = $this->columns;
        $this->columns = ["COUNT({$columns}) as aggregate"];

        $result = $this->first();
        $this->columns = $previousColumns;

        return (int) ($result['aggregate'] ?? 0);
    }

    /**
     * Retrieve the sum of the values of a given column.
     */
    public function sum(string $column): float|int
    {
        $previousColumns = $this->columns;
        $this->columns = ["SUM({$column}) as aggregate"];

        $result = $this->first();
        $this->columns = $previousColumns;

        return is_numeric($result['aggregate'] ?? null) ? $result['aggregate'] + 0 : 0;
    }

    /**
     * Retrieve the average of the values of a given column.
     */
    public function avg(string $column): float
    {
        $previousColumns = $this->columns;
        $this->columns = ["AVG({$column}) as aggregate"];

        $result = $this->first();
        $this->columns = $previousColumns;

        return (float) ($result['aggregate'] ?? 0);
    }

    /**
     * Retrieve the minimum value of a given column.
     */
    public function min(string $column): mixed
    {
        $previousColumns = $this->columns;
        $this->columns = ["MIN({$column}) as aggregate"];

        $result = $this->first();
        $this->columns = $previousColumns;

        return $result['aggregate'] ?? null;
    }

    /**
     * Retrieve the maximum value of a given column.
     */
    public function max(string $column): mixed
    {
        $previousColumns = $this->columns;
        $this->columns = ["MAX({$column}) as aggregate"];

        $result = $this->first();
        $this->columns = $previousColumns;

        return $result['aggregate'] ?? null;
    }

    /**
     * Determine if any rows exist for the current query.
     */
    public function exists(): bool
    {
        $previousLimit = $this->limit;
        $this->limit = 1;
        $results = $this->get();
        $this->limit = $previousLimit;

        return !empty($results);
    }

    /**
     * Chunk the results of the query into smaller batches.
     */
    public function chunk(int $count, callable $callback): bool
    {
        $page = 1;

        do {
            $clone = clone $this;
            $results = $clone->offset(($page - 1) * $count)->limit($count)->get();

            $countResults = count($results);

            if ($countResults === 0) {
                break;
            }

            if ($callback($results, $page) === false) {
                return false;
            }

            unset($results);
            $page++;
        } while ($countResults === $count);

        return true;
    }

    /**
     * Paginate the given query into a Paginator instance.
     */
    public function paginate(int $perPage = 15, ?int $page = null): Paginator
    {
        $page = $page ?: (isset($_GET['page']) ? (int) $_GET['page'] : 1);
        $page = max(1, $page);

        $total = (clone $this)->count();

        $results = (clone $this)
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return new Paginator($results, $total, $perPage, $page);
    }

    /**
     * Execute an INSERT query.
     *
     * @param array<string, mixed> $values
     */
    public function insert(array $values): bool
    {
        if ($this->table === '') {
            throw new RuntimeException('No table selected in query builder.');
        }

        $columns = array_keys($values);
        $escapedColumns = array_map([$this, 'sanitizeName'], $columns);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = 'INSERT INTO ' . $this->sanitizeName($this->table) . 
            ' (' . implode(', ', $escapedColumns) . ') VALUES (' . implode(', ', $placeholders) . ')';

        $pdo = $this->connection->getPdo();
        $stmt = $pdo->prepare($sql);

        return $stmt->execute(array_values($values));
    }

    /**
     * Execute an UPDATE query.
     *
     * @param array<string, mixed> $values
     */
    public function update(array $values): int
    {
        if ($this->table === '') {
            throw new RuntimeException('No table selected in query builder.');
        }

        $sets = [];
        foreach ($values as $column => $value) {
            $sets[] = $this->sanitizeName($column) . ' = ?';
        }

        $sql = 'UPDATE ' . $this->sanitizeName($this->table) . ' SET ' . implode(', ', $sets);

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        $pdo = $this->connection->getPdo();
        $stmt = $pdo->prepare($sql);

        $bindings = array_merge(array_values($values), $this->bindings);
        $stmt->execute($bindings);

        return $stmt->rowCount();
    }

    /**
     * Execute a DELETE query.
     */
    public function delete(): int
    {
        if ($this->table === '') {
            throw new RuntimeException('No table selected in query builder.');
        }

        $sql = 'DELETE FROM ' . $this->sanitizeName($this->table);

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        $pdo = $this->connection->getPdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($this->bindings);

        return $stmt->rowCount();
    }

    /**
     * Get the query bindings.
     *
     * @return array<mixed>
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }
}
