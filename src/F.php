<?php

namespace RebelCode\Atlas;

use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Expression\UnaryExpr;

/**
 * Helper class for easily creating unary expressions for SQL functions.
 */
abstract class F
{
    /**
     * Creates a unary expression.
     *
     * @param string $operator The called method name, which corresponds to the operator (a.k.a. function name).
     * @param Term[] $arguments The call arguments, which corresponds to the operand. Only the first argument is used.
     * @return UnaryExpr
     */
    public static function __callStatic(string $operator, array $arguments)
    {
        return new UnaryExpr($operator, $arguments[0]);
    }
}
