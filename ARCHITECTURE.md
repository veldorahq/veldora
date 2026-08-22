# VELDORA — Master Build Prompt for AI Coding Agent

> **How to use this document:** This is the single source of truth for building Veldora.
> Give this entire file to your AI coding agent (Cursor / Windsurf / Antigravity / Claude Code) as the system/project prompt. Because this is a large, multi-month framework, **do not ask the agent to build everything in one shot**. Use the "Build Phases" section to drive work session by session, always pointing the agent back to this file for architecture decisions. Keep this file in the repo root as `ARCHITECTURE.md` so every future session has full context.

---

## 1. Identity

- **Name:** Veldora
- **Tagline:** _"A PHP framework you actually own."_
- **One-line pitch:** Veldora is a full-stack PHP framework with a built-in CMS, a shadcn/ui-style component ownership model, and a Vercel/Linear-grade default design system — built for developers who are tired of ugly PHP admin panels and vendor-locked UI packages.
- **License:** Open source (MIT), free forever. No paid tier. Sustainability comes from community contribution, not monetization — design every system (plugins, themes, CLI) so outside contributors can extend Veldora without needing the core maintainer to do it personally.
- **Primary maintainer constraint:** Solo developer, part-time. Every architectural decision below is made with this constraint in mind — favor simplicity, convention-over-configuration, and code generation over runtime magic, because generated/owned code is easier to maintain long-term than a sprawling internal framework core.

---

## 2. Core Philosophy (do not violate these while building)

1. **Everything is yours.** UI components, once added via CLI, are copied into the user's own codebase (`app/components/ui/`). Veldora core never "owns" a component after generation. No `node_modules`-style black box.
2. **Beautiful by default.** A fresh `veldora new` install must look production-ready with zero configuration — dark mode, clean typography, no Bootstrap-y defaults.
3. **Convention over configuration**, but every convention must be overridable.
4. **No magic the developer can't read.** Prefer generated, readable PHP code over deep reflection/magic-method trickery, even if it's slightly more verbose. A solo maintainer (and outside contributors) must be able to debug this in 5 minutes.
5. **Small composable core, big optional plugin ecosystem.** The framework core should be the smallest thing that lets everything else be built as a plugin.

---

## 3. Tech Stack

- **Language:** PHP 8.3+ (use enums, readonly properties, first-class callable syntax, match expressions — write modern PHP, not PHP 5-style code)
- **Package manager:** Composer, PSR-4 autoloading
- **Database:** MySQL/MariaDB primary support, SQLite for local dev/testing, PostgreSQL as stretch goal
- **Frontend build:** Vite (for the admin panel and any JS the components need — Alpine.js for lightweight interactivity, no React/Vue dependency required for the framework itself)
- **CSS:** Utility-first, Tailwind-compatible output (ship a small first-party utility CSS layer inspired by Tailwind so components don't force a build step, but support Tailwind if the dev wants it)
- **Testing:** PHPUnit + Pest
- **Coding standard:** PSR-12, enforced via PHP-CS-Fixer
- **Static analysis:** PHPStan level 6+ minimum

---

## 4. Design Language Specification (CRITICAL — apply to every screen and every default component)

The single biggest differentiator of Veldora vs Laravel/WordPress is default visual quality. The agent must treat this as a hard requirement, not a nice-to-have.

**Inspiration:** shadcn/ui, Vercel Dashboard, Linear, GitHub, Radix UI.

**Rules:**

- Dark mode first (light mode is the secondary variant, not the default)
- Monochrome-first palette: neutral grays as the base, ONE accent color (configurable), used sparingly
- **No gradients. No glassmorphism. No drop-shadow-heavy "AI generated" look.**
- Flat surfaces, 1px borders using low-opacity foreground color, not heavy box-shadows
- Typography: a clean sans-serif system font stack by default (`ui-sans-serif, system-ui, ...`), generous line-height, restrained font-weight variation (400/500/600 only)
- Border radius: consistent small-to-medium radius scale (e.g., 6px/8px/12px), not overly rounded "bubbly" UI
- Icons: only from Veldora's own icon library (see §11) — never mix icon styles
- Spacing: use a strict 4px spacing scale, no arbitrary padding values
- Every component ships with `default`, `hover`, `focus-visible`, `disabled`, and `dark`/`light` states defined — never ship a component missing focus states (accessibility matters)

This design system must be encoded as **design tokens** (CSS custom properties) in `resources/css/tokens.css`, and every generated UI component must consume tokens, never hardcoded hex values.

---

## 5. Folder Structure

```
veldora-core/                  # the framework package itself (composer package: veldora/framework)
    src/
        Foundation/             # Application kernel, Container, ServiceProvider base
        Http/                   # Request, Response, Router, Middleware pipeline
        Database/                # Query Builder, Migrations, Schema, Connection
        Validation/
        Auth/
        Events/
        Queue/
        Cache/
        Mail/
        Storage/
        View/                    # .veldora template compiler/engine
        Console/                 # CLI kernel + built-in commands
        Support/                 # helpers, collections, str/arr utilities
    stubs/                       # code-generation stubs used by `make` and `add` commands

veldora-cms/                    # first-party CMS package, built ON TOP of veldora-core (not inside it)
    src/
        Content/                 # Pages, Posts, Categories, Tags
        Media/
        Menus/
        Settings/
        SeoManager/

veldora-ui/                     # the shadcn-style component registry (source of truth for `veldora add`)
    components/                  # each component: .veldora template + PHP class + metadata json
    icons/                       # SVG icon source files

create-veldora-app/             # the `veldora new` scaffolding CLI (separate composer/npm-installable tool)

# Example of a generated end-user application:
app/
    Controllers/
    Models/
    Components/ui/               # user-owned copies of components, created via `veldora add`
    Middleware/
    Services/
bootstrap/
config/
database/
    migrations/
    seeders/
    factories/
public/
resources/
    views/                        # .veldora template files
    css/
    js/
themes/
    default/
plugins/
routes/
    web.php
    api.php
storage/
tests/
```

**Agent instruction:** build `veldora-core`, `veldora-cms`, and `veldora-ui` as **separate Composer packages** from day one (even in a monorepo), not as one giant framework blob. This keeps the CMS and UI system optional/swappable and keeps the core small — critical for a solo-maintained project.

---

## 6. Build Phases

Even though full scope is in play, build in this order. Each phase should be a fully working, demoable increment — never leave a phase half-broken before moving on.

### Phase 0 — Foundation

- Composer monorepo setup, PSR-4 autoloading, CI (GitHub Actions: lint + PHPStan + PHPUnit)
- Application kernel + DI Container (autowiring via reflection, with the ability to bind interfaces to concrete classes in a `config/container.php`)
- Basic Request/Response objects, Router (static + dynamic segments + route groups + named routes), Middleware pipeline
- `veldora new` CLI scaffolds a runnable "Hello World" app

### Phase 1 — Database Layer

- Connection manager (MySQL/SQLite)
- Query Builder (fluent, parameterized queries only — no raw string concatenation, prevent SQL injection by design)
- Schema Builder + Migrations (`veldora migrate`, `migrate:rollback`, `migrate:fresh`)
- Basic ORM: Active Record–style models with relationships (hasOne, hasMany, belongsTo, belongsToMany), soft deletes, timestamps
- Seeders + Factories

### Phase 2 — Template Engine (`.veldora` files)

- Blade-inspired compiler: `{{ }}` escaped output, `{!! !!}` raw output, `@if/@foreach/@include/@extends/@section/@yield`
- Component syntax: `<x-button variant="primary">Save</x-button>` compiling to PHP component classes
- Slots support (default slot + named slots)
- Layouts + nested layouts
- Compiled template caching (compile `.veldora` → plain PHP once, cache, recompile only on change — never compile on every request in production)

### Phase 3 — Validation, Auth, Sessions

- Validation: rule-string syntax (`"email" => "required|email|unique:users"`) + custom rule classes
- Session/Cookie handling (secure, httpOnly, signed cookies)
- Auth: registration, login, logout, password reset via signed email links, email verification
- Middleware: `auth`, `guest`, `verified`
- CSRF protection built into every form helper by default (not opt-in)

### Phase 4 — UI Component System (`veldora add`) — the signature feature

- Build the component registry format: each component = `{name}.veldora` (template) + `{name}.json` (metadata: dependencies, description) + optional `{name}.php` (component class for stateful/complex components)
- `veldora add button` → copies files into `app/components/ui/Button.veldora` + registers Tailwind-compatible classes, fully editable afterward, zero framework coupling once copied
- Ship the first 15 components: Button, Input, Textarea, Select, Checkbox, Radio, Card, Table, Modal, Drawer, Toast, Tabs, Breadcrumb, Badge, Alert
- Every component must satisfy the Design Language spec in §4 — this is the differentiator, don't ship generic-looking components

### Phase 5 — Icon Library

- Original SVG icon set (not copied from Lucide — style-inspired only: 24x24 viewbox, 2px stroke, rounded linecap/linejoin, no fill)
- Start with ~120 commonly needed icons (nav/UI/file/action/social icons)
- Usage: `<Icon name="home" />` component + `icon('home')` helper function returning raw SVG string
- `veldora add icon {name}` for adding individual icons to a project (tree-shakeable — don't ship the whole icon set to every app)

### Phase 6 — CLI

- `veldora new {project}` — scaffold new app
- `veldora serve` — dev server
- `veldora make controller|model|migration|middleware|component|event|listener|job`
- `veldora add {component}` / `veldora add icon {name}`
- `veldora migrate` / `migrate:rollback` / `migrate:fresh` / `db:seed`
- `veldora plugin install {name}` / `plugin list`
- `veldora build` — production asset build (wraps Vite)
- Use Symfony Console component or build a lightweight equivalent — don't hand-roll argument parsing from scratch

### Phase 7 — CMS Core

- Content types: Pages, Posts, Categories, Tags (extensible — allow custom post types via plugin API, similar to WordPress CPTs but type-safe PHP classes, not stringly-typed)
- Media Library (upload, organize into folders, image variants/thumbnails)
- Menu Builder (drag-and-drop nested menu items)
- Settings API (typed settings, not a giant options blob)
- Basic SEO fields (title, meta description, OG image) per content item

### Phase 8 — Admin Panel

- Dashboard shell: sidebar nav, topbar, command palette (Cmd+K to jump anywhere — this is a huge DX/perception win, prioritize it)
- CRUD screens for Pages/Posts/Users/Media generated from the same UI component library (dogfood your own components — the admin panel IS the proof the design system works)
- Dark mode toggle, responsive layout
- Activity log (who changed what, when)
- Role-based access on every admin screen (re-validate server-side on every request, never trust client-side role checks)

### Phase 9 — Theme Engine

- `themes/{name}/` structure, `Theme::use("default")`
- Theme = a set of `.veldora` view overrides + its own `css/js` assets
- Theme switching without breaking the CMS/admin (admin panel is NOT themeable, only the public-facing site is)

### Phase 10 — Plugin System

- `plugins/{name}/` structure, each plugin is a Composer package with a `ServiceProvider`
- Plugin API surface: register routes, register admin menu items, register migrations, register middleware, register CLI commands, hook into events
- Event/hook system (`Event::listen`, `Hook::filter` — WordPress-style filter hooks are genuinely useful for a plugin ecosystem, consider including both an event bus AND a filter-hook system)
- Plugin manager UI in admin panel (list installed, enable/disable — actual "install from marketplace" is v2+, local plugin folder is enough for v1)

### Phase 11 — API Layer

- JSON Resources (transform models to consistent API responses)
- API route group with token-based auth (personal access tokens, Sanctum-style)
- Basic rate limiting middleware
- API versioning convention (`/api/v1/...`)

### Phase 12 — Remaining Systems

- Event system: Events + Listeners (sync by default)
- Queue: database driver first, Redis driver second (jobs table, `veldora queue:work`)
- Cache: file driver first, Redis second, unified `Cache::get/put/remember` API
- Mail: SMTP transport, `.veldora`-based mail templates, queued sending support
- Storage: local disk driver first, S3-compatible driver second, unified `Storage::disk('s3')->put(...)` API

### Phase 13 (explicitly labeled Roadmap — do not build now, just leave extension points)

Two-Factor Auth, Social Login (OAuth), full RBAC/Permissions UI, GraphQL, WebSocket/broadcasting, multi-tenancy, visual page builder, plugin marketplace, Docker one-command install, desktop installer. **Architect the Auth, Plugin, and Route systems so these can be added later without breaking changes** — e.g., make sure the Auth system has clean extension points for adding a `TwoFactorChallenge` middleware later, and the plugin ServiceProvider pattern should already support what a future plugin marketplace needs.

---

## 7. Coding Standards for the Agent

- PSR-12 formatting, enforced automatically — set up PHP-CS-Fixer and run it as part of `veldora build`/CI, don't rely on manual discipline
- Every public class/method that isn't trivially self-explanatory gets a PHPDoc block
- No `@ts-ignore`-style suppressions or `@phpstan-ignore` without a comment explaining why
- Never concatenate raw SQL — Query Builder or prepared statements only
- Never trust `$_GET`/`$_POST` directly — always go through the Request/Validation layer
- Every migration must have a working `down()` method
- Write PHPUnit/Pest tests alongside each Phase, not "later" — at minimum, test the Router, Query Builder, Validation rules, and Auth flows, since these are the highest-blast-radius systems if broken
- Commit in small, phase-aligned chunks with clear messages (`feat(core): add router with middleware pipeline`) so a solo maintainer can bisect issues later

---

## 8. Deliverables Checklist (agent should track this)

- [ ] Phase 0: Foundation + working `veldora new`
- [x] Phase 1: Database layer + migrations working
- [x] Phase 2: `.veldora` template engine compiling + caching
- [x] Phase 3: Auth + validation + CSRF
- [ ] Phase 4: First 15 UI components via `veldora add`, matching design spec
- [ ] Phase 5: Icon library (120+ icons) + `<Icon>` component
- [ ] Phase 6: Full CLI surface
- [ ] Phase 7: CMS core (Pages/Posts/Media/Menus/Settings)
- [ ] Phase 8: Admin panel dogfooding the UI library, with command palette
- [ ] Phase 9: Theme engine, at least 1 shippable "default" theme
- [ ] Phase 10: Plugin system + 1 example plugin (e.g., a simple contact-form plugin) proving the API works end to end
- [ ] Phase 11: API layer + JSON resources + token auth
- [ ] Phase 12: Events, Queue, Cache, Mail, Storage
- [ ] Docs site (even a simple `.veldora`-templated docs site, dogfooding the framework itself, is a strong proof point)

---

## 9. Notes for the Agent on Working With a Solo, Part-Time Maintainer

- Prefer finishing one phase completely (with tests) over starting three in parallel — half-finished systems are the #1 cause of solo-maintained OSS projects dying.
- When in doubt between "more powerful" and "more maintainable," choose maintainable — this project has no team to absorb complexity debt.
- Every new system should include a short `README.md` inside its package folder explaining _why_ it's built the way it is, not just what it does — this is for future-you and future contributors, not just current AI-agent context.

# VELDORA — Advanced Features Prompt (Phase 13+)

> **When to use this:** Only feed this to the AI agent AFTER Phases 0–12 from `VELDORA_MASTER_PROMPT.md` are working and tested. This prompt assumes the core framework, CMS, UI system, and admin panel already exist. Every feature below plugs into that foundation — do not restructure core systems to fit these, extend them.
>
> **Why these exist:** These were originally scoped as "roadmap only" because they add real long-term maintenance weight for a solo/part-time maintainer. They're being promoted to build-now because differentiating from Laravel's ecosystem dominance requires feature parity in these areas, not just a nicer UI layer. Build each one fully, with tests, and with a `README.md` in its package folder explaining the design decisions — this matters even more here, since these are the systems most likely to accumulate confusing edge cases over time.

---

## 13.1 — Two-Factor Authentication

- TOTP-based (Google Authenticator / Authy compatible) — use a standard algorithm implementation (RFC 6238), don't hand-roll the crypto
- QR code generation for enrollment (server-side SVG/PNG QR, no external API dependency)
- Recovery codes: generate 8–10 single-use codes on enrollment, hashed at rest like passwords
- Middleware: `TwoFactorChallenge` — sits after `auth`, redirects to a challenge screen if 2FA is enabled but not yet verified for the session
- Admin panel: user-facing settings screen to enable/disable 2FA, regenerate recovery codes
- Must NOT break existing Phase 3 auth flow — 2FA is opt-in per user, not forced globally by default (allow a config flag to force it org-wide later)

## 13.2 — Social Login (OAuth)

- Build a thin OAuth2 client abstraction (`SocialProvider` interface) rather than depending on a single third-party package, so new providers are easy to add
- Ship first-party drivers for: Google, GitHub, Discord (good coverage for Veldora's likely developer audience)
- Account linking: if a verified email from OAuth matches an existing local account, link instead of duplicate — but require the user to confirm this via a "link accounts" flow, never auto-merge silently (security-sensitive)
- Store provider tokens encrypted, never in plaintext
- Config-driven: adding a new provider should only require a config entry + client ID/secret, no core code changes

## 13.3 — Full RBAC (Roles & Permissions)

- Data model: Users ↔ Roles ↔ Permissions (many-to-many both levels), plus support for direct user-level permission overrides
- Permission checks via a simple API: `$user->can('posts.edit')`, `@can('posts.edit')` template directive, `can:posts.edit` route middleware
- Seed a sensible default role set (Admin, Editor, Author, Viewer) but make roles fully editable from the admin UI — this is not hardcoded
- Admin panel: full CRUD UI for roles/permissions, assignable per user, with a clear visual matrix (permissions as rows, roles as columns — checkbox grid) built using the existing Table/Checkbox UI components, not a new bespoke UI pattern
- Every admin screen already scaffolded in Phase 8 must now respect these permissions server-side — go back and add permission checks to Phase 7/8 CRUD controllers, don't leave them checking only `auth`

## 13.4 — GraphQL API Layer

- Add as an alternate API surface alongside the REST layer from Phase 11 — do not replace REST, some consumers will want REST and some GraphQL
- Schema-first approach: define `.graphql` schema files, generate resolver stubs from them
- Resolvers should reuse the same underlying service/repository layer as the REST JSON Resources — no duplicated business logic between REST and GraphQL
- N+1 query prevention: implement dataloader-style batching for relationship resolution from the start, not as an afterthought
- Auth: reuse the existing token-based auth from Phase 11, GraphQL requests authenticate the same way REST does

## 13.5 — Real-Time / Broadcasting (WebSockets)

- Event broadcasting: extend the Phase 12 Event system so any event can be marked `ShouldBroadcast`
- Transport: support a self-hostable WebSocket server (e.g., a small dedicated PHP/Swoole or Node-based broadcast server that Veldora ships as an optional companion process) — do not require a paid third-party service (like Pusher) as the only option, since Veldora is free/self-hosted by philosophy
- Channels: public, private (auth-gated), and presence channels (who's currently viewing)
- Client-side: ship a small first-party JS client (`veldora-echo.js`) for subscribing to channels — keep it framework-agnostic (works with plain JS or Alpine.js, no React dependency)
- Document clearly that this is an optional add-on process, not required for a basic Veldora site to run

## 13.6 — Multi-Tenancy

- Approach: single-database, tenant-scoped-by-column model as the default (simplest to self-host and maintain) — implement it as a global query scope automatically applied to tenant-aware models, not something every query has to remember manually
- Tenant resolution: by subdomain by default (`tenant1.app.com`), pluggable to support custom domains later
- Isolate: database rows (via scope), file storage (per-tenant storage prefix), and cache keys (per-tenant cache prefix) — all three, not just the database
- Provide a clear `Tenantable` trait/interface for models that need tenant scoping, and make sure CMS/plugin systems from Phases 7–10 respect it
- This is the highest-risk feature for introducing security bugs (data leaking across tenants) — write explicit tests asserting tenant isolation on every tenant-aware model and API endpoint, this is non-negotiable

## 13.7 — Plugin Marketplace (Scaffolding, Not Full Hosted Service)

- A full hosted marketplace (payments, reviews, auto-updates) is out of scope for a solo maintainer — build the scaffolding that makes it possible later without over-committing now
- `veldora plugin search {query}` / `veldora plugin install {name}` — pointed at a simple static JSON index (a `plugins.json` manifest hosted on GitHub Pages or similar) rather than a full backend service
- Plugin manifest format: name, version, description, compatibility range, install source (git URL or zip URL), checksum for integrity verification
- This keeps the door open for a real marketplace later (swap the static index for a real API) without needing one now

## 13.8 — Docker One-Command Install

- `docker-compose.yml` covering: app container (PHP-FPM + Nginx or a single PHP built-in server for simplicity), MySQL, Redis, and optionally the WebSocket broadcast server from §13.5
- `veldora new --docker` should scaffold this automatically
- Provide sane defaults (`.env.example` matching the compose services) so `docker compose up` genuinely works with zero manual config on a fresh clone
- Document the non-Docker install path equally well — Docker should be an option, not the only supported path (keeps the barrier to entry low for contributors who don't use Docker)

## 13.9 — Visual Page Builder (Scoped Down)

Full drag-and-drop visual builders (like Elementor) are a multi-year product on their own — scope this down to something achievable:

- **Not** a full canvas-based drag-and-drop editor in v1 of this feature
- **Instead:** a block-based editor (WordPress Gutenberg–style, simpler) where each "block" maps directly to one of the existing UI components from Phase 4 (Card, Text, Image, Button, Columns, Spacer) — this reuses the component library instead of building a parallel rendering system
- Admin UI: a sidebar of available blocks, click-to-insert, drag-to-reorder (reordering only, not free-form canvas positioning) — this is achievable and still feels "visual" without the huge engineering cost of a true canvas editor
- Output: blocks serialize to structured JSON stored per-page, rendered server-side back into `.veldora` component calls — never store raw HTML from the builder (security + theme-consistency risk)
- If a true canvas-based builder is wanted later, this block-based version becomes the content model underneath it — not wasted work

---

## Updated Deliverables Checklist Addendum

- [ ] 13.1 Two-Factor Auth (TOTP + recovery codes + admin toggle)
- [ ] 13.2 Social login (Google, GitHub, Discord) with safe account linking
- [ ] 13.3 Full RBAC with admin permission-matrix UI, retrofitted onto Phase 7/8 controllers
- [ ] 13.4 GraphQL layer sharing service logic with REST, with N+1 protection
- [ ] 13.5 Broadcasting/WebSocket support with self-hostable transport + JS client
- [ ] 13.6 Multi-tenancy with DB + storage + cache isolation, and explicit isolation tests
- [ ] 13.7 Plugin marketplace scaffolding (static index-based)
- [ ] 13.8 Docker one-command install path
- [ ] 13.9 Block-based visual page builder reusing the Phase 4 component library

## Note on Sequencing

Build in roughly this order: **13.3 (RBAC) → 13.1 (2FA) → 13.2 (Social login) → 13.6 (Multi-tenancy) → 13.8 (Docker) → 13.4 (GraphQL) → 13.5 (Broadcasting) → 13.9 (Page builder) → 13.7 (Marketplace scaffolding)**.

Reasoning: RBAC touches every other admin feature being built here, so it should exist first. Multi-tenancy is easiest to bolt on before GraphQL/broadcasting exist (retrofitting tenant scoping onto a real-time layer afterward is much harder than building it in from the start). The page builder and marketplace are the most self-contained and least risky to build last.
