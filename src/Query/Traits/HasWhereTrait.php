<?php

namespace RebelCode\Atlas\Query\Traits;

use RebelCode\Atlas\Expression\ExprInterface;

trait HasWhereTrait
{
    protected ?ExprInterface $where = null;

    /**
     * Creates a copy with a new WHERE condition.
     *
     * @param ExprInterface|null $where The new WHERE condition, or null for no condition.
     * @return static The new instance.
     */
    public function where(?ExprInterface $where): self
    {
        $new = clone $this;
        $new->where = $where;
        return $new;
    }

    /**
     * Compiles the WHERE fragment of an SQL query.
     *
     * @return string
     */
    protected function compileWhere(): string
    {
        if ($this->where !== null) {
            return 'WHERE ' . $this->where->toSql();
        } else {
            return '';
        }
    }
}
