<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class DownCommand extends Command
{
    protected static ?string $defaultName = 'down';

    protected function configure(): void
    {
        $this
            ->setName('down')
            ->setDescription('Put the application into maintenance mode')
            ->addOption('secret', null, InputOption::VALUE_OPTIONAL, 'Secret token to bypass maintenance mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $app = Application::getInstance();
        $downFile = $app->storagePath('framework/down');

        $dir = dirname($downFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $payload = [
            'down_at' => date('Y-m-d H:i:s'),
            'secret'  => $input->getOption('secret'),
        ];

        file_put_contents($downFile, json_encode($payload, JSON_PRETTY_PRINT));

        $output->writeln('<comment>Application is now in maintenance mode.</comment>');

        if ($payload['secret']) {
            $output->writeln("  Bypass via: <info>?secret={$payload['secret']}</info>");
        }

        return Command::SUCCESS;
    }
}
