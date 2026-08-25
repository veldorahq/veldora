<?php

declare(strict_types=1);

namespace Veldora\Framework\Mail;

class PendingMail
{
    /**
     * @var array<string, string|null>
     */
    protected array $to = [];

    /**
     * @var array<string, string|null>
     */
    protected array $cc = [];

    /**
     * @var array<string, string|null>
     */
    protected array $bcc = [];

    /**
     * Create a new PendingMail instance.
     */
    public function __construct(protected Mailer $mailer)
    {
    }

    /**
     * Set the recipients.
     */
    public function to(mixed $users, ?string $name = null): static
    {
        if (is_array($users)) {
            foreach ($users as $key => $val) {
                if (is_numeric($key)) {
                    $this->to[$val] = null;
                } else {
                    $this->to[$key] = $val;
                }
            }
        } elseif (is_string($users)) {
            $this->to[$users] = $name;
        } elseif (is_object($users) && isset($users->email)) {
            $this->to[$users->email] = $users->name ?? null;
        }

        return $this;
    }

    /**
     * Set CC recipients.
     */
    public function cc(mixed $users, ?string $name = null): static
    {
        if (is_array($users)) {
            foreach ($users as $key => $val) {
                if (is_numeric($key)) {
                    $this->cc[$val] = null;
                } else {
                    $this->cc[$key] = $val;
                }
            }
        } elseif (is_string($users)) {
            $this->cc[$users] = $name;
        }

        return $this;
    }

    /**
     * Set BCC recipients.
     */
    public function bcc(mixed $users, ?string $name = null): static
    {
        if (is_array($users)) {
            foreach ($users as $key => $val) {
                if (is_numeric($key)) {
                    $this->bcc[$val] = null;
                } else {
                    $this->bcc[$key] = $val;
                }
            }
        } elseif (is_string($users)) {
            $this->bcc[$users] = $name;
        }

        return $this;
    }

    /**
     * Send the mailable.
     */
    public function send(Mailable $mailable): bool
    {
        $this->applyRecipients($mailable);
        return $this->mailer->send($mailable);
    }

    /**
     * Push the mailable onto the queue.
     */
    public function queue(Mailable $mailable, string $queue = 'default', int $delay = 0): mixed
    {
        $this->applyRecipients($mailable);
        return $this->mailer->queue($mailable, $queue, $delay);
    }

    /**
     * Apply pending recipients onto the mailable.
     */
    protected function applyRecipients(Mailable $mailable): void
    {
        if (!empty($this->to)) {
            $mailable->toAddresses = array_merge($mailable->toAddresses, $this->to);
        }
        if (!empty($this->cc)) {
            $mailable->ccAddresses = array_merge($mailable->ccAddresses, $this->cc);
        }
        if (!empty($this->bcc)) {
            $mailable->bccAddresses = array_merge($mailable->bccAddresses, $this->bcc);
        }
    }
}
