<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class MakeMailCommand extends Command
{
    protected static ?string $defaultName = 'make:mail';

    protected function configure(): void
    {
        $this
            ->setName('make:mail')
            ->setDescription('Create a new Mailable class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the Mailable class');
    }

    public function executeDirect(string $name): void
    {
        $app = Application::getInstance();
        $file = $app->basePath("app/Mail/{$name}.php");

        $viewName = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\Mail;

use Veldora\Framework\Mail\Mailable;

class {$name} extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct(public array \$data = [])
    {
    }

    /**
     * Build the message.
     */
    public function build(): static
    {
        return \$this
            ->subject('{$name}')
            ->view('emails.{$viewName}', \$this->data);
    }
}
PHP;

        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($file)) {
            echo "\033[31mError:\033[0m Mailable already exists: app/Mail/{$name}.php\n";
            return;
        }

        file_put_contents($file, $content);
        echo "\033[32m✔ Created Mailable:\033[0m app/Mail/{$name}.php\n";

        // Also create sample view template
        $viewFile = $app->basePath("resources/views/emails/{$viewName}.veldora.php");
        $viewDir = dirname($viewFile);
        if (!is_dir($viewDir)) {
            mkdir($viewDir, 0755, true);
        }
        if (!file_exists($viewFile)) {
            file_put_contents($viewFile, "<h1>Hello!</h1>\n<p>This is your email content from {$name}.</p>\n");
            echo "\033[32m✔ Created Email View:\033[0m resources/views/emails/{$viewName}.veldora.php\n";
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) $input->getArgument('name');
        $this->executeDirect($name);
        return Command::SUCCESS;
    }
}
