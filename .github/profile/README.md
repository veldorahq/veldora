<div align="center">

<img src="https://raw.githubusercontent.com/veldorahq/veldora/main/public/favicon.svg" width="80" height="80" alt="Veldora Logo">

# Veldora

**The Modern PHP 8.2+ Framework You Actually Own.**

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Latest Release](https://img.shields.io/badge/Release-v0.6.0-8b5cf6?style=flat-square)](https://github.com/veldorahq/veldora-core/releases)
[![Packagist Framework](https://img.shields.io/packagist/v/veldora/framework?style=flat-square&logo=packagist&logoColor=white&color=F28D1A)](https://packagist.org/packages/veldora/framework)
[![Packagist UI](https://img.shields.io/packagist/v/veldora/ui?style=flat-square&logo=packagist&logoColor=white&color=10B981)](https://packagist.org/packages/veldora/ui)
[![npm create-veldora-app](https://img.shields.io/npm/v/create-veldora-app?style=flat-square&logo=npm&color=CB3837)](https://www.npmjs.com/package/create-veldora-app)
[![VS Code Extension](https://img.shields.io/visual-studio-marketplace/v/veldora.veldora-vscode?style=flat-square&logo=visualstudiocode&color=007ACC)](https://marketplace.visualstudio.com/items?itemName=veldora.veldora-vscode)
[![Documentation](https://img.shields.io/badge/Documentation-veldora.modrao.com-10B981?style=flat-square)](https://veldora.modrao.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue?style=flat-square)](LICENSE)

</div>

---

## 🌟 Welcome to the Veldora Ecosystem

Veldora is a next-generation PHP framework conceived to provide **developer joy, exceptional speed, and zero bloat**. It gives you a full-featured MVC stack — expressive routing, ActiveRecord ORM, session authentication, 49 built-in CLI commands with `executeDirect()` zero-dependency execution, queue workers, mailers, and a 41+ UI component system with multi-aesthetic design (Skeuomorphic 3D, Neumorphic Soft UI, Flat Minimalist, and Glassmorphic).

---

## 📦 Official Organization Repositories

| Repository | Description | Package / Tag | Latest Version |
|---|---|---|:---:|
| [**`veldora`**](https://github.com/veldorahq/veldora) | Official Starter Application and project skeleton template | `veldora/veldora` | **`v0.6.0`** |
| [**`veldora-core`**](https://github.com/veldorahq/veldora-core) | Core MVC framework engine, router, ORM, CLI, auth, views | `veldora/framework` | **`v0.6.0`** |
| [**`veldora-ui`**](https://github.com/veldorahq/veldora-ui) | 41+ UI components supporting Skeuomorphic, Neumorphic, Flat & Glass | `veldora/ui` | **`v0.6.0`** |
| [**`create-veldora-app`**](https://github.com/veldorahq/create-veldora-app) | Zero-configuration interactive CLI project scaffolder | `npm: create-veldora-app` | **`v0.6.0`** |
| [**`veldora-vscode`**](https://github.com/veldorahq/veldora-vscode) | Syntax highlighting, 32+ snippets, and icons for `.veldora.php` | VS Code Marketplace | **`v0.6.0`** |
| [**`veldora-docs`**](https://github.com/veldorahq/veldora-docs) | Official documentation website source code | [veldora.modrao.com](https://veldora.modrao.com) | **`v0.6.0`** |

---

## 🚀 Quick Start in 10 Seconds

### Option A: Via `create-veldora-app` (Recommended)

```bash
npx create-veldora-app my-app
cd my-app
php veldora serve
```

### Option B: Via Composer

```bash
composer create-project veldora/veldora my-app
cd my-app
php veldora serve
```

Your app is immediately live at **`http://localhost:8000`** with real-time colored server request logs!

---

## ⚡ What's New in v0.6.0

- 🧱 **Extended Blueprint Schema Builder**: Added native helpers for `date()`, `dateTime()`, `decimal()`, `float()`, `bigInteger()`, `unsignedInteger()`, `foreignId()`, `json()`, and `enum()` with SQLite & MySQL compilation.
- 🗄️ **Model ORM Enhancements**: Added `Model::create()`, `Model::firstOrCreate()`, and `Model::updateOrCreate()`.
- 🔗 **Relationship QueryBuilder Proxying**: `Relation` base class proxies all query methods so `$post->comments()->where(...)->count()` works natively.
- 🔄 **Response Chaining**: `Response::with($key, $value)` enables direct session flashing on redirects (`return back()->with('error', '...')`).
- 🌐 **Global View & JSON Helpers**: `view($template, $data)` and `json($data)` helpers in `src/helpers.php` for clean controller responses.
- 🛡️ **Resilient Auth Guard**: `SessionGuard` gracefully handles users without `remember_token` columns.
- ⚙️ **49 Built-in CLI Commands**: Added `php veldora make:policy <Name>` generator.

---

## 📚 Resources & Community

- 📖 **Documentation**: [https://veldora.modrao.com](https://veldora.modrao.com)
- 💬 **GitHub Discussions**: [https://github.com/veldorahq/veldora/discussions](https://github.com/veldorahq/veldora/discussions)
- 🐛 **Issue Tracker**: Report bugs in the respective repository issue tracker
- 📄 **License**: Open-source under the [MIT License](https://opensource.org/licenses/MIT)

---

<div align="center">
  <sub>Architected and maintained by <strong>Shahriyar Fahim</strong> and the Veldora Community.</sub>
</div>
