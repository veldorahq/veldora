<?php

declare(strict_types=1);

namespace Veldora\Framework\Session;

class Session
{
    /**
     * The session identifier.
     */
    protected string $id;

    /**
     * The loaded session data.
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Whether the session has been started.
     */
    protected bool $started = false;

    /**
     * Create a new Session instance.
     */
    public function __construct(
        protected SessionDriverInterface $driver,
        ?string $id = null
    ) {
        $this->id = $id ?: $this->generateId();
    }

    /**
     * Start the session and load data.
     */
    public function start(): void
    {
        if ($this->started) {
            return;
        }

        $this->data = $this->driver->read($this->id);

        // Manage flash data lifecycle
        $flash = $this->data['_flash'] ?? ['old' => [], 'new' => []];
        
        // Move current 'new' flashes to 'old' so they are accessible this request
        $flash['old'] = $flash['new'] ?? [];
        $flash['new'] = [];
        
        $this->data['_flash'] = $flash;

        $this->started = true;
    }

    /**
     * Save session data back to storage.
     */
    public function save(): void
    {
        $this->driver->write($this->id, $this->data);
    }

    /**
     * Retrieve the session ID.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Generate a new session ID.
     */
    protected function generateId(): string
    {
        return bin2hex(random_bytes(20));
    }

    /**
     * Regenerate the session ID.
     */
    public function regenerate(bool $destroy = false): void
    {
        $oldId = $this->id;
        $this->id = $this->generateId();

        if ($destroy) {
            $this->driver->destroy($oldId);
        }

        $this->save();
    }

    /**
     * Get a value from the session.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        // Check flash data (both old and newly set in current request)
        if (isset($this->data['_flash']['new'][$key])) {
            return $this->data['_flash']['new'][$key];
        }

        return $this->data['_flash']['old'][$key] ?? $default;
    }

    /**
     * Put a value into the session.
     */
    public function put(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Check if a value is present in the session.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data) 
            || isset($this->data['_flash']['old'][$key])
            || isset($this->data['_flash']['new'][$key]);
    }

    /**
     * Forget a value from the session.
     */
    public function forget(string $key): void
    {
        unset($this->data[$key]);
    }

    /**
     * Put a flash value into the session (lasts only for the next request).
     */
    public function flash(string $key, mixed $value): void
    {
        $this->data['_flash']['new'][$key] = $value;
    }

    /**
     * Get the CSRF token, generating one if not present.
     */
    public function csrfToken(): string
    {
        $token = $this->get('_csrf_token');

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            $this->put('_csrf_token', $token);
        }

        return $token;
    }

    /**
     * Get the session driver.
     */
    public function getDriver(): SessionDriverInterface
    {
        return $this->driver;
    }

    /**
     * Regenerate the CSRF token.
     */
    public function regenerateToken(): void
    {
        $this->put('_csrf_token', bin2hex(random_bytes(32)));
    }

    /**
     * Clear all session data.
     */
    public function flush(): void
    {
        $this->data = ['_flash' => ['old' => [], 'new' => []]];
    }
}
