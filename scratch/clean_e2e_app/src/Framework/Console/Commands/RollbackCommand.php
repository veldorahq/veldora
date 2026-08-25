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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $app = Application::getInstance();
        
        /** @var Migrator $migrator */
        $migrator = $app->get(Migrator::class);
        $migrationDir = $app->basePath('database/migrations');

        $output->writeln('<info>Rolling back migrations...</info>');

        $rolledBack = $migrator->rollback($migrationDir);

        if (empty($rolledBack)) {
            $output->writeln('<comment>Nothing to rollback.</comment>');
            return Command::SUCCESS;
        }

        foreach ($rolledBack as $migration) {
            $output->writeln("<info>Rolled back:</info> {$migration}");
        }

        return Command::SUCCESS;
    }
}
