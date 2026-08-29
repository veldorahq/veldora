<?php

declare(strict_types=1);

namespace Veldora\Framework\Database\Relations;

use Veldora\Framework\Database\Model;
use Veldora\Framework\Database\QueryBuilder;

class HasMany extends Relation
{
    /**
     * Create a new HasMany relation instance.
     */
    public function __construct(
        QueryBuilder $query,
        Model $parent,
        Model $related,
        protected string $foreignKey,
        protected string $localKey
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

        $results = $this->query->where($this->foreignKey, '=', $parentKeyValue)->get();
        return array_map(fn($row) => $this->related->newFromBuilder($row), $results);
    }
}
