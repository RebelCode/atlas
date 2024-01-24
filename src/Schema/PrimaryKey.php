<?php

namespace RebelCode\Atlas\Schema;

class PrimaryKey extends Key
{
    /** @var string[] */
    protected array $columns;

    /**
     * Constructor.
     *
     * @param string[] $columns The columns that make up the primary key.
     */
    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    /** @inheritDoc */
    public function toSql(string $name): string
    {
        $columns = implode('`, `', $this->columns);
        return "CONSTRAINT `$name` PRIMARY KEY (`$columns`)";
    }
}
