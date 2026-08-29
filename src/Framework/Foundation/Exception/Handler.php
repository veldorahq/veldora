<?php

declare(strict_types=1);

namespace Veldora\Framework\Foundation\Exception;

use Throwable;
use ErrorException;

class Handler
{
    // ──────────────────────────────────────────────────────────────────────
    // Registration
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Register PHP exception / error / shutdown handlers globally.
     * Must be called as early as possible (before any output or require).
     */
    public static function register(): void
    {
        $handler = new self();

        // 1. Uncaught exception handler
        set_exception_handler([$handler, 'handleException']);

        // 2. Turn PHP warnings / notices into ErrorExceptions
        set_error_handler(static function (int $level, string $message, string $file = '', int $line = 0): bool {
            if (error_reporting() & $level) {
                throw new ErrorException($message, 0, $level, $file, $line);
            }
            return false;
        });

        // 3. Fatal / parse / compile error catcher (runs on shutdown)
        register_shutdown_function(static function () use ($handler): void {
            $error = error_get_last();
            if (
                $error !== null && in_array($error['type'], [
                    E_ERROR,
                    E_PARSE,
                    E_CORE_ERROR,
                    E_COMPILE_ERROR,
                    E_USER_ERROR,
                    E_RECOVERABLE_ERROR,
                ], true)
            ) {
                // Clear any partial output that may have been buffered
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                $e = new ErrorException(
                    $error['message'],
                    0,
                    $error['type'],
                    $error['file'],
                    $error['line']
                );
                $handler->handleException($e);
            }
        });
    }

    /**
     * Static helper called from the catch block in public/index.php.
     */
    public static function handleThrowable(Throwable $e): never
    {
        (new self())->handleException($e);
        exit(1); // handleException calls exit but static analysis needs this
    }

    // ──────────────────────────────────────────────────────────────────────
    // Core handler
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Handle an uncaught exception — renders an appropriate page and exits.
     */
    public function handleException(Throwable $e): void
    {
        // Discard any buffered output so we render a clean page
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Validation exception: flash errors and redirect back, or return 422 JSON
        if ($e instanceof \Veldora\Framework\Validation\ValidationException) {
            $errors = $e->getErrors();
            $isJson = isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json');

            if ($isJson) {
                if (!headers_sent()) {
                    http_response_code(422);
                    header('Content-Type: application/json; charset=UTF-8');
                }
                echo json_encode([
                    'message' => 'The given data was invalid.',
                    'errors'  => $errors,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                exit(422);
            }

            if (function_exists('session')) {
                try {
                    session()->flash('errors', $errors);
                    session()->flash('_old_input', $_POST ?? []);
                } catch (\Throwable) {}
            }

            $referer = $_SERVER['HTTP_REFERER'] ?? '/';
            if (!headers_sent()) {
                header("Location: {$referer}");
            }
            exit(0);
        }

        // Determine status code and debug mode
        $statusCode = $this->resolveStatusCode($e);
        $isDebug = $this->isDebugMode();

        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: text/html; charset=UTF-8');
            header('Cache-Control: no-cache, no-store');
        }

        if ($statusCode === 404) {
            echo $this->render404Page($e);
        } elseif ($statusCode === 403) {
            echo $this->render403Page($e);
        } elseif ($isDebug) {
            echo $this->renderDebugPage($e, $statusCode);
        } else {
            echo $this->renderProductionPage($statusCode);
        }

        exit(1);
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    protected function resolveStatusCode(Throwable $e): int
    {
        // NotFoundException (router 404)
        if ($e instanceof NotFoundException) {
            return 404;
        }

        // Http exceptions that carry their own code
        if (method_exists($e, 'getStatusCode')) {
            $code = $e->getStatusCode();
            if ($code >= 400 && $code < 600) {
                return $code;
            }
        }

        // Exception code used as HTTP status
        $code = $e->getCode();
        if ($code === 403)
            return 403;
        if ($code === 404)
            return 404;

        return 500;
    }

    protected function isDebugMode(): bool
    {
        // env() helper may not yet be loaded for fatal boot errors, so be safe
        if (function_exists('env')) {
            $val = env('APP_DEBUG', true);
        } else {
            // Fallback: read .env manually
            $val = $this->readEnvDebug();
        }

        return in_array($val, [true, 'true', '1', 1, 'TRUE', 'on', 'yes'], true)
            || $val === null; // null = not set = default to debug in development
    }

    protected function readEnvDebug(): mixed
    {
        // Walk up to find .env file relative to public/
        $dir = dirname(__DIR__, 4); // template root from src/Framework/Foundation/Exception
        $envFile = $dir . '/.env';
        if (!file_exists($envFile))
            return null;

        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if (str_starts_with($line, '#'))
                continue;
            if (str_starts_with($line, 'APP_DEBUG=')) {
                return trim(substr($line, strlen('APP_DEBUG=')), " \t\"'");
            }
        }
        return null;
    }

    // ──────────────────────────────────────────────────────────────────────
    // CLI renderer
    // ──────────────────────────────────────────────────────────────────────

    protected function renderConsoleException(Throwable $e): void
    {
        $class = get_class($e);
        fwrite(STDERR, "\n\033[41;37;1m {$class} \033[0m \033[97m" . $e->getMessage() . "\033[0m\n");
        fwrite(STDERR, "  \033[90mIn\033[0m \033[33m" . $e->getFile() . "\033[0m \033[90mon line\033[0m \033[33m" . $e->getLine() . "\033[0m\n\n");
        fwrite(STDERR, "\033[1mStack Trace:\033[0m\n");
        fwrite(STDERR, $e->getTraceAsString() . "\n\n");
    }

    // ──────────────────────────────────────────────────────────────────────
    // Shared CSS / HTML helpers
    // ──────────────────────────────────────────────────────────────────────

    private function sharedHead(string $title, bool $includeMonoFont = false): string
    {
        $monoLink = $includeMonoFont
            ? '<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">'
            : '<link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    {$monoLink}
HTML;
    }

    private function velodraLogo(): string
    {
        // Minimal "V" wordmark
        return <<<SVG
<svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
  <rect width="32" height="32" rx="8" fill="#8b5cf6"/>
  <path d="M8 9L16 23L24 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
SVG;
    }

    // ──────────────────────────────────────────────────────────────────────
    // 404 Page
    // ──────────────────────────────────────────────────────────────────────

    protected function render404Page(Throwable $e): string
    {
        $uri = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8');
        $logo = $this->velodraLogo();

        return <<<HTML
{$this->sharedHead('404 — Page Not Found')}
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #09090b;
            color: #a1a1aa;
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            -webkit-font-smoothing: antialiased;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 48px;
            text-decoration: none;
        }
        .brand-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #fafafa;
            letter-spacing: -0.02em;
        }
        .error-code {
            font-size: clamp(6rem, 20vw, 9rem);
            font-weight: 800;
            color: #1c1c22;
            letter-spacing: -0.05em;
            line-height: 1;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #27272a 0%, #18181b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fafafa;
            margin-bottom: 12px;
            letter-spacing: -0.02em;
        }
        .error-subtitle {
            font-size: 0.95rem;
            color: #52525b;
            margin-bottom: 8px;
            line-height: 1.6;
        }
        .error-uri {
            font-size: 0.85rem;
            color: #3f3f46;
            margin-bottom: 36px;
            font-family: monospace;
        }
        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .btn-primary {
            background: #8b5cf6;
            color: #fff;
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: background 0.15s ease, transform 0.1s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary:hover { background: #7c3aed; transform: translateY(-1px); }
        .btn-secondary {
            background: transparent;
            color: #71717a;
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid #27272a;
            transition: border-color 0.15s ease, color 0.15s ease;
        }
        .btn-secondary:hover { border-color: #52525b; color: #a1a1aa; }
        .divider {
            width: 1px;
            height: 60px;
            background: linear-gradient(to bottom, transparent, #27272a, transparent);
            margin: 40px 0;
        }
        .hint {
            font-size: 0.8rem;
            color: #3f3f46;
            text-align: center;
        }
    </style>
</head>
<body>
    <a href="/" class="brand">
        {$logo}
        <span class="brand-name">Veldora</span>
    </a>

    <div class="error-code">404</div>
    <h1 class="error-title">Page not found</h1>
    <p class="error-subtitle">The page you're looking for doesn't exist or has been moved.</p>
    <p class="error-uri">{$uri}</p>

    <div class="actions">
        <a href="/" class="btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Back to Home
        </a>
        <a onclick="history.back(); return false;" href="#" class="btn-secondary">Go Back</a>
    </div>

    <div class="divider"></div>
    <p class="hint">If you think this is a mistake, check your routes in <code>routes/web.php</code></p>
</body>
</html>
HTML;
    }

    // ──────────────────────────────────────────────────────────────────────
    // 403 Page
    // ──────────────────────────────────────────────────────────────────────

    protected function render403Page(Throwable $e): string
    {
        $uri = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8');
        $logo = $this->velodraLogo();

        return <<<HTML
{$this->sharedHead('403 — Forbidden')}
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #09090b;
            color: #a1a1aa;
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            -webkit-font-smoothing: antialiased;
        }
        .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 48px; text-decoration: none; }
        .brand-name { font-size: 1.1rem; font-weight: 700; color: #fafafa; letter-spacing: -0.02em; }
        .error-code {
            font-size: clamp(6rem, 20vw, 9rem);
            font-weight: 800;
            letter-spacing: -0.05em;
            line-height: 1;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #3b1f1f 0%, #1c1014 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .error-title { font-size: 1.5rem; font-weight: 700; color: #fafafa; margin-bottom: 12px; letter-spacing: -0.02em; }
        .error-subtitle { font-size: 0.95rem; color: #52525b; margin-bottom: 8px; line-height: 1.6; }
        .error-uri { font-size: 0.85rem; color: #3f3f46; margin-bottom: 36px; font-family: monospace; }
        .actions { display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; }
        .btn-primary {
            background: #ef4444;
            color: #fff;
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: background 0.15s, transform 0.1s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary:hover { background: #dc2626; transform: translateY(-1px); }
        .btn-secondary {
            background: transparent; color: #71717a; padding: 10px 24px;
            border-radius: 8px; text-decoration: none; font-size: 0.9rem;
            font-weight: 600; border: 1px solid #27272a;
            transition: border-color 0.15s, color 0.15s;
        }
        .btn-secondary:hover { border-color: #52525b; color: #a1a1aa; }
        .divider { width: 1px; height: 60px; background: linear-gradient(to bottom, transparent, #27272a, transparent); margin: 40px 0; }
        .hint { font-size: 0.8rem; color: #3f3f46; text-align: center; }
    </style>
</head>
<body>
    <a href="/" class="brand">
        {$logo}
        <span class="brand-name">Veldora</span>
    </a>

    <div class="error-code">403</div>
    <h1 class="error-title">Access denied</h1>
    <p class="error-subtitle">You don't have permission to access this resource.</p>
    <p class="error-uri">{$uri}</p>

    <div class="actions">
        <a href="/" class="btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Back to Home
        </a>
        <a onclick="history.back(); return false;" href="#" class="btn-secondary">Go Back</a>
    </div>

    <div class="divider"></div>
    <p class="hint">If you're signed in and still seeing this, you may lack the required role or permission.</p>
</body>
</html>
HTML;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Production 5xx Page
    // ──────────────────────────────────────────────────────────────────────

    protected function renderProductionPage(int $statusCode = 500): string
    {
        $logo = $this->velodraLogo();

        return <<<HTML
{$this->sharedHead("{$statusCode} — Server Error")}
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: #09090b;
            color: #a1a1aa;
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
            -webkit-font-smoothing: antialiased;
        }
        .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 48px; text-decoration: none; }
        .brand-name { font-size: 1.1rem; font-weight: 700; color: #fafafa; letter-spacing: -0.02em; }
        .card {
            background: #111114;
            border: 1px solid #1e1e24;
            border-radius: 16px;
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .icon-wrap {
            width: 56px; height: 56px;
            background: rgba(239,68,68,.1);
            border: 1px solid rgba(239,68,68,.2);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
        }
        h1 { font-size: 1.4rem; font-weight: 700; color: #fafafa; margin-bottom: 10px; letter-spacing: -0.02em; }
        p  { font-size: 0.9rem; line-height: 1.7; color: #52525b; margin-bottom: 28px; }
        .btn {
            background: #8b5cf6;
            color: #fff;
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-block;
            transition: background 0.15s, transform 0.1s;
        }
        .btn:hover { background: #7c3aed; transform: translateY(-1px); }
        .code-badge {
            font-size: 0.75rem;
            color: #ef4444;
            background: rgba(239,68,68,.08);
            border: 1px solid rgba(239,68,68,.15);
            padding: 3px 10px;
            border-radius: 99px;
            display: inline-block;
            margin-bottom: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <a href="/" class="brand">{$logo}<span class="brand-name">Veldora</span></a>
    <div class="card">
        <div class="icon-wrap">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="12 2 22 20 2 20"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <div class="code-badge">{$statusCode} Server Error</div>
        <h1>Something went wrong</h1>
        <p>An unexpected error occurred on our end. Please try again in a moment. If the issue persists, contact support.</p>
        <a href="/" class="btn">← Back to Home</a>
    </div>
</body>
</html>
HTML;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Laravel/Ignition-style Debug Page
    // ──────────────────────────────────────────────────────────────────────

    protected function renderDebugPage(Throwable $e, int $statusCode = 500): string
    {
        $exceptionName = get_class($e);
        $shortName = basename(str_replace('\\', '/', $exceptionName));
        $message = htmlspecialchars($e->getMessage() ?: 'No message provided.', ENT_QUOTES, 'UTF-8');
        $file = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');
        $line = $e->getLine();

        // ── Code snippet ────────────────────────────────────────────────
        $codeSnippet = '';
        if (file_exists($e->getFile())) {
            $lines = file($e->getFile());
            if ($lines !== false) {
                $start = max(0, $line - 8);
                $end = min(count($lines) - 1, $line + 6);

                for ($i = $start; $i <= $end; $i++) {
                    $currLine = $i + 1;
                    $lineContent = htmlspecialchars($lines[$i] ?? '', ENT_QUOTES, 'UTF-8');
                    $isErr = ($currLine === $line);
                    $cls = $isErr ? 'code-line error-line' : 'code-line';
                    $arrow = $isErr ? '<span class="err-arrow">▶</span>' : '<span class="err-arrow"> </span>';
                    $codeSnippet .= "<div class=\"{$cls}\">{$arrow}<span class=\"line-num\">{$currLine}</span><span class=\"line-code\">{$lineContent}</span></div>";
                }
            }
        } else {
            $codeSnippet = '<div class="code-line"><span class="line-code" style="color:#52525b">Source file not readable.</span></div>';
        }

        // ── Stack trace ─────────────────────────────────────────────────
        $traceHtml = '';
        $fullTraceStr = $e->getTraceAsString();

        foreach ($e->getTrace() as $index => $step) {
            $tFile = isset($step['file']) ? htmlspecialchars($step['file'], ENT_QUOTES, 'UTF-8') : '[internal]';
            $tLine = $step['line'] ?? '-';
            $tClass = $step['class'] ?? '';
            $tType = $step['type'] ?? '';
            $tFunc = htmlspecialchars($step['function'] ?? '', ENT_QUOTES, 'UTF-8');
            $call = $tClass ? htmlspecialchars("{$tClass}{$tType}", ENT_QUOTES, 'UTF-8') . "<b>{$tFunc}()</b>" : "<b>{$tFunc}()</b>";

            $isApp = !str_contains($tFile, 'vendor') && !str_contains($tFile, '[internal');
            $tagClass = $isApp ? 'tag-app' : 'tag-vendor';
            $tagText = $isApp ? 'App' : 'Vendor';

            $traceHtml .= <<<HTML
            <div class="trace-item">
                <div class="trace-header">
                    <span class="trace-num">#{$index}</span>
                    <span class="trace-call">{$call}</span>
                    <span class="trace-tag {$tagClass}">{$tagText}</span>
                </div>
                <div class="trace-file">{$tFile}<span class="trace-lnum">:{$tLine}</span></div>
            </div>
HTML;
        }

        // ── Request / env info ──────────────────────────────────────────
        $phpVersion = PHP_VERSION;
        $reqUri = htmlspecialchars($_SERVER['REQUEST_URI'] ?? '/', ENT_QUOTES, 'UTF-8');
        $reqMethod = htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'GET', ENT_QUOTES, 'UTF-8');
        $reqHost = htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost', ENT_QUOTES, 'UTF-8');
        $memMB = round(memory_get_usage(true) / 1024 / 1024, 2);
        $copyPL = addslashes("{$exceptionName}: {$e->getMessage()}\nIn {$e->getFile()}:{$e->getLine()}\n\n{$fullTraceStr}");

        return <<<HTML
{$this->sharedHead("{$shortName}: {$message}", true)}
    <style>
        :root {
            --bg:       #09090b;
            --surface:  #111115;
            --code-bg:  #07070a;
            --border:   #1e1e24;
            --border2:  #2e2e38;
            --txt:      #e4e4e7;
            --muted:    #71717a;
            --danger:   #ef4444;
            --danger-bg: rgba(239,68,68,.09);
            --accent:   #8b5cf6;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg);
            color: var(--txt);
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 15px;
            line-height: 1.5;
            min-height: 100vh;
            padding: 2.5rem 1.5rem 5rem;
            -webkit-font-smoothing: antialiased;
        }
        .wrap { max-width: 1160px; margin: 0 auto; }

        /* ── Top bar ─────────────────────────── */
        .topbar {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 2rem;
        }
        .topbar-brand { font-size: 0.95rem; font-weight: 700; color: #fff; }
        .topbar-sep { color: var(--border2); font-size: 1.2rem; }
        .topbar-label { font-size: 0.85rem; color: var(--muted); }

        /* ── Error banner ────────────────────── */
        .banner {
            background: var(--surface);
            border: 1px solid var(--border);
            border-top: 3px solid var(--danger);
            border-radius: 12px;
            padding: 1.75rem 2rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1.5rem;
        }
        .banner-meta { display: flex; align-items: center; gap: 8px; margin-bottom: 0.6rem; flex-wrap: wrap; }
        .pill {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 6px;
        }
        .pill-exc  { color: #fca5a5; background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.2); }
        .pill-http { color: var(--muted); background: #16161b; border: 1px solid var(--border); }
        .pill-php  { color: #a78bfa; background: rgba(139,92,246,.1); border: 1px solid rgba(139,92,246,.2); }
        .banner-title {
            font-size: 1.45rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.025em;
            line-height: 1.3;
            margin-bottom: 0.6rem;
        }
        .banner-file {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: var(--muted);
            word-break: break-all;
        }
        .banner-file b { color: #38bdf8; }

        /* ── Copy button ─────────────────────── */
        .copy-btn {
            flex-shrink: 0;
            background: #16161b;
            border: 1px solid var(--border);
            color: var(--txt);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: border-color .15s, background .15s;
        }
        .copy-btn:hover { border-color: var(--border2); background: #1e1e26; }

        /* ── Code viewer ─────────────────────── */
        .code-panel {
            background: var(--code-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 1.25rem;
            font-family: 'JetBrains Mono', monospace;
        }
        .code-header {
            background: #0e0e13;
            border-bottom: 1px solid var(--border);
            padding: 9px 16px;
            font-size: 0.75rem;
            color: var(--muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .code-body { padding: 12px 0; overflow-x: auto; }
        .code-line {
            display: flex;
            align-items: baseline;
            padding: 1.5px 16px;
            border-left: 3px solid transparent;
            gap: 0;
        }
        .err-arrow { width: 16px; color: var(--danger); font-size: 0.65rem; flex-shrink: 0; }
        .line-num {
            width: 42px;
            text-align: right;
            margin-right: 16px;
            color: #3f3f46;
            user-select: none;
            flex-shrink: 0;
            font-size: 0.8rem;
        }
        .line-code { color: #d4d4d8; white-space: pre; font-size: 0.82rem; }
        .error-line {
            background: rgba(239,68,68,.07);
            border-left-color: var(--danger);
        }
        .error-line .line-num { color: #f87171; font-weight: 700; }
        .error-line .line-code { color: #fff; font-weight: 600; }

        /* ── Bottom grid ─────────────────────── */
        .grid { display: grid; grid-template-columns: 3fr 2fr; gap: 1.25rem; }
        @media (max-width: 800px) {
            .grid { grid-template-columns: 1fr; }
            .banner { flex-direction: column; }
        }

        /* ── Panel ───────────────────────────── */
        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.4rem;
        }
        .panel-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .panel-title svg { flex-shrink: 0; }

        /* ── Stack trace ─────────────────────── */
        .trace-item {
            padding: 9px 11px;
            border-radius: 8px;
            border: 1px solid #19191f;
            background: #0d0d10;
            margin-bottom: 6px;
            transition: border-color .12s;
        }
        .trace-item:hover { border-color: var(--border2); }
        .trace-header { display: flex; align-items: center; gap: 8px; margin-bottom: 3px; flex-wrap: wrap; }
        .trace-num { font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: #52525b; }
        .trace-call { font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; color: #c4c4cc; flex: 1; word-break: break-all; }
        .trace-call b { color: #fff; }
        .trace-tag { font-size: 0.65rem; padding: 2px 6px; border-radius: 4px; font-weight: 700; flex-shrink: 0; }
        .tag-app { background: rgba(139,92,246,.15); color: #a78bfa; border: 1px solid rgba(139,92,246,.25); }
        .tag-vendor { background: #18181b; color: #52525b; border: 1px solid #27272a; }
        .trace-file { font-family: 'JetBrains Mono', monospace; font-size: 0.72rem; color: #3f3f46; word-break: break-all; }
        .trace-lnum { color: #38bdf8; font-weight: 600; }

        /* ── Info table ──────────────────────── */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #16161b;
            font-size: 0.82rem;
            gap: 12px;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: var(--muted); flex-shrink: 0; }
        .info-val { font-family: 'JetBrains Mono', monospace; color: #fff; font-weight: 500; text-align: right; word-break: break-all; }
        .method-pill { background: var(--accent); color: #fff; font-size: 0.68rem; padding: 2px 6px; border-radius: 4px; font-weight: 700; }
    </style>
</head>
<body>
<div class="wrap">

    <!-- Top brand bar -->
    <div class="topbar">
        <svg width="22" height="22" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="32" height="32" rx="7" fill="#8b5cf6"/>
            <path d="M8 9L16 23L24 9" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span class="topbar-brand">Veldora</span>
        <span class="topbar-sep">/</span>
        <span class="topbar-label">Debug Mode</span>
    </div>

    <!-- Error banner -->
    <div class="banner">
        <div>
            <div class="banner-meta">
                <span class="pill pill-exc">{$shortName}</span>
                <span class="pill pill-http">HTTP {$statusCode}</span>
                <span class="pill pill-php">PHP {$phpVersion}</span>
            </div>
            <h1 class="banner-title">{$message}</h1>
            <div class="banner-file">{$file}<b>:{$line}</b></div>
        </div>
        <button class="copy-btn" onclick="copyError(this)">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="9" y="9" width="13" height="13" rx="2"/>
                <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
            </svg>
            Copy error
        </button>
    </div>

    <!-- Code snippet -->
    <div class="code-panel">
        <div class="code-header">
            <span>{$file}</span>
            <span>Line {$line}</span>
        </div>
        <div class="code-body">{$codeSnippet}</div>
    </div>

    <!-- Stack trace + env grid -->
    <div class="grid">

        <!-- Stack trace -->
        <div class="panel">
            <div class="panel-title">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                    <line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/>
                    <line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
                Stack Trace
            </div>
            {$traceHtml}
        </div>

        <!-- Request & Environment -->
        <div class="panel">
            <div class="panel-title">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                Request &amp; Environment
            </div>
            <div class="info-row">
                <span class="info-label">Request</span>
                <span class="info-val"><span class="method-pill">{$reqMethod}</span> {$reqUri}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Host</span>
                <span class="info-val">{$reqHost}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Framework</span>
                <span class="info-val">Veldora v<?= \Veldora\Framework\Foundation\Application::VERSION ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">PHP</span>
                <span class="info-val">{$phpVersion}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Memory</span>
                <span class="info-val">{$memMB} MB</span>
            </div>
        </div>

    </div>
</div>

<script>
function copyError(btn) {
    const txt = `{$copyPL}`;
    navigator.clipboard.writeText(txt).then(() => {
        btn.innerHTML = '<span style="color:#34d399">✔ Copied!</span>';
        setTimeout(() => {
            btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg> Copy error';
        }, 2500);
    });
}
</script>
</body>
</html>
HTML;
    }
}
