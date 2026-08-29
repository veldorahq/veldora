<?php

declare(strict_types=1);

namespace Veldora\Framework\Storage;

use InvalidArgumentException;
use Veldora\Framework\Foundation\Application;
use Veldora\Framework\Storage\Drivers\LocalDriver;

class StorageManager
{
    /**
     * The array of resolved filesystem disks.
     *
     * @var array<string, StorageDriverInterface>
     */
    protected array $disks = [];

    /**
     * The registered custom driver creators.
     *
     * @var array<string, callable>
     */
    protected array $customCreators = [];

    /**
     * The default disk name.
     */
    protected ?string $defaultDisk = null;

    public function __construct(protected Application $app)
    {
    }

    /**
     * Get a filesystem disk instance.
     */
    public function disk(?string $name = null): StorageDriverInterface
    {
        $name = $name ?: $this->getDefaultDisk();

        if (!isset($this->disks[$name])) {
            $this->disks[$name] = $this->resolve($name);
        }

        return $this->disks[$name];
    }

    /**
     * Resolve the given disk.
     */
    protected function resolve(string $name): StorageDriverInterface
    {
        if (isset($this->customCreators[$name])) {
            return ($this->customCreators[$name])($this->app);
        }

        $config = $this->getConfig($name);
        $driver = $config['driver'] ?? 'local';

        return match ($driver) {
            'local' => new LocalDriver(
                $config['root'] ?? $this->app->basePath('storage/app/' . $name),
                $config['url'] ?? ('/storage/' . $name)
            ),
            default => throw new InvalidArgumentException("Filesystem driver [{$driver}] is not supported."),
        };
    }

    /**
     * Register a custom driver creator Closure.
     */
    public function extend(string $driver, callable $callback): static
    {
        $this->customCreators[$driver] = $callback;
        return $this;
    }

    /**
     * Manually set a disk instance.
     */
    public function setDisk(string $name, StorageDriverInterface $driver): static
    {
        $this->disks[$name] = $driver;
        return $this;
    }

    /**
     * Set the default disk name.
     */
    public function setDefaultDisk(string $name): static
    {
        $this->defaultDisk = $name;
        return $this;
    }

    /**
     * Get the default disk name.
     */
    public function getDefaultDisk(): string
    {
        if ($this->defaultDisk !== null) {
            return $this->defaultDisk;
        }

        if (function_exists('config')) {
            return (string) config('filesystems.default', 'local');
        }

        return 'local';
    }

    /**
     * Get the disk configuration.
     *
     * @return array<string, mixed>
     */
    protected function getConfig(string $name): array
    {
        if (function_exists('config')) {
            return (array) config("filesystems.disks.{$name}", [
                'driver' => 'local',
                'root' => $this->app->basePath('storage/app' . ($name === 'public' ? '/public' : '')),
                'url' => $name === 'public' ? '/storage' : '',
            ]);
        }

        return [
            'driver' => 'local',
            'root' => $this->app->basePath('storage/app' . ($name === 'public' ? '/public' : '')),
            'url' => $name === 'public' ? '/storage' : '',
        ];
    }

    /**
     * Create an HTTP file download response.
     */
    public function download(string $path, ?string $name = null, array $headers = []): void
    {
        $content = $this->disk()->get($path);
        if ($content === null) {
            http_response_code(404);
            echo 'File not found.';
            return;
        }

        $filename = $name ?: basename($path);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($content));

        foreach ($headers as $header => $value) {
            header("{$header}: {$value}");
        }

        echo $content;
        exit(0);
    }

    /**
     * Dynamically call the default disk instance.
     *
     * @param array<mixed> $parameters
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->disk()->$method(...$parameters);
    }
}
