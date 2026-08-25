<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class MakeEventCommand extends Command
{
    protected static ?string $defaultName = 'make:event';

    protected function configure(): void
    {
        $this
            ->setName('make:event')
            ->setDescription('Create a new Event class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the Event class');
    }

    public function executeDirect(string $name): void
    {
        $app = Application::getInstance();
        $file = $app->basePath("app/Events/{$name}.php");

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\Events;

use Veldora\Framework\Events\Event;

class {$name} extends Event
{
    /**
     * Create a new event instance.
     */
    public function __construct(public mixed \$data = null)
    {
    }
}
PHP;

        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($file)) {
            echo "\033[31mError:\033[0m Event already exists: app/Events/{$name}.php\n";
            return;
        }

        file_put_contents($file, $content);
        echo "\033[32m✔ Created Event:\033[0m app/Events/{$name}.php\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) $input->getArgument('name');
        $this->executeDirect($name);
        return Command::SUCCESS;
    }
}
