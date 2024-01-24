<?php

namespace RebelCode\Atlas\Query;

use RebelCode\Atlas\Query;

/**
 * A query wrapper for multiple queries.
 *
 * This is intended to allow multiple queries to be executed in a single call to the database adapter. Rendering the
 * queries to SQL will concatenate the SQL of each query and a semicolon after each query.
 */
class CompoundQuery extends Query
{
    /** @var Query[] */
    protected array $queries;

    /**
     * Constructor.
     *
     * @param Query[] $queries The queries to execute.
     */
    public function __construct(array $queries)
    {
        parent::__construct(null);
        $this->queries = $queries;
    }

    /**
     * Retrieves the queries.
     *
     * @return Query[]
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /** @inheritDoc */
    public function toSql(): string
    {
        $list = [];
        foreach ($this->queries as $query) {
            $list[] = $query->toSql() . ';';
        }

        return implode("\n", $list);
    }

    /** @inheritDoc */
    public function exec()
    {
        $results = [];
        foreach ($this->queries as $query) {
            $results[] = $query->exec();
        }

        return $results;
    }
}
