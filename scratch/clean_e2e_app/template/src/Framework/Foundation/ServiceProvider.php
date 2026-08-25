<?php

declare(strict_types=1);

namespace Veldora\Framework\Foundation;

abstract class ServiceProvider
{
    /**
     * Create a new service provider instance.
     */
    public function __construct(protected Application $app)
    {
    }

    /**
     * Register any application services.
     */
    abstract public function register(): void;

    /**
     * Boot any application services.
     */
    public function boot(): void
    {
        // Default implementation does nothing
    }
}
