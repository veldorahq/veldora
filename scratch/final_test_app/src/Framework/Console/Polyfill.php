<?php

declare(strict_types=1);

namespace Symfony\Component\Console\Command {
    if (!class_exists(Command::class, false)) {
        class Command
        {
            public const SUCCESS = 0;
            public const FAILURE = 1;
            public const INVALID = 2;

            protected ?string $name = null;
            protected ?string $description = null;

            public function __construct(?string $name = null)
            {
                if ($name !== null) {
                    $this->name = $name;
                }
            }

            public function setName(string $name): static
            {
                $this->name = $name;
                return $this;
            }

            public function setDescription(string $description): static
            {
                $this->description = $description;
                return $this;
            }

            public function addOption(string $name, ?string $shortcut = null, ?int $mode = null, string $description = '', mixed $default = null): static
            {
                return $this;
            }

            public function addArgument(string $name, ?int $mode = null, string $description = '', mixed $default = null): static
            {
                return $this;
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
        }
    }
}

namespace Symfony\Component\Console\Input {
    if (!interface_exists(InputInterface::class, false)) {
        interface InputInterface {}
    }
    if (!class_exists(InputOption::class, false)) {
        class InputOption
        {
            public const VALUE_NONE = 1;
            public const VALUE_REQUIRED = 2;
            public const VALUE_OPTIONAL = 4;
            public const VALUE_IS_ARRAY = 8;
        }
    }
    if (!class_exists(InputArgument::class, false)) {
        class InputArgument
        {
            public const REQUIRED = 1;
            public const OPTIONAL = 2;
            public const IS_ARRAY = 4;
        }
    }
    if (!class_exists(ArrayInput::class, false)) {
        class ArrayInput implements InputInterface
        {
            public function __construct(protected array $parameters = []) {}
        }
    }
}

namespace Symfony\Component\Console\Output {
    if (!interface_exists(OutputInterface::class, false)) {
        interface OutputInterface {}
    }
    if (!class_exists(ConsoleOutput::class, false)) {
        class ConsoleOutput implements OutputInterface
        {
            public function writeln(string $messages): void
            {
                echo $messages . "\n";
            }
            public function write(string $messages): void
            {
                echo $messages;
            }
        }
    }
}

namespace Symfony\Component\Console\Question {
    if (!class_exists(ConfirmationQuestion::class, false)) {
        class ConfirmationQuestion
        {
            public function __construct(public string $question, public bool $default = true) {}
        }
    }
}
