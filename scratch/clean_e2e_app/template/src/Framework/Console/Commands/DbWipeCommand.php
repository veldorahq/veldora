<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Database\Connection;
use Veldora\Framework\Foundation\Application;

class DbWipeCommand extends Command
{
    protected static ?string $defaultName = 'db:wipe';

    protected function configure(): void
    {
        $this
            ->setName('db:wipe')
            ->setDescription('Drop all tables, views, and types in the database');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        $db = $app->get(Connection::class);
        $pdo = $db->getPdo();
        $driver = $db->getDriverName();

        echo "\n\033[35m\033[1m  ▲ Wiping Database...\033[0m\n\n";

        if ($driver === 'sqlite') {
            $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($tables as $tbl) {
                $pdo->exec("DROP TABLE IF EXISTS \"{$tbl}\"");
                echo "  \033[31mDropped table:\033[0m {$tbl}\n";
            }
        } elseif ($driver === 'mysql') {
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
            $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
            foreach ($tables as $tbl) {
                $pdo->exec("DROP TABLE IF EXISTS `{$tbl}`");
                echo "  \033[31mDropped table:\033[0m {$tbl}\n";
            }
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
        }

        echo "\n  \033[32m\033[1m✔ Database wiped successfully!\033[0m\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
