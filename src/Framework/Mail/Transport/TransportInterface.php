<?php

declare(strict_types=1);

namespace Veldora\Framework\Mail\Transport;

use Veldora\Framework\Mail\MailMessage;

interface TransportInterface
{
    /**
     * Send the given mail message.
     */
    public function send(MailMessage $message): bool;
}
