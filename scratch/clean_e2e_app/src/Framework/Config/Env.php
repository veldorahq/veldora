<?php

declare(strict_types=1);

namespace Veldora\Framework\Config;

use RuntimeException;

/**
 * Veldora Environment Loader
 *
 * Reads .env files in a familiar KEY=VALUE format with smart enhancements:
 *
 *   APP_NAME  = Veldora              # inline comments supported
 *   APP_DEBUG = true                 # auto-cast to bool (not string)
 *   DB_PORT   = 3306                 # auto-cast to int
 *   APP_KEY   = "my secret key"      # quoted for values with spaces
 *   APP_URL   = http://${APP_HOST}   # ${VAR} interpolation
 *   export DB_USER = root            # bash-style export prefix
 *
 *   Multiline values:
 *   APP_CERT = """
 *   -----BEGIN CERTIFICATE-----
 *   MIIBIjANBgkq...
 *   -----END CERTIFICATE-----
 *   """
 *
 * Type auto-casting (no annotation needed):
 *   true / false  → bool
 *   null          → null
 *   42 / 3.14     → int / float
 *   everything else → string
 */
class Env
{
    /** @var array<string, mixed> */
    private static array $data = [];

    private static bool $loaded = false;

    // -----------------------------------------------------------------------
    // Public API
    // -----------------------------------------------------------------------

    /**
     * Load the .env file from the given directory.
     * Safe to call multiple times — only loads once.
     */
    public static function load(string $basePath): void
    {
        if (self::$loaded) {
            return;
        }

        $file = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '.env';

        if (!file_exists($file)) {
            self::$loaded = true;
            return;
        }

        $content = file_get_contents($file);
        if ($content === false) {
            throw new RuntimeException("Cannot read .env file: {$file}");
        }

        self::parse($content);
        self::$loaded = true;
    }

    /**
     * Get an environment value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, self::$data)) {
            return self::$data[$key];
        }

        $fromSystem = getenv($key);
        if ($fromSystem !== false) {
            return self::cast($fromSystem);
        }

        return $_ENV[$key] ?? $default;
    }

    /**
     * Check if a key exists.
     */
    public static function has(string $key): bool
    {
        return array_key_exists($key, self::$data) || getenv($key) !== false || isset($_ENV[$key]);
    }

    /**
     * Return all loaded values.
     *
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return self::$data;
    }

    /**
     * Set a value at runtime (does not write to file).
     */
    public static function set(string $key, mixed $value): void
    {
        self::$data[$key] = $value;
    }

    /**
     * Reset state — mainly for testing.
     */
    public static function reset(): void
    {
        self::$data   = [];
        self::$loaded = false;
    }

    // -----------------------------------------------------------------------
    // Parser
    // -----------------------------------------------------------------------

    private static function parse(string $content): void
    {
        // Normalise line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Handle triple-quoted multiline values first
        $content = self::extractMultilines($content);

        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip blank lines and full-line comments
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            // Strip optional `export ` prefix (bash-style)
            if (str_starts_with($line, 'export ')) {
                $line = ltrim(substr($line, 7));
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $raw] = explode('=', $line, 2);
            $key = trim($key);
            $raw = trim($raw);

            // Validate key characters
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_.]*$/', $key)) {
                continue;
            }

            $value = self::parseValue($raw);

            // Resolve ${VAR} interpolation (only on string values)
            if (is_string($value)) {
                $value = self::interpolate($value);
            }

            self::$data[$key] = $value;

            // Sync to system env as strings for third-party library compatibility
            $strVal = match (true) {
                $value === null  => '',
                is_bool($value)  => ($value ? 'true' : 'false'),
                is_array($value) => implode(',', $value),
                default          => (string) $value,
            };

            $_ENV[$key] = $strVal;
            putenv("{$key}={$strVal}");
        }
    }

    /**
     * Extract triple-quoted multiline values and replace with a single-line placeholder.
     */
    private static function extractMultilines(string $content): string
    {
        return (string) preg_replace_callback(
            '/^([A-Za-z_][A-Za-z0-9_.]*)\s*=\s*"""\s*\n(.*?)\n\s*"""/ms',
            function (array $m) {
                $key   = $m[1];
                // Store raw, preserving newlines internally with \n escape placeholder
                $value = str_replace("\n", '\n', trim($m[2]));
                return "{$key}=\"{$value}\"";
            },
            $content
        );
    }

    /**
     * Parse a raw value string into its native PHP type.
     */
    private static function parseValue(string $raw): mixed
    {
        // Double-quoted string → strip quotes, unescape sequences
        if (str_starts_with($raw, '"')) {
            $inner = (string) preg_replace_callback(
                '/"((?:[^"\\\\]|\\\\.)*)"/s',
                fn(array $m) => $m[1],
                $raw
            );
            // Strip inline comment after closing quote
            $inner = self::stripInlineComment($inner, true);
            return stripcslashes($inner);
        }

        // Single-quoted string → no escape processing
        if (str_starts_with($raw, "'")) {
            if (preg_match("/^'([^']*)'/", $raw, $m)) {
                return $m[1];
            }
            return trim($raw, "'");
        }

        // Strip inline comment from unquoted value
        $raw = self::stripInlineComment($raw, false);

        return self::cast($raw);
    }

    /**
     * Auto-cast a raw string to its proper PHP type.
     */
    private static function cast(string $raw): mixed
    {
        $lower = strtolower($raw);

        return match (true) {
            $raw === ''       => '',
            $lower === 'null' => null,
            $lower === 'true' => true,
            $lower === 'false'=> false,
            is_numeric($raw) && !str_contains($raw, '.') => (int) $raw,
            is_numeric($raw)  => (float) $raw,
            default           => $raw,
        };
    }

    /**
     * Resolve ${KEY} placeholders using already-loaded values.
     */
    private static function interpolate(string $value): string
    {
        return (string) preg_replace_callback(
            '/\$\{([A-Za-z_][A-Za-z0-9_.]*)\}/',
            function (array $m) {
                $resolved = self::get($m[1]);
                return is_string($resolved) || is_numeric($resolved) ? (string) $resolved : $m[0];
            },
            $value
        );
    }

    /**
     * Strip trailing inline comment.
     * For unquoted: strip ` #` and everything after.
     * For post-quoted: strip any trailing ` # comment`.
     */
    private static function stripInlineComment(string $value, bool $afterQuote): string
    {
        if ($afterQuote) {
            // Strip anything after the closing quote
            $value = trim($value);
            $pos   = strpos($value, ' #');
            return $pos !== false ? rtrim(substr($value, 0, $pos)) : $value;
        }

        // Unquoted: space-hash marks start of comment
        $pos = strpos($value, ' #');
        return $pos !== false ? rtrim(substr($value, 0, $pos)) : $value;
    }
}
