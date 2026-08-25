<?php

declare(strict_types=1);

namespace Veldora\Framework\Http\Middleware;

use Closure;
use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Http\MiddlewareInterface;
use Veldora\Framework\Auth\Auth;

class Authenticate implements MiddlewareInterface
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login');
        }

        return $next($request);
    }
}
