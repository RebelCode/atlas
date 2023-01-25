<?php

namespace RebelCode\Atlas;

/**
 * An adapter for a database connection that allows queries created by Atlas to be executed.
 */
interface DatabaseAdapter
{
    /**
     * Executes a query that returns results.
     *
     * @param string $sql The SQL for the query to execute.
     * @return array<string,mixed>[] A list of rows, where each row is a map of column names to values.
     */
    public function queryResults(string $sql): array;

    /**
     * Executes a query and returns the number of affected rows.
     *
     * @param string $sql The SQL for the query to execute.
     * @return int The number of affected rows.
     */
    public function queryNumRows(string $sql): int;

    /**
     * Executes a query and returns whether the query was successful.
     *
     * @param string $sql The SQL for the query to execute.
     * @return bool True if the query was successful, false otherwise.
     */
    public function query(string $sql): bool;

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
