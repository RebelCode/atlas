<?php

namespace RebelCode\Atlas\Query;

use DomainException;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\QuerySqlException;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\Schema;
use RebelCode\Atlas\Schema\ForeignKey;
use Throwable;
use UnexpectedValueException;

/** @psalm-immutable */
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

    /**
     * @inheritDoc
     * @psalm-mutation-free
     */
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
     * @psalm-mutation-free
     *
     * @param Schema $schema The table schema.
     * @return string
     */
    protected function compileSchema(Schema $schema): string
    {
        $lines = [];

        foreach ($schema->getColumns() as $name => $column) {
            $parts = ["`$name`", $column->getType()];

            $defaultVal = $column->getDefaultValue();
            if ($defaultVal !== null) {
                $parts[] = "DEFAULT " . $defaultVal->toSql();
            } else {
                $parts[] = $column->getIsNullable() ? 'NULL' : 'NOT NULL';
            }

            if ($column->getIsAutoInc()) {
                $parts[] = 'AUTO_INCREMENT';
            }

            $lines[] = implode(' ', $parts);
        }

        foreach ($schema->getKeys() as $name => $key) {
            $type = $key->isPrimary() ? "PRIMARY KEY" : "UNIQUE";

            $columns = $key->getColumns();
            $columnsStr = implode('`, `', $columns);

            $lines[] = "CONSTRAINT `$name` $type (`$columnsStr`)";
        }

        foreach ($schema->getForeignKeys() as $name => $foreignKey) {
            $mappings = $foreignKey->getMappings();
            $foreignTable = $foreignKey->getForeignTable();
            $updateRule = $foreignKey->getUpdateRule();
            $deleteRule = $foreignKey->getDeleteRule();

            $tableColumns = implode('`, `', array_keys($mappings));
            $foreignColumns = implode('`, `', array_values($mappings));

            $constraint = "CONSTRAINT `$name` FOREIGN KEY (`$tableColumns`) REFERENCES `$foreignTable` (`$foreignColumns`)";

            if ($updateRule !== ForeignKey::RESTRICT) {
                $constraint .= " ON UPDATE " . $updateRule;
            }

            if ($deleteRule !== ForeignKey::RESTRICT) {
                $constraint .= ' ON DELETE ' . $deleteRule;
            }

            $lines[] = $constraint;
        }

        return implode(",\n  ", $lines);
    }

    /**
     * @inheritDoc
     *
     * @return bool True if the query was executed successfully, false otherwise.
     */
    public function exec(): bool
    {
        return $this->getAdapter()->query($this->toSql());
    }
}
