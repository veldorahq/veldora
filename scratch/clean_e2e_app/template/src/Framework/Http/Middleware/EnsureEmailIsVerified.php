<?php

declare(strict_types=1);

namespace Veldora\Framework\Http\Middleware;

use Closure;
use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Http\MiddlewareInterface;
use Veldora\Framework\Auth\Auth;

class EnsureEmailIsVerified implements MiddlewareInterface
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return Response::redirect('/login');
        }

        $user = Auth::user();

        if ($user !== null && empty($user->email_verified_at)) {
            return Response::redirect('/email/verify');
        }

        return $next($request);
    }
}
