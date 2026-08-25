<?php

declare(strict_types=1);

namespace Veldora\Framework\Mail;

use Closure;
use InvalidArgumentException;
use Veldora\Framework\Foundation\Application;
use Veldora\Framework\Mail\Transport\ArrayTransport;
use Veldora\Framework\Mail\Transport\PhpMailTransport;
use Veldora\Framework\Mail\Transport\SmtpTransport;
use Veldora\Framework\Mail\Transport\TransportInterface;
use Veldora\Framework\Queue\QueueManager;
use Veldora\Framework\View\Engine;

class Mailer
{
    /**
     * @var array<string, TransportInterface>
     */
    protected array $transports = [];

    /**
     * @var array<string, Closure>
     */
    protected array $customTransports = [];

    /**
     * The default transport name.
     */
    protected ?string $defaultDriver = null;

    /**
     * Create a new Mailer instance.
     */
    public function __construct(
        protected ?Application $app = null,
        protected ?Engine $viewEngine = null
    ) {
    }

    /**
     * Set the default mail driver.
     */
    public function setDefaultDriver(string $driver): static
    {
        $this->defaultDriver = $driver;
        return $this;
    }

    /**
     * Set a transport instance directly.
     */
    public function setTransport(string $name, TransportInterface $transport): static
    {
        $this->transports[$name] = $transport;
        return $this;
    }

    /**
     * Get a transport instance by name.
     */
    public function transport(?string $name = null): TransportInterface
    {
        $name = $name ?: $this->getDefaultDriver();

        if (!isset($this->transports[$name])) {
            $this->transports[$name] = $this->resolveTransport($name);
        }

        return $this->transports[$name];
    }

    /**
     * Begin composing an email to the given recipient(s).
     */
    public function to(mixed $users): PendingMail
    {
        return (new PendingMail($this))->to($users);
    }

    /**
     * Send a mailable message.
     */
    public function send(Mailable $mailable): bool
    {
        $mailable->build();

        $message = new MailMessage();
        $message->to = $mailable->toAddresses;
        $message->from = $mailable->fromAddress ?: $this->getDefaultFrom();
        $message->cc = $mailable->ccAddresses;
        $message->bcc = $mailable->bccAddresses;
        $message->replyTo = $mailable->replyToAddress;
        $message->subject = $mailable->subjectLine;
        $message->attachments = $mailable->attachmentsList;

        // Render HTML content
        if ($mailable->rawHtml !== null) {
            $message->htmlBody = $mailable->rawHtml;
        } elseif ($mailable->viewTemplate !== null) {
            $message->htmlBody = $this->renderView($mailable->viewTemplate, $mailable->viewData);
        }

        // Render Plain text content
        if ($mailable->rawText !== null) {
            $message->textBody = $mailable->rawText;
        } elseif ($mailable->textViewTemplate !== null) {
            $message->textBody = $this->renderView($mailable->textViewTemplate, $mailable->viewData);
        } elseif ($message->htmlBody !== '') {
            $message->textBody = strip_tags($message->htmlBody);
        }

        return $this->transport()->send($message);
    }

    /**
     * Queue a mailable message for background sending.
     */
    public function queue(Mailable $mailable, string $queue = 'default', int $delay = 0): mixed
    {
        /** @var QueueManager $queueManager */
        $queueManager = app(QueueManager::class);
        return $queueManager->push($mailable, $queue, $delay);
    }

    /**
     * Send a raw text message.
     */
    public function raw(string $text, Closure $callback): bool
    {
        $mailable = new class($text, $callback) extends Mailable {
            public function __construct(protected string $textMsg, protected Closure $cb)
            {
            }

            public function build(): static
            {
                $this->text($this->textMsg);
                ($this->cb)($this);
                return $this;
            }
        };

        return $this->send($mailable);
    }

    /**
     * Send a raw HTML message.
     */
    public function html(string $html, Closure $callback): bool
    {
        $mailable = new class($html, $callback) extends Mailable {
            public function __construct(protected string $htmlMsg, protected Closure $cb)
            {
            }

            public function build(): static
            {
                $this->html($this->htmlMsg);
                ($this->cb)($this);
                return $this;
            }
        };

        return $this->send($mailable);
    }

    /**
     * Register a custom transport creator.
     */
    public function extend(string $driver, Closure $callback): static
    {
        $this->customTransports[$driver] = $callback;
        return $this;
    }

    /**
     * Render a view template.
     *
     * @param array<string, mixed> $data
     */
    protected function renderView(string $view, array $data = []): string
    {
        if ($this->viewEngine !== null) {
            return $this->viewEngine->render($view, $data);
        }

        if (function_exists('view')) {
            $response = view($view, $data);
            return $response->getContent();
        }

        return '';
    }

    /**
     * Get the default mail driver from config.
     */
    public function getDefaultDriver(): string
    {
        return config('mail.default', 'mail');
    }

    /**
     * Get default From address from config.
     *
     * @return array{email: string, name: ?string}
     */
    public function getDefaultFrom(): array
    {
        return [
            'email' => config('mail.from.address', 'noreply@veldora.local'),
            'name' => config('mail.from.name', 'Veldora Framework'),
        ];
    }

    /**
     * Resolve a transport by name.
     */
    protected function resolveTransport(string $name): TransportInterface
    {
        if (isset($this->customTransports[$name])) {
            return ($this->customTransports[$name])($this->app);
        }

        return match ($name) {
            'smtp' => new SmtpTransport(config('mail.mailers.smtp', [])),
            'mail' => new PhpMailTransport(),
            'array' => new ArrayTransport(),
            default => throw new InvalidArgumentException("Mail transport [{$name}] is not supported."),
        };
    }
}
