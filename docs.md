# Veldora Framework — Developer Documentation

> **Veldora** — _"A PHP framework you actually own."_
>
> This is the single living documentation source for the Veldora PHP framework.
> Covers Phases 1 through 3 — from the service container and routing to authentication and CLI scaffolding.
> Update this file at the end of each phase before moving to the next.

---

## Table of Contents

| # | Section | Phase |
|---|---------|-------|
| 1 | [Project Structure](#1-project-structure) | All |
| 2 | [Service Container & Application Boot](#2-service-container--application-boot) | 1 |
| 3 | [HTTP Layer — Request, Response & Router](#3-http-layer--request-response--router) | 1 |
| 4 | [Middleware Pipeline](#4-middleware-pipeline) | 1 |
| 5 | [Database — Connection & Query Builder](#5-database--connection--query-builder) | 1 |
| 6 | [ActiveRecord Models](#6-activerecord-models) | 1 |
| 7 | [Database Schema & Migrations](#7-database-schema--migrations) | 1 |
| 8 | [Template Compiler & Engine](#8-template-compiler--engine) | 2 |
| 9 | [Components & Slots](#9-components--slots) | 2 |
| 10 | [Layout System](#10-layout-system) | 2 |
| 11 | [Environment & Configuration](#11-environment--configuration) | 3 |
| 12 | [Sessions & Flash Data](#12-sessions--flash-data) | 3 |
| 13 | [Cookie Jar & Signed Cookies](#13-cookie-jar--signed-cookies) | 3 |
| 14 | [Validation Engine](#14-validation-engine) | 3 |
| 15 | [Authentication — Guards & Auth Facade](#15-authentication--guards--auth-facade) | 3 |
| 16 | [Global Helper Functions](#16-global-helper-functions) | 3 |
| 17 | [CLI Console & Make Commands](#17-cli-console--make-commands) | 3 |
| 18 | [VS Code Extension](#18-vs-code-extension) | All |
| 19 | [UI Component System & Veldora UI](#19-ui-component-system-veldora-ui) | 3.1 |
| 20 | [Event Dispatcher & Listeners](#20-event-dispatcher--listeners) | 4 |
| 21 | [Background Queue System & Workers](#21-background-queue-system--workers) | 4 |
| 22 | [Mail System & SMTP Transport](#22-mail-system--smtp-transport) | 4 |
| 23 | [Advanced ORM, Relations & Pagination](#23-advanced-orm-relations--pagination) | 4 |
| 24 | [Cache System](#24-cache-system) | 4 |
| 25 | [File Storage & Disks](#25-file-storage--disks) | 4 |
| 26 | [PSR-3 Logging System](#26-psr-3-logging-system) | 4 |
| 27 | [HTTP Client & Testing Fakes](#27-http-client--testing-fakes) | 4 |
| 28 | [API JSON Resources](#28-api-json-resources) | 4 |
| 29 | [Form Request Validation](#29-form-request-validation) | 4 |
| 30 | [Testing Infrastructure & Model Factories](#30-testing-infrastructure--model-factories) | 4 |

---

## 1. Project Structure

```
Veldora/
├── veldora-core/                   # Core framework package (veldora/framework)
│   ├── bin/
│   │   └── veldora                 # CLI entry point (php veldora ...)
│   ├── src/
│   │   ├── Auth/                   # Auth guards, manager, facade
│   │   ├── Config/                 # Env loader + Config class
│   │   ├── Console/
│   │   │   └── Commands/           # make:auth, make:migration, migrate, etc.
│   │   ├── Database/
│   │   │   ├── Schema/             # Blueprint, Migration, Migrator, Schema
│   │   │   └── Relations/          # HasMany, BelongsTo, etc.
│   │   ├── Foundation/             # Application, Container, ServiceProvider
│   │   ├── Http/                   # Request, Response, Router, Pipeline, CookieJar
│   │   │   └── Middleware/         # StartSession, Auth, CSRF, Admin, etc.
│   │   ├── Session/                # Session, FileDriver, ArrayDriver interfaces
│   │   ├── Support/                # UrlSigner, helpers
│   │   ├── Validation/             # Validator, Rule interface, ValidationException
│   │   ├── View/                   # Compiler, Engine
│   │   └── helpers.php             # Global helper functions
│   └── tests/
│       └── Unit/                   # PHPUnit test suite
│
├── playground/                     # Example Veldora application
│   ├── app/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   ├── Models/
│   │   └── Services/
│   ├── bootstrap/app.php           # Application bootstrap
│   ├── config/                     # app.php, auth.php, database.php, session.php
│   ├── database/
│   │   └── migrations/
│   ├── public/index.php            # Web entry point
│   ├── resources/views/            # .veldora.php template files
│   ├── routes/web.php              # Route definitions
│   ├── storage/framework/sessions/ # File-based session storage
│   ├── .env                        # Local environment values
│   └── .env.example                # Template for env variables
│
├── veldora-vscode/                 # VS Code extension for .veldora templates
├── create-veldora-app/             # `veldora new` project scaffolder
├── docs.md                         # ← You are here
└── ARCHITECTURE.md                 # Master build prompt for AI agents
```

---

## 2. Service Container & Application Boot

### Container (`Foundation/Container.php`)

Veldora's IoC container supports binding, singleton registration, and constructor autowiring via reflection.

```php
use Veldora\Framework\Foundation\Container;

$c = new Container();

// Factory binding — resolved fresh on every call
$c->bind(Mailer::class, fn($c) => new Mailer(config('mail')));

// Singleton — resolved once and cached
$c->singleton(DatabaseConnection::class, fn($c) => new DatabaseConnection([
    'driver'   => 'sqlite',
    'database' => 'database/veldora.sqlite',
]));

// Resolve with autowiring
$controller = $c->get(UserController::class);
```

### Application (`Foundation/Application.php`)

`Application` extends `Container` and manages base path resolution and core singletons.

```php
use Veldora\Framework\Foundation\Application;

// Create instance — call once in bootstrap/app.php
$app = new Application(dirname(__DIR__));

// Path helpers
$app->basePath('database/migrations');      // /your/app/database/migrations
$app->storagePath('framework/sessions');    // /your/app/storage/framework/sessions
$app->configPath('app.php');                // /your/app/config/app.php
$app->publicPath('assets/logo.svg');        // /your/app/public/assets/logo.svg
```

At boot the Application automatically:
1. Calls `Env::load($basePath)` — parses `.env` before any config files run.
2. Registers core singletons: `Config`, `Router`, `Session`, `CookieJar`, `AuthManager`.

---

## 3. HTTP Layer — Request, Response & Router

### Request (`Http/Request.php`)

```php
use Veldora\Framework\Http\Request;

$request = Request::capture(); // built from PHP superglobals

$request->getPath();           // '/users/42'
$request->getMethod();         // 'GET'
$request->input('email');      // $_POST['email'] ?? null
$request->query('page');       // $_GET['page'] ?? null
$request->all();               // merged GET + POST
$request->only(['name', 'email']);
$request->except(['password']);
$request->has('token');        // bool
$request->ip();                // client IP string
$request->isMethod('POST');    // bool
```

### Response (`Http/Response.php`)

```php
use Veldora\Framework\Http\Response;

// Plain text / HTML response
$r = new Response('<h1>Hello</h1>', 200);
$r->header('X-Custom', 'yes');
$r->send();

// JSON response
Response::json(['ok' => true], 201)->send();

// Redirect
Response::redirect('/dashboard', 302)->send();
```

### Router (`Http/Router.php`)

```php
// routes/web.php receives $router automatically

// Basic verbs
$router->get('/', fn() => view('welcome'));
$router->post('/login', [LoginController::class, 'login']);
$router->put('/users/{id}', [UserController::class, 'update']);
$router->delete('/users/{id}', [UserController::class, 'destroy']);

// Route with middleware
$router->get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth']);

// Groups
$router->group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function($router) {
    $router->get('/users', [AdminController::class, 'index']);
    $router->delete('/users/{id}', [AdminController::class, 'destroy']);
});

// Named route parameters
$router->get('/posts/{slug}', function(string $slug) {
    return view('post', ['slug' => $slug]);
});
```

---

## 4. Middleware Pipeline

### Built-in Middleware

| Alias | Class | Purpose |
|---|---|---|
| `start_session` | `StartSession` | Reads session cookie, starts session, saves on response |
| `csrf` | `VerifyCsrfToken` | Validates `_token` on POST/PUT/PATCH/DELETE |
| `auth` | `Authenticate` | Redirects to `/login` if user not logged in |
| `guest` | `RedirectIfAuthenticated` | Redirects to `/` if user is already logged in |
| `verified` | `EnsureEmailIsVerified` | Requires `email_verified_at` to be set |
| `admin` | `EnsureUserIsAdmin` | Requires `is_admin = 1` on the authenticated user |

### Writing Custom Middleware

```php
namespace App\Middleware;

use Closure;
use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Http\MiddlewareInterface;

class LogRequests implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): Response
    {
        error_log($request->getMethod() . ' ' . $request->getPath());
        return $next($request);
    }
}
```

Register in the router:
```php
$router->get('/api/status', [StatusController::class, 'index'])
    ->middleware([LogRequests::class]);
```

---

## 5. Database — Connection & Query Builder

### Connection (`Database/Connection.php`)

```php
use Veldora\Framework\Database\Connection;

$connection = new Connection([
    'driver'   => 'sqlite',
    'database' => __DIR__ . '/../database/veldora.sqlite',
]);

// Raw query
$results = $connection->select('SELECT * FROM users WHERE id = ?', [1]);
$connection->statement('UPDATE users SET name = ? WHERE id = ?', ['John', 1]);
```

### Query Builder (`Database/QueryBuilder.php`)

```php
use Veldora\Framework\Database\QueryBuilder;

$builder = new QueryBuilder($connection);

// SELECT
$users = $builder->table('users')
    ->where('is_admin', '=', 1)
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();

// INSERT
$builder->table('posts')->insert([
    'title'      => 'My Post',
    'body'       => 'Content here',
    'created_at' => date('Y-m-d H:i:s'),
]);

// UPDATE
$builder->table('users')
    ->where('id', '=', 5)
    ->update(['name' => 'Updated Name']);

// DELETE
$builder->table('sessions')
    ->where('expired_at', '<', date('Y-m-d H:i:s'))
    ->delete();
```

---

## 6. ActiveRecord Models

### Defining a Model (`Database/Model.php`)

```php
namespace App\Models;

use Veldora\Framework\Database\Model;

class Post extends Model
{
    // Defaults to snake_case plural of class name: 'posts'
    // protected ?string $table = 'posts';
}
```

### Model CRUD Operations

```php
// Create
$post = new Post();
$post->title = 'Hello World';
$post->body  = 'First post content.';
$post->save(); // INSERT

// Find by primary key
$post = Post::find(1);

// Find all
$posts = Post::all();

// Update
$post->title = 'Updated Title';
$post->save(); // UPDATE

// Delete
$post->delete();

// Fluent WHERE chain
$published = (new Post())->query()
    ->where('is_published', '=', 1)
    ->orderBy('created_at', 'DESC')
    ->get();
```

---

## 7. Database Schema & Migrations

### Creating a Migration

```php
use Veldora\Framework\Database\Schema\Blueprint;
use Veldora\Framework\Database\Schema\Migration;
use Veldora\Framework\Database\Schema\Schema;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_admin')->default(0); // optional admin support
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
```

### Blueprint Column Types

| Method | SQL Type | Notes |
|---|---|---|
| `->id()` | `INTEGER PRIMARY KEY AUTOINCREMENT` | Auto-increment PK |
| `->string(name, length=255)` | `VARCHAR(n)` | Short text |
| `->text(name)` | `TEXT` | Long text |
| `->integer(name)` | `INTEGER` / `INT` | Whole numbers |
| `->boolean(name)` | `INTEGER` (SQLite) / `TINYINT(1)` (MySQL) | 0 or 1 |
| `->timestamp(name)` | `DATETIME` / `TIMESTAMP` | Date & time |
| `->timestamps()` | `created_at` + `updated_at` | Auto timestamps pair |
| `->rememberToken()` | `VARCHAR(100) NULL` | Remember-me token |
| `->softDeletes()` | `deleted_at DATETIME NULL` | Soft delete support |

### Blueprint Modifiers

```php
$table->string('bio')->nullable();       // Allows NULL
$table->boolean('is_active')->default(1); // Sets DEFAULT value
$table->string('email')->unique();        // Adds UNIQUE constraint
```

### Running Migrations

```bash
php veldora migrate           # Run pending
php veldora migrate:fresh     # Drop all + re-run
php veldora migrate:rollback  # Roll back last batch
```

---

## 8. Template Compiler & Engine

Veldora templates use `.veldora.php` (or `.veldora`) as the file extension and compile to plain PHP, cached until the source changes.

### Rendering Views

```php
// In a controller:
return view('home', ['user' => $user]);

// Full path resolves to: resources/views/home.veldora.php
// Nested:
return view('admin/users/index', compact('users'));
// → resources/views/admin/users/index.veldora.php
```

### Output Syntax

```html
{{-- Escaped (HTML-safe) output --}}
<p>{{ $user->name }}</p>

{{-- Raw unescaped output --}}
<div>{!! $article->html_body !!}</div>

{{-- PHP comments (stripped from output) --}}
{{-- This is a Veldora comment, invisible in rendered HTML --}}
```

### Directives Reference

#### Conditionals
```html
@if ($user->is_admin)
    <span>Administrator</span>
@elseif ($user->is_verified)
    <span>Verified User</span>
@else
    <span>Guest</span>
@endif

@unless ($user->hasVerifiedEmail())
    <div class="alert">Please verify your email.</div>
@endunless
```

#### Loops
```html
@foreach ($posts as $post)
    <article>{{ $post->title }}</article>
@endforeach

@forelse ($comments as $comment)
    <p>{{ $comment->body }}</p>
@empty
    <p>No comments yet.</p>
@endforelse

@for ($i = 0; $i < 5; $i++)
    <span>{{ $i }}</span>
@endfor

@while ($queue->isNotEmpty())
    {{ $queue->pop() }}
@endwhile
```

#### Auth Guards
```html
@auth
    <a href="/logout">Sign Out</a>
@endauth

@guest
    <a href="/login">Sign In</a>
@endguest

@admin
    <a href="/admin">Admin Panel</a>
@endadmin
```

#### Utilities
```html
{{-- Forms: auto-inserts hidden CSRF token input --}}
<form method="POST" action="/login">
    @csrf
    ...
</form>

{{-- Inline PHP block --}}
@php
    $timestamp = now();
@endphp

{{-- Debug: dumps variable to output --}}
@dump($user)
```

---

## 9. Components & Slots

Components are HTML-like elements `<x-name>` that compile to PHP component rendering.

### Creating a Component

```
resources/views/components/card.veldora.php
```

```html
<div class="card">
    <h3 class="card-title">{{ $title }}</h3>
    <div class="card-body">{{ $slot }}</div>

    @if (isset($footer))
        <div class="card-footer">{{ $footer }}</div>
    @endif
</div>
```

### Using a Component

```html
<x-card title="User Profile">
    This is the default slot content.

    <x-slot name="footer">
        <small>Last updated 2 days ago</small>
    </x-slot>
</x-card>
```

### Passing PHP Variables as Attributes

```html
<x-alert type="{{ $alertType }}" message="{{ $msg }}">
    Additional content in the default slot.
</x-alert>
```

---

## 10. Layout System

### Defining a Layout

```html
<!-- resources/views/layouts/app.veldora.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Veldora App')</title>
</head>
<body>
    <nav><!-- navigation --></nav>

    <main>
        @yield('content')
    </main>

    <footer>
        @yield('footer', '<p>Default footer</p>')
    </footer>
</body>
</html>
```

### Extending a Layout

```html
<!-- resources/views/dashboard.veldora.php -->
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1>Welcome back, {{ $user->name }}</h1>
    @include('partials.stats')
@endsection
```

---

## 11. Environment & Configuration

### `.env` File Syntax

```ini
#
# Veldora Environment Configuration
#

# ------------------------------------
# App
# ------------------------------------

APP_NAME  = Veldora
APP_ENV   = local
APP_DEBUG = true
APP_KEY   = "your-secret-key-here"
APP_URL   = http://localhost:8000

# ------------------------------------
# Database
# ------------------------------------

DB_CONNECTION = sqlite
DB_DATABASE   = ./database/veldora.sqlite

# ------------------------------------
# Session
# ------------------------------------

SESSION_DRIVER   = file
SESSION_LIFETIME = 120

# ------------------------------------
# Mail
# ------------------------------------

MAIL_HOST       = smtp.mailtrap.io
MAIL_PORT       = 587
MAIL_FROM_NAME  = ${APP_NAME}
```

### Auto Type Casting

| Raw Value | PHP Type | Value |
|---|---|---|
| `true` / `false` | `bool` | `true` / `false` |
| `null` | `null` | `null` |
| `3306` | `int` | `3306` |
| `1.5` | `float` | `1.5` |
| `"hello world"` | `string` | `hello world` |
| `${APP_NAME}` | resolved string | value of `APP_NAME` |

### Config Files (`config/*.php`)

```php
// config/app.php
return [
    'name'  => env('APP_NAME', 'Veldora'),
    'env'   => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'key'   => env('APP_KEY', ''),
    'url'   => env('APP_URL', 'http://localhost'),
];
```

### Reading Config

```php
config('app.name');                         // 'Veldora'
config('database.connections.sqlite.driver'); // 'sqlite'
config('session.lifetime', 60);              // fallback default
```

---

## 12. Sessions & Flash Data

### Session Drivers

| Driver | Class | Description |
|---|---|---|
| `file` | `FileDriver` | Stores sessions as PHP serialized files in `storage/framework/sessions/` |
| `array` | `ArrayDriver` | In-memory only (useful for testing) |

### Session API

```php
// Write
session()->put('cart_count', 3);

// Read
$count = session()->get('cart_count', 0); // default = 0

// Check existence
session()->has('cart_count'); // bool

// Remove
session()->forget('cart_count');

// Flash (available for next request only)
session()->flash('success', 'Product added to cart!');

// Clear all data
session()->flush();

// CSRF token
$token = session()->csrfToken();
session()->regenerateToken(); // force a new token
```

---

## 13. Cookie Jar & Signed Cookies

### Signed Cookies

Signed cookies include an HMAC-SHA256 signature using `APP_KEY`. Tampering breaks the signature and the cookie is rejected.

```php
use Veldora\Framework\Http\CookieJar;

$jar = $app->get(CookieJar::class);

// Queue a signed cookie (name, value, minutes)
$jar->queueSigned('remember_token', $tokenValue, 43200); // 30 days

// Queue a plain cookie
$jar->queue('theme', 'dark', 60);

// Remove a cookie
$jar->queueForget('remember_token');
```

Queued cookies are automatically sent as HTTP `Set-Cookie` headers when the `Response` is dispatched by the `Router`.

---

## 14. Validation Engine

### Basic Validation in a Controller

```php
public function store(Request $request): Response
{
    $data = $request->validate([
        'name'                  => 'required|string',
        'email'                 => 'required|email|unique:users,email',
        'password'              => 'required|min:8|confirmed',
        'password_confirmation' => 'required',
    ]);

    // $data only contains the validated keys
    User::create($data);

    return redirect('/dashboard');
}
```

### Standalone Validator

```php
use Veldora\Framework\Validation\Validator;

$validator = new Validator($request->all(), [
    'age' => 'required|integer|between:18,99',
]);

if ($validator->fails()) {
    $errors = $validator->errors(); // ['age' => ['The age must be between 18 and 99.']]
}

$validated = $validator->validated(); // safe array
```

### All Built-in Rules

| Rule | Description |
|---|---|
| `required` | Must be present and not empty |
| `string` | Must be a string |
| `integer` | Must be a whole integer |
| `numeric` | Must be numeric (int or float) |
| `boolean` | Must be true/false or 1/0 |
| `array` | Must be an array |
| `email` | Must be a valid email format |
| `min:val` | String length / numeric value ≥ val |
| `max:val` | String length / numeric value ≤ val |
| `between:min,max` | Value or length is within range |
| `same:field` | Must match another field's value |
| `confirmed` | Must match `{field}_confirmation` |
| `nullable` | Skips other rules if value is empty |
| `in:a,b,c` | Must be one of the listed values |
| `not_in:a,b` | Must not be any of the listed values |
| `exists:table,col` | Value must exist in a DB column |
| `unique:table,col,except` | Value must be unique in a DB column |

### Custom Rule Classes

```php
use Veldora\Framework\Validation\Rule;

class StrongPassword implements Rule
{
    public function passes(string $attribute, mixed $value): bool
    {
        return preg_match('/[A-Z]/', $value) && preg_match('/[0-9]/', $value);
    }

    public function message(): string
    {
        return 'The :attribute must contain at least one uppercase letter and one number.';
    }
}

// Usage
$validator = new Validator($data, [
    'password' => ['required', new StrongPassword()],
]);
```

---

## 15. Authentication — Guards & Auth Facade

### Static Facade (`Auth/Auth.php`)

```php
use Veldora\Framework\Auth\Auth;

// Attempt login
if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
    return redirect('/dashboard');
}

// Manual login (already have the model)
Auth::login($user);
Auth::login($user, remember: true); // sets remember cookie

// Current user
Auth::check();    // bool — is logged in?
Auth::guest();    // bool — is a guest?
Auth::user();     // User model instance | null
Auth::id();       // int | null
Auth::isAdmin();  // bool — checks is_admin flag

// Log out
Auth::logout();
```

### Guard Configuration (`config/auth.php`)

```php
return [
    'default' => 'web',

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'model',
            'model'  => 'App\Models\User',
        ],
    ],
];
```

### Admin Support on the User Model

When using `make:auth` with admin enabled, the `User` model includes:

```php
class User extends Model
{
    public function isAdmin(): bool
    {
        return (bool) ($this->attributes['is_admin'] ?? false);
    }

    public function hasVerifiedEmail(): bool
    {
        return !empty($this->attributes['email_verified_at']);
    }
}
```

---

## 16. Global Helper Functions

All helpers are auto-loaded from `veldora-core/src/helpers.php`.

| Helper | Return Type | Description |
|---|---|---|
| `env(key, default)` | `mixed` | Read from `.env` (typed) |
| `config(key, default)` | `mixed` | Read from `config/*.php` via dot-notation |
| `session(key?, default?)` | `Session\|mixed` | Session instance or a value |
| `csrf_token()` | `string` | Active CSRF token string |
| `csrf_field()` | `string` | Full `<input type="hidden">` HTML |
| `auth()` | `AuthManager` | The auth manager singleton |
| `url(path, signed?, expiresAt?)` | `string` | Build full URL, optionally signed |
| `signed_url(path)` | `string` | Permanent signed URL |
| `temporary_signed_url(path, seconds)` | `string` | Expiring signed URL |
| `redirect(to, status=302)` | `Response` | Redirect response object |
| `back(status=302)` | `Response` | Redirect to previous URL |
| `abort(code, message?)` | `never` | Terminate with HTTP error |

---

## 17. CLI Console & Make Commands

The Veldora CLI provides 40+ built-in commands with zero configuration. It is accessible from the root of any Veldora project:

```bash
php veldora [command] [options]
```

### Complete Commands Reference

#### Application & Diagnostics
| Command | Description | Example |
|---|---|---|
| `serve` / `server` | Start local development HTTP server with real-time colorized logs | `php veldora serve --port=8080` |
| `about` | Display environment, PHP version, DB driver, and cache status | `php veldora about` |
| `doctor` | Run automated system health diagnostics and extension checks | `php veldora doctor` |
| `key:generate` | Generate and set a secure 32-character `APP_KEY` in `.env` | `php veldora key:generate` |
| `storage:link` | Create symlink from `public/storage` to `storage/app/public` | `php veldora storage:link` |
| `env` | Display current application environment and debug state | `php veldora env` |
| `env:encrypt` | Encrypt `.env` file using `APP_KEY` for secure version control | `php veldora env:encrypt` |
| `env:decrypt` | Decrypt `.env.encrypted` file using `APP_KEY` | `php veldora env:decrypt` |

#### Code Generators (`make:*`)
| Command | Description | Example |
|---|---|---|
| `make:controller <Name>` | Create a new HTTP Controller class | `php veldora make:controller PostController` |
| `make:model <Name> [-m]` | Create an ActiveRecord Model (optionally with migration) | `php veldora make:model Post -m` |
| `make:migration <name>` | Create a timestamped database migration file | `php veldora make:migration create_posts_table` |
| `make:middleware <Name>` | Create a new HTTP Middleware class | `php veldora make:middleware EnsureUserIsAdmin` |
| `make:request <Name>` | Create a Form Request Validator class | `php veldora make:request StorePostRequest` |
| `make:seeder <Name>` | Create a Database Seeder class | `php veldora make:seeder UserSeeder` |
| `make:command <Name>` | Create a custom Veldora Console Command class | `php veldora make:command SendWeeklyDigest` |
| `make:job <Name>` | Create a background Queue Job class | `php veldora make:job ProcessPodcast` |
| `make:event <Name>` | Create an Event class | `php veldora make:event OrderShipped` |
| `make:listener <Name>` | Create an Event Listener class | `php veldora make:listener SendShipmentNotification` |
| `make:mail <Name>` | Create a Mailable class + email view template | `php veldora make:mail WelcomeEmail` |
| `make:component <name>` | Create a new UI component template | `php veldora make:component card-header` |
| `make:auth` | Interactive scaffold for complete authentication system | `php veldora make:auth` |

#### Database (`db:*` & `migrate:*`)
| Command | Description | Example |
|---|---|---|
| `migrate` | Run pending database migrations | `php veldora migrate` |
| `migrate:rollback` | Roll back the last migration batch | `php veldora migrate:rollback` |
| `migrate:fresh` | Drop all tables and re-run all migrations from scratch | `php veldora migrate:fresh` |
| `migrate:status` | Display status table of all migrations (Ran vs Pending) | `php veldora migrate:status` |
| `db:seed [class]` | Execute database seeders from `database/seeders/` | `php veldora db:seed` |
| `db:wipe` | Drop all tables, views, and types in the database | `php veldora db:wipe` |
| `db:show` | Display database driver, file size, and table row counts | `php veldora db:show` |

#### Routing (`route:*`)
| Command | Description | Example |
|---|---|---|
| `route:list` | Render a formatted table of all registered routes and middleware | `php veldora route:list` |
| `route:cache` | Compile routes into a cache file for instant resolution | `php veldora route:cache` |
| `route:clear` | Remove the route cache file | `php veldora route:clear` |

#### Optimization & Caching (`config:*`, `view:*`, `cache:*`, `optimize:*`)
| Command | Description | Example |
|---|---|---|
| `optimize` | One-command production optimizer (caches config, routes, views) | `php veldora optimize` |
| `optimize:clear` | Clear all framework caches (config, routes, views, app cache) | `php veldora optimize:clear` |
| `config:cache` | Combine all configuration files into a single cache file | `php veldora config:cache` |
| `config:clear` | Remove the configuration cache file | `php veldora config:clear` |
| `config:show [key]` | Inspect configuration values in terminal | `php veldora config:show app` |
| `view:cache` | Pre-compile all `.veldora.php` views into PHP cache files | `php veldora view:cache` |
| `view:clear` | Clear all compiled view cache files | `php veldora view:clear` |
| `cache:clear` | Flush application and session caches | `php veldora cache:clear` |

#### UI Components (`ui:*` & `add`)
| Command | Description | Example |
|---|---|---|
| `ui:list` | List all 21 available Veldora UI components | `php veldora ui:list` |
| `add <components...>` | Add pre-styled components into `resources/views/components/` | `php veldora add button card modal` |

---

### `make:auth` Interactive Flow

```
$ php veldora make:auth

Enable admin support? (Y/n) y

Scaffolding authentication layer...
Created migration:  database/migrations/2026_07_15_000000_create_users_table.php
Created model:      app/Models/User.php
Created controller: app/Controllers/LoginController.php
Created controller: app/Controllers/RegisterController.php
Created view:       resources/views/auth/login.veldora.php
Created view:       resources/views/auth/register.veldora.php
Appended routes to: routes/web.php
Authentication scaffolding completed successfully!
```

**With `is_admin = Y`** — migration includes:
```php
$table->boolean('is_admin')->default(0);
```

**With `is_admin = N`** — column is omitted entirely.

---

## 18. VS Code Extension

Install `veldora-vscode` for syntax highlighting, auto-completion, and bracket matching in `.veldora.php` template files.

### Installing the Extension

```bash
# From the veldora-vscode directory
npx @vscode/vsce package --no-dependencies
code --install-extension veldora-vscode-0.2.0.vsix
```

### Snippet Prefixes Quick Reference

| Prefix | Directive |
|---|---|
| `v-if` | `@if ... @endif` |
| `v-foreach` | `@foreach ... @endforeach` |
| `v-forelse` | `@forelse ... @empty ... @endforelse` |
| `v-unless` | `@unless ... @endunless` |
| `v-for` | `@for ... @endfor` |
| `v-while` | `@while ... @endwhile` |
| `v-php` | `@php ... @endphp` |
| `v-csrf` | `@csrf` |
| `v-auth` | `@auth ... @endauth` |
| `v-guest` | `@guest ... @endguest` |
| `v-admin` | `@admin ... @endadmin` |
| `v-dump` | `@dump($var)` |
| `v-extends` | `@extends('layout')` |
| `v-section` | `@section('name') ... @endsection` |
| `v-yield` | `@yield('name')` |
| `v-comp` | `<x-component> ... </x-component>` |
| `v-esc` | `{{ $variable }}` |

---

## 19. UI Component System & Veldora UI

Veldora includes an optional UI Component System (`veldora/ui`) allowing developers to quickly add accessible, monochrome-first components.

### UI CLI Commands

Install one or more components into your `resources/views/components/` directory:

```bash
# List all available UI components
php veldora ui:list

# Add a single component
php veldora add button

# Add multiple components at once
php veldora add button input textarea select alert card modal spinner avatar dropdown navbar toast
```

### Component Reference & Usages

Once added, components are used via the `<x-component>` HTML syntax in views.

| Component | Usage snippet | Props & Customization |
|---|---|---|
| **Button** | `<x-button variant="primary">Submit</x-button>` | `variant` (primary, secondary, ghost, danger), `size` (sm, md, lg), `disabled` |
| **Input** | `<x-input name="email" label="Email" type="email" />` | `name`, `label`, `type`, `value`, `placeholder`, `error`, `helper`, `required` |
| **Textarea** | `<x-textarea name="bio" label="Biography" />` | `name`, `label`, `rows`, `placeholder`, `error`, `helper`, `required` |
| **Select** | `<x-select name="status" label="Status" :options="$options" />` | `name`, `label`, `options` (array), `selected`, `placeholder`, `error` |
| **Checkbox** | `<x-checkbox name="agree" label="Accept terms" />` | `name`, `label`, `value`, `checked`, `disabled`, `error` |
| **Radio** | `<x-radio name="color" value="red" label="Red" />` | `name`, `value`, `label`, `checked`, `disabled` |
| **Badge** | `<x-badge variant="success" dot>Active</x-badge>` | `variant` (default, success, warning, danger, info, purple), `dot` (bool) |
| **Alert** | `<x-alert variant="success" title="Done!" dismissible>Saved.</x-alert>` | `variant` (success, warning, danger, info), `title`, `dismissible` (bool) |
| **Card** | `<x-card title="Post Info"><p>Body text</p></x-card>` | `title`, `subtitle`, `padding` (bool), footer slot supported |
| **Modal** | `<x-modal id="confirm" title="Confirm Delete">...</x-modal>` | `id`, `title`, `size` (sm, md, lg, xl), footer slot supported |
| **Spinner** | `<x-spinner size="md" />` | `size` (sm, md, lg), custom screen-reader label `label` |
| **Avatar** | `<x-avatar src="/user.jpg" name="John Doe" size="md" />` | `src`, `name` (initials fallback), `size` (xs to xl), `shape` (circle, square) |
| **Dropdown** | `<x-dropdown label="Menu"><li><a href="#">Action</a></li></x-dropdown>` | `label`, `align` (left, right), click outside auto-dismiss |
| **Navbar** | `<x-navbar brand="My App"><a href="/">Home</a></x-navbar>` | `brand`, `brandHref`, `sticky` (bool), mobile hamburger menu support |
| **Toast** | `<x-toast id="t1" variant="success" message="Success!" />` | `id`, `variant` (success, warning, danger, info), `message`, `duration` (ms) |
| **Tabs** | `<x-tabs id="t" :tabs="['a'=>'Tab A']"><div class="vui-tab-pane vui-tab-pane-active" id="tab-pane-t-a">Content</div></x-tabs>` | `id`, `tabs` (array), `active` (default key) |
| **Accordion** | `<x-accordion title="FAQ Item" :open="true">Answer text</x-accordion>` | `id`, `title`, `open` (bool) |
| **Progress** | `<x-progress :value="75" variant="primary" :striped="true" />` | `value` (0-100), `max`, `variant`, `size`, `striped`, `animated`, `showLabel` |
| **Tooltip** | `<x-tooltip text="Help info" position="top"><button>Hover</button></x-tooltip>` | `text`, `position` (top, bottom, left, right) |
| **Breadcrumb** | `<x-breadcrumb :items="[['label'=>'Home','href'=>'/'],['label'=>'Docs']]" />` | `items` (array of `label`, `href`) |
| **Table** | `<x-table :striped="true" :hover="true"><thead>...</thead><tbody>...</tbody></x-table>` | `striped` (bool), `hover` (bool), `bordered` (bool), `compact` (bool) |

### Including Component Styles

All component designs utilize CSS custom variables and styling. Copy `veldora-ui.css` into your public assets folder or reference it in your master layout:

```html
<link rel="stylesheet" href="/vendor/veldora-ui/veldora-ui.css">
```

---

## 20. Event Dispatcher & Listeners

Veldora provides an event dispatcher implementation allowing you to subscribe and listen to various events that occur in your application.

### Defining Events

```php
namespace App\Events;

use App\Models\User;
use Veldora\Framework\Events\Event;

class UserRegistered extends Event
{
    public function __construct(public User $user)
    {
    }
}
```

Dispatch an event using the static `dispatch()` method or the `event()` global helper:

```php
UserRegistered::dispatch($user);
// or
event(new UserRegistered($user));
```

### Defining Listeners

```php
namespace App\Listeners;

use App\Events\UserRegistered;
use Veldora\Framework\Events\Event;
use Veldora\Framework\Events\Listener;

class SendWelcomeNotification implements Listener
{
    public function handle(Event $event): void
    {
        if ($event instanceof UserRegistered) {
            mailer($event->user->email)->send(new WelcomeEmail($event->user));
        }
    }
}
```

---

## 21. Background Queue System & Workers

Veldora's queue system provides a unified API across diverse queue backends, allowing you to defer the processing of time-consuming tasks.

### Generating Queue Jobs

```bash
php veldora make:job ProcessPodcast
```

```php
namespace App\Jobs;

use Veldora\Framework\Queue\Job;

class ProcessPodcast extends Job
{
    public int $maxTries = 3;
    public int $retryAfter = 60;

    public function __construct(public int $podcastId)
    {
    }

    public function handle(): void
    {
        // Process podcast audio encoding...
    }
}
```

### Dispatching Jobs

```php
ProcessPodcast::dispatch(42)->onQueue('high')->delay(120);
// or
dispatch(new ProcessPodcast(42));
```

### Running the Queue Worker

```bash
php veldora queue:work --queue=default --sleep=3
```

---

## 22. Mail System & SMTP Transport

Send rich HTML and plain text emails using SMTP, PHP `mail()`, or in-memory array transport.

### Generating Mailables

```bash
php veldora make:mail WelcomeEmail
```

```php
namespace App\Mail;

use Veldora\Framework\Mail\Mailable;

class WelcomeEmail extends Mailable
{
    public function build(): static
    {
        return $this->subject('Welcome to Veldora!')
            ->view('emails.welcome', ['name' => 'Fahim']);
    }
}
```

### Sending and Queueing Emails

```php
// Synchronous send
mailer('user@example.com')->send(new WelcomeEmail());

// Background queue send (Mailable extends Job automatically!)
mailer('user@example.com')->queue(new WelcomeEmail());
```

---

## 23. Advanced ORM, Relations & Pagination

### BelongsToMany & Pivot Tables

```php
class User extends Model
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }
}

// Attach, Detach, Sync, and Toggle
$user->roles()->attach([1, 2]);
$user->roles()->detach(1);
$user->roles()->sync([1, 3]);
$user->roles()->toggle([2, 4]);
```

### Model Pagination

```php
// Paginate 15 users per page with accessible HTML link rendering
$users = User::paginate(15);

// In your .veldora.php view:
{!! $users->links() !!}
```

### Model Casts & Hidden Attributes

```php
class User extends Model
{
    protected array $fillable = ['name', 'email', 'is_active', 'preferences'];
    
    protected array $casts = [
        'is_active' => 'bool',
        'preferences' => 'array',
        'created_at' => 'datetime',
    ];
    
    protected array $hidden = ['password'];
}
```

---

## 24. Cache System

Veldora includes a high-performance Cache manager with atomic file locking and in-memory array drivers.

```php
// Store value with TTL (seconds)
cache(['theme' => 'dark'], 3600);

// Retrieve or compute
$stats = cache()->remember('dashboard_stats', 600, function () {
    return DB::table('orders')->count();
});

// Increment counters
cache()->increment('views_count');
```

---

## 25. File Storage & Disks

Manage files across local and public storage disks.

```php
// Write file
storage('public')->put('avatars/user_1.png', $fileContent);

// Get public URL
$url = storage('public')->url('avatars/user_1.png');

// Check existence & delete
if (storage('public')->exists('avatars/user_1.png')) {
    storage('public')->delete('avatars/user_1.png');
}
```

---

## 26. PSR-3 Logging System

Veldora includes structured, rotating daily log files with JSON context serialization and exception tracing.

```php
log_info('User profile updated', ['user_id' => 12]);
log_error('Payment gateway timeout', ['exception' => $e]);
logger('Debugging SQL query');
```

---

## 27. HTTP Client & Testing Fakes

A fluent, cURL-powered HTTP Client with mock support.

```php
use Veldora\Framework\Http\Client\Http;

$response = Http::withToken('token123')
    ->acceptJson()
    ->get('https://api.github.com/user');

if ($response->successful()) {
    $username = $response->json('login');
}
```

### Faking in Unit Tests

```php
Http::fake([
    'https://api.github.com/*' => Http::response(['login' => 'octocat'], 200),
]);
```

---

## 28. API JSON Resources

Transform your ActiveRecord models into consistent, clean JSON responses.

```bash
php veldora make:resource UserResource
```

```php
namespace App\Http\Resources;

use Veldora\Framework\Http\Resources\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}

// Controller usage
return (new UserResource($user))->toResponse();
return UserResource::collection(User::paginate(10))->toResponse();
```

---

## 29. Form Request Validation

Encapsulate authorization and request validation logic cleanly.

```bash
php veldora make:request StorePostRequest
```

```php
namespace App\Http\Requests;

use Veldora\Framework\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|min:5|max:255',
            'body' => 'required',
        ];
    }
}
```

---

## 30. Testing Infrastructure & Model Factories

Veldora includes an integrated PHPUnit test suite harness with HTTP simulation and Model Factories.

### Generating Factories

```bash
php veldora make:factory UserFactory --model=User
```

```php
namespace Database\Factories;

use App\Models\User;
use Veldora\Framework\Database\Factories\Factory;

class UserFactory extends Factory
{
    protected string $model = User::class;

    public function definition(): array
    {
        return [
            'name' => 'John Doe',
            'email' => 'user' . rand(100, 999) . '@example.com',
            'is_active' => 1,
        ];
    }
}
```

### Writing Feature Tests

```php
use Veldora\Framework\Testing\TestCase;

class UserFeatureTest extends TestCase
{
    public function test_user_api_endpoint(): void
    {
        $response = $this->get('/api/users');

        $response->assertOk()
            ->assertJsonFragment(['name' => 'John Doe'])
            ->assertSee('John');
    }
}
```

---

> **Framework Version:** `v1.0.0-rc`
> **Test Status:** ✅ 87 unit tests, 344 assertions — 100% passing
> **Extension Version:** `veldora-vscode 0.5.5`

