<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class MakeControllerCommand extends Command
{
    protected static ?string $defaultName = 'make:controller';

    protected function configure(): void
    {
        $this
            ->setName('make:controller')
            ->setDescription('Create a new Controller class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the Controller class');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) $input->getArgument('name');
        if (!str_ends_with($name, 'Controller')) {
            $name .= 'Controller';
        }

        $app = Application::getInstance();
        $controllerFile = $app->basePath("app/Controllers/{$name}.php");

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\Controllers;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Response;

class {$name}
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request \$request): Response
    {
        return view('welcome');
    }
}
PHP;

        $dir = dirname($controllerFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($controllerFile)) {
            $output->writeln("<error>Controller already exists:</error> app/Controllers/{$name}.php");
            return Command::FAILURE;
        }

        file_put_contents($controllerFile, $content);
        $output->writeln("<info>Created Controller:</info> app/Controllers/{$name}.php");

        return Command::SUCCESS;
    }
}
