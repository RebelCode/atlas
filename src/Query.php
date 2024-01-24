<?php

namespace RebelCode\Atlas;

use LogicException;
use RebelCode\Atlas\Exception\DatabaseException;
use RebelCode\Atlas\Exception\QuerySqlException;

abstract class Query
{
    protected ?DatabaseAdapter $adapter;

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
     * Compiles the query into an SQL string.
     *
     * @return string The compiled SQL string.
     * @throws QuerySqlException If an error occurred while compiling the SQL.
     */
    abstract public function toSql(): string;

    /**
     * Executes the query.
     *
     * @return mixed The query result.
     * @throws DatabaseException If an error occurred while executing the query.
     */
    abstract public function exec();
}
