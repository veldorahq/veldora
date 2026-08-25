<?php

declare(strict_types=1);

namespace Veldora\Framework\Auth;

use InvalidArgumentException;
use Veldora\Framework\Foundation\Application;
use Veldora\Framework\Session\Session;
use Veldora\Framework\Http\CookieJar;

class AuthManager
{
    /**
     * The active resolved guards.
     *
     * @var array<string, GuardInterface>
     */
    protected array $guards = [];

    /**
     * Create a new AuthManager instance.
     */
    public function __construct(protected Application $app)
    {
    }

    /**
     * Get a guard instance by name.
     */
    public function guard(?string $name = null): GuardInterface
    {
        $name = $name ?: $this->getDefaultDriver();

        if (!isset($this->guards[$name])) {
            $this->guards[$name] = $this->resolve($name);
        }

        return $this->guards[$name];
    }

    /**
     * Resolve the given guard.
     */
    protected function resolve(string $name): GuardInterface
    {
        if ($name === 'web') {
            /** @var Session $session */
            $session = $this->app->get(Session::class);
            /** @var CookieJar $cookieJar */
            $cookieJar = $this->app->get(CookieJar::class);
            
            /** @var class-string<\Veldora\Framework\Database\Model> $userModel */
            $userModel = config('auth.providers.users.model', 'App\\Models\\User');

            return new SessionGuard($session, $userModel, $cookieJar);
        }

        throw new InvalidArgumentException("Auth guard [{$name}] is not defined.");
    }

    /**
     * Get the default driver/guard name.
     */
    public function getDefaultDriver(): string
    {
        /** @var string $driver */
        $driver = config('auth.default', 'web');
        return $driver;
    }

    /**
     * Dynamically delegate method calls to the default guard.
     *
     * @param array<mixed> $parameters
     */
    public function __call(string $method, array $parameters): mixed
    {
        /** @var callable $callable */
        $callable = [$this->guard(), $method];
        return $callable(...$parameters);
    }
}
