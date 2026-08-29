<?php

declare(strict_types=1);

namespace Veldora\Framework\Foundation;

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use ReflectionIntersectionType;
use Veldora\Framework\Foundation\Exception\ContainerException;
use Veldora\Framework\Foundation\Exception\NotFoundException;

class Container implements ContainerInterface
{
    /**
     * The bound definitions.
     *
     * @var array<string, mixed>
     */
    protected array $bindings = [];

    /**
     * The instantiated singleton instances.
     *
     * @var array<string, mixed>
     */
    protected array $instances = [];

    /**
     * Retrieve an entry from the container.
     */
    public function get(string $id): mixed
    {
        if ($this->hasInstance($id)) {
            return $this->instances[$id];
        }

        if ($this->has($id)) {
            $concrete = $this->bindings[$id]['concrete'];
            $isSingleton = $this->bindings[$id]['shared'];

            if ($concrete instanceof \Closure) {
                $object = $concrete($this);
            } elseif (is_string($concrete) && class_exists($concrete)) {
                $object = $this->resolve($concrete);
            } else {
                $object = $concrete;
            }

            if ($isSingleton) {
                $this->instances[$id] = $object;
            }

            return $object;
        }

        // If it's a valid class name, try autowiring it
        if (class_exists($id)) {
            return $this->resolve($id);
        }

        throw new NotFoundException("Entry not found or unresolvable: {$id}");
    }

    /**
     * Check if an entry is bound.
     */
    public function has(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    /**
     * Check if a singleton instance is already created.
     */
    public function hasInstance(string $id): bool
    {
        return isset($this->instances[$id]);
    }

    /**
     * Bind a transient dependency.
     */
    public function set(string $id, mixed $concrete = null): void
    {
        if ($concrete === null) {
            $concrete = $id;
        }

        unset($this->instances[$id]);

        $this->bindings[$id] = [
            'concrete' => $concrete,
            'shared' => false,
        ];
    }

    /**
     * Bind a singleton dependency.
     */
    public function singleton(string $id, mixed $concrete = null): void
    {
        if ($concrete === null) {
            $concrete = $id;
        }

        unset($this->instances[$id]);

        $this->bindings[$id] = [
            'concrete' => $concrete,
            'shared' => true,
        ];
    }

    /**
     * Register an existing instance as shared in the container.
     */
    public function instance(string $id, mixed $instance): mixed
    {
        $this->instances[$id] = $instance;
        $this->bindings[$id] = [
            'concrete' => $instance,
            'shared' => true,
        ];

        return $instance;
    }

    /**
     * Determine if the given abstract type has been bound.
     */
    public function bound(string $id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    /**
     * Determine if the given abstract type has been bound (alias).
     */
    public function isBound(string $id): bool
    {
        return $this->bound($id);
    }

    /**
     * Resolve a class using reflection autowiring.
     */
    public function resolve(string $class): mixed
    {
        if (!class_exists($class)) {
            throw new ContainerException("Target class [{$class}] does not exist.");
        }

        $reflector = new ReflectionClass($class);

        if (!$reflector->isInstantiable()) {
            throw new ContainerException("Target class [{$class}] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->resolveDependencies($parameters, $class);

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Resolve a parameter list recursively.
     *
     * @param array<ReflectionParameter> $parameters
     * @param string $class
     * @return array<mixed>
     */
    protected function resolveDependencies(array $parameters, string $class): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $type = $parameter->getType();

            if ($type === null) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }
                throw new ContainerException(
                    "Cannot resolve parameter [{$parameter->getName()}] with no type in class [{$class}]."
                );
            }

            if ($type instanceof ReflectionNamedType) {
                if (!$type->isBuiltin()) {
                    $dependencies[] = $this->get($type->getName());
                    continue;
                }
            } elseif ($type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType) {
                // Try resolving class types in a union or intersection
                $resolved = false;
                foreach ($type->getTypes() as $subType) {
                    if ($subType instanceof ReflectionNamedType && !$subType->isBuiltin()) {
                        try {
                            $dependencies[] = $this->get($subType->getName());
                            $resolved = true;
                            break;
                        } catch (\Throwable) {
                            // Continue to next type if resolution fails
                        }
                    }
                }
                if ($resolved) {
                    continue;
                }
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                $dependencies[] = null;
            } else {
                throw new ContainerException(
                    "Cannot resolve primitive or uninstantiable parameter [{$parameter->getName()}] in class [{$class}]."
                );
            }
        }

        return $dependencies;
    }
}
