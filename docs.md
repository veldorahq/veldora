# Veldora Framework Documentation

> **Veldora** — _A PHP framework you actually own._
> Modern PHP 8.2+ MVC architecture, expressive routing, Blade-inspired templates, guard-based authentication, 41+ UI components, queues, mail, events, cache, storage, and zero framework lock-in.

---

## 1. Getting Started & Installation

Veldora provides two first-class installation methods: **Composer** (standard for PHP workflows) and **npx / npm** (an interactive zero-config scaffolder).

### System Requirements

Make sure your machine or server meets the following requirements:

| Requirement | Minimum Version | Note |
|---|---|---|
| **PHP** | 8.2 or higher | Strict typing, readonly classes, constructor promotion |
| **PDO Extension** | Enabled | Required for all database drivers |
| **SQLite Extension** | Enabled | Used by default for instant local setup |
| **Composer** | 2.0+ | Standard PHP package manager |
| **Node.js** | 18+ | (Optional) Only required if using `npx` / `npm` |

---

### Option 1 — Composer Installation (Recommended)

You can create a fresh, self-contained Veldora project in seconds using Composer:

```bash
composer create-project veldora/veldora my-app
cd my-app
php veldora serve
```

```terminal
Creating a "veldora/veldora" project at "./my-app"
Installing veldora/veldora (v0.5.1)
  - Downloading veldora/veldora (v0.5.1)
  - Installing veldora/veldora (v0.5.1): Extracting archive
Created project in ./my-app
Generating optimized autoload files
> @php -r "file_exists('.env') || copy('.env.example', '.env');"
> @php veldora key:generate

✔ Application key set successfully!

🎉 Success! Created my-app at ./my-app
```

---

### Option 2 — Interactive npm / npx Installer

If you prefer using Node.js or an interactive prompt:

```bash
# Run instantly with npx (no global install needed):
npx create-veldora-app my-app

# Or install globally for a permanent `veldora` command:
npm install -g create-veldora-app
veldora new my-app
```

```terminal
  ▲ Veldora Framework  v0.5.1
  The modern PHP framework you actually own.

  ? What is your project named? (my-veldora-app): my-blog

  Creating a new Veldora app in ./my-blog...

  ✔ Configured project skeleton
  ✔ Generated secure APP_KEY
  ✔ Configured storage and logs directory

  🎉 Success! Created my-blog at ./my-blog
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

Veldora's ActiveRecord ORM supports intuitive relationships between database tables. Relations allow you to define connections directly inside model classes and traverse them cleanly without writing complex `JOIN` queries.

### Supported Relationship Types

| Relationship | Method | Example Use Case |
|---|---|---|
| **One-to-One** | `$this->hasOne(Profile::class)` | A User has one Profile |
| **One-to-Many** | `$this->hasMany(Post::class)` | An Author has many Posts |
| **Inverse One-to-Many** | `$this->belongsTo(User::class)` | A Post belongs to an Author |
| **Many-to-Many** | `$this->belongsToMany(Role::class, 'role_user')` | A User has many Roles via a pivot table |

### Defining Relationships

```php
namespace App\Models;

use Veldora\Framework\Database\Model;
use Veldora\Framework\Database\Relations\BelongsTo;
use Veldora\Framework\Database\Relations\HasMany;
use Veldora\Framework\Database\Relations\HasOne;
use Veldora\Framework\Database\Relations\BelongsToMany;

class User extends Model
{
    // One-to-One
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'user_id');
    }

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
    // Inverse relationship back to User
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // One-to-Many for Comments
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'post_id');
    }
}
```

### Querying and Using Relations

Access related models as dynamic properties:

```php
$user = User::find(1);

// Lazy load related posts (returns array/collection of Post models)
$posts = $user->posts;

// Access inverse relation
$post = Post::find(10);
$authorName = $post->author->name;

// Filter through a relationship query
$publishedPosts = $user->posts()->where('is_published', '=', 1)->get();
```

### Managing Many-to-Many Pivot Tables

```php
$user = User::find(1);

// Attach a role ID
$user->roles()->attach(2);

// Detach a role ID
$user->roles()->detach(1);

// Sync: ensures only the given IDs are attached (removes all others)
$user->roles()->sync([2, 3, 5]);

// Check if user has a role
$hasAdminRole = $user->roles()->where('slug', '=', 'admin')->exists();
```

---

## 8. Authentication System

Veldora includes a robust, secure authentication system out of the box with session management, password hashing (Argon2id/Bcrypt), remember tokens, and route protection middleware.

### Generating Full Auth Scaffold

Generate complete, production-ready login, registration, and dashboard controllers and views with a single command:

```bash
php veldora make:auth
php veldora migrate
```

This creates:
- `app/Controllers/AuthController.php` — Handles login, registration, logout, and password resets
- `app/Models/User.php` — Authenticatable model with password hashing and fillable attributes
- `database/migrations/*_create_users_table.php` — Database schema with `email`, `password`, `remember_token`
- `resources/views/auth/login.veldora.php` — Accessible login form with CSRF token
- `resources/views/auth/register.veldora.php` — Registration form with client and server validation
- `resources/views/dashboard.veldora.php` — Protected user dashboard
- Automatic authentication routes registered in `routes/web.php`

### Global Authentication Helpers

```php
// Check if the current visitor is logged in
if (auth()->check()) {
    $user = auth()->user(); // Returns current User model instance
    $userId = auth()->id();  // Returns current user ID
}

// Log in a specific user model
auth()->login($user, $remember = true);

// Log out and invalidate the session
auth()->logout();
```

### Protecting Routes with Middleware

Protect routes so only authenticated users or guests can access them:

```php
// Only logged-in users can access
$router->group(['middleware' => ['auth']], function ($r) {
    $r->get('/dashboard', [DashboardController::class, 'index']);
    $r->get('/settings',  [SettingsController::class, 'index']);
});

// Only guests (unauthenticated visitors) can access login/register
$router->group(['middleware' => ['guest']], function ($r) {
    $r->get('/login',    [AuthController::class, 'showLogin']);
    $r->post('/login',   [AuthController::class, 'login']);
    $r->get('/register', [AuthController::class, 'showRegister']);
    $r->post('/register',[AuthController::class, 'register']);
});
```

### Password Hashing

Passwords are automatically hashed securely using PHP's native `password_hash()` with modern Argon2id or Bcrypt:

```php
use Veldora\Framework\Support\Hash;

// Hash a plaintext password
$hashed = Hash::make($request->input('password'));

// Verify password against hash
if (Hash::check($request->input('password'), $user->password)) {
    // Password matches!
}
```

---

## 9. Validation & Form Requests

Veldora provides an expressive, rule-based validation engine. You can perform inline validation directly inside controller actions or encapsulate validation logic inside reusable Form Request classes.

### Inline Validation in Controllers

The `$request->validated()` method validates incoming request data against a set of rules. If validation fails, it automatically redirects back with input errors and old form values:

```php
public function store(Request $request): Response
{
    $data = $request->validated([
        'title'    => 'required|min:3|max:255',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
        'age'      => 'nullable|integer|min:18',
        'status'   => 'required|in:draft,published,archived',
    ]);

    // If execution reaches here, data is 100% valid
    $user = User::create($data);

    return Response::redirect('/users')
        ->with('success', 'User created successfully!');
}
```

### Available Validation Rules

| Rule | Description | Example |
|---|---|---|
| `required` | Field must be present and not empty | `'name' => 'required'` |
| `nullable` | Field may be null or empty | `'phone' => 'nullable\|numeric'` |
| `email` | Must be a valid email address format | `'email' => 'required\|email'` |
| `min:value` | Minimum string length or numeric value | `'password' => 'min:8'` |
| `max:value` | Maximum string length or numeric value | `'title' => 'max:255'` |
| `numeric` | Must be a numeric value | `'price' => 'required\|numeric'` |
| `integer` | Must be an integer | `'age' => 'required\|integer\|min:1'` |
| `in:foo,bar` | Must match one of the allowed values | `'role' => 'in:admin,editor,user'` |
| `confirmed` | Field must match `{field}_confirmation` | `'password' => 'confirmed'` |
| `unique:table,col` | Must be unique in the specified database table | `'email' => 'unique:users,email'` |
| `url` | Must be a valid URL | `'website' => 'nullable\|url'` |

### Displaying Validation Errors in Views

In your `.veldora.php` templates, display validation feedback easily:

```html
<form method="POST" action="/users">
    @csrf

    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}" class="input">
        @error('name')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}" class="input">
        @error('email')
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">Create User</button>
</form>
```

### Dedicated Form Request Classes

For complex forms, keep controllers clean by creating a dedicated Form Request:

```bash
php veldora make:request StoreUserRequest
```

```php
namespace App\Http\Requests;

use Veldora\Framework\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    // Authorization logic: return false to abort with 403 Forbidden
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    // Validation rules
    public function rules(): array
    {
        return [
            'name'     => 'required|min:2|max:100',
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

Events provide a simple observer pattern implementation, allowing you to subscribe and listen for various events that occur in your application. This cleanly decouples business operations (such as sending a welcome email after registration) from your HTTP controllers.

### Generating Events & Listeners

```bash
php veldora make:event OrderPlaced
php veldora make:listener SendOrderConfirmation
```

### Defining the Event

Events are lightweight data containers holding the information related to the event:

```php
// app/Events/OrderPlaced.php
namespace App\Events;

use App\Models\Order;
use Veldora\Framework\Events\Event;

class OrderPlaced extends Event
{
    public function __construct(
        public readonly Order $order
    ) {}
}
```

### Defining the Listener

Listeners handle the logic when an event is fired:

```php
// app/Listeners/SendOrderConfirmation.php
namespace App\Listeners;

use Veldora\Framework\Events\Event;
use Veldora\Framework\Events\Listener;
use App\Mail\OrderInvoiceEmail;

class SendOrderConfirmation implements Listener
{
    public function handle(Event $event): void
    {
        // Access event payload directly
        $order = $event->order;

        // Send email via mailer
        mailer($order->customer_email)->send(new OrderInvoiceEmail($order));

        log_info('Order invoice sent', ['order_id' => $order->id]);
    }
}
```

### Registering Events and Listeners

Register your event-listener mappings in `config/events.php`:

```php
return [
    'listen' => [
        \App\Events\OrderPlaced::class => [
            \App\Listeners\SendOrderConfirmation::class,
            \App\Listeners\UpdateInventoryStock::class,
        ],
        \App\Events\UserRegistered::class => [
            \App\Listeners\SendWelcomeNotification::class,
        ],
    ],
];
```

### Dispatching Events

Dispatch events anywhere in your application:

```php
use App\Events\OrderPlaced;

// Option 1: Static dispatch method
OrderPlaced::dispatch($order);

// Option 2: Global event() helper
event(new OrderPlaced($order));
```

---

## 12. Background Queues & Jobs

Queues allow you to defer time-consuming tasks (like sending emails, processing images, or calling third-party webhooks) to a background process, dramatically speeding up web request response times.

### Queue Drivers

Configure the queue driver in `.env`:

```ini
QUEUE_DRIVER=database   # Options: sync, database
```

| Driver | Description | Best For |
|---|---|---|
| `sync` | Runs jobs immediately in the same process | Local debugging & testing |
| `database` | Stores jobs in the `jobs` database table and processes asynchronously | Production apps |

### Creating a Job

```bash
php veldora make:job ProcessVideoEncoding
```

```php
// app/Jobs/ProcessVideoEncoding.php
namespace App\Jobs;

use Veldora\Framework\Queue\Job;

class ProcessVideoEncoding extends Job
{
    public int $maxTries = 3;     // Maximum attempts before failing
    public int $retryAfter = 60;  // Delay in seconds between retries

    public function __construct(
        public readonly int $videoId,
        public readonly string $format = 'mp4'
    ) {}

    public function handle(): void
    {
        // Heavy processing logic runs in background worker
        log_info("Encoding video {$this->videoId} to {$this->format}");

        // Perform encoding...
    }
}
```

### Dispatching Jobs

```php
use App\Jobs\ProcessVideoEncoding;

// Dispatch immediately to default queue
ProcessVideoEncoding::dispatch($video->id);

// Dispatch with a delay (runs after 2 minutes)
ProcessVideoEncoding::dispatch($video->id)->delay(120);

// Dispatch to a specific queue channel
ProcessVideoEncoding::dispatch($video->id)->onQueue('media');
```

### Running the Queue Worker

Run the background worker via the CLI:

```bash
# Process jobs continuously on default queue
php veldora queue:work

# Specify queue channel and sleep interval
php veldora queue:work --queue=media,default --sleep=3 --tries=3
```

---

## 13. Mail & SMTP Transport

Veldora includes a clean, expressive Mailable system for sending HTML and plain-text emails via SMTP, Mailgun, or local log file transport.

### Configuration

Configure your mail settings in `.env`:

```ini
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Veldora App"
```

### Creating a Mailable

```bash
php veldora make:mail WelcomeEmail
php veldora make:mail OrderReceiptEmail
```

### Defining a Mailable

```php
// app/Mail/WelcomeEmail.php
namespace App\Mail;

use App\Models\User;
use Veldora\Framework\Mail\Mailable;

class WelcomeEmail extends Mailable
{
    public function __construct(
        public readonly User $user
    ) {}

    public function build(): static
    {
        return $this
            ->subject('Welcome to ' . config('app.name') . '!')
            ->from('hello@example.com', 'Veldora Team')
            ->view('emails.welcome', [
                'user'      => $this->user,
                'loginUrl'  => url('/login'),
            ]);
    }
}
```

### Designing the Email View Template

Create the template at `resources/views/emails/welcome.veldora.php`:

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2>Hello, &#123;&#123; $user->name &#125;&#125;!</h2>
    <p>Thank you for joining <strong>&#123;&#123; config('app.name') &#125;&#125;</strong>. We're excited to have you on board.</p>
    <p>
        <a href="&#123;&#123; $loginUrl &#125;&#125;" style="background: #8b5cf6; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 6px; display: inline-block;">
            Go to Your Dashboard
        </a>
    </p>
</body>
</html>
```

### Sending & Queueing Emails

```php
use App\Mail\WelcomeEmail;

// Send immediately via SMTP
mailer($user->email)->send(new WelcomeEmail($user));

// Queue for background delivery (requires queue:work running)
mailer($user->email)->queue(new WelcomeEmail($user));

// Send with CC and BCC
mailer($user->email)
    ->cc('admin@example.com')
    ->bcc('audit@example.com')
    ->send(new WelcomeEmail($user));
```

---

## 14. Cache System

The cache system lets you store the result of expensive database queries, computed data, or external API responses so they don't need to be recalculated on every request. Veldora supports two drivers: **file** (default, persists to disk) and **array** (in-memory, resets per request — useful for testing).

### Configuration

Set the driver in your `.env` file:

```ini
CACHE_DRIVER=file   # Options: file, array
```

Or change it per-environment in `config/cache.php`.

### Cache Drivers

| Driver | Description | When to use |
|---|---|---|
| `file` | Stores cached items in `storage/framework/cache/` | Default for most apps; persistent across requests |
| `array` | Stores in PHP memory only; lost after each request | Automated tests, local debugging |

### Storing & Reading Values

```php
// Store a value for 10 minutes (600 seconds)
cache(['top_posts' => $posts], 600);

// Retrieve a cached value (returns null if missing or expired)
$posts = cache('top_posts');
```

### The `remember()` Pattern (Recommended)

The `remember()` helper is the cleanest way to use the cache. It checks if a value is cached — if yes, it returns it; if not, it runs your closure, stores the result, and returns it:

```php
// Fetch from cache, or run the closure and cache for 1 hour
$stats = cache()->remember('dashboard_stats', 3600, function () {
    return [
        'total_users' => User::count(),
        'total_posts' => Post::count(),
        'new_today'   => User::where('created_at', '>=', date('Y-m-d'))->count(),
    ];
});
```

This is the preferred pattern for controller actions that serve expensive aggregated data.

### All Cache Methods

```php
// Store (TTL in seconds)
cache(['key' => $value], 3600);
cache()->put('key', $value, 600);

// Retrieve
$value = cache('key');            // Returns null if missing
$value = cache('key', 'default'); // Returns default if missing

// Remember (fetch or compute)
$value = cache()->remember('key', 3600, fn() => expensiveQuery());

// Check existence
if (cache()->has('key')) { ... }

// Remove single item
cache()->forget('key');

// Clear entire cache store
cache()->flush();

// Atomic counter increment / decrement (great for rate limiting, view counts)
cache()->increment('page_views:post-42');
cache()->increment('page_views:post-42', 5); // Increment by 5
cache()->decrement('credits_remaining');

// Store forever (no expiry)
cache()->forever('site_settings', $settings);
```

### Practical Example — Caching Posts in a Controller

```php
public function index(Request $request): Response
{
    $page = (int) $request->query('page', 1);

    $posts = cache()->remember("posts:page:{$page}", 300, function () use ($page) {
        return Post::where('is_published', '=', 1)
                   ->orderBy('created_at', 'DESC')
                   ->paginate(15, $page);
    });

    return view('posts.index', ['posts' => $posts]);
}
```

---

## 15. File Storage & Disks

Veldora provides a unified filesystem API for managing files across multiple storage **disks**. By default, two disks are pre-configured: `local` (private, server-only) and `public` (web-accessible via a URL).

### Configuration

Disks are defined in `config/filesystems.php`:

```php
return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app'),       // storage/app/
        ],
        'public' => [
            'driver' => 'local',
            'root'   => storage_path('app/public'), // storage/app/public/
            'url'    => '/storage',                 // Web URL prefix
        ],
    ],
];
```

### When to Use Each Disk

| Disk | Path | Web Accessible | Use For |
|---|---|---|---|
| `local` | `storage/app/` | ❌ No | Private files: invoices, exports, backups |
| `public` | `storage/app/public/` | ✅ Yes (via `/storage/`) | User-uploaded avatars, images, documents |

### Storing Files

```php
// Store raw binary content
storage('public')->put('avatars/user-42.png', $binaryContent);

// Store an uploaded file from a request
$file = $request->file('avatar'); // Returns a PHP SplFileInfo instance
$path = 'avatars/' . uniqid() . '.jpg';
storage('public')->put($path, file_get_contents($file->getPathname()));
```

### Reading Files

```php
// Read raw file contents
$content = storage('local')->get('exports/report.csv');

// Get file size in bytes
$size = storage('local')->size('exports/report.csv');

// Check if file exists
if (storage('public')->exists('avatars/user-42.png')) {
    echo 'File found!';
}
```

### Generating Public URLs

For files on the `public` disk, generate a browser-accessible URL:

```php
// Returns: /storage/avatars/user-42.png
$url = storage('public')->url('avatars/user-42.png');

// Use in a view:
// <img src="{{ $url }}" alt="Avatar">
```

### Deleting Files

```php
// Delete a single file
storage('public')->delete('avatars/old-avatar.png');

// Delete multiple files
storage('public')->delete(['thumbs/img1.jpg', 'thumbs/img2.jpg']);
```

### Listing Files in a Directory

```php
// List all files in a directory
$files = storage('public')->files('avatars');

// List all directories
$dirs = storage('local')->directories('exports');
```

### Practical Example — File Upload Controller

```php
public function uploadAvatar(Request $request): Response
{
    $validated = $request->validated([
        'avatar' => 'required',
    ]);

    $user = auth()->user();
    $file = $request->file('avatar');

    // Delete old avatar if it exists
    if ($user->avatar_path && storage('public')->exists($user->avatar_path)) {
        storage('public')->delete($user->avatar_path);
    }

    // Store new avatar
    $path = 'avatars/user-' . $user->id . '-' . uniqid() . '.jpg';
    storage('public')->put($path, file_get_contents($file->getPathname()));

    // Save path to user record
    $user->avatar_path = $path;
    $user->save();

    return Response::redirect('/profile')
        ->with('success', 'Avatar updated successfully!');
}
```

---

## 16. PSR-3 Logging

Veldora includes a PSR-3 compliant logger that writes structured log entries to daily rotating log files in `storage/logs/`. Each log entry includes a timestamp, severity level, message, and optional context array.

### Log File Location

Log files are stored at `storage/logs/app.log` and automatically rotate daily (e.g., `app-2026-08-23.log`) based on your `config/logging.php` settings.

### Log Levels

Veldora supports all 8 standard PSR-3 log levels, from lowest to highest severity:

| Level | Helper | Use For |
|---|---|---|
| `debug` | `log_debug()` | Detailed development/diagnostic info |
| `info` | `log_info()` | Normal application events (user login, payment processed) |
| `notice` | `logger()->notice()` | Normal but significant conditions |
| `warning` | `logger()->warning()` | Non-critical issues that should be investigated |
| `error` | `log_error()` | Runtime errors that don't require immediate action |
| `critical` | `logger()->critical()` | Critical conditions (component unavailable) |
| `alert` | `logger()->alert()` | Action must be taken immediately |
| `emergency` | `logger()->emergency()` | System is unusable |

### Using Global Helpers

```php
// Informational events (user actions, successful operations)
log_info('User signed in', [
    'user_id' => $user->id,
    'ip'      => $request->ip(),
    'agent'   => $request->userAgent(),
]);

// Debug info during development
log_debug('Slow query detected', [
    'query'   => $sql,
    'elapsed' => '1250ms',
]);

// Errors that need investigation
log_error('Payment gateway failure', [
    'order_id' => $order->id,
    'gateway'  => 'stripe',
    'error'    => $e->getMessage(),
    'trace'    => $e->getTraceAsString(),
]);
```

### Using the Logger Instance

```php
$logger = logger();

$logger->warning('Rate limit approaching threshold', ['attempts' => 95, 'limit' => 100]);
$logger->critical('Database connection lost!', ['host' => config('database.host')]);
$logger->alert('Disk space below 5%', ['free_bytes' => disk_free_space('/'), 'path' => '/']);
```

### Logging in Exception Handlers

A common pattern is to log errors inside try/catch blocks:

```php
public function processPayment(Request $request): Response
{
    try {
        $charge = $this->paymentService->charge(
            $request->input('amount'),
            $request->input('card_token')
        );

        log_info('Payment successful', [
            'user_id'    => auth()->id(),
            'amount'     => $charge->amount,
            'charge_id'  => $charge->id,
        ]);

        return Response::redirect('/dashboard')
            ->with('success', 'Payment complete!');

    } catch (\Exception $e) {
        log_error('Payment failed', [
            'user_id' => auth()->id(),
            'error'   => $e->getMessage(),
        ]);

        return Response::redirect('/checkout')
            ->with('error', 'Payment could not be processed. Please try again.');
    }
}
```

### Log Configuration

Adjust the log channel and retention in `config/logging.php`:

```php
return [
    'default' => env('LOG_CHANNEL', 'daily'),

    'channels' => [
        'daily' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/app.log'),
            'days'   => 14,    // Keep logs for 14 days
            'level'  => env('LOG_LEVEL', 'debug'),
        ],
        'single' => [
            'driver' => 'single',
            'path'   => storage_path('logs/app.log'),
            'level'  => 'debug',
        ],
    ],
];
```

---

## 17. HTTP Client

The Veldora HTTP Client provides a clean, fluent interface for making outbound HTTP requests to external APIs and services. It is built on top of PHP's native cURL and streams, with support for JSON, authentication, retries, and fake responses in tests.

### Basic Requests

```php
use Veldora\Framework\Http\Client\Http;

// Simple GET request
$response = Http::get('https://api.github.com/users/veldorahq');

// GET with query parameters
$response = Http::get('https://api.example.com/posts', [
    'page'     => 1,
    'per_page' => 20,
    'status'   => 'published',
]);

// POST with JSON body (auto sets Content-Type: application/json)
$response = Http::post('https://api.example.com/users', [
    'name'  => 'John Doe',
    'email' => 'john@example.com',
]);

// PUT and DELETE
$response = Http::put('https://api.example.com/users/42', ['name' => 'Jane Doe']);
$response = Http::delete('https://api.example.com/users/42');
```

### Authentication

```php
// Bearer token (OAuth 2.0, JWT, API keys)
$response = Http::withToken('your-api-token-here')
    ->get('https://api.example.com/protected-resource');

// Basic HTTP authentication
$response = Http::withBasicAuth('username', 'password')
    ->get('https://api.example.com/data');

// Custom headers
$response = Http::withHeaders([
    'X-API-Key'    => config('services.stripe.key'),
    'X-Request-ID' => uniqid('req_'),
])->post('https://api.stripe.com/v1/charges', $payload);
```

### Working with Responses

```php
$response = Http::get('https://api.github.com/repos/veldorahq/veldora');

// Check status
$response->successful();      // true if status 200-299
$response->failed();          // true if status 400+
$response->status();          // integer: 200, 404, 500, etc.
$response->ok();              // true if status 200
$response->notFound();        // true if status 404
$response->serverError();     // true if status 500+

// Read body
$data   = $response->json();           // Decoded PHP array
$text   = $response->body();           // Raw string
$header = $response->header('X-RateLimit-Remaining');

// Fluent conditional
if ($response->successful()) {
    $repo = $response->json();
    log_info('Repo fetched', ['stars' => $repo['stargazers_count']]);
} else {
    log_error('GitHub API error', ['status' => $response->status()]);
}
```

### Request Options

```php
// Accept JSON responses (sets Accept: application/json header)
$response = Http::acceptJson()->get('https://api.example.com/data');

// Send as form (application/x-www-form-urlencoded)
$response = Http::asForm()->post('https://api.example.com/login', [
    'username' => 'admin',
    'password' => 'secret',
]);

// Set a timeout (in seconds)
$response = Http::timeout(30)->get('https://slow-api.example.com/data');

// Follow redirects (enabled by default; pass false to disable)
$response = Http::withoutRedirecting()->get('https://api.example.com');
```

### Retries & Fault Tolerance

```php
// Retry up to 3 times, waiting 500ms between attempts
$response = Http::retry(3, 500)->get('https://unstable-api.com/feed');

// Retry with exponential backoff
$response = Http::retry(5, 200)->post('https://api.example.com/data', $payload);
```

### Practical Example — Integrating a Payment Gateway

```php
namespace App\Services;

use Veldora\Framework\Http\Client\Http;

class StripeService
{
    public function createCharge(int $amountCents, string $cardToken): array
    {
        $response = Http::withBasicAuth(config('services.stripe.secret'), '')
            ->asForm()
            ->post('https://api.stripe.com/v1/charges', [
                'amount'      => $amountCents,
                'currency'    => 'usd',
                'source'      => $cardToken,
                'description' => 'Veldora order',
            ]);

        if ($response->failed()) {
            $error = $response->json()['error']['message'] ?? 'Unknown error';
            throw new \RuntimeException("Stripe charge failed: {$error}");
        }

        return $response->json();
    }
}
```

---

## 18. API JSON Resources

JSON Resources provide a dedicated transformation layer between your Eloquent models and the JSON responses your API returns. This lets you control exactly what fields are exposed, add computed properties, include related data, and maintain a consistent API contract without putting transformation logic inside your controllers.

### Why Use Resources?

Without resources, your controller might directly return model data:

```php
// ❌ Bad: exposes all fields including sensitive ones (password, email, etc.)
return Response::json($user->toArray());
```

With resources, you control the output explicitly:

```php
// ✅ Good: clean, controlled API response
return (new UserResource($user))->toResponse();
```

### Creating a Resource

```bash
php veldora make:resource PostResource
php veldora make:resource UserResource
```

### Defining a Resource

```php
// app/Http/Resources/PostResource.php
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
            'excerpt'      => substr($this->body, 0, 120) . '...',
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'is_published' => (bool) $this->is_published,
            'author'       => [
                'id'     => $this->author->id,
                'name'   => $this->author->name,
                'avatar' => storage('public')->url($this->author->avatar_path ?? 'default.png'),
            ],
            'meta' => [
                'comment_count' => $this->comments()->count(),
            ],
        ];
    }
}
```

### Returning Single Resources

```php
// routes/web.php
$router->get('/api/posts/{id}', [PostController::class, 'show']);

// app/Controllers/PostController.php
public function show(string $id): Response
{
    $post = Post::find((int) $id);

    if (! $post) {
        return Response::json(['error' => 'Post not found'], 404);
    }

    return (new PostResource($post))->toResponse();
}
```

Response:
```json
{
  "id": 1,
  "title": "Hello Veldora",
  "slug": "hello-veldora",
  "excerpt": "Veldora is a modern PHP framework...",
  "published_at": "2026-08-23 14:00:00",
  "is_published": true,
  "author": { "id": 5, "name": "Jane Doe", "avatar": "/storage/avatars/user-5.png" },
  "meta": { "comment_count": 12 }
}
```

### Returning Resource Collections

```php
// Return all posts as a collection
public function index(Request $request): Response
{
    $posts = Post::where('is_published', '=', 1)
                 ->orderBy('created_at', 'DESC')
                 ->get();

    return PostResource::collection($posts)->toResponse();
}
```

### Returning Paginated Collections

Pass a paginator instead of a collection to automatically include `meta` and `links`:

```php
public function index(Request $request): Response
{
    $page = (int) $request->query('page', 1);
    $paginator = Post::where('is_published', '=', 1)->paginate(15, $page);

    return PostResource::collection($paginator)->toResponse();
}
```

Response:
```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 120,
    "last_page": 8
  },
  "links": {
    "first": "/api/posts?page=1",
    "last": "/api/posts?page=8",
    "prev": null,
    "next": "/api/posts?page=2"
  }
}
```

### Building a Complete REST API

```php
// routes/web.php — RESTful API routes
$router->group(['prefix' => '/api'], function ($r) {
    $r->get('/posts',        [PostController::class, 'index']);   // GET  /api/posts
    $r->post('/posts',       [PostController::class, 'store']);   // POST /api/posts
    $r->get('/posts/{id}',   [PostController::class, 'show']);    // GET  /api/posts/1
    $r->put('/posts/{id}',   [PostController::class, 'update']);  // PUT  /api/posts/1
    $r->delete('/posts/{id}',[PostController::class, 'destroy']); // DELETE /api/posts/1
});

// app/Controllers/PostController.php
class PostController
{
    public function index(Request $request): Response
    {
        $page  = (int) $request->query('page', 1);
        $posts = Post::where('is_published', '=', 1)->paginate(15, $page);
        return PostResource::collection($posts)->toResponse();
    }

    public function store(Request $request): Response
    {
        $data = $request->validated([
            'title' => 'required|min:3|max:255',
            'body'  => 'required|min:10',
        ]);

        $post = Post::create([
            ...$data,
            'user_id'      => auth()->id(),
            'slug'         => strtolower(str_replace(' ', '-', $data['title'])),
            'is_published' => 1,
        ]);

        return (new PostResource($post))->toResponse();
    }

    public function update(string $id, Request $request): Response
    {
        $post = Post::find((int) $id);

        if (! $post || $post->user_id !== auth()->id()) {
            return Response::json(['error' => 'Not found or unauthorized'], 403);
        }

        $data = $request->validated([
            'title' => 'required|min:3|max:255',
            'body'  => 'required|min:10',
        ]);

        $post->title = $data['title'];
        $post->body  = $data['body'];
        $post->save();

        return (new PostResource($post))->toResponse();
    }

    public function destroy(string $id): Response
    {
        $post = Post::find((int) $id);

        if (! $post || $post->user_id !== auth()->id()) {
            return Response::json(['error' => 'Not found or unauthorized'], 403);
        }

        $post->delete();

        return Response::json(['message' => 'Post deleted successfully']);
    }
}
```

---

## 19. Testing & Model Factories

Veldora includes an expressive testing framework built on top of PHPUnit. Write feature tests that simulate real HTTP requests, assert on responses, and verify database state — without needing a real browser.

### Setting Up Tests

Tests live in the `tests/` directory. Run the full test suite with:

```bash
php vendor/bin/phpunit
```

Or run a single test file:

```bash
php vendor/bin/phpunit tests/Feature/PostTest.php
```

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

    public function test_user_can_view_their_posts(): void
    {
        $user = (new UserFactory())->create();
        $post = (new PostFactory())->create(['user_id' => $user->id, 'title' => 'My Post']);

        $response = $this->actingAs($user)->get('/posts');

        $response->assertOk();
        $response->assertSee('My Post');
    }

    public function test_user_cannot_delete_another_users_post(): void
    {
        $owner  = (new UserFactory())->create();
        $other  = (new UserFactory())->create();
        $post   = (new PostFactory())->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->delete('/posts/' . $post->id);

        $response->assertStatus(403);
        $this->assertDatabaseHas('posts', ['id' => $post->id]); // Still exists
    }
}
```

### All Response Assertion Methods

```php
// HTTP status
$response->assertOk();              // status 200
$response->assertStatus(201);       // specific status code
$response->assertRedirect('/login'); // 302 redirect to URL
$response->assertNotFound();        // 404
$response->assertForbidden();       // 403

// Response body
$response->assertSee('Welcome');         // Contains string
$response->assertDontSee('Error');       // Does NOT contain string
$response->assertSeeText('Dashboard');   // Contains plain text

// JSON responses
$response->assertJson(['status' => 'ok']);          // JSON has these keys/values
$response->assertJsonCount(5, 'data');              // JSON array has N items
$response->assertJsonPath('data.0.title', 'Post 1'); // Specific JSON path value
```

### Database Assertions

```php
// Assert a record exists in the database
$this->assertDatabaseHas('posts', [
    'title'   => 'My Post',
    'user_id' => 42,
]);

// Assert a record does NOT exist in the database
$this->assertDatabaseMissing('posts', ['title' => 'Deleted Post']);

// Assert a record was soft-deleted (deleted_at is not null)
$this->assertSoftDeleted('posts', ['id' => 1]);
```

### Model Factories

Factories let you generate fake model instances for tests and seeders without writing manual SQL or repetitive `User::create(...)` calls.

Generate a factory:

```bash
php veldora make:factory PostFactory
```

Define the factory:

```php
// database/factories/PostFactory.php
namespace Database\Factories;

use App\Models\Post;
use Veldora\Framework\Database\Factory;

class PostFactory extends Factory
{
    protected string $model = Post::class;

    public function definition(): array
    {
        return [
            'user_id'      => (new UserFactory())->create()->id,
            'title'        => $this->faker->sentence(6),
            'slug'         => $this->faker->slug(),
            'body'         => $this->faker->paragraphs(3, true),
            'is_published' => 1,
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
        ];
    }
}
```

Use factories in tests:

```php
// Create a single instance and persist to database
$post = (new PostFactory())->create();

// Create with overridden attributes
$draftPost = (new PostFactory())->create(['is_published' => 0]);

// Create multiple instances
$posts = (new PostFactory())->count(5)->create();

// Make instance WITHOUT persisting (for unit tests)
$post = (new PostFactory())->make();
```

### Testing JSON API Endpoints

```php
class PostApiTest extends TestCase
{
    public function test_api_returns_paginated_posts(): void
    {
        (new PostFactory())->count(20)->create();

        $response = $this->get('/api/posts?page=1');

        $response->assertOk();
        $response->assertJson(['meta' => ['per_page' => 15]]);
        $response->assertJsonCount(15, 'data');
    }

    public function test_api_requires_auth_for_store(): void
    {
        $response = $this->post('/api/posts', [
            'title' => 'Test',
            'body'  => 'Body',
        ]);

        $response->assertStatus(401);
    }

    public function test_api_creates_post_for_authenticated_user(): void
    {
        $user = (new UserFactory())->create();

        $response = $this->actingAs($user)->post('/api/posts', [
            'title' => 'New API Post',
            'body'  => 'This is the post body content here.',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('title', 'New API Post');
        $this->assertDatabaseHas('posts', ['title' => 'New API Post', 'user_id' => $user->id]);
    }
}
```

### Running the Test Suite

```bash
# Run all tests
php vendor/bin/phpunit

# Run a single test file
php vendor/bin/phpunit tests/Feature/PostTest.php

# Run tests matching a filter
php vendor/bin/phpunit --filter test_guests_cannot_create_posts

# Run with verbose output
php vendor/bin/phpunit --testdox
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
You are an expert software engineer specialized in the Veldora PHP Framework (v0.5.0).
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
  $res = Http::withToken('token')->post('hDatabase & ORM Enhancements:
  // DB Facade & Transactions
  db()->transaction(function() {
      db()->statement("UPDATE accounts SET balance = balance - 100 WHERE id = 1");
      db()->statement("UPDATE accounts SET balance = balance + 100 WHERE id = 2");
  });

  // Soft Deletes
  class Post extends Model {
      use \Veldora\Framework\Database\SoftDeletes;
  }
  Post::withTrashed()->get();
  Post::onlyTrashed()->get();
  $post->restore();

  // Model Lifecycle Events
  Post::creating(function (Post $post) {
      $post->slug = \Veldora\Framework\Support\Str::slug($post->title);
  });

Routing & Helpers:
  $url = route('users.show', ['id' => 42]);
  ->middleware('throttle:60,1') // 60 requests per 1 minute window

Maintenance Mode:
  php veldora down --secret=mysecret
  php veldora up

Authentication Scaffolding (Zero-dependency .veldora.php templates):
  php veldora make:auth
  // Generates Login, Register, Forgot Password, Reset Password, Profile, Email Verify

UI Components (41+ available):
  button, input, textarea, select, checkbox, radio, badge, alert, card,
  modal, spinner, avatar, dropdown, navbar, toast, tabs, accordion,
  progress, tooltip, breadcrumb, table, switch, pagination, skeleton,
  empty, divider, drawer, popover, confirm, datepicker, fileupload,
  combobox, inputgroup, stat, datatable, timeline, stepper, sidebar,
  container, footer, rating.
  Install: php veldora add button input modal tabs footer rating

================================================================================
9. AI DEVELOPER CODE GENERATION RULES
================================================================================
1. Always declare strict_types=1 at the top of every PHP file.
2. Use exact Veldora namespaces:
   - Veldora\Framework\Http\Request
   - Veldora\Framework\Http\Response
   - Veldora\Framework\Database\Model
   - Veldora\Framework\Database\DB
   - Veldora\Framework\Database\SoftDeletes
   - Veldora\Framework\Database\Schema\Schema
   - Veldora\Framework\Database\Schema\Blueprint
   - Veldora\Framework\Database\Schema\Migration
3. In views (.veldora.php), ALWAYS use native Veldora templating directives (@csrf, @method('PUT'), @if, @foreach, {{ $var }}) and <x-component> tags. Never output raw unstyled HTML forms.
4. Always generate complete, fully working, syntactically valid code without placeholders or omissions.
```

