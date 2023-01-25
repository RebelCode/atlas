<?php

namespace RebelCode\Atlas\Query;

use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Group;
use RebelCode\Atlas\Order;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\QueryType\Select;

class SelectQuery extends Query
{
    /**
     * @param string|mixed $from
     * @psalm-immutable
     */
    public function from($from): self
    {
        return $this->withAddedData([Select::FROM => $from]);
    }

    /** @psalm-immutable */
    public function columns(array $columns): self
    {
        return $this->withAddedData([Select::COLUMNS => $columns]);
    }

    /** @psalm-immutable */
    public function where(?ExprInterface $expr): self
    {
        return $this->withAddedData([Select::WHERE => $expr]);
    }

    /**
     * @param Group[] $groups
     * @psalm-immutable
     */
    public function groupBy(array $groups): self
    {
        return $this->withAddedData([Select::GROUP => $groups]);
    }

    /** @psalm-immutable */
    public function having(?ExprInterface $expr): self
    {
        return $this->withAddedData([Select::HAVING => $expr]);
    }

    /**
     * @param Order[] $order
     * @psalm-immutable
     */
    public function orderBy(array $order): self
    {
        return $this->withAddedData([Select::ORDER => $order]);
    }

    /** @psalm-immutable */
    public function limit(?int $limit): self
    {
        return $this->withAddedData([Select::LIMIT => $limit]);
    }

    /** @psalm-immutable */
    public function offset(?int $offset): self
    {
        return $this->withAddedData([Select::OFFSET => $offset]);
    }
}
