<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class ConfigShowCommand extends Command
{
    protected static ?string $defaultName = 'config:show';

    protected function configure(): void
    {
        $this
            ->setName('config:show')
            ->setDescription('Display the current configuration for a specific key')
            ->addArgument('key', InputArgument::OPTIONAL, 'The config key to display (e.g. app or database)');
    }

    public function executeDirect(?string $key = null): void
    {
        $app = Application::getInstance();

        if ($key === null) {
            $configPath = $app->basePath('config');
            echo "\n\033[35m\033[1m  ▲ Available Configuration Files:\033[0m\n\n";
            if (is_dir($configPath)) {
                foreach (scandir($configPath) as $file) {
                    if (str_ends_with($file, '.php')) {
                        echo "  • \033[36m" . basename($file, '.php') . "\033[0m \033[90m(config/{$file})\033[0m\n";
                    }
                }
            }
            echo "\n  \033[90mRun \033[36mphp veldora config:show <name>\033[90m to view contents.\033[0m\n\n";
            return;
        }

        $val = config($key);
        echo "\n\033[35m\033[1m  ▲ Config [{$key}]:\033[0m\n\n";
        if (is_array($val)) {
            echo json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
        } else {
            var_dump($val);
            echo "\n";
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = $input->getArgument('key') ? (string) $input->getArgument('key') : null;
        $this->executeDirect($key);
        return Command::SUCCESS;
    }
}
