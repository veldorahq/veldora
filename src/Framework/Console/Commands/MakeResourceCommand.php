<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class MakeResourceCommand extends Command
{
    protected static ?string $defaultName = 'make:resource';

    protected function configure(): void
    {
        $this
            ->setName('make:resource')
            ->setDescription('Create a new API JSON Resource class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the Resource class');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) $input->getArgument('name');
        if (!str_ends_with($name, 'Resource')) {
            $name .= 'Resource';
        }

        $app  = Application::getInstance();
        $file = $app->basePath("app/Http/Resources/{$name}.php");

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Veldora\Framework\Http\Request;
use Veldora\Framework\Http\Resources\JsonResource;

class {$name} extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(?Request \$request = null): array
    {
        return [
            'id' => \$this->id,
            'created_at' => \$this->created_at,
            'updated_at' => \$this->updated_at,
        ];
    }
}
PHP;

        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($file)) {
            $output->writeln("<error>Resource already exists:</error> app/Http/Resources/{$name}.php");
            return Command::FAILURE;
        }

        file_put_contents($file, $content);
        $output->writeln("<info>Created Resource:</info> app/Http/Resources/{$name}.php");

        return Command::SUCCESS;
    }
}
