<?php

namespace RebelCode\Atlas\Schema;

/** @psalm-immutable */
class ForeignKey
{
    public const SET_NULL = 'SET NULL';
    public const SET_DEFAULT = 'SET DEFAULT';
    public const CASCADE = 'CASCADE';
    public const RESTRICT = 'RESTRICT';

    protected string $foreignTable;
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
        $this->foreignTable = $foreignTable;
        $this->mappings = $mappings;
        $this->updateRule = $updateRule ?? self::RESTRICT;
        $this->deleteRule = $deleteRule ?? self::RESTRICT;
    }

    /** @return string */
    public function getForeignTable(): string
    {
        return $this->foreignTable;
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
}
