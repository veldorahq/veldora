@extends('layouts.app')

@section('content')
<?php
use Veldora\Framework\Foundation\Application;
$version    = Application::VERSION;
$phpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
$appName    = env('APP_NAME', 'Veldora');
$appEnv     = env('APP_ENV', 'local');
$dbDriver   = env('DB_CONNECTION', 'sqlite');
?>
<style>
/* ── Welcome Container ─────────────────────────────────────────────── */
.welcome-container {
    max-width: 960px;
    margin: 0 auto;
    padding: 2.5rem 0 4rem;
}

/* ── Hero Section ──────────────────────────────────────────────────── */
.hero {
    text-align: center;
    margin-bottom: 3.5rem;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.78rem;
    font-weight: 500;
    color: #a78bfa;
    background: rgba(139, 92, 246, 0.08);
    border: 1px solid rgba(139, 92, 246, 0.2);
    padding: 6px 14px;
    border-radius: 9999px;
    margin-bottom: 1.5rem;
    backdrop-filter: blur(8px);
}

.hero-badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 8px #10b981;
}

.hero-title {
    font-size: clamp(2.4rem, 5vw, 3.5rem);
    font-weight: 800;
    letter-spacing: -0.04em;
    line-height: 1.15;
    color: #ffffff;
    margin-bottom: 1.25rem;
}

.hero-title .gradient-text {
    background: linear-gradient(135deg, #c4b5fd 0%, #8b5cf6 50%, #6d28d9 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-subtitle {
    font-size: 1.1rem;
    color: var(--text-muted);
    max-width: 580px;
    margin: 0 auto 2rem;
    line-height: 1.6;
}

.hero-subtitle code {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.9em;
    color: #e2e8f0;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    padding: 2px 6px;
    border-radius: 4px;
}

/* ── Quick Terminal Box ────────────────────────────────────────────── */
.cli-box {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: rgba(17, 17, 20, 0.9);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 10px 16px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.85rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
    transition: border-color 0.2s;
}

.cli-box:hover {
    border-color: rgba(139, 92, 246, 0.4);
}

.cli-prompt {
    color: #64748b;
    user-select: none;
}

.cli-command {
    color: #38bdf8;
    font-weight: 500;
}

.cli-copy-btn {
    background: transparent;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 4px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
}

.cli-copy-btn:hover {
    color: #ffffff;
    background: rgba(255, 255, 255, 0.08);
}

/* ── Resource Grid ─────────────────────────────────────────────────── */
.resource-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
    margin-bottom: 3.5rem;
}

.resource-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1.5rem;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
}

.resource-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(139, 92, 246, 0.3), transparent);
    opacity: 0;
    transition: opacity 0.2s;
}

.resource-card:hover {
    border-color: rgba(139, 92, 246, 0.35);
    background: var(--surface-hover);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.resource-card:hover::before {
    opacity: 1;
}

.card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 0.85rem;
}

.card-icon-wrapper {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: rgba(139, 92, 246, 0.1);
    border: 1px solid rgba(139, 92, 246, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #a78bfa;
    flex-shrink: 0;
}

.card-title {
    font-size: 1.05rem;
    font-weight: 600;
    color: #ffffff;
}

.card-desc {
    font-size: 0.875rem;
    color: var(--text-muted);
    line-height: 1.6;
    margin-bottom: 1.25rem;
}

.card-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.825rem;
    font-weight: 500;
    color: #a78bfa;
    transition: gap 0.15s ease;
}

.resource-card:hover .card-action {
    gap: 9px;
    color: #c4b5fd;
}

/* ── Environment Status Bar ────────────────────────────────────────── */
.status-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(17, 17, 20, 0.6);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 12px 20px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.8rem;
    color: var(--text-muted);
    flex-wrap: wrap;
    gap: 12px;
}

.status-items {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.status-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.status-item strong {
    color: #e2e8f0;
    font-weight: 500;
}

.status-divider {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: var(--border-hover);
}

@media (max-width: 640px) {
    .resource-grid {
        grid-template-columns: 1fr;
    }
    .status-bar {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<div class="welcome-container">

    <!-- ── Hero Section ────────────────────────────────────────────── -->
    <div class="hero">
        <div class="hero-badge">
            <span class="hero-badge-dot"></span>
            Veldora Framework &middot; v<?= htmlspecialchars($version) ?>
        </div>

        <h1 class="hero-title">
            Welcome to <span class="gradient-text"><?= htmlspecialchars($appName) ?></span>
        </h1>

        <p class="hero-subtitle">
            Get started by editing <code>app/Controllers/HomeController.php</code> or explore the resources below to build your application.
        </p>

        <div class="cli-box">
            <span class="cli-prompt">$</span>
            <span class="cli-command">php veldora serve</span>
            <button class="cli-copy-btn" onclick="copySnippet('php veldora serve', this)" title="Copy command" aria-label="Copy command">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- ── Resource Cards Grid ─────────────────────────────────────── -->
    <div class="resource-grid">

        <!-- Documentation Card -->
        <a href="https://veldora.modrao.com/docs" target="_blank" rel="noopener" class="resource-card">
            <div>
                <div class="card-header">
                    <div class="card-icon-wrapper">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                        </svg>
                    </div>
                    <div class="card-title">Documentation</div>
                </div>
                <div class="card-desc">
                    Comprehensive guides on routing, ActiveRecord ORM, controllers, middleware, and authentication.
                </div>
            </div>
            <div class="card-action">
                <span>Read documentation</span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>
        </a>

        <!-- UI Components Card -->
        <a href="https://veldora.modrao.com/components" target="_blank" rel="noopener" class="resource-card">
            <div>
                <div class="card-header">
                    <div class="card-icon-wrapper">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </div>
                    <div class="card-title">UI Components</div>
                </div>
                <div class="card-desc">
                    Explore pre-designed, accessible UI components ready to be dropped into your templates.
                </div>
            </div>
            <div class="card-action">
                <span>Browse components</span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>
        </a>

        <!-- CLI Generators Card -->
        <a href="https://veldora.modrao.com/docs/5-the-basics-controllers" target="_blank" rel="noopener" class="resource-card">
            <div>
                <div class="card-header">
                    <div class="card-icon-wrapper">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="4 17 10 11 4 5"></polyline>
                            <line x1="12" y1="19" x2="20" y2="19"></line>
                        </svg>
                    </div>
                    <div class="card-title">CLI Generators</div>
                </div>
                <div class="card-desc">
                    Scaffold controllers, models, migrations, seeders, and requests effortlessly using the CLI tool.
                </div>
            </div>
            <div class="card-action">
                <span>Explore commands</span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>
        </a>

        <!-- Community & GitHub Card -->
        <a href="https://github.com/veldorahq" target="_blank" rel="noopener" class="resource-card">
            <div>
                <div class="card-header">
                    <div class="card-icon-wrapper">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path>
                        </svg>
                    </div>
                    <div class="card-title">GitHub Repository</div>
                </div>
                <div class="card-desc">
                    Contribute to the framework, report issues, and follow ongoing development on GitHub.
                </div>
            </div>
            <div class="card-action">
                <span>View repository</span>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>
        </a>

    </div>

    <!-- ── Environment Status Bar ──────────────────────────────────── -->
    <div class="status-bar">
        <div class="status-items">
            <span class="status-item">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                </svg>
                <span>Environment: <strong><?= htmlspecialchars($appEnv) ?></strong></span>
            </span>
            <span class="status-divider"></span>
            <span class="status-item">
                <span>PHP: <strong>v<?= htmlspecialchars($phpVersion) ?></strong></span>
            </span>
            <span class="status-divider"></span>
            <span class="status-item">
                <span>Database: <strong><?= htmlspecialchars($dbDriver) ?></strong></span>
            </span>
        </div>
        <div>
            <span>Veldora <strong>v<?= htmlspecialchars($version) ?></strong></span>
        </div>
    </div>

</div>

<script>
function copySnippet(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
        setTimeout(() => { btn.innerHTML = originalHtml; }, 2000);
    });
}
</script>
@endsection
