<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Veldora Autoloader
|--------------------------------------------------------------------------
|
| Includes Composer's vendor/autoload.php and registers global
| error & exception handlers.
|
*/

$basePath = dirname(__DIR__);

// 1. If Composer vendor autoload exists, load it first
$vendorAutoload = $basePath . '/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

// 2. Register PSR-4 fallback autoloader for App namespace
spl_autoload_register(function (string $class) use ($basePath): void {
    $prefix = 'App\\';
    $baseDir = $basePath . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// 3. Register the global exception / error / fatal-shutdown handler
if (class_exists(\Veldora\Framework\Foundation\Exception\Handler::class)) {
    \Veldora\Framework\Foundation\Exception\Handler::register();
}
