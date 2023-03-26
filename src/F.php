<?php

namespace RebelCode\Atlas;

use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\FnExpr;
use RebelCode\Atlas\Expression\Term;

/**
 * Helper class for easily creating unary expressions for SQL functions.
 */
abstract class F
{
    /**
     * Creates a function expression.
     *
     * @param string $operator The called method name, which corresponds to the operator (a.k.a. function name).
     * @param list<mixed|ExprInterface> $arguments The call arguments.
     * @return FnExpr The created function expression.
     */
    public static function __callStatic(string $operator, array $arguments): FnExpr
    {
        return new FnExpr($operator, array_map([Term::class, 'create'], $arguments));
    }
}
