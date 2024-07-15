<?php

namespace RebelCode\Atlas;

use RebelCode\Atlas\Exception\DatabaseException;

/** An adapter for a database connection that allows queries created by Atlas to be executed. */
interface DatabaseAdapter
{
    /**
     * Gets the placeholder to use for a value before preparing the query.
     *
     * @param string $name The name of the value.
     * @param mixed $value The value.
     * @return string The placeholder string, such as '?'.
     */
    public function getPlaceholder(string $name, $value): string;

    /**
     * Replaces ??{var}?? placeholders in the SQL with '?' and collects the
     * values in the order they appear in the string.
     *
     * @param string $sql The SQL string
     * @param list<mixed> $values The values
     * @return array{0:string,1:list<mixed>} The SQL and var values.
     */
    public function prepare(string $sql, array $values): array;

    /**
     * Executes a query that returns results.
     *
     * @param string $sql The SQL for the query to execute.
     * @return array<string,mixed>[] A list of rows, where each row is a map of column names to values.
     * @throws DatabaseException If an error occurred while executing the query.
     */
    public function queryResults(string $sql, array $args = []): array;

    /**
     * Executes a query and returns the number of affected rows.
     *
     * @param string $sql The SQL for the query to execute.
     * @return int The number of affected rows.
     * @throws DatabaseException If an error occurred while executing the query.
     */
    public function queryNumRows(string $sql, array $args = []): int;

    /**
     * Executes a query and returns whether the query was successful.
     *
     * @param string $sql The SQL for the query to execute.
     * @return bool True if the query was successful, false otherwise.
     * @throws DatabaseException If an error occurred while executing the query.
     */
    public function query(string $sql, array $args = []): bool;

    /**
     * Gets the value generated for an AUTO_INCREMENT column by the last query.
     *
     * @return int|null The generated ID, or null if the last query did not generate an ID.
     */
    public function getInsertId(): ?int;

    /**
     * Gets the error message, if any, from the last query.
     *
     * @return string|null The error message, or null if the last query was successful.
     */
    public function getError(): ?string;
}
