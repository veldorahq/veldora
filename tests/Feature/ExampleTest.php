<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_application_boots_properly(): void
    {
        $this->assertInstanceOf(\Veldora\Framework\Foundation\Application::class, $this->app);
    }
}
