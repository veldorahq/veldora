<?php

declare(strict_types=1);

$app = require __DIR__ . '/bootstrap/app.php';
$app->boot();

use Veldora\Framework\Database\Connection;
use Veldora\Framework\Database\Schema\Migrator;

$connection = $app->get(Connection::class);
$migrator = new Migrator($connection);

echo "Running migrations...\n";
$ran = $migrator->run($app->basePath('database/migrations'));
echo "Migrations completed: " . (empty($ran) ? "None" : implode(', ', $ran)) . "\n";

$pdo = $connection->getPdo();
$count = (int) $pdo->query('SELECT COUNT(*) FROM posts;')->fetchColumn();

if ($count === 0) {
    echo "Seeding default post...\n";
    $stmt = $pdo->prepare('INSERT INTO posts (title, body, created_at, updated_at) VALUES (?, ?, ?, ?);');
    $stmt->execute([
        'Getting Started with Veldora',
        'Veldora is a premium PHP framework featuring PSR-11 compliance, reflection-driven autowiring, and an Active Record ORM built from the ground up.',
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s')
    ]);
    echo "Seeding completed successfully.\n";
}
