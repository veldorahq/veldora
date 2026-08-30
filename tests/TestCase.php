<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Veldora\Framework\Foundation\Application;

abstract class TestCase extends BaseTestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = require __DIR__ . '/../bootstrap/app.php';
        $this->app->boot();
    }
}
