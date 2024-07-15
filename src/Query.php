<?php

namespace RebelCode\Atlas;

use LogicException;
use RebelCode\Atlas\Exception\DatabaseException;
use RebelCode\Atlas\Exception\QuerySqlException;

abstract class Query
{
    protected ?DatabaseAdapter $adapter;

    /**
     * Constructor.
     *
     * @param DatabaseAdapter|null $adapter The database adapter to execute the query.
     */
    public function __construct(?DatabaseAdapter $adapter = null)
    {
        $this->adapter = $adapter;
    }

    /**
     * Gets the database adapter to execute the query.
     *
     * @return DatabaseAdapter The database adapter.
     * @throws LogicException If the query has no database adapter.
     */
    protected function getAdapter(): DatabaseAdapter
    {
        if ($this->adapter === null) {
            throw new LogicException('Cannot execute query; please provide a database adapter');
        }

        return $this->adapter;
    }

    /**
     * Compiles the query into an SQL string.
     *
     * @return string The compiled SQL string.
     * @throws QuerySqlException If an error occurred while compiling the SQL.
     */
    abstract public function toSql(): string;

    /**
     * Executes the query.
     *
     * @param array<string,mixed> $args A map of values to interpolate. The keys
     *        correspond to {@link VarExpr} names.
     * @return mixed The query result.
     * @throws DatabaseException If an error occurred while executing the query.
     */
    abstract public function exec(array $args = []);

    /**
     * Replaces ??{var}?? placeholders in the SQL with '?' and collects the
     * values in the order they appear in the string.
     *
     * @param string $sql The SQL string
     * @param array $args An array of args, mapping each arg name to its value.
     * @return array{0:string,1:list<mixed>} The SQL and var values.
     */
    protected function templateVars(string $sql, array $args)
    {
        if (count($args) === 0) {
            return [$sql, []];
        }

        $vars = [];
        $result = preg_replace_callback(
            '/(\?\?\{([\w\d_]+)\}\?\?)/im',
            function (array $match) use ($args, &$vars) {
                $name = trim($match[2] ?? '');
                if (empty($name) && !array_key_exists($name, $args)) {
                    return $match[1];
                }
                $value = $args[$name];
                $vars[] = $value;
                return $this->adapter->getPlaceholder($name, $value);
            },
            $sql
        );

        return [$result, $vars];
    }
}
