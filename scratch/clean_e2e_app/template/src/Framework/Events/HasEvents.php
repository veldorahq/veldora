<?php

declare(strict_types=1);

namespace Veldora\Framework\Events;

trait HasEvents
{
    /**
     * Dispatch an event.
     *
     * @return array<mixed>
     */
    protected function fireEvent(string|object $event, mixed $payload = []): array
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = app(EventDispatcher::class);

        return $dispatcher->dispatch($event, $payload);
    }
}
