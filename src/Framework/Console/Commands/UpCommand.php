<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class UpCommand extends Command
{
    protected static ?string $defaultName = 'up';

    protected function configure(): void
    {
        $this
            ->setName('up')
            ->setDescription('Bring the application out of maintenance mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $app = Application::getInstance();
        $downFile = $app->storagePath('framework/down');

        if (file_exists($downFile)) {
            unlink($downFile);
            $output->writeln('<info>Application is now live.</info>');
        } else {
            $output->writeln('<comment>Application is already live.</comment>');
        }

        return Command::SUCCESS;
    }
}
