<?php

namespace RebelCode\Atlas\Exception;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when an error occurs while compiling SQL strings.
 */
class SqlCompileException extends RuntimeException
{
    /**
     * Constructor.
     *
     * @param string $message The exception message.
     * @param Throwable|null $previous The previous exception.
     */
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
