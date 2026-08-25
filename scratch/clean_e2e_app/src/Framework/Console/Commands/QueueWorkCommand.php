<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Queue\QueueManager;
use Veldora\Framework\Queue\Worker;

class QueueWorkCommand extends Command
{
    protected static ?string $defaultName = 'queue:work';

    protected function configure(): void
    {
        $this
            ->setName('queue:work')
            ->setDescription('Start processing jobs on the queue as a daemon')
            ->addOption('connection', null, InputOption::VALUE_OPTIONAL, 'The name of the queue connection', null)
            ->addOption('queue', null, InputOption::VALUE_OPTIONAL, 'The names of the queues to work', 'default')
            ->addOption('once', null, InputOption::VALUE_NONE, 'Only process the next job on the queue')
            ->addOption('sleep', null, InputOption::VALUE_OPTIONAL, 'Number of seconds to sleep when no job is available', 3)
            ->addOption('max-jobs', null, InputOption::VALUE_OPTIONAL, 'The maximum number of jobs to process', 0)
            ->addOption('stop-when-empty', null, InputOption::VALUE_NONE, 'Stop when the queue is empty');
    }

    public function executeDirect(
        string $connection = 'default',
        string $queue = 'default',
        bool $once = false,
        int $sleep = 3,
        int $maxJobs = 0,
        bool $stopWhenEmpty = false
    ): int {
        /** @var QueueManager $manager */
        $manager = app(QueueManager::class);
        $worker = new Worker($manager);

        if ($once) {
            echo "Processing next job on queue [{$queue}]...\n";
            $processed = $worker->processNextJob($connection, $queue);
            if ($processed) {
                echo "\033[32m✔ Processed 1 job successfully.\033[0m\n";
            } else {
                echo "No jobs available on queue [{$queue}].\n";
            }
            return Command::SUCCESS;
        }

        echo "Worker listening on connection [{$connection}] queue [{$queue}]...\n";
        echo "Press Ctrl+C to stop.\n";

        $count = $worker->daemon([
            'connection' => $connection,
            'queue' => $queue,
            'sleep' => $sleep,
            'maxJobs' => $maxJobs,
            'stopWhenEmpty' => $stopWhenEmpty,
        ]);

        echo "\033[32m✔ Worker finished. Processed {$count} job(s).\033[0m\n";
        return Command::SUCCESS;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $connection = (string) ($input->getOption('connection') ?? config('queue.default', 'sync'));
        $queue = (string) $input->getOption('queue');
        $once = (bool) $input->getOption('once');
        $sleep = (int) $input->getOption('sleep');
        $maxJobs = (int) $input->getOption('max-jobs');
        $stopWhenEmpty = (bool) $input->getOption('stop-when-empty');

        return $this->executeDirect($connection, $queue, $once, $sleep, $maxJobs, $stopWhenEmpty);
    }
}
