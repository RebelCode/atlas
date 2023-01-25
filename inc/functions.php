<?php

namespace RebelCode\Atlas;

use RebelCode\Atlas\Expression\Term;

/**
 * Creates a new column term.
 *
 * @param string $name The column name.
 * @return Term The column term.
 */
function col(string $name): Term
{
    return new Term(Term::COLUMN, $name);
}

/**
 * Creates an ascending order instance for a column.
 *
 * @param string $column The column name.
 * @return Order The order instance.
 */
function asc(string $column): Order
{
    return new Order($column, Order::ASC);
}

/**
 * Creates a descending order instance for a column.
 *
 * @param string $column The column name.
 * @return Order The order instance.
 */
function desc(string $column): Order
{
    return new Order($column, Order::DESC);
}
