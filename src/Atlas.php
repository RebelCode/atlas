<?php

namespace RebelCode\Atlas;

class Atlas
{
    /** @var Config */
    protected $config;

    /** @var array<string,Table> */
    protected $tables;

    /** @param Config $config */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->tables = [];
    }

    public function getTable(string $name): ?Table
    {
        return $this->tables[$name] ?? null;
    }

    public function addTable(string $name, ?Schema $schema = null): ?Table
    {
        return $this->tables[$name] = new Table($this->config, $name, $schema);
    }

    public function table(string $name, ?Schema $schema = null): Table
    {
        return $this->tables[$name] ?? $this->tables[$name] = new Table($this->config, $name, $schema);
    }

    public static function createDefault(): self
    {
        return new self(Config::createDefault());
    }
}
