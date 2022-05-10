<?php

namespace RebelCode\Atlas\Schema;

/** @psalm-immutable */
class Column
{
    /** @var string */
    protected $type;

    /** @var string|null */
    protected $defaultValue;

    /** @var bool */
    protected $isNullable;

    /** @var bool */
    protected $autoInc;

    /**
     * Construct a new column.
     *
     * @param string $type The data type for the column.
     * @param string|null $defaultVal Optional default value.
     * @param bool $isNullable Whether values in the column can be NULL.
     * @param bool $autoInc Whether values in the column auto increments.
     */
    public function __construct(
        string $type,
        ?string $defaultVal = null,
        bool $isNullable = true,
        bool $autoInc = false
    ) {
        $this->type = $type;
        $this->defaultValue = $defaultVal;
        $this->isNullable = $isNullable;
        $this->autoInc = $autoInc;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /** @return static */
    public function withType(string $type): self
    {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    /** @return static */
    public function withDefaultVal(?string $defaultVal): self
    {
        $clone = clone $this;
        $clone->defaultValue = $defaultVal;
        return $clone;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    /** @return static */
    public function withIsNullable(bool $isNullable): self
    {
        $clone = clone $this;
        $clone->isNullable = $isNullable;
        return $clone;
    }

    public function isAutoInc(): bool
    {
        return $this->autoInc;
    }

    /** @return static */
    public function withAutoInc(bool $autoInc): self
    {
        $clone = clone $this;
        $clone->autoInc = $autoInc;
        return $clone;
    }
}
