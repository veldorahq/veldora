<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Database\Connection;
use Veldora\Framework\Foundation\Application;

class MigrateStatusCommand extends Command
{
    protected static ?string $defaultName = 'migrate:status';

    protected function configure(): void
    {
        $this
            ->setName('migrate:status')
            ->setDescription('Show the status of each migration');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        $db = $app->get(Connection::class);
        $pdo = $db->getPdo();

        echo "\n\033[35m\033[1m  ▲ Veldora Migration Status\033[0m\n\n";

        // Check if migrations table exists
        $hasMigrationsTable = false;
        try {
            $pdo->query("SELECT 1 FROM migrations LIMIT 1");
            $hasMigrationsTable = true;
        } catch (\Throwable $e) {
            $hasMigrationsTable = false;
        }

        $ranMigrations = [];
        if ($hasMigrationsTable) {
            $stmt = $pdo->query("SELECT migration, batch FROM migrations ORDER BY id ASC");
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $ranMigrations[$row['migration']] = $row['batch'];
            }
        }

        $migrationsDir = $app->basePath('database/migrations');
        $files = [];
        if (is_dir($migrationsDir)) {
            foreach (scandir($migrationsDir) as $f) {
                if (str_ends_with($f, '.php')) {
                    $files[] = basename($f, '.php');
                }
            }
            sort($files);
        }

        if (empty($files)) {
            echo "  \033[90mNo migration files found.\033[0m\n\n";
            return;
        }

        printf("  %-12s %-50s %-10s\n", "\033[1mRan?\033[0m", "\033[1mMigration\033[0m", "\033[1mBatch\033[0m");
        echo "  " . str_repeat('─', 74) . "\n";

        foreach ($files as $file) {
            if (isset($ranMigrations[$file])) {
                $status = "\033[32m✔ Yes\033[0m";
                $batch = "\033[36m[" . $ranMigrations[$file] . "]\033[0m";
            } else {
                $status = "\033[31m✖ Pending\033[0m";
                $batch = "\033[90m—\033[0m";
            }

            printf("  %-20s %-50s %-10s\n", $status, $file, $batch);
        }

        echo "\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
