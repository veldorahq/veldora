<?php

declare(strict_types=1);

namespace Veldora\Framework\Events;

/**
 * Listener interface.
 *
 * All event listeners must implement this interface.
 *
 * Usage:
 *   class SendWelcomeEmail implements Listener {
 *       public function handle(UserRegistered $event): void {
 *           // send email to $event->user
 *       }
 *   }
 */
interface Listener
{
    /**
     * Handle the event.
     *
     * @param Event $event The dispatched event
     */
    public function handle(Event $event): void;
}
