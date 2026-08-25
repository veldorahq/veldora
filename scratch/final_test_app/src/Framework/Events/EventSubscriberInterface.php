<?php

declare(strict_types=1);

namespace Veldora\Framework\Events;

/**
 * Event Subscriber Interface.
 *
 * Allows a subscriber class to subscribe to multiple events with different methods.
 */
interface EventSubscriberInterface
{
    /**
     * Subscribe to events on the dispatcher.
     *
     * @return array<string, string|array<string|int, string|int>>
     */
    public function subscribe(EventDispatcher $events): array;
}
