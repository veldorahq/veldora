<?php

declare(strict_types=1);

namespace Veldora\Framework\Http\Middleware;

use Closure;
use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Http\MiddlewareInterface;
use Veldora\Framework\Auth\Auth;

class EnsureUserIsAdmin implements MiddlewareInterface
{
    /**
     * Handle an incoming request.
     *
     * Requires 'auth' middleware to run first so a user is guaranteed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login');
        }

        if (!Auth::isAdmin()) {
            // 403 Forbidden — user is authenticated but not an admin
            return new Response('Forbidden', 403, ['Content-Type' => 'text/plain']);
        }

        return $next($request);
    }
}
