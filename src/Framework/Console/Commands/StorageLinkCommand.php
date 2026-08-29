<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class StorageLinkCommand extends Command
{
    protected static ?string $defaultName = 'storage:link';

    protected function configure(): void
    {
        $this
            ->setName('storage:link')
            ->setDescription('Create the symbolic link from public/storage to storage/app/public');
    }

    public function executeDirect(): void
    {
        $app = Application::getInstance();
        $target = $app->basePath('storage/app/public');
        $link = $app->basePath('public/storage');

        if (!is_dir($target)) {
            mkdir($target, 0777, true);
        }

        if (file_exists($link) || is_link($link)) {
            echo "\n\033[33mThe [public/storage] link already exists.\033[0m\n\n";
            return;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $mode = is_dir($target) ? 'J' : 'H';
            exec("mklink /{$mode} \"{$link}\" \"{$target}\"", $out, $ret);
            if ($ret !== 0) {
                // Fallback copy or symlink
                @symlink($target, $link);
            }
        } else {
            symlink($target, $link);
        }

        echo "\n\033[32m✔ The [public/storage] link has been connected to [storage/app/public].\033[0m\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }
}
