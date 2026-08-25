<?php

declare(strict_types=1);

namespace Veldora\Framework\Support;

class UrlSigner
{
    /**
     * Generate a signed URL.
     */
    public static function signed(string $url, ?int $expires = null): string
    {
        $appKey = config('app.key', 'default-key');
        
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '/';
        
        $query = [];
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
        }

        if ($expires !== null) {
            $query['expires'] = (string) $expires;
        }

        // Sort by key to ensure signature query parameters are ordered consistently
        ksort($query);

        $queryString = http_build_query($query);
        $payload = $path . '?' . $queryString;
        
        $signature = hash_hmac('sha256', $payload, $appKey);
        $query['signature'] = $signature;

        $scheme = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '';
        $host = $parsed['host'] ?? '';
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';

        return $scheme . $host . $port . $path . '?' . http_build_query($query);
    }

    /**
     * Generate a temporary signed URL.
     */
    public static function temporarySigned(string $url, int $expires): string
    {
        return static::signed($url, $expires);
    }

    /**
     * Validate the URL's signature and expiration status.
     */
    public static function hasValidSignature(string $url): bool
    {
        $appKey = config('app.key', 'default-key');
        
        $parsed = parse_url($url);
        if (!isset($parsed['query'])) {
            return false;
        }

        $query = [];
        parse_str($parsed['query'], $query);

        if (!isset($query['signature'])) {
            return false;
        }

        $signature = $query['signature'];
        unset($query['signature']);

        // Check expiration
        if (isset($query['expires'])) {
            $expires = (int) $query['expires'];
            if (time() > $expires) {
                return false;
            }
        }

        // Reconstruct path & query
        $path = $parsed['path'] ?? '/';
        ksort($query);
        $queryString = http_build_query($query);
        $payload = $path . '?' . $queryString;

        $recomputed = hash_hmac('sha256', $payload, $appKey);

        return hash_equals($recomputed, $signature);
    }
}
