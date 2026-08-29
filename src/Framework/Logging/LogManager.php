<?php

declare(strict_types=1);

namespace Veldora\Framework\Logging;

use InvalidArgumentException;
use Stringable;
use Throwable;
use Veldora\Framework\Foundation\Application;

class LogManager implements LoggerInterface
{
    /**
     * The array of resolved log channels.
     *
     * @var array<string, LoggerInterface>
     */
    protected array $channels = [];

    /**
     * The registered custom channel creators.
     *
     * @var array<string, callable>
     */
    protected array $customCreators = [];

    /**
     * The default channel name.
     */
    protected ?string $defaultChannel = null;

    /**
     * The base logs directory.
     */
    protected string $logsPath;

    public function __construct(protected Application $app)
    {
        $this->logsPath = $this->app->basePath('storage/logs');
        $this->ensureDirectoryExists($this->logsPath);
    }

    /**
     * Get a log channel instance.
     */
    public function channel(?string $channel = null): LoggerInterface
    {
        $channel = $channel ?: $this->getDefaultChannel();

        if (!isset($this->channels[$channel])) {
            $this->channels[$channel] = $this->resolve($channel);
        }

        return $this->channels[$channel];
    }

    /**
     * Resolve the given log channel.
     */
    protected function resolve(string $name): LoggerInterface
    {
        if (isset($this->customCreators[$name])) {
            return ($this->customCreators[$name])($this->app);
        }

        return $this;
    }

    /**
     * Register a custom channel creator.
     */
    public function extend(string $channel, callable $callback): static
    {
        $this->customCreators[$channel] = $callback;
        return $this;
    }

    /**
     * Set the default channel name.
     */
    public function setDefaultChannel(string $channel): static
    {
        $this->defaultChannel = $channel;
        return $this;
    }

    /**
     * Get the default channel name.
     */
    public function getDefaultChannel(): string
    {
        if ($this->defaultChannel !== null) {
            return $this->defaultChannel;
        }

        if (function_exists('config')) {
            return (string) config('logging.default', 'daily');
        }

        return 'daily';
    }

    /**
     * Set a custom logs directory path.
     */
    public function setLogsPath(string $path): static
    {
        $this->logsPath = $path;
        $this->ensureDirectoryExists($this->logsPath);
        return $this;
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function log(string $level, string|Stringable $message, array $context = []): void
    {
        $level = strtoupper($level);
        $message = (string) $message;

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = '';

        // Extract exception from context if present
        if (isset($context['exception']) && $context['exception'] instanceof Throwable) {
            $e = $context['exception'];
            $context['exception'] = [
                'class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
        }

        if (!empty($context)) {
            $contextStr = ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $line = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

        $logFile = $this->getLogFilePath();
        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Determine the log file path based on channel configuration.
     */
    protected function getLogFilePath(): string
    {
        $channel = $this->getDefaultChannel();

        if ($channel === 'single') {
            return $this->logsPath . '/veldora.log';
        }

        // Daily rotating log file
        $date = date('Y-m-d');
        return "{$this->logsPath}/veldora-{$date}.log";
    }

    protected function ensureDirectoryExists(string $dir): void
    {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
}
