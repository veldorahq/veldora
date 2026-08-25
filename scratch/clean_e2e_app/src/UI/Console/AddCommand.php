<?php

namespace Veldora\UI\Console;

use Veldora\UI\Registry\ComponentRegistry;

class AddCommand
{
    private ComponentRegistry $registry;
    private string $projectRoot;

    public function __construct(string $projectRoot)
    {
        $this->registry    = new ComponentRegistry();
        $this->projectRoot = rtrim($projectRoot, '/\\');
    }

    /**
     * @param array<int, string> $args
     */
    public function handle(array $args): void
    {
        if (empty($args)) {
            $this->printUsage();
            return;
        }

        // veldora ui:list
        if ($args[0] === '--list' || $args[0] === 'list') {
            $this->listComponents();
            return;
        }

        foreach ($args as $name) {
            $this->addComponent(strtolower(trim($name)));
        }
    }

    private function addComponent(string $name): void
    {
        if (!$this->registry->has($name)) {
            $this->error("Component [{$name}] not found. Run `php veldora ui:list` to see available components.");
            return;
        }

        $component = $this->registry->get($name);
        $dest      = $this->projectRoot . '/resources/views/components/' . $name . '.veldora.php';

        if (file_exists($dest)) {
            $this->warn("Component [{$name}] already exists at: resources/views/components/{$name}.veldora.php");
            return;
        }

        // Ensure directory exists
        $dir = dirname($dest);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($dest, $component['template']);
        $this->success("Added component [{$name}] → resources/views/components/{$name}.veldora.php");

        // Show usage hint
        if (!empty($component['usage'])) {
            $this->line("  Usage:  " . $component['usage']);
        }
    }

    private function listComponents(): void
    {
        $this->line('');
        $this->line('  <fg=purple>Veldora UI — Available Components</fg>');
        $this->line('  ' . str_repeat('─', 48));

        $components = $this->registry->all();
        foreach ($components as $name => $meta) {
            $pad = str_pad($name, 16);
            $this->line("  <fg=green>{$pad}</fg>  {$meta['description']}");
        }

        $this->line('');
        $this->line('  Install: php veldora add <component>');
        $this->line('');
    }

    private function printUsage(): void
    {
        $this->line('');
        $this->line('  Usage:');
        $this->line('    php veldora add <component>          Add a single component');
        $this->line('    php veldora add <c1> <c2> ...        Add multiple components');
        $this->line('    php veldora ui:list                  List all available components');
        $this->line('');
        $this->line('  Examples:');
        $this->line('    php veldora add button');
        $this->line('    php veldora add button input alert card');
        $this->line('');
    }

    // ── Output helpers ────────────────────────────────────────────────────────

    private function success(string $msg): void
    {
        echo "\033[32m  ✓ {$msg}\033[0m" . PHP_EOL;
    }

    private function error(string $msg): void
    {
        echo "\033[31m  ✗ {$msg}\033[0m" . PHP_EOL;
    }

    private function warn(string $msg): void
    {
        echo "\033[33m  ! {$msg}\033[0m" . PHP_EOL;
    }

    private function line(string $msg): void
    {
        // Strip basic fg tags for CLI
        $msg = preg_replace('/<\/?fg=[a-z]+>/', '', $msg);
        echo $msg . PHP_EOL;
    }
}
