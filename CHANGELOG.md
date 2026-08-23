# Changelog

All notable changes to the **Veldora PHP Framework** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
- Updated all official documentation references to `https://veldorahq.dev`.

---

## [0.3.0] - 2026-07-15
- Initial release of Veldora Core MVC engine.
- Active Record Model support with relationships (`hasOne`, `hasMany`, `belongsTo`).
- Template compiler supporting Blade directives (`@if`, `@foreach`, `@extends`, `@section`).
