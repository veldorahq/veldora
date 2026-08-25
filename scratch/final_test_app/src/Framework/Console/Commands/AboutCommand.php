<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class AboutCommand extends Command
{
    protected static ?string $defaultName = 'about';

    protected function configure(): void
    {
        $this
            ->setName('about')
            ->setDescription('Display basic information about your Veldora application');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        $phpVersion = PHP_VERSION;
        $env = env('APP_ENV', 'local');
        $debug = env('APP_DEBUG', true) ? 'true' : 'false';
        $url = env('APP_URL', 'http://localhost:8000');
        $dbDriver = env('DB_CONNECTION', 'sqlite');
        $dbName = env('DB_DATABASE', 'database/veldora.sqlite');

        echo "\n\033[35m\033[1m  ▲ Veldora Framework\033[0m \033[90mv0.4.0\033[0m\n";
        echo "  \033[90mThe modern PHP framework you actually own.\033[0m\n\n";

        echo "  \033[1;36mEnvironment\033[0m\n";
        echo "  \033[90mApplication Name\033[0m   " . env('APP_NAME', 'Veldora') . "\n";
        echo "  \033[90mEnvironment\033[0m        \033[32m" . $env . "\033[0m\n";
        echo "  \033[90mDebug Mode\033[0m         \033[" . ($debug === 'true' ? '33' : '32') . "m" . $debug . "\033[0m\n";
        echo "  \033[90mURL\033[0m                " . $url . "\n";
        echo "  \033[90mPHP Version\033[0m        " . $phpVersion . "\n";
        echo "  \033[90mTimezone\033[0m           " . date_default_timezone_get() . "\n\n";

        echo "  \033[1;36mDatabase\033[0m\n";
        echo "  \033[90mDriver\033[0m             " . $dbDriver . "\n";
        echo "  \033[90mDatabase Name\033[0m      " . $dbName . "\n\n";

        echo "  \033[1;36mCache & Storage\033[0m\n";
        $routesCache = file_exists($app->basePath('storage/framework/cache/routes.php')) ? "\033[32mCached\033[0m" : "\033[90mNot cached\033[0m";
        $configCache = file_exists($app->basePath('storage/framework/cache/config.php')) ? "\033[32mCached\033[0m" : "\033[90mNot cached\033[0m";
        echo "  \033[90mConfig Cache\033[0m       " . $configCache . "\n";
        echo "  \033[90mRoute Cache\033[0m        " . $routesCache . "\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
