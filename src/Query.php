<?php

namespace RebelCode\Atlas;

use LogicException;
use RebelCode\Atlas\Exception\DatabaseException;
use RebelCode\Atlas\Exception\QueryCompileException;

abstract class Query
{
    /** @var DatabaseAdapter|null */
    protected $adapter;

    /**
     * Constructor.
     *
     * @param DatabaseAdapter|null $adapter The database adapter to execute the query.
     */
    public function __construct(?DatabaseAdapter $adapter = null)
    {
        $this->adapter = $adapter;
    }

    /**
     * Gets the database adapter to execute the query.
     *
     * @return DatabaseAdapter The database adapter.
     * @throws LogicException If the query has no database adapter.
     */
    protected function getAdapter(): DatabaseAdapter
    {
        if ($this->adapter === null) {
            throw new LogicException('Cannot execute query; please provide a database adapter');
        }

        return $this->adapter;
    }

    /**
     * Compiles the query into a SQL string.
     *
     * @psalm-mutation-free
     * @return string The SQL string.
     * @throws QueryCompileException If an error occurred while compiling the query.
     */
    abstract public function compile(): string;

    /**
     * Executes the query.
     *
     * @return mixed The query result.
     * @throws DatabaseException If an error occurred while executing the query.
     */
    abstract public function exec();

    /**
     * Casts the query to a string by compiling it into SQL.
     *
     * @return string The SQL string.
     */
    public function __toString(): string
    {
        return $this->compile();
    }
}
