<?php

namespace RebelCode\Atlas\Query\Traits;

use RebelCode\Atlas\Expression\ExprInterface;

trait HasHavingTrait
{
    protected ?ExprInterface $having = null;

    /**
     * Creates a copy with a new HAVING condition.
     *
     * @psalm-immutable
     * @param ExprInterface|null $having The new HAVING condition.
     * @return static The new instance.
     */
    public function having(?ExprInterface $expr): self
    {
        $new = clone $this;
        $new->having = $expr;
        return $new;
    }

    /**
     * Compiles the HAVING fragment of an SQL query.
     *
     * @psalm-mutation-free
     * @return string
     */
    protected function compileHaving(): string
    {
        if ($this->having !== null) {
            return 'HAVING ' . $this->having->toSql();
        } else {
            return '';
        }
    }
}
