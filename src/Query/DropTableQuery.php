<?php

namespace RebelCode\Atlas\Query;

use DomainException;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\QueryCompileException;
use Throwable;

class DropTableQuery extends Query
{
    /** @var string */
    protected $table;
    /** @var bool */
    protected $ifExists;
    /** @var bool */
    protected $cascade;

    /**
     * Constructor.
     *
     * @param DatabaseAdapter|null $adapter The database adapter to execute the query with.
     * @param string $table The name of the table to drop.
     * @param bool $ifExists Whether to only drop the table if it exists.
     * @param bool $cascade Whether to cascade the drop.
     */
    public function __construct(
        ?DatabaseAdapter $adapter,
        string $table,
        bool $ifExists = false,
        bool $cascade = false
    ) {
        parent::__construct($adapter);
        $this->table = $table;
        $this->ifExists = $ifExists;
        $this->cascade = $cascade;
    }

    /**
     * @inheritDoc
     * @psalm-mutation-free
     */
    public function compile(): string
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
            throw new QueryCompileException('Cannot compile DROP TABLE query - ' . $e->getMessage(), $this, $e);
        }
    }

    /**
     * @inheritDoc
     *
     * @return bool True if the table was dropped, false if not.
     */
    public function exec(): bool
    {
        return $this->getAdapter()->query($this->compile());
    }
}
