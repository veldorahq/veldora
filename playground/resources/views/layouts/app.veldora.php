<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Veldora' }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="/css/veldora-ui.css">

    <style>
        :root {
            --bg: #0a0a0c;
            --surface: #111114;
            --surface-hover: #17171c;
            --border: #222228;
            --border-hover: #33333f;
            --text: #f0f0f3;
            --text-muted: #8c8c9a;
            --accent: #8b5cf6;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            background-image: radial-gradient(circle at 50% 0%, rgba(139, 92, 246, 0.08) 0%, transparent 60%);
            background-repeat: no-repeat;
        }

        /* ── Header ─────────────────────────────────────────── */
        .site-header {
            width: 100%;
            border-bottom: 1px solid var(--border);
            background: rgba(10, 10, 12, 0.8);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .header-container {
            max-width: 1080px;
            margin: 0 auto;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-link {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #fff;
            font-weight: 700;
            font-size: 1.05rem;
            letter-spacing: -0.01em;
        }

        .brand-icon {
            width: 22px;
            height: 22px;
            fill: var(--accent);
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-nav a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: color 0.15s ease;
        }

        .header-nav a:hover {
            color: #ffffff;
        }

        .github-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: var(--surface);
            color: var(--text) !important;
            font-size: 0.8rem !important;
            transition: border-color 0.15s, background 0.15s;
        }

        .github-link:hover {
            border-color: var(--border-hover);
            background: var(--surface-hover);
        }

        /* ── Main Content ───────────────────────────────────── */
        main {
            flex: 1;
            max-width: 1080px;
            width: 100%;
            margin: 0 auto;
            padding: 3rem 1.5rem 4rem;
        }

        /* ── Footer ─────────────────────────────────────────── */
        .site-footer {
            border-top: 1px solid var(--border);
            padding: 1.75rem 1.5rem;
            background: rgba(10, 10, 12, 0.4);
        }

        .footer-container {
            max-width: 1080px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.825rem;
            color: var(--text-muted);
            flex-wrap: wrap;
            gap: 12px;
        }

        .footer-info {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.78rem;
        }

        @media (max-width: 640px) {
            .header-nav .hide-mobile {
                display: none;
            }
            .footer-container {
                flex-direction: column;
                text-align: center;
                gap: 8px;
            }
        }
    </style>
</head>
<body>

    <header class="site-header">
        <div class="header-container">
            <a href="/" class="brand-link">
                <svg class="brand-icon" viewBox="0 0 24 24">
                    <polygon points="12,2 22,20 2,20"></polygon>
                </svg>
                <span>Veldora</span>
            </a>

            <nav class="header-nav">
                <a href="https://veldora.modrao.com/docs" target="_blank" rel="noopener" class="hide-mobile">Documentation</a>
                <a href="https://veldora.modrao.com/components" target="_blank" rel="noopener" class="hide-mobile">Components</a>
                <a href="https://github.com/veldorahq" target="_blank" rel="noopener" class="github-link">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                    </svg>
                    GitHub
                </a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="footer-container">
            <div>
                Crafted by <strong>Shahriyar Fahim</strong>
            </div>
            <div class="footer-info">
                Veldora v0.4.0 (PHP v<?= PHP_VERSION ?>)
            </div>
        </div>
    </footer>

    <script>
        function copyCode(text, btn) {
            navigator.clipboard.writeText(text).then(() => {
                const originalSvg = btn.innerHTML;
                btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                setTimeout(() => {
                    btn.innerHTML = originalSvg;
                }, 2000);
            });
        }
    </script>
</body>
</html>
