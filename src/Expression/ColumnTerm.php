<?php

namespace RebelCode\Atlas\Expression;

/** An expression that represents a column name. */
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

    /** Retrieves the table name, if any. */
    public function getTable(): ?string
    {
        return $this->table;
    }

    /** Retrieves the column name. */
    public function getName(): string
    {
        return $this->column;
    }

    /**
     * Changes whether the column is distinct.
     *
     * @param bool $distinct True to make the column distinct, false to make it non-distinct. Defaults to true.
     * @return ColumnTerm The new column term instance.
     */
    public function distinct(bool $distinct = true): self
    {
        $clone = clone $this;
        $clone->distinct = $distinct;
        return $clone;
    }

    /** @inheritDoc */
    protected function toBaseString(): string
    {
        $colName = $this->column === '*'
            ? '*'
            : "`{$this->column}`";

        $result = $this->table !== null
            ? "`{$this->table}`.{$colName}"
            : "{$colName}";

        if ($this->distinct) {
            $result = "DISTINCT $result";
        }

        return $result;
    }
}
