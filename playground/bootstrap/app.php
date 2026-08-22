<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Instantiate Veldora Application
$app = new Veldora\Framework\Foundation\Application(
    dirname(__DIR__)
);

// Register HTTP Router
$app->singleton(Veldora\Framework\Http\Router::class, function ($app) {
    return new Veldora\Framework\Http\Router($app);
});

// Register View Engine
$app->singleton(Veldora\Framework\View\Engine::class, function ($app) {
    return new Veldora\Framework\View\Engine($app);
});

// Register SQLite Database Connection
$app->singleton(Veldora\Framework\Database\Connection::class, function ($app) {
    return new Veldora\Framework\Database\Connection([
        'driver' => 'sqlite',
        'database' => $app->storagePath('database.sqlite')
    ]);
});

return $app;