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

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function getIsNullable(): bool
    {
        return $this->isNullable;
    }

    public function getIsAutoInc(): bool
    {
        return $this->autoInc;
    }

    /** @return static */
    public function type(string $type): self
    {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    /** @return static */
    public function default(?string $defaultVal): self
    {
        $clone = clone $this;
        $clone->defaultValue = $defaultVal;
        return $clone;
    }

    /** @return static */
    public function nullable(bool $isNullable = true): self
    {
        $clone = clone $this;
        $clone->isNullable = $isNullable;
        return $clone;
    }

    /** @return static */
    public function autoInc(bool $autoInc = true): self
    {
        $clone = clone $this;
        $clone->autoInc = $autoInc;
        return $clone;
    }

    /** Helper static method to aid fluent creation of columns. */
    public static function ofType(string $type): self
    {
        return new self($type);
    }
}
