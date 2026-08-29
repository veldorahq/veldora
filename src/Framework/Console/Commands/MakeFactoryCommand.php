<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class MakeFactoryCommand extends Command
{
    protected static ?string $defaultName = 'make:factory';

    protected function configure(): void
    {
        $this
            ->setName('make:factory')
            ->setDescription('Create a new Model Factory class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the Factory class')
            ->addOption('model', 'm', InputOption::VALUE_OPTIONAL, 'The corresponding Model class');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) $input->getArgument('name');
        if (!str_ends_with($name, 'Factory')) {
            $name .= 'Factory';
        }

        $model = (string) ($input->getOption('model') ?: substr($name, 0, -7));

        $app  = Application::getInstance();
        $file = $app->basePath("database/factories/{$name}.php");

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\\{$model};
use Veldora\Framework\Database\Factories\Factory;

/**
 * @extends Factory<{$model}>
 */
class {$name} extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<{$model}>
     */
    protected string \$model = {$model}::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'name' => 'Sample Name',
            // 'email' => 'user' . rand(100, 999) . '@example.com',
        ];
    }
}
PHP;

        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($file)) {
            $output->writeln("<error>Factory already exists:</error> database/factories/{$name}.php");
            return Command::FAILURE;
        }

        file_put_contents($file, $content);
        $output->writeln("<info>Created Factory:</info> database/factories/{$name}.php");

        return Command::SUCCESS;
    }
}
