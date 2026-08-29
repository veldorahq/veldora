<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Queue\Drivers\DatabaseDriver;
use Veldora\Framework\Queue\QueueManager;

class QueueFailedCommand extends Command
{
    protected static ?string $defaultName = 'queue:failed';

    protected function configure(): void
    {
        $this
            ->setName('queue:failed')
            ->setDescription('List all failed queue jobs');
    }

    public function executeDirect(): int
    {
        /** @var QueueManager $manager */
        $manager = app(QueueManager::class);
        $driver = $manager->driver('database');

        if (!($driver instanceof DatabaseDriver)) {
            echo "\033[33mFailed jobs tracking is only supported on the database queue driver.\033[0m\n";
            return Command::SUCCESS;
        }

        $failed = $driver->getFailedJobs();

        if (empty($failed)) {
            echo "\033[32mNo failed jobs found.\033[0m\n";
            return Command::SUCCESS;
        }

        echo "\n\033[1;31mFailed Queue Jobs (" . count($failed) . ")\033[0m\n";
        echo str_repeat("─", 80) . "\n";
        echo sprintf("%-6s | %-12s | %-30s | %s\n", "ID", "Queue", "Failed At", "Exception");
        echo str_repeat("─", 80) . "\n";

        foreach ($failed as $job) {
            $lines = explode("\n", $job['exception']);
            $firstLine = mb_substr(trim($lines[0] ?? ''), 0, 50);
            echo sprintf("%-6d | %-12s | %-30s | %s\n", $job['id'], $job['queue'], $job['failed_at'], $firstLine);
        }

        echo str_repeat("─", 80) . "\n";
        echo "Run \033[36mphp veldora queue:retry <id|all>\033[0m to retry failed jobs.\n\n";

        return Command::SUCCESS;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->executeDirect();
    }
}
