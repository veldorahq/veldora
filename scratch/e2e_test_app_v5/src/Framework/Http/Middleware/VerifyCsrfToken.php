<?php

declare(strict_types=1);

namespace Veldora\Framework\Http\Middleware;

use Closure;
use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Http\MiddlewareInterface;

class VerifyCsrfToken implements MiddlewareInterface
{
    /**
     * HTTP verbs that do not require CSRF verification.
     *
     * @var array<string>
     */
    protected array $safeVerbs = ['GET', 'HEAD', 'OPTIONS'];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array($request->getMethod(), $this->safeVerbs, true)) {
            if (!$request->hasSession()) {
                return new Response('Session not available — CSRF cannot be verified.', 500);
            }

            $sessionToken = $request->session()->csrfToken();

            // Token may come from form field or X-CSRF-TOKEN header
            $inputToken = $request->input('_token');
            if (!is_string($inputToken) || $inputToken === '') {
                $inputToken = $request->header('X-CSRF-TOKEN', '');
            }

            if (!is_string($inputToken) || !hash_equals($sessionToken, $inputToken)) {
                return new Response('CSRF token mismatch.', 419, ['Content-Type' => 'text/plain']);
            }
        }

        return $next($request);
    }
}
