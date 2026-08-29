<?php

declare(strict_types=1);

namespace Veldora\Framework\Testing;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Veldora\Framework\Auth\AuthManager;
use Veldora\Framework\Database\Model;
use Veldora\Framework\Foundation\Application;
use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;
use Veldora\Framework\Http\Router;

abstract class TestCase extends BaseTestCase
{
    /**
     * The application instance.
     */
    protected Application $app;

    /**
     * Additional server headers for testing requests.
     *
     * @var array<string, string>
     */
    protected array $serverVariables = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = $this->createApplication();
    }

    /**
     * Create and bootstrap the application instance.
     */
    protected function createApplication(): Application
    {
        $app = Application::getInstance();

        if (!$app->isBound(Router::class)) {
            $app->singleton(Router::class, fn () => new Router($app));
        }

        return $app;
    }

    /**
     * Set the currently logged in user for testing.
     */
    public function actingAs(Model $user): static
    {
        /** @var AuthManager $auth */
        $auth = $this->app->get(AuthManager::class);
        $auth->setUser($user);

        return $this;
    }

    /**
     * Add headers to the upcoming test request.
     *
     * @param array<string, string> $headers
     */
    public function withHeaders(array $headers): static
    {
        foreach ($headers as $key => $value) {
            $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
            $this->serverVariables[$serverKey] = $value;
        }

        return $this;
    }

    /**
     * Perform a GET request within the application.
     *
     * @param array<string, string> $headers
     */
    public function get(string $uri, array $headers = []): TestResponse
    {
        return $this->call('GET', $uri, [], $headers);
    }

    /**
     * Perform a POST request within the application.
     *
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public function post(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->call('POST', $uri, $data, $headers);
    }

    /**
     * Perform a PUT request within the application.
     *
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public function put(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->call('PUT', $uri, $data, $headers);
    }

    /**
     * Perform a PATCH request within the application.
     *
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public function patch(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->call('PATCH', $uri, $data, $headers);
    }

    /**
     * Perform a DELETE request within the application.
     *
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public function delete(string $uri, array $data = [], array $headers = []): TestResponse
    {
        return $this->call('DELETE', $uri, $data, $headers);
    }

    /**
     * Call the given URI and return the Response.
     *
     * @param array<string, mixed> $parameters
     * @param array<string, string> $headers
     */
    public function call(string $method, string $uri, array $parameters = [], array $headers = []): TestResponse
    {
        $this->withHeaders($headers);

        $parsedUrl = parse_url($uri);
        $path = $parsedUrl['path'] ?? '/';
        $queryString = $parsedUrl['query'] ?? '';

        $server = array_merge($_SERVER, $this->serverVariables, [
            'REQUEST_METHOD' => strtoupper($method),
            'REQUEST_URI' => $uri,
            'PATH_INFO' => $path,
            'QUERY_STRING' => $queryString,
        ]);

        $request = new Request(
            $method === 'GET' ? $parameters : [],
            $method !== 'GET' ? $parameters : [],
            [],
            [],
            $server
        );
        $this->app->instance(Request::class, $request);

        /** @var Router $router */
        $router = $this->app->get(Router::class);
        $response = $router->dispatch($request);

        if (!$response instanceof Response) {
            $response = new Response((string) $response);
        }

        return new TestResponse($response);
    }
}
