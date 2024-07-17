<?php

namespace RebelCode\Atlas;

use RebelCode\Atlas\Expression\ColumnTerm;

class Group
{
    public const ASC = 'ASC';
    public const DESC = 'DESC';

    protected ColumnTerm $column;
    protected ?string $sort;

    /**
     * Constructor.
     *
     * @param string|ColumnTerm $column The column to sort by.
     * @param string|null $sort The sort order.
     */
    public function __construct($column, ?string $sort = null)
    {
        $this->column = $column instanceof ColumnTerm ? $column : new ColumnTerm(null, $column);
        $this->sort = $sort;
    }

    public function getColumn(): ColumnTerm
    {
        return $this->column;
    }

    public function getSort(): ?string
    {
        return $this->sort;
    }

    public function asc(): Group
    {
        return ($this->sort !== self::ASC)
            ? new self($this->column, self::ASC)
            : $this;
    }

    public function desc(): Group
    {
        return ($this->sort !== self::DESC)
            ? new self($this->column, self::DESC)
            : $this;
    }

    public function noSort(): Group
    {
        return ($this->sort !== null)
            ? new self($this->column, null)
            : $this;
    }

    public function dir(?string $order, string $default = self::ASC): Group
    {
        if ($order !== null) {
            $order = strtoupper($order);
            $order = ($order === self::ASC || $order === self::DESC) ? $order : $default;
        }
        return new self($this->column, $order);
    }

    public static function by(string $column): Group
    {
        return new self($column);
    }
}
