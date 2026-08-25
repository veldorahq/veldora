<?php

declare(strict_types=1);

namespace Veldora\Framework\Events;

use Closure;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use Veldora\Framework\Foundation\Application;

class EventDispatcher
{
    /**
     * The registered event listeners.
     *
     * @var array<string, array<int, array{listener: Closure|string|array<mixed>, priority: int}>>
     */
    protected array $listeners = [];

    /**
     * Wildcard event listeners.
     *
     * @var array<string, array<int, array{listener: Closure|string|array<mixed>, priority: int}>>
     */
    protected array $wildcards = [];

    /**
     * Cached sorted listeners.
     *
     * @var array<string, array<int, callable>>
     */
    protected array $sorted = [];

    /**
     * Create a new EventDispatcher instance.
     */
    public function __construct(protected ?Application $container = null)
    {
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param string|array<string> $events
     * @param Closure|string|array<mixed> $listener
     */
    public function listen(string|array $events, Closure|string|array $listener, int $priority = 0): void
    {
        foreach ((array) $events as $event) {
            if (str_contains($event, '*')) {
                $this->setupWildcardListener($event, $listener, $priority);
            } else {
                $this->listeners[$event][] = [
                    'listener' => $listener,
                    'priority' => $priority,
                ];
                unset($this->sorted[$event]);
            }
        }
    }

    /**
     * Register a subscriber with the dispatcher.
     */
    public function subscribe(string|EventSubscriberInterface $subscriber): void
    {
        $resolved = is_string($subscriber) ? $this->resolveListener($subscriber) : $subscriber;

        if ($resolved instanceof EventSubscriberInterface) {
            $events = $resolved->subscribe($this);
            foreach ($events as $event => $method) {
                if (is_string($method)) {
                    $this->listen($event, [$resolved, $method]);
                } elseif (is_array($method)) {
                    $priority = $method[1] ?? 0;
                    $this->listen($event, [$resolved, $method[0]], (int) $priority);
                }
            }
        }
    }

    /**
     * Dispatch an event and call the listeners.
     *
     * @param string|object $event
     * @param mixed $payload
     * @return array<mixed>
     */
    public function dispatch(string|object $event, mixed $payload = []): array
    {
        $eventName = is_object($event) ? $event::class : (string) $event;
        $eventObject = is_object($event) ? $event : null;

        $responses = [];

        foreach ($this->getListenersWithMetadata($eventName) as $entry) {
            if ($eventObject instanceof Event && $eventObject->isPropagationStopped()) {
                break;
            }

            $response = $this->executeListener($entry['callable'], $event, $payload, $entry['is_wildcard']);

            if ($response === false) {
                if ($eventObject instanceof Event) {
                    $eventObject->stopPropagation();
                }
                break;
            }

            $responses[] = $response;
        }

        return $responses;
    }

    /**
     * Determine if a given event has listeners.
     */
    public function hasListeners(string $event): bool
    {
        return !empty($this->listeners[$event]) || !empty($this->wildcards);
    }

    /**
     * Get all listeners with metadata for a given event name.
     *
     * @return array<int, array{callable: callable, is_wildcard: bool}>
     */
    public function getListenersWithMetadata(string $event): array
    {
        $rawListeners = [];

        foreach ($this->listeners[$event] ?? [] as $entry) {
            $rawListeners[] = [
                'entry' => $entry,
                'is_wildcard' => false,
            ];
        }

        // Check wildcards
        foreach ($this->wildcards as $pattern => $wildcardListeners) {
            if ($this->matchWildcard($pattern, $event)) {
                foreach ($wildcardListeners as $entry) {
                    $rawListeners[] = [
                        'entry' => $entry,
                        'is_wildcard' => true,
                    ];
                }
            }
        }

        // Sort by priority descending
        usort($rawListeners, fn ($a, $b) => $b['entry']['priority'] <=> $a['entry']['priority']);

        $callables = [];
        foreach ($rawListeners as $item) {
            $callables[] = [
                'callable' => $this->makeListenerCallable($item['entry']['listener']),
                'is_wildcard' => $item['is_wildcard'],
            ];
        }

        return $callables;
    }

    /**
     * Get all listeners for a given event name.
     *
     * @return array<int, callable>
     */
    public function getListeners(string $event): array
    {
        if (isset($this->sorted[$event])) {
            return $this->sorted[$event];
        }

        $withMeta = $this->getListenersWithMetadata($event);
        $callables = array_map(fn ($item) => $item['callable'], $withMeta);

        return $this->sorted[$event] = $callables;
    }

    /**
     * Forget all listeners for a given event.
     */
    public function forget(string $event): void
    {
        unset($this->listeners[$event], $this->sorted[$event]);
    }

    /**
     * Flush all listeners from the dispatcher.
     */
    public function flush(): void
    {
        $this->listeners = [];
        $this->wildcards = [];
        $this->sorted = [];
    }

    /**
     * Execute a single listener callable with parameters.
     */
    protected function executeListener(callable $listener, string|object $event, mixed $payload, bool $isWildcard = false): mixed
    {
        if (is_object($event)) {
            return $listener($event, $payload);
        }

        if ($isWildcard) {
            return $listener($event, $payload);
        }

        if (is_array($payload) && array_is_list($payload)) {
            return $listener(...$payload);
        }

        return $listener($payload);
    }

    /**
     * Make a listener into a PHP callable.
     */
    protected function makeListenerCallable(mixed $listener): callable
    {
        if ($listener instanceof Closure) {
            return $listener;
        }

        if (is_array($listener) && is_object($listener[0])) {
            return $listener;
        }

        if (is_array($listener) && is_string($listener[0])) {
            $instance = $this->resolveListener($listener[0]);
            return [$instance, $listener[1]];
        }

        if (is_string($listener)) {
            if (str_contains($listener, '@')) {
                [$class, $method] = explode('@', $listener, 2);
                $instance = $this->resolveListener($class);
                return [$instance, $method];
            }

            $instance = $this->resolveListener($listener);
            if ($instance instanceof Listener) {
                return [$instance, 'handle'];
            }
            if (method_exists($instance, 'handle')) {
                return [$instance, 'handle'];
            }
            if (is_callable($instance)) {
                return $instance;
            }
        }

        if (is_callable($listener)) {
            return $listener;
        }

        throw new \InvalidArgumentException("Invalid event listener provided.");
    }

    /**
     * Resolve a listener class from the container if available.
     */
    protected function resolveListener(string $class): object
    {
        if ($this->container !== null && $this->container->has($class)) {
            return $this->container->get($class);
        }

        if ($this->container !== null && method_exists($this->container, 'make')) {
            return $this->container->make($class);
        }

        return new $class();
    }

    /**
     * Set up a wildcard listener.
     */
    protected function setupWildcardListener(string $event, mixed $listener, int $priority): void
    {
        $this->wildcards[$event][] = [
            'listener' => $listener,
            'priority' => $priority,
        ];
        $this->sorted = [];
    }

    /**
     * Match a wildcard pattern against an event name.
     */
    protected function matchWildcard(string $pattern, string $event): bool
    {
        $regex = str_replace(['\*', '\.'], ['.*', '\.'], preg_quote($pattern, '/'));
        return (bool) preg_match('/^' . $regex . '$/u', $event);
    }
}
