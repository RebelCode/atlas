<?php

namespace RebelCode\Atlas\Query\Traits;

use RebelCode\Atlas\Join;

trait HasJoinsTrait
{
    /** @var Join[] */
    protected array $joins = [];

    /**
     * Creates a copy with a new JOIN clauses.
     *
     * @param Join[] $joins A list of joins.
     * @return static
     */
    public function join(array $joins): self
    {
        $new = clone $this;
        $new->joins = $joins;
        return $new;
    }

    /** Compiles the JOIN clauses into an SQL fragment. */
    protected function compileJoins(): string
    {
        if (empty($this->joins)) {
            return '';
        } else {
            $joinSql = [];
            foreach ($this->joins as $join) {
                $joinSql[] = $join->toSql();
            }

            return implode(' ', $joinSql);
        }
    }
}
