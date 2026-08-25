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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $registry = $this->buildRegistry();

        if (empty($registry)) {
            $output->writeln('<error>Could not load component registry. Is veldora/ui installed?</error>');
            return Command::FAILURE;
        }

        $output->writeln('');
        $output->writeln('  <fg=magenta;options=bold>Veldora UI — Available Components</>');
        $output->writeln('  ' . str_repeat('─', 52));

        $table = new Table($output);
        $table->setStyle('compact');
        $table->setHeaders(['  Component', 'Description', 'Usage']);

        $viewsDir = $this->projectRoot . '/resources/views/components';
        foreach ($registry as $name => $meta) {
            $installed = file_exists($viewsDir . '/' . $name . '.veldora.php')
                ? '<fg=green>✓ installed</>'
                : '<fg=gray>not added</>';

            $table->addRow([
                "  <comment>{$name}</comment>",
                $meta['description'],
                "<fg=gray>php veldora add {$name}</>",
            ]);
        }

        $table->render();
        $output->writeln('');

        $viewsDir = $this->projectRoot . '/resources/views/components';
        $installedCount = 0;
        foreach (array_keys($registry) as $name) {
            if (file_exists($viewsDir . '/' . $name . '.veldora.php')) {
                $installedCount++;
            }
        }

        $total = count($registry);
        $output->writeln("  <fg=gray>{$installedCount}/{$total} components installed in this project.</>");
        $output->writeln('  Run <info>php veldora add <component></info> to install.');
        $output->writeln('');

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
