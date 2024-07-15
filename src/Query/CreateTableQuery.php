<?php

namespace RebelCode\Atlas\Query;

use DomainException;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\QuerySqlException;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\Schema;
use Throwable;
use UnexpectedValueException;

class CreateTableQuery extends Query
{
    protected string $name;
    protected bool $ifNotExists;
    protected Schema $schema;
    protected ?string $collate;

    /**
     * Constructor.
     *
     * @param DatabaseAdapter|null $adapter The database adapter to execute the query.
     * @param string $name The name of the table to create.
     * @param bool $ifNotExists Whether to add the "IF NOT EXISTS" clause.
     * @param Schema $schema The table schema.
     * @param string|null $collate The collation to use for the table.
     */
    public function __construct(
        ?DatabaseAdapter $adapter,
        string $name,
        bool $ifNotExists,
        Schema $schema,
        ?string $collate = null
    ) {
        parent::__construct($adapter);
        $this->ifNotExists = $ifNotExists;
        $this->name = $name;
        $this->schema = $schema;
        $this->collate = $collate;
    }

    /** @inheritDoc */
    public function toSql(): string
    {
        try {
            $name = trim($this->name);
            if (empty($name)) {
                throw new DomainException('Table name is missing');
            }

            $command = 'CREATE TABLE' . ($this->ifNotExists ? ' IF NOT EXISTS' : '');
            $schemaStr = $this->compileSchema($this->schema);

            if (empty($schemaStr)) {
                throw new UnexpectedValueException('A table schema is required');
            }

            $result = "$command `$name` (\n  $schemaStr\n)";

            if ($this->collate !== null) {
                $result .= ' COLLATE ' . $this->collate;
            }

            return $result;
        } catch (Throwable $e) {
            throw new QuerySqlException('Cannot compile CREATE TABLE query - ' . $e->getMessage(), $this, $e);
        }
    }

    /**
     * Compiles the schema for a table.
     *
     * @param Schema $schema The table schema.
     * @return string
     */
    protected function compileSchema(Schema $schema): string
    {
        $lines = [];

        foreach ($schema->getColumns() as $name => $column) {
            $lines[] = $column->toSql($name);
        }

        foreach ($schema->getKeys() as $name => $key) {
            $lines[] = $key->toSql($name);
        }

        return implode(",\n  ", $lines);
    }

    /**
     * @inheritDoc
     *
     * @return bool True if the query was executed successfully, false otherwise.
     */
    public function exec(array $args = []): bool
    {
        [$sql, $values] = $this->templateVars($this->toSql(), $args);

        return $this->getAdapter()->query($sql, $values);
    }
}
