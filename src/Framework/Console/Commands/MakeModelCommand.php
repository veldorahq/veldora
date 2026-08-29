<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class MakeModelCommand extends Command
{
    protected static ?string $defaultName = 'make:model';

    protected function configure(): void
    {
        $this
            ->setName('make:model')
            ->setDescription('Create a new Database Model class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the Model class')
            ->addOption('migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for the model');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) $input->getArgument('name');
        
        $app = Application::getInstance();
        $modelFile = $app->basePath("app/Models/{$name}.php");

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\Models;

use Veldora\Framework\Database\Model;

class {$name} extends Model
{
    // protected ?string \$table = null;
}
PHP;

        $dir = dirname($modelFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($modelFile)) {
            $output->writeln("<error>Model already exists:</error> app/Models/{$name}.php");
            return Command::FAILURE;
        }

        file_put_contents($modelFile, $content);
        $output->writeln("<info>Created Model:</info> app/Models/{$name}.php");

        // Optional migration creation
        if ($input->getOption('migration')) {
            $pluralName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
            if (str_ends_with($pluralName, 'y')) {
                $pluralName = substr($pluralName, 0, -1) . 'ies';
            } else {
                $pluralName .= 's';
            }

            $migrationName = "create_{$pluralName}_table";
            
            // Execute the make:migration command internally
            $command = $this->getApplication()->find('make:migration');
            $command->run(
                new \Symfony\Component\Console\Input\ArrayInput(['name' => $migrationName]),
                $output
            );
        }

        return Command::SUCCESS;
    }
}
