<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class DoctorCommand extends Command
{
    protected static ?string $defaultName = 'doctor';

    protected function configure(): void
    {
        $this
            ->setName('doctor')
            ->setDescription('Run a system diagnostic check on your Veldora environment');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();

        echo "\n\033[35m\033[1m  ▲ Veldora Doctor — System Diagnostics\033[0m\n\n";

        $checks = [];

        // 1. PHP Version
        $phpVer = PHP_VERSION;
        $phpPass = version_compare($phpVer, '8.2.0', '>=');
        $checks[] = [
            'name' => 'PHP Version (>= 8.2.0 required)',
            'status' => $phpPass,
            'details' => "Running PHP {$phpVer}",
        ];

        // 2. Extensions
        $requiredExtensions = ['pdo', 'mbstring', 'openssl', 'tokenizer', 'json', 'ctype'];
        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            $checks[] = [
                'name' => "PHP Extension [{$ext}]",
                'status' => $loaded,
                'details' => $loaded ? 'Installed & Loaded' : 'Missing! Please enable in php.ini',
            ];
        }

        // 3. Database driver
        $dbDriver = env('DB_CONNECTION', 'sqlite');
        $driverExt = ($dbDriver === 'sqlite') ? 'pdo_sqlite' : (($dbDriver === 'mysql') ? 'pdo_mysql' : 'pdo_pgsql');
        $driverLoaded = extension_loaded($driverExt);
        $checks[] = [
            'name' => "Database Driver [{$dbDriver}] ({$driverExt})",
            'status' => $driverLoaded,
            'details' => $driverLoaded ? 'Driver available' : "Driver extension {$driverExt} not loaded in PHP",
        ];

        // 4. .env File
        $envExists = file_exists($app->basePath('.env'));
        $checks[] = [
            'name' => 'Environment File (.env)',
            'status' => $envExists,
            'details' => $envExists ? 'Found .env file' : 'Missing .env (copy from .env.example)',
        ];

        // 5. APP_KEY
        $appKey = env('APP_KEY');
        $hasKey = !empty($appKey) && strlen((string)$appKey) >= 16;
        $checks[] = [
            'name' => 'Application Encryption Key (APP_KEY)',
            'status' => $hasKey,
            'details' => $hasKey ? 'Configured' : 'Missing or weak (run: php veldora key:generate)',
        ];

        // 6. Storage writable directories
        $storagePaths = [
            'storage/framework/views',
            'storage/framework/cache',
            'storage/logs',
        ];
        foreach ($storagePaths as $sPath) {
            $fullPath = $app->basePath($sPath);
            if (!is_dir($fullPath)) {
                @mkdir($fullPath, 0777, true);
            }
            $writable = is_writable($fullPath);
            $checks[] = [
                'name' => "Directory Writable [{$sPath}]",
                'status' => $writable,
                'details' => $writable ? 'Writable' : 'Not writable! Check permissions',
            ];
        }

        // Print results
        $allPassed = true;
        foreach ($checks as $check) {
            $mark = $check['status'] ? "\033[32m✔\033[0m" : "\033[31m✖\033[0m";
            echo "  {$mark}  \033[1m" . str_pad($check['name'], 48) . "\033[0m \033[90m" . $check['details'] . "\033[0m\n";
            if (!$check['status']) {
                $allPassed = false;
            }
        }

        echo "\n";
        if ($allPassed) {
            echo "  \033[32m\033[1m🎉 All diagnostics passed! Your Veldora environment is in perfect health.\033[0m\n\n";
        } else {
            echo "  \033[33m\033[1m⚠ Some checks failed. Please resolve the issues marked with ✖ above.\033[0m\n\n";
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
