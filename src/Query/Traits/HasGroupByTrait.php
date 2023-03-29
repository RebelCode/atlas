<?php

namespace RebelCode\Atlas\Query\Traits;

use InvalidArgumentException;
use RebelCode\Atlas\Group;

/** @psalm-immutable */
trait HasGroupByTrait
{
    /** @var Group[] */
    protected array $groups = [];

    /**
     * Creates a copy with a new GROUP BY clause.
     *
     * @psalm-immutable
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
     * @psalm-mutation-free
     * @return string
     */
    protected function compileGroupBy(): string
    {
        if (empty($this->groups)) {
            return '';
        }

        $groupParts = [];
        foreach ($this->groups as $group) {
            $groupParts[] = "`{$group->getColumn()}` {$group->getSort()}";
        }

        return 'GROUP BY ' . implode(', ', $groupParts);
    }
}
