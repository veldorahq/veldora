<?php

declare(strict_types=1);

namespace Veldora\Framework\Database\Relations;

use Veldora\Framework\Database\Model;
use Veldora\Framework\Database\QueryBuilder;

class BelongsToMany extends Relation
{
    /**
     * Create a new BelongsToMany relation instance.
     */
    public function __construct(
        QueryBuilder $query,
        Model $parent,
        Model $related,
        protected string $table,
        protected string $foreignPivotKey,
        protected string $relatedPivotKey,
        protected string $parentKey = 'id',
        protected string $relatedKey = 'id'
    ) {
        parent::__construct($query, $parent, $related);
    }

    /**
     * Resolve the relationship results.
     *
     * @return array<Model>
     */
    public function getResults(): array
    {
        $parentKeyValue = $this->parent->getAttribute($this->parentKey);
        if ($parentKeyValue === null) {
            return [];
        }

        $relatedTable = $this->related->getTable();
        $relatedPrimaryKey = $this->relatedKey;

        // Perform inner join on the pivot table
        $results = $this->query
            ->select("{$relatedTable}.*")
            ->join(
                $this->table,
                "{$this->table}.{$this->relatedPivotKey}",
                '=',
                "{$relatedTable}.{$relatedPrimaryKey}"
            )
            ->where("{$this->table}.{$this->foreignPivotKey}", '=', $parentKeyValue)
            ->get();

        return array_map(fn ($row) => $this->related->newFromBuilder($row), $results);
    }

    /**
     * Attach a model to the parent via the pivot table.
     *
     * @param int|string|array<int|string> $ids
     * @param array<string, mixed> $attributes
     */
    public function attach(int|string|array $ids, array $attributes = []): void
    {
        $parentKeyValue = $this->parent->getAttribute($this->parentKey);
        if ($parentKeyValue === null) {
            return;
        }

        $ids = (array) $ids;
        $db = $this->query->getConnection();

        foreach ($ids as $id) {
            $record = array_merge([
                $this->foreignPivotKey => $parentKeyValue,
                $this->relatedPivotKey => $id,
            ], $attributes);

            $db->table($this->table)->insert($record);
        }
    }

    /**
     * Detach models from the relationship in the pivot table.
     *
     * @param int|string|array<int|string>|null $ids
     */
    public function detach(int|string|array|null $ids = null): int
    {
        $parentKeyValue = $this->parent->getAttribute($this->parentKey);
        if ($parentKeyValue === null) {
            return 0;
        }

        $db = $this->query->getConnection();
        $query = $db->table($this->table)->where($this->foreignPivotKey, '=', $parentKeyValue);

        if ($ids !== null) {
            $ids = (array) $ids;
            $query->whereIn($this->relatedPivotKey, $ids);
        }

        return $query->delete();
    }

    /**
     * Sync the intermediate table with a list of IDs.
     *
     * @param array<int|string|array{id: int|string, ...}> $ids
     */
    public function sync(array $ids): array
    {
        $parentKeyValue = $this->parent->getAttribute($this->parentKey);
        if ($parentKeyValue === null) {
            return ['attached' => [], 'detached' => [], 'updated' => []];
        }

        // Get currently attached IDs
        $db = $this->query->getConnection();
        $current = $db->table($this->table)
            ->where($this->foreignPivotKey, '=', $parentKeyValue)
            ->get();

        $currentIds = array_column($current, $this->relatedPivotKey);
        $targetIds = [];
        $targetAttributes = [];

        foreach ($ids as $key => $value) {
            if (is_array($value)) {
                $id = $value['id'] ?? $key;
                $targetIds[] = $id;
                $targetAttributes[$id] = array_filter($value, fn ($k) => $k !== 'id', ARRAY_FILTER_USE_KEY);
            } else {
                $targetIds[] = $value;
                $targetAttributes[$value] = [];
            }
        }

        $detachIds = array_diff($currentIds, $targetIds);
        $attachIds = array_diff($targetIds, $currentIds);

        if (!empty($detachIds)) {
            $this->detach(array_values($detachIds));
        }

        foreach ($attachIds as $id) {
            $this->attach($id, $targetAttributes[$id] ?? []);
        }

        return [
            'attached' => array_values($attachIds),
            'detached' => array_values($detachIds),
            'updated' => [],
        ];
    }

    /**
     * Toggle the given IDs on the pivot table.
     *
     * @param int|string|array<int|string> $ids
     */
    public function toggle(int|string|array $ids): array
    {
        $parentKeyValue = $this->parent->getAttribute($this->parentKey);
        if ($parentKeyValue === null) {
            return ['attached' => [], 'detached' => []];
        }

        $ids = (array) $ids;
        $db = $this->query->getConnection();
        $current = $db->table($this->table)
            ->where($this->foreignPivotKey, '=', $parentKeyValue)
            ->whereIn($this->relatedPivotKey, $ids)
            ->get();

        $currentIds = array_column($current, $this->relatedPivotKey);
        $detachIds = array_intersect($ids, $currentIds);
        $attachIds = array_diff($ids, $currentIds);

        if (!empty($detachIds)) {
            $this->detach(array_values($detachIds));
        }

        if (!empty($attachIds)) {
            $this->attach(array_values($attachIds));
        }

        return [
            'attached' => array_values($attachIds),
            'detached' => array_values($detachIds),
        ];
    }
}
