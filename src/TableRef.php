<?php

namespace RebelCode\Atlas;

use RebelCode\Atlas\Expression\ColumnTerm;

/** A data source that refers to a table by name. This is useful when the {@link Table} instance cannot be obtained. */
class TableRef implements DataSource
{
    protected string $name;
    protected ?string $alias;

    /**
     * Constructor.
     *
     * @param string $name The table name.
     * @param string|null $alias Optional alias name.
     */
    public function __construct(string $name, ?string $alias = null)
    {
        $this->name = $name;
        $this->alias = $alias;
    }

    /** @inheritDoc */
    public function as(?string $alias): DataSource
    {
        $clone = clone $this;
        $clone->alias = $alias;
        return $clone;
    }

    /**
     * Creates a column term for a column using the table ref's name or alias.
     *
     * Note: the table alias is prioritized over the name.
     *
     * @param string $col The column name.
     * @return ColumnTerm The created column term.
     */
    public function col(string $col): ColumnTerm
    {
        return new ColumnTerm($this->alias ?? $this->name, $col);
    }

    /**
     * Magic getter alias for {@link TableRef::col()}.
     *
     * @param string $name The column name.
     * @return ColumnTerm The created column term.
     */
    public function __get(string $name): ColumnTerm
    {
        return $this->col($name);
    }

    /** Retrieves the table name.  */
    public function getName(): string
    {
        return $this->name;
    }

    /** @inheritDoc */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /** @inheritDoc */
    public function compileSource(): string
    {
        $result = "`$this->name`";
        if ($this->alias !== null) {
            return "$result AS `$this->alias`";
        } else {
            return $result;
        }
    }
}
