<?php

declare(strict_types=1);

require_once __DIR__ . '/autoload.php';

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

// Register Database Connection
$app->singleton(Veldora\Framework\Database\Connection::class, function ($app) {
    $dbConnection = env('DB_CONNECTION', 'sqlite');
    if ($dbConnection === 'sqlite') {
        $dbPath = env('DB_DATABASE', $app->basePath('database/veldora.sqlite'));
        if (!str_starts_with($dbPath, '/') && !preg_match('/^[A-Za-z]:/', $dbPath)) {
            $dbPath = $app->basePath($dbPath);
        }
        return new Veldora\Framework\Database\Connection([
            'driver'   => 'sqlite',
            'database' => $dbPath,
        ]);
    }

    return new Veldora\Framework\Database\Connection([
        'driver'   => $dbConnection,
        'host'     => env('DB_HOST', '127.0.0.1'),
        'port'     => (int) env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'veldora'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
    ]);
});

return $app;