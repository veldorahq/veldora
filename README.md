<div align="center">

<img src="https://raw.githubusercontent.com/veldorahq/veldora/main/public/favicon.svg" width="64" height="64" alt="Veldora Logo">

# Veldora Starter Application

**The modern PHP framework you actually own.**

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-8b5cf6?style=flat-square)](LICENSE)
[![Docs](https://img.shields.io/badge/Documentation-veldora.modrao.com-10B981?style=flat-square)](https://veldora.modrao.com)

</div>

---

## 🚀 Quick Start

### 1. Start the Development Server

```bash
php veldora serve
```

Your application will be live at `http://localhost:8000`.

---

## 🛠️ Common Commands

```bash
# System Diagnostics & Info
php veldora doctor                   # Extension & environment health check
php veldora about                    # Display environment, DB & cache details
php veldora key:generate             # Generate application key

# Authentication & Database
php veldora make:auth                # Scaffold full login, register & profile views
php veldora migrate                  # Run database migrations
php veldora db:show                  # Inspect database tables and row counts

# UI Components (41+ Available)
php veldora ui:list                  # List all available components
php veldora add button modal footer  # Copy components directly to resources/views/components/

# Code Generation
php veldora make:controller PostController
php veldora make:model Post -m
php veldora make:middleware EnsureAdmin
```

---

## 📚 Documentation

For full guides, routing, database ORM, templating, and component documentation, visit **[https://veldora.modrao.com](https://veldora.modrao.com)**.

---

## 📄 License

The Veldora Framework is open-sourced software licensed under the [MIT license](LICENSE).
