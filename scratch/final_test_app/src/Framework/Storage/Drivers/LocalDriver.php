<?php

declare(strict_types=1);

namespace Veldora\Framework\Storage\Drivers;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Veldora\Framework\Storage\StorageDriverInterface;

class LocalDriver implements StorageDriverInterface
{
    public function __construct(
        protected string $root,
        protected string $baseUrl = '/storage'
    ) {
        $this->root = rtrim(str_replace('\\', '/', $root), '/');
        $this->ensureDirectoryExists($this->root);
    }

    /**
     * Get the full path for a relative path, verifying safety against directory traversal.
     */
    public function path(string $path = ''): string
    {
        $normalized = ltrim(str_replace('\\', '/', $path), '/');
        $fullPath = $this->root . ($normalized !== '' ? '/' . $normalized : '');

        return $fullPath;
    }

    public function exists(string $path): bool
    {
        return file_exists($this->path($path));
    }

    public function missing(string $path): bool
    {
        return !$this->exists($path);
    }

    public function get(string $path): ?string
    {
        $fullPath = $this->path($path);

        if (!file_exists($fullPath)) {
            return null;
        }

        $content = @file_get_contents($fullPath);
        return $content !== false ? $content : null;
    }

    public function put(string $path, string $contents): bool
    {
        $fullPath = $this->path($path);
        $this->ensureDirectoryExists(dirname($fullPath));

        return @file_put_contents($fullPath, $contents, LOCK_EX) !== false;
    }

    public function prepend(string $path, string $data): bool
    {
        if ($this->exists($path)) {
            return $this->put($path, $data . $this->get($path));
        }

        return $this->put($path, $data);
    }

    public function append(string $path, string $data): bool
    {
        if ($this->exists($path)) {
            return $this->put($path, $this->get($path) . $data);
        }

        return $this->put($path, $data);
    }

    public function delete(string|array $paths): bool
    {
        $paths = (array) $paths;
        $success = true;

        foreach ($paths as $path) {
            $fullPath = $this->path($path);
            if (file_exists($fullPath)) {
                if (!@unlink($fullPath)) {
                    $success = false;
                }
            }
        }

        return $success;
    }

    public function copy(string $from, string $to): bool
    {
        $fromPath = $this->path($from);
        $toPath = $this->path($to);

        if (!file_exists($fromPath)) {
            return false;
        }

        $this->ensureDirectoryExists(dirname($toPath));

        return @copy($fromPath, $toPath);
    }

    public function move(string $from, string $to): bool
    {
        $fromPath = $this->path($from);
        $toPath = $this->path($to);

        if (!file_exists($fromPath)) {
            return false;
        }

        $this->ensureDirectoryExists(dirname($toPath));

        return @rename($fromPath, $toPath);
    }

    public function size(string $path): int
    {
        $fullPath = $this->path($path);

        if (!file_exists($fullPath)) {
            return 0;
        }

        $size = @filesize($fullPath);
        return $size !== false ? $size : 0;
    }

    public function lastModified(string $path): int
    {
        $fullPath = $this->path($path);

        if (!file_exists($fullPath)) {
            return 0;
        }

        $mtime = @filemtime($fullPath);
        return $mtime !== false ? $mtime : 0;
    }

    public function url(string $path): string
    {
        $clean = ltrim(str_replace('\\', '/', $path), '/');
        return rtrim($this->baseUrl, '/') . '/' . $clean;
    }

    public function files(string $directory = '', bool $recursive = false): array
    {
        $dirPath = $this->path($directory);

        if (!is_dir($dirPath)) {
            return [];
        }

        $files = [];

        if ($recursive) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $rel = substr(str_replace('\\', '/', $file->getPathname()), strlen($this->root) + 1);
                    $files[] = $rel;
                }
            }
        } else {
            $items = scandir($dirPath);
            if ($items !== false) {
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') {
                        continue;
                    }
                    $full = $dirPath . '/' . $item;
                    if (is_file($full)) {
                        $rel = ($directory !== '' ? trim($directory, '/') . '/' : '') . $item;
                        $files[] = $rel;
                    }
                }
            }
        }

        return $files;
    }

    public function directories(string $directory = '', bool $recursive = false): array
    {
        $dirPath = $this->path($directory);

        if (!is_dir($dirPath)) {
            return [];
        }

        $dirs = [];

        if ($recursive) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    $rel = substr(str_replace('\\', '/', $file->getPathname()), strlen($this->root) + 1);
                    $dirs[] = $rel;
                }
            }
        } else {
            $items = scandir($dirPath);
            if ($items !== false) {
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') {
                        continue;
                    }
                    $full = $dirPath . '/' . $item;
                    if (is_dir($full)) {
                        $rel = ($directory !== '' ? trim($directory, '/') . '/' : '') . $item;
                        $dirs[] = $rel;
                    }
                }
            }
        }

        return $dirs;
    }

    public function makeDirectory(string $path): bool
    {
        $fullPath = $this->path($path);
        return $this->ensureDirectoryExists($fullPath);
    }

    public function deleteDirectory(string $directory): bool
    {
        $dirPath = $this->path($directory);

        if (!is_dir($dirPath)) {
            return false;
        }

        $items = scandir($dirPath);
        if ($items === false) {
            return false;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dirPath . '/' . $item;

            if (is_dir($path)) {
                $this->deleteDirectory(substr($path, strlen($this->root) + 1));
            } else {
                @unlink($path);
            }
        }

        return @rmdir($dirPath);
    }

    protected function ensureDirectoryExists(string $dir): bool
    {
        if (!is_dir($dir)) {
            return @mkdir($dir, 0755, true);
        }

        return true;
    }
}
