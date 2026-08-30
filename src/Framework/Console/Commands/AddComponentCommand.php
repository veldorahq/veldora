<?php

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddComponentCommand extends Command
{
    protected static ?string $defaultName = 'add';

    private string $projectRoot;

    public function __construct(?string $projectRoot = null)
    {
        parent::__construct();
        $this->projectRoot = $projectRoot ?? getcwd();
    }

    protected function configure(): void
    {
        $this
            ->setName('add')
            ->setDescription('Add one or more Veldora UI components to your project')
            ->setHelp("Install UI components into resources/views/components/\n\n  php veldora add button\n  php veldora add button input alert card\n")
            ->addArgument('components', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Component name(s) to add');
    }

    public function executeDirect(array $components = []): void
    {
        if (empty($components)) {
            echo "  Usage: php veldora add <component-name>\n";
            return;
        }

        $registry    = $this->buildRegistry();
        $available   = array_keys($registry);
        $installed   = 0;
        $skipped     = 0;

        foreach ($components as $name) {
            $name = strtolower(trim($name));

            if (!isset($registry[$name])) {
                echo "  \033[31m✗\033[0m Component [{$name}] not found. Available: " . implode(', ', $available) . "\n";
                continue;
            }

            $dest = $this->projectRoot . '/resources/views/components/' . $name . '.veldora.php';

            if (file_exists($dest)) {
                echo "  \033[33m!\033[0m Component [{$name}] already exists — skipped.\n";
                $skipped++;
                continue;
            }

            $dir = dirname($dest);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($dest, $registry[$name]['template']);

            echo "  \033[32m✓\033[0m Added [{$name}] → resources/views/components/{$name}.veldora.php\n";
            if (!empty($registry[$name]['usage'])) {
                echo "      \033[90mUsage: {$registry[$name]['usage']}\033[0m\n";
            }
            $installed++;
        }

        echo "\n  \033[32mDone.\033[0m Installed: {$installed}, Skipped: {$skipped}\n\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $components = $input->getArgument('components');
        ob_start();
        $this->executeDirect((array) $components);
        $content = (string) ob_get_clean();
        if ($content !== '') {
            $output->write($content);
        }
        return Command::SUCCESS;
    }

    private function printUsage(OutputInterface $output): void
    {
        $output->writeln('');
        $output->writeln('  <fg=magenta>Usage:</>');
        $output->writeln('    php veldora add <component>          Add a single component');
        $output->writeln('    php veldora add <c1> <c2> ...        Add multiple components');
        $output->writeln('    php veldora ui:list                  List all components');
        $output->writeln('');
        $output->writeln('  <fg=magenta>Examples:</>');
        $output->writeln('    php veldora add button');
        $output->writeln('    php veldora add button input alert card modal');
        $output->writeln('');
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

        // Resolve veldora-ui package path
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
