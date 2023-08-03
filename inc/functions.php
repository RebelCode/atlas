<?php

namespace RebelCode\Atlas;

use InvalidArgumentException;
use LogicException;
use RebelCode\Atlas\Expression\BinaryExpr;
use RebelCode\Atlas\Expression\ColumnTerm;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;

/**
 * Creates a new column term.
 *
 * @psalm-pure
 * @param string|ColumnTerm|Table|TableRef $arg1 The column name or term if only 1 arg is given. Otherwise, the table
 *                                               name, instance, or reference.
 * @param string|null $arg2 The column name if the 1st arg is a table name, instance, or reference.
 * @return ColumnTerm The column term.
 *
 * @throws InvalidArgumentException
 */
function col($arg1, ?string $arg2 = null): ColumnTerm
{
    if ($arg2 === null) {
        if ($arg1 instanceof ColumnTerm) {
            return $arg1;
        } elseif (is_string($arg1)) {
            return new ColumnTerm(null, $arg1);
        } else {
            throw new InvalidArgumentException('Expected column name or term, got ' . gettype($arg1));
        }
    } else {
        if ($arg1 instanceof Table || $arg1 instanceof TableRef) {
            return new ColumnTerm($arg1->getAlias() ?? $arg1->getName(), $arg2);
        } elseif (is_string($arg1)) {
            return new ColumnTerm($arg1, $arg2);
        } else {
            throw new InvalidArgumentException('Expected table name, instance, or reference, got ' . gettype($arg1));
        }
    }
}

/**
 * Creates a data source for a table. This is an alias for the {@link TableRef} constructor.
 *
 * @psalm-pure
 * @param string $name The table name.
 * @param string|null $alias Optional alias.
 * @return TableRef The created data source.
 */
function table(string $name, ?string $alias = null): TableRef
{
    return new TableRef($name, $alias);
}

/**
 * Creates a column term that references all columns for a data source.
 *
 * @param string|DataSource $source The data source.
 * @return ColumnTerm The created column term.
 *
 * @throws LogicException If the data source is not a table or table ref and has no alias.
 */
function all($source): ColumnTerm
{
    if ($source instanceof DataSource) {
        $name = $source->getAlias();

        if ($source instanceof Table || $source instanceof TableRef) {
            $name = $name ?? $source->getName();
        } elseif ($name === null) {
            throw new LogicException('Data source must have an alias to be used as a derived table');
        }
    } else {
        $name = $source;
    }

    return new ColumnTerm($name, '*');
}

/**
 * Creates a join instance. This is an alias for the {@link Join} constructor.
 *
 * Note that this creates a join with an empty table name. Be sure to call {@link Join::with()} to set the table name.
 *
 * @psalm-pure
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
 * @psalm-pure
 * @param string|ColumnTerm $column The column name or term.
 * @return Order|Group The order instance.
 */
function asc($column): Order
{
    return new Order($column, Order::ASC);
}

/**
 * Creates a descending order instance for a column.
 *
 * @psalm-pure
 * @param string|ColumnTerm $column The column name or term.
 * @return Order|Group The order instance.
 */
function desc($column): Order
{
    return new Order($column, Order::DESC);
}

/**
 * Creates a new expression term or binary expression.
 *
 * When given only one argument, this function simply creates a term using {@link Term::create()}. When given two or
 * three arguments, this function creates a binary expression using the first argument as the left-hand side, the second
 * argument as the operator, and the third argument as the right-hand side.
 *
 * @psalm-pure
 * @param mixed $value The value to create the term from.
 * @param string|null $operator Optional operator to create a binary expression.
 * @param mixed|null $value2 Optional second value to create a binary expression.
 * @return ExprInterface The created expression.
 */
function expr($value, ?string $operator = null, $value2 = null): ExprInterface
{
    $left = Term::create($value);

    if ($operator === null) {
        return $left;
    } else {
        $right = Term::create($value2);
        return new BinaryExpr($left, $operator, $right);
    }
}

/**
 * Creates a boolean NOT unary expression. This is an alias for calling the {@link ExprInterface::not()} method on the
 * passed argument.
 *
 * @psalm-pure
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
 * @psalm-pure
 * @param ExprInterface $expr The expression to negate.
 * @return ExprInterface The created expression.
 */
function neg(ExprInterface $expr): ExprInterface
{
    return $expr->neg();
}

/**
 * Creates a distinct column term. This is an alias for calling the {@link ColumnTerm::distinct()} method on the column.
 *
 * @psalm-pure
 * @param ColumnTerm $col The column term.
 * @return ColumnTerm The distinct column term.
 */
function distinct(ColumnTerm $col): ColumnTerm
{
    return $col->distinct();
}

/**
 * ORs a list of expressions into a single expression.
 *
 * @psalm-pure
 * @param iterable<ExprInterface|null> $exprs The expressions to OR.
 * @return ExprInterface The created expression.
 */
function orAll(iterable $exprs): ExprInterface
{
    $result = null;

    foreach ($exprs as $expr) {
        if ($expr instanceof ExprInterface) {
            $result = $result ? $result->or($expr) : $expr;
        }
    }

    if ($result === null) {
        return Term::create(false);
    } else {
        return $result;
    }
}

/**
 * ANDs a list of expressions into a single expression.
 *
 * @psalm-pure
 * @param iterable<ExprInterface|null> $exprs The expressions to AND.
 * @return ExprInterface The created expression.
 */
function andAll(iterable $exprs): ExprInterface
{
    $result = null;

    foreach ($exprs as $expr) {
        if ($expr instanceof ExprInterface) {
            $result = $result ? $result->and($expr) : $expr;
        }
    }

    if ($result === null) {
        return Term::create(false);
    } else {
        return $result;
    }
}
