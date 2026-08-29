<?php

declare(strict_types=1);

namespace Veldora\Framework\Foundation;

use Veldora\Framework\Auth\AuthManager;
use Veldora\Framework\Config\Config;
use Veldora\Framework\Config\Env;
use Veldora\Framework\Http\CookieJar;
use Veldora\Framework\Session\Session;
use Veldora\Framework\Session\FileDriver;

final class Application extends Container
{
    public const VERSION = '0.5.1';

    /**
     * The static instance of the application.
     */
    protected static ?Application $instance = null;

    /**
     * The application base path.
     */
    protected string $basePath;

    /**
     * Whether the application has booted.
     */
    protected bool $booted = false;

    /**
     * The registered service providers.
     *
     * @var array<string, ServiceProvider>
     */
    protected array $providers = [];

    /**
     * Create a new Application instance.
     */
    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, DIRECTORY_SEPARATOR);
        
        static::$instance = $this;

        // Load .env before anything else so env() is available in config files
        Env::load($this->basePath);

        // Register global error/exception handlers
        \Veldora\Framework\Foundation\Exception\Handler::register();

        $this->registerCoreBindings();
    }

    /**
     * Get the static application instance.
     */
    public static function getInstance(): Application
    {
        if (static::$instance === null) {
            static::$instance = new self(getcwd() ?: '');
        }

        return static::$instance;
    }

    /**
     * Register core container bindings.
     */
    protected function registerCoreBindings(): void
    {
        $this->singleton(Container::class, $this);
        $this->singleton(ContainerInterface::class, $this);
        $this->singleton(Application::class, $this);
        $this->singleton(Config::class, function () {
            return new Config($this->configPath());
        });
        $this->singleton(CookieJar::class, function () {
            return new CookieJar();
        });
        $this->singleton(Session::class, function () {
            $driver = config('session.driver', 'file');
            if ($driver === 'array') {
                $sessionDriver = new \Veldora\Framework\Session\ArrayDriver();
            } else {
                $path = $this->storagePath('framework/sessions');
                $sessionDriver = new FileDriver($path);
            }
            $cookieName = (string) config('session.cookie', 'veldora_session');
            $sessionId = $_COOKIE[$cookieName] ?? null;
            return new Session($sessionDriver, $sessionId);
        });
        $this->singleton(AuthManager::class, function () {
            return new AuthManager($this);
        });
        $this->singleton(\Veldora\Framework\Events\EventDispatcher::class, function () {
            return new \Veldora\Framework\Events\EventDispatcher($this);
        });
        $this->singleton(\Veldora\Framework\Queue\QueueManager::class, function () {
            return new \Veldora\Framework\Queue\QueueManager($this);
        });
        $this->singleton(\Veldora\Framework\Mail\Mailer::class, function () {
            return new \Veldora\Framework\Mail\Mailer($this);
        });
        $this->singleton(\Veldora\Framework\Cache\CacheManager::class, function () {
            return new \Veldora\Framework\Cache\CacheManager($this);
        });
        $this->singleton(\Veldora\Framework\Storage\StorageManager::class, function () {
            return new \Veldora\Framework\Storage\StorageManager($this);
        });
        $this->singleton(\Veldora\Framework\Logging\LogManager::class, function () {
            return new \Veldora\Framework\Logging\LogManager($this);
        });
    }

    /**
     * Get the application base path.
     */
    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    /**
     * Get the config path.
     */
    public function configPath(string $path = ''): string
    {
        return $this->basePath('config') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    /**
     * Get the routes path.
     */
    public function routesPath(string $path = ''): string
    {
        return $this->basePath('routes') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    /**
     * Get the public path.
     */
    public function publicPath(string $path = ''): string
    {
        return $this->basePath('public') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    /**
     * Get the storage path.
     */
    public function storagePath(string $path = ''): string
    {
        return $this->basePath('storage') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }

    /**
     * Register a service provider with the application.
     */
    public function registerProvider(string $providerClass): ServiceProvider
    {
        if (isset($this->providers[$providerClass])) {
            return $this->providers[$providerClass];
        }

        if (!class_exists($providerClass)) {
            throw new \InvalidArgumentException("Service Provider [{$providerClass}] does not exist.");
        }

        /** @var ServiceProvider $provider */
        $provider = new $providerClass($this);
        $provider->register();

        $this->providers[$providerClass] = $provider;

        if ($this->booted) {
            $provider->boot();
        }

        return $provider;
    }

    /**
     * Boot all registered service providers.
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->providers as $provider) {
            $provider->boot();
        }

        $this->booted = true;
    }

    /**
     * Determine if the application has booted.
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }
}
