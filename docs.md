# Veldora Framework Documentation

> **Veldora** — _A PHP framework you actually own._
> Modern PHP 8.2+. Zero magic. Full control.

---

## Getting Started

## Installation

The quickest way to create a new Veldora project is with the official installer. You only need Composer installed on your machine.

```bash
composer create-project veldora/veldora my-app
cd my-app
php veldora serve
```

Your app will be running at **http://localhost:8000**. Open the browser and you'll see the Veldora welcome page.

### System Requirements

Before you begin, make sure your server meets these requirements:

- PHP **8.2 or higher**
- The **PDO** PHP extension
- The **SQLite** extension (for the default SQLite database)
- **Composer** for dependency management

### Project Structure

After creating a new project, here is what the directory structure looks like and what each folder is for:

```
my-app/
├── app/
│   ├── Controllers/     # Request handlers — one action per method
│   ├── Middleware/      # Custom request/response interceptors
│   ├── Models/          # Database models (one per table)
│   └── Services/        # Business logic, reusable across controllers
├── bootstrap/
│   └── app.php          # Application bootstrap — binds services, loads config
├── config/
│   ├── app.php          # App name, URL, debug mode, timezone
│   ├── auth.php         # Guard settings, user model
│   ├── cache.php        # Cache driver and TTL settings
│   ├── database.php     # Database driver, host, credentials
│   ├── filesystems.php  # Storage disks (local, public)
│   ├── logging.php      # Log channel, daily rotation
│   ├── mail.php         # SMTP or native mailer settings
│   ├── queue.php        # Queue driver (sync, database)
│   └── session.php      # Session driver, lifetime, cookie settings
├── database/
│   ├── factories/       # Model factories for tests and seeders
│   ├── migrations/      # Versioned database schema files
│   └── seeders/         # Seed the database with sample data
├── public/
│   └── index.php        # Web entry point — all HTTP requests go here
├── resources/
│   └── views/           # Template files (.veldora.php)
├── routes/
│   └── web.php          # All your application routes
├── storage/
│   ├── app/             # User-uploaded and generated files
│   ├── framework/       # Sessions, cache files (auto-managed)
│   └── logs/            # Application log files (app.log)
├── .env                 # Your local environment variables (never commit this)
├── .env.example         # Template for .env — commit this
└── veldora              # CLI entry point: php veldora <command>
```

### Environment Configuration

Veldora uses a `.env` file at the root of your project to manage environment-specific settings such as your database credentials and debug mode. This file is never committed to version control.

```ini
APP_NAME=Veldora
APP_URL=http://localhost:8000
APP_ENV=local
APP_DEBUG=true

DB_DRIVER=sqlite
DB_DATABASE=database/veldora.sqlite

SESSION_DRIVER=file
SESSION_LIFETIME=120
```

Access any value from your application using the `env()` helper or the `config()` helper after the value has been defined in a config file.

```php
$debug = env('APP_DEBUG', false);
$name  = config('app.name');
```

---

## Routing

## Defining Routes

All application routes are defined in `routes/web.php`. The `$router` variable is automatically available in this file.

```php
// routes/web.php

$router->get('/', [HomeController::class, 'index']);
$router->post('/contact', [ContactController::class, 'store']);
$router->put('/users/{id}', [UserController::class, 'update']);
$router->delete('/users/{id}', [UserController::class, 'destroy']);
```

### Route Parameters

Capture dynamic URL segments by wrapping the segment name in curly braces. The value is automatically passed to your controller method by name.

```php
$router->get('/posts/{slug}', [PostController::class, 'show']);

// In your controller:
class PostController
{
    public function show(string $slug): Response
    {
        $post = Post::where('slug', '=', $slug)->first();
        return view('posts.show', compact('post'));
    }
}
```

### Route Groups

Group routes that share common middleware or a URL prefix:

```php
// All routes in this group require the user to be logged in
$router->group(['middleware' => ['auth']], function ($router) {
    $router->get('/dashboard', [DashboardController::class, 'index']);
    $router->get('/profile', [ProfileController::class, 'edit']);
    $router->put('/profile', [ProfileController::class, 'update']);
});

// Admin-only routes behind a prefix
$router->group(['prefix' => '/admin', 'middleware' => ['auth', 'admin']], function ($router) {
    $router->get('/users', [Admin\UserController::class, 'index']);
});
```

### Attaching Middleware

You can attach middleware directly to any route:

```php
$router->get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified']);
```

---

## Controllers

## Creating a Controller

Use the CLI to generate a new controller:

```bash
php veldora make:controller PostController
```

This creates `app/Controllers/PostController.php`. Controllers are plain PHP classes — each public method handles one route action.

```php
namespace App\Controllers;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;

class PostController
{
    public function index(Request $request): Response
    {
        $posts = Post::all();
        return view('posts.index', compact('posts'));
    }

    public function store(Request $request): Response
    {
        $data = $request->validated(['title' => 'required|min:3', 'body' => 'required']);

        $post = new Post();
        $post->fill($data)->save();

        return Response::redirect('/posts');
    }
}
```

### Returning Responses

Controllers can return different types of responses:

```php
// Render a view template
return view('posts.index', ['posts' => $posts]);

// Return a JSON response (for APIs)
return Response::json(['status' => 'ok', 'data' => $posts]);

// Redirect the user
return Response::redirect('/dashboard');

// Redirect with a flash message
return Response::redirect('/posts')->with('success', 'Post created!');
```

---

## Templates

## How Templates Work

Veldora uses its own template engine. Template files have the `.veldora.php` extension and live in `resources/views/`. The engine compiles `@directives` and `{{ expressions }}` into plain PHP before executing.

### Displaying Data

Use double curly braces to display a variable. The output is automatically HTML-escaped to prevent XSS.

```html
<!-- resources/views/posts/show.veldora.php -->
<h1>{{ $post->title }}</h1>
<p>By {{ $post->author->name }}</p>
```

To output **unescaped** HTML (use with care):

```html
{!! $post->body_html !!}
```

### Control Flow

```html
@if($user->isAdmin())
    <a href="/admin">Admin Panel</a>
@elseif($user->isModerator())
    <a href="/moderate">Moderate Content</a>
@else
    <p>Welcome back, {{ $user->name }}!</p>
@endif

@foreach($posts as $post)
    <article>
        <h2>{{ $post->title }}</h2>
        <p>{{ $post->excerpt }}</p>
    </article>
@endforeach

@forelse($posts as $post)
    <li>{{ $post->title }}</li>
@empty
    <p>No posts yet. Be the first to write one!</p>
@endforelse
```

### Layouts and Sections

Define a base layout that all pages share:

```html
<!-- resources/views/layouts/app.veldora.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title', 'My App')</title>
</head>
<body>
    <nav><!-- navigation here --></nav>

    <main>
        @yield('content')
    </main>

    <footer>Built with Veldora</footer>
</body>
</html>
```

Then extend it in each page:

```html
<!-- resources/views/posts/index.veldora.php -->
@extends('layouts.app')

@section('title', 'All Posts')

@section('content')
    <h1>Recent Posts</h1>
    @foreach($posts as $post)
        <a href="/posts/{{ $post->slug }}">{{ $post->title }}</a>
    @endforeach
@endsection
```

### Components

Reusable template pieces are stored in `resources/views/components/`. Use them anywhere with the `<x-component-name>` syntax:

```html
<!-- resources/views/components/alert.veldora.php -->
<div class="alert alert-{{ $variant }}">
    <strong>{{ $title }}</strong>
    {{ $slot }}
</div>

<!-- Usage in a view: -->
<x-alert variant="success" title="Saved!">
    Your changes have been saved successfully.
</x-alert>
```

---

## Database

## Configuration

Set your database connection details in `.env`:

```ini
DB_DRIVER=sqlite
DB_DATABASE=database/veldora.sqlite

# For MySQL:
# DB_DRIVER=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=myapp
# DB_USERNAME=root
# DB_PASSWORD=secret
```

Veldora supports **SQLite**, **MySQL**, and **PostgreSQL** out of the box.

## Migrations

Migrations are version-controlled database schemas. Each migration describes what should be added, changed, or removed from your database.

### Creating a Migration

```bash
php veldora make:migration create_posts_table
```

This generates a file in `database/migrations/`. Open it and define your schema:

```php
use Veldora\Framework\Database\Schema\Blueprint;
use Veldora\Framework\Database\Schema\Migration;
use Veldora\Framework\Database\Schema\Schema;

class CreatePostsTable extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();                          // Auto-increment primary key
            $table->integer('user_id');            // Foreign key to users
            $table->string('title');               // VARCHAR(255)
            $table->string('slug')->unique();      // Unique slug for URLs
            $table->text('body');                  // Long text content
            $table->boolean('is_published')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();                  // created_at + updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
}
```

### Running Migrations

```bash
# Run all pending migrations
php veldora migrate

# Check migration status
php veldora migrate:status

# Roll back the last batch
php veldora migrate:rollback

# Drop everything and re-run from scratch (development only)
php veldora migrate:fresh
```

### Available Column Types

| Method | Description |
|---|---|
| `->id()` | Auto-increment primary key |
| `->string('name')` | VARCHAR(255) text |
| `->text('body')` | Unlimited text |
| `->integer('count')` | Integer number |
| `->float('price')` | Floating point number |
| `->boolean('active')` | True/false (0/1) |
| `->timestamp('at')` | Date and time |
| `->timestamps()` | Adds `created_at` and `updated_at` |
| `->softDeletes()` | Adds `deleted_at` for soft deletion |
| `->nullable()` | Allows NULL values |
| `->default(value)` | Sets a default value |
| `->unique()` | Adds a UNIQUE constraint |

## Models

Each database table has a corresponding Model class. The model name should be the singular, PascalCase version of the table name (e.g. `Post` for `posts`).

### Creating a Model

```bash
php veldora make:model Post
```

This creates `app/Models/Post.php`:

```php
namespace App\Models;

use Veldora\Framework\Database\Model;

class Post extends Model
{
    // Mass-assignable fields (can be filled via create() or fill())
    protected array $fillable = ['title', 'slug', 'body', 'user_id', 'is_published'];

    // Automatically cast these fields to native PHP types
    protected array $casts = [
        'is_published'   => 'bool',
        'published_at'   => 'datetime',
    ];

    // These fields are hidden from toArray() and toJson()
    protected array $hidden = ['password'];
}
```

### Basic Model Operations

```php
// Create and save a new record in one line
$post = Post::create(['title' => 'Hello World', 'slug' => 'hello-world', 'body' => '...']);

// Find a record by its primary key — returns null if not found
$post = Post::find(1);

// Retrieve all records
$posts = Post::all();

// Update a record
$post->title = 'Updated Title';
$post->save();

// Delete a record
$post->delete();
```

### Querying

```php
// Chain conditions to build a query
$published = Post::where('is_published', '=', 1)
    ->orderBy('published_at', 'DESC')
    ->limit(10)
    ->get();

// Fetch only the first match
$post = Post::where('slug', '=', 'hello-world')->first();

// Count matching records
$total = Post::where('is_published', '=', 1)->count();

// Paginate results — returns 15 per page by default
$posts = Post::paginate(15);
```

### Relationships

Define how models relate to each other directly inside the model class:

```php
class Post extends Model
{
    // A Post belongs to one User
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // A Post has many Comments
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'post_id');
    }
}

class User extends Model
{
    // A User has many Posts
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    // A User belongs to many Roles (many-to-many via pivot table)
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }
}
```

Using relationships:

```php
$post = Post::find(1);
$author = $post->author;           // Related User model
$comments = $post->comments;       // Collection of Comment models

$user = User::find(1);
$roles = $user->roles;             // Collection of Role models

// Attach/detach roles from a user
$user->roles()->attach([1, 2]);
$user->roles()->detach(3);
$user->roles()->sync([1, 4]);      // Replace all roles with these
```

---

## Authentication

## Setting Up Auth

Veldora includes a complete authentication scaffold you can generate with one command:

```bash
php veldora make:auth
```

This generates login, register, and dashboard pages, a `User` model with migration, and all the controllers you need. After running it, apply the migration:

```bash
php veldora migrate
```

You now have a fully functional login/register system.

## Checking Authentication Status

In your routes, use the `auth` middleware to protect pages:

```php
$router->get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth']);
```

In your controllers and templates, use the `auth()` helper:

```php
// In a controller
if (auth()->check()) {
    $user = auth()->user();  // Get the current User model
}

// Log the user in
auth()->login($user);

// Log the user out
auth()->logout();
```

In templates:

```html
@auth
    <p>Welcome, {{ auth()->user()->name }}!</p>
    <a href="/logout">Logout</a>
@endauth

@guest
    <a href="/login">Login</a>
    <a href="/register">Register</a>
@endguest
```

---

## Validation

## Validating Input

The simplest way to validate a request is in the controller using `$request->validated()`:

```php
public function store(Request $request): Response
{
    $data = $request->validated([
        'title' => 'required|min:5|max:255',
        'email' => 'required|email',
        'body'  => 'required',
    ]);

    // $data only contains the validated fields
    Post::create($data);

    return Response::redirect('/posts')->with('success', 'Created!');
}
```

If validation fails, the user is automatically redirected back with the errors and old input available in the template:

```html
@if($errors->has('title'))
    <p class="error">{{ $errors->first('title') }}</p>
@endif

<input name="title" value="{{ old('title') }}">
```

## Form Request Classes

For more complex validation logic, generate a dedicated request class:

```bash
php veldora make:request StorePostRequest
```

```php
namespace App\Http\Requests;

use Veldora\Framework\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    // Who is allowed to make this request?
    public function authorize(): bool
    {
        return auth()->check();
    }

    // What are the validation rules?
    public function rules(): array
    {
        return [
            'title' => 'required|min:5|max:255',
            'body'  => 'required',
            'tags'  => 'array',
        ];
    }
}
```

### Available Validation Rules

| Rule | What it checks |
|---|---|
| `required` | Field must be present and not empty |
| `email` | Must be a valid email address |
| `min:N` | Minimum length (strings) or value (numbers) of N |
| `max:N` | Maximum length (strings) or value (numbers) of N |
| `numeric` | Must be a number |
| `integer` | Must be a whole number |
| `boolean` | Must be true, false, 0, or 1 |
| `array` | Must be an array |
| `unique:table,column` | Value must not exist in the given database table |
| `confirmed` | Must match a `field_confirmation` field |
| `nullable` | Field may be null or absent |

---

## CLI Commands

## The Veldora CLI

All CLI commands are run as `php veldora <command>`. Run `php veldora list` to see every available command.

### Code Generators (make:*)

These commands scaffold files so you don't have to write boilerplate:

| Command | What it creates |
|---|---|
| `php veldora make:controller PostController` | A new controller class |
| `php veldora make:model Post` | A new model class |
| `php veldora make:migration create_posts_table` | A new migration file |
| `php veldora make:middleware LogRequests` | A new middleware class |
| `php veldora make:request StorePostRequest` | A form validation class |
| `php veldora make:resource PostResource` | An API resource transformer |
| `php veldora make:job SendEmail` | A background queue job |
| `php veldora make:event UserRegistered` | An event class |
| `php veldora make:listener SendWelcomeEmail` | An event listener |
| `php veldora make:mail WelcomeEmail` | A mailable email class |
| `php veldora make:seeder UserSeeder` | A database seeder |
| `php veldora make:factory UserFactory` | A model factory for testing |
| `php veldora make:auth` | Full auth scaffold (login, register, etc.) |
| `php veldora make:command MyCommand` | A custom CLI command |

### Database Commands

| Command | What it does |
|---|---|
| `php veldora migrate` | Run all pending migrations |
| `php veldora migrate:rollback` | Undo the last migration batch |
| `php veldora migrate:fresh` | Drop all tables and re-run migrations |
| `php veldora migrate:status` | Show which migrations have run |
| `php veldora db:seed` | Run all database seeders |

### Queue Commands

| Command | What it does |
|---|---|
| `php veldora queue:work` | Start the background queue worker |
| `php veldora queue:failed` | List all failed jobs |
| `php veldora queue:retry {id}` | Retry a specific failed job |
| `php veldora queue:clear` | Delete all pending and failed jobs |

### Other Commands

| Command | What it does |
|---|---|
| `php veldora serve` | Start the built-in PHP dev server on port 8000 |
| `php veldora env` | Display all current environment variables |
| `php veldora ui:list` | List all available UI components |
| `php veldora add button input modal` | Add UI components to your project |

---

## Events

## How Events Work

Events allow different parts of your application to communicate without creating tight dependencies. When something happens (like a user registers), you fire an event. Any number of listeners can respond to it independently.

### Step 1 — Create an Event

```bash
php veldora make:event UserRegistered
```

```php
namespace App\Events;

use App\Models\User;
use Veldora\Framework\Events\Event;

class UserRegistered extends Event
{
    public function __construct(public readonly User $user) {}
}
```

### Step 2 — Create a Listener

```bash
php veldora make:listener SendWelcomeEmail --event=UserRegistered
```

```php
namespace App\Listeners;

use App\Events\UserRegistered;
use Veldora\Framework\Events\Event;
use Veldora\Framework\Events\Listener;

class SendWelcomeEmail implements Listener
{
    public function handle(Event $event): void
    {
        // Send a welcome email to the new user
        mailer($event->user->email)->send(new WelcomeEmail($event->user));
    }
}
```

### Step 3 — Dispatch the Event

Fire the event anywhere in your application — usually in a controller after an action completes:

```php
use App\Events\UserRegistered;

// In your RegisterController:
$user = User::create($data);

UserRegistered::dispatch($user);
// or: event(new UserRegistered($user));
```

---

## Queue System

## Background Jobs

The queue lets you defer slow operations (like sending email or processing images) to run in the background so your user doesn't have to wait.

### Creating a Job

```bash
php veldora make:job SendWelcomeEmail
```

```php
namespace App\Jobs;

use App\Models\User;
use Veldora\Framework\Queue\Job;

class SendWelcomeEmail extends Job
{
    public int $maxTries = 3;      // Retry up to 3 times on failure
    public int $retryAfter = 60;   // Wait 60 seconds before retrying

    public function __construct(public readonly User $user) {}

    public function handle(): void
    {
        // This runs in the background worker process
        mailer($this->user->email)->send(new WelcomeEmail($this->user));
    }
}
```

### Dispatching a Job

```php
// Push to the queue — runs in background
SendWelcomeEmail::dispatch($user);

// Push to a specific queue channel
SendWelcomeEmail::dispatch($user)->onQueue('emails');

// Delay the job by 5 minutes
SendWelcomeEmail::dispatch($user)->delay(300);
```

### Running the Worker

Start the queue worker in a separate terminal window. It will continuously poll for new jobs:

```bash
php veldora queue:work --queue=default --sleep=3
```

For production, use a process manager like **Supervisor** to keep the worker running.

---

## Mail

## Sending Email

Create a Mailable class to represent each type of email your app sends:

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
            ->subject("Welcome to Veldora, {$this->user->name}!")
            ->view('emails.welcome', ['user' => $this->user]);
    }
}
```

### Sending

```php
// Send immediately (blocks until delivered)
mailer($user->email)->send(new WelcomeEmail($user));

// Queue for background delivery (recommended for production)
mailer($user->email)->queue(new WelcomeEmail($user));
```

### Mail Configuration

Set up your SMTP credentials in `.env`:

```ini
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME=MyApp
```

For local development, set `MAIL_MAILER=array` to capture emails without actually sending them.

---

## Cache

## Caching Data

Use the `cache()` helper to store and retrieve values. Caching expensive database queries or API calls dramatically improves performance.

```php
// Store a value for 1 hour (3600 seconds)
cache(['site_stats' => $stats], 3600);

// Retrieve a cached value (returns null if not found or expired)
$stats = cache('site_stats');

// The most common pattern: retrieve or compute
$popularPosts = cache()->remember('popular_posts', 600, function () {
    return Post::where('views', '>', 1000)->orderBy('views', 'DESC')->limit(10)->get();
});

// Delete a cached value
cache()->forget('site_stats');

// Atomic counter — great for tracking views or rate limiting
cache()->increment('post_views_1');
cache()->decrement('remaining_attempts');
```

### Cache Configuration

Change the cache driver in `config/cache.php`:

```php
'default' => env('CACHE_DRIVER', 'file'),
```

| Driver | Description |
|---|---|
| `file` | Stores cache in `storage/framework/cache/` (default) |
| `array` | In-memory only — useful for testing |

---

## File Storage

## Storing Files

The storage system gives you a clean, consistent API for working with files regardless of where they are stored.

```php
// Write a file to the public disk (web-accessible)
storage('public')->put('avatars/user-42.png', $fileContents);

// Read a file
$contents = storage('local')->get('reports/annual.pdf');

// Check if a file exists
if (storage('public')->exists('avatars/user-42.png')) {
    // ...
}

// Get the public URL for a file
$url = storage('public')->url('avatars/user-42.png');
// Returns: /storage/avatars/user-42.png

// Delete a file
storage('public')->delete('avatars/old-avatar.png');
```

### Storage Configuration

Disks are configured in `config/filesystems.php`:

```php
'disks' => [
    'local'  => ['driver' => 'local', 'root' => storage_path('app')],
    'public' => ['driver' => 'local', 'root' => storage_path('app/public'), 'url' => '/storage'],
],
```

---

## Logging

## Writing Logs

Use the logging helpers to record events, errors, and debug information to `storage/logs/app.log`.

```php
// Different log levels
log_info('User logged in', ['user_id' => $user->id]);
log_error('Payment failed', ['order_id' => $order->id, 'reason' => $e->getMessage()]);

// Or use the logger() helper for more flexibility
logger()->warning('Disk usage is high', ['usage' => '85%']);
logger()->critical('Database connection failed');

// Log an exception with full stack trace
logger()->error('Uncaught exception', ['exception' => $e]);
```

Log entries include timestamps, log level, message, and any context data you pass. By default, logs rotate daily so you don't end up with a single enormous file.

---

## HTTP Client

## Making HTTP Requests

Use the `Http` facade to make HTTP requests to external APIs:

```php
use Veldora\Framework\Http\Client\Http;

// Simple GET request
$response = Http::get('https://api.github.com/users/octocat');

// POST request with JSON body
$response = Http::post('https://api.example.com/orders', [
    'product_id' => 42,
    'quantity'   => 3,
]);

// With authentication and custom headers
$response = Http::withToken('your-api-token')
    ->acceptJson()
    ->get('https://api.example.com/me');

// Check response and extract data
if ($response->successful()) {
    $name = $response->json('name');
    $data = $response->json();     // Full response as array
}

// Handle failures
if ($response->failed()) {
    log_error('API call failed', ['status' => $response->status()]);
}
```

### Retries

Automatically retry a request on failure:

```php
$response = Http::retry(3, 1000)  // 3 attempts, 1 second between each
    ->get('https://api.example.com/data');
```

---

## API Resources

## Transforming API Responses

JSON Resources give you a clean layer between your database models and your API responses. They let you control exactly what data is returned and in what format.

### Creating a Resource

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
            'excerpt'      => substr($this->body, 0, 150),
            'published_at' => $this->published_at?->format('Y-m-d'),
            'author'       => [
                'id'   => $this->author->id,
                'name' => $this->author->name,
            ],
        ];
    }
}
```

### Using Resources in Controllers

```php
// Return a single resource
return (new PostResource($post))->toResponse();

// Return a collection
return PostResource::collection(Post::all())->toResponse();

// Return a paginated collection — includes pagination links automatically
return PostResource::collection(Post::paginate(15))->toResponse();
```

The paginated response includes `links` and `meta` blocks automatically:

```json
{
    "data": [...],
    "links": {
        "first": "/api/posts?page=1",
        "next": "/api/posts?page=2",
        "last": "/api/posts?page=5"
    },
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 72
    }
}
```

---

## Testing

## Writing Tests

Veldora ships with a testing foundation built on top of PHPUnit. Your test classes can extend `TestCase` to get HTTP helpers and other utilities.

### HTTP Tests

```php
use Veldora\Framework\Testing\TestCase;

class PostTest extends TestCase
{
    public function test_homepage_loads(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('Welcome');
    }

    public function test_creating_a_post_requires_auth(): void
    {
        $response = $this->post('/posts', ['title' => 'Test', 'body' => 'Content']);
        $response->assertStatus(302); // Redirect to login
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = UserFactory::new()->create();
        $response = $this->actingAs($user)->post('/posts', [
            'title' => 'My Post',
            'body'  => 'Post content here',
        ]);
        $response->assertRedirect('/posts');
    }
}
```

### Available Assertions

| Method | Checks |
|---|---|
| `->assertOk()` | Status is 200 |
| `->assertStatus(201)` | Status is the given code |
| `->assertRedirect('/url')` | Response redirects to the given URL |
| `->assertSee('text')` | Response body contains the text |
| `->assertDontSee('text')` | Response body does not contain the text |
| `->assertJson(['key' => 'val'])` | Response JSON contains the given data |
| `->assertJsonFragment(['key' => 'val'])` | Response JSON contains this fragment |
| `->assertHeader('X-Header', 'value')` | Response has the given header |

### Model Factories

Factories let you generate test data quickly without writing raw database inserts:

```bash
php veldora make:factory PostFactory --model=Post
```

```php
namespace Database\Factories;

use App\Models\Post;
use Veldora\Framework\Database\Factories\Factory;

class PostFactory extends Factory
{
    protected string $model = Post::class;

    public function definition(): array
    {
        return [
            'title'        => 'Sample Post ' . rand(1, 999),
            'slug'         => 'sample-post-' . rand(1, 999),
            'body'         => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'is_published' => 1,
            'user_id'      => 1,
        ];
    }
}
```

Using factories in tests:

```php
// Create 1 model and save to database
$post = (new PostFactory())->create();

// Create 5 models
$posts = (new PostFactory())->count(5)->create();

// Create with specific attributes
$post = (new PostFactory())->create(['title' => 'My Specific Title']);

// Make without saving (in-memory only)
$post = (new PostFactory())->make();
```

### Running Tests

```bash
php vendor/bin/phpunit
```

---

## UI Components

## Installing Components

Veldora UI provides 21 pre-built, accessible components. Add them to your project with one command:

```bash
# See all available components
php veldora ui:list

# Add individual components
php veldora add button input card modal alert

# Add all components at once
php veldora add button input textarea select checkbox radio badge alert card modal spinner avatar dropdown navbar toast tabs accordion progress tooltip breadcrumb table
```

Each command copies the component template to `resources/views/components/` so you own the code and can customize it freely.

## Using Components

Once added, use components with the `<x-component-name>` syntax in any view:

```html
<!-- Button -->
<x-button variant="primary">Submit Form</x-button>
<x-button variant="ghost" size="sm">Cancel</x-button>

<!-- Input with validation error -->
<x-input name="email" label="Email Address" type="email" :error="$errors->first('email')" />

<!-- Alert message -->
<x-alert variant="success" title="Saved!" dismissible>
    Your changes have been saved successfully.
</x-alert>

<!-- Card -->
<x-card title="Recent Posts">
    <p>Card body content goes here.</p>
</x-card>

<!-- Modal -->
<x-modal id="confirm-delete" title="Confirm Delete">
    <p>Are you sure you want to delete this item?</p>
    <x-slot name="footer">
        <x-button variant="danger" onclick="closeModal('confirm-delete')">Delete</x-button>
        <x-button variant="ghost" onclick="closeModal('confirm-delete')">Cancel</x-button>
    </x-slot>
</x-modal>
```

## Component Reference

| Component | Key Props |
|---|---|
| **Button** | `variant` (primary, secondary, ghost, danger), `size` (sm, md, lg) |
| **Input** | `name`, `label`, `type`, `value`, `error`, `required` |
| **Textarea** | `name`, `label`, `rows`, `error` |
| **Select** | `name`, `label`, `options` (array), `selected`, `error` |
| **Checkbox** | `name`, `label`, `value`, `checked` |
| **Radio** | `name`, `value`, `label`, `checked` |
| **Badge** | `variant` (default, success, warning, danger, info), `dot` |
| **Alert** | `variant`, `title`, `dismissible` |
| **Card** | `title`, `subtitle`, footer slot |
| **Modal** | `id`, `title`, `size` (sm, md, lg, xl) |
| **Spinner** | `size` (sm, md, lg), `label` |
| **Avatar** | `src`, `name` (initials fallback), `size`, `shape` |
| **Dropdown** | `label`, `align` (left, right) |
| **Navbar** | `brand`, `brandHref`, `sticky` |
| **Toast** | `id`, `variant`, `message`, `duration` (ms) |
| **Tabs** | `id`, `tabs` (array key=>label), `active` |
| **Accordion** | `id`, `title`, `open` |
| **Progress** | `value` (0-100), `variant`, `striped`, `animated` |
| **Tooltip** | `text`, `position` (top, bottom, left, right) |
| **Breadcrumb** | `items` (array of label+href) |
| **Table** | `striped`, `hover`, `bordered`, `compact` |

---

## VS Code Extension

## Installing the Extension

The Veldora VS Code extension adds full syntax highlighting, snippets, and auto-completion for `.veldora.php` template files.

Install it from the [VS Code Marketplace](https://marketplace.visualstudio.com/items?itemName=veldora.veldora-vscode) or from the command line:

```bash
code --install-extension veldora.veldora-vscode
```

## What You Get

- **Syntax highlighting** — `@directives`, `{{ expressions }}`, and embedded PHP all highlighted correctly
- **32 snippets** — type `v-if`, `v-foreach`, `v-comp`, etc. and press Tab to expand
- **Bracket matching** — `@if` pairs with its `@endif` automatically
- **Embedded PHP** — full PHP IntelliSense inside template files

## Snippet Quick Reference

| Prefix | Expands to |
|---|---|
| `v-if` | `@if ... @endif` |
| `v-foreach` | `@foreach ... @endforeach` |
| `v-forelse` | `@forelse ... @empty ... @endforelse` |
| `v-unless` | `@unless ... @endunless` |
| `v-extends` | `@extends('layout')` |
| `v-section` | `@section('name') ... @endsection` |
| `v-yield` | `@yield('name')` |
| `v-comp` | `<x-component> ... </x-component>` |
| `v-auth` | `@auth ... @endauth` |
| `v-guest` | `@guest ... @endguest` |
| `v-csrf` | `@csrf` |
| `v-dump` | `@dump($var)` |
| `v-esc` | `{{ $variable }}` |

---

## AI Context Prompt

## Using Veldora with AI Assistants

Copy the prompt below and paste it at the start of any conversation with an AI assistant (like Claude, ChatGPT, or Gemini). The AI will then know the full Veldora API and can generate correct, idiomatic Veldora code without making things up.

---

> You are an expert in the **Veldora PHP framework** (version 1.0). Veldora is a modern PHP 8.2+ MVC framework inspired by Laravel but completely independent. Here is a complete reference of its API:
>
> **Routing:** `$router->get/post/put/delete/patch($uri, $handler)->middleware([...])`. Groups via `$router->group(['prefix' => '/api', 'middleware' => ['auth']], fn($r) => ...)`.
>
> **Controllers:** Plain PHP classes. Methods receive `Request $request`. Return `view()`, `Response::json()`, or `Response::redirect()`.
>
> **Templates:** Files end in `.veldora.php`. Use `@extends`, `@section`, `@yield`, `@if/@endif`, `@foreach/@endforeach`, `@forelse/@empty/@endforelse`, `@auth/@endauth`, `@guest/@endguest`. Variables: `{{ $var }}` (escaped), `{!! $var !!}` (raw). Components: `<x-button variant="primary">Label</x-button>`.
>
> **Models:** Extend `Veldora\Framework\Database\Model`. Properties: `$table`, `$fillable`, `$guarded`, `$casts`, `$hidden`. Methods: `::find($id)`, `::all()`, `::create($data)`, `->save()`, `->delete()`, `->where()->get()`, `->paginate(15)`. Relations: `hasMany()`, `belongsTo()`, `belongsToMany()`, `hasManyThrough()`.
>
> **Auth:** `auth()->check()`, `auth()->user()`, `auth()->login($user)`, `auth()->logout()`. Middleware aliases: `auth`, `guest`, `admin`, `verified`.
>
> **Validation:** `$request->validated(['field' => 'required|min:3'])`. Or `make:request` form request class with `rules()` and `authorize()` methods. Rules: `required`, `email`, `min:N`, `max:N`, `numeric`, `unique:table,col`, `confirmed`, `nullable`.
>
> **Cache:** `cache(['key' => $val], $ttl)`, `cache('key')`, `cache()->remember('key', $ttl, fn() => ...)`, `cache()->forget('key')`, `cache()->increment('key')`.
>
> **Storage:** `storage('public')->put($path, $contents)`, `->get($path)`, `->exists($path)`, `->url($path)`, `->delete($path)`.
>
> **Logging:** `log_info('msg', $ctx)`, `log_error('msg', $ctx)`, `logger()->warning('msg')`.
>
> **Mail:** `mailer($email)->send(new MyMailable())`, `mailer($email)->queue(new MyMailable())`. Mailables extend `Mailable` and implement `build()` returning `$this->subject()->view()`.
>
> **Queue:** Jobs extend `Job`, implement `handle()`. Dispatch: `MyJob::dispatch($args)->onQueue('name')->delay(60)`. Worker: `php veldora queue:work`.
>
> **Events:** Events extend `Event`. Dispatch: `MyEvent::dispatch($data)` or `event(new MyEvent($data))`. Listeners implement `handle(Event $event)`.
>
> **HTTP Client:** `Http::get($url)`, `Http::post($url, $data)`, `Http::withToken($t)->acceptJson()->get($url)`. Response: `->json()`, `->successful()`, `->status()`.
>
> **API Resources:** Extend `JsonResource`, implement `toArray($request)`. Use: `new MyResource($model)`, `MyResource::collection($collection)`.
>
> **Testing:** Extend `TestCase`. Methods: `$this->get/post/put/delete($uri, $data)`, `->actingAs($user)`. Assertions: `->assertOk()`, `->assertStatus(N)`, `->assertRedirect()`, `->assertSee()`, `->assertJson()`.
>
> **CLI generators:** `php veldora make:controller`, `make:model`, `make:migration`, `make:middleware`, `make:request`, `make:resource`, `make:job`, `make:event`, `make:listener`, `make:mail`, `make:seeder`, `make:factory`, `make:auth`, `make:command`. DB: `migrate`, `migrate:rollback`, `migrate:fresh`, `migrate:status`, `db:seed`. Queue: `queue:work`, `queue:failed`, `queue:retry`, `queue:clear`. UI: `ui:list`, `add <component>`.
>
> Generate complete, working Veldora code. Never use Laravel-specific classes or helpers that don't exist in Veldora.

---

> **Framework Version:** `v1.0.0`
> **Test Coverage:** 87 tests, 344 assertions — all passing
> **PHP Required:** 8.2+
> **License:** MIT
