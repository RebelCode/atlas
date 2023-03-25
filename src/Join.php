<?php

namespace RebelCode\Atlas;

use RebelCode\Atlas\Expression\ExprInterface;

/** @psalm-immutable */
class Join
{
    const INNER = 'INNER';
    const CROSS = 'CROSS';
    const STRAIGHT = 'STRAIGHT';
    const LEFT = 'LEFT';
    const RIGHT = 'RIGHT';
    const NATURAL_LEFT = 'NATURAL LEFT';
    const NATURAL_RIGHT = 'NATURAL RIGHT';
    protected string $type;
    protected DataSource $table;
    protected ?ExprInterface $condition;

    /**
     * Constructor.
     *
     * @param string $type The join type.
     * @param DataSource $table The data source to join with.
     * @param ExprInterface|null $condition The join condition.
     */
    public function __construct(string $type, DataSource $table, ?ExprInterface $condition = null)
    {
        $this->type = $type;
        $this->table = $table;
        $this->condition = $condition;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTable(): DataSource
    {
        return $this->table;
    }

    public function getCondition(): ?ExprInterface
    {
        return $this->condition;
    }

    public function type(string $type): self
    {
        $new = clone $this;
        $new->type = $type;
        return $new;
    }

    public function with(DataSource $table): self
    {
        $new = clone $this;
        $new->table = $table;
        return $new;
    }

    public function on(?ExprInterface $on): self
    {
        $new = clone $this;
        $new->condition = $on;
        return $new;
    }

    public function toSql(): string
    {
        $result = $this->type . ' JOIN ' . $this->table->compileSource();

        if ($this->condition !== null) {
            $result .= ' ON ' . $this->condition->toSql();
        }

        return $result;
    }
}
