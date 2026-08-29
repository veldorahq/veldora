<?php

declare(strict_types=1);

namespace Veldora\Framework\Mail;

use Veldora\Framework\Queue\Job;

abstract class Mailable extends Job
{
    /**
     * The person the message should be from.
     *
     * @var array{email: string, name: ?string}|null
     */
    public ?array $fromAddress = null;

    /**
     * The recipients of the message.
     *
     * @var array<string, string|null>
     */
    public array $toAddresses = [];

    /**
     * The recipients of the message.
     *
     * @var array<string, string|null>
     */
    public array $ccAddresses = [];

    /**
     * The recipients of the message.
     *
     * @var array<string, string|null>
     */
    public array $bccAddresses = [];

    /**
     * The reply-to address.
     *
     * @var array{email: string, name: ?string}|null
     */
    public ?array $replyToAddress = null;

    /**
     * The subject of the message.
     */
    public string $subjectLine = '';

    /**
     * The view template to use for the message.
     */
    public ?string $viewTemplate = null;

    /**
     * Plain text view template.
     */
    public ?string $textViewTemplate = null;

    /**
     * Raw HTML content.
     */
    public ?string $rawHtml = null;

    /**
     * Raw plain text content.
     */
    public ?string $rawText = null;

    /**
     * The view data for the message.
     *
     * @var array<string, mixed>
     */
    public array $viewData = [];

    /**
     * The attachments for the message.
     *
     * @var array<array{path: string, name: string, mime: string}>
     */
    public array $attachmentsList = [];

    /**
     * Build the message.
     */
    public function build(): static
    {
        return $this;
    }

    /**
     * Set the sender of the message.
     */
    public function from(string $address, ?string $name = null): static
    {
        $this->fromAddress = ['email' => $address, 'name' => $name];
        return $this;
    }

    /**
     * Set the recipient of the message.
     */
    public function to(string|array $address, ?string $name = null): static
    {
        if (is_array($address)) {
            foreach ($address as $key => $val) {
                if (is_numeric($key)) {
                    $this->toAddresses[$val] = null;
                } else {
                    $this->toAddresses[$key] = $val;
                }
            }
        } else {
            $this->toAddresses[$address] = $name;
        }

        return $this;
    }

    /**
     * Set CC recipients.
     */
    public function cc(string|array $address, ?string $name = null): static
    {
        if (is_array($address)) {
            foreach ($address as $key => $val) {
                if (is_numeric($key)) {
                    $this->ccAddresses[$val] = null;
                } else {
                    $this->ccAddresses[$key] = $val;
                }
            }
        } else {
            $this->ccAddresses[$address] = $name;
        }

        return $this;
    }

    /**
     * Set BCC recipients.
     */
    public function bcc(string|array $address, ?string $name = null): static
    {
        if (is_array($address)) {
            foreach ($address as $key => $val) {
                if (is_numeric($key)) {
                    $this->bccAddresses[$val] = null;
                } else {
                    $this->bccAddresses[$key] = $val;
                }
            }
        } else {
            $this->bccAddresses[$address] = $name;
        }

        return $this;
    }

    /**
     * Set Reply-To address.
     */
    public function replyTo(string $address, ?string $name = null): static
    {
        $this->replyToAddress = ['email' => $address, 'name' => $name];
        return $this;
    }

    /**
     * Set the subject of the message.
     */
    public function subject(string $subject): static
    {
        $this->subjectLine = $subject;
        return $this;
    }

    /**
     * Set the view template.
     *
     * @param array<string, mixed> $data
     */
    public function view(string $view, array $data = []): static
    {
        $this->viewTemplate = $view;
        $this->viewData = array_merge($this->viewData, $data);
        return $this;
    }

    /**
     * Set raw HTML body.
     */
    public function html(string $html): static
    {
        $this->rawHtml = $html;
        return $this;
    }

    /**
     * Set raw text body.
     */
    public function text(string $text): static
    {
        $this->rawText = $text;
        return $this;
    }

    /**
     * Add view data.
     *
     * @param string|array<string, mixed> $key
     */
    public function with(string|array $key, mixed $value = null): static
    {
        if (is_array($key)) {
            $this->viewData = array_merge($this->viewData, $key);
        } else {
            $this->viewData[$key] = $value;
        }

        return $this;
    }

    /**
     * Attach a file.
     */
    public function attach(string $file, array $options = []): static
    {
        $this->attachmentsList[] = [
            'path' => $file,
            'name' => $options['as'] ?? basename($file),
            'mime' => $options['mime'] ?? 'application/octet-stream',
        ];

        return $this;
    }

    /**
     * When executed as a queued Job.
     */
    public function handle(): void
    {
        /** @var Mailer $mailer */
        $mailer = app(Mailer::class);
        $mailer->send($this);
    }

    /**
     * Send this mailable directly.
     */
    public function send(?Mailer $mailer = null): bool
    {
        $mailer = $mailer ?: app(Mailer::class);
        return $mailer->send($this);
    }

    /**
     * Queue this mailable for background processing.
     */
    public function queue(?string $queue = 'default', int $delay = 0): mixed
    {
        /** @var Mailer $mailer */
        $mailer = app(Mailer::class);
        return $mailer->queue($this, $queue ?: 'default', $delay);
    }
}
