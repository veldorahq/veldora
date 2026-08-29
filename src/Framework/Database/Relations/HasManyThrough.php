<?php

declare(strict_types=1);

namespace Veldora\Framework\Database\Relations;

use Veldora\Framework\Database\Model;
use Veldora\Framework\Database\QueryBuilder;

class HasManyThrough extends Relation
{
    /**
     * Create a new HasManyThrough relation instance.
     */
    public function __construct(
        QueryBuilder $query,
        Model $parent,
        Model $related,
        protected Model $through,
        protected string $firstKey,      // e.g. users.country_id
        protected string $secondKey,     // e.g. posts.user_id
        protected string $localKey = 'id',   // e.g. countries.id
        protected string $secondLocalKey = 'id' // e.g. users.id
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
        $parentKeyValue = $this->parent->getAttribute($this->localKey);
        if ($parentKeyValue === null) {
            return [];
        }

        $throughTable = $this->through->getTable();
        $relatedTable = $this->related->getTable();

        $results = $this->query
            ->select("{$relatedTable}.*")
            ->join(
                $throughTable,
                "{$throughTable}.{$this->secondLocalKey}",
                '=',
                "{$relatedTable}.{$this->secondKey}"
            )
            ->where("{$throughTable}.{$this->firstKey}", '=', $parentKeyValue)
            ->get();

        return array_map(fn ($row) => $this->related->newFromBuilder($row), $results);
    }
}
