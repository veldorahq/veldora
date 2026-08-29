# Changelog

All notable changes to the **Veldora PHP Framework** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.5.3] - 2026-08-30

### Fixed
- **Clean Starter Skeleton**: Streamlined starter application package distribution for Composer `create-project` and npm scaffolder.
- **Component Registry**: Synchronized all multi-aesthetic UI components and styles into starter template.

---

## [0.5.2] - 2026-08-30

### Added
- **Multi-Aesthetic Design System**: Added Skeuomorphic 3D tactile, Flat Minimalist 2D, Neumorphic Soft UI, and Glassmorphic variants for `Radio`, `Checkbox`, `Switch`, and `Button` components (`.vui-radio-skeuo`, `.vui-checkbox-skeuo`, `.vui-switch-skeuo`, `.vui-btn-skeuo`, `.vui-btn-flat`, `.vui-btn-neumorphic`, `.vui-btn-glass`).
- **Modern SaaS Sidebar**: High-performance dashboard navigation with Workspace switcher, Quick Search (⌘K), categorized sections, active state indicator, badge pills, and user profile footer.
- **Interactive Toast Engine**: Ambient notification system with `window.showToast(message, type, duration)` API and copy action feedback.
- **ComponentRegistry Multi-Aesthetic Support**: Template generators updated for `<x-button>`, `<x-checkbox>`, `<x-switch>`, and `<x-radio>`.
- **Packagist Release Sync**: Unified package version metadata to `0.5.2` across framework, UI, and scaffolding packages.

### Fixed
- Fixed horizontal inline-flex row alignment for custom radio & checkbox controls so the disc/box icon and title text always sit cleanly on the same line.
- Fixed Packagist VCS release tag version matching.

---

## [0.5.1] - 2026-08-28

### Added
- Enhanced routing pipeline with strict pattern constraint matching.
- Dark mode custom property palette enhancements.

### Fixed
- Component registry CLI installer directory path resolution.

---

## [0.5.0] - 2026-08-25

### Added
- **DB Facade**: `statement()`, `select()`, `selectOne()`, `insert()`, `update()`, `delete()`, `transaction()`.
- **SoftDeletes Trait**: `deleted_at` auto-management, `withTrashed()`, `onlyTrashed()`, `restore()`, `forceDelete()`.
- **Model Lifecycle Events**: `creating`, `created`, `updating`, `updated`, `deleting`, `deleted` hooks.
- **Named Route URLs**: `route('name', ['id' => 1])` global helper with parameter substitution.
- **ThrottleRequests Middleware**: Token-bucket rate limiter with `429 Too Many Requests` response.
- **CheckForMaintenanceMode Middleware**: `storage/framework/.down` status + bypass `?secret=`.
- **Complete Auth Scaffold**: `php veldora make:auth` generates full auth layer with native `.veldora.php` views.
- **PasswordBroker**: HMAC token-based password reset with expiry validation.
- **Console Polyfill**: Zero-dependency Symfony\Console shim (`src/Console/Polyfill.php`).
- **New UI Components**: `footer`, `rating`, `switch`, `pagination`, `skeleton`, `empty`, `divider`, `drawer`, `popover`, `confirm`, `datepicker`, `fileupload`, `combobox`, `inputgroup`, `stat`, `datatable`, `timeline`, `stepper`, `sidebar`, `container` (41+ total components).

---

## [0.4.0] - 2026-08-23

### Added
- **41 Built-in CLI Commands**: Complete suite of code generators (`make:controller`, `make:model`, `make:migration`, `make:middleware`, `make:request`, `make:seeder`, `make:command`, `make:job`, `make:event`, `make:listener`, `make:mail`, `make:component`, `make:auth`), database management (`migrate`, `migrate:rollback`, `migrate:fresh`, `migrate:status`, `db:seed`, `db:wipe`, `db:show`), performance optimization (`optimize`, `optimize:clear`, `route:cache`, `config:cache`, `view:cache`), and system diagnostics (`doctor`, `about`, `key:generate`, `storage:link`, `env`, `env:encrypt`, `env:decrypt`).
- **39 Production-Ready UI Components**: Full collection of pre-styled, accessible UI components added to `Veldora UI` and docs interactive previewer (Button, Input, Textarea, Select, Checkbox, Radio, Badge, Alert, Card, Modal, Spinner, Avatar, Dropdown, Navbar, Toast, Tabs, Accordion, Progress, Tooltip, Breadcrumb, Table, Switch, Pagination, Skeleton, Empty, Divider, Drawer, Popover, Confirm, DatePicker, FileUpload, Combobox, InputGroup, Stat, DataTable, Timeline, Stepper, Sidebar, Container).
- **Veldora VS Code Extension v0.5.2**: Official Veldora file icon with `v-icon.png` theme, 55+ IntelliSense snippets covering all 39 UI components, syntax highlighting for `.veldora` and `*.veldora.php` files.
- **Ignition-style Developer Exception Screen**: Dark-mode error viewer with highlighted source code lines, stack trace viewer, request/environment details table, and one-click clipboard error copy.
- **Fatal Shutdown Error Handler**: `register_shutdown_function` capturing compile and parse errors before execution.
- **Built-in Zero-Config Autoloader**: Standalone PSR-4 autoloader running applications with zero Composer dependencies out of the box.
- **Custom Error Pages**: Modern, themed templates for HTTP 404 (Not Found), 403 (Forbidden), and 500 (Production Server Error).

### Changed
- Refactored `public/index.php` to boot through early handler registration.
- Colorized development server logs (`php veldora serve`) with real-time HTTP status, methods, and response times.
- Updated all official documentation references to `https://veldora.modrao.com`.

---

## [0.3.0] - 2026-07-15
- Initial release of Veldora Core MVC engine.
- Active Record Model support with relationships (`hasOne`, `hasMany`, `belongsTo`).
- Template compiler supporting Blade directives (`@if`, `@foreach`, `@extends`, `@section`).
