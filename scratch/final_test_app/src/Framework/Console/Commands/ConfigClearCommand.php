<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class ConfigClearCommand extends Command
{
    protected static ?string $defaultName = 'config:clear';

    protected function configure(): void
    {
        $this
            ->setName('config:clear')
            ->setDescription('Remove the configuration cache file');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        $cacheFile = $app->basePath('storage/framework/cache/config.php');

        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }

        echo "\n\033[32m✔ Configuration cache cleared!\033[0m\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
