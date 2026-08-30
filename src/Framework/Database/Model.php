<?php

declare(strict_types=1);

namespace Veldora\Framework\Database;

use ArrayAccess;
use DateTimeInterface;
use DateTimeImmutable;
use JsonSerializable;
use Veldora\Framework\Database\Relations\BelongsTo;
use Veldora\Framework\Database\Relations\BelongsToMany;
use Veldora\Framework\Database\Relations\HasMany;
use Veldora\Framework\Database\Relations\HasManyThrough;
use Veldora\Framework\Database\Relations\HasOne;
use Veldora\Framework\Database\Relations\Relation;
use Veldora\Framework\Foundation\Application;

/**
 * @phpstan-consistent-constructor
 * @implements ArrayAccess<string, mixed>
 */
abstract class Model implements ArrayAccess, JsonSerializable
{
    /**
     * The table associated with the model.
     */
    protected ?string $table = null;

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected array $fillable = [];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>
     */
    protected array $guarded = ['*'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected array $casts = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected array $hidden = [];

    /**
     * The attributes that should be visible in serialization.
     *
     * @var array<string>
     */
    protected array $visible = [];

    /**
     * The model attributes.
     *
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * The model's original attributes.
     *
     * @var array<string, mixed>
     */
    protected array $original = [];

    /**
     * Indicates if the model exists in the database.
     */
    protected bool $exists = false;

    /**
     * Indicates if the model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * Indicates if the model uses soft deletes.
     */
    protected bool $softDelete = false;

    /**
     * Registered model lifecycle event listeners.
     *
     * @var array<string, array<string, array<callable>>>
     */
    protected static array $events = [];

    /**
     * Create a new model instance.
     *
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();
        $this->initializeTraits();
        $this->fill($attributes);
    }

    /**
     * Boot the model if it hasn't been booted yet.
     */
    protected function bootIfNotBooted(): void
    {
        static::boot();
    }

    /**
     * Initialize any traits used by the model.
     */
    protected function initializeTraits(): void
    {
        $class = static::class;
        $traits = [];
        do {
            $traits = array_merge(class_uses($class) ?: [], $traits);
        } while ($class = get_parent_class($class));

        foreach ($traits as $trait) {
            $basename = (string) strrchr($trait, '\\');
            $method = 'initialize' . ltrim($basename !== '' ? $basename : $trait, '\\');
            if (method_exists($this, $method)) {
                $this->{$method}();
            }
        }
    }

    /**
     * The "booting" method of the model.
     */
    protected static function boot(): void
    {
    }

    /**
     * Get the active database connection.
     */
    public static function getConnection(): Connection
    {
        return Application::getInstance()->get(Connection::class);
    }

    /**
     * Get a new QueryBuilder instance for the model's table.
     */
    public function query(): QueryBuilder
    {
        $builder = (new QueryBuilder(static::getConnection()))
            ->table($this->getTable())
            ->setModelClass(static::class);

        if ($this->softDelete) {
            $builder->whereNull('deleted_at');
        }
        return $builder;
    }

    /**
     * Fill the model with an array of attributes respecting fillable/guarded.
     *
     * @param array<string, mixed> $attributes
     */
    public function fill(array $attributes): static
    {
        foreach ($this->filterMassAssignment($attributes) as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Filter the given attributes using fillable / guarded rules.
     *
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    protected function filterMassAssignment(array $attributes): array
    {
        if (count($this->fillable) > 0) {
            return array_intersect_key($attributes, array_flip($this->fillable));
        }

        if ($this->guarded === ['*']) {
            return [];
        }

        return array_diff_key($attributes, array_flip($this->guarded));
    }

    /**
     * Save a new model and return the instance.
     *
     * @param array<string, mixed> $attributes
     */
    public static function create(array $attributes = []): static
    {
        $model = new static();
        $model->fill($attributes);
        $model->save();
        return $model;
    }

    /**
     * Get the table name associated with the model.
     */
    public function getTable(): string
    {
        if ($this->table !== null) {
            return $this->table;
        }

        $class = (string) strrchr(static::class, '\\');
        $basename = ltrim($class !== '' ? $class : static::class, '\\');
        $snake = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $basename));

        if (str_ends_with($snake, 'y')) {
            return substr($snake, 0, -1) . 'ies';
        }

        return $snake . 's';
    }

    /**
     * Get the default foreign key name for the model.
     */
    public function getForeignKey(): string
    {
        $class = (string) strrchr(static::class, '\\');
        $basename = ltrim($class !== '' ? $class : static::class, '\\');
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $basename)) . '_id';
    }

    /**
     * Create a new model instance loaded from database row attributes.
     *
     * @param array<string, mixed>|static $attributes
     */
    public function newFromBuilder(array|self $attributes): static
    {
        if ($attributes instanceof static) {
            return $attributes;
        }

        if ($attributes instanceof self) {
            $attributes = $attributes->getAttributes();
        }

        $model = new static();
        $model->attributes = (array) $attributes;
        $model->original = (array) $attributes;
        $model->exists = true;
        return $model;
    }

    /**
     * Get a specific attribute value.
     */
    public function getAttribute(string $key): mixed
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->castAttribute($key, $this->attributes[$key]);
        }

        // Check if there is a custom accessor method: getFooAttribute()
        $accessor = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute';
        if (method_exists($this, $accessor)) {
            return $this->$accessor($this->attributes[$key] ?? null);
        }

        // Check for relationship
        if (method_exists($this, $key)) {
            $relation = $this->$key();
            if ($relation instanceof Relation) {
                $result = $relation->getResults();
                $this->attributes[$key] = $result;
                return $result;
            }
        }

        return null;
    }

    /**
     * Set an attribute value.
     */
    public function setAttribute(string $key, mixed $value): void
    {
        // Check for custom mutator: setFooAttribute($value)
        $mutator = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))) . 'Attribute';
        if (method_exists($this, $mutator)) {
            $this->$mutator($value);
            return;
        }

        // Store serialized/formatted value if cast is JSON/array
        if (isset($this->casts[$key]) && in_array($this->casts[$key], ['array', 'json'], true) && is_array($value)) {
            $this->attributes[$key] = json_encode($value);
            return;
        }

        $this->attributes[$key] = $value;
    }

    /**
     * Cast an attribute to its declared native PHP type.
     */
    protected function castAttribute(string $key, mixed $value): mixed
    {
        if ($value === null || !isset($this->casts[$key])) {
            return $value;
        }

        $type = strtolower($this->casts[$key]);

        return match ($type) {
            'int', 'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'string' => (string) $value,
            'bool', 'boolean' => (bool) $value,
            'array', 'json' => is_string($value) ? (json_decode($value, true) ?: []) : (array) $value,
            'datetime' => is_string($value) ? new DateTimeImmutable($value) : $value,
            default => $value,
        };
    }

    /**
     * Dynamic getter.
     */
    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamic setter.
     */
    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Dynamic isset checker.
     */
    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Dynamic unset.
     */
    public function __unset(string $key): void
    {
        unset($this->attributes[$key]);
    }

    /**
     * Save the model to the database.
     */
    public function save(): bool
    {
        $now = date('Y-m-d H:i:s');

        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        if ($this->timestamps) {
            if (!$this->exists && !isset($this->attributes['created_at'])) {
                $this->attributes['created_at'] = $now;
            }
            $this->attributes['updated_at'] = $now;
        }

        if ($this->exists) {
            if ($this->fireModelEvent('updating') === false) {
                return false;
            }

            $dirty = array_diff_assoc($this->attributes, $this->original);
            if (empty($dirty)) {
                return true;
            }

            $primaryVal = $this->attributes[$this->primaryKey] ?? null;
            if ($primaryVal === null) {
                return false;
            }

            $affected = $this->query()->where($this->primaryKey, '=', $primaryVal)->update($dirty);
            if ($affected > 0) {
                $this->original = $this->attributes;
                $this->fireModelEvent('updated');
                $this->fireModelEvent('saved');
                return true;
            }
            return false;
        }

        // Insert
        if ($this->fireModelEvent('creating') === false) {
            return false;
        }

        $success = $this->query()->insert($this->attributes);
        if ($success) {
            $this->attributes[$this->primaryKey] = static::getConnection()->getPdo()->lastInsertId();
            $this->original = $this->attributes;
            $this->exists = true;

            $this->fireModelEvent('created');
            $this->fireModelEvent('saved');
            return true;
        }

        return false;
    }

    /**
     * Delete the model from the database.
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }

        $primaryVal = $this->attributes[$this->primaryKey] ?? null;
        if ($primaryVal === null) {
            return false;
        }

        if ($this->softDelete) {
            $this->attributes['deleted_at'] = date('Y-m-d H:i:s');
            $saved = $this->save();
            if ($saved) {
                $this->fireModelEvent('deleted');
            }
            return $saved;
        }

        $affected = $this->query()->where($this->primaryKey, '=', $primaryVal)->delete();
        if ($affected > 0) {
            $this->exists = false;
            $this->fireModelEvent('deleted');
            return true;
        }

        return false;
    }

    /**
     * Find a model by its primary key.
     */
    public static function find(mixed $id): ?static
    {
        $model = new static();
        
        $result = $model->query()
            ->where($model->primaryKey, '=', $id)
            ->first();

        if ($result === null) {
            return null;
        }

        if ($result instanceof static) {
            return $result;
        }

        return $model->newFromBuilder($result);
    }

    /**
     * Retrieve all records of the model.
     *
     * @return array<static>
     */
    public static function all(): array
    {
        $model = new static();
        $results = $model->query()->get();

        return array_map(function ($row) use ($model) {
            return $row instanceof static ? $row : $model->newFromBuilder($row);
        }, $results);
    }

    /**
     * Paginate the model query into a Paginator instance with model objects.
     */
    public static function paginate(int $perPage = 15, ?int $page = null): Paginator
    {
        $model = new static();
        $rawPaginator = $model->query()->paginate($perPage, $page);

        $hydrated = array_map(fn ($row) => $model->newFromBuilder($row), $rawPaginator->items());

        return new Paginator(
            $hydrated,
            $rawPaginator->total(),
            $rawPaginator->perPage(),
            $rawPaginator->currentPage()
        );
    }

    /**
     * Chunk results into hydrated models.
     */
    public static function chunk(int $count, callable $callback): bool
    {
        $model = new static();
        return $model->query()->chunk($count, function (array $rows, int $page) use ($model, $callback) {
            $hydrated = array_map(fn ($row) => $model->newFromBuilder($row), $rows);
            return $callback($hydrated, $page);
        });
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $array = [];

        foreach ($this->attributes as $key => $value) {
            if (!empty($this->visible) && !in_array($key, $this->visible, true)) {
                continue;
            }
            if (in_array($key, $this->hidden, true)) {
                continue;
            }

            $castVal = $this->getAttribute($key);

            if ($castVal instanceof DateTimeInterface) {
                $array[$key] = $castVal->format('Y-m-d H:i:s');
            } elseif ($castVal instanceof Model) {
                $array[$key] = $castVal->toArray();
            } elseif (is_array($castVal)) {
                $array[$key] = array_map(fn ($item) => $item instanceof Model ? $item->toArray() : $item, $castVal);
            } else {
                $array[$key] = $castVal;
            }
        }

        return $array;
    }

    /**
     * Convert the model to its JSON representation.
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options) ?: '{}';
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    // --- Relationships ---

    /**
     * Define a HasOne relationship.
     *
     * @template T of Model
     * @param class-string<T> $related
     */
    protected function hasOne(string $related, ?string $foreignKey = null, ?string $localKey = null): HasOne
    {
        /** @var T $instance */
        $instance = new $related();
        
        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $localKey = $localKey ?? $this->primaryKey;

        return new HasOne($instance->query(), $this, $instance, $foreignKey, $localKey);
    }

    /**
     * Define a HasMany relationship.
     *
     * @template T of Model
     * @param class-string<T> $related
     */
    protected function hasMany(string $related, ?string $foreignKey = null, ?string $localKey = null): HasMany
    {
        /** @var T $instance */
        $instance = new $related();

        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $localKey = $localKey ?? $this->primaryKey;

        return new HasMany($instance->query(), $this, $instance, $foreignKey, $localKey);
    }

    /**
     * Define a BelongsTo relationship.
     *
     * @template T of Model
     * @param class-string<T> $related
     */
    protected function belongsTo(string $related, ?string $foreignKey = null, ?string $ownerKey = null): BelongsTo
    {
        /** @var T $instance */
        $instance = new $related();

        $foreignKey = $foreignKey ?? $instance->getForeignKey();
        $ownerKey = $ownerKey ?? $instance->primaryKey;

        return new BelongsTo($instance->query(), $this, $instance, $foreignKey, $ownerKey);
    }

    /**
     * Define a BelongsToMany relationship.
     *
     * @template T of Model
     * @param class-string<T> $related
     */
    protected function belongsToMany(
        string $related,
        ?string $table = null,
        ?string $foreignPivotKey = null,
        ?string $relatedPivotKey = null,
        ?string $parentKey = null,
        ?string $relatedKey = null
    ): BelongsToMany {
        /** @var T $instance */
        $instance = new $related();

        if ($table === null) {
            $segments = [$this->getTable(), $instance->getTable()];
            sort($segments);
            // Singularize both for table name (e.g. role_user)
            $table = implode('_', array_map(fn ($s) => rtrim($s, 's'), $segments));
        }

        $foreignPivotKey = $foreignPivotKey ?? $this->getForeignKey();
        $relatedPivotKey = $relatedPivotKey ?? $instance->getForeignKey();
        $parentKey = $parentKey ?? $this->primaryKey;
        $relatedKey = $relatedKey ?? $instance->primaryKey;

        return new BelongsToMany(
            $instance->query(),
            $this,
            $instance,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey
        );
    }

    /**
     * Define a HasManyThrough relationship.
     *
     * @template TRelated of Model
     * @template TThrough of Model
     * @param class-string<TRelated> $related
     * @param class-string<TThrough> $through
     */
    protected function hasManyThrough(
        string $related,
        string $through,
        ?string $firstKey = null,
        ?string $secondKey = null,
        ?string $localKey = null,
        ?string $secondLocalKey = null
    ): HasManyThrough {
        /** @var TRelated $relatedInstance */
        $relatedInstance = new $related();
        /** @var TThrough $throughInstance */
        $throughInstance = new $through();

        $firstKey = $firstKey ?? $this->getForeignKey();
        $secondKey = $secondKey ?? $throughInstance->getForeignKey();
        $localKey = $localKey ?? $this->primaryKey;
        $secondLocalKey = $secondLocalKey ?? $throughInstance->primaryKey;

        return new HasManyThrough(
            $relatedInstance->query(),
            $this,
            $relatedInstance,
            $throughInstance,
            $firstKey,
            $secondKey,
            $localKey,
            $secondLocalKey
        );
    }

    // --- Model Event Listeners & Hooks ---

    /**
     * Register a model lifecycle listener.
     */
    public static function registerModelEvent(string $event, callable $callback): void
    {
        static::$events[static::class][$event][] = $callback;
    }

    public static function creating(callable $callback): void
    {
        static::registerModelEvent('creating', $callback);
    }

    public static function created(callable $callback): void
    {
        static::registerModelEvent('created', $callback);
    }

    public static function updating(callable $callback): void
    {
        static::registerModelEvent('updating', $callback);
    }

    public static function updated(callable $callback): void
    {
        static::registerModelEvent('updated', $callback);
    }

    public static function saving(callable $callback): void
    {
        static::registerModelEvent('saving', $callback);
    }

    public static function saved(callable $callback): void
    {
        static::registerModelEvent('saved', $callback);
    }

    public static function deleting(callable $callback): void
    {
        static::registerModelEvent('deleting', $callback);
    }

    public static function deleted(callable $callback): void
    {
        static::registerModelEvent('deleted', $callback);
    }

    public static function restoring(callable $callback): void
    {
        static::registerModelEvent('restoring', $callback);
    }

    public static function restored(callable $callback): void
    {
        static::registerModelEvent('restored', $callback);
    }

    /**
     * Fire a model lifecycle event.
     */
    protected function fireModelEvent(string $event): ?bool
    {
        $callbacks = static::$events[static::class][$event] ?? [];

        foreach ($callbacks as $callback) {
            if ($callback($this) === false) {
                return false;
            }
        }

        return true;
    }

    // --- ArrayAccess Implementation ---

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getAttribute((string) $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setAttribute((string) $offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Forward dynamic static calls to query builder or local scopes.
     *
     * @param array<mixed> $parameters
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        $model = new static();

        // Check for local scope: scopeFoo($query, ...$params)
        $scopeMethod = 'scope' . ucfirst($method);
        if (method_exists($model, $scopeMethod)) {
            $query = $model->query();
            return $model->$scopeMethod($query, ...$parameters) ?: $query;
        }

        return $model->query()->$method(...$parameters);
    }
}
