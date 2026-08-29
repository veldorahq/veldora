<?php

declare(strict_types=1);

namespace Veldora\Framework\Http\Client;

use RuntimeException;
use Throwable;

class PendingRequest
{
    /**
     * The request headers.
     *
     * @var array<string, string>
     */
    protected array $headers = [];

    /**
     * The request query parameters.
     *
     * @var array<string, mixed>
     */
    protected array $query = [];

    /**
     * The body format ('json' or 'form').
     */
    protected string $bodyFormat = 'json';

    /**
     * The connection / request timeout in seconds.
     */
    protected int $timeout = 30;

    /**
     * Number of times to retry a failed request.
     */
    protected int $retries = 0;

    /**
     * Milliseconds to sleep between retries.
     */
    protected int $retryDelay = 100;

    /**
     * Base URL for the request.
     */
    protected string $baseUrl = '';

    /**
     * Set the base URL for the request.
     */
    public function baseUrl(string $url): static
    {
        $this->baseUrl = rtrim($url, '/');
        return $this;
    }

    /**
     * Add headers to the request.
     *
     * @param array<string, string> $headers
     */
    public function withHeaders(array $headers): static
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Add a Bearer token authorization header.
     */
    public function withToken(string $token, string $type = 'Bearer'): static
    {
        $this->headers['Authorization'] = "{$type} {$token}";
        return $this;
    }

    /**
     * Add Basic HTTP Authentication.
     */
    public function withBasicAuth(string $username, string $password): static
    {
        $credentials = base64_encode("{$username}:{$password}");
        $this->headers['Authorization'] = "Basic {$credentials}";
        return $this;
    }

    /**
     * Indicate that JSON is accepted.
     */
    public function acceptJson(): static
    {
        $this->headers['Accept'] = 'application/json';
        return $this;
    }

    /**
     * Format the request body as JSON.
     */
    public function asJson(): static
    {
        $this->bodyFormat = 'json';
        $this->headers['Content-Type'] = 'application/json';
        return $this;
    }

    /**
     * Format the request body as form URL-encoded.
     */
    public function asForm(): static
    {
        $this->bodyFormat = 'form';
        $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
        return $this;
    }

    /**
     * Set the timeout in seconds.
     */
    public function timeout(int $seconds): static
    {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * Set retry attempts.
     */
    public function retry(int $times, int $delay = 100): static
    {
        $this->retries = $times;
        $this->retryDelay = $delay;
        return $this;
    }

    /**
     * Add query parameters to the request.
     *
     * @param array<string, mixed> $query
     */
    public function withQuery(array $query): static
    {
        $this->query = array_merge($this->query, $query);
        return $this;
    }

    /**
     * Issue a GET request.
     *
     * @param array<string, mixed> $query
     */
    public function get(string $url, array $query = []): Response
    {
        return $this->send('GET', $url, ['query' => $query]);
    }

    /**
     * Issue a POST request.
     *
     * @param array<string, mixed>|string $data
     */
    public function post(string $url, array|string $data = []): Response
    {
        return $this->send('POST', $url, ['data' => $data]);
    }

    /**
     * Issue a PUT request.
     *
     * @param array<string, mixed>|string $data
     */
    public function put(string $url, array|string $data = []): Response
    {
        return $this->send('PUT', $url, ['data' => $data]);
    }

    /**
     * Issue a PATCH request.
     *
     * @param array<string, mixed>|string $data
     */
    public function patch(string $url, array|string $data = []): Response
    {
        return $this->send('PATCH', $url, ['data' => $data]);
    }

    /**
     * Issue a DELETE request.
     *
     * @param array<string, mixed>|string $data
     */
    public function delete(string $url, array|string $data = []): Response
    {
        return $this->send('DELETE', $url, ['data' => $data]);
    }

    /**
     * Send the HTTP request through cURL or mock handler.
     *
     * @param array{query?: array<string, mixed>, data?: array<string, mixed>|string} $options
     */
    public function send(string $method, string $url, array $options = []): Response
    {
        $fullUrl = $this->buildUrl($url, $options['query'] ?? []);

        // Check if there is a fake response registered for this URL
        $fake = Http::getFakeResponse($fullUrl, $method);
        if ($fake !== null) {
            return $fake;
        }

        $attempts = 0;
        $maxAttempts = 1 + $this->retries;

        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                return $this->executeCurl($method, $fullUrl, $options['data'] ?? null);
            } catch (Throwable $e) {
                if ($attempts >= $maxAttempts) {
                    throw $e;
                }
                usleep($this->retryDelay * 1000);
            }
        }

        throw new RuntimeException("Failed to execute request to {$fullUrl}");
    }

    /**
     * Execute the cURL request.
     */
    protected function executeCurl(string $method, string $url, mixed $data): Response
    {
        if (!extension_loaded('curl')) {
            throw new RuntimeException('The cURL PHP extension is required for HTTP client.');
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Body payload
        if ($data !== null && $method !== 'GET') {
            if ($this->bodyFormat === 'json') {
                $payload = is_string($data) ? $data : json_encode($data);
                $this->headers['Content-Type'] = 'application/json';
            } else {
                $payload = is_array($data) ? http_build_query($data) : (string) $data;
                $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        // Format headers for cURL
        $formattedHeaders = [];
        foreach ($this->headers as $name => $value) {
            $formattedHeaders[] = "{$name}: {$value}";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $formattedHeaders);

        $rawResponse = curl_exec($ch);

        if ($rawResponse === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException("HTTP request error: {$error}");
        }

        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $rawHeaders = substr((string) $rawResponse, 0, $headerSize);
        $body = substr((string) $rawResponse, $headerSize);

        $parsedHeaders = $this->parseHeaders($rawHeaders);

        return new Response($statusCode, $body, $parsedHeaders);
    }

    /**
     * Build the final request URL with query parameters.
     */
    protected function buildUrl(string $url, array $query): string
    {
        $final = $this->baseUrl !== '' ? $this->baseUrl . '/' . ltrim($url, '/') : $url;
        $mergedQuery = array_merge($this->query, $query);

        if (!empty($mergedQuery)) {
            $qs = http_build_query($mergedQuery);
            $final .= (str_contains($final, '?') ? '&' : '?') . $qs;
        }

        return $final;
    }

    /**
     * Parse raw HTTP headers string into key-value array.
     *
     * @return array<string, string>
     */
    protected function parseHeaders(string $rawHeaders): array
    {
        $headers = [];
        $lines = explode("\r\n", $rawHeaders);

        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                [$key, $val] = explode(':', $line, 2);
                $headers[trim($key)] = trim($val);
            }
        }

        return $headers;
    }
}
