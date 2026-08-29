<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;
use Veldora\Framework\View\Compiler;

class ViewCacheCommand extends Command
{
    protected static ?string $defaultName = 'view:cache';

    protected function configure(): void
    {
        $this
            ->setName('view:cache')
            ->setDescription('Compile all Veldora views into PHP cache files');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        $viewsDir = $app->basePath('resources/views');
        $cacheDir = $app->basePath('storage/framework/views');

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $compiler = new Compiler();
        $count = 0;

        if (is_dir($viewsDir)) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($viewsDir));
            foreach ($iterator as $file) {
                if ($file->isFile() && str_ends_with($file->getFilename(), '.veldora.php')) {
                    $raw = file_get_contents($file->getPathname());
                    $compiled = $compiler->compile($raw);
                    $hash = sha1($file->getPathname());
                    file_put_contents($cacheDir . '/' . $hash . '.php', $compiled);
                    $count++;
                }
            }
        }

        echo "\n\033[32m✔ Compiled {$count} views successfully!\033[0m\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
