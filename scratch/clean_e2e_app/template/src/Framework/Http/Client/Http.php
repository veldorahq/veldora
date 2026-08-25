<?php

declare(strict_types=1);

namespace Veldora\Framework\Http\Client;

class Http
{
    /**
     * The registered fake callbacks or URL mappings.
     *
     * @var array<string|int, callable|Response>|null
     */
    protected static ?array $fakes = null;

    /**
     * Create a new pending request instance.
     */
    public static function create(): PendingRequest
    {
        return new PendingRequest();
    }

    /**
     * Register fake responses for testing.
     *
     * @param array<string|int, callable|Response>|callable|Response $callback
     */
    public static function fake(mixed $callback = null): void
    {
        if ($callback === null) {
            static::$fakes = ['*' => static::response([], 200)];
            return;
        }

        if (is_callable($callback) || $callback instanceof Response) {
            static::$fakes = ['*' => $callback];
            return;
        }

        static::$fakes = (array) $callback;
    }

    /**
     * Clear all fake handlers.
     */
    public static function resetFakes(): void
    {
        static::$fakes = null;
    }

    /**
     * Get a matching fake response if registered.
     */
    public static function getFakeResponse(string $url, string $method): ?Response
    {
        if (static::$fakes === null) {
            return null;
        }

        foreach (static::$fakes as $pattern => $handler) {
            $matched = false;

            if ($pattern === '*' || $pattern === $url) {
                $matched = true;
            } elseif (fnmatch((string) $pattern, $url)) {
                $matched = true;
            }

            if ($matched) {
                if ($handler instanceof Response) {
                    return $handler;
                }

                if (is_callable($handler)) {
                    $res = $handler($url, $method);
                    if ($res instanceof Response) {
                        return $res;
                    }
                    if (is_array($res) || is_string($res)) {
                        return static::response($res);
                    }
                }
            }
        }

        return static::response([], 200);
    }

    /**
     * Create a fake response instance.
     *
     * @param array<mixed>|string $body
     * @param array<string, string> $headers
     */
    public static function response(array|string $body = [], int $status = 200, array $headers = []): Response
    {
        $bodyStr = is_array($body) ? json_encode($body) : (string) $body;
        if (is_array($body) && !isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        return new Response($status, $bodyStr, $headers);
    }

    /**
     * Forward static calls to a new PendingRequest instance.
     *
     * @param array<mixed> $parameters
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        return static::create()->$method(...$parameters);
    }
}
