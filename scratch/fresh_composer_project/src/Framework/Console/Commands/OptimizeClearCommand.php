<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OptimizeClearCommand extends Command
{
    protected static ?string $defaultName = 'optimize:clear';

    protected function configure(): void
    {
        $this
            ->setName('optimize:clear')
            ->setDescription('Remove all cached bootstrap files');
    }

    public function executeDirect(): void
    {
        echo "\n\033[35m\033[1m  ▲ Clearing All Caches...\033[0m\n\n";

        (new ConfigClearCommand())->executeDirect();
        (new RouteClearCommand())->executeDirect();
        (new ViewClearCommand())->executeDirect();
        (new CacheClearCommand())->executeDirect();

        echo "  \033[32m\033[1m✔ All caches cleared successfully!\033[0m\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
