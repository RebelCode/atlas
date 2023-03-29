<?php

namespace RebelCode\Atlas;

use RebelCode\Atlas\Expression\ColumnTerm;

/** @psalm-immutable */
class Order
{
    public const ASC = 'ASC';
    public const DESC = 'DESC';

    protected string $column;
    /** @psalm-var Order::* */
    protected string $sort;

    /**
     * Constructor.
     *
     * @param string|ColumnTerm $column The column to sort by.
     * @param string $sort The sort order.
     *
     * @psalm-param Order::* $sort
     */
    public function __construct($column, string $sort = self::ASC)
    {
        $this->column = $column instanceof ColumnTerm ? $column->getName() : $column;
        $this->sort = $sort;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    /** @psalm-return Order::* */
    public function getSort(): string
    {
        return $this->sort;
    }

    public function asc(): Order
    {
        return ($this->sort === self::DESC)
            ? new self($this->column, self::ASC)
            : $this;
    }

    public function desc(): Order
    {
        return ($this->sort === self::ASC)
            ? new self($this->column, self::DESC)
            : $this;
    }

    public static function by(string $column): Order
    {
        return new self($column);
    }
}
