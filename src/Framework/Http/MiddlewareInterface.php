<?php

declare(strict_types=1);

namespace Veldora\Framework\Http;

interface MiddlewareInterface
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, \Closure $next): Response;
}
