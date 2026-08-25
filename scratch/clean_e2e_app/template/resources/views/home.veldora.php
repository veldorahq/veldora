@extends('layouts.app')

@section('content')
<style>
    /* ── Hero ────────────────────────────────────────────── */
    .welcome-hero {
        text-align: center;
        max-width: 640px;
        margin: 1.5rem auto 3.5rem;
    }

    .hero-icon {
        width: 48px;
        height: 48px;
        fill: var(--accent);
        margin-bottom: 1.5rem;
        filter: drop-shadow(0 0 16px rgba(139, 92, 246, 0.4));
    }

    .welcome-title {
        font-size: 2.25rem;
        font-weight: 700;
        letter-spacing: -0.025em;
        margin-bottom: 0.75rem;
        color: #ffffff;
    }

    .welcome-subtitle {
        font-size: 1.05rem;
        color: var(--text-muted);
        line-height: 1.6;
    }

    /* ── Cards Grid ──────────────────────────────────────── */
    .features-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
        max-width: 960px;
        margin: 0 auto;
    }

    .feature-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.5rem;
        transition: border-color 0.2s, background 0.2s;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .feature-card:hover {
        border-color: var(--border-hover);
        background: var(--surface-hover);
    }

    .feature-top {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 1.25rem;
    }

    .feature-icon-box {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: rgba(139, 92, 246, 0.1);
        border: 1px solid rgba(139, 92, 246, 0.2);
        color: var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .feature-title {
        font-size: 1.05rem;
        font-weight: 600;
        color: #ffffff;
        margin-bottom: 0.25rem;
    }

    .feature-desc {
        font-size: 0.875rem;
        color: var(--text-muted);
        line-height: 1.5;
    }

    .feature-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--accent);
        text-decoration: none;
        transition: gap 0.15s ease;
    }

    .feature-link:hover {
        gap: 9px;
        color: #a78bfa;
    }

    /* ── Snippet Box ─────────────────────────────────────── */
    .snippet-box {
        background: #060608;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.8rem;
        overflow: hidden;
    }

    .snippet-text {
        color: #38bdf8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .copy-btn {
        background: transparent;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: color 0.15s, background 0.15s;
        flex-shrink: 0;
    }

    .copy-btn:hover {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.08);
    }

    /* ── Responsive ──────────────────────────────────────── */
    @media (max-width: 768px) {
        .features-grid {
            grid-template-columns: 1fr;
        }
        .welcome-title {
            font-size: 1.85rem;
        }
    }
</style>

<!-- Hero -->
<div class="welcome-hero">
    <svg class="hero-icon" viewBox="0 0 24 24">
        <polygon points="12,2 22,20 2,20"></polygon>
    </svg>
    <h1 class="welcome-title">Welcome to your application</h1>
    <p class="welcome-subtitle">
        Veldora gives you a clean, lightweight foundation to build modern web applications in PHP.
    </p>
</div>

<!-- Features & Commands Grid -->
<div class="features-grid">

    <!-- Card 1: Documentation -->
    <div class="feature-card">
        <div class="feature-top">
            <div class="feature-icon-box">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                </svg>
            </div>
            <div>
                <h3 class="feature-title">Documentation</h3>
                <p class="feature-desc">Explore comprehensive guides on routing, controllers, middleware, and ActiveRecord models.</p>
            </div>
        </div>
        <div>
            <a href="https://veldora.modrao.com/docs" target="_blank" rel="noopener" class="feature-link">
                Read documentation <span>→</span>
            </a>
        </div>
    </div>

    <!-- Card 2: UI Components -->
    <div class="feature-card">
        <div class="feature-top">
            <div class="feature-icon-box">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                    <line x1="9" y1="21" x2="9" y2="9"></line>
                </svg>
            </div>
            <div>
                <h3 class="feature-title">UI Components</h3>
                <p class="feature-desc">Browse 21 production-ready, accessible Blade components you can copy directly into your project.</p>
            </div>
        </div>
        <div>
            <a href="https://veldora.modrao.com/components" target="_blank" rel="noopener" class="feature-link">
                Browse components <span>→</span>
            </a>
        </div>
    </div>

    <!-- Card 3: Dev Server -->
    <div class="feature-card">
        <div class="feature-top">
            <div class="feature-icon-box">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                </svg>
            </div>
            <div>
                <h3 class="feature-title">Development Server</h3>
                <p class="feature-desc">Start your local server with instant reloading and colorized request logs.</p>
            </div>
        </div>
        <div class="snippet-box">
            <span class="snippet-text">php veldora serve</span>
            <button class="copy-btn" onclick="copyCode('php veldora serve', this)" title="Copy to clipboard">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Card 4: Code Generators -->
    <div class="feature-card">
        <div class="feature-top">
            <div class="feature-icon-box">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="16 18 22 12 16 6"></polyline>
                    <polyline points="8 6 2 12 8 18"></polyline>
                </svg>
            </div>
            <div>
                <h3 class="feature-title">CLI Generators</h3>
                <p class="feature-desc">Scaffold controllers, models, migrations, and authentication in seconds.</p>
            </div>
        </div>
        <div class="snippet-box">
            <span class="snippet-text">php veldora make:controller PostController</span>
            <button class="copy-btn" onclick="copyCode('php veldora make:controller PostController', this)" title="Copy to clipboard">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                </svg>
            </button>
        </div>
    </div>

</div>

@endsection
