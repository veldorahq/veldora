<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class EnvDecryptCommand extends Command
{
    protected static ?string $defaultName = 'env:decrypt';

    protected function configure(): void
    {
        $this
            ->setName('env:decrypt')
            ->setDescription('Decrypt an encrypted environment file');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        $encryptedFile = $app->basePath('.env.encrypted');
        $envFile = $app->basePath('.env');

        if (!file_exists($encryptedFile)) {
            echo "\033[31mError:\033[0m .env.encrypted file not found.\n";
            return;
        }

        $rawKey = env('APP_KEY');
        if (!$rawKey) {
            echo "\033[31mError:\033[0m APP_KEY is not set in environment.\n";
            return;
        }

        $key = str_starts_with($rawKey, 'base64:') ? base64_decode(substr($rawKey, 7)) : $rawKey;
        $payload = base64_decode(file_get_contents($encryptedFile));
        $iv = substr($payload, 0, 16);
        $encrypted = substr($payload, 16);

        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);

        if ($decrypted === false) {
            echo "\033[31mError:\033[0m Decryption failed. Invalid key.\n";
            return;
        }

        file_put_contents($envFile, $decrypted);

        echo "\n\033[32m✔ Environment successfully decrypted to:\033[0m \033[36m.env\033[0m\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
