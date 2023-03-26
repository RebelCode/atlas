<?php

namespace RebelCode\Atlas;

use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;

/**
 * Creates a new column term.
 *
 * @param string $arg1 The column name if one argument is given, the source name or alias if two arguments are given.
 * @param string|null $arg2 The column name if the first argument is the source name or alias.
 * @return Term The column term.
 */
function col(string $arg1, ?string $arg2 = null): Term
{
    if ($arg2 === null) {
        return new Term(Term::COLUMN, $arg1);
    } else {
        return new Term(Term::COLUMN, [$arg1, $arg2]);
    }
}

/**
 * Creates a data source for a table. This is an alias for the {@link TableRef} constructor.
 *
 * @param string $name The table name.
 * @param string|null $alias Optional alias.
 * @return TableRef The created data source.
 */
function table(string $name, ?string $alias = null): TableRef
{
    return new TableRef($name, $alias);
}

/**
 * Creates a join instance. This is an alias for the {@link Join} constructor.
 *
 * Note that this creates a join with an empty table name. Be sure to call {@link Join::with()} to set the table name.
 *
 * @param string $type The join type.
 * @param DataSource|null $with The data source to join with.
 * @param ExprInterface|null $on The condition to join on.
 * @return Join The created join instance.
 */
function using(string $type, ?DataSource $with = null, ?ExprInterface $on = null): Join
{
    return new Join($type, $with ?? new TableRef(''), $on);
}

/**
 * Creates an ascending order instance for a column.
 *
 * @param string $column The column name.
 * @return Order|Group The order instance.
 */
function asc(string $column): Order
{
    return new Order($column, Order::ASC);
}

/**
 * Creates a descending order instance for a column.
 *
 * @param string $column The column name.
 * @return Order|Group The order instance.
 */
function desc(string $column): Order
{
    return new Order($column, Order::DESC);
}

/**
 * Creates a new expression term. This is an alias for the {@link Term::create()} method.
 *
 * @param mixed $value The value to create the term from.
 * @return ExprInterface The created expression term.
 */
function expr($value): ExprInterface
{
    return Term::create($value);
}

/**
 * Creates a boolean NOT unary expression. This is an alias for calling the {@link ExprInterface::not()} method on the
 * passed argument.
 *
 * @param ExprInterface $expr The expression to negate.
 * @return ExprInterface The created expression.
 */
function not(ExprInterface $expr): ExprInterface
{
    return $expr->not();
}

/**
 * Creates a number negation unary expression. This is an alias for calling the {@link ExprInterface::not()} method on
 * the passed argument.
 *
 * @param ExprInterface $expr The expression to negate.
 * @return ExprInterface The created expression.
 */
function neg(ExprInterface $expr): ExprInterface
{
    return $expr->neg();
}
