<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Database\Connection;
use Veldora\Framework\Foundation\Application;

class DbShowCommand extends Command
{
    protected static ?string $defaultName = 'db:show';

    protected function configure(): void
    {
        $this
            ->setName('db:show')
            ->setDescription('Display information about the database and its tables');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        $db = $app->get(Connection::class);
        $pdo = $db->getPdo();
        $driver = $db->getDriverName();

        echo "\n\033[35m\033[1m  ▲ Veldora Database Inspector\033[0m\n\n";
        echo "  \033[90mDriver:\033[0m     \033[1;36m{$driver}\033[0m\n";

        if ($driver === 'sqlite') {
            $dbFile = env('DB_DATABASE', $app->basePath('database/veldora.sqlite'));
            $size = file_exists($dbFile) ? round(filesize($dbFile) / 1024, 2) . ' KB' : '0 KB';
            echo "  \033[90mFile:\033[0m       {$dbFile}\n";
            echo "  \033[90mSize:\033[0m       {$size}\n\n";

            $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(\PDO::FETCH_COLUMN);
        } else {
            echo "  \033[90mHost:\033[0m       " . env('DB_HOST', '127.0.0.1') . ":" . env('DB_PORT', '3306') . "\n";
            echo "  \033[90mDatabase:\033[0m   " . env('DB_DATABASE', 'veldora') . "\n\n";

            $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
        }

        echo "  \033[1mTables in Database:\033[0m\n";
        echo "  " . str_repeat('─', 50) . "\n";

        if (empty($tables)) {
            echo "  \033[90mNo tables found. Run: php veldora migrate\033[0m\n\n";
            return;
        }

        foreach ($tables as $t) {
            $cnt = $pdo->query("SELECT COUNT(*) FROM \"{$t}\"")->fetchColumn();
            printf("  • \033[32m%-30s\033[0m \033[90m(%s rows)\033[0m\n", $t, $cnt);
        }

        echo "\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
