<?php

declare(strict_types=1);

require_once __DIR__ . '/../create-veldora-app/template/bootstrap/autoload.php';

$app = require __DIR__ . '/../create-veldora-app/template/bootstrap/app.php';

use Veldora\Framework\Database\Connection;
use Veldora\Framework\Database\DB;
use Veldora\Framework\Database\Model;
use Veldora\Framework\Database\SoftDeletes;
use Veldora\Framework\Auth\Auth;
use Veldora\Framework\Auth\PasswordBroker;
use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Http\Router;
use Veldora\Framework\Http\Middleware\ThrottleRequests;
use Veldora\Framework\Http\Middleware\CheckForMaintenanceMode;
use Veldora\Framework\View\Engine as ViewEngine;
use Veldora\UI\Registry\ComponentRegistry;

$results = [];
$passed = 0;
$failed = 0;

function it(string $description, callable $test) {
    global $results, $passed, $failed;
    try {
        $test();
        $results[] = "[\033[32mPASS\033[0m] " . $description;
        $passed++;
    } catch (\Throwable $e) {
        $results[] = "[\033[31mFAIL\033[0m] " . $description . "\n       Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
        $failed++;
    }
}

echo "\n\033[35m=== VELDORA 0.5.0 FULL E2E SUITE ===\033[0m\n\n";

// 1. Database & DB Facade
it('DB Facade executes raw SQL and transactions', function () {
    $db = db();
    $db->statement("DROP TABLE IF EXISTS test_items");
    $db->statement("CREATE TABLE test_items (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, created_at TEXT, updated_at TEXT, deleted_at TEXT NULL)");

    $db->transaction(function () use ($db) {
        $db->statement("INSERT INTO test_items (name) VALUES (?)", ['Item 1']);
        $db->statement("INSERT INTO test_items (name) VALUES (?)", ['Item 2']);
    });

    $rows = $db->select("SELECT * FROM test_items");
    if (count($rows) !== 2) throw new \Exception("Expected 2 rows, got " . count($rows));
});

// 2. Model & SoftDeletes
class TestItem extends Model {
    use SoftDeletes;
    protected ?string $table = 'test_items';
}

it('Model SoftDeletes works correctly with scopes', function () {
    $item = new TestItem();
    $item->name = 'Soft Delete Item';
    $item->save();
    $id = $item->id;

    $found = TestItem::find($id);
    if (!$found) throw new \Exception("Item not found");

    $item->delete(); // soft delete

    $afterDelete = TestItem::find($id);
    if ($afterDelete !== null) throw new \Exception("Item should be filtered by SoftDeletes");

    $withTrashed = TestItem::withTrashed()->where('id', $id)->first();
    if (!$withTrashed) throw new \Exception("Item not found with withTrashed()");

    $item->restore();
    $restored = TestItem::find($id);
    if (!$restored) throw new \Exception("Item should be restored");
});

// 3. Named Routes & route() Helper
it('Router generates URLs for named routes', function () {
    $router = app(Router::class);
    $router->get('/users/{id}', fn() => 'user')->name('users.show');
    $router->get('/posts/{slug}/comments/{id?}', fn() => 'comment')->name('posts.comments');

    $url1 = route('users.show', ['id' => 42]);
    if ($url1 !== '/users/42') throw new \Exception("Expected /users/42, got {$url1}");

    $url2 = route('posts.comments', ['slug' => 'hello-world', 'id' => 7]);
    if ($url2 !== '/posts/hello-world/comments/7') throw new \Exception("Expected /posts/hello-world/comments/7, got {$url2}");
});

// 4. Rate Limiting Middleware (ThrottleRequests)
it('ThrottleRequests rate-limits after max attempts', function () {
    $throttle = new ThrottleRequests(3, 1);
    $req = Request::create('GET', '/api/test-rate-limit');
    $throttle->clear($req);

    $next = fn($r) => new Response('OK', 200);

    $res1 = $throttle->handle($req, $next);
    if ($res1->getStatusCode() !== 200) throw new \Exception("Attempt 1 failed");

    $res2 = $throttle->handle($req, $next);
    if ($res2->getStatusCode() !== 200) throw new \Exception("Attempt 2 failed");

    $res3 = $throttle->handle($req, $next);
    if ($res3->getStatusCode() !== 200) throw new \Exception("Attempt 3 failed");

    $res4 = $throttle->handle($req, $next);
    if ($res4->getStatusCode() !== 429) throw new \Exception("Attempt 4 should be 429, got " . $res4->getStatusCode());
});

// 5. Maintenance Mode Middleware
it('CheckForMaintenanceMode handles down status and bypass secret', function () {
    $downFile = storage_path('framework/down');
    $middleware = new CheckForMaintenanceMode();
    $next = fn($r) => new Response('Live App', 200);

    // Live
    if (file_exists($downFile)) unlink($downFile);
    $reqLive = Request::create('GET', '/');
    $resLive = $middleware->handle($reqLive, $next);
    if ($resLive->getStatusCode() !== 200) throw new \Exception("Live app returned non-200");

    // Put Down
    file_put_contents($downFile, json_encode(['secret' => 'supersecret']));
    $reqDown = Request::create('GET', '/');
    $resDown = $middleware->handle($reqDown, $next);
    if ($resDown->getStatusCode() !== 503) throw new \Exception("Down app returned " . $resDown->getStatusCode());

    // Bypass with secret
    $reqBypass = Request::create('GET', '/?secret=supersecret');
    $resBypass = $middleware->handle($reqBypass, $next);
    if ($resBypass->getStatusCode() !== 200) throw new \Exception("Bypass failed");

    // Clean up
    if (file_exists($downFile)) unlink($downFile);
});

// 6. View Compiler Directives
it('View Compiler compiles @if, @csrf, @method, {{ }} and custom directives', function () {
    /** @var ViewEngine $engine */
    $engine = app(ViewEngine::class);
    $template = <<<'VELDORA'
@if ($isAdmin)
  <h1>Hello {{ $name }}</h1>
  @csrf
  @method('PUT')
@else
  <p>Guest</p>
@endif
VELDORA;

    $tempFile = storage_path('framework/views/test_template.veldora.php');
    file_put_contents($tempFile, $template);

    $htmlAdmin = $engine->renderFile($tempFile, ['isAdmin' => true, 'name' => 'Admin User']);
    if (!str_contains($htmlAdmin, 'Hello Admin User')) throw new \Exception("Name interpolation failed");
    if (!str_contains($htmlAdmin, 'name="_token"')) throw new \Exception("@csrf failed");
    if (!str_contains($htmlAdmin, 'name="_method" value="PUT"')) throw new \Exception("@method failed");

    $htmlGuest = $engine->renderFile($tempFile, ['isAdmin' => false, 'name' => 'Anon']);
    if (!str_contains($htmlGuest, 'Guest')) throw new \Exception("Else condition failed");

    unlink($tempFile);
});

// 7. Veldora UI Registry (Footer & Rating)
it('ComponentRegistry includes footer and rating components', function () {
    $registry = new ComponentRegistry();
    if (!$registry->has('footer')) throw new \Exception("footer component missing from registry");
    if (!$registry->has('rating')) throw new \Exception("rating component missing from registry");

    $footer = $registry->get('footer');
    if (!str_contains($footer['template'], 'vui-footer')) throw new \Exception("footer template invalid");

    $rating = $registry->get('rating');
    if (!str_contains($rating['template'], 'vui-rating')) throw new \Exception("rating template invalid");
});

// 8. PasswordBroker Reset Flow
it('PasswordBroker generates tokens and resets password', function () {
    db()->statement("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT UNIQUE, password TEXT, remember_token TEXT, reset_token TEXT, reset_token_expires_at TEXT, deleted_at TEXT, created_at TEXT, updated_at TEXT)");
    db()->statement("DELETE FROM users WHERE email = 'test@example.com'");
    db()->statement("INSERT INTO users (name, email, password) VALUES ('Test User', 'test@example.com', ?)", [password_hash('oldpassword', PASSWORD_BCRYPT)]);

    $token = PasswordBroker::createToken('test@example.com');
    if (empty($token)) throw new \Exception("Token generation failed");

    $resetOk = PasswordBroker::reset('test@example.com', $token, 'newpassword123');
    if (!$resetOk) throw new \Exception("Password reset failed");

    $user = db()->selectOne("SELECT * FROM users WHERE email = 'test@example.com'");
    if (!password_verify('newpassword123', $user['password'])) {
        throw new \Exception("New password verify failed");
    }
});

// Print summary
foreach ($results as $res) {
    echo $res . "\n";
}

echo "\n\033[1mSummary:\033[0m {$passed} Passed, {$failed} Failed\n\n";
exit($failed > 0 ? 1 : 0);
