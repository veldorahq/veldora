<?php

declare(strict_types=1);

namespace Veldora\Framework\Config;

class Config
{
    /**
     * The config repository items.
     *
     * @var array<string, mixed>
     */
    protected array $items = [];

    /**
     * Create a new Config instance and load files.
     */
    public function __construct(string $configPath)
    {
        if (is_dir($configPath)) {
            $files = glob($configPath . '/*.php') ?: [];
            foreach ($files as $file) {
                $name = basename($file, '.php');
                /** @var mixed $content */
                $content = require $file;
                if (is_array($content)) {
                    $this->items[$name] = $content;
                }
            }
        }
    }

    /**
     * Get a configuration item using dot notation.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $current = $this->items;

        foreach ($parts as $part) {
            if (!is_array($current) || !array_key_exists($part, $current)) {
                return $default;
            }
            $current = $current[$part];
        }

        return $current;
    }

    /**
     * Set a configuration item.
     */
    public function set(string $key, mixed $value): void
    {
        $parts = explode('.', $key);
        $current = &$this->items;

        foreach ($parts as $part) {
            if (!isset($current[$part]) || !is_array($current[$part])) {
                $current[$part] = [];
            }
            $current = &$current[$part];
        }

        $current = $value;
    }
}
