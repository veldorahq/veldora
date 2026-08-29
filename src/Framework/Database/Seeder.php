<?php

declare(strict_types=1);

namespace Veldora\Framework\Database;

abstract class Seeder
{
    /**
     * The database connection.
     */
    protected Connection $db;

    /**
     * Create a new Seeder instance.
     */
    public function __construct(?Connection $db = null)
    {
        $this->db = $db ?: app(Connection::class);
    }

    /**
     * Run the database seeds.
     */
    abstract public function run(): void;

    /**
     * Seed the given connection from the given classes.
     *
     * @param array<class-string<Seeder>>|class-string<Seeder> $class
     */
    public function call(array|string $class): static
    {
        $classes = (array) $class;

        foreach ($classes as $seederClass) {
            /** @var Seeder $seeder */
            $seeder = new $seederClass($this->db);
            $seeder->run();
        }

        return $this;
    }
}
