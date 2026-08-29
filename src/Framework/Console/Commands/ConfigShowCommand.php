<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Config\Env;
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
            echo "\n\033[35m\033[1m  ▲ Available Configuration Sources:\033[0m\n\n";
            echo "  • \033[32m.env\033[0m \033[90m(Environment Variables: " . count(Env::all()) . " loaded)\033[0m\n";

            if (is_dir($configPath)) {
                foreach (scandir($configPath) as $file) {
                    if (str_ends_with($file, '.php')) {
                        echo "  • \033[36m" . basename($file, '.php') . "\033[0m \033[90m(config/{$file})\033[0m\n";
                    }
                }
            }
            echo "\n  \033[90mRun \033[36mphp veldora config:show .env\033[90m or \033[36mphp veldora config:show <name>\033[90m to view contents.\033[0m\n\n";
            return;
        }

        $keyLower = strtolower(trim($key));

        // 1. Show all .env variables
        if ($keyLower === '.env' || $keyLower === 'env') {
            $envData = Env::all();
            echo "\n\033[35m\033[1m  ▲ Environment Variables (.env):\033[0m\n";
            echo "  " . str_repeat('─', 55) . "\n";

            if (empty($envData)) {
                echo "  \033[90mNo .env variables loaded. Create a .env file in project root.\033[0m\n\n";
                return;
            }

            foreach ($envData as $k => $v) {
                $kPad = str_pad((string) $k, 24);
                if (is_bool($v)) {
                    $vStr = $v ? "\033[32mtrue\033[0m" : "\033[31mfalse\033[0m";
                } elseif ($v === null) {
                    $vStr = "\033[90mnull\033[0m";
                } else {
                    $vStr = "\033[37m" . (string) $v . "\033[0m";
                }
                echo "  \033[36m{$kPad}\033[0m = {$vStr}\n";
            }
            echo "\n";
            return;
        }

        // 2. Check if key is a specific .env variable (e.g. APP_NAME, DB_PORT)
        if (Env::has($key)) {
            $val = Env::get($key);
            echo "\n\033[35m\033[1m  ▲ Environment Variable [{$key}]:\033[0m\n";
            echo "  " . str_repeat('─', 45) . "\n";
            if (is_bool($val)) {
                echo "  \033[32m" . ($val ? 'true' : 'false') . "\033[0m (boolean)\n\n";
            } elseif ($val === null) {
                echo "  \033[90mnull\033[0m\n\n";
            } else {
                echo "  \033[37m" . (string) $val . "\033[0m\n\n";
            }
            return;
        }

        // 3. Look up from Config repository
        $val = config($key);
        if ($val !== null) {
            echo "\n\033[35m\033[1m  ▲ Config [{$key}]:\033[0m\n";
            echo "  " . str_repeat('─', 45) . "\n";
            if (is_array($val)) {
                echo json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n\n";
            } else {
                var_dump($val);
                echo "\n";
            }
            return;
        }

        // 4. If key wasn't found in config or env
        echo "\n\033[33m  ! Config or ENV key [{$key}] not found.\033[0m\n";
        echo "  Run \033[36mphp veldora config:show\033[0m to list available files and .env variables.\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = $input->getArgument('key') ? (string) $input->getArgument('key') : null;
        $this->executeDirect($key);
        return Command::SUCCESS;
    }
}
