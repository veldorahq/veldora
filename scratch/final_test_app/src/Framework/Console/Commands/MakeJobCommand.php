<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class MakeJobCommand extends Command
{
    protected static ?string $defaultName = 'make:job';

    protected function configure(): void
    {
        $this
            ->setName('make:job')
            ->setDescription('Create a new Queue Job class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the Job class');
    }

    public function executeDirect(string $name): void
    {
        if (!str_ends_with($name, 'Job')) {
            $name .= 'Job';
        }

        $app = Application::getInstance();
        $file = $app->basePath("app/Jobs/{$name}.php");

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\Jobs;

use Veldora\Framework\Queue\Job;

class {$name} extends Job
{
    /**
     * Create a new job instance.
     */
    public function __construct(public array \$payload = [])
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Process background task
    }
}
PHP;

        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($file)) {
            echo "\033[31mError:\033[0m Job already exists: app/Jobs/{$name}.php\n";
            return;
        }

        file_put_contents($file, $content);
        echo "\033[32m✔ Created Job:\033[0m app/Jobs/{$name}.php\n";
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) $input->getArgument('name');
        $this->executeDirect($name);
        return Command::SUCCESS;
    }
}
