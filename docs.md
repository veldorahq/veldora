# Veldora Framework Documentation

> **Veldora** — _A PHP framework you actually own._
> Modern PHP 8.2+ MVC architecture, expressive routing, Blade-inspired templates, guard-based authentication, 21 UI components, queues, mail, events, cache, storage, and zero framework lock-in.

---

## 1. Getting Started & Installation

Veldora provides two first-class installation methods: an **interactive npm / npx installer** (which sets up your app with zero manual configuration) and a **Composer package creator** (for standard PHP environments).

### System Requirements

Make sure your machine or server meets the following requirements:

| Requirement | Minimum Version | Note |
|---|---|---|
| **PHP** | 8.2 or higher | Strict typing, readonly classes, constructor promotion |
| **PDO Extension** | Enabled | Required for all database drivers |
| **SQLite Extension** | Enabled | Used by default for instant local setup |
| **Composer** | 2.0+ | Dependency manager |
| **Node.js** | 18+ | (Optional) Only required for the `npx` / `npm` installer |

---

### Method A — Interactive npm / npx Installer (Recommended)

The official `create-veldora-app` package allows you to scaffold a production-ready application in seconds.

```bash
# Run instantly with npx (no global install needed):
npx create-veldora-app my-app

# Or install globally for a permanent `veldora` command:
npm install -g create-veldora-app
veldora new my-app
```

```terminal
  ▲ Veldora Framework  v1.0.0
  The modern PHP framework you actually own.

  ? What is your project named? (my-veldora-app): my-blog

  Creating a new Veldora app in /home/user/my-blog...

  Installing dependencies via Composer... ✔ Done
  Generating secure APP_KEY... ✔ Done
  Configuring storage and logs directory... ✔ Done

  🎉 Success! Created my-blog at /home/user/my-blog

  Inside your new project, you can run:

    php veldora serve
    Starts local dev server at http://localhost:8000

    php veldora make:auth
    Scaffolds complete login, registration, and dashboard

    php veldora add button input modal tabs
    Copies UI components into resources/views/components/
```

#### npm CLI Options

```bash
# Create project directly
npx create-veldora-app my-app

# Create using the `new` subcommand
npx create-veldora-app new my-app

# Show help guide and all available flags
npx create-veldora-app --help

# Check installed version
npx create-veldora-app --version
```

---

### Method B — Composer Installation

If you prefer using Composer without Node.js:

```bash
composer create-project veldora/veldora my-app
cd my-app
cp .env.example .env
```

---

### Starting the Development Server

Once your project is created, enter the folder and start the built-in development server:

```bash
cd my-app
php veldora serve
```

Open **http://localhost:8000** in your browser. You will see the Veldora welcome screen.

To run on a custom port or host:

```bash
php veldora serve --port=8080 --host=0.0.0.0
```

---

### Project Structure Overview

```
my-app/
├── app/
│   ├── Controllers/          # Request handlers
│   ├── Middleware/           # HTTP filters (Auth, CSRF, Admin, etc.)
│   ├── Models/               # ActiveRecord ORM entities
│   ├── Services/             # Application business logic
│   ├── Events/               # Event classes
│   ├── Listeners/            # Event listeners
│   ├── Jobs/                 # Background queue jobs
│   ├── Mail/                 # Mailable classes
│   └── Http/
│       ├── Requests/         # Form validation request classes
│       └── Resources/        # JSON API resource transformers
├── bootstrap/
│   └── app.php               # Container & service registration
├── config/
│   ├── app.php               # App name, debug mode, timezone
│   ├── auth.php              # Guard settings & user providers
│   ├── database.php          # Connection credentials (sqlite, mysql, pgsql)
│   ├── mail.php              # SMTP & mail transport settings
│   ├── queue.php             # Queue driver settings (sync, database)
│   ├── cache.php             # Cache store settings (file, array)
│   ├── filesystems.php       # Storage disks (local, public)
│   ├── logging.php           # Log channels & daily log rotation
│   └── session.php           # Session driver & cookie configuration
├── database/
│   ├── factories/            # Model factories for testing & seeding
│   ├── migrations/           # Versioned database schema definitions
│   └── seeders/              # Database seeders
├── public/
│   └── index.php             # Web entry point
├── resources/
│   └── views/                # .veldora.php view templates
│       ├── components/       # UI components (<x-button>, etc.)
│       └── layouts/          # Base layouts
├── routes/
│   └── web.php               # Route definitions
├── storage/
│   ├── app/                  # Private file storage
│   ├── framework/            # Compiled views, sessions, cache
│   └── logs/                 # Daily application logs (app.log)
├── .env                      # Local environment configuration
├── .env.example              # Environment template
└── veldora                   # Framework CLI binary (php veldora ...)
```

---

### Environment & `.env` Configuration

Veldora loads environment variables from `.env` on every boot before configuration files are read:

```ini
APP_NAME=Veldora
APP_ENV=local
APP_KEY=base64:your_generated_app_key_here
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_DRIVER=sqlite
DB_DATABASE=database/veldora.sqlite

SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_DRIVER=sync
CACHE_DRIVER=file
```

Read environment variables in code using the global `env()` or `config()` helpers:

```php
$debug = env('APP_DEBUG', false);
$appName = config('app.name', 'Veldora');
```

---

## 2. Routing & HTTP Layer

All web routes are defined in `routes/web.php`. The framework injects the global `$router` instance automatically.

### Basic Routes

```php
// routes/web.php

use App\Controllers\HomeController;
use App\Controllers\PostController;
use App\Controllers\AuthController;

// Closure route returning a view
$router->get('/', fn() => view('welcome'));

// Controller action route
$router->get('/posts', [PostController::class, 'index']);
$router->post('/posts', [PostController::class, 'store']);
$router->get('/posts/{id}', [PostController::class, 'show']);
$router->put('/posts/{id}', [PostController::class, 'update']);
$router->delete('/posts/{id}', [PostController::class, 'destroy']);
```

### Route Parameters

Route parameters are wrapped in curly braces and injected directly into controller methods by name:

```php
// Route
$router->get('/users/{id}/posts/{slug}', [PostController::class, 'userPost']);

// Controller
class PostController
{
    public function userPost(string $id, string $slug): Response
    {
        $post = Post::where('user_id', '=', $id)
                    ->where('slug', '=', $slug)
                    ->first();

        return view('posts.show', ['post' => $post]);
    }
}
```

### Route Groups & Middleware

Group routes that share common path prefixes or middleware:

```php
// Protected routes
$router->group(['middleware' => ['auth']], function ($r) {
    $r->get('/dashboard', [DashboardController::class, 'index']);
    $r->get('/profile', [ProfileController::class, 'edit']);
    $r->put('/profile', [ProfileController::class, 'update']);
});

// Admin group with prefix and dual middleware
$router->group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function ($r) {
    $r->get('/users', [AdminController::class, 'users']);
    $r->delete('/users/{id}', [AdminController::class, 'deleteUser']);
});
```

---

## 3. Controllers & Requests

Controllers organize your request handling logic into discrete classes.

### Generating a Controller

```bash
php veldora make:controller PostController
```

### Writing Controller Actions

```php
namespace App\Controllers;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use App\Models\Post;

class PostController
{
    public function index(Request $request): Response
    {
        $page = (int) $request->query('page', 1);
        $posts = Post::paginate(10, $page);

        return view('posts.index', ['posts' => $posts]);
    }

    public function store(Request $request): Response
    {
        $validated = $request->validated([
            'title' => 'required|min:3|max:255',
            'body'  => 'required',
        ]);

        $post = Post::create([
            'title'   => $validated['title'],
            'body'    => $validated['body'],
            'user_id' => auth()->id(),
        ]);

        return Response::redirect('/posts/' . $post->id)
            ->with('success', 'Post published successfully!');
    }
}
```

### Response Helpers

```php
// Render a view template
return view('welcome', ['name' => 'World']);

// Return JSON response (sets application/json header)
return Response::json(['success' => true, 'data' => $user], 200);

// Redirect to URL
return Response::redirect('/dashboard');

// Redirect with flash message
return Response::redirect('/posts')->with('success', 'Post saved!');
```

---

## 4. Blade-Inspired Templates

Veldora templates end with `.veldora.php` and live in `resources/views/`. They provide clean syntax and compile to pure PHP without runtime overhead.

### Outputting Variables

```html
<!-- Escaped output (prevents XSS) -->
<h1>&#123;&#123; $post->title &#125;&#125;</h1>

<!-- Raw unescaped HTML -->
<div>{!! $post->content_html !!}</div>
```

### Control Directives

```html
<!-- Conditionals -->
&#64;if($user->isAdmin())
    <span class="badge">Admin</span>
&#64;elseif($user->isEditor())
    <span class="badge">Editor</span>
&#64;else
    <span>Member</span>
&#64;endif

<!-- Loops -->
&#64;foreach($posts as $post)
    <div class="card">
        <h3>&#123;&#123; $post->title &#125;&#125;</h3>
    </div>
&#64;endforeach

<!-- Forelse with fallback -->
&#64;forelse($comments as $comment)
    <p>&#123;&#123; $comment->body &#125;&#125;</p>
&#64;empty
    <p>No comments yet.</p>
&#64;endforelse

<!-- Auth state checks -->
&#64;auth
    <a href="/dashboard">Dashboard</a>
    <a href="/logout">Logout</a>
&#64;endauth

&#64;guest
    <a href="/login">Login</a>
    <a href="/register">Register</a>
&#64;endguest
```

### Layout Inheritance & Yields

Create a parent layout in `resources/views/layouts/app.veldora.php`:

```html
<!DOCTYPE html>
<html>
<head>
    <title>&#64;yield('title', 'My Application')</title>
</head>
<body>
    <header>
        <nav><!-- navigation links --></nav>
    </header>

    <main>
        &#64;yield('content')
    </main>

    <footer>&copy; &#123;&#123; date('Y') &#125;&#125; Veldora</footer>
</body>
</html>
```

Extend it in your page views:

```html
&#64;extends('layouts.app')

&#64;section('title', 'Blog Posts')

&#64;section('content')
    <h1>All Posts</h1>
    <!-- Page content here -->
&#64;endsection
```

### Reusable UI Components

Use the `<x-component-name>` syntax to render reusable components:

```html
<x-button variant="primary" size="lg">Save Changes</x-button>

<x-card title="Account Settings">
    <x-input name="username" label="Username" value="&#123;&#123; $user->name &#125;&#125;" />
</x-card>
```

---

## 5. Database, Schema & Migrations

Veldora supports SQLite, MySQL, and PostgreSQL with a unified Schema Blueprint.

### Creating a Migration

```bash
php veldora make:migration create_posts_table
```

```php
use Veldora\Framework\Database\Schema\Blueprint;
use Veldora\Framework\Database\Schema\Migration;
use Veldora\Framework\Database\Schema\Schema;

class CreatePostsTable extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('body');
            $table->boolean('is_published')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
}
```

### Migration Commands

```bash
# Run pending migrations
php veldora migrate

# Check migration status
php veldora migrate:status

# Rollback last migration batch
php veldora migrate:rollback

# Reset database & re-run all migrations
php veldora migrate:fresh
```

### Available Blueprint Column Types

| Method | SQL Type | Description |
|---|---|---|
| `$table->id()` | AUTO_INCREMENT PK | Primary key ID |
| `$table->string('name', 255)` | VARCHAR(255) | String column |
| `$table->text('content')` | TEXT | Large text block |
| `$table->integer('count')` | INT | Integer |
| `$table->boolean('active')` | TINYINT(1) / INTEGER | Boolean true/false |
| `$table->timestamp('created_at')` | TIMESTAMP | Date & time |
| `$table->timestamps()` | created_at + updated_at | Auto timestamp pair |
| `$table->softDeletes()` | deleted_at | Soft deletion column |
| `$table->rememberToken()` | remember_token | Auth remember token |

---

## 6. ActiveRecord Models & Query Builder

Models extend `Veldora\Framework\Database\Model` and map directly to database tables.

### Creating a Model

```bash
php veldora make:model Post
```

```php
namespace App\Models;

use Veldora\Framework\Database\Model;
use Veldora\Framework\Database\Relations\BelongsTo;
use Veldora\Framework\Database\Relations\HasMany;

class Post extends Model
{
    // Allowed fields for mass assignment
    protected array $fillable = [
        'title',
        'slug',
        'body',
        'user_id',
        'is_published',
        'published_at',
    ];

    // Automatic type casting
    protected array $casts = [
        'is_published' => 'bool',
        'published_at' => 'datetime',
    ];

    // Hidden from JSON serialization
    protected array $hidden = ['deleted_at'];
}
```

### CRUD Operations

```php
// Create
$post = Post::create([
    'title' => 'New Release',
    'slug'  => 'new-release',
    'body'  => 'Veldora 1.0 is here!',
]);

// Find by ID
$post = Post::find(1);

// Find or fail
$post = Post::where('slug', '=', 'new-release')->first();

// Update
$post->title = 'Updated Title';
$post->save();

// Delete
$post->delete();

// Query builder chain
$published = Post::where('is_published', '=', 1)
                 ->where('user_id', '=', 5)
                 ->orderBy('created_at', 'DESC')
                 ->limit(10)
                 ->get();

// Paginate results
$paginator = Post::where('is_published', '=', 1)->paginate(15);
```

---

## 7. Model Relationships

Define relations on your models to traverse database connections seamlessly.

### Relationship Types

```php
namespace App\Models;

use Veldora\Framework\Database\Model;
use Veldora\Framework\Database\Relations\BelongsTo;
use Veldora\Framework\Database\Relations\HasMany;
use Veldora\Framework\Database\Relations\BelongsToMany;

class User extends Model
{
    // One-to-Many
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    // Many-to-Many via pivot table
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }
}

class Post extends Model
{
    // Inverse One-to-Many
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

### Using Relationships

```php
$user = User::find(1);

// Access related models
$posts = $user->posts;

// Access relation on post
$post = Post::find(1);
$authorName = $post->author->name;

// Manage Many-to-Many pivot attachments
$user->roles()->attach(2);
$user->roles()->detach(1);
$user->roles()->sync([2, 3]);
```

---

## 8. Authentication System

Veldora includes complete authentication scaffolding out of the box.

### Generating Full Auth Scaffold

```bash
php veldora make:auth
php veldora migrate
```

This generates:
- `app/Controllers/AuthController.php`
- `app/Models/User.php`
- `database/migrations/2026_07_15_000000_create_users_table.php`
- `resources/views/auth/login.veldora.php`
- `resources/views/auth/register.veldora.php`
- `resources/views/dashboard.veldora.php`
- Auth routes in `routes/web.php`

### Auth Helpers & Methods

```php
// Check if user is logged in
if (auth()->check()) {
    $user = auth()->user();
    $userId = auth()->id();
}

// Log a user in manually
auth()->login($user, $remember = true);

// Log out current user
auth()->logout();
```

---

## 9. Validation & Form Requests

### Inline Validation in Controllers

```php
public function store(Request $request): Response
{
    $data = $request->validated([
        'title' => 'required|min:5|max:255',
        'email' => 'required|email|unique:users,email',
        'age'   => 'nullable|integer|min:18',
    ]);

    User::create($data);

    return Response::redirect('/users');
}
```

### Custom FormRequest Classes

```bash
php veldora make:request StoreUserRequest
```

```php
namespace App\Http\Requests;

use Veldora\Framework\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|min:2',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ];
    }
}
```

---

## 10. CLI Console & Make Commands

The `php veldora` CLI binary provides generators and maintenance utilities.

| Command | Description |
|---|---|
| `php veldora serve` | Start local development server |
| `php veldora make:controller <Name>` | Scaffold a new HTTP Controller |
| `php veldora make:model <Name>` | Scaffold an ActiveRecord Model |
| `php veldora make:migration <name>` | Create a new database migration |
| `php veldora make:middleware <Name>` | Create a custom middleware |
| `php veldora make:request <Name>` | Create a validated FormRequest class |
| `php veldora make:resource <Name>` | Create an API JSON Resource transformer |
| `php veldora make:job <Name>` | Scaffold a background queue job |
| `php veldora make:event <Name>` | Scaffold a dispatchable event |
| `php veldora make:listener <Name>` | Scaffold an event listener |
| `php veldora make:mail <Name>` | Scaffold a Mailable email class |
| `php veldora make:seeder <Name>` | Create a database seeder |
| `php veldora make:factory <Name>` | Create a model factory for testing |
| `php veldora make:auth` | Generate full login, register, dashboard auth system |
| `php veldora migrate` | Run pending database migrations |
| `php veldora migrate:rollback` | Rollback last migration batch |
| `php veldora migrate:fresh` | Drop all tables and re-run all migrations |
| `php veldora queue:work` | Start background queue worker |
| `php veldora ui:list` | List all 21 available UI components |
| `php veldora add <components...>` | Copy UI components into views/components/ |

---

## 11. Events & Listeners

Decouple application logic using events and listeners.

### Generating Events & Listeners

```bash
php veldora make:event UserRegistered
php veldora make:listener SendWelcomeNotification
```

```php
// app/Events/UserRegistered.php
namespace App\Events;

use App\Models\User;
use Veldora\Framework\Events\Event;

class UserRegistered extends Event
{
    public function __construct(public readonly User $user) {}
}

// app/Listeners/SendWelcomeNotification.php
namespace App\Listeners;

use Veldora\Framework\Events\Event;
use Veldora\Framework\Events\Listener;
use App\Mail\WelcomeEmail;

class SendWelcomeNotification implements Listener
{
    public function handle(Event $event): void
    {
        mailer($event->user->email)->send(new WelcomeEmail($event->user));
    }
}
```

### Dispatching Events

```php
use App\Events\UserRegistered;

// Dispatch with class method or helper
UserRegistered::dispatch($user);
// or
event(new UserRegistered($user));
```

---

## 12. Background Queues & Jobs

Run time-consuming operations asynchronously in worker processes.

### Creating a Job

```bash
php veldora make:job ProcessVideo
```

```php
namespace App\Jobs;

use Veldora\Framework\Queue\Job;

class ProcessVideo extends Job
{
    public int $maxTries = 3;
    public int $retryAfter = 60;

    public function __construct(public readonly int $videoId) {}

    public function handle(): void
    {
        // Process video in background worker
    }
}
```

### Dispatching Jobs & Running Worker

```php
// Dispatch immediately to queue
ProcessVideo::dispatch($video->id);

// Dispatch with delay
ProcessVideo::dispatch($video->id)->delay(120);

// Dispatch to specific queue channel
ProcessVideo::dispatch($video->id)->onQueue('media');
```

Start the queue worker:

```bash
php veldora queue:work --queue=default --sleep=3
```

---

## 13. Mail & SMTP Transport

Send beautifully formatted emails using Mailable classes.

### Creating a Mailable

```bash
php veldora make:mail WelcomeEmail
```

```php
namespace App\Mail;

use App\Models\User;
use Veldora\Framework\Mail\Mailable;

class WelcomeEmail extends Mailable
{
    public function __construct(public readonly User $user) {}

    public function build(): static
    {
        return $this
            ->subject('Welcome to Veldora!')
            ->view('emails.welcome', ['user' => $this->user]);
    }
}
```

### Sending and Queueing Emails

```php
// Send immediately via SMTP
mailer($user->email)->send(new WelcomeEmail($user));

// Queue email for background delivery
mailer($user->email)->queue(new WelcomeEmail($user));
```

---

## 14. Cache System

Cache expensive queries or API responses using the file or memory cache drivers.

```php
// Store for 10 minutes (600 seconds)
cache(['top_posts' => $posts], 600);

// Retrieve cached item
$posts = cache('top_posts');

// Remember pattern: fetch or compute
$stats = cache()->remember('dashboard_stats', 3600, function () {
    return [
        'users' => User::count(),
        'posts' => Post::count(),
    ];
});

// Remove from cache
cache()->forget('top_posts');

// Atomic counter increments
cache()->increment('page_views_123');
```

---

## 15. File Storage & Disks

Manage local and public files with the filesystem abstraction.

```php
// Store user avatar on public disk
storage('public')->put('avatars/user-1.png', $binaryData);

// Read file contents
$content = storage('local')->get('exports/report.csv');

// Check existence
if (storage('public')->exists('avatars/user-1.png')) {
    // Generate public web URL: /storage/avatars/user-1.png
    $url = storage('public')->url('avatars/user-1.png');
}

// Delete file
storage('public')->delete('avatars/old-avatar.png');
```

---

## 16. PSR-3 Logging

Write structured logs to daily rotating files in `storage/logs/app.log`.

```php
log_info('User signed in', ['user_id' => $user->id, 'ip' => $request->ip()]);
log_error('Payment gateway failure', ['order_id' => $order->id, 'error' => $e->getMessage()]);

logger()->warning('Rate limit approaching threshold', ['attempts' => 95]);
logger()->critical('Database connection lost!');
```

---

## 17. HTTP Client

Make outbound HTTP requests to third-party APIs.

```php
use Veldora\Framework\Http\Client\Http;

// GET Request
$response = Http::get('https://api.github.com/users/veldorahq');

// POST Request with Bearer Token
$response = Http::withToken('api_key_123')
    ->acceptJson()
    ->post('https://api.example.com/orders', [
        'item'     => 'Widget',
        'quantity' => 2,
    ]);

if ($response->successful()) {
    $data = $response->json();
    $status = $response->status();
}

// Automatic retries on failure
$response = Http::retry(3, 500)->get('https://unstable-api.com/feed');
```

---

## 18. API JSON Resources

Transform models into clean, structured JSON API responses.

```bash
php veldora make:resource PostResource
```

```php
namespace App\Http\Resources;

use Veldora\Framework\Http\Resources\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'slug'         => $this->slug,
            'excerpt'      => substr($this->body, 0, 120),
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'author'       => [
                'id'   => $this->author->id,
                'name' => $this->author->name,
            ],
        ];
    }
}
```

```php
// In your controller:
return (new PostResource($post))->toResponse();

// Or return paginated collection with meta & links:
return PostResource::collection(Post::paginate(10))->toResponse();
```

---

## 19. Testing & Model Factories

Veldora includes an expressive testing framework built on top of PHPUnit.

### Writing HTTP Feature Tests

```php
namespace Tests\Feature;

use Veldora\Framework\Testing\TestCase;
use Database\Factories\UserFactory;
use Database\Factories\PostFactory;

class PostTest extends TestCase
{
    public function test_guests_cannot_create_posts(): void
    {
        $response = $this->post('/posts', ['title' => 'Test', 'body' => 'Body']);
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = (new UserFactory())->create();

        $response = $this->actingAs($user)->post('/posts', [
            'title' => 'My First Post',
            'body'  => 'This is the body content.',
        ]);

        $response->assertRedirect('/posts');
        $this->assertDatabaseHas('posts', ['title' => 'My First Post']);
    }
}
```

### Running the Test Suite

```bash
php vendor/bin/phpunit
```

---

## 20. Veldora UI (21 Components)

Add pre-built accessible UI components directly into your project:

```bash
# List all 21 components
php veldora ui:list

# Install specific components
php veldora add button input modal tabs card toast alert

# Install all components
php veldora add button input textarea select checkbox radio badge alert card modal spinner avatar dropdown navbar toast tabs accordion progress tooltip breadcrumb table
```

### Using Components in Views

```html
<!-- Button -->
<x-button variant="primary" size="md">Click Me</x-button>

<!-- Modal -->
<x-modal id="confirm-modal" title="Confirm Action">
    <p>Are you sure you want to proceed?</p>
    <x-slot name="footer">
        <x-button variant="danger">Delete</x-button>
    </x-slot>
</x-modal>

<!-- Tabs -->
<x-tabs :tabs="['overview' => 'Overview', 'settings' => 'Settings']" active="overview">
    <div id="tab-overview">Overview Content</div>
    <div id="tab-settings">Settings Content</div>
</x-tabs>
```

---

## 21. VS Code Extension

The official **Veldora VS Code Extension** provides syntax highlighting, autocomplete, and 32 snippets for `.veldora.php` files.

### Installation

Install directly from the VS Code Marketplace:

```bash
code --install-extension veldora.veldora-vscode
```

### Popular Snippets

| Prefix | Expands To |
|---|---|
| `v-if` | `&#64;if(...) ... &#64;endif` |
| `v-foreach` | `&#64;foreach(...) ... &#64;endforeach` |
| `v-forelse` | `&#64;forelse(...) ... &#64;empty ... &#64;endforelse` |
| `v-extends` | `&#64;extends('layouts.app')` |
| `v-section` | `&#64;section('name') ... &#64;endsection` |
| `v-yield` | `&#64;yield('name')` |
| `v-auth` | `&#64;auth ... &#64;endauth` |
| `v-guest` | `&#64;guest ... &#64;endguest` |
| `v-comp` | `<x-component-name>...</x-component-name>` |

---

## 22. AI Context Prompt & AI Skills

Because **Veldora** is a modern independent PHP framework, general AI models (ChatGPT, Claude, Gemini, Cursor, Copilot, Antigravity) do not have it in their pre-training data.

Use this **Complete Veldora AI Master Prompt** to teach any AI model the full framework architecture, CLI commands, routing, ORM, templates, and conventions.

### How to Use

1. Click **Copy Full Master Prompt** below.
2. Paste it as the first message or System Prompt in your AI assistant conversation (ChatGPT, Claude, Cursor `.cursorrules`, Antigravity, or Copilot).
3. The AI will immediately understand all Veldora APIs and write 100% correct, runnable Veldora code.

```
You are an expert software engineer specialized in the Veldora PHP Framework (v1.0.0).
Veldora is a modern, independent, lightweight PHP 8.2+ MVC framework designed for maximum performance, clean developer ergonomics, zero boilerplate magic, and complete developer ownership.

================================================================================
1. VELDORA CORE ARCHITECTURE & PHILOSOPHY
================================================================================
- Language: PHP 8.2 or higher (uses strict typing, readonly properties, constructor promotion).
- Architecture: Classic MVC (Model-View-Controller) with IoC Container, PSR-4 Autoloading, Middleware Pipeline, ActiveRecord ORM, and Blade-inspired Template Engine.
- Key Difference: Unlike Laravel, Veldora does NOT use global static Facades (like Route::get or DB::table) or heavy runtime magic. Everything is clean, typed, and straightforward.

Directory Layout:
  app/
    Controllers/       -> HTTP Request handlers (methods receive Request $request, return Response)
    Middleware/        -> HTTP filters (Auth, CSRF, Admin, StartSession, etc.)
    Models/            -> ActiveRecord database entities (extend Veldora\Framework\Database\Model)
    Services/          -> Business logic & external service integrations
    Events/            -> Event classes (extend Veldora\Framework\Events\Event)
    Listeners/         -> Event listeners (implement Veldora\Framework\Events\Listener)
    Jobs/              -> Background queue jobs (extend Veldora\Framework\Queue\Job)
    Mail/              -> Mailable email classes (extend Veldora\Framework\Mail\Mailable)
    Http/
      Requests/        -> Form validation requests (extend Veldora\Framework\Http\FormRequest)
      Resources/       -> API JSON transformers (extend Veldora\Framework\Http\Resources\JsonResource)
  bootstrap/
    app.php            -> Application bootstrapper & container bindings
  config/              -> app.php, auth.php, database.php, mail.php, queue.php, cache.php, session.php
  database/
    factories/         -> Model factories for testing & seeding
    migrations/        -> Versioned database migrations
    seeders/           -> Database seeders
  public/
    index.php          -> Single entry point for all HTTP web traffic
  resources/
    views/             -> .veldora.php view templates
      components/      -> Reusable UI components (<x-button>, <x-modal>, etc.)
      layouts/         -> Base application layouts (@extends, @yield)
  routes/
    web.php            -> Application route definitions
  storage/
    app/               -> Private file storage
    framework/         -> Compiled views, file sessions, cache
    logs/              -> Daily rotating log files (app.log)
  .env                 -> Environment variables (loaded automatically before config)
  veldora              -> Framework CLI binary (php veldora <command>)

================================================================================
2. INSTALLATION & SETUP
================================================================================
Method A (npm/npx interactive wizard):
  npx create-veldora-app my-app
  cd my-app
  php veldora serve

Method B (Composer):
  composer create-project veldora/veldora my-app
  cd my-app
  cp .env.example .env
  php veldora serve

Dev Server:
  php veldora serve --port=8000 --host=127.0.0.1

================================================================================
3. COMPLETE CLI COMMANDS REFERENCE (php veldora ...)
================================================================================
Generators:
  php veldora make:controller <Name>      -> Create app/Controllers/<Name>.php
  php veldora make:model <Name>           -> Create app/Models/<Name>.php
  php veldora make:migration <name>       -> Create database/migrations/<timestamp>_<name>.php
  php veldora make:middleware <Name>      -> Create app/Middleware/<Name>.php
  php veldora make:request <Name>         -> Create app/Http/Requests/<Name>.php
  php veldora make:resource <Name>        -> Create app/Http/Resources/<Name>.php
  php veldora make:job <Name>             -> Create app/Jobs/<Name>.php
  php veldora make:event <Name>           -> Create app/Events/<Name>.php
  php veldora make:listener <Name>        -> Create app/Listeners/<Name>.php
  php veldora make:mail <Name>            -> Create app/Mail/<Name>.php
  php veldora make:seeder <Name>          -> Create database/seeders/<Name>.php
  php veldora make:factory <Name>         -> Create database/factories/<Name>.php
  php veldora make:auth                   -> Scaffold full login, register, dashboard & auth controllers
  php veldora make:command <Name>         -> Create a custom CLI command

Database:
  php veldora migrate                     -> Run all pending migrations
  php veldora migrate:rollback            -> Roll back the last migration batch
  php veldora migrate:fresh               -> Drop all tables and re-run all migrations
  php veldora migrate:status              -> View status of all migrations
  php veldora db:seed                     -> Run database seeders

Queue:
  php veldora queue:work                  -> Start queue worker process (--queue=default --sleep=3)
  php veldora queue:failed                -> List all failed jobs
  php veldora queue:retry <id>            -> Retry a failed job
  php veldora queue:clear                 -> Clear all jobs from queue

UI Components:
  php veldora ui:list                     -> List all 21 available UI components
  php veldora add <components...>         -> Copy components into resources/views/components/

================================================================================
4. ROUTING & CONTROLLERS
================================================================================
In routes/web.php (receives $router automatically):
  $router->get('/', [HomeController::class, 'index']);
  $router->post('/posts', [PostController::class, 'store']);
  $router->get('/posts/{id}', [PostController::class, 'show']);
  $router->put('/posts/{id}', [PostController::class, 'update']);
  $router->delete('/posts/{id}', [PostController::class, 'destroy']);

  // Route Groups:
  $router->group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function ($r) {
      $r->get('/dashboard', [AdminController::class, 'index']);
      $r->get('/users', [AdminController::class, 'users']);
  });

Controller Structure:
  namespace App\Controllers;
  use Veldora\Framework\Http\Request;
  use Veldora\Framework\Http\Response;

  class PostController {
      public function index(Request $request): Response {
          $posts = Post::paginate(10);
          return view('posts.index', ['posts' => $posts]);
      }

      public function store(Request $request): Response {
          $data = $request->validated(['title' => 'required|min:3', 'body' => 'required']);
          $post = Post::create($data);
          return Response::redirect('/posts/' . $post->id)->with('success', 'Published!');
      }

      public function api(Request $request): Response {
          return Response::json(['status' => 'ok', 'data' => Post::all()], 200);
      }
  }

================================================================================
5. TEMPLATES (.veldora.php)
================================================================================
- Escaping: {{ $var }} (escaped via htmlspecialchars), {!! $raw !!} (raw unescaped)
- Conditionals: @if($cond) ... @elseif($cond) ... @else ... @endif
- Loops: @foreach($items as $item) ... @endforeach
- Forelse: @forelse($items as $item) ... @empty ... @endforelse
- Auth Directives: @auth ... @endauth, @guest ... @endguest
- Layouts: @extends('layouts.app'), @section('title', 'Page Title'), @section('content') ... @endsection
- Yielding: @yield('title', 'Default'), @yield('content')
- CSRF & Method Spoofing: @csrf, @method('PUT'), @method('DELETE')
- UI Components: <x-button variant="primary" size="md">Save</x-button>, <x-modal id="my-modal" title="Title">Content</x-modal>

================================================================================
6. ACTIVRECORD MODELS, RELATIONSHIPS & MIGRATIONS
================================================================================
Model Definition:
  namespace App\Models;
  use Veldora\Framework\Database\Model;
  use Veldora\Framework\Database\Relations\BelongsTo;
  use Veldora\Framework\Database\Relations\HasMany;
  use Veldora\Framework\Database\Relations\BelongsToMany;

  class Post extends Model {
      protected ?string $table = 'posts';
      protected array $fillable = ['title', 'slug', 'body', 'user_id', 'is_published'];
      protected array $casts = ['is_published' => 'bool', 'published_at' => 'datetime'];
      protected array $hidden = ['password'];

      public function author(): BelongsTo {
          return $this->belongsTo(User::class, 'user_id');
      }

      public function comments(): HasMany {
          return $this->hasMany(Comment::class, 'post_id');
      }
  }

Model CRUD Operations:
  $post = Post::create(['title' => 'Title', 'body' => 'Body']);
  $post = Post::find(1);
  $posts = Post::where('is_published', '=', 1)->orderBy('created_at', 'DESC')->get();
  $first = Post::where('slug', '=', 'my-post')->first();
  $paginator = Post::paginate(15);
  $post->title = 'New Title';
  $post->save();
  $post->delete();

Migrations:
  use Veldora\Framework\Database\Schema\Blueprint;
  use Veldora\Framework\Database\Schema\Migration;
  use Veldora\Framework\Database\Schema\Schema;

  class CreatePostsTable extends Migration {
      public function up(): void {
          Schema::create('posts', function (Blueprint $table) {
              $table->id();
              $table->integer('user_id');
              $table->string('title');
              $table->string('slug')->unique();
              $table->text('body');
              $table->boolean('is_published')->default(0);
              $table->timestamps();
          });
      }
      public function down(): void {
          Schema::dropIfExists('posts');
      }
  }

================================================================================
7. AUTHENTICATION & MIDDLEWARE
================================================================================
Helpers:
  auth()->check()        -> bool (true if user logged in)
  auth()->user()         -> ?Model (current User instance)
  auth()->id()           -> ?int (current user ID)
  auth()->login($user)   -> logs in the user instance
  auth()->logout()       -> logs out and destroys session

Built-in Middleware:
  'auth'                 -> Requires authenticated session (redirects to /login)
  'guest'                -> Requires guest (redirects to / if logged in)
  'admin'                -> Requires user->is_admin == 1
  'verified'             -> Requires email_verified_at != null
  'csrf'                 -> Verifies CSRF token on POST/PUT/DELETE
  'start_session'        -> Reads & saves session cookies

================================================================================
8. SUBSYSTEMS REFERENCE
================================================================================
Validation:
  $data = $request->validated([
      'title' => 'required|min:3|max:255',
      'email' => 'required|email|unique:users,email',
      'age'   => 'nullable|integer|min:18'
  ]);

Queues:
  SendEmailJob::dispatch($user)->onQueue('emails')->delay(60);
  Worker: php veldora queue:work

Mail:
  mailer($user->email)->send(new WelcomeEmail($user));
  mailer($user->email)->queue(new WelcomeEmail($user));

Events:
  UserRegistered::dispatch($user);
  event(new UserRegistered($user));

Cache:
  cache(['key' => $value], 3600);
  $val = cache('key');
  $val = cache()->remember('key', 3600, fn() => computeValue());
  cache()->forget('key');
  cache()->increment('views');

Storage:
  storage('public')->put('avatars/user.png', $data);
  $content = storage('local')->get('files/doc.pdf');
  $url = storage('public')->url('avatars/user.png');
  storage('public')->delete('avatars/user.png');

Logging:
  log_info('Message', ['context' => 'data']);
  log_error('Error occurred', ['exception' => $e]);
  logger()->warning('Warning message');

HTTP Client:
  $res = Http::get('https://api.example.com/data');
  $res = Http::withToken('token')->post('https://api.example.com/items', ['name' => 'Item']);
  $data = $res->json();

Testing:
  class PostTest extends TestCase {
      public function test_can_view_posts() {
          $res = $this->get('/posts');
          $res->assertOk()->assertSee('All Posts');
      }
      public function test_auth_required() {
          $res = $this->post('/posts', ['title' => 'Test']);
          $res->assertStatus(302)->assertRedirect('/login');
      }
  }

UI Components (21 available):
  button, input, textarea, select, checkbox, radio, badge, alert, card,
  modal, spinner, avatar, dropdown, navbar, toast, tabs, accordion,
  progress, tooltip, breadcrumb, table.
  Install: php veldora add button input modal tabs

================================================================================
9. AI DEVELOPER CODE GENERATION RULES
================================================================================
1. Always declare strict_types=1 at the top of every PHP file.
2. Use exact Veldora namespaces:
   - Veldora\Framework\Http\Request
   - Veldora\Framework\Http\Response
   - Veldora\Framework\Database\Model
   - Veldora\Framework\Database\Schema\Schema
   - Veldora\Framework\Database\Schema\Blueprint
   - Veldora\Framework\Database\Schema\Migration
3. Do NOT import non-existent Laravel facades (e.g. Route::, DB::, Schema:: without proper Veldora namespace, Auth::). Use Veldora router ($router), models, and helpers (auth(), view(), cache(), storage(), mailer(), log_info()).
4. Always generate complete, fully working, syntactically valid code without placeholders or omissions.
```

