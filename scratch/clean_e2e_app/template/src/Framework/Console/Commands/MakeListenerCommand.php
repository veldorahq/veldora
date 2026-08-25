<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class MakeListenerCommand extends Command
{
    protected static ?string $defaultName = 'make:listener';

    protected function configure(): void
    {
        $this
            ->setName('make:listener')
            ->setDescription('Create a new Event Listener class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the Listener class');
    }

    public function executeDirect(string $name): void
    {
        if (!str_ends_with($name, 'Listener')) {
            $name .= 'Listener';
        }

        $app = Application::getInstance();
        $file = $app->basePath("app/Listeners/{$name}.php");

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\Listeners;

use Veldora\Framework\Events\Event;
use Veldora\Framework\Events\Listener;

class {$name} implements Listener
{
    /**
     * Handle the event.
     */
    public function handle(Event \$event): void
    {
        // React to event
    }
}
PHP;

        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($file)) {
            echo "\033[31mError:\033[0m Listener already exists: app/Listeners/{$name}.php\n";
            return;
        }

        file_put_contents($file, $content);
        echo "\033[32m✔ Created Listener:\033[0m app/Listeners/{$name}.php\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) $input->getArgument('name');
        $this->executeDirect($name);
        return Command::SUCCESS;
    }
}
