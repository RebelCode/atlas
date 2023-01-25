<?php

namespace RebelCode\Atlas\Query;

use RebelCode\Atlas\Exception\DatabaseException;
use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\QueryType\Update;

class UpdateQuery extends Query
{
    /** @psalm-immutable */
    public function table(string $table): self
    {
        return $this->withAddedData([Update::TABLE => $table]);
    }

    /** @psalm-immutable */
    public function set(array $set): self
    {
        return $this->withAddedData([Update::SET => $set]);
    }

    /** @psalm-immutable */
    public function where(?ExprInterface $expr): self
    {
        return $this->withAddedData([Update::WHERE => $expr]);
    }

    /** @psalm-immutable */
    public function orderBy(array $order): self
    {
        return $this->withAddedData([Update::ORDER => $order]);
    }

    /** @psalm-immutable */
    public function limit(?int $limit): self
    {
        return $this->withAddedData([Update::LIMIT => $limit]);
    }

    /**
     * Executes the UPDATE query.
     *
     * @return int The number of rows affected by the query.
     * @throws DatabaseException If an error occurred while executing the query.
     */
    public function exec(): int
    {
        return $this->getAdapter()->queryNumRows($this->compile());
    }
}
