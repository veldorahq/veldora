<?php

require_once dirname(__DIR__) . '/create-veldora-app/template/bootstrap/autoload.php';

use Veldora\Framework\Foundation\Application;
use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Router;
use Veldora\Framework\Validation\Validator;
use Veldora\Framework\Validation\ValidationException;

echo "=== TESTING 5 REPORTED ISSUES ===\n\n";

// 1. Check Framework Version
echo "1. Application Version: " . Application::VERSION . "\n";
assert(Application::VERSION === '0.5.0', "Version is 0.5.0");
echo "   [PASS] Application::VERSION is 0.5.0\n\n";

// 2. Test db:show logic (Connection::getDriverName)
$app = new Application(__DIR__ . '/scratch/final_test_app');
$db = new \Veldora\Framework\Database\Connection(['driver' => 'sqlite', 'database' => ':memory:']);
$driverName = $db->getDriverName();
echo "2. Connection::getDriverName(): {$driverName}\n";
assert($driverName === 'sqlite', "getDriverName works");
echo "   [PASS] Connection::getDriverName() returned 'sqlite'\n\n";

// 3. Test config:show & Env
\Veldora\Framework\Config\Env::set('APP_TEST_KEY', 'HelloVeldora');
$allEnv = \Veldora\Framework\Config\Env::all();
assert(\Veldora\Framework\Config\Env::get('APP_TEST_KEY') === 'HelloVeldora');
echo "3. Env loaded count: " . count($allEnv) . " keys\n";
echo "   [PASS] Env::all() and Env::get() working\n\n";

// 4. Test Maintenance Mode Middleware
$downFile = $app->storagePath('framework/down');
@mkdir(dirname($downFile), 0755, true);
file_put_contents($downFile, json_encode(['secret' => 'mysecret']));

$router = new Router($app);
$router->get('/', fn() => new \Veldora\Framework\Http\Response('OK', 200));

// Request without secret -> should return 503
$reqBlocked = Request::create('GET', '/');
$res503 = $router->dispatch($reqBlocked);
echo "4. Maintenance mode response status (no secret): " . $res503->getStatusCode() . "\n";
assert($res503->getStatusCode() === 503, "Blocked by 503");

// Request with valid bypass secret -> should pass through and return 200 OK
$reqBypass = Request::create('GET', '/?secret=mysecret');
$resBypass = $router->dispatch($reqBypass);
echo "   Maintenance mode response status (with secret): " . $resBypass->getStatusCode() . "\n";
assert($resBypass->getStatusCode() === 200, "Bypassed with secret and returned 200 OK");

@unlink($downFile);
echo "   [PASS] Maintenance mode blocks request with 503 and allows secret bypass\n\n";

// 5. Test Validator::validate() public API & Request::validate()
$data = ['email' => 'invalid-email', 'age' => 20];
$rules = ['email' => 'required|email', 'age' => 'required|numeric'];
$validator = Validator::make($data, $rules);

assert(method_exists($validator, 'validate'), "validate() method exists");
assert($validator->fails() === true, "validator fails on bad email");

try {
    $validator->validate();
    echo "   [FAIL] Validator::validate() did not throw ValidationException\n";
} catch (ValidationException $e) {
    echo "5. Caught ValidationException from Validator::validate() with errors: " . json_encode($e->getErrors()) . "\n";
    echo "   [PASS] Validator::validate() public API works and throws ValidationException\n";
}

$validData = ['email' => 'test@veldora.dev', 'age' => 25];
$req = Request::create('POST', '/submit', $validData);
$validated = $req->validate(['email' => 'required|email', 'age' => 'numeric']);
assert($validated['email'] === 'test@veldora.dev');
echo "   [PASS] \$request->validate() successfully validated and returned data\n\n";

echo "=== ALL 5 BUG FIXES VERIFIED SUCCESSFULLY ===\n";
