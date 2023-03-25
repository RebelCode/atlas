<?php

namespace RebelCode\Atlas\Schema;

abstract class Key
{
    /**
     * Converts the key into an SQL fragment string.
     *
     * @psalm-mutation-free
     * @param string $name The name of the key.
     * @return string The SQL fragment string.
     */
    abstract public function toSql(string $name): string;

    /**
     * Creates a unique key.
     *
     * @param string[] $columns The columns that make up the unique key.
     * @return UniqueKey The created unique key.
     */
    public static function unique(array $columns): UniqueKey
    {
        return new UniqueKey($columns);
    }

    /**
     * Creates a primary key.
     *
     * @param string[] $columns The columns that make up the primary key.
     * @return PrimaryKey The created primary key.
     */
    public static function primary(array $columns): PrimaryKey
    {
        return new PrimaryKey($columns);
    }

    /**
     * Creates a foreign key.
     *
     * @param string $table The name of the foreign table.
     * @param array<string,string> $columns A mapping of local column names to foreign column names.
     * @return ForeignKey The created foreign key.
     */
    public static function foreign(string $table, array $columns): ForeignKey
    {
        return new ForeignKey($table, $columns);
    }
}
