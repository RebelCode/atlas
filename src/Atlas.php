<?php

namespace RebelCode\Atlas;

class Atlas
{
    protected ?DatabaseAdapter $adapter;
    /** @var array<string,Table> */
    protected array $tables;

    /**
     * Constructor.
     *
     * @param DatabaseAdapter|null $adapter Optional database adapter to be able to execute queries.
     */
    public function __construct(?DatabaseAdapter $adapter = null)
    {
        $this->adapter = $adapter;
        $this->tables = [];
    }

    public function table(string $name, ?Schema $schema = null): Table
    {
        if (!array_key_exists($name, $this->tables) || $schema !== null) {
            $this->tables[$name] = new Table($name, $schema, $this->adapter);
        }

        return $this->tables[$name];
    }

    public function getDbAdapter(): ?DatabaseAdapter
    {
        return $this->adapter;
    }

    /** @return Table[] */
    public function getTables(): array
    {
        return $this->tables;
    }
}
