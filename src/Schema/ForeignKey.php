<?php

namespace RebelCode\Atlas\Schema;

class ForeignKey extends Key
{
    public const SET_NULL = 'SET NULL';
    public const SET_DEFAULT = 'SET DEFAULT';
    public const CASCADE = 'CASCADE';
    public const RESTRICT = 'RESTRICT';
    protected string $table;
    /** @var array<string,string> */
    protected array $mappings;
    /** @psalm-var ForeignKey::* */
    protected string $updateRule;
    /** @psalm-var ForeignKey::* */
    protected string $deleteRule;

    /**
     * Constructor.
     *
     * @param string $foreignTable The name of the foreign table.
     * @param array<string,string> $mappings A mapping of column names to foreign column names.
     * @param string|null $updateRule Optional update rule. Defaults to {@link ForeignKey::RESTRICT}
     * @param string|null $deleteRule Optional delete rule. Defaults to {@link ForeignKey::RESTRICT}
     *
     * @psalm-param ForeignKey::*|null $updateRule
     * @psalm-param ForeignKey::*|null $deleteRule
     */
    public function __construct(
        string $foreignTable,
        array $mappings,
        ?string $updateRule = self::RESTRICT,
        ?string $deleteRule = self::RESTRICT
    ) {
        $this->table = $foreignTable;
        $this->mappings = $mappings;
        $this->updateRule = $updateRule ?? self::RESTRICT;
        $this->deleteRule = $deleteRule ?? self::RESTRICT;
    }

    /** @return string */
    public function getTable(): string
    {
        return $this->table;
    }

    /** @return array<string,string> */
    public function getMappings(): array
    {
        return $this->mappings;
    }

    /** @psalm-return ForeignKey::* */
    public function getUpdateRule(): string
    {
        return $this->updateRule;
    }

    /** @psalm-return ForeignKey::* */
    public function getDeleteRule(): string
    {
        return $this->deleteRule;
    }

    /**
     * Creates a copy with an update rule.
     *
     * @param string|null $updateRule Optional update rule. Defaults to {@link ForeignKey::RESTRICT}
     * @return static The new instance.
     *
     * @psalm-param ForeignKey::*|null $updateRule
     */
    public function onUpdate(?string $updateRule): self
    {
        $new = clone $this;
        $new->updateRule = $updateRule ?? self::RESTRICT;
        return $new;
    }

    /**
     * Creates a copy with a delete rule.
     *
     * @param string|null $deleteRule Optional delete rule. Defaults to {@link ForeignKey::RESTRICT}
     * @return static The new instance.
     *
     * @psalm-param ForeignKey::*|null $deleteRule
     */
    public function onDelete(?string $deleteRule): self
    {
        $new = clone $this;
        $new->deleteRule = $deleteRule ?? self::RESTRICT;
        return $new;
    }

    /** @inheritDoc */
    public function toSql(string $name): string
    {
        $tableColumns = implode('`, `', array_keys($this->mappings));
        $foreignColumns = implode('`, `', array_values($this->mappings));

        $result = "CONSTRAINT `$name` FOREIGN KEY (`$tableColumns`) REFERENCES `$this->table` (`$foreignColumns`)";

        if ($this->updateRule !== ForeignKey::RESTRICT) {
            $result .= " ON UPDATE " . $this->updateRule;
        }

        if ($this->deleteRule !== ForeignKey::RESTRICT) {
            $result .= ' ON DELETE ' . $this->deleteRule;
        }

        return $result;
    }
}
