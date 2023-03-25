<?php

namespace RebelCode\Atlas\Query\Traits;

use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;

trait HasSelectColumnListTrait
{
    /** @var array<string|Term> */
    protected array $columns = [];

    /**
     * Creates a copy with a different column selection.
     *
     * @psalm-immutable
     * @param array<string|Term> $columns The new column selection.
     * @return static The new instance.
     */
    public function columns(array $columns): self
    {
        $new = clone $this;
        $new->columns = $columns;
        return $new;
    }

    /**
     * Compiles the list of columns.
     *
     * @psalm-mutation-free
     * @return string
     */
    protected function compileColumnList(): string
    {
        if (empty($this->columns)) {
            return '*';
        }

        $list = [];
        foreach ($this->columns as $key => $value) {
            if ($value === '*') {
                $list[] = '*';
            } else {
                $expr = ($value instanceof ExprInterface)
                    ? $value
                    : new Term(Term::COLUMN, $value);

                $list[] = $expr->toSql() . (is_numeric($key) ? '' : " AS `$key`");
            }
        }

        return implode(', ', $list);
    }
}
