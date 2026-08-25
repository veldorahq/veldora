<?php

declare(strict_types=1);

namespace Veldora\Framework\Http\Middleware;

use Veldora\Framework\Http\MiddlewareInterface;
use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Foundation\Application;

/**
 * Rate-limiting middleware.
 *
 * Usage in routes:  ->middleware('throttle:60,1')
 *   - 60 = max requests per window
 *   - 1  = window in minutes
 *
 * Falls back to a file-based token bucket stored in storage/framework/cache/.
 */
class ThrottleRequests implements MiddlewareInterface
{
    public function __construct(
        protected int $maxAttempts = 60,
        protected int $decayMinutes = 1
    ) {
    }

    public function handle(Request $request, \Closure $next): Response
    {
        $key = $this->resolveKey($request);
        $storageFile = $this->storagePath($key);
        $now = time();
        $window = $this->decayMinutes * 60;

        $data = $this->readData($storageFile);

        if (empty($data) || ($now - ($data['reset_at'] ?? 0)) >= $window) {
            $data = ['attempts' => 0, 'reset_at' => $now + $window];
        }

        if ($data['attempts'] >= $this->maxAttempts) {
            $retryAfter = max(0, ($data['reset_at'] ?? $now + $window) - $now);
            return new Response('Too Many Requests', 429, [
                'Content-Type'  => 'text/plain',
                'Retry-After'   => (string) $retryAfter,
                'X-RateLimit-Limit' => (string) $this->maxAttempts,
                'X-RateLimit-Remaining' => '0',
            ]);
        }

        $data['attempts']++;
        $this->writeData($storageFile, $data);

        /** @var Response $response */
        $response = $next($request);
        $remaining = max(0, $this->maxAttempts - $data['attempts']);
        $response->setHeader('X-RateLimit-Limit', (string) $this->maxAttempts);
        $response->setHeader('X-RateLimit-Remaining', (string) $remaining);

        return $response;
    }

    public function clear(Request $request): void
    {
        $key = $this->resolveKey($request);
        $storageFile = $this->storagePath($key);
        if (file_exists($storageFile)) {
            unlink($storageFile);
        }
    }

    protected function resolveKey(Request $request): string
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return 'throttle_' . md5($ip . '|' . $request->getPath());
    }

    protected function storagePath(string $key): string
    {
        $dir = storage_path('framework/cache');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir . DIRECTORY_SEPARATOR . $key . '.json';
    }

    /**
     * @return array<string, mixed>
     */
    protected function readData(string $file): array
    {
        if (!file_exists($file)) {
            return [];
        }
        $content = file_get_contents($file);
        if ($content === false) {
            return [];
        }
        return json_decode($content, true) ?: [];
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function writeData(string $file, array $data): void
    {
        file_put_contents($file, json_encode($data), LOCK_EX);
    }
}
