<?php

declare(strict_types=1);

namespace Veldora\Framework\Database;

/**
 * Trait SoftDeletes
 *
 * Adds soft delete capabilities to ActiveRecord models.
 * Automatically excludes soft-deleted records unless explicitly included.
 */
trait SoftDeletes
{
    /**
     * Boot the soft deleting trait for a model.
     */
    public function initializeSoftDeletes(): void
    {
        $this->softDelete = true;
        if (!in_array('deleted_at', $this->casts, true)) {
            $this->casts['deleted_at'] = 'datetime';
        }
    }

    /**
     * Perform the actual soft delete on the model.
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $this->attributes['deleted_at'] = $now;

        $primaryVal = $this->attributes[$this->primaryKey] ?? null;
        if ($primaryVal === null) {
            return false;
        }

        $affected = (new QueryBuilder(static::getConnection()))
            ->table($this->getTable())
            ->where($this->primaryKey, '=', $primaryVal)
            ->update(['deleted_at' => $now]);

        if ($affected > 0) {
            $this->original['deleted_at'] = $now;
            $this->fireModelEvent('deleted');
            return true;
        }

        return false;
    }

    /**
     * Restore a soft-deleted model instance.
     */
    public function restore(): bool
    {
        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }

        $this->attributes['deleted_at'] = null;

        $primaryVal = $this->attributes[$this->primaryKey] ?? null;
        if ($primaryVal === null) {
            return false;
        }

        $affected = (new QueryBuilder(static::getConnection()))
            ->table($this->getTable())
            ->where($this->primaryKey, '=', $primaryVal)
            ->update(['deleted_at' => null]);

        if ($affected > 0) {
            $this->original['deleted_at'] = null;
            $this->fireModelEvent('restored');
            return true;
        }

        return false;
    }

    /**
     * Force a hard delete on a soft-deletable model.
     */
    public function forceDelete(): bool
    {
        $primaryVal = $this->attributes[$this->primaryKey] ?? null;
        if ($primaryVal === null) {
            return false;
        }

        $affected = (new QueryBuilder(static::getConnection()))
            ->table($this->getTable())
            ->where($this->primaryKey, '=', $primaryVal)
            ->delete();

        if ($affected > 0) {
            $this->exists = false;
            $this->fireModelEvent('deleted');
            return true;
        }

        return false;
    }

    /**
     * Determine if the model instance has been soft-deleted.
     */
    public function trashed(): bool
    {
        return !empty($this->attributes['deleted_at']);
    }

    /**
     * Get a query builder that includes soft-deleted records.
     */
    public static function withTrashed(): QueryBuilder
    {
        $model = new static();
        return (new QueryBuilder(static::getConnection()))
            ->table($model->getTable())
            ->setModelClass(static::class);
    }

    /**
     * Get a query builder that only returns soft-deleted records.
     */
    public static function onlyTrashed(): QueryBuilder
    {
        $model = new static();
        return (new QueryBuilder(static::getConnection()))
            ->table($model->getTable())
            ->whereNotNull('deleted_at')
            ->setModelClass(static::class);
    }
}
