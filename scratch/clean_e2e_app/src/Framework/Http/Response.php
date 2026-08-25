<?php

declare(strict_types=1);

namespace Veldora\Framework\Http;

class Response
{
    /**
     * The response headers.
     *
     * @var array<string, string>
     */
    protected array $headers = [];

    /**
     * Create a new Response instance.
     *
     * @param array<string, string|int> $headers
     */
    public function __construct(
        protected mixed $content = '',
        protected int $statusCode = 200,
        array $headers = []
    ) {
        foreach ($headers as $name => $value) {
            $this->setHeader($name, (string) $value);
        }
    }

    /**
     * Set a header on the response.
     */
    public function setHeader(string $name, string $value): self
    {
        $normalizedName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $name))));
        $this->headers[$normalizedName] = $value;
        return $this;
    }

    /**
     * Get a header from the response.
     */
    public function getHeader(string $name): ?string
    {
        $normalizedName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $name))));
        return $this->headers[$normalizedName] ?? null;
    }

    /**
     * Get all response headers.
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set the status code.
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Get the status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Set the response content.
     */
    public function setContent(mixed $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get the response content.
     */
    public function getContent(): mixed
    {
        return $this->content;
    }

    /**
     * Create a JSON response.
     *
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public static function json(array $data, int $statusCode = 200, array $headers = []): self
    {
        $content = json_encode($data);
        if ($content === false) {
            throw new \InvalidArgumentException('Failed encoding JSON data: ' . json_last_error_msg());
        }

        $headers['Content-Type'] = 'application/json';

        return new self($content, $statusCode, $headers);
    }

    /**
     * Create a redirect response.
     *
     * @param array<string, string> $headers
     */
    public static function redirect(string $url, int $statusCode = 302, array $headers = []): self
    {
        $headers['Location'] = $url;

        return new self('', $statusCode, $headers);
    }

    /**
     * The response cookies.
     *
     * @var array<array{name: string, value: string, options: array<string, mixed>}>
     */
    protected array $cookies = [];

    /**
     * Add a cookie to the response queue.
     */
    public function cookie(
        string $name,
        string $value,
        int $minutes = 0,
        string $path = '/',
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ): self {
        $expires = $minutes > 0 ? time() + ($minutes * 60) : 0;

        $options = [
            'expires' => $expires,
            'path' => $path,
            'secure' => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite,
        ];

        if ($domain !== null) {
            $options['domain'] = $domain;
        }

        $this->cookies[] = [
            'name' => $name,
            'value' => $value,
            'options' => $options,
        ];

        return $this;
    }

    /**
     * Alias for adding a cookie.
     */
    public function withCookie(
        string $name,
        string $value,
        int $minutes = 0,
        string $path = '/',
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax'
    ): self {
        return $this->cookie($name, $value, $minutes, $path, $domain, $secure, $httpOnly, $sameSite);
    }

    /**
     * Queue a cookie deletion.
     */
    public function forgetCookie(string $name, string $path = '/', ?string $domain = null): self
    {
        return $this->cookie($name, '', -2628000, $path, $domain);
    }

    /**
     * Send the headers and content to the client.
     */
    public function send(): void
    {
        if (headers_sent()) {
            echo $this->content;
            return;
        }

        http_response_code($this->statusCode);

        foreach ($this->cookies as $cookie) {
            setcookie($cookie['name'], $cookie['value'], $cookie['options']);
        }

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}", true);
        }

        echo $this->content;
    }
}
