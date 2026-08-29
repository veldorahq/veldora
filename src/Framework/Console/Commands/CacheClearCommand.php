<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class CacheClearCommand extends Command
{
    protected static ?string $defaultName = 'cache:clear';

    protected function configure(): void
    {
        $this
            ->setName('cache:clear')
            ->setDescription('Flush the application cache');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        $cacheDir = $app->basePath('storage/framework/cache');
        $count = 0;

        if (is_dir($cacheDir)) {
            foreach (scandir($cacheDir) as $file) {
                if ($file !== '.' && $file !== '..' && $file !== '.gitkeep') {
                    @unlink($cacheDir . '/' . $file);
                    $count++;
                }
            }
        }

        echo "\n\033[32m✔ Application cache cleared ({$count} items removed)!\033[0m\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
