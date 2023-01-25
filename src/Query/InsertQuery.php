<?php

namespace RebelCode\Atlas\Query;

use DomainException;
use InvalidArgumentException;
use RebelCode\Atlas\Query;
use RebelCode\Atlas\DatabaseAdapter;
use RebelCode\Atlas\Exception\QueryCompileException;
use RebelCode\Atlas\Expression\Term;
use RebelCode\Atlas\QueryCompiler;
use Throwable;
use UnexpectedValueException;

class InsertQuery extends Query
{
    /** @var string */
    protected $into;
    /** @var string[] */
    protected $columns;
    /** @var array<string,mixed>[] */
    protected $values;
    /** @var array<string, mixed> */
    protected $assignList;

    /**
     * Constructor.
     *
     * @param DatabaseAdapter|null $adapter Optional database adapter.
     * @param string $into The table to insert into.
     * @param string[] $columns The columns to insert into.
     * @param array<string,mixed>[] $values A list of associative arrays, each representing a row to be inserted.
     * @param array<string, mixed> $assignList Optional assignment list to use in the "ON DUPLICATE KEY" clause.
     */
    public function __construct(
        ?DatabaseAdapter $adapter = null,
        string $into = '',
        array $columns = [],
        array $values = [],
        array $assignList = []
    ) {
        parent::__construct($adapter);
        $this->into = $into;
        $this->columns = $columns;
        $this->values = $values;
        $this->assignList = $assignList;
    }

    /**
     * Creates a copy with a new table to insert into.
     *
     * @psalm-mutation-free
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
     * @psalm-mutation-free
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
     * @psalm-mutation-free
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
     * Creates a copy with a new assignment list to use in the "ON DUPLICATE KEY" clause.
     *
     * @psalm-mutation-free
     * @param array<string, mixed> $assignList The assignment list, as a map of column names to values.
     * @return self The new instance.
     */
    public function onDuplicateKey(array $assignList): self
    {
        $new = clone $this;
        $new->assignList = $assignList;
        return $new;
    }

    /**
     * @inheritDoc
     * @psalm-mutation-free
     */
    public function compile(): string
    {
        try {
            $table = trim($this->into);
            if (empty($table)) {
                throw new DomainException('Table name is missing');
            }

            $columnsStr = QueryCompiler::compileColumnList($this->columns);
            if (empty($columnsStr)) {
                throw new UnexpectedValueException('Column list cannot be empty');
            }

            $numColumns = count($this->columns);
            $valuesStr = self::compileInsertValues($this->values, $numColumns);

            $result = "INSERT INTO `$table` ({$columnsStr}) VALUES {$valuesStr}";

            if (!empty($this->assignList)) {
                $result .= ' ON DUPLICATE KEY ' . QueryCompiler::compileAssignmentList('UPDATE', $this->assignList);
            }

            return $result;
        } catch (Throwable $e) {
            throw new QueryCompileException('Cannot compile INSERT query - ' . $e->getMessage(), $this, $e);
        }
    }

    /**
     * Compiles the VALUES fragment of the INSERT query.
     *
     * @psalm-mutation-free
     *
     * @param mixed $values A list of records, where each record is a list that contains the record's values.
     * @param int $numColumns The number of columns that the records should have.
     * @return string
     */
    protected static function compileInsertValues($values, int $numColumns): string
    {
        /** @var array<array<string,mixed>> $values */
        if (empty($values) || !is_array($values)) {
            throw new InvalidArgumentException('VALUES list is empty or not an array');
        }

        $valuesList = [];
        foreach ($values as $i => $record) {
            if (empty($record) || !is_array($record)) {
                throw new DomainException("Value set #$i is not an array or is empty");
            } else {
                $numValues = count($record);
                if ($numValues !== $numColumns) {
                    throw new DomainException(
                        "Value set #$i has $numValues values, should have $numColumns"
                    );
                } else {
                    $recordValues = [];
                    foreach ($record as $value) {
                        $recordValues[] = Term::create($value)->toString();
                    }

                    $valuesList[] = '(' . implode(', ', $recordValues) . ')';
                }
            }
        }

        return implode(', ', $valuesList);
    }

    /**
     * @inheritDoc
     *
     * @return int|null The last inserted ID, or null if no rows were inserted.
     */
    public function exec(): ?int
    {
        $adapter = $this->getAdapter();
        $numRows = $adapter->queryNumRows($this->compile());

        if ($numRows > 0) {
            return $adapter->getInsertId();
        } else {
            return null;
        }
    }
}
