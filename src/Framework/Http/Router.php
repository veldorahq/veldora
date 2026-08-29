<?php

declare(strict_types=1);

namespace Veldora\Framework\Http;

use Closure;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use RuntimeException;
use Veldora\Framework\Foundation\ContainerInterface;
use Veldora\Framework\Foundation\Exception\NotFoundException;

class Router
{
    /**
     * All registered routes.
     *
     * @var array<Route>
     */
    protected array $routes = [];

    /**
     * The attributes for the current route group.
     *
     * @var array<array<string, mixed>>
     */
    protected array $groupStack = [];

    /**
     * Named middleware aliases resolving to FQCN.
     *
     * @var array<string, string>
     */
    protected array $middlewareAliases = [
        'auth'          => \Veldora\Framework\Http\Middleware\Authenticate::class,
        'guest'         => \Veldora\Framework\Http\Middleware\RedirectIfAuthenticated::class,
        'verified'      => \Veldora\Framework\Http\Middleware\EnsureEmailIsVerified::class,
        'admin'         => \Veldora\Framework\Http\Middleware\EnsureUserIsAdmin::class,
        'csrf'          => \Veldora\Framework\Http\Middleware\VerifyCsrfToken::class,
        'start_session' => \Veldora\Framework\Http\Middleware\StartSession::class,
    ];

    /**
     * Global middleware applied to every request.
     *
     * @var array<string>
     */
    protected array $globalMiddleware = [
        \Veldora\Framework\Http\Middleware\CheckForMaintenanceMode::class,
        \Veldora\Framework\Http\Middleware\StartSession::class,
    ];

    /**
     * Create a new Router instance.
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * Register a custom middleware alias.
     */
    public function aliasMiddleware(string $alias, string $className): self
    {
        $this->middlewareAliases[$alias] = $className;
        return $this;
    }

    /**
     * Prepend global middleware classes.
     *
     * @param array<string> $middleware
     */
    public function withGlobalMiddleware(array $middleware): self
    {
        $this->globalMiddleware = array_unique(array_merge($middleware, $this->globalMiddleware));
        return $this;
    }

    /**
     * Remove a class from the global middleware stack.
     */
    public function withoutGlobalMiddleware(string $className): self
    {
        $this->globalMiddleware = array_values(array_filter(
            $this->globalMiddleware,
            fn($m) => $m !== $className
        ));
        return $this;
    }

    /**
     * Resolve middleware names to their FQCN.
     *
     * @param array<mixed> $middleware
     * @return array<string>
     */
    protected function resolveMiddleware(array $middleware): array
    {
        $resolved = [];
        foreach ($middleware as $item) {
            if (is_string($item) && isset($this->middlewareAliases[$item])) {
                $resolved[] = $this->middlewareAliases[$item];
            } else {
                $resolved[] = (string) $item;
            }
        }
        return $resolved;
    }

    /**
     * Register a GET route.
     */
    public function get(string $uri, mixed $action): Route
    {
        return $this->addRoute(['GET', 'HEAD'], $uri, $action);
    }

    /**
     * Register a POST route.
     */
    public function post(string $uri, mixed $action): Route
    {
        return $this->addRoute(['POST'], $uri, $action);
    }

    /**
     * Register a PUT route.
     */
    public function put(string $uri, mixed $action): Route
    {
        return $this->addRoute(['PUT'], $uri, $action);
    }

    /**
     * Register a DELETE route.
     */
    public function delete(string $uri, mixed $action): Route
    {
        return $this->addRoute(['DELETE'], $uri, $action);
    }

    /**
     * Register a PATCH route.
     */
    public function patch(string $uri, mixed $action): Route
    {
        return $this->addRoute(['PATCH'], $uri, $action);
    }

    /**
     * Register a route matching any HTTP verb.
     */
    public function any(string $uri, mixed $action): Route
    {
        return $this->addRoute(['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'PATCH'], $uri, $action);
    }

    /**
     * Create a route group with shared attributes.
     *
     * @param array<string, mixed> $attributes
     */
    public function group(array $attributes, Closure $callback): void
    {
        $this->updateGroupStack($attributes);

        $callback($this);

        array_pop($this->groupStack);
    }

    /**
     * Update the active route group stack.
     *
     * @param array<string, mixed> $attributes
     */
    protected function updateGroupStack(array $attributes): void
    {
        if (!empty($this->groupStack)) {
            $last = end($this->groupStack);
            
            $prefix = isset($last['prefix']) ? rtrim((string) $last['prefix'], '/') : '';
            $newPrefix = isset($attributes['prefix']) ? '/' . trim((string) $attributes['prefix'], '/') : '';
            $attributes['prefix'] = $prefix . $newPrefix;

            $middleware = (array) ($last['middleware'] ?? []);
            $newMiddleware = (array) ($attributes['middleware'] ?? []);
            $attributes['middleware'] = array_merge($middleware, $newMiddleware);
        } else {
            $attributes['prefix'] = isset($attributes['prefix']) ? '/' . trim((string) $attributes['prefix'], '/') : '';
            $attributes['middleware'] = (array) ($attributes['middleware'] ?? []);
        }

        $this->groupStack[] = $attributes;
    }

    /**
     * Add a route definition to the collection.
     *
     * @param array<string> $methods
     */
    protected function addRoute(array $methods, string $uri, mixed $action): Route
    {
        $groupAttributes = end($this->groupStack);

        if ($groupAttributes) {
            $prefix = $groupAttributes['prefix'] ?? '';
            $uri = rtrim((string) $prefix, '/') . '/' . ltrim($uri, '/');
        }

        $route = new Route($methods, $uri, $action);

        if ($groupAttributes && !empty($groupAttributes['middleware'])) {
            $route->middleware((array) $groupAttributes['middleware']);
        }

        $this->routes[] = $route;

        return $route;
    }

    /**
     * Generate a URL for a named route with optional parameter substitution.
     *
     * @param array<string, mixed> $parameters
     */
    public function url(string $name, array $parameters = []): string
    {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                $uri = $route->getUri();
                foreach ($parameters as $key => $value) {
                    $uri = str_replace(['{' . $key . '}', '{' . $key . '?}'], (string) $value, $uri);
                }
                // Remove any unresolved optional parameters
                $uri = preg_replace('/\/\{[a-zA-Z0-9_]+\?\}/', '', $uri) ?? $uri;
                return $uri;
            }
        }

        throw new \InvalidArgumentException("Route [{$name}] not defined.");
    }

    /**
     * Dispatch the request through the router and return a response.
     */
    public function dispatch(Request $request): Response
    {
        $pipeline = new Pipeline($this->container);

        return $pipeline
            ->send($request)
            ->through($this->globalMiddleware)
            ->then(function (Request $request): Response {
                $path = $request->getPath();
                $method = $request->getMethod();

                foreach ($this->routes as $route) {
                    if ($route->matches($path, $method)) {
                        return $this->runRoute($route, $request);
                    }
                }

                throw new NotFoundException("Route not found: [{$method}] {$path}");
            });
    }

    /**
     * Execute the matched route's action through the route middleware pipeline.
     */
    protected function runRoute(Route $route, Request $request): Response
    {
        $pipeline = new Pipeline($this->container);

        $routeMiddleware = $this->resolveMiddleware($route->getMiddleware());

        $response = $pipeline
            ->send($request)
            ->through($routeMiddleware)
            ->then(function (Request $request) use ($route) {
                $response = $this->runAction(
                    $route->getAction(),
                    $route->getParameters(),
                    $request
                );

                if ($response instanceof Response) {
                    return $response;
                }

                if (is_array($response) || $response instanceof \JsonSerializable) {
                    return Response::json((array) $response);
                }

                return new Response((string) $response);
            });

        if ($this->container->has(\Veldora\Framework\Http\CookieJar::class)) {
            /** @var \Veldora\Framework\Http\CookieJar $cookieJar */
            $cookieJar = $this->container->get(\Veldora\Framework\Http\CookieJar::class);
            foreach ($cookieJar->flushQueuedCookies() as $cookie) {
                if ($cookie['signed']) {
                    $appKey = config('app.key', 'default-key');
                    $signedValue = $cookie['value'] . '.' . hash_hmac('sha256', $cookie['value'], $appKey);
                    $response->cookie(
                        $cookie['name'],
                        $signedValue,
                        $cookie['minutes'],
                        $cookie['path'],
                        $cookie['domain'],
                        $cookie['secure'],
                        $cookie['httpOnly'],
                        $cookie['sameSite']
                    );
                } else {
                    $response->cookie(
                        $cookie['name'],
                        $cookie['value'],
                        $cookie['minutes'],
                        $cookie['path'],
                        $cookie['domain'],
                        $cookie['secure'],
                        $cookie['httpOnly'],
                        $cookie['sameSite']
                    );
                }
            }
        }

        return $response;
    }

    /**
     * Execute the action handler for a matched route.
     *
     * @param array<string, string> $parameters
     */
    protected function runAction(mixed $action, array $parameters, Request $request): mixed
    {
        if ($action instanceof Closure) {
            return $this->resolveCallable($action, $parameters, $request);
        }

        if (is_array($action)) {
            [$controllerClass, $method] = $action;
            $controller = $this->container->get($controllerClass);

            if (!method_exists($controller, $method)) {
                throw new RuntimeException("Method [{$method}] does not exist on controller [{$controllerClass}].");
            }

            $reflector = new ReflectionMethod($controller, $method);
            $args = $this->resolveMethodDependencies($reflector->getParameters(), $parameters, $request);

            return $reflector->invokeArgs($controller, $args);
        }

        if (is_string($action) && str_contains($action, '@')) {
            [$controllerClass, $method] = explode('@', $action);
            $controller = $this->container->get($controllerClass);

            if (!method_exists($controller, $method)) {
                throw new RuntimeException("Method [{$method}] does not exist on controller [{$controllerClass}].");
            }

            $reflector = new ReflectionMethod($controller, $method);
            $args = $this->resolveMethodDependencies($reflector->getParameters(), $parameters, $request);

            return $reflector->invokeArgs($controller, $args);
        }

        throw new InvalidArgumentException('Invalid route action handler.');
    }

    /**
     * Resolve Closure dependencies via reflection.
     *
     * @param array<string, string> $parameters
     */
    protected function resolveCallable(Closure $closure, array $parameters, Request $request): mixed
    {
        $reflector = new ReflectionFunction($closure);
        $args = $this->resolveMethodDependencies($reflector->getParameters(), $parameters, $request);
        return $closure(...$args);
    }

    /**
     * Map request and route parameters to method/closure parameters.
     *
     * @param array<\ReflectionParameter> $reflectionParameters
     * @param array<string, string> $routeParameters
     * @return array<int, mixed>
     */
    protected function resolveMethodDependencies(
        array $reflectionParameters,
        array $routeParameters,
        Request $request
    ): array {
        $args = [];

        foreach ($reflectionParameters as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && $type->getName() === Request::class) {
                $args[] = $request;
                continue;
            }

            if (array_key_exists($name, $routeParameters)) {
                $args[] = $routeParameters[$name];
                continue;
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $args[] = $this->container->get($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                $args[] = null;
            } else {
                throw new RuntimeException("Unresolvable route action parameter [{$name}].");
            }
        }

        return $args;
    }

    /**
     * Generate URL for a named route.
     *
     * @param array<string, mixed> $parameters
     */
    public function generateUrl(string $name, array $parameters = []): string
    {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                $uri = $route->getUri();
                foreach ($parameters as $key => $value) {
                    $uri = str_replace(['{' . $key . '}', '{' . $key . '?}'], (string) $value, $uri);
                }
                $uri = preg_replace('/\/\{[a-zA-Z_][a-zA-Z0-9_-]*\?\}/', '', $uri);
                return $uri;
            }
        }

        throw new InvalidArgumentException("Route [{$name}] is not defined.");
    }

    /**
     * Get all registered routes.
     *
     * @return array<Route>
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
