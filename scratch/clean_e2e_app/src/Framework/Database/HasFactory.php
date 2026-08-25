<?php

declare(strict_types=1);

namespace Veldora\Framework\Database;

use Veldora\Framework\Database\Factories\Factory;

trait HasFactory
{
    /**
     * Get a new factory instance for the model.
     */
    public static function factory(int $count = 1): Factory
    {
        $class = (string) strrchr(static::class, '\\');
        $basename = ltrim($class !== '' ? $class : static::class, '\\');
        $factoryClass = 'Database\\Factories\\' . $basename . 'Factory';

        if (class_exists($factoryClass)) {
            /** @var Factory $factory */
            $factory = new $factoryClass();
            return $count > 1 ? $factory->count($count) : $factory;
        }

        throw new \RuntimeException("Factory [{$factoryClass}] not found for model [" . static::class . "].");
    }
}
