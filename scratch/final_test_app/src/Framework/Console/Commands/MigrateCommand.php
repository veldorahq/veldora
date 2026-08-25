<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Database\Schema\Migrator;
use Veldora\Framework\Foundation\Application;

class MigrateCommand extends Command
{
    protected static ?string $defaultName = 'migrate';

    protected function configure(): void
    {
        $this
            ->setName('migrate')
            ->setDescription('Run the database migrations');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        
        /** @var Migrator $migrator */
        $migrator = $app->get(Migrator::class);
        $migrationDir = $app->basePath('database/migrations');

        echo "\n\033[36mRunning migrations...\033[0m\n";

        $ran = $migrator->run($migrationDir);

        if (empty($ran)) {
            echo "  \033[90mNothing to migrate.\033[0m\n\n";
            return;
        }

        foreach ($ran as $migration) {
            echo "  \033[32m✔ Migrated:\033[0m {$migration}\n";
        }
        echo "\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
