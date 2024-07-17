<?php

namespace RebelCode\Atlas\Query\Traits;

use RebelCode\Atlas\Group;

trait HasGroupByTrait
{
    /** @var Group[] */
    protected array $groups = [];

    /**
     * Creates a copy with a new GROUP BY clause.
     *
     * @param Group[] $groups An array of {@link Group} instances.
     * @return static The new instance.
     */
    public function groupBy(array $groups): self
    {
        $new = clone $this;
        $new->groups = $groups;
        return $new;
    }

    /**
     * Compiles the GROUP BY fragment of an SQL query.
     *
     * @return string
     */
    protected function compileGroupBy(): string
    {
        if (empty($this->groups)) {
            return '';
        }

        $groupParts = [];
        foreach ($this->groups as $group) {
            $col = $group->getColumn();
            $sort = $group->getSort();
            if ($sort === null) {
                $groupParts[] = $col;
            } else {
                $groupParts[] = "$col $sort";
            }
        }

        return 'GROUP BY ' . implode(', ', $groupParts);
    }
}
