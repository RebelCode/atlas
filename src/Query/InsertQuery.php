<?php

namespace RebelCode\Atlas\Query;

use RebelCode\Atlas\Exception\DatabaseException;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\QueryType\Insert;

class InsertQuery extends Query
{
    /** @psalm-mutation-free */
    public function into(string $table): self
    {
        return $this->withAddedData([Insert::TABLE => $table]);
    }

    /**
     * @param string[] $columns
     * @psalm-mutation-free
     */
    public function columns(array $columns): self
    {
        return $this->withAddedData([Insert::COLUMNS => $columns]);
    }

    /**
     * @param array[] $values
     * @psalm-param array<string, mixed>[] $values
     * @psalm-mutation-free
     */
    public function values(array $values): self
    {
        return $this->withAddedData([Insert::VALUES => $values]);
    }

    /**
     * @param array<string, mixed> $assignList
     * @psalm-mutation-free
     */
    public function onDuplicateKey(array $assignList): self
    {
        return $this->withAddedData([Insert::ON_DUPLICATE_KEY => $assignList]);
    }

    /**
     * Executes the INSERT query.
     *
     * @return int|null The last inserted ID, or null if no rows were inserted.
     * @throws DatabaseException If an error occurred while executing the query.
     */
    public function exec(): ?int
    {
        $adapter = $this->getAdapter();
        $numRows = $adapter->queryNumRows($this->compile());

        if ($numRows > 0) {
            return $adapter->getInsertId();
        } else {
            return null;
        }
    }
}
