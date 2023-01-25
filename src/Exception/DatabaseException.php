<?php

namespace RebelCode\Atlas\Exception;

use Exception;
use RebelCode\Atlas\DatabaseAdapter;
use Throwable;

class DatabaseException extends Exception
{
    /** @var DatabaseAdapter|null */
    protected $adapter;

    /**
     * Constructor.
     *
     * @param string $message The exception message.
     * @param DatabaseAdapter|null $adapter The database adapter that caused the exception.
     * @param Throwable|null $previous The previous exception.
     */
    public function __construct(string $message = "", ?DatabaseAdapter $adapter = null, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->adapter = $adapter;
    }

    /**
     * Retrieves the database adapter that caused the exception.
     *
     * @return DatabaseAdapter|null The database adapter, or null if none was set.
     */
    public function getAdapter() : ?DatabaseAdapter
    {
        return $this->adapter;
    }
}
