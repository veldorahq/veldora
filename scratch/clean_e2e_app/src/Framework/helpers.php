<?php

declare(strict_types=1);

use Veldora\Framework\Auth\AuthManager;
use Veldora\Framework\Config\Config;
use Veldora\Framework\Config\Env;
use Veldora\Framework\Foundation\Application;
use Veldora\Framework\Session\Session;

if (!function_exists('config')) {
    /**
     * Get or set configuration values.
     */
    function config(string $key, mixed $default = null): mixed
    {
        $app = Application::getInstance();
        /** @var Config $config */
        $config = $app->get(Config::class);
        return $config->get($key, $default);
    }
}

if (!function_exists('env')) {
    /**
     * Get an environment variable value from the Veldora .env loader.
     *
     * Supports Veldora's typed .env format:
     *   APP_DEBUG:bool = true
     *   DB_PORT:int    = 3306
     */
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('session')) {
    /**
     * Get the session instance or retrieve a value.
     */
    function session(?string $key = null, mixed $default = null): mixed
    {
        $app = Application::getInstance();
        /** @var Session $session */
        $session = $app->get(Session::class);

        if ($key === null) {
            return $session;
        }

        return $session->get($key, $default);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get the active CSRF token.
     */
    function csrf_token(): string
    {
        return session()->csrfToken();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate a CSRF hidden input field HTML snippet.
     */
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('auth')) {
    /**
     * Get the AuthManager instance.
     */
    function auth(): AuthManager
    {
        $app = Application::getInstance();
        return $app->get(AuthManager::class);
    }
}

if (!function_exists('url')) {
    /**
     * Generate a URL with optional signed signature.
     */
    function url(string $path, bool $signed = false, ?int $expiresAt = null): string
    {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base = $scheme . '://' . $host;
        $fullUrl = $base . '/' . ltrim($path, '/');

        if ($signed) {
            return \Veldora\Framework\Support\UrlSigner::signed($fullUrl, $expiresAt);
        }

        return $fullUrl;
    }
}

if (!function_exists('signed_url')) {
    /**
     * Generate a signed URL (permanent).
     */
    function signed_url(string $path): string
    {
        return url($path, true);
    }
}

if (!function_exists('temporary_signed_url')) {
    /**
     * Generate a signed URL that expires after a given number of seconds.
     */
    function temporary_signed_url(string $path, int $expiresInSeconds): string
    {
        return url($path, true, time() + $expiresInSeconds);
    }
}

if (!function_exists('redirect')) {
    /**
     * Create a redirect Response.
     */
    function redirect(string $to, int $status = 302): \Veldora\Framework\Http\Response
    {
        return \Veldora\Framework\Http\Response::redirect($to, $status);
    }
}

if (!function_exists('back')) {
    /**
     * Create a redirect Response back to the previous URL.
     */
    function back(int $status = 302): \Veldora\Framework\Http\Response
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        return redirect($referer, $status);
    }
}

if (!function_exists('abort')) {
    /**
     * Abort the request with an HTTP error response.
     */
    function abort(int $code, string $message = ''): never
    {
        $messages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            419 => 'Page Expired',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
        ];

        $text = $message !== '' ? $message : ($messages[$code] ?? 'Error');
        $response = new \Veldora\Framework\Http\Response($text, $code);
        $response->send();
        exit($code);
    }
}

if (!function_exists('app')) {
    /**
     * Get the available container instance or resolve a binding.
     */
    function app(?string $abstract = null): mixed
    {
        $instance = Application::getInstance();

        if ($abstract === null) {
            return $instance;
        }

        return $instance->get($abstract);
    }
}

if (!function_exists('event')) {
    /**
     * Dispatch an event and call the listeners.
     *
     * @return array<mixed>
     */
    function event(string|object $event, mixed $payload = []): array
    {
        /** @var \Veldora\Framework\Events\EventDispatcher $dispatcher */
        $dispatcher = app(\Veldora\Framework\Events\EventDispatcher::class);

        return $dispatcher->dispatch($event, $payload);
    }
}

if (!function_exists('queue')) {
    /**
     * Get the QueueManager instance or push a job.
     */
    function queue(?\Veldora\Framework\Queue\JobInterface $job = null, string $queue = 'default', int $delay = 0): mixed
    {
        /** @var \Veldora\Framework\Queue\QueueManager $manager */
        $manager = app(\Veldora\Framework\Queue\QueueManager::class);

        if ($job === null) {
            return $manager;
        }

        return $manager->push($job, $queue, $delay);
    }
}

if (!function_exists('dispatch')) {
    /**
     * Dispatch a queued job.
     */
    function dispatch(\Veldora\Framework\Queue\JobInterface $job): \Veldora\Framework\Queue\PendingDispatch
    {
        return new \Veldora\Framework\Queue\PendingDispatch($job);
    }
}

if (!function_exists('mailer')) {
    /**
     * Get the Mailer instance or create a pending mail.
     */
    function mailer(mixed $to = null): \Veldora\Framework\Mail\Mailer|\Veldora\Framework\Mail\PendingMail
    {
        /** @var \Veldora\Framework\Mail\Mailer $mailer */
        $mailer = app(\Veldora\Framework\Mail\Mailer::class);

        if ($to === null) {
            return $mailer;
        }

        return $mailer->to($to);
    }
}

if (!function_exists('cache')) {
    /**
     * Get / set the specified cache value or retrieve the CacheManager.
     */
    function cache(mixed $key = null, mixed $default = null): mixed
    {
        /** @var \Veldora\Framework\Cache\CacheManager $cache */
        $cache = app(\Veldora\Framework\Cache\CacheManager::class);

        if ($key === null) {
            return $cache;
        }

        if (is_array($key)) {
            // Setting values from array: cache(['key' => 'val'], $ttl)
            $ttl = is_int($default) ? $default : null;
            foreach ($key as $k => $v) {
                $cache->set($k, $v, $ttl);
            }
            return true;
        }

        return $cache->get((string) $key, $default);
    }
}

if (!function_exists('storage')) {
    /**
     * Get a filesystem disk instance.
     */
    function storage(?string $disk = null): \Veldora\Framework\Storage\StorageDriverInterface
    {
        /** @var \Veldora\Framework\Storage\StorageManager $storage */
        $storage = app(\Veldora\Framework\Storage\StorageManager::class);

        return $storage->disk($disk);
    }
}

if (!function_exists('logger')) {
    /**
     * Log a debug message to the logs or retrieve the LogManager instance.
     */
    function logger(?string $message = null, array $context = []): mixed
    {
        /** @var \Veldora\Framework\Logging\LogManager $log */
        $log = app(\Veldora\Framework\Logging\LogManager::class);

        if ($message === null) {
            return $log;
        }

        $log->debug($message, $context);
        return null;
    }
}

if (!function_exists('log_info')) {
    /**
     * Log an informational message.
     */
    function log_info(string $message, array $context = []): void
    {
        /** @var \Veldora\Framework\Logging\LogManager $log */
        $log = app(\Veldora\Framework\Logging\LogManager::class);
        $log->info($message, $context);
    }
}

if (!function_exists('log_error')) {
    /**
     * Log an error message with context.
     */
    function log_error(string $message, array $context = []): void
    {
        /** @var \Veldora\Framework\Logging\LogManager $log */
        $log = app(\Veldora\Framework\Logging\LogManager::class);
        $log->error($message, $context);
    }
}

// ─── Path Helpers ───────────────────────────────────────────────────────────

if (!function_exists('base_path')) {
    /**
     * Get the absolute path to the project root, with optional sub-path appended.
     */
    function base_path(string $path = ''): string
    {
        return app()->basePath($path);
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the absolute path to the storage directory, with optional sub-path appended.
     */
    function storage_path(string $path = ''): string
    {
        return app()->storagePath($path);
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the absolute path to the public directory, with optional sub-path appended.
     */
    function public_path(string $path = ''): string
    {
        return app()->publicPath($path);
    }
}

if (!function_exists('resource_path')) {
    /**
     * Get the absolute path to the resources directory, with optional sub-path appended.
     */
    function resource_path(string $path = ''): string
    {
        return app()->basePath('resources' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (!function_exists('database_path')) {
    /**
     * Get the absolute path to the database directory, with optional sub-path appended.
     */
    function database_path(string $path = ''): string
    {
        return app()->basePath('database' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (!function_exists('config_path')) {
    /**
     * Get the absolute path to the config directory, with optional sub-path appended.
     */
    function config_path(string $path = ''): string
    {
        return app()->configPath($path);
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the absolute path to the app directory, with optional sub-path appended.
     */
    function app_path(string $path = ''): string
    {
        return app()->basePath('app' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

// ─── Routing Helpers ────────────────────────────────────────────────────────

if (!function_exists('route')) {
    /**
     * Generate a URL for a named route with optional parameter substitution.
     *
     * @param array<string, mixed> $parameters
     */
    function route(string $name, array $parameters = []): string
    {
        /** @var \Veldora\Framework\Http\Router $router */
        $router = app(\Veldora\Framework\Http\Router::class);
        return $router->url($name, $parameters);
    }
}

// ─── Database Helpers ───────────────────────────────────────────────────────

if (!function_exists('db')) {
    /**
     * Get the DB facade for raw queries and transactions.
     */
    function db(): \Veldora\Framework\Database\DB
    {
        return new \Veldora\Framework\Database\DB();
    }
}
