<?php

namespace RebelCode\Atlas\Query;

use DomainException;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\QuerySqlException;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\Schema\Index;
use Throwable;

class CreateIndexQuery extends Query
{
    protected string $table;
    protected string $name;
    protected Index $index;

    /**
     * Constructor.
     *
     * @param DatabaseAdapter|null $adapter The database adapter to execute the query.
     * @param string $from The name of the table to create the index on.
     * @param string $name The name of the index to create.
     * @param Index $index The index to create.
     */
    public function __construct(
        ?DatabaseAdapter $adapter,
        string $from,
        string $name,
        Index $index
    ) {
        parent::__construct($adapter);
        $this->table = $from;
        $this->name = $name;
        $this->index = $index;
    }

    /** @inheritDoc */
    public function toSql(): string
    {
        try {
            $table = trim($this->table);
            if (empty($table)) {
                throw new DomainException('Table name is missing');
            }

            $name = trim($this->name);
            if (empty($name)) {
                throw new DomainException('The index name was not specified');
            }

            $columns = $this->index->getColumns();
            if (empty($columns)) {
                throw new DomainException('The column list is empty');
            }

            $columnList = [];
            foreach ($this->index->getColumns() as $col => $order) {
                $order = $order ?? Order::ASC;
                $columnList[] = "`$col` $order";
            }

            $columnStr = implode(', ', $columnList);
            $command = $this->index->isUnique() ? 'CREATE UNIQUE INDEX' : 'CREATE INDEX';

            return "$command `$name` ON `$table` ($columnStr)";
        } catch (Throwable $e) {
            throw new QuerySqlException('Cannot compile CREATE INDEX query - ' . $e->getMessage(), $this, $e);
        }
    }

    /**
     * @inheritDoc
     *
     * @return bool True if the index was created successfully, false otherwise.
     */
    public function exec(): bool
    {
        return $this->getAdapter()->query($this->toSql());
    }
}
