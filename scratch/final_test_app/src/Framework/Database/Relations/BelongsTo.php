<?php

declare(strict_types=1);

namespace Veldora\Framework\Database\Relations;

use Veldora\Framework\Database\Model;
use Veldora\Framework\Database\QueryBuilder;

class BelongsTo extends Relation
{
    /**
     * Create a new BelongsTo relation instance.
     */
    public function __construct(
        QueryBuilder $query,
        Model $parent,
        Model $related,
        protected string $foreignKey,
        protected string $ownerKey
    ) {
        parent::__construct($query, $parent, $related);
    }

    /**
     * Resolve the relationship results.
     */
    public function getResults(): ?Model
    {
        $foreignKeyValue = $this->parent->getAttribute($this->foreignKey);
        if ($foreignKeyValue === null) {
            return null;
        }

        $attributes = $this->query->where($this->ownerKey, '=', $foreignKeyValue)->first();
        if ($attributes === null) {
            return null;
        }

        return $this->related->newFromBuilder($attributes);
    }
}
