<?php

namespace RebelCode\Atlas\Expression;

/**
 * An expression that represents a column name.
 *
 * @psalm-immutable
 */
class ColumnTerm extends BaseExpr
{
    protected ?string $table;
    protected string $column;
    protected bool $distinct;

    /**
     * Constructor.
     *
     * @param string|null $table Optional table name.
     * @param string $column The column name.
     * @param bool $distinct Whether the column is distinct.
     */
    public function __construct(?string $table, string $column, bool $distinct = false)
    {
        $this->table = $table;
        $this->column = $column;
        $this->distinct = $distinct;
    }

    public function distinct(bool $distinct = true): self
    {
        $clone = clone $this;
        $clone->distinct = $distinct;
        return $clone;
    }

    /** @inheritDoc */
    protected function toBaseString(): string
    {
        $result = $this->table !== null
            ? "`{$this->table}`.`{$this->column}`"
            : "`$this->column`";

        if ($this->distinct) {
            $result = "DISTINCT $result";
        }

        return $result;
    }
}
