<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class ViewClearCommand extends Command
{
    protected static ?string $defaultName = 'view:clear';

    protected function configure(): void
    {
        $this
            ->setName('view:clear')
            ->setDescription('Clear all compiled view files');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        $cacheDir = $app->basePath('storage/framework/views');
        $count = 0;

        if (is_dir($cacheDir)) {
            foreach (scandir($cacheDir) as $file) {
                if (str_ends_with($file, '.php')) {
                    @unlink($cacheDir . '/' . $file);
                    $count++;
                }
            }
        }

        echo "\n\033[32m✔ Cleared {$count} compiled view files!\033[0m\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
