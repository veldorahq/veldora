<?php

declare(strict_types=1);

namespace Database\Seeders;

use Veldora\Framework\Database\Connection;

class DatabaseSeeder
{
    public function __construct(protected Connection $db)
    {
    }

    public function run(): void
    {
        // Seed default records here
    }
}
