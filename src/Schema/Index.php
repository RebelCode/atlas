<?php

namespace RebelCode\Atlas\Schema;

use RebelCode\Atlas\Order;

/** @psalm-immutable */
class Index
{
    protected bool $isUnique;
    /**
     * @var array<string,string|null>
     * @psalm-var array<string, Order::*|null>
     */
    protected array $columns;

    /**
     * Constructor.
     *
     * @param bool $isUnique Whether the index is unique.
     * @param array<string, string|null> $columns A mapping of column names to their respective sorting. The sorting
     *                                            should be one of the constants in {@link Order}. Null values are
     *                                            interpreted as ascending sorting.
     *
     * @psalm-param array<string, Order::*|null> $columns
     */
    public function __construct(bool $isUnique, array $columns)
    {
        $this->isUnique = $isUnique;
        $this->columns = $columns;
    }

    /** @return bool */
    public function isUnique(): bool
    {
        return $this->isUnique;
    }

    /**
     * @return array<string, string|null>
     * @psalm-return array<string, Order::*|null>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Creates a copy of the index that is unique or non-unique.
     *
     * @param bool $isUnique Whether the index should be unique. Defaults to true.
     * @return static The created instance.
     */
    public function unique(bool $isUnique = true): self
    {
        $clone = clone $this;
        $clone->isUnique = $isUnique;
        return $clone;
    }

    /**
     * Static constructor.
     *
     * @param array<string,string|null> $columns A mapping of column names to their respective sorting. The sorting
     *                                           should be one of the constants in {@link Order}. Null values are
     *                                           interpreted as ascending sorting.
     * @psalm-param array<string, Order::*|null> $columns
     * @return Index
     */
    public static function columns(array $columns): self
    {
        return new self(false, $columns);
    }
}
