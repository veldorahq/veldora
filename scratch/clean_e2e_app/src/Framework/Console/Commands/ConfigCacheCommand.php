<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class ConfigCacheCommand extends Command
{
    protected static ?string $defaultName = 'config:cache';

    protected function configure(): void
    {
        $this
            ->setName('config:cache')
            ->setDescription('Create a cache file for faster configuration loading');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        $cacheDir = $app->basePath('storage/framework/cache');
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $configPath = $app->basePath('config');
        $configs = [];

        if (is_dir($configPath)) {
            foreach (scandir($configPath) as $file) {
                if (str_ends_with($file, '.php')) {
                    $key = basename($file, '.php');
                    $configs[$key] = require $configPath . '/' . $file;
                }
            }
        }

        $cacheFile = $cacheDir . '/config.php';
        file_put_contents($cacheFile, '<?php return ' . var_export($configs, true) . ';' . PHP_EOL);

        echo "\n\033[32m✔ Configuration cached successfully!\033[0m\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
