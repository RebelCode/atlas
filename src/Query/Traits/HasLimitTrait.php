<?php

namespace RebelCode\Atlas\Query\Traits;

trait HasLimitTrait
{
    protected ?int $limit = null;

    /**
     * Creates a copy with a new limit.
     *
     * @param int|null $limit The limit.
     * @return static The new instance.
     */
    public function limit(?int $limit): self
    {
        $new = clone $this;
        $new->limit = $limit;
        return $new;
    }

    /**
     * Compiles the LIMIT fragment of an SQL query.
     *
     * @return string
     */
    protected function compileLimit(): string
    {
        if ($this->limit !== null) {
            return 'LIMIT ' . (string) $this->limit;
        } else {
            return '';
        }
    }
}
