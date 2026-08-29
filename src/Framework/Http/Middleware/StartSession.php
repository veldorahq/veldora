<?php

declare(strict_types=1);

namespace Veldora\Framework\Http\Middleware;

use Closure;
use Veldora\Framework\Foundation\Application;
use Veldora\Framework\Http\CookieJar;
use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Http\MiddlewareInterface;
use Veldora\Framework\Session\Session;

class StartSession implements MiddlewareInterface
{
    /**
     * Handle an incoming request.
     *
     * Boots the session driver and attaches the session instance to the request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $app = Application::getInstance();

        /** @var Session $session */
        $session = $app->get(Session::class);
        $session->start();

        $request->setSession($session);

        $response = $next($request);

        $session->save();

        if ($app->has(CookieJar::class)) {
            /** @var CookieJar $cookieJar */
            $cookieJar = $app->get(CookieJar::class);
            $cookieName = (string) config('session.cookie', 'veldora_session');
            $cookieJar->queue($cookieName, $session->getId(), 0, '/', null, false, true, 'Lax', false);
        }

        return $response;
    }
}
