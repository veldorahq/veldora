<?php

declare(strict_types=1);

namespace Veldora\Framework\Http;

class Request
{
    /**
     * The request query parameters ($_GET).
     *
     * @var array<string, mixed>
     */
    protected array $query;

    /**
     * The request post parameters ($_POST).
     *
     * @var array<string, mixed>
     */
    protected array $request;

    /**
     * The combined input parameters.
     *
     * @var array<string, mixed>
     */
    protected array $input;

    /**
     * The request cookies ($_COOKIE).
     *
     * @var array<string, string>
     */
    protected array $cookies;

    /**
     * The request files ($_FILES).
     *
     * @var array<string, mixed>
     */
    protected array $files;

    /**
     * The server parameters ($_SERVER).
     *
     * @var array<string, mixed>
     */
    protected array $server;

    /**
     * The request headers.
     *
     * @var array<string, string>
     */
    protected array $headers;

    /**
     * The raw body content.
     */
    protected ?string $rawBody = null;

    /**
     * Create a new Request instance.
     *
     * @param array<string, mixed> $query
     * @param array<string, mixed> $request
     * @param array<string, string> $cookies
     * @param array<string, mixed> $files
     * @param array<string, mixed> $server
     */
    public function __construct(
        array $query = [],
        array $request = [],
        array $cookies = [],
        array $files = [],
        array $server = []
    ) {
        $this->query = $query;
        $this->request = $request;
        $this->cookies = $cookies;
        $this->files = $files;
        $this->server = $server;
        $this->headers = $this->parseHeaders($server);
        
        $this->parseBody();
    }

    /**
     * Capture the current request from PHP globals.
     */
    public static function capture(): self
    {
        return new self(
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES,
            $_SERVER
        );
    }

    /**
     * Create a Request object from explicit parameters.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, string> $cookies
     * @param array<string, mixed> $files
     * @param array<string, mixed> $server
     */
    public static function create(
        string $method,
        string $uri,
        array $parameters = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        ?string $content = null
    ): self {
        $parsedUrl = parse_url($uri);
        $path = $parsedUrl['path'] ?? '/';
        $queryString = $parsedUrl['query'] ?? '';

        $query = [];
        if ($queryString !== '') {
            parse_str($queryString, $query);
        }

        $request = [];
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $request = $parameters;
        } else {
            $query = array_merge($query, $parameters);
        }

        $server = array_merge([
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI'    => $uri,
            'PATH_INFO'      => $path,
            'REMOTE_ADDR'    => '127.0.0.1',
            'SERVER_NAME'    => 'localhost',
            'SERVER_PORT'    => '8000',
            'HTTP_HOST'      => 'localhost',
        ], $server);

        $instance = new self($query, $request, $cookies, $files, $server);
        if ($content !== null) {
            $instance->rawBody = $content;
        }
        return $instance;
    }

    /**
     * Parse headers from server variables.
     *
     * @param array<string, mixed> $server
     * @return array<string, string>
     */
    protected function parseHeaders(array $server): array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$name] = (string) $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], true)) {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                $headers[$name] = (string) $value;
            }
        }

        return $headers;
    }

    /**
     * Parse the request body, including JSON payloads.
     */
    protected function parseBody(): void
    {
        $this->input = array_merge($this->query, $this->request);

        $contentType = $this->header('Content-Type', '');

        if (is_string($contentType) && str_contains(strtolower($contentType), 'application/json')) {
            $this->rawBody = file_get_contents('php://input') ?: '';
            if ($this->rawBody !== '') {
                $decoded = json_decode($this->rawBody, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $this->input = array_merge($this->input, $decoded);
                }
            }
        }
    }

    /**
     * Get the request path (without query parameters).
     */
    public function getPath(): string
    {
        $uri = (string) ($this->server['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        return '/' . trim($path, '/');
    }

    /**
     * Get the HTTP request method. Supports REST method spoofing via POST field '_method'.
     */
    public function getMethod(): string
    {
        $method = strtoupper((string) ($this->server['REQUEST_METHOD'] ?? 'GET'));

        if ($method === 'POST') {
            $override = $this->input['_method'] ?? $this->header('X-HTTP-Method-Override');
            if ($override) {
                $method = strtoupper((string) $override);
            }
        }

        return $method;
    }

    /**
     * Retrieve a value from the request query string ($_GET).
     */
    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->query;
        }
        return $this->query[$key] ?? $default;
    }

    /**
     * Retrieve a value from the request input (query, post, json).
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->input[$key] ?? $default;
    }

    /**
     * Retrieve all input data.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->input;
    }

    /**
     * Retrieve a header value.
     */
    public function header(string $key, mixed $default = null): mixed
    {
        $normalizedKey = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
        return $this->headers[$normalizedKey] ?? $default;
    }

    /**
     * Retrieve all headers.
     *
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * The active session instance.
     */
    protected ?\Veldora\Framework\Session\Session $session = null;

    /**
     * Set the session store.
     */
    public function setSession(\Veldora\Framework\Session\Session $session): void
    {
        $this->session = $session;
    }

    /**
     * Retrieve the session instance.
     */
    public function session(): \Veldora\Framework\Session\Session
    {
        if ($this->session === null) {
            throw new \RuntimeException('Session store not set on request.');
        }
        return $this->session;
    }

    /**
     * Check if request has session.
     */
    public function hasSession(): bool
    {
        return $this->session !== null;
    }

    /**
     * Retrieve cookie value.
     */
    public function cookie(string $key, ?string $default = null): ?string
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Retrieve signed cookie value.
     */
    public function signedCookie(string $key, ?string $default = null): ?string
    {
        $value = $this->cookie($key);
        if ($value === null || $value === '') {
            return $default;
        }

        $parts = explode('.', $value, 2);
        if (count($parts) === 2) {
            [$val, $signature] = $parts;
            $appKey = config('app.key', 'default-key');
            if (hash_equals(hash_hmac('sha256', $val, $appKey), $signature)) {
                return $val;
            }
        }

        return $default;
    }

    /**
     * Retrieve uploaded files.
     */
    public function file(string $key): mixed
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get raw request body.
     */
    public function getRawBody(): string
    {
        if ($this->rawBody === null) {
            $this->rawBody = file_get_contents('php://input') ?: '';
        }

        return $this->rawBody;
    }

    /**
     * Validate request inputs.
     *
     * @param array<string, string|array<mixed>> $rules
     * @return array<string, mixed>
     */
    public function validate(array $rules): array
    {
        $validator = new \Veldora\Framework\Validation\Validator($this->all(), $rules);
        if ($validator->fails()) {
            throw new \Veldora\Framework\Validation\ValidationException($validator);
        }
        return $validator->validated();
    }

    /**
     * Get subset of request inputs.
     *
     * @param array<string> $keys
     * @return array<string, mixed>
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->all(), array_flip($keys));
    }

    /**
     * Get all request inputs except specified keys.
     *
     * @param array<string> $keys
     * @return array<string, mixed>
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->all(), array_flip($keys));
    }

    /**
     * Cast input parameter to boolean.
     */
    public function boolean(string $key, bool $default = false): bool
    {
        $value = $this->input($key);
        if ($value === null) {
            return $default;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Cast input parameter to integer.
     */
    public function integer(string $key, int $default = 0): int
    {
        $value = $this->input($key);
        if ($value === null) {
            return $default;
        }
        return (int) $value;
    }

    /**
     * Get the authenticated user model.
     */
    public function user(): ?\Veldora\Framework\Database\Model
    {
        $app = \Veldora\Framework\Foundation\Application::getInstance();
        if ($app->has(\Veldora\Framework\Auth\AuthManager::class)) {
            return $app->get(\Veldora\Framework\Auth\AuthManager::class)->user();
        }
        return null;
    }
}
