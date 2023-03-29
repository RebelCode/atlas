<?php

namespace RebelCode\Atlas\Query\Traits;

use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;

/** @psalm-immutable */
trait HasAssignmentTrait
{
    /** @var array<string,scalar|ExprInterface> */
    protected array $assign;

    /**
     * Creates a copy with a different assign list.
     *
     * @psalm-immutable
     * @param array<string,mixed|ExprInterface> $assign An assoc array that maps column names to the update values.
     * @return static The new instance.
     */
    public function assign(array $assign): self
    {
        $new = clone $this;
        $new->assign = $assign;
        return $new;
    }

    /**
     * Compiles an assignment list. Used by "UPDATE" and "INSERT ... ON DUPLICATE KEY UPDATE" queries.
     *
     * @psalm-mutation-free
     * @param string $prefix The prefix for the compiled fragment. Typically, either SET or UPDATE.
     * @return string
     */
    protected function compileAssignment(string $prefix): string
    {
        if (empty($this->assign)) {
            return '';
        }

        $list = [];
        foreach ($this->assign as $col => $value) {
            $list[] = "`$col` = " . Term::create($value)->toSql();
        }

        return $prefix . ' ' . implode(', ', $list);
    }
}
