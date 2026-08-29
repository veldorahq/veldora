<?php

declare(strict_types=1);

namespace Veldora\Framework\Mail\Transport;

use Veldora\Framework\Mail\MailMessage;

class ArrayTransport implements TransportInterface
{
    /**
     * The collection of sent messages.
     *
     * @var array<int, MailMessage>
     */
    protected array $messages = [];

    /**
     * Send the message by recording it in memory.
     */
    public function send(MailMessage $message): bool
    {
        $this->messages[] = $message;
        return true;
    }

    /**
     * Get all recorded messages.
     *
     * @return array<int, MailMessage>
     */
    public function messages(): array
    {
        return $this->messages;
    }

    /**
     * Get the last recorded message.
     */
    public function lastMessage(): ?MailMessage
    {
        $count = count($this->messages);
        return $count > 0 ? $this->messages[$count - 1] : null;
    }

    /**
     * Flush all recorded messages.
     */
    public function flush(): void
    {
        $this->messages = [];
    }

    /**
     * Get count of sent messages.
     */
    public function count(): int
    {
        return count($this->messages);
    }
}
