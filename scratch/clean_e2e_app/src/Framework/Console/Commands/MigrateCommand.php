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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $app = Application::getInstance();
        
        /** @var Migrator $migrator */
        $migrator = $app->get(Migrator::class);
        $migrationDir = $app->basePath('database/migrations');

        $output->writeln('<info>Running migrations...</info>');

        $ran = $migrator->run($migrationDir);

        if (empty($ran)) {
            $output->writeln('<comment>Nothing to migrate.</comment>');
            return Command::SUCCESS;
        }

        foreach ($ran as $migration) {
            $output->writeln("<info>Migrated:</info> {$migration}");
        }

        return Command::SUCCESS;
    }
}
