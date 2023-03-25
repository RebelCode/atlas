<?php

namespace RebelCode\Atlas\Query\Traits;

trait HasOffsetTrait
{
    protected ?int $offset = null;

    /**
     * Creates a copy with a new selection offset.
     *
     * @psalm-immutable
     * @param int|null $offset The new selection offset, or null or zero for no offset.
     * @return static The new instance.
     */
    public function offset(?int $offset): self
    {
        $new = clone $this;
        $new->offset = $offset;
        return $new;
    }

    /**
     * Compiles the OFFSET fragment of an SQL query.
     *
     * @psalm-mutation-free
     * @return string
     */
    protected function compileOffset(): string
    {
        if ($this->offset !== null) {
            return 'OFFSET ' . (string) $this->offset;
        } else {
            return '';
        }
    }
}
