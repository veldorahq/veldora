<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Queue\Drivers\DatabaseDriver;
use Veldora\Framework\Queue\QueueManager;

class QueueRetryCommand extends Command
{
    protected static ?string $defaultName = 'queue:retry';

    protected function configure(): void
    {
        $this
            ->setName('queue:retry')
            ->setDescription('Retry a failed queue job by ID or all')
            ->addArgument('id', InputArgument::REQUIRED, 'The ID of the failed job or "all"');
    }

    public function executeDirect(string $id): int
    {
        /** @var QueueManager $manager */
        $manager = app(QueueManager::class);
        $driver = $manager->driver('database');

        if (!($driver instanceof DatabaseDriver)) {
            echo "\033[33mQueue retry is only supported on the database queue driver.\033[0m\n";
            return Command::FAILURE;
        }

        $count = $driver->retryFailed($id);

        if ($count > 0) {
            echo "\033[32m✔ Successfully pushed {$count} failed job(s) back onto the queue.\033[0m\n";
        } else {
            echo "\033[31mNo failed job found matching ID [{$id}].\033[0m\n";
        }

        return Command::SUCCESS;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = (string) $input->getArgument('id');
        return $this->executeDirect($id);
    }
}
