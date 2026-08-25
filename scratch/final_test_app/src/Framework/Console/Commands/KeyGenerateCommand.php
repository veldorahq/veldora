<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class KeyGenerateCommand extends Command
{
    protected static ?string $defaultName = 'key:generate';

    protected function configure(): void
    {
        $this
            ->setName('key:generate')
            ->setDescription('Set the application key');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        $envFile = $app->basePath('.env');

        if (!file_exists($envFile)) {
            $example = $app->basePath('.env.example');
            if (file_exists($example)) {
                copy($example, $envFile);
            } else {
                file_put_contents($envFile, "APP_KEY=\n");
            }
        }

        $key = 'base64:' . base64_encode(random_bytes(32));
        $content = file_get_contents($envFile);

        if (preg_match('/^APP_KEY=.*$/m', $content)) {
            $content = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $content);
        } else {
            $content .= "\nAPP_KEY=" . $key . "\n";
        }

        file_put_contents($envFile, $content);

        echo "\n\033[32m✔ Application key set successfully:\033[0m \033[36m{$key}\033[0m\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
