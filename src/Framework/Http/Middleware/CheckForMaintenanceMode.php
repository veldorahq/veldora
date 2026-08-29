<?php

declare(strict_types=1);

namespace Veldora\Framework\Http\Middleware;

use Veldora\Framework\Http\MiddlewareInterface;
use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Foundation\Application;

/**
 * Middleware to check if the application is in maintenance mode.
 * Renders a clean 503 page when maintenance mode is active.
 */
class CheckForMaintenanceMode implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response
    {
        $app = Application::getInstance();
        $downFile = $app->storagePath('framework/down');

        if (!file_exists($downFile)) {
            return $next($request);
        }

        // Check if request provides a valid bypass secret
        $payload = json_decode((string) file_get_contents($downFile), true) ?? [];
        $secret = $payload['secret'] ?? null;

        if ($secret && ($request->query('secret') === $secret)) {
            return $next($request);
        }

        $html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Service Unavailable</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#09090b;color:#a1a1aa;font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;text-align:center}
.card{max-width:420px;padding:2.5rem;border:1px solid #27272a;border-radius:1rem}
h1{color:#f4f4f5;font-size:1.5rem;margin-bottom:.75rem}
p{font-size:.95rem;line-height:1.6}
.code{font-size:4rem;font-weight:800;background:linear-gradient(135deg,#8b5cf6,#6366f1);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:1rem}
</style></head><body>
<div class="card"><div class="code">503</div>
<h1>Down for Maintenance</h1>
<p>The application is temporarily unavailable. Please check back shortly.</p>
</div></body></html>';

        return new Response($html, 503, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
