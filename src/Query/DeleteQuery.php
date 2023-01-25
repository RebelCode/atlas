<?php

namespace RebelCode\Atlas\Query;

use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\QueryType\Delete;

class DeleteQuery extends Query
{
    /** @psalm-mutation-free */
    public function from(string $table): self
    {
        return $this->withAddedData([Delete::FROM => $table]);
    }

    /** @psalm-mutation-free */
    public function where(?ExprInterface $expr): self
    {
        return $this->withAddedData([Delete::WHERE => $expr]);
    }

    /** @psalm-mutation-free */
    public function orderBy(array $order): self
    {
        return $this->withAddedData([Delete::ORDER => $order]);
    }

    /** @psalm-mutation-free */
    public function limit(?int $limit): self
    {
        return $this->withAddedData([Delete::LIMIT => $limit]);
    }
}
