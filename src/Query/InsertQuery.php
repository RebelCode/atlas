<?php

namespace RebelCode\Atlas\Query;

use DomainException;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\QuerySqlException;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\Query;
use Throwable;

class InsertQuery extends Query
{
    use Query\Traits\HasAssignmentTrait {
        assign as onDuplicateKey;
    }

    protected string $into;
    protected bool $replace;
    /** @var string[] */
    protected array $columns;
    /** @var list<array<string,mixed>> */
    protected array $values;

    /**
     * Constructor.
     *
     * @param DatabaseAdapter|null $adapter Optional database adapter.
     * @param string $into The table to insert into.
     * @param string[] $columns The columns to insert into.
     * @param list<array<string,mixed>> $values A list of associative arrays, each representing a row to be inserted.
     * @param array<string,mixed> $assign Optional assignment list to use in the "ON DUPLICATE KEY" clause.
     */
    public function __construct(
        ?DatabaseAdapter $adapter = null,
        string $into = '',
        array $columns = [],
        array $values = [],
        array $assign = [],
        bool $replace = false
    ) {
        parent::__construct($adapter);
        $this->into = $into;
        $this->columns = $columns;
        $this->values = $values;
        $this->assign = $assign;
        $this->replace = $replace;
    }

    /**
     * Creates a copy with a new table to insert into.
     *
     * @param string $table The table to insert into.
     * @return self The new instance.
     */
    public function into(string $table): self
    {
        $new = clone $this;
        $new->into = $table;
        return $new;
    }

    /**
     * Creates a copy with new columns to insert into.
     *
     * @param string[] $columns The columns to insert into.
     * @return self The new instance.
     */
    public function columns(array $columns): self
    {
        $new = clone $this;
        $new->columns = $columns;
        return $new;
    }

    /**
     * Creates a copy with new values to insert.
     *
     * @param array<string,mixed>[] $values The values to insert.
     * @return self The new instance.
     */
    public function values(array $values): self
    {
        $new = clone $this;
        $new->values = $values;
        return $new;
    }

    /**
     * Changes the INSERT query to a REPLACE query, or vice-versa.
     *
     * @param bool $replace Whether to use REPLACE instead of INSERT.
     * @return self The new instance.
     */
    public function replace(bool $replace = true): self
    {
        $new = clone $this;
        $new->replace = $replace;
        return $new;
    }

    /** @inheritDoc */
    public function toSql(): string
    {
        try {
            $table = trim($this->into);
            if (empty($table)) {
                throw new DomainException('Table name is missing');
            }

            $columnsStr = count($this->columns) > 0
                ? '`' . implode('`, `', $this->columns) . '`'
                : '';

            $valuesStr = $this->compileInsertValues();
            $onDupeKey = $this->compileAssignment('UPDATE');

            $type = $this->replace ? 'REPLACE' : 'INSERT';
            $result = "$type INTO `$table` ($columnsStr) VALUES $valuesStr";

            if (!empty($onDupeKey)) {
                $result .= ' ON DUPLICATE KEY ' . $onDupeKey;
            }

            return $result;
        } catch (Throwable $e) {
            throw new QuerySqlException('Cannot compile INSERT query - ' . $e->getMessage(), $this, $e);
        }
    }

    /**
     * Compiles the VALUES fragment of the INSERT query.
     *
     * @return string
     */
    protected function compileInsertValues(): string
    {
        $values = [];
        foreach ($this->values as $record) {
            $row = [];
            foreach ($record as $value) {
                $row[] = Term::create($value)->toSql();
            }

            $values[] = '(' . implode(', ', $row) . ')';
        }

        return implode(', ', $values);
    }

    /**
     * @inheritDoc
     *
     * @return int|null The last inserted ID, or null if no rows were inserted.
     */
    public function exec(): ?int
    {
        $adapter = $this->getAdapter();
        $numRows = $adapter->queryNumRows($this->toSql());

        if ($numRows > 0) {
            return $adapter->getInsertId();
        } else {
            return null;
        }
    }
}
