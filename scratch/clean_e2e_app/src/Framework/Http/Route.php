<?php

declare(strict_types=1);

namespace Veldora\Framework\Http;

use Closure;

class Route
{
    /**
     * The name of the route.
     */
    protected ?string $name = null;

    /**
     * The middleware stack for the route.
     *
     * @var array<mixed>
     */
    protected array $middleware = [];

    /**
     * The parameters extracted from the route.
     *
     * @var array<string, string>
     */
    protected array $parameters = [];

    /**
     * Create a new Route instance.
     *
     * @param array<string> $methods
     * @param string $uri
     * @param Closure|array<mixed>|string $action
     */
    public function __construct(
        protected array $methods,
        protected string $uri,
        protected mixed $action
    ) {
        $this->uri = '/' . trim($uri, '/');
    }

    /**
     * Check if the route matches the given request path and method.
     */
    public function matches(string $path, string $method): bool
    {
        if (!in_array($method, $this->methods, true)) {
            return false;
        }

        $pattern = $this->compile();

        if (preg_match($pattern, $path, $matches)) {
            $this->parameters = array_filter(
                $matches,
                fn($key) => is_string($key),
                ARRAY_FILTER_USE_KEY
            );
            return true;
        }

        return false;
    }

    /**
     * Compile the route URI to a regex pattern.
     */
    protected function compile(): string
    {
        $uri = $this->uri;

        // Compile optional parameters e.g. {id?}
        $uri = preg_replace('/\/\{([a-zA-Z_][a-zA-Z0-9_-]*)\?\}/', '(?:/(?P<$1>[^/]+))?', $uri);

        // Compile required parameters e.g. {id}
        $uri = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_-]*)\}/', '(?P<$1>[^/]+)', $uri);

        return '#^' . $uri . '$#';
    }

    /**
     * Set the name of the route.
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Add middleware to the route.
     *
     * @param string|array<mixed> $middleware
     */
    public function middleware(string|array $middleware): self
    {
        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            $this->middleware[] = $middleware;
        }
        return $this;
    }

    /**
     * Get the route name.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the route middleware.
     *
     * @return array<mixed>
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Get the parameters extracted from the URI.
     *
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get the route action (Closure, Controller array, or string).
     */
    public function getAction(): mixed
    {
        return $this->action;
    }

    /**
     * Get the HTTP methods this route responds to.
     *
     * @return array<string>
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Get the route URI pattern.
     */
    public function getUri(): string
    {
        return $this->uri;
    }
}
