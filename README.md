<div align="center">

# ▲ Veldora

**A modern PHP framework you actually own.**

Built-in zero-config CLI • 41+ Built-in Commands • Blade-like Template Engine • 21 Accessible UI Components • Modern MVC Architecture

<br>

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![npm version](https://img.shields.io/npm/v/create-veldora-app?style=flat-square&logo=npm&color=CB3837)](https://www.npmjs.com/package/create-veldora-app)
[![VS Code Extension](https://img.shields.io/badge/VS_Code-v0.5.1-007ACC?style=flat-square&logo=visualstudiocode&logoColor=white)](https://github.com/veldorahq/veldora-vscode)
[![License: MIT](https://img.shields.io/badge/License-MIT-8b5cf6?style=flat-square)](LICENSE)
[![Docs](https://img.shields.io/badge/Documentation-veldora.modrao.com-10B981?style=flat-square)](https://veldora.modrao.com)

</div>

---

## ✦ Why Veldora?

- **Zero Composer Installation Required to Start** — Scaffolding creates an instantly bootable application with built-in PSR-4 autoloader.
- **You Own Everything** — UI components (`app/components/ui/`) and backend models are copied directly into your codebase. No vendor lock-in.
- **41+ Full-featured CLI Commands** — Built-in generators (`make:*`), database migration manager (`migrate:*`, `db:*`), optimization pipeline (`optimize`), and diagnostics (`doctor`, `about`).
- **Expressive `.veldora.php` Template Engine** — Blade-inspired syntax (`{{ }}`, `{!! !!}`, `@if`, `@foreach`, `<x-component>`) with pre-compiled view caching.
- **Production-Ready Exception & Diagnostics** — Ignition-style dark error screens with interactive code viewer, error-line highlighter, and one-click stack trace copy.

---

## 🚀 Quick Start

### 1. Scaffold a New Project
```bash
npx create-veldora-app my-app
cd my-app
```

### 2. Start the Development Server
```bash
php veldora serve
```
Your application will be live at `http://localhost:8000`.

---

## 🛠️ CLI Highlights (41+ Commands)

```bash
# Development & System Health
php veldora serve                    # Start local development server
php veldora doctor                   # Run system diagnostics and extension checks
php veldora about                    # Display environment, DB & cache details
php veldora key:generate             # Generate 32-character APP_KEY in .env

# Code Generators
php veldora make:controller PostController
php veldora make:model Post -m       # Model with migration
php veldora make:middleware EnsureAdmin
php veldora make:request StorePostRequest
php veldora make:job ProcessPodcast
php veldora make:auth                # Complete authentication scaffolding

# Database Management
php veldora migrate                  # Run pending migrations
php veldora migrate:status           # Display status of all migrations
php veldora db:seed                  # Seed database with sample data
php veldora db:show                  # Display schema & table counts

# Performance & Optimization
php veldora route:list               # Formatted table of registered routes
php veldora optimize                 # Cache config, routes, and views for production
php veldora optimize:clear           # Clear all framework caches
```

---

## 🎨 UI Component System

Copy pre-styled, accessible UI components directly into `resources/views/components/`:

```bash
php veldora add button card modal input badge alert tabs
```

Use them seamlessly in any template:
```html
<x-card title="Welcome to Veldora">
    <p>Build fast, beautiful PHP apps with zero hassle.</p>
    <x-button variant="primary">Get Started</x-button>
</x-card>
```

---

## 📦 Ecosystem Repositories

| Repository | Description |
|---|---|
| [veldorahq/create-veldora-app](https://github.com/veldorahq/create-veldora-app) | Official npm initializer & scaffolding package |
| [veldorahq/veldora-ui](https://github.com/veldorahq/veldora-ui) | UI Component Registry & Styles |
| [veldorahq/veldora-vscode](https://github.com/veldorahq/veldora-vscode) | Official VS Code extension (syntax, snippets, file icons) |
| [veldorahq/veldora-docs](https://github.com/veldorahq/veldora-docs) | Official documentation web application |

---

## 📄 License & Author

- **Author**: Shahriyar Fahim
- **License**: [MIT](LICENSE)
- **Website**: [https://veldora.modrao.com](https://veldora.modrao.com)
