<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EnvCommand extends Command
{
    protected static ?string $defaultName = 'env';

    protected function configure(): void
    {
        $this
            ->setName('env')
            ->setDescription('Display the current framework environment');
    }

    public function executeDirect(): void
    {
        $env = env('APP_ENV', 'local');
        $debug = env('APP_DEBUG', true) ? 'true' : 'false';

        echo "\n\033[35m\033[1m  ▲ Current Environment:\033[0m \033[1;32m[{$env}]\033[0m  \033[90m(Debug: {$debug})\033[0m\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
