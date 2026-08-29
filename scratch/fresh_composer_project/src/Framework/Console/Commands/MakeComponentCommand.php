<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class MakeComponentCommand extends Command
{
    protected static ?string $defaultName = 'make:component';

    protected function configure(): void
    {
        $this
            ->setName('make:component')
            ->setDescription('Create a new UI component template')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the component (e.g. hero or card-header)');
    }

    public function executeDirect(string $name): void
    {
        $app = Application::getInstance();
        $normalized = strtolower(str_replace(['.', '_'], ['/', '-'], $name));
        $file = $app->basePath("resources/views/components/{$normalized}.veldora.php");

        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($file)) {
            echo "\033[31mError:\033[0m Component already exists: resources/views/components/{$normalized}.veldora.php\n";
            return;
        }

        $tag = basename($normalized);
        $content = <<<HTML
<div {{ \$attributes->merge(['class' => 'veldora-{$tag}']) }}>
    {{ \$slot }}
</div>
HTML;

        file_put_contents($file, $content);
        echo "\033[32m✔ Created UI Component:\033[0m resources/views/components/{$normalized}.veldora.php\n";
        echo "  \033[90mUsage in views:\033[0m <x-{$tag}>...</x-{$tag}>\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) $input->getArgument('name');
        $this->executeDirect($name);
        return Command::SUCCESS;
    }
}
