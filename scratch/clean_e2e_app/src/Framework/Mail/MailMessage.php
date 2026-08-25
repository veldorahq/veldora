<?php

declare(strict_types=1);

namespace Veldora\Framework\Mail;

class MailMessage
{
    /**
     * @var array<string, string|null> Email => Name
     */
    public array $to = [];

    /**
     * @var array{email: string, name: ?string}|null
     */
    public ?array $from = null;

    /**
     * @var array<string, string|null>
     */
    public array $cc = [];

    /**
     * @var array<string, string|null>
     */
    public array $bcc = [];

    /**
     * @var array{email: string, name: ?string}|null
     */
    public ?array $replyTo = null;

    public string $subject = '';
    public string $htmlBody = '';
    public string $textBody = '';

    /**
     * @var array<array{path: string, name: string, mime: string}>
     */
    public array $attachments = [];

    /**
     * @var array<string, string> Custom headers
     */
    public array $headers = [];
}
