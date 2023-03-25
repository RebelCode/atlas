<?php

namespace RebelCode\Atlas\Exception;

use RebelCode\Atlas\Query;
use Throwable;

/**
 * Exception thrown when a query fails to compile to SQL.
 */
class QuerySqlException extends SqlCompileException
{
    protected Query $query;

    /**
     * Constructor.
     *
     * @param string $message The exception message.
     * @param Query $query The query that failed to compile.
     * @param Throwable|null $previous The previous exception.
     */
    public function __construct(string $message, Query $query, ?Throwable $previous = null)
    {
        parent::__construct($message, $previous);
        $this->query = $query;
    }

    /**
     * Retrieves the query that failed to compile.
     *
     * @return Query
     */
    public function getQuery(): Query
    {
        return $this->query;
    }
}
