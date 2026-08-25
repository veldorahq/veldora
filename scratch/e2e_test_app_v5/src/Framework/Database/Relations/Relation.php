<?php

declare(strict_types=1);

namespace Veldora\Framework\Database\Relations;

use Veldora\Framework\Database\Model;
use Veldora\Framework\Database\QueryBuilder;

abstract class Relation
{
    /**
     * Create a new Relation instance.
     */
    public function __construct(
        protected QueryBuilder $query,
        protected Model $parent,
        protected Model $related
    ) {
    }

    /**
     * Resolve the relationship results.
     */
    abstract public function getResults(): mixed;
}
