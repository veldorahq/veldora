<?php

/**
 * Veldora - A PHP Framework For Web Artisans
 *
 * This file allows us to emulate Apache's "mod_rewrite" functionality from the
 * built-in PHP web server. This provides a convenient way to test a Veldora
 * application without having installed a "real" web server software here.
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? ''
);

// If the file exists in the public directory, serve it directly
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

require_once __DIR__ . '/public/index.php';
