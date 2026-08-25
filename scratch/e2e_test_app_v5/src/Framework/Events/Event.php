<?php

declare(strict_types=1);

namespace Veldora\Framework\Events;

/**
 * Base Event class.
 *
 * All application events should extend this class.
 *
 * Usage:
 *   class UserRegistered extends Event {
 *       public function __construct(public readonly User $user) {}
 *   }
 *   Event::dispatch(new UserRegistered($user));
 */
abstract class Event
{
    /**
     * Whether propagation has been stopped.
     */
    protected bool $propagationStopped = false;

    /**
     * Dispatch this event via the application dispatcher.
     *
     * @return array<mixed> Listener results
     */
    public static function dispatch(mixed ...$args): array
    {
        /** @var static $event */
        $event = new static(...$args);

        /** @var EventDispatcher $dispatcher */
        $dispatcher = app(EventDispatcher::class);

        return $dispatcher->dispatch($event);
    }

    /**
     * Stop event propagation to subsequent listeners.
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * Check if propagation has been stopped.
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Get the event name (FQCN by default).
     */
    public function getName(): string
    {
        return static::class;
    }
}
