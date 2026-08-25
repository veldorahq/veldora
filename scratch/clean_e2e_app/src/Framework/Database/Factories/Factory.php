<?php

declare(strict_types=1);

namespace Veldora\Framework\Database\Factories;

use Veldora\Framework\Database\Model;

/**
 * @template TModel of Model
 */
abstract class Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<TModel>
     */
    protected string $model;

    /**
     * The number of models that should be generated.
     */
    protected int $count = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    abstract public function definition(): array;

    /**
     * Set the number of models that should be generated.
     */
    public function count(int $count): static
    {
        $clone = clone $this;
        $clone->count = $count;
        return $clone;
    }

    /**
     * Create a collection of models and persist them to the database.
     *
     * @param array<string, mixed> $attributes
     * @return TModel|array<TModel>
     */
    public function create(array $attributes = []): mixed
    {
        $results = [];

        for ($i = 0; $i < $this->count; $i++) {
            $data = array_merge($this->definition(), $attributes);
            /** @var TModel $model */
            $model = new $this->model();
            $model->fill($data);
            $model->save();
            $results[] = $model;
        }

        return $this->count === 1 ? $results[0] : $results;
    }

    /**
     * Create a collection of models without persisting them.
     *
     * @param array<string, mixed> $attributes
     * @return TModel|array<TModel>
     */
    public function make(array $attributes = []): mixed
    {
        $results = [];

        for ($i = 0; $i < $this->count; $i++) {
            $data = array_merge($this->definition(), $attributes);
            /** @var TModel $model */
            $model = new $this->model();
            $model->fill($data);
            $results[] = $model;
        }

        return $this->count === 1 ? $results[0] : $results;
    }
}
