<?php

namespace RebelCode\Atlas;

use DomainException;
use InvalidArgumentException;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;

/**
 * Compiles fragments of SQL queries.
 *
 * The methods largely accept `mixed` arguments. This is to allow consumer code to pass data retrieved from
 * {@link Query::get()} without needing to perform validation. The methods in this class will perform the validation
 * themselves.
 */
class QueryCompiler
{
    /**
     * Compiles the FROM fragment os SQL queries.
     *
     * @psalm-mutation-free
     *
     * @param mixed $source The source. Either a table name, or a {@link Query} instance if the $subQueries argument
     *                      is true.
     * @param mixed $alias Optional alias string.
     * @param bool $subQueries If true, {@link Query} instances as the first argument are accepted.
     * @return string
     */
    public static function compileFrom($source, $alias = null, bool $subQueries = false): string
    {
        $isSubQuery = ($subQueries && $source instanceof Query);
        $sourceStr = $isSubQuery ? $source->compile() : $source;

        if (is_string($sourceStr)) {
            $sourceStr = trim($sourceStr);
            if (empty($sourceStr)) {
                throw new DomainException('Query source is a empty string');
            }

            $sourceStr = $isSubQuery ? "($sourceStr)" : "`$sourceStr`";
            $suffix = empty($alias) ? '' : " AS `$alias`";

            return 'FROM ' . $sourceStr . $suffix;
        } else {
            throw new InvalidArgumentException(
                $subQueries
                    ? 'Query source is not a valid string or sub-query'
                    : 'Query source is not a valid string'
            );
        }
    }

    /**
     * Compiles a list of columns.
     *
     * @psalm-mutation-free
     *
     * @param mixed $columns The columns. If the $select argument is false, a list of strings is expected.
     *                       If the $select argument is true, the list may also include {@link ExprInterface}
     *                       instances. Additionally, the elements may have string keys to denote aliases.
     * @param bool $select True if the column list is being compiled for a SELECT query. This will enable the columns
     *                     to be {@link ExprInterface} instances and have aliases.
     * @return string
     */
    public static function compileColumnList($columns, bool $select = false): string
    {
        if (empty($columns)) {
            return '';
        }

        if (!is_array($columns)) {
            throw new InvalidArgumentException('Column list is not an array');
        }

        if ($select) {
            $list = [];
            foreach ($columns as $key => $value) {
                if ($value === '*') {
                    $list[] = '*';
                } else {
                    $expr = ($value instanceof ExprInterface)
                        ? $value
                        : new Term(Term::COLUMN, $value);

                    $list[] = $expr->toString() . (is_numeric($key) ? '' : " AS `$key`");
                }
            }

            return implode(', ', $list);
        } else {
            return '`' . implode('`, `', $columns) . '`';
        }
    }

    /**
     * Compiles the LIMIT fragment of an SQL query.
     *
     * @psalm-mutation-free
     *
     * @param mixed $limit The limit count.
     * @return string
     */
    public static function compileLimit($limit): string
    {
        if (is_numeric($limit)) {
            return 'LIMIT ' . (string) intval($limit);
        } else {
            return '';
        }
    }

    /**
     * Compiles the OFFSET fragment of an SQL query.
     *
     * @psalm-mutation-free
     *
     * @param mixed $offset The offset count.
     * @return string
     */
    public static function compileOffset($offset): string
    {
        if (is_numeric($offset)) {
            return 'OFFSET ' . (string) intval($offset);
        } else {
            return '';
        }
    }

    /**
     * Compiles the ORDER BY fragment of an SQL query.
     *
     * @psalm-mutation-free
     *
     * @param mixed $orderList A list of {@link Order} instances.
     * @return string
     */
    public static function compileOrder($orderList): string
    {
        if ($orderList !== null && !is_array($orderList)) {
            throw new InvalidArgumentException('ORDER BY list is not an array');
        }

        if (empty($orderList)) {
            return '';
        }

        $orderParts = [];
        foreach ($orderList as $order) {
            if ($order instanceof Order) {
                $orderParts[] = "`{$order->getField()}` {$order->getSort()}";
            } else {
                throw new InvalidArgumentException('ORDER BY list contains a non-Order value');
            }
        }

        return 'ORDER BY ' . implode(', ', $orderParts);
    }

    /**
     * Compiles the WHERE fragment of an SQL query.
     *
     * @psalm-mutation-free
     *
     * @param mixed $where An {@link ExprInterface} instance.
     * @return string
     */
    public static function compileWhere($where): string
    {
        if ($where instanceof ExprInterface) {
            return 'WHERE ' . $where->toString();
        } elseif ($where === null) {
            return '';
        } else {
            throw new InvalidArgumentException('WHERE is not an expression');
        }
    }
}
