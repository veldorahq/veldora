<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class EnvEncryptCommand extends Command
{
    protected static ?string $defaultName = 'env:encrypt';

    protected function configure(): void
    {
        $this
            ->setName('env:encrypt')
            ->setDescription('Encrypt an environment file');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        $envFile = $app->basePath('.env');
        $encryptedFile = $app->basePath('.env.encrypted');

        if (!file_exists($envFile)) {
            echo "\033[31mError:\033[0m .env file not found.\n";
            return;
        }

        $rawKey = env('APP_KEY');
        if (!$rawKey) {
            echo "\033[31mError:\033[0m APP_KEY is not set in .env.\n";
            return;
        }

        $key = str_starts_with($rawKey, 'base64:') ? base64_decode(substr($rawKey, 7)) : $rawKey;
        $plain = file_get_contents($envFile);
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($plain, 'AES-256-CBC', $key, 0, $iv);

        file_put_contents($encryptedFile, base64_encode($iv . $encrypted));

        echo "\n\033[32m✔ Environment successfully encrypted to:\033[0m \033[36m.env.encrypted\033[0m\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
