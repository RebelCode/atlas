<?php

namespace RebelCode\Atlas\Exception;

use RebelCode\Atlas\Query;
use RuntimeException;
use Throwable;

class QueryCompileException extends RuntimeException
{
    /** @var Query */
    protected $query;

    /**
     * Constructor.
     *
     * @param string $message The exception message.
     * @param Query $query The query that failed to compile.
     * @param Throwable|null $previous The previous exception.
     */
    public function __construct(string $message, Query $query, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
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
