<?php

declare(strict_types=1);

// ── Bootstrap: autoloader + PSR-4 + Handler registration ─────────────────
// Handler::register() is called inside autoload.php so fatal parse/compile
// errors are caught even before the app instance is created.
require_once dirname(__DIR__) . '/bootstrap/autoload.php';

// ── Boot Application ──────────────────────────────────────────────────────
try {
    /** @var \Veldora\Framework\Foundation\Application $app */
    $app = require_once dirname(__DIR__) . '/bootstrap/app.php';

    $app->boot();

    $request = \Veldora\Framework\Http\Request::capture();
    $router  = $app->get(\Veldora\Framework\Http\Router::class);

    // Load web routes
    require_once $app->routesPath('web.php');

    // Dispatch and send
    $router->dispatch($request)->send();

} catch (\Throwable $e) {
    \Veldora\Framework\Foundation\Exception\Handler::handleThrowable($e);
}