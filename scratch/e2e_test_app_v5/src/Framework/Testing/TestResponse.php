<?php

declare(strict_types=1);

namespace Veldora\Framework\Testing;

use PHPUnit\Framework\Assert;
use Veldora\Framework\Http\Response;

class TestResponse
{
    public function __construct(public Response $baseResponse)
    {
    }

    public function status(): int
    {
        return $this->baseResponse->getStatusCode();
    }

    public function getContent(): string
    {
        return $this->baseResponse->getContent();
    }

    public function json(?string $key = null, mixed $default = null): mixed
    {
        $decoded = json_decode($this->getContent(), true);

        if (!is_array($decoded)) {
            return $default;
        }

        if ($key === null) {
            return $decoded;
        }

        return $decoded[$key] ?? $default;
    }

    public function assertStatus(int $status): static
    {
        $actual = $this->status();
        Assert::assertSame($status, $actual, "Expected HTTP status [{$status}] but received [{$actual}]. Response: {$this->getContent()}");
        return $this;
    }

    public function assertOk(): static
    {
        return $this->assertStatus(200);
    }

    public function assertCreated(): static
    {
        return $this->assertStatus(201);
    }

    public function assertForbidden(): static
    {
        return $this->assertStatus(403);
    }

    public function assertNotFound(): static
    {
        return $this->assertStatus(404);
    }

    public function assertUnauthorized(): static
    {
        return $this->assertStatus(401);
    }

    public function assertUnprocessable(): static
    {
        return $this->assertStatus(422);
    }

    public function assertSee(string $value, bool $escape = true): static
    {
        $content = $this->getContent();
        Assert::assertStringContainsString($value, $content, "Expected response to contain [{$value}].");
        return $this;
    }

    public function assertDontSee(string $value): static
    {
        $content = $this->getContent();
        Assert::assertStringNotContainsString($value, $content, "Expected response not to contain [{$value}].");
        return $this;
    }

    public function assertJson(array $data): static
    {
        $json = $this->json();
        Assert::assertIsArray($json, "Response does not contain valid JSON: {$this->getContent()}");

        foreach ($data as $key => $expectedValue) {
            Assert::assertArrayHasKey($key, $json);
            Assert::assertEquals($expectedValue, $json[$key]);
        }

        return $this;
    }

    public function assertJsonFragment(array $data): static
    {
        $json = $this->json();
        Assert::assertIsArray($json, "Response does not contain valid JSON.");

        $encodedHaystack = json_encode($json);
        $encodedNeedle = json_encode($data);

        // Remove opening/closing braces for subset match
        $trimmedNeedle = trim($encodedNeedle, '{}[]');
        Assert::assertStringContainsString($trimmedNeedle, $encodedHaystack, "Unable to find JSON fragment [{$encodedNeedle}] in response.");

        return $this;
    }

    public function assertRedirect(?string $uri = null): static
    {
        $status = $this->status();
        Assert::assertTrue($status >= 300 && $status < 400, "Response status [{$status}] is not a redirect.");

        if ($uri !== null) {
            $location = $this->baseResponse->getHeaders()['Location'] ?? null;
            Assert::assertSame($uri, $location, "Expected redirect to [{$uri}] but received [{$location}].");
        }

        return $this;
    }

    public function assertHeader(string $headerName, ?string $value = null): static
    {
        $headers = $this->baseResponse->getHeaders();
        $normalized = strtolower($headerName);

        $found = false;
        $actualVal = null;
        foreach ($headers as $k => $v) {
            if (strtolower($k) === $normalized) {
                $found = true;
                $actualVal = $v;
                break;
            }
        }

        Assert::assertTrue($found, "Header [{$headerName}] was not present in response.");

        if ($value !== null) {
            Assert::assertSame($value, $actualVal);
        }

        return $this;
    }
}
