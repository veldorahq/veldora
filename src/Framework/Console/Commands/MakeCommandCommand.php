<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class MakeCommandCommand extends Command
{
    protected static ?string $defaultName = 'make:command';

    protected function configure(): void
    {
        $this
            ->setName('make:command')
            ->setDescription('Create a new Veldora CLI Command class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the Command class');
    }

    public function executeDirect(string $name): void
    {
        if (!str_ends_with($name, 'Command')) {
            $name .= 'Command';
        }

        $app = Application::getInstance();
        $file = $app->basePath("app/Console/Commands/{$name}.php");

        $commandSignature = strtolower(preg_replace('/(?<!^)[A-Z]/', ':$0', str_replace('Command', '', $name)));

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class {$name} extends Command
{
    protected static ?string \$defaultName = '{$commandSignature}';

    protected function configure(): void
    {
        \$this
            ->setName('{$commandSignature}')
            ->setDescription('Custom console command description');
    }

    protected function execute(InputInterface \$input, OutputInterface \$output): int
    {
        \$output->writeln('<info>Command executed successfully!</info>');
        return Command::SUCCESS;
    }
}
PHP;

        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($file)) {
            echo "\033[31mError:\033[0m Command already exists: app/Console/Commands/{$name}.php\n";
            return;
        }

        file_put_contents($file, $content);
        echo "\033[32m✔ Created Command:\033[0m app/Console/Commands/{$name}.php\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) $input->getArgument('name');
        $this->executeDirect($name);
        return Command::SUCCESS;
    }
}
