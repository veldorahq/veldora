<?php

declare(strict_types=1);

namespace Veldora\Framework\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Veldora\Framework\Foundation\Application;

class MakeRequestCommand extends Command
{
    protected static ?string $defaultName = 'make:request';

    protected function configure(): void
    {
        $this
            ->setName('make:request')
            ->setDescription('Create a new Form Request (Validation) class')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the Request class');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = (string) $input->getArgument('name');
        if (!str_ends_with($name, 'Request')) {
            $name .= 'Request';
        }

        $app  = Application::getInstance();
        $file = $app->basePath("app/Http/Requests/{$name}.php");

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Veldora\Framework\Http\FormRequest;

class {$name} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<string>|string>
     */
    public function rules(): array
    {
        return [
            // 'title' => 'required|string|max:255',
            // 'email' => 'required|email',
        ];
    }
}
PHP;

        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($file)) {
            $output->writeln("<error>Request already exists:</error> app/Http/Requests/{$name}.php");
            return Command::FAILURE;
        }

        file_put_contents($file, $content);
        $output->writeln("<info>Created Request:</info> app/Http/Requests/{$name}.php");

        return Command::SUCCESS;
    }
}
