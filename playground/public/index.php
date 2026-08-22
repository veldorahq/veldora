<?php

declare(strict_types=1);

try {
    /** @var Veldora\Framework\Foundation\Application $app */
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    $app->boot();

    $request = Veldora\Framework\Http\Request::capture();
    $router  = $app->get(Veldora\Framework\Http\Router::class);

    // Load web routes
    require_once $app->routesPath('web.php');

    // Dispatch request
    $response = $router->dispatch($request);
    $response->send();
} catch (\Throwable $e) {
    (new \Veldora\Framework\Foundation\Exception\Handler())->handleException($e);
}