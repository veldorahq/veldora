<?php

declare(strict_types=1);

/** @var Veldora\Framework\Http\Router $router */

$router->get('/', [App\Controllers\HomeController::class, 'index']);