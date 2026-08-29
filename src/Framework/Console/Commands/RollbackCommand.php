<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Database\Schema\Migrator;
use Veldora\Framework\Foundation\Application;

class RollbackCommand extends Command
{
    protected static ?string $defaultName = 'migrate:rollback';

    protected function configure(): void
    {
        $this
            ->setName('migrate:rollback')
            ->setDescription('Rollback the last database migration');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        
        /** @var Migrator $migrator */
        $migrator = $app->get(Migrator::class);
        $migrationDir = $app->basePath('database/migrations');

        echo "\n\033[36mRolling back migrations...\033[0m\n";

        $rolledBack = $migrator->rollback($migrationDir);

        if (empty($rolledBack)) {
            echo "  \033[90mNothing to rollback.\033[0m\n\n";
            return;
        }

        foreach ($rolledBack as $migration) {
            echo "  \033[33mRolled back:\033[0m {$migration}\n";
        }
        echo "\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
