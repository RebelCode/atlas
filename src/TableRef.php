<?php

namespace RebelCode\Atlas;

/**
 * A data source that refers to a table by name. This is useful when the {@link Table} class cannot be obtained.
 *
 * @psalm-immutable
 */
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
