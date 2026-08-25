<?php

declare(strict_types=1);

namespace Veldora\Framework\Database\Relations;

use Veldora\Framework\Database\Model;
use Veldora\Framework\Database\QueryBuilder;

class HasOne extends Relation
{
    /**
     * Create a new HasOne relation instance.
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
     */
    public function getResults(): ?Model
    {
        $parentKeyValue = $this->parent->getAttribute($this->localKey);
        if ($parentKeyValue === null) {
            return null;
        }

        $attributes = $this->query->where($this->foreignKey, '=', $parentKeyValue)->first();
        if ($attributes === null) {
            return null;
        }

        return $this->related->newFromBuilder($attributes);
    }
}
