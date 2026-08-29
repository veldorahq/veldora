<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Queue\QueueManager;

class QueueClearCommand extends Command
{
    protected static ?string $defaultName = 'queue:clear';

    protected function configure(): void
    {
        $this
            ->setName('queue:clear')
            ->setDescription('Delete all jobs from the specified queue')
            ->addOption('connection', null, InputOption::VALUE_OPTIONAL, 'The name of the queue connection', null)
            ->addOption('queue', null, InputOption::VALUE_OPTIONAL, 'The name of the queue to clear', 'default');
    }

    public function executeDirect(string $connection = 'default', string $queue = 'default'): int
    {
        /** @var QueueManager $manager */
        $manager = app(QueueManager::class);
        $driver = $manager->connection($connection);

        $cleared = $driver->clear($queue);

        echo "\033[32m✔ Cleared {$cleared} job(s) from [{$queue}] queue.\033[0m\n";
        return Command::SUCCESS;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = (string) ($input->getOption('connection') ?? config('queue.default', 'sync'));
        $queue = (string) $input->getOption('queue');

        return $this->executeDirect($connection, $queue);
    }
}
