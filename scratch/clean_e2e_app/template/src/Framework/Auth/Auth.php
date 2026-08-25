<?php

declare(strict_types=1);

namespace Veldora\Framework\Auth;

use Veldora\Framework\Foundation\Application;

class Auth
{
    /**
     * Dynamically proxy static calls to the AuthManager singleton.
     *
     * @param array<mixed> $parameters
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        /** @var AuthManager $manager */
        $manager = Application::getInstance()->get(AuthManager::class);
        /** @var callable $callable */
        $callable = [$manager, $method];
        return $callable(...$parameters);
    }
}
