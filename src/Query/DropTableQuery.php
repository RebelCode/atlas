<?php

namespace RebelCode\Atlas\Query;

use DomainException;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\QuerySqlException;
use RebelCode\Atlas\Query;
use Throwable;

class DropTableQuery extends Query
{
    protected string $table;
    protected bool $ifExists;
    protected bool $cascade;

    /**
     * Constructor.
     *
     * @param DatabaseAdapter|null $adapter The database adapter to execute the query with.
     * @param string $from The name of the table to drop.
     * @param bool $ifExists Whether to only drop the table if it exists.
     * @param bool $cascade Whether to cascade the drop.
     */
    public function __construct(
        ?DatabaseAdapter $adapter = null,
        string $from = '',
        bool $ifExists = false,
        bool $cascade = false
    ) {
        parent::__construct($adapter);
        $this->table = $from;
        $this->ifExists = $ifExists;
        $this->cascade = $cascade;
    }

    /** @inheritDoc */
    public function toSql(): string
    {
        try {
            $table = trim($this->table);
            if (empty($table)) {
                throw new DomainException('Table name is missing');
            }

            $result = 'DROP TABLE';
            if ($this->ifExists) {
                $result .= ' IF EXISTS';
            }

            $result .= " `$table`";

            if ($this->cascade) {
                $result .= ' CASCADE';
            }

            return $result;
        } catch (Throwable $e) {
            throw new QuerySqlException('Cannot compile DROP TABLE query - ' . $e->getMessage(), $this, $e);
        }
    }

    /**
     * @inheritDoc
     *
     * @return bool True if the table was dropped, false if not.
     */
    public function exec(array $args = []): bool
    {
        [$sql, $values] = $this->templateVars($this->toSql(), $args);

        return $this->getAdapter()->query($sql, $values);
    }
}
