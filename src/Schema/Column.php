<?php

namespace RebelCode\Atlas\Schema;

use RebelCode\Atlas\Expression\ExprInterface;
use RebelCode\Atlas\Expression\Term;

/** @psalm-immutable */
class Column
{
    protected string $type;
    protected ?ExprInterface $defaultValue;
    protected bool $isNullable;
    protected bool $autoInc;

    /**
     * Construct a new column.
     *
     * @param string $type The data type for the column.
     * @param Term|mixed|null $defaultVal Optional default value.
     * @param bool $isNullable Whether values in the column can be NULL.
     * @param bool $autoInc Whether values in the column auto increments.
     */
    public function __construct(
        string $type,
        $defaultVal = null,
        bool $isNullable = true,
        bool $autoInc = false
    ) {
        $this->type = $type;
        $this->defaultValue = $defaultVal !== null ? Term::create($defaultVal) : null;
        $this->isNullable = $isNullable;
        $this->autoInc = $autoInc;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDefaultValue(): ?ExprInterface
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

    /**
     * @param Term|mixed|null $defaultVal
     * @return static
     */
    public function default($defaultVal): self
    {
        $clone = clone $this;
        $clone->defaultValue = $defaultVal !== null ? Term::create($defaultVal) : null;
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

    public function toSql(string $name): string
    {
        $parts = ["`$name`", $this->type];

        if ($this->defaultValue !== null) {
            $parts[] = "DEFAULT " . $this->defaultValue->toSql();
        } else {
            $parts[] = $this->isNullable ? 'NULL' : 'NOT NULL';
        }

        if ($this->autoInc) {
            $parts[] = 'AUTO_INCREMENT';
        }

        return implode(' ', $parts);
    }

    /** Helper static method to aid fluent creation of columns. */
    public static function ofType(string $type): self
    {
        return new self($type);
    }
}
