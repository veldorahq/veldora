<?php

declare(strict_types=1);

namespace Psr\Container {
    if (!interface_exists(ContainerInterface::class, false)) {
        interface ContainerInterface
        {
            public function get(string $id);
            public function has(string $id): bool;
        }
    }
    if (!interface_exists(NotFoundExceptionInterface::class, false)) {
        interface NotFoundExceptionInterface extends \Throwable
        {
        }
    }
    if (!interface_exists(ContainerExceptionInterface::class, false)) {
        interface ContainerExceptionInterface extends \Throwable
        {
        }
    }
}

namespace Veldora\Framework\Foundation {

    use Psr\Container\ContainerInterface as PsrContainerInterface;

    interface ContainerInterface extends PsrContainerInterface
    {
        /**
         * Finds an entry of the container by its identifier and returns it.
         */
        public function get(string $id): mixed;

        /**
         * Returns true if the container can return an entry for the given identifier.
         */
        public function has(string $id): bool;

        /**
         * Bind a transient dependency (new instance generated on every resolution).
         */
        public function set(string $id, mixed $concrete = null): void;

        /**
         * Bind a singleton dependency (cached instance returned on subsequent resolutions).
         */
        public function singleton(string $id, mixed $concrete = null): void;

        /**
         * Resolve a class using reflection autowiring.
         *
         * @template T
         * @param class-string<T> $class
         * @return T
         */
        public function resolve(string $class): mixed;
    }
}
