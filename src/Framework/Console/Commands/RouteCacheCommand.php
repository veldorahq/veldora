<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class RouteCacheCommand extends Command
{
    protected static ?string $defaultName = 'route:cache';

    protected function configure(): void
    {
        $this
            ->setName('route:cache')
            ->setDescription('Create a route cache file for faster route registration');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        $cacheDir = $app->basePath('storage/framework/cache');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $routesFile = $app->routesPath('web.php');
        $cacheFile = $cacheDir . '/routes.php';

        if (file_exists($routesFile)) {
            copy($routesFile, $cacheFile);
        }

        echo "\n\033[32m✔ Routes cached successfully!\033[0m\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
