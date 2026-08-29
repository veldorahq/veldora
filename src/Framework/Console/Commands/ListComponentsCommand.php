<?php

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class ListComponentsCommand extends Command
{
    protected static ?string $defaultName = 'ui:list';

    private string $projectRoot;

    public function __construct(?string $projectRoot = null)
    {
        parent::__construct();
        $this->projectRoot = $projectRoot ?? getcwd();
    }

    protected function configure(): void
    {
        $this
            ->setName('ui:list')
            ->setDescription('List all available Veldora UI components');
    }

    public function executeDirect(): void
    {
        $registry = $this->buildRegistry();

        if (empty($registry)) {
            echo "\033[31mCould not load component registry. Is veldora/ui installed?\033[0m\n";
            return;
        }

        echo "\n\033[35m\033[1m  ▲ Veldora UI — Available Components\033[0m\n";
        echo "  " . str_repeat('─', 65) . "\n";

        $viewsDir = $this->projectRoot . '/resources/views/components';
        foreach ($registry as $name => $meta) {
            $installed = file_exists($viewsDir . '/' . $name . '.veldora.php')
                ? "\033[32minstalled\033[0m"
                : "\033[90mnot added\033[0m";

            $namePad = str_pad($name, 14);
            $descPad = str_pad($meta['description'], 40);
            echo "  \033[36m{$namePad}\033[0m \033[97m{$descPad}\033[0m  {$installed}\n";
        }

        $installedCount = 0;
        foreach (array_keys($registry) as $name) {
            if (file_exists($viewsDir . '/' . $name . '.veldora.php')) {
                $installedCount++;
            }
        }

        $total = count($registry);
        echo "\n  \033[90m{$installedCount}/{$total} components installed in this project.\033[0m\n";
        echo "  Run \033[33mphp veldora add <component>\033[0m to install.\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->executeDirect();
        return Command::SUCCESS;
    }

    /**
     * @return array<string, array{description: string, usage: string, template: string}>
     */
    private function buildRegistry(): array
    {
        if (class_exists(\Veldora\UI\Registry\ComponentRegistry::class)) {
            $reg = new \Veldora\UI\Registry\ComponentRegistry();
            return $reg->all();
        }

        $paths = [
            $this->projectRoot . '/vendor/veldora/ui/src/Registry/ComponentRegistry.php',
            dirname($this->projectRoot) . '/veldora-ui/src/Registry/ComponentRegistry.php',
            __DIR__ . '/../../../../veldora-ui/src/Registry/ComponentRegistry.php',
            __DIR__ . '/../../../../../veldora-ui/src/Registry/ComponentRegistry.php',
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                if (class_exists(\Veldora\UI\Registry\ComponentRegistry::class)) {
                    $reg = new \Veldora\UI\Registry\ComponentRegistry();
                    return $reg->all();
                }
            }
        }

        return [];
    }
}
