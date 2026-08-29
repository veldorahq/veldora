<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Veldora Zero-Config Built-in Autoloader
|--------------------------------------------------------------------------
|
| This autoloader maps PSR-4 namespaces for App, Framework, and UI
| without requiring Composer to be run first. If Composer dependencies
| are installed later, vendor/autoload.php is seamlessly included.
|
*/

$basePath = dirname(__DIR__);

// 1. If Composer vendor autoload exists, load it first
$vendorAutoload = $basePath . '/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

// 1.5. If Symfony Console is not installed via Composer, load built-in polyfill
if (!class_exists(\Symfony\Component\Console\Command\Command::class, false)) {
    $polyfill = $basePath . '/src/Framework/Console/Polyfill.php';
    if (file_exists($polyfill)) {
        require_once $polyfill;
    }
}

// 2. Register PSR-4 autoloader for Veldora application & bundled core
spl_autoload_register(function (string $class) use ($basePath): void {
    $prefixes = [
        'App\\'               => $basePath . '/app/',
        'Veldora\\Framework\\' => $basePath . '/src/Framework/',
        'Veldora\\UI\\'        => $basePath . '/src/UI/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            continue;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// 3. Load global framework helpers
$helpersFile = $basePath . '/src/Framework/helpers.php';
if (file_exists($helpersFile)) {
    require_once $helpersFile;
}

// 4. Register the global exception / error / fatal-shutdown handler
//    This must happen here so that even fatal errors in app/routes are caught
\Veldora\Framework\Foundation\Exception\Handler::register();
