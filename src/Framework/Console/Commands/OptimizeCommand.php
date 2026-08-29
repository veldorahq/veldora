<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OptimizeCommand extends Command
{
    protected static ?string $defaultName = 'optimize';

    protected function configure(): void
    {
        $this
            ->setName('optimize')
            ->setDescription('Cache the framework bootstrap files for maximum performance');
    }

    public function executeDirect(): void
    {
        echo "\n\033[35m\033[1m  ▲ Optimizing Veldora Framework...\033[0m\n\n";

        (new ConfigCacheCommand())->executeDirect();
        (new RouteCacheCommand())->executeDirect();
        (new ViewCacheCommand())->executeDirect();

        echo "  \033[32m\033[1m🎉 Framework optimized for production deployment!\033[0m\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
