<?php

declare(strict_types=1);

/**
 * Veldora Console Polyfill
 *
 * Provides a complete zero-dependency compatibility layer for Symfony Console
 * components so that all Veldora CLI commands can execute without requiring
 * external composer dependencies installed.
 */

namespace Symfony\Component\Console\Formatter {
    if (!interface_exists(OutputFormatterInterface::class, false)) {
        interface OutputFormatterInterface
        {
            public function format(?string $message): ?string;
            public function setDecorated(bool $decorated): void;
            public function isDecorated(): bool;
        }
    }

    if (!class_exists(OutputFormatter::class, false)) {
        class OutputFormatter implements OutputFormatterInterface
        {
            protected bool $decorated = true;

            public function __construct(bool $decorated = true)
            {
                $this->decorated = $decorated;
            }

            public function format(?string $message): ?string
            {
                if ($message === null) {
                    return null;
                }
                return preg_replace('/<[^>]*>/', '', $message) ?? $message;
            }

            public function setDecorated(bool $decorated): void
            {
                $this->decorated = $decorated;
            }

            public function isDecorated(): bool
            {
                return $this->decorated;
            }
        }
    }
}

namespace Symfony\Component\Console\Input {
    if (!class_exists(InputDefinition::class, false)) {
        class InputDefinition
        {
            public function __construct(protected array $definition = []) {}
            public function setDefinition(array $definition): void { $this->definition = $definition; }
            public function getDefinition(): array { return $this->definition; }
            public function getArguments(): array { return []; }
            public function getOptions(): array { return []; }
            public function hasArgument(string|int $name): bool { return false; }
            public function hasOption(string $name): bool { return false; }
        }
    }

    if (!interface_exists(InputInterface::class, false)) {
        interface InputInterface
        {
            public function getFirstArgument(): ?string;
            public function hasParameterOption(string|array $values, bool $onlyParams = false): bool;
            public function getParameterOption(string|array $values, string|bool|int|float|array|null $default = false, bool $onlyParams = false): mixed;
            public function bind(InputDefinition $definition): void;
            public function validate(): void;
            public function getArguments(): array;
            public function getArgument(string $name): mixed;
            public function setArgument(string $name, mixed $value): void;
            public function hasArgument(string $name): bool;
            public function getOptions(): array;
            public function getOption(string $name): mixed;
            public function setOption(string $name, mixed $value): void;
            public function hasOption(string $name): bool;
            public function isInteractive(): bool;
            public function setInteractive(bool $interactive): void;
            public function __toString(): string;
        }
    }

    if (!class_exists(InputOption::class, false)) {
        class InputOption
        {
            public const VALUE_NONE = 1;
            public const VALUE_REQUIRED = 2;
            public const VALUE_OPTIONAL = 4;
            public const VALUE_IS_ARRAY = 8;
            public const VALUE_NEGATABLE = 16;

            public function __construct(
                protected string $name,
                protected string|array|null $shortcut = null,
                protected ?int $mode = null,
                protected string $description = '',
                protected mixed $default = null
            ) {}
        }
    }

    if (!class_exists(InputArgument::class, false)) {
        class InputArgument
        {
            public const REQUIRED = 1;
            public const OPTIONAL = 2;
            public const IS_ARRAY = 4;

            public function __construct(
                protected string $name,
                protected ?int $mode = null,
                protected string $description = '',
                protected mixed $default = null
            ) {}
        }
    }

    if (!class_exists(ArrayInput::class, false)) {
        class ArrayInput implements InputInterface
        {
            protected array $parameters;
            protected bool $interactive = true;

            public function __construct(array $parameters = [])
            {
                $this->parameters = $parameters;
            }

            public function getFirstArgument(): ?string
            {
                foreach ($this->parameters as $key => $value) {
                    if (!str_starts_with((string) $key, '-')) {
                        return (string) $value;
                    }
                }
                return null;
            }

            public function hasParameterOption(string|array $values, bool $onlyParams = false): bool
            {
                $values = (array) $values;
                foreach ($values as $value) {
                    if (array_key_exists($value, $this->parameters) || in_array($value, $this->parameters, true)) {
                        return true;
                    }
                }
                return false;
            }

            public function getParameterOption(string|array $values, string|bool|int|float|array|null $default = false, bool $onlyParams = false): mixed
            {
                $values = (array) $values;
                foreach ($values as $value) {
                    if (array_key_exists($value, $this->parameters)) {
                        return $this->parameters[$value];
                    }
                }
                return $default;
            }

            public function bind(InputDefinition $definition): void {}

            public function validate(): void {}

            public function getArguments(): array
            {
                return $this->parameters;
            }

            public function getArgument(string $name): mixed
            {
                return $this->parameters[$name] ?? $this->parameters['--' . $name] ?? null;
            }

            public function setArgument(string $name, mixed $value): void
            {
                $this->parameters[$name] = $value;
            }

            public function hasArgument(string $name): bool
            {
                return array_key_exists($name, $this->parameters);
            }

            public function getOptions(): array
            {
                return $this->parameters;
            }

            public function getOption(string $name): mixed
            {
                return $this->parameters['--' . $name] ?? $this->parameters[$name] ?? null;
            }

            public function setOption(string $name, mixed $value): void
            {
                $this->parameters['--' . $name] = $value;
            }

            public function hasOption(string $name): bool
            {
                return array_key_exists('--' . $name, $this->parameters) || array_key_exists($name, $this->parameters);
            }

            public function isInteractive(): bool
            {
                return $this->interactive;
            }

            public function setInteractive(bool $interactive): void
            {
                $this->interactive = $interactive;
            }

            public function __toString(): string
            {
                $tokens = [];
                foreach ($this->parameters as $key => $val) {
                    $tokens[] = is_string($key) ? "{$key}={$val}" : (string) $val;
                }
                return implode(' ', $tokens);
            }
        }
    }
}

namespace Symfony\Component\Console\Output {
    use Symfony\Component\Console\Formatter\OutputFormatterInterface;
    use Symfony\Component\Console\Formatter\OutputFormatter;

    if (!interface_exists(OutputInterface::class, false)) {
        interface OutputInterface
        {
            public const VERBOSITY_QUIET = 16;
            public const VERBOSITY_NORMAL = 32;
            public const VERBOSITY_VERBOSE = 64;
            public const VERBOSITY_VERY_VERBOSE = 128;
            public const VERBOSITY_DEBUG = 256;

            public const OUTPUT_NORMAL = 1;
            public const OUTPUT_RAW = 2;
            public const OUTPUT_PLAIN = 4;

            public function write(string|iterable $messages, bool $newline = false, int $options = 0): void;
            public function writeln(string|iterable $messages, int $options = 0): void;
            public function setVerbosity(int $level): void;
            public function getVerbosity(): int;
            public function isQuiet(): bool;
            public function isVerbose(): bool;
            public function isVeryVerbose(): bool;
            public function isDebug(): bool;
            public function setDecorated(bool $decorated): void;
            public function isDecorated(): bool;
            public function setFormatter(OutputFormatterInterface $formatter): void;
            public function getFormatter(): OutputFormatterInterface;
        }
    }

    if (!interface_exists(ConsoleOutputInterface::class, false)) {
        interface ConsoleOutputInterface extends OutputInterface
        {
            public function getErrorOutput(): OutputInterface;
            public function setErrorOutput(OutputInterface $error): void;
        }
    }

    if (!class_exists(ConsoleOutput::class, false)) {
        class ConsoleOutput implements ConsoleOutputInterface
        {
            protected int $verbosity = OutputInterface::VERBOSITY_NORMAL;
            protected bool $decorated = true;
            protected ?OutputFormatterInterface $formatter = null;
            protected ?OutputInterface $errorOutput = null;

            public function __construct(
                int $verbosity = OutputInterface::VERBOSITY_NORMAL,
                ?bool $decorated = null,
                ?OutputFormatterInterface $formatter = null
            ) {
                $this->verbosity = $verbosity;
                $this->decorated = $decorated ?? true;
                $this->formatter = $formatter ?? new OutputFormatter($this->decorated);
            }

            public function writeln(string|iterable $messages, int $options = 0): void
            {
                $this->write($messages, true, $options);
            }

            public function write(string|iterable $messages, bool $newline = false, int $options = 0): void
            {
                if ($this->isQuiet()) {
                    return;
                }

                $messages = is_iterable($messages) ? $messages : [$messages];
                foreach ($messages as $message) {
                    $text = $this->formatMessage((string) $message);
                    echo $text . ($newline ? "\n" : '');
                }
            }

            protected function formatMessage(string $message): string
            {
                if ($this->isDecorated()) {
                    // Convert simple Symfony color tags to ANSI terminal colors
                    $replace = [
                        '<info>' => "\033[32m",
                        '</info>' => "\033[0m",
                        '<comment>' => "\033[33m",
                        '</comment>' => "\033[0m",
                        '<error>' => "\033[37;41m",
                        '</error>' => "\033[0m",
                        '<question>' => "\033[30;46m",
                        '</question>' => "\033[0m",
                        '<fg=red>' => "\033[31m",
                        '<fg=green>' => "\033[32m",
                        '<fg=yellow>' => "\033[33m",
                        '<fg=blue>' => "\033[34m",
                        '<fg=magenta>' => "\033[35m",
                        '<fg=cyan>' => "\033[36m",
                        '<fg=white>' => "\033[37m",
                        '<fg=gray>' => "\033[90m",
                        '</>' => "\033[0m",
                    ];
                    $formatted = str_replace(array_keys($replace), array_values($replace), $message);
                    return preg_replace('/<[^>]*>/', '', $formatted) ?? $formatted;
                }

                return preg_replace('/<[^>]*>/', '', $message) ?? $message;
            }

            public function setVerbosity(int $level): void { $this->verbosity = $level; }
            public function getVerbosity(): int { return $this->verbosity; }
            public function isQuiet(): bool { return $this->verbosity === OutputInterface::VERBOSITY_QUIET; }
            public function isVerbose(): bool { return $this->verbosity >= OutputInterface::VERBOSITY_VERBOSE; }
            public function isVeryVerbose(): bool { return $this->verbosity >= OutputInterface::VERBOSITY_VERY_VERBOSE; }
            public function isDebug(): bool { return $this->verbosity >= OutputInterface::VERBOSITY_DEBUG; }
            public function setDecorated(bool $decorated): void { $this->decorated = $decorated; }
            public function isDecorated(): bool { return $this->decorated; }
            public function setFormatter(OutputFormatterInterface $formatter): void { $this->formatter = $formatter; }
            public function getFormatter(): OutputFormatterInterface { return $this->formatter ??= new OutputFormatter($this->decorated); }

            public function getErrorOutput(): OutputInterface
            {
                return $this->errorOutput ??= $this;
            }

            public function setErrorOutput(OutputInterface $error): void
            {
                $this->errorOutput = $error;
            }
        }
    }

    if (!class_exists(NullOutput::class, false)) {
        class NullOutput implements OutputInterface
        {
            protected int $verbosity = OutputInterface::VERBOSITY_QUIET;
            protected ?OutputFormatterInterface $formatter = null;

            public function write(string|iterable $messages, bool $newline = false, int $options = 0): void {}
            public function writeln(string|iterable $messages, int $options = 0): void {}
            public function setVerbosity(int $level): void { $this->verbosity = $level; }
            public function getVerbosity(): int { return $this->verbosity; }
            public function isQuiet(): bool { return true; }
            public function isVerbose(): bool { return false; }
            public function isVeryVerbose(): bool { return false; }
            public function isDebug(): bool { return false; }
            public function setDecorated(bool $decorated): void {}
            public function isDecorated(): bool { return false; }
            public function setFormatter(OutputFormatterInterface $formatter): void { $this->formatter = $formatter; }
            public function getFormatter(): OutputFormatterInterface { return $this->formatter ??= new OutputFormatter(false); }
        }
    }
}

namespace Symfony\Component\Console\Command {
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\ArrayInput;
    use Symfony\Component\Console\Input\InputDefinition;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Output\ConsoleOutput;

    if (!class_exists(Command::class, false)) {
        class Command
        {
            public const SUCCESS = 0;
            public const FAILURE = 1;
            public const INVALID = 2;

            protected ?string $name = null;
            protected ?string $description = null;
            protected ?string $help = null;
            protected array $arguments = [];
            protected array $options = [];
            protected ?InputDefinition $definition = null;

            public function __construct(?string $name = null)
            {
                if ($name !== null) {
                    $this->name = $name;
                }
                $this->configure();
            }

            protected function configure(): void {}

            public function setName(string $name): static
            {
                $this->name = $name;
                return $this;
            }

            public function getName(): ?string
            {
                return $this->name;
            }

            public function setDescription(string $description): static
            {
                $this->description = $description;
                return $this;
            }

            public function getDescription(): ?string
            {
                return $this->description;
            }

            public function setHelp(string $help): static
            {
                $this->help = $help;
                return $this;
            }

            public function getHelp(): ?string
            {
                return $this->help;
            }

            public function addOption(string $name, string|array|null $shortcut = null, ?int $mode = null, string $description = '', mixed $default = null): static
            {
                $this->options[$name] = [
                    'shortcut' => $shortcut,
                    'mode' => $mode,
                    'description' => $description,
                    'default' => $default,
                ];
                return $this;
            }

            public function addArgument(string $name, ?int $mode = null, string $description = '', mixed $default = null): static
            {
                $this->arguments[$name] = [
                    'mode' => $mode,
                    'description' => $description,
                    'default' => $default,
                ];
                return $this;
            }

            public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
            {
                $input ??= new ArrayInput();
                $output ??= new ConsoleOutput();

                return $this->execute($input, $output);
            }

            protected function execute(InputInterface $input, OutputInterface $output): int
            {
                if (method_exists($this, 'executeDirect')) {
                    $this->executeDirect();
                    return self::SUCCESS;
                }
                return self::SUCCESS;
            }

            protected function getHelper(string $name): mixed
            {
                return new class {
                    public function ask(mixed $input, mixed $output, mixed $question): mixed
                    {
                        return true;
                    }
                };
            }

            public function getHelperSet(): mixed
            {
                return new class {
                    public function get(string $name): mixed
                    {
                        return new class {
                            public function ask(mixed $input, mixed $output, mixed $question): mixed
                            {
                                return true;
                            }
                        };
                    }
                };
            }
        }
    }
}

namespace Symfony\Component\Console\Helper {
    use Symfony\Component\Console\Output\OutputInterface;

    if (!class_exists(Table::class, false)) {
        class Table
        {
            protected array $headers = [];
            protected array $rows = [];
            protected string $style = 'default';

            public function __construct(protected ?OutputInterface $output = null) {}

            public function setHeaders(array $headers): static
            {
                $this->headers = $headers;
                return $this;
            }

            public function setRows(array $rows): static
            {
                $this->rows = $rows;
                return $this;
            }

            public function addRow(array $row): static
            {
                $this->rows[] = $row;
                return $this;
            }

            public function setStyle(string $style): static
            {
                $this->style = $style;
                return $this;
            }

            public function render(): void
            {
                if (!empty($this->headers)) {
                    echo "  " . implode('  |  ', $this->headers) . "\n";
                    echo "  " . str_repeat('─', 60) . "\n";
                }
                foreach ($this->rows as $row) {
                    echo "  " . implode('  |  ', $row) . "\n";
                }
            }
        }
    }

    if (!class_exists(QuestionHelper::class, false)) {
        class QuestionHelper
        {
            public function ask(mixed $input, mixed $output, mixed $question): mixed
            {
                return true;
            }
        }
    }
}

namespace Symfony\Component\Console\Question {
    if (!class_exists(Question::class, false)) {
        class Question
        {
            public function __construct(public string $question, public mixed $default = null) {}
        }
    }

    if (!class_exists(ConfirmationQuestion::class, false)) {
        class ConfirmationQuestion extends Question
        {
            public function __construct(string $question, bool $default = true)
            {
                parent::__construct($question, $default);
            }
        }
    }

    if (!class_exists(ChoiceQuestion::class, false)) {
        class ChoiceQuestion extends Question
        {
            public function __construct(string $question, public array $choices = [], mixed $default = null)
            {
                parent::__construct($question, $default);
            }
        }
    }
}

namespace Symfony\Component\Console {
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\ArrayInput;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Output\ConsoleOutput;

    if (!class_exists(Application::class, false)) {
        class Application
        {
            /** @var array<string, Command> */
            protected array $commands = [];

            public function __construct(public string $name = 'Veldora', public string $version = '0.5.0') {}

            public function add(Command $command): ?Command
            {
                $name = $command->getName();
                if ($name !== null) {
                    $this->commands[$name] = $command;
                }
                return $command;
            }

            public function find(string $name): Command
            {
                if (isset($this->commands[$name])) {
                    return $this->commands[$name];
                }
                throw new \InvalidArgumentException("Command [{$name}] is not defined.");
            }

            public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
            {
                global $argv;
                $commandName = $argv[1] ?? 'help';

                if (isset($this->commands[$commandName])) {
                    return $this->commands[$commandName]->run($input, $output);
                }

                echo "\n  ▲ {$this->name} {$this->version}\n\n";
                echo "  Available commands:\n";
                foreach ($this->commands as $name => $cmd) {
                    $desc = $cmd->getDescription() ?? '';
                    printf("    \033[36m%-25s\033[0m %s\n", $name, $desc);
                }
                echo "\n";
                return 0;
            }
        }
    }
}
