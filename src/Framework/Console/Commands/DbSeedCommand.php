<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Database\Connection;
use Veldora\Framework\Foundation\Application;

class DbSeedCommand extends Command
{
    protected static ?string $defaultName = 'db:seed';

    protected function configure(): void
    {
        $this
            ->setName('db:seed')
            ->setDescription('Seed the database with records');
    }

    public function executeDirect(?string $class = null): void
    {
        $app = Application::getInstance();
        $db = $app->get(Connection::class);
        $seedersDir = $app->basePath('database/seeders');

        echo "\n\033[35m\033[1m  ▲ Running Database Seeders...\033[0m\n\n";

        if (!is_dir($seedersDir)) {
            echo "  \033[90mNo seeders found in database/seeders/\033[0m\n\n";
            return;
        }

        $ran = 0;
        foreach (scandir($seedersDir) as $file) {
            if (str_ends_with($file, '.php')) {
                require_once $seedersDir . '/' . $file;
                $className = 'Database\\Seeders\\' . basename($file, '.php');
                if (class_exists($className)) {
                    $seeder = new $className($db);
                    if (method_exists($seeder, 'run')) {
                        $start = microtime(true);
                        $seeder->run();
                        $ms = round((microtime(true) - $start) * 1000, 2);
                        echo "  \033[32m✔\033[0m Seeded: \033[1m{$className}\033[0m \033[90m({$ms}ms)\033[0m\n";
                        $ran++;
                    }
                }
            }
        }

        if ($ran === 0) {
            echo "  \033[90mNo seeder classes executed. Create one with: php veldora make:seeder UserSeeder\033[0m\n\n";
        } else {
            echo "\n  \033[32m\033[1mDatabase seeding completed successfully!\033[0m\n\n";
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
